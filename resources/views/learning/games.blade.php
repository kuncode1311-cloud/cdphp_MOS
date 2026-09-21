{{-- Trang chọn trò chơi giải lao, phong cách Game 3D Adventure rực rỡ đồng bộ toàn hệ thống. --}}
@extends('layouts.app')
@section('title', 'Khu trò chơi — IC3 Quest')

@section('content')
<div class="adventure-world-wrapper">

    <!-- ===================================================================
         🎪 CENTER CAPSULE HEADER 3D: KHU TRÒ CHƠI GIẢI LAO THÔNG MINH
         =================================================================== -->
    <div class="mission-chooser-container" style="margin-bottom: 22px;">
        <div class="choose-mission-capsule" style="background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #3b82f6 100%); border: 3.5px solid #ffffff; box-shadow: 0 12px 28px rgba(139, 92, 246, 0.4), inset 0 -4px 0 rgba(0,0,0,0.25); padding: 12px 32px; border-radius: 999px; display: inline-flex; align-items: center; gap: 12px; color: #ffffff; font-weight: 1000; font-size: 15.5px; letter-spacing: 0.6px; text-transform: uppercase;">
            <span style="font-size: 24px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">🎪</span>
            <span>KHU TRÒ CHƠI GIẢI LAO THÔNG MINH</span>
            <span style="background: rgba(255,255,255,0.25); border: 1.5px solid #ffffff; font-size: 11px; padding: 3px 10px; border-radius: 999px; letter-spacing: 0.5px;">ĐÃ MỞ KHÓA</span>
        </div>
    </div>

    <!-- ===================================================================
         💎 3 VIÊN TINH THỂ NĂNG LƯỢNG 3D (TOP ENERGY PODS)
         =================================================================== -->
    <div class="stat-pods-grid" style="width: 100%; max-width: 1140px; margin-bottom: 24px;">
        
        <!-- Pod 1: Ví Sao Thưởng Của Bé -->
        <div class="stat-pod stat-pod-stars">
            <div class="pod-icon-orb orb-gold-sparkle">
                <span class="pod-icon-emoji">⭐</span>
            </div>
            <div class="pod-content">
                <span class="pod-label">VÍ SAO THƯỞNG CỦA BÉ</span>
                <div class="pod-value value-gold">
                    <span id="display-stars">{{ number_format($rewardStars) }}</span>
                    <span class="pod-unit">Sao</span>
                </div>
                <small class="pod-hint">Tích lũy từ các bài luyện thi IC3 để đổi vé chơi game</small>
            </div>
        </div>

        <!-- Pod 2: Thời Gian Chơi Game Còn Lại -->
        <div class="stat-pod stat-pod-game">
            <div class="pod-icon-orb orb-blue-game">
                <span class="pod-icon-emoji">⏳</span>
            </div>
            <div class="pod-content">
                <span class="pod-label">THỜI GIAN CHƠI GAME CÒN LẠI</span>
                <div class="pod-value value-blue">
                    <span id="display-time" style="color: {{ $gameTimeSeconds > 0 ? '#0284c7' : '#ef4444' }};">
                        {{ floor($gameTimeSeconds / 60) }} phút {{ $gameTimeSeconds % 60 }} giây
                    </span>
                </div>
                <small class="pod-hint">Tự động đếm ngược khi bé tham gia phòng trò chơi</small>
            </div>
        </div>

        <!-- Pod 3: Nút Nạp Giờ Chơi Nhanh 3D -->
        <div class="stat-pod stat-pod-action" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 50%, #fed7aa 100%); border: 3.5px solid #fb923c; box-shadow: 0 12px 28px rgba(249, 115, 22, 0.22), inset 0 -4px 0 rgba(194, 65, 12, 0.18);">
            <div class="pod-icon-orb" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                <span class="pod-icon-emoji">🚀</span>
            </div>
            <div class="pod-content" style="justify-content: center;">
                <span class="pod-label" style="color: #c2410c;">CỬA HÀNG ĐỔI THƯỞNG</span>
                <a href="#shop-exchange-section" class="btn-3d-tactile-orange" style="margin-top: 5px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; border-radius: 14px; font-size: 13.5px; font-weight: 900; color: #ffffff; background: linear-gradient(180deg, #f97316 0%, #ea580c 100%); border: 2.5px solid #ffffff; box-shadow: 0 4px 0 #9a3412, 0 8px 18px rgba(234, 88, 12, 0.35); transition: all 0.15s;">
                    <span>✨</span> Đổi Thêm Giờ Chơi ➔
                </a>
            </div>
        </div>
    </div>

    <!-- ===================================================================
         ⚔️ THẺ GAME NỔI BẬT 3D HERO ARCADE: HIỆP SĨ SONG KIẾM
         =================================================================== -->
    <div class="featured-game-card-3d" style="width: 100%; max-width: 1140px; margin-bottom: 28px; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #3730a3 100%); border: 4px solid #ffffff; border-radius: 28px; box-shadow: 0 20px 48px rgba(15, 23, 42, 0.38), inset 0 -6px 0 rgba(0,0,0,0.25); overflow: hidden; position: relative;">
        <div style="display: grid; grid-template-columns: 1.15fr 1.35fr; gap: 0; align-items: stretch;">
            
            <!-- Cột hình ảnh & Badge Motion Game AR -->
            <div style="position: relative; overflow: hidden; background: linear-gradient(135deg, #0f172a, #1e1b4b); min-height: 280px; display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('images/ic3-quest-hero.png') }}" alt="Hiệp sĩ Song Kiếm" style="width: 100%; height: 100%; object-fit: cover; object-position: center; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                
                <div style="position: absolute; top: 16px; left: 16px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); border: 2px solid #38bdf8; border-radius: 999px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 8px; color: #38bdf8; font-size: 11.5px; font-weight: 900; letter-spacing: 0.6px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    <span style="font-size: 14px;">⚔️</span> MOTION GAME AR
                </div>
                
                <div style="position: absolute; bottom: 14px; left: 16px; right: 16px; background: rgba(2, 6, 23, 0.75); backdrop-filter: blur(6px); border-radius: 12px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; gap: 8px; color: #e2e8f0; font-size: 11px; font-weight: 700;">
                    <span>📷</span> Tự động bật Camera nhận diện chuyển động hai tay
                </div>
            </div>

            <!-- Cột nội dung thông tin & Nút hành động 3D -->
            <div style="padding: 30px 32px; display: flex; flex-direction: column; justify-content: space-between; color: #ffffff;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 900; letter-spacing: 0.5px; margin-bottom: 10px; border: 1.5px solid #fef08a; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                        <span>🔥</span> TRÒ CHƠI TƯƠNG TÁC THỰC TẾ ẢO ĐỘC QUYỀN
                    </div>
                    
                    <h2 style="margin: 0 0 10px; font-family: 'Fredoka', cursive, sans-serif; font-size: 32px; font-weight: 700; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.3); line-height: 1.15;">
                        Hiệp Sĩ Song Kiếm
                    </h2>
                    
                    <p style="margin: 0 0 18px; color: #cbd5e1; font-size: 13.5px; line-height: 1.6; font-weight: 600;">
                        Bật camera, đứng trước màn hình và dùng hai tay điều khiển cặp song kiếm ánh sáng để chém virus độc hại, bảo vệ em bé và tích lũy điểm thưởng!
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 22px;">
                        <div style="display: flex; align-items: center; gap: 10px; color: #e0e7ff; font-size: 12.5px; font-weight: 750;">
                            <span style="width: 22px; height: 22px; border-radius: 50%; background: #10b981; color: #ffffff; display: grid; place-items: center; font-size: 12px; font-weight: 900; border: 1.5px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">✓</span>
                            <span>Điều khiển trực tiếp bằng chuyển động hai tay qua Camera</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; color: #e0e7ff; font-size: 12.5px; font-weight: 750;">
                            <span style="width: 22px; height: 22px; border-radius: 50%; background: #10b981; color: #ffffff; display: grid; place-items: center; font-size: 12px; font-weight: 900; border: 1.5px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">✓</span>
                            <span>Rèn luyện phản xạ nhanh, kích thích vận động thể chất giải lao</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; color: #e0e7ff; font-size: 12.5px; font-weight: 750;">
                            <span style="width: 22px; height: 22px; border-radius: 50%; background: #10b981; color: #ffffff; display: grid; place-items: center; font-size: 12px; font-weight: 900; border: 1.5px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">✓</span>
                            <span>Hệ thống tự động đếm ngược và bảo vệ thời gian chơi của bé</span>
                        </div>
                    </div>
                </div>

                <!-- Khối nút bấm vào chơi game 3D tactile xúc giác -->
                <div>
                    <a id="game-main-link" 
                       href="{{ $gameTimeSeconds > 0 ? asset('games/bao-ve-em-be.html') : 'javascript:void(0)' }}" 
                       onclick="{{ $gameTimeSeconds > 0 ? '' : 'handleNoTimeClick(event)' }}"
                       style="text-decoration: none; display: block;">
                        
                        <div id="game-action-btn" class="btn-play-game-3d" style="width: 100%; padding: 16px 24px; border-radius: 18px; border: 3.5px solid #ffffff; text-align: center; color: #ffffff; font-size: 15.5px; font-weight: 900; letter-spacing: 0.5px; transition: all 0.16s ease; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; background: {{ $gameTimeSeconds > 0 ? 'linear-gradient(180deg, #10b981 0%, #059669 100%)' : 'linear-gradient(180deg, #f97316 0%, #ea580c 100%)' }}; box-shadow: inset 0 -6px 0 {{ $gameTimeSeconds > 0 ? '#047857' : '#9a3412' }}, 0 10px 24px rgba(0,0,0,0.28);">
                            @if($gameTimeSeconds > 0)
                                <span style="font-size: 20px;">🎮</span>
                                <span>VÀO PHÒNG CHƠI NGAY (Còn {{ floor($gameTimeSeconds / 60) }} phút) →</span>
                            @else
                                <span style="font-size: 20px;">🔒</span>
                                <span>HẾT GIỜ CHƠI — ĐỔI SAO ĐỂ VÀO PHÒNG →</span>
                            @endif
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================
         🎮 BỘ SƯU TẬP CÁC MINI-GAME RÈN LUYỆN KỸ NĂNG SỐ (ARCADE SHELF)
         =================================================================== -->
    <div style="width: 100%; max-width: 1140px; margin-bottom: 28px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
            <span style="font-size: 22px;">🕹️</span>
            <h3 style="margin: 0; font-size: 17px; font-weight: 900; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.4);">
                BỘ SƯU TẬP TRÒ CHƠI GIẢI TRÍ & RÈN LUYỆN KỸ NĂNG SỐ
            </h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px;">
            
            <!-- Mini-Game 2: Đấu Sĩ Gõ Phím Siêu Tốc -->
            <div class="mini-game-card-3d" style="background: linear-gradient(135deg, #065f46 0%, #047857 50%, #0d9488 100%); border: 3.5px solid #ffffff; border-radius: 22px; padding: 22px; box-shadow: 0 12px 28px rgba(4, 120, 87, 0.35), inset 0 -4px 0 rgba(0,0,0,0.2); color: #ffffff; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="background: rgba(255,255,255,0.2); border: 1.5px solid rgba(255,255,255,0.4); padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 850;">
                            ⚡ LUYỆN GÕ 10 NGÓN
                        </span>
                        <span style="background: #fef08a; color: #854d0e; font-size: 10.5px; font-weight: 900; padding: 3px 10px; border-radius: 999px;">
                            SẮP RA MẮT
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 10px;">
                        <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(255,255,255,0.2); border: 2px solid #ffffff; display: grid; place-items: center; font-size: 28px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            ⌨️
                        </div>
                        <div>
                            <b style="font-size: 18px; font-family: 'Fredoka', cursive, sans-serif; display: block; line-height: 1.15;">Đấu Sĩ Gõ Phím Siêu Tốc</b>
                            <small style="color: #a7f3d0; font-size: 12px; font-weight: 700;">Rèn phản xạ bàn phím chuẩn IC3</small>
                        </div>
                    </div>

                    <p style="font-size: 12.5px; color: #d1fae5; line-height: 1.5; font-weight: 600; margin: 0 0 16px;">
                        Luyện gõ các thuật ngữ tin học và phím tắt thông dụng chuẩn quốc tế để vượt qua các chướng ngại vật kỳ thú!
                    </p>
                </div>

                <button type="button" class="btn-3d-locked" style="width: 100%; background: rgba(255,255,255,0.18); border: 2px dashed rgba(255,255,255,0.5); color: #ffffff; padding: 11px; border-radius: 14px; font-size: 13px; font-weight: 800; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 6px;" onclick="alert('Trò chơi đang được hoàn thiện cập nhật phiên bản mới. Bé hãy chơi Hiệp Sĩ Song Kiếm trước nhé!')">
                    <span>🔒</span> Đang Nâng Cấp (Tháng 10/2026)
                </button>
            </div>

            <!-- Mini-Game 3: Đố Vui Tốc Độ IC3 -->
            <div class="mini-game-card-3d" style="background: linear-gradient(135deg, #581c87 0%, #6b21a8 50%, #7c3aed 100%); border: 3.5px solid #ffffff; border-radius: 22px; padding: 22px; box-shadow: 0 12px 28px rgba(107, 33, 168, 0.35), inset 0 -4px 0 rgba(0,0,0,0.2); color: #ffffff; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="background: rgba(255,255,255,0.2); border: 1.5px solid rgba(255,255,255,0.4); padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 850;">
                            🧠 ĐỐ VUI CÔNG NGHỆ
                        </span>
                        <span style="background: #fef08a; color: #854d0e; font-size: 10.5px; font-weight: 900; padding: 3px 10px; border-radius: 999px;">
                            SẮP RA MẮT
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 10px;">
                        <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(255,255,255,0.2); border: 2px solid #ffffff; display: grid; place-items: center; font-size: 28px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            💡
                        </div>
                        <div>
                            <b style="font-size: 18px; font-family: 'Fredoka', cursive, sans-serif; display: block; line-height: 1.15;">Đố Vui Phản Xạ 60 Giây</b>
                            <small style="color: #ddd6fe; font-size: 12px; font-weight: 700;">Hỏi nhanh đáp gọn cùng Robot IC3</small>
                        </div>
                    </div>

                    <p style="font-size: 12.5px; color: #ede9fe; line-height: 1.5; font-weight: 600; margin: 0 0 16px;">
                        Thử tài giải đố mẹo công nghệ trong 60 giây, giành cúp Hiệp sĩ thông thái và nhận thêm điểm Sao thưởng!
                    </p>
                </div>

                <button type="button" class="btn-3d-locked" style="width: 100%; background: rgba(255,255,255,0.18); border: 2px dashed rgba(255,255,255,0.5); color: #ffffff; padding: 11px; border-radius: 14px; font-size: 13px; font-weight: 800; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 6px;" onclick="alert('Trò chơi đang được hoàn thiện cập nhật phiên bản mới. Bé hãy chơi Hiệp Sĩ Song Kiếm trước nhé!')">
                    <span>🔒</span> Đang Nâng Cấp (Tháng 10/2026)
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================================
         🏪 CỬA HÀNG ĐỔI GIỜ CHƠI MINI-GAME (3D CARD ĐỒNG BỘ RỰC RỠ)
         =================================================================== -->
    <div id="shop-exchange-section" style="width: 100%; max-width: 1140px; background: #ffffff; border-radius: 28px; padding: 30px; border: 4px solid #ffffff; box-shadow: 0 16px 36px rgba(0,0,0,0.15), inset 0 -4px 0 rgba(0,0,0,0.06); margin-bottom: 30px;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; flex-wrap: wrap; gap: 12px; border-bottom: 2px solid #fed7aa; padding-bottom: 16px;">
            <div>
                <h3 style="margin: 0; font-size: 19px; font-weight: 900; color: #9a3412; text-transform: uppercase; letter-spacing: 0.4px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">🏪</span> CỬA HÀNG QUY ĐỔI GIỜ CHƠI MINI-GAME
                </h3>
                <small style="color: #7c2d12; font-weight: 700; font-size: 13.5px; display: block; margin-top: 4px;">
                    Dùng số Sao tích lũy được từ các bài luyện thi IC3 để đổi lấy phút chơi giải lao lành mạnh!
                </small>
            </div>
            
            <a href="{{ route('programs') }}" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1d4ed8; border: 2px solid #93c5fd; padding: 9px 18px; border-radius: 999px; font-size: 12.5px; font-weight: 850; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 3px 8px rgba(37, 99, 235, 0.15); transition: all 0.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <span>📚</span> Làm bài luyện để nhận thêm Sao ➔
            </a>
        </div>

        <!-- Lưới các gói quy đổi 3D Card -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            @foreach($packages as $pkgId => $pkg)
                <div class="exchange-package-card-3d" style="background: {{ $pkgId == 2 ? 'linear-gradient(145deg, #fff7ed 0%, #ffedd5 50%, #fed7aa 100%)' : 'linear-gradient(145deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)' }}; border: 3.5px solid {{ $pkgId == 2 ? '#fb923c' : '#cbd5e1' }}; border-radius: 24px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 10px 24px rgba(0,0,0,0.08), inset 0 -4px 0 rgba(0,0,0,0.06); position: relative; overflow: hidden; transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                    
                    @if($pkgId == 2)
                        <div style="position: absolute; top: 14px; right: 14px; background: linear-gradient(135deg, #ea580c, #dc2626); color: #ffffff; font-size: 11px; font-weight: 900; padding: 4px 12px; border-radius: 999px; box-shadow: 0 3px 8px rgba(220, 38, 38, 0.35); border: 1.5px solid #ffffff;">
                            {{ $pkg['badge'] ?? '🔥 HOT NHẤT' }}
                        </div>
                    @else
                        <div style="position: absolute; top: 14px; right: 14px; background: #64748b; color: #ffffff; font-size: 11px; font-weight: 850; padding: 4px 12px; border-radius: 999px; border: 1.5px solid #ffffff;">
                            {{ $pkg['badge'] ?? 'TIẾT KIỆM' }}
                        </div>
                    @endif

                    <div>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="width: 56px; height: 56px; border-radius: 18px; background: {{ $pkgId == 2 ? 'linear-gradient(135deg, #ea580c, #f97316)' : 'linear-gradient(135deg, #f59e0b, #fbbf24)' }}; color: #ffffff; display: grid; place-items: center; font-size: 28px; border: 2.5px solid #ffffff; box-shadow: 0 6px 14px rgba(0,0,0,0.18);">
                                {{ $pkgId == 2 ? '🚀' : '⚡' }}
                            </div>
                            <div>
                                <b style="font-size: 18px; color: #0f172a; display: block; font-family: 'Fredoka', cursive, sans-serif;">{{ $pkg['title'] }}</b>
                                <span style="font-size: 13px; color: #475569; font-weight: 700;">Nhận ngay <b style="color: #ea580c;">{{ $pkg['minutes'] }} phút</b> chơi mini-game</span>
                            </div>
                        </div>

                        <!-- Khối chi phí -->
                        <div style="background: #ffffff; border-radius: 16px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; border: 2px solid {{ $pkgId == 2 ? '#fed7aa' : '#e2e8f0' }}; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                            <span style="font-size: 13px; font-weight: 750; color: #475569;">Chi phí quy đổi:</span>
                            <b style="font-size: 22px; color: #ea580c; font-family: 'Fredoka', cursive, sans-serif; display: flex; align-items: center; gap: 4px;">
                                <span>{{ number_format($pkg['stars']) }}</span> <span style="font-size: 18px;">⭐</span>
                            </b>
                        </div>
                    </div>

                    <!-- Nút bấm xúc giác 3D tactile đổi gói -->
                    <div>
                        <button type="button" 
                            onclick="exchangePackage({{ $pkgId }}, {{ $pkg['stars'] }}, {{ $pkg['minutes'] }})"
                            class="btn-exchange-pkg-3d"
                            id="btn-pkg-{{ $pkgId }}"
                            style="width: 100%; border: 3px solid #ffffff; background: {{ $pkgId == 2 ? 'linear-gradient(180deg, #f97316 0%, #ea580c 100%)' : 'linear-gradient(180deg, #f59e0b 0%, #d97706 100%)' }}; color: #ffffff; padding: 14px; border-radius: 16px; font-size: 14.5px; font-weight: 900; cursor: pointer; transition: all 0.15s ease; box-shadow: inset 0 -4px 0 {{ $pkgId == 2 ? '#9a3412' : '#92400e' }}, 0 6px 14px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span style="font-size: 16px;">✨</span> Đổi Gói Ngay
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Hộp thông báo kết quả đổi gói -->
        <div id="exchange-feedback" style="display: none; margin-top: 20px; padding: 16px 20px; border-radius: 16px; font-size: 14px; font-weight: 800; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.06);"></div>
    </div>
</div>

<style>
    /* ===================================================================
       🌟 PHONG CÁCH NỀN PHIÊU LƯU ĐỒNG BỘ TOÀN DỰ ÁN
       =================================================================== */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 28px 20px 70px;
        background: transparent;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
    .adventure-world-wrapper:before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.12) 0%, rgba(0, 0, 0, 0.25) 100%);
        pointer-events: none;
    }
    .adventure-world-wrapper > * {
        position: relative;
        z-index: 2;
    }

    /* 3D Stat Energy Pods (Đồng bộ với achievements.blade.php) */
    .stat-pods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 18px;
    }
    .stat-pod {
        border-radius: 24px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.22s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    .stat-pod:hover {
        transform: translateY(-4px) scale(1.015);
    }

    /* Pod 1: Ví Sao Thưởng (Hổ Phách Vàng Kim) */
    .stat-pod-stars {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 40%, #fde68a 100%);
        border: 3.5px solid #fcd34d;
        box-shadow: 0 12px 28px rgba(245, 158, 11, 0.22), inset 0 -4px 0 rgba(217, 119, 6, 0.18);
    }
    .stat-pod-stars .pod-label { color: #92400e; }
    .stat-pod-stars .pod-value { color: #78350f; }
    .stat-pod-stars .pod-unit { color: #b45309; }
    .stat-pod-stars .pod-hint { color: #92400e; font-weight: 700; }

    /* Pod 2: Thời Gian Chơi Game (Lam Biển Sky Blue) */
    .stat-pod-game {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 40%, #bae6fd 100%);
        border: 3.5px solid #7dd3fc;
        box-shadow: 0 12px 28px rgba(2, 132, 199, 0.22), inset 0 -4px 0 rgba(3, 105, 161, 0.18);
    }
    .stat-pod-game .pod-label { color: #0369a1; }
    .stat-pod-game .pod-value { color: #0284c7; }
    .stat-pod-game .pod-hint { color: #0284c7; font-weight: 700; }

    .pod-icon-orb {
        width: 60px;
        height: 60px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        font-size: 30px;
        flex-shrink: 0;
        border: 3px solid #ffffff;
        box-shadow: 0 8px 18px rgba(0,0,0,0.14), inset 0 -3px 0 rgba(0,0,0,0.15);
    }
    .orb-gold-sparkle { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .orb-blue-game { background: linear-gradient(135deg, #38bdf8, #0284c7); }

    .pod-content { display: flex; flex-direction: column; flex: 1; }
    .pod-label {
        font-size: 11px;
        font-weight: 850;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .pod-value {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.15;
        margin: 3px 0;
    }
    .pod-unit {
        font-size: 14px;
        font-weight: 700;
        margin-left: 2px;
    }
    .pod-hint {
        font-size: 11.5px;
        line-height: 1.35;
    }

    /* Hiệu ứng xúc giác cho nút bấm 3D Tactile */
    .btn-play-game-3d:hover {
        transform: translateY(-3px);
        filter: brightness(1.06);
    }
    .btn-play-game-3d:active {
        transform: translateY(4px);
        box-shadow: inset 0 -2px 0 rgba(0,0,0,0.3), 0 4px 10px rgba(0,0,0,0.2) !important;
    }

    .btn-exchange-pkg-3d:hover {
        transform: translateY(-2px);
        filter: brightness(1.05);
    }
    .btn-exchange-pkg-3d:active {
        transform: translateY(3px);
        box-shadow: inset 0 -1px 0 rgba(0,0,0,0.3), 0 2px 6px rgba(0,0,0,0.15) !important;
    }

    .btn-3d-tactile-orange:hover {
        transform: translateY(-2px);
        filter: brightness(1.06);
    }
    .btn-3d-tactile-orange:active {
        transform: translateY(3px);
        box-shadow: 0 1px 0 #9a3412, 0 3px 8px rgba(234, 88, 12, 0.25) !important;
    }

    .exchange-package-card-3d:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.12), inset 0 -4px 0 rgba(0,0,0,0.06);
    }

    .mini-game-card-3d {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .mini-game-card-3d:hover {
        transform: translateY(-4px);
    }

    @media (max-width: 900px) {
        .featured-game-card-3d > div {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    let currentStars = {{ (int) $rewardStars }};
    let currentGameTime = {{ (int) $gameTimeSeconds }};

    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m} phút ${s} giây`;
    }

    function handleNoTimeClick(e) {
        e.preventDefault();
        const shop = document.getElementById('shop-exchange-section');
        shop.scrollIntoView({ behavior: 'smooth' });
        const feedback = document.getElementById('exchange-feedback');
        feedback.style.display = 'block';
        feedback.style.background = '#fef2f2';
        feedback.style.color = '#b91c1c';
        feedback.style.border = '2px solid #fca5a5';
        feedback.innerHTML = '⏰ Thời gian chơi game của bé hiện tại đang là 0. Hãy chọn gói bên dưới để đổi Sao lấy giờ chơi nhé!';
    }

    async function exchangePackage(packageId, stars, minutes) {
        const btn = document.getElementById(`btn-pkg-${packageId}`);
        const feedback = document.getElementById('exchange-feedback');

        if (currentStars < stars) {
            feedback.style.display = 'block';
            feedback.style.background = '#fef2f2';
            feedback.style.color = '#b91c1c';
            feedback.style.border = '2px solid #fca5a5';
            feedback.innerHTML = `⚠️ Bé chưa đủ Sao thưởng (Hiện có ${currentStars.toLocaleString('vi-VN')} ⭐ / Cần ${stars.toLocaleString('vi-VN')} ⭐). Hãy hoàn thành thêm bài thi để nhận thêm Sao nhé!`;
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Đang quy đổi...';
        feedback.style.display = 'none';

        try {
            const res = await fetch('{{ route('games.exchange') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ package: packageId })
            });

            const data = await res.json();

            if (data.success) {
                currentStars = data.remaining_stars;
                currentGameTime = data.game_time_seconds;

                // Cập nhật giao diện đồng bộ
                if (typeof window.updateGlobalStarWallet === 'function') {
                    window.updateGlobalStarWallet(currentStars);
                }
                const displayStarsEl = document.getElementById('display-stars');
                if (displayStarsEl) {
                    displayStarsEl.innerText = currentStars.toLocaleString('vi-VN');
                }
                const displayTime = document.getElementById('display-time');
                if (displayTime) {
                    displayTime.innerText = formatTime(currentGameTime);
                    displayTime.style.color = '#0284c7';
                }

                // Mở khóa nút chơi game
                const gameLink = document.getElementById('game-main-link');
                const actionBtn = document.getElementById('game-action-btn');
                gameLink.href = '{{ asset('games/bao-ve-em-be.html') }}';
                gameLink.onclick = null;
                actionBtn.style.background = 'linear-gradient(180deg, #10b981 0%, #059669 100%)';
                actionBtn.style.boxShadow = 'inset 0 -6px 0 #047857, 0 10px 24px rgba(16, 185, 129, 0.45)';
                actionBtn.innerHTML = `<span style="font-size: 20px;">🎮</span> <span>VÀO PHÒNG CHƠI NGAY (Còn ${Math.floor(currentGameTime / 60)} phút) →</span>`;

                feedback.style.display = 'block';
                feedback.style.background = '#f0fdf4';
                feedback.style.color = '#15803d';
                feedback.style.border = '2px solid #86efac';
                feedback.innerHTML = `🎉 Tuyệt vời! ${data.message} (Thời gian chơi hiện có: <b>${formatTime(currentGameTime)}</b>). <a href="{{ asset('games/bao-ve-em-be.html') }}" style="color: #15803d; text-decoration: underline; font-weight: 850; margin-left: 8px;">Vào chơi ngay ➔</a>`;
            } else {
                feedback.style.display = 'block';
                feedback.style.background = '#fef2f2';
                feedback.style.color = '#b91c1c';
                feedback.style.border = '2px solid #fca5a5';
                feedback.innerHTML = `⚠️ ${data.message || 'Không thể đổi gói. Vui lòng thử lại!'}`;
            }
        } catch (err) {
            feedback.style.display = 'block';
            feedback.style.background = '#fef2f2';
            feedback.style.color = '#b91c1c';
            feedback.style.border = '2px solid #fca5a5';
            feedback.innerHTML = '⚠️ Lỗi kết nối máy chủ. Vui lòng kiểm tra lại mạng.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
</script>
@endsection
