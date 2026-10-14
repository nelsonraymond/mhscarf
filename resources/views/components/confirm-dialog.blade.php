{{-- Confirm dialog reusable. Pakai: tambahkan data-confirm pada <form>. --}}
<dialog id="confirm-dialog"
    class="confirm-dialog m-auto w-[calc(100%-2rem)] max-w-sm p-0 rounded-2xl bg-white text-ink shadow-2xl border border-line backdrop:bg-ink/40 backdrop:backdrop-blur-sm"
    aria-labelledby="confirm-title" aria-describedby="confirm-message">
    <div class="p-6 text-center">
        <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4" aria-hidden="true">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17L18.16 19.67a2.25 2.25 0 01-2.24 2.08H8.08a2.25 2.25 0 01-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 00-3.48-.4m-12 .57c.34-.06.68-.12 1.02-.17m0 0a48.1 48.1 0 013.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 00-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 00-7.5 0"/>
            </svg>
        </div>
        <h2 id="confirm-title" class="font-display text-lg font-bold text-ink">Konfirmasi</h2>
        <p id="confirm-message" class="text-sm text-muted mt-1.5 leading-relaxed"></p>
    </div>

    <div class="px-6 pb-6 flex flex-col-reverse sm:flex-row gap-2.5">
        <button type="button" data-confirm-cancel
            class="flex-1 h-11 rounded-xl border border-line bg-white text-sm font-medium text-ink hover:bg-cream transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
            Batal
        </button>
        <button type="button" data-confirm-ok
            class="flex-1 h-11 rounded-xl bg-rose-500 hover:bg-rose-600 text-sm font-medium text-white transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-rose-500">
            Ya, Hapus
        </button>
    </div>
</dialog>

<style>
    .confirm-dialog[open] { animation: confirm-pop .22s cubic-bezier(.22, .61, .36, 1); }
    .confirm-dialog[open]::backdrop { animation: confirm-fade .22s ease; }
    @keyframes confirm-pop  { from { opacity: 0; transform: translateY(8px) scale(.96); } to { opacity: 1; transform: none; } }
    @keyframes confirm-fade { from { opacity: 0; } to { opacity: 1; } }
    @media (prefers-reduced-motion: reduce) {
        .confirm-dialog[open], .confirm-dialog[open]::backdrop { animation: none; }
    }
</style>

<script>
    (function () {
        var dlg = document.getElementById('confirm-dialog');
        if (!dlg) return;

        var titleEl = document.getElementById('confirm-title');
        var msgEl = document.getElementById('confirm-message');
        var okBtn = dlg.querySelector('[data-confirm-ok]');
        var cancelBtn = dlg.querySelector('[data-confirm-cancel]');
        var pendingForm = null;

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form.matches || !form.matches('form[data-confirm]')) return;
            if (form.dataset.confirmed === '1') return;

            e.preventDefault();
            pendingForm = form;
            titleEl.textContent = form.dataset.confirmTitle || 'Konfirmasi';
            msgEl.textContent = form.dataset.confirm;
            okBtn.textContent = form.dataset.confirmOk || 'Ya, Lanjutkan';
            dlg.showModal();
            cancelBtn.focus();
        });

        okBtn.addEventListener('click', function () {
            var form = pendingForm;
            pendingForm = null;
            dlg.close();
            if (form) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });

        cancelBtn.addEventListener('click', function () { pendingForm = null; dlg.close(); });

        // Klik area gelap di luar kartu = batal
        dlg.addEventListener('click', function (e) {
            if (e.target === dlg) { pendingForm = null; dlg.close(); }
        });
    })();
</script>