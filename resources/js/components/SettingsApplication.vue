<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Paramètres de l'application</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez le nom, le logo et les options de votre application
                </p>
            </div>
        </div>

        <!-- Success Message -->
        <div
            v-if="successMessage"
            class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-200"
        >
            {{ successMessage }}
        </div>

        <!-- Error Message -->
        <div
            v-if="errorMessage"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
        >
            {{ errorMessage }}
        </div>

        <!-- App Name Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nom de l'application</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Modifiez le nom de votre application
                        </p>
                    </div>
                </div>
            </header>
            <div class="px-6 py-6">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input
                            v-model="appName"
                            type="text"
                            class="input"
                            placeholder="Nom de l'application"
                        />
                    </div>
                    <button
                        @click="updateAppName"
                        :disabled="isUpdatingName"
                        class="btn-primary"
                    >
                        <svg
                            v-if="isUpdatingName"
                            class="mr-2 h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8" />
                        </svg>
                        {{ isUpdatingName ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                </div>
            </div>
        </section>

        <!-- App Logo Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Logo de l'application</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Téléchargez ou modifiez le logo de votre application
                        </p>
                    </div>
                </div>
            </header>
            <div class="px-6 py-6">
                <div class="flex items-center gap-6">
                    <!-- Logo Preview -->
                    <div class="flex-shrink-0">
                        <div
                            v-if="appLogo"
                            class="h-24 w-24 rounded-lg border-2 border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800 flex items-center justify-center"
                        >
                            <img
                                :src="getLogoUrl(appLogo)"
                                alt="Logo"
                                class="h-full w-full object-contain"
                            />
                        </div>
                        <div
                            v-else
                            class="h-24 w-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-50 dark:bg-gray-800"
                        >
                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="flex-1 space-y-3">
                        <div>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <div class="flex gap-3">
                                <button
                                    @click="$refs.fileInput.click()"
                                    class="btn-secondary"
                                    :disabled="isUploadingLogo"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Télécharger un logo
                                </button>
                                <button
                                    v-if="appLogo"
                                    @click="deleteLogo"
                                    class="btn-danger"
                                    :disabled="isDeletingLogo"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Supprimer
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Formats acceptés: JPG, PNG, GIF, SVG (max. 2 Mo)
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Types Règlement Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30">
                            <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Types Règlement</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Gérez les types de règlement disponibles dans les formulaires
                            </p>
                        </div>
                    </div>
                    <button
                        @click="showTypeReglementForm = !showTypeReglementForm"
                        class="btn-primary text-sm"
                    >
                        <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ showTypeReglementForm ? 'Annuler' : 'Ajouter' }}
                    </button>
                </div>
            </header>
            <div class="px-6 py-4">
                <!-- Add Form -->
                <div v-if="showTypeReglementForm" class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                            <input
                                v-model="typeReglementForm.code"
                                type="text"
                                class="input"
                                placeholder="Ex: VIR"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Libellé</label>
                            <input
                                v-model="typeReglementForm.libelle"
                                type="text"
                                class="input"
                                placeholder="Ex: Virement"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="saveTypeReglement"
                                :disabled="isSavingTypeReglement"
                                class="btn-primary w-full"
                            >
                                {{ editingTypeReglementId ? 'Modifier' : 'Ajouter' }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- List -->
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="type in typesReglement"
                        :key="type.id"
                        class="flex items-center gap-2 px-3 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg text-sm"
                    >
                        <span class="font-medium">{{ type.libelle }}</span>
                        <span class="text-xs text-green-600 dark:text-green-400">({{ type.code }})</span>
                        <button
                            @click="editTypeReglement(type)"
                            class="ml-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200"
                            title="Modifier"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            @click="removeTypeReglement(type)"
                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            title="Supprimer"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="typesReglement.length === 0" class="text-gray-500 dark:text-gray-400 text-sm py-2">
                        Aucun type règlement configuré
                    </div>
                </div>
            </div>
        </section>

        <!-- Échéances Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-900/30">
                            <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Échéances</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Gérez les options d'échéance disponibles dans les formulaires
                            </p>
                        </div>
                    </div>
                    <button
                        @click="showEcheanceForm = !showEcheanceForm"
                        class="btn-primary text-sm"
                    >
                        <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ showEcheanceForm ? 'Annuler' : 'Ajouter' }}
                    </button>
                </div>
            </header>
            <div class="px-6 py-4">
                <!-- Add Form -->
                <div v-if="showEcheanceForm" class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                            <input
                                v-model="echeanceForm.code"
                                type="text"
                                class="input"
                                placeholder="Ex: 30J"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Libellé</label>
                            <input
                                v-model="echeanceForm.libelle"
                                type="text"
                                class="input"
                                placeholder="Ex: 30 jours"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jours</label>
                            <input
                                v-model.number="echeanceForm.jours"
                                type="number"
                                class="input"
                                placeholder="30"
                                min="0"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="saveEcheance"
                                :disabled="isSavingEcheance"
                                class="btn-primary w-full"
                            >
                                {{ editingEcheanceId ? 'Modifier' : 'Ajouter' }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- List -->
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="ech in echeances"
                        :key="ech.id"
                        class="flex items-center gap-2 px-3 py-2 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200 rounded-lg text-sm"
                    >
                        <span class="font-medium">{{ ech.libelle }}</span>
                        <span v-if="ech.jours" class="text-xs text-orange-600 dark:text-orange-400">({{ ech.jours }}j)</span>
                        <button
                            @click="editEcheance(ech)"
                            class="ml-1 text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-200"
                            title="Modifier"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            @click="removeEcheance(ech)"
                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            title="Supprimer"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="echeances.length === 0" class="text-gray-500 dark:text-gray-400 text-sm py-2">
                        Aucune échéance configurée
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const appName = ref('')
const appLogo = ref(null)

const isUpdatingName = ref(false)
const isUploadingLogo = ref(false)
const isDeletingLogo = ref(false)

const successMessage = ref('')
const errorMessage = ref('')

const fileInput = ref(null)

// Types Règlement
const typesReglement = ref([])
const showTypeReglementForm = ref(false)
const editingTypeReglementId = ref(null)
const isSavingTypeReglement = ref(false)
const typeReglementForm = ref({
    code: '',
    libelle: ''
})

// Échéances
const echeances = ref([])
const showEcheanceForm = ref(false)
const editingEcheanceId = ref(null)
const isSavingEcheance = ref(false)
const echeanceForm = ref({
    code: '',
    libelle: '',
    jours: null
})

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

const loadSettings = async () => {
    try {
        const response = await fetch('/api/settings')
        const data = await response.json()
        appName.value = data.app_name
        appLogo.value = data.app_logo
        // Force reload image if logo exists
        if (appLogo.value) {
            // Create a new image object to check if it loads
            const img = new Image()
            img.onerror = () => {
                console.warn('Logo image failed to load, path:', appLogo.value)
            }
            img.src = getLogoUrl(appLogo.value)
        }
    } catch (error) {
        console.error('Erreur lors du chargement des paramètres:', error)
        showMessage('Erreur lors du chargement des paramètres', true)
    }
}

const updateAppName = async () => {
    if (!appName.value.trim()) {
        showMessage('Le nom de l\'application ne peut pas être vide', true)
        return
    }

    try {
        isUpdatingName.value = true
        const response = await fetch('/api/settings/app-name', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ app_name: appName.value })
        })

        if (response.ok) {
            showMessage('Nom de l\'application mis à jour avec succès')
        } else {
            throw new Error('Erreur lors de la mise à jour')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la mise à jour du nom', true)
    } finally {
        isUpdatingName.value = false
    }
}

const handleFileChange = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('logo', file)

    try {
        isUploadingLogo.value = true
        const response = await fetch('/api/settings/logo', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })

        const data = await response.json()
        if (response.ok) {
            appLogo.value = data.logo_path || data.logo_url
            showMessage('Logo téléchargé avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors du téléchargement')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors du téléchargement du logo', true)
    } finally {
        isUploadingLogo.value = false
        fileInput.value.value = ''
    }
}

const deleteLogo = async () => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer le logo ?')) return

    try {
        isDeletingLogo.value = true
        const response = await fetch('/api/settings/logo', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })

        if (response.ok) {
            appLogo.value = null
            showMessage('Logo supprimé avec succès')
        } else {
            throw new Error('Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la suppression du logo', true)
    } finally {
        isDeletingLogo.value = false
    }
}

const getLogoUrl = (path) => {
    if (!path) return ''
    // If path already starts with /storage, return as is
    if (path.startsWith('/storage/')) {
        return path
    }
    // Otherwise, prepend /storage/
    return `/storage/${path}`
}

// =====================================================
// TYPES RÈGLEMENT
// =====================================================

const loadTypesReglement = async () => {
    try {
        const response = await fetch('/api/settings/types-reglement')
        const data = await response.json()
        typesReglement.value = data.types_reglement || []
    } catch (error) {
        console.error('Erreur lors du chargement des types règlement:', error)
    }
}

const resetTypeReglementForm = () => {
    typeReglementForm.value = { code: '', libelle: '' }
    editingTypeReglementId.value = null
}

const editTypeReglement = (type) => {
    editingTypeReglementId.value = type.id
    typeReglementForm.value = {
        code: type.code || '',
        libelle: type.libelle || ''
    }
    showTypeReglementForm.value = true
}

const saveTypeReglement = async () => {
    if (!typeReglementForm.value.code || !typeReglementForm.value.libelle) {
        showMessage('Veuillez remplir le code et le libellé', true)
        return
    }

    try {
        isSavingTypeReglement.value = true
        const url = editingTypeReglementId.value 
            ? '/api/settings/types-reglement/update'
            : '/api/settings/types-reglement/add'
        
        const body = editingTypeReglementId.value
            ? { ...typeReglementForm.value, id: editingTypeReglementId.value }
            : typeReglementForm.value

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(body)
        })

        const data = await response.json()
        if (response.ok) {
            typesReglement.value = data.types_reglement
            resetTypeReglementForm()
            showTypeReglementForm.value = false
            showMessage(editingTypeReglementId.value ? 'Type règlement modifié' : 'Type règlement ajouté')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'enregistrement')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'enregistrement', true)
    } finally {
        isSavingTypeReglement.value = false
    }
}

const removeTypeReglement = async (type) => {
    if (!confirm(`Supprimer "${type.libelle}" ?`)) return

    try {
        const response = await fetch('/api/settings/types-reglement/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: type.id })
        })

        const data = await response.json()
        if (response.ok) {
            typesReglement.value = data.types_reglement
            showMessage('Type règlement supprimé')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la suppression', true)
    }
}

// =====================================================
// ÉCHÉANCES
// =====================================================

const loadEcheances = async () => {
    try {
        const response = await fetch('/api/settings/echeances')
        const data = await response.json()
        echeances.value = data.echeances || []
    } catch (error) {
        console.error('Erreur lors du chargement des échéances:', error)
    }
}

const resetEcheanceForm = () => {
    echeanceForm.value = { code: '', libelle: '', jours: null }
    editingEcheanceId.value = null
}

const editEcheance = (ech) => {
    editingEcheanceId.value = ech.id
    echeanceForm.value = {
        code: ech.code || '',
        libelle: ech.libelle || '',
        jours: ech.jours || null
    }
    showEcheanceForm.value = true
}

const saveEcheance = async () => {
    if (!echeanceForm.value.code || !echeanceForm.value.libelle) {
        showMessage('Veuillez remplir le code et le libellé', true)
        return
    }

    try {
        isSavingEcheance.value = true
        const url = editingEcheanceId.value 
            ? '/api/settings/echeances/update'
            : '/api/settings/echeances/add'
        
        const body = editingEcheanceId.value
            ? { ...echeanceForm.value, id: editingEcheanceId.value }
            : echeanceForm.value

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(body)
        })

        const data = await response.json()
        if (response.ok) {
            echeances.value = data.echeances
            resetEcheanceForm()
            showEcheanceForm.value = false
            showMessage(editingEcheanceId.value ? 'Échéance modifiée' : 'Échéance ajoutée')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'enregistrement')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'enregistrement', true)
    } finally {
        isSavingEcheance.value = false
    }
}

const removeEcheance = async (ech) => {
    if (!confirm(`Supprimer "${ech.libelle}" ?`)) return

    try {
        const response = await fetch('/api/settings/echeances/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: ech.id })
        })

        const data = await response.json()
        if (response.ok) {
            echeances.value = data.echeances
            showMessage('Échéance supprimée')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la suppression', true)
    }
}

onMounted(() => {
    loadSettings()
    loadTypesReglement()
    loadEcheances()
})
</script>

<style scoped>
@reference '../../css/app.css';

.input {
    @apply w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100;
}

.btn-primary {
    @apply inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-400;
}

.btn-secondary {
    @apply inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-500 dark:hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50;
}

.btn-danger {
    @apply inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50;
}
</style>

