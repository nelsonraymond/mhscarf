@if (config('services.recaptcha.enabled') && config('services.recaptcha.site_key'))
    <div>
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        @error('g-recaptcha-response')
            <p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    @once
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endonce
@endif