<template>
    <aside 
        :class="[
            'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-out',
            isCollapsed ? 'w-20' : 'w-72',
            isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <!-- Sidebar Background - BTP Industrial Theme -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#1a1d24] via-[#1e2530] to-[#141820]"></div>
        
        <!-- Steel blue overlay for industrial feel -->
        <div class="absolute inset-0 opacity-40">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-950/60 via-transparent to-slate-900/50"></div>
        </div>
        
        <!-- Decorative construction accents -->
        <div class="absolute inset-0 overflow-hidden">
            <!-- Top orange/amber glow - construction safety accent -->
            <div class="absolute -top-24 -right-12 w-56 h-56 bg-gradient-to-br from-orange-500/15 to-amber-600/10 rounded-full blur-3xl"></div>
            <!-- Bottom steel blue glow -->
            <div class="absolute -bottom-20 -left-12 w-52 h-52 bg-gradient-to-tr from-blue-600/15 to-slate-500/10 rounded-full blur-3xl"></div>
            <!-- Yellow caution accent -->
            <div class="absolute top-1/2 right-0 w-32 h-32 bg-gradient-to-l from-yellow-500/8 to-transparent rounded-full blur-2xl"></div>
            <!-- Industrial grid pattern -->
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 20px 20px;"></div>
        </div>
        
        <!-- Animated BTP Construction Icons -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <!-- Floating Hard Hat 1 -->
            <svg class="absolute top-[10%] left-[10%] w-8 h-8 opacity-[0.08] animate-float-slow" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C8.5 2 5.5 4.5 5 8H4C3.5 8 3 8.5 3 9V10C3 10.5 3.5 11 4 11H5V12C5 13.1 5.9 14 7 14H17C18.1 14 19 13.1 19 12V11H20C20.5 11 21 10.5 21 10V9C21 8.5 20.5 8 20 8H19C18.5 4.5 15.5 2 12 2Z" class="text-orange-400"/>
            </svg>
            
            <!-- Rotating Gear 1 -->
            <svg class="absolute top-[25%] right-[15%] w-10 h-10 opacity-[0.06] animate-spin-slow" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 15.5A3.5 3.5 0 0 1 8.5 12A3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5a3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.64.07-.97c0-.33-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.39-1.06-.73-1.69-.98l-.37-2.65A.506.506 0 0 0 14 2h-4c-.25 0-.46.18-.5.42l-.37 2.65c-.63.25-1.17.59-1.69.98l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1c0 .33.03.65.07.97l-2.11 1.66c-.19.15-.25.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.06.74 1.69.99l.37 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.37-2.65c.63-.26 1.17-.59 1.69-.99l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66Z" class="text-blue-400"/>
            </svg>
            
            <!-- Floating Wrench -->
            <svg class="absolute top-[40%] left-[5%] w-7 h-7 opacity-[0.07] animate-float-medium" viewBox="0 0 24 24" fill="currentColor">
                <path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9c-2-2-5-2.4-7.4-1.3L9 6 6 9L1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4Z" class="text-amber-400"/>
            </svg>
            
            <!-- Floating Hammer -->
            <svg class="absolute top-[55%] right-[8%] w-6 h-6 opacity-[0.06] animate-float-fast" viewBox="0 0 24 24" fill="currentColor">
                <path d="M2 19.63L13.43 8.2L12.72 7.5L14.14 6.07L12 3.93C13.56 2.37 16.03 2.37 17.59 3.93L19 2.5L21.5 5L20.07 6.43L21.5 7.85L20.07 9.28L18.66 7.87L13.41 13.12L14.83 14.54L13.41 15.95L12 14.54L3.41 23.13L2 21.72L10.59 13.13L9.17 11.71L10.59 10.29L12 11.71L14.83 8.88L13.41 7.46L14.83 6.05L12.72 3.93L11.3 5.35L13.43 7.5L12 8.91L3.41 17.5L2 19.63Z" class="text-slate-400"/>
            </svg>
            
            <!-- Rotating Gear 2 (smaller) -->
            <svg class="absolute top-[70%] left-[15%] w-6 h-6 opacity-[0.05] animate-spin-reverse" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 15.5A3.5 3.5 0 0 1 8.5 12A3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5a3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.64.07-.97c0-.33-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.39-1.06-.73-1.69-.98l-.37-2.65A.506.506 0 0 0 14 2h-4c-.25 0-.46.18-.5.42l-.37 2.65c-.63.25-1.17.59-1.69.98l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1c0 .33.03.65.07.97l-2.11 1.66c-.19.15-.25.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.06.74 1.69.99l.37 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.37-2.65c.63-.26 1.17-.59 1.69-.99l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66Z" class="text-orange-400"/>
            </svg>
            
            <!-- Floating Crane -->
            <svg class="absolute top-[15%] right-[5%] w-12 h-12 opacity-[0.07] animate-float-slow" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.5 10H19V4H17V10H12V6H10V10H5C3.9 10 3 10.9 3 12V21H21V12C21 10.9 20.1 10 20.5 10M19 19H5V12H19V19M6 13H8V15H6V13M6 16H8V18H6V16M10 13H12V15H10V13M10 16H12V18H10V16M14 13H16V15H14V13M14 16H16V18H14V16Z" class="text-amber-400"/>
            </svg>
            
            <!-- Floating Bulldozer -->
            <svg class="absolute bottom-[30%] right-[12%] w-9 h-9 opacity-[0.06] animate-float-medium" viewBox="0 0 24 24" fill="currentColor">
                <path d="M4 17H2V15H4V17M20 15V17H22V15H20M18 15V17H19V15H18M20 14H22V12H20V14M17 14V12H18V14H17M4 14V12H2V14H4M5 14H6V12H5V14M21 5V11H3V5H21M19 7H5V9H19V7M6 13V17H7V13H6M17 17V13H16V17H17M8 17H15V13H8V17M9 14H14V16H9V14Z" class="text-yellow-500"/>
            </svg>
            
            <!-- Floating Hard Hat 2 -->
            <svg class="absolute bottom-[45%] left-[8%] w-6 h-6 opacity-[0.05] animate-float-fast" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C8.5 2 5.5 4.5 5 8H4C3.5 8 3 8.5 3 9V10C3 10.5 3.5 11 4 11H5V12C5 13.1 5.9 14 7 14H17C18.1 14 19 13.1 19 12V11H20C20.5 11 21 10.5 21 10V9C21 8.5 20.5 8 20 8H19C18.5 4.5 15.5 2 12 2Z" class="text-yellow-400"/>
            </svg>
            
            <!-- Floating Screwdriver -->
            <svg class="absolute top-[85%] right-[20%] w-5 h-5 opacity-[0.05] animate-float-slow" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.36 2.64C19.07 1.93 20.23 1.93 20.94 2.64C21.66 3.36 21.66 4.5 20.94 5.22L13.73 12.43L11.57 10.27L18.36 2.64M11 6.83L6.5 11.34C5.5 12.34 5.5 14 6.5 15L9 17.5C10 18.5 11.66 18.5 12.66 17.5L17.17 13L14.5 10.33L11 6.83M6 21H4V19L9.77 13.22L11.78 15.23L6 21Z" class="text-blue-400"/>
            </svg>
            
            <!-- Floating Truck -->
            <svg class="absolute bottom-[15%] left-[20%] w-10 h-10 opacity-[0.06] animate-float-medium" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 18.5C18.83 18.5 19.5 17.83 19.5 17C19.5 16.17 18.83 15.5 18 15.5C17.17 15.5 16.5 16.17 16.5 17C16.5 17.83 17.17 18.5 18 18.5M19.5 9.5L21.46 12H17V9.5M6 18.5C6.83 18.5 7.5 17.83 7.5 17C7.5 16.17 6.83 15.5 6 15.5C5.17 15.5 4.5 16.17 4.5 17C4.5 17.83 5.17 18.5 6 18.5M20 8H17V4H3C1.89 4 1 4.89 1 6V17H3C3 18.66 4.34 20 6 20C7.66 20 9 18.66 9 17H15C15 18.66 16.34 20 18 20C19.66 20 21 18.66 21 17H23V12L20 8Z" class="text-orange-400"/>
            </svg>
            
            <!-- Pulsing Circle Accent 1 -->
            <div class="absolute top-[20%] left-[50%] w-3 h-3 rounded-full bg-orange-400/20 animate-pulse-slow"></div>
            
            <!-- Pulsing Circle Accent 2 -->
            <div class="absolute top-[60%] right-[25%] w-2 h-2 rounded-full bg-blue-400/20 animate-pulse-medium"></div>
            
            <!-- Pulsing Circle Accent 3 -->
            <div class="absolute bottom-[25%] left-[35%] w-2 h-2 rounded-full bg-amber-400/20 animate-pulse-fast"></div>
            
            <!-- City skyline silhouette at bottom (static) -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-32 opacity-[0.04]" viewBox="0 0 400 100" preserveAspectRatio="xMidYMax slice" fill="currentColor">
                <rect x="10" y="50" width="25" height="50" class="text-white"/>
                <rect x="40" y="30" width="35" height="70" class="text-white"/>
                <rect x="85" y="60" width="20" height="40" class="text-white"/>
                <rect x="115" y="20" width="40" height="80" class="text-white"/>
                <rect x="165" y="45" width="30" height="55" class="text-white"/>
                <rect x="200" y="65" width="22" height="35" class="text-white"/>
                <rect x="230" y="15" width="45" height="85" class="text-white"/>
                <rect x="285" y="55" width="25" height="45" class="text-white"/>
                <rect x="320" y="35" width="35" height="65" class="text-white"/>
                <rect x="365" y="60" width="30" height="40" class="text-white"/>
            </svg>
        </div>
        
        <!-- Right edge industrial border -->
        <div class="absolute top-0 right-0 w-px h-full bg-gradient-to-b from-orange-500/20 via-slate-600/30 to-orange-500/20"></div>
        
        <!-- Bottom accent stripe - construction safety -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-orange-500/30 to-transparent"></div>
        
        <!-- Content -->
        <div class="relative z-10 flex flex-col h-full">
            <!-- Sidebar Header -->
            <div class="flex-shrink-0 flex items-center justify-between h-20 px-4 border-b border-white/5">
                <div v-if="!isCollapsed" class="flex items-center gap-3">
                    <div class="relative">
                        <!-- Gradient logo icon -->
                        <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 p-0.5 shadow-lg shadow-orange-500/40">
                            <div class="h-full w-full rounded-[10px] bg-[#1a1d24] flex items-center justify-center">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="url(#logoGrad1)" stroke-width="2"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="url(#logoGrad2)" stroke-width="2"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="url(#logoGrad3)" stroke-width="2"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="url(#logoGrad4)" stroke-width="2"/>
                                    <defs>
                                        <linearGradient id="logoGrad1" x1="3" y1="3" x2="10" y2="10">
                                            <stop stop-color="#f97316"/>
                                            <stop offset="1" stop-color="#f59e0b"/>
                                        </linearGradient>
                                        <linearGradient id="logoGrad2" x1="14" y1="3" x2="21" y2="10">
                                            <stop stop-color="#f59e0b"/>
                                            <stop offset="1" stop-color="#eab308"/>
                                        </linearGradient>
                                        <linearGradient id="logoGrad3" x1="3" y1="14" x2="10" y2="21">
                                            <stop stop-color="#ea580c"/>
                                            <stop offset="1" stop-color="#3b82f6"/>
                                        </linearGradient>
                                        <linearGradient id="logoGrad4" x1="14" y1="14" x2="21" y2="21">
                                            <stop stop-color="#3b82f6"/>
                                            <stop offset="1" stop-color="#0ea5e9"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-gradient-to-r from-orange-400 via-amber-400 to-yellow-400 bg-clip-text text-transparent">Bouyahya</span>
                    </div>
                </div>
                <div v-else class="flex items-center justify-center w-full">
                    <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 p-0.5 shadow-lg shadow-orange-500/40">
                        <div class="h-full w-full rounded-[10px] bg-[#1a1d24] flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="url(#logoGrad1)" stroke-width="2"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="url(#logoGrad2)" stroke-width="2"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="url(#logoGrad3)" stroke-width="2"/>
                                <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="url(#logoGrad4)" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <button 
                    @click="toggleCollapse"
                    class="p-2.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all duration-200 hidden lg:flex border border-white/5"
                    v-if="!isMobile"
                >
                    <svg :class="['w-4 h-4 transition-transform duration-300', isCollapsed ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button 
                    @click="closeMobile"
                    class="p-2.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all duration-200 lg:hidden border border-white/5"
                    v-if="isMobile"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 min-h-0 overflow-y-auto py-6 px-3 space-y-1 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                <!-- Dashboard -->
                <MenuItem 
                    v-if="authorizedMenuItems.dashboard"
                    :item="authorizedMenuItems.dashboard" 
                    :is-collapsed="isCollapsed"
                    :is-active="currentRoute === authorizedMenuItems.dashboard?.route"
                    icon-color="orange"
                />

                <!-- Section Label -->
                <div v-if="!isCollapsed && (authorizedMenuItems.clients || authorizedMenuItems.fournisseurs)" class="pt-6 pb-2 px-4">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500/80">Principal</span>
                </div>
                <div v-else-if="authorizedMenuItems.clients || authorizedMenuItems.fournisseurs" class="pt-4 pb-2 flex justify-center">
                    <div class="w-6 h-px bg-gradient-to-r from-transparent via-gray-600 to-transparent"></div>
                </div>

                <!-- Gestion des Clients -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.clients"
                    :item="authorizedMenuItems.clients" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.clients"
                    @toggle="toggleMenu('clients')"
                    icon-color="amber"
                />

                <!-- Gestion des Fournisseurs -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.fournisseurs"
                    :item="authorizedMenuItems.fournisseurs" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.fournisseurs"
                    @toggle="toggleMenu('fournisseurs')"
                    icon-color="blue"
                />

                <!-- Section Label -->
                <div v-if="!isCollapsed && (authorizedMenuItems.achats || authorizedMenuItems.ventes || authorizedMenuItems.stock)" class="pt-6 pb-2 px-4">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500/80">Operations</span>
                </div>
                <div v-else-if="authorizedMenuItems.achats || authorizedMenuItems.ventes || authorizedMenuItems.stock" class="pt-4 pb-2 flex justify-center">
                    <div class="w-6 h-px bg-gradient-to-r from-transparent via-gray-600 to-transparent"></div>
                </div>

                <!-- Gestion des Achats -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.achats"
                    :item="authorizedMenuItems.achats" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.achats"
                    @toggle="toggleMenu('achats')"
                    icon-color="sky"
                />

                <!-- Gestion des Ventes -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.ventes"
                    :item="authorizedMenuItems.ventes" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.ventes"
                    @toggle="toggleMenu('ventes')"
                    icon-color="orange"
                />

                <!-- Gestion du Stock -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.stock"
                    :item="authorizedMenuItems.stock" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.stock"
                    @toggle="toggleMenu('stock')"
                    icon-color="emerald"
                />

                <!-- Section Label -->
                <div v-if="!isCollapsed && authorizedMenuItems.tresorerie" class="pt-6 pb-2 px-4">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500/80">Finance</span>
                </div>
                <div v-else-if="authorizedMenuItems.tresorerie" class="pt-4 pb-2 flex justify-center">
                    <div class="w-6 h-px bg-gradient-to-r from-transparent via-gray-600 to-transparent"></div>
                </div>

                <!-- Gestion Trésorerie -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.tresorerie"
                    :item="authorizedMenuItems.tresorerie" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.tresorerie"
                    @toggle="toggleMenu('tresorerie')"
                    icon-color="green"
                />

                <!-- Section Label -->
                <div v-if="!isCollapsed && (authorizedMenuItems.utilisateurs || authorizedMenuItems.parametres)" class="pt-6 pb-2 px-4">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500/80">Systeme</span>
                </div>
                <div v-else-if="authorizedMenuItems.utilisateurs || authorizedMenuItems.parametres" class="pt-4 pb-2 flex justify-center">
                    <div class="w-6 h-px bg-gradient-to-r from-transparent via-gray-600 to-transparent"></div>
                </div>

                <!-- Gestion des Utilisateurs -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.utilisateurs"
                    :item="authorizedMenuItems.utilisateurs" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.utilisateurs"
                    @toggle="toggleMenu('utilisateurs')"
                    icon-color="sky"
                />

                <!-- Paramètres -->
                <CollapsibleMenuItem 
                    v-if="authorizedMenuItems.parametres"
                    :item="authorizedMenuItems.parametres" 
                    :is-collapsed="isCollapsed"
                    :is-open="openMenus.parametres"
                    @toggle="toggleMenu('parametres')"
                    icon-color="amber"
                />
            </nav>

            <!-- Sidebar Footer - User Profile -->
            <div class="flex-shrink-0 p-4 border-t border-white/5">
                <div v-if="!isCollapsed" class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/5 transition-all duration-200 cursor-pointer group">
                    <!-- Avatar with gradient ring -->
                    <div class="relative">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-500 via-amber-500 to-blue-500 p-0.5">
                            <div class="h-full w-full rounded-full bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center text-white text-sm font-bold">
                                {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                        </div>
                        <!-- Online indicator -->
                        <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-[#1a1d24]"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ user?.name || 'Utilisateur' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ getUserRole() }}</p>
                    </div>
                    <!-- Logout button -->
                    <a href="/logout" class="p-2 rounded-lg text-gray-500 hover:text-white hover:bg-white/10 transition-all duration-200 opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
                <div v-else class="flex justify-center py-2">
                    <div class="relative group cursor-pointer">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-500 via-amber-500 to-blue-500 p-0.5">
                            <div class="h-full w-full rounded-full bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center text-white text-sm font-bold">
                                {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                        </div>
                        <!-- Online indicator -->
                        <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-[#1a1d24]"></div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Mobile Overlay -->
    <div 
        v-if="isMobile && isMobileOpen"
        @click="closeMobile"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-opacity"
    ></div>

    <!-- Mobile Menu Toggle Button -->
    <button 
        @click="openMobile"
        class="fixed top-5 left-5 z-50 p-3 bg-[#1a1d24]/90 backdrop-blur-sm rounded-xl shadow-xl lg:hidden border border-orange-500/20 group transition-all hover:bg-[#1e2530]"
    >
        <svg class="w-5 h-5 text-white group-hover:text-orange-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import MenuItem from './MenuItem.vue'
import CollapsibleMenuItem from './CollapsibleMenuItem.vue'

const emit = defineEmits(['collapse-change'])

const props = defineProps({
    user: {
        type: Object,
        default: () => ({})
    }
})

const isCollapsed = ref(false)
const isMobileOpen = ref(false)
const isMobile = ref(false)

const hasPermission = (permission) => {
    if (!props.user) return false
    
    // Fallback for legacy superadmin role column
    if (props.user.role === 'superadmin') return true
    
    if (!props.user.roles) return false
    
    // Superadmin role name check
    if (props.user.roles.some(role => role.name === 'superadmin')) return true
    
    // Extract all permission names
    const userPermissions = (props.user?.roles || []).flatMap(role => {
        if (!role || !role.permissions) return []
        return role.permissions.map(p => typeof p === 'string' ? p : (p?.name || ''))
    })

    const checkSingle = (p) => {
        if (!p) return true
        
        // Direct match
        if (userPermissions.includes(p)) return true
        
        // Granular to legacy/module mapping (e.g. user has 'manage achats', we check 'achats.view')
        if (p.startsWith('achats.') && userPermissions.includes('manage achats')) return true
        if (p.startsWith('ventes.') && userPermissions.includes('manage ventes')) return true
        if (p.startsWith('stock.') && userPermissions.includes('manage stock')) return true
        if (p.startsWith('tresorerie.') && userPermissions.includes('manage tresorerie')) return true
        if (p.startsWith('contacts.clients.') && userPermissions.includes('manage clients')) return true
        if (p.startsWith('contacts.fournisseurs.') && userPermissions.includes('manage fournisseurs')) return true
        if ((p.startsWith('admin.users.') || p.startsWith('admin.roles.')) && userPermissions.includes('manage users')) return true
        if (p.startsWith('admin.settings.') && userPermissions.includes('manage settings')) return true

        // Legacy/Module to granular mapping (e.g. user has 'achats.view', we check 'manage achats')
        if (p === 'manage achats' && userPermissions.some(up => up.startsWith('achats.'))) return true
        if (p === 'manage ventes' && userPermissions.some(up => up.startsWith('ventes.'))) return true
        if (p === 'manage stock' && userPermissions.some(up => up.startsWith('stock.'))) return true
        if (p === 'manage tresorerie' && userPermissions.some(up => up.startsWith('tresorerie.'))) return true
        if (p === 'manage clients' && userPermissions.some(up => up.startsWith('contacts.clients.'))) return true
        if (p === 'manage fournisseurs' && userPermissions.some(up => up.startsWith('contacts.fournisseurs.'))) return true
        if (p === 'manage users' && userPermissions.some(up => up.startsWith('admin.users.') || up.startsWith('admin.roles.'))) return true
        if (p === 'manage settings' && userPermissions.some(up => up.startsWith('admin.settings.'))) return true
        
        return false
    }

    if (Array.isArray(permission)) {
        return permission.some(p => checkSingle(p))
    }
    
    return checkSingle(permission)
}

const getUserRole = () => {
    if (!props.user) return ''
    if (props.user.role === 'superadmin') return 'Super Admin'
    if (props.user.roles && props.user.roles.length > 0) {
        return props.user.roles[0].name
    }
    return 'Utilisateur'
}

const openMenus = ref({
    achats: false,
    ventes: false,
    clients: false,
    fournisseurs: false,
    stock: false,
    tresorerie: false,
    utilisateurs: false,
    parametres: false
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
        route: '/dashboard',
        permission: 'view dashboard'
    },
    utilisateurs: {
        title: 'Utilisateurs',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        permission: 'manage users',
        children: [
            { title: 'Liste utilisateurs', route: '/utilisateurs', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', permission: 'admin.users.view' },
            { title: 'Roles et Permissions', route: '/utilisateurs/roles', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', permission: 'admin.roles.view' }
        ]
    },
    parametres: {
        title: 'Parametres',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        permission: 'manage settings',
        children: [
            { title: 'Application', route: '/parametres/application', icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', permission: 'admin.settings.view' },
            { title: 'Villes', route: '/parametres/villes', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', permission: 'admin.settings.view' },
            { title: 'Articles', route: '/parametres/articles', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', permission: 'admin.settings.view' },
            { title: 'Commerciales', route: '/parametres/commerciales', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', permission: 'admin.settings.view' },
            { title: 'Transports', route: '/parametres/transports', icon: 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2', permission: 'admin.settings.view' },
            { title: 'Matricules', route: '/parametres/matricules', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', permission: 'admin.settings.view' },
            { title: 'Banques', route: '/parametres/banques', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', permission: 'admin.settings.view' }
        ]
    },
    achats: {
        title: 'Achats',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        permission: 'manage achats',
        children: [
            { title: 'Bon de commande', route: '/achats/bon-commande', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', permission: 'achats.bon-commande.view' },
            { title: 'Bon d\'achat Fournisseur', route: '/achats/bon-achat-fournisseur', icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', permission: 'achats.bon-achat.view' },
            { title: 'Reglements fournisseurs', route: '/achats/reglements-fournisseurs', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', permission: 'achats.reglement.view' },
            { title: 'Historique achats', route: '/achats/historique', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', permission: 'achats.historique.view' },
            { title: 'Releve compte fournisseurs', route: '/achats/releve-compte-fournisseurs', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', permission: 'achats.releve.view' },
            { title: 'Echeancier fournisseurs', route: '/achats/echeancier-fournisseurs', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', permission: 'achats.echeancier.view' }
        ]
    },
    ventes: {
        title: 'Ventes',
        icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        permission: 'manage ventes',
        children: [
            { title: 'Bon de commande', route: '/ventes/bon-commande', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', permission: 'ventes.bon-commande.view' },
            { title: 'Bon de livraison', route: '/ventes/bon-livraison', icon: 'M5 13l4 4L19 7', permission: 'ventes.bon-livraison.view' },
            { title: 'Reglements clients', route: '/ventes/reglements-clients-gestion', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', permission: 'ventes.reglement.view' },
            { title: 'Historique ventes', route: '/ventes/historique', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', permission: 'ventes.historique.view' },
            { title: 'Releve compte clients', route: '/ventes/releve-compte-clients', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', permission: 'ventes.releve.view' }
        ]
    },
    clients: {
        title: 'Clients',
        icon: 'M17 20h5V8H2v12h5m10 0V8m0 12v2m-10-2v2m0-2H2m10 0h10M2 8l10-6 10 6',
        permission: 'manage clients',
        children: [
            { title: 'Liste des clients', route: '/clients', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', permission: 'contacts.clients.view' },
            { title: 'Nouveau client', route: '/clients/create', icon: 'M12 4v16m8-8H4', permission: 'contacts.clients.create' }
        ]
    },
    fournisseurs: {
        title: 'Fournisseurs',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        permission: 'manage fournisseurs',
        children: [
            { title: 'Liste des fournisseurs', route: '/fournisseurs', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', permission: 'contacts.fournisseurs.view' },
            { title: 'Nouveau fournisseur', route: '/fournisseurs/create', icon: 'M12 4v16m8-8H4', permission: 'contacts.fournisseurs.create' }
        ]
    },
    stock: {
        title: 'Stock',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        permission: 'manage stock',
        children: [
            { title: 'Articles', route: '/stock/articles', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', permission: 'stock.articles.view' },
            { title: 'Mouvement stock', route: '/stock/mouvement', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', permission: 'stock.mouvement.view' },
            { title: 'Les stocks', route: '/stock/stocks', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', permission: 'stock.etat.view' }
        ]
    },
    tresorerie: {
        title: 'Tresorerie',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        permission: 'manage tresorerie',
        children: [
            { title: 'Types de charges', route: '/tresorerie/types-charges', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', permission: 'tresorerie.charges.view' },
            { title: 'Releve reglements', route: '/tresorerie/releve-reglements', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', permission: 'tresorerie.releve-reglements.view' },
            { title: 'Balance caisse', route: '/tresorerie/balance-caisse', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', permission: 'tresorerie.balance-caisse.view' },
            { title: 'Liste reg impots', route: '/tresorerie/liste-impots', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', permission: 'tresorerie.impots.view' },
            { title: 'Compte tresorerie', route: '/tresorerie/compte-bancaire', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', permission: 'tresorerie.compte-bancaire.view' },
            { title: 'Encaissement / Decaissement', route: '/tresorerie/encaissement-decaissement', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', permission: 'tresorerie.encaissement.view' }
        ]
    }
}

// Helper to check if user has a permission DIRECTLY (not through bidirectional mapping)
const hasDirectPermission = (permission) => {
    if (!props.user) return false
    if (props.user.role === 'superadmin') return true
    if (!props.user.roles) return false
    if (props.user.roles.some(role => role.name === 'superadmin')) return true
    
    const userPermissions = (props.user?.roles || []).flatMap(role => {
        if (!role || !role.permissions) return []
        return role.permissions.map(p => typeof p === 'string' ? p : (p?.name || ''))
    })
    
    return userPermissions.includes(permission)
}

// Computed property to filter menu items based on authorized children or main permission
const authorizedMenuItems = computed(() => {
    const result = {}
    
    for (const [key, item] of Object.entries(menuItems)) {
        if (key === 'dashboard') {
            if (hasPermission(item.permission)) result[key] = item
            continue
        }

        // Check if user has the DIRECT legacy permission (like 'manage achats')
        // Only then show ALL children - otherwise filter by specific permissions
        const hasFullAccess = item.permission && hasDirectPermission(item.permission)

        const filteredChildren = item.children?.filter(child => {
            // If user has full access via legacy permission, show all children
            if (hasFullAccess) return true
            // Otherwise, only show children the user has specific permission for
            return !child.permission || hasDirectPermission(child.permission)
        })

        // Item is shown if it has at least one authorized child
        if (filteredChildren && filteredChildren.length > 0) {
            result[key] = {
                ...item,
                children: filteredChildren
            }
        }
    }
    return result
})


const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value
    emit('collapse-change', isCollapsed.value)
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
    } else if (currentPath.startsWith('/utilisateurs')) {
        openMenus.value.utilisateurs = true
    } else if (currentPath.startsWith('/parametres')) {
        openMenus.value.parametres = true
    }
})

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
    window.removeEventListener('popstate', updateRoute)
})
</script>

<style scoped>
/* Floating animations */
@keyframes float-slow {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(5deg);
    }
}

@keyframes float-medium {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-10px) rotate(-3deg);
    }
}

@keyframes float-fast {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

/* Spin animations */
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes spin-reverse {
    from {
        transform: rotate(360deg);
    }
    to {
        transform: rotate(0deg);
    }
}

/* Pulse animations */
@keyframes pulse-slow {
    0%, 100% {
        opacity: 0.2;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.5);
    }
}

@keyframes pulse-medium {
    0%, 100% {
        opacity: 0.15;
        transform: scale(1);
    }
    50% {
        opacity: 0.4;
        transform: scale(1.3);
    }
}

@keyframes pulse-fast {
    0%, 100% {
        opacity: 0.1;
        transform: scale(1);
    }
    50% {
        opacity: 0.35;
        transform: scale(1.2);
    }
}

/* Animation classes */
.animate-float-slow {
    animation: float-slow 8s ease-in-out infinite;
}

.animate-float-medium {
    animation: float-medium 6s ease-in-out infinite;
}

.animate-float-fast {
    animation: float-fast 4s ease-in-out infinite;
}

.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}

.animate-spin-reverse {
    animation: spin-reverse 15s linear infinite;
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}

.animate-pulse-medium {
    animation: pulse-medium 3s ease-in-out infinite;
}

.animate-pulse-fast {
    animation: pulse-fast 2s ease-in-out infinite;
}
</style>
