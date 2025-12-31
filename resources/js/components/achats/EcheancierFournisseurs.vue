<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg flex flex-col h-[calc(100vh-130px)] overflow-hidden">
        <!-- Main Content Area -->
        <div class="flex flex-col lg:flex-row gap-4 h-full overflow-hidden">
            <!-- Left Section: Filters + Table -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Sticky Header Section -->
                <div class="p-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex-none bg-white dark:bg-gray-800 z-20">
                    <!-- Section Header -->
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                        Etat Règlements Fournisseur
                    </h2>

                    <!-- Filter Row -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                    <!-- Mois -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois</label>
                        <select 
                            v-model="filters.mois"
                            class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option value="">Tous</option>
                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                    </div>

                    <!-- Nom Fournisseur -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom Fournisseur</label>
                        <select 
                            v-model="filters.nomFournisseur"
                            class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
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
                            class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option value="">Tous</option>
                            <option v-for="nom in uniqueClients" :key="nom" :value="nom">{{ nom }}</option>
                        </select>
                    </div>

                    <!-- Statue (Status) with Action Buttons -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statue</label>
                        <div class="flex gap-2">
                            <select 
                                v-model="filters.statut"
                                class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                                <option value="">Tous</option>
                                <option value="paye">Payé</option>
                                <option value="impaye">Impayé</option>
                                <option value="reporte">Reporté</option>
                                <option value="instance">Instance</option>
                            </select>
                            <button 
                                @click="addNew"
                                class="p-2 bg-blue-100 dark:bg-blue-600 hover:bg-blue-200 dark:hover:bg-blue-500 rounded transition-colors"
                                title="Ajouter"
                            >
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                            <button 
                                @click="loadReglements"
                                class="p-2 bg-green-100 dark:bg-green-600 hover:bg-green-200 dark:hover:bg-green-500 rounded transition-colors"
                                title="Actualiser"
                            >
                                <svg class="w-5 h-5 text-green-600 dark:text-green-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TTC Display + Actions -->
                <div class="flex items-center gap-3 mb-4">
                    <button 
                        class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 rounded-lg text-white font-bold text-xl shadow-md"
                    >
                        {{ formatCurrency(totalTTC) }} TTC
                    </button>
                    <button 
                        @click="sendEmail"
                        class="p-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-full transition-colors"
                        title="Envoyer par email"
                    >
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </button>
                    <button 
                        @click="printTable"
                        class="p-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-full transition-colors"
                        title="Imprimer"
                    >
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </button>
                </div>

                <!-- Remise Bancaire Header -->
                <div class="text-gray-600 dark:text-gray-300 text-sm mb-2 font-medium">
                    Remise Bancaire N° .......
                </div>
                </div>

                <!-- Scrollable Table Section -->
                <div class="flex-1 overflow-hidden bg-gray-50 dark:bg-gray-900/40">
                    <div class="overflow-x-auto overflow-y-auto relative h-full">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10 shadow-sm border-b border-gray-200 dark:border-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N°</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom Fournisseur</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom Client</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Banque</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom Tiré</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date Encais</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Loading State -->
                                <tr v-if="loading">
                                    <td colspan="9" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
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
                                    <td colspan="9" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                        Aucun règlement trouvé
                                    </td>
                                </tr>
                                <!-- Data Rows -->
                                <tr 
                                    v-else 
                                    v-for="reglement in filteredReglements" 
                                    :key="reglement.id" 
                                    class="transition-colors"
                                    :class="[
                                        getRowBackgroundClass(reglement.statut),
                                        !getRowBackgroundClass(reglement.statut) ? 'hover:bg-gray-50 dark:hover:bg-gray-700' : 'hover:opacity-80'
                                    ]"
                                >
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ formatDate(reglement.date_reglement) }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ reglement.numero_piece || '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ reglement.fournisseur?.nom_fournisseur || '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ getClientName(reglement) }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ reglement.type_reglement || '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ reglement.banque || '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ reglement.nom_beneficiaire || '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ formatCurrency(reglement.montant) }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ formatDate(reglement.date_encaissement) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Section: Calendar -->
            <div class="w-full lg:w-96 xl:w-[420px] flex-none p-6 bg-gray-100 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 overflow-y-auto">
                <!-- Month Navigation -->
                <div class="flex items-center justify-center mb-4">
                    <button 
                        @click="previousMonth"
                        class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                        title="Mois précédent"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="mx-4 px-6 py-2 bg-green-500 text-white font-bold rounded text-base">
                        {{ currentDate.getDate() }} {{ getMonthName(currentDate.getMonth()) }} {{ currentDate.getFullYear() }}
                    </div>
                    <button 
                        @click="nextMonth"
                        class="p-2 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition-colors"
                        title="Mois suivant"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Days of Week -->
                <div class="grid grid-cols-7 gap-2 mb-3">
                    <div 
                        v-for="(day, index) in dayNames" 
                        :key="index"
                        class="text-center py-2.5 text-sm font-bold text-white rounded"
                        :style="{ backgroundColor: day.color }"
                    >
                        {{ day.abbr }}
                    </div>
                </div>

                <!-- Calendar Days -->
                <div class="grid grid-cols-7 gap-2">
                    <div 
                        v-for="(day, index) in calendarDays" 
                        :key="index"
                        class="text-center py-2.5 text-sm rounded transition-colors min-h-[40px] flex items-center justify-center"
                        :class="[
                            day === 0 ? 'text-transparent' : '',
                            isWeekendOrHoliday(day) ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-900 dark:text-white',
                            day === currentDate.getDate() && isCurrentMonth ? 'bg-blue-100 dark:bg-blue-900/30 font-bold' : ''
                        ]"
                    >
                        {{ day || '' }}
                    </div>
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
const allFournisseurs = ref([])
const loading = ref(false)
const currentDate = ref(new Date(2025, 2, 3)) // March 3, 2025

const filters = ref({
    mois: '',
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

const dayNames = [
    { abbr: 'SUN', color: '#FCD34D' }, // Yellow
    { abbr: 'MON', color: '#F472B6' }, // Pink
    { abbr: 'TUE', color: '#60A5FA' }, // Light Blue
    { abbr: 'WED', color: '#14B8A6' }, // Teal
    { abbr: 'THU', color: '#FB923C' }, // Orange
    { abbr: 'FRI', color: '#F472B6' }, // Pink
    { abbr: 'SAT', color: '#10B981' }  // Green
]

// Red dates for March 2025 (weekends and holidays based on image)
const redDates = [3, 5, 9, 13, 19, 20, 22, 25, 28, 29, 30, 31]

// =============================================================================
// COMPUTED PROPERTIES
// =============================================================================

const uniqueFournisseurs = computed(() => {
    // Use all fournisseurs from API if available, otherwise fallback to reglements data
    if (allFournisseurs.value.length > 0) {
        return allFournisseurs.value.map(f => f.nom_fournisseur).filter(Boolean)
    }
    return [...new Set(reglements.value.map(r => r.fournisseur?.nom_fournisseur).filter(Boolean))]
})

const uniqueClients = computed(() => {
    // Get client names from bon_achat.client_livre (not nom_beneficiaire which is nom tiré)
    const clients = []
    reglements.value.forEach(r => {
        if (r.lignes && r.lignes.length > 0) {
            r.lignes.forEach(ligne => {
                // Laravel serializes relationships in snake_case
                const bonAchat = ligne.bon_achat || ligne.bonAchat
                if (bonAchat?.client_livre) {
                    clients.push(bonAchat.client_livre)
                }
            })
        }
    })
    return [...new Set(clients.filter(Boolean))]
})

const filteredReglements = computed(() => {
    return reglements.value.filter(r => {
        // Month filter - based on date_encaissement (not date_reglement)
        if (filters.value.mois) {
            const encaissementMonth = r.date_encaissement ? r.date_encaissement.substring(5, 7) : ''
            if (encaissementMonth !== filters.value.mois) return false
        }
        
        // Fournisseur filter
        if (filters.value.nomFournisseur && r.fournisseur?.nom_fournisseur !== filters.value.nomFournisseur) return false
        
        // Client filter - check client_livre from bon_achat
        if (filters.value.nomClient) {
            const hasMatchingClient = r.lignes?.some(ligne => {
                const bonAchat = ligne.bon_achat || ligne.bonAchat
                return bonAchat?.client_livre === filters.value.nomClient
            })
            if (!hasMatchingClient) return false
        }
        
        // Statut filter
        if (filters.value.statut && r.statut !== filters.value.statut) return false
        
        return true
    })
})

const totalTTC = computed(() => {
    return filteredReglements.value.reduce((sum, r) => sum + (parseFloat(r.montant) || 0), 0)
})

const emptyRowsCount = computed(() => {
    const minRows = 8
    const currentRows = filteredReglements.value.length
    return Math.max(0, minRows - currentRows)
})

const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const firstDay = new Date(year, month, 1).getDay()
    const daysInMonth = new Date(year, month + 1, 0).getDate()
    const days = []
    
    // Adjust first day: 0 = Sunday, 1 = Monday, etc.
    // We want Sunday to be first (0)
    const adjustedFirstDay = firstDay === 0 ? 0 : firstDay
    
    // Add empty cells for days before the 1st
    for (let i = 0; i < adjustedFirstDay; i++) {
        days.push(0)
    }
    
    // Add the actual days
    for (let i = 1; i <= daysInMonth; i++) {
        days.push(i)
    }
    
    // Fill remaining cells to complete the grid (up to 6 rows x 7 days = 42)
    while (days.length < 42) {
        days.push(0)
    }
    
    // Limit to 35 cells (5 rows) if possible
    if (days.length > 35 && days.slice(35, 42).every(d => d === 0)) {
        return days.slice(0, 35)
    }
    
    return days
})

const isCurrentMonth = computed(() => {
    const now = new Date()
    return currentDate.value.getMonth() === now.getMonth() && 
           currentDate.value.getFullYear() === now.getFullYear()
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
 * Get month name in French
 */
const getMonthName = (monthIndex) => {
    const monthNames = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ]
    return monthNames[monthIndex]
}

/**
 * Navigate to previous month
 */
const previousMonth = () => {
    const newDate = new Date(currentDate.value)
    newDate.setMonth(newDate.getMonth() - 1)
    currentDate.value = newDate
    filters.value.mois = (newDate.getMonth() + 1).toString().padStart(2, '0')
}

/**
 * Navigate to next month
 */
const nextMonth = () => {
    const newDate = new Date(currentDate.value)
    newDate.setMonth(newDate.getMonth() + 1)
    currentDate.value = newDate
    filters.value.mois = (newDate.getMonth() + 1).toString().padStart(2, '0')
}

/**
 * Check if a date is a weekend or holiday (highlighted in red)
 */
const isWeekendOrHoliday = (day) => {
    if (day === 0) return false
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const date = new Date(year, month, day)
    const dayOfWeek = date.getDay()
    
    // Check if it's a weekend (Saturday = 6, Sunday = 0)
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        return true
    }
    
    // Check if it's in the red dates list for the current month
    if (month === 2 && year === 2025) { // March 2025
        return redDates.includes(day)
    }
    
    return false
}

/**
 * Add new entry (placeholder)
 */
const addNew = () => {
    alert('Fonctionnalité d\'ajout à implémenter')
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

/**
 * Load all fournisseurs from API
 */
const loadFournisseurs = async () => {
    try {
        const response = await fetch('/api/fournisseurs')
        if (response.ok) {
            allFournisseurs.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement des fournisseurs:', error)
    }
}

/**
 * Get row background class based on statut
 */
const getRowBackgroundClass = (statut) => {
    switch (statut) {
        case 'impaye':
            return 'bg-red-100 dark:bg-red-900/30'
        case 'devalide':
            return 'bg-orange-100 dark:bg-orange-900/30'
        default:
            return ''
    }
}

/**
 * Get client name from reglement (from bon_achat.client_livre)
 */
const getClientName = (reglement) => {
    if (reglement.lignes && reglement.lignes.length > 0) {
        const ligne = reglement.lignes[0]
        const bonAchat = ligne?.bon_achat || ligne?.bonAchat
        if (bonAchat?.client_livre) return bonAchat.client_livre
    }
    return '-'
}

// =============================================================================
// LIFECYCLE
// =============================================================================

onMounted(() => {
    loadReglements()
    loadFournisseurs()
})
</script>

<style scoped>
/* Print styles */
@media print {
    .lg\:w-96,
    .xl\:w-\[420px\] {
        display: none;
    }
}
</style>
