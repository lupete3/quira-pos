<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">

      <!-- Left: copyright & creator -->
      <div class="text-body">
        © <?php echo date('Y'); ?>, fait avec ❤️ par
        <a href="{{ config('variables.creatorUrl') ?? '#' }}" target="_blank" class="footer-link">
          {{ config('variables.creatorName') ?? '' }}
        </a>
      </div>

      <!-- Right: links (visible sur lg et plus) -->
      <div class="d-none d-lg-inline-block">
        <a href="{{ config('variables.licenseUrl') ?? '#' }}" class="footer-link me-4" target="_blank">Licence</a>
        <a href="{{ config('variables.moreThemes') ?? '#' }}" target="_blank" class="footer-link me-4">Plus de Thèmes</a>
        <a href="{{ config('variables.documentation') ? config('variables.documentation').'/laravel-introduction.html' : '#' }}" target="_blank" class="footer-link me-4">Documentation</a>
        <a href="{{ config('variables.support') ?? '#' }}" target="_blank" class="footer-link d-none d-sm-inline-block">Support</a>
      </div>

    </div>
  </div>
</footer>
<!-- /Footer -->
