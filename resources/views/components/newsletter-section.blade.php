@props(['id' => 'newsletter'])

{{-- Newsletter — DESIGN.md §42 brand voice; frontend-only validation, no external mailing service. --}}
<section id="{{ $id }}" class="relative overflow-hidden bg-gradient-to-br from-cream to-pink-soft/50 border-y border-line">
    {{-- Floral accents at edges only (§6: never behind inputs) --}}
    <svg class="absolute -left-8 -bottom-10 w-40 h-40 text-pink-deep opacity-[0.10] pointer-events-none" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
        <path d="M20 95 C22 70 28 50 45 30"/><path d="M30 70 C18 66 12 55 14 42 C28 48 33 60 30 70Z"/>
        <circle cx="47" cy="26" r="6"/><circle cx="38" cy="20" r="4.5"/><circle cx="56" cy="20" r="4.5"/>
    </svg>

    <div class="max-w-xl mx-auto px-4 sm:px-6 py-12 md:py-20 text-center relative">
        <p class="text-[11px] tracking-[0.25em] uppercase text-pink-mauve font-semibold mb-2">Newsletter</p>
        <h2 class="font-display text-2xl md:text-4xl font-semibold text-ink leading-snug">A little beauty,<br class="hidden sm:block"> delivered.</h2>
        <p class="text-sm text-muted mt-3 leading-relaxed">Koleksi baru, cerita bahan, dan penawaran khusus — langsung ke inbox Anda. Tanpa spam.</p>

        <form class="mt-6 flex flex-col sm:flex-row gap-3 max-w-md mx-auto" action="#" method="POST"
            onsubmit="event.preventDefault(); const f=this; if(!f.querySelector('input').checkValidity()){f.querySelector('input').reportValidity(); return;} f.querySelector('input').value=''; f.querySelector('button').textContent='Berhasil'; f.querySelector('[data-newsletter-note]').classList.remove('hidden');">
            @csrf
            <label for="newsletter-email-input" class="sr-only">Alamat email</label>
            <input id="newsletter-email-input" type="email" required placeholder="Email Anda"
                class="flex-1 min-w-0 px-4 py-3 rounded-xl border border-line bg-white text-sm focus:outline-none focus:ring-1 focus:ring-pink-deep min-h-[48px]">
            <button type="submit" class="shrink-0 inline-flex items-center justify-center px-7 py-3 rounded-xl bg-pink-deep hover:bg-pink-mauve text-white text-sm font-medium transition-all duration-300 cursor-pointer min-h-[48px]">
                Join
            </button>
        </form>
        <p data-newsletter-note class="hidden text-xs text-pink-mauve mt-3">Terima kasih — selamat datang di keluarga MH Scarf</p>
    </div>
</section>
