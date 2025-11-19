<template>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Component -->
        <Sidebar></Sidebar>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-64 transition-all duration-300" id="main-content">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
                        {{ pageTitle }}
                    </h1>
                    <div class="flex items-center space-x-4">
                        <!-- Theme Toggle -->
                        <ThemeToggle />
                        
                        <!-- User Info -->
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name || 'Utilisateur' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email || '' }}</p>
                            </div>
                            <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">
                                {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                        </div>
                        <!-- Logout Button -->
                        <div id="logout-button-placeholder"></div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900">
                <component v-if="pageComponent" :is="pageComponent" />
                <slot v-else></slot>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Sidebar from './components/Sidebar.vue'
import ThemeToggle from './components/ThemeToggle.vue'

const pageTitle = ref('Tableau de bord')
const user = ref(null)
const pageComponent = ref('')

onMounted(() => {
    // Get page title from data attribute on the app element
    const appEl = document.getElementById('app')
    if (appEl && appEl.dataset.pageTitle) {
        pageTitle.value = appEl.dataset.pageTitle
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
})
</script>

