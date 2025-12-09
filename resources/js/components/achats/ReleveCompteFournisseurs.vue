<template>
    <div class="space-y-4">
        <!-- Top Filter Row -->
        <div class="bg-gray-800 dark:bg-gray-900 rounded-lg shadow-lg p-4">
            <div class="flex flex-wrap items-end gap-3">
                <!-- Date Début -->
                <div class="min-w-[130px]">
                    <label class="block text-xs text-gray-400 mb-1">Date Début</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            v-model="filters.dateDebut"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>
                
                <!-- Date Fin -->
                <div class="min-w-[130px]">
                    <label class="block text-xs text-gray-400 mb-1">Date Fin</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            v-model="filters.dateFin"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>
                
                <!-- Période Prédéfinie -->
                <div class="min-w-[130px]">
                    <label class="block text-xs text-gray-400 mb-1">Période Prédéfinie</label>
                    <select 
                        v-model="filters.periodePredefinee"
                        @change="applyPredefinedPeriod"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Sélectionner...</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="quarter">Ce trimestre</option>
                        <option value="year">Cette année</option>
                        <option value="all">Tout</option>
                    </select>
                </div>
                
                <!-- Code Fournisseur -->
                <div class="min-w-[120px]">
                    <label class="block text-xs text-gray-400 mb-1">Code</label>
                    <div class="relative">
                        <select 
                            v-model="filters.fournisseurId"
                            @change="onFournisseurChange"
                            class="w-full px-3 py-2 pr-8 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Tous</option>
                            <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.code_fournisseur }}</option>
                        </select>
                        <span class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                    </div>
                </div>
                
                <!-- Nom Fournisseur -->
                <div class="min-w-[140px]">
                    <label class="block text-xs text-gray-400 mb-1">Nom Fournisseur</label>
                    <input 
                        type="text" 
                        :value="selectedFournisseurName"
                        readonly
                        placeholder="—"
                        class="w-full px-3 py-2 bg-gray-600 border border-gray-600 rounded text-gray-300 text-sm"
                    />
                </div>
                
                <!-- Client Livré -->
                <div class="min-w-[140px]">
                    <label class="block text-xs text-gray-400 mb-1">Client Livré</label>
                    <select 
                        v-model="filters.clientLivre"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Tous</option>
                        <option v-for="client in clientsLivres" :key="client" :value="client">{{ client }}</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="flex items-end">
                    <button 
                        @click="resetFilters"
                        class="px-3 py-2 bg-gray-700 hover:bg-gray-600 border border-gray-600 rounded text-white text-sm transition-colors flex items-center gap-2"
                        title="Réinitialiser les filtres"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="text-xs">Réinitialiser</span>
                    </button>
                </div>
                
                <!-- Small Summary Cards -->
                <div class="flex gap-2 ml-auto">
                    <!-- Imp (Impayé) -->
                    <div class="bg-red-600 rounded-lg px-4 py-2 min-w-[130px] text-center">
                        <p class="text-xs font-bold text-white">Imp</p>
                        <p class="text-xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.impaye) }}</p>
                    </div>
                    <!-- Déva (Dévalidé) -->
                    <div class="bg-fuchsia-600 rounded-lg px-4 py-2 min-w-[130px] text-center">
                        <p class="text-xs font-bold text-white">Déva</p>
                        <p class="text-xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.devalide) }}</p>
                    </div>
                    <!-- Repo (Reporté) -->
                    <div class="bg-yellow-400 rounded-lg px-4 py-2 min-w-[130px] text-center">
                        <p class="text-xs font-bold text-gray-900">Repo</p>
                        <p class="text-xl font-bold text-gray-900 whitespace-nowrap">{{ formatCurrency(totals.reporte) }}</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col gap-1 ml-2">
                    <button 
                        @click="sendPDF"
                        class="flex items-center gap-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 border border-gray-600 rounded text-white text-sm transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Envoyer
                    </button>
                    <button 
                        @click="printReleve"
                        class="flex items-center gap-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 border border-gray-600 rounded text-white text-sm transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimer
                    </button>
                    <button 
                        @click="closeReleve"
                        class="flex items-center gap-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 border border-gray-600 rounded text-white text-sm transition-colors"
                    >
                        Fermer
                        <span class="bg-green-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">FERMÉ</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Large Summary Cards Row -->
        <div class="flex flex-wrap gap-4 items-center">
            <!-- Crédit (Green) -->
            <div class="bg-green-600 rounded-lg px-8 py-4 min-w-[240px]">
                <p class="text-sm font-semibold text-green-100">Crédit</p>
                <p class="text-4xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.credit) }}</p>
            </div>
            
            <!-- Débit (Blue) -->
            <div class="bg-blue-600 rounded-lg px-8 py-4 min-w-[240px]">
                <p class="text-sm font-semibold text-blue-100">Débit</p>
                <p class="text-4xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.debit) }}</p>
            </div>
            
            <!-- Solde (Red) -->
            <div class="bg-red-600 rounded-lg px-8 py-4 min-w-[240px]">
                <p class="text-sm font-semibold text-red-100">Solde</p>
                <p class="text-4xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.solde) }}</p>
            </div>
            
            <!-- Quantité (Yellow) -->
            <div class="bg-yellow-500 rounded-lg px-8 py-4 min-w-[240px]">
                <p class="text-sm font-semibold text-yellow-900">Quantité</p>
                <p class="text-4xl font-bold text-yellow-900 whitespace-nowrap">{{ formatNumber(totals.quantite) }}</p>
            </div>
            
            <!-- Spacer -->
            <div class="flex-1"></div>
            
            <!-- Print/Export Buttons -->
            <div class="flex gap-2">
                <button 
                    @click="printReleve"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimer
                </button>
                <button 
                    @click="exportPDF"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exporter PDF
                </button>
            </div>
        </div>

        <!-- Results Table -->
        <div class="bg-gray-800 dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Pièce</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">N° de Bon</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Client Livré</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Ville Livraison</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Quantité</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Débit</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Crédit</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Solde</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Règlement</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Echéance</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Banque</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Payé</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-red-400 uppercase tracking-wider">Dévalidé</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-red-400 uppercase tracking-wider">Impayé</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Reporté</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="16" class="px-4 py-8 text-center text-gray-400">
                                <div class="flex justify-center items-center">
                                    <svg class="animate-spin h-6 w-6 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Chargement...
                                </div>
                            </td>
                        </tr>
                        <tr v-else-if="!filters.fournisseurId">
                            <td colspan="16" class="px-4 py-8 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p>Veuillez sélectionner un fournisseur pour afficher le relevé</p>
                            </td>
                        </tr>
                        <tr v-else-if="filteredData.length === 0">
                            <td colspan="16" class="px-4 py-8 text-center text-gray-400">
                                Aucune donnée trouvée pour les filtres sélectionnés.
                            </td>
                        </tr>
                        <template v-else>
                            <tr v-for="(row, index) in paginatedData" :key="index" 
                                class="hover:bg-gray-700/50 transition-colors"
                            >
                                <!-- Pièce -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 py-1 rounded text-xs font-semibold',
                                        row.type === 'achat' ? 'bg-blue-600 text-white' : 'bg-gray-600 text-gray-200'
                                    ]">
                                        {{ row.type === 'achat' ? 'Achat' : 'Rég' }}
                                    </span>
                                </td>
                                <!-- Date -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ formatDate(row.date) }}</td>
                                <!-- N° de Bon -->
                                <td class="px-3 py-2 whitespace-nowrap text-blue-400 font-medium">{{ row.numero || '-' }}</td>
                                <!-- Client Livré -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.client_livre || '-' }}</td>
                                <!-- Ville Livraison -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.ville || '-' }}</td>
                                <!-- Quantité -->
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-300">{{ row.quantite ? formatNumber(row.quantite) : '' }}</td>
                                <!-- Débit -->
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-300">{{ row.debit ? formatCurrency(row.debit) : '' }}</td>
                                <!-- Crédit -->
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-300">{{ row.credit ? formatCurrency(row.credit) : '' }}</td>
                                <!-- Solde (cumulative) -->
                                <td class="px-3 py-2 whitespace-nowrap text-right font-medium" :class="getSoldeClass(row.solde_cumule)">
                                    {{ formatCurrency(row.solde_cumule) }}
                                </td>
                                <!-- Règlement -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.type_reglement || '' }}</td>
                                <!-- Echéance -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.echeance ? formatDate(row.echeance) : (row.type === 'achat' ? 'Invalid Date' : '') }}</td>
                                <!-- Banque -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.banque || '' }}</td>
                                <!-- Payé -->
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-300">{{ row.paye ? formatCurrency(row.paye) : '' }}</td>
                                <!-- Dévalidé -->
                                <td class="px-3 py-2 whitespace-nowrap text-right text-red-400 font-medium">{{ row.devalide ? formatCurrency(row.devalide) : '' }}</td>
                                <!-- Impayé -->
                                <td class="px-3 py-2 whitespace-nowrap text-right text-red-400 font-medium">{{ row.impaye ? formatCurrency(row.impaye) : '' }}</td>
                                <!-- Reporté -->
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-300">{{ row.reporte ? formatCurrency(row.reporte) : '' }}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="filteredData.length > 0" class="flex justify-between items-center px-4 py-3 border-t border-gray-700">
                <p class="text-sm text-gray-400">
                    Affichage de {{ paginationStart }}-{{ paginationEnd }} sur {{ filteredData.length }} entrées
                </p>
                
                <div class="flex items-center space-x-1">
                    <button 
                        @click="previousPage"
                        :disabled="currentPage === 1"
                        class="px-3 py-1 border border-gray-600 rounded text-gray-300 hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <button 
                        v-for="page in displayedPages" 
                        :key="page"
                        @click="goToPage(page)"
                        :class="[
                            'px-3 py-1 border rounded transition-colors',
                            page === currentPage 
                                ? 'bg-green-600 border-green-600 text-white' 
                                : 'border-gray-600 text-gray-300 hover:bg-gray-700'
                        ]"
                    >
                        {{ page }}
                    </button>
                    
                    <button 
                        @click="nextPage"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1 border border-gray-600 rounded text-gray-300 hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

// State
const loading = ref(false)
const fournisseurs = ref([])
const bonsAchat = ref([])
const bonsHistorique = ref([]) // Data from historique API with pre-calculated solde/reliquat
const reglements = ref([])
const clientsLivres = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(15)

// Filters
const filters = ref({
    dateDebut: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0],
    dateFin: new Date(new Date().getFullYear(), 11, 31).toISOString().split('T')[0],
    periodePredefinee: '',
    fournisseurId: '',
    clientLivre: ''
})

// Computed: Selected Fournisseur Name
const selectedFournisseurName = computed(() => {
    if (!filters.value.fournisseurId) return ''
    const f = fournisseurs.value.find(f => f.id == filters.value.fournisseurId)
    return f ? f.nom_fournisseur : ''
})

// Computed: Combined and sorted data
const combinedData = computed(() => {
    if (!filters.value.fournisseurId) return []
    
    let data = []
    
    // Add bons d'achat (as credit entries)
    bonsAchat.value.forEach(bon => {
        data.push({
            type: 'achat',
            date: bon.date,
            numero: bon.numero_bon,
            client_livre: bon.client_livre,
            ville: bon.ville,
            quantite: parseFloat(bon.total_qte) || 0,
            debit: 0,
            credit: parseFloat(bon.total_ttc) || 0,
            type_reglement: '',
            echeance: bon.echeance,
            banque: '',
            paye: 0,
            cour: 0,
            instance: 0,
            devalide: 0,
            impaye: 0,
            reporte: 0,
            raw: bon
        })
    })
    
    // Add règlements (as debit entries)
    // Only règlements with status 'paye', 'cour', 'instance' count as effective payments (same as historique)
    reglements.value.forEach(reg => {
        const montant = parseFloat(reg.montant) || 0
        const isEffectivePayment = ['paye', 'cour', 'instance'].includes(reg.statut)
        
        // Extract client_livre values from linked bons
        // API returns snake_case: bon_achat (Laravel default)
        const linkedClients = new Set()
        if (reg.lignes && reg.lignes.length > 0) {
            reg.lignes.forEach(ligne => {
                // Check both snake_case and camelCase for compatibility
                const bonAchat = ligne.bon_achat || ligne.bonAchat
                if (bonAchat && bonAchat.client_livre) {
                    linkedClients.add(bonAchat.client_livre)
                }
            })
        }
        
        data.push({
            type: 'reglement',
            date: reg.date_reglement,
            numero: reg.code_reglement,
            client_livre: '-',
            ville: '-',
            quantite: 0,
            debit: isEffectivePayment ? montant : 0, // Only effective payments reduce the balance
            credit: 0,
            type_reglement: reg.type_reglement,
            echeance: reg.date_encaissement,
            banque: reg.banque,
            paye: reg.statut === 'paye' ? montant : 0,
            cour: reg.statut === 'cour' ? montant : 0,
            instance: reg.statut === 'instance' ? montant : 0,
            devalide: reg.statut === 'devalide' ? montant : 0,
            impaye: reg.statut === 'impaye' ? montant : 0,
            reporte: reg.statut === 'reporte' ? montant : 0,
            linkedClients: Array.from(linkedClients), // Store linked clients for filtering
            raw: reg
        })
    })
    
    // Sort by date
    data.sort((a, b) => new Date(a.date) - new Date(b.date))
    
    // Calculate cumulative solde (Credit - Debit)
    let solde = 0
    data.forEach(row => {
        solde = solde + row.credit - row.debit
        row.solde_cumule = solde
    })
    
    return data
})

// Computed: Filtered data
const filteredData = computed(() => {
    let data = combinedData.value
    
    // Filter by date range
    if (filters.value.dateDebut) {
        data = data.filter(row => row.date >= filters.value.dateDebut)
    }
    if (filters.value.dateFin) {
        data = data.filter(row => row.date <= filters.value.dateFin)
    }
    
    // Filter by client livré
    if (filters.value.clientLivre) {
        data = data.filter(row => {
            if (row.type === 'achat') {
                // For achats, filter by client_livre directly
                return row.client_livre === filters.value.clientLivre
            } else if (row.type === 'reglement') {
                // For règlements, filter by linked bons' client_livre
                return row.linkedClients && row.linkedClients.includes(filters.value.clientLivre)
            }
            return false
        })
    }
    
    return data
})

// Computed: Totals
const totals = computed(() => {
    const data = filteredData.value
    
    const credit = data.reduce((sum, row) => sum + (row.credit || 0), 0)
    const debit = data.reduce((sum, row) => sum + (row.debit || 0), 0)
    const paye = data.reduce((sum, row) => sum + (row.paye || 0), 0)
    const cour = data.reduce((sum, row) => sum + (row.cour || 0), 0)
    const instance = data.reduce((sum, row) => sum + (row.instance || 0), 0)
    const devalide = data.reduce((sum, row) => sum + (row.devalide || 0), 0)
    const impaye = data.reduce((sum, row) => sum + (row.impaye || 0), 0)
    const reporte = data.reduce((sum, row) => sum + (row.reporte || 0), 0)
    
    // Use the pre-calculated solde from historique API (same as Historique achats page)
    // This sums individual bon soldes where solde = max(TTC - montant_paye, 0)
    // Filter by client_livre if selected
    let historiqueFiltered = bonsHistorique.value
    if (filters.value.clientLivre) {
        historiqueFiltered = historiqueFiltered.filter(bon => bon.client_livre === filters.value.clientLivre)
    }
    // Filter by date range
    if (filters.value.dateDebut) {
        historiqueFiltered = historiqueFiltered.filter(bon => bon.date >= filters.value.dateDebut)
    }
    if (filters.value.dateFin) {
        historiqueFiltered = historiqueFiltered.filter(bon => bon.date <= filters.value.dateFin)
    }
    
    const solde = historiqueFiltered.reduce((sum, bon) => sum + (parseFloat(bon.solde) || 0), 0)
    const reliquat = historiqueFiltered.reduce((sum, bon) => sum + (parseFloat(bon.reliquat) || 0), 0)
    
    return {
        quantite: data.reduce((sum, row) => sum + (row.quantite || 0), 0),
        debit: debit,
        credit: credit,
        solde: solde,
        reliquat: reliquat,
        paye: paye,
        devalide: devalide,
        impaye: impaye,
        reporte: reporte
    }
})

// Pagination
const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredData.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / itemsPerPage.value) || 1
})

const displayedPages = computed(() => {
    const pages = []
    const total = totalPages.value
    const current = currentPage.value
    
    if (total <= 5) {
        for (let i = 1; i <= total; i++) pages.push(i)
    } else {
        if (current <= 3) {
            pages.push(1, 2, 3, 4, 5)
        } else if (current >= total - 2) {
            pages.push(total - 4, total - 3, total - 2, total - 1, total)
        } else {
            pages.push(current - 2, current - 1, current, current + 1, current + 2)
        }
    }
    
    return pages
})

const paginationStart = computed(() => {
    if (filteredData.value.length === 0) return 0
    return (currentPage.value - 1) * itemsPerPage.value + 1
})

const paginationEnd = computed(() => {
    const end = currentPage.value * itemsPerPage.value
    return Math.min(end, filteredData.value.length)
})

// Methods
const loadFournisseurs = async () => {
    try {
        const response = await fetch('/api/fournisseurs')
        if (response.ok) {
            fournisseurs.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement des fournisseurs:', error)
    }
}

const loadDataForFournisseur = async () => {
    if (!filters.value.fournisseurId) {
        bonsAchat.value = []
        bonsHistorique.value = []
        reglements.value = []
        clientsLivres.value = []
        return
    }
    
    loading.value = true
    try {
        // Load bons d'achat
        const bonsResponse = await fetch(`/api/bon-achat-fournisseur?fournisseur_id=${filters.value.fournisseurId}`)
        if (bonsResponse.ok) {
            const bonsData = await bonsResponse.json()
            bonsAchat.value = bonsData.filter(bon => bon.statut === 'valide')
            
            // Extract unique clients livrés
            const clients = new Set()
            bonsAchat.value.forEach(bon => {
                if (bon.client_livre) clients.add(bon.client_livre)
            })
            clientsLivres.value = Array.from(clients).sort()
        }
        
        // Load historique data (with pre-calculated solde/reliquat/montant_paye)
        const historiqueResponse = await fetch('/api/bon-achat-fournisseur/historique')
        if (historiqueResponse.ok) {
            const historiqueData = await historiqueResponse.json()
            // Filter by fournisseur
            bonsHistorique.value = historiqueData.filter(bon => bon.fournisseur_id == filters.value.fournisseurId)
        }
        
        // Load règlements
        const reglementsResponse = await fetch('/api/reglements-fournisseurs')
        if (reglementsResponse.ok) {
            const reglementsData = await reglementsResponse.json()
            reglements.value = reglementsData.filter(reg => reg.fournisseur_id == filters.value.fournisseurId)
        }
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error)
    } finally {
        loading.value = false
    }
}

const onFournisseurChange = () => {
    currentPage.value = 1
    filters.value.clientLivre = ''
    loadDataForFournisseur()
}

const resetFilters = () => {
    const currentYear = new Date().getFullYear()
    filters.value = {
        dateDebut: new Date(currentYear, 0, 1).toISOString().split('T')[0],
        dateFin: new Date(currentYear, 11, 31).toISOString().split('T')[0],
        periodePredefinee: '',
        fournisseurId: '',
        clientLivre: ''
    }
    currentPage.value = 1
    bonsAchat.value = []
    bonsHistorique.value = []
    reglements.value = []
    clientsLivres.value = []
}

const applyPredefinedPeriod = () => {
    const today = new Date()
    const period = filters.value.periodePredefinee
    
    switch (period) {
        case 'today':
            filters.value.dateDebut = today.toISOString().split('T')[0]
            filters.value.dateFin = today.toISOString().split('T')[0]
            break
        case 'week':
            const weekStart = new Date(today)
            weekStart.setDate(today.getDate() - today.getDay())
            filters.value.dateDebut = weekStart.toISOString().split('T')[0]
            filters.value.dateFin = today.toISOString().split('T')[0]
            break
        case 'month':
            filters.value.dateDebut = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0]
            filters.value.dateFin = today.toISOString().split('T')[0]
            break
        case 'quarter':
            const quarter = Math.floor(today.getMonth() / 3)
            filters.value.dateDebut = new Date(today.getFullYear(), quarter * 3, 1).toISOString().split('T')[0]
            filters.value.dateFin = today.toISOString().split('T')[0]
            break
        case 'year':
            filters.value.dateDebut = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0]
            filters.value.dateFin = new Date(today.getFullYear(), 11, 31).toISOString().split('T')[0]
            break
        case 'all':
            filters.value.dateDebut = ''
            filters.value.dateFin = ''
            break
    }
}

const goToPage = (page) => {
    currentPage.value = page
}

const previousPage = () => {
    if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++
}

// Formatting
const formatCurrency = (value) => {
    if (value === null || value === undefined) return '00.00'
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value)
}

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0'
    return new Intl.NumberFormat('fr-FR').format(value)
}

const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    if (isNaN(d.getTime())) return 'Invalid Date'
    return d.toLocaleDateString('fr-FR')
}

const getSoldeClass = (solde) => {
    if (solde > 0) return 'text-green-400'
    if (solde < 0) return 'text-red-400'
    return 'text-gray-300'
}

// Print & Export
const printReleve = () => {
    const printWindow = window.open('', '_blank')
    
    const fournisseur = fournisseurs.value.find(f => f.id == filters.value.fournisseurId)
    const fournisseurName = fournisseur ? fournisseur.nom_fournisseur : 'N/A'
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Relevé Compte Fournisseur - ${fournisseurName}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 11px; }
                h1 { text-align: center; color: #1e40af; margin-bottom: 10px; font-size: 18px; }
                .info { margin-bottom: 15px; padding: 10px; background: #f3f4f6; border-radius: 8px; }
                .info p { margin: 3px 0; }
                .summary { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
                .summary-card { padding: 10px 15px; border-radius: 6px; text-align: center; min-width: 100px; color: white; }
                .summary-card.green { background: #16a34a; }
                .summary-card.blue { background: #2563eb; }
                .summary-card.red { background: #dc2626; }
                .summary-card.yellow { background: #eab308; color: #1a1a1a; }
                .summary-card h4 { margin: 0 0 5px 0; font-size: 10px; }
                .summary-card p { margin: 0; font-size: 14px; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; font-size: 10px; }
                th { background-color: #374151; color: white; }
                tr:nth-child(even) { background-color: #f9fafb; }
                .text-right { text-align: right; }
                .text-red { color: #dc2626; }
                .text-green { color: #16a34a; }
                @media print { button { display: none; } }
            </style>
        </head>
        <body>
            <h1>Relevé Compte Fournisseur</h1>
            
            <div class="info">
                <p><strong>Fournisseur:</strong> ${fournisseurName}</p>
                <p><strong>Période:</strong> ${filters.value.dateDebut ? formatDate(filters.value.dateDebut) : 'Début'} - ${filters.value.dateFin ? formatDate(filters.value.dateFin) : 'Fin'}</p>
                ${filters.value.clientLivre ? `<p><strong>Client Livré:</strong> ${filters.value.clientLivre}</p>` : ''}
            </div>
            
            <div class="summary">
                <div class="summary-card green"><h4>Crédit</h4><p>${formatCurrency(totals.value.credit)}</p></div>
                <div class="summary-card blue"><h4>Débit</h4><p>${formatCurrency(totals.value.debit)}</p></div>
                <div class="summary-card red"><h4>Solde</h4><p>${formatCurrency(totals.value.solde)}</p></div>
                <div class="summary-card yellow"><h4>Quantité</h4><p>${formatNumber(totals.value.quantite)}</p></div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Pièce</th>
                        <th>Date</th>
                        <th>N° Bon</th>
                        <th>Client</th>
                        <th>Ville</th>
                        <th class="text-right">Qté</th>
                        <th class="text-right">Débit</th>
                        <th class="text-right">Crédit</th>
                        <th class="text-right">Solde</th>
                        <th>Règlement</th>
                        <th>Echéance</th>
                        <th>Banque</th>
                        <th class="text-right">Payé</th>
                        <th class="text-right">Dévalidé</th>
                        <th class="text-right">Impayé</th>
                        <th class="text-right">Reporté</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredData.value.map(row => `
                        <tr>
                            <td>${row.type === 'achat' ? 'Achat' : 'Rég'}</td>
                            <td>${formatDate(row.date)}</td>
                            <td>${row.numero || '-'}</td>
                            <td>${row.client_livre || '-'}</td>
                            <td>${row.ville || '-'}</td>
                            <td class="text-right">${row.quantite ? formatNumber(row.quantite) : ''}</td>
                            <td class="text-right">${row.debit ? formatCurrency(row.debit) : ''}</td>
                            <td class="text-right">${row.credit ? formatCurrency(row.credit) : ''}</td>
                            <td class="text-right ${row.solde_cumule >= 0 ? 'text-green' : 'text-red'}">${formatCurrency(row.solde_cumule)}</td>
                            <td>${row.type_reglement || ''}</td>
                            <td>${row.echeance ? formatDate(row.echeance) : ''}</td>
                            <td>${row.banque || ''}</td>
                            <td class="text-right">${row.paye ? formatCurrency(row.paye) : ''}</td>
                            <td class="text-right text-red">${row.devalide ? formatCurrency(row.devalide) : ''}</td>
                            <td class="text-right text-red">${row.impaye ? formatCurrency(row.impaye) : ''}</td>
                            <td class="text-right">${row.reporte ? formatCurrency(row.reporte) : ''}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #1e40af; color: white; border: none; cursor: pointer; border-radius: 6px;">
                Imprimer
            </button>
        </body>
        </html>
    `
    
    printWindow.document.write(htmlContent)
    printWindow.document.close()
}

const exportPDF = () => {
    printReleve()
}

const sendPDF = () => {
    alert('Fonctionnalité d\'envoi PDF en cours de développement')
}

const closeReleve = () => {
    filters.value.fournisseurId = ''
    filters.value.clientLivre = ''
    filters.value.dateDebut = new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0]
    filters.value.dateFin = new Date(new Date().getFullYear(), 11, 31).toISOString().split('T')[0]
    filters.value.periodePredefinee = ''
    bonsAchat.value = []
    bonsHistorique.value = []
    reglements.value = []
}

// Watch for filter changes
watch(() => filters.value.dateDebut, () => { currentPage.value = 1 })
watch(() => filters.value.dateFin, () => { currentPage.value = 1 })
watch(() => filters.value.clientLivre, () => { currentPage.value = 1 })

// Initialize
onMounted(() => {
    loadFournisseurs()
})
</script>
