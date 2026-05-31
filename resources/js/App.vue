<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-950">
        <!-- Sidebar Component -->
        <Sidebar :user="user" :app-name="appName" @collapse-change="onSidebarCollapseChange"></Sidebar>
        
        <!-- Main Content Area -->
        <div 
            :class="[
                'flex-1 flex flex-col overflow-hidden transition-all duration-300',
                sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-72'
            ]" 
            id="main-content"
        >
            <!-- Top Header -->
            <header class="relative z-10 flex-shrink-0">
                <!-- Glass effect background -->
                <div class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-800/50"></div>
                
                <div class="relative flex items-center justify-between px-6 lg:px-8 py-4">
                    <!-- Page Title -->
                    <div class="flex items-center gap-4">
                        <div class="hidden lg:block">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ pageTitle }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ getCurrentDate() }}
                            </p>
                        </div>
                        <h1 class="lg:hidden text-lg font-bold text-gray-900 dark:text-white ml-14">
                            {{ pageTitle }}
                        </h1>
                    </div>
                    
                    <!-- Right Actions -->
                    <div class="flex items-center gap-3">
                        <!-- Year Selector -->
                        <div class="hidden md:flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-violet-500 to-purple-600 rounded-xl shadow-lg shadow-violet-500/25">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <select 
                                v-model="selectedYear"
                                @change="changeYear($event.target.value)"
                                class="bg-transparent border-0 text-sm font-bold text-white focus:ring-0 cursor-pointer appearance-none pr-6"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27white%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0 center; background-size: 1em;"
                            >
                                <option v-for="year in availableYears" :key="year" :value="year" class="text-gray-900">
                                    {{ year }}
                                </option>
                            </select>
                        </div>
                        
                        <!-- Search Button (Desktop) -->
                        <button class="hidden md:flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-xl text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Rechercher...</span>
                            <kbd class="px-2 py-0.5 text-xs font-mono bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">⌘K</kbd>
                        </button>
                        
                        <!-- Notifications -->
                        <button class="relative p-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Notification badge -->
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white dark:ring-gray-900"></span>
                        </button>
                        
                        <!-- Theme Toggle -->
                        <ThemeToggle />
                        
                        <!-- Divider -->
                        <div class="hidden sm:block w-px h-8 bg-gray-200 dark:bg-gray-700"></div>
                        
                        <!-- User Menu -->
                        <div class="hidden sm:flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ user?.name || 'Utilisateur' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ getRoleName() }}</p>
                            </div>
                            <div class="relative">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-500 to-rose-500 flex items-center justify-center text-white font-bold shadow-lg shadow-violet-500/25">
                                    {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                                </div>
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-gray-900"></div>
                            </div>
                        </div>
                        
                        <!-- Logout Button -->
                        <div id="logout-button-placeholder"></div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <!-- Decorative background -->
                <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
                    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-violet-500/5 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-rose-500/5 rounded-full blur-3xl"></div>
                </div>
                
                <div class="relative p-6 lg:p-8">
                    <component v-if="pageComponent" :is="pageComponent" />
                    <slot v-else></slot>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Sidebar from './components/Sidebar.vue'
import ThemeToggle from './components/ThemeToggle.vue'

const pageTitle = ref('Tableau de bord')
const appName = ref('Bouyahya')
const user = ref(null)
const pageComponent = ref('')
const sidebarCollapsed = ref(false)
const selectedYear = ref(new Date().getFullYear())
const availableYears = ref([])

const onSidebarCollapseChange = (collapsed) => {
    sidebarCollapsed.value = collapsed
}

const getCurrentDate = () => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    const date = new Date().toLocaleDateString('fr-FR', options)
    return date.charAt(0).toUpperCase() + date.slice(1)
}

const getRoleName = () => {
    if (!user.value) return ''
    if (user.value.role === 'superadmin') return 'Super Admin'
    if (user.value.roles && user.value.roles.length > 0) {
        return user.value.roles[0].name
    }
    return 'Utilisateur'
}

const fetchSelectedYear = async () => {
    try {
        const response = await fetch('/api/selected-year')
        const data = await response.json()
        selectedYear.value = data.selectedYear
        availableYears.value = data.availableYears
    } catch (error) {
        console.error('Error fetching selected year:', error)
        // Fallback to current year
        const currentYear = new Date().getFullYear()
        selectedYear.value = currentYear
        availableYears.value = Array.from({ length: currentYear - 2019 }, (_, i) => 2020 + i)
    }
}

const changeYear = async (year) => {
    try {
        await fetch('/api/selected-year', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ year: year })
        })
        selectedYear.value = year
        // Reload the page to apply the year filter
        window.location.reload()
    } catch (error) {
        console.error('Error changing year:', error)
    }
}

const updateDocumentTitle = (name) => {
    const currentTitle = document.title
    const separatorIndex = currentTitle.lastIndexOf(' - ')
    if (separatorIndex !== -1) {
        document.title = `${currentTitle.slice(0, separatorIndex)} - ${name}`
    }
}

const handleAppNameUpdated = (event) => {
    if (event.detail) {
        appName.value = event.detail
        updateDocumentTitle(event.detail)
    }
}

onMounted(() => {
    // Get page title from data attribute on the app element
    const appEl = document.getElementById('app')
    if (appEl && appEl.dataset.pageTitle) {
        pageTitle.value = appEl.dataset.pageTitle
    }

    if (appEl && appEl.dataset.appName) {
        appName.value = appEl.dataset.appName
    }

    if (appEl && appEl.dataset.pageComponent) {
        pageComponent.value = appEl.dataset.pageComponent
    }
    
    // Get user info from data attribute
    if (appEl && appEl.dataset.user) {
        try {
            user.value = JSON.parse(appEl.dataset.user)
        } catch (e) {
            console.error('Error parsing user data:', e)
        }
    }
    
    // Insert logout form from template
    const logoutPlaceholder = document.getElementById('logout-button-placeholder')
    const logoutTemplate = document.getElementById('logout-form-template')
    if (logoutPlaceholder && logoutTemplate) {
        logoutPlaceholder.innerHTML = logoutTemplate.innerHTML
    }
    
    // Fetch selected year
    fetchSelectedYear()

    window.addEventListener('app-name-updated', handleAppNameUpdated)
})

onUnmounted(() => {
    window.removeEventListener('app-name-updated', handleAppNameUpdated)
})
</script>
