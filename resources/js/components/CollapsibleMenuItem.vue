<template>
    <div class="relative">
        <!-- Parent Menu Item -->
        <button
            @click="$emit('toggle')"
            :class="[
                'w-full group relative flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200',
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
                        stroke-width="1.5" 
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
                class="relative flex-1 text-left truncate z-10"
            >
                {{ item.title }}
            </span>
            
            <!-- Chevron indicator -->
            <svg 
                v-if="!isCollapsed"
                :class="[
                    'relative z-10 w-4 h-4 text-gray-500 transition-transform duration-300',
                    isOpen ? 'rotate-90' : ''
                ]"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Submenu Items (Expanded) -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2 max-h-0"
            enter-to-class="opacity-100 translate-y-0 max-h-96"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 max-h-96"
            leave-to-class="opacity-0 -translate-y-2 max-h-0"
        >
            <div
                v-if="isOpen && !isCollapsed"
                class="mt-1 ml-4 pl-4 space-y-1 border-l border-white/10 overflow-hidden"
            >
                <a
                    v-for="(child, index) in item.children"
                    :key="child.route"
                    :href="child.route"
                    :class="[
                        'group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200',
                        isChildActive(child.route)
                            ? getChildActiveClass
                            : 'text-gray-500 hover:bg-white/5 hover:text-gray-300'
                    ]"
                    :style="{ animationDelay: `${index * 50}ms` }"
                >
                    <svg 
                        class="flex-shrink-0 w-4 h-4 transition-transform duration-200 group-hover:scale-110" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path 
                            stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="1.5" 
                            :d="child.icon || 'M9 5l7 7-7 7'" 
                        />
                    </svg>
                    <span class="truncate">{{ child.title }}</span>
                    
                    <!-- Active dot -->
                    <div 
                        v-if="isChildActive(child.route)" 
                        :class="[
                            'ml-auto w-1.5 h-1.5 rounded-full',
                            getChildDotClass
                        ]"
                    ></div>
                </a>
            </div>
        </Transition>

        <!-- Collapsed Submenu (Floating Panel) -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 translate-x-2"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-2"
        >
            <div
                v-if="isCollapsed && isOpen"
                class="absolute left-full ml-3 top-0 py-2 w-56 bg-[#1a1d24]/98 backdrop-blur-xl rounded-xl shadow-2xl border border-orange-500/10 z-50"
            >
                <!-- Panel header -->
                <div class="px-4 py-2 border-b border-white/5 mb-2">
                    <span :class="['text-xs font-semibold uppercase tracking-wider', getIconColorClass]">{{ item.title }}</span>
                </div>
                
                <div class="space-y-0.5 px-2">
                    <a
                        v-for="child in item.children"
                        :key="child.route"
                        :href="child.route"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150',
                            isChildActive(child.route)
                                ? getChildActiveClass
                                : 'text-gray-400 hover:bg-white/5 hover:text-white'
                        ]"
                    >
                        <svg 
                            class="flex-shrink-0 w-4 h-4" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path 
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="1.5" 
                                :d="child.icon || 'M9 5l7 7-7 7'" 
                            />
                        </svg>
                        <span class="truncate">{{ child.title }}</span>
                    </a>
                </div>
            </div>
        </Transition>
    </div>
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
    isOpen: {
        type: Boolean,
        default: false
    },
    iconColor: {
        type: String,
        default: 'violet'
    }
})

defineEmits(['toggle'])

const colorClasses = {
    orange: {
        icon: 'text-orange-400',
        glow: 'bg-orange-400',
        activeBg: 'bg-gradient-to-r from-orange-500/20 via-orange-500/10 to-transparent',
        indicator: 'bg-orange-400',
        childActive: 'bg-orange-500/10 text-orange-300',
        childDot: 'bg-orange-400'
    },
    cyan: {
        icon: 'text-cyan-400',
        glow: 'bg-cyan-400',
        activeBg: 'bg-gradient-to-r from-cyan-500/20 via-cyan-500/10 to-transparent',
        indicator: 'bg-cyan-400',
        childActive: 'bg-cyan-500/10 text-cyan-300',
        childDot: 'bg-cyan-400'
    },
    violet: {
        icon: 'text-violet-400',
        glow: 'bg-violet-400',
        activeBg: 'bg-gradient-to-r from-violet-500/20 via-violet-500/10 to-transparent',
        indicator: 'bg-violet-400',
        childActive: 'bg-violet-500/10 text-violet-300',
        childDot: 'bg-violet-400'
    },
    purple: {
        icon: 'text-purple-400',
        glow: 'bg-purple-400',
        activeBg: 'bg-gradient-to-r from-purple-500/20 via-purple-500/10 to-transparent',
        indicator: 'bg-purple-400',
        childActive: 'bg-purple-500/10 text-purple-300',
        childDot: 'bg-purple-400'
    },
    blue: {
        icon: 'text-blue-400',
        glow: 'bg-blue-400',
        activeBg: 'bg-gradient-to-r from-blue-500/20 via-blue-500/10 to-transparent',
        indicator: 'bg-blue-400',
        childActive: 'bg-blue-500/10 text-blue-300',
        childDot: 'bg-blue-400'
    },
    fuchsia: {
        icon: 'text-fuchsia-400',
        glow: 'bg-fuchsia-400',
        activeBg: 'bg-gradient-to-r from-fuchsia-500/20 via-fuchsia-500/10 to-transparent',
        indicator: 'bg-fuchsia-400',
        childActive: 'bg-fuchsia-500/10 text-fuchsia-300',
        childDot: 'bg-fuchsia-400'
    },
    emerald: {
        icon: 'text-emerald-400',
        glow: 'bg-emerald-400',
        activeBg: 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent',
        indicator: 'bg-emerald-400',
        childActive: 'bg-emerald-500/10 text-emerald-300',
        childDot: 'bg-emerald-400'
    },
    green: {
        icon: 'text-green-400',
        glow: 'bg-green-400',
        activeBg: 'bg-gradient-to-r from-green-500/20 via-green-500/10 to-transparent',
        indicator: 'bg-green-400',
        childActive: 'bg-green-500/10 text-green-300',
        childDot: 'bg-green-400'
    },
    sky: {
        icon: 'text-sky-400',
        glow: 'bg-sky-400',
        activeBg: 'bg-gradient-to-r from-sky-500/20 via-sky-500/10 to-transparent',
        indicator: 'bg-sky-400',
        childActive: 'bg-sky-500/10 text-sky-300',
        childDot: 'bg-sky-400'
    },
    amber: {
        icon: 'text-amber-400',
        glow: 'bg-amber-400',
        activeBg: 'bg-gradient-to-r from-amber-500/20 via-amber-500/10 to-transparent',
        indicator: 'bg-amber-400',
        childActive: 'bg-amber-500/10 text-amber-300',
        childDot: 'bg-amber-400'
    },
    rose: {
        icon: 'text-rose-400',
        glow: 'bg-rose-400',
        activeBg: 'bg-gradient-to-r from-rose-500/20 via-rose-500/10 to-transparent',
        indicator: 'bg-rose-400',
        childActive: 'bg-rose-500/10 text-rose-300',
        childDot: 'bg-rose-400'
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

const getChildActiveClass = computed(() => {
    return colorClasses[props.iconColor]?.childActive || colorClasses.violet.childActive
})

const getChildDotClass = computed(() => {
    return colorClasses[props.iconColor]?.childDot || colorClasses.violet.childDot
})

const isChildActive = (childRoute) => {
    const currentPath = window.location.pathname
    return currentPath === childRoute || currentPath.startsWith(childRoute + '/')
}

const isActive = computed(() => {
    return props.item.children?.some(child => isChildActive(child.route)) || false
})
</script>
