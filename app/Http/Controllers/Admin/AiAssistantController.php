<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Quản lý Trợ lý AI: gói bán, tài khoản đang có quyền dùng AI, và lịch sử mua gói AI.
 * Gói AI không hiện trong Danh Mục Gói Dịch Vụ, mọi thao tác với gói AI nằm ở đây.
 */
class AiAssistantController extends Controller
{
    private const TABS = ['goi', 'tai-khoan', 'lich-su'];
    private const STATUSES = ['tat-ca', 'con-han', 'het-han'];
    private const DEFAULT_EXTEND_DAYS = 30;
    private const EXPIRING_DAYS = 7;

    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'tai-khoan';
        $status = in_array($request->query('trang_thai'), self::STATUSES, true) ? $request->query('trang_thai') : 'tat-ca';
        $search = trim((string) $request->query('q', ''));

        // Bộ lọc lịch sử mua dùng tham số riêng để không lẫn với bộ lọc tài khoản
        $ordersSearch = trim((string) $request->query('don_q', ''));
        $ordersStatus = in_array($request->query('don_trang_thai'), ['tat-ca', 'cho-thanh-toan', 'da-kich-hoat', 'da-huy'], true) ? $request->query('don_trang_thai') : 'tat-ca';
        $ordersRange = in_array($request->query('don_thoi_gian'), ['tat-ca', '7', '30', '90'], true) ? $request->query('don_thoi_gian') : 'tat-ca';

        $packages = Package::where('grants_ai_assistant', true)->orderBy('target_audience')->orderBy('price')->get();
        $aiOrders = PackageOrder::query()
            ->where('status', PackageOrder::STATUS_ACTIVE)
            ->whereHas('package', fn ($q) => $q->where('grants_ai_assistant', true));

        return view('admin.ai-assistant.index', [
            'tab' => $tab,
            'status' => $status,
            'search' => $search,
            'packages' => $packages,
            'stats' => [
                'goi_dang_ban' => $packages->where('is_active', true)->count(),
                'goi_tong' => $packages->count(),
                'dang_dung' => User::where('ai_assistant_until', '>', now())->count(),
                'sap_het_han' => User::whereBetween('ai_assistant_until', [now(), now()->addDays(self::EXPIRING_DAYS)])->count(),
                'so_don' => (clone $aiOrders)->count(),
                'tong_don' => PackageOrder::query()->whereHas('package', fn ($q) => $q->where('grants_ai_assistant', true))->count(),
                'doanh_thu' => (int) (clone $aiOrders)->sum('price'),
            ],
            // Tải sẵn cả ba tab để chuyển tab không cần tải lại trang
            'members' => $this->members($status, $search),
            'orders' => $this->orders($ordersSearch, $ordersStatus, $ordersRange),
            'ordersSearch' => $ordersSearch,
            'ordersStatus' => $ordersStatus,
            'ordersRange' => $ordersRange,
            'expiringDays' => self::EXPIRING_DAYS,
        ]);
    }

    /** Tạo gói Trợ lý AI mới. Gói học sinh luôn 1 HS, gói giáo viên không giới hạn số HS. */
    public function storePackage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_audience' => ['required', Rule::in(['student', 'teacher'])],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $package = Package::create([
            'slug' => Str::slug($data['name']) . '-ai-' . Str::lower(Str::random(5)),
            'name' => $data['name'],
            'target_audience' => $data['target_audience'],
            'price' => $data['price'],
            'duration_days' => $data['duration_days'],
            'max_students' => $data['target_audience'] === 'student' ? 1 : 0,
            'is_active' => $request->boolean('is_active', true),
            'grants_ai_assistant' => true,
            'description' => 'Trợ lý AI tra cứu kết quả học tập và mở bài thi.',
        ]);

        Log::info('Admin tạo gói Trợ lý AI', ['admin_id' => $request->user()->id, 'package_id' => $package->id]);

        return back()->with('ok', "Đã tạo gói \"{$package->name}\".");
    }

    /** Sửa tên, giá, thời hạn và trạng thái mở bán của gói Trợ lý AI */
    public function updatePackage(Request $request, Package $package): RedirectResponse
    {
        abort_unless($package->grants_ai_assistant, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $package->update($data + ['is_active' => $request->boolean('is_active')]);

        Log::info('Admin sửa gói Trợ lý AI', ['admin_id' => $request->user()->id, 'package_id' => $package->id]);

        return back()->with('ok', "Đã lưu gói \"{$package->name}\".");
    }

    /** Gia hạn quyền AI: cộng thêm số ngày vào hạn hiện tại (hoặc tính từ hôm nay nếu đã hết hạn) */
    /**
     * Cấp Trợ lý AI cho một tài khoản chưa có (tặng, dùng thử, hỗ trợ khách): tìm theo mã học sinh, email hoặc đúng họ tên.
     * Tài khoản đang còn hạn thì cộng dồn thêm ngày như gia hạn.
     */
    public function grant(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account' => ['required', 'string', 'max:120'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ], [], ['account' => 'mã học sinh, email hoặc họ tên']);

        $key = trim($data['account']);
        $found = User::query()
            ->whereIn('role', ['student', 'teacher'])
            ->where(fn ($q) => $q->where('student_code', $key)->orWhere('email', $key)->orWhere('name', $key))
            ->limit(3)
            ->get();

        if ($found->isEmpty()) {
            return back()->withErrors(['account' => "Không tìm thấy tài khoản «{$key}». Thử nhập mã học sinh hoặc email."])->withInput();
        }
        if ($found->count() > 1) {
            return back()->withErrors(['account' => "Có nhiều tài khoản tên «{$key}». Hãy nhập mã học sinh hoặc email để chọn đúng người."])->withInput();
        }

        return $this->extend($request, $found->first());
    }

    public function extend(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);
        $days = (int) ($data['days'] ?? self::DEFAULT_EXTEND_DAYS);

        $base = $user->hasAiAssistant() ? $user->ai_assistant_until->copy() : now();
        $user->forceFill(['ai_assistant_until' => $base->addDays($days)])->save();

        Log::info('Admin gia hạn Trợ lý AI', ['admin_id' => $request->user()->id, 'user_id' => $user->id, 'days' => $days]);

        return back()->with('ok', "Đã gia hạn Trợ lý AI cho {$user->name} thêm {$days} ngày.");
    }

    /** Thu hồi quyền dùng AI ngay lập tức (không ảnh hưởng hạn học tập) */
    public function revoke(Request $request, User $user): RedirectResponse
    {
        $user->forceFill(['ai_assistant_until' => null])->save();

        Log::info('Admin thu hồi Trợ lý AI', ['admin_id' => $request->user()->id, 'user_id' => $user->id]);

        return back()->with('ok', "Đã thu hồi quyền Trợ lý AI của {$user->name}.");
    }

    /**
     * Tài khoản có quyền AI, sắp hết hạn lên đầu. Lọc theo trạng thái và tìm theo tên, email, mã HS.
     *
     * @return LengthAwarePaginator<int, User>
     */
    private function members(string $status, string $search): LengthAwarePaginator
    {
        return User::query()
            ->whereNotNull('ai_assistant_until')
            ->when($status === 'con-han', fn ($q) => $q->where('ai_assistant_until', '>', now()))
            ->when($status === 'het-han', fn ($q) => $q->where('ai_assistant_until', '<=', now()))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('ai_assistant_until')
            ->paginate(20)
            ->withQueryString();
    }

    /** Lịch sử mua gói Trợ lý AI (mọi trạng thái), mới nhất trước */
    /**
     * Lịch sử mua gói AI, lọc theo từ khóa, trạng thái và khoảng thời gian. Mới nhất trước.
     *
     * @return LengthAwarePaginator<int, PackageOrder>
     */
    private function orders(string $search, string $status, string $range): LengthAwarePaginator
    {
        $statusMap = [
            'cho-thanh-toan' => PackageOrder::STATUS_PENDING,
            'da-kich-hoat' => PackageOrder::STATUS_ACTIVE,
            'da-huy' => PackageOrder::STATUS_REJECTED,
        ];

        return PackageOrder::query()
            ->with(['user:id,name,email,student_code', 'package:id,name'])
            ->whereHas('package', fn ($q) => $q->where('grants_ai_assistant', true))
            ->when(isset($statusMap[$status]), fn ($q) => $q->where('status', $statusMap[$status]))
            ->when($range !== 'tat-ca', fn ($q) => $q->where('created_at', '>=', now()->subDays((int) $range)))
            ->when($search !== '', function ($q) use ($search) {
                $like = "%{$search}%";
                $q->where(function ($inner) use ($like) {
                    $inner->where('code', 'like', $like)
                        ->orWhere('package_name', 'like', $like)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('student_code', 'like', $like));
                });
            })
            ->latest('id')
            ->paginate(20, ['*'], 'trang')
            ->withQueryString();
    }
}
