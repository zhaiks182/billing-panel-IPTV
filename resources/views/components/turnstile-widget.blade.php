@props(['siteKey'])

@if ($siteKey)
    <div class="flex flex-col items-center">
        <div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-theme="{{ \App\Models\TurnstileSetting::current()->theme }}"></div>
        <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
