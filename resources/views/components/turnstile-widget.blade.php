@props(['siteKey'])

@if ($siteKey)
    <div>
        <div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-theme="dark"></div>
        <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
