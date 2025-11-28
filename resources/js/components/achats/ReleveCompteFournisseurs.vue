<template>
    <div class="space-y-6">
        <!-- Filter Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Filtrer et Générer un Relevé</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <!-- Date du -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date du</label>
                    <input 
                        type="date" 
                        v-model="filters.dateFrom"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Date au -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date au</label>
                    <input 
                        type="date" 
                        v-model="filters.dateTo"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Code fournisseur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code fournisseur</label>
                    <input 
                        type="text" 
                        v-model="filters.codeFournisseur"
                        placeholder="Entrer le code"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Nom fournisseur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom fournisseur</label>
                    <input 
                        type="text" 
                        v-model="filters.nomFournisseur"
                        placeholder="Entrer le nom"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Generate Button -->
                <div>
                    <button 
                        @click="generateReleve"
                        class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        Générer
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Résultats du Relevé</h3>
                
                <div class="flex space-x-2">
                    <button 
                        @click="printReleve"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimer
                    </button>
                    <button 
                        @click="exportPDF"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exporter PDF
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code Règlement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type Règlement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N° Règlement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant Règlement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code Fournisseur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom Fournisseur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Banque</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date Encaissement</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex justify-center items-center">
                                    <svg class="animate-spin h-6 w-6 mr-2 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Chargement...
                                </div>
                            </td>
                        </tr>
                        <tr v-else-if="paginatedReglements.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Aucun règlement trouvé. Veuillez générer un relevé.
                            </td>
                        </tr>
                        <tr v-else v-for="reglement in paginatedReglements" :key="reglement.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">{{ reglement.code_reglement }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <span :class="getTypeClass(reglement.type_reglement)" class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ reglement.type_reglement }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.numero_piece || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ formatCurrency(reglement.montant) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ reglement.fournisseur?.code_fournisseur || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.fournisseur?.nom_fournisseur || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ reglement.banque || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(reglement.date_encaissement) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-800">
                    <p class="text-sm text-amber-600 dark:text-amber-400 font-medium mb-1">Montant CHQ TTC</p>
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ formatCurrency(montantCHQ) }}</p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                    <p class="text-sm text-purple-600 dark:text-purple-400 font-medium mb-1">Montant Traite TTC</p>
                    <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ formatCurrency(montantTraite) }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">Montant Virement TTC</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ formatCurrency(montantVirement) }}</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Affichage de {{ paginationStart }}-{{ paginationEnd }} sur {{ filteredReglements.length }}
                </p>
                
                <div class="flex items-center space-x-1">
                    <button 
                        @click="previousPage"
                        :disabled="currentPage === 1"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
                                : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                        ]"
                    >
                        {{ page }}
                    </button>
                    
                    <button 
                        @click="nextPage"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
import { ref, computed, onMounted } from 'vue'

// State
const reglements = ref([])
const loading = ref(false)
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Filters
const filters = ref({
    dateFrom: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0], // Start of year
    dateTo: new Date(new Date().getFullYear(), 11, 31).toISOString().split('T')[0], // End of year
    codeFournisseur: '',
    nomFournisseur: ''
})

// Computed properties
const filteredReglements = computed(() => {
    let filtered = reglements.value

    // Filter by date range
    if (filters.value.dateFrom) {
        filtered = filtered.filter(r => {
            const date = r.date_encaissement || r.date_reglement
            return date >= filters.value.dateFrom
        })
    }
    
    if (filters.value.dateTo) {
        filtered = filtered.filter(r => {
            const date = r.date_encaissement || r.date_reglement
            return date <= filters.value.dateTo
        })
    }

    // Filter by supplier code
    if (filters.value.codeFournisseur) {
        const code = filters.value.codeFournisseur.toLowerCase()
        filtered = filtered.filter(r => 
            r.fournisseur?.code_fournisseur?.toLowerCase().includes(code)
        )
    }

    // Filter by supplier name
    if (filters.value.nomFournisseur) {
        const nom = filters.value.nomFournisseur.toLowerCase()
        filtered = filtered.filter(r => 
            r.fournisseur?.nom_fournisseur?.toLowerCase().includes(nom)
        )
    }

    return filtered
})

const paginatedReglements = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredReglements.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(filteredReglements.value.length / itemsPerPage.value) || 1
})

const displayedPages = computed(() => {
    const pages = []
    const total = totalPages.value
    const current = currentPage.value
    
    if (total <= 5) {
        for (let i = 1; i <= total; i++) pages.push(i)
    } else {
        if (current <= 3) {
            pages.push(1, 2, 3)
        } else if (current >= total - 2) {
            pages.push(total - 2, total - 1, total)
        } else {
            pages.push(current - 1, current, current + 1)
        }
    }
    
    return pages
})

const paginationStart = computed(() => {
    if (filteredReglements.value.length === 0) return 0
    return (currentPage.value - 1) * itemsPerPage.value + 1
})

const paginationEnd = computed(() => {
    const end = currentPage.value * itemsPerPage.value
    return Math.min(end, filteredReglements.value.length)
})

// Summary calculations
const montantCHQ = computed(() => {
    return filteredReglements.value
        .filter(r => r.type_reglement === 'Chèque')
        .reduce((sum, r) => sum + parseFloat(r.montant || 0), 0)
})

const montantTraite = computed(() => {
    return filteredReglements.value
        .filter(r => r.type_reglement === 'Traite')
        .reduce((sum, r) => sum + parseFloat(r.montant || 0), 0)
})

const montantVirement = computed(() => {
    return filteredReglements.value
        .filter(r => r.type_reglement === 'Virement')
        .reduce((sum, r) => sum + parseFloat(r.montant || 0), 0)
})

// Methods
const loadReglements = async () => {
    loading.value = true
    try {
        const response = await fetch('/api/reglements-fournisseurs')
        if (response.ok) {
            reglements.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error)
    } finally {
        loading.value = false
    }
}

const generateReleve = () => {
    currentPage.value = 1
    // The filtering is done in computed, so just reset the page
}

const goToPage = (page) => {
    currentPage.value = page
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

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-MA', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0) + ' DH'
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

const getTypeClass = (type) => {
    switch (type) {
        case 'Virement':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'
        case 'Chèque':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300'
        case 'Traite':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300'
        case 'Carte Bancaire':
            return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'
        case 'Espèces':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
}

const printReleve = () => {
    const printWindow = window.open('', '_blank')
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Relevé Compte Fournisseurs</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
                h1 { text-align: center; color: #1e40af; margin-bottom: 20px; }
                .filters { margin-bottom: 20px; padding: 10px; background: #f3f4f6; border-radius: 8px; }
                .filters p { margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
                th { background-color: #1e40af; color: white; font-size: 11px; }
                tr:nth-child(even) { background-color: #f9fafb; }
                .summary { display: flex; gap: 20px; margin-top: 20px; }
                .summary-card { flex: 1; padding: 15px; border-radius: 8px; text-align: center; }
                .summary-chq { background: #fef3c7; border: 1px solid #f59e0b; }
                .summary-traite { background: #f3e8ff; border: 1px solid #9333ea; }
                .summary-virement { background: #dbeafe; border: 1px solid #2563eb; }
                .summary-card h3 { margin: 0 0 5px 0; font-size: 12px; }
                .summary-card p { margin: 0; font-size: 18px; font-weight: bold; }
                @media print { 
                    button { display: none; } 
                    .summary { flex-wrap: wrap; }
                    .summary-card { min-width: 150px; }
                }
            </style>
        </head>
        <body>
            <h1>Relevé Compte Fournisseurs</h1>
            
            <div class="filters">
                <p><strong>Période:</strong> Du ${formatDate(filters.value.dateFrom)} au ${formatDate(filters.value.dateTo)}</p>
                ${filters.value.codeFournisseur ? `<p><strong>Code fournisseur:</strong> ${filters.value.codeFournisseur}</p>` : ''}
                ${filters.value.nomFournisseur ? `<p><strong>Nom fournisseur:</strong> ${filters.value.nomFournisseur}</p>` : ''}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Code Règlement</th>
                        <th>Type</th>
                        <th>N° Règlement</th>
                        <th>Montant</th>
                        <th>Code Fournisseur</th>
                        <th>Nom Fournisseur</th>
                        <th>Banque</th>
                        <th>Date Encaissement</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredReglements.value.map(r => `
                        <tr>
                            <td>${r.code_reglement}</td>
                            <td>${r.type_reglement}</td>
                            <td>${r.numero_piece || '-'}</td>
                            <td>${formatCurrency(r.montant)}</td>
                            <td>${r.fournisseur?.code_fournisseur || '-'}</td>
                            <td>${r.fournisseur?.nom_fournisseur || '-'}</td>
                            <td>${r.banque || '-'}</td>
                            <td>${formatDate(r.date_encaissement)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            
            <div class="summary">
                <div class="summary-card summary-chq">
                    <h3>Montant CHQ TTC</h3>
                    <p>${formatCurrency(montantCHQ.value)}</p>
                </div>
                <div class="summary-card summary-traite">
                    <h3>Montant Traite TTC</h3>
                    <p>${formatCurrency(montantTraite.value)}</p>
                </div>
                <div class="summary-card summary-virement">
                    <h3>Montant Virement TTC</h3>
                    <p>${formatCurrency(montantVirement.value)}</p>
                </div>
            </div>
            
            <br><br>
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

// Initialize
onMounted(() => {
    loadReglements()
})
</script>

