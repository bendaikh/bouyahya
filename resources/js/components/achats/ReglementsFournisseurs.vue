<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg flex flex-col h-[calc(100vh-130px)] overflow-hidden">
        <!-- Sticky Top Section -->
        <div class="p-6 pb-4 border-b border-gray-200 dark:border-gray-700 flex-none bg-white dark:bg-gray-800 z-20">
            <!-- Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">
                        consultation règlements des fournisseurs
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        consultation règlements des fournisseurs
                    </p>
                </div>

                <div class="flex flex-wrap justify-end gap-2">
                    <button v-if="!showForm" @click="openCreateForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center text-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Nouveau règlement
                    </button>

                    <template v-else>
                        <button @click="cancelForm" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Quitter
                        </button>
                        <button v-if="formMode !== 'view'" @click="saveReglement" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Valider
                        </button>
                        <button v-if="formMode === 'view' && editingReglementId" @click="formMode = 'edit'" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Modifier
                        </button>
                    </template>
                </div>
            </div>

            <!-- List View Top: Stats & Filters -->
            <div v-if="!showForm">
                <!-- Stat blocks -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-lg px-4 py-3 bg-blue-600 text-white shadow">
                        <div class="text-sm font-semibold">Total Règlements Fournisseurs</div>
                        <div class="mt-1 text-xl font-bold">{{ formatCurrencySimple(totalReglements) }}</div>
                    </div>
                    <div class="rounded-lg px-4 py-3 bg-green-600 text-white shadow">
                        <div class="text-sm font-semibold">Total Décaissé</div>
                        <div class="mt-1 text-xl font-bold">{{ formatCurrencySimple(totalDecaisse) }}</div>
                    </div>
                    <div class="rounded-lg px-4 py-3 bg-red-600 text-white shadow">
                        <div class="text-sm font-semibold">Total Impayé</div>
                        <div class="mt-1 text-xl font-bold">{{ formatCurrencySimple(totalImpaye) }}</div>
                    </div>
                </div>

                <!-- Export Buttons & Filters Row -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">État Règlement</label>
                        <select v-model="filterEtat" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs">
                            <option value="">Tous</option><option value="paye">Payé</option><option value="impaye">Impayé</option><option value="reporte">Reporté</option><option value="instance">Instance</option><option value="cour">En cour</option><option value="devalide">Dévalidé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">N° pièce</label>
                        <input type="text" v-model="filterNumeroPiece" placeholder="N° pièce..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Banque</label>
                        <input type="text" v-model="filterBanque" placeholder="Banque..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Montant</label>
                        <input type="number" v-model.number="filterMontant" placeholder="Montant..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs" />
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                            <input type="date" v-model="filterDateEncaissement" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs" />
                        </div>
                        <div class="flex gap-1 mt-5">
                            <button @click="exportToExcel" class="p-2 bg-green-600 text-white rounded-md hover:bg-green-700" title="Excel"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></button>
                            <button @click="exportToPDF" class="p-2 bg-red-600 text-white rounded-md hover:bg-red-700" title="PDF"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Content Section -->
        <div class="flex-1 overflow-hidden bg-gray-50 dark:bg-gray-900/40">
            <!-- List View Table -->
            <div v-if="!showForm" class="p-0 h-full">
                <div class="overflow-x-auto overflow-y-auto relative h-full">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10 shadow-sm border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fournisseur</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">N° pièce</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Banque</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date encaissement</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Chargement...
                            </td>
                        </tr>
                        <tr v-else-if="reglements.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Aucun règlement trouvé
                            </td>
                        </tr>
                        <tr v-else-if="filteredReglements.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Aucun règlement ne correspond aux filtres
                            </td>
                        </tr>
                        <tr v-else v-for="reglement in filteredReglements" :key="reglement.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">{{ reglement.code_reglement }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(reglement.date_reglement) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.fournisseur?.nom_fournisseur || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.type_reglement }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.numero_piece || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.banque || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(reglement.date_encaissement) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ formatCurrency(reglement.montant) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap relative">
                                <!-- Status Badge - Click to toggle dropdown -->
                                <span 
                                    @click.stop="toggleStatusDropdown(reglement.id)"
                                    :class="getStatusClass(reglement.statut)" 
                                    class="px-2 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-80 transition-opacity inline-flex items-center gap-1"
                                    title="Cliquer pour modifier le statut">
                                    <span>{{ getStatusText(reglement.statut) }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </span>
                                
                                <!-- Status Dropdown -->
                                <div 
                                    v-show="openStatusDropdownId === reglement.id"
                                    @click.away="openStatusDropdownId = null"
                                    class="absolute z-50 mt-1 left-0 w-40 rounded-lg shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 overflow-hidden"
                                    style="display: none;"
                                    :style="{ display: openStatusDropdownId === reglement.id ? 'block' : 'none' }">
                                    
                                    <!-- Payé Option -->
                                    <button 
                                        @click.stop="updateStatusDirect(reglement.id, 'paye')"
                                        class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="reglement.statut === 'paye' ? 'bg-green-50 dark:bg-green-900/30' : ''">
                                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                        <span class="text-green-700 dark:text-green-400 font-medium">Payé</span>
                                    </button>
                                    
                                    <!-- Impayé Option -->
                                    <button 
                                        @click.stop="updateStatusDirect(reglement.id, 'impaye')"
                                        class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="reglement.statut === 'impaye' ? 'bg-red-50 dark:bg-red-900/30' : ''">
                                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                        <span class="text-red-700 dark:text-red-400 font-medium">Impayé</span>
                                    </button>
                                    
                                    <!-- Reporté Option -->
                                    <button 
                                        @click.stop="updateStatusDirect(reglement.id, 'reporte')"
                                        class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="reglement.statut === 'reporte' ? 'bg-orange-50 dark:bg-orange-900/30' : ''">
                                        <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                                        <span class="text-orange-700 dark:text-orange-400 font-medium">Reporté</span>
                                    </button>
                                    
                                    <!-- Instance Option -->
                                    <button 
                                        @click.stop="updateStatusDirect(reglement.id, 'instance')"
                                        class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="reglement.statut === 'instance' ? 'bg-gray-50 dark:bg-gray-900/30' : ''">
                                        <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                                        <span class="text-gray-700 dark:text-gray-400 font-medium">Instance</span>
                                    </button>
                                    
                                    <!-- En cour Option -->
                                    <button 
                                        @click.stop="updateStatusDirect(reglement.id, 'cour')"
                                        class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="reglement.statut === 'cour' ? 'bg-blue-50 dark:bg-blue-900/30' : ''">
                                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                        <span class="text-blue-700 dark:text-blue-400 font-medium">En cour</span>
                                    </button>
                                    
                                    <!-- Dévalidé Option -->
                                    <button 
                                        @click.stop="updateStatusDirect(reglement.id, 'devalide')"
                                        class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="reglement.statut === 'devalide' ? 'bg-purple-50 dark:bg-purple-900/30' : ''">
                                        <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                                        <span class="text-purple-700 dark:text-purple-400 font-medium">Dévalidé</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <button @click="viewReglement(reglement)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Voir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button @click="printReglementPDF(reglement)" class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 transition-colors" title="Imprimer PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                    <button v-if="reglement.statut !== 'paye'" @click="editReglement(reglement)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition-colors" title="Modifier">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button v-if="reglement.statut === 'impaye'" @click="markAsPaid(reglement)" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 transition-colors" title="Marquer comme payé">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button v-if="reglement.statut !== 'paye'" @click="deleteReglement(reglement)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Form View (Scrollable) -->
            <div v-if="showForm" class="p-6 h-full overflow-y-auto">
            <!-- Header -->
            <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ formMode === 'create' ? 'Nouveau règlement fournisseur' : (formMode === 'edit' ? 'Modifier le règlement' : 'Détails du règlement') }}
                </h3>
            </div>

            <!-- Total TTC and Solde TTC Display -->
            <div v-if="form.fournisseur_id && bonsAchatFournisseur.length > 0" class="mt-4 mb-6 grid grid-cols-2 gap-4">
                <!-- Total TTC Box -->
                <div class="rounded-lg px-6 py-4 bg-red-600 text-white shadow-lg">
                    <div class="text-sm font-semibold opacity-90 mb-1">Total TTC</div>
                    <div class="text-3xl font-bold">{{ formatCurrencySimple(totalTTC) }}</div>
                </div>
                
                <!-- Solde TTC Box -->
                <div class="rounded-lg px-6 py-4 bg-yellow-500 text-white shadow-lg">
                    <div class="text-sm font-semibold opacity-90 mb-1">Solde TTC</div>
                    <div class="text-3xl font-bold">{{ formatCurrencySimple(soldeTTC) }}</div>
                </div>
            </div>

            <!-- État Règlement & État Remboursement - Side by Side -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- État Règlement Legend - Clickable -->
                <div class="bg-slate-800 dark:bg-slate-900 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-white font-semibold text-sm">État Règlement</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                        <button 
                            type="button"
                            @click="setStatut('instance')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.statut === 'instance' ? 'bg-gray-600 ring-2 ring-gray-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">Instance</span>
                            <span class="w-3 h-3 rounded-full bg-gray-400 border border-gray-300"></span>
                        </button>
                        <button 
                            type="button"
                            @click="setStatut('paye')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.statut === 'paye' ? 'bg-green-900/50 ring-2 ring-green-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">Payé</span>
                            <span class="w-3 h-3 rounded-full bg-green-500 border border-green-400"></span>
                        </button>
                        <button 
                            type="button"
                            @click="setStatut('reporte')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.statut === 'reporte' ? 'bg-orange-900/50 ring-2 ring-orange-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">Reporté</span>
                            <span class="w-3 h-3 rounded-full bg-orange-500 border border-orange-400"></span>
                        </button>
                        <button 
                            type="button"
                            @click="setStatut('cour')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.statut === 'cour' ? 'bg-blue-900/50 ring-2 ring-blue-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">En cour</span>
                            <span class="w-3 h-3 rounded-full bg-blue-500 border border-blue-400"></span>
                        </button>
                        <button 
                            type="button"
                            @click="setStatut('impaye')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.statut === 'impaye' ? 'bg-red-900/50 ring-2 ring-red-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">Impayé</span>
                            <span class="w-3 h-3 rounded-full bg-red-500 border border-red-400"></span>
                        </button>
                        <button 
                            type="button"
                            @click="setStatut('devalide')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.statut === 'devalide' ? 'bg-purple-900/50 ring-2 ring-purple-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">Dévalidé</span>
                            <span class="w-3 h-3 rounded-full bg-purple-500 border border-purple-400"></span>
                        </button>
                    </div>
                </div>

                <!-- État Remboursement Legend - Clickable -->
                <div class="bg-slate-800 dark:bg-slate-900 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-white font-semibold text-sm">État Remboursement</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                        <button 
                            type="button"
                            @click="setEtatRemboursement('devalide')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.etat_remboursement === 'devalide' ? 'bg-purple-900/50 ring-2 ring-purple-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">Remboursement Dévalidé</span>
                            <span class="w-3 h-3 rounded-full bg-purple-500 border border-purple-400"></span>
                        </button>
                        <button 
                            type="button"
                            @click="setEtatRemboursement('impaye')"
                            :disabled="formMode === 'view'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200"
                            :class="form.etat_remboursement === 'impaye' ? 'bg-red-900/50 ring-2 ring-red-400' : 'hover:bg-slate-700'"
                        >
                            <span class="text-gray-300 text-sm">Remboursement Impayé</span>
                            <span class="w-3 h-3 rounded-full bg-red-500 border border-red-400"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Détails du règlement -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300 mb-4">Détails du règlement</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Row 1 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date règlement</label>
                        <input 
                            type="date" 
                            v-model="form.date_reglement" 
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code règlement</label>
                        <input 
                            type="text" 
                            v-model="form.code_reglement" 
                            readonly
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code fournisseur</label>
                        <select 
                            v-model="form.fournisseur_id" 
                            @change="onFournisseurChange"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        >
                            <option value="">Sélectionner...</option>
                            <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.code_fournisseur }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom fournisseur</label>
                        <input 
                            type="text" 
                            :value="selectedFournisseurName" 
                            readonly
                            placeholder="Nom s'affichera ici"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>

                    <!-- Row 2 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type règlement</label>
                        <select 
                            v-model="form.type_reglement"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        >
                            <option value="Virement">Virement</option>
                            <option value="Chèque">Chèque</option>
                            <option value="Espèces">Espèces</option>
                            <option value="Traite">Traite</option>
                            <option value="A VUE">A VUE</option>
                            <option value="VERSEMENT">VERSEMENT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° pièce</label>
                        <input 
                            type="text" 
                            v-model="form.numero_piece"
                            :disabled="formMode === 'view'"
                            placeholder="N° chèque/virement"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banque</label>
                        <select 
                            v-model="form.banque"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        >
                            <option value="">Sélectionner banque</option>
                            <option v-for="banque in banques" :key="banque.id" :value="banque.nom">{{ banque.nom }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de tiré</label>
                        <input 
                            type="text" 
                            v-model="form.nom_beneficiaire"
                            :disabled="formMode === 'view'"
                            placeholder="Entrez le nom du bénéficiaire"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>

                    <!-- Row 3 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant règlement</label>
                        <input 
                            type="number" 
                            v-model.number="form.montant"
                            @input="distributePayment"
                            :disabled="formMode === 'view'"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date encaissement</label>
                        <input 
                            type="date" 
                            v-model="form.date_encaissement"
                            :disabled="formMode === 'view'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observation</label>
                        <input 
                            type="text" 
                            v-model="form.observation"
                            :disabled="formMode === 'view'"
                            placeholder="Remarques..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Bons d'achat impayés - Table -->
            <div v-if="form.fournisseur_id" class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">
                        Commandes en attente de paiement pour {{ selectedFournisseurName }}
                    </h4>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Sélectionnez les commandes et imputez le montant du règlement
                    </span>
                </div>

                <div v-if="loadingBonsAchat" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Chargement des bons d'achat...</p>
                </div>

                <div v-else-if="bonsAchatFournisseur.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p>Aucun bon d'achat impayé pour ce fournisseur</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">SÉLECTION</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">N° BON</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">DATE COMMANDE</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">CLIENT LIVRÉ</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">MONTANT COMMANDE</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">MONTANT PAYÉ</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">RESTE</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="bon in bonsAchatFournisseur" :key="bon.id" 
                                :class="{'bg-blue-50 dark:bg-blue-900/20': bon.selected}">
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="bon.selected"
                                        @change="onBonSelectionChange(bon)"
                                        :disabled="formMode === 'view'"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">
                                    {{ bon.numero_bon }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ formatDate(bon.date) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ bon.client_livre || '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrencySimple(bon.total_ttc) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold"
                                    :class="getMontantPayeClass(bon)">
                                    {{ formatCurrencySimple(getMontantPaye(bon)) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold"
                                    :class="getResteClass(bon)">
                                    {{ formatCurrencySimple(getReste(bon)) }}
                                </td>
                            </tr>
                        </tbody>
                        <!-- Footer with totals -->
                        <tfoot class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <td colspan="4"></td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    TOTAL IMPUTÉ
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatCurrencySimple(totalImpute) }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    RESTE À IMPUTER
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold"
                                    :class="resteAImputer > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400'">
                                    {{ formatCurrencySimple(resteAImputer) }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    DIFFÉRENCE
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold"
                                    :class="difference === 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    {{ formatCurrencySimple(difference) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- No fournisseur selected message -->
            <div v-else class="bg-gray-50 dark:bg-gray-900 rounded-lg p-8 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Sélectionnez un fournisseur</h4>
                <p class="text-gray-500 dark:text-gray-400">Les bons d'achat impayés du fournisseur s'afficheront ici</p>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const reglements = ref([])
const fournisseurs = ref([])
const bonsAchatFournisseur = ref([])
const banques = ref([])
const loading = ref(false)
const loadingBonsAchat = ref(false)
const showForm = ref(false)
const formMode = ref('create') // 'create', 'edit', 'view'
const editingReglementId = ref(null)

// Status dropdown state
const openStatusDropdownId = ref(null)

// List filters
const filterEtat = ref('')
const filterNumeroPiece = ref('')
const filterBanque = ref('')
const filterMontant = ref(null)
const filterDateEncaissement = ref('')

const form = ref({
    code_reglement: '',
    date_reglement: new Date().toISOString().split('T')[0],
    fournisseur_id: '',
    type_reglement: 'Virement',
    numero_piece: '',
    banque: '',
    nom_beneficiaire: '',
    montant: 0,
    date_encaissement: '',
    observation: '',
    statut: 'impaye',
    etat_remboursement: null,
    lignes: []
})

// Computed properties
const selectedFournisseurName = computed(() => {
    if (!form.value.fournisseur_id) return ''
    const fournisseur = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
    return fournisseur ? fournisseur.nom_fournisseur : ''
})

// Filtered list of règlements for display & export
const filteredReglements = computed(() => {
    let list = reglements.value

    if (filterEtat.value) {
        list = list.filter(r => r.statut === filterEtat.value)
    }

    if (filterNumeroPiece.value) {
        const query = filterNumeroPiece.value.toLowerCase()
        list = list.filter(r => (r.numero_piece || '').toLowerCase().includes(query))
    }

    if (filterBanque.value) {
        const query = filterBanque.value.toLowerCase()
        list = list.filter(r => (r.banque || '').toLowerCase().includes(query))
    }

    if (filterMontant.value != null && filterMontant.value !== '') {
        const montant = parseFloat(filterMontant.value)
        if (!isNaN(montant)) {
            list = list.filter(r => parseFloat(r.montant) === montant)
        }
    }

    if (filterDateEncaissement.value) {
        list = list.filter(r => (r.date_encaissement || '').startsWith(filterDateEncaissement.value))
    }

    return list
})

// Totals for stat blocks
const totalReglements = computed(() => {
    return reglements.value.reduce((sum, r) => sum + (parseFloat(r.montant) || 0), 0)
})

const totalDecaisse = computed(() => {
    return reglements.value
        .filter(r => r.statut === 'paye')
        .reduce((sum, r) => sum + (parseFloat(r.montant) || 0), 0)
})

const totalImpaye = computed(() => {
    return reglements.value
        .filter(r => r.statut === 'impaye')
        .reduce((sum, r) => sum + (parseFloat(r.montant) || 0), 0)
})

const totalImpute = computed(() => {
    return bonsAchatFournisseur.value
        .filter(bon => bon.selected)
        .reduce((sum, bon) => sum + (parseFloat(bon.montant_a_imputer) || 0), 0)
})

const resteAImputer = computed(() => {
    return (parseFloat(form.value.montant) || 0) - totalImpute.value
})

const difference = computed(() => {
    // Difference between what should be distributed and what is
    return Math.abs(resteAImputer.value)
})

// Total TTC of all bons d'achat for the selected fournisseur
const totalTTC = computed(() => {
    return bonsAchatFournisseur.value.reduce((sum, bon) => sum + (parseFloat(bon.total_ttc) || 0), 0)
})

// Solde TTC (remaining amount to pay) for the selected fournisseur
const soldeTTC = computed(() => {
    return bonsAchatFournisseur.value.reduce((sum, bon) => sum + (parseFloat(bon.solde_restant) || 0), 0)
})

// Load data functions
const loadReglements = async () => {
    loading.value = true
    try {
        const response = await fetch('/api/reglements-fournisseurs')
        if (response.ok) {
            reglements.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error)
    } finally {
        loading.value = false
    }
}

const loadFournisseurs = async () => {
    try {
        const response = await fetch('/api/fournisseurs')
        if (response.ok) {
            fournisseurs.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement des fournisseurs:', error)
    }
}

const loadBanques = async () => {
    try {
        const response = await fetch('/api/settings/banques')
        if (response.ok) {
            const data = await response.json()
            banques.value = data.banques || []
        }
    } catch (error) {
        console.error('Erreur lors du chargement des banques:', error)
    }
}

const loadBonsAchatFournisseur = async (fournisseurId, excludeReglementId = null) => {
    if (!fournisseurId) {
        bonsAchatFournisseur.value = []
        return
    }
    
    loadingBonsAchat.value = true
    try {
        let url = `/api/reglements-fournisseurs/bons-achat/${fournisseurId}`
        const queryParams = []
        
        if (excludeReglementId) {
            queryParams.push(`exclude_reglement_id=${excludeReglementId}`)
        }
        
        if (form.value.etat_remboursement) {
            queryParams.push(`etat_remboursement=${form.value.etat_remboursement}`)
        }
        
        if (queryParams.length > 0) {
            url += `?${queryParams.join('&')}`
        }
        
        const response = await fetch(url)
        if (response.ok) {
            const data = await response.json()
            // Add selection and imputation fields
            // When filtering by remboursement status:
            //   - Show bons linked to règlements with that statut
            //   - BUT exclude bons that are already fully paid (solde_restant <= 0)
            // Otherwise, only show unpaid bons
            bonsAchatFournisseur.value = data
                .filter(bon => {
                    if (form.value.etat_remboursement) {
                        // When filtering by remboursement status:
                        // Show ONLY bons linked to règlements with that statut
                        // AND that still have a remaining balance to pay
                        return bon.linked_to_remboursement_statut === true && bon.solde_restant > 0
                    } else {
                        // Default: only show unpaid bons
                        return bon.solde_restant > 0
                    }
                })
                .map(bon => ({
                    ...bon,
                    selected: false,
                    montant_a_imputer: 0
                }))
        }
    } catch (error) {
        console.error('Erreur lors du chargement des bons d\'achat:', error)
    } finally {
        loadingBonsAchat.value = false
    }
}

const getNextCode = async () => {
    try {
        const response = await fetch('/api/reglements-fournisseurs/next-code')
        if (response.ok) {
            const data = await response.json()
            form.value.code_reglement = data.code_reglement
        }
    } catch (error) {
        console.error('Erreur lors de la génération du code:', error)
    }
}

// Form functions
const openCreateForm = () => {
    resetForm()
    getNextCode()
    formMode.value = 'create'
    showForm.value = true
}

const resetForm = () => {
    form.value = {
        code_reglement: '',
        date_reglement: new Date().toISOString().split('T')[0],
        fournisseur_id: '',
        type_reglement: 'Virement',
        numero_piece: '',
        banque: '',
        nom_beneficiaire: '',
        montant: 0,
        date_encaissement: '',
        observation: '',
        statut: 'impaye',
        etat_remboursement: null,
        lignes: []
    }
    bonsAchatFournisseur.value = []
    editingReglementId.value = null
}

// Set the status of the reglement
const setStatut = (statut) => {
    if (formMode.value !== 'view') {
        form.value.statut = statut
    }
}

// Set the remboursement status and reload bons d'achat
const setEtatRemboursement = (etat) => {
    if (formMode.value !== 'view') {
        // Toggle: if already selected, unselect; otherwise select
        form.value.etat_remboursement = form.value.etat_remboursement === etat ? null : etat
        // Reload bons d'achat with the new filter
        if (form.value.fournisseur_id) {
            loadBonsAchatFournisseur(form.value.fournisseur_id, editingReglementId.value)
        }
    }
}

const cancelForm = () => {
    showForm.value = false
    resetForm()
}

const onFournisseurChange = () => {
    if (form.value.fournisseur_id) {
        // Auto-fill nom_beneficiaire
        const fournisseur = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
        if (fournisseur) {
            form.value.nom_beneficiaire = fournisseur.nom_fournisseur
        }
        loadBonsAchatFournisseur(form.value.fournisseur_id)
    } else {
        bonsAchatFournisseur.value = []
    }
}

const onBonSelectionChange = (bon) => {
    if (!bon.selected) {
        bon.montant_a_imputer = 0
    } else {
        // Auto-distribute remaining payment to this bon
        distributePayment()
    }
}

const onMontantImputerChange = (bon) => {
    // Ensure montant_a_imputer doesn't exceed solde_restant
    if (bon.montant_a_imputer > bon.solde_restant) {
        bon.montant_a_imputer = bon.solde_restant
    }
    if (bon.montant_a_imputer < 0) {
        bon.montant_a_imputer = 0
    }
}

// Compute MONTANT PAYÉ dynamically (original paid + current imputation)
const getMontantPaye = (bon) => {
    const originalMontantRegle = parseFloat(bon.montant_regle) || 0
    const currentImputation = bon.selected ? (parseFloat(bon.montant_a_imputer) || 0) : 0
    return originalMontantRegle + currentImputation
}

// Compute RESTE dynamically (solde_restant - current imputation)
const getReste = (bon) => {
    const soldeRestant = parseFloat(bon.solde_restant) || 0
    const currentImputation = bon.selected ? (parseFloat(bon.montant_a_imputer) || 0) : 0
    return soldeRestant - currentImputation
}

// Get class for MONTANT PAYÉ column
const getMontantPayeClass = (bon) => {
    const montantPaye = getMontantPaye(bon)
    const totalTtc = parseFloat(bon.total_ttc) || 0
    
    if (montantPaye >= totalTtc && totalTtc > 0) {
        return 'text-green-600 dark:text-green-400'
    } else if (montantPaye > 0) {
        return 'text-blue-600 dark:text-blue-400'
    }
    return 'text-gray-600 dark:text-gray-400'
}

// Get class for RESTE column
const getResteClass = (bon) => {
    const reste = getReste(bon)
    if (reste <= 0) {
        return 'text-green-600 dark:text-green-400'
    }
    return 'text-red-600 dark:text-red-400'
}

const distributePayment = () => {
    // Get the payment amount
    let remainingAmount = parseFloat(form.value.montant) || 0
    
    // Distribute to selected bons in order
    const selectedBons = bonsAchatFournisseur.value.filter(bon => bon.selected)
    
    for (const bon of selectedBons) {
        if (remainingAmount <= 0) {
            bon.montant_a_imputer = 0
        } else {
            const amountForThisBon = Math.min(remainingAmount, bon.solde_restant)
            bon.montant_a_imputer = amountForThisBon
            remainingAmount -= amountForThisBon
        }
    }
}

// Status dropdown functions
const toggleStatusDropdown = (reglementId) => {
    if (openStatusDropdownId.value === reglementId) {
        openStatusDropdownId.value = null
    } else {
        openStatusDropdownId.value = reglementId
    }
}

const updateStatusDirect = async (reglementId, newStatus) => {
    try {
        const response = await fetch(`/api/reglements-fournisseurs/${reglementId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                statut: newStatus
            })
        })
        
        const data = await response.json()
        
        if (response.ok) {
            // Update the status in the local list
            const reglement = reglements.value.find(r => r.id === reglementId)
            if (reglement) {
                reglement.statut = newStatus
            }
            openStatusDropdownId.value = null
        } else {
            alert('Erreur: ' + (data.error || data.message || 'Erreur lors de la mise à jour'))
        }
    } catch (error) {
        console.error('Erreur lors de la mise à jour du statut:', error)
        alert('Erreur: ' + error.message)
    }
}

// CRUD operations
const saveReglement = async () => {
    // Validation
    if (!form.value.fournisseur_id) {
        alert('Veuillez sélectionner un fournisseur')
        return
    }
    
    if (!form.value.montant || form.value.montant <= 0) {
        alert('Veuillez saisir un montant valide')
        return
    }
    
    // Prepare lignes from selected bons
    const lignes = bonsAchatFournisseur.value
        .filter(bon => bon.selected && bon.montant_a_imputer > 0)
        .map(bon => ({
            bon_achat_id: bon.id,
            montant_regle: bon.montant_a_imputer
        }))
    
    // Check if total imputé matches montant
    if (totalImpute.value > form.value.montant) {
        alert('Le total imputé ne peut pas dépasser le montant du règlement')
        return
    }
    
    try {
        const url = formMode.value === 'edit' 
            ? `/api/reglements-fournisseurs/${editingReglementId.value}`
            : '/api/reglements-fournisseurs'
        
        const method = formMode.value === 'edit' ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                ...form.value,
                lignes
            })
        })
        
        if (response.ok) {
            alert(formMode.value === 'edit' ? 'Règlement modifié avec succès' : 'Règlement créé avec succès')
            showForm.value = false
            loadReglements()
            resetForm()
        } else {
            const error = await response.json()
            alert('Erreur: ' + (error.message || 'Une erreur est survenue'))
        }
    } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur lors de l\'enregistrement')
    }
}

const viewReglement = async (reglement) => {
    try {
        const response = await fetch(`/api/reglements-fournisseurs/${reglement.id}`)
        if (response.ok) {
            const data = await response.json()
            // Helper to format date for date input (extract YYYY-MM-DD from datetime string)
            const formatDateForInput = (dateStr) => {
                if (!dateStr) return ''
                // Handle both "2025-12-17" and "2025-12-17T00:00:00.000Z" formats
                return dateStr.split('T')[0]
            }
            form.value = {
                code_reglement: data.code_reglement,
                date_reglement: formatDateForInput(data.date_reglement),
                fournisseur_id: data.fournisseur_id,
                type_reglement: data.type_reglement,
                numero_piece: data.numero_piece || '',
                banque: data.banque || '',
                nom_beneficiaire: data.nom_beneficiaire || '',
                montant: parseFloat(data.montant),
                date_encaissement: formatDateForInput(data.date_encaissement),
                observation: data.observation || '',
                statut: data.statut || 'impaye',
                etat_remboursement: data.etat_remboursement || null,
                lignes: data.lignes?.map(l => ({
                    bon_achat_id: l.bon_achat_id,
                    montant_regle: parseFloat(l.montant_regle)
                })) || []
            }
            editingReglementId.value = data.id
            
            // Load bons d'achat for this fournisseur (exclude current reglement to get correct remaining amounts)
            await loadBonsAchatFournisseur(data.fournisseur_id, data.id)
            
            // Mark bons that were part of this reglement and add any bons that were paid
            if (data.lignes) {
                for (const ligne of data.lignes) {
                    let bon = bonsAchatFournisseur.value.find(b => b.id === ligne.bon_achat_id)
                    
                    // If the bon is not in the list (fully paid by this reglement), we need to add it
                    if (!bon && ligne.bon_achat) {
                        bon = {
                            ...ligne.bon_achat,
                            montant_regle: parseFloat(ligne.bon_achat.montant_regle || 0),
                            solde_restant: parseFloat(ligne.montant_regle), // The amount from this reglement becomes available again
                            selected: false,
                            montant_a_imputer: 0
                        }
                        bonsAchatFournisseur.value.push(bon)
                    }
                    
                    if (bon) {
                        bon.selected = true
                        bon.montant_a_imputer = parseFloat(ligne.montant_regle)
                        // Add back the amount from this reglement to solde_restant for display
                        bon.solde_restant = (parseFloat(bon.solde_restant) || 0) + parseFloat(ligne.montant_regle)
                    }
                }
            }
            
            // Sort bons by date
            bonsAchatFournisseur.value.sort((a, b) => new Date(a.date) - new Date(b.date))
            
            formMode.value = 'view'
            showForm.value = true
        }
    } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur lors du chargement')
    }
}

const editReglement = async (reglement) => {
    await viewReglement(reglement)
    formMode.value = 'edit'
}

const markAsPaid = async (reglement) => {
    if (confirm('Voulez-vous marquer ce règlement comme payé ?')) {
        try {
            const response = await fetch(`/api/reglements-fournisseurs/${reglement.id}/mark-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            
            if (response.ok) {
                alert('Règlement marqué comme payé avec succès')
                loadReglements()
            } else {
                const error = await response.json()
                alert('Erreur: ' + (error.message || 'Une erreur est survenue'))
            }
        } catch (error) {
            console.error('Erreur:', error)
            alert('Erreur lors de la mise à jour du statut')
        }
    }
}

const deleteReglement = async (reglement) => {
    if (confirm('Voulez-vous vraiment supprimer ce règlement ?')) {
        try {
            const response = await fetch(`/api/reglements-fournisseurs/${reglement.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            
            if (response.ok) {
                alert('Règlement supprimé avec succès')
                loadReglements()
            } else {
                const error = await response.json()
                alert('Erreur: ' + (error.message || 'Une erreur est survenue'))
            }
        } catch (error) {
            console.error('Erreur:', error)
            alert('Erreur lors de la suppression')
        }
    }
}

// Formatting functions
const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD'
    }).format(value || 0)
}

const formatCurrencySimple = (value) => {
    return new Intl.NumberFormat('fr-MA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0) + ' MAD'
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

const getStatusClass = (statut) => {
    switch (statut) {
        case 'paye':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        case 'impaye':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        case 'reporte':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
        case 'instance':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
        case 'cour':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
        case 'devalide':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    }
}

const getStatusText = (statut) => {
    switch (statut) {
        case 'paye':
            return 'Payé'
        case 'impaye':
            return 'Impayé'
        case 'reporte':
            return 'Reporté'
        case 'instance':
            return 'Instance'
        case 'cour':
            return 'En cour'
        case 'devalide':
            return 'Dévalidé'
        default:
            return statut || 'N/A'
    }
}

// Export functions
const exportToExcel = () => {
    if (filteredReglements.value.length === 0) {
        alert('Aucune donnée à exporter')
        return
    }
    
    const data = filteredReglements.value.map(reg => ({
        'Code': reg.code_reglement,
        'Date': formatDate(reg.date_reglement),
        'Fournisseur': reg.fournisseur?.nom_fournisseur || 'N/A',
        'Type': reg.type_reglement,
        'N° Pièce': reg.numero_piece || '-',
        'Banque': reg.banque || '-',
        'Montant': reg.montant,
        'Statut': getStatusText(reg.statut)
    }))
    
    const headers = Object.keys(data[0])
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => {
            const value = row[header]
            return typeof value === 'string' && value.includes(',') 
                ? `"${value.replace(/"/g, '""')}"` 
                : value
        }).join(','))
    ].join('\n')
    
    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `reglements-fournisseurs-${new Date().toISOString().split('T')[0]}.csv`
    link.click()
}

const exportToPDF = () => {
    if (filteredReglements.value.length === 0) {
        alert('Aucune donnée à exporter')
        return
    }
    
    const printWindow = window.open('', '_blank')
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Règlements Fournisseurs</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { text-align: center; color: #333; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
                th { background-color: #2563eb; color: white; }
                tr:nth-child(even) { background-color: #f2f2f2; }
                .status-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
                .status-paye { background-color: #d4edda; color: #155724; }
                .status-impaye { background-color: #f8d7da; color: #721c24; }
                .status-reporte { background-color: #ffe4c4; color: #8b4513; }
                @media print { button { display: none; } }
            </style>
        </head>
        <body>
            <h1>Liste des Règlements Fournisseurs</h1>
            <p>Date d'export: ${new Date().toLocaleDateString('fr-FR')}</p>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Fournisseur</th>
                        <th>Type</th>
                        <th>N° Pièce</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredReglements.value.map(reg => `
                        <tr>
                            <td>${reg.code_reglement}</td>
                            <td>${formatDate(reg.date_reglement)}</td>
                            <td>${reg.fournisseur?.nom_fournisseur || 'N/A'}</td>
                            <td>${reg.type_reglement}</td>
                            <td>${reg.numero_piece || '-'}</td>
                            <td>${formatCurrency(reg.montant)}</td>
                            <td><span class="status-badge status-${reg.statut}">${getStatusText(reg.statut)}</span></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            <br>
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #2563eb; color: white; border: none; cursor: pointer; border-radius: 4px;">
                Imprimer / Sauvegarder en PDF
            </button>
        </body>
        </html>
    `
    
    printWindow.document.write(htmlContent)
    printWindow.document.close()
}

const printReglementPDF = (reglement) => {
    const printWindow = window.open('', '_blank')
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Règlement ${reglement.code_reglement}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 40px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    margin-bottom: 40px;
                    border-bottom: 3px solid #2563eb;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #2563eb;
                    margin: 0 0 10px 0;
                    font-size: 28px;
                }
                .header .code {
                    font-size: 18px;
                    color: #666;
                }
                .info-section {
                    margin-bottom: 30px;
                }
                .info-section h2 {
                    font-size: 16px;
                    color: #2563eb;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 8px;
                    margin-bottom: 15px;
                }
                .info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 15px;
                }
                .info-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px;
                    background-color: #f8f9fa;
                    border-radius: 4px;
                }
                .info-item .label {
                    font-weight: bold;
                    color: #555;
                }
                .info-item .value {
                    color: #333;
                }
                .amount-section {
                    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                    color: white;
                    padding: 25px;
                    border-radius: 8px;
                    text-align: center;
                    margin: 30px 0;
                }
                .amount-section .label {
                    font-size: 14px;
                    opacity: 0.9;
                    margin-bottom: 5px;
                }
                .amount-section .amount {
                    font-size: 32px;
                    font-weight: bold;
                }
                .status-badge {
                    display: inline-block;
                    padding: 6px 16px;
                    border-radius: 20px;
                    font-weight: bold;
                    font-size: 14px;
                }
                .status-paye { background-color: #d4edda; color: #155724; }
                .status-impaye { background-color: #f8d7da; color: #721c24; }
                .status-reporte { background-color: #ffe4c4; color: #8b4513; }
                .footer {
                    margin-top: 50px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    display: flex;
                    justify-content: space-between;
                    font-size: 12px;
                    color: #666;
                }
                .signature-section {
                    margin-top: 60px;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-box {
                    width: 200px;
                    text-align: center;
                }
                .signature-box .line {
                    border-top: 1px solid #333;
                    margin-top: 60px;
                    padding-top: 10px;
                }
                @media print { 
                    button { display: none; } 
                    body { padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>RÈGLEMENT FOURNISSEUR</h1>
                <div class="code">${reglement.code_reglement}</div>
            </div>
            
            <div class="info-section">
                <h2>Informations du règlement</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Date de règlement</span>
                        <span class="value">${formatDate(reglement.date_reglement)}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Statut</span>
                        <span class="value"><span class="status-badge status-${reglement.statut}">${getStatusText(reglement.statut)}</span></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Type de règlement</span>
                        <span class="value">${reglement.type_reglement}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">N° Pièce</span>
                        <span class="value">${reglement.numero_piece || '-'}</span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h2>Informations fournisseur</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Fournisseur</span>
                        <span class="value">${reglement.fournisseur?.nom_fournisseur || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Code fournisseur</span>
                        <span class="value">${reglement.fournisseur?.code_fournisseur || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Banque</span>
                        <span class="value">${reglement.banque || '-'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Bénéficiaire</span>
                        <span class="value">${reglement.nom_beneficiaire || '-'}</span>
                    </div>
                </div>
            </div>
            
            <div class="amount-section">
                <div class="label">MONTANT DU RÈGLEMENT</div>
                <div class="amount">${formatCurrency(reglement.montant)}</div>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="line">Signature émetteur</div>
                </div>
                <div class="signature-box">
                    <div class="line">Signature bénéficiaire</div>
                </div>
            </div>
            
            <div class="footer">
                <span>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</span>
                <span>Bouyahya - Système de Gestion</span>
            </div>
            
            <br><br>
            <button onclick="window.print()" style="padding: 12px 24px; background-color: #2563eb; color: white; border: none; cursor: pointer; border-radius: 6px; font-size: 14px;">
                Imprimer / Sauvegarder en PDF
            </button>
        </body>
        </html>
    `
    
    printWindow.document.write(htmlContent)
    printWindow.document.close()
}

// Initialize
onMounted(() => {
    loadReglements()
    loadFournisseurs()
    loadBanques()
})
</script>
