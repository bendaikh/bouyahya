<template>
    <div class="bg-gray-800 dark:bg-gray-900 rounded-lg shadow-lg flex flex-col h-[calc(100vh-130px)] overflow-hidden">
        <!-- Sticky Top Section -->
        <div class="p-4 pb-4 border-b border-gray-700 flex-none bg-gray-800 dark:bg-gray-900 z-20 space-y-4">
            <!-- Top Filter Row -->
            <div class="flex flex-wrap items-end gap-3">
                <!-- Date de -->
                <div class="min-w-[120px]">
                    <label class="block text-xs text-gray-400 mb-1">Date de</label>
                    <input 
                        type="date" 
                        v-model="filters.dateDebut"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Date au -->
                <div class="min-w-[120px]">
                    <label class="block text-xs text-gray-400 mb-1">Date au</label>
                    <input 
                        type="date" 
                        v-model="filters.dateFin"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Période Prédéfinie -->
                <div class="min-w-[140px]">
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
                
                <!-- Client (Code & Nom) -->
                <div class="min-w-[250px] flex-1">
                    <label class="block text-xs text-gray-400 mb-1">Client (Code ou Nom)</label>
                    <div class="relative">
                        <input 
                            type="text"
                            v-model="clientSearch"
                            list="clients-datalist"
                            @input="handleClientSearch"
                            placeholder="Rechercher par code ou nom..."
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <datalist id="clients-datalist">
                            <option v-for="c in clients" :key="c.id" :value="`${c.code_client || c.id} | ${c.raison_sociale || ''}`"></option>
                        </datalist>
                        <span v-if="clientSearch" @click="clearClientSelection" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </div>
                </div>
                
                <!-- Refresh Button -->
                <div class="flex items-end">
                    <button 
                        @click="refreshData"
                        class="p-2 bg-transparent hover:bg-gray-700 rounded text-cyan-400 transition-colors"
                        title="Actualiser"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
                
                <!-- Close/Reset Button -->
                <div class="flex items-end">
                    <button 
                        @click="resetFilters"
                        class="p-2 bg-transparent hover:bg-gray-700 rounded text-red-400 transition-colors"
                        title="Réinitialiser"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Status Filter Buttons -->
                <button 
                    @click="toggleStatusFilter('impaye')"
                    :class="[
                        'px-6 py-2 rounded-full text-sm font-semibold transition-colors',
                        statusFilters.impaye 
                            ? 'bg-yellow-500 text-gray-900' 
                            : 'bg-yellow-500/80 text-gray-900 hover:bg-yellow-500'
                    ]"
                >
                    Impayé
                </button>
                <button 
                    @click="toggleStatusFilter('devalide')"
                    :class="[
                        'px-6 py-2 rounded-full text-sm font-semibold transition-colors',
                        statusFilters.devalide 
                            ? 'bg-red-600 text-white' 
                            : 'bg-red-600/80 text-white hover:bg-red-600'
                    ]"
                >
                    Dévalidé
                </button>
                <button 
                    @click="toggleStatusFilter('reporte')"
                    :class="[
                        'px-6 py-2 rounded-full text-sm font-semibold transition-colors',
                        statusFilters.reporte 
                            ? 'bg-blue-500 text-white' 
                            : 'bg-blue-500/80 text-white hover:bg-blue-500'
                    ]"
                >
                    Reporté
                </button>
            </div>

            <!-- Summary Cards and Export Buttons Row -->
            <div class="flex flex-wrap gap-3 items-stretch">
                <!-- Débit (Green) -->
                <div class="bg-green-600 rounded-lg px-6 py-3 min-w-[180px] flex items-center gap-3 border-2 border-green-500">
                    <div>
                        <p class="text-sm font-semibold text-green-100">Débit</p>
                        <p class="text-2xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.debit) }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-auto">
                        <span class="text-4xl">💵</span>
                    </div>
                </div>
                
                <!-- Crédit (Blue) -->
                <div class="bg-blue-700 rounded-lg px-6 py-3 min-w-[180px] flex items-center gap-3 border-2 border-blue-500">
                    <div>
                        <p class="text-sm font-semibold text-blue-100">Crédit</p>
                        <p class="text-2xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.credit) }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-auto">
                        <span class="text-4xl">🏛️</span>
                    </div>
                </div>
                
                <!-- Solde (Red) -->
                <div class="bg-red-700 rounded-lg px-6 py-3 min-w-[180px] flex items-center gap-3 border-2 border-red-500">
                    <div>
                        <p class="text-sm font-semibold text-red-100">Solde</p>
                        <p class="text-2xl font-bold text-white whitespace-nowrap">{{ formatCurrency(totals.solde) }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-auto">
                        <span class="text-4xl">⚖️</span>
                    </div>
                </div>
                
                <!-- Quantité (Yellow) -->
                <div class="bg-yellow-500 rounded-lg px-6 py-3 min-w-[180px] flex items-center gap-3 border-2 border-yellow-400">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Quantité</p>
                        <p class="text-2xl font-bold text-gray-900 whitespace-nowrap">{{ formatNumber(totals.quantite) }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-auto">
                        <span class="text-4xl">🛒</span>
                    </div>
                </div>

                <!-- Export/Print Buttons -->
                <div class="flex items-center gap-2 ml-auto">
                    <button 
                        @click="printReleve"
                        class="px-4 py-2 bg-blue-600 border border-blue-400 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                    >
                        Imprimer
                    </button>
                    <button 
                        @click="exportPDF"
                        class="px-4 py-2 bg-cyan-600 border border-cyan-400 text-white rounded-lg hover:bg-cyan-700 transition-colors text-sm font-medium"
                    >
                        PDF
                    </button>
                    <button 
                        @click="exportExcel"
                        class="px-4 py-2 bg-blue-500 border border-blue-400 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium"
                    >
                        Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Scrollable Content Section -->
        <div class="flex-1 overflow-hidden bg-gray-900">
            <div class="h-full">
                <div class="overflow-x-auto overflow-y-auto relative h-full bg-gray-800">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-700 sticky top-0 z-10 shadow-sm border-b border-gray-600">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Pièce</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Date</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">N°</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Nom Client</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Ville Livraison</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 tracking-wider">Quantité</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 tracking-wider">Débit</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 tracking-wider">Crédit</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 tracking-wider">Solde</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Règlement</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">N° Chèque</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Nom Tirée</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Banque</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 tracking-wider">Echéance</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 tracking-wider">Payé</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-yellow-400 tracking-wider">Impayé</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-red-400 tracking-wider">Dévalidé</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-blue-400 tracking-wider">Reporté</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="18" class="px-4 py-8 text-center text-gray-400">
                                <div class="flex justify-center items-center">
                                    <svg class="animate-spin h-6 w-6 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Chargement...
                                </div>
                            </td>
                        </tr>
                        <!-- Placeholder row when no client is selected -->
                        <tr v-else-if="!filters.clientId" class="bg-gray-800">
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400">Livraison</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400">... / ... / ...</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400">BL-...</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                        </tr>
                        <tr v-else-if="filteredData.length === 0" class="bg-gray-800">
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400">Livraison</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400">... / ... / ...</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400">BL-...</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400"></td>
                        </tr>
                        <template v-else>
                            <tr v-for="(row, index) in paginatedData" :key="index" 
                                class="bg-gray-800 hover:bg-gray-700/50 transition-colors"
                            >
                                <!-- Pièce -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.type === 'livraison' ? 'Livraison' : 'Règlement' }}</td>
                                <!-- Date -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ formatDate(row.date) }}</td>
                                <!-- N° -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.numero || '-' }}</td>
                                <!-- Nom Client -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.nom_client || '-' }}</td>
                                <!-- Ville Livraison -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.ville || '-' }}</td>
                                <!-- Quantité -->
                                <td class="px-3 py-2 whitespace-nowrap text-center text-gray-300">{{ row.quantite ? formatNumber(row.quantite) : '' }}</td>
                                <!-- Débit -->
                                <td class="px-3 py-2 whitespace-nowrap text-center text-gray-300">{{ row.debit ? formatCurrency(row.debit) : '' }}</td>
                                <!-- Crédit -->
                                <td class="px-3 py-2 whitespace-nowrap text-center text-gray-300">{{ row.credit ? formatCurrency(row.credit) : '' }}</td>
                                <!-- Solde (cumulative) -->
                                <td class="px-3 py-2 whitespace-nowrap text-center font-medium" :class="getSoldeClass(row.solde_cumule)">
                                    {{ formatCurrency(row.solde_cumule) }}
                                </td>
                                <!-- Règlement -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.type_reglement || '' }}</td>
                                <!-- N° Chèque -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.numero_piece || '' }}</td>
                                <!-- Nom Tirée -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.nom_beneficiaire || '' }}</td>
                                <!-- Banque -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.banque || '' }}</td>
                                <!-- Echéance -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ row.echeance ? formatDate(row.echeance) : '' }}</td>
                                <!-- Payé -->
                                <td class="px-3 py-2 whitespace-nowrap text-center text-green-400">{{ row.paye ? formatCurrency(row.paye) : '' }}</td>
                                <!-- Impayé -->
                                <td class="px-3 py-2 whitespace-nowrap text-center text-yellow-400 font-medium">{{ row.impaye ? formatCurrency(row.impaye) : '' }}</td>
                                <!-- Dévalidé -->
                                <td class="px-3 py-2 whitespace-nowrap text-center text-red-400 font-medium">{{ row.devalide ? formatCurrency(row.devalide) : '' }}</td>
                                <!-- Reporté -->
                                <td class="px-3 py-2 whitespace-nowrap text-center text-blue-400">{{ row.reporte ? formatCurrency(row.reporte) : '' }}</td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="filteredData.length > 0" class="flex justify-between items-center px-4 py-3 border-t border-gray-700 bg-gray-800">
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
                                    ? 'bg-blue-600 border-blue-600 text-white' 
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
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

// State
const loading = ref(false)
const clients = ref([])
const clientSearch = ref('')
const livraisons = ref([])
const reglements = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(15)

// Filters
const filters = ref({
    dateDebut: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0],
    dateFin: new Date(new Date().getFullYear(), 11, 31).toISOString().split('T')[0],
    periodePredefinee: '',
    clientId: ''
})

// Status filters
const statusFilters = ref({
    impaye: false,
    devalide: false,
    reporte: false
})

// Computed: Selected Client Name
const selectedClientName = computed(() => {
    if (!filters.value.clientId) return ''
    const c = clients.value.find(c => c.id == filters.value.clientId)
    return c ? (c.raison_sociale || '') : ''
})

// Computed: Combined and sorted data
const combinedData = computed(() => {
    if (!filters.value.clientId) return []
    
    let data = []
    
    // Add livraisons (as credit entries - client owes us)
    livraisons.value.forEach(liv => {
        data.push({
            type: 'livraison',
            date: liv.date,
            numero: liv.numero_bon || liv.numero,
            nom_client: liv.nom_client || selectedClientName.value,
            ville: liv.ville_livraison || liv.ville,
            quantite: parseFloat(liv.total_qte) || 0,
            debit: 0,
            credit: parseFloat(liv.total_ttc) || 0,
            type_reglement: '',
            echeance: liv.date_echeance || liv.echeance,
            banque: '',
            paye: 0,
            devalide: 0,
            impaye: parseFloat(liv.total_ttc) || 0, // Default to impaye for new livraisons
            reporte: 0,
            raw: liv
        })
    })
    
    // Add règlements (as debit entries - client pays us)
    reglements.value.forEach(reg => {
        const montant = parseFloat(reg.montant) || 0
        const isEffectivePayment = ['paye', 'cour', 'instance'].includes(reg.statut)
        
        data.push({
            type: 'reglement',
            date: reg.date_reglement || reg.date,
            numero: reg.code_reglement || reg.numero,
            nom_client: selectedClientName.value,
            ville: '-',
            quantite: 0,
            debit: isEffectivePayment ? montant : 0,
            credit: 0,
            type_reglement: reg.type_reglement || reg.type,
            numero_piece: reg.numero_piece,
            nom_beneficiaire: reg.nom_tire,
            echeance: reg.date_encaissement || reg.echeance,
            banque: reg.banque || '',
            paye: reg.statut === 'paye' ? montant : 0,
            devalide: reg.statut === 'devalide' ? montant : 0,
            impaye: reg.statut === 'impaye' ? montant : 0,
            reporte: reg.statut === 'reporte' ? montant : 0,
            raw: reg
        })
    })
    
    // Sort by date
    data.sort((a, b) => new Date(a.date) - new Date(b.date))
    
    // Calculate cumulative solde
    let runningSolde = 0
    data.forEach(row => {
        const credit = row.credit || 0
        const debit = row.debit || 0
        runningSolde = runningSolde + credit - debit
        row.solde_cumule = runningSolde
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
    
    // Filter by status
    if (statusFilters.value.impaye) {
        data = data.filter(row => row.impaye > 0)
    }
    if (statusFilters.value.devalide) {
        data = data.filter(row => row.devalide > 0)
    }
    if (statusFilters.value.reporte) {
        data = data.filter(row => row.reporte > 0)
    }
    
    return data
})

// Computed: Totals
const totals = computed(() => {
    const data = filteredData.value
    
    const credit = data.reduce((sum, row) => sum + (row.credit || 0), 0)
    const debit = data.reduce((sum, row) => sum + (row.debit || 0), 0)
    const paye = data.reduce((sum, row) => sum + (row.paye || 0), 0)
    const devalide = data.reduce((sum, row) => sum + (row.devalide || 0), 0)
    const impaye = data.reduce((sum, row) => sum + (row.impaye || 0), 0)
    const reporte = data.reduce((sum, row) => sum + (row.reporte || 0), 0)
    
    // Solde = Credit - Debit
    const solde = credit - debit
    
    return {
        quantite: data.reduce((sum, row) => sum + (row.quantite || 0), 0),
        debit: debit,
        credit: credit,
        solde: solde,
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
const loadClients = async () => {
    try {
        const response = await fetch('/api/clients')
        if (response.ok) {
            clients.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement des clients:', error)
    }
}

const loadDataForClient = async () => {
    if (!filters.value.clientId) {
        livraisons.value = []
        reglements.value = []
        return
    }
    
    loading.value = true
    try {
        // Load livraisons (bon de livraison clients)
        const livResponse = await fetch(`/api/bon-livraison-clients?client_id=${filters.value.clientId}`)
        if (livResponse.ok) {
            const livData = await livResponse.json()
            // Accept different status variations
            livraisons.value = livData.filter(liv => 
                liv.statut === 'valide' || 
                liv.statut === 'Validé' ||
                liv.statut === 'Valide' ||
                liv.statut === 'livre' ||
                liv.statut === 'Livré' ||
                liv.statut === 'Livre'
            )
        }
        
        // Load règlements clients
        const reglementsResponse = await fetch(`/api/reglements-clients?client_id=${filters.value.clientId}`)
        if (reglementsResponse.ok) {
            reglements.value = await reglementsResponse.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error)
    } finally {
        loading.value = false
    }
}

const onClientChange = () => {
    currentPage.value = 1
    loadDataForClient()
}

const handleClientSearch = () => {
    const selected = clients.value.find(c => `${c.code_client || c.id} | ${c.raison_sociale || ''}` === clientSearch.value)
    if (selected) {
        filters.value.clientId = selected.id
        onClientChange()
    } else if (clientSearch.value === '') {
        filters.value.clientId = ''
        onClientChange()
    }
}

const clearClientSelection = () => {
    clientSearch.value = ''
    filters.value.clientId = ''
    onClientChange()
}

const resetFilters = () => {
    const currentYear = new Date().getFullYear()
    filters.value = {
        dateDebut: new Date(currentYear, 0, 1).toISOString().split('T')[0],
        dateFin: new Date(currentYear, 11, 31).toISOString().split('T')[0],
        periodePredefinee: '',
        clientId: ''
    }
    clientSearch.value = ''
    statusFilters.value = {
        impaye: false,
        devalide: false,
        reporte: false
    }
    currentPage.value = 1
    livraisons.value = []
    reglements.value = []
}

const refreshData = () => {
    if (filters.value.clientId) {
        loadDataForClient()
    }
}

const toggleStatusFilter = (status) => {
    statusFilters.value[status] = !statusFilters.value[status]
    currentPage.value = 1
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
    if (!date) return '.../.../...'
    const d = new Date(date)
    if (isNaN(d.getTime())) return '.../.../...'
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
    
    const client = clients.value.find(c => c.id == filters.value.clientId)
    const clientName = client ? (client.nom_client || client.nom || 'N/A') : 'N/A'
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Relevé Compte Client - ${clientName}</title>
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
            <h1>Relevé Compte Client</h1>
            
            <div class="info">
                <p><strong>Client:</strong> ${clientName}</p>
                <p><strong>Période:</strong> ${filters.value.dateDebut ? formatDate(filters.value.dateDebut) : 'Début'} - ${filters.value.dateFin ? formatDate(filters.value.dateFin) : 'Fin'}</p>
            </div>
            
            <div class="summary">
                <div class="summary-card green"><h4>Débit</h4><p>${formatCurrency(totals.value.debit)}</p></div>
                <div class="summary-card blue"><h4>Crédit</h4><p>${formatCurrency(totals.value.credit)}</p></div>
                <div class="summary-card red"><h4>Solde</h4><p>${formatCurrency(totals.value.solde)}</p></div>
                <div class="summary-card yellow"><h4>Quantité</h4><p>${formatNumber(totals.value.quantite)}</p></div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Pièce</th>
                        <th>Date</th>
                        <th>N°</th>
                        <th>Nom Client</th>
                        <th>Ville Livraison</th>
                        <th class="text-right">Qté</th>
                        <th class="text-right">Débit</th>
                        <th class="text-right">Crédit</th>
                        <th class="text-right">Solde</th>
                        <th>Règlement</th>
                        <th>N° Chèque</th>
                        <th>Nom Tirée</th>
                        <th>Banque</th>
                        <th>Echéance</th>
                        <th class="text-right">Payé</th>
                        <th class="text-right">Impayé</th>
                        <th class="text-right">Dévalidé</th>
                        <th class="text-right">Reporté</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredData.value.map(row => `
                        <tr>
                            <td>${row.type === 'livraison' ? 'Livraison' : 'Rég'}</td>
                            <td>${formatDate(row.date)}</td>
                            <td>${row.numero || '-'}</td>
                            <td>${row.nom_client || '-'}</td>
                            <td>${row.ville || '-'}</td>
                            <td class="text-right">${row.quantite ? formatNumber(row.quantite) : ''}</td>
                            <td class="text-right">${row.debit ? formatCurrency(row.debit) : ''}</td>
                            <td class="text-right">${row.credit ? formatCurrency(row.credit) : ''}</td>
                            <td class="text-right ${row.solde_cumule >= 0 ? 'text-green' : 'text-red'}">${formatCurrency(row.solde_cumule)}</td>
                            <td>${row.type_reglement || ''}</td>
                            <td>${row.numero_piece || ''}</td>
                            <td>${row.nom_beneficiaire || ''}</td>
                            <td>${row.banque || ''}</td>
                            <td>${row.echeance ? formatDate(row.echeance) : ''}</td>
                            <td class="text-right">${row.paye ? formatCurrency(row.paye) : ''}</td>
                            <td class="text-right text-red">${row.impaye ? formatCurrency(row.impaye) : ''}</td>
                            <td class="text-right text-red">${row.devalide ? formatCurrency(row.devalide) : ''}</td>
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

const exportExcel = () => {
    alert('Fonctionnalité d\'export Excel en cours de développement')
}

// Watch for filter changes
watch(() => filters.value.dateDebut, () => { currentPage.value = 1 })
watch(() => filters.value.dateFin, () => { currentPage.value = 1 })

// Initialize
onMounted(() => {
    loadClients()
})
</script>

