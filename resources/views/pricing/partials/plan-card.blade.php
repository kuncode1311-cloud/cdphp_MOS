@php
    $slug = strtolower($pkg->slug ?? '');
    $isStudentPkg = $pkg->isForStudents();
    
    // 🎨 BẢNG MÀU 6 THEME ĐA SẮC MÀU TUẦN HOÀN CHUẨN IC3 ADVENTURE
    $paletteThemes = [
        0 => ['tier' => 'tier-starter',  'icon' => '🚀', 'badge' => '🚀 Trải Nghiệm Khám Phá',       'btn_icon' => '🚀', 'name' => 'Xanh Ngọc Lục Bảo'],
        1 => ['tier' => 'tier-standard', 'icon' => '👑', 'badge' => '👑 Phổ Biến Nhất ⭐',            'btn_icon' => '👑', 'name' => 'Tím Hoàng Gia VIP'],
        2 => ['tier' => 'tier-pro',      'icon' => '🏫', 'badge' => '🔥 Siêu Tiết Kiệm Toàn Trường',  'btn_icon' => '🔥', 'name' => 'Cam Hổ Phách'],
        3 => ['tier' => 'tier-cyan',     'icon' => '💎', 'badge' => '💎 Gói Chuyên Sâu Quốc Tế',     'btn_icon' => '💎', 'name' => 'Xanh Biển Sky Blue'],
        4 => ['tier' => 'tier-rose',     'icon' => '🎯', 'badge' => '🎯 Gói Luyện Thi Bứt Phá',      'btn_icon' => '🎯', 'name' => 'Đỏ San Hô Ruby'],
        5 => ['tier' => 'tier-fuchsia',  'icon' => '🏆', 'badge' => '🏆 Gói Bản Quyền Toàn Diện',    'btn_icon' => '🏆', 'name' => 'Tím Hồng Fuchsia'],
    ];

    if ($isStudentPkg) {
        // Theme riêng rực rỡ cho học sinh
        if (str_contains($slug, 'kham-pha')) {
            $selectedTheme = $paletteThemes[0];
        } elseif (str_contains($slug, 'but-pha')) {
            $selectedTheme = $paletteThemes[1];
        } elseif (str_contains($slug, 'chinh-phuc')) {
            $selectedTheme = $paletteThemes[2];
        } else {
            $selectedTheme = $paletteThemes[$loop->index % count($paletteThemes)];
        }
    } else {
        if (str_contains($slug, 'starter') || str_contains($slug, 'khoi-dau')) {
            $selectedTheme = $paletteThemes[0];
        } elseif (str_contains($slug, 'standard') || str_contains($slug, 'tieu-chuan')) {
            $selectedTheme = $paletteThemes[1];
        } elseif (str_contains($slug, 'pro') || str_contains($slug, 'truong-hoc')) {
            $selectedTheme = $paletteThemes[2];
        } else {
            $selectedTheme = $paletteThemes[$loop->index % count($paletteThemes)];
        }
    }

    $tierClass = $selectedTheme['tier'];
    $btnIcon = $selectedTheme['btn_icon'];

    // Emoji trích xuất thông minh
    $detectedEmoji = null;
    if (!empty($pkg->badge) && preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $pkg->badge, $m)) {
        $detectedEmoji = $m[0];
    } elseif (preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $pkg->name, $m)) {
        $detectedEmoji = $m[0];
    }

    $orbIcon = $detectedEmoji ?: $selectedTheme['icon'];
    $badgeText = $pkg->badge ?: $selectedTheme['badge'];
    $friendlyPkgName = preg_replace('/\s*\((Starter|Standard|Pro School)\)\s*/i', '', $pkg->name);
@endphp

<div class="plan-card {{ $tierClass }}">
    <div class="featured-tag">{{ $badgeText }}</div>

    <div>
        <!-- Orb Icon 3D Nổi Khối Chuẩn Adventure -->
        <div class="plan-orb-wrapper">
            <div class="plan-icon-orb">
                <span class="plan-icon-emoji">{{ $orbIcon }}</span>
            </div>
        </div>

        <div class="plan-header">
            <h3 class="plan-name">{{ $friendlyPkgName }}</h3>
            <p class="plan-desc">{{ $pkg->description }}</p>
        </div>

        <div class="plan-price-wrap">
            @if($pkg->original_price && $pkg->original_price > $pkg->price)
                <div class="plan-original-price">{{ $pkg->formatted_original_price }}</div>
            @else
                <div class="plan-original-price" style="visibility:hidden;">0 đ</div>
            @endif
            <div class="plan-price-main">
                <span class="price-val">{{ $pkg->formatted_price }}</span>
            </div>
            <span class="price-duration">/ {{ $pkg->duration_text }}</span>
        </div>

        <!-- Core Specs (Pod 3D Đa Sắc Màu Chuẩn IC3 Adventure) -->
        <div class="plan-specs">
            @if($isStudentPkg)
                <div class="spec-item spec-students">
                    <div class="spec-label"><i>👤</i> Dành cho:</div>
                    <div class="spec-value">1 Học sinh tự luyện</div>
                </div>
            @else
                <div class="spec-item spec-students">
                    <div class="spec-label"><i>👥</i> Sĩ số quản lý:</div>
                    <div class="spec-value">{{ $pkg->max_students_text }}</div>
                </div>
            @endif
            <div class="spec-item spec-levels">
                <div class="spec-label"><i>🔑</i> Khối lớp:</div>
                <div class="spec-value">{{ $pkg->short_levels_text }}</div>
            </div>
            <div class="spec-item spec-duration">
                <div class="spec-label"><i>📅</i> Thời hạn:</div>
                <div class="spec-value">{{ $pkg->duration_days }} ngày</div>
            </div>
        </div>

        <!-- Features -->
        <ul class="features-list">
            @if(!empty($pkg->features) && is_array($pkg->features))
                @foreach($pkg->features as $feature)
                    <li>
                        <span class="check-icon">✓</span>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            @else
                <li><span class="check-icon">✓</span> Đầy đủ ngân hàng đề thi IC3 GS6</li>
                <li><span class="check-icon">✓</span> Tự động chấm điểm chuẩn quốc tế</li>
                <li><span class="check-icon">✓</span> Báo cáo tiến độ học tập chi tiết</li>
            @endif
        </ul>
    </div>

    <!-- Action Button (Nút Bấm 3D Xúc Giác Cực Đẹp) -->
    <div class="plan-cta-wrap">
        @auth
            @if(! $isStudentPkg && auth()->user()->isStudent())
                <button type="button" class="btn-select-plan" style="background:#f1f5f9; color:#94a3b8; border:2px solid #cbd5e1; cursor:not-allowed; box-shadow:none;" disabled title="Gói này dành riêng cho Giáo viên">
                    🔒 Dành riêng cho Giáo viên
                </button>
            @else
                <button type="button" class="btn-select-plan" onclick="openOrderModal({{ json_encode($pkg) }}, true)">
                    {{ $btnIcon }} {{ $isStudentPkg ? 'Đăng Ký Gói Này' : 'Thuê Gói Này' }} <b>→</b>
                </button>
            @endif
        @else
            <!-- Dành cho Khách chưa có tài khoản -->
            <button type="button" class="btn-select-plan" onclick="openOrderModal({{ json_encode($pkg) }}, false)">
                {{ $btnIcon }} {{ $isStudentPkg ? 'Đăng Ký Tài Khoản Học Sinh' : 'Đăng Ký Tài Khoản Giáo Viên' }} <b>→</b>
            </button>
        @endauth
    </div>
</div>
