<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <!-- Filters Section -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Month Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois</label>
                    <div class="relative">
                        <select 
                            v-model="filters.mois"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white appearance-none cursor-pointer focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Sélectionner un mois</option>
                            <option v-for="month in months" :key="month.value" :value="month.value">
                                {{ month.label }}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Code Fournisseur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code Fournisseur</label>
                    <input 
                        type="text" 
                        v-model="filters.codeFournisseur"
                        placeholder="Rechercher par code"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <!-- Nom Fournisseur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom Fournisseur</label>
                    <input 
                        type="text" 
                        v-model="filters.nomFournisseur"
                        placeholder="Rechercher par nom"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <!-- Search Button -->
                <div>
                    <button 
                        @click="rechercher" 
                        class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center justify-center"
                    >
                        Rechercher
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Résultats</h3>
                
                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button 
                        @click="imprimer" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimer
                    </button>
                    <button 
                        @click="exporterPDF" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Exporter PDF
                    </button>
                    <button 
                        @click="showReliquatOnly = !showReliquatOnly" 
                        :class="showReliquatOnly ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
                        class="px-4 py-2 border rounded-lg hover:opacity-90 transition-colors flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Reliquat
                    </button>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">N° BON</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">DATE</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">CODE FOURNISSEUR</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">NOM FOURNISSEUR</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">CLIENT LIVRÉ</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">VILLE</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">QTE</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">MONTANT TTC</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">MONTANT PAYÉ</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">SOLDE</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">RELIQUAT</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="11" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <svg class="animate-spin h-6 w-6 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="mt-2">Chargement...</p>
                            </td>
                        </tr>
                        <tr v-else-if="filteredBons.length === 0">
                            <td colspan="11" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Aucun bon d'achat trouvé
                            </td>
                        </tr>
                        <tr v-else v-for="bon in paginatedBons" :key="bon.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <a 
                                    href="#" 
                                    @click.prevent="viewBon(bon)"
                                    class="text-blue-600 dark:text-blue-400 hover:underline font-medium"
                                >
                                    {{ bon.numero_bon }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ formatDate(bon.date) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ bon.fournisseur?.code_fournisseur || '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ bon.fournisseur?.nom_fournisseur || '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ bon.client_livre || '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ bon.ville || '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white text-center">
                                {{ bon.total_qte }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">
                                {{ formatNumber(bon.total_ttc) }} MAD
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">
                                {{ formatNumber(bon.montant_paye) }} MAD
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold"
                                :class="getSoldeClass(getSolde(bon))">
                                {{ formatNumber(getSolde(bon)) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold"
                                :class="getReliquatClass(getReliquat(bon))">
                                {{ formatNumber(getReliquat(bon)) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary Footer -->
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Montant Total TTC :</span>
                        <span class="ml-2 text-lg font-bold text-gray-900 dark:text-white">{{ formatNumber(totaux.montantTTC) }} MAD</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Solde TTC :</span>
                        <span class="ml-2 text-lg font-bold text-green-600">{{ formatNumber(totaux.solde) }} MAD</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Reliquat TTC :</span>
                        <span class="ml-2 text-lg font-bold text-blue-600">{{ formatNumber(totaux.reliquat) }} MAD</span>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Affichage de {{ paginationStart }}-{{ paginationEnd }} sur {{ filteredBons.length }}
                </div>
                <div class="flex items-center space-x-1">
                    <button 
                        @click="currentPage--" 
                        :disabled="currentPage === 1"
                        class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                    >
                        Précédent
                    </button>
                    <button 
                        v-for="page in visiblePages" 
                        :key="page"
                        @click="currentPage = page"
                        :class="currentPage === page 
                            ? 'bg-blue-600 text-white border-blue-600' 
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'"
                        class="px-3 py-1 text-sm border rounded min-w-[32px]"
                    >
                        {{ page }}
                    </button>
                    <button 
                        @click="currentPage++" 
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300"
                    >
                        Suivant
                    </button>
                </div>
            </div>
        </div>

        <!-- Bon Details Modal -->
        <div v-if="showBonModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeBonModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Détails du bon {{ selectedBon?.numero_bon }}
                    </h3>
                    <button @click="closeBonModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6" v-if="selectedBon">
                    <!-- Bon Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(selectedBon.date) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fournisseur</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ selectedBon.fournisseur?.nom_fournisseur || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Client livré</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ selectedBon.client_livre || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ville</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ selectedBon.ville || '-' }}</p>
                        </div>
                    </div>

                    <!-- Articles Table -->
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mb-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300">Référence</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300">Désignation</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-300">Qté</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-300">Prix Unit.</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-300">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="article in selectedBon.articles" :key="article.id">
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ article.ref_article }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ article.designation_article }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-center">{{ article.qte }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-right">{{ formatNumber(article.prix_unitaire_ttc) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-right font-semibold">{{ formatNumber(article.total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="flex justify-end">
                        <div class="w-64 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Total TTC:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ formatNumber(selectedBon.total_ttc) }} MAD</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Montant payé:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ formatNumber(selectedBon.montant_paye) }} MAD</span>
                            </div>
                            <div class="flex justify-between text-sm border-t pt-2">
                                <span class="text-gray-600 dark:text-gray-400">Solde:</span>
                                <span class="font-bold" :class="getSoldeClass(getSolde(selectedBon))">{{ formatNumber(getSolde(selectedBon)) }} MAD</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// State
const bons = ref([])
const loading = ref(false)
const showReliquatOnly = ref(false)
const showBonModal = ref(false)
const selectedBon = ref(null)
const currentPage = ref(1)
const perPage = ref(5)

// Filters
const filters = ref({
    mois: '',
    codeFournisseur: '',
    nomFournisseur: ''
})

// Months for filter
const months = computed(() => {
    const currentYear = new Date().getFullYear()
    return [
        { value: `${currentYear}-01`, label: 'Janvier ' + currentYear },
        { value: `${currentYear}-02`, label: 'Février ' + currentYear },
        { value: `${currentYear}-03`, label: 'Mars ' + currentYear },
        { value: `${currentYear}-04`, label: 'Avril ' + currentYear },
        { value: `${currentYear}-05`, label: 'Mai ' + currentYear },
        { value: `${currentYear}-06`, label: 'Juin ' + currentYear },
        { value: `${currentYear}-07`, label: 'Juillet ' + currentYear },
        { value: `${currentYear}-08`, label: 'Août ' + currentYear },
        { value: `${currentYear}-09`, label: 'Septembre ' + currentYear },
        { value: `${currentYear}-10`, label: 'Octobre ' + currentYear },
        { value: `${currentYear}-11`, label: 'Novembre ' + currentYear },
        { value: `${currentYear}-12`, label: 'Décembre ' + currentYear },
    ]
})

// Computed
const filteredBons = computed(() => {
    let result = bons.value

    // Filter by month
    if (filters.value.mois) {
        result = result.filter(bon => bon.date && bon.date.startsWith(filters.value.mois))
    }

    // Filter by supplier code
    if (filters.value.codeFournisseur) {
        const search = filters.value.codeFournisseur.toLowerCase()
        result = result.filter(bon => 
            bon.fournisseur?.code_fournisseur?.toLowerCase().includes(search)
        )
    }

    // Filter by supplier name
    if (filters.value.nomFournisseur) {
        const search = filters.value.nomFournisseur.toLowerCase()
        result = result.filter(bon => 
            bon.fournisseur?.nom_fournisseur?.toLowerCase().includes(search)
        )
    }

    // Filter by reliquat only (computed from montant payé vs TTC)
    if (showReliquatOnly.value) {
        result = result.filter(bon => getReliquat(bon) > 0)
    }

    return result
})

const totalPages = computed(() => Math.ceil(filteredBons.value.length / perPage.value) || 1)

const paginatedBons = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    return filteredBons.value.slice(start, start + perPage.value)
})

const paginationStart = computed(() => {
    if (filteredBons.value.length === 0) return 0
    return (currentPage.value - 1) * perPage.value + 1
})

const paginationEnd = computed(() => {
    return Math.min(currentPage.value * perPage.value, filteredBons.value.length)
})

const visiblePages = computed(() => {
    const pages = []
    const total = totalPages.value
    const current = currentPage.value
    
    if (total <= 5) {
        for (let i = 1; i <= total; i++) pages.push(i)
    } else {
        if (current <= 3) {
            pages.push(1, 2, 3, 4, 5)
        } else if (current >= total - 2) {
            for (let i = total - 4; i <= total; i++) pages.push(i)
        } else {
            for (let i = current - 2; i <= current + 2; i++) pages.push(i)
        }
    }
    
    return pages
})

const totaux = computed(() => {
    return filteredBons.value.reduce((acc, bon) => {
        acc.montantTTC += parseFloat(bon.total_ttc) || 0
        acc.solde += getSolde(bon)
        acc.reliquat += getReliquat(bon)
        return acc
    }, { montantTTC: 0, solde: 0, reliquat: 0 })
})

// Methods
const loadBons = async () => {
    loading.value = true
    try {
        const response = await fetch('/api/bon-achat-fournisseur/historique')
        if (response.ok) {
            bons.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error)
    } finally {
        loading.value = false
    }
}

const rechercher = () => {
    currentPage.value = 1
    // Filters are reactive, so the computed will update automatically
}

const viewBon = (bon) => {
    selectedBon.value = bon
    showBonModal.value = true
}

const closeBonModal = () => {
    showBonModal.value = false
    selectedBon.value = null
}

const imprimer = () => {
    const printWindow = window.open('', '_blank')
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Historique Achats</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { text-align: center; color: #333; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
                th { background-color: #2563eb; color: white; }
                tr:nth-child(even) { background-color: #f2f2f2; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .text-green { color: #16a34a; }
                .text-red { color: #dc2626; }
                .text-blue { color: #2563eb; }
                .summary { margin-top: 20px; padding: 10px; background: #f8f9fa; }
                @media print { button { display: none; } }
            </style>
        </head>
        <body>
            <h1>Historique des Achats</h1>
            <p>Date d'impression: ${new Date().toLocaleDateString('fr-FR')}</p>
            <table>
                <thead>
                    <tr>
                        <th>N° BON</th>
                        <th>DATE</th>
                        <th>CODE FOURNISSEUR</th>
                        <th>NOM FOURNISSEUR</th>
                        <th>CLIENT LIVRÉ</th>
                        <th>VILLE</th>
                        <th class="text-center">QTE</th>
                        <th class="text-right">MONTANT TTC</th>
                        <th class="text-right">MONTANT PAYÉ</th>
                        <th class="text-right">SOLDE</th>
                        <th class="text-right">RELIQUAT</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredBons.value.map(bon => `
                        <tr>
                            <td>${bon.numero_bon}</td>
                            <td>${formatDate(bon.date)}</td>
                            <td>${bon.fournisseur?.code_fournisseur || '-'}</td>
                            <td>${bon.fournisseur?.nom_fournisseur || '-'}</td>
                            <td>${bon.client_livre || '-'}</td>
                            <td>${bon.ville || '-'}</td>
                            <td class="text-center">${bon.total_qte}</td>
                            <td class="text-right">${formatNumber(bon.total_ttc)} MAD</td>
                            <td class="text-right">${formatNumber(bon.montant_paye)} MAD</td>
                            <td class="text-right ${getSolde(bon) > 0 ? 'text-red' : 'text-green'}">${formatNumber(getSolde(bon))}</td>
                            <td class="text-right ${getReliquat(bon) > 0 ? 'text-blue' : 'text-green'}">${formatNumber(getReliquat(bon))}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            <div class="summary">
                <strong>Montant Total TTC:</strong> ${formatNumber(totaux.value.montantTTC)} MAD | 
                <strong>Solde TTC:</strong> <span class="text-green">${formatNumber(totaux.value.solde)} MAD</span> | 
                <strong>Reliquat TTC:</strong> <span class="text-blue">${formatNumber(totaux.value.reliquat)} MAD</span>
            </div>
            <br>
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #2563eb; color: white; border: none; cursor: pointer; border-radius: 4px;">
                Imprimer
            </button>
        </body>
        </html>
    `
    
    printWindow.document.write(htmlContent)
    printWindow.document.close()
}

const exporterPDF = () => {
    // Same as imprimer but with PDF focus
    imprimer()
}

// Formatting
// Compute SOLDE and RELIQUAT based on Montant TTC and Montant Payé
// SOLDE: montant restant à payer (TTC > payé) - ce que le client doit encore
// RELIQUAT: trop-perçu (payé > TTC) - excédent de paiement
const getSolde = (bon) => {
    if (!bon) return 0
    const ttc = parseFloat(bon.total_ttc) || 0
    const paye = parseFloat(bon.montant_paye) || 0
    return Math.max(ttc - paye, 0)
}

const getReliquat = (bon) => {
    if (!bon) return 0
    const ttc = parseFloat(bon.total_ttc) || 0
    const paye = parseFloat(bon.montant_paye) || 0
    return Math.max(paye - ttc, 0)
}

const formatNumber = (value) => {
    const num = parseFloat(value) || 0
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num)
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

const getSoldeClass = (solde) => {
    const value = parseFloat(solde) || 0
    if (value === 0) return 'text-green-600 dark:text-green-400'
    return 'text-red-600 dark:text-red-400'
}

const getReliquatClass = (reliquat) => {
    const value = parseFloat(reliquat) || 0
    if (value === 0) return 'text-green-600 dark:text-green-400'
    return 'text-blue-600 dark:text-blue-400'
}

// Initialize
onMounted(() => {
    loadBons()
})
</script>

