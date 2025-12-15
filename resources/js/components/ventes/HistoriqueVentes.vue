<template>
    <div class="space-y-4">
        <!-- Filters Section -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-end gap-4">
                <!-- Date du -->
                <div class="flex-shrink-0" style="width: 200px;">
                    <label class="block text-sm font-medium text-gray-300 mb-2">date du</label>
                    <input 
                        type="date" 
                        v-model="filters.dateFrom"
                        class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <!-- Date au -->
                <div class="flex-shrink-0" style="width: 200px;">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Date au</label>
                    <input 
                        type="date" 
                        v-model="filters.dateTo"
                        class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <!-- Nom -->
                <div class="flex-shrink-0" style="width: 200px;">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Nom</label>
                    <input 
                        type="text" 
                        v-model="filters.nom"
                        placeholder="Nom du client"
                        class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <!-- N° de Bon -->
                <div class="flex-shrink-0" style="width: 200px;">
                    <label class="block text-sm font-medium text-gray-300 mb-2">N° de Bon</label>
                    <input 
                        type="text" 
                        v-model="filters.numeroBon"
                        placeholder="N° de bon"
                        class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <!-- Search Button -->
                <div class="flex-shrink-0">
                    <button 
                        @click="rechercher"
                        class="px-8 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium"
                    >
                        Rechercher
                    </button>
                </div>
                
                <!-- Spacer to push totals to the right -->
                <div class="flex-grow"></div>
                
                <!-- Total TTC -->
                <div class="flex-shrink-0 bg-green-600 rounded-lg px-6 py-2.5 text-center min-w-[140px]">
                    <div class="text-xs font-semibold text-white">Total TTC</div>
                    <div class="text-xl font-bold text-white leading-tight">{{ formatMontantSimple(totals.totalTTC) }}</div>
                    <div class="text-xs font-semibold text-white">MAD</div>
                </div>
                
                <!-- Solde TTC -->
                <div class="flex-shrink-0 bg-red-600 rounded-lg px-6 py-2.5 text-center min-w-[140px]">
                    <div class="text-xs font-semibold text-white">Solde TTC</div>
                    <div class="text-xl font-bold text-white leading-tight">{{ formatMontantSimple(totals.soldeTTC) }}</div>
                    <div class="text-xs font-semibold text-white">MAD</div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-gray-800 rounded-lg shadow-lg">
            <!-- Header with title and actions -->
            <div class="flex justify-between items-center p-4 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-gray-200">Résultats</h3>
                <div class="flex gap-2">
                    <button 
                        @click="imprimer"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg transition-colors duration-200 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimer
                    </button>
                    <button 
                        @click="exporterPDF"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg transition-colors duration-200 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Exporter PDF
                    </button>
                    <button 
                        @click="afficherReliquat"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg transition-colors duration-200"
                    >
                        Reliquat
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">N° BON</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">CODE Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">NOM Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date Livraison</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">VILLE</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">GTE</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">MONTANT TTC</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">MONTANT PAYÉ</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">SOLDE</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">RELIQUAT</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <tr v-if="loading" class="text-center">
                            <td colspan="10" class="px-4 py-8 text-gray-400">
                                <div class="flex justify-center items-center">
                                    <svg class="animate-spin h-5 w-5 text-blue-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Chargement...
                                </div>
                            </td>
                        </tr>
                        <tr v-else-if="ventesFiltered.length === 0" class="text-center">
                            <td colspan="10" class="px-4 py-8 text-gray-400">
                                Aucun résultat trouvé
                            </td>
                        </tr>
                        <tr v-else v-for="vente in ventesPaginated" :key="vente.id" class="hover:bg-gray-750 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-400 font-medium">{{ vente.numero_bon }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">{{ vente.client?.code_client || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">{{ vente.client?.raison_sociale || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">{{ formatDate(vente.date) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">{{ vente.ville_livraison || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300 text-right">{{ vente.total_quantites || 0 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300 text-right">{{ formatMontant(vente.total_general) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300 text-right">{{ formatMontant(vente.montant_paye) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium" :class="getSoldeClass(vente.solde)">
                                {{ formatMontant(vente.solde) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium" :class="getReliquatClass(vente.reliquat)">
                                {{ formatMontant(vente.reliquat) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer with totals and pagination -->
            <div class="bg-gray-900 px-4 py-3 border-t border-gray-700">
                <div class="flex justify-between items-center">
                    <!-- Totals -->
                    <div class="flex gap-6 text-sm">
                        <div>
                            <span class="text-gray-400">Montant Total TTC : </span>
                            <span class="text-white font-bold">{{ formatMontant(totals.totalTTC) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Solde TTC : </span>
                            <span class="text-green-400 font-bold">{{ formatMontant(totals.soldeTTC) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Reliquat TTC : </span>
                            <span class="text-blue-400 font-bold">{{ formatMontant(totals.reliquatTTC) }}</span>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-400">
                            Affichage de {{ paginationStart }}-{{ paginationEnd }} sur {{ ventesFiltered.length }}
                        </span>
                        <div class="flex gap-1 ml-4">
                            <button 
                                @click="previousPage"
                                :disabled="currentPage === 1"
                                class="px-3 py-1 bg-gray-700 text-gray-300 rounded hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Précédent
                            </button>
                            <button 
                                v-for="page in visiblePages" 
                                :key="page"
                                @click="goToPage(page)"
                                :class="[
                                    'px-3 py-1 rounded transition-colors',
                                    page === currentPage 
                                        ? 'bg-blue-600 text-white font-medium' 
                                        : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                                ]"
                            >
                                {{ page }}
                            </button>
                            <button 
                                @click="nextPage"
                                :disabled="currentPage === totalPages"
                                class="px-3 py-1 bg-gray-700 text-gray-300 rounded hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Suivant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// State
const ventes = ref([])
const loading = ref(false)
const filters = ref({
    dateFrom: '',
    dateTo: '',
    nom: '',
    numeroBon: ''
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(25)

// Fetch data
const fetchVentes = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/ventes/historique')
        ventes.value = response.data
    } catch (error) {
        console.error('Error fetching ventes:', error)
        alert('Erreur lors du chargement des données')
    } finally {
        loading.value = false
    }
}

// Computed: Filtered ventes
const ventesFiltered = computed(() => {
    let result = [...ventes.value]
    
    if (filters.value.dateFrom) {
        result = result.filter(v => v.date >= filters.value.dateFrom)
    }
    
    if (filters.value.dateTo) {
        result = result.filter(v => v.date <= filters.value.dateTo)
    }
    
    if (filters.value.nom) {
        const nom = filters.value.nom.toLowerCase()
        result = result.filter(v => 
            v.client?.raison_sociale?.toLowerCase().includes(nom)
        )
    }
    
    if (filters.value.numeroBon) {
        const numero = filters.value.numeroBon.toLowerCase()
        result = result.filter(v => 
            v.numero_bon?.toLowerCase().includes(numero)
        )
    }
    
    return result
})

// Computed: Pagination
const totalPages = computed(() => Math.ceil(ventesFiltered.value.length / itemsPerPage.value))

const ventesPaginated = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return ventesFiltered.value.slice(start, end)
})

const paginationStart = computed(() => {
    if (ventesFiltered.value.length === 0) return 0
    return (currentPage.value - 1) * itemsPerPage.value + 1
})

const paginationEnd = computed(() => {
    const end = currentPage.value * itemsPerPage.value
    return Math.min(end, ventesFiltered.value.length)
})

const visiblePages = computed(() => {
    const pages = []
    const maxVisible = 5
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
    let end = Math.min(totalPages.value, start + maxVisible - 1)
    
    if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1)
    }
    
    for (let i = start; i <= end; i++) {
        pages.push(i)
    }
    
    return pages
})

// Computed: Totals
const totals = computed(() => {
    return ventesFiltered.value.reduce((acc, vente) => {
        acc.totalTTC += parseFloat(vente.total_general || 0)
        acc.soldeTTC += parseFloat(vente.solde || 0)
        acc.reliquatTTC += parseFloat(vente.reliquat || 0)
        return acc
    }, { totalTTC: 0, soldeTTC: 0, reliquatTTC: 0 })
})

// Methods
const rechercher = () => {
    currentPage.value = 1
}

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const goToPage = (page) => {
    currentPage.value = page
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

const formatMontant = (montant) => {
    if (!montant || montant == 0) return '0,00 MAD'
    return parseFloat(montant).toLocaleString('fr-MA', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    }) + ' MAD'
}

const formatMontantSimple = (montant) => {
    if (!montant || montant == 0) return '0,00'
    return parseFloat(montant).toLocaleString('fr-MA', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    })
}

const getSoldeClass = (solde) => {
    const value = parseFloat(solde || 0)
    if (value === 0) return 'text-gray-300'
    if (value > 0) return 'text-red-400'
    return 'text-gray-300'
}

const getReliquatClass = (reliquat) => {
    const value = parseFloat(reliquat || 0)
    if (value === 0) return 'text-green-400'
    return 'text-gray-300'
}

const imprimer = () => {
    window.print()
}

const exporterPDF = () => {
    alert('Fonctionnalité d\'export PDF en cours de développement')
}

const afficherReliquat = () => {
    // Filter to show only items with reliquat > 0
    filters.value = {
        ...filters.value,
        // Could add a reliquat filter here
    }
    alert('Affichage des reliquats uniquement')
}

// Lifecycle
onMounted(() => {
    fetchVentes()
})
</script>

<style scoped>
/* Custom hover effect */
.hover\:bg-gray-750:hover {
    background-color: #2d3748;
}

/* Print styles */
@media print {
    button {
        display: none;
    }
}
</style>

