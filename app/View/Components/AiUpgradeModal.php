<?php

namespace App\View\Components;

use App\Models\Package;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Bảng "Nâng cấp Trợ lý AI": liệt kê các gói Trợ lý AI đang mở bán (lấy từ cơ sở dữ liệu) và thanh toán PayOS ngay trong bảng.
 * Học sinh thấy gói dành cho học sinh, Giáo viên thấy gói dành cho giáo viên.
 */
class AiUpgradeModal extends Component
{
    /** @var Collection<int, Package> */
    public Collection $packages;

    public function __construct()
    {
        $user = auth()->user();

        $query = Package::active()->where('grants_ai_assistant', true)->ordered();
        $user?->isStudent() ? $query->forStudents() : $query->forTeachers();

        $this->packages = $user && ! $user->canAccessAdmin() ? $query->get() : collect();
    }

    public function render(): View
    {
        return view('components.ai-upgrade-modal');
    }
}
