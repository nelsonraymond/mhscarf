{{-- Footer — responsive MH Scarf layout, no newsletter, brand colors only. --}}
<footer class="mt-16 bg-surface text-muted">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-8 lg:px-16 pt-10 sm:pt-12 pb-6 sm:pb-7">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-x-8 lg:gap-x-10 gap-y-9 sm:gap-y-10">

            {{-- Brand / About --}}
            <div class="sm:col-span-2 lg:col-span-4">
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted mb-4 sm:mb-5">MH Scarf</h3>
                <p class="max-w-md text-sm leading-6 text-muted">
                    Hijab premium dengan desain feminin dan material pilihan untuk menemani setiap momen Anda.
                    Temukan gaya yang nyaman, elegan, dan effortless setiap hari.
                </p>

                {{-- Social media --}}
                <div class="mt-5 sm:mt-6 flex items-center gap-2.5 text-muted" aria-label="Media sosial MH Scarf">
                    {{-- Instagram --}}
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer"
                        class="group inline-flex h-10 w-10 items-center justify-center rounded-full border border-line transition duration-200 hover:-translate-y-1 hover:border-pink-deep hover:bg-pink-soft hover:text-pink-deep hover:shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                        aria-label="Instagram MH Scarf">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3.25" y="3.25" width="17.5" height="17.5" rx="5" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="12" cy="12" r="4.1" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="17.45" cy="6.65" r="1.1" fill="currentColor"/>
                        </svg>
                    </a>

                    {{-- TikTok --}}
                    <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer"
                        class="group inline-flex h-10 w-10 items-center justify-center rounded-full border border-line transition duration-200 hover:-translate-y-1 hover:border-pink-deep hover:bg-pink-soft hover:text-pink-deep hover:shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                        aria-label="TikTok MH Scarf">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M16.65 3.5h-3.22v11.12a2.72 2.72 0 1 1-2.72-2.72c.28 0 .55.04.8.12V8.73a6.1 6.1 0 1 0 5.14 6.02V9.39a7.28 7.28 0 0 0 4.22 1.34V7.5c-2.06-.1-3.79-1.75-4.22-4Z"/>
                        </svg>
                    </a>

                    {{-- WhatsApp --}}
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                        class="group inline-flex h-10 w-10 items-center justify-center rounded-full border border-line transition duration-200 hover:-translate-y-1 hover:border-pink-deep hover:bg-pink-soft hover:text-pink-deep hover:shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                        aria-label="WhatsApp MH Scarf">
                        <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2.5a9.45 9.45 0 0 0-8.16 14.22L2.5 21.5l4.91-1.29A9.5 9.5 0 1 0 12 2.5Zm0 17.2a7.66 7.66 0 0 1-3.9-1.07l-.28-.17-2.91.77.78-2.83-.18-.29A7.7 7.7 0 1 1 12 19.7Zm4.22-5.74c-.23-.12-1.35-.67-1.56-.75-.21-.08-.36-.12-.52.12-.15.23-.59.75-.72.91-.13.16-.27.18-.5.06-1.36-.68-2.25-1.22-3.15-2.77-.24-.41.24-.38.68-1.27.08-.16.04-.3-.02-.42-.06-.12-.52-1.25-.71-1.72-.19-.45-.38-.39-.52-.4h-.44c-.16 0-.42.06-.64.3-.22.23-.84.82-.84 2s.86 2.31.98 2.47c.12.16 1.68 2.57 4.07 3.6.57.25 1.01.4 1.35.51.57.18 1.09.15 1.5.09.46-.07 1.35-.55 1.54-1.08.19-.53.19-.98.13-1.08-.05-.1-.21-.16-.44-.28Z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Customer services --}}
            <div class="lg:col-span-3">
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted mb-4 sm:mb-5">Customer Services</h3>
                <div class="space-y-4 text-sm leading-6 text-muted">
                    <p>
                        <span class="block">Chat with Us :</span>
                        <a href="tel:+6281234567890" class="text-ink transition hover:text-pink-deep">0812 - 3456 - 7890</a>
                    </p>
                    <p>Monday - Sunday :<br><span class="text-ink">8:30 AM - 9:00 PM</span></p>
                </div>
            </div>

            {{-- About us --}}
            <div class="lg:col-span-2">
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted mb-4 sm:mb-5">About Us</h3>
                <ul class="space-y-3 text-sm text-muted">
                    <li><a href="{{ route('faq') }}" class="transition hover:text-pink-deep">Contact Us</a></li>
                    <li><a href="{{ route('orders.index') }}" class="transition hover:text-pink-deep">Track Order</a></li>
                </ul>
            </div>

            {{-- Customer care --}}
            <div class="lg:col-span-3">
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted mb-4 sm:mb-5">Customer Care</h3>
                <ul class="space-y-3 text-sm text-muted">
                    <li><a href="{{ route('faq') }}" class="transition hover:text-pink-deep">Payment Information</a></li>
                    <li><a href="{{ route('faq') }}" class="transition hover:text-pink-deep">How to Order</a></li>
                    <li><a href="{{ route('faq') }}" class="transition hover:text-pink-deep">How to Pay</a></li>
                    <li><a href="{{ route('faq') }}" class="transition hover:text-pink-deep">Shipping Policy</a></li>
                    <li><a href="{{ route('faq') }}" class="transition hover:text-pink-deep">Terms of Service</a></li>
                </ul>
            </div>
        </div>

@php
    $paymentMethods = [
        ['name' => 'Midtrans', 'file' => 'midtrans.svg'],
        ['name' => 'BCA',      'file' => 'bca.svg'],
        ['name' => 'Seabank',  'file' => 'SeaBank.svg'],
        ['name' => 'DANA',     'file' => 'dana.svg'],
        ['name' => 'Mandiri',  'file' => 'mandiri.svg'],
        ['name' => 'BNI',      'file' => 'bni.svg'],
        ['name' => 'QRIS',     'file' => 'qris.svg'],
    ];
@endphp

{{-- Payment methods and copyright --}}
<div class="mt-8 sm:mt-10 pt-5 border-t border-line flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2" aria-label="Metode pembayaran">
            @foreach ($paymentMethods as $payment)
                <img
                    src="{{ asset('images/icons/' . $payment['file']) }}"
                    alt="{{ $payment['name'] }}"
                    class="block h-4 sm:h-5 w-auto max-w-[72px] object-contain select-none pointer-events-none"
                    loading="lazy"
                    draggable="false"
                >
            @endforeach
        </div>
    </div>

    <div class="text-xs text-muted leading-5 lg:text-right">
        <p>© {{ date('Y') }} MH Scarf</p>
        <p>Elegance in Every Wrap</p>
    </div>
</div>

    </div>
</footer>
