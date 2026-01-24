<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Clients</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Recherchez, filtrez et maintenez facilement la base clients.
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
                        placeholder="Rechercher par code ou raison sociale..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
                <button type="button" class="btn-primary" @click="goToCreate">
                    Nouveau client
                </button>
            </div>
        </div>

        <div v-if="feedbackMessage" :class="feedbackClasses" class="rounded-xl px-4 py-3 text-sm">
            {{ feedbackMessage }}
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ filteredClients.length }} client(s) trouvé(s)
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
                            <th scope="col" class="px-6 py-3">Code client</th>
                            <th scope="col" class="px-6 py-3">Raison sociale</th>
                            <th scope="col" class="px-6 py-3">Ville</th>
                            <th scope="col" class="px-6 py-3">Téléphone</th>
                            <th scope="col" class="px-6 py-3">Type client</th>
                            <th scope="col" class="px-6 py-3">Mode paiement</th>
                            <th scope="col" class="px-6 py-3">Échéance</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="client in paginatedClients" :key="client.id" class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                {{ client.codeClient }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 dark:text-gray-100">{{ client.raisonSociale }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ client.nomGerant || '—' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">{{ client.ville || '—' }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">{{ client.telephone || '—' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-200"
                                >
                                    {{ client.typeClient }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">{{ client.modePaiement }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ client.echeance }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="client.bloquer ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-200' : 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-200'"
                                    >
                                        {{ client.bloquer ? 'Bloqué' : 'Actif' }}
                                    </span>
                                    <button type="button" class="btn-ghost" @click="openEditModal(client)">
                                        Éditer
                                    </button>
                                    <button type="button" class="btn-danger" @click="handleDelete(client)">
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!paginatedClients.length">
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Aucun client ne correspond à votre recherche.
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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Modifier {{ editForm.raisonSociale }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Code client : {{ editForm.codeClient }}</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeEditModal">
                        <span class="sr-only">Fermer</span>
                        ✕
                    </button>
                </header>

                <form class="space-y-6 px-6 py-6" @submit.prevent="handleUpdate">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="label" for="editRaisonSociale">Raison sociale *</label>
                            <input id="editRaisonSociale" v-model="editForm.raisonSociale" type="text" class="input" />
                            <p v-if="editErrors.raisonSociale" class="error">{{ editErrors.raisonSociale }}</p>
                        </div>
                        <div>
                            <label class="label" for="editNomGerant">Nom du gérant *</label>
                            <input id="editNomGerant" v-model="editForm.nomGerant" type="text" class="input" />
                            <p v-if="editErrors.nomGerant" class="error">{{ editErrors.nomGerant }}</p>
                        </div>
                        <div>
                            <label class="label" for="editVille">Ville *</label>
                            <select id="editVille" v-model="editForm.ville" class="input">
                                <option value="">Sélectionnez une ville</option>
                                <option v-for="city in villeOptions" :key="city" :value="city">
                                    {{ city }}
                                </option>
                            </select>
                            <p v-if="editErrors.ville" class="error">{{ editErrors.ville }}</p>
                        </div>
                        <div>
                            <label class="label" for="editTelephone">Téléphone</label>
                            <input id="editTelephone" v-model="editForm.telephone" type="text" class="input" placeholder="Ex: +212 6XX XXX XXX" />
                        </div>
                        <div>
                            <label class="label" for="editTypeClient">Type client *</label>
                            <select id="editTypeClient" v-model="editForm.typeClient" class="input">
                                <option value="">Sélectionnez un type</option>
                                <option v-for="option in typeOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                            <p v-if="editErrors.typeClient" class="error">{{ editErrors.typeClient }}</p>
                        </div>
                        <div>
                            <label class="label" for="editModePaiement">Mode de paiement *</label>
                            <select id="editModePaiement" v-model="editForm.modePaiement" class="input">
                                <option value="">Sélectionnez un mode</option>
                                <option v-for="option in modePaiementOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                            <p v-if="editErrors.modePaiement" class="error">{{ editErrors.modePaiement }}</p>
                        </div>
                        <div>
                            <label class="label" for="editEcheance">Échéance *</label>
                            <select id="editEcheance" v-model="editForm.echeance" class="input">
                                <option value="">Choisissez une échéance</option>
                                <option v-for="option in echeanceOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                            <p v-if="editErrors.echeance" class="error">{{ editErrors.echeance }}</p>
                        </div>
                        <div>
                            <label class="label" for="editBanque">Banque</label>
                            <input id="editBanque" v-model="editForm.banque" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label" for="editRib">RIB</label>
                            <input id="editRib" v-model="editForm.rib" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label" for="editPlafond">Plafond (MAD)</label>
                            <input id="editPlafond" v-model.number="editForm.plafond" type="number" min="0" class="input" />
                            <p v-if="editErrors.plafond" class="error">{{ editErrors.plafond }}</p>
                        </div>
                        <div class="flex items-center gap-3 md:col-span-2">
                            <span class="label mb-0">Bloquer</span>
                            <button
                                type="button"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="editForm.bloquer ? 'bg-red-500' : 'bg-gray-200 dark:bg-gray-700'"
                                @click="editForm.bloquer = !editForm.bloquer"
                                role="switch"
                                :aria-checked="editForm.bloquer"
                            >
                                <span
                                    aria-hidden="true"
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="editForm.bloquer ? 'translate-x-5' : 'translate-x-0'"
                                ></span>
                            </button>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ editForm.bloquer ? 'Client bloqué' : 'Client actif' }}
                            </span>
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
import { useClients } from '../../composables/useClients'
import { useSettings } from '../../composables/useSettings'

const props = defineProps({
    newClientUrl: {
        type: String,
        default: '/clients/create'
    }
})

const { clients, isLoading, fetchClients, deleteClient, updateClient } = useClients()
const { fetchCities } = useSettings()

const searchTerm = ref('')
const pageSize = ref(10)
const currentPage = ref(1)

const feedbackMessage = ref('')
const feedbackVariant = ref('success')

const isEditModalOpen = ref(false)
const editForm = reactive({
    id: '',
    codeClient: '',
    raisonSociale: '',
    nomGerant: '',
    ville: '',
    telephone: '',
    typeClient: '',
    modePaiement: '',
    echeance: '',
    banque: '',
    rib: '',
    plafond: null,
    bloquer: false
})
const editErrors = reactive({})

const villeOptions = ref([])
const typeOptions = ['REV', 'PROMO', 'ENTR', 'CON.FI']
const modePaiementOptions = ['Espèces', 'Virement', 'Chèque', 'Traite']
const echeanceOptions = ['0j', '30j', '45j', '60j', '90j']

const filteredClients = computed(() => {
    const search = searchTerm.value.trim().toLowerCase()
    if (!search) {
        return clients.value
    }
    return clients.value.filter((client) => {
        return (
            client.codeClient?.toLowerCase().includes(search) ||
            client.raisonSociale?.toLowerCase().includes(search)
        )
    })
})

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredClients.value.length / pageSize.value) || 1)
})

const paginatedClients = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return filteredClients.value.slice(start, start + pageSize.value)
})

watch([searchTerm, pageSize], () => {
    currentPage.value = 1
})

watch(filteredClients, (newVal) => {
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
    if (props.newClientUrl) {
        window.location.href = props.newClientUrl
    }
}

const handleDelete = (client) => {
    const confirmation = window.confirm(`Supprimer ${client.raisonSociale} ? Cette action est définitive.`)
    if (!confirmation) return

    deleteClient(client.id)
    setFeedback('Client supprimé avec succès.')
}

const openEditModal = (client) => {
    Object.assign(editForm, client)
    if (client.ville && !villeOptions.value.includes(client.ville)) {
        villeOptions.value = [client.ville, ...villeOptions.value]
    }
    isEditModalOpen.value = true
}

const closeEditModal = () => {
    isEditModalOpen.value = false
    Object.assign(editForm, {
        id: '',
        codeClient: '',
        raisonSociale: '',
        nomGerant: '',
        ville: '',
        telephone: '',
        typeClient: '',
        modePaiement: '',
        echeance: '',
        banque: '',
        rib: '',
        plafond: null,
        bloquer: false
    })
    Object.keys(editErrors).forEach((key) => {
        editErrors[key] = ''
    })
}

const validateEditForm = () => {
    const requiredFields = ['raisonSociale', 'nomGerant', 'ville', 'typeClient', 'modePaiement', 'echeance']
    let isValid = true

    requiredFields.forEach((field) => {
        if (!editForm[field]) {
            editErrors[field] = 'Ce champ est obligatoire.'
            isValid = false
        } else {
            editErrors[field] = ''
        }
    })

    if (editForm.plafond !== null && editForm.plafond < 0) {
        editErrors.plafond = 'Le plafond doit être positif.'
        isValid = false
    } else {
        editErrors.plafond = ''
    }

    return isValid
}

const handleUpdate = () => {
    if (!validateEditForm()) {
        return
    }

    updateClient(editForm.id, {
        ...editForm,
        plafond: editForm.plafond !== null ? Number(editForm.plafond) : null
    })

    setFeedback('Client mis à jour avec succès.')
    closeEditModal()
}

onMounted(async () => {
    await fetchClients()

    try {
        villeOptions.value = await fetchCities()
        if (villeOptions.value.length === 0) {
            villeOptions.value = [
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
        }
    } catch (error) {
        console.error('Erreur lors du chargement des villes:', error)
    }

    const searchParams = new URLSearchParams(window.location.search)
    if (searchParams.get('created') === '1') {
        setFeedback('Client créé avec succès.')
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
