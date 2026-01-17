<template>
    <button
        @click="toggleTheme"
        class="relative p-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-violet-500/50 group overflow-hidden"
        :aria-label="isDark ? 'Passer en mode clair' : 'Passer en mode sombre'"
        :title="isDark ? 'Passer en mode clair' : 'Passer en mode sombre'"
    >
        <!-- Background glow on hover -->
        <div class="absolute inset-0 bg-gradient-to-br from-amber-400/20 to-rose-400/20 dark:from-violet-400/20 dark:to-blue-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        
        <!-- Sun Icon (shown in dark mode) -->
        <svg
            v-if="isDark"
            class="relative w-5 h-5 text-amber-400 transition-transform duration-500 group-hover:rotate-45"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
            />
        </svg>
        
        <!-- Moon Icon (shown in light mode) -->
        <svg
            v-else
            class="relative w-5 h-5 text-violet-600 transition-transform duration-500 group-hover:-rotate-12"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
        </svg>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const isDark = ref(false)

const toggleTheme = () => {
    isDark.value = !isDark.value
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
    applyTheme()
}

const applyTheme = () => {
    const html = document.documentElement
    if (isDark.value) {
        html.classList.add('dark')
    } else {
        html.classList.remove('dark')
    }
    // Force a repaint to ensure styles update
    void html.offsetHeight
}

onMounted(() => {
    // Initialize theme from localStorage or system preference
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme) {
        isDark.value = savedTheme === 'dark'
    } else {
        // Check system preference
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    applyTheme()
})
</script>
