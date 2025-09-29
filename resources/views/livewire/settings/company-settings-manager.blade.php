<div>
    <!-- Navigation par Onglets -->
    <ul class="nav nav-tabs mb-4" id="companyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                class="nav-link @if($activeTab === 'settings') active @endif"
                id="settings-tab"
                wire:click.prevent="$set('activeTab', 'settings')"
                type="button"
                role="tab"
                aria-controls="settings"
                aria-selected="{{ $activeTab === 'settings' ? 'true' : 'false' }}"
            >
                ⚙️ Paramètres
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link @if($activeTab === 'history') active @endif"
                id="history-tab"
                wire:click.prevent="$set('activeTab', 'history')"
                type="button"
                role="tab"
                aria-controls="history"
                aria-selected="{{ $activeTab === 'history' ? 'true' : 'false' }}"
            >
                @if (Auth::user()->tenant_id)
                📜 Historique des Souscriptions
                @endif
            </button>
        </li>
    </ul>
    <hr>

    <!-- Contenu des Onglets -->
    <div class="tab-content" id="companyTabsContent">

        <!-- Onglet 1: Paramètres de l'entreprise (Formulaire) -->
        <div
            class="tab-pane fade @if($activeTab === 'settings') show active @endif"
            id="settings"
            role="tabpanel"
            aria-labelledby="settings-tab"
        >
            <h4 class="mb-3">Paramètres de l’entreprise</h4>
            <form wire:submit.prevent="save" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom de l’entreprise</label>
                        <input type="text" wire:model="name" class="form-control">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" wire:model="email" class="form-control">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" wire:model="phone" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Adresse</label>
                        <textarea wire:model="address" class="form-control"></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">RCCM</label>
                        <input type="text" wire:model="rccm" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">ID NAT</label>
                        <input type="text" wire:model="id_nat" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">DEVISE</label>
                        <input type="text" wire:model="devise" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Logo</label>
                    <input type="file" wire:model="new_logo" class="form-control">
                    @if ($logo)
                        <div class="mt-2">
                            <!-- Utilisez asset() pour l'affichage du logo existant -->
                            <img src="{{ asset('storage/'.$logo) }}" alt="Logo actuel" width="120" class="img-thumbnail rounded-md">
                        </div>
                    @endif
                    @error('new_logo') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <button class="btn btn-primary" wire:loading.attr="disabled">
                  <span wire:loading class="spinner-border spinner-border-sm me-2" role="status"></span>
                  💾 {{ __('Enregistrer') }}
                </button>
            </form>
        </div>

        <!-- Onglet 2: Historique des Souscriptions (Tableau) -->
        @if (Auth::user()->tenant_id)
          
        
        <div
            class="tab-pane fade @if($activeTab === 'history') show active @endif"
            id="history"
            role="tabpanel"
            aria-labelledby="history-tab"
        >
            <h4 class="mb-3">Historique des Souscriptions</h4>

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="">
                        <tr>
                            <th>Plan</th>
                            <th>Durée</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Limite Magasins</th>
                            <th>Limite Utilisateurs</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $sub->plan?->name }}</span><br>
                                    <small class="text-muted">{{ number_format($sub->amount, 2) }} $</small>
                                </td>
                                <td>{{ $sub->plan?->duration_days >= 10000 ? 'Illimité' : $sub->plan?->duration_days.' jours' }}</td>
                                <td>{{ $sub->start_date }}</td>
                                <td>
                                    {{ $sub->plan?->duration_days >= 10000 ? 'Illimité' : $sub->end_date }}
                                    @if(now()->diffInDays($sub->end_date, false) <= 7 && now()->lt($sub->end_date))
                                        <span class="badge bg-warning ms-2">⚠️ Bientôt expiré</span>
                                    @endif
                                </td>
                                <td>{{ $sub->plan?->max_stores ?? 'Illimité' }}</td>
                                <td>{{ $sub->plan?->max_users ?? 'Illimité' }}</td>
                                <td>
                                    @if($sub->end_date >= now())
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Expiré</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Aucune souscription trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $subscriptions->links() }}
            </div>
        </div>@endif
    </div>
</div>


