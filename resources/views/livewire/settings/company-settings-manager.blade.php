<div>
    <h4 class="mb-3">⚙️ Paramètres de l’entreprise</h4>
    <form wire:submit.prevent="save" enctype="multipart/form-data">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Nom de l’entreprise</label>
                <input type="text" wire:model="name" class="form-control">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label>Email</label>
                <input type="email" wire:model="email" class="form-control">
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Téléphone</label>
                <input type="text" wire:model="phone" class="form-control">
            </div>

            <div class="col-md-6">
                <label>Adresse</label>
                <textarea wire:model="address" class="form-control"></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>RCCM</label>
                <input type="text" wire:model="rccm" class="form-control">
            </div>

            <div class="col-md-4">
                <label>ID NAT</label>
                <input type="text" wire:model="id_nat" class="form-control">
            </div>

            <div class="col-md-4">
                <label>DEVISE</label>
                <input type="text" wire:model="devise" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Logo</label>
            <input type="file" wire:model="new_logo" class="form-control">
            @if ($logo)
                <div class="mt-2">
                    <img src="{{ asset('storage/'.$logo) }}" alt="Logo" width="120" class="img-thumbnail">
                </div>
            @endif
        </div>

        <button class="btn btn-primary">
            💾 Enregistrer
        </button>
    </form>
</div>