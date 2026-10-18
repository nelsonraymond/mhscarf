@props([
    'image' => asset('images/promo-okt.webp'),
    'link' => route('shop.index'),
    'alt' => 'Promo MH Scarf',
    'key' => 'mh-promo-popup',
    'delay' => 600,
])

<div
    id="promo-popup"
    class="hidden fixed inset-0 z-[100] items-center justify-center p-3 sm:p-4
           bg-black/55 backdrop-blur-sm opacity-0 transition-opacity duration-300"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $alt }}"
    data-key="{{ $key }}"
    data-delay="{{ $delay }}"
>
    <div
        class="flex flex-col items-center w-fit max-w-full
               scale-95 transition-transform duration-300"
        data-promo-panel
    >
        {{-- Container mengikuti ukuran gambar --}}
        <div class="relative inline-block overflow-hidden rounded-md">
            <img
                src="{{ $image }}"
                alt="{{ $alt }}"
                class="block w-auto h-auto max-w-[min(92vw,572px)]
                       max-h-[78vh] object-contain"
            >

            <a
                href="{{ $link }}"
                class="absolute inset-x-0 bottom-0
                       min-h-11 px-4 py-3
                       flex items-center justify-center
                       bg-black/55 hover:bg-black/70
                       text-white text-sm font-medium
                       transition-colors
                       focus-visible:outline-none
                       focus-visible:ring-2 focus-visible:ring-inset
                       focus-visible:ring-white"
            >
                Order Sekarang
            </a>
        </div>

        <button
            type="button"
            data-promo-close
            class="mt-3 inline-flex items-center gap-2
                   text-white text-sm
                   focus-visible:outline-none
                   focus-visible:ring-2 focus-visible:ring-white
                   rounded px-3 py-2"
        >
            Tutup
            <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 6l12 12M18 6L6 18"
                />
            </svg>
        </button>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('promo-popup');
        if (!modal) return;

        var key = modal.dataset.key;

        try {
            if (sessionStorage.getItem(key)) return;
        } catch (e) {}

        var panel = modal.querySelector('[data-promo-panel]');
        var closeBtn = modal.querySelector('[data-promo-close]');

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(function () {
                modal.classList.remove('opacity-0');
                panel.classList.remove('scale-95');
            });

            closeBtn.focus();
        }

        function close() {
            try {
                sessionStorage.setItem(key, '1');
            } catch (e) {}

            modal.classList.add('opacity-0');
            panel.classList.add('scale-95');
            document.body.style.overflow = '';

            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);

            document.removeEventListener('keydown', onKey);
        }

        function onKey(e) {
            if (e.key === 'Escape') close();
        }

        closeBtn.addEventListener('click', close);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) close();
        });

        document.addEventListener('keydown', onKey);

        setTimeout(open, parseInt(modal.dataset.delay, 10) || 0);
    })();
</script>