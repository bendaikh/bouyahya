<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
        <!-- Main Content Area -->
        <div class="flex flex-col lg:flex-row">
            <!-- Left Section: Filters + Table -->
            <div class="flex-1 p-6">
                <!-- Section Header -->
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">
                    Etat Règlements Fournisseur
                </h2>

                <!-- Filter Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <!-- Mois -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois</label>
                        <select 
                            v-model="filters.mois"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option value="">Tous</option>
                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                        <select 
                            v-model="filters.code"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option value="">Tous</option>
                            <option v-for="code in uniqueCodes" :key="code" :value="code">{{ code }}</option>
                        </select>
                    </div>

                    <!-- Nom Fournisseur -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom Fournisseur</label>
                        <select 
                            v-model="filters.nomFournisseur"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option value="">Tous</option>
                            <option v-for="nom in uniqueFournisseurs" :key="nom" :value="nom">{{ nom }}</option>
                        </select>
                    </div>

                    <!-- Nom Client -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom Client</label>
                        <select 
                            v-model="filters.nomClient"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option value="">Tous</option>
                            <option v-for="nom in uniqueClients" :key="nom" :value="nom">{{ nom }}</option>
                        </select>
                    </div>

                    <!-- Statue (Status) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                        <select 
                            v-model="filters.statut"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option value="">Tous</option>
                            <option value="paye">Payé</option>
                            <option value="impaye">Impayé</option>
                            <option value="reporte">Reporté</option>
                            <option value="instance">Instance</option>
                        </select>
                    </div>

                    <!-- TTC Display + Actions -->
                    <div class="flex items-end gap-2">
                        <div class="flex-1 px-4 py-2 bg-gradient-to-r from-yellow-400 to-green-500 rounded-lg text-center">
                            <span class="text-white font-bold text-sm lg:text-base whitespace-nowrap">
                                {{ formatCurrency(totalTTC) }} TTC
                            </span>
                        </div>
                        <button 
                            @click="sendEmail"
                            class="p-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors"
                            title="Envoyer par email"
                        >
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </button>
                        <button 
                            @click="printTable"
                            class="p-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors"
                            title="Imprimer"
                        >
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remise Bancaire Header -->
                <div class="text-gray-600 dark:text-gray-300 text-sm mb-3 font-medium">
                    Remise Bancaire N° .......
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N°</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom Fournisseur</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom Client</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Banque</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom Tiré</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date Encais</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Loading State -->
                            <tr v-if="loading">
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Chargement...
                                    </div>
                                </td>
                            </tr>
                            <!-- Empty State -->
                            <tr v-else-if="filteredReglements.length === 0">
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Aucun règlement trouvé
                                </td>
                            </tr>
                            <!-- Data Rows -->
                            <tr 
                                v-else 
                                v-for="reglement in filteredReglements" 
                                :key="reglement.id" 
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ formatDate(reglement.date_reglement) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ reglement.numero_piece || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                    {{ reglement.code_reglement }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ reglement.fournisseur?.nom_fournisseur || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ reglement.nom_beneficiaire || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ reglement.type_reglement || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ reglement.banque || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ reglement.nom_beneficiaire || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ formatCurrency(reglement.montant) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ formatDate(reglement.date_encaissement) }}
                                </td>
                            </tr>
                            <!-- Empty rows for visual consistency -->
                            <tr v-for="n in emptyRowsCount" :key="'empty-' + n" class="h-12">
                                <td colspan="10" class="border-t border-gray-100 dark:border-gray-700"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Section: Calendar -->
            <div class="w-full lg:w-80 p-6 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <!-- Year Display -->
                <div class="text-center mb-6">
                    <div class="flex justify-center items-baseline gap-1">
                        <span class="text-5xl font-bold text-yellow-500">2</span>
                        <span class="text-5xl font-bold text-cyan-500">0</span>
                        <span class="text-5xl font-bold text-green-500">2</span>
                        <span class="text-5xl font-bold text-pink-500">5</span>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-3 gap-3">
                    <div 
                        v-for="(month, index) in calendarMonths" 
                        :key="index"
                        @click="selectMonth(index + 1)"
                        class="cursor-pointer rounded-lg overflow-hidden border-2 transition-all duration-200 hover:shadow-md"
                        :class="[
                            selectedMonth === index + 1 
                                ? 'border-blue-500 ring-2 ring-blue-500/50' 
                                : 'border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-400'
                        ]"
                    >
                        <!-- Month Header -->
                        <div 
                            class="text-center py-1.5 text-[10px] font-bold text-white tracking-wide"
                            :style="{ backgroundColor: month.color }"
                        >
                            {{ month.name }}
                        </div>
                        <!-- Mini Calendar Grid -->
                        <div class="bg-white p-1.5">
                            <div class="grid grid-cols-7 gap-px text-center">
                                <!-- Day headers -->
                                <div 
                                    v-for="(day, i) in ['D', 'L', 'M', 'M', 'J', 'V', 'S']" 
                                    :key="'header-' + i" 
                                    class="text-[7px] text-gray-400 font-semibold"
                                >
                                    {{ day }}
                                </div>
                                <!-- Days -->
                                <div 
                                    v-for="(day, dayIndex) in month.days" 
                                    :key="dayIndex" 
                                    class="text-[7px] text-gray-600 leading-tight"
                                    :class="{ 'text-transparent': day === 0 }"
                                >
                                    {{ day || '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clear Filter Button -->
                <div v-if="selectedMonth" class="mt-4 text-center">
                    <button 
                        @click="clearMonthFilter"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    >
                        Effacer le filtre du mois
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// =============================================================================
// STATE
// =============================================================================

const reglements = ref([])
const loading = ref(false)
const selectedMonth = ref(null)

const filters = ref({
    mois: '',
    code: '',
    nomFournisseur: '',
    nomClient: '',
    statut: ''
})

// =============================================================================
// CONSTANTS
// =============================================================================

const months = [
    { value: '01', label: 'Janvier' },
    { value: '02', label: 'Février' },
    { value: '03', label: 'Mars' },
    { value: '04', label: 'Avril' },
    { value: '05', label: 'Mai' },
    { value: '06', label: 'Juin' },
    { value: '07', label: 'Juillet' },
    { value: '08', label: 'Août' },
    { value: '09', label: 'Septembre' },
    { value: '10', label: 'Octobre' },
    { value: '11', label: 'Novembre' },
    { value: '12', label: 'Décembre' }
]

const calendarMonths = [
    { name: 'JANUARY', color: '#3B82F6', days: generateMonthDays(2025, 0) },
    { name: 'FEBRUARY', color: '#EC4899', days: generateMonthDays(2025, 1) },
    { name: 'MARCH', color: '#10B981', days: generateMonthDays(2025, 2) },
    { name: 'APRIL', color: '#F59E0B', days: generateMonthDays(2025, 3) },
    { name: 'MAY', color: '#8B5CF6', days: generateMonthDays(2025, 4) },
    { name: 'JUNE', color: '#06B6D4', days: generateMonthDays(2025, 5) },
    { name: 'JULY', color: '#6366F1', days: generateMonthDays(2025, 6) },
    { name: 'AUGUST', color: '#F97316', days: generateMonthDays(2025, 7) },
    { name: 'SEPTEMBER', color: '#14B8A6', days: generateMonthDays(2025, 8) },
    { name: 'OCTOBER', color: '#EF4444', days: generateMonthDays(2025, 9) },
    { name: 'NOVEMBER', color: '#84CC16', days: generateMonthDays(2025, 10) },
    { name: 'DECEMBER', color: '#F43F5E', days: generateMonthDays(2025, 11) }
]

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Generate days array for a given month (with leading empty cells for alignment)
 */
function generateMonthDays(year, month) {
    const firstDay = new Date(year, month, 1).getDay()
    const daysInMonth = new Date(year, month + 1, 0).getDate()
    const days = []
    
    // Add empty cells for days before the 1st
    for (let i = 0; i < firstDay; i++) {
        days.push(0)
    }
    
    // Add the actual days
    for (let i = 1; i <= daysInMonth; i++) {
        days.push(i)
    }
    
    // Fill remaining cells to complete the grid (6 rows x 7 days = 42)
    while (days.length < 42) {
        days.push(0)
    }
    
    return days
}

// =============================================================================
// COMPUTED PROPERTIES
// =============================================================================

const uniqueCodes = computed(() => {
    return [...new Set(reglements.value.map(r => r.code_reglement).filter(Boolean))]
})

const uniqueFournisseurs = computed(() => {
    return [...new Set(reglements.value.map(r => r.fournisseur?.nom_fournisseur).filter(Boolean))]
})

const uniqueClients = computed(() => {
    return [...new Set(reglements.value.map(r => r.nom_beneficiaire).filter(Boolean))]
})

const filteredReglements = computed(() => {
    return reglements.value.filter(r => {
        // Month filter
        if (filters.value.mois) {
            const reglementMonth = r.date_reglement ? r.date_reglement.substring(5, 7) : ''
            if (reglementMonth !== filters.value.mois) return false
        }
        
        // Code filter
        if (filters.value.code && r.code_reglement !== filters.value.code) return false
        
        // Fournisseur filter
        if (filters.value.nomFournisseur && r.fournisseur?.nom_fournisseur !== filters.value.nomFournisseur) return false
        
        // Client filter
        if (filters.value.nomClient && r.nom_beneficiaire !== filters.value.nomClient) return false
        
        // Statut filter
        if (filters.value.statut && r.statut !== filters.value.statut) return false
        
        return true
    })
})

const totalTTC = computed(() => {
    return filteredReglements.value.reduce((sum, r) => sum + (parseFloat(r.montant) || 0), 0)
})

const emptyRowsCount = computed(() => {
    const minRows = 6
    const currentRows = filteredReglements.value.length
    return Math.max(0, minRows - currentRows)
})

// =============================================================================
// METHODS
// =============================================================================

/**
 * Format date to French locale
 */
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
    })
}

/**
 * Format currency in Moroccan Dirham
 */
const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-MA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)
}

/**
 * Select a month from the calendar widget
 */
const selectMonth = (month) => {
    selectedMonth.value = month
    filters.value.mois = month.toString().padStart(2, '0')
}

/**
 * Clear the month filter
 */
const clearMonthFilter = () => {
    selectedMonth.value = null
    filters.value.mois = ''
}

/**
 * Send email functionality (placeholder)
 */
const sendEmail = () => {
    alert('Fonctionnalité d\'envoi par email à implémenter')
}

/**
 * Print the table
 */
const printTable = () => {
    window.print()
}

/**
 * Load règlements from API
 */
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

// =============================================================================
// LIFECYCLE
// =============================================================================

onMounted(() => {
    loadReglements()
})
</script>

<style scoped>
/* Print styles */
@media print {
    .lg\:w-80 {
        display: none;
    }
}
</style>
