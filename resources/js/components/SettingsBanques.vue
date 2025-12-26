<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Gestion des Banques</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez les banques disponibles dans les sélections de règlements
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

        <!-- Banques Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Liste des Banques</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Ajoutez ou supprimez les banques disponibles
                        </p>
                    </div>
                </div>
            </header>
            <div class="px-6 py-6 space-y-4">
                <!-- Add Banque Form -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input
                            v-model="newBanque"
                            type="text"
                            class="input"
                            placeholder="Nom de la banque (ex: Attijariwafa Bank, BMCE Bank...)"
                            @keyup.enter="addBanque"
                        />
                    </div>
                    <button
                        @click="addBanque"
                        :disabled="isAddingBanque || !newBanque.trim()"
                        class="btn-primary"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </button>
                </div>

                <!-- Banques List -->
                <div v-if="banques.length > 0" class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="banque in banques"
                        :key="banque.id"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ banque.nom }}</span>
                        </div>
                        <button
                            @click="removeBanque(banque)"
                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            :disabled="removingBanque === banque.id"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Aucune banque configurée
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const banques = ref([])
const newBanque = ref('')
const isAddingBanque = ref(false)
const removingBanque = ref(null)

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

const loadBanques = async () => {
    try {
        const response = await fetch('/api/settings/banques')
        const data = await response.json()
        banques.value = data.banques || []
    } catch (error) {
        console.error('Erreur lors du chargement des banques:', error)
        showMessage('Erreur lors du chargement des banques', true)
    }
}

const addBanque = async () => {
    if (!newBanque.value.trim()) return

    try {
        isAddingBanque.value = true
        const response = await fetch('/api/settings/banques/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ nom: newBanque.value.trim() })
        })

        const data = await response.json()
        if (response.ok) {
            banques.value = data.banques
            newBanque.value = ''
            showMessage('Banque ajoutée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'ajout')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'ajout de la banque', true)
    } finally {
        isAddingBanque.value = false
    }
}

const removeBanque = async (banque) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer "${banque.nom}" ?`)) return

    try {
        removingBanque.value = banque.id
        const response = await fetch('/api/settings/banques/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: banque.id })
        })

        const data = await response.json()
        if (response.ok) {
            banques.value = data.banques
            showMessage('Banque supprimée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de la suppression de la banque', true)
    } finally {
        removingBanque.value = null
    }
}

onMounted(() => {
    loadBanques()
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

