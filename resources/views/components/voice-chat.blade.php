{{--
    Trò chuyện bằng giọng nói với Trợ lý AI (kiểu ChatGPT/Gemini voice): em nói, AI nghe, AI nghĩ, AI đọc to; chữ cả hai bên hiện trên màn hình.
    - Nghe: nhận dạng giọng nói có sẵn trong trình duyệt (Chrome/Edge); máy không hỗ trợ thì ghi âm ngắn và gửi lên máy chủ.
    - Nghĩ: Trợ lý AI qua máy chủ, biết dữ liệu học tập + chủ đề + câu sai + sao thưởng của chính em. Đọc: giọng tiếng Việt có sẵn của trình duyệt.
    - Làm câu hỏi: AI ra câu hỏi thật từ ngân hàng đề, em nói đáp án, máy chủ chấm và AI giải thích (chỉ luyện tập, không cộng sao).
    - Ngắt lời: chạm vào quả cầu, hoặc cứ nói chen vào khi AI đang nói (nên đeo tai nghe để tránh tiếng loa lọt vào micro).
    Mở bằng window.openVoiceChat(). Không lưu âm thanh.
--}}
@php
    $vcUser = auth()->user();
@endphp
@if($vcUser && ! $vcUser->canAccessAdmin())
@php
    $vcHasAi = $vcUser->hasAiAssistant();
    $vcGreeting = $vcUser->isStudent()
        ? 'Chào em! Cô đây. Em muốn nói chuyện hay làm vài câu hỏi luyện tập nào?'
        : 'Chào Thầy Cô! Mình có thể giúp gì cho Thầy Cô ạ?';
@endphp
<style>
    .vc-overlay { position: fixed; inset: 0; z-index: 100001; display: none; flex-direction: column; color: #fff; font-family: 'Nunito', 'Segoe UI', sans-serif; background: radial-gradient(circle at 50% 22%, #5b21b6 0%, #2e1065 52%, #170a38 100%); }
    .vc-overlay.open { display: flex; }
    .vc-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; }
    .vc-title { font-family: 'Fredoka', 'Nunito', sans-serif; font-size: 18px; font-weight: 800; }
    .vc-score { display: none; margin-left: 10px; padding: 4px 12px; border-radius: 999px; background: #fde68a; color: #78350f; font-size: 13px; font-weight: 900; }
    .vc-score.show { display: inline-block; }
    .vc-close { border: 0; border-radius: 12px; padding: 9px 16px; font-size: 14px; font-weight: 900; color: #fff; cursor: pointer; background: #ef4444; box-shadow: 0 4px 0 #991b1b; }
    .vc-close:active { transform: translateY(3px); box-shadow: 0 1px 0 #991b1b; }
    .vc-start, .vc-talk { flex: 1; min-height: 0; display: none; }
    .vc-start { align-items: center; justify-content: center; text-align: center; padding: 20px; flex-direction: column; gap: 12px; }
    .vc-start.show { display: flex; }
    .vc-start .vc-bot { font-size: 72px; line-height: 1; }
    .vc-start h2 { margin: 0; font-family: 'Fredoka', 'Nunito', sans-serif; font-size: 30px; font-weight: 800; }
    .vc-start p { margin: 0; max-width: 540px; font-size: 15px; font-weight: 700; color: #ddd6fe; line-height: 1.6; }
    .vc-note { font-size: 12.5px !important; color: #c4b5fd !important; }
    .vc-go { margin-top: 8px; border: 3.5px solid #fff; border-radius: 22px; padding: 16px 30px; font-size: 18px; font-weight: 900; color: #fff; cursor: pointer; background: linear-gradient(135deg, #f59e0b, #ec4899); box-shadow: 0 16px 36px rgba(0,0,0,0.25), inset 0 -6px 0 rgba(0,0,0,0.15); transition: transform .12s; }
    .vc-go:hover { transform: translateY(-2px); }
    .vc-go:active { transform: translateY(4px); }
    .vc-talk.show { display: flex; flex-direction: column; align-items: center; }

    /* Quả cầu */
    .vc-stage { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 4px 16px 6px; flex-shrink: 0; }
    .vc-orb { --lvl: 0; position: relative; width: 132px; height: 132px; border-radius: 50%; border: 0; cursor: pointer; background: radial-gradient(circle at 35% 30%, #fff 0%, #c4b5fd 30%, #7c3aed 70%, #4c1d95 100%); box-shadow: 0 0 0 calc(8px + var(--lvl) * 26px) rgba(196,181,253,0.25), 0 0 60px rgba(167,139,250,0.6); transition: box-shadow .08s, transform .2s; }
    .vc-orb::after { content: ''; position: absolute; inset: -14px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.28); opacity: 0; }
    .vc-listening .vc-orb { transform: scale(calc(1 + var(--lvl) * 0.12)); background: radial-gradient(circle at 35% 30%, #fff 0%, #a7f3d0 30%, #10b981 70%, #065f46 100%); box-shadow: 0 0 0 calc(8px + var(--lvl) * 26px) rgba(110,231,183,0.25), 0 0 60px rgba(52,211,153,0.55); }
    .vc-thinking .vc-orb { animation: vcThink 1.1s ease-in-out infinite; background: radial-gradient(circle at 35% 30%, #fff 0%, #fde68a 30%, #f59e0b 70%, #92400e 100%); box-shadow: 0 0 60px rgba(251,191,36,0.55); }
    .vc-speaking .vc-orb { animation: vcSpeak 0.9s ease-in-out infinite; }
    .vc-speaking .vc-orb::after { animation: vcRing 1.4s ease-out infinite; }
    .vc-muted .vc-orb { filter: grayscale(0.8); opacity: .75; }
    @keyframes vcThink { 0%, 100% { transform: scale(0.94); } 50% { transform: scale(1.04); } }
    @keyframes vcSpeak { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
    @keyframes vcRing { 0% { transform: scale(0.9); opacity: .8; } 100% { transform: scale(1.35); opacity: 0; } }
    .vc-state { font-size: 15px; font-weight: 900; text-align: center; min-height: 20px; }
    .vc-hint { font-size: 12px; font-weight: 700; color: #c4b5fd; text-align: center; min-height: 15px; }

    /* Khung chat: kính mờ, có ảnh đại diện, bong bóng hiện dần */
    .vc-panel { flex: 1; min-height: 0; width: min(800px, calc(100% - 28px)); display: flex; flex-direction: column; border-radius: 26px; border: 2px solid rgba(255,255,255,0.16); background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); box-shadow: 0 18px 50px rgba(0,0,0,0.28), inset 0 1px 0 rgba(255,255,255,0.18); backdrop-filter: blur(10px); overflow: hidden; }
    .vc-log { flex: 1; min-height: 0; overflow-y: auto; padding: 16px 16px 10px; display: flex; flex-direction: column; gap: 12px; scroll-behavior: smooth; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.3) transparent; }
    .vc-row { flex-shrink: 0; display: flex; align-items: flex-end; gap: 9px; animation: vcPop .28s ease-out both; }
    .vc-row.vc-user { flex-direction: row-reverse; }
    .vc-ava { flex: 0 0 36px; width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; font-size: 19px; border: 2.5px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.25); }
    .vc-row.vc-ai .vc-ava { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
    .vc-row.vc-user .vc-ava { background: linear-gradient(135deg, #38bdf8, #6366f1); }
    .vc-msg { max-width: min(78%, 560px); padding: 11px 16px; border-radius: 20px; font-size: 16px; font-weight: 700; line-height: 1.6; word-break: break-word; box-shadow: 0 6px 16px rgba(0,0,0,0.18); }
    .vc-row.vc-ai .vc-msg { background: #ffffff; color: #2e1065; border-bottom-left-radius: 6px; }
    .vc-row.vc-user .vc-msg { background: linear-gradient(135deg, #3b82f6, #6366f1); border-bottom-right-radius: 6px; }
    .vc-row.vc-sys { justify-content: center; }
    .vc-row.vc-sys .vc-msg { background: rgba(255,255,255,0.14); font-size: 13.5px; text-align: center; box-shadow: none; }
    /* Thẻ thống kê: ô số liệu + biểu đồ cột + chủ đề cần ôn */
    .vc-statcard { flex-shrink: 0; align-self: stretch; margin-left: 45px; border-radius: 20px; border: 3px solid #fff; background: #fff; color: #1e1b4b; overflow: hidden; box-shadow: 0 12px 28px rgba(0,0,0,0.25); animation: vcPop .35s ease-out both; }
    .vc-stat-head { padding: 8px 14px; background: linear-gradient(135deg, #7c3aed, #ec4899); color: #fff; font-size: 13px; font-weight: 900; }
    .vc-tiles { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; padding: 10px 12px 4px; }
    @media (max-width: 420px) { .vc-tiles { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .vc-tile { background: #f5f3ff; border: 2px solid #ddd6fe; border-radius: 14px; padding: 7px 6px; text-align: center; }
    .vc-tile i { display: block; font-style: normal; font-size: 18px; line-height: 1.2; }
    .vc-tile b { display: block; font-size: 19px; font-weight: 900; color: #5b21b6; line-height: 1.15; }
    .vc-tile span { display: block; font-size: 11px; font-weight: 800; color: #6b7280; }
    .vc-sec { padding: 8px 14px 2px; font-size: 12px; font-weight: 900; color: #6b7280; }
    .vc-chart { position: relative; display: flex; align-items: flex-end; gap: 6px; height: 110px; margin: 4px 14px 2px; padding-top: 6px; border-bottom: 2px solid #ddd6fe; }
    .vc-passline { position: absolute; left: 0; right: 0; border-top: 2px dashed #f59e0b; }
    .vc-passline::after { content: 'Mức đạt'; position: absolute; right: 0; top: -15px; font-size: 10px; font-weight: 900; color: #b45309; }
    .vc-bar-col { flex: 1; min-width: 0; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; }
    .vc-bar-val { font-size: 10.5px; font-weight: 900; color: #374151; }
    .vc-bar-fill { width: 100%; max-width: 34px; height: 0; border-radius: 7px 7px 0 0; background: #f97316; transition: height .6s cubic-bezier(.2,.8,.2,1); }
    .vc-bar-fill.pass { background: #22c55e; }
    .vc-xlabels { display: flex; gap: 6px; margin: 2px 14px 6px; }
    .vc-xlabels span { flex: 1; min-width: 0; text-align: center; font-size: 10px; font-weight: 800; color: #6b7280; }
    .vc-weak { display: grid; gap: 5px; padding: 2px 14px 12px; }
    .vc-weak-row { display: grid; grid-template-columns: minmax(80px, 38%) 1fr 26px; gap: 8px; align-items: center; font-size: 12px; font-weight: 800; }
    .vc-weak-track { height: 9px; border-radius: 6px; background: #fee2e2; overflow: hidden; }
    .vc-weak-fill { height: 100%; width: 0; border-radius: 6px; background: #ef4444; transition: width .6s cubic-bezier(.2,.8,.2,1); }
    .vc-rich p { margin: 0 0 7px; }
    .vc-rich ul { margin: 2px 0 8px; padding-left: 20px; }
    .vc-rich li { margin: 3px 0; }
    .vc-rich strong { color: #5b21b6; font-weight: 900; }
    .vc-rich > :last-child { margin-bottom: 0; }
    .vc-blk { animation: vcPop .3s ease-out both; }
    .vc-caret::after { content: '▍'; margin-left: 2px; opacity: .55; animation: vcBlink .8s steps(2) infinite; }
    @keyframes vcBlink { 50% { opacity: 0; } }
    @keyframes vcPop { from { opacity: 0; transform: translateY(10px) scale(.97); } to { opacity: 1; transform: none; } }
    .vc-dots { display: inline-flex; gap: 5px; padding: 4px 2px; }
    .vc-dots i { width: 8px; height: 8px; border-radius: 50%; background: #7c3aed; animation: vcDot 1s infinite ease-in-out; }
    .vc-dots i:nth-child(2) { animation-delay: .15s; }
    .vc-dots i:nth-child(3) { animation-delay: .3s; }
    @keyframes vcDot { 0%, 80%, 100% { transform: translateY(0); opacity: .45; } 40% { transform: translateY(-6px); opacity: 1; } }
    .vc-live { align-self: flex-end; max-width: 78%; padding: 9px 15px; border-radius: 18px; font-size: 15px; font-weight: 700; font-style: italic; color: #e9d5ff; background: rgba(255,255,255,0.10); border: 1.5px dashed rgba(255,255,255,0.3); }
    .vc-live:empty { display: none; }

    /* Thẻ câu hỏi luyện tập */
    .vc-quiz { flex-shrink: 0; align-self: center; width: min(100%, 620px); border-radius: 22px; overflow: hidden; border: 3.5px solid #fff; background: #fff; color: #1e1b4b; box-shadow: 0 16px 36px rgba(0,0,0,0.25), inset 0 -6px 0 rgba(0,0,0,0.12); animation: vcPop .3s ease-out both; }
    .vc-quiz-head { display: flex; justify-content: space-between; gap: 8px; padding: 9px 16px; background: linear-gradient(135deg, #f59e0b, #ec4899); color: #fff; font-size: 12.5px; font-weight: 900; }
    .vc-skip { display: none; border: 0; border-radius: 999px; padding: 3px 11px; font: inherit; font-size: 11.5px; font-weight: 900; color: #be185d; background: #fff; cursor: pointer; box-shadow: 0 2px 0 rgba(0,0,0,0.15); white-space: nowrap; }
    .vc-speaking .vc-skip { display: inline-block; }
    .vc-skip:hover { background: #fdf2f8; }
    .vc-quiz-q { padding: 14px 16px 6px; font-size: 17px; font-weight: 900; line-height: 1.5; }
    .vc-quiz-multi { padding: 0 16px; font-size: 12.5px; font-weight: 800; color: #b45309; }
    .vc-opts { display: grid; gap: 8px; padding: 10px 14px 14px; }
    .vc-opt { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 14px; border: 2.5px solid #ddd6fe; background: #f5f3ff; font-size: 15.5px; font-weight: 800; transition: background .2s, border-color .2s; }
    .vc-opt b { flex: 0 0 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; background: #7c3aed; color: #fff; font-size: 14px; }
    .vc-opt.ok { background: #dcfce7; border-color: #22c55e; }
    .vc-opt.ok b { background: #16a34a; }
    .vc-opt.bad { background: #fee2e2; border-color: #ef4444; }
    .vc-opt.bad b { background: #dc2626; }
    .vc-action { flex-shrink: 0; align-self: flex-start; margin-left: 45px; display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 16px; border: 3px solid #fff; background: linear-gradient(135deg, #10b981, #0ea5e9); color: #fff; font-size: 15px; font-weight: 900; text-decoration: none; box-shadow: 0 10px 24px rgba(0,0,0,0.25), inset 0 -4px 0 rgba(0,0,0,0.15); animation: vcPop .3s ease-out both; }
    .vc-action:hover { filter: brightness(1.08); }
    .vc-opt.picked::after { content: 'Em chọn'; margin-left: auto; font-size: 11.5px; font-weight: 900; color: #6d28d9; }

    /* Bảng câu hỏi nổi cố định phía trên khung chat: nhìn rõ, bấm chọn được, không bị cuộn mất */
    .vc-quiz { box-sizing: border-box; }
    .vc-quizpane { overflow-x: hidden; display: none; width: min(800px, calc(100% - 28px)); max-height: 46vh; overflow-y: auto; flex-shrink: 0; margin: 0 0 10px; }
    .vc-quizmode .vc-quizpane { display: block; }
    .vc-quizmode .vc-orb { width: 80px; height: 80px; }
    .vc-quizmode .vc-hint { display: none; }
    .vc-quizpane .vc-quiz { width: 100%; align-self: stretch; }
    button.vc-opt { width: 100%; text-align: left; font-family: inherit; color: #1e1b4b; cursor: pointer; }
    button.vc-opt:hover { border-color: #7c3aed; background: #ede9fe; }
    .vc-opt.sel { border-color: #7c3aed; background: #ddd6fe; }
    .vc-opt.sel b { background: #5b21b6; }
    .vc-quiz.done .vc-opt { pointer-events: none; }
    .vc-quiz-tip { padding: 0 16px 2px; font-size: 12px; font-weight: 800; color: #6b7280; }
    .vc-quiz-result { padding: 4px 16px 0; font-size: 16px; font-weight: 900; }
    .vc-quiz-result.ok { color: #15803d; }
    .vc-quiz-result.bad { color: #b91c1c; }
    .vc-quiz-foot { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; padding: 0 14px 14px; }
    .vc-quiz-img { display: block; max-width: 100%; max-height: 210px; margin: 6px auto 4px; border-radius: 12px; border: 2px solid #ddd6fe; background: #fff; object-fit: contain; }
    .vc-opt img { max-height: 84px; max-width: 46%; border-radius: 8px; border: 1.5px solid #ddd6fe; background: #fff; margin-left: auto; }
    .vc-rows { display: grid; gap: 8px; padding: 10px 14px 4px; }
    .vc-rowitem { display: grid; grid-template-columns: 1fr minmax(150px, 42%); gap: 10px; align-items: center; padding: 8px 10px; border-radius: 14px; border: 2.5px solid #ddd6fe; background: #f5f3ff; font-size: 15px; font-weight: 800; }
    .vc-rowitem select { width: 100%; padding: 8px; border-radius: 10px; border: 2px solid #c4b5fd; font: inherit; font-weight: 800; color: #1e1b4b; background: #fff; }
    .vc-rowitem.ok { border-color: #22c55e; background: #dcfce7; }
    .vc-rowitem.bad { border-color: #ef4444; background: #fee2e2; }
    .vc-rowitem .vc-fix { grid-column: 1 / -1; font-size: 13px; font-weight: 800; color: #15803d; }
    .vc-quiz.done .vc-rowitem select { pointer-events: none; opacity: .85; }
    @media (max-width: 560px) { .vc-rowitem { grid-template-columns: 1fr; } }
    .vc-qbtn { border: 0; border-radius: 12px; padding: 9px 16px; font-size: 14px; font-weight: 900; color: #fff; cursor: pointer; background: linear-gradient(135deg, #7c3aed, #5b21b6); box-shadow: 0 4px 0 #3b0f8a; }
    .vc-qbtn:active { transform: translateY(3px); box-shadow: 0 1px 0 #3b0f8a; }
    .vc-qbtn.alt { background: #e5e7eb; color: #374151; box-shadow: 0 4px 0 #9ca3af; }
    .vc-bar { display: flex; gap: 12px; justify-content: center; padding: 10px 16px 14px; flex-shrink: 0; }
    .vc-btn { border: 3px solid rgba(255,255,255,0.7); border-radius: 16px; padding: 10px 18px; font-size: 14.5px; font-weight: 900; color: #fff; cursor: pointer; background: rgba(255,255,255,0.14); }
    .vc-btn:hover { background: rgba(255,255,255,0.24); }
    .vc-btn.is-off { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .vc-talk .vc-bar { width: 100%; }
    /* ===== Bố cục khi đang làm câu hỏi: thẻ câu hỏi là trung tâm, ít phải cuộn ===== */
    .vc-stage { grid-area: stage; }
    .vc-quizpane { grid-area: quiz; }
    .vc-panel { grid-area: chat; }
    .vc-talk .vc-bar { grid-area: bar; }
    .vc-quizmode .vc-talk.show {
        display: grid; align-items: stretch; justify-items: stretch;
        grid-template-columns: minmax(0, 1.55fr) minmax(280px, 1fr);
        grid-template-rows: auto minmax(0, 1fr) auto;
        grid-template-areas: "stage stage" "quiz chat" "bar bar";
        gap: 10px 16px; padding: 0 16px 8px;
        width: 100%; max-width: 1240px; margin: 0 auto;
    }
    .vc-quizmode .vc-stage { flex-direction: row; justify-content: center; gap: 14px; padding: 2px 0 0; }
    .vc-quizmode .vc-orb { width: 58px; height: 58px; box-shadow: 0 0 0 calc(4px + var(--lvl) * 14px) rgba(196,181,253,0.25), 0 0 26px rgba(167,139,250,0.55); }
    .vc-quizmode .vc-orb::after { inset: -7px; border-width: 2px; }
    .vc-quizmode .vc-state { font-size: 14px; min-height: 0; }
    .vc-quizmode .vc-quizpane { width: auto; max-height: none; margin: 0; min-height: 0; overflow-y: auto; overflow-x: hidden; }
    .vc-quizmode .vc-panel { width: auto; min-height: 0; }
    /* Căn thẻ vào giữa theo chiều dọc; thẻ cao hơn khung thì cuộn bình thường, không bị cắt */
    .vc-quizmode .vc-quizpane { display: flex; flex-direction: column; }
    .vc-quizmode .vc-quizpane .vc-quiz { margin: 0; flex: 1 0 auto; display: flex; flex-direction: column; }
    .vc-quizmode .vc-quiz-foot { margin-top: auto; }
    .vc-quizmode .vc-opts { align-content: start; }
    /* Thanh cuộn mảnh, bo tròn; luôn chừa chỗ cho nó để thẻ không bị co lại và giật khi nội dung dài ra */
    .vc-quizpane, .vc-log { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.35) transparent; }
    .vc-quizpane { scrollbar-gutter: stable; }
    .vc-quizpane::-webkit-scrollbar, .vc-log::-webkit-scrollbar { width: 8px; }
    .vc-quizpane::-webkit-scrollbar-track, .vc-log::-webkit-scrollbar-track { background: transparent; }
    .vc-quizpane::-webkit-scrollbar-thumb, .vc-log::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.32); border-radius: 8px; }
    .vc-quizpane::-webkit-scrollbar-thumb:hover, .vc-log::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.5); }
    /* Chừa sẵn chỗ cho dòng kết quả và hàng nút: hiện kết quả không làm thẻ cao thêm */
    .vc-quiz-tip, .vc-quiz-result { min-height: 24px; line-height: 24px; }
    .vc-quiz-foot { min-height: 52px; }
    .vc-quiz-tip.vc-quiz-result { padding: 0 16px 2px; }
    /* Các dòng phân loại / ghép nối gọn hơn; đáp án đúng hiện ngay trong ô chọn nên dòng không cao thêm */
    .vc-quizmode .vc-rowitem { padding: 6px 10px; gap: 8px; font-size: clamp(13.5px, 1.1vw, 15px); min-height: 52px; }
    .vc-quizmode .vc-rowitem select { padding: 6px 8px; font-size: 14px; }
    .vc-ans { display: flex; flex-direction: column; gap: 1px; justify-content: center; min-height: 38px; font-size: 12.5px; font-weight: 800; line-height: 1.25; }
    .vc-ans .mine { color: #b91c1c; text-decoration: line-through; text-decoration-thickness: 1.5px; }
    .vc-ans .good { color: #15803d; }
    .vc-quizmode .vc-bar { padding: 4px 0 2px; }
    .vc-quizmode .vc-btn { padding: 8px 16px; }
    /* Thẻ gọn hơn: chữ co giãn theo màn hình, đáp án xếp 2 cột, nút luôn dính ở đáy thẻ */
    /* Không cắt nội dung ở góc bo để nút dưới đáy dính được vào khung cuộn */
    .vc-quizmode .vc-quiz { overflow: visible; }
    .vc-quizmode .vc-quiz-head { padding: 7px 14px; border-radius: 18px 18px 0 0; }
    .vc-quizmode .vc-quiz-q { padding: 12px 16px 6px; font-size: clamp(15px, 1.35vw, 18px); line-height: 1.45; }
    .vc-quizmode .vc-quiz-head { font-size: 12px; }
    .vc-quizmode .vc-quiz-multi { font-size: 12px; }
    .vc-quizmode .vc-quiz-result { font-size: 14.5px; }
    .vc-quizmode .vc-qbtn { font-size: 13px; padding: 8px 14px; }
    .vc-quizmode .vc-opts { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); padding: 8px 14px 10px; gap: 8px; }
    .vc-quizmode .vc-opt { padding: 8px 12px; min-height: 44px; font-size: clamp(13.5px, 1.15vw, 15px); }
    .vc-quizmode .vc-opt b { flex-basis: 26px; height: 26px; font-size: 13px; }
    .vc-quizmode .vc-rows { padding: 8px 14px 4px; gap: 7px; }
    .vc-quizmode .vc-quiz-foot { position: sticky; bottom: 0; padding: 8px 14px 10px; border-radius: 0 0 18px 18px; background: linear-gradient(180deg, rgba(255,255,255,0), #fff 28%); }
    .vc-quizmode .vc-quiz-img { max-height: 24vh; }
    .vc-quizmode .vc-msg { font-size: 14px; padding: 8px 12px; line-height: 1.5; }
    .vc-quizmode .vc-ava { flex-basis: 30px; width: 30px; height: 30px; font-size: 16px; }
    .vc-quizmode .vc-log { padding: 12px 12px 8px; gap: 9px; }
    /* Màn hình hẹp (điện thoại, máy tính bảng): thẻ câu hỏi ở trên, khung chat gọn phía dưới */
    @media (max-width: 900px) {
        .vc-quizmode .vc-talk.show {
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto minmax(0, 1fr) minmax(86px, 24vh) auto;
            grid-template-areas: "stage" "quiz" "chat" "bar";
            padding: 0 10px 8px;
        }
        .vc-quizmode .vc-opts { grid-template-columns: 1fr; }
        .vc-quizmode .vc-quiz-img { max-height: 15vh; }
        .vc-quizmode .vc-opt img { max-height: 52px; }
        .vc-quizmode .vc-quiz-tip { display: none; }
        .vc-quizmode .vc-quiz-q { font-size: 15px; padding-top: 10px; }
    }
    @media (max-width: 520px) {
        .vc-head { padding: 10px 12px; }
        .vc-title { font-size: 15px; white-space: nowrap; }
        .vc-score { margin-left: 6px; padding: 3px 9px; font-size: 12px; }
        .vc-close { padding: 7px 12px; font-size: 13px; white-space: nowrap; }
        .vc-quizmode .vc-talk.show { grid-template-rows: auto minmax(0, 1fr) minmax(80px, 22vh) auto; }
    }
    @media (max-height: 640px) {
        .vc-quizmode .vc-talk.show { grid-template-rows: auto minmax(0, 1fr) minmax(64px, 20vh) auto; }
        .vc-quizmode .vc-orb { width: 44px; height: 44px; }
        .vc-quizmode .vc-quiz-q { font-size: 15.5px; padding-top: 8px; }
        .vc-quizmode .vc-opt { min-height: 40px; padding: 6px 10px; }
    }
    @media (max-width: 640px) { .vc-orb { width: 108px; height: 108px; } .vc-msg { max-width: 86%; font-size: 15px; } .vc-start h2 { font-size: 24px; } .vc-panel { border-radius: 20px; } }
    @media (max-height: 760px) { .vc-orb { width: 96px; height: 96px; } }
    @media (prefers-reduced-motion: reduce) { .vc-thinking .vc-orb, .vc-speaking .vc-orb, .vc-speaking .vc-orb::after, .vc-row, .vc-quiz, .vc-dots i, .vc-caret::after { animation: none; } }
</style>

<div class="vc-overlay" id="vc-overlay" role="dialog" aria-modal="true" aria-label="Trò chuyện bằng giọng nói với Trợ lý AI" aria-hidden="true">
    <div class="vc-head">
        <span><span class="vc-title">🎙️ Trò chuyện với Trợ lý AI</span><span class="vc-score" id="vc-score"></span></span>
        <button type="button" class="vc-close" onclick="closeVoiceChat()">✕ Kết thúc</button>
    </div>

    {{-- Màn hình bắt đầu: cần một cú bấm của em để trình duyệt cho phép dùng micro và phát tiếng --}}
    <div class="vc-start show" id="vc-start">
        <div class="vc-bot">🤖</div>
        <h2>Nói chuyện cùng Trợ lý AI nhé!</h2>
        <p>Em hỏi về điểm số, bài đã làm, số sao, câu hay sai, hoặc nói "cô ra câu hỏi cho em" để làm câu hỏi ngay tại đây. Em có thể nói chen vào để ngắt lời bất cứ lúc nào.</p>
        <p class="vc-note">🔒 Giọng nói của em chỉ dùng để trò chuyện, MOS không lưu lại âm thanh. Em nên đeo tai nghe để Trợ lý nghe rõ hơn nhé. Làm câu hỏi ở đây chỉ để luyện tập, không tính sao.</p>
        <button type="button" class="vc-go" onclick="startVoiceChat()">🎙️ Bắt đầu trò chuyện</button>
        <p class="vc-note" id="vc-support-note" style="display:none;"></p>
    </div>

    {{-- Màn hình trò chuyện --}}
    <div class="vc-talk" id="vc-talk">
        <div class="vc-stage">
            <button type="button" class="vc-orb" id="vc-orb" onclick="voiceChatTapOrb()" aria-label="Chạm để ngắt lời Trợ lý"></button>
            <div class="vc-state" id="vc-state">Đang chuẩn bị...</div>
            <div class="vc-hint" id="vc-hint"></div>
        </div>
        <div class="vc-quizpane" id="vc-quizpane"></div>
        <div class="vc-panel">
            <div class="vc-log" id="vc-log" aria-live="polite"></div>
        </div>
        <div class="vc-bar">
            <button type="button" class="vc-btn" id="vc-mute" onclick="voiceChatToggleMute()">🎙️ Tắt mic</button>
            <button type="button" class="vc-btn" onclick="voiceChatTapOrb()">✋ Ngắt lời</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var overlay = document.getElementById('vc-overlay');
        if (!overlay) { return; }

        var HAS_AI = @json($vcHasAi);
        var GREETING = @json($vcGreeting);
        var URLS = {
            reply: @json(route('voice.reply')),
            stt: @json(route('voice.transcribe')),
            quizStart: @json(route('voice.quiz.start')),
            quizAnswer: @json(route('voice.quiz.answer')),
            quizCancel: @json(route('voice.quiz.cancel'))
        };
        var CSRF = @json(csrf_token());
        var SR = window.SpeechRecognition || window.webkitSpeechRecognition;

        var active = false, muted = false, state = 'idle';
        var stream = null, audioCtx = null, analyser = null, vadTimer = null, buf = null;
        var rec = null, recFinal = '', recErr = '', recEndTimer = null;
        var useServerStt = !SR;
        var history = [], fetchCtl = null;
        var speakGen = 0, voice = null;
        var noise = 0.01, calib = 0, thr = 0.03, loudFrames = 0, canBarge = false;
        var mr = null, mrChunks = [], speechFrames = 0, silenceMs = 0, mrStart = 0;
        var typing = [];
        // Trạng thái làm câu hỏi: pending = đang chờ em nói đáp án; awaitNext = chờ em nói có làm tiếp không
        var quiz = { on: false, pending: false, awaitNext: false, hint: '', right: 0, total: 0, card: null, spoken: '' };

        function $(id) { return document.getElementById(id); }
        function str(v) { return typeof v === 'string' ? v : ''; }
        function norm(s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd').replace(/[^a-z0-9 ]+/g, ' ').replace(/\s+/g, ' ').trim(); }
        function postJson(url, body, signal) {
            return fetch(url, {
                method: 'POST', signal: signal,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(body || {})
            }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, d: d }; }); });
        }

        // Báo máy chủ quên câu đang chờ để AI không nhắc lại một câu hỏi mà em không còn thấy trên màn hình
        function cancelQuizOnServer() {
            try {
                fetch(URLS.quizCancel, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: '{}' }).catch(function () {});
            } catch (e) {}
        }

        // ----- Giao diện -----
        var LABELS = { listening: 'Cô đang nghe em nói...', thinking: 'Cô đang suy nghĩ...', speaking: 'Cô đang nói...', idle: '...', muted: 'Mic đang tắt' };
        function setState(s) {
            state = s;
            overlay.classList.remove('vc-listening', 'vc-thinking', 'vc-speaking', 'vc-muted');
            if (muted && s !== 'speaking' && s !== 'thinking') { overlay.classList.add('vc-muted'); $('vc-state').textContent = LABELS.muted; }
            else { if (s !== 'idle') { overlay.classList.add('vc-' + s); } $('vc-state').textContent = LABELS[s] || ''; }
            $('vc-hint').textContent = s === 'speaking' ? 'Chạm vào quả cầu hoặc nói chen vào để ngắt lời'
                : (s === 'listening' && quiz.pending ? 'Em nói đáp án nhé, ví dụ "đáp án B". Nói "đọc lại" để nghe lại câu hỏi.' : '');
            if (s !== 'listening') { $('vc-orb').style.setProperty('--lvl', 0); }
        }
        function scrollLog() { var log = $('vc-log'); log.scrollTop = log.scrollHeight; }
        function appendRow(row) {
            var log = $('vc-log'), live = $('vc-live');
            if (live) { log.insertBefore(row, live); } else { log.appendChild(row); }
            scrollLog();
        }
        function makeRow(kind, avatar) {
            var row = document.createElement('div');
            row.className = 'vc-row ' + kind;
            if (avatar) { var a = document.createElement('span'); a.className = 'vc-ava'; a.textContent = avatar; row.appendChild(a); }
            var bubble = document.createElement('div');
            bubble.className = 'vc-msg';
            row.appendChild(bubble);
            return { row: row, bubble: bubble };
        }
        function addUser(text) { var m = makeRow('vc-user', '😊'); m.bubble.textContent = text; appendRow(m.row); }
        function addSys(text) { var m = makeRow('vc-sys', ''); m.bubble.textContent = text; appendRow(m.row); }
        // Lời AI: hiện chữ chạy dần như đang gõ, đồng bộ gần với tiếng đọc
        function addAi(text, display) {
            var m = makeRow('vc-ai', '🤖');
            appendRow(m.row);
            var d = str(display);
            // Có đoạn/gạch đầu dòng/in đậm thì hiện từng khối cho dễ đọc; câu ngắn thì chữ chạy như đang gõ
            if (d && /\n|\*\*|^- /m.test(d)) { renderRich(m.bubble, d); } else { typeText(m.bubble, str(text)); }
        }
        // Thẻ thống kê: ô số liệu + biểu đồ cột + chủ đề cần ôn (số liệu lấy từ database, vẽ bằng DOM, không dùng thư viện)
        function addCard(card) {
            if (!card || card.type !== 'stats') { return; }
            var el = document.createElement('div'); el.className = 'vc-statcard';
            var head = document.createElement('div'); head.className = 'vc-stat-head'; head.textContent = '📊 ' + str(card.title); el.appendChild(head);
            var nf = function (n) { try { return Number(n).toLocaleString('vi-VN'); } catch (e) { return String(n); } };
            if ((card.tiles || []).length) {
                var grid = document.createElement('div'); grid.className = 'vc-tiles';
                card.tiles.forEach(function (t) {
                    var d = document.createElement('div'); d.className = 'vc-tile';
                    var i = document.createElement('i'); i.textContent = str(t.icon);
                    var b = document.createElement('b'); b.textContent = nf(t.value);
                    var s = document.createElement('span'); s.textContent = str(t.label);
                    d.appendChild(i); d.appendChild(b); d.appendChild(s); grid.appendChild(d);
                });
                el.appendChild(grid);
            }
            var fills = [];
            if ((card.bars || []).length) {
                var sec = document.createElement('div'); sec.className = 'vc-sec'; sec.textContent = str(card.bars_title); el.appendChild(sec);
                var chart = document.createElement('div'); chart.className = 'vc-chart';
                var max = Number(card.max) || 1000;
                var pl = document.createElement('div'); pl.className = 'vc-passline'; pl.style.bottom = Math.min(100, (Number(card.pass) / max) * 100) + '%'; chart.appendChild(pl);
                card.bars.forEach(function (bar) {
                    var col = document.createElement('div'); col.className = 'vc-bar-col'; col.title = str(bar.name);
                    var v = document.createElement('div'); v.className = 'vc-bar-val'; v.textContent = nf(bar.value);
                    var f = document.createElement('div'); f.className = 'vc-bar-fill' + (bar.pass ? ' pass' : '');
                    fills.push({ el: f, h: Math.max(3, Math.min(100, (Number(bar.value) / max) * 100)) });
                    col.appendChild(v); col.appendChild(f); chart.appendChild(col);
                });
                el.appendChild(chart);
                var xl = document.createElement('div'); xl.className = 'vc-xlabels';
                card.bars.forEach(function (bar) { var s = document.createElement('span'); s.textContent = str(bar.label); xl.appendChild(s); });
                el.appendChild(xl);
            }
            var weakFills = [];
            if ((card.weak || []).length) {
                var sec2 = document.createElement('div'); sec2.className = 'vc-sec'; sec2.textContent = str(card.weak_title); el.appendChild(sec2);
                var wk = document.createElement('div'); wk.className = 'vc-weak';
                var wmax = Math.max.apply(null, card.weak.map(function (w) { return Number(w.value) || 0; }).concat([1]));
                card.weak.forEach(function (w) {
                    var row = document.createElement('div'); row.className = 'vc-weak-row';
                    var l = document.createElement('span'); l.textContent = str(w.label);
                    var tr = document.createElement('div'); tr.className = 'vc-weak-track'; var fl = document.createElement('div'); fl.className = 'vc-weak-fill'; tr.appendChild(fl);
                    var n = document.createElement('b'); n.textContent = nf(w.value);
                    weakFills.push({ el: fl, w: ((Number(w.value) || 0) / wmax) * 100 });
                    row.appendChild(l); row.appendChild(tr); row.appendChild(n); wk.appendChild(row);
                });
                el.appendChild(wk);
            }
            appendRow(el);
            // Cột và thanh mọc lên cho dễ nhìn
            setTimeout(function () {
                fills.forEach(function (x) { x.el.style.height = x.h + '%'; });
                weakFills.forEach(function (x) { x.el.style.width = x.w + '%'; });
                scrollLog();
            }, 80);
        }
        // Trình bày đơn giản và an toàn (không dùng innerHTML): đoạn văn, gạch đầu dòng "- ", in đậm **...**
        function renderRich(el, md) {
            el.classList.add('vc-rich');
            var idx = 0, ul = null;
            function inline(node, t) {
                t.split(/\*\*(.+?)\*\*/g).forEach(function (part, k) {
                    if (!part) { return; }
                    if (k % 2) { var b = document.createElement('strong'); b.textContent = part; node.appendChild(b); }
                    else { node.appendChild(document.createTextNode(part)); }
                });
            }
            function reveal(node) { node.classList.add('vc-blk'); node.style.animationDelay = (idx * 130) + 'ms'; idx++; el.appendChild(node); }
            md.split(/\n/).forEach(function (line) {
                var t = line.trim();
                if (!t) { ul = null; return; }
                var m = t.match(/^[-•]\s+(.*)$/);
                if (m) {
                    if (!ul) { ul = document.createElement('ul'); reveal(ul); }
                    var li = document.createElement('li'); inline(li, m[1]); ul.appendChild(li);
                } else {
                    ul = null;
                    var p = document.createElement('p'); inline(p, t); reveal(p);
                }
            });
            scrollLog();
        }
        function typeText(el, text) {
            var i = 0, obj = { el: el, text: text, timer: null };
            el.classList.add('vc-caret');
            obj.timer = setInterval(function () {
                i += 1;
                el.textContent = text.slice(0, i);
                scrollLog();
                if (i >= text.length) { finishTyping(obj); }
            }, 52);
            typing.push(obj);
        }
        function finishTyping(obj) {
            clearInterval(obj.timer);
            obj.el.textContent = obj.text;
            obj.el.classList.remove('vc-caret');
            typing = typing.filter(function (t) { return t !== obj; });
        }
        function flushTyping() { typing.slice().forEach(finishTyping); scrollLog(); }
        function showThinking() {
            hideThinking();
            var m = makeRow('vc-ai', '🤖');
            m.row.id = 'vc-thinking-row';
            m.bubble.innerHTML = '<span class="vc-dots"><i></i><i></i><i></i></span>';
            appendRow(m.row);
        }
        function hideThinking() { var t = $('vc-thinking-row'); if (t) { t.remove(); } }
        function showLive(t) {
            var live = $('vc-live');
            if (!live) { live = document.createElement('div'); live.id = 'vc-live'; live.className = 'vc-live'; $('vc-log').appendChild(live); }
            live.textContent = t || '';
            scrollLog();
        }
        function updateScore() {
            var s = $('vc-score');
            if (quiz.total > 0) { s.textContent = '⭐ ' + quiz.right + '/' + quiz.total + ' câu đúng'; s.classList.add('show'); }
            else { s.classList.remove('show'); }
        }

        // ----- Mở / đóng -----
        window.openVoiceChat = function () {
            if (!HAS_AI) { if (window.openAiUpgrade) { window.openAiUpgrade(); } return; }
            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
            $('vc-start').classList.add('show');
            $('vc-talk').classList.remove('show');
            var note = $('vc-support-note');
            if (!SR) { note.style.display = 'block'; note.textContent = 'Trình duyệt này chưa tự nhận dạng giọng nói, Trợ lý sẽ nghe bằng cách khác nên có thể chậm hơn một chút. Dùng Chrome hoặc Edge sẽ nhanh nhất.'; }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !window.speechSynthesis) {
                note.style.display = 'block';
                note.textContent = 'Trình duyệt này chưa hỗ trợ trò chuyện bằng giọng nói. Em hãy dùng Chrome hoặc Edge nhé.';
            }
        };
        window.closeVoiceChat = function () {
            active = false;
            closeActionTab();
            cancelQuizOnServer();
            stopAll();
            overlay.classList.remove('open');
            overlay.setAttribute('aria-hidden', 'true');
            $('vc-log').innerHTML = '';
            showQuizPane(false);
            history = [];
            muted = false;
            quiz = { on: false, pending: false, awaitNext: false, hint: '', right: 0, total: 0, card: null, spoken: '' };
            updateScore();
            $('vc-mute').textContent = '🎙️ Tắt mic';
            $('vc-mute').classList.remove('is-off');
        };
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay.classList.contains('open')) { window.closeVoiceChat(); } });

        function stopAll() {
            speakGen++;
            flushTyping();
            try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch (e) {}
            if (fetchCtl) { try { fetchCtl.abort(); } catch (e) {} fetchCtl = null; }
            if (recEndTimer) { clearTimeout(recEndTimer); recEndTimer = null; }
            if (rec) { try { rec.onend = null; rec.abort(); } catch (e) {} rec = null; }
            if (mr && mr.state !== 'inactive') { try { mr.onstop = null; mr.stop(); } catch (e) {} }
            mr = null; mrChunks = [];
            if (vadTimer) { clearInterval(vadTimer); vadTimer = null; }
            if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
            if (audioCtx) { try { audioCtx.close(); } catch (e) {} audioCtx = null; }
            analyser = null;
            setState('idle');
        }

        // ----- Bắt đầu: cần cú bấm của người dùng -----
        window.startVoiceChat = function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !window.speechSynthesis) { return; }
            active = true;
            closeActionTab();
            actionTab = prepareActionTab();
            cancelQuizOnServer();
            $('vc-start').classList.remove('show');
            $('vc-talk').classList.add('show');
            setState('idle');
            $('vc-state').textContent = 'Đang mở micro...';
            pickVoice();

            navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true } })
                .then(function (s) {
                    if (!active) { s.getTracks().forEach(function (t) { t.stop(); }); return; }
                    stream = s;
                    try { canBarge = !!(s.getAudioTracks()[0].getSettings().echoCancellation); } catch (e) { canBarge = false; }
                    setupAnalyser();
                    addAi(GREETING);
                    speak(GREETING, true);
                }, function () {
                    addSys('Em cần bấm "Cho phép" dùng micro trên trình duyệt để trò chuyện nhé. Sau đó bấm Kết thúc rồi mở lại.');
                    $('vc-state').textContent = 'Chưa dùng được micro';
                    active = false;
                });
        };

        // ----- Giọng đọc tiếng Việt của trình duyệt -----
        function pickVoice() {
            var list = (window.speechSynthesis.getVoices() || []).filter(function (v) { return /^vi/i.test(v.lang); });
            if (!list.length) { voice = null; return; }
            var score = function (v) { var n = (v.name || '').toLowerCase(); return (/natural|online/.test(n) ? 4 : 0) + (/hoaimy|namminh|an\b/.test(n) ? 2 : 0) + (/google/.test(n) ? 1 : 0); };
            list.sort(function (a, b) { return score(b) - score(a); });
            voice = list[0];
        }
        if (window.speechSynthesis) { window.speechSynthesis.onvoiceschanged = pickVoice; }

        function splitSentences(text) {
            // Tách theo dấu kết câu (không dùng lookbehind để chạy được trên Safari đời cũ)
            var parts = text.replace(/\s+/g, ' ').trim().match(/[^.!?…]+[.!?…]*\s*/g) || [];
            return parts.filter(function (p) { return p.trim().length > 0; });
        }
        // Đọc từng câu một: câu đầu phát ngay, không phải đợi cả đoạn
        function speak(text, thenListen, onDone) {
            var gen = ++speakGen;
            try { window.speechSynthesis.cancel(); } catch (e) {}
            var sentences = splitSentences(str(text));
            if (!sentences.length) { flushTyping(); if (thenListen) { beginListening(); } return; }
            setState('speaking');
            loudFrames = 0;
            var i = 0;
            (function next() {
                if (gen !== speakGen || !active) { return; }
                if (i >= sentences.length) { flushTyping(); if (onDone) { onDone(); return; } if (thenListen !== false) { beginListening(); } return; }
                var u = new SpeechSynthesisUtterance(sentences[i++]);
                u.lang = 'vi-VN';
                if (voice) { u.voice = voice; }
                u.rate = 1.03; u.pitch = 1.08;
                u.onend = function () { next(); };
                u.onerror = function () { if (gen === speakGen) { next(); } };
                try { window.speechSynthesis.speak(u); } catch (e) { next(); }
            })();
        }
        function interruptSpeaking() {
            if (state !== 'speaking') { return; }
            speakGen++;
            flushTyping();
            try { window.speechSynthesis.cancel(); } catch (e) {}
            beginListening();
        }

        // ----- Nghe -----
        function beginListening() {
            if (!active) { return; }
            if (muted) { setState('listening'); return; }
            setState('listening');
            showLive('');
            if (useServerStt) { startServerListening(); } else { startRecognition(); }
        }
        // Em ngừng thở một nhịp chưa chắc là nói xong. Chờ thêm; nếu câu còn dang dở ("...là", "...và", "...của") thì chờ lâu hơn.
        var HANGING = /(^|\s)(la|thi|va|cua|voi|ma|hay|hoac|nhung|de|vi|nen|neu|khi|rang|cho|o|trong|ve|nhu|cac|nhung|mot|cai|con|rat|duoc|bi|se|dang|da)$/;
        function pauseBeforeSend(text) {
            var t = String(text || '').trim();
            var n = norm(t);
            if (/[,;:…-]$/.test(t) || HANGING.test(n)) { return 2600; }
            if (/[.?!]$/.test(t)) { return 1100; }
            return n.split(' ').length <= 3 ? 1800 : 1500;
        }
        function startRecognition() {
            if (rec) { return; }
            recFinal = ''; recErr = '';
            var r = new SR();
            r.lang = 'vi-VN'; r.interimResults = true; r.continuous = true; r.maxAlternatives = 1;
            r.onresult = function (e) {
                // Ghép toàn bộ những gì em đã nói từ đầu lượt (kể cả phần đang nói dở)
                var all = '';
                for (var i = 0; i < e.results.length; i++) { all += (all ? ' ' : '') + e.results[i][0].transcript; }
                recFinal = all.trim();
                showLive(recFinal);
                // Mỗi lần nghe thêm chữ mới thì hẹn lại giờ "em nói xong"; hết hẹn mới gửi cả câu
                if (recEndTimer) { clearTimeout(recEndTimer); }
                recEndTimer = setTimeout(function () { if (rec) { try { rec.stop(); } catch (er) {} } }, pauseBeforeSend(recFinal));
            };
            r.onerror = function (e) {
                recErr = e.error || '';
                if (recErr === 'not-allowed' || recErr === 'service-not-allowed') { fail('Em cần cho phép dùng micro để trò chuyện nhé.'); }
                else if (recErr === 'network') { useServerStt = true; }
            };
            r.onend = function () {
                rec = null;
                if (recEndTimer) { clearTimeout(recEndTimer); recEndTimer = null; }
                if (!active || state !== 'listening') { return; }
                var t = recFinal.trim();
                showLive('');
                if (t) { handleUserText(t); }
                else { setTimeout(beginListening, 250); }
            };
            rec = r;
            try { r.start(); } catch (e) { rec = null; setTimeout(beginListening, 400); }
        }
        function fail(msg) {
            addSys(msg);
            active = false;
            stopAll();
            $('vc-state').textContent = 'Đã dừng';
        }

        // ----- Phân tích âm lượng: hiệu ứng quả cầu, ngắt lời khi nói chen, và nghe dự phòng bằng máy chủ -----
        function setupAnalyser() {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (!AC || !stream) { return; }
            audioCtx = new AC();
            var src = audioCtx.createMediaStreamSource(stream);
            analyser = audioCtx.createAnalyser();
            analyser.fftSize = 1024;
            src.connect(analyser);
            buf = new Float32Array(analyser.fftSize);
            calib = 0; noise = 0.01;
            vadTimer = setInterval(vadTick, 60);
        }
        function currentRms() {
            analyser.getFloatTimeDomainData(buf);
            var sum = 0;
            for (var i = 0; i < buf.length; i++) { sum += buf[i] * buf[i]; }
            return Math.sqrt(sum / buf.length);
        }
        function vadTick() {
            if (!analyser || !active) { return; }
            var rms = currentRms();
            if (calib < 10) { noise = Math.max(noise, rms); calib++; thr = Math.max(0.03, noise * 3); return; }

            if (state === 'listening') {
                var lvl = Math.min(1, rms * 9);
                $('vc-orb').style.setProperty('--lvl', lvl.toFixed(2));
                if (useServerStt && !muted) { serverVad(rms); }
            } else if (state === 'speaking' && canBarge && !muted) {
                // Nói chen vào khi AI đang nói: đủ to trong khoảng 0,3 giây thì ngắt lời
                loudFrames = rms > Math.max(0.09, thr * 2.5) ? loudFrames + 1 : 0;
                if (loudFrames >= 5) { loudFrames = 0; interruptSpeaking(); }
            }
        }

        // ----- Nghe dự phòng: ghi âm một câu nói, gửi lên máy chủ chép thành chữ -----
        function startServerListening() { speechFrames = 0; silenceMs = 0; }
        function serverVad(rms) {
            var speaking = rms > thr;
            if (!mr || mr.state === 'inactive') {
                speechFrames = speaking ? speechFrames + 1 : 0;
                if (speechFrames >= 3 && stream) {
                    mrChunks = [];
                    try { mr = new MediaRecorder(stream); } catch (e) { fail('Trình duyệt này chưa ghi âm được. Em hãy dùng Chrome hoặc Edge nhé.'); return; }
                    mr.ondataavailable = function (ev) { if (ev.data && ev.data.size) { mrChunks.push(ev.data); } };
                    mr.onstop = onUtteranceEnd;
                    mrStart = Date.now(); silenceMs = 0;
                    mr.start();
                    showLive('(đang nghe...)');
                }
            } else {
                silenceMs = speaking ? 0 : silenceMs + 60;
                if (silenceMs >= 1700 || Date.now() - mrStart > 25000) { try { mr.stop(); } catch (e) {} }
            }
        }
        function onUtteranceEnd() {
            var chunks = mrChunks; mrChunks = [];
            var long = Date.now() - mrStart - silenceMs;
            mr = null;
            showLive('');
            if (!active || state !== 'listening') { return; }
            if (long < 450 || !chunks.length) { beginListening(); return; }
            setState('thinking');
            toWav16k(new Blob(chunks)).then(function (wav) {
                var fd = new FormData();
                fd.append('audio', wav, 'nghe.wav');
                return fetch(URLS.stt, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
            }).then(function (r) { return r.json(); }).then(function (d) {
                if (!active) { return; }
                if (d.ok && d.text) { handleUserText(d.text); } else { beginListening(); }
            }).catch(function () { if (active) { beginListening(); } });
        }
        function toWav16k(blob) {
            return blob.arrayBuffer().then(function (ab) {
                var AC = window.AudioContext || window.webkitAudioContext;
                var ctx = new AC();
                return ctx.decodeAudioData(ab).then(function (decoded) {
                    ctx.close();
                    var off = new OfflineAudioContext(1, Math.max(1, Math.ceil(decoded.duration * 16000)), 16000);
                    var s = off.createBufferSource();
                    s.buffer = decoded; s.connect(off.destination); s.start();
                    return off.startRendering();
                });
            }).then(function (rendered) {
                var data = rendered.getChannelData(0);
                var view = new DataView(new ArrayBuffer(44 + data.length * 2));
                var w = function (o, s) { for (var i = 0; i < s.length; i++) { view.setUint8(o + i, s.charCodeAt(i)); } };
                w(0, 'RIFF'); view.setUint32(4, 36 + data.length * 2, true); w(8, 'WAVE'); w(12, 'fmt ');
                view.setUint32(16, 16, true); view.setUint16(20, 1, true); view.setUint16(22, 1, true);
                view.setUint32(24, 16000, true); view.setUint32(28, 32000, true); view.setUint16(32, 2, true); view.setUint16(34, 16, true);
                w(36, 'data'); view.setUint32(40, data.length * 2, true);
                for (var i = 0; i < data.length; i++) {
                    var x = Math.max(-1, Math.min(1, data[i]));
                    view.setInt16(44 + i * 2, x < 0 ? x * 0x8000 : x * 0x7FFF, true);
                }
                return new Blob([view], { type: 'audio/wav' });
            });
        }

        // ----- Điều phối lời em nói: trò chuyện thường hay đang làm câu hỏi -----
        // Mọi câu em nói đều gửi cho AI phân tích (chọn đáp án, đọc lại, đổi câu, dừng, hỏi gợi ý, làm tiếp...).
        // AI trả về dấu hiệu, máy chủ kiểm tra rồi trình duyệt làm theo. Không dò từ khóa ở đây.
        function handleUserText(text) {
            addUser(text);
            chat(text);
        }

        // ----- Trò chuyện thường: gửi chữ cho Trợ lý AI nghĩ -----
        function chat(text) {
            setState('thinking');
            showThinking();
            fetchCtl = new AbortController();
            postJson(URLS.reply, { text: text, history: history.slice(-16) }, fetchCtl.signal)
                .then(function (res) {
                    fetchCtl = null; hideThinking();
                    if (!active) { return; }
                    var reply = str(res.d && res.d.text);
                    if (res.ok && res.d.ok && reply) {
                        // AI hiểu em đã chọn đáp án: máy chủ chấm xong, hiện kết quả trên thẻ câu hỏi
                        if (res.d.graded) { showGraded(res.d.graded, text); return; }
                        history.push({ role: 'user', text: text });
                        history.push({ role: 'assistant', text: reply });
                        if (res.d.control && runQuizControl(res.d.control, reply, res.d.display)) { return; }
                        addAi(reply, res.d.display);
                        if (res.d.card) { addCard(res.d.card); }
                        if (res.d.action && res.d.action.url) {
                            addAction(res.d.action);
                            if (res.d.action.auto) { openInNewTab(res.d.action.url); }
                        }
                        if (res.d.quiz && res.d.quiz.question) {
                            // AI muốn cho em làm câu hỏi: hiện thẻ câu hỏi rồi đọc lời dẫn + câu hỏi
                            beginQuiz(res.d.quiz, '');
                            speak(reply + ' ' + str(res.d.quiz.spoken), true);
                        } else {
                            speak(reply, true);
                        }
                    } else {
                        sayError(str(res.d && res.d.message) || 'Cô chưa nghe rõ, em nói lại giúp cô nhé!');
                    }
                })
                .catch(function (e) {
                    hideThinking();
                    if (e && e.name === 'AbortError') { return; }
                    fetchCtl = null;
                    if (active) { sayError('Mạng đang chập chờn. Em nói lại giúp cô nhé!'); }
                });
        }
        // Lệnh điều khiển câu hỏi do AI hiểu từ lời em: đọc lại, đổi câu, dừng. Trả về true nếu đã xử lý xong lượt này.
        function runQuizControl(control, reply, display) {
            if (control === 'doc_lai' && quiz.pending) {
                if (reply) { addAi(reply, display); }
                speak((reply ? reply + ' ' : '') + str(quiz.spoken), true);
                return true;
            }
            if (control === 'doi_cau' && quiz.on) {
                requestQuestion(quiz.hint, reply || 'Được rồi, mình đổi câu khác nhé.');
                return true;
            }
            if (control === 'dung' && quiz.on) {
                exitQuiz();
                return true;
            }
            return false;
        }
        // Hiện kết quả chấm trên thẻ câu hỏi + lời nhận xét của cô
        function showGraded(d, text) {
            finishCard(d);
            quiz.total += 1; if (d.correct) { quiz.right += 1; }
            updateScore();
            quiz.pending = false; quiz.awaitNext = true;
            rememberQuiz(d, text);
            addAi(d.feedback, d.feedback_display);
            speak(d.spoken, true);
        }
        function addAction(action) {
            var a = document.createElement('a');
            a.className = 'vc-action';
            a.href = action.url;
            a.target = '_blank';
            a.rel = 'noopener';
            a.textContent = '➡️ Mở: ' + str(action.label) + ' (tab mới)';
            appendRow(a);
        }
        // Tab mới dành riêng cho trợ lý giọng nói: mở sẵn lúc bấm "Bắt đầu" (cú bấm của người dùng nên không bị chặn),
        // rồi dùng lại khi em cần chuyển trang. Cuối phiên, tab chưa dùng sẽ được đóng.
        var actionTab = null;
        var actionTabUsed = false;
        function prepareActionTab() {
            actionTabUsed = false;
            try {
                var t = window.open('about:blank', '_blank');
                if (t) { try { t.opener = null; } catch (e) {} }
                return t;
            } catch (e) { return null; }
        }
        function closeActionTab() {
            if (actionTab && !actionTab.closed && !actionTabUsed) { try { actionTab.close(); } catch (e) {} }
            actionTab = null;
            actionTabUsed = false;
        }
        // Em yêu cầu chuyển trang: mở trong tab riêng để cuộc trò chuyện vẫn còn nguyên.
        function openInNewTab(url) {
            var w = null;
            if (actionTab && !actionTab.closed) {
                try { actionTab.location.href = url; w = actionTab; actionTabUsed = true; } catch (e) { w = null; }
            }
            if (!w) {
                try { w = window.open(url, '_blank'); } catch (e) { w = null; }
            }
            if (w) { try { w.opener = null; } catch (e) {} }
            else { addSys('Trình duyệt đang chặn tab mới. Em bấm nút "Mở" ở trên nhé.'); }
        }
        function sayError(msg) { addSys(msg); speak(msg, true); }
        // Gợi ý chủ đề để "làm tiếp câu nữa" cùng chủ đề: lấy theo thẻ câu hỏi đang hiện

        // ----- Làm câu hỏi -----
        function beginQuiz(payload, hint) {
            quiz.on = true; quiz.pending = true; quiz.awaitNext = false;
            quiz.hint = payload.hint || (payload.source === 'cau_sai' ? 'cau sai' : (payload.question.topic || hint || ''));
            quiz.spoken = str(payload.spoken);
            quiz.q = payload.question;
            renderQuizCard(payload.question, payload.source);
        }
        function requestQuestion(hint, intro) {
            setState('thinking');
            showThinking();
            postJson(URLS.quizStart, { hint: hint || '' }).then(function (res) {
                hideThinking();
                if (!active) { return; }
                if (res.ok && res.d.ok && res.d.quiz) {
                    beginQuiz(res.d.quiz, hint);
                    if (intro) { addAi(intro); }
                    speak((intro ? intro + ' ' : '') + str(res.d.quiz.spoken), true);
                } else {
                    quiz.on = false; quiz.pending = false; quiz.awaitNext = false;
                    showQuizPane(false);
                    sayError(str(res.d && res.d.message) || 'Cô chưa tìm được câu hỏi phù hợp. Em thử chủ đề khác nhé!');
                }
            }).catch(function () { hideThinking(); if (active) { sayError('Mạng đang chập chờn. Em nói lại giúp cô nhé!'); } });
        }
        function rememberQuiz(d, answerText) {
            var q = quiz.q || {};
            history.push({ role: 'assistant', text: ('Cô ra câu hỏi: ' + str(q.text)).slice(0, 690) });
            history.push({ role: 'user', text: str(answerText).slice(0, 690) });
            history.push({ role: 'assistant', text: ((d.correct ? 'Em làm đúng. ' : 'Em làm chưa đúng. ') + str(d.feedback)).slice(0, 690) });
            if (history.length > 40) { history = history.slice(-40); }
        }
        function answerQuestion(text, answers, keys) {
            setState('thinking');
            showThinking();
            var body = { text: text };
            if (answers) { body.answers = answers; }
            if (keys) { body.keys = keys; }
            postJson(URLS.quizAnswer, body).then(function (res) {
                hideThinking();
                if (!active) { return; }
                var d = res.d || {};
                if (!res.ok || !d.ok) { sayError(str(d.message) || 'Cô chưa nghe rõ, em nói lại giúp cô nhé!'); return; }
                if (d.status === 'graded') {
                    showGraded(d, answers ? 'Em đã làm xong câu hỏi trên màn hình.' : text);
                } else if (d.status === 'unclear') {
                    addAi(d.spoken);
                    speak(d.spoken, true);
                } else {
                    quiz.pending = false; quiz.awaitNext = false; quiz.on = false;
                    showQuizPane(false);
                    var m = str(d.spoken) || 'Em muốn cô ra câu hỏi không?';
                    addAi(m);
                    speak(m, true);
                }
            }).catch(function () { hideThinking(); if (active) { sayError('Mạng đang chập chờn. Em nói lại giúp cô nhé!'); } });
        }
        function exitQuiz() {
            var msg = quiz.total > 0
                ? 'Giỏi lắm! Em làm đúng ' + quiz.right + ' trên ' + quiz.total + ' câu. Mình nghỉ ở đây nhé, em cần gì cứ nói với cô.'
                : 'Được rồi, mình dừng làm câu hỏi nhé. Em cần gì cứ nói với cô.';
            quiz.on = false; quiz.pending = false; quiz.awaitNext = false;
            showQuizPane(false);
            cancelQuizOnServer();
            addAi(msg);
            speak(msg, true);
        }
        function showQuizPane(on) {
            overlay.classList.toggle('vc-quizmode', !!on);
            if (!on) { $('vc-quizpane').innerHTML = ''; quiz.card = null; }
        }
        // Dừng mọi thứ đang diễn ra (đọc, nghe, chờ AI) để xử lý một cú bấm tay
        function stopEverythingForTap() {
            speakGen++;
            flushTyping();
            try { window.speechSynthesis.cancel(); } catch (e) {}
            if (fetchCtl) { try { fetchCtl.abort(); } catch (e) {} fetchCtl = null; }
            if (recEndTimer) { clearTimeout(recEndTimer); recEndTimer = null; }
            if (rec) { try { rec.onend = null; rec.abort(); } catch (e) {} rec = null; }
            if (mr && mr.state !== 'inactive') { try { mr.onstop = null; mr.stop(); } catch (e) {} }
            mr = null; mrChunks = [];
            showLive('');
            hideThinking();
        }
        function renderQuizCard(q, source) {
            var pane = $('vc-quizpane');
            pane.innerHTML = '';
            var screenType = (q.type === 'MultipleChoiceText' || q.type === 'Matching');
            var card = document.createElement('div');
            card.className = 'vc-quiz';
            var head = document.createElement('div'); head.className = 'vc-quiz-head';
            var l = document.createElement('span');
            l.textContent = (source === 'cau_sai' ? '🔁 Ôn câu từng sai' : '📝 Câu hỏi luyện tập') + (q.topic ? ' · ' + q.topic : '');
            head.appendChild(l);
            // Cô đang đọc đề: em bấm để bỏ qua phần đọc và làm luôn (không phải đợi cô đọc hết)
            var skip = document.createElement('button'); skip.type = 'button'; skip.className = 'vc-skip'; skip.textContent = '⏭️ Bỏ qua phần đọc';
            skip.addEventListener('click', function () { voiceChatTapOrb(); });
            head.appendChild(skip);
            card.appendChild(head);
            var qq = document.createElement('div'); qq.className = 'vc-quiz-q'; qq.textContent = str(q.text); card.appendChild(qq);
            (q.images || []).forEach(function (src) {
                var img = document.createElement('img'); img.className = 'vc-quiz-img'; img.alt = 'Hình của câu hỏi'; img.src = src; card.appendChild(img);
            });
            if (q.multi) { var m = document.createElement('div'); m.className = 'vc-quiz-multi'; m.textContent = 'Câu này có thể có nhiều đáp án đúng. Em bấm chọn các đáp án rồi bấm "Trả lời".'; card.appendChild(m); }

            if (screenType) {
                var rows = document.createElement('div'); rows.className = 'vc-rows';
                var isMatch = q.type === 'Matching';
                var labels = isMatch ? (q.left || []) : (q.items || []);
                labels.forEach(function (it, i) {
                    var row = document.createElement('div'); row.className = 'vc-rowitem'; row.setAttribute('data-index', i);
                    var t = document.createElement('span'); t.textContent = str(it.text); row.appendChild(t);
                    var sel = document.createElement('select');
                    var ph = document.createElement('option'); ph.value = ''; ph.textContent = '— Chọn —'; sel.appendChild(ph);
                    var choices = isMatch ? (q.right || []).map(function (r) { return { v: r.id, t: r.text }; }) : (it.choices || []).map(function (c, k) { return { v: k, t: c }; });
                    choices.forEach(function (c) { var o = document.createElement('option'); o.value = String(c.v); o.textContent = str(c.t); sel.appendChild(o); });
                    row.appendChild(sel);
                    rows.appendChild(row);
                });
                card.appendChild(rows);
            } else {
                var list = document.createElement('div'); list.className = 'vc-opts';
                (q.options || []).forEach(function (o) {
                    var btn = document.createElement('button');
                    btn.type = 'button'; btn.className = 'vc-opt'; btn.setAttribute('data-key', o.key);
                    var b = document.createElement('b'); b.textContent = o.key;
                    var t = document.createElement('span'); t.textContent = str(o.text);
                    btn.appendChild(b); btn.appendChild(t);
                    if (o.image) { var im = document.createElement('img'); im.src = o.image; im.alt = 'Hình ' + o.key; btn.appendChild(im); }
                    btn.addEventListener('click', function () { onPickOption(o.key, !!q.multi); });
                    list.appendChild(btn);
                });
                card.appendChild(list);
            }

            var tip = document.createElement('div'); tip.className = 'vc-quiz-tip';
            tip.textContent = screenType
                ? '🎙️ Em làm trên màn hình rồi bấm "Trả lời". Cần gợi ý thì cứ hỏi cô, hoặc nói "đọc lại".'
                : '🎙️ Em có thể bấm chọn đáp án, hoặc nói "đáp án B", "đọc lại", "bỏ qua".';
            card.appendChild(tip);
            var foot = document.createElement('div'); foot.className = 'vc-quiz-foot'; foot.id = 'vc-quiz-foot';
            if (q.multi || screenType) {
                var go = document.createElement('button'); go.type = 'button'; go.className = 'vc-qbtn'; go.textContent = '✅ Trả lời';
                go.addEventListener('click', function () {
                    if (screenType) {
                        var vals = Array.prototype.map.call(card.querySelectorAll('.vc-rowitem select'), function (el) { return el.value; });
                        if (vals.some(function (v) { return v === ''; })) { tip.textContent = '⚠️ Em chọn đủ tất cả các dòng rồi bấm Trả lời nhé.'; return; }
                        submitScreenAnswers(vals.map(Number));
                    } else {
                        var keys = Array.prototype.map.call(card.querySelectorAll('.vc-opt.sel'), function (el) { return el.getAttribute('data-key'); });
                        submitPicked(keys);
                    }
                });
                foot.appendChild(go);
            }
            card.appendChild(foot);
            pane.appendChild(card);
            pane.scrollTop = 0;
            quiz.card = card;
            showQuizPane(true);
            quiz.card = card;
        }
        function onPickOption(key, multi) {
            if (!quiz.card || quiz.card.classList.contains('done') || !quiz.pending) { return; }
            if (multi) {
                var el = quiz.card.querySelector('.vc-opt[data-key="' + key + '"]');
                if (el) { el.classList.toggle('sel'); }
            } else {
                submitPicked([key]);
            }
        }
        // Em bấm chọn bằng tay: làm giống như em nói "đáp án ..." (máy chủ vẫn chấm theo cơ sở dữ liệu)
        function submitPicked(keys) {
            if (!keys.length || !quiz.pending || !active) { return; }
            stopEverythingForTap();
            var text = 'Đáp án ' + keys.join(' và ');
            addUser(text);
            answerQuestion(text, null, keys);
        }
        // Câu chọn trong ô / ghép nối: gửi kết quả em làm trên màn hình
        function submitScreenAnswers(values) {
            if (!quiz.pending || !active) { return; }
            stopEverythingForTap();
            addUser('Em làm xong rồi, cô chấm giúp em nhé!');
            answerQuestion('Trả lời trên màn hình', values);
        }
        function finishCard(d) {
            var card = quiz.card;
            if (!card) { return; }
            card.classList.add('done');
            var q = quiz.q || {};
            if (card.querySelector('.vc-rows')) {
                var wrong = d.wrong_items || [], sol = d.solution || [];
                card.querySelectorAll('.vc-rowitem').forEach(function (row) {
                    var i = Number(row.getAttribute('data-index'));
                    var bad = wrong.indexOf(i) > -1;
                    var sel = row.querySelector('select');
                    var mine = sel && sel.selectedIndex > -1 ? sel.options[sel.selectedIndex].text : '';
                    var good;
                    if (q.type === 'Matching') { var r = (q.right || []).filter(function (x) { return x.id === sol[i]; })[0]; good = r ? r.text : ''; }
                    else { good = ((q.items || [])[i] || {}).choices ? q.items[i].choices[sol[i]] : ''; }
                    row.classList.add(bad ? 'bad' : 'ok');
                    // Thay ô chọn bằng kết quả cùng chiều cao: dòng không bị cao thêm nên thẻ không nhảy
                    var ans = document.createElement('div'); ans.className = 'vc-ans';
                    if (bad) {
                        var m1 = document.createElement('span'); m1.className = 'mine'; m1.textContent = str(mine);
                        ans.appendChild(m1);
                    }
                    var g1 = document.createElement('span'); g1.className = 'good'; g1.textContent = '✔ ' + str(good);
                    ans.appendChild(g1);
                    if (sel) { sel.style.display = 'none'; row.appendChild(ans); }
                });
            } else {
                var picked = d.picked_keys || [], correct = d.correct_keys || [];
                card.querySelectorAll('.vc-opt').forEach(function (el) {
                    var k = el.getAttribute('data-key');
                    el.classList.remove('sel');
                    if (picked.indexOf(k) > -1) { el.classList.add('picked'); }
                    if (correct.indexOf(k) > -1) { el.classList.add('ok'); }
                    else if (picked.indexOf(k) > -1) { el.classList.add('bad'); }
                });
            }
            // Dòng kết quả dùng đúng chỗ của dòng gợi ý (không thêm dòng mới nên chiều cao thẻ giữ nguyên)
            var tip = card.querySelector('.vc-quiz-tip');
            if (!tip) { tip = document.createElement('div'); card.querySelector('.vc-quiz-foot').insertAdjacentElement('beforebegin', tip); }
            tip.className = 'vc-quiz-tip vc-quiz-result ' + (d.correct ? 'ok' : 'bad');
            tip.textContent = d.correct ? '✅ Chính xác! Giỏi lắm!' : (d.correct_keys ? '❌ Chưa đúng. Đáp án đúng là ' + d.correct_keys.join(' và ') + '.' : '❌ Chưa đúng. Em xem các dòng màu đỏ nhé.');
            var foot = card.querySelector('.vc-quiz-foot');
            foot.innerHTML = '';
            var next = document.createElement('button'); next.type = 'button'; next.className = 'vc-qbtn'; next.textContent = '➡️ Câu tiếp theo';
            next.addEventListener('click', function () { stopEverythingForTap(); requestQuestion(quiz.hint, ''); });
            var stop = document.createElement('button'); stop.type = 'button'; stop.className = 'vc-qbtn alt'; stop.textContent = '⏹️ Dừng luyện tập';
            stop.addEventListener('click', function () { stopEverythingForTap(); exitQuiz(); });
            foot.appendChild(stop); foot.appendChild(next);
            // Nếu thẻ dài hơn khung thì cuộn xuống đáy để thấy kết quả và nút
            var pn = $('vc-quizpane'); pn.scrollTop = pn.scrollHeight;
        }

        // ----- Nút bấm -----
        window.voiceChatTapOrb = function () {
            if (state === 'speaking') { interruptSpeaking(); }
            else if (state === 'thinking' && fetchCtl) { fetchCtl.abort(); fetchCtl = null; hideThinking(); beginListening(); }
        };
        window.voiceChatToggleMute = function () {
            muted = !muted;
            var b = $('vc-mute');
            b.textContent = muted ? '🔇 Bật mic' : '🎙️ Tắt mic';
            b.classList.toggle('is-off', muted);
            if (muted) {
                if (recEndTimer) { clearTimeout(recEndTimer); recEndTimer = null; }
                if (rec) { try { rec.onend = null; rec.abort(); } catch (e) {} rec = null; }
                if (mr && mr.state !== 'inactive') { try { mr.onstop = null; mr.stop(); } catch (e) {} mr = null; }
                showLive('');
                if (state === 'listening') { setState('listening'); }
            } else if (state === 'listening') {
                beginListening();
            }
        };
    })();
</script>
@endif
