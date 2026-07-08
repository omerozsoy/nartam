// Müzayede sayfası filtre çubuğu: kelime + lot no + sanatçı + kategori.
// #lotlar içindeki kartları data-* alanlarına göre süzer.
(function () {
    const bar = document.querySelector('[data-alan="filtre"]');
    if (!bar) {
        return;
    }
    const kelimeEl = bar.querySelector('[data-alan="f-kelime"]');
    const lotEl = bar.querySelector('[data-alan="f-lot"]');
    const sanatciEl = bar.querySelector('[data-alan="f-sanatci"]');
    const kategoriEl = bar.querySelector('[data-alan="f-kategori"]');

    function uygula() {
        const kelime = (kelimeEl && kelimeEl.value || '').trim().toLowerCase();
        const lot = (lotEl && lotEl.value || '').trim();
        const sanatci = (sanatciEl && sanatciEl.value) || '';
        const kategori = (kategoriEl && kategoriEl.value) || '';
        const aktif = !!(kelime || lot || sanatci || kategori);
        let gorunen = 0;

        document.querySelectorAll('#lotlar .lot').forEach((kart) => {
            const okKelime = !kelime || (kart.dataset.ara || '').indexOf(kelime) !== -1;
            const okLot = !lot || (kart.dataset.lot || '') === lot;
            const okSanatci = !sanatci || (kart.dataset.sanatci || '') === sanatci;
            const okKategori = !kategori || (kart.dataset.kategori || '') === kategori;
            const goster = okKelime && okLot && okSanatci && okKategori;
            kart.classList.toggle('arama-gizli', !goster);
            if (goster) {
                gorunen++;
            }
        });

        document.querySelectorAll('.lot-bolum').forEach((bolum) => {
            const toplam = bolum.querySelectorAll('.lot').length;
            const gizli = bolum.querySelectorAll('.lot.arama-gizli').length;
            bolum.classList.toggle('arama-gizli', toplam > 0 && gizli === toplam);
        });

        const yok = document.querySelector('[data-alan="arama-yok"]');
        if (yok) {
            yok.hidden = !(aktif && gorunen === 0);
        }
        const slider = document.querySelector('[data-alan="slider"]');
        if (slider) {
            slider.classList.toggle('arama-gizli', aktif);
        }
    }

    if (kelimeEl) kelimeEl.addEventListener('input', uygula);
    if (lotEl) lotEl.addEventListener('input', uygula);
    if (sanatciEl) sanatciEl.addEventListener('change', uygula);
    if (kategoriEl) kategoriEl.addEventListener('change', uygula);
})();
