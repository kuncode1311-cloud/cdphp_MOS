<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bảng Giá Bản Quyền IC3 GS6 & Spark Quest — Dành Cho Giáo Viên & Nhà Trường</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&family=Fredoka:wght@600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-soft: #eff6ff;
            --text-dark: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --border-hover: #cbd5e1;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(circle at 50% 0%, #e0f2fe 0%, #ecfdf5 30%, #fdf4ff 70%, #f8fafc 100%);
            color: #0f172a;
            line-height: 1.5;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* 🌟 Navbar Gamified Adventure */
        .pricing-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: linear-gradient(180deg, #1070b8 0%, #0d5b94 100%);
            border-bottom: 3.5px solid #48c3f7;
            box-shadow: inset 0 -4px 0 #073a61, 0 10px 25px rgba(6, 38, 68, 0.4);
            padding: 0 32px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo-badge {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffcf33, #ff9b26);
            color: #4a2700;
            border: 2.5px solid #ffffff;
            display: grid;
            place-items: center;
            font-size: 18px;
            font-weight: 1000;
            box-shadow: 0 4px 0 #b35600;
        }

        .brand-text h2 {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #ffe658;
            margin: 0;
            line-height: 1.1;
            text-shadow: 0 2px 0 #6e350c;
            letter-spacing: 0.5px;
        }

        .brand-text small {
            font-size: 10px;
            color: #d5ebff;
            font-weight: 900;
            letter-spacing: 1.5px;
            display: block;
            margin-top: 2px;
            text-shadow: 0 1px 0 #074775;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-btn-link {
            font-size: 13px;
            font-weight: 800;
            color: #cceeff;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 12px;
            transition: all 0.15s;
        }

        .nav-btn-link:hover {
            color: #ffe464;
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(180deg, #ffc933 0%, #ff8e1c 100%);
            color: #4a2700;
            font-size: 13px;
            font-weight: 1000;
            padding: 8px 20px;
            border-radius: 999px;
            border: 2.5px solid #ffffff;
            text-decoration: none;
            box-shadow: 0 4px 0 #b35600, 0 6px 15px rgba(255, 142, 28, 0.4);
            transition: all 0.15s;
        }

        .nav-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #b35600, 0 8px 20px rgba(255, 142, 28, 0.5);
        }

        /* 🎯 Hero Header Rực Rỡ */
        .pricing-hero {
            max-width: 860px;
            margin: 40px auto 32px;
            text-align: center;
            padding: 0 20px;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 18px;
            background: linear-gradient(135deg, #fef08a, #facc15);
            color: #713f12;
            border: 2.5px solid #ffffff;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(234, 179, 8, 0.35);
        }

        .pricing-hero h1 {
            font-family: 'Fredoka', 'Plus Jakarta Sans', sans-serif;
            font-size: 38px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            line-height: 1.25;
            background: linear-gradient(135deg, #1e40af 0%, #4338ca 50%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .pricing-hero p {
            font-size: 15.5px;
            color: #475569;
            line-height: 1.6;
            font-weight: 600;
            max-width: 700px;
            margin: 0 auto;
        }

        /* 📦 Pricing Cards Layout (Lưới Thẻ 3D Đa Sắc Màu) */
        .pricing-container {
            max-width: 1220px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }

        .pricing-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
            align-items: stretch;
        }

        /* 🎨 Thẻ 3D Card Đa Sắc Màu - Tách Biệt Từng Vùng (Không Tệp Màu, Không Nháy Trắng) */
        .plan-card {
            border-radius: 28px;
            padding: 36px 26px 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            background: #ffffff;
            border: 3.5px solid #ffffff;
            box-sizing: border-box;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            will-change: transform;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        .plan-card:hover {
            transform: translateY(-5px) translateZ(0);
        }

        /* =========================================================
           1. THẺ GÓI KHỞI ĐẦU: XANH NGỌC LỤC BẢO (EMERALD)
           ========================================================= */
        .plan-card.tier-starter {
            border-color: #10b981;
            box-shadow: 0 16px 36px rgba(16, 185, 129, 0.16), 0 0 0 1px rgba(16, 185, 129, 0.1);
        }
        .plan-card.tier-starter:hover {
            box-shadow: 0 24px 48px rgba(16, 185, 129, 0.25), 0 0 0 1.5px rgba(16, 185, 129, 0.2);
        }
        .plan-card.tier-starter .featured-tag {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
            color: #ffffff;
        }
        .plan-card.tier-starter .plan-icon-orb {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-color: #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .plan-card.tier-starter .plan-name {
            color: #065f46;
        }
        .plan-card.tier-starter .plan-price-wrap {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 2px solid #86efac;
        }
        .plan-card.tier-starter .price-val {
            color: #059669;
        }
        .plan-card.tier-starter .price-duration {
            background: #d1fae5;
            color: #065f46;
        }
        .plan-card.tier-starter .btn-select-plan {
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 5px 0 #047857, 0 10px 22px rgba(16, 185, 129, 0.35);
        }
        .plan-card.tier-starter .btn-select-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #047857, 0 14px 26px rgba(16, 185, 129, 0.45);
        }
        .plan-card.tier-starter .btn-select-plan:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #047857;
        }

        /* =========================================================
           2. THẺ GÓI TIÊU CHUẨN: TÍM THẠCH ANH & VÀNG HOÀNG GIA (VIP)
           ========================================================= */
        .plan-card.tier-standard {
            border-color: #fbbf24;
            transform: scale(1.03) translateZ(0);
            z-index: 2;
            box-shadow: 0 22px 50px rgba(99, 102, 241, 0.22), 0 0 0 2px rgba(251, 191, 36, 0.3);
        }
        .plan-card.tier-standard:hover {
            transform: scale(1.04) translateY(-5px) translateZ(0);
            box-shadow: 0 30px 60px rgba(99, 102, 241, 0.3), 0 0 0 2.5px rgba(251, 191, 36, 0.4);
        }
        .plan-card.tier-standard .featured-tag {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            box-shadow: 0 5px 16px rgba(245, 158, 11, 0.45);
            border: 2.5px solid #ffffff;
            font-size: 12px;
            padding: 6px 20px;
        }
        .plan-card.tier-standard .plan-icon-orb {
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
            border-color: #fbbf24;
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.25);
        }
        .plan-card.tier-standard .plan-name {
            color: #312e81;
        }
        .plan-card.tier-standard .plan-price-wrap {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border: 2px solid #d8b4fe;
        }
        .plan-card.tier-standard .price-val {
            color: #4f46e5;
        }
        .plan-card.tier-standard .price-duration {
            background: #ede9fe;
            color: #4338ca;
        }
        .plan-card.tier-standard .btn-select-plan {
            background: linear-gradient(180deg, #6366f1 0%, #4338ca 100%);
            color: #ffffff;
            box-shadow: 0 5px 0 #312e81, 0 12px 24px rgba(79, 70, 229, 0.4);
        }
        .plan-card.tier-standard .btn-select-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #312e81, 0 16px 30px rgba(79, 70, 229, 0.5);
        }
        .plan-card.tier-standard .btn-select-plan:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #312e81;
        }

        /* =========================================================
           3. THẺ GÓI TRƯỜNG HỌC: CAM HỔ PHÁCH & SAN HÔ (PRO SCHOOL)
           ========================================================= */
        .plan-card.tier-pro {
            border-color: #f97316;
            box-shadow: 0 16px 36px rgba(249, 115, 22, 0.16), 0 0 0 1px rgba(249, 115, 22, 0.1);
        }
        .plan-card.tier-pro:hover {
            box-shadow: 0 24px 48px rgba(249, 115, 22, 0.25), 0 0 0 1.5px rgba(249, 115, 22, 0.2);
        }
        .plan-card.tier-pro .featured-tag {
            background: linear-gradient(135deg, #ff8f31 0%, #f24e7a 100%);
            box-shadow: 0 4px 14px rgba(242, 78, 122, 0.4);
            color: #ffffff;
        }
        .plan-card.tier-pro .plan-icon-orb {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            border-color: #f97316;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        }
        .plan-card.tier-pro .plan-name {
            color: #7c2d12;
        }
        .plan-card.tier-pro .plan-price-wrap {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 2px solid #fdba74;
        }
        .plan-card.tier-pro .price-val {
            color: #ea580c;
        }
        .plan-card.tier-pro .price-duration {
            background: #fed7aa;
            color: #9a3412;
        }
        .plan-card.tier-pro .btn-select-plan {
            background: linear-gradient(180deg, #ff9b26 0%, #ea580c 100%);
            color: #ffffff;
            box-shadow: 0 5px 0 #9a3412, 0 12px 24px rgba(234, 88, 12, 0.38);
        }
        .plan-card.tier-pro .btn-select-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #9a3412, 0 16px 30px rgba(234, 88, 12, 0.5);
        }
        .plan-card.tier-pro .btn-select-plan:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #9a3412;
        }

        /* =========================================================
           4. THẺ GÓI MỞ RỘNG 1: XANH BIỂN & ĐẠI DƯƠNG (CYAN OCEAN)
           ========================================================= */
        .plan-card.tier-cyan {
            border-color: #0284c7;
            box-shadow: 0 16px 36px rgba(2, 132, 199, 0.16), 0 0 0 1px rgba(2, 132, 199, 0.1);
        }
        .plan-card.tier-cyan:hover {
            box-shadow: 0 24px 48px rgba(2, 132, 199, 0.25), 0 0 0 1.5px rgba(2, 132, 199, 0.2);
        }
        .plan-card.tier-cyan .featured-tag {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);
            color: #ffffff;
        }
        .plan-card.tier-cyan .plan-icon-orb {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-color: #0284c7;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
        }
        .plan-card.tier-cyan .plan-name {
            color: #0369a1;
        }
        .plan-card.tier-cyan .plan-price-wrap {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #7dd3fc;
        }
        .plan-card.tier-cyan .price-val {
            color: #0284c7;
        }
        .plan-card.tier-cyan .price-duration {
            background: #bae6fd;
            color: #0369a1;
        }
        .plan-card.tier-cyan .btn-select-plan {
            background: linear-gradient(180deg, #38bdf8 0%, #0284c7 100%);
            color: #ffffff;
            box-shadow: 0 5px 0 #0369a1, 0 12px 24px rgba(2, 132, 199, 0.38);
        }
        .plan-card.tier-cyan .btn-select-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #0369a1, 0 16px 30px rgba(2, 132, 199, 0.5);
        }
        .plan-card.tier-cyan .btn-select-plan:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #0369a1;
        }

        /* =========================================================
           5. THẺ GÓI MỞ RỘNG 2: ĐỎ SAN HÔ & RUBY (ROSE NEON)
           ========================================================= */
        .plan-card.tier-rose {
            border-color: #e11d48;
            box-shadow: 0 16px 36px rgba(225, 29, 72, 0.16), 0 0 0 1px rgba(225, 29, 72, 0.1);
        }
        .plan-card.tier-rose:hover {
            box-shadow: 0 24px 48px rgba(225, 29, 72, 0.25), 0 0 0 1.5px rgba(225, 29, 72, 0.2);
        }
        .plan-card.tier-rose .featured-tag {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            box-shadow: 0 4px 14px rgba(225, 29, 72, 0.4);
            color: #ffffff;
        }
        .plan-card.tier-rose .plan-icon-orb {
            background: linear-gradient(135deg, #fff1f2, #ffe4e6);
            border-color: #e11d48;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2);
        }
        .plan-card.tier-rose .plan-name {
            color: #9f1239;
        }
        .plan-card.tier-rose .plan-price-wrap {
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            border: 2px solid #fda4af;
        }
        .plan-card.tier-rose .price-val {
            color: #e11d48;
        }
        .plan-card.tier-rose .price-duration {
            background: #fecdd3;
            color: #9f1239;
        }
        .plan-card.tier-rose .btn-select-plan {
            background: linear-gradient(180deg, #fb7185 0%, #e11d48 100%);
            color: #ffffff;
            box-shadow: 0 5px 0 #9f1239, 0 12px 24px rgba(225, 29, 72, 0.38);
        }
        .plan-card.tier-rose .btn-select-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #9f1239, 0 16px 30px rgba(225, 29, 72, 0.5);
        }
        .plan-card.tier-rose .btn-select-plan:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #9f1239;
        }

        /* =========================================================
           6. THẺ GÓI MỞ RỘNG 3: TÍM HỒNG & PHÉP THUẬT (FUCHSIA MAGIC)
           ========================================================= */
        .plan-card.tier-fuchsia {
            border-color: #c026d3;
            box-shadow: 0 16px 36px rgba(192, 38, 211, 0.16), 0 0 0 1px rgba(192, 38, 211, 0.1);
        }
        .plan-card.tier-fuchsia:hover {
            box-shadow: 0 24px 48px rgba(192, 38, 211, 0.25), 0 0 0 1.5px rgba(192, 38, 211, 0.2);
        }
        .plan-card.tier-fuchsia .featured-tag {
            background: linear-gradient(135deg, #d946ef 0%, #c026d3 100%);
            box-shadow: 0 4px 14px rgba(192, 38, 211, 0.4);
            color: #ffffff;
        }
        .plan-card.tier-fuchsia .plan-icon-orb {
            background: linear-gradient(135deg, #fdf4ff, #fae8ff);
            border-color: #c026d3;
            box-shadow: 0 4px 12px rgba(192, 38, 211, 0.2);
        }
        .plan-card.tier-fuchsia .plan-name {
            color: #701a75;
        }
        .plan-card.tier-fuchsia .plan-price-wrap {
            background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%);
            border: 2px solid #f0abfc;
        }
        .plan-card.tier-fuchsia .price-val {
            color: #c026d3;
        }
        .plan-card.tier-fuchsia .price-duration {
            background: #f5d0fe;
            color: #701a75;
        }
        .plan-card.tier-fuchsia .btn-select-plan {
            background: linear-gradient(180deg, #e879f9 0%, #c026d3 100%);
            color: #ffffff;
            box-shadow: 0 5px 0 #701a75, 0 12px 24px rgba(192, 38, 211, 0.38);
        }
        .plan-card.tier-fuchsia .btn-select-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #701a75, 0 16px 30px rgba(192, 38, 211, 0.5);
        }
        .plan-card.tier-fuchsia .btn-select-plan:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #701a75;
        }

        /* Vòng Tròn Orb 3D Biểu Tượng Gói */
        .plan-orb-wrapper {
            display: flex;
            justify-content: center;
            margin: 2px 0 14px;
        }

        .plan-icon-orb {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 3px solid;
            display: grid;
            place-items: center;
        }

        .plan-icon-emoji {
            font-size: 34px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
        }

        /* 3D Badge nổi ở đầu thẻ */
        .featured-tag {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11.5px;
            font-weight: 1000;
            padding: 5px 18px;
            border-radius: 999px;
            letter-spacing: 0.5px;
            white-space: nowrap;
            border: 2.5px solid #ffffff;
        }

        /* ====== ROW 1: HEADER (TÊN GÓI & MÔ TẢ) ====== */
        .plan-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .plan-name {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 25px;
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1.25;
            min-height: 34px;
        }

        .plan-desc {
            font-size: 13.5px;
            color: #475569;
            line-height: 1.5;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-weight: 600;
        }

        /* ====== ROW 2: HỘP GIÁ TIỀN NỔI BẬT (KHÔNG BAO GIỜ RỚT DÒNG CHỮ Đ) ====== */
        .plan-price-wrap {
            padding: 14px 16px;
            border-radius: 18px;
            margin-bottom: 20px;
            min-height: 86px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .plan-original-price {
            font-size: 12.5px;
            color: #94a3b8;
            text-decoration: line-through;
            font-weight: 700;
            margin-bottom: 3px;
            min-height: 16px;
        }

        .plan-price-main {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
            width: 100%;
        }

        .price-val {
            font-size: 34px;
            font-weight: 1000;
            letter-spacing: -0.8px;
            line-height: 1.1;
            white-space: nowrap !important;
            display: inline-block;
        }

        .price-duration {
            font-size: 12px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 999px;
            white-space: nowrap;
            display: inline-block;
            margin-top: 4px;
        }

        /* ====== ROW 3: THÔNG SỐ CỐT LÕI (3 POD ĐA SẮC MÀU TÁCH BIỆT RÕ RÀNG) ====== */
        .plan-specs {
            display: flex;
            flex-direction: column;
            gap: 9px;
            margin-bottom: 22px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-radius: 14px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 700;
            box-sizing: border-box;
            min-height: 44px;
            border: 1.8px solid;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        /* Pod 1: Sĩ số (Xanh biển Sky Blue) */
        .spec-students {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-color: #7dd3fc;
            color: #0369a1;
        }
        .spec-students .spec-value {
            background: #ffffff;
            color: #0c4a6e;
            box-shadow: 0 1px 3px rgba(3, 105, 161, 0.15);
        }

        /* Pod 2: Khối lớp (Tím thạch anh Amethyst) */
        .spec-levels {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border-color: #c084fc;
            color: #6b21a8;
        }
        .spec-levels .spec-value {
            background: #ffffff;
            color: #581c87;
            box-shadow: 0 1px 3px rgba(107, 33, 168, 0.15);
        }

        /* Pod 3: Thời hạn (Vàng kim hoàng gia / Hổ phách) */
        .spec-duration {
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
            border-color: #facc15;
            color: #854d0e;
        }
        .spec-duration .spec-value {
            background: #ffffff;
            color: #713f12;
            box-shadow: 0 1px 3px rgba(133, 77, 14, 0.15);
        }

        .spec-item .spec-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-weight: 800;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .spec-item .spec-value {
            font-weight: 900;
            text-align: right;
            padding: 3px 12px;
            border-radius: 999px;
            font-size: 12.5px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            white-space: nowrap;
        }

        /* ====== ROW 4: DANH SÁCH TÍNH NĂNG (NỀN SÁNG, CHỮ ĐẬM SẮC NÉT) ====== */
        .features-list {
            list-style: none;
            margin: 0 0 24px 0;
            padding: 0;
            flex: 1;
            min-height: 180px;
        }

        .features-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13.5px;
            color: #1e293b;
            margin-bottom: 11px;
            line-height: 1.45;
            font-weight: 650;
        }

        .features-list li .check-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 1000;
            flex-shrink: 0;
            margin-top: 1px;
            color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
        }

        .tier-starter .features-list li .check-icon { background: #10b981; }
        .tier-standard .features-list li .check-icon { background: #6366f1; }
        .tier-pro .features-list li .check-icon { background: #f97316; }
        .tier-cyan .features-list li .check-icon { background: #0284c7; }
        .tier-rose .features-list li .check-icon { background: #e11d48; }
        .tier-fuchsia .features-list li .check-icon { background: #c026d3; }

        /* ====== ROW 5: NÚT HÀNH ĐỘNG 3D (TACTILE 3D BUTTON) ====== */
        .plan-cta-wrap {
            margin-top: auto;
        }

        .btn-select-plan {
            width: 100%;
            padding: 15px 22px;
            border-radius: 18px;
            font-size: 15.5px;
            font-weight: 1000;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            box-sizing: border-box;
            border: 2.5px solid #ffffff;
            letter-spacing: 0.3px;
        }

        /* ====== RESPONSIVE OPTIMIZATIONS ====== */
        @media (max-width: 1080px) {
            .pricing-container {
                padding: 0 16px 60px;
            }
            .pricing-cards-grid {
                gap: 16px;
            }
            .plan-card {
                padding: 26px 18px 22px;
            }
            .plan-name {
                font-size: 21px;
            }
            .plan-price b {
                font-size: 28px;
            }
        }

        @media (max-width: 880px) {
            .pricing-cards-grid {
                grid-template-columns: 1fr;
                max-width: 480px;
                margin: 0 auto;
                gap: 32px;
            }
            .plan-card.tier-standard {
                transform: none;
            }
            .plan-card.tier-standard:hover {
                transform: translateY(-4px);
            }
            .plan-name,
            .plan-desc,
            .plan-specs .spec-levels,
            .features-list {
                min-height: auto !important;
                height: auto !important;
            }
            .pricing-hero h1 {
                font-size: 28px;
            }
            .pricing-hero p {
                font-size: 14px;
            }
        }

        @media (max-width: 640px) {
            .pricing-nav {
                padding: 0 16px;
            }
            .brand-text small {
                display: none;
            }
            .nav-actions .nav-btn-link {
                display: none;
            }
        }

        /* ============================
           📋 MODAL ĐĂNG KÝ - CLEAN UI
           ============================ */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(6px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal-container-2col {
            background: #ffffff;
            border-radius: 20px;
            width: min(850px, 94vw);
            max-height: min(580px, 94vh);
            overflow: hidden;
            display: grid;
            grid-template-columns: 275px 1fr;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
            animation: modalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.96) translateY(8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        @media (max-width: 768px) {
            .modal-container-2col {
                grid-template-columns: 1fr;
                max-height: 94vh;
                overflow-y: auto;
                width: min(500px, 94vw);
            }
            .summary-meta-list {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 6px;
            }
            .summary-guarantees {
                display: none;
            }
        }

        /* Modal Left Column: Package Summary (Sạch Sẽ, Tinh Tế, Đẳng Cấp) */
        .modal-summary-col {
            background: #0f172a;
            color: #ffffff;
            padding: 20px 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }

        .summary-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(37, 99, 235, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #93c5fd;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .summary-pkg-title {
            font-size: 17px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 8px;
            letter-spacing: -0.2px;
        }

        /* Hộp Giá Tiền Tối Giản */
        .summary-price-box {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 8px 12px;
            margin-bottom: 10px;
        }

        .summary-price-header {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 2px;
        }

        .summary-price-box small {
            font-size: 9.5px;
            color: #94a3b8;
            display: block;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .summary-price-box b {
            font-size: 22px;
            color: #38bdf8;
            font-weight: 800;
            letter-spacing: -0.4px;
            display: block;
            line-height: 1.1;
        }

        /* Danh Sách Thông Số Gọn Gàng */
        .summary-meta-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 10px;
        }

        .summary-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 6px 10px;
        }

        .meta-icon-badge {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: grid;
            place-items: center;
            font-size: 12px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .meta-content {
            flex: 1;
            min-width: 0;
        }

        .meta-label {
            display: block;
            font-size: 9.5px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .meta-value {
            display: block;
            font-size: 12px;
            color: #ffffff;
            font-weight: 700;
        }

        /* Cam Kết Dịch Vụ */
        .summary-guarantees {
            border-top: 1px solid rgba(255, 255, 255, 0.14);
            padding-top: 8px;
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .guarantee-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10.5px;
            color: #f1f5f9;
            font-weight: 600;
            line-height: 1.25;
        }

        .guarantee-icon {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.12);
            display: grid;
            place-items: center;
            font-size: 10px;
            flex-shrink: 0;
        }

        /* Modal Right Column: Form Tinh Tế, Vừa Vặn Tuyệt Đối Trong 1 Màn Hình */
        .modal-form-col {
            padding: 16px 20px 14px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow-y: auto;
            position: relative;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .modal-close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            font-size: 13px;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all 0.15s;
            z-index: 10;
        }

        .modal-close-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .form-header {
            margin-bottom: 7px;
            padding-right: 28px;
        }

        .form-header h3 {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .form-header p {
            font-size: 11.5px;
            color: #64748b;
            margin: 0;
            line-height: 1.3;
        }

        /* Phân Khu 2 Bước Tinh Gọn (Không Bị Dính Khối, Không Bị Dài) */
        .form-section-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: 4px 9px;
            margin-bottom: 6px;
            margin-top: 2px;
        }

        .form-section-title-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .step-num-pill {
            width: 17px;
            height: 17px;
            border-radius: 5px;
            background: linear-gradient(135deg, #1a73e8, #4f46e5);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 10px;
            font-weight: 900;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .form-field {
            margin-bottom: 5px;
        }

        .form-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 15px;
            margin-bottom: 2px;
        }

        .form-label {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 0;
        }

        .form-input {
            width: 100%;
            padding: 5px 10px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 12px;
            font-family: inherit;
            color: #0f172a;
            transition: border-color 0.15s, box-shadow 0.15s, background-color 0.15s;
            background: #f8fafc;
            box-sizing: border-box;
            height: 33px;
        }

        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        /* ====== FORM INPUT STATES ====== */
        .form-input.is-valid {
            border-color: #22c55e;
            background: #f0fdf4;
        }
        .form-input.is-invalid {
            border-color: #ef4444 !important;
            background: #fff5f5;
            box-shadow: 0 0 0 2px rgba(239,68,68,0.15);
        }
        .field-error {
            font-size: 10px;
            color: #ef4444;
            font-weight: 700;
            display: none;
            white-space: nowrap;
            line-height: 1;
        }
        .field-error.show {
            display: inline-block;
        }
        .required-star { color: #ef4444; font-weight: 900; margin-left: 2px; }

        /* ====== SINGLE PAYMENT METHOD CARD (TINH TẾ - ĐẸP - GỌN) ====== */
        .pay-badge-security {
            font-size: 9.5px;
            font-weight: 700;
            color: #059669;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 1px 6px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        .pay-method-single-card {
            display: flex;
            align-items: center;
            gap: 9px;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1.5px solid #10b981;
            border-radius: 9px;
            padding: 6px 10px;
            margin-bottom: 6px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
        }

        .pay-single-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
        }

        .pay-single-info {
            flex: 1;
            min-width: 0;
        }

        .pay-single-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 800;
            color: #065f46;
            margin-bottom: 1px;
            line-height: 1.2;
        }

        .pay-single-desc {
            font-size: 10px;
            color: #047857;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pay-badge-rec {
            background: #059669;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: 800;
            padding: 1px 5px;
            border-radius: 999px;
            letter-spacing: 0.2px;
            display: inline-block;
        }

        .pay-single-check {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #10b981;
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 10.5px;
            font-weight: 900;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3);
        }

        /* ====== SUBMIT BUTTON ====== */
        .btn-submit-order {
            width: 100%;
            padding: 9px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(5,150,105,0.25);
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            letter-spacing: 0.2px;
            margin-top: 2px;
        }
        .btn-submit-order:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(5,150,105,0.35);
        }
        .btn-submit-order:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        /* Tối ưu khi màn hình có chiều cao khiêm tốn (Laptop 14", 1366x768) */
        @media (max-height: 680px) {
            .modal-container-2col {
                max-height: 96vh;
            }
            .modal-summary-col {
                padding: 12px 14px;
            }
            .modal-form-col {
                padding: 12px 16px 10px;
            }
            .summary-guarantees {
                display: none;
            }
            .form-field {
                margin-bottom: 3px;
            }
            .form-input {
                height: 30px;
            }
        }

        /* =====================================================================
           💳 POPUP CHUYỂN KHOẢN VIETQR RIÊNG CỦA WEB (XỊN - ĐẸP - GỌN - ĐẦY ĐỦ)
           ===================================================================== */
        .modal-pay-card {
            background: #ffffff;
            border-radius: 20px;
            width: min(720px, 95vw);
            max-height: min(580px, 92vh);
            overflow: hidden;
            box-shadow: 0 25px 50px -15px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1);
            animation: modalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .pay-card-head {
            padding: 16px 20px 14px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .pay-card-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pay-brand-chip {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            flex-shrink: 0;
        }

        .pay-head-text h3 {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
        }

        .pay-head-text .pay-meta-sub {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
        }

        .pay-order-code-badge {
            display: inline-flex;
            align-items: center;
            padding: 1px 7px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
        }

        .pay-live-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            color: #059669;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pay-live-status-pill .pulse-dot {
            width: 7px;
            height: 7px;
            background: #059669;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.7);
            animation: pulseWave 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        .pay-guide-banner {
            background: #eff6ff;
            border-bottom: 1px solid #dbeafe;
            padding: 9px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #1e40af;
            line-height: 1.35;
        }

        .pay-content-grid {
            padding: 16px 20px;
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 16px;
            align-items: start;
            overflow-y: auto;
        }

        @media (max-width: 600px) {
            .pay-content-grid {
                grid-template-columns: 1fr;
                padding: 14px;
                gap: 14px;
            }
        }

        .pay-qr-box {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .pay-qr-img-wrap {
            border-radius: 10px;
            overflow: hidden;
            display: inline-block;
            margin-bottom: 8px;
            border: 1px solid #f1f5f9;
        }

        .pay-qr-img-wrap img {
            width: 100%;
            max-width: 196px;
            aspect-ratio: 1/1;
            height: auto;
            display: block;
        }

        .pay-qr-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 6px;
        }

        .pay-qr-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            color: #475569;
        }

        .pay-qr-hint {
            font-size: 10.5px;
            color: #64748b;
            line-height: 1.35;
        }

        .pay-details-list {
            display: flex;
            flex-direction: column;
            gap: 7.5px;
        }

        .pay-detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 11px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.15s;
        }

        .pay-detail-row:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .pay-detail-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 10.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .pay-detail-val {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            word-break: break-all;
            margin-top: 1px;
        }

        .pay-row-amount {
            background: #eff6ff !important;
            border-color: #bfdbfe !important;
        }

        .pay-row-amount .pay-detail-meta {
            color: #1d4ed8;
        }

        .pay-row-amount .pay-detail-val {
            font-size: 15.5px;
            font-weight: 800;
            color: #1d4ed8;
        }

        .pay-row-memo {
            background: #fffbeb !important;
            border-color: #fde68a !important;
        }

        .pay-row-memo .pay-detail-meta {
            color: #b45309;
        }

        .pay-row-memo .pay-detail-val {
            font-size: 14px;
            font-weight: 800;
            color: #b45309;
            letter-spacing: 0.02em;
        }

        .pay-copy-btn {
            background: #ffffff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: all 0.15s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pay-copy-btn:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .pay-copy-btn.copied {
            background: #059669;
            color: #ffffff;
            border-color: #059669;
        }

        .pay-note-alert {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 9px;
            padding: 7px 10px;
            font-size: 11px;
            color: #92400e;
            line-height: 1.4;
            display: flex;
            align-items: flex-start;
            gap: 5px;
        }

        .pay-foot-actions {
            padding: 12px 20px 14px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 600px) {
            .pay-foot-actions {
                flex-direction: column-reverse;
                padding: 12px 14px;
            }
            .pay-foot-actions button {
                width: 100%;
                justify-content: center;
            }
        }

        .pay-btn-close {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            color: #475569;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
        }

        .pay-btn-close:hover {
            background: #f1f5f9;
        }

        .pay-btn-confirm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            border-radius: 999px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.15s;
        }

        .pay-btn-confirm:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .pay-btn-confirm:disabled {
            opacity: 0.8;
            cursor: default;
        }

        .pay-success-card {
            padding: 36px 20px;
            text-align: center;
        }

        .pay-success-badge {
            width: 64px;
            height: 64px;
            background: #ecfdf5;
            color: #059669;
            border: 2px solid #a7f3d0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 14px;
            animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .pay-success-card h3 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .pay-success-card p {
            font-size: 13px;
            color: #475569;
            max-width: 420px;
            margin: 0 auto 16px;
            line-height: 1.45;
        }

        .pay-progress-bar {
            width: 100%;
            max-width: 280px;
            height: 5px;
            background: #e2e8f0;
            border-radius: 999px;
            margin: 0 auto;
            overflow: hidden;
        }

        .pay-progress-bar-fill {
            height: 100%;
            width: 0%;
            background: #059669;
            transition: width 2.5s linear;
        }

        /* 💬 FLOATING MESSENGER / TELEGRAM LIVE CHAT WIDGET */
        .live-chat-toggle {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #0084ff 0%, #00c6ff 100%);
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 999px;
            box-shadow: 0 6px 24px rgba(0, 132, 255, 0.4);
            cursor: pointer;
            border: none;
            font-size: 13.5px;
            font-weight: 800;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .live-chat-toggle:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 28px rgba(0, 132, 255, 0.5);
        }

        .live-chat-toggle .online-dot {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border: 2px solid #ffffff;
            border-radius: 50%;
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        /* Chat Popover Window */
        .live-chat-box {
            position: fixed;
            bottom: 84px;
            right: 24px;
            width: 360px;
            max-width: calc(100vw - 32px);
            height: 520px;
            max-height: calc(100vh - 120px);
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
            border: 1px solid #e2e8f0;
            z-index: 1001;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: modalPop 0.2s ease;
        }

        .live-chat-box.open {
            display: flex;
        }

        .chat-header {
            background: linear-gradient(135deg, #0084ff 0%, #0072db 100%);
            color: #ffffff;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #ffffff;
            color: #0084ff;
            display: grid;
            place-items: center;
            font-size: 18px;
            font-weight: 900;
        }

        .chat-title h4 {
            font-size: 14px;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }

        .chat-title small {
            font-size: 11px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .chat-close-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #ffffff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 14px;
        }

        .chat-close-btn:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        .chat-body {
            flex: 1;
            padding: 14px;
            overflow-y: auto;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .chat-msg {
            max-width: 85%;
            font-size: 12.5px;
            line-height: 1.45;
            padding: 10px 14px;
            border-radius: 16px;
        }

        .chat-msg-bot {
            align-self: flex-start;
            background: #ffffff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .chat-msg-user {
            align-self: flex-end;
            background: #0084ff;
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }

        .chat-footer {
            padding: 12px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
        }

        .chat-footer form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-input-compact {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 12px;
            font-family: inherit;
        }

        .chat-input-compact:focus {
            outline: none;
            border-color: #0084ff;
        }

        .btn-send-chat {
            background: #0084ff;
            color: #ffffff;
            border: none;
            padding: 8px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-send-chat:hover {
            background: #0072db;
        }

        /* Footer */
        .pricing-footer {
            border-top: 1px solid #e2e8f0;
            padding: 32px 20px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            background: #ffffff;
        }
    </style>
</head>
<body>

    <!-- Navbar Minimalist -->
    <nav class="pricing-nav">
        <a href="{{ route('pricing.index') }}" class="nav-brand">
            <div class="brand-logo-badge">IC3</div>
            <div class="brand-text">
                <h2>IC3 QUEST</h2>
                <small>BẢN QUYỀN GIÁO VIÊN & NHÀ TRƯỜNG</small>
            </div>
        </a>

        <div class="nav-actions">
            @auth
                @if(auth()->user()->canAccessAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="nav-btn-link">
                        🏫 Về Quản Trị
                    </a>
                @else
                    <a href="{{ route('home') }}" class="nav-btn-link">
                        🏠 Về Trang Chủ
                    </a>
                @endif
                <a href="{{ route('pricing.history') }}" class="nav-btn-link">
                    📜 Đơn của tôi
                </a>
                <form method="post" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="nav-btn-link" style="border:none; background:none; cursor:pointer;">
                        Đăng xuất
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-btn-link">
                    Đã có tài khoản? Đăng nhập
                </a>
                <a href="{{ route('login') }}" class="nav-btn-primary">
                    🔑 Đăng Nhập
                </a>
            @endauth
        </div>
    </nav>

    <!-- Hero Header -->
    <header class="pricing-hero">
        <div class="hero-pill">
            <span>✨</span> BẢN QUYỀN CHUẨN QUỐC TẾ CHO GIÁO VIÊN & NHÀ TRƯỜNG
        </div>
        <h1>Chọn Gói Bản Quyền Đồng Hành Cùng Lớp Học</h1>
        <p>Cấp tài khoản giáo viên quản lý lớp, mở khóa toàn bộ ngân hàng đề thi chuẩn IC3 GS6 quốc tế, chấm điểm và xếp loại học sinh tự động.</p>
    </header>

    <!-- Main Pricing Cards Grid -->
    <main class="pricing-container">
        @if(session('ok'))
            <div style="background: #ecfdf5; border: 1.5px solid #10b981; border-radius: 12px; padding: 12px 18px; color: #065f46; font-size: 13.5px; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                <span>✓</span> <div>{{ session('ok') }}</div>
            </div>
        @endif

        @if(session('err'))
            <div style="background: #fef2f2; border: 1.5px solid #ef4444; border-radius: 12px; padding: 12px 18px; color: #991b1b; font-size: 13.5px; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span> <div>{{ session('err') }}</div>
            </div>
        @endif

        <div class="pricing-cards-grid">
            @forelse($packages as $pkg)
                @php
                    $slug = strtolower($pkg->slug ?? '');
                    
                    // 🎨 BẢNG MÀU 6 THEME ĐA SẮC MÀU TUẦN HOÀN CHUẨN IC3 ADVENTURE
                    // Dù Admin thêm bao nhiêu gói (4, 5, 6, 7...), các card tiếp theo đều tự động có màu sắc và icon riêng biệt
                    $paletteThemes = [
                        0 => ['tier' => 'tier-starter',  'icon' => '🚀', 'badge' => '🚀 Trải Nghiệm Khám Phá',       'btn_icon' => '🚀', 'name' => 'Xanh Ngọc Lục Bảo'],
                        1 => ['tier' => 'tier-standard', 'icon' => '👑', 'badge' => '👑 Phổ Biến Nhất ⭐',            'btn_icon' => '👑', 'name' => 'Tím Hoàng Gia VIP'],
                        2 => ['tier' => 'tier-pro',      'icon' => '🏫', 'badge' => '🔥 Siêu Tiết Kiệm Toàn Trường',  'btn_icon' => '🔥', 'name' => 'Cam Hổ Phách'],
                        3 => ['tier' => 'tier-cyan',     'icon' => '💎', 'badge' => '💎 Gói Chuyên Sâu Quốc Tế',     'btn_icon' => '💎', 'name' => 'Xanh Biển Sky Blue'],
                        4 => ['tier' => 'tier-rose',     'icon' => '🎯', 'badge' => '🎯 Gói Luyện Thi Bứt Phá',      'btn_icon' => '🎯', 'name' => 'Đỏ San Hô Ruby'],
                        5 => ['tier' => 'tier-fuchsia',  'icon' => '🏆', 'badge' => '🏆 Gói Bản Quyền Toàn Diện',    'btn_icon' => '🏆', 'name' => 'Tím Hồng Fuchsia'],
                    ];

                    // Ưu tiên khớp theo từ khóa slug hoặc tự động xoay vòng theo thứ tự
                    if (str_contains($slug, 'starter') || str_contains($slug, 'khoi-dau')) {
                        $selectedTheme = $paletteThemes[0];
                    } elseif (str_contains($slug, 'standard') || str_contains($slug, 'tieu-chuan')) {
                        $selectedTheme = $paletteThemes[1];
                    } elseif (str_contains($slug, 'pro') || str_contains($slug, 'truong-hoc')) {
                        $selectedTheme = $paletteThemes[2];
                    } else {
                        $selectedTheme = $paletteThemes[$loop->index % count($paletteThemes)];
                    }

                    $tierClass = $selectedTheme['tier'];
                    $btnIcon = $selectedTheme['btn_icon'];

                    // 💡 Tính năng thông minh: Nếu Admin nhập emoji trong trường Badge hoặc Tên gói, tự động trích xuất làm Orb icon!
                    $detectedEmoji = null;
                    if (!empty($pkg->badge) && preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $pkg->badge, $m)) {
                        $detectedEmoji = $m[0];
                    } elseif (preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $pkg->name, $m)) {
                        $detectedEmoji = $m[0];
                    }

                    $orbIcon = $detectedEmoji ?: $selectedTheme['icon'];
                    $badgeText = $pkg->badge ?: $selectedTheme['badge'];
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
                            @php
                                $friendlyPkgName = preg_replace('/\s*\((Starter|Standard|Pro School)\)\s*/i', '', $pkg->name);
                            @endphp
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
                            <div class="spec-item spec-students">
                                <div class="spec-label"><i>👥</i> Sĩ số quản lý:</div>
                                <div class="spec-value">{{ $pkg->max_students_text }}</div>
                            </div>
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
                                <li><span class="check-icon">✓</span> Báo cáo tiến độ học sinh</li>
                            @endif
                        </ul>
                    </div>

                    <!-- Action Button (Nút Bấm 3D Xúc Giác Cực Đẹp) -->
                    <div class="plan-cta-wrap">
                        @auth
                            @if(auth()->user()->isStudent())
                                <button type="button" class="btn-select-plan" style="background:#f1f5f9; color:#94a3b8; border:2px solid #cbd5e1; cursor:not-allowed; box-shadow:none;" disabled title="Gói này dành riêng cho Giáo viên">
                                    🔒 Dành riêng cho Giáo viên
                                </button>
                            @else
                                <button type="button" class="btn-select-plan" onclick="openOrderModal({{ json_encode($pkg) }}, true)">
                                    {{ $btnIcon }} Thuê Gói Này <b>→</b>
                                </button>
                            @endif
                        @else
                            <!-- Dành cho Khách chưa có tài khoản -->
                            <button type="button" class="btn-select-plan" onclick="openOrderModal({{ json_encode($pkg) }}, false)">
                                {{ $btnIcon }} Đăng Ký Tài Khoản <b>→</b>
                            </button>
                        @endauth
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #ffffff; border-radius: 16px; border: 1px dashed #cbd5e1;">
                    <p style="color: #64748b; font-size: 14px;">Hiện chưa có gói dịch vụ nào mở bán.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- 📋 MODAL ĐĂNG KÝ NHẬN TÀI KHOẢN GIÁO VIÊN HOẶC THUÊ GÓI (1 MÀN HÌNH CHUẨN ĐẸP) -->
    <div id="order-modal" class="modal-overlay" aria-hidden="true">
        <!-- BƯỚC 1: ĐIỀN THÔNG TIN & CHỌN PHƯƠNG THỨC -->
        <div class="modal-container-2col" id="modal-step-order">
            <!-- Left Column: Package Summary Preview (Giao diện Cao Cấp, Sắc Nét, Không Tệp Màu) -->
            <div class="modal-summary-col" id="modal-summary-panel">
                <div>
                    <span class="summary-badge" id="modal-pkg-badge">👑 GÓI BẢN QUYỀN GIÁO VIÊN</span>
                    <h3 class="summary-pkg-title" id="modal-pkg-name">Gói Tiêu Chuẩn</h3>
                    
                    <div class="summary-price-box">
                        <div class="summary-price-header">
                            <span style="font-size: 14px;">💰</span>
                            <small>TỔNG TIỀN THANH TOÁN</small>
                        </div>
                        <b id="modal-pkg-price">990.000 đ</b>
                    </div>

                    <div class="summary-meta-list">
                        <div class="summary-meta-item">
                            <div class="meta-icon-badge meta-icon-students">👥</div>
                            <div class="meta-content">
                                <span class="meta-label">Sĩ số quản lý</span>
                                <span class="meta-value" id="modal-pkg-students">100 học sinh</span>
                            </div>
                        </div>
                        <div class="summary-meta-item">
                            <div class="meta-icon-badge meta-icon-duration">📅</div>
                            <div class="meta-content">
                                <span class="meta-label">Thời hạn bản quyền</span>
                                <span class="meta-value" id="modal-pkg-duration">90 ngày</span>
                            </div>
                        </div>
                        <div class="summary-meta-item">
                            <div class="meta-icon-badge meta-icon-access">🔑</div>
                            <div class="meta-content">
                                <span class="meta-label">Quyền truy cập</span>
                                <span class="meta-value" id="modal-pkg-access">Toàn bộ ngân hàng đề thi IC3 GS6</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-guarantees">
                    <div class="guarantee-item">
                        <span class="guarantee-icon">⚡</span>
                        <span>Kích hoạt tài khoản tự động ngay khi thanh toán</span>
                    </div>
                    <div class="guarantee-item">
                        <span class="guarantee-icon">🛡️</span>
                        <span>Bảo mật dữ liệu học sinh & quản lý theo lớp</span>
                    </div>
                    <div class="guarantee-item">
                        <span class="guarantee-icon">💬</span>
                        <span>Hỗ trợ kỹ thuật 24/7 qua Zalo & Hotline</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form Inputs -->
            <div class="modal-form-col">
                <button type="button" class="modal-close-btn" onclick="closeOrderModal()" aria-label="Đóng">✕</button>

                <div class="form-header">
                    <h3 id="modal-heading">Đăng Ký Nhận Tài Khoản Giáo Viên</h3>
                    <p id="modal-subheading">Điền thông tin giáo viên để hệ thống tự động cấp tài khoản và hướng dẫn thanh toán.</p>
                </div>

                <!-- Form Order -->
                <form id="order-form" method="POST" action="" novalidate>
                    @csrf

                    @auth
                    <!-- Banner tài khoản đang đăng nhập -->
                    <div id="logged-user-banner" style="background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:12px; padding:8px 14px; margin-bottom:12px; display:none; align-items:center; justify-content:space-between; gap:10px;">
                        <div style="min-width:0; overflow:hidden;">
                            <div style="font-size:10.5px; color:#1d4ed8; font-weight:800; text-transform:uppercase; letter-spacing:0.4px;">✓ Đang đăng nhập tài khoản:</div>
                            <div style="font-size:13px; font-weight:800; color:#0f172a; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                {{ auth()->user()->name }} ({{ auth()->user()->email }})
                            </div>
                        </div>
                        <button type="button" id="btn-toggle-account-mode" onclick="toggleAccountMode()" style="font-size:11.5px; color:#2563eb; background:#ffffff; border:1.5px solid #93c5fd; border-radius:8px; padding:4px 10px; cursor:pointer; font-weight:800; white-space:nowrap;">
                            Đăng ký mới
                        </button>
                    </div>
                    @endauth

                    <!-- BƯỚC 1: THÔNG TIN GIÁO VIÊN NHẬN BẢN QUYỀN -->
                    <div class="form-section-banner">
                        <div class="form-section-title-wrap">
                            <span class="step-num-pill">1</span>
                            <span>Thông Tin Thầy/Cô Nhận Bản Quyền</span>
                        </div>
                    </div>

                    <div id="customer-fields">
                        <!-- Họ và tên -->
                        <div class="form-field">
                            <div class="form-label-row">
                                <label class="form-label" for="input-name">
                                    👤 Họ và tên Giáo viên <span class="required-star">*</span>
                                </label>
                                <span class="field-error" id="err-name">⚠ Vui lòng nhập họ tên</span>
                            </div>
                            <input type="text" name="name" id="input-name" class="form-input"
                                   placeholder="Ví dụ: Thầy Trần Quang Huy / Cô Nguyễn Mai Linh"
                                   value="{{ auth()->user()?->name ?? '' }}"
                                   oninput="validateField(this, 'name')">
                        </div>

                        <!-- Email + Mật khẩu -->
                        <div class="form-grid-2">
                            <div class="form-field">
                                <div class="form-label-row">
                                    <label class="form-label" for="input-email">
                                        📧 Email đăng nhập <span class="required-star">*</span>
                                    </label>
                                    <span class="field-error" id="err-email">⚠ Chưa đúng</span>
                                </div>
                                <input type="email" name="email" id="input-email" class="form-input"
                                       placeholder="giaovien@gmail.com"
                                       value="{{ auth()->user()?->email ?? '' }}"
                                       oninput="validateField(this, 'email')">
                            </div>
                            <div class="form-field" id="wrap-input-password">
                                <div class="form-label-row">
                                    <label class="form-label" for="input-password">
                                        🔒 Mật khẩu <span class="required-star" id="password-required-star">*</span>
                                    </label>
                                    <span class="field-error" id="err-password">⚠ ≥ 6 ký tự</span>
                                </div>
                                <input type="password" name="password" id="input-password" class="form-input"
                                       placeholder="{{ auth()->check() ? 'Giữ nguyên mật khẩu hiện tại' : 'Tối thiểu 6 ký tự' }}"
                                       oninput="validateField(this, 'password')">
                            </div>
                        </div>

                        <!-- Số điện thoại & Trường học -->
                        <div class="form-grid-2">
                            <div class="form-field">
                                <div class="form-label-row">
                                    <label class="form-label" for="input-phone">
                                        📱 Số điện thoại liên hệ (Zalo) <span class="required-star">*</span>
                                    </label>
                                    <span class="field-error" id="err-phone">⚠ Cần 10 chữ số</span>
                                </div>
                                <input type="tel" name="phone" id="input-phone" class="form-input"
                                       placeholder="Ví dụ: 0912 345 678"
                                       value="{{ auth()->user()?->phone ?? '' }}"
                                       oninput="validateField(this, 'phone')">
                            </div>
                            <div class="form-field">
                                <div class="form-label-row">
                                    <label class="form-label" for="input-school">
                                        🏫 Trường học / Đơn vị
                                    </label>
                                </div>
                                <input type="text" name="school_name" id="input-school" class="form-input"
                                       placeholder="Ví dụ: TH Lê Quý Đôn"
                                       value="{{ auth()->user()?->school_name ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- BƯỚC 2: HÌNH THỨC THANH TOÁN TỰ ĐỘNG DUY NHẤT -->
                    <div class="form-section-banner">
                        <div class="form-section-title-wrap">
                            <span class="step-num-pill">2</span>
                            <span>Hình Thức Chuyển Khoản Tự Động</span>
                        </div>
                        <span class="pay-badge-security">🛡️ An Toàn & Bảo Mật</span>
                    </div>

                    <input type="hidden" name="payment_method" value="payos" id="pay-method-payos">

                    <div class="pay-method-single-card">
                        <div class="pay-single-icon">⚡</div>
                        <div class="pay-single-info">
                            <div class="pay-single-title">
                                <span>Quét Mã VietQR Tự Động</span>
                                <span class="pay-badge-rec">⚡ Kích hoạt ngay</span>
                            </div>
                            <div class="pay-single-desc">
                                Mở App Ngân hàng bất kỳ để quét mã VietQR · Tự động điền STK & số tiền · Tài khoản kích hoạt ngay
                            </div>
                        </div>
                        <div class="pay-single-check">✓</div>
                    </div>

                    <!-- Ghi chú (optional) -->
                    <div class="form-field" style="margin-bottom: 8px;">
                        <input type="text" name="notes" id="input-notes" class="form-input" style="height:36px; font-size:12.5px;" placeholder="💬 Ghi chú thêm cho lớp / trường học (nếu có)...">
                    </div>

                    <!-- Submit Button -->
                    <button type="button" id="modal-submit-btn" class="btn-submit-order" onclick="handleOrderSubmit()">
                        🚀 Đăng Ký & Quét Mã QR Kích Hoạt Ngay →
                    </button>
                </form>
            </div>
        </div>

        <!-- BƯỚC 2: GIAO DIỆN CHUYỂN KHOẢN VIETQR RIÊNG XỊN ĐẸP GỌN ĐẦY ĐỦ -->
        <div class="modal-pay-card" id="modal-step-payment" style="display: none;">
            <!-- View Thành Công khi đơn được kích hoạt -->
            <div class="pay-success-card" id="modal-pay-success" style="display: none;">
                <div class="pay-success-badge">🎉</div>
                <h3>Thanh Toán & Kích Hoạt Thành Công!</h3>
                <p>
                    Gói bản quyền <b id="pay-success-pkg-name"></b> của Thầy/Cô đã được kích hoạt thành công. Đang chuyển hướng về Bàn Làm Việc...
                </p>
                <div class="pay-progress-bar">
                    <div class="pay-progress-bar-fill" id="pay-progress-bar-fill"></div>
                </div>
                <div style="margin-top: 18px;">
                    <a id="pay-success-direct-btn" href="{{ auth()->user()?->canAccessAdmin() ? route('admin.dashboard') : route('programs') }}" class="pay-btn-confirm" style="display:inline-flex; text-decoration:none; padding:10px 24px; font-size:13.5px;">
                        🚀 Vào Bàn Làm Việc Ngay →
                    </a>
                </div>
            </div>

            <!-- View Chi Tiết Chuyển Khoản & Quét Mã -->
            <div id="modal-pay-details-view">
                <!-- Header -->
                <div class="pay-card-head">
                    <div class="pay-card-brand">
                        <div class="pay-brand-chip">IC3</div>
                        <div class="pay-head-text">
                            <h3>Thanh Toán Chuyển Khoản</h3>
                            <div class="pay-meta-sub">
                                <span class="pay-order-code-badge" id="pay-modal-order-code">#MOS-...</span>
                                <span>· Gói: <b id="pay-modal-pkg-name"></b></span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="pay-live-status-pill">
                            <span class="pulse-dot"></span>
                            <span id="pay-modal-status-text">Đang chờ thanh toán...</span>
                        </div>
                        <button type="button" class="modal-close-btn" onclick="closeOrderModal()" aria-label="Đóng" style="position: static; transform: none; width: 28px; height: 28px; font-size: 13px;">✕</button>
                    </div>
                </div>

                <!-- Tip Guideline Banner -->
                <div class="pay-guide-banner">
                    <span style="font-size: 15px; flex-shrink: 0;">💡</span>
                    <span>Mở App Ngân hàng bất kỳ để <b>quét mã VietQR</b> hoặc <b>chuyển khoản</b> chính xác số tiền & nội dung bên dưới.</span>
                </div>

                <!-- Body Grid (QR Left, Info Right) -->
                <div class="pay-content-grid">
                    <!-- Left: VietQR Box -->
                    <div class="pay-qr-box">
                        <div class="pay-qr-img-wrap">
                            <img src="" alt="Mã VietQR" id="pay-modal-qr-img" loading="eager">
                        </div>
                        <div class="pay-qr-badges">
                            <span class="pay-qr-badge">⚡ Napas 247</span>
                            <span class="pay-qr-badge">🛡️ VietQR PRO</span>
                        </div>
                        <p class="pay-qr-hint">
                            Quét bằng App Ngân hàng bất kỳ hoặc Ví MoMo, ZaloPay, Viettel Money
                        </p>
                    </div>

                    <!-- Right: Bank Info Rows -->
                    <div class="pay-details-list">
                        <!-- Ngân hàng -->
                        <div class="pay-detail-row">
                            <div>
                                <div class="pay-detail-meta">🏦 Ngân hàng thụ hưởng</div>
                                <div class="pay-detail-val" id="pay-modal-bank-name">MB Bank</div>
                            </div>
                        </div>

                        <!-- Chủ tài khoản -->
                        <div class="pay-detail-row">
                            <div>
                                <div class="pay-detail-meta">👤 Chủ tài khoản</div>
                                <div class="pay-detail-val" id="pay-modal-acc-name">LE MINH TRI</div>
                            </div>
                        </div>

                        <!-- Số tài khoản -->
                        <div class="pay-detail-row">
                            <div>
                                <div class="pay-detail-meta">🔢 Số tài khoản</div>
                                <div class="pay-detail-val" id="pay-modal-acc-no" style="font-family: monospace; font-size: 14.5px; letter-spacing: 0.04em;">0345151438</div>
                            </div>
                            <button type="button" class="pay-copy-btn" onclick="copyModalText('pay-modal-acc-no', this)">
                                <span>📋</span> <span>Sao chép</span>
                            </button>
                        </div>

                        <!-- Số tiền -->
                        <div class="pay-detail-row pay-row-amount">
                            <div>
                                <div class="pay-detail-meta">💰 Số tiền thanh toán</div>
                                <div class="pay-detail-val" id="pay-modal-amount">390.000 đ</div>
                            </div>
                            <button type="button" class="pay-copy-btn" id="btn-copy-amount" onclick="copyModalVal(this)">
                                <span>📋</span> <span>Sao chép</span>
                            </button>
                        </div>

                        <!-- Nội dung -->
                        <div class="pay-detail-row pay-row-memo">
                            <div>
                                <div class="pay-detail-meta">✍️ Nội dung chuyển khoản (Bắt buộc)</div>
                                <div class="pay-detail-val" id="pay-modal-memo">MOS MOS...</div>
                            </div>
                            <button type="button" class="pay-copy-btn" onclick="copyModalText('pay-modal-memo', this)">
                                <span>📋</span> <span>Sao chép</span>
                            </button>
                        </div>

                        <!-- Lưu ý note -->
                        <div class="pay-note-alert">
                            <span>⚠️</span>
                            <span>Lưu ý: Vui lòng nhập <b>chính xác số tiền</b> và <b>nội dung chuyển khoản</b> để hệ thống tự động kích hoạt tài khoản ngay.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pay-foot-actions">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" class="pay-btn-close" onclick="closeOrderModal()">
                            ✕ Đóng
                        </button>
                        <button type="button" class="pay-btn-close" onclick="backToOrderForm()" style="color:#2563eb; border-color:#bfdbfe; background:#eff6ff;" title="Quay lại chỉnh sửa thông tin">
                            ← Đổi thông tin
                        </button>
                    </div>

                    <button type="button" class="pay-btn-confirm" id="btn-modal-confirm-transferred" onclick="confirmModalPaymentTransferred()">
                        <span>⚡ Tôi Đã Chuyển Khoản Xong</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 💬 FLOATING MESSENGER-STYLE LIVE CHAT WIDGET -->
    <button type="button" class="live-chat-toggle" onclick="toggleLiveChat()" aria-label="Tư vấn trực tuyến">
        <span class="online-dot"></span>
        <span>💬 Tư Vấn Giáo Viên</span>
    </button>

    <div id="live-chat-box" class="live-chat-box">
        <div class="chat-header">
            <div class="chat-header-profile">
                <div class="chat-avatar">IC3</div>
                <div class="chat-title">
                    <h4>Hỗ Trợ Giáo Viên IC3 Quest</h4>
                    <small>🟢 Trực tuyến · Phản hồi nhanh qua Zalo</small>
                </div>
            </div>
            <button type="button" class="chat-close-btn" onclick="toggleLiveChat()">✕</button>
        </div>

        <div class="chat-body" id="chat-messages-body">
            <div class="chat-msg chat-msg-bot">
                👋 Xin chào Quý Thầy/Cô! Em là chuyên viên tư vấn IC3 Quest. Thầy/Cô cần hỗ trợ gói bản quyền, cấu hình trường học hay hướng dẫn sử dụng có thể để lại tin nhắn ngay tại đây ạ!
            </div>
        </div>

        <div class="chat-footer">
            <form id="live-chat-form" onsubmit="submitSupportChat(event)">
                <input type="text" id="chat-sender-name" class="chat-input-compact" placeholder="Họ và tên của Thầy/Cô (*)" required>
                <input type="text" id="chat-sender-contact" class="chat-input-compact" placeholder="Số điện thoại hoặc Zalo liên hệ (*)" required>
                <textarea id="chat-sender-message" class="chat-input-compact" rows="2" placeholder="Thầy/Cô cần tư vấn gói nào hoặc có câu hỏi gì ạ? (*)" required></textarea>
                <button type="submit" id="chat-submit-btn" class="btn-send-chat">
                    🚀 Gửi Yêu Cầu Tư Vấn
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="pricing-footer">
        <p>Hệ Thống Luyện Thi Kỹ Năng Số Chuẩn Quốc Tế IC3 GS6 & Spark Quest © 2026. Hỗ trợ Giáo viên: 0987.654.321</p>
    </footer>

    <script>
        // =====================================================================
        // 🛒 MODAL ĐĂNG KÝ / THUÊ GÓI
        // =====================================================================
        let currentPkgData = null;
        let currentIsLoggedIn = false;
        let isCreateNewAccountMode = false;
        let selectedPaymentMethod = 'payos';
        let modalPollTimer = null;
        let currentModalOrderCode = null;

        const authUserEmail = '{{ auth()->user()?->email ?? "" }}';
        const authUserName = '{{ addslashes(auth()->user()?->name ?? "") }}';
        const authUserPhone = '{{ auth()->user()?->phone ?? "" }}';
        const authUserSchool = '{{ addslashes(auth()->user()?->school_name ?? "") }}';

        function selectPaymentMethod(method) {
            selectedPaymentMethod = 'payos';
            const input = document.getElementById('pay-method-payos');
            if (input) input.value = 'payos';
            updateSubmitButtonText();
        }

        function updateSubmitButtonText() {
            const btn = document.getElementById('modal-submit-btn');
            if (!btn) return;
            if (currentIsLoggedIn && !isCreateNewAccountMode) {
                btn.innerHTML = '⚡ Tiếp Tục Quét Mã QR Kích Hoạt →';
            } else {
                btn.innerHTML = '🚀 Đăng Ký & Quét Mã QR Kích Hoạt Ngay →';
            }
        }

        function openOrderModal(pkg, isLoggedIn) {
            currentPkgData = pkg;
            currentIsLoggedIn = isLoggedIn;
            isCreateNewAccountMode = false;

            // Làm sạch tên gói
            let friendlyName = pkg.name || '';
            friendlyName = friendlyName.replace(/\s*\((Starter|Standard|Pro School)\)\s*/gi, '').trim();
            document.getElementById('modal-pkg-name').innerText = friendlyName || pkg.name;

            document.getElementById('modal-pkg-duration').innerText = pkg.duration_days + ' ngày';
            document.getElementById('modal-pkg-students').innerText = pkg.max_students > 0 ? (pkg.max_students + ' học sinh') : 'Không giới hạn học sinh';

            const priceFormatted = new Intl.NumberFormat('vi-VN').format(pkg.price) + ' đ';
            document.getElementById('modal-pkg-price').innerText = priceFormatted;

            const badgeText = pkg.badge ? pkg.badge.toUpperCase() : 'BẢN QUYỀN GIÁO VIÊN';
            document.getElementById('modal-pkg-badge').innerText = '👑 ' + badgeText;

            const form = document.getElementById('order-form');
            const banner = document.getElementById('logged-user-banner');
            const passStar = document.getElementById('password-required-star');
            const passInput = document.getElementById('input-password');

            if (isLoggedIn) {
                document.getElementById('modal-heading').innerText = 'Xác Nhận Đăng Ký Thuê Gói Bản Quyền';
                document.getElementById('modal-subheading').innerText = 'Kiểm tra thông tin tài khoản và hoàn tất thanh toán để gia hạn hoặc kích hoạt gói.';
                if (banner) banner.style.display = 'flex';
                if (passStar) passStar.style.display = 'none';
                if (passInput) {
                    passInput.placeholder = 'Giữ nguyên mật khẩu hiện tại';
                    passInput.value = '';
                }
                const nameIn = document.getElementById('input-name');
                if (nameIn) nameIn.value = authUserName;
                const emailIn = document.getElementById('input-email');
                if (emailIn) emailIn.value = authUserEmail;
                const phoneIn = document.getElementById('input-phone');
                if (phoneIn) phoneIn.value = authUserPhone;
                const schoolIn = document.getElementById('input-school');
                if (schoolIn) schoolIn.value = authUserSchool;
                form.action = '/bang-gia/thue-goi/' + pkg.slug;
            } else {
                document.getElementById('modal-heading').innerText = 'Đăng Ký Nhận Tài Khoản Giáo Viên';
                document.getElementById('modal-subheading').innerText = 'Điền thông tin giáo viên để hệ thống cấp tài khoản và hướng dẫn thanh toán.';
                if (banner) banner.style.display = 'none';
                if (passStar) passStar.style.display = 'inline';
                if (passInput) {
                    passInput.placeholder = 'Tối thiểu 6 ký tự';
                    passInput.value = '';
                }
                const nameIn = document.getElementById('input-name');
                if (nameIn) nameIn.value = '';
                const emailIn = document.getElementById('input-email');
                if (emailIn) emailIn.value = '';
                const phoneIn = document.getElementById('input-phone');
                if (phoneIn) phoneIn.value = '';
                const schoolIn = document.getElementById('input-school');
                if (schoolIn) schoolIn.value = '';
                form.action = '/bang-gia/dang-ky-va-thue-goi/' + pkg.slug;
            }

            selectPaymentMethod('payos');
            resetFormValidation();

            // Reset modal step view
            if (modalPollTimer) clearInterval(modalPollTimer);
            const stepOrder = document.getElementById('modal-step-order');
            const stepPayment = document.getElementById('modal-step-payment');
            if (stepOrder) stepOrder.style.display = '';
            if (stepPayment) stepPayment.style.display = 'none';

            document.getElementById('order-modal').classList.add('open');
        }

        function toggleAccountMode() {
            isCreateNewAccountMode = !isCreateNewAccountMode;
            const btn = document.getElementById('btn-toggle-account-mode');
            const passStar = document.getElementById('password-required-star');
            const passInput = document.getElementById('input-password');
            const form = document.getElementById('order-form');

            if (isCreateNewAccountMode) {
                if (btn) btn.innerText = 'Dùng tài khoản đang đăng nhập';
                if (passStar) passStar.style.display = 'inline';
                if (passInput) {
                    passInput.placeholder = 'Tối thiểu 6 ký tự cho tài khoản mới';
                    passInput.value = '';
                }
                document.getElementById('input-name').value = '';
                document.getElementById('input-email').value = '';
                document.getElementById('input-phone').value = '';
                document.getElementById('input-school').value = '';
                form.action = '/bang-gia/dang-ky-va-thue-goi/' + currentPkgData.slug;
                document.getElementById('modal-heading').innerText = 'Đăng Ký Tài Khoản Giáo Viên Mới';
                document.getElementById('modal-subheading').innerText = 'Tạo tài khoản riêng biệt mới cho Giáo viên này.';
            } else {
                if (btn) btn.innerText = 'Đăng ký mới';
                if (passStar) passStar.style.display = 'none';
                if (passInput) {
                    passInput.placeholder = 'Giữ nguyên mật khẩu hiện tại';
                    passInput.value = '';
                }
                document.getElementById('input-name').value = authUserName;
                document.getElementById('input-email').value = authUserEmail;
                document.getElementById('input-phone').value = authUserPhone;
                document.getElementById('input-school').value = authUserSchool;
                form.action = '/bang-gia/thue-goi/' + currentPkgData.slug;
                document.getElementById('modal-heading').innerText = 'Xác Nhận Đăng Ký Thuê Gói Bản Quyền';
                document.getElementById('modal-subheading').innerText = 'Kiểm tra thông tin tài khoản và hoàn tất thanh toán để gia hạn hoặc kích hoạt gói.';
            }
            updateSubmitButtonText();
        }

        function backToOrderForm() {
            if (modalPollTimer) clearInterval(modalPollTimer);
            const stepOrder = document.getElementById('modal-step-order');
            const stepPayment = document.getElementById('modal-step-payment');
            if (stepPayment) stepPayment.style.display = 'none';
            if (stepOrder) stepOrder.style.display = 'grid';
            const btn = document.getElementById('modal-submit-btn');
            if (btn) {
                btn.disabled = false;
                updateSubmitButtonText();
            }
        }

        function closeOrderModal() {
            if (modalPollTimer) clearInterval(modalPollTimer);
            document.getElementById('order-modal').classList.remove('open');
        }

        document.getElementById('order-modal').addEventListener('click', function(e) {
            if (e.target === this) closeOrderModal();
        });

        // =====================================================================
        // ✅ REAL-TIME VALIDATION
        // =====================================================================
        function validateField(input, type) {
            const errorEl = document.getElementById('err-' + type);
            let valid = true;
            const val = input ? input.value.trim() : '';

            if (type === 'name') {
                valid = val.length >= 2;
            } else if (type === 'email') {
                valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
            } else if (type === 'password') {
                valid = val.length >= 6;
            } else if (type === 'phone') {
                valid = /^(0|\+84)[0-9]{9}$/.test(val.replace(/\s/g,''));
            }

            if (val === '') {
                if (input) input.classList.remove('is-valid', 'is-invalid');
                if (errorEl) errorEl.classList.remove('show');
            } else if (valid) {
                if (input) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                }
                if (errorEl) errorEl.classList.remove('show');
            } else {
                if (input) {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                }
                if (errorEl) errorEl.classList.add('show');
            }
            return valid && val !== '';
        }

        function resetFormValidation() {
            ['input-name','input-email','input-password','input-phone'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.classList.remove('is-valid','is-invalid'); }
            });
            document.querySelectorAll('.field-error').forEach(e => e.classList.remove('show'));
        }

        async function handleOrderSubmit() {
            const isGuestOrNew = !currentIsLoggedIn || isCreateNewAccountMode;
            let isValid = true;

            const nameInput  = document.getElementById('input-name');
            const emailInput = document.getElementById('input-email');
            const passInput  = document.getElementById('input-password');
            const phoneInput = document.getElementById('input-phone');

            if (isGuestOrNew) {
                const nameOk   = validateField(nameInput,  'name');
                const emailOk  = validateField(emailInput, 'email');
                const passOk   = validateField(passInput,  'password');
                const phoneOk  = validateField(phoneInput, 'phone');

                if (!nameOk) {
                    if (nameInput) nameInput.classList.add('is-invalid');
                    document.getElementById('err-name')?.classList.add('show');
                    isValid = false;
                }
                if (!emailOk) {
                    if (emailInput) emailInput.classList.add('is-invalid');
                    document.getElementById('err-email')?.classList.add('show');
                    isValid = false;
                }
                if (!passOk) {
                    if (passInput) passInput.classList.add('is-invalid');
                    document.getElementById('err-password')?.classList.add('show');
                    isValid = false;
                }
                if (!phoneOk) {
                    if (phoneInput) phoneInput.classList.add('is-invalid');
                    document.getElementById('err-phone')?.classList.add('show');
                    isValid = false;
                }
            } else {
                // Đã đăng nhập: chỉ kiểm tra họ tên và SĐT nếu được điền
                if (nameInput && nameInput.value.trim().length < 2) {
                    nameInput.classList.add('is-invalid');
                    document.getElementById('err-name')?.classList.add('show');
                    isValid = false;
                }
                if (phoneInput && phoneInput.value.trim().length > 0) {
                    const phoneOk = validateField(phoneInput, 'phone');
                    if (!phoneOk) {
                        phoneInput.classList.add('is-invalid');
                        document.getElementById('err-phone')?.classList.add('show');
                        isValid = false;
                    }
                }
            }

            if (!isValid) {
                const firstErr = document.querySelector('.form-input.is-invalid');
                if (firstErr) {
                    try { firstErr.focus({ preventScroll: true }); } catch (e) { firstErr.focus(); }
                }
                return;
            }

            const btn = document.getElementById('modal-submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '⏳ Đang khởi tạo đơn hàng & tạo mã VietQR...';
            }

            const form = document.getElementById('order-form');
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    if (btn) {
                        btn.disabled = false;
                        updateSubmitButtonText();
                    }
                    if (data.errors) {
                        for (const [key, msgs] of Object.entries(data.errors)) {
                            const input = document.getElementById('input-' + key);
                            const err = document.getElementById('err-' + key);
                            if (input) input.classList.add('is-invalid');
                            if (err) {
                                err.innerText = '⚠ ' + msgs[0];
                                err.classList.add('show');
                            }
                        }
                    } else {
                        alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                    }
                    return;
                }

                if (data.ok && data.order) {
                    // MỞ POPUP CHUYỂN KHOẢN NGAY TRÊN CÙNG MODAL (KHÔNG CHUYỂN TRANG)!
                    openPaymentPopup(data);
                } else {
                    if (btn) {
                        btn.disabled = false;
                        updateSubmitButtonText();
                    }
                    alert(data.message || 'Không thể tạo đơn hàng. Vui lòng thử lại.');
                }
            } catch (err) {
                console.error('Submit order error:', err);
                if (btn) {
                    btn.disabled = false;
                    updateSubmitButtonText();
                }
                alert('Có lỗi khi gửi yêu cầu. Vui lòng kiểm tra kết nối mạng và thử lại!');
            }
        }

        // =====================================================================
        // 💳 XỬ LÝ POPUP CHUYỂN KHOẢN VIETQR RIÊNG CỦA WEB (POPUP XỊN ĐẸP GỌN)
        // =====================================================================
        function openPaymentPopup(data) {
            currentModalOrderCode = data.order.code;

            // Ẩn form đặt hàng, hiển thị card chuyển khoản
            const stepOrder = document.getElementById('modal-step-order');
            const stepPayment = document.getElementById('modal-step-payment');
            if (stepOrder) stepOrder.style.display = 'none';
            if (stepPayment) stepPayment.style.display = 'flex';

            // Gán dữ liệu đơn hàng và ngân hàng vào popup
            document.getElementById('pay-modal-order-code').innerText = '#' + data.order.code;
            document.getElementById('pay-modal-pkg-name').innerText = data.order.package_name;
            document.getElementById('pay-modal-status-text').innerText = 'Đang chờ thanh toán...';
            document.getElementById('pay-modal-qr-img').src = data.qr_url;

            document.getElementById('pay-modal-bank-name').innerText = data.bank.bank_name;
            document.getElementById('pay-modal-acc-name').innerText = data.bank.account_name;
            document.getElementById('pay-modal-acc-no').innerText = data.bank.account_no;
            document.getElementById('pay-modal-amount').innerText = data.order.price_formatted;
            document.getElementById('btn-copy-amount').setAttribute('data-val', data.order.price);
            document.getElementById('pay-modal-memo').innerText = data.transfer_content;

            // Khôi phục giao diện ban đầu
            document.getElementById('modal-pay-details-view').style.display = 'block';
            document.getElementById('modal-pay-success').style.display = 'none';
            const btnConfirm = document.getElementById('btn-modal-confirm-transferred');
            if (btnConfirm) {
                btnConfirm.disabled = false;
                btnConfirm.innerHTML = '<span>⚡ Tôi Đã Chuyển Khoản Xong</span>';
            }

            // Real-time Polling: Kiểm tra trạng thái kích hoạt mỗi 2 giây
            if (modalPollTimer) clearInterval(modalPollTimer);
            modalPollTimer = setInterval(() => {
                pollModalOrderStatus(data.order.code);
            }, 2000);
        }

        function copyModalText(elementId, btn) {
            const el = document.getElementById(elementId);
            if (!el) return;
            executeModalCopy(el.innerText.trim(), btn);
        }

        function copyModalVal(btn) {
            const val = btn.getAttribute('data-val');
            if (val) executeModalCopy(val, btn);
        }

        function executeModalCopy(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const oldHtml = btn.innerHTML;
                btn.classList.add('copied');
                btn.innerHTML = '<span>✓</span> <span>Đã chép</span>';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = oldHtml;
                }, 1800);
            }).catch(() => {
                prompt("Nhấn Ctrl+C để sao chép:", text);
            });
        }

        function confirmModalPaymentTransferred() {
            if (!currentModalOrderCode) return;
            const btn = document.getElementById('btn-modal-confirm-transferred');
            if (!btn) return;
            btn.disabled = true;
            btn.innerHTML = '<span>⏳ Đang gửi thông báo tới Admin...</span>';

            fetch(`/bang-gia/don-hang/${currentModalOrderCode}/da-chuyen-khoan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<span>✓ Đã báo Admin kiểm tra!</span>';
                document.getElementById('pay-modal-status-text').innerText = 'Đang đợi kiểm tra và kích hoạt...';
            })
            .catch(err => {
                btn.innerHTML = '<span>✓ Đã ghi nhận chuyển khoản</span>';
            });
        }

        function pollModalOrderStatus(orderCode) {
            if (!orderCode) return;
            fetch(`/bang-gia/don-hang/${orderCode}/trang-thai`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.is_active) {
                    if (modalPollTimer) clearInterval(modalPollTimer);
                    showModalActivationSuccess(data.redirect_url);
                }
            })
            .catch(err => console.error('Modal poll error:', err));
        }

        function showModalActivationSuccess(redirectUrl) {
            document.getElementById('modal-pay-details-view').style.display = 'none';
            const successView = document.getElementById('modal-pay-success');
            if (successView) {
                successView.style.display = 'block';
                const pkgName = document.getElementById('pay-modal-pkg-name')?.innerText || '';
                const successPkgName = document.getElementById('pay-success-pkg-name');
                if (successPkgName) successPkgName.innerText = pkgName;

                const bar = document.getElementById('pay-progress-bar-fill');
                setTimeout(() => { if (bar) bar.style.width = '100%'; }, 100);

                setTimeout(() => {
                    window.location.href = redirectUrl || '/chuong-trinh';
                }, 2600);
            }
        }

        /* Live Chat logic */
        function toggleLiveChat() {
            const chatBox = document.getElementById('live-chat-box');
            chatBox.classList.toggle('open');
        }

        function submitSupportChat(e) {
            e.preventDefault();
            const btn = document.getElementById('chat-submit-btn');
            const name = document.getElementById('chat-sender-name').value.trim();
            const contact = document.getElementById('chat-sender-contact').value.trim();
            const message = document.getElementById('chat-sender-message').value.trim();
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!name || !contact || !message) return;

            btn.disabled = true;
            btn.innerText = 'Đang gửi...';

            fetch('/ho-tro/gui-tin-nhan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    contact: contact,
                    message: message
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = '🚀 Gửi Tới Ban Quản Trị';

                const body = document.getElementById('chat-messages-body');
                
                // Add user message bubble
                const userBubble = document.createElement('div');
                userBubble.className = 'chat-msg chat-msg-user';
                userBubble.innerText = message;
                body.appendChild(userBubble);

                // Add response bubble
                const botBubble = document.createElement('div');
                botBubble.className = 'chat-msg chat-msg-bot';
                botBubble.style.background = '#ecfdf5';
                botBubble.style.color = '#065f46';
                botBubble.style.borderColor = '#a7f3d0';
                botBubble.innerHTML = '✅ <b>Đã gửi tin nhắn tới Telegram Quản trị viên!</b> Em đã nhận thông tin của Thầy/Cô và sẽ liên hệ phản hồi qua SĐT/Telegram trong ít phút ạ!';
                body.appendChild(botBubble);

                body.scrollTop = body.scrollHeight;

                if (data.message_id) {
                    activeTeacherChatId = data.message_id;
                    localStorage.setItem('mos_active_support_id', activeTeacherChatId);
                    if (!teacherPollingTimer) {
                        teacherPollingTimer = setInterval(pollTeacherChatReply, 2500);
                    }
                }

                // Reset message field
                document.getElementById('chat-sender-message').value = '';
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = '🚀 Gửi Tới Ban Quản Trị';
                alert('Có lỗi xảy ra khi gửi tin nhắn. Thầy/Cô vui lòng thử lại hoặc gọi hotline!');
            });
        }

        // =====================================================================
        // ⚡ REAL-TIME POLLING CHO KHÁCH HÀNG (NHẬN PHẢN HỒI TỪ ADMIN TỨC THÌ)
        // =====================================================================
        let activeTeacherChatId = localStorage.getItem('mos_active_support_id') || null;
        let lastDisplayedAdminReply = null;
        let teacherPollingTimer = null;

        function playTeacherChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.4);
            } catch(e) {}
        }

        async function pollTeacherChatReply() {
            if (!activeTeacherChatId) return;
            try {
                const res = await fetch(`/ho-tro/tin-nhan/kiem-tra?id=${activeTeacherChatId}`);
                if (!res.ok) return;
                const data = await res.json();
                if (data.ok && data.admin_reply && data.admin_reply !== lastDisplayedAdminReply) {
                    lastDisplayedAdminReply = data.admin_reply;
                    playTeacherChime();

                    const body = document.getElementById('chat-messages-body');
                    if (body) {
                        let replyContainer = document.getElementById('admin-reply-bubble-' + activeTeacherChatId);
                        if (!replyContainer) {
                            replyContainer = document.createElement('div');
                            replyContainer.id = 'admin-reply-bubble-' + activeTeacherChatId;
                            replyContainer.className = 'chat-msg chat-msg-bot';
                            replyContainer.style.background = 'linear-gradient(135deg, #eff6ff, #dbeafe)';
                            replyContainer.style.color = '#1e3a8a';
                            replyContainer.style.border = '1px solid #bfdbfe';
                            replyContainer.style.borderRadius = '16px 16px 16px 4px';
                            body.appendChild(replyContainer);
                        }

                        const timeStr = data.replied_at || 'Vừa xong';
                        const safeContent = data.admin_reply.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        replyContainer.innerHTML = `
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px; font-weight:800; font-size:12px; color:#1d4ed8;">
                                <span>👨‍💼 Ban Quản Trị IC3 Quest</span>
                                <span style="font-size:10.5px; opacity:0.75; font-weight:normal;">${timeStr}</span>
                            </div>
                            <div style="font-size:13.5px; line-height:1.45; color:#0f172a; white-space:pre-line;">${safeContent}</div>
                        `;
                        body.scrollTop = body.scrollHeight;
                    }
                }
            } catch(e) {}
        }

        if (activeTeacherChatId) {
            teacherPollingTimer = setInterval(pollTeacherChatReply, 2500);
        }
    </script>
</body>
</html>
