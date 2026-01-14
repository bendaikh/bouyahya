<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Utilisateurs</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez les utilisateurs de l'application et leurs rôles.
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
                        placeholder="Rechercher par nom ou email..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
                <button type="button" class="btn-primary" @click="openCreateModal">
                    Nouvel utilisateur
                </button>
            </div>
        </div>

        <div v-if="feedbackMessage" :class="feedbackClasses" class="rounded-xl px-4 py-3 text-sm">
            {{ feedbackMessage }}
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ filteredUsers.length }} utilisateur(s) trouvé(s)
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nom</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Rôle</th>
                            <th scope="col" class="px-6 py-3">Créé le</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                {{ user.name }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    v-for="role in user.roles" :key="role.id"
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-200"
                                >
                                    {{ role.name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" class="btn-ghost" @click="openEditModal(user)">
                                        Éditer
                                    </button>
                                    <button type="button" class="btn-danger" @click="handleDelete(user)">
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filteredUsers.length">
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeModal">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                <header class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ isEditing ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeModal">
                        ✕
                    </button>
                </header>

                <form class="space-y-6 px-6 py-6" @submit.prevent="handleSubmit">
                    <div>
                        <label class="label">Nom *</label>
                        <input v-model="form.name" type="text" class="input" required />
                        <p v-if="errors.name" class="error">{{ errors.name[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Email *</label>
                        <input v-model="form.email" type="email" class="input" required />
                        <p v-if="errors.email" class="error">{{ errors.email[0] }}</p>
                    </div>
                    <div>
                        <label class="label">{{ isEditing ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' }}</label>
                        <input v-model="form.password" type="password" class="input" :required="!isEditing" />
                        <p v-if="errors.password" class="error">{{ errors.password[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Rôle *</label>
                        <select v-model="form.role" class="input" required>
                            <option value="">Sélectionnez un rôle</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">
                                {{ role.name }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="error">{{ errors.role[0] }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" class="btn-secondary" @click="closeModal">Annuler</button>
                        <button type="submit" class="btn-primary" :disabled="isLoading">
                            {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useUsers } from '../../composables/useUsers'
import axios from 'axios'

const { users, isLoading, fetchUsers, createUser, updateUser, deleteUser } = useUsers()
const roles = ref([])
const searchTerm = ref('')
const feedbackMessage = ref('')
const feedbackVariant = ref('success')

const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: ''
})

const errors = ref({})

const filteredUsers = computed(() => {
    const search = searchTerm.value.trim().toLowerCase()
    if (!search) return users.value
    return users.value.filter(u => 
        u.name.toLowerCase().includes(search) || 
        u.email.toLowerCase().includes(search)
    )
})

const feedbackClasses = computed(() => {
    return feedbackVariant.value === 'success' 
        ? 'border border-green-200 bg-green-50 text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-200'
        : 'border border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200'
})

const setFeedback = (msg, variant = 'success') => {
    feedbackMessage.value = msg
    feedbackVariant.value = variant
    setTimeout(() => feedbackMessage.value = '', 4000)
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('fr-FR')
}

const openCreateModal = () => {
    isEditing.value = false
    editingId.value = null
    Object.assign(form, { name: '', email: '', password: '', role: '' })
    errors.value = {}
    isModalOpen.value = true
}

const openEditModal = (user) => {
    isEditing.value = true
    editingId.value = user.id
    Object.assign(form, { 
        name: user.name, 
        email: user.email, 
        password: '', 
        role: user.roles.length ? user.roles[0].name : '' 
    })
    errors.value = {}
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
}

const fetchRoles = async () => {
    try {
        const response = await axios.get('/api/roles')
        roles.value = response.data
    } catch (err) {
        console.error('Erreur lors du chargement des rôles', err)
    }
}

const handleSubmit = async () => {
    errors.value = {}
    try {
        if (isEditing.value) {
            await updateUser(editingId.value, form)
            setFeedback('Utilisateur mis à jour avec succès')
        } else {
            await createUser(form)
            setFeedback('Utilisateur créé avec succès')
        }
        closeModal()
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors
        } else {
            setFeedback(err.response?.data?.message || 'Une erreur est survenue', 'error')
        }
    }
}

const handleDelete = async (user) => {
    if (!confirm(`Supprimer l'utilisateur ${user.name} ?`)) return
    try {
        await deleteUser(user.id)
        setFeedback('Utilisateur supprimé avec succès')
    } catch (err) {
        setFeedback(err.response?.data?.message || 'Erreur lors de la suppression', 'error')
    }
}

onMounted(() => {
    fetchUsers()
    fetchRoles()
})
</script>

<style scoped>
@reference "../../../css/app.css";

.btn-primary {
    @apply inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50;
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
