// Bật/tắt menu; dấu ?. giúp trang không có nút này vẫn chạy được.
document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => document.querySelector('[data-sidebar]')?.classList.toggle('open'));
// Nhớ giao diện sáng/tối trên trình duyệt này cho lần mở sau.
if (localStorage.getItem('ic3-theme') === 'dark') document.body.classList.add('dark');
document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    localStorage.setItem('ic3-theme', document.body.classList.contains('dark') ? 'dark' : 'light');
});


// Popup bắt đầu bài: thêm class open để hiện, bỏ class để đóng.
const modal = document.querySelector('[data-test-modal]');
const close = () => {
    modal?.classList.remove('open');
    modal?.setAttribute('aria-hidden', 'true');
};
document.querySelector('[data-start-test]')?.addEventListener('click', () => {
    modal?.classList.add('open');
    modal?.setAttribute('aria-hidden', 'false');
});
document.querySelectorAll('[data-modal-close]').forEach(b => b.addEventListener('click', close));
document.querySelector('[data-demo-confirm]')?.addEventListener('click', e => {
    e.currentTarget.textContent = 'Đã sẵn sàng ✓';
    setTimeout(close, 800);
});

// Mở và đóng bảng hướng dẫn nhận sao.
const starModal = document.getElementById('star-guide-modal');
const openStarModal = () => {
    starModal?.classList.add('open');
    starModal?.setAttribute('aria-hidden', 'false');
};
const closeStarModal = () => {
    starModal?.classList.remove('open');
    starModal?.setAttribute('aria-hidden', 'true');
};
document.querySelectorAll('[data-open-star-modal]').forEach(b => b.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    openStarModal();
}));
document.querySelectorAll('[data-close-star-modal]').forEach(b => b.addEventListener('click', closeStarModal));
starModal?.addEventListener('click', (e) => {
    if (e.target === starModal) closeStarModal();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        close();
        closeStarModal();
    }
});

// Vẽ các ngôi sao nhỏ chạy theo chuột bằng canvas.
const canvas = document.getElementById('magic-canvas');
if (canvas) {
    const ctx = canvas.getContext('2d');
    const colors = ['#ffe135', '#ff9f1a', '#00f2fe', '#4facfe', '#2ed573', '#ff4757', '#ffffff'];
    let sparkles = [];
    let isRunning = false;

    const resize = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    };
    resize();
    window.addEventListener('resize', resize);

    window.addEventListener('mousemove', e => {
        for (let i = 0; i < 2; i++) {
            sparkles.push({
                x: e.clientX,
                y: e.clientY,
                size: Math.random() * 3 + 2,
                alpha: 1,
                vx: (Math.random() - 0.5) * 1.2,
                vy: Math.random() * 1.2 + 0.4,
                color: colors[Math.floor(Math.random() * colors.length)],
                rotation: Math.random() * Math.PI
            });
        }
        if (sparkles.length > 35) sparkles.splice(0, sparkles.length - 35);
        if (!isRunning) {
            isRunning = true;
            requestAnimationFrame(render);
        }
    });

    function drawSparkle(cx, cy, spikes, outerRadius, innerRadius, color, alpha, rotation = 0) {
        let rot = Math.PI / 2 * 3 + rotation;
        let x = cx;
        let y = cy;
        let step = Math.PI / spikes;

        ctx.save();
        ctx.globalAlpha = Math.max(0, Math.min(1, alpha));
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.moveTo(cx + Math.cos(rot) * outerRadius, cy + Math.sin(rot) * outerRadius);
        for (let i = 0; i < spikes; i++) {
            x = cx + Math.cos(rot) * outerRadius;
            y = cy + Math.sin(rot) * outerRadius;
            ctx.lineTo(x, y);
            rot += step;

            x = cx + Math.cos(rot) * innerRadius;
            y = cy + Math.sin(rot) * innerRadius;
            ctx.lineTo(x, y);
            rot += step;
        }
        ctx.closePath();
        ctx.fill();
        ctx.restore();
    }

    function render() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (let i = sparkles.length - 1; i >= 0; i--) {
            const s = sparkles[i];
            s.x += s.vx;
            s.y += s.vy;
            s.alpha -= 0.04;
            s.rotation += 0.08;

            if (s.alpha <= 0) {
                sparkles.splice(i, 1);
                continue;
            }

            drawSparkle(s.x, s.y, 4, s.size * 2, s.size * 0.6, s.color, s.alpha, s.rotation);
        }

        if (sparkles.length > 0) {
            requestAnimationFrame(render);
        } else {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            isRunning = false;
        }
    }
}

// Dấu hoàn thành lưu ở trình duyệt để hiển thị; lịch sử điểm trong database là dữ liệu riêng.
document.querySelectorAll('.test-item').forEach(item => {
    const slug = item.getAttribute('href')?.split('/').pop();
    if (slug && localStorage.getItem(`completed-${slug}`)) {
        item.classList.add('is-complete');
        const status = item.querySelector('.test-status');
        if (status) status.textContent = '✓';
    }
});
