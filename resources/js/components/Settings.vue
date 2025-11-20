<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Paramètres de l'application</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez le nom de l'application, le logo et les villes disponibles
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nom de l'application</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Modifiez le nom de votre application
                </p>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Logo de l'application</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Téléchargez ou modifiez le logo de votre application
                </p>
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

        <!-- Cities Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Gestion des villes</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ajoutez ou supprimez les villes disponibles dans les formulaires
                </p>
            </header>
            <div class="px-6 py-6 space-y-4">
                <!-- Add City Form -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input
                            v-model="newCity"
                            type="text"
                            class="input"
                            placeholder="Nom de la ville"
                            @keyup.enter="addCity"
                        />
                    </div>
                    <button
                        @click="addCity"
                        :disabled="isAddingCity || !newCity.trim()"
                        class="btn-primary"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </button>
                </div>

                <!-- Cities List -->
                <div v-if="cities.length > 0" class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="(city, index) in cities"
                        :key="index"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <span class="text-sm text-gray-900 dark:text-white">{{ city }}</span>
                        <button
                            @click="removeCity(city)"
                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            :disabled="removingCity === city"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Aucune ville configurée
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const appName = ref('')
const appLogo = ref(null)
const cities = ref([])
const newCity = ref('')

const isUpdatingName = ref(false)
const isUploadingLogo = ref(false)
const isDeletingLogo = ref(false)
const isAddingCity = ref(false)
const removingCity = ref(null)

const successMessage = ref('')
const errorMessage = ref('')

const fileInput = ref(null)

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
        cities.value = data.cities || []
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
            appLogo.value = data.logo_path
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

const addCity = async () => {
    if (!newCity.value.trim()) return

    try {
        isAddingCity.value = true
        const response = await fetch('/api/settings/cities/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ city: newCity.value.trim() })
        })

        const data = await response.json()
        if (response.ok) {
            cities.value = data.cities
            newCity.value = ''
            showMessage('Ville ajoutée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'ajout')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de l\'ajout de la ville', true)
    } finally {
        isAddingCity.value = false
    }
}

const removeCity = async (city) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer "${city}" ?`)) return

    try {
        removingCity.value = city
        const response = await fetch('/api/settings/cities/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ city })
        })

        const data = await response.json()
        if (response.ok) {
            cities.value = data.cities
            showMessage('Ville supprimée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la suppression de la ville', true)
    } finally {
        removingCity.value = null
    }
}

const getLogoUrl = (path) => {
    if (!path) return ''
    return `/storage/${path}`
}

onMounted(() => {
    loadSettings()
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

