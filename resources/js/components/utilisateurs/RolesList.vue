<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Rôles et Permissions</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Définissez les rôles et attribuez des permissions pour contrôler l'accès aux différentes parties de l'application.
                </p>
            </div>
            <button type="button" class="btn-primary" @click="openCreateModal">
                Nouveau rôle
            </button>
        </div>

        <div v-if="feedbackMessage" :class="feedbackClasses" class="rounded-xl px-4 py-3 text-sm">
            {{ feedbackMessage }}
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="role in roles" :key="role.id" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white capitalize">{{ role.name }}</h3>
                    <div class="flex gap-2" v-if="role.name !== 'superadmin'">
                        <button @click="openEditModal(role)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button @click="handleDelete(role)" class="text-red-600 hover:text-red-800 dark:text-red-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permissions :</p>
                    <div class="flex flex-wrap gap-2">
                        <span 
                            v-for="permission in role.permissions" :key="permission.id"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                        >
                            {{ permission.name }}
                        </span>
                        <span v-if="role.name === 'superadmin'" class="text-xs text-gray-500 italic">Toutes les permissions</span>
                        <span v-else-if="!role.permissions.length" class="text-xs text-gray-500 italic">Aucune permission</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeModal">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                <header class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ isEditing ? 'Modifier le rôle' : 'Nouveau rôle' }}
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeModal">
                        ✕
                    </button>
                </header>

                <form class="space-y-6 px-6 py-6" @submit.prevent="handleSubmit">
                    <div>
                        <label class="label">Nom du rôle *</label>
                        <input v-model="form.name" type="text" class="input" required :disabled="isEditing && form.name === 'superadmin'" />
                        <p v-if="errors.name" class="error">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="label mb-4">Permissions (Droit d'accès)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="permission in allPermissions" :key="permission.id" class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input 
                                        :id="'perm-' + permission.id" 
                                        v-model="form.permissions" 
                                        :value="permission.name"
                                        type="checkbox" 
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-800 dark:border-gray-700"
                                    />
                                </div>
                                <div class="ml-3 text-sm">
                                    <label :for="'perm-' + permission.id" class="font-medium text-gray-700 dark:text-gray-300 capitalize">
                                        {{ permission.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
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
import { ref, onMounted, reactive, computed } from 'vue'
import axios from 'axios'

const roles = ref([])
const allPermissions = ref([])
const isLoading = ref(false)
const feedbackMessage = ref('')
const feedbackVariant = ref('success')

const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = reactive({
    name: '',
    permissions: []
})

const errors = ref({})

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

const fetchRoles = async () => {
    try {
        const response = await axios.get('/api/roles')
        roles.value = response.data
    } catch (err) {
        console.error('Erreur lors du chargement des rôles', err)
    }
}

const fetchPermissions = async () => {
    try {
        const response = await axios.get('/api/roles/permissions')
        allPermissions.value = response.data
    } catch (err) {
        console.error('Erreur lors du chargement des permissions', err)
    }
}

const openCreateModal = () => {
    isEditing.value = false
    editingId.value = null
    Object.assign(form, { name: '', permissions: [] })
    errors.value = {}
    isModalOpen.value = true
}

const openEditModal = (role) => {
    isEditing.value = true
    editingId.value = role.id
    Object.assign(form, { 
        name: role.name, 
        permissions: role.permissions.map(p => p.name)
    })
    errors.value = {}
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
}

const handleSubmit = async () => {
    isLoading.value = true
    errors.value = {}
    try {
        if (isEditing.value) {
            await axios.put(`/api/roles/${editingId.value}`, form)
            setFeedback('Rôle mis à jour avec succès')
        } else {
            await axios.post('/api/roles', form)
            setFeedback('Rôle créé avec succès')
        }
        await fetchRoles()
        closeModal()
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors
        } else {
            setFeedback(err.response?.data?.message || 'Une erreur est survenue', 'error')
        }
    } finally {
        isLoading.value = false
    }
}

const handleDelete = async (role) => {
    if (!confirm(`Supprimer le rôle ${role.name} ?`)) return
    try {
        await axios.delete(`/api/roles/${role.id}`)
        setFeedback('Rôle supprimé avec succès')
        await fetchRoles()
    } catch (err) {
        setFeedback(err.response?.data?.message || 'Erreur lors de la suppression', 'error')
    }
}

onMounted(() => {
    fetchRoles()
    fetchPermissions()
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
