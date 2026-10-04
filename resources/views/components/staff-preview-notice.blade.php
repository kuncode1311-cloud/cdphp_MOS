{{--
    Thông báo cho Quản trị viên/Giáo viên khi xem các trang dành cho học sinh:
    bài làm thử của họ không được lưu nên dữ liệu cá nhân (sao, điểm, câu sai) luôn trống.
--}}
@props(['text' => 'Bài làm thử của Quản trị viên và Giáo viên không được lưu vào hệ thống, nên phần này sẽ trống. Hãy đăng nhập tài khoản học sinh để xem dữ liệu thật.'])
@auth
    @if(! auth()->user()->isStudent())
        <div role="note" style="margin-bottom: 18px; padding: 12px 16px; border-radius: 14px; border: 2px solid #f59e0b; background: #fffbeb; color: #92400e; font-weight: 800; font-size: 13.5px; display: flex; gap: 10px; align-items: center; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.10);">
            <span style="font-size: 20px;">👀</span>
            <span>Bạn đang xem với tư cách {{ auth()->user()->isAdmin() ? 'Quản trị viên' : 'Giáo viên' }}. {{ $text }}</span>
        </div>
    @endif
@endauth
