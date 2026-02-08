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
                            v-if="role.name === 'superadmin'" 
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                        >
                            Toutes les permissions
                        </span>
                        <template v-else>
                            <span 
                                v-for="permission in role.permissions" :key="permission.id"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                            >
                                {{ permission.name }}
                            </span>
                            <span v-if="!role.permissions.length" class="text-xs text-gray-500 italic">Aucune permission</span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeModal">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                <header class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
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
                        <div class="flex items-center justify-between mb-4">
                            <label class="label mb-0">Permissions (Droit d'accès)</label>
                            <div class="flex gap-2">
                                <button type="button" @click="expandAll" class="text-xs text-blue-600 hover:underline">Tout développer</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="collapseAll" class="text-xs text-blue-600 hover:underline">Tout réduire</button>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="group in structuredGroups" :key="group.id" class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                <!-- Group Header -->
                                <div 
                                    @click="toggleSection(group.id)" 
                                    class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <input 
                                            type="checkbox" 
                                            :checked="isGroupSelected(group)" 
                                            :indeterminate.prop="isGroupIndeterminate(group)"
                                            @click.stop="toggleGroup(group)"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                                        >
                                        <span class="font-bold text-gray-900 dark:text-white">{{ group.label }}</span>
                                    </div>
                                    <svg 
                                        class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                        :class="{'rotate-180': expandedSections.includes(group.id)}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>

                                <!-- Group Content (Subsections) -->
                                <div v-show="expandedSections.includes(group.id)" class="p-4 space-y-6 bg-white dark:bg-gray-900">
                                    <div v-for="sub in group.subGroups" :key="sub.id" class="border-l-2 border-gray-100 dark:border-gray-800 ml-2 pl-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input 
                                                type="checkbox" 
                                                :checked="isSubSelected(sub)" 
                                                :indeterminate.prop="isSubIndeterminate(sub)"
                                                @click.stop="toggleSub(sub)"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                                            >
                                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ sub.label }}</span>
                                        </div>
                                        
                                        <!-- Permissions Grid -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-2 ml-6">
                                            <label v-for="p in sub.perms" :key="p.id" class="flex items-center gap-2 cursor-pointer group">
                                                <input 
                                                    type="checkbox" 
                                                    v-model="form.permissions" 
                                                    :value="p.id" 
                                                    class="h-3.5 w-3.5 text-blue-500 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-800 dark:border-gray-700"
                                                >
                                                <span class="text-xs text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">
                                                    {{ p.label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white py-4 border-t border-gray-100 dark:bg-gray-900 dark:border-gray-800 flex items-center justify-end gap-3">
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

const expandedSections = ref(['achats'])

const form = reactive({
    name: '',
    permissions: []
})

const errors = ref({})

// Define the permission structure for the UI
const permissionGroups = [
    {
        id: 'dashboard',
        label: 'Tableau de bord',
        subsections: [
            {
                id: 'dash_view',
                label: 'Vue Générale',
                permissions: [
                    { id: 'view dashboard', label: 'Voir le tableau de bord' },
                ]
            }
        ]
    },
    {
        id: 'achats',
        label: 'Gestion des Achats',
        subsections: [
            {
                id: 'achats_bc',
                label: 'Bon de Commande',
                permissions: [
                    { id: 'achats.bon-commande.view', label: 'Voir la liste' },
                    { id: 'achats.bon-commande.create', label: 'Créer' },
                    { id: 'achats.bon-commande.edit', label: 'Modifier' },
                    { id: 'achats.bon-commande.delete', label: 'Supprimer' },
                    { id: 'achats.bon-commande.print', label: 'Imprimer' },
                    { id: 'achats.bon-commande.validate', label: 'Valider' },
                    { id: 'achats.bon-commande.convert', label: 'Convertir en Bon d\'achat' },
                ]
            },
            {
                id: 'achats_ba',
                label: 'Bon d\'achat fournisseur',
                permissions: [
                    { id: 'achats.bon-achat.view', label: 'Voir la liste' },
                    { id: 'achats.bon-achat.create', label: 'Créer' },
                    { id: 'achats.bon-achat.edit', label: 'Modifier' },
                    { id: 'achats.bon-achat.delete', label: 'Supprimer' },
                ]
            },
            {
                id: 'achats_reg',
                label: 'Règlement fournisseur',
                permissions: [
                    { id: 'achats.reglement.view', label: 'Voir la liste' },
                    { id: 'achats.reglement.create', label: 'Créer' },
                    { id: 'achats.reglement.edit', label: 'Modifier' },
                    { id: 'achats.reglement.delete', label: 'Supprimer' },
                ]
            },
            {
                id: 'achats_hist',
                label: 'Historique achat',
                permissions: [
                    { id: 'achats.historique.view', label: 'Consulter l\'historique' },
                ]
            },
            {
                id: 'achats_releve',
                label: 'Relevé compte fournisseur',
                permissions: [
                    { id: 'achats.releve.view', label: 'Consulter les relevés' },
                ]
            },
            {
                id: 'achats_echeance',
                label: 'Échéancier fournisseur',
                permissions: [
                    { id: 'achats.echeancier.view', label: 'Consulter l\'échéancier' },
                ]
            },
            {
                id: 'achats_legacy',
                label: 'Contrôle Global',
                permissions: [
                    { id: 'manage achats', label: 'Accès complet aux achats' },
                ]
            }
        ]
    },
    {
        id: 'ventes',
        label: 'Gestion des Ventes',
        subsections: [
            {
                id: 'ventes_bc',
                label: 'Bon de Commande Client',
                permissions: [
                    { id: 'ventes.bon-commande.view', label: 'Voir la liste' },
                    { id: 'ventes.bon-commande.create', label: 'Créer' },
                    { id: 'ventes.bon-commande.edit', label: 'Modifier' },
                    { id: 'ventes.bon-commande.delete', label: 'Supprimer' },
                    { id: 'ventes.bon-commande.print', label: 'Imprimer' },
                    { id: 'ventes.bon-commande.validate', label: 'Valider' },
                    { id: 'ventes.bon-commande.cancel', label: 'Annuler' },
                    { id: 'ventes.bon-commande.convert', label: 'Convertir en Bon de Livraison' },
                ]
            },
            {
                id: 'ventes_bl',
                label: 'Bon de Livraison',
                permissions: [
                    { id: 'ventes.bon-livraison.view', label: 'Voir la liste' },
                    { id: 'ventes.bon-livraison.create', label: 'Créer' },
                    { id: 'ventes.bon-livraison.edit', label: 'Modifier' },
                    { id: 'ventes.bon-livraison.delete', label: 'Supprimer' },
                    { id: 'ventes.bon-livraison.print', label: 'Imprimer' },
                    { id: 'ventes.bon-livraison.validate', label: 'Valider' },
                ]
            },
            {
                id: 'ventes_reg',
                label: 'Règlement client',
                permissions: [
                    { id: 'ventes.reglement.view', label: 'Voir la liste' },
                    { id: 'ventes.reglement.create', label: 'Créer' },
                    { id: 'ventes.reglement.delete', label: 'Supprimer' },
                ]
            },
            {
                id: 'ventes_misc',
                label: 'Autres (Ventes)',
                permissions: [
                    { id: 'ventes.historique.view', label: 'Historique ventes' },
                    { id: 'ventes.releve.view', label: 'Relevé compte clients' },
                    { id: 'manage ventes', label: 'Accès complet aux ventes' },
                ]
            }
        ]
    },
    {
        id: 'stock',
        label: 'Gestion du Stock',
        subsections: [
            {
                id: 'stock_art',
                label: 'Articles',
                permissions: [
                    { id: 'stock.articles.view', label: 'Voir la liste' },
                    { id: 'stock.articles.create', label: 'Créer' },
                    { id: 'stock.articles.edit', label: 'Modifier' },
                    { id: 'stock.articles.delete', label: 'Supprimer' },
                ]
            },
            {
                id: 'stock_mov',
                label: 'Mouvements & États',
                permissions: [
                    { id: 'stock.mouvement.view', label: 'Mouvement Stock' },
                    { id: 'stock.etat.view', label: 'État Stock' },
                    { id: 'manage stock', label: 'Accès complet au stock' },
                ]
            }
        ]
    },
    {
        id: 'tresorerie',
        label: 'Trésorerie',
        subsections: [
            {
                id: 'treso_stats',
                label: 'États & Rapports',
                permissions: [
                    { id: 'tresorerie.etat-journalier.view', label: 'État Journalier' },
                    { id: 'tresorerie.releve-reglements.view', label: 'Relevé Règlements' },
                    { id: 'tresorerie.balance-caisse.view', label: 'Balance Caisse' },
                    { id: 'tresorerie.impots.view', label: 'Liste Rég. Impôts' },
                ]
            },
            {
                id: 'treso_bank',
                label: 'Comptes Bancaires',
                permissions: [
                    { id: 'tresorerie.compte-bancaire.view', label: 'Voir la liste' },
                    { id: 'tresorerie.compte-bancaire.create', label: 'Créer' },
                    { id: 'tresorerie.compte-bancaire.delete', label: 'Supprimer' },
                ]
            },
            {
                id: 'treso_misc',
                label: 'Autres (Trésorerie)',
                permissions: [
                    { id: 'tresorerie.encaissement.view', label: 'Encaissement / Décaissement' },
                    { id: 'tresorerie.charges.view', label: 'Types de Charges' },
                    { id: 'manage tresorerie', label: 'Accès complet trésorerie' },
                ]
            }
        ]
    },
    {
        id: 'contacts',
        label: 'Clients & Fournisseurs',
        subsections: [
            {
                id: 'cont_clients',
                label: 'Clients',
                permissions: [
                    { id: 'contacts.clients.view', label: 'Voir la liste' },
                    { id: 'contacts.clients.create', label: 'Créer' },
                    { id: 'contacts.clients.edit', label: 'Modifier' },
                    { id: 'contacts.clients.delete', label: 'Supprimer' },
                    { id: 'manage clients', label: 'Accès complet clients' },
                ]
            },
            {
                id: 'cont_fourn',
                label: 'Fournisseurs',
                permissions: [
                    { id: 'contacts.fournisseurs.view', label: 'Voir la liste' },
                    { id: 'contacts.fournisseurs.create', label: 'Créer' },
                    { id: 'contacts.fournisseurs.edit', label: 'Modifier' },
                    { id: 'contacts.fournisseurs.delete', label: 'Supprimer' },
                    { id: 'manage fournisseurs', label: 'Accès complet fournisseurs' },
                ]
            }
        ]
    },
    {
        id: 'admin',
        label: 'Administration',
        subsections: [
            {
                id: 'adm_users',
                label: 'Utilisateurs & Rôles',
                permissions: [
                    { id: 'admin.users.view', label: 'Gérer les utilisateurs' },
                    { id: 'admin.roles.view', label: 'Voir les rôles' },
                    { id: 'admin.roles.manage', label: 'Gérer les permissions' },
                    { id: 'manage users', label: 'Accès complet administration' },
                ]
            },
            {
                id: 'adm_settings',
                label: 'Paramètres',
                permissions: [
                    { id: 'admin.settings.view', label: 'Voir les paramètres' },
                    { id: 'admin.settings.manage', label: 'Modifier les paramètres' },
                    { id: 'manage settings', label: 'Accès complet paramètres' },
                ]
            }
        ]
    }
]

// Computed property to structure permissions from the API
const structuredGroups = computed(() => {
    const usedPerms = new Set()
    const result = permissionGroups.map(group => {
        const subGroups = group.subsections.map(sub => {
            const perms = sub.permissions.filter(p => {
                const found = allPermissions.value.some(ap => ap.name === p.id)
                if (found) usedPerms.add(p.id)
                return found
            })
            return { ...sub, perms }
        }).filter(sub => sub.perms.length > 0)
        return { ...group, subGroups }
    }).filter(group => group.subGroups.length > 0)

    // Add unmapped permissions
    const others = allPermissions.value.filter(p => !usedPerms.has(p.name))
    if (others.length > 0) {
        result.push({
            id: 'others',
            label: 'Autres Permissions',
            subGroups: [
                {
                    id: 'unmapped',
                    label: 'Permissions Générales',
                    perms: others.map(p => ({ id: p.name, label: p.name }))
                }
            ]
        })
    }
    return result
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

const toggleSection = (id) => {
    const index = expandedSections.value.indexOf(id)
    if (index === -1) expandedSections.value.push(id)
    else expandedSections.value.splice(index, 1)
}

const expandAll = () => {
    expandedSections.value = structuredGroups.value.map(g => g.id)
}

const collapseAll = () => {
    expandedSections.value = []
}

// Group Selection Helpers
const isGroupSelected = (group) => {
    const allIds = group.subGroups.flatMap(sub => sub.perms.map(p => p.id))
    return allIds.length > 0 && allIds.every(id => form.permissions.includes(id))
}

const isGroupIndeterminate = (group) => {
    const allIds = group.subGroups.flatMap(sub => sub.perms.map(p => p.id))
    const selected = allIds.filter(id => form.permissions.includes(id)).length
    return selected > 0 && selected < allIds.length
}

const toggleGroup = (group) => {
    const allIds = group.subGroups.flatMap(sub => sub.perms.map(p => p.id))
    const allSelected = isGroupSelected(group)
    
    allIds.forEach(id => {
        const index = form.permissions.indexOf(id)
        if (allSelected) {
            if (index !== -1) form.permissions.splice(index, 1)
        } else {
            if (index === -1) form.permissions.push(id)
        }
    })
}

// Subsection Selection Helpers
const isSubSelected = (sub) => {
    return sub.perms.length > 0 && sub.perms.every(p => form.permissions.includes(p.id))
}

const isSubIndeterminate = (sub) => {
    const selected = sub.perms.filter(p => form.permissions.includes(p.id)).length
    return selected > 0 && selected < sub.perms.length
}

const toggleSub = (sub) => {
    const allSelected = isSubSelected(sub)
    sub.perms.forEach(p => {
        const index = form.permissions.indexOf(p.id)
        if (allSelected) {
            if (index !== -1) form.permissions.splice(index, 1)
        } else {
            if (index === -1) form.permissions.push(p.id)
        }
    })
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

/* Custom styles for better scrollbar in modal */
.max-h-\[90vh\]::-webkit-scrollbar {
    width: 6px;
}
.max-h-\[90vh\]::-webkit-scrollbar-track {
    @apply bg-transparent;
}
.max-h-\[90vh\]::-webkit-scrollbar-thumb {
    @apply bg-gray-200 dark:bg-gray-700 rounded-full;
}

input:indeterminate {
    @apply bg-blue-500 border-blue-500;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3E%3Cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M5 8h6'/%3E%3C/svg%3E");
    background-size: 100% 100%;
    background-position: center;
    background-repeat: no-repeat;
}
</style>
