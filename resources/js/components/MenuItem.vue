<template>
    <a
        :href="item.route"
        :class="[
            'group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200',
            isActive 
                ? 'bg-gradient-to-r from-violet-500/20 to-violet-500/5 text-white' 
                : 'text-gray-400 hover:bg-white/5 hover:text-white',
            isCollapsed && 'justify-center px-3'
        ]"
        :title="isCollapsed ? item.title : ''"
    >
        <!-- Icon container with glow effect when active -->
        <div :class="[
            'relative flex-shrink-0 flex items-center justify-center',
            isActive && 'text-violet-400'
        ]">
            <svg 
                :class="[
                    'w-5 h-5 transition-transform duration-200',
                    !isCollapsed && 'group-hover:scale-110'
                ]"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    :stroke-width="isActive ? '2' : '1.5'" 
                    :d="item.icon" 
                />
            </svg>
            <!-- Glow effect for active state -->
            <div 
                v-if="isActive" 
                class="absolute inset-0 bg-violet-500/30 blur-lg rounded-full"
            ></div>
        </div>
        
        <span 
            v-if="!isCollapsed" 
            class="flex-1 truncate"
        >
            {{ item.title }}
        </span>
        
        <!-- Active indicator -->
        <div 
            v-if="isActive && !isCollapsed" 
            class="w-1.5 h-1.5 rounded-full bg-violet-400 shadow-lg shadow-violet-500/50"
        ></div>
    </a>
</template>

<script setup>
defineProps({
    item: {
        type: Object,
        required: true
    },
    isCollapsed: {
        type: Boolean,
        default: false
    },
    isActive: {
        type: Boolean,
        default: false
    }
})
</script>
