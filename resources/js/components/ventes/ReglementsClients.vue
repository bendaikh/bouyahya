<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">
                    consultation règlements des
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ showForm ? (formMode === 'create' ? 'Nouveau règlement client' : 'Détails du règlement') : 'Liste des règlements clients' }}
                </p>
            </div>

            <!-- Global actions (form + list) -->
            <div class="flex flex-wrap justify-end gap-2">
                <!-- List view: create button -->
                <button 
                    v-if="!showForm"
                    @click="openCreateForm" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center text-sm"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau règlement
                </button>

                <!-- Form view: main actions -->
                <template v-else>
                    <button 
                        @click="cancelForm" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Quitter
                    </button>
                    <button 
                        v-if="formMode !== 'view'"
                        @click="saveReglement" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Valider
                    </button>
                    <button 
                        v-if="formMode === 'view' && editingReglementId"
                        @click="formMode = 'edit'" 
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Modifier
                    </button>
                </template>
            </div>
        </div>

        <!-- List View -->
        <div v-if="!showForm">
            <!-- Stat blocks -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-lg px-4 py-3 bg-blue-600 text-white shadow">
                    <div class="text-sm font-semibold">Total Règlements Clients</div>
                    <div class="mt-1 text-xl font-bold">
                        {{ formatCurrencySimple(totalReglements) }}
                    </div>
                </div>
                <div class="rounded-lg px-4 py-3 bg-green-600 text-white shadow">
                    <div class="text-sm font-semibold">Total Encaissé</div>
                    <div class="mt-1 text-xl font-bold">
                        {{ formatCurrencySimple(totalEncaisse) }}
                    </div>
                </div>
                <div class="rounded-lg px-4 py-3 bg-red-600 text-white shadow">
                    <div class="text-sm font-semibold">Total Impayé</div>
                    <div class="mt-1 text-xl font-bold">
                        {{ formatCurrencySimple(totalImpaye) }}
                    </div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="mb-4 flex justify-end space-x-2">
                <button 
                    @click="exportToExcel" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exporter Excel
                </button>
                <button 
                    @click="exportToPDF" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Exporter PDF
                </button>
            </div>

            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">État Règlement</label>
                    <select 
                        v-model="filterEtat"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"
                    >
                        <option value="">Tous</option>
                        <option value="paye">Payé</option>
                        <option value="impaye">Impayé</option>
                        <option value="reporte">Reporté</option>
                        <option value="instance">Instance</option>
                        <option value="cour">En cour</option>
                        <option value="devalide">Dévalidé</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">N° pièce</label>
                    <input 
                        type="text"
                        v-model="filterNumeroPiece"
                        placeholder="Filtrer par N° pièce..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Banque</label>
                    <input 
                        type="text"
                        v-model="filterBanque"
                        placeholder="Filtrer par banque..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Montant</label>
                    <input 
                        type="number"
                        v-model.number="filterMontant"
                        placeholder="Filtrer par montant..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date encaissement</label>
                    <input 
                        type="date"
                        v-model="filterDateEncaissement"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"
                    />
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N° pièce</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Banque</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date encaissement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="10" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Chargement...
                            </td>
                        </tr>
                        <tr v-else-if="reglements.length === 0">
                            <td colspan="10" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Aucun règlement trouvé
                            </td>
                        </tr>
                        <tr v-else-if="filteredReglements.length === 0">
                            <td colspan="10" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Aucun règlement ne correspond aux filtres
                            </td>
                        </tr>
                        <tr v-else v-for="reglement in filteredReglements" :key="reglement.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">{{ reglement.code_reglement }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(reglement.date_reglement) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.client?.raison_sociale || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.type_reglement }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.numero_piece || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ reglement.banque || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(reglement.date_encaissement) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ formatCurrency(reglement.montant) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(reglement.statut)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ getStatusText(reglement.statut) }}
                                </span>
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
                                    <button @click="editReglement(reglement)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition-colors" title="Modifier">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button v-if="reglement.statut === 'impaye'" @click="markAsPaid(reglement)" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 transition-colors" title="Marquer comme payé">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteReglement(reglement)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer">
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

        <!-- Form View -->
        <div v-if="showForm" class="space-y-6">
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
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code / Nom Client</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                v-model="clientSearch"
                                @focus="showClientDropdown = true"
                                @input="showClientDropdown = true"
                                @blur="closeClientDropdown"
                                :disabled="formMode === 'view'"
                                placeholder="Rechercher par code ou nom..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                            />
                            <!-- Dropdown -->
                            <div 
                                v-if="showClientDropdown && filteredClients.length > 0"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto"
                            >
                                <div 
                                    v-for="c in filteredClients" 
                                    :key="c.id"
                                    @click="selectClient(c)"
                                    class="px-3 py-2 hover:bg-blue-100 dark:hover:bg-blue-900 cursor-pointer text-sm"
                                >
                                    <span class="font-medium text-blue-600 dark:text-blue-400">{{ c.code_client }}</span>
                                    <span class="text-gray-600 dark:text-gray-300"> - {{ c.raison_sociale }}</span>
                                </div>
                            </div>
                            <!-- No results -->
                            <div 
                                v-if="showClientDropdown && clientSearch && filteredClients.length === 0"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg p-3 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Aucun client trouvé
                            </div>
                        </div>
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
                            <option value="Attijariwafa Bank">Attijariwafa Bank</option>
                            <option value="BMCE Bank">BMCE Bank</option>
                            <option value="Banque Populaire">Banque Populaire</option>
                            <option value="BMCI">BMCI</option>
                            <option value="Société Générale">Société Générale</option>
                            <option value="CIH Bank">CIH Bank</option>
                            <option value="Crédit du Maroc">Crédit du Maroc</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de tiré</label>
                        <input 
                            type="text" 
                            v-model="form.nom_tire"
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

                    <!-- Trésorerie (full width) -->
                    <div class="md:col-span-4">
                        <div class="flex items-center gap-4">
                            <span class="text-lg font-bold text-gray-800 dark:text-white">Trésorerie</span>
                            <select 
                                v-model="form.tresorerie_id"
                                :disabled="formMode === 'view'"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                            >
                                <option value="">Sélectionner banque</option>
                                <option v-for="t in tresoreries" :key="t.id" :value="t.id">{{ t.libelle }} ({{ t.code }})</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-column layout: Bons de livraison + Fiche Règlements -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left: Bons de livraison impayés -->
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden">
                    <div class="bg-green-600 text-white px-4 py-2 font-semibold text-sm">
                        <div class="grid grid-cols-6 gap-2 text-center">
                            <span>Sélectin</span>
                            <span>N° de Bon</span>
                            <span>Date</span>
                            <span>Montant TTC</span>
                            <span>Montant Payé</span>
                            <span>Reste</span>
                        </div>
                    </div>
                    
                    <div v-if="!form.client_id" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p>Sélectionnez un client pour voir les bons de livraison</p>
                    </div>
                    
                    <div v-else-if="loadingBonsLivraison" class="p-8 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Chargement...</p>
                    </div>
                    
                    <div v-else-if="bonsLivraisonClient.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p>Aucun bon de livraison impayé pour ce client</p>
                    </div>
                    
                    <div v-else class="max-h-64 overflow-y-auto">
                        <div v-for="bon in bonsLivraisonClient" :key="bon.id" 
                            class="grid grid-cols-6 gap-2 px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-sm items-center"
                            :class="{'bg-blue-50 dark:bg-blue-900/20': bon.selected}"
                        >
                            <div class="text-center">
                                <input 
                                    type="checkbox" 
                                    v-model="bon.selected"
                                    @change="onBonSelectionChange(bon)"
                                    :disabled="formMode === 'view'"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                            </div>
                            <div class="text-center text-blue-600 dark:text-blue-400 font-medium">{{ bon.numero_bon }}</div>
                            <div class="text-center text-gray-900 dark:text-white">{{ formatDate(bon.date) }}</div>
                            <div class="text-center text-gray-900 dark:text-white font-medium">{{ formatCurrencySimple(bon.total_general) }}</div>
                            <div class="text-center" :class="getMontantPayeClass(bon)">{{ formatCurrencySimple(getMontantPaye(bon)) }}</div>
                            <div class="text-center" :class="getResteClass(bon)">{{ formatCurrencySimple(getReste(bon)) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Right: Fiche Règlements -->
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden flex flex-col">
                    <div class="bg-green-600 text-white px-4 py-2 font-semibold text-sm">
                        Fiche Règlements
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="min-w-full">
                            <thead class="bg-green-600 text-white text-xs">
                                <tr>
                                    <th class="px-2 py-2 text-left">N° Rég</th>
                                    <th class="px-2 py-2 text-left">Type</th>
                                    <th class="px-2 py-2 text-left">N°</th>
                                    <th class="px-2 py-2 text-left">Bnq</th>
                                    <th class="px-2 py-2 text-left">Nom de Tiré</th>
                                    <th class="px-2 py-2 text-right">Montant</th>
                                    <th class="px-2 py-2 text-left">Échéance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <tr v-if="ficheReglements.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Aucun règlement
                                    </td>
                                </tr>
                                <tr 
                                    v-else 
                                    v-for="fiche in ficheReglements" 
                                    :key="fiche.id"
                                    @click="selectFicheReglement(fiche)"
                                    class="cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    :class="{'bg-blue-100 dark:bg-blue-900/40': selectedFicheReglement && selectedFicheReglement.id === fiche.id}"
                                >
                                    <td class="px-2 py-2 text-blue-600 dark:text-blue-400">{{ fiche.code_reglement }}</td>
                                    <td class="px-2 py-2 text-gray-900 dark:text-white">{{ fiche.type_reglement }}</td>
                                    <td class="px-2 py-2 text-gray-900 dark:text-white">{{ fiche.numero_piece || '-' }}</td>
                                    <td class="px-2 py-2 text-gray-900 dark:text-white">{{ fiche.banque || '-' }}</td>
                                    <td class="px-2 py-2 text-gray-900 dark:text-white">{{ fiche.nom_tire || '-' }}</td>
                                    <td class="px-2 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ formatCurrencySimple(fiche.montant) }}</td>
                                    <td class="px-2 py-2 text-gray-900 dark:text-white">{{ formatDate(fiche.date_encaissement) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Action buttons - outside table -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                        <button 
                            @click="viewSelectedFiche" 
                            :disabled="!selectedFicheReglement"
                            class="px-3 py-1.5 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Afficher
                        </button>
                        <button 
                            @click="printSelectedFiche" 
                            :disabled="!selectedFicheReglement"
                            class="px-3 py-1.5 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Imprimer
                        </button>
                        <button 
                            @click="printSelectedFichePDF" 
                            :disabled="!selectedFicheReglement"
                            class="px-3 py-1.5 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const reglements = ref([])
const clients = ref([])
const tresoreries = ref([])
const bonsLivraisonClient = ref([])
const loading = ref(false)
const loadingBonsLivraison = ref(false)
const showForm = ref(false)
const formMode = ref('create')
const editingReglementId = ref(null)

// Client search
const clientSearch = ref('')
const showClientDropdown = ref(false)
const selectedFicheReglement = ref(null)

// List filters
const filterEtat = ref('')
const filterNumeroPiece = ref('')
const filterBanque = ref('')
const filterMontant = ref(null)
const filterDateEncaissement = ref('')

const form = ref({
    code_reglement: '',
    date_reglement: new Date().toISOString().split('T')[0],
    client_id: '',
    type_reglement: 'Virement',
    numero_piece: '',
    banque: '',
    nom_tire: '',
    tresorerie_id: '',
    montant: 0,
    date_encaissement: '',
    observation: '',
    statut: 'impaye',
    etat_remboursement: null,
    lignes: []
})

// Computed properties
const selectedClientName = computed(() => {
    if (!form.value.client_id) return ''
    const clientId = parseInt(form.value.client_id)
    const client = clients.value.find(c => c.id === clientId)
    return client ? (client.raison_sociale || '') : ''
})

// Filtered clients for searchable dropdown
const filteredClients = computed(() => {
    if (!clientSearch.value) return clients.value
    const search = clientSearch.value.toLowerCase()
    return clients.value.filter(c => 
        c.code_client.toLowerCase().includes(search) || 
        (c.raison_sociale || '').toLowerCase().includes(search)
    )
})

// Fiche Règlements for the selected client
const ficheReglements = computed(() => {
    if (!form.value.client_id) return []
    return reglements.value.filter(r => r.client_id === form.value.client_id)
})

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
            list = list.filter(r => parseFloat(r.montant) >= montant)
        }
    }

    if (filterDateEncaissement.value) {
        list = list.filter(r => (r.date_encaissement || '').startsWith(filterDateEncaissement.value))
    }

    return list
})

const totalReglements = computed(() => {
    return reglements.value.reduce((sum, r) => sum + (parseFloat(r.montant) || 0), 0)
})

const totalEncaisse = computed(() => {
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
    return bonsLivraisonClient.value
        .filter(bon => bon.selected)
        .reduce((sum, bon) => sum + (parseFloat(bon.montant_a_imputer) || 0), 0)
})

// Load data functions
const loadReglements = async () => {
    loading.value = true
    try {
        const response = await fetch('/api/reglements-clients')
        if (response.ok) {
            reglements.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error)
    } finally {
        loading.value = false
    }
}

const loadClients = async () => {
    try {
        const response = await fetch('/api/clients')
        if (response.ok) {
            clients.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement des clients:', error)
    }
}

const loadTresoreries = async () => {
    try {
        const response = await fetch('/api/reglements-clients/tresoreries')
        if (response.ok) {
            tresoreries.value = await response.json()
        }
    } catch (error) {
        console.error('Erreur lors du chargement des trésoreries:', error)
    }
}

const loadBonsLivraisonClient = async (clientId, excludeReglementId = null) => {
    if (!clientId) {
        bonsLivraisonClient.value = []
        return
    }
    
    loadingBonsLivraison.value = true
    try {
        let url = `/api/reglements-clients/bons-livraison/${clientId}`
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
            bonsLivraisonClient.value = data
                .filter(bon => {
                    if (form.value.etat_remboursement) {
                        return bon.linked_to_remboursement_statut === true
                    } else {
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
        console.error('Erreur lors du chargement des bons de livraison:', error)
    } finally {
        loadingBonsLivraison.value = false
    }
}

const getNextCode = async () => {
    try {
        const response = await fetch('/api/reglements-clients/next-code')
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
        client_id: '',
        type_reglement: 'Virement',
        numero_piece: '',
        banque: '',
        nom_tire: '',
        tresorerie_id: '',
        montant: 0,
        date_encaissement: '',
        observation: '',
        statut: 'impaye',
        etat_remboursement: null,
        lignes: []
    }
    bonsLivraisonClient.value = []
    editingReglementId.value = null
    clientSearch.value = ''
    showClientDropdown.value = false
    selectedFicheReglement.value = null
}

const setStatut = (statut) => {
    if (formMode.value !== 'view') {
        form.value.statut = statut
    }
}

const setEtatRemboursement = (etat) => {
    if (formMode.value !== 'view') {
        form.value.etat_remboursement = form.value.etat_remboursement === etat ? null : etat
        if (form.value.client_id) {
            loadBonsLivraisonClient(form.value.client_id, editingReglementId.value)
        }
    }
}

const cancelForm = () => {
    showForm.value = false
    resetForm()
}

// Select a client from the searchable dropdown
const selectClient = (client) => {
    form.value.client_id = client.id
    clientSearch.value = `${client.code_client} - ${client.raison_sociale || ''}`
    showClientDropdown.value = false
    form.value.nom_tire = client.raison_sociale || ''
    selectedFicheReglement.value = null
    loadBonsLivraisonClient(client.id)
}

// Close dropdown when clicking outside
const closeClientDropdown = () => {
    setTimeout(() => {
        showClientDropdown.value = false
    }, 200)
}

// Select a fiche règlement
const selectFicheReglement = (fiche) => {
    selectedFicheReglement.value = fiche
}

// Action buttons for selected fiche règlement
const viewSelectedFiche = () => {
    if (selectedFicheReglement.value) {
        viewReglement(selectedFicheReglement.value)
    }
}

const printSelectedFiche = () => {
    if (selectedFicheReglement.value) {
        printReglementPDF(selectedFicheReglement.value)
    }
}

const printSelectedFichePDF = () => {
    if (selectedFicheReglement.value) {
        printReglementPDF(selectedFicheReglement.value)
    }
}

const onClientChange = () => {
    if (form.value.client_id) {
        const clientId = parseInt(form.value.client_id)
        const client = clients.value.find(c => c.id === clientId)
        if (client) {
            form.value.nom_tire = client.raison_sociale || ''
            clientSearch.value = `${client.code_client} - ${client.raison_sociale || ''}`
        }
        loadBonsLivraisonClient(form.value.client_id)
    } else {
        bonsLivraisonClient.value = []
        clientSearch.value = ''
    }
}

const onBonSelectionChange = (bon) => {
    if (!bon.selected) {
        bon.montant_a_imputer = 0
    } else {
        distributePayment()
    }
}

const getMontantPaye = (bon) => {
    const originalMontantRegle = parseFloat(bon.montant_regle) || 0
    const currentImputation = bon.selected ? (parseFloat(bon.montant_a_imputer) || 0) : 0
    return originalMontantRegle + currentImputation
}

const getReste = (bon) => {
    const soldeRestant = parseFloat(bon.solde_restant) || 0
    const currentImputation = bon.selected ? (parseFloat(bon.montant_a_imputer) || 0) : 0
    return soldeRestant - currentImputation
}

const getMontantPayeClass = (bon) => {
    const montantPaye = getMontantPaye(bon)
    const totalGeneral = parseFloat(bon.total_general) || 0
    
    if (montantPaye >= totalGeneral && totalGeneral > 0) {
        return 'text-green-600 dark:text-green-400 font-semibold'
    } else if (montantPaye > 0) {
        return 'text-blue-600 dark:text-blue-400 font-semibold'
    }
    return 'text-gray-600 dark:text-gray-400'
}

const getResteClass = (bon) => {
    const reste = getReste(bon)
    if (reste <= 0) {
        return 'text-green-600 dark:text-green-400 font-semibold'
    }
    return 'text-red-600 dark:text-red-400 font-semibold'
}

const distributePayment = () => {
    let remainingAmount = parseFloat(form.value.montant) || 0
    const selectedBons = bonsLivraisonClient.value.filter(bon => bon.selected)
    
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

// CRUD operations
const saveReglement = async () => {
    if (!form.value.client_id) {
        alert('Veuillez sélectionner un client')
        return
    }
    
    if (!form.value.montant || form.value.montant <= 0) {
        alert('Veuillez saisir un montant valide')
        return
    }
    
    const lignes = bonsLivraisonClient.value
        .filter(bon => bon.selected && bon.montant_a_imputer > 0)
        .map(bon => ({
            bon_livraison_id: bon.id,
            montant_regle: bon.montant_a_imputer
        }))
    
    if (totalImpute.value > form.value.montant) {
        alert('Le total imputé ne peut pas dépasser le montant du règlement')
        return
    }
    
    try {
        const url = formMode.value === 'edit' 
            ? `/api/reglements-clients/${editingReglementId.value}`
            : '/api/reglements-clients'
        
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
        const response = await fetch(`/api/reglements-clients/${reglement.id}`)
        if (response.ok) {
            const data = await response.json()
            form.value = {
                code_reglement: data.code_reglement,
                date_reglement: data.date_reglement,
                client_id: data.client_id,
                type_reglement: data.type_reglement,
                numero_piece: data.numero_piece || '',
                banque: data.banque || '',
                nom_tire: data.nom_tire || '',
                tresorerie_id: data.tresorerie_id || '',
                montant: parseFloat(data.montant),
                date_encaissement: data.date_encaissement || '',
                observation: data.observation || '',
                statut: data.statut || 'impaye',
                etat_remboursement: data.etat_remboursement || null,
                lignes: data.lignes?.map(l => ({
                    bon_livraison_id: l.bon_livraison_id,
                    montant_regle: parseFloat(l.montant_regle)
                })) || []
            }
            editingReglementId.value = data.id
            
            // Set the clientSearch with the client info
            if (data.client) {
                clientSearch.value = `${data.client.code_client} - ${data.client.raison_sociale || ''}`
            }
            selectedFicheReglement.value = null
            
            await loadBonsLivraisonClient(data.client_id, data.id)
            
            if (data.lignes) {
                for (const ligne of data.lignes) {
                    let bon = bonsLivraisonClient.value.find(b => b.id === ligne.bon_livraison_id)
                    
                    if (!bon && ligne.bon_livraison) {
                        bon = {
                            ...ligne.bon_livraison,
                            montant_regle: parseFloat(ligne.bon_livraison.montant_regle || 0),
                            solde_restant: parseFloat(ligne.montant_regle),
                            selected: false,
                            montant_a_imputer: 0
                        }
                        bonsLivraisonClient.value.push(bon)
                    }
                    
                    if (bon) {
                        bon.selected = true
                        bon.montant_a_imputer = parseFloat(ligne.montant_regle)
                        bon.solde_restant = (parseFloat(bon.solde_restant) || 0) + parseFloat(ligne.montant_regle)
                    }
                }
            }
            
            bonsLivraisonClient.value.sort((a, b) => new Date(a.date) - new Date(b.date))
            
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
            const response = await fetch(`/api/reglements-clients/${reglement.id}/mark-paid`, {
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
            const response = await fetch(`/api/reglements-clients/${reglement.id}`, {
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
        'Client': reg.client?.raison_sociale || 'N/A',
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
    link.download = `reglements-clients-${new Date().toISOString().split('T')[0]}.csv`
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
            <title>Règlements Clients</title>
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
                @media print { button { display: none; } }
            </style>
        </head>
        <body>
            <h1>Liste des Règlements Clients</h1>
            <p>Date d'export: ${new Date().toLocaleDateString('fr-FR')}</p>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Client</th>
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
                            <td>${reg.client?.raison_sociale || 'N/A'}</td>
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
                body { font-family: Arial, sans-serif; margin: 0; padding: 40px; color: #333; }
                .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #2563eb; padding-bottom: 20px; }
                .header h1 { color: #2563eb; margin: 0 0 10px 0; font-size: 28px; }
                .header .code { font-size: 18px; color: #666; }
                .info-section { margin-bottom: 30px; }
                .info-section h2 { font-size: 16px; color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 8px; margin-bottom: 15px; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .info-item { display: flex; justify-content: space-between; padding: 10px; background-color: #f8f9fa; border-radius: 4px; }
                .info-item .label { font-weight: bold; color: #555; }
                .info-item .value { color: #333; }
                .amount-section { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 25px; border-radius: 8px; text-align: center; margin: 30px 0; }
                .amount-section .label { font-size: 14px; opacity: 0.9; margin-bottom: 5px; }
                .amount-section .amount { font-size: 32px; font-weight: bold; }
                .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; }
                .status-paye { background-color: #d4edda; color: #155724; }
                .status-impaye { background-color: #f8d7da; color: #721c24; }
                .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; font-size: 12px; color: #666; }
                .signature-section { margin-top: 60px; display: flex; justify-content: space-between; }
                .signature-box { width: 200px; text-align: center; }
                .signature-box .line { border-top: 1px solid #333; margin-top: 60px; padding-top: 10px; }
                @media print { button { display: none; } body { padding: 20px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>RÈGLEMENT CLIENT</h1>
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
                <h2>Informations client</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Client</span>
                        <span class="value">${reglement.client?.raison_sociale || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Code client</span>
                        <span class="value">${reglement.client?.code_client || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Banque</span>
                        <span class="value">${reglement.banque || '-'}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Nom de tiré</span>
                        <span class="value">${reglement.nom_tire || '-'}</span>
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
                    <div class="line">Signature client</div>
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
    loadClients()
    loadTresoreries()
})
</script>

