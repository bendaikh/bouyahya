<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Gestion des commerciales</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez les commerciales disponibles dans les formulaires
                </p>
            </div>
            <button
                @click="showForm = !showForm"
                class="btn-primary"
            >
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span v-if="!showForm">Nouveau Commercial</span>
                <span v-else>Annuler</span>
            </button>
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

        <!-- Add/Edit Form -->
        <section v-if="showForm" class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ editingId ? 'Modifier le commercial' : 'Nouveau commercial' }}
                </h3>
            </header>
            <div class="px-6 py-6">
                <form @submit.prevent="saveCommerciale" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Code Commercial -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Code Commercial <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.code_commercial"
                                type="text"
                                class="input"
                                placeholder="Code commercial"
                                required
                            />
                        </div>

                        <!-- Nom Commercial -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nom Commercial <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.nom_commercial"
                                type="text"
                                class="input"
                                placeholder="Nom commercial"
                                required
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="cancelForm"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="isSaving"
                            class="btn-primary"
                        >
                            <span v-if="isSaving">Enregistrement...</span>
                            <span v-else>{{ editingId ? 'Modifier' : 'Ajouter' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Commerciales Table -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Liste des commerciales</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Consultez et gérez tous les commerciales enregistrés
                        </p>
                    </div>
                </div>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="commerciale in commerciales" :key="commerciale.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ commerciale.code_commercial }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ commerciale.nom_commercial }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="editCommerciale(commerciale)"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                        title="Modifier"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="removeCommerciale(commerciale)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        :disabled="removingCommerciale === commerciale.id"
                                        title="Supprimer"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="commerciales.length === 0">
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Aucun commercial configuré
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const commerciales = ref([])
const showForm = ref(false)
const editingId = ref(null)
const isSaving = ref(false)
const removingCommerciale = ref(null)

const formData = ref({
    code_commercial: '',
    nom_commercial: ''
})

const successMessage = ref('')
const errorMessage = ref('')

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

const resetForm = () => {
    formData.value = {
        code_commercial: '',
        nom_commercial: ''
    }
    editingId.value = null
}

const cancelForm = () => {
    resetForm()
    showForm.value = false
}

const loadCommerciales = async () => {
    try {
        const response = await fetch('/api/settings/commerciales')
        const data = await response.json()
        // Handle both old format (strings) and new format (objects)
        commerciales.value = (data.commerciales || []).map(c => {
            if (typeof c === 'string') {
                // Legacy format - convert to new format
                return {
                    id: uniqid('com_'),
                    code_commercial: c,
                    nom_commercial: c
                }
            }
            return c
        })
    } catch (error) {
        console.error('Erreur lors du chargement des commerciales:', error)
        showMessage('Erreur lors du chargement des commerciales', true)
    }
}

const saveCommerciale = async () => {
    try {
        isSaving.value = true
        const url = editingId.value 
            ? '/api/settings/commerciales/update'
            : '/api/settings/commerciales/add'
        
        const body = editingId.value
            ? { ...formData.value, id: editingId.value }
            : formData.value

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
            commerciales.value = data.commerciales
            resetForm()
            showForm.value = false
            showMessage(editingId.value ? 'Commercial modifié avec succès' : 'Commercial ajouté avec succès')
        } else {
            throw new Error(data.message || Object.values(data.errors || {}).flat().join(', ') || 'Erreur lors de l\'enregistrement')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'enregistrement', true)
    } finally {
        isSaving.value = false
    }
}

const editCommerciale = (commerciale) => {
    editingId.value = commerciale.id
    formData.value = {
        code_commercial: commerciale.code_commercial || '',
        nom_commercial: commerciale.nom_commercial || ''
    }
    showForm.value = true
    // Scroll to form
    setTimeout(() => {
        document.querySelector('section[v-if="showForm"]')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }, 100)
}

const removeCommerciale = async (commerciale) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer "${commerciale.nom_commercial || commerciale.code_commercial}" ?`)) return

    try {
        removingCommerciale.value = commerciale.id
        const response = await fetch('/api/settings/commerciales/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: commerciale.id })
        })

        const data = await response.json()
        if (response.ok) {
            commerciales.value = data.commerciales
            showMessage('Commercial supprimé avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la suppression du commercial', true)
    } finally {
        removingCommerciale.value = null
    }
}

const uniqid = (prefix = '') => {
    return prefix + Date.now().toString(36) + Math.random().toString(36).substr(2)
}

onMounted(() => {
    loadCommerciales()
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
</style>
