<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Gestion des villes</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez les villes disponibles dans les formulaires
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

        <!-- Cities Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 dark:bg-teal-900/30">
                        <svg class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Liste des villes</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Ajoutez ou supprimez les villes disponibles dans les formulaires
                        </p>
                    </div>
                </div>
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
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-xs font-semibold">
                                {{ city.charAt(0).toUpperCase() }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ city }}</span>
                        </div>
                        <button
                            @click="removeCity(city)"
                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            :disabled="removingCity === city"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Aucune ville configurée
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const cities = ref([])
const newCity = ref('')

const isAddingCity = ref(false)
const removingCity = ref(null)

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

const loadSettings = async () => {
    try {
        const response = await fetch('/api/settings')
        const data = await response.json()
        cities.value = data.cities || []
    } catch (error) {
        console.error('Erreur lors du chargement des paramètres:', error)
        showMessage('Erreur lors du chargement des paramètres', true)
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
</style>

