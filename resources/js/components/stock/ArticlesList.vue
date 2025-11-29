<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Articles</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez les articles de votre inventaire
                </p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouvel article
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

        <!-- Search and Filters -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="input pl-10"
                        placeholder="Rechercher par référence ou désignation..."
                        @input="handleSearch"
                    />
                </div>
            </div>
            <select v-model="filterFamille" class="input w-auto" @change="loadArticles">
                <option value="">Toutes les familles</option>
                <option v-for="famille in familles" :key="famille.id" :value="famille.id">
                    {{ famille.nom }}
                </option>
            </select>
        </div>

        <!-- Articles Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:border-gray-700 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Désignation</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Famille</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Unité</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">TVA</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="isLoading">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Chargement des articles...</p>
                            </td>
                        </tr>
                        <tr v-else-if="articles.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Aucun article trouvé</p>
                                <button @click="openCreateModal" class="mt-4 btn-primary">
                                    Créer votre premier article
                                </button>
                            </td>
                        </tr>
                        <tr 
                            v-else
                            v-for="article in articles" 
                            :key="article.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ article.reference }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ article.designation }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                <span v-if="article.famille_nom">{{ article.famille_nom }}</span>
                                <span v-else class="text-gray-400 italic">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                <span v-if="article.unite_mesure" class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 text-xs font-medium">
                                    {{ article.unite_mesure.abreviation }}
                                </span>
                                <span v-else class="text-gray-400 italic">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ article.tva }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span :class="[
                                    'font-medium',
                                    article.stock_actuel <= article.stock_minimum ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'
                                ]">
                                    {{ article.stock_actuel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button
                                    @click="editArticle(article)"
                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteArticle(article)"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    :disabled="article.stock_actuel > 0"
                                    :class="{ 'opacity-50 cursor-not-allowed': article.stock_actuel > 0 }"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeModal"></div>
                
                <!-- Modal Content -->
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                    <!-- Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ isEditing ? 'Modifier l\'article' : 'Nouvel Article' }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ isEditing ? 'Modifiez les informations de l\'article' : 'Remplissez les informations ci-dessous pour créer un nouvel article.' }}
                        </p>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-6 space-y-6">
                        <!-- Row 1: Reference & Designation -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Référence Article
                                </label>
                                <input
                                    v-model="form.reference"
                                    type="text"
                                    class="input"
                                    placeholder="Entrez la référence"
                                    :disabled="isEditing"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Désignation Article
                                </label>
                                <input
                                    v-model="form.designation"
                                    type="text"
                                    class="input"
                                    placeholder="Entrez la désignation"
                                />
                            </div>
                        </div>

                        <!-- Row 2: Famille & Sous-Famille -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Famille Article
                                </label>
                                <select v-model="form.famille_id" class="input" @change="onFamilleChange">
                                    <option value="">Sélectionner une famille</option>
                                    <option v-for="famille in familles" :key="famille.id" :value="famille.id">
                                        {{ famille.nom }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Sous-Famille
                                </label>
                                <select 
                                    v-model="form.sous_famille_id" 
                                    class="input"
                                    :disabled="!form.famille_id || filteredSousFamilles.length === 0"
                                >
                                    <option value="">Sélectionner une sous-famille</option>
                                    <option v-for="sf in filteredSousFamilles" :key="sf.id" :value="sf.id">
                                        {{ sf.nom }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Unite & TVA -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Unité de mesure
                                </label>
                                <select v-model="form.unite_mesure_id" class="input">
                                    <option value="">Sélectionner une unité</option>
                                    <option v-for="unite in unitesMesure" :key="unite.id" :value="unite.id">
                                        {{ unite.nom }} ({{ unite.abreviation }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    TVA (%)
                                </label>
                                <input
                                    v-model="form.tva"
                                    type="number"
                                    class="input"
                                    placeholder="ex: 20"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                />
                            </div>
                        </div>

                        <!-- Row 4: Allow negative stock -->
                        <div class="flex items-center justify-between py-4 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Autoriser stock négatif</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Permet à la quantité en stock de devenir négative.
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="form.autoriser_stock_negatif = !form.autoriser_stock_negatif"
                                :class="[
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                                    form.autoriser_stock_negatif ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                        form.autoriser_stock_negatif ? 'translate-x-5' : 'translate-x-0'
                                    ]"
                                ></span>
                            </button>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end gap-3">
                        <button @click="closeModal" class="btn-secondary">
                            Annuler
                        </button>
                        <button @click="saveArticle" class="btn-primary" :disabled="isSaving">
                            <svg v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isSaving ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Data
const articles = ref([])
const familles = ref([])
const sousFamilles = ref([])
const unitesMesure = ref([])

// UI State
const isLoading = ref(false)
const isSaving = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

// Search & Filter
const searchQuery = ref('')
const filterFamille = ref('')

// Messages
const successMessage = ref('')
const errorMessage = ref('')

// Form
const form = ref({
    reference: '',
    designation: '',
    famille_id: '',
    sous_famille_id: '',
    unite_mesure_id: '',
    tva: 20,
    autoriser_stock_negatif: false
})

// Computed
const filteredSousFamilles = computed(() => {
    if (!form.value.famille_id) return []
    return sousFamilles.value.filter(sf => sf.famille_id === form.value.famille_id)
})

// Methods
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
    }, 4000)
}

const loadReferenceData = async () => {
    try {
        const response = await fetch('/api/articles/reference-data')
        const data = await response.json()
        familles.value = data.familles || []
        sousFamilles.value = data.sous_familles || []
        unitesMesure.value = data.unites_mesure || []
    } catch (error) {
        console.error('Error loading reference data:', error)
    }
}

const loadArticles = async () => {
    try {
        isLoading.value = true
        let url = '/api/articles'
        const params = new URLSearchParams()
        
        if (searchQuery.value) {
            params.append('search', searchQuery.value)
        }
        if (filterFamille.value) {
            params.append('famille_id', filterFamille.value)
        }
        
        if (params.toString()) {
            url += '?' + params.toString()
        }
        
        const response = await fetch(url)
        const data = await response.json()
        articles.value = data.articles || []
    } catch (error) {
        console.error('Error loading articles:', error)
        showMessage('Erreur lors du chargement des articles', true)
    } finally {
        isLoading.value = false
    }
}

const handleSearch = () => {
    // Debounce search
    clearTimeout(window.searchTimeout)
    window.searchTimeout = setTimeout(() => {
        loadArticles()
    }, 300)
}

const getNextReference = async () => {
    try {
        const response = await fetch('/api/articles/next-reference')
        const data = await response.json()
        return data.reference
    } catch (error) {
        console.error('Error getting next reference:', error)
        return 'ART-0001'
    }
}

const openCreateModal = async () => {
    isEditing.value = false
    editingId.value = null
    form.value = {
        reference: await getNextReference(),
        designation: '',
        famille_id: '',
        sous_famille_id: '',
        unite_mesure_id: '',
        tva: 20,
        autoriser_stock_negatif: false
    }
    showModal.value = true
}

const editArticle = (article) => {
    isEditing.value = true
    editingId.value = article.id
    form.value = {
        reference: article.reference,
        designation: article.designation,
        famille_id: article.famille_id || '',
        sous_famille_id: article.sous_famille_id || '',
        unite_mesure_id: article.unite_mesure_id || '',
        tva: article.tva,
        autoriser_stock_negatif: article.autoriser_stock_negatif
    }
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    isEditing.value = false
    editingId.value = null
}

const onFamilleChange = () => {
    // Reset sous-famille when famille changes
    form.value.sous_famille_id = ''
}

const saveArticle = async () => {
    // Validation
    if (!form.value.reference.trim()) {
        showMessage('La référence est obligatoire', true)
        return
    }
    if (!form.value.designation.trim()) {
        showMessage('La désignation est obligatoire', true)
        return
    }

    try {
        isSaving.value = true
        
        const url = isEditing.value 
            ? `/api/articles/${editingId.value}` 
            : '/api/articles'
        
        const method = isEditing.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                reference: form.value.reference,
                designation: form.value.designation,
                famille_id: form.value.famille_id || null,
                sous_famille_id: form.value.sous_famille_id || null,
                unite_mesure_id: form.value.unite_mesure_id || null,
                tva: form.value.tva,
                autoriser_stock_negatif: form.value.autoriser_stock_negatif
            })
        })

        const data = await response.json()

        if (response.ok) {
            showMessage(isEditing.value ? 'Article mis à jour avec succès' : 'Article créé avec succès')
            closeModal()
            loadArticles()
        } else {
            throw new Error(data.message || Object.values(data.errors || {}).flat().join(', ') || 'Erreur lors de l\'enregistrement')
        }
    } catch (error) {
        console.error('Error saving article:', error)
        showMessage(error.message || 'Erreur lors de l\'enregistrement', true)
    } finally {
        isSaving.value = false
    }
}

const deleteArticle = async (article) => {
    if (article.stock_actuel > 0) {
        showMessage('Impossible de supprimer un article avec du stock', true)
        return
    }

    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'article "${article.designation}" ?`)) {
        return
    }

    try {
        const response = await fetch(`/api/articles/${article.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })

        const data = await response.json()

        if (response.ok) {
            showMessage('Article supprimé avec succès')
            loadArticles()
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Error deleting article:', error)
        showMessage(error.message || 'Erreur lors de la suppression', true)
    }
}

// Lifecycle
onMounted(() => {
    loadReferenceData()
    loadArticles()
})
</script>

<style scoped>
@reference '../../../css/app.css';

.input {
    @apply w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed dark:disabled:bg-gray-700;
}

.btn-primary {
    @apply inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-400;
}

.btn-secondary {
    @apply inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700;
}
</style>

