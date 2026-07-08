// Takip et / takipten çık — tüm sayfalarda (kart, detay, hesabım).
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    document.addEventListener('click', async (olay) => {
        const btn = olay.target.closest('[data-alan="takip"]');
        if (!btn) {
            return;
        }
        olay.preventDefault();
        btn.disabled = true;
        try {
            const yanit = await fetch('/takip/' + encodeURIComponent(btn.dataset.id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF,
                },
            });
            const sonuc = await yanit.json();
            if (yanit.ok && sonuc.ok) {
                btn.classList.toggle('takip-aktif', sonuc.takip);
                btn.textContent = sonuc.takip ? 'Takip Ediliyor' : 'Takip Et';
                btn.setAttribute('aria-pressed', sonuc.takip ? 'true' : 'false');
            }
        } catch (e) {
            /* sessizce geç */
        } finally {
            btn.disabled = false;
        }
    });
})();
