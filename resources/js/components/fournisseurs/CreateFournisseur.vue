<template>
    <div class="space-y-6">
        <!-- Breadcrumbs -->
        <nav class="text-sm text-gray-600 dark:text-gray-400">
            <ol class="flex items-center space-x-2">
                <li><a href="/fournisseurs" class="hover:text-gray-900 dark:hover:text-white">Gestion des Fournisseurs</a></li>
                <li>/</li>
                <li class="text-gray-900 dark:text-white">Nouveau Fournisseur</li>
            </ol>
        </nav>

        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Ajouter un nouveau fournisseur</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Remplissez les informations ci-dessous pour créer un nouveau fournisseur.
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full bg-blue-50 px-4 py-1 text-sm font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300"
            >
                Code généré : {{ form.codeFournisseur }}
            </span>
        </div>

        <form class="space-y-8" @submit.prevent="handleSubmit">
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="grid gap-6 px-6 py-6 md:grid-cols-4">
                    <div>
                        <label class="label" for="codeFournisseur">Code fournisseur</label>
                        <input
                            id="codeFournisseur"
                            v-model="form.codeFournisseur"
                            type="text"
                            readonly
                            class="input read-only:bg-gray-100 dark:read-only:bg-gray-800"
                        />
                    </div>

                    <div>
                        <label class="label" for="nomFournisseur">Nom du fournisseur *</label>
                        <input 
                            id="nomFournisseur" 
                            v-model="form.nomFournisseur" 
                            type="text" 
                            class="input"
                            placeholder="Entrez le nom du fournisseur"
                        />
                        <p v-if="errors.nomFournisseur" class="error">{{ errors.nomFournisseur }}</p>
                    </div>

                    <div>
                        <label class="label" for="nomGerant">Nom du gérant *</label>
                        <input 
                            id="nomGerant" 
                            v-model="form.nomGerant" 
                            type="text" 
                            class="input"
                            placeholder="Entrez le nom du gérant"
                        />
                        <p v-if="errors.nomGerant" class="error">{{ errors.nomGerant }}</p>
                    </div>

                    <div>
                        <label class="label" for="telephone">Numéro de téléphone *</label>
                        <input 
                            id="telephone" 
                            v-model="form.telephone" 
                            type="text" 
                            class="input"
                            placeholder="Entrez le numéro de téléphone"
                        />
                        <p v-if="errors.telephone" class="error">{{ errors.telephone }}</p>
                    </div>

                    <div>
                        <label class="label" for="email">Email</label>
                        <input 
                            id="email" 
                            v-model="form.email" 
                            type="email" 
                            class="input"
                            placeholder="Entrez l'email"
                        />
                        <p v-if="errors.email" class="error">{{ errors.email }}</p>
                    </div>

                    <div>
                        <label class="label" for="activite">Activité</label>
                        <input 
                            id="activite" 
                            v-model="form.activite" 
                            type="text" 
                            class="input"
                            placeholder="Entrez l'activité"
                        />
                    </div>

                    <div>
                        <label class="label" for="ville">Ville</label>
                        <select id="ville" v-model="form.ville" class="input">
                            <option value="">Sélectionner une ville</option>
                            <option v-for="city in villeOptions" :key="city" :value="city">
                                {{ city }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="label" for="ice">ICE</label>
                        <input 
                            id="ice" 
                            v-model="form.ice" 
                            type="text" 
                            class="input"
                            placeholder="Entrez le numéro ICE"
                        />
                    </div>

                    <div>
                        <label class="label" for="modePaiement">Mode de paiement</label>
                        <select id="modePaiement" v-model="form.modePaiement" class="input">
                            <option value="">Sélectionner un mode</option>
                            <option v-for="option in modePaiementOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-4 border-t border-dashed border-gray-200 pt-6 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Les champs marqués d'un astérisque (*) sont obligatoires.
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-create" :disabled="isSubmitting">
                        <svg
                            v-if="isSubmitting"
                            class="mr-2 h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8" />
                        </svg>
                        <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ isSubmitting ? 'Enregistrement...' : 'Créer' }}
                    </button>
                    <button type="button" class="btn-modify" @click="handleModify">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Modifier
                    </button>
                    <button type="button" class="btn-delete" @click="handleDelete">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Supprimer
                    </button>
                    <button type="button" class="btn-print" @click="handlePrint">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimer
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
import { useFournisseurs } from '../../composables/useFournisseurs'
import { useSettings } from '../../composables/useSettings'

const { createFournisseur, generateFournisseurCode } = useFournisseurs()
const { fetchCities } = useSettings()
const fournisseursListUrl = '/fournisseurs'

const villeOptions = ref([])

const modePaiementOptions = ['Virement bancaire', 'Espèces', 'Chèque', 'Traite']

const createEmptyForm = () => ({
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

const form = reactive(createEmptyForm())
const errors = reactive({})
const isSubmitting = ref(false)
const successMessage = ref('')

const validateForm = () => {
    const requiredFields = ['nomFournisseur', 'nomGerant', 'telephone']
    let isValid = true

    requiredFields.forEach((field) => {
        if (!form[field]) {
            errors[field] = 'Ce champ est obligatoire.'
            isValid = false
        } else {
            errors[field] = ''
        }
    })

    if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Veuillez entrer une adresse email valide.'
        isValid = false
    } else if (form.email) {
        errors.email = ''
    }

    return isValid
}

const handleSubmit = async () => {
    if (isSubmitting.value) return

    if (!validateForm()) {
        return
    }

    try {
        isSubmitting.value = true
        await new Promise((resolve) => setTimeout(resolve, 400))

        createFournisseur({
            ...form
        })

        successMessage.value = 'Fournisseur enregistré avec succès. Redirection en cours...'
        setTimeout(() => {
            window.location.href = `${fournisseursListUrl}?created=1`
        }, 1200)
    } catch (error) {
        console.error('Erreur lors de la création du fournisseur', error)
    } finally {
        isSubmitting.value = false
    }
}

const handleModify = () => {
    // Navigate to edit page or open edit modal
    // For now, just show a message
    alert('Fonctionnalité de modification à implémenter')
}

const handleDelete = () => {
    // This would typically be used when editing an existing supplier
    // For create page, this might not be applicable
    alert('Fonctionnalité de suppression à implémenter')
}

const handlePrint = () => {
    window.print()
}

onMounted(async () => {
    form.codeFournisseur = await generateFournisseurCode()
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

.btn-create {
    @apply inline-flex items-center justify-center rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700;
}

.btn-modify {
    @apply inline-flex items-center justify-center rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700;
}

.btn-delete {
    @apply inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2;
}

.btn-print {
    @apply inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
}

.error {
    @apply mt-1 text-sm text-red-500;
}
</style>

