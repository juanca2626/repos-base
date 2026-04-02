{{-- Global configuration object --}}
<script>
  window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
    ]); ?>
</script>
@php
    $config = [
        'appName' => config('app.name'),
        'locale' => $locale = config('app.locale'),
        'locales' => config('app.locales'),
        'githubAuth' => config('services.github.client_id'),
    ];
@endphp
<script>window.config = @json($config);</script>

{{-- Polyfill some features via polyfill.io --}}
@php
    $polyfills = [
        'Promise',
        'Object.assign',
        'Object.values',
        'Array.prototype.find',
        'Array.prototype.findIndex',
        'Array.prototype.includes',
        'String.prototype.includes',
        'String.prototype.startsWith',
        'String.prototype.endsWith',
    ];
@endphp

{{--    Push--}}

<script defer src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>

<script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-database.js"></script>
<script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-firestore.js"></script>
<script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-messaging.js"></script>
<script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-functions.js"></script>
<link rel="manifest" href="manifest.json">
{{--    Push--}}

{{-- <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features={{ implode(',', $polyfills) }}"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/core-js-bundle@3.33.2/minified.js"></script>

{{-- Load the application scripts --}}
@env('local')
<script src="{{ asset('js/app.js?_=' . time()) }}"></script>
@elseenv('production')
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ asset('js/app.js?_=' . time()) }}"></script>
@endenv

@if(env('site') === 'aurora')
    <script data-jsd-embedded data-key="7b467f3d-286d-47fb-acc0-e7adb80f9464"
            data-base-url="https://jsd-widget.atlassian.com"
            src="https://jsd-widget.atlassian.com/assets/embed.js"></script>
@endif
