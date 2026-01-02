<template>
    <div class="min-h-screen bg-slate-800 p-6">
        <!-- Header Section -->
        <div class="flex justify-between items-start mb-6">
            <!-- Left Side - Title and Filters -->
            <div class="bg-slate-700/50 rounded-lg p-4 flex-1 mr-4">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-white mb-1">Types de charges</h2>
                    <p class="text-gray-400 text-sm">Gérer les types de charges</p>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-white text-sm font-medium">Mois</label>
                        <div class="relative">
                            <select v-model="filterMois" class="appearance-none bg-gray-300 border-0 rounded px-4 py-2 pr-8 text-gray-800 text-sm min-w-[140px]">
                                <option value="">Tous les mois</option>
                                <option v-for="(month, index) in months" :key="index" :value="index + 1">{{ month }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <button @click="searchByMois" class="p-2 bg-blue-500 rounded hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-white text-sm font-medium">Type</label>
                        <div class="relative">
                            <select v-model="filterType" class="appearance-none bg-gray-300 border-0 rounded px-4 py-2 pr-8 text-gray-800 text-sm min-w-[140px]">
                                <option value="">Tous les types</option>
                                <option v-for="type in typesList" :key="type.id" :value="type.id">{{ type.libelle }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <button @click="searchByType" class="p-2 bg-blue-500 rounded hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side - Total TTC and Button -->
            <div class="flex flex-col items-end gap-3">
                <!-- Total TTC -->
                <div class="bg-green-500 rounded-lg px-6 py-3 min-w-[200px]">
                    <div class="text-white text-sm font-semibold">Total TTC</div>
                    <div class="text-white text-2xl font-bold">{{ formatCurrency(totalTTC) }}</div>
                </div>

                <!-- New Button -->
                <button 
                    @click="openModal" 
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nouveau type de charge
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-slate-700/30 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-600">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Date</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Réf</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Libellé</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Bénéficiaire</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Montant</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Type</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">N°</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Compte Caisse</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Observation</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-600">
                    <tr v-if="loading">
                        <td colspan="10" class="px-4 py-8 text-center text-gray-400">
                            Chargement...
                        </td>
                    </tr>
                    <tr v-else-if="filteredCharges.length === 0">
                        <td colspan="10" class="px-4 py-8 text-center text-gray-400">
                            Aucune charge trouvée
                        </td>
                    </tr>
                    <tr v-else v-for="charge in filteredCharges" :key="charge.id" class="hover:bg-slate-600/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-white">{{ formatDate(charge.date) }}</td>
                        <td class="px-4 py-3 text-sm text-white">{{ charge.reference }}</td>
                        <td class="px-4 py-3 text-sm text-white">{{ charge.libelle }}</td>
                        <td class="px-4 py-3 text-sm text-white">{{ charge.beneficiaire }}</td>
                        <td class="px-4 py-3 text-sm text-white font-semibold">{{ formatCurrency(charge.montant) }}</td>
                        <td class="px-4 py-3 text-sm text-white">{{ charge.type_libelle }}</td>
                        <td class="px-4 py-3 text-sm text-white">{{ charge.numero }}</td>
                        <td class="px-4 py-3 text-sm text-white">{{ charge.compte_caisse_libelle }}</td>
                        <td class="px-4 py-3 text-sm text-white max-w-[200px] truncate" :title="charge.observation">{{ charge.observation }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button @click="viewCharge(charge)" class="text-blue-400 hover:text-blue-300 transition-colors" title="Voir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button @click="editCharge(charge)" class="text-green-400 hover:text-green-300 transition-colors" title="Modifier">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteCharge(charge)" class="text-red-400 hover:text-red-300 transition-colors" title="Supprimer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <button @click="printCharge(charge)" class="text-purple-400 hover:text-purple-300 transition-colors" title="Imprimer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Popup - Fiche de SAISI -->
        <div v-if="showModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-slate-700 rounded-lg shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="bg-slate-600 px-6 py-4 rounded-t-lg">
                    <h3 class="text-lg font-semibold text-white">Fiche de SAISI</h3>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Row 1: Date, Heure, Opérateur -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">Date</label>
                            <input 
                                type="text" 
                                :value="form.date" 
                                readonly 
                                class="w-full px-3 py-2 bg-white rounded text-gray-800 text-sm"
                                placeholder="automatique"
                            />
                        </div>
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">Heure</label>
                            <input 
                                type="text" 
                                :value="form.heure" 
                                readonly 
                                class="w-full px-3 py-2 bg-white rounded text-gray-800 text-sm"
                                placeholder="automatique"
                            />
                        </div>
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">Opérateur</label>
                            <div class="relative">
                                <select 
                                    v-model="form.operateur" 
                                    :disabled="formMode === 'view'"
                                    class="appearance-none w-full px-3 py-2 bg-white rounded text-gray-800 text-sm pr-8 disabled:bg-gray-200"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option v-for="op in operateursList" :key="op.id" :value="op.libelle">{{ op.libelle }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Compte Caisse, Type, N° -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">Compte Caisse</label>
                            <div class="relative">
                                <select 
                                    v-model="form.compte_caisse_id" 
                                    :disabled="formMode === 'view'"
                                    class="appearance-none w-full px-3 py-2 bg-white rounded text-gray-800 text-sm pr-8 disabled:bg-gray-200"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option v-for="compte in comptesCaisse" :key="compte.id" :value="compte.id">{{ compte.libelle }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">Type</label>
                            <div class="relative">
                                <select 
                                    v-model="form.type_id" 
                                    :disabled="formMode === 'view'"
                                    class="appearance-none w-full px-3 py-2 bg-white rounded text-gray-800 text-sm pr-8 disabled:bg-gray-200"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option v-for="type in typesList" :key="type.id" :value="type.id">{{ type.libelle }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">N°</label>
                            <input 
                                type="text" 
                                v-model="form.numero" 
                                :disabled="formMode === 'view'"
                                class="w-full px-3 py-2 bg-white rounded text-gray-800 text-sm disabled:bg-gray-200"
                            />
                        </div>
                    </div>

                    <!-- Row 3: Libellé and Montant TTC -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">Libellé</label>
                            <input 
                                type="text" 
                                v-model="form.libelle" 
                                :disabled="formMode === 'view'"
                                class="w-full px-3 py-2 bg-white rounded text-gray-800 text-sm disabled:bg-gray-200"
                            />
                        </div>
                        <div>
                            <!-- Montant TTC Box -->
                            <div class="bg-green-500 rounded-lg p-4 h-full flex flex-col justify-center">
                                <label class="block text-white text-sm font-semibold mb-1">Montant TTC</label>
                                <input 
                                    type="number" 
                                    v-model.number="form.montant" 
                                    :disabled="formMode === 'view'"
                                    step="0.01"
                                    class="w-full px-3 py-2 bg-transparent border-0 text-white text-3xl font-bold text-center focus:outline-none disabled:opacity-70"
                                    placeholder="00.00"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Bénéficiaire -->
                    <div class="mb-4">
                        <label class="block text-white text-sm font-semibold mb-2">Bénéficiaire</label>
                        <input 
                            type="text" 
                            v-model="form.beneficiaire" 
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 bg-white rounded text-gray-800 text-sm disabled:bg-gray-200"
                        />
                    </div>

                    <!-- Row 5: Observation -->
                    <div class="mb-6">
                        <label class="block text-white text-sm font-semibold mb-2">Observation</label>
                        <textarea 
                            v-model="form.observation" 
                            :disabled="formMode === 'view'"
                            rows="4"
                            class="w-full px-3 py-2 bg-gray-100 rounded text-gray-800 text-sm resize-none disabled:bg-gray-200"
                        ></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-center gap-4">
                        <button 
                            v-if="formMode !== 'view'"
                            @click="saveCharge" 
                            :disabled="saving"
                            class="px-8 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-colors disabled:opacity-50"
                        >
                            {{ saving ? 'Enregistrement...' : 'Valider' }}
                        </button>
                        <button 
                            @click="closeModal" 
                            class="px-8 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold transition-colors"
                        >
                            Annuler
                        </button>
                        <button 
                            @click="printCurrentCharge" 
                            class="px-8 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition-colors"
                        >
                            Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Data
const charges = ref([])
const typesList = ref([])
const operateursList = ref([])
const comptesCaisse = ref([])
const loading = ref(false)
const saving = ref(false)
const showModal = ref(false)
const formMode = ref('create') // 'create', 'edit', 'view'
const editingId = ref(null)

// Filters
const filterMois = ref('')
const filterType = ref('')

// Messages
const successMessage = ref('')
const errorMessage = ref('')

// Form
const form = ref({
    date: '',
    heure: '',
    operateur: '',
    compte_caisse_id: '',
    type_id: '',
    numero: '',
    libelle: '',
    beneficiaire: '',
    montant: 0,
    observation: ''
})

// Months
const months = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
]

// Computed
const filteredCharges = computed(() => {
    let result = charges.value

    if (filterMois.value) {
        result = result.filter(charge => {
            const chargeMonth = new Date(charge.date).getMonth() + 1
            return chargeMonth === parseInt(filterMois.value)
        })
    }

    if (filterType.value) {
        result = result.filter(charge => charge.type_id === parseInt(filterType.value))
    }

    return result
})

const totalTTC = computed(() => {
    return filteredCharges.value.reduce((sum, charge) => sum + parseFloat(charge.montant || 0), 0)
})

// Methods
const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR')
}

const getCurrentDateTime = () => {
    const now = new Date()
    return {
        date: now.toLocaleDateString('fr-FR'),
        heure: now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
    }
}

const resetForm = () => {
    const { date, heure } = getCurrentDateTime()
    form.value = {
        date: date,
        heure: heure,
        operateur: '',
        compte_caisse_id: '',
        type_id: '',
        numero: '',
        libelle: '',
        beneficiaire: '',
        montant: 0,
        observation: ''
    }
    editingId.value = null
}

const openModal = () => {
    formMode.value = 'create'
    resetForm()
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    resetForm()
}

const viewCharge = (charge) => {
    formMode.value = 'view'
    editingId.value = charge.id
    form.value = {
        date: formatDate(charge.date),
        heure: charge.heure || '',
        operateur: charge.operateur || '',
        compte_caisse_id: charge.compte_caisse_id || '',
        type_id: charge.type_id || '',
        numero: charge.numero || '',
        libelle: charge.libelle || '',
        beneficiaire: charge.beneficiaire || '',
        montant: charge.montant || 0,
        observation: charge.observation || ''
    }
    showModal.value = true
}

const editCharge = (charge) => {
    formMode.value = 'edit'
    editingId.value = charge.id
    form.value = {
        date: formatDate(charge.date),
        heure: charge.heure || '',
        operateur: charge.operateur || '',
        compte_caisse_id: charge.compte_caisse_id || '',
        type_id: charge.type_id || '',
        numero: charge.numero || '',
        libelle: charge.libelle || '',
        beneficiaire: charge.beneficiaire || '',
        montant: charge.montant || 0,
        observation: charge.observation || ''
    }
    showModal.value = true
}

const saveCharge = async () => {
    if (!form.value.libelle) {
        showMessage('Veuillez remplir le libellé', true)
        return
    }

    try {
        saving.value = true
        const url = editingId.value 
            ? `/api/types-charges/${editingId.value}`
            : '/api/types-charges'
        
        const method = editingId.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                operateur: form.value.operateur,
                compte_caisse_id: form.value.compte_caisse_id || null,
                type_id: form.value.type_id || null,
                numero: form.value.numero,
                libelle: form.value.libelle,
                beneficiaire: form.value.beneficiaire,
                montant: form.value.montant,
                observation: form.value.observation
            })
        })

        const data = await response.json()
        
        if (response.ok) {
            showMessage(editingId.value ? 'Charge modifiée avec succès' : 'Charge ajoutée avec succès')
            closeModal()
            loadCharges()
        } else {
            throw new Error(data.message || 'Erreur lors de l\'enregistrement')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'enregistrement', true)
    } finally {
        saving.value = false
    }
}

const deleteCharge = async (charge) => {
    if (!confirm(`Supprimer cette charge "${charge.libelle}" ?`)) return

    try {
        const response = await fetch(`/api/types-charges/${charge.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })

        if (response.ok) {
            showMessage('Charge supprimée avec succès')
            loadCharges()
        } else {
            const data = await response.json()
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de la suppression', true)
    }
}

const printCharge = (charge) => {
    // Implement print functionality
    window.open(`/api/types-charges/${charge.id}/print`, '_blank')
}

const printCurrentCharge = () => {
    if (editingId.value) {
        printCharge({ id: editingId.value })
    } else {
        showMessage('Veuillez d\'abord enregistrer la charge', true)
    }
}

const searchByMois = () => {
    // Filter is reactive, no need for additional action
}

const searchByType = () => {
    // Filter is reactive, no need for additional action
}

const showMessage = (message, isError = false) => {
    if (isError) {
        errorMessage.value = message
        successMessage.value = ''
    } else {
        successMessage.value = message
        errorMessage.value = ''
    }
    setTimeout(() => {
        successMessage.value = ''
        errorMessage.value = ''
    }, 3000)
}

const loadCharges = async () => {
    try {
        loading.value = true
        const response = await fetch('/api/types-charges')
        const data = await response.json()
        charges.value = data.charges || []
    } catch (error) {
        console.error('Erreur lors du chargement des charges:', error)
        showMessage('Erreur lors du chargement des données', true)
    } finally {
        loading.value = false
    }
}

const loadTypes = async () => {
    try {
        // Load types from types_charges table
        const response = await fetch('/api/types-charges/types')
        const data = await response.json()
        typesList.value = data.types || []
    } catch (error) {
        console.error('Erreur lors du chargement des types:', error)
    }
}

const loadOperateurs = async () => {
    try {
        const response = await fetch('/api/settings/operateurs')
        const data = await response.json()
        operateursList.value = data.operateurs || []
    } catch (error) {
        console.error('Erreur lors du chargement des opérateurs:', error)
    }
}

const loadComptesCaisse = async () => {
    try {
        const response = await fetch('/api/comptes-caisse')
        const data = await response.json()
        comptesCaisse.value = data.comptes || []
    } catch (error) {
        console.error('Erreur lors du chargement des comptes caisse:', error)
    }
}

onMounted(() => {
    loadCharges()
    loadTypes()
    loadOperateurs()
    loadComptesCaisse()
})
</script>

<style scoped>
/* Custom scrollbar for modal */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Remove number input spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    appearance: textfield;
    -moz-appearance: textfield;
}
</style>

