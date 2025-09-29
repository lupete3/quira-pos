<div>
    <div class="flex-grow-1">

      @if (Auth::user()->role_id == 1)
        <div class="mb-4 col-md-4">
            <label for="storeFilter" class="form-label">Filtrer par Magasin</label>
            <select wire:model.lazy="storeId" id="storeFilter" class="form-select">
                <option value="">Tous les magasins</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </select>
        </div>
      @endif

        <!-- ===== Stats Cards ===== -->
        <div class="row g-4 mb-4">
            <!-- Ventes Aujourd'hui -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="d-block text-muted mb-1">Ventes Aujourd'hui</span>
                                <h3 class="card-title mb-2">{{ number_format($todaySales, 2, ',', ' ') }}
                                    {{ company()?->devise }}</h3>
                                <span
                                    class="badge rounded-pill bg-label-{{ $salesGrowth >= 0 ? 'success' : 'danger' }}">
                                    <i
                                        class="bx {{ $salesGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} me-1"></i>
                                    {{ number_format($salesGrowth, 1) }}% vs hier
                                </span>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="menu-icon tf-icons bx bx-cart"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ventes du mois -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="d-block text-muted mb-1">Ventes du Mois</span>
                                <h3 class="card-title mb-2">{{ number_format($currentMonthSales, 2, ',', ' ') }}
                                    {{ company()?->devise }}</h3>
                                <span
                                    class="badge rounded-pill bg-label-{{ $monthSalesGrowth >= 0 ? 'success' : 'danger' }}">
                                    <i
                                        class="bx {{ $monthSalesGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} me-1"></i>
                                    {{ number_format($monthSalesGrowth, 1) }}% vs mois passé
                                </span>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achats du mois -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="d-block text-muted mb-1">Achats du Mois</span>
                                <h3 class="card-title mb-2">{{ number_format($currentMonthPurchases, 2, ',', ' ') }}
                                    {{ company()?->devise }}</h3>
                                <span
                                    class="badge rounded-pill bg-label-{{ $monthPurchasesGrowth >= 0 ? 'success' : 'danger' }}">
                                    <i
                                        class="bx {{ $monthPurchasesGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} me-1"></i>
                                    {{ number_format($monthPurchasesGrowth, 1) }}% vs mois passé
                                </span>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="menu-icon tf-icons bx bx-cart-download"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produits en stock -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="d-block text-muted mb-1">Solde en caisse</span>
                                <h3 class="card-title mb-2">{{ $current_balance }} {{ company()?->devise }}</h3>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="menu-icon tf-icons bx bx-box"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Charts & Recent Sales ===== -->
        <div class="row g-4 mb-4">
            <!-- Sales Chart -->
            <div class="col-lg-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Ventes Hebdomadaires</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" wire:ignore style="max-height: 320px"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Ventes Récentes</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse ($recentSales as $sale)
                                <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i
                                                    class="tf-icons bx bx-receipt"></i></span>
                                        </div>
                                        <div>
                                            <div class="fw-medium">#INV-{{ $sale->id }}</div>
                                            <small class="text-muted">{{ $sale->sale_date }}</small>
                                        </div>
                                    </div>
                                    <span
                                        class="fw-semibold">{{ number_format($sale->total_paid, 2, ',', ' ') }}{{ company()?->devise }}</span>
                                </li>
                            @empty
                                <span class="badge rounded-pill bg-label-info">
                                      <p class="text-muted mb-0">Aucune ventes effectuée pour le moment.</p>
                                  </span>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Popular Products & Quick Actions ===== -->
        <div class="row g-4">
            <!-- Popular Products -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Produits Populaires</h5>
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 g-3">
                            @forelse ($popularProducts as $product)
                                <div class="col">
                                    <div class="card h-100 border-1 product-card">
                                        <div class="card-body text-center">
                                            {{-- Nom du produit --}}
                                            <h6 class="card-title mb-1 text-truncate" title="{{ $product->name }}">
                                                {{ $product->name }}
                                            </h6>

                                            {{-- Prix de vente --}}
                                            <div class="text-primary fw-semibold mb-1">
                                                {{ number_format($product->sale_price, 2, ',', ' ') }}{{ company()?->devise }}
                                            </div>

                                            {{-- Stock actuel dans tous les magasins --}}
                                            <small class="text-muted d-block">
                                                Stock : {{ $product->stores->sum('pivot.quantity') }}
                                            </small>

                                            {{-- Quantité totale vendue --}}
                                            <small class="text-success d-block fw-semibold">
                                                Vendus : {{ $product->total_sold ?? 0 }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center">
                                  <span class="badge rounded-pill bg-label-info">
                                      <p class="text-muted mb-0">Aucun produit vendu pour le moment.</p>
                                  </span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Purchases-->
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Achats Récents</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse ($recentPurchases as $purchase)
                                <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i
                                                    class="tf-icons bx bx-receipt"></i></span>
                                        </div>
                                        <div>
                                            <div class="fw-medium">#ORD-{{ $purchase->id }}</div>
                                            <small class="text-muted">{{ $purchase->purchase_date }}</small>
                                        </div>
                                    </div>
                                    <span
                                        class="fw-semibold">{{ number_format($purchase->total_paid, 2, ',', ' ') }}{{ company()?->devise }}</span>
                                </li>

                            @empty
                                <span class="badge rounded-pill bg-label-info">
                                      <p class="text-muted mb-0">Aucun achat effectué pour le moment.</p>
                                </span>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-12">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Actions Rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-3">
                                <a href="{{ route('pos.index') }}"
                                    class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3">
                                    <i class="tf-icons bx bx-receipt fs-2 mb-2"></i>
                                    <span>Nouvelle Vente</span>
                                </a>
                            </div>
                            <div class="col-3">
                                <a href="{{ route('clients.index') }}"
                                    class="btn btn-outline-success w-100 d-flex flex-column align-items-center py-3">
                                    <i class="tf-icons bx bx-user-plus fs-2 mb-2"></i>
                                    <span>Nouveau Client</span>
                                </a>
                            </div>
                            @if (Auth::user()->role_id == 1)
                            <div class="col-3">
                                <a href="{{ route('products.index') }}"
                                    class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-3">
                                    <i class="tf-icons bx bx-box fs-2 mb-2"></i>
                                    <span>Ajouter Stock</span>
                                </a>
                            </div>
                            <div class="col-3">
                                <a href="{{ route('purchases.index') }}"
                                    class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-3">
                                    <i class="tf-icons bx bx-cart-add fs-2 mb-2"></i>
                                    <span>Nouvel Achat</span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===== Chart.js ===== -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const labels = @json($weeklySales->keys()->map(fn($w) => 'Semaine ' . $w));
            const dataVals = @json($weeklySales->values());
            const ctx = document.getElementById('salesChart');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ventes ({{ company()?->devise }})',
                        data: dataVals,
                        backgroundColor: '#6EB8F1', // Sneat primary with alpha
                        borderColor: '#6EB8F4',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => value + '{{ company()?->devise }}'
                            }
                        }
                    }
                }
            });
        })();
    </script>

    <script>
        window.addEventListener('load', function() {
            Livewire.on('updateChartData', function(data) {
                var chartInstance = document.getElementById('salesChart').getContext('2d');
                if (chartInstance.chart !== undefined && chartInstance.chart !== null) {
                    chartInstance.chart.destroy();
                }
            })
        })
    </script>

    <script>
        window.addEventListener('weeklySalesUpdated', function(event) {

            const labels = event.detail.labels;
            const dataVals = event.detail.data;;
            const ctx = document.getElementById('salesChart');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ventes ({{ company()?->devise }})',
                        data: dataVals,
                        backgroundColor: '#6EB8F1', // Sneat primary with alpha
                        borderColor: '#6EB8F4',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => value + '{{ company()?->devise }}'
                            }
                        }
                    }
                }
            });

        });
    </script>

</div>
