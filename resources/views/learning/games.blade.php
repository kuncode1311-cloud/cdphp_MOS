{{-- Trang chọn trò chơi giải lao, dùng chung khung layouts/app. --}}
@extends('layouts.app')
@section('title', 'Khu trò chơi — IC3 Quest')

@section('content')
<div class="page-wrap nav-page">
    <div class="mission-heading" style="margin-top: 10px;">
        <span>🎪</span>
        <div>
            <b>KHU TRÒ CHƠI GIẢI LAO THÔNG MINH</b>
            <small>Dùng Sao thưởng luyện thi để đổi lấy thời gian chơi các mini-game rèn phản xạ</small>
        </div>
        <em>ĐÃ MỞ KHÓA</em>
    </div>

    <!-- Thanh trạng thái Sao & Thời gian chơi game của bé -->
    <div style="background: linear-gradient(135deg, #1e1b4b, #312e81); border: 2px solid #6366f1; border-radius: 20px; padding: 18px 24px; margin-top: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 10px 25px rgba(49, 46, 129, 0.35);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.15); display: grid; place-items: center; font-size: 26px;">
                ⭐
            </div>
            <div>
                <span style="font-size: 11px; font-weight: 800; color: #a5b4fc; text-transform: uppercase; letter-spacing: 0.5px;">Ví Sao Của Bé</span>
                <b style="font-size: 24px; color: #fde047; display: block; font-family: 'Fredoka', cursive, sans-serif; line-height: 1.1;">
                    <span id="display-stars">{{ number_format($rewardStars) }}</span> <small style="font-size: 14px; color: #cbd5e1;">Sao</small>
                </b>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.15); display: grid; place-items: center; font-size: 26px;">
                ⏱️
            </div>
            <div>
                <span style="font-size: 11px; font-weight: 800; color: #a5b4fc; text-transform: uppercase; letter-spacing: 0.5px;">Thời Gian Chơi Game Còn Lại</span>
                <b style="font-size: 24px; color: {{ $gameTimeSeconds > 0 ? '#4ade80' : '#f87171' }}; display: block; font-family: 'Fredoka', cursive, sans-serif; line-height: 1.1;" id="time-display-wrap">
                    <span id="display-time">{{ floor($gameTimeSeconds / 60) }} phút {{ $gameTimeSeconds % 60 }} giây</span>
                </b>
            </div>
        </div>

        <div>
            <a href="#shop-exchange-section" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 14px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);">
                <span>✨</span> Đổi Thêm Giờ Chơi
            </a>
        </div>
    </div>

    <!-- Thẻ Game Nổi Bật -->
    <a class="featured-game arcade-hero" id="game-main-link" href="{{ $gameTimeSeconds > 0 ? asset('games/bao-ve-em-be.html') : 'javascript:void(0)' }}" onclick="{{ $gameTimeSeconds > 0 ? '' : 'handleNoTimeClick(event)' }}" style="margin-top: 20px; text-decoration: none;">
        <div class="game-media-col">
            <img src="{{ asset('images/ic3-quest-hero.png') }}" alt="Hiệp sĩ Song Kiếm">
            <div class="game-badge-pulse">
                <span>⚔️</span> MOTION GAME
            </div>
        </div>
        <div class="game-info-col">
            <span class="game-tag">🔥 TRÒ CHƠI NỔI BẬT NHẤT</span>
            <h2>Hiệp Sĩ Song Kiếm</h2>
            <p>Bật camera, đứng trước màn hình và dùng hai tay điều khiển cặp song kiếm ánh sáng để chém virus độc hại, bảo vệ em bé và tích điểm thưởng!</p>
            <div class="game-features">
                <span>✓ Điều khiển bằng chuyển động cơ thể</span>
                <span>✓ Rèn luyện phản xạ nhanh</span>
                <span>✓ Hệ thống tự đếm ngược thời gian chơi</span>
            </div>
            <div class="game-btn-wrap">
                <b class="game-play-btn" id="game-action-btn" style="background: {{ $gameTimeSeconds > 0 ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ea580c, #c2410c)' }};">
                    @if($gameTimeSeconds > 0)
                        <span>🎮</span> VÀO PHÒNG CHƠI NGAY (Còn {{ floor($gameTimeSeconds / 60) }}p) →
                    @else
                        <span>🔒</span> HẾT GIỜ CHƠI — ĐỔI SAO ĐỂ VÀO PHÒNG →
                    @endif
                </b>
            </div>
        </div>
    </a>

    <!-- ===================================================================
         🏪 CỬA HÀNG ĐỔI GIỜ CHƠI MINI-GAME
         =================================================================== -->
    <div id="shop-exchange-section" style="background: #ffffff; border-radius: 24px; padding: 26px; border: 2px solid #fed7aa; box-shadow: 0 6px 24px rgba(249, 115, 22, 0.08); margin-top: 28px; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 900; color: #9a3412; text-transform: uppercase; letter-spacing: 0.4px; display: flex; align-items: center; gap: 8px;">
                    <span>🏪</span> CỬA HÀNG QUY ĐỔI GIỜ CHƠI MINI-GAME
                </h3>
                <small style="color: #7c2d12; font-weight: 650; font-size: 13px; display: block; margin-top: 3px;">
                    Dùng số Sao tích lũy được từ bài luyện thi IC3 để đổi lấy phút chơi giải lao!
                </small>
            </div>
            <a href="{{ route('programs') }}" style="background: #eff6ff; color: #2563eb; border: 1.5px solid #bfdbfe; padding: 8px 16px; border-radius: 999px; font-size: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>📚</span> Làm bài luyện để nhận thêm Sao ➔
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
            @foreach($packages as $pkgId => $pkg)
                <div style="background: #ffffff; border: 2px solid {{ $pkgId == 2 ? '#fb923c' : '#fed7aa' }}; border-radius: 20px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 14px rgba(0,0,0,0.04); position: relative; overflow: hidden;">
                    @if($pkgId == 2)
                        <div style="position: absolute; top: 14px; right: 14px; background: linear-gradient(135deg, #ea580c, #dc2626); color: #fff; font-size: 11px; font-weight: 800; padding: 3px 12px; border-radius: 999px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);">
                            {{ $pkg['badge'] ?? 'HOT NHẤT' }}
                        </div>
                    @endif
                    <div>
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                            <div style="width: 52px; height: 52px; border-radius: 16px; background: {{ $pkgId == 2 ? 'linear-gradient(135deg, #ea580c, #f97316)' : 'linear-gradient(135deg, #f59e0b, #fbbf24)' }}; color: #fff; display: grid; place-items: center; font-size: 26px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                {{ $pkgId == 2 ? '🚀' : '⚡' }}
                            </div>
                            <div>
                                <b style="font-size: 17px; color: #0f172a; display: block;">{{ $pkg['title'] }}</b>
                                <span style="font-size: 12.5px; color: #64748b; font-weight: 600;">Nhận ngay <b>{{ $pkg['minutes'] }} phút</b> chơi mini-game</span>
                            </div>
                        </div>

                        <div style="background: #f8fafc; border-radius: 14px; padding: 12px 16px; margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #f1f5f9;">
                            <span style="font-size: 12.5px; font-weight: 700; color: #475569;">Chi phí quy đổi:</span>
                            <b style="font-size: 18px; color: #ea580c; font-family: 'Fredoka', cursive, sans-serif;">{{ number_format($pkg['stars']) }} ⭐</b>
                        </div>
                    </div>

                    <div>
                        <button type="button" 
                            onclick="exchangePackage({{ $pkgId }}, {{ $pkg['stars'] }}, {{ $pkg['minutes'] }})"
                            class="btn-exchange-pkg"
                            id="btn-pkg-{{ $pkgId }}"
                            style="width: 100%; border: none; background: {{ $pkgId == 2 ? 'linear-gradient(135deg, #ea580c, #c2410c)' : 'linear-gradient(135deg, #f59e0b, #d97706)' }}; color: #ffffff; padding: 13px; border-radius: 14px; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span>✨</span> Đổi Gói Ngay
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="exchange-feedback" style="display: none; margin-top: 16px; padding: 14px 18px; border-radius: 14px; font-size: 13.5px; font-weight: 700; text-align: center;"></div>
    </div>
</div>

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
        feedback.style.border = '1px solid #fecaca';
        feedback.innerHTML = '⏰ Thời gian chơi game của bé hiện tại đang là 0. Hãy chọn gói bên dưới để đổi Sao lấy giờ chơi nhé!';
    }

    async function exchangePackage(packageId, stars, minutes) {
        const btn = document.getElementById(`btn-pkg-${packageId}`);
        const feedback = document.getElementById('exchange-feedback');

        if (currentStars < stars) {
            feedback.style.display = 'block';
            feedback.style.background = '#fef2f2';
            feedback.style.color = '#b91c1c';
            feedback.style.border = '1px solid #fecaca';
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

                // Update UI elements
                document.getElementById('display-stars').innerText = currentStars.toLocaleString('vi-VN');
                document.getElementById('display-time').innerText = formatTime(currentGameTime);
                document.getElementById('time-display-wrap').style.color = '#4ade80';

                // Unlock game button
                const gameLink = document.getElementById('game-main-link');
                const actionBtn = document.getElementById('game-action-btn');
                gameLink.href = '{{ asset('games/bao-ve-em-be.html') }}';
                gameLink.onclick = null;
                actionBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                actionBtn.innerHTML = `<span>🎮</span> VÀO PHÒNG CHƠI NGAY (Còn ${Math.floor(currentGameTime / 60)}p) →`;

                feedback.style.display = 'block';
                feedback.style.background = '#f0fdf4';
                feedback.style.color = '#15803d';
                feedback.style.border = '1px solid #bbf7d0';
                feedback.innerHTML = `🎉 Tuyệt vời! ${data.message} (Thời gian chơi hiện có: <b>${formatTime(currentGameTime)}</b>). <a href="{{ asset('games/bao-ve-em-be.html') }}" style="color: #15803d; text-decoration: underline; font-weight: 800; margin-left: 6px;">Vào chơi ngay ➔</a>`;
            } else {
                feedback.style.display = 'block';
                feedback.style.background = '#fef2f2';
                feedback.style.color = '#b91c1c';
                feedback.style.border = '1px solid #fecaca';
                feedback.innerHTML = `⚠️ ${data.message || 'Không thể đổi gói. Vui lòng thử lại!'}`;
            }
        } catch (err) {
            feedback.style.display = 'block';
            feedback.style.background = '#fef2f2';
            feedback.style.color = '#b91c1c';
            feedback.style.border = '1px solid #fecaca';
            feedback.innerHTML = '⚠️ Lỗi kết nối máy chủ. Vui lòng kiểm tra lại mạng.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
</script>
@endsection

