<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Règlements fournisseurs</h2>
                <p class="text-gray-600 dark:text-gray-400">Gérer les règlements des fournisseurs</p>
            </div>
            <button 
                v-if="!showForm"
                @click="openCreateForm" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouveau règlement
            </button>
        </div>

        <!-- List View -->
        <div v-if="!showForm">
            <!-- Export Buttons -->
            <div class="mb-4 flex justify-end space-x-2">
                <button 
                    @click="exportToExcel" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exporter Excel
                </button>
                <button 
                    @click="exportToPDF" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Exporter PDF
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fournisseur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Chargement...
                            </td>
                        </tr>
                        <tr v-else-if="reglements.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Aucun règlement trouvé
                            </td>
                        </tr>
                        <tr v-else v-for="reglement in reglements" :key="reglement.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">{{ reglement.code_reglement }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(reglement.date_reglement) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.fournisseur?.nom_fournisseur || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.type_reglement }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ formatCurrency(reglement.montant) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(reglement.statut)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ getStatusText(reglement.statut) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <button @click="viewReglement(reglement)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Voir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button @click="printReglementPDF(reglement)" class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 transition-colors" title="Imprimer PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                    <button v-if="reglement.statut !== 'paye'" @click="editReglement(reglement)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition-colors" title="Modifier">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button v-if="reglement.statut === 'impaye'" @click="markAsPaid(reglement)" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 transition-colors" title="Marquer comme payé">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteReglement(reglement)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form View -->
        <div v-if="showForm" class="space-y-6">
            <!-- Header with buttons -->
            <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ formMode === 'create' ? 'Nouveau règlement fournisseur' : (formMode === 'edit' ? 'Modifier le règlement' : 'Détails du règlement') }}
                </h3>
                <div class="flex space-x-2">
                    <button 
                        @click="cancelForm" 
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm"
                    >
                        Annuler
                    </button>
                    <button 
                        v-if="formMode !== 'view'"
                        @click="saveReglement" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </div>

            <!-- Détails du règlement -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300 mb-4">Détails du règlement</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Row 1 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date règlement</label>
                        <input 
                            type="date" 
                            v-model="form.date_reglement" 
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code règlement</label>
                        <input 
                            type="text" 
                            v-model="form.code_reglement" 
                            readonly
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code fournisseur</label>
                        <select 
                            v-model="form.fournisseur_id" 
                            @change="onFournisseurChange"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        >
                            <option value="">Sélectionner...</option>
                            <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.code_fournisseur }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom fournisseur</label>
                        <input 
                            type="text" 
                            :value="selectedFournisseurName" 
                            readonly
                            placeholder="Nom s'affichera ici"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>

                    <!-- Row 2 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type règlement</label>
                        <select 
                            v-model="form.type_reglement"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        >
                            <option value="Virement">Virement</option>
                            <option value="Chèque">Chèque</option>
                            <option value="Espèces">Espèces</option>
                            <option value="Traite">Traite</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° pièce</label>
                        <input 
                            type="text" 
                            v-model="form.numero_piece"
                            :disabled="formMode === 'view'"
                            placeholder="N° chèque/virement"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banque</label>
                        <select 
                            v-model="form.banque"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        >
                            <option value="">Sélectionner banque</option>
                            <option value="Attijariwafa Bank">Attijariwafa Bank</option>
                            <option value="BMCE Bank">BMCE Bank</option>
                            <option value="Banque Populaire">Banque Populaire</option>
                            <option value="BMCI">BMCI</option>
                            <option value="Société Générale">Société Générale</option>
                            <option value="CIH Bank">CIH Bank</option>
                            <option value="Crédit du Maroc">Crédit du Maroc</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de tiré</label>
                        <input 
                            type="text" 
                            v-model="form.nom_beneficiaire"
                            :disabled="formMode === 'view'"
                            placeholder="Entrez le nom du bénéficiaire"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>

                    <!-- Row 3 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant règlement</label>
                        <input 
                            type="number" 
                            v-model.number="form.montant"
                            :disabled="formMode === 'view'"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date encaissement</label>
                        <input 
                            type="date" 
                            v-model="form.date_encaissement"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reste à ventiler</label>
                        <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-semibold"
                             :class="resteAVentiler < 0 ? 'text-red-600 dark:text-red-400' : (resteAVentiler === 0 ? 'text-green-600 dark:text-green-400' : '')">
                            {{ formatCurrency(resteAVentiler) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons at bottom -->
            <div class="flex justify-center space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button 
                    @click="openCreateForm" 
                    class="px-6 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors text-sm flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter
                </button>
                <button 
                    v-if="formMode === 'view' && editingReglementId"
                    @click="formMode = 'edit'" 
                    class="px-6 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors text-sm flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifier
                </button>
                <button 
                    v-if="formMode !== 'view'"
                    @click="saveReglement" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Valider
                </button>
                <button 
                    @click="cancelForm" 
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Annuler
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const reglements = ref([])
const fournisseurs = ref([])
const loading = ref(false)
const showForm = ref(false)
const formMode = ref('create') // 'create', 'edit', 'view'
const editingReglementId = ref(null)

const form = ref({
    code_reglement: '',
    date_reglement: new Date().toISOString().split('T')[0],
    fournisseur_id: '',
    type_reglement: 'Virement',
    numero_piece: '',
    banque: '',
    nom_beneficiaire: '',
    montant: 0,
    date_encaissement: '',
    observation: '',
    lignes: []
})

// Computed properties
const selectedFournisseurName = computed(() => {
    if (!form.value.fournisseur_id) return ''
    const fournisseur = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
    return fournisseur ? fournisseur.nom_fournisseur : ''
})

const totalVentile = computed(() => {
    return form.value.lignes.reduce((sum, ligne) => sum + (parseFloat(ligne.montant_regle) || 0), 0)
})

const resteAVentiler = computed(() => {
    return (parseFloat(form.value.montant) || 0) - totalVentile.value
})

// Load data functions
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

const getNextCode = async () => {
    try {
        const response = await fetch('/api/reglements-fournisseurs/next-code')
        if (response.ok) {
            const data = await response.json()
            form.value.code_reglement = data.code_reglement
        }
    } catch (error) {
        console.error('Erreur lors de la génération du code:', error)
    }
}

// Form functions
const openCreateForm = () => {
    resetForm()
    getNextCode()
    formMode.value = 'create'
    showForm.value = true
}

const resetForm = () => {
    form.value = {
        code_reglement: '',
        date_reglement: new Date().toISOString().split('T')[0],
        fournisseur_id: '',
        type_reglement: 'Virement',
        numero_piece: '',
        banque: '',
        nom_beneficiaire: '',
        montant: 0,
        date_encaissement: '',
        observation: '',
        lignes: []
    }
    editingReglementId.value = null
}

const cancelForm = () => {
    showForm.value = false
    resetForm()
}

const onFournisseurChange = () => {
    form.value.lignes = []
    if (form.value.fournisseur_id) {
        // Auto-fill nom_beneficiaire
        const fournisseur = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
        if (fournisseur) {
            form.value.nom_beneficiaire = fournisseur.nom_fournisseur
        }
    }
}

// CRUD operations
const saveReglement = async () => {
    // Validation
    if (!form.value.fournisseur_id) {
        alert('Veuillez sélectionner un fournisseur')
        return
    }
    
    if (!form.value.montant || form.value.montant <= 0) {
        alert('Veuillez saisir un montant valide')
        return
    }
    
    try {
        const url = formMode.value === 'edit' 
            ? `/api/reglements-fournisseurs/${editingReglementId.value}`
            : '/api/reglements-fournisseurs'
        
        const method = formMode.value === 'edit' ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(form.value)
        })
        
        if (response.ok) {
            alert(formMode.value === 'edit' ? 'Règlement modifié avec succès' : 'Règlement créé avec succès')
            showForm.value = false
            loadReglements()
            resetForm()
        } else {
            const error = await response.json()
            alert('Erreur: ' + (error.message || 'Une erreur est survenue'))
        }
    } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur lors de l\'enregistrement')
    }
}

const viewReglement = async (reglement) => {
    try {
        const response = await fetch(`/api/reglements-fournisseurs/${reglement.id}`)
        if (response.ok) {
            const data = await response.json()
            form.value = {
                code_reglement: data.code_reglement,
                date_reglement: data.date_reglement,
                fournisseur_id: data.fournisseur_id,
                type_reglement: data.type_reglement,
                numero_piece: data.numero_piece || '',
                banque: data.banque || '',
                nom_beneficiaire: data.nom_beneficiaire || '',
                montant: parseFloat(data.montant),
                date_encaissement: data.date_encaissement || '',
                observation: data.observation || '',
                lignes: data.lignes?.map(l => ({
                    bon_achat_id: l.bon_achat_id,
                    montant_regle: parseFloat(l.montant_regle)
                })) || []
            }
            editingReglementId.value = data.id
            
            formMode.value = 'view'
            showForm.value = true
        }
    } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur lors du chargement')
    }
}

const editReglement = async (reglement) => {
    await viewReglement(reglement)
    formMode.value = 'edit'
}

const markAsPaid = async (reglement) => {
    if (confirm('Voulez-vous marquer ce règlement comme payé ?')) {
        try {
            const response = await fetch(`/api/reglements-fournisseurs/${reglement.id}/mark-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            
            if (response.ok) {
                alert('Règlement marqué comme payé avec succès')
                loadReglements()
            } else {
                const error = await response.json()
                alert('Erreur: ' + (error.message || 'Une erreur est survenue'))
            }
        } catch (error) {
            console.error('Erreur:', error)
            alert('Erreur lors de la mise à jour du statut')
        }
    }
}

const deleteReglement = async (reglement) => {
    if (confirm('Voulez-vous vraiment supprimer ce règlement ?')) {
        try {
            const response = await fetch(`/api/reglements-fournisseurs/${reglement.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            
            if (response.ok) {
                alert('Règlement supprimé avec succès')
                loadReglements()
            } else {
                const error = await response.json()
                alert('Erreur: ' + (error.message || 'Une erreur est survenue'))
            }
        } catch (error) {
            console.error('Erreur:', error)
            alert('Erreur lors de la suppression')
        }
    }
}

// Formatting functions
const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD'
    }).format(value || 0)
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

const getStatusClass = (statut) => {
    switch (statut) {
        case 'paye':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        case 'impaye':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        case 'reporte':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    }
}

const getStatusText = (statut) => {
    switch (statut) {
        case 'paye':
            return 'Payé'
        case 'impaye':
            return 'Impayé'
        case 'reporte':
            return 'Reporté'
        default:
            return statut || 'N/A'
    }
}

// Export functions
const exportToExcel = () => {
    if (reglements.value.length === 0) {
        alert('Aucune donnée à exporter')
        return
    }
    
    const data = reglements.value.map(reg => ({
        'Code': reg.code_reglement,
        'Date': formatDate(reg.date_reglement),
        'Fournisseur': reg.fournisseur?.nom_fournisseur || 'N/A',
        'Type': reg.type_reglement,
        'N° Pièce': reg.numero_piece || '-',
        'Banque': reg.banque || '-',
        'Montant': reg.montant,
        'Statut': getStatusText(reg.statut)
    }))
    
    const headers = Object.keys(data[0])
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => {
            const value = row[header]
            return typeof value === 'string' && value.includes(',') 
                ? `"${value.replace(/"/g, '""')}"` 
                : value
        }).join(','))
    ].join('\n')
    
    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `reglements-fournisseurs-${new Date().toISOString().split('T')[0]}.csv`
    link.click()
}

const exportToPDF = () => {
    if (reglements.value.length === 0) {
        alert('Aucune donnée à exporter')
        return
    }
    
    const printWindow = window.open('', '_blank')
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Règlements Fournisseurs</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { text-align: center; color: #333; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
                th { background-color: #2563eb; color: white; }
                tr:nth-child(even) { background-color: #f2f2f2; }
                .status-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
                .status-paye { background-color: #d4edda; color: #155724; }
                .status-impaye { background-color: #f8d7da; color: #721c24; }
                .status-reporte { background-color: #ffe4c4; color: #8b4513; }
                @media print { button { display: none; } }
            </style>
        </head>
        <body>
            <h1>Liste des Règlements Fournisseurs</h1>
            <p>Date d'export: ${new Date().toLocaleDateString('fr-FR')}</p>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Fournisseur</th>
                        <th>Type</th>
                        <th>N° Pièce</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    ${reglements.value.map(reg => `
                        <tr>
                            <td>${reg.code_reglement}</td>
                            <td>${formatDate(reg.date_reglement)}</td>
                            <td>${reg.fournisseur?.nom_fournisseur || 'N/A'}</td>
                            <td>${reg.type_reglement}</td>
                            <td>${reg.numero_piece || '-'}</td>
                            <td>${formatCurrency(reg.montant)}</td>
                            <td><span class="status-badge status-${reg.statut}">${getStatusText(reg.statut)}</span></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            <br>
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #2563eb; color: white; border: none; cursor: pointer; border-radius: 4px;">
                Imprimer / Sauvegarder en PDF
            </button>
        </body>
        </html>
    `
    
    printWindow.document.write(htmlContent)
    printWindow.document.close()
}

const printReglementPDF = (reglement) => {
    const printWindow = window.open('', '_blank')
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Règlement ${reglement.code_reglement}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 40px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    margin-bottom: 40px;
                    border-bottom: 3px solid #2563eb;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #2563eb;
                    margin: 0 0 10px 0;
                    font-size: 28px;
                }
                .header .code {
                    font-size: 18px;
                    color: #666;
                }
                .info-section {
                    margin-bottom: 30px;
                }
                .info-section h2 {
                    font-size: 16px;
                    color: #2563eb;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 8px;
                    margin-bottom: 15px;
                }
                .info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 15px;
                }
                .info-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px;
                    background-color: #f8f9fa;
                    border-radius: 4px;
                }
                .info-item .label {
                    font-weight: bold;
                    color: #555;
                }
                .info-item .value {
                    color: #333;
                }
                .amount-section {
                    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                    color: white;
                    padding: 25px;
                    border-radius: 8px;
                    text-align: center;
                    margin: 30px 0;
                }
                .amount-section .label {
                    font-size: 14px;
                    opacity: 0.9;
                    margin-bottom: 5px;
                }
                .amount-section .amount {
                    font-size: 32px;
                    font-weight: bold;
                }
                .status-badge {
                    display: inline-block;
                    padding: 6px 16px;
                    border-radius: 20px;
                    font-weight: bold;
                    font-size: 14px;
                }
                .status-paye { background-color: #d4edda; color: #155724; }
                .status-impaye { background-color: #f8d7da; color: #721c24; }
                .status-reporte { background-color: #ffe4c4; color: #8b4513; }
                .footer {
                    margin-top: 50px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    display: flex;
                    justify-content: space-between;
                    font-size: 12px;
                    color: #666;
                }
                .signature-section {
                    margin-top: 60px;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-box {
                    width: 200px;
                    text-align: center;
                }
                .signature-box .line {
                    border-top: 1px solid #333;
                    margin-top: 60px;
                    padding-top: 10px;
                }
                @media print { 
                    button { display: none; } 
                    body { padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>RÈGLEMENT FOURNISSEUR</h1>
                <div class="code">${reglement.code_reglement}</div>
            </div>
            
            <div class="info-section">
                <h2>Informations du règlement</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Date de règlement</span>
                        <span class="value">${formatDate(reglement.date_reglement)}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Statut</span>
                        <span class="value"><span class="status-badge status-${reglement.statut}">${getStatusText(reglement.statut)}</span></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Type de règlement</span>
                        <span class="value">${reglement.type_reglement}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">N° Pièce</span>
                        <span class="value">${reglement.numero_piece || '-'}</span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h2>Informations fournisseur</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Fournisseur</span>
                        <span class="value">${reglement.fournisseur?.nom_fournisseur || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Code fournisseur</span>
                        <span class="value">${reglement.fournisseur?.code_fournisseur || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Banque</span>
                        <span class="value">${reglement.banque || '-'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Bénéficiaire</span>
                        <span class="value">${reglement.nom_beneficiaire || '-'}</span>
                    </div>
                </div>
            </div>
            
            <div class="amount-section">
                <div class="label">MONTANT DU RÈGLEMENT</div>
                <div class="amount">${formatCurrency(reglement.montant)}</div>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="line">Signature émetteur</div>
                </div>
                <div class="signature-box">
                    <div class="line">Signature bénéficiaire</div>
                </div>
            </div>
            
            <div class="footer">
                <span>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</span>
                <span>Bouyahya - Système de Gestion</span>
            </div>
            
            <br><br>
            <button onclick="window.print()" style="padding: 12px 24px; background-color: #2563eb; color: white; border: none; cursor: pointer; border-radius: 6px; font-size: 14px;">
                Imprimer / Sauvegarder en PDF
            </button>
        </body>
        </html>
    `
    
    printWindow.document.write(htmlContent)
    printWindow.document.close()
}

// Initialize
onMounted(() => {
    loadReglements()
    loadFournisseurs()
})
</script>

