<template>
    <div class="relative">
        <!-- Parent Menu Item -->
        <button
            @click="$emit('toggle')"
            :class="[
                'w-full group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200',
                isActive 
                    ? 'bg-gradient-to-r from-violet-500/20 to-violet-500/5 text-white' 
                    : 'text-gray-400 hover:bg-white/5 hover:text-white',
                isCollapsed && 'justify-center px-3'
            ]"
            :title="isCollapsed ? item.title : ''"
        >
            <!-- Icon container -->
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
                        stroke-width="1.5" 
                        :d="item.icon" 
                    />
                </svg>
                <!-- Glow effect -->
                <div 
                    v-if="isActive" 
                    class="absolute inset-0 bg-violet-500/30 blur-lg rounded-full"
                ></div>
            </div>
            
            <span 
                v-if="!isCollapsed" 
                class="flex-1 text-left truncate"
            >
                {{ item.title }}
            </span>
            
            <!-- Chevron indicator -->
            <svg 
                v-if="!isCollapsed"
                :class="[
                    'w-4 h-4 text-gray-500 transition-transform duration-300',
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
                            ? 'bg-violet-500/10 text-violet-300'
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
                        class="ml-auto w-1.5 h-1.5 rounded-full bg-violet-400"
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
                class="absolute left-full ml-3 top-0 py-2 w-56 bg-gray-900 rounded-xl shadow-2xl border border-white/10 z-50"
            >
                <!-- Panel header -->
                <div class="px-4 py-2 border-b border-white/5 mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ item.title }}</span>
                </div>
                
                <div class="space-y-0.5 px-2">
                    <a
                        v-for="child in item.children"
                        :key="child.route"
                        :href="child.route"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150',
                            isChildActive(child.route)
                                ? 'bg-violet-500/20 text-violet-300'
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
    }
})

defineEmits(['toggle'])

const isChildActive = (childRoute) => {
    const currentPath = window.location.pathname
    return currentPath === childRoute || currentPath.startsWith(childRoute + '/')
}

const isActive = computed(() => {
    return props.item.children?.some(child => isChildActive(child.route)) || false
})
</script>
