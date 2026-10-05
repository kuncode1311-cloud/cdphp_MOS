{{-- Chặn trình duyệt tự điền tài khoản đã lưu (vd: "admin") vào các ô tìm kiếm làm lọc sai dữ liệu --}}
<script>
    (function () {
        const selector = 'input.search-input, input[id$="search-input"], input[id$="SearchInput"], input[id="ts-modal-search"], input[id="filter-keyword"]';
        const clearAutofill = () => {
            document.querySelectorAll(selector).forEach(input => {
                if (input.dataset.userTyped === '1' || document.activeElement === input || input.value === '') return;
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        };
        document.addEventListener('keydown', e => { if (e.target && e.target.matches && e.target.matches(selector)) e.target.dataset.userTyped = '1'; }, true);
        document.addEventListener('paste', e => { if (e.target && e.target.matches && e.target.matches(selector)) e.target.dataset.userTyped = '1'; }, true);
        window.addEventListener('pageshow', clearAutofill);
        [300, 900, 2000].forEach(ms => setTimeout(clearAutofill, ms));
    })();
</script>
