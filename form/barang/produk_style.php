<style>
.app-main.pr-page { padding: 24px; background: #fff8fa; }

/* Header */
.pr-page .pr-hero {
    background: linear-gradient(120deg, #f7d4dc, #dceaff);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 20px;
    color: #493747;
}
.pr-page .pr-hero h1, .pr-page .pr-hero h2 { font-weight: 800; margin: 4px 0 8px; }
.pr-page .pr-hero p { margin-bottom: 0; }

/* Filter */
.pr-page form.pr-filter {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 16px;
    background: #fff;
    border-radius: 18px;
    padding: 18px 20px;
    margin: 0 0 16px;
    box-shadow: 0 4px 18px rgba(73,55,71,.08);
}
.pr-page form.pr-filter > * { flex: 1 1 220px; width: auto; max-width: none; margin: 0; padding: 0; }
.pr-page form.pr-filter label { display: block; font-weight: 700; margin: 0 0 6px; color: #493747; }
.pr-page form.pr-filter input:not([type="hidden"]):not([type="submit"]),
.pr-page form.pr-filter select {
    display: block;
    width: 100%;
    height: 44px;
    margin: 0;
    padding: 0 14px;
    border: 1px solid #e5c3cd;
    border-radius: 12px;
    background-color: #fff;
}
.pr-page form.pr-filter button {
    height: 44px;
    margin: 0 12px 0 0;
    padding: 0 24px;
    border: 0;
    border-radius: 12px;
    background: #d9779a;
    color: #fff;
    font-weight: 700;
}
.pr-page form.pr-filter a { color: #a64d70; font-weight: 600; }

/* Grid produk */
.pr-page .pr-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin-left: 0;
    margin-right: 0;
}
.pr-page .pr-grid > :not(.pr-item) { grid-column: 1 / -1; }
.pr-page .pr-grid > .pr-item {
    display: flex;
    flex-direction: column;
    width: auto !important;
    max-width: none !important;
    min-width: 0;
    margin: 0 !important;
    padding: 0;
}

/* Kartu */
.pr-page .pr-card {
    display: flex !important;
    flex-direction: column;
    flex: 1;
    width: 100% !important;
    max-width: 300px !important;
    background: #fff;
    border: 1px solid #f4dde4;
    border-radius: 18px;
    overflow: hidden;
    padding: 0 0 16px !important;
    box-shadow: 0 4px 16px rgba(73,55,71,.08);
    transition: transform .2s, box-shadow .2s;
}
.pr-page .pr-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(73,55,71,.15); }
.pr-page .pr-card > * { margin: 6px 16px 0; }
.pr-page .pr-card > img,
.pr-page .pr-card > .pr-media { margin: 0 0 6px; }
.pr-page .pr-card img {
    display: block;
    width: 100% !important;
    max-width: none !important;
    height: 190px !important;
    object-fit: cover;
    border-radius: 0 !important;
}
.pr-page .pr-card .pr-body { display: flex; flex-direction: column; flex: 1; }
.pr-page .pr-card h1, .pr-page .pr-card h2, .pr-page .pr-card h3,
.pr-page .pr-card h4, .pr-page .pr-card h5 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #493747;
    margin-top: 4px;
    margin-bottom: 4px;
}
.pr-page .pr-card p { margin-bottom: 4px; }
.pr-page .pr-card a.pr-btn {
    display: block;
    margin-top: auto;
    padding: 9px 12px;
    border-radius: 12px;
    background: #f3b6c2;
    color: #493747;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
}
.pr-page .pr-card > a.pr-btn { margin: auto 16px 0; }
.pr-page .pr-card a.pr-btn:hover { background: #d9779a; color: #fff; }
</style>

<script>
(function () {
    var main = document.querySelector('main.app-main');
    if (!main) return;
    main.classList.add('pr-page');

    function tombolDetail(el) {
        return Array.prototype.filter.call(el.querySelectorAll('a, button'), function (a) {
            return a.textContent.trim().toLowerCase() === 'lihat detail';
        });
    }

    function rapikan() {
        var judul = main.querySelector('h1') || main.querySelector('h2');
        if (judul && judul.parentElement !== main &&
            !judul.parentElement.querySelector('form, img')) {
            judul.parentElement.classList.add('pr-hero');
        }

        var input = main.querySelector('form select, form input[type="text"], form input[type="search"]');
        if (input) {
            var form = input.closest('form');
            form.classList.add('pr-filter');

            // Filter harus tetap di dashboard sales
            form.setAttribute('action', 'dashboard_admin.php');
            form.setAttribute('method', 'get');
            var menu = form.querySelector('input[name="menu"]');
            if (!menu) {
                menu = document.createElement('input');
                menu.type = 'hidden';
                menu.name = 'menu';
                form.appendChild(menu);
            }
            if (menu.value !== 'produk') menu.value = 'produk';

            Array.prototype.forEach.call(form.querySelectorAll('a'), function (a) {
                if (a.textContent.trim().toLowerCase() === 'reset') {
                    a.setAttribute('href', 'dashboard_admin.php?menu=produk');
                }
            });
        }

        tombolDetail(main).forEach(function (btn) {
            var card = btn.parentElement;
            while (card !== main && !card.querySelector('img')) card = card.parentElement;
            if (card === main || tombolDetail(card).length !== 1) return;

            var item = card;
            while (item.parentElement !== main &&
                   tombolDetail(item.parentElement).length === 1 &&
                   !item.parentElement.querySelector('h1, select')) {
                item = item.parentElement;
            }

            card.classList.add('pr-card');
            item.classList.add('pr-item');
            item.parentElement.classList.add('pr-grid');
            btn.classList.add('pr-btn');

            Array.prototype.forEach.call(card.children, function (anak) {
                if (anak === btn) return;
                if (anak.contains(btn)) {
                    for (var el = btn.parentElement; el !== card; el = el.parentElement) {
                        el.classList.add('pr-body');
                    }
                } else if (anak.tagName === 'IMG' || anak.querySelector('img')) {
                    anak.classList.add('pr-media');
                }
            });
        });
    }

    rapikan();
    // Berlaku juga untuk produk yang dimuat belakangan (AJAX)
    new MutationObserver(rapikan).observe(main, { childList: true, subtree: true });
})();
</script>