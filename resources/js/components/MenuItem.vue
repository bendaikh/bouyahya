<template>
    <a
        :href="item.route"
        :class="[
            'group relative flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200',
            isActive 
                ? 'text-white' 
                : 'text-gray-400 hover:bg-white/5 hover:text-white',
            isCollapsed && 'justify-center px-3'
        ]"
        :title="isCollapsed ? item.title : ''"
    >
        <!-- Active background glow -->
        <div 
            v-if="isActive" 
            :class="[
                'absolute inset-0 rounded-xl opacity-100',
                getActiveBgClass
            ]"
        ></div>
        
        <!-- Left border indicator for active state -->
        <div 
            v-if="isActive && !isCollapsed" 
            :class="[
                'absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 rounded-r-full',
                getActiveIndicatorClass
            ]"
        ></div>
        
        <!-- Icon container with color -->
        <div :class="[
            'relative flex-shrink-0 flex items-center justify-center z-10',
            getIconColorClass
        ]">
            <svg 
                :class="[
                    'w-5 h-5 transition-all duration-200',
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
            <!-- Glow effect for icon -->
            <div 
                v-if="isActive" 
                :class="[
                    'absolute inset-0 blur-lg rounded-full opacity-50',
                    getIconGlowClass
                ]"
            ></div>
        </div>
        
        <span 
            v-if="!isCollapsed" 
            class="relative flex-1 truncate z-10"
        >
            {{ item.title }}
        </span>
    </a>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
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
    },
    iconColor: {
        type: String,
        default: 'violet'
    }
})

const colorClasses = {
    orange: {
        icon: 'text-orange-400',
        glow: 'bg-orange-400',
        activeBg: 'bg-gradient-to-r from-orange-500/20 via-orange-500/10 to-transparent',
        indicator: 'bg-orange-400'
    },
    cyan: {
        icon: 'text-cyan-400',
        glow: 'bg-cyan-400',
        activeBg: 'bg-gradient-to-r from-cyan-500/20 via-cyan-500/10 to-transparent',
        indicator: 'bg-cyan-400'
    },
    violet: {
        icon: 'text-violet-400',
        glow: 'bg-violet-400',
        activeBg: 'bg-gradient-to-r from-violet-500/20 via-violet-500/10 to-transparent',
        indicator: 'bg-violet-400'
    },
    purple: {
        icon: 'text-purple-400',
        glow: 'bg-purple-400',
        activeBg: 'bg-gradient-to-r from-purple-500/20 via-purple-500/10 to-transparent',
        indicator: 'bg-purple-400'
    },
    blue: {
        icon: 'text-blue-400',
        glow: 'bg-blue-400',
        activeBg: 'bg-gradient-to-r from-blue-500/20 via-blue-500/10 to-transparent',
        indicator: 'bg-blue-400'
    },
    fuchsia: {
        icon: 'text-fuchsia-400',
        glow: 'bg-fuchsia-400',
        activeBg: 'bg-gradient-to-r from-fuchsia-500/20 via-fuchsia-500/10 to-transparent',
        indicator: 'bg-fuchsia-400'
    },
    emerald: {
        icon: 'text-emerald-400',
        glow: 'bg-emerald-400',
        activeBg: 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent',
        indicator: 'bg-emerald-400'
    },
    green: {
        icon: 'text-green-400',
        glow: 'bg-green-400',
        activeBg: 'bg-gradient-to-r from-green-500/20 via-green-500/10 to-transparent',
        indicator: 'bg-green-400'
    },
    sky: {
        icon: 'text-sky-400',
        glow: 'bg-sky-400',
        activeBg: 'bg-gradient-to-r from-sky-500/20 via-sky-500/10 to-transparent',
        indicator: 'bg-sky-400'
    },
    amber: {
        icon: 'text-amber-400',
        glow: 'bg-amber-400',
        activeBg: 'bg-gradient-to-r from-amber-500/20 via-amber-500/10 to-transparent',
        indicator: 'bg-amber-400'
    },
    rose: {
        icon: 'text-rose-400',
        glow: 'bg-rose-400',
        activeBg: 'bg-gradient-to-r from-rose-500/20 via-rose-500/10 to-transparent',
        indicator: 'bg-rose-400'
    }
}

const getIconColorClass = computed(() => {
    return colorClasses[props.iconColor]?.icon || colorClasses.violet.icon
})

const getIconGlowClass = computed(() => {
    return colorClasses[props.iconColor]?.glow || colorClasses.violet.glow
})

const getActiveBgClass = computed(() => {
    return colorClasses[props.iconColor]?.activeBg || colorClasses.violet.activeBg
})

const getActiveIndicatorClass = computed(() => {
    return colorClasses[props.iconColor]?.indicator || colorClasses.violet.indicator
})
</script>
