<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Fournisseurs</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Recherchez, filtrez et maintenez facilement la base fournisseurs.
                </p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1 sm:max-w-xs">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="searchTerm"
                        type="search"
                        placeholder="Rechercher par code ou nom..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
                <button type="button" class="btn-primary" @click="goToCreate">
                    Nouveau fournisseur
                </button>
            </div>
        </div>

        <div v-if="feedbackMessage" :class="feedbackClasses" class="rounded-xl px-4 py-3 text-sm">
            {{ feedbackMessage }}
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ filteredFournisseurs.length }} fournisseur(s) trouvé(s)
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                    <span>Par page</span>
                    <select v-model.number="pageSize" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="15">15</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">Code fournisseur</th>
                            <th scope="col" class="px-6 py-3">Nom du fournisseur</th>
                            <th scope="col" class="px-6 py-3">Nom du gérant</th>
                            <th scope="col" class="px-6 py-3">Téléphone</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Ville</th>
                            <th scope="col" class="px-6 py-3">Mode paiement</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="fournisseur in paginatedFournisseurs" :key="fournisseur.id" class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                {{ fournisseur.codeFournisseur }}
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">
                                {{ fournisseur.nomFournisseur }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                {{ fournisseur.nomGerant || '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                {{ fournisseur.telephone || '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                {{ fournisseur.email || '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                {{ fournisseur.ville || '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                {{ fournisseur.modePaiement || '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" class="btn-ghost" @click="openEditModal(fournisseur)">
                                        Modifier
                                    </button>
                                    <button type="button" class="btn-danger" @click="handleDelete(fournisseur)">
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!paginatedFournisseurs.length">
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Aucun fournisseur ne correspond à votre recherche.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 border-t border-gray-100 px-6 py-4 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400 md:flex-row md:items-center md:justify-between">
                <div>
                    Page {{ currentPage }} sur {{ totalPages }}
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn-secondary" :disabled="currentPage === 1" @click="currentPage--">
                        Précédent
                    </button>
                    <button type="button" class="btn-secondary" :disabled="currentPage === totalPages" @click="currentPage++">
                        Suivant
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeEditModal">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                <header class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Modifier {{ editForm.nomFournisseur }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Code fournisseur : {{ editForm.codeFournisseur }}</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeEditModal">
                        <span class="sr-only">Fermer</span>
                        ✕
                    </button>
                </header>

                <form class="space-y-6 px-6 py-6" @submit.prevent="handleUpdate">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="label" for="editNomFournisseur">Nom du fournisseur *</label>
                            <input id="editNomFournisseur" v-model="editForm.nomFournisseur" type="text" class="input" />
                            <p v-if="editErrors.nomFournisseur" class="error">{{ editErrors.nomFournisseur }}</p>
                        </div>
                        <div>
                            <label class="label" for="editNomGerant">Nom du gérant *</label>
                            <input id="editNomGerant" v-model="editForm.nomGerant" type="text" class="input" />
                            <p v-if="editErrors.nomGerant" class="error">{{ editErrors.nomGerant }}</p>
                        </div>
                        <div>
                            <label class="label" for="editTelephone">Numéro de téléphone *</label>
                            <input id="editTelephone" v-model="editForm.telephone" type="text" class="input" />
                            <p v-if="editErrors.telephone" class="error">{{ editErrors.telephone }}</p>
                        </div>
                        <div>
                            <label class="label" for="editEmail">Email</label>
                            <input id="editEmail" v-model="editForm.email" type="email" class="input" />
                            <p v-if="editErrors.email" class="error">{{ editErrors.email }}</p>
                        </div>
                        <div>
                            <label class="label" for="editActivite">Activité</label>
                            <input id="editActivite" v-model="editForm.activite" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label" for="editVille">Ville</label>
                            <select id="editVille" v-model="editForm.ville" class="input">
                                <option value="">Sélectionnez une ville</option>
                                <option v-for="city in villeOptions" :key="city" :value="city">
                                    {{ city }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="editIce">ICE</label>
                            <input id="editIce" v-model="editForm.ice" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label" for="editModePaiement">Mode de paiement</label>
                            <select id="editModePaiement" v-model="editForm.modePaiement" class="input">
                                <option value="">Sélectionnez un mode</option>
                                <option v-for="option in modePaiementOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" class="btn-secondary" @click="closeEditModal">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue'
import { useFournisseurs } from '../../composables/useFournisseurs'

const props = defineProps({
    newFournisseurUrl: {
        type: String,
        default: '/fournisseurs/create'
    }
})

const { fournisseurs, isLoading, fetchFournisseurs, deleteFournisseur, updateFournisseur } = useFournisseurs()

const searchTerm = ref('')
const pageSize = ref(10)
const currentPage = ref(1)

const feedbackMessage = ref('')
const feedbackVariant = ref('success')

const isEditModalOpen = ref(false)
const editForm = reactive({
    id: '',
    codeFournisseur: '',
    nomFournisseur: '',
    nomGerant: '',
    telephone: '',
    email: '',
    activite: '',
    ville: '',
    ice: '',
    modePaiement: ''
})
const editErrors = reactive({})

const villeOptions = [
    'Casablanca',
    'Rabat',
    'Marrakech',
    'Fès',
    'Agadir',
    'Tanger',
    'Kenitra',
    'Oujda',
    'Tetouan'
]
const modePaiementOptions = ['Virement bancaire', 'Espèces', 'Chèque', 'Traite']

const filteredFournisseurs = computed(() => {
    const search = searchTerm.value.trim().toLowerCase()
    if (!search) {
        return fournisseurs.value
    }
    return fournisseurs.value.filter((fournisseur) => {
        return (
            fournisseur.codeFournisseur?.toLowerCase().includes(search) ||
            fournisseur.nomFournisseur?.toLowerCase().includes(search)
        )
    })
})

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredFournisseurs.value.length / pageSize.value) || 1)
})

const paginatedFournisseurs = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return filteredFournisseurs.value.slice(start, start + pageSize.value)
})

watch([searchTerm, pageSize], () => {
    currentPage.value = 1
})

watch(filteredFournisseurs, (newVal) => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value
    }
})

const feedbackClasses = computed(() => {
    if (feedbackVariant.value === 'success') {
        return 'border border-green-200 bg-green-50 text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-200'
    }
    if (feedbackVariant.value === 'error') {
        return 'border border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200'
    }
    return 'border border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200'
})

const setFeedback = (message, variant = 'success') => {
    feedbackMessage.value = message
    feedbackVariant.value = variant
    if (message) {
        setTimeout(() => {
            feedbackMessage.value = ''
        }, 4000)
    }
}

const goToCreate = () => {
    if (props.newFournisseurUrl) {
        window.location.href = props.newFournisseurUrl
    }
}

const handleDelete = (fournisseur) => {
    const confirmation = window.confirm(`Supprimer ${fournisseur.nomFournisseur} ? Cette action est définitive.`)
    if (!confirmation) return

    deleteFournisseur(fournisseur.id)
    setFeedback('Fournisseur supprimé avec succès.')
}

const openEditModal = (fournisseur) => {
    Object.assign(editForm, fournisseur)
    isEditModalOpen.value = true
}

const closeEditModal = () => {
    isEditModalOpen.value = false
    Object.assign(editForm, {
        id: '',
        codeFournisseur: '',
        nomFournisseur: '',
        nomGerant: '',
        telephone: '',
        email: '',
        activite: '',
        ville: '',
        ice: '',
        modePaiement: ''
    })
    Object.keys(editErrors).forEach((key) => {
        editErrors[key] = ''
    })
}

const validateEditForm = () => {
    const requiredFields = ['nomFournisseur', 'nomGerant', 'telephone']
    let isValid = true

    requiredFields.forEach((field) => {
        if (!editForm[field]) {
            editErrors[field] = 'Ce champ est obligatoire.'
            isValid = false
        } else {
            editErrors[field] = ''
        }
    })

    if (editForm.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(editForm.email)) {
        editErrors.email = 'Veuillez entrer une adresse email valide.'
        isValid = false
    } else if (editForm.email) {
        editErrors.email = ''
    }

    return isValid
}

const handleUpdate = () => {
    if (!validateEditForm()) {
        return
    }

    updateFournisseur(editForm.id, {
        ...editForm
    })

    setFeedback('Fournisseur mis à jour avec succès.')
    closeEditModal()
}

onMounted(async () => {
    await fetchFournisseurs()
    
    const searchParams = new URLSearchParams(window.location.search)
    if (searchParams.get('created') === '1') {
        setFeedback('Fournisseur créé avec succès.')
        searchParams.delete('created')
        const newQuery = searchParams.toString()
        const newUrl = newQuery ? `${window.location.pathname}?${newQuery}` : window.location.pathname
        window.history.replaceState({}, '', newUrl)
    }
})
</script>

<style scoped>
@reference '../../../css/app.css';

.btn-primary {
    @apply inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
}

.btn-secondary {
    @apply inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200;
}

.btn-ghost {
    @apply rounded-xl border border-transparent px-3 py-2 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-gray-50 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-800;
}

.btn-danger {
    @apply rounded-xl border border-transparent px-3 py-2 text-sm font-medium text-red-600 transition hover:border-red-200 hover:bg-red-50 dark:text-red-300 dark:hover:border-red-500/40 dark:hover:bg-red-500/10;
}

.label {
    @apply mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300;
}

.input {
    @apply w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100;
}

.error {
    @apply mt-1 text-sm text-red-500;
}
</style>

