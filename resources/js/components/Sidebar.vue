<template>
    <aside 
        :class="[
            'fixed inset-y-0 left-0 z-50 bg-slate-100 dark:bg-slate-900 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out shadow-lg',
            isCollapsed ? 'w-20' : 'w-64',
            isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700 bg-slate-50 dark:bg-slate-800">
            <div v-if="!isCollapsed" class="flex items-center space-x-2">
                <div class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-800 dark:text-white">Bouyahya</span>
            </div>
            <button 
                @click="toggleCollapse"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300"
                v-if="!isMobile"
            >
                <svg :class="['w-5 h-5 transition-transform', isCollapsed ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
            <button 
                @click="closeMobile"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 lg:hidden"
                v-if="isMobile"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 px-2">
            <div class="space-y-1">
                <!-- Dashboard -->
                <MenuItem 
                    :item="menuItems.dashboard" 
                    :is-collapsed="isCollapsed"
                    :is-active="currentRoute === menuItems.dashboard.route"
                />

                <!-- Gestion des Clients -->
                <CollapsibleMenuItem 
                    :item="menuItems.clients" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.clients"
                    @toggle="toggleMenu('clients')"
                />

                <!-- Gestion des Fournisseurs -->
                <CollapsibleMenuItem 
                    :item="menuItems.fournisseurs" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.fournisseurs"
                    @toggle="toggleMenu('fournisseurs')"
                />

                <!-- Gestion des Achats -->
                <CollapsibleMenuItem 
                    :item="menuItems.achats" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.achats"
                    @toggle="toggleMenu('achats')"
                />

                <!-- Gestion des Ventes -->
                <CollapsibleMenuItem 
                    :item="menuItems.ventes" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.ventes"
                    @toggle="toggleMenu('ventes')"
                />

                <!-- Gestion du Stock -->
                <CollapsibleMenuItem 
                    :item="menuItems.stock" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.stock"
                    @toggle="toggleMenu('stock')"
                />

                <!-- Gestion Trésorerie -->
                <CollapsibleMenuItem 
                    :item="menuItems.tresorerie" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.tresorerie"
                    @toggle="toggleMenu('tresorerie')"
                />
            </div>
        </nav>
    </aside>

    <!-- Mobile Overlay -->
    <div 
        v-if="isMobile && isMobileOpen"
        @click="closeMobile"
        class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    ></div>

    <!-- Mobile Menu Toggle Button -->
    <button 
        @click="openMobile"
        class="fixed top-4 left-4 z-50 p-2 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 rounded-lg shadow-lg lg:hidden border border-gray-200 dark:border-gray-700"
    >
        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import MenuItem from './MenuItem.vue'
import CollapsibleMenuItem from './CollapsibleMenuItem.vue'

const isCollapsed = ref(false)
const isMobileOpen = ref(false)
const isMobile = ref(false)

const openMenus = ref({
    achats: false,
    ventes: false,
    clients: false,
    fournisseurs: false,
    stock: false,
    tresorerie: false
})

const currentRoute = ref(window.location.pathname || '/')

// Update route on navigation
const updateRoute = () => {
    if (typeof window !== 'undefined') {
        currentRoute.value = window.location.pathname || '/'
    }
}


const menuItems = {
    dashboard: {
        title: 'Tableau de bord',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        route: '/dashboard'
    },
    achats: {
        title: 'La gestion des achats',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        children: [
            { title: 'Bon de commande', route: '/achats/bon-commande', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { title: 'Bon de réception', route: '/achats/bon-reception', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
            { title: 'Règlements fournisseurs', route: '/achats/reglements-fournisseurs', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            { title: 'Historique achats', route: '/achats/historique', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
            { title: 'Relevé compte fournisseurs', route: '/achats/releve-compte-fournisseurs', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { title: 'Échéancier fournisseurs', route: '/achats/echeancier-fournisseurs', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' }
        ]
    },
    ventes: {
        title: 'La gestion des ventes',
        icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        children: [
            { title: 'Bon de commande', route: '/ventes/bon-commande', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { title: 'Bon de livraison', route: '/ventes/bon-livraison', icon: 'M5 13l4 4L19 7' },
            { title: 'Règlements clients', route: '/ventes/reglements-clients', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            { title: 'Règlements recouvrement', route: '/ventes/reglements-recouvrement', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
            { title: 'Historique ventes', route: '/ventes/historique', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
            { title: 'Relevé compte clients', route: '/ventes/releve-compte-clients', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' }
        ]
    },
    clients: {
        title: 'Gestion des clients',
        icon: 'M17 20h5V8H2v12h5m10 0V8m0 12v2m-10-2v2m0-2H2m10 0h10M2 8l10-6 10 6',
        children: [
            { title: 'Liste des clients', route: '/clients', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { title: 'Nouveau client', route: '/clients/create', icon: 'M12 4v16m8-8H4' }
        ]
    },
    fournisseurs: {
        title: 'Gestion des Fournisseurs',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        children: [
            { title: 'Liste des fournisseurs', route: '/fournisseurs', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { title: 'Nouveau fournisseur', route: '/fournisseurs/create', icon: 'M12 4v16m8-8H4' }
        ]
    },
    stock: {
        title: 'La gestion du stock',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        children: [
            { title: 'Articles', route: '/stock/articles', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
            { title: 'Familles', route: '/stock/familles', icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' },
            { title: 'Sous-familles', route: '/stock/sous-familles', icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' },
            { title: 'Unités de mesure', route: '/stock/unites-mesure', icon: 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14' },
            { title: 'Mouvement stock', route: '/stock/mouvement', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
            { title: 'Les stocks', route: '/stock/stocks', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' }
        ]
    },
    tresorerie: {
        title: 'La gestion trésorerie',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        children: [
            { title: 'État journalier', route: '/tresorerie/etat-journalier', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
            { title: 'Relevé règlements', route: '/tresorerie/releve-reglements', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { title: 'Balance caisse', route: '/tresorerie/balance-caisse', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
            { title: 'Liste rég impôts', route: '/tresorerie/liste-impots', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { title: 'Compte bancaire', route: '/tresorerie/compte-bancaire', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
            { title: 'Encaissement / Décaissement', route: '/tresorerie/encaissement-decaissement', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
            { title: 'Types de charges', route: '/tresorerie/types-charges', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' }
        ]
    }
}

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value
}

const toggleMenu = (menu) => {
    openMenus.value[menu] = !openMenus.value[menu]
}

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024
    if (!isMobile.value) {
        isMobileOpen.value = false
    }
}

const openMobile = () => {
    isMobileOpen.value = true
}

const closeMobile = () => {
    isMobileOpen.value = false
}

onMounted(() => {
    checkMobile()
    window.addEventListener('resize', checkMobile)
    
    // Update route
    updateRoute()
    window.addEventListener('popstate', updateRoute)
    
    // Auto-open menu if current route matches
    const currentPath = currentRoute.value
    if (currentPath.startsWith('/achats')) {
        openMenus.value.achats = true
    } else if (currentPath.startsWith('/ventes')) {
        openMenus.value.ventes = true
    } else if (currentPath.startsWith('/clients')) {
        openMenus.value.clients = true
    } else if (currentPath.startsWith('/fournisseurs')) {
        openMenus.value.fournisseurs = true
    } else if (currentPath.startsWith('/stock')) {
        openMenus.value.stock = true
    } else if (currentPath.startsWith('/tresorerie')) {
        openMenus.value.tresorerie = true
    }
})

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
    window.removeEventListener('popstate', updateRoute)
})
</script>

