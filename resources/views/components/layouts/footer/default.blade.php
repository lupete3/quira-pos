<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">

      <!-- Left: copyright & creator -->
      <div class="text-body">
        © {{ date('Y') }}, {{ __('footer.made_with') }} ❤️ {{ __('footer.by') }}
        <a href="{{ config('variables.creatorUrl') ?? '#' }}" target="_blank" class="footer-link">
          {{ config('variables.creatorName') ?? '' }}
        </a>
      </div>

      <!-- Right: links -->
      <div class="d-none d-lg-inline-block">
        <a href="{{ config('variables.licenseUrl') ?? '#' }}" class="footer-link me-4" target="_blank">
          {{ __('footer.license') }}
        </a>
        {{-- <a href="{{ config('variables.moreThemes') ?? '#' }}" target="_blank" class="footer-link me-4">
          {{ __('footer.more_themes') }}
        </a> --}}
        <a href="{{ config('variables.documentation') ? config('variables.documentation').'/laravel-introduction.html' : '#' }}" target="_blank" class="footer-link me-4">
          {{ __('footer.documentation') }}
        </a>
        <a href="{{ config('variables.support') ?? '#' }}" target="_blank" class="footer-link d-none d-sm-inline-block">
          {{ __('footer.support') }}
        </a>
      </div>

    </div>
  </div>
</footer>
<!-- /Footer -->
