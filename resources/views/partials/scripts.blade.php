<!-- BEGIN: Vendor JS-->

@vite([
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
])

<!-- Page Vendor JS-->
@yield('vendor-script')
<!-- END: Page Vendor JS-->

@vite(['resources/js/app.js'])

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<script>
    document.addEventListener('livewire:init', () => {
        const handleModal = (modalId, action) => {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                if (action === 'hide') {
                    modal.hide();
                }
            }
        };

        Livewire.on('close-modal', (event) => {
            // Close any modal, assuming only one is open at a time.
            // A more robust solution could pass the modal ID from the component.
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modalEl => {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
            });
        });

        Livewire.on('show-validate-confirmation', () => {
            Swal.fire({
                title: 'Etes-vous sûr ?',
                text: "Vous êtes au point d'appliquer cette action !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('validateConfirmed');
                }
            });
        });

        Livewire.on('show-delete-confirmation', () => {
            Swal.fire({
                title: 'Etes-vous sûr ?',
                text: "Vous êtes au point de supprimer cette opération !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, Supprimer',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('deleteConfirmed');
                }
            });
        });

    });
</script>

@stack('scripts')

<!-- Place this tag before closing body tag for github widget button. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')

