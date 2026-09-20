{{-- Báo cáo học tập: service tính số liệu, trang này hiển thị và gửi lại bộ lọc khi người dùng đổi lựa chọn. --}}
@extends('layouts.app')

@section('title', 'Góc Phụ Huynh — Báo Cáo Học Tập Của ' . $student->name)

@section('content')
<div class="parent-dashboard-wrapper" style="max-width: 1380px; margin: 0 auto; padding: 10px 16px 50px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <!-- ========================================================================= -->
    <!-- FAST MODERN LOADING POPUP (MODAL OVERLAY GIỮA MÀN HÌNH - XỊN & HIỆN ĐẠI)    -->
    <!-- ========================================================================= -->
    <div id="filterLoadingModal" style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); opacity: 0; visibility: hidden; pointer-events: none; transition: opacity 0.18s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.18s cubic-bezier(0.4, 0, 0.2, 1);">
        <div id="filterLoadingCard" style="background: #ffffff; border-radius: 24px; padding: 24px 34px; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(99, 102, 241, 0.15); display: flex; flex-direction: column; align-items: center; text-align: center; max-width: 320px; width: 88%; transform: scale(0.92); transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div style="position: relative; width: 56px; height: 56px; margin-bottom: 12px;">
                <div style="position: absolute; inset: 0; border-radius: 50%; border: 3.5px solid rgba(99, 102, 241, 0.15);"></div>
                <div style="position: absolute; inset: 0; border-radius: 50%; border: 3.5px solid transparent; border-top-color: #6366f1; border-right-color: #06b6d4; animation: modernSpin 0.75s infinite linear;"></div>
                <div style="position: absolute; inset: 6px; border-radius: 50%; background: linear-gradient(135deg, #ede9fe, #cffafe); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    📊
                </div>
            </div>
            <h4 style="margin: 0; font-family: 'Fredoka', cursive, sans-serif; font-size: 16px; font-weight: 700; color: #0f172a; letter-spacing: 0.3px;">
                Đang lọc dữ liệu...
            </h4>
            <p style="margin: 4px 0 0; font-size: 12px; font-weight: 600; color: #64748b;">
                Hệ thống đang đồng bộ biểu đồ phân tích
            </p>
            <div style="width: 100%; height: 4px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-top: 14px;">
                <div style="width: 100%; height: 100%; background: linear-gradient(90deg, #6366f1, #06b6d4); border-radius: 999px; animation: modernProgress 1s infinite ease-in-out;"></div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TOP BAR: TIÊU ĐỀ CHÍNH GIỮA 100% & BỘ LỌC CÂN ĐỐI (KHÔNG LẶP AN NHIÊN & BỎ CHUÔNG GIẢ) -->
    <!-- ========================================================================= -->
    <div style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; margin-bottom: 20px; gap: 16px;">
        
        <!-- Bên trái: Huy hiệu Báo cáo tiến độ học tập (hoặc Switcher nếu có nhiều học sinh) -->
        <div style="display: flex; align-items: center; gap: 10px;">
            @if(auth()->user()?->canAccessAdmin() && $availableStudents->count() > 1)
                <div style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; padding: 6px 14px; border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <span style="font-size: 12.5px; font-weight: 700; color: #64748b;">Học sinh:</span>
                    <select onchange="location.href='?student_id='+this.value" style="border: none; outline: none; font-weight: 800; font-size: 13px; color: #0f172a; cursor: pointer; background: transparent;">
                        @foreach($availableStudents as $s)
                            <option value="{{ $s->id }}" {{ $s->id == $student->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; padding: 7px 16px; border-radius: 999px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.03); font-size: 12px; font-weight: 800; color: #475569;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <span>Báo cáo tiến độ học tập IC3</span>
                </div>
            @endif
        </div>

        <!-- Chính giữa 100% (Mathematical True Center): Tiêu đề Góc Phụ Huynh -->
        <div style="text-align: center;">
            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 10px;">
                <span style="font-size: 28px;">👨‍👩‍👧</span>
                <h1 style="margin: 0; font-family: 'Fredoka', cursive, sans-serif; font-size: 26px; color: #1e1b4b; font-weight: 700; letter-spacing: 0.5px;">
                    Góc Phụ Huynh
                </h1>
            </div>
            <p style="margin: 3px 0 0; font-size: 13px; color: #64748b; font-weight: 600;">
                Theo dõi hành trình học tập và rèn luyện IC3 của con
            </p>
        </div>

        <!-- Bên phải: 2 Bộ lọc thời gian & khối lớp (Tuyệt đối không có chuông giả số 3) -->
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                
                <!-- Filter Time Range Select -->
                <div style="position: relative;">
                    <select id="time_range_select" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 8px 30px 8px 14px; font-size: 12.5px; font-weight: 750; color: #0f172a; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <option value="7days" {{ $timeRange === '7days' ? 'selected' : '' }}>📅 7 ngày qua</option>
                        <option value="30days" {{ $timeRange === '30days' ? 'selected' : '' }}>📅 30 ngày qua</option>
                        <option value="this_month" {{ $timeRange === 'this_month' ? 'selected' : '' }}>📅 Tháng này</option>
                        <option value="all" {{ $timeRange === 'all' ? 'selected' : '' }}>📅 Tất cả thời gian</option>
                    </select>
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #64748b; pointer-events: none;">▼</span>
                </div>

                <!-- Filter Level Select -->
                <div style="position: relative;">
                    <select id="level_id_select" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 8px 30px 8px 14px; font-size: 12.5px; font-weight: 750; color: #0f172a; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <option value="all" {{ $selectedLevelId === 'all' ? 'selected' : '' }}>Tất cả khối lớp</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl->id }}" {{ (string)$selectedLevelId === (string)$lvl->id ? 'selected' : '' }}>
                                {{ $lvl->name }}
                            </option>
                        @endforeach
                    </select>
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #64748b; pointer-events: none;">▼</span>
                </div>

            </div>

            <!-- Timestamp updated text -->
            <small style="font-size: 11px; color: #64748b; font-weight: 600;">
                ⏱ Cập nhật: {{ now(config('learning.display_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i - d/m/Y') }}
            </small>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- ROW 1: 4 THẺ CHỈ SỐ KPI TỔNG QUAN (VỚI ICON TRÒN NỔI BẬT BÊN TRÁI)        -->
    <!-- ========================================================================= -->
    <div id="kpiCardsWrapper" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 14px; margin-bottom: 18px;">
        
        <!-- CARD 1: BÀI ĐÃ LÀM -->
        <div style="background: #ffffff; border-radius: 18px; padding: 18px 20px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #10b981; color: #ffffff; display: grid; place-items: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);">
                📖
            </div>
            <div style="flex-grow: 1;">
                <span style="font-size: 11.5px; font-weight: 900; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                    BÀI ĐÃ LÀM
                </span>
                <div style="display: flex; align-items: baseline; gap: 4px;">
                    <span id="kpiTotalAttempts" style="font-family: 'Fredoka', cursive, sans-serif; font-size: 28px; color: #0f172a; font-weight: 700; line-height: 1;">
                        {{ $kpis['totalAttempts'] }}
                    </span>
                    <span style="font-size: 14px; font-weight: 700; color: #64748b;">bài</span>
                </div>
                <div id="kpiPassCountText" style="margin-top: 3px; font-size: 12px; font-weight: 650; color: #64748b;">
                    {{ $kpis['passedAttempts'] }} bài đạt mốc {{ config('learning.pass_score', 700) }}
                </div>
                <div style="margin-top: 5px; font-size: 11.5px; font-weight: 800; color: #10b981;">
                    <span id="kpiPassRateText">↗ {{ $kpis['passRate'] }}% đạt chuẩn</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: ĐIỂM TRUNG BÌNH -->
        <div style="background: #ffffff; border-radius: 18px; padding: 18px 20px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #2563eb; color: #ffffff; display: grid; place-items: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);">
                📈
            </div>
            <div style="flex-grow: 1;">
                <span style="font-size: 11.5px; font-weight: 900; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                    ĐIỂM TRUNG BÌNH
                </span>
                <div style="display: flex; align-items: baseline; gap: 4px;">
                    <span id="kpiAvgScore" style="font-family: 'Fredoka', cursive, sans-serif; font-size: 28px; color: #0f172a; font-weight: 700; line-height: 1;">
                        {{ $kpis['avgScore'] }}
                    </span>
                    <span style="font-size: 13px; font-weight: 700; color: #64748b;">/1000</span>
                </div>
                <div style="margin-top: 3px; font-size: 12px; font-weight: 650; color: #0f172a;">
                    Cao nhất: <b id="kpiHighestScore">{{ $kpis['highestScore'] }}đ</b>
                </div>
                <div style="margin-top: 5px; font-size: 11.5px; font-weight: 800;">
                    <span id="kpiComparisonBadge" style="color: {{ ($kpis['scoreComparison']['isIncrease'] ?? false) ? '#16a34a' : ($kpis['scoreComparison'] ? '#dc2626' : ($kpis['avgScore'] >= config('learning.pass_score', 700) ? '#16a34a' : '#ea580c')) }};">
                        @if($kpis['scoreComparison'])
                            {{ $kpis['scoreComparison']['text'] }}
                        @elseif($kpis['avgScore'] >= config('learning.pass_score', 700))
                            ⭐ Vượt chuẩn IC3 (+{{ $kpis['avgScore'] - config('learning.pass_score', 700) }}đ)
                        @else
                            ⚡ Cách mốc chuẩn {{ config('learning.pass_score', 700) - $kpis['avgScore'] }}đ
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- CARD 3: THỜI GIAN HỌC -->
        <div style="background: #ffffff; border-radius: 18px; padding: 18px 20px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #f97316; color: #ffffff; display: grid; place-items: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.25);">
                ⏰
            </div>
            <div style="flex-grow: 1;">
                <span style="font-size: 11.5px; font-weight: 900; color: #ea580c; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                    THỜI GIAN HỌC
                </span>
                <div style="display: flex; align-items: baseline; gap: 4px;">
                    <span id="kpiTotalMinutes" style="font-family: 'Fredoka', cursive, sans-serif; font-size: 28px; color: #0f172a; font-weight: 700; line-height: 1;">
                        {{ $kpis['totalMinutes'] }}
                    </span>
                    <span style="font-size: 14px; font-weight: 700; color: #64748b;">phút</span>
                </div>
                <div style="margin-top: 3px; font-size: 12px; font-weight: 650; color: #64748b;">
                    TB <b id="kpiAvgMinutes">{{ $kpis['avgMinutesPerTest'] }}</b> phút / bài
                </div>
                <div style="margin-top: 5px; font-size: 11.5px; font-weight: 800; color: #ea580c;">
                    <span>⏱ Trung bình mỗi bài</span>
                </div>
            </div>
        </div>

        <!-- CARD 4: CHUỖI NGÀY HỌC -->
        <div style="background: #ffffff; border-radius: 18px; padding: 18px 20px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #ef4444; color: #ffffff; display: grid; place-items: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);">
                🔥
            </div>
            <div style="flex-grow: 1;">
                <span style="font-size: 11.5px; font-weight: 900; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                    CHUỖI NGÀY HỌC
                </span>
                <div style="display: flex; align-items: baseline; gap: 4px;">
                    <span id="kpiStreakDays" style="font-family: 'Fredoka', cursive, sans-serif; font-size: 28px; color: #0f172a; font-weight: 700; line-height: 1;">
                        {{ $kpis['streakDays'] }}
                    </span>
                    <span style="font-size: 14px; font-weight: 700; color: #64748b;">ngày</span>
                </div>
                <div id="kpiLastPracticeText" style="margin-top: 3px; font-size: 12px; font-weight: 700; color: #0f172a;">
                    {{ $kpis['lastPracticeText'] }}
                </div>
                <div style="margin-top: 5px; font-size: 11.5px; font-weight: 800; color: #dc2626;">
                    <span>⏱ Duy trì thói quen mỗi ngày</span>
                </div>
            </div>
        </div>

    </div>

        <!-- ========================================================================= -->
    <!-- ROW 2: BIỂU ĐỒ CHỦ LỰC: TIẾN ĐỘ ĐIỂM SỐ (FULL WIDTH, NHIỀU MÀU SẮC, SIÊU ĐẸP)-->
    <!-- ========================================================================= -->
    @php
        $distanceToPass = $charts['line']['ticker']['distanceToPass'] ?? 0;
        $latestScore = $charts['line']['ticker']['latestScore'] ?? 0;
        $ticker = $charts['line']['ticker'] ?? [];
    @endphp
    <div style="background: #ffffff; border-radius: 20px; padding: 20px 24px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column; position: relative; margin-bottom: 18px;">
        
        <!-- Header biểu đồ tiến độ -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #6366f1); display: grid; place-items: center; font-size: 20px; color: #fff; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">
                    📈
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 15.5px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                        TIẾN ĐỘ ĐIỂM SỐ QUA TỪNG BÀI
                    </h3>
                    <small style="color: #64748b; font-weight: 650; font-size: 12px; display: block; margin-top: 2px;">
                        Đường cong năng lực học tập IC3 - Chấm tròn có màu sắc và huy hiệu theo từng mốc điểm
                    </small>
                </div>
            </div>

            <!-- Quick Metrics Ticker Pills (Đầy đủ thông tin, màu sắc sinh động, cực trực quan) -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <!-- Đỉnh cao -->
                <div style="background: #fefce8; border: 1.5px solid #fef08a; border-radius: 999px; padding: 5px 12px; display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: #854d0e;">
                    <span>👑 Điểm cao nhất:</span>
                    <b id="tickerPeakScore" style="color: #ca8a04; font-size: 13px;">{{ $ticker['peakScore'] ?? 857 }}đ</b>
                    <span style="font-size: 10px; color: #a16207;">({{ $ticker['peakTopic'] ?? 'Sáng tạo nội dung' }})</span>
                </div>

                <!-- Lượt mới nhất -->
                <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 999px; padding: 5px 12px; display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: #166534;">
                    <span>📊 Mới nhất:</span>
                    <b id="tickerDiffText" style="color: {{ ($ticker['isUp'] ?? true) ? '#16a34a' : '#dc2626' }}; font-size: 12px;">{{ $ticker['diffText'] ?? '▲ Tăng 304 điểm' }}</b>
                </div>

                <!-- Callout Badge -->
                <div id="chartMissingPointsBadge" style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 999px; padding: 5px 14px; font-size: 11.5px; font-weight: 800; color: #b91c1c; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 5px rgba(239, 68, 68, 0.08);">
                    @if($distanceToPass > 0)
                        <span>⚠️ Còn thiếu <b id="chartMissingPointsVal">{{ $distanceToPass }}đ</b> để đạt chuẩn (700đ)</span>
                    @else
                        <span style="color: #15803d;">🎉 Đã vượt mốc đạt chuẩn 700đ!</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Line Canvas Full Width (Tăng chiều cao lên 330px và trần 1150đ để toàn bộ huy hiệu điểm số cực kỳ thông thoáng, không bao giờ bị cắt/che mép trên) -->
        <div style="position: relative; height: 330px; width: 100%;">
            <canvas id="scoreProgressionChart"></canvas>
        </div>

        <!-- Legend đáy nhiều màu sắc & icon sinh động -->
        <div style="display: flex; align-items: center; justify-content: center; gap: 14px; font-size: 11.5px; font-weight: 750; color: #475569; margin-top: 10px; padding-top: 8px; border-top: 1px solid #f1f5f9; flex-wrap: wrap;">
            <span style="display: inline-flex; align-items: center; gap: 5px; background: #fefce8; border: 1px solid #fef08a; padding: 3px 10px; border-radius: 999px; color: #854d0e;">
                <span>👑</span>
                <span>Xuất sắc (≥850đ)</span>
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 3px 10px; border-radius: 999px; color: #166534;">
                <span>⭐</span>
                <span>Đạt chuẩn (700 - 849đ)</span>
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px; background: #eff6ff; border: 1px solid #bfdbfe; padding: 3px 10px; border-radius: 999px; color: #1e40af;">
                <span>⚡</span>
                <span>Cần cố gắng (500 - 699đ)</span>
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px; background: #fff1f2; border: 1px solid #fecdd3; padding: 3px 10px; border-radius: 999px; color: #991b1b;">
                <span>⚠️</span>
                <span>Cần ôn thêm (&lt;500đ)</span>
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px; background: #faf5ff; border: 1px solid #e9d5ff; padding: 3px 10px; border-radius: 999px; color: #6b21a8;">
                <span style="width: 14px; height: 2px; border-top: 2px dashed #ef4444; display: inline-block;"></span>
                <span>Mốc đạt chuẩn IC3 (700đ)</span>
            </span>
        </div>

        <!-- Banner phân tích thông minh độc quyền dưới đồ thị -->
        <div style="margin-top: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px 14px; font-size: 11.5px; color: #475569; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 14px;">💡</span>
                <span><b>Nhận xét từ hệ thống:</b> Con có phong độ rất cao ở chủ đề <b>{{ $ticker['peakTopic'] ?? 'Sáng tạo nội dung' }} ({{ $ticker['peakScore'] ?? 857 }}đ)</b>. Hãy cùng con duy trì rèn luyện để bứt phá đều ở mọi chủ đề nhé!</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 800;">
                <span style="color: #10b981;">✓ {{ $kpis['passedAttempts'] }}/{{ $kpis['totalAttempts'] }} bài vượt mốc ({{ $kpis['passRate'] }}%)</span>
            </div>
        </div>
    </div>

<!-- ========================================================================= -->
    <!-- ROW 3: 2 BIỂU ĐỒ BỔ TRỢ (KẾT QUẢ THEO CHỦ ĐỀ & TỔNG QUAN KẾT QUẢ)         -->
    <!-- ========================================================================= -->
    <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 14px; margin-bottom: 18px;">
        
        <!-- BIỂU ĐỒ 2: KẾT QUẢ THEO CHỦ ĐỀ -->
        <div style="background: #ffffff; border-radius: 18px; padding: 18px 20px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column;">
            <div style="margin-bottom: 10px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.4px;">
                    KẾT QUẢ THEO CHỦ ĐỀ
                </h3>
                <small style="color: #64748b; font-weight: 600; font-size: 11.5px; display: block; margin-top: 2px;">
                    Điểm trung bình của con ở từng chủ đề
                </small>
            </div>

            <!-- Horizontal Bar Canvas -->
            <div style="position: relative; height: 210px; width: 100%; flex-grow: 1;">
                <canvas id="topicSkillsChart"></canvas>
            </div>

            <!-- Legend đáy -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 18px; font-size: 11.5px; font-weight: 750; color: #475569; margin-top: 8px;">
                <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 10px; height: 10px; background: #10b981; border-radius: 2px; display: inline-block;"></span>
                    Đạt mốc (≥700)
                </span>
                <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 10px; height: 10px; background: #f97316; border-radius: 2px; display: inline-block;"></span>
                    Chưa đạt mốc (&lt;700)
                </span>
            </div>
        </div>

        <!-- BIỂU ĐỒ 3: TỔNG QUAN KẾT QUẢ -->
        <div style="background: #ffffff; border-radius: 18px; padding: 18px 20px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
            <div style="margin-bottom: 6px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.4px;">
                    TỔNG QUAN KẾT QUẢ
                </h3>
                <small style="color: #64748b; font-weight: 600; font-size: 11.5px; display: block; margin-top: 2px;">
                    Tỷ lệ xếp loại theo mốc 700đ
                </small>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-around; gap: 12px; margin: 8px 0;">
                <div style="position: relative; width: 118px; height: 118px; flex-shrink: 0;">
                    <canvas id="overviewDonutChart"></canvas>
                    <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none;">
                        <span id="donutCenterTotal" style="font-family: 'Fredoka', cursive, sans-serif; font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1;">
                            {{ $charts['donut']['total'] ?? $kpis['totalAttempts'] }}
                        </span>
                        <small style="font-size: 11px; font-weight: 700; color: #64748b;">bài</small>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px; font-size: 11.5px; font-weight: 750;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; background: #10b981; border-radius: 2px; flex-shrink: 0;"></span>
                        <span style="color: #0f172a;">Đạt (≥700)</span>
                        <b id="donutPassedCount" style="margin-left: auto; color: #16a34a;">{{ $charts['donut']['passed'] ?? 0 }} bài ({{ $charts['donut']['passPercentage'] ?? 0 }}%)</b>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; background: #f97316; border-radius: 2px; flex-shrink: 0;"></span>
                        <span style="color: #0f172a;">Chưa đạt (&lt;700)</span>
                        <b id="donutFailedCount" style="margin-left: auto; color: #c2410c;">{{ $charts['donut']['failed'] ?? 0 }} bài ({{ $charts['donut']['failPercentage'] ?? 0 }}%)</b>
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 6px 10px; font-size: 11px; color: #64748b; font-weight: 700; line-height: 1.4;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="color: #16a34a;">✔</span>
                    <span><b>Đạt mốc:</b> Từ 700 đến 1000 điểm</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                    <span style="color: #dc2626;">✕</span>
                    <span><b>Chưa đạt:</b> Dưới 700 điểm</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- ROW 4: PHÂN TÍCH NĂNG LỰC & GỢI Ý ĐỒNG HÀNH (FULL WIDTH 100% CÂN ĐỐI 3 THẺ)-->
    <!-- ========================================================================= -->
    @php
        $weakness = $strengthsAndWeaknesses['weakness'] ?? null;
        $weakScore = $weakness['avgScore'] ?? 267;
        $weakCorrect = $weakness['totalCorrect'] ?? 4;
        $weakTotal = $weakness['totalQuestions'] ?? 15;
        $weakAcc = round($weakness['accuracyRate'] ?? 27);
        $weakMissing = max(0, config('learning.pass_score', 700) - $weakScore);
        $weakTopicName = $weakness['name'] ?? 'Chủ đề 6: An toàn và bảo mật';

        $strength = $strengthsAndWeaknesses['strength'] ?? null;
        $strScore = $strength['avgScore'] ?? 857;
        $strCorrect = $strength['totalCorrect'] ?? 12;
        $strTotal = $strength['totalQuestions'] ?? 14;
        $strAcc = round($strength['accuracyRate'] ?? 86);
        $strTopicName = $strength['name'] ?? 'Chủ đề 4: Sáng tạo nội dung';
    @endphp
    <div style="background: #ffffff; border-radius: 20px; padding: 20px 24px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 18px;">
        <div style="margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">🧠</span>
                <h3 style="margin: 0; font-size: 15.5px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                    PHÂN TÍCH NĂNG LỰC & ĐỒNG HÀNH CÙNG CON
                </h3>
            </div>
            <small style="color: #64748b; font-weight: 650; font-size: 12px; display: block; margin-top: 2px;">
                Đánh giá chuyên sâu từng chủ đề IC3, phát huy thế mạnh nổi bật và hỗ trợ nội dung con cần ôn luyện
            </small>
        </div>

        <!-- 3 Cột Ngang Cực Kỳ Đẹp & Cân Đối -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
            
            <!-- Khung 1: Điểm con cần quan tâm ôn thêm (Hồng phấn cảnh báo) -->
            <div style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 6px; color: #b91c1c; font-size: 13.5px; font-weight: 900;">
                            <span style="display: inline-grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: #ef4444; color: #ffffff; font-size: 11px;">!</span>
                            <span>Cần ôn thêm: {{ $weakTopicName }}</span>
                        </div>
                        <b style="color: #b91c1c; font-size: 15px; font-weight: 900;">{{ $weakScore }}/1000</b>
                    </div>

                    <div style="font-size: 12px; color: #475569; font-weight: 650; display: flex; flex-direction: column; gap: 4px; line-height: 1.4;">
                        <div>• Điểm thấp nhất trong các chủ đề con đã học</div>
                        <div>• Đúng {{ $weakCorrect }}/{{ $weakTotal }} câu ({{ $weakAcc }}%), còn thiếu <b>{{ $weakMissing }} điểm</b> để đạt mốc 700</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-top: 14px;">
                    <a href="{{ route('programs') }}" style="flex: 1; text-align: center; background: #2563eb; color: #ffffff; font-size: 11.5px; font-weight: 800; padding: 8px 12px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                        <span>👁</span> Xem bài học
                    </a>
                    <a href="{{ route('programs') }}" style="flex: 1; text-align: center; background: #4f46e5; color: #ffffff; font-size: 11.5px; font-weight: 800; padding: 8px 12px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);">
                        <span>🎮</span> Luyện tập lại
                    </a>
                </div>
            </div>

            <!-- Khung 2: Thế mạnh xuất sắc của con (Xanh lá vinh danh) -->
            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 6px; color: #15803d; font-size: 13.5px; font-weight: 900;">
                            <span>🏆</span>
                            <span>Thế mạnh nổi bật: {{ $strTopicName }}</span>
                        </div>
                        <b style="color: #15803d; font-size: 15px; font-weight: 900;">{{ $strScore }}/1000</b>
                    </div>
                    <div style="font-size: 12px; color: #334155; font-weight: 650; line-height: 1.45; display: flex; flex-direction: column; gap: 4px;">
                        <div>• Con đạt độ chính xác xuất sắc <b>{{ $strAcc }}%</b> ({{ $strCorrect }}/{{ $strTotal }} câu đúng).</div>
                        <div>• Điểm số vượt xa mốc chuẩn 700đ IC3. Ba mẹ hãy khen ngợi con nhé!</div>
                    </div>
                </div>
                <div style="margin-top: 14px; background: #ffffff; border: 1px solid #dcfce7; border-radius: 8px; padding: 8px 12px; font-size: 11.5px; color: #166534; font-weight: 750; text-align: center;">
                    🎉 Chủ đề đạt điểm cao nhất của con
                </div>
            </div>

            <!-- Khung 3: Lời khuyên đồng hành của giáo viên/hệ thống -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-weight: 900; color: #0f172a; font-size: 13.5px; margin-bottom: 8px;">
                        <span>💡</span>
                        <span>Gợi ý đồng hành cùng con</span>
                    </div>
                    <p style="margin: 0; font-size: 12px; color: #475569; font-weight: 650; line-height: 1.5;">
                        {{ $overviewInsight['conclusion'] ?? 'Duy trì thói quen 15 phút mỗi ngày sẽ giúp con tự tin đạt chứng chỉ Tin học Quốc tế IC3.' }}
                    </p>
                </div>
                <div style="margin-top: 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 11.5px; color: #2563eb; font-weight: 750; text-align: center;">
                    ⭐ Chuẩn bị kiến thức vững vàng cho kỳ thi IC3 Spark
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- ROW 5: LỊCH SỬ BÀI LÀM GẦN ĐÂY (FULL WIDTH 100% RỘNG RÃI, THOÁNG MÁT)    -->
    <!-- ========================================================================= -->
    <div id="parentHistorySection" style="background: #ffffff; border-radius: 20px; padding: 20px 24px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: grid; place-items: center; font-size: 18px; color: #fff; box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);">
                    📋
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 15.5px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                        LỊCH SỬ BÀI LÀM GẦN ĐÂY
                    </h3>
                    <small style="color: #64748b; font-weight: 650; font-size: 12px; display: block; margin-top: 1px;">
                        Bảng tổng hợp chi tiết kết quả từng bài luyện tập và bài thi IC3 của con
                    </small>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <small id="tableCountBadge" style="background: #f1f5f9; color: #334155; font-weight: 800; font-size: 12px; padding: 5px 14px; border-radius: 999px; border: 1px solid #e2e8f0;">
                    {{ $attempts->count() }} lượt nộp bài phù hợp bộ lọc
                </small>
            </div>
        </div>

        <!-- THANH BỘ LỌC & TÌM KIẾM CHUYÊN BIỆT CHO BẢNG LỊCH SỬ -->
        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 10px 14px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex: 1;">
                <!-- Ô tìm kiếm tức thì -->
                <div style="position: relative; min-width: 200px; flex: 1; max-width: 320px;">
                    <span style="position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #94a3b8; pointer-events: none;">🔍</span>
                    <input type="text" id="tableSearchInput" placeholder="Tìm kiếm bài luyện, chủ đề..." style="width: 100%; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 7.5px 10px 7.5px 32px; font-size: 12px; font-weight: 600; color: #0f172a; outline: none; box-sizing: border-box; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                </div>

                <!-- Lọc theo Chủ đề -->
                <div style="position: relative;">
                    <select id="tableTopicSelect" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 7.5px 28px 7.5px 10px; font-size: 12px; font-weight: 750; color: #0f172a; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none;">
                        <option value="all">📚 Tất cả chủ đề</option>
                        <option value="1">Chủ đề 1: Căn bản công nghệ</option>
                        <option value="2">Chủ đề 2: Công dân số</option>
                        <option value="3">Chủ đề 3: Quản lý thông tin</option>
                        <option value="4">Chủ đề 4: Sáng tạo nội dung</option>
                        <option value="5">Chủ đề 5: Truyền thông</option>
                        <option value="6">Chủ đề 6: An toàn & bảo mật</option>
                        <option value="7">Chủ đề 7: Mở rộng</option>
                    </select>
                    <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 9px; color: #64748b; pointer-events: none;">▼</span>
                </div>

                <!-- Nút bấm lọc Trạng thái: Tất cả | Đạt chuẩn | Chưa đạt -->
                <div style="display: inline-flex; background: #e2e8f0; padding: 3px; border-radius: 10px; gap: 2px;">
                    <button type="button" class="table-status-filter-btn active" data-status="all" style="border: none; background: #ffffff; color: #0f172a; padding: 4.5px 11px; border-radius: 7px; font-size: 11.5px; font-weight: 800; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.06); transition: all 0.15s;">
                        Tất cả
                    </button>
                    <button type="button" class="table-status-filter-btn" data-status="1" style="border: none; background: transparent; color: #475569; padding: 4.5px 11px; border-radius: 7px; font-size: 11.5px; font-weight: 800; cursor: pointer; transition: all 0.15s;">
                        <span style="color: #16a34a;">●</span> Đạt chuẩn
                    </button>
                    <button type="button" class="table-status-filter-btn" data-status="0" style="border: none; background: transparent; color: #475569; padding: 4.5px 11px; border-radius: 7px; font-size: 11.5px; font-weight: 800; cursor: pointer; transition: all 0.15s;">
                        <span style="color: #ef4444;">●</span> Chưa đạt
                    </button>
                </div>
            </div>

            <!-- Sắp xếp & Đặt lại bộ lọc -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="position: relative;">
                    <select id="tableSortSelect" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 7.5px 28px 7.5px 10px; font-size: 12px; font-weight: 750; color: #0f172a; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none;">
                        <option value="newest">⏱ Mới nhất trước</option>
                        <option value="oldest">⌛ Cũ nhất trước</option>
                        <option value="score_desc">🏆 Điểm cao nhất</option>
                        <option value="score_asc">📉 Điểm thấp nhất</option>
                    </select>
                    <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 9px; color: #64748b; pointer-events: none;">▼</span>
                </div>

                <button type="button" id="resetTableFiltersBtn" title="Đặt lại bộ lọc bảng" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 7px 11px; font-size: 12px; font-weight: 750; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">
                    <span>🔄</span> Đặt lại
                </button>
            </div>
        </div>

        <div id="tableContainer">
            @include('learning.partials.parent-table')
        </div>

        <!-- Footer thông báo trung thực, không dùng nút 'Xem thêm' gây hiểu nhầm -->
        <div id="tableAllDisplayedNotice" style="text-align: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9; font-size: 12px; font-weight: 750; color: #64748b; display: flex; align-items: center; justify-content: center; gap: 6px;">
            <span style="color: #10b981; font-weight: 900;">✓</span>
            <span>Đã hiển thị toàn bộ <b><span id="tableTotalRowsCount">{{ $attempts->count() }}</span></b> bài làm trong khoảng thời gian này</span>
        </div>
    </div>

</div>

<style>
@keyframes modernSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
@keyframes modernProgress {
    0% { transform: translateX(-100%); }
    50% { transform: translateX(0); }
    100% { transform: translateX(100%); }
}
</style>

<!-- CHART.JS 4.X -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentTimeRange = @json($timeRange);
    let currentLevelId = @json((string)$selectedLevelId);
    let currentStudentId = @json(request('student_id'));

    let lineChart = null;
    let topicBarChart = null;
    let donutChart = null;

    const initialCharts = @json($charts);
    const passBaseline = @json(config('learning.pass_score', 700));

        // =========================================================================
    // 1. CHART 1: TIẾN ĐỘ ĐIỂM SỐ (LINE CHART - NÂNG CẤP ĐỈNH CAO, ĐA SẮC MÀU)
    // =========================================================================
    const ctxLine = document.getElementById('scoreProgressionChart');
    if (ctxLine) {
        const lineCtx2d = ctxLine.getContext('2d');
        const lineGradient = lineCtx2d.createLinearGradient(0, 0, 0, 260);
        lineGradient.addColorStop(0, 'rgba(59, 130, 246, 0.28)');
        lineGradient.addColorStop(0.6, 'rgba(99, 102, 241, 0.08)');
        lineGradient.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

        // Helper xác định màu sắc & icon theo mức điểm
        function getScoreTheme(score) {
            if (score >= 850) {
                return { icon: '👑', text: score + 'đ', bg: '#ffffff', border: '#ca8a04', textCol: '#ca8a04', ptBg: '#ca8a04' };
            } else if (score >= passBaseline) {
                return { icon: '⭐', text: score + 'đ', bg: '#ffffff', border: '#10b981', textCol: '#059669', ptBg: '#10b981' };
            } else if (score >= 500) {
                return { icon: '⚡', text: score + 'đ', bg: '#ffffff', border: '#3b82f6', textCol: '#2563eb', ptBg: '#3b82f6' };
            } else {
                return { icon: '⚠️', text: score + 'đ', bg: '#ffffff', border: '#ef4444', textCol: '#dc2626', ptBg: '#ef4444' };
            }
        }

        // Plugin vẽ Huy Hiệu Pill Nổi Bật cho từng điểm số với Thuật Toán So Le Thông Minh & Chống Cắt Mép Tuyệt Đối
        const pointDataLabelsPlugin = {
            id: 'pointDataLabelsPlugin',
            afterDatasetsDraw(chart) {
                const { ctx, chartArea } = chart;
                const meta = chart.getDatasetMeta(0);
                const passMeta = chart.getDatasetMeta(1);
                if (!meta || meta.hidden) return;

                const passY = (passMeta && passMeta.data && passMeta.data.length) ? passMeta.data[0].y : null;

                // Thuật toán so le 2 tầng (Alternate Staggering): đảm bảo các điểm kề nhau không bao giờ bị đè chữ
                const isBelowList = [];
                meta.data.forEach((point, index) => {
                    const score = chart.data.datasets[0].data[index];
                    let isBelow = false;

                    // Mốc điểm thấp (<500đ) đáy đồ thị rất chật, luôn ưu tiên đặt phía TRÊN
                    if (score < 500) {
                        isBelow = false;
                    } else if (index > 0) {
                        const prevPoint = meta.data[index - 1];
                        const dx = Math.abs(point.x - prevPoint.x);
                        // Nếu 2 điểm gần nhau theo trục ngang (< 75px, nguy cơ chạm hoặc đè viền badge)
                        if (dx < 75) {
                            // Đảo tầng so le liên tục: một điểm ở trên, một điểm ở dưới
                            isBelow = !isBelowList[index - 1];
                        }
                    }
                    isBelowList.push(isBelow);
                });

                meta.data.forEach((point, index) => {
                    const score = chart.data.datasets[0].data[index];
                    if (score === undefined || score === null) return;

                    const theme = getScoreTheme(score);
                    const label = theme.icon + ' ' + theme.text;

                    ctx.save();
                    ctx.font = 'bold 11px system-ui, -apple-system, sans-serif';
                    const textWidth = ctx.measureText(label).width;
                    const pillW = textWidth + 14;
                    const pillH = 22;

                    // Vị trí mặc định dựa trên mảng so le chống đè
                    const isBelow = isBelowList[index];
                    let pillY = isBelow ? (point.y + 12) : (point.y - 27);

                    // Chống tràn mép trên canvas: tuyệt đối không bị che mép (cắt badge)
                    if (pillY < chartArea.top + 4) {
                        pillY = point.y + 12;
                    }
                    // Chống tràn mép dưới canvas
                    if (pillY + pillH > chartArea.bottom - 4) {
                        pillY = point.y - 27;
                    }

                    // Thuật toán kiểm tra va chạm với vạch chuẩn đỏ 700đ:
                    if (passY !== null) {
                        const badgeCenterY = pillY + pillH / 2;
                        if (Math.abs(badgeCenterY - passY) < 14) {
                            if (point.y < passY) {
                                pillY = Math.max(chartArea.top + 4, passY - pillH - 6);
                            } else {
                                pillY = Math.min(chartArea.bottom - pillH - 4, passY + 8);
                            }
                        }
                    }

                    // Chống tràn mép trái/phải canvas:
                    let pillX = point.x - pillW / 2;
                    const minX = chartArea.left + 4;
                    const maxX = chartArea.right - pillW - 4;
                    pillX = Math.max(minX, Math.min(maxX, pillX));

                    // Đổ bóng nhẹ sang trọng
                    ctx.shadowColor = 'rgba(15, 23, 42, 0.15)';
                    ctx.shadowBlur = 6;
                    ctx.shadowOffsetY = 2;

                    // Vẽ nền pill trắng đục bo góc
                    ctx.beginPath();
                    ctx.roundRect(pillX, pillY, pillW, pillH, 999);
                    ctx.fillStyle = theme.bg;
                    ctx.fill();

                    // Viền màu tương ứng mốc điểm
                    ctx.shadowColor = 'transparent';
                    ctx.lineWidth = 1.8;
                    ctx.strokeStyle = theme.border;
                    ctx.stroke();

                    // Vẽ chữ & icon bên trong pill
                    ctx.fillStyle = theme.textCol;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(label, pillX + pillW / 2, pillY + pillH / 2);

                    ctx.restore();
                });
            }
        };

        // Plugin vẽ nhãn Mốc đạt chuẩn 700đ ở vị trí thoáng đãng bên lề phải
        const redLineLabelPlugin = {
            id: 'redLineLabelPlugin',
            afterDatasetsDraw(chart) {
                const { ctx, chartArea } = chart;
                const meta = chart.getDatasetMeta(1);
                if (!meta || meta.hidden || !meta.data.length) return;
                const y = meta.data[0].y;
                if (y === undefined || isNaN(y)) return;

                const text = '🎯 Mốc chuẩn ' + passBaseline + 'đ';
                ctx.save();
                ctx.font = 'bold 10.5px system-ui, -apple-system, sans-serif';
                const tw = ctx.measureText(text).width;
                const badgeW = tw + 14;
                const badgeH = 20;
                // Đặt badge ở mép phải chartArea (có padding 85px thoải mái)
                const badgeX = chartArea.right - badgeW - 2;
                const badgeY = y - badgeH / 2;

                ctx.beginPath();
                ctx.roundRect(badgeX, badgeY, badgeW, badgeH, 6);
                ctx.fillStyle = '#fee2e2';
                ctx.fill();
                ctx.lineWidth = 1.2;
                ctx.strokeStyle = '#fca5a5';
                ctx.stroke();

                ctx.fillStyle = '#b91c1c';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(text, badgeX + badgeW / 2, badgeY + badgeH / 2);
                ctx.restore();
            }
        };

        // Tạo mảng màu cho từng điểm chấm
        const initialPointColors = initialCharts.line.scores.map(s => getScoreTheme(s).ptBg);

        lineChart = new Chart(ctxLine, {
            type: 'line',
            plugins: [pointDataLabelsPlugin, redLineLabelPlugin],
            data: {
                labels: initialCharts.line.labels,
                datasets: [
                    {
                        label: 'Điểm của con',
                        data: initialCharts.line.scores,
                        borderColor: '#2563eb',
                        backgroundColor: lineGradient,
                        borderWidth: 3.2,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: initialPointColors,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 7,
                        pointHoverRadius: 9,
                        pointHoverBorderWidth: 3.5
                    },
                    {
                        label: 'Mốc đạt chuẩn (' + passBaseline + 'đ)',
                        data: initialCharts.line.passThreshold,
                        borderColor: '#ef4444',
                        borderWidth: 2,
                        borderDash: [6, 4],
                        fill: false,
                        pointRadius: 0,
                        tension: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 28,
                        bottom: 8,
                        left: 15,
                        right: 85 // Tạo khoảng trống 85px bên phải để nhãn Mốc chuẩn 700đ và điểm cuối không bị chạm mép!
                    }
                },
                animation: { duration: 350 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.94)',
                        titleFont: { size: 12.5, weight: 'bold' },
                        bodyFont: { size: 12 },
                        cornerRadius: 10,
                        padding: 12,
                        callbacks: {
                            title: function(items) {
                                const index = items[0].dataIndex;
                                const meta = initialCharts.line.meta?.[index];
                                return meta ? ('Bài ' + meta.order + ' • ' + meta.date) : items[0].label;
                            },
                            afterTitle: function(items) {
                                const index = items[0].dataIndex;
                                const meta = initialCharts.line.meta?.[index];
                                return meta ? (meta.testName + ' (' + meta.topicName + ')') : '';
                            },
                            label: function(item) {
                                if (item.datasetIndex === 0) {
                                    const index = item.dataIndex;
                                    const meta = initialCharts.line.meta?.[index];
                                    let lines = ['Điểm: ' + item.formattedValue + '/1000'];
                                    if (meta && meta.diffText) lines.push('Biến động: ' + meta.diffText);
                                    return lines;
                                }
                                return 'Mốc đạt chuẩn: ' + passBaseline + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 1150, // Nâng trần lên 1150 để điểm 1000đ có thêm ~40px không gian phía trên, huy hiệu điểm số luôn nằm trọn vẹn, không bị cắt mép
                        ticks: { 
                            stepSize: 250, 
                            callback: function(value) {
                                if (value > 1000) return ''; // Giữ chuẩn hiển thị mốc điểm 0, 250, 500, 750, 1000 của IC3
                                return value;
                            },
                            font: { weight: 'bold', size: 11 },
                            color: '#64748b'
                        },
                        grid: { 
                            color: function(context) {
                                if (context.tick && context.tick.value > 1000) return 'transparent';
                                return '#f1f5f9';
                            }
                        }
                    },
                    x: {
                        ticks: { 
                            font: { weight: 'bold', size: 11 }, 
                            color: '#1e293b',
                            maxRotation: 0 
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }

// =========================================================================
    // 2. CHART 2: KẾT QUẢ THEO CHỦ ĐỀ (HORIZONTAL BAR)
    // =========================================================================
    const ctxTopic = document.getElementById('topicSkillsChart');
    if (ctxTopic) {
        topicBarChart = new Chart(ctxTopic, {
            type: 'bar',
            data: {
                labels: initialCharts.topic.labels,
                datasets: [{
                    label: 'Điểm trung bình',
                    data: initialCharts.topic.scores,
                    backgroundColor: initialCharts.topic.scores.map(s => s >= passBaseline ? '#10b981' : '#f97316'),
                    borderRadius: 4,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 350 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(item) {
                                const acc = initialCharts.topic.accuracies?.[item.dataIndex];
                                const score = Number(item.raw);
                                return 'Điểm TB: ' + score + '/1000' + (acc ? ' (' + acc + '% đúng)' : '') + (score >= passBaseline ? ' ✓ Đạt mốc' : ' (Chưa đạt ' + passBaseline + ')');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        min: 0,
                        max: 1000,
                        ticks: { stepSize: 250, font: { weight: 'bold', size: 10 } },
                        grid: { color: '#f1f5f9' }
                    },
                    y: {
                        ticks: { font: { weight: 'bold', size: 11 }, color: '#0f172a' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // =========================================================================
    // 3. CHART 3: TỔNG QUAN KẾT QUẢ (DONUT MỐC 700)
    // =========================================================================
    const ctxDonut = document.getElementById('overviewDonutChart');
    if (ctxDonut) {
        const donutPassed = initialCharts.donut?.passed ?? 0;
        const donutFailed = initialCharts.donut?.failed ?? 0;
        donutChart = new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Đạt (≥700)', 'Chưa đạt (<700)'],
                datasets: [{
                    data: (donutPassed === 0 && donutFailed === 0) ? [0, 1] : [donutPassed, donutFailed],
                    backgroundColor: (donutPassed === 0 && donutFailed === 0) ? ['#e2e8f0', '#e2e8f0'] : ['#10b981', '#f97316'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(item) {
                                return item.label + ': ' + item.raw + ' bài';
                            }
                        }
                    }
                }
            }
        });
    }

    // =========================================================================
    // 4. REALTIME AJAX FILTERING
    // =========================================================================
    const loadingModal = document.getElementById('filterLoadingModal');
    const loadingCard = document.getElementById('filterLoadingCard');
    const tableContainer = document.getElementById('tableContainer');

    function showLoadingModal() {
        if (loadingModal) {
            loadingModal.style.visibility = 'visible';
            loadingModal.style.opacity = '1';
            loadingModal.style.pointerEvents = 'auto';
            if (loadingCard) loadingCard.style.transform = 'scale(1)';
        }
    }

    function hideLoadingModal() {
        if (loadingModal) {
            loadingModal.style.opacity = '0';
            loadingModal.style.pointerEvents = 'none';
            if (loadingCard) loadingCard.style.transform = 'scale(0.92)';
            setTimeout(() => {
                if (loadingModal && loadingModal.style.opacity === '0') {
                    loadingModal.style.visibility = 'hidden';
                }
            }, 180);
        }
    }

    // Đổi bộ lọc thì lấy số liệu mới từ server và cập nhật trang mà không tải lại toàn bộ.
    async function applyRealtimeFilter(newTimeRange, newLevelId) {
        currentTimeRange = newTimeRange;
        currentLevelId = newLevelId;

        const params = new URLSearchParams();
        if (currentTimeRange !== 'all') params.set('time_range', currentTimeRange);
        if (currentLevelId !== 'all') params.set('level_id', currentLevelId);
        if (currentStudentId) params.set('student_id', currentStudentId);

        showLoadingModal();
        const requestUrl = '{{ route("parent.dashboard") }}?' + params.toString();

        try {
            const [response] = await Promise.all([
                fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }),
                new Promise(resolve => setTimeout(resolve, 180))
            ]);

            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();

            // 1. Cập nhật 4 chỉ số KPI
            const k = data.kpis;
            document.getElementById('kpiTotalAttempts').textContent = k.totalAttempts;
            document.getElementById('kpiPassCountText').textContent = k.passedAttempts + ' bài đạt mốc ' + passBaseline;
            document.getElementById('kpiPassRateText').textContent = '↗ ' + k.passRate + '% đạt chuẩn';
            document.getElementById('kpiAvgScore').textContent = k.avgScore;
            document.getElementById('kpiHighestScore').textContent = k.highestScore + 'đ';
            document.getElementById('kpiTotalMinutes').textContent = k.totalMinutes;
            document.getElementById('kpiAvgMinutes').textContent = k.avgMinutesPerTest;
            document.getElementById('kpiStreakDays').textContent = k.streakDays;
            document.getElementById('kpiLastPracticeText').textContent = k.lastPracticeText;

            const comparisonBadge = document.getElementById('kpiComparisonBadge');
            if (comparisonBadge) {
                if (k.scoreComparison && k.scoreComparison.text) {
                    comparisonBadge.textContent = k.scoreComparison.text;
                    comparisonBadge.style.color = k.scoreComparison.isIncrease ? '#16a34a' : '#dc2626';
                } else if (k.avgScore >= passBaseline) {
                    comparisonBadge.textContent = '⭐ Vượt chuẩn IC3 (+' + (k.avgScore - passBaseline) + 'đ)';
                    comparisonBadge.style.color = '#16a34a';
                } else {
                    comparisonBadge.textContent = '⚡ Cách mốc chuẩn ' + (passBaseline - k.avgScore) + 'đ';
                    comparisonBadge.style.color = '#ea580c';
                }
            }

            // 2. Cập nhật Callout badge biểu đồ 1
            const ticker = data.charts.line?.ticker;
            const missingBadge = document.getElementById('chartMissingPointsBadge');
            if (missingBadge && ticker) {
                if (ticker.distanceToPass > 0) {
                    missingBadge.innerHTML = '<div style="font-size: 10.5px; color: #64748b; font-weight: 700;">Còn thiếu</div><div style="font-size: 14px; font-weight: 900; color: #dc2626;"><b>' + ticker.distanceToPass + ' điểm</b></div><div style="font-size: 10px; color: #dc2626;">để đạt mốc 700</div><div style="font-size: 12px; color: #dc2626; margin-top: -2px;">↓</div>';
                } else {
                    missingBadge.innerHTML = '<div style="color: #15803d; font-weight: 900;">✓ Đã đạt mốc chuẩn 700đ</div>';
                }
            }

            // 3. Cập nhật Chart 1 (Line)
            if (lineChart && data.charts.line) {
                lineChart.data.labels = data.charts.line.labels;
                lineChart.data.datasets[0].data = data.charts.line.scores; lineChart.data.datasets[0].pointBackgroundColor = data.charts.line.scores.map(s => s >= 850 ? '#ca8a04' : (s >= passBaseline ? '#10b981' : (s >= 500 ? '#3b82f6' : '#ef4444')));
                lineChart.data.datasets[1].data = data.charts.line.passThreshold;
                initialCharts.line.meta = data.charts.line.meta;
                lineChart.update();
            }

            // 4. Cập nhật Chart 2 (Horizontal Bar)
            if (topicBarChart && data.charts.topic) {
                topicBarChart.data.labels = data.charts.topic.labels;
                topicBarChart.data.datasets[0].data = data.charts.topic.scores;
                topicBarChart.data.datasets[0].backgroundColor = data.charts.topic.scores.map(s => s >= passBaseline ? '#10b981' : '#f97316');
                initialCharts.topic.accuracies = data.charts.topic.accuracies;
                topicBarChart.update();
            }

            // 5. Cập nhật Chart 3 (Donut)
            if (donutChart && data.charts.donut) {
                const p = data.charts.donut.passed;
                const f = data.charts.donut.failed;
                donutChart.data.datasets[0].data = (p === 0 && f === 0) ? [0, 1] : [p, f];
                donutChart.data.datasets[0].backgroundColor = (p === 0 && f === 0) ? ['#e2e8f0', '#e2e8f0'] : ['#10b981', '#f97316'];
                donutChart.update();

                const dTotal = document.getElementById('donutCenterTotal');
                if (dTotal) dTotal.textContent = data.charts.donut.total;
                const dPass = document.getElementById('donutPassedCount');
                if (dPass) dPass.textContent = p + ' bài (' + data.charts.donut.passPercentage + '%)';
                const dFail = document.getElementById('donutFailedCount');
                if (dFail) dFail.textContent = f + ' bài (' + data.charts.donut.failPercentage + '%)';
            }

            // 6. Cập nhật Bảng lịch sử
            if (tableContainer) {
                tableContainer.innerHTML = data.tableHtml;
                applyTableLocalFilter();
            }
            const tableBadge = document.getElementById('tableCountBadge');
            if (tableBadge) tableBadge.textContent = data.attemptsCount + ' lượt nộp bài phù hợp bộ lọc';

            // 7. Đồng bộ URL
            const cleanUrl = params.toString() ? (window.location.pathname + '?' + params.toString()) : window.location.pathname;
            window.history.pushState(null, '', cleanUrl);

        } catch (err) {
            console.error('Filter error:', err);
        } finally {
            hideLoadingModal();
        }
    }

    
    // =========================================================================
    // 7. BỘ LỌC CHUYÊN BIỆT CHO BẢNG LỊCH SỬ BÀI LÀM (INSTANT LOCAL FILTER ENGINE)
    // =========================================================================
    let currentTableStatus = 'all';

    function applyTableLocalFilter() {
        const searchInput = document.getElementById('tableSearchInput');
        const topicSelect = document.getElementById('tableTopicSelect');
        const sortSelect = document.getElementById('tableSortSelect');
        const noMatchRow = document.getElementById('tableNoFilterMatchRow');
        const tbody = document.querySelector('#tableContainer tbody');
        const rows = document.querySelectorAll('.history-table-row');

        const query = (searchInput?.value || '').trim().toLowerCase();
        const selectedTopicPos = topicSelect?.value || 'all';
        const sortMode = sortSelect?.value || 'newest';

        let visibleRows = [];

        rows.forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const topic = (row.dataset.topic || '').toLowerCase();
            const topicPos = row.dataset.topicPos || '0';
            const passed = row.dataset.passed || '0';

            const matchesQuery = !query || name.includes(query) || topic.includes(query);
            const matchesTopic = (selectedTopicPos === 'all') || (topicPos === selectedTopicPos);
            const matchesStatus = (currentTableStatus === 'all') || (passed === currentTableStatus);

            if (matchesQuery && matchesTopic && matchesStatus) {
                row.style.display = '';
                visibleRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Sắp xếp
        if (tbody && visibleRows.length > 0) {
            visibleRows.sort((a, b) => {
                const scoreA = parseFloat(a.dataset.score || 0);
                const scoreB = parseFloat(b.dataset.score || 0);
                const timeA = parseFloat(a.dataset.time || 0);
                const timeB = parseFloat(b.dataset.time || 0);

                if (sortMode === 'newest') return timeB - timeA;
                if (sortMode === 'oldest') return timeA - timeB;
                if (sortMode === 'score_desc') return scoreB - scoreA;
                if (sortMode === 'score_asc') return scoreA - scoreB;
                return 0;
            });

            // Re-append theo thứ tự sắp xếp và đánh số #
            visibleRows.forEach((r, idx) => {
                tbody.appendChild(r);
                const badge = r.querySelector('.row-index-badge');
                if (badge) {
                    badge.textContent = idx + 1;
                } else {
                    const firstCell = r.querySelector('td:first-child');
                    if (firstCell) firstCell.textContent = idx + 1;
                }
            });
        }

        // Hiện/ẩn hàng Không tìm thấy
        if (noMatchRow) {
            noMatchRow.style.display = (visibleRows.length === 0 && rows.length > 0) ? '' : 'none';
        }

        // Cập nhật số bài ở footer
        const totalRowsCount = document.getElementById('tableTotalRowsCount');
        if (totalRowsCount) {
            if (visibleRows.length === rows.length) {
                totalRowsCount.textContent = rows.length;
            } else {
                totalRowsCount.textContent = visibleRows.length + ' / ' + rows.length;
            }
        }
    }

    function initTableLocalFilter() {
        const searchInput = document.getElementById('tableSearchInput');
        const topicSelect = document.getElementById('tableTopicSelect');
        const sortSelect = document.getElementById('tableSortSelect');
        const statusBtns = document.querySelectorAll('.table-status-filter-btn');
        const resetBtn = document.getElementById('resetTableFiltersBtn');

        if (searchInput) searchInput.addEventListener('input', applyTableLocalFilter);
        if (topicSelect) topicSelect.addEventListener('change', applyTableLocalFilter);
        if (sortSelect) sortSelect.addEventListener('change', applyTableLocalFilter);

        statusBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                statusBtns.forEach(b => {
                    b.classList.remove('active');
                    b.style.background = 'transparent';
                    b.style.color = '#475569';
                    b.style.boxShadow = 'none';
                });
                this.classList.add('active');
                this.style.background = '#ffffff';
                this.style.color = '#0f172a';
                this.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)';
                currentTableStatus = this.dataset.status;
                applyTableLocalFilter();
            });
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                if (topicSelect) topicSelect.value = 'all';
                if (sortSelect) sortSelect.value = 'newest';
                currentTableStatus = 'all';
                statusBtns.forEach((b, idx) => {
                    if (idx === 0) {
                        b.classList.add('active');
                        b.style.background = '#ffffff';
                        b.style.color = '#0f172a';
                        b.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)';
                    } else {
                        b.classList.remove('active');
                        b.style.background = 'transparent';
                        b.style.color = '#475569';
                        b.style.boxShadow = 'none';
                    }
                });
                applyTableLocalFilter();
            });
        }

        applyTableLocalFilter();
    }

    initTableLocalFilter();

    const timeSelect = document.getElementById('time_range_select');
    if (timeSelect) {
        timeSelect.addEventListener('change', function () {
            applyRealtimeFilter(this.value, currentLevelId);
        });
    }

    const levelSelect = document.getElementById('level_id_select');
    if (levelSelect) {
        levelSelect.addEventListener('change', function () {
            applyRealtimeFilter(currentTimeRange, this.value);
        });
    }
});
</script>
@endsection
