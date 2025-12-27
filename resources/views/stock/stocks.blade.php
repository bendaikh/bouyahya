@extends('layouts.app')

@section('title', 'Les stocks - Bouyahya')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg flex flex-col h-[calc(100vh-130px)] overflow-hidden">
    <!-- Sticky Top Section -->
    <div class="p-6 pb-4 border-b border-gray-200 dark:border-gray-700 flex-none bg-white dark:bg-gray-800 z-20">
        <!-- Header -->
        <div class="mb-4 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-1">Les stocks</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Gestion des stocks - Quantités calculées depuis les achats et ventes</p>
            </div>
            <button 
                onclick="window.print()"
                class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors flex items-center gap-2 text-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimer
            </button>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/30 dark:to-teal-800/30 rounded-lg p-3 border-l-4 border-teal-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Total Articles</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalArticles) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-teal-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-lg p-3 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Total en Stock</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalStock) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-lg p-3 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">En Stock Normal</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($inStockCount) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 rounded-lg p-3 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Stock Faible</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($lowStockCount) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche</label>
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Réf ou désignation..." 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Famille</label>
                <select 
                    id="familleFilter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >
                    <option value="">Toutes les familles</option>
                    @foreach($familles as $famille)
                        <option value="{{ $famille['id'] }}">{{ $famille['nom'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                <select 
                    id="statusFilter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                >
                    <option value="">Tous les statuts</option>
                    <option value="Normal">Normal</option>
                    <option value="Stock faible">Stock faible</option>
                    <option value="Rupture">Rupture</option>
                </select>
            </div>
            <div class="flex items-end">
                <button 
                    id="resetFilters"
                    class="w-full px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-xs flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>
    
    <!-- Scrollable Content Section -->
    <div class="flex-1 overflow-hidden bg-gray-50 dark:bg-gray-900/40">
        <div class="p-0 h-full">
            <div class="overflow-x-auto overflow-y-auto relative h-full">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="stockTable">
                    <thead class="bg-gradient-to-r from-teal-600 to-teal-700 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-white uppercase tracking-wider">Réf</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-white uppercase tracking-wider">Désignation</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-white uppercase tracking-wider">Famille</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-white uppercase tracking-wider">Unité</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-white uppercase tracking-wider">Qté Achetée</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-white uppercase tracking-wider">Qté Vendue</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-white uppercase tracking-wider">Stock Actuel</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-white uppercase tracking-wider">Stock Min</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-white uppercase tracking-wider">Prix Achat</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-white uppercase tracking-wider">Prix Vente</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-white uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($stocks as $stock)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors stock-row" 
                            data-famille-id="{{ $stock['famille_id'] ?? '' }}"
                            data-status="{{ $stock['status'] }}"
                            data-reference="{{ strtolower($stock['reference']) }}"
                            data-designation="{{ strtolower($stock['designation']) }}">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm font-medium text-teal-600 dark:text-teal-400">{{ $stock['reference'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-900 dark:text-white">{{ $stock['designation'] }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $stock['famille'] ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $stock['unite_symbole'] ?: $stock['unite_mesure'] ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ number_format($stock['quantite_achetee'], 0, ',', ' ') }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ number_format($stock['quantite_vendue'], 0, ',', ' ') }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="text-sm font-bold {{ $stock['stock_actuel'] > 0 ? 'text-gray-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($stock['stock_actuel'], 0, ',', ' ') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($stock['stock_minimum'], 0, ',', ' ') }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-900 dark:text-white">{{ number_format($stock['prix_achat'], 2, ',', ' ') }} DH</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($stock['prix_vente'], 2, ',', ' ') }} DH</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $stock['status_class'] }}">
                                    {{ $stock['status'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400 text-lg">Aucun article trouvé</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Ajoutez des articles dans la section Articles</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                        <!-- No results row (hidden by default) -->
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400">Aucun résultat trouvé</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Essayez de modifier vos filtres</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Info Box at bottom -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex-none">
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Calcul du stock</h4>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        Stock = <strong>Quantité Achetée</strong> (Bon d'achat Fournisseur validé) - <strong>Quantité Vendue</strong> (Bon de livraison livré)
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    function initStockFilters() {
        var searchInput = document.getElementById('searchInput');
        var familleFilter = document.getElementById('familleFilter');
        var statusFilter = document.getElementById('statusFilter');
        var resetButton = document.getElementById('resetFilters');
        var tableBody = document.querySelector('#stockTable tbody');
        var noResultsRow = document.getElementById('noResultsRow');
        
        if (!tableBody) {
            console.error('Stock table not found');
            return;
        }

        function filterTable() {
            var searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            var selectedFamille = familleFilter ? familleFilter.value : '';
            var selectedStatus = statusFilter ? statusFilter.value : '';
            var visibleCount = 0;
            
            // Get all stock rows (exclude special rows like noResultsRow and emptyRow)
            var rows = tableBody.querySelectorAll('tr.stock-row');
            
            rows.forEach(function(row) {
                var reference = (row.getAttribute('data-reference') || '').toLowerCase();
                var designation = (row.getAttribute('data-designation') || '').toLowerCase();
                var familleId = row.getAttribute('data-famille-id') || '';
                var status = row.getAttribute('data-status') || '';

                // Search matches if term is in reference OR designation
                var matchesSearch = searchTerm === '' || 
                    reference.indexOf(searchTerm) !== -1 || 
                    designation.indexOf(searchTerm) !== -1;
                
                // Famille filter - compare with famille ID as string
                var matchesFamille = selectedFamille === '' || familleId === selectedFamille;
                
                // Status filter - exact match
                var matchesStatus = selectedStatus === '' || status === selectedStatus;

                var isVisible = matchesSearch && matchesFamille && matchesStatus;
                
                if (isVisible) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (noResultsRow) {
                if (visibleCount === 0 && rows.length > 0) {
                    noResultsRow.style.display = '';
                } else {
                    noResultsRow.style.display = 'none';
                }
            }
        }

        // Add event listeners with proper binding
        if (searchInput) {
            searchInput.addEventListener('input', filterTable);
            searchInput.addEventListener('keyup', filterTable);
            searchInput.addEventListener('change', filterTable);
        }
        
        if (familleFilter) {
            familleFilter.addEventListener('change', filterTable);
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', filterTable);
        }
        
        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (familleFilter) familleFilter.value = '';
                if (statusFilter) statusFilter.value = '';
                filterTable();
            });
        }

        // Initial filter
        filterTable();
        
        // Debug: log that filters are initialized
        console.log('Stock filters initialized. Found ' + tableBody.querySelectorAll('tr.stock-row').length + ' rows.');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStockFilters);
    } else {
        // DOM already loaded
        initStockFilters();
    }
    
    // Also try after a short delay in case of dynamic content
    setTimeout(initStockFilters, 500);
})();
</script>

<style>
@media print {
    .no-print, button, select, input {
        display: none !important;
    }
    
    .bg-gradient-to-r {
        background: #00A89D !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    thead {
        background-color: #00A89D !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    th {
        color: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .h-\[calc\(100vh-130px\)\] {
        height: auto !important;
        overflow: visible !important;
    }
    
    .overflow-hidden, .overflow-y-auto, .overflow-x-auto {
        overflow: visible !important;
    }
}
</style>
@endsection
