<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Nouveau client</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Renseignez les informations requises afin de créer une fiche client complète.
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full bg-blue-50 px-4 py-1 text-sm font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300"
            >
                Code généré : {{ form.codeClient }}
            </span>
        </div>

        <form class="space-y-8" @submit.prevent="handleSubmit">
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informations générales</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Données principales pour identifier le client et ses conditions commerciales.
                    </p>
                </header>
                <div class="grid gap-6 px-6 py-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="label" for="codeClient">Code client</label>
                        <input
                            id="codeClient"
                            v-model="form.codeClient"
                            type="text"
                            readonly
                            class="input read-only:bg-gray-100 dark:read-only:bg-gray-800"
                        />
                    </div>

                    <div>
                        <label class="label" for="raisonSociale">Raison sociale *</label>
                        <input id="raisonSociale" v-model="form.raisonSociale" type="text" class="input" />
                        <p v-if="errors.raisonSociale" class="error">{{ errors.raisonSociale }}</p>
                    </div>

                    <div>
                        <label class="label" for="nomGerant">Nom du gérant *</label>
                        <input id="nomGerant" v-model="form.nomGerant" type="text" class="input" />
                        <p v-if="errors.nomGerant" class="error">{{ errors.nomGerant }}</p>
                    </div>

                    <div>
                        <label class="label" for="ville">Ville *</label>
                        <select id="ville" v-model="form.ville" class="input">
                            <option value="">Sélectionnez une ville</option>
                            <option v-for="city in villeOptions" :key="city" :value="city">
                                {{ city }}
                            </option>
                        </select>
                        <p v-if="errors.ville" class="error">{{ errors.ville }}</p>
                    </div>

                    <div>
                        <label class="label" for="typeClient">Type client *</label>
                        <select id="typeClient" v-model="form.typeClient" class="input">
                            <option value="">Sélectionnez un type</option>
                            <option v-for="option in typeOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                        <p v-if="errors.typeClient" class="error">{{ errors.typeClient }}</p>
                    </div>

                    <div>
                        <label class="label" for="modePaiement">Mode de paiement *</label>
                        <select id="modePaiement" v-model="form.modePaiement" class="input">
                            <option value="">Sélectionnez un mode</option>
                            <option v-for="option in modePaiementOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                        <p v-if="errors.modePaiement" class="error">{{ errors.modePaiement }}</p>
                    </div>

                    <div>
                        <label class="label" for="echeance">Échéance *</label>
                        <select id="echeance" v-model="form.echeance" class="input">
                            <option value="">Choisissez une échéance</option>
                            <option v-for="option in echeanceOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                        <p v-if="errors.echeance" class="error">{{ errors.echeance }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Données juridiques</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pièces officielles d’identification.</p>
                </header>
                <div class="grid gap-6 px-6 py-6 md:grid-cols-2">
                    <div>
                        <label class="label" for="cin">CIN</label>
                        <input id="cin" v-model="form.cin" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label" for="ifFiscal">IF (Identifiant fiscal)</label>
                        <input id="ifFiscal" v-model="form.ifFiscal" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label" for="patente">PATENTE</label>
                        <input id="patente" v-model="form.patente" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label" for="cnss">CNSS</label>
                        <input id="cnss" v-model="form.cnss" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label" for="ice">ICE</label>
                        <input id="ice" v-model="form.ice" type="text" class="input" />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Données bancaires</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Suivi des limites et moyens de paiement.</p>
                </header>
                <div class="grid gap-6 px-6 py-6 md:grid-cols-2">
                    <div>
                        <label class="label" for="banque">Banque</label>
                        <input id="banque" v-model="form.banque" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label" for="rib">RIB</label>
                        <input id="rib" v-model="form.rib" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label" for="plafond">Plafond (MAD)</label>
                        <input id="plafond" v-model.number="form.plafond" type="number" min="0" class="input" />
                        <p v-if="errors.plafond" class="error">{{ errors.plafond }}</p>
                    </div>
                    <div class="flex items-center gap-3 md:col-span-2">
                        <span class="label mb-0">Bloquer</span>
                        <button
                            type="button"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="form.bloquer ? 'bg-red-500' : 'bg-gray-200 dark:bg-gray-700'"
                            @click="form.bloquer = !form.bloquer"
                            role="switch"
                            :aria-checked="form.bloquer"
                        >
                            <span
                                aria-hidden="true"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="form.bloquer ? 'translate-x-5' : 'translate-x-0'"
                            ></span>
                        </button>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ form.bloquer ? 'Client bloqué' : 'Client actif' }}
                        </span>
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-4 border-t border-dashed border-gray-200 pt-6 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Les champs marqués d’un astérisque (*) sont obligatoires.
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="button" class="btn-secondary" @click="resetForm">Réinitialiser</button>
                    <button type="submit" class="btn-primary" :disabled="isSubmitting">
                        <svg
                            v-if="isSubmitting"
                            class="mr-2 h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8" />
                        </svg>
                        {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer le client' }}
                    </button>
                </div>
            </div>
        </form>

        <div
            v-if="successMessage"
            class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-200"
        >
            {{ successMessage }}
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useClients } from '../../composables/useClients'
import { useSettings } from '../../composables/useSettings'

const { createClient, generateClientCode, isLoading } = useClients()
const { fetchCities } = useSettings()
const clientsListUrl = '/clients'

const villeOptions = ref([])

const typeOptions = ['REV', 'PROMO', 'ENTR', 'CON.FI']
const modePaiementOptions = ['Espèces', 'Virement', 'Chèque', 'Traite']
const echeanceOptions = ['0j', '30j', '45j', '60j', '90j']

const createEmptyForm = () => ({
    codeClient: '',
    raisonSociale: '',
    nomGerant: '',
    ville: '',
    typeClient: '',
    modePaiement: '',
    echeance: '',
    cin: '',
    ifFiscal: '',
    patente: '',
    cnss: '',
    ice: '',
    banque: '',
    rib: '',
    plafond: null,
    bloquer: false
})

const form = reactive(createEmptyForm())
const errors = reactive({})
const isSubmitting = ref(false)
const successMessage = ref('')

const validateForm = () => {
    const requiredFields = ['raisonSociale', 'nomGerant', 'ville', 'typeClient', 'modePaiement', 'echeance']
    let isValid = true

    requiredFields.forEach((field) => {
        if (!form[field]) {
            errors[field] = 'Ce champ est obligatoire.'
            isValid = false
        } else {
            errors[field] = ''
        }
    })

    if (form.plafond !== null && form.plafond < 0) {
        errors.plafond = 'Le plafond doit être positif.'
        isValid = false
    } else {
        errors.plafond = ''
    }

    return isValid
}

const resetForm = () => {
    Object.assign(form, createEmptyForm())
    Object.keys(errors).forEach((key) => {
        errors[key] = ''
    })
}

const handleSubmit = async () => {
    if (isSubmitting.value) return

    if (!validateForm()) {
        return
    }

    try {
        isSubmitting.value = true
        await new Promise((resolve) => setTimeout(resolve, 400))

        await createClient({
            ...form,
            plafond: form.plafond !== null ? Number(form.plafond) : null
        })

        successMessage.value = 'Client enregistré avec succès. Redirection en cours...'
        setTimeout(() => {
            window.location.href = `${clientsListUrl}?created=1`
        }, 1200)
    } catch (error) {
        console.error('Erreur lors de la création du client', error)
        const errorMessage = error.response?.data?.errors 
            ? Object.values(error.response.data.errors).flat().join(', ')
            : error.response?.data?.message || 'Erreur lors de la création du client'
        alert(errorMessage)
        successMessage.value = ''
    } finally {
        isSubmitting.value = false
    }
}

onMounted(async () => {
    form.codeClient = await generateClientCode()
    // Load cities from settings
    try {
        villeOptions.value = await fetchCities()
        // Fallback to default cities if none configured
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
})
</script>

<style scoped>
@reference '../../../css/app.css';

.label {
    @apply mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300;
}

.input {
    @apply w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100;
}

.btn-primary {
    @apply inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-400;
}

.btn-secondary {
    @apply inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-500 dark:hover:bg-gray-800;
}

.error {
    @apply mt-1 text-sm text-red-500;
}
</style>
