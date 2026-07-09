<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    (function () {
        const grid = document.querySelector('.carusel-izgara');
        if (!grid) { return; }

        // Seçili kartları DOM sırasına göre 1,2,3… numaralar; seçilmeyenleri boşaltır.
        function numarala() {
            let n = 1;
            grid.querySelectorAll('.carusel-secim').forEach(function (card) {
                const cb = card.querySelector('input[type="checkbox"]');
                const sira = card.querySelector('.carusel-sira');
                if (!sira) { return; }
                if (cb && cb.checked) { sira.value = n++; } else { sira.value = ''; }
            });
        }

        grid.querySelectorAll('.carusel-secim input[type="checkbox"]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                cb.closest('.carusel-secim').classList.toggle('secili', cb.checked);
            });
        });

        // Sürükle-bırak ile sırala → bırakınca numaraları yeniden yaz.
        if (window.Sortable) {
            Sortable.create(grid, {
                animation: 150,
                draggable: '.carusel-secim',
                onEnd: numarala,
            });
        }
    })();
</script>
