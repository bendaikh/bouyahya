@extends('layouts.app')

@section('title', 'Tableau de bord - Bouyahya')
@section('page-title', 'Tableau de bord')

@push('styles')
<style>
    /* Custom scrollbar for tables */
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.05);
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.15);
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.25);
    }
    
    /* Animated gradient background */
    .gradient-animate {
        background-size: 200% 200%;
        animation: gradientShift 8s ease infinite;
    }
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* Fade in animation */
    .fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Stagger animation delays */
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
</style>
@endpush

@section('content')
<div class="space-y-8 p-6 lg:p-8 xl:p-10 max-w-[1800px] mx-auto">
    <!-- Header with Date -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 fade-in">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
            </p>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                Bienvenue sur votre Système
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white text-sm font-bold">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->name ?? 'Utilisateur' }}</span>
            </div>
        </div>
    </div>

    <!-- Main Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 xl:gap-10 fade-in delay-100">
        <!-- Ventes Card -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 via-rose-600 to-rose-700 p-6 lg:p-7 text-white shadow-xl shadow-rose-500/20 gradient-animate">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold opacity-90">Ventes</span>
                </div>
                <p class="text-3xl font-bold tracking-tight">{{ number_format($totalVentes, 2, ',', ' ') }}</p>
            </div>
        </div>

        <!-- Achats Card -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-600 via-slate-700 to-slate-800 p-6 lg:p-7 text-white shadow-xl shadow-slate-500/20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold opacity-90">Achats</span>
                </div>
                <p class="text-3xl font-bold tracking-tight">{{ number_format($totalAchats, 2, ',', ' ') }}</p>
            </div>
        </div>

        <!-- Solde Clients Card -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 via-amber-500 to-orange-500 p-6 lg:p-7 text-white shadow-xl shadow-amber-500/20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold opacity-90">Solde Clients</span>
                </div>
                <p class="text-3xl font-bold tracking-tight">{{ number_format($soldeClients, 2, ',', ' ') }}</p>
            </div>
        </div>

        <!-- Solde Fournisseurs Card -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-600 p-6 lg:p-7 text-white shadow-xl shadow-emerald-500/20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold opacity-90">Solde Fournisseurs</span>
                </div>
                <p class="text-3xl font-bold tracking-tight">{{ number_format($soldeFournisseurs, 2, ',', ' ') }}</p>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 fade-in delay-200">
        <!-- Derniers Bons de Livraisons -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-rose-500 to-rose-600">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    5 Derniers Bons de livraisons
                </h3>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">N° BL</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Nom Client</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Montant TTC</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Statue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($derniersBonsLivraison as $bon)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $bon['date'] }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-medium whitespace-nowrap">{{ $bon['numero'] }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium truncate max-w-[150px]">{{ $bon['client'] }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-semibold text-right whitespace-nowrap">{{ $bon['montant_ttc'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $bon['statut_class'] }}">
                                    {{ $bon['statut'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Aucun bon de livraison
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Derniers Bons d'Achats -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-emerald-500 to-emerald-600">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    5 Derniers Bons de D'achats
                </h3>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">N° BL</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Nom Client</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Montant TTC</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Statue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($derniersBonsAchat as $bon)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $bon['date'] }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-medium whitespace-nowrap">{{ $bon['numero'] }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium truncate max-w-[150px]">{{ $bon['fournisseur'] }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-semibold text-right whitespace-nowrap">{{ $bon['montant_ttc'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $bon['statut_class'] }}">
                                    {{ $bon['statut'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Aucun bon d'achat
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart and Treasury Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 fade-in delay-300">
        <!-- Chart Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 lg:p-8">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Mouvements Achats Ventes {{ $currentYear }}
            </h3>
            <div class="h-80">
                <canvas id="mouvementsChart"></canvas>
            </div>
            <div class="flex justify-center gap-6 mt-4">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-blue-500"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">achats</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-orange-500"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">ventes</span>
                </div>
            </div>
        </div>

        <!-- Treasury Section -->
        <div class="space-y-6 lg:space-y-8">
            <!-- Etat Caisse -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 lg:p-7">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Etat Caisse
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @forelse($etatCaisse as $caisse)
                    <div class="bg-gradient-to-br from-orange-400 to-orange-500 rounded-xl p-4 text-white shadow-lg shadow-orange-500/20">
                        <p class="text-xs font-semibold opacity-90 mb-1">{{ $caisse['libelle'] }}</p>
                        <p class="text-xl font-bold">{{ number_format($caisse['solde'], 2, ',', ' ') }}</p>
                    </div>
                    @empty
                    <div class="col-span-3 text-center text-gray-500 dark:text-gray-400 py-4">
                        Aucune caisse configurée
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Etat Banque -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 lg:p-7">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Etat Banque
                </h3>
                
                <!-- Decaiss, Encaiss, Charge -->
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-4 text-white shadow-lg shadow-red-500/20">
                        <p class="text-xs font-semibold opacity-90 mb-1">Décaiss</p>
                        <p class="text-lg font-bold">{{ number_format($decaissements, 0, ',', ' ') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg shadow-green-500/20">
                        <p class="text-xs font-semibold opacity-90 mb-1">Encaiss</p>
                        <p class="text-lg font-bold">{{ number_format($encaissements, 0, ',', ' ') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-4 text-white shadow-lg shadow-amber-500/20">
                        <p class="text-xs font-semibold opacity-90 mb-1">Charge</p>
                        <p class="text-lg font-bold">{{ number_format($charges, 0, ',', ' ') }}</p>
                    </div>
                </div>
                
                <!-- Payment Breakdown Pie Charts -->
                <div class="grid grid-cols-4 gap-4">
                    <div class="flex flex-col items-center">
                        <div class="relative w-16 h-16">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#3b82f6" stroke-width="4"
                                        stroke-dasharray="{{ $paymentBreakdown['cheques'] * 0.88 }} 88"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $paymentBreakdown['cheques'] }}%</span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-2">Chèques</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="relative w-16 h-16">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#22c55e" stroke-width="4"
                                        stroke-dasharray="{{ $paymentBreakdown['especes'] * 0.88 }} 88"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $paymentBreakdown['especes'] }}%</span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-2">Espèces</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="relative w-16 h-16">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#ef4444" stroke-width="4"
                                        stroke-dasharray="{{ $paymentBreakdown['traites'] * 0.88 }} 88"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $paymentBreakdown['traites'] }}%</span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-2">Traites</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="relative w-16 h-16">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#a855f7" stroke-width="4"
                                        stroke-dasharray="{{ $paymentBreakdown['virements'] * 0.88 }} 88"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $paymentBreakdown['virements'] }}%</span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-2">Virements</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 lg:p-8 fade-in delay-400 mb-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Actions rapides</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 lg:gap-6">
            <a href="/achats/bon-commande" class="group flex flex-col items-center gap-3 p-4 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/10 hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-900/30 dark:hover:to-blue-800/20 transition-all duration-200 border border-blue-200/50 dark:border-blue-800/50">
                <div class="w-12 h-12 rounded-xl bg-blue-500 flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Nouvel Achat</span>
            </a>
            <a href="/ventes/bon-commande" class="group flex flex-col items-center gap-3 p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/10 hover:from-emerald-100 hover:to-emerald-200 dark:hover:from-emerald-900/30 dark:hover:to-emerald-800/20 transition-all duration-200 border border-emerald-200/50 dark:border-emerald-800/50">
                <div class="w-12 h-12 rounded-xl bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Nouvelle Vente</span>
            </a>
            <a href="/clients" class="group flex flex-col items-center gap-3 p-4 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/10 hover:from-amber-100 hover:to-amber-200 dark:hover:from-amber-900/30 dark:hover:to-amber-800/20 transition-all duration-200 border border-amber-200/50 dark:border-amber-800/50">
                <div class="w-12 h-12 rounded-xl bg-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Clients</span>
            </a>
            <a href="/fournisseurs" class="group flex flex-col items-center gap-3 p-4 rounded-xl bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/20 dark:to-violet-800/10 hover:from-violet-100 hover:to-violet-200 dark:hover:from-violet-900/30 dark:hover:to-violet-800/20 transition-all duration-200 border border-violet-200/50 dark:border-violet-800/50">
                <div class="w-12 h-12 rounded-xl bg-violet-500 flex items-center justify-center shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Fournisseurs</span>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('mouvementsChart');
    if (!ctx) return;
    
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
    
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($mouvementsData['labels']) !!},
            datasets: [
                {
                    label: 'Achats',
                    data: {!! json_encode($mouvementsData['achats']) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.85)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Ventes',
                    data: {!! json_encode($mouvementsData['ventes']) !!},
                    backgroundColor: 'rgba(249, 115, 22, 0.85)',
                    borderColor: 'rgba(249, 115, 22, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#fff',
                    titleColor: isDark ? '#fff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#4b5563',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR', {
                                style: 'decimal',
                                minimumFractionDigits: 2
                            }).format(context.raw) + ' DH';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor,
                        font: {
                            size: 10,
                            weight: '500'
                        }
                    }
                },
                y: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        font: {
                            size: 10
                        },
                        callback: function(value) {
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return (value / 1000).toFixed(0) + 'K';
                            }
                            return value;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>
@endsection
