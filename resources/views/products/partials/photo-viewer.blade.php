{{--
    Visionneur de photo produit (réutilisable).
    Tout élément portant data-photo-viewer ouvre la modale :
      data-photo-src, data-photo-title, data-photo-subtitle, data-photo-detail-url (optionnel)
--}}
<div class="modal fade" id="productPhotoViewer" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content overflow-hidden">
            <div class="modal-header py-3">
                <div class="text-truncate">
                    <h5 class="modal-title text-truncate mb-0" data-viewer-title></h5>
                    <small class="text-muted" data-viewer-subtitle></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('product.fermer') }}"></button>
            </div>
            <div class="modal-body p-0 d-flex align-items-center justify-content-center overflow-hidden" style="background: #11151c; min-height: 320px;">
                <img src="" alt="" data-viewer-img class="img-fluid"
                     style="max-height: 75vh; object-fit: contain; cursor: zoom-in; transition: transform .2s ease;">
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <a href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" data-viewer-open>
                    <i class="bx bx-link-external me-1"></i> {{ __('product.ouvrir_original') }}
                </a>
                <a href="#" class="btn btn-sm btn-primary" data-viewer-detail>
                    <i class="bx bx-show me-1"></i> {{ __('product.voir_details') }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.__productPhotoViewerInit) return;
        window.__productPhotoViewerInit = true;

        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-photo-viewer]');
            if (!trigger) return;
            e.preventDefault();

            var modalEl = document.getElementById('productPhotoViewer');
            if (!modalEl || typeof bootstrap === 'undefined') return;

            var img = modalEl.querySelector('[data-viewer-img]');
            var src = trigger.getAttribute('data-photo-src');
            img.src = src;
            img.alt = trigger.getAttribute('data-photo-title') || '';
            img.style.transform = 'scale(1)';
            img.style.cursor = 'zoom-in';

            modalEl.querySelector('[data-viewer-title]').textContent = trigger.getAttribute('data-photo-title') || '';
            modalEl.querySelector('[data-viewer-subtitle]').textContent = trigger.getAttribute('data-photo-subtitle') || '';
            modalEl.querySelector('[data-viewer-open]').href = src;

            var detail = modalEl.querySelector('[data-viewer-detail]');
            var detailUrl = trigger.getAttribute('data-photo-detail-url');
            detail.classList.toggle('d-none', !detailUrl);
            if (detailUrl) detail.href = detailUrl;

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        // Zoom au clic sur l'image
        document.addEventListener('click', function (e) {
            var img = e.target.closest('#productPhotoViewer [data-viewer-img]');
            if (!img) return;
            var zoomed = img.style.transform === 'scale(1.8)';
            img.style.transform = zoomed ? 'scale(1)' : 'scale(1.8)';
            img.style.cursor = zoomed ? 'zoom-in' : 'zoom-out';
        });
    })();
</script>
