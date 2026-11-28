<script>
/* Posisikan area konten pas di samping sidebar dan di bawah header */
(function () {
    var main = document.querySelector('.app-main, .content-wrapper, main');
    if (!main) return;
    var sidebar = document.querySelector('.app-sidebar, .main-sidebar, aside');
    var header = document.querySelector('.app-header, .main-header, nav.navbar, header');

    function set(el, nama, nilai) {
        el.style.setProperty(nama, nilai, 'important');
    }

    // Hilangkan jarak ganda dari area konten dan pembungkusnya
    var el = main;
    while (el && el !== document.body && !el.classList.contains('app-wrapper')) {
        set(el, 'transition', 'none');
        set(el, 'margin', '0');
        set(el, 'padding', '0');
        set(el, 'max-width', 'none');
        el = el.parentElement;
    }

    function atur() {
        set(main, 'margin-left', '0px');
        set(main, 'padding-top', '0px');
        var kotak = main.getBoundingClientRect();

        if (sidebar && window.innerWidth >= 992) {
            var kanan = sidebar.getBoundingClientRect().right;
            set(main, 'margin-left', Math.max(0, kanan - kotak.left) + 'px');
        }
        if (header) {
            var tinggi = header.getBoundingClientRect().height;
            set(main, 'padding-top', Math.max(0, tinggi - (kotak.top + window.scrollY)) + 'px');
        }
    }

    atur();
    window.addEventListener('load', atur);
    window.addEventListener('resize', atur);
    // Saat sidebar dibuka atau ditutup
    document.addEventListener('click', function () { setTimeout(atur, 400); });
    if (sidebar) sidebar.addEventListener('transitionend', atur);
})();
</script>