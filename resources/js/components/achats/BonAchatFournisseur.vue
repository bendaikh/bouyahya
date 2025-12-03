<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Bon d'achat Fournisseur</h2>
                <p class="text-gray-600 dark:text-gray-400">Gérer les bons d'achat fournisseurs</p>
            </div>
            <button 
                v-if="!showForm"
                @click="openCreateForm" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouveau bon d'achat
            </button>
        </div>

        <!-- List View -->
        <div v-if="!showForm">
            <!-- Search Filter -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        placeholder="Rechercher par nom ou code fournisseur..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                    <button 
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
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
            
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N° de bon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client livré</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Qté</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total TTC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-if="loading">
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Chargement...
                        </td>
                    </tr>
                    <tr v-else-if="filteredBonAchats.length === 0 && searchQuery">
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Aucun bon d'achat trouvé pour "{{ searchQuery }}"
                        </td>
                    </tr>
                    <tr v-else-if="bonAchats.length === 0">
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Aucun bon d'achat trouvé
                        </td>
                    </tr>
                    <tr v-else v-for="bon in filteredBonAchats" :key="bon.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ bon.numero_bon }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(bon.date) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ bon.fournisseur?.nom_fournisseur || 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ bon.client_livre || '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900 dark:text-white">{{ bon.total_qte || 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">{{ formatCurrency(bon.total_ttc) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(bon.statut)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                {{ getStatusText(bon.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-3">
                                <button @click="viewBon(bon)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Voir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button v-if="bon.statut === 'brouillon'" @click="editBon(bon)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition-colors" title="Modifier">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button v-if="bon.statut === 'brouillon'" @click="validateBon(bon)" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 transition-colors" title="Valider">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <button @click="exportSingleToExcel(bon)" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors" title="Exporter Excel">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </button>
                                <button @click="exportSingleToPDF(bon)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Exporter PDF">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <button @click="deleteBon(bon)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer">
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
            <!-- Action Buttons -->
            <div class="flex space-x-2 pb-4 border-b border-gray-200 dark:border-gray-700">
                <button 
                    @click="addBon" 
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm"
                    :disabled="formMode === 'view'"
                >
                    + Ajouter
                </button>
                <button 
                    @click="modifyBon" 
                    class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors text-sm"
                    v-if="formMode === 'view'"
                >
                    Modifier
                </button>
                <button 
                    @click="saveBon" 
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors text-sm"
                    v-if="formMode !== 'view'"
                >
                    Valider
                </button>
                <button 
                    @click="cancelForm" 
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm"
                >
                    Annuler
                </button>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Row 1 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° de bon</label>
                    <input 
                        type="text" 
                        v-model="form.numero_bon" 
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                    <input 
                        type="date" 
                        v-model="form.date" 
                        :disabled="formMode === 'view'"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fournisseur</label>
                    <select 
                        v-model="form.fournisseur_id" 
                        @change="onFournisseurChange"
                        :disabled="formMode === 'view'"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    >
                        <option value="">Sélectionner un fournisseur</option>
                        <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.nom_fournisseur }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code Fournisseur</label>
                    <input 
                        type="text" 
                        v-model="selectedFournisseurCode" 
                        readonly
                        placeholder="Code s'affichera ici"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    />
                </div>

                <!-- Row 2 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type Paiement</label>
                    <select 
                        v-model="form.type_paiement"
                        :disabled="formMode === 'view'"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    >
                        <option value="Chèque">Chèque</option>
                        <option value="Espèces">Espèces</option>
                        <option value="Virement">Virement</option>
                        <option value="Traite">Traite</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Échéance</label>
                    <select 
                        v-model="form.echeance"
                        :disabled="formMode === 'view'"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    >
                        <option value="Comptant">Comptant</option>
                        <option value="30 jours">30 jours</option>
                        <option value="60 jours">60 jours</option>
                        <option value="90 jours">90 jours</option>
                    </select>
                </div>
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client livré</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            v-model="clientSearchQuery"
                            @focus="showClientDropdown = true; formMode !== 'view'"
                            @blur="setTimeout(() => showClientDropdown = false, 200)"
                            @input="onClientSearchInput"
                            :disabled="formMode === 'view'"
                            :placeholder="form.client_livre || 'Rechercher un client...'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm pr-8"
                        />
                        <!-- Clear button -->
                        <button 
                            v-if="form.client_livre && formMode !== 'view'"
                            @mousedown.prevent="clearClientSelection"
                            type="button"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <!-- Dropdown arrow when no selection -->
                        <div 
                            v-if="!form.client_livre && formMode !== 'view'"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        
                        <!-- Client suggestions dropdown -->
                        <div 
                            v-if="showClientDropdown && formMode !== 'view' && filteredClients.length > 0"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                        >
                            <div 
                                v-for="client in filteredClients" 
                                :key="client.id"
                                @mousedown.prevent="selectClient(client)"
                                class="px-3 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-medium text-blue-600 dark:text-blue-400 text-sm">{{ client.code_client }}</span>
                                        <span class="text-gray-600 dark:text-gray-400 text-sm mx-1">-</span>
                                        <span class="text-gray-900 dark:text-white text-sm">{{ client.raison_sociale }}</span>
                                    </div>
                                    <span v-if="client.ville" class="text-gray-500 dark:text-gray-400 text-xs">{{ client.ville }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- No results message -->
                        <div 
                            v-if="showClientDropdown && formMode !== 'view' && filteredClients.length === 0 && clientSearchQuery.length > 0"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-3"
                        >
                            <p class="text-gray-500 dark:text-gray-400 text-sm text-center">Aucun client trouvé</p>
                        </div>
                    </div>
                    <!-- Selected client display -->
                    <div v-if="form.client_livre && formMode === 'view'" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ form.client_livre }}
                    </div>
                </div>

                <!-- Row 3 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Famille</label>
                    <select 
                        v-model="form.famille"
                        :disabled="formMode === 'view'"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    >
                        <option value="REV">REV</option>
                        <option value="ENTR">ENTR</option>
                        <option value="PROMO">PROMO</option>
                        <option value="AUTRE">AUTRE</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville</label>
                    <select 
                        v-model="form.ville"
                        :disabled="formMode === 'view'"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    >
                        <option value="">Sélectionner une ville</option>
                        <option v-for="city in cities" :key="city" :value="city">
                            {{ city }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chauffeur</label>
                    <input 
                        type="text" 
                        v-model="form.chauffeur"
                        :disabled="formMode === 'view'"
                        placeholder="Nom du chauffeur"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Matricule</label>
                    <input 
                        type="text" 
                        v-model="form.matricule"
                        :disabled="formMode === 'view'"
                        placeholder="Matricule du véhicule"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    />
                </div>
            </div>

            <!-- Articles Table -->
            <div class="mt-6 overflow-visible">
                <div class="overflow-visible">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">RÉF ARTICLE</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">DÉSIGNATION ARTICLE</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">QTÉ</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">PRIX U TTC</th>
                                <th v-if="formMode !== 'view'" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(article, index) in form.articles" :key="index">
                                <td class="px-4 py-2">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            :value="article.ref_article"
                                            @input="onArticleRefInput(index, $event)"
                                            @focus="activeArticleIndex = index; activeFieldType = 'ref'; articleSearchQuery = article.ref_article; showArticleSuggestions = article.ref_article?.length > 0"
                                            @blur="setTimeout(() => closeSuggestions(), 200)"
                                            :disabled="formMode === 'view'"
                                            placeholder="Tapez pour rechercher..."
                                            class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                        />
                                        <!-- Article Suggestions Dropdown - only show when ref field is active -->
                                        <div 
                                            v-if="showArticleSuggestions && activeArticleIndex === index && activeFieldType === 'ref' && filteredArticles.length > 0"
                                            class="absolute z-50 w-[450px] mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                        >
                                            <div 
                                                v-for="art in filteredArticles" 
                                                :key="art.id"
                                                @mousedown.prevent="selectArticle(art, index)"
                                                class="px-3 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                            >
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <span class="font-medium text-blue-600 dark:text-blue-400 text-sm">{{ art.reference }}</span>
                                                        <p class="text-gray-700 dark:text-gray-300 text-sm">{{ art.designation }}</p>
                                                    </div>
                                                    <span class="text-green-600 dark:text-green-400 font-medium text-sm">
                                                        {{ formatCurrency(art.prix_achat || art.prix_vente || 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- No results message - only for ref field -->
                                        <div 
                                            v-if="showArticleSuggestions && activeArticleIndex === index && activeFieldType === 'ref' && filteredArticles.length === 0 && articleSearchQuery.length > 0"
                                            class="absolute z-50 w-[450px] mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-3"
                                        >
                                            <p class="text-gray-500 dark:text-gray-400 text-sm text-center">Aucun article trouvé</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            :value="article.designation_article"
                                            @input="onArticleDesignationInput(index, $event)"
                                            @focus="activeArticleIndex = index; activeFieldType = 'designation'; articleSearchQuery = article.designation_article; showArticleSuggestions = article.designation_article?.length > 0"
                                            @blur="setTimeout(() => closeSuggestions(), 200)"
                                            :disabled="formMode === 'view'"
                                            placeholder="Tapez pour rechercher..."
                                            class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                        />
                                        <!-- Article Suggestions Dropdown - only show when designation field is active -->
                                        <div 
                                            v-if="showArticleSuggestions && activeArticleIndex === index && activeFieldType === 'designation' && filteredArticles.length > 0"
                                            class="absolute z-50 w-[450px] mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                        >
                                            <div 
                                                v-for="art in filteredArticles" 
                                                :key="art.id"
                                                @mousedown.prevent="selectArticle(art, index)"
                                                class="px-3 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                            >
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <span class="font-medium text-blue-600 dark:text-blue-400 text-sm">{{ art.reference }}</span>
                                                        <p class="text-gray-700 dark:text-gray-300 text-sm">{{ art.designation }}</p>
                                                    </div>
                                                    <span class="text-green-600 dark:text-green-400 font-medium text-sm">
                                                        {{ formatCurrency(art.prix_achat || art.prix_vente || 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- No results message - only for designation field -->
                                        <div 
                                            v-if="showArticleSuggestions && activeArticleIndex === index && activeFieldType === 'designation' && filteredArticles.length === 0 && articleSearchQuery.length > 0"
                                            class="absolute z-50 w-[450px] mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-3"
                                        >
                                            <p class="text-gray-500 dark:text-gray-400 text-sm text-center">Aucun article trouvé</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <input 
                                        type="number" 
                                        v-model.number="article.qte"
                                        :disabled="formMode === 'view'"
                                        min="1"
                                        class="w-24 px-2 py-1 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                    />
                                </td>
                                <td class="px-4 py-2">
                                    <input 
                                        type="number" 
                                        v-model.number="article.prix_unitaire_ttc"
                                        :disabled="formMode === 'view'"
                                        step="0.01"
                                        min="0"
                                        class="w-28 px-2 py-1 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                    />
                                </td>
                                <td v-if="formMode !== 'view'" class="px-4 py-2">
                                    <button @click="removeArticle(index)" class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button 
                    v-if="formMode !== 'view'"
                    @click="addArticle" 
                    class="mt-4 text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center"
                >
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un article
                </button>
            </div>

            <!-- Totals -->
            <div class="flex justify-end space-x-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sous total TTC</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(calculateTotal()) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Qté</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ calculateTotalQte() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total TTC</p>
                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(calculateTotal()) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const bonAchats = ref([])
const fournisseurs = ref([])
const clients = ref([])
const cities = ref([])
const allArticles = ref([]) // All articles from database
const loading = ref(false)
const showForm = ref(false)
const formMode = ref('create') // 'create', 'edit', 'view'
const editingBonId = ref(null)
const searchQuery = ref('')

// Article autocomplete state
const activeArticleIndex = ref(null) // Which article row is being searched
const activeFieldType = ref(null) // 'ref' or 'designation' - which field is active
const articleSearchQuery = ref('')
const showArticleSuggestions = ref(false)

// Client search/filter state
const clientSearchQuery = ref('')
const showClientDropdown = ref(false)

const filteredBonAchats = computed(() => {
    if (!searchQuery.value.trim()) {
        return bonAchats.value
    }
    const query = searchQuery.value.toLowerCase().trim()
    return bonAchats.value.filter(bon => {
        const fournisseurNom = (bon.fournisseur?.nom_fournisseur || '').toLowerCase()
        const fournisseurCode = (bon.fournisseur?.code_fournisseur || '').toLowerCase()
        return fournisseurNom.includes(query) || fournisseurCode.includes(query)
    })
})

const form = ref({
    numero_bon: '',
    date: new Date().toISOString().split('T')[0],
    fournisseur_id: '',
    type_paiement: 'Chèque',
    echeance: '30 jours',
    client_livre: '',
    famille: 'REV',
    ville: '',
    chauffeur: '',
    matricule: '',
    articles: []
})

const selectedFournisseurName = computed(() => {
    if (!form.value.fournisseur_id) return ''
    const fournisseur = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
    return fournisseur ? fournisseur.nom_fournisseur : ''
})

const selectedFournisseurCode = computed(() => {
    if (!form.value.fournisseur_id) return ''
    const fournisseur = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
    return fournisseur ? fournisseur.code_fournisseur : ''
})

// Filtered clients based on search query
const filteredClients = computed(() => {
    if (!clientSearchQuery.value || clientSearchQuery.value.length === 0) {
        // If no search query, show all clients (limited to first 50)
        return clients.value.slice(0, 50)
    }
    const query = clientSearchQuery.value.toLowerCase()
    return clients.value.filter(client => 
        (client.code_client?.toLowerCase() || '').includes(query) ||
        (client.raison_sociale?.toLowerCase() || '').includes(query) ||
        (client.ville?.toLowerCase() || '').includes(query)
    ).slice(0, 50) // Limit to 50 suggestions
})

// Load data
const loadBonAchats = async () => {
    loading.value = true
    try {
        const response = await fetch('/api/bon-achat-fournisseur')
        if (response.ok) {
            bonAchats.value = await response.json()
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

const loadCities = async () => {
    try {
        const response = await fetch('/api/settings')
        if (response.ok) {
            const settings = await response.json()
            cities.value = settings.cities || []
        }
    } catch (error) {
        console.error('Erreur lors du chargement des villes:', error)
    }
}

// Load all articles for autocomplete
const loadAllArticles = async () => {
    try {
        const response = await fetch('/api/articles')
        if (response.ok) {
            const data = await response.json()
            allArticles.value = data.articles || []
        }
    } catch (error) {
        console.error('Erreur lors du chargement des articles:', error)
    }
}

// Filtered articles based on search query
const filteredArticles = computed(() => {
    if (!articleSearchQuery.value || articleSearchQuery.value.length < 1) {
        return []
    }
    const query = articleSearchQuery.value.toLowerCase()
    return allArticles.value.filter(article => 
        article.reference.toLowerCase().includes(query) ||
        article.designation.toLowerCase().includes(query)
    ).slice(0, 10) // Limit to 10 suggestions
})

// Handle article reference input
const onArticleRefInput = (index, event) => {
    const value = event.target.value
    form.value.articles[index].ref_article = value
    articleSearchQuery.value = value
    activeArticleIndex.value = index
    activeFieldType.value = 'ref'
    showArticleSuggestions.value = value.length > 0
}

// Handle article designation input (also search by designation)
const onArticleDesignationInput = (index, event) => {
    const value = event.target.value
    form.value.articles[index].designation_article = value
    articleSearchQuery.value = value
    activeArticleIndex.value = index
    activeFieldType.value = 'designation'
    showArticleSuggestions.value = value.length > 0
}

// Select article from suggestions
const selectArticle = (article, index) => {
    form.value.articles[index].ref_article = article.reference
    form.value.articles[index].designation_article = article.designation
    // Use prix_achat if available, otherwise use prix_vente or 0
    form.value.articles[index].prix_unitaire_ttc = article.prix_achat || article.prix_vente || 0
    showArticleSuggestions.value = false
    activeArticleIndex.value = null
    activeFieldType.value = null
    articleSearchQuery.value = ''
}

// Close suggestions when clicking outside
const closeSuggestions = () => {
    showArticleSuggestions.value = false
    activeArticleIndex.value = null
    activeFieldType.value = null
}

// Client selection methods
const onClientSearchInput = () => {
    showClientDropdown.value = true
}

const selectClient = (client) => {
    form.value.client_livre = client.raison_sociale
    clientSearchQuery.value = ''
    showClientDropdown.value = false
    
    // Optionally auto-fill ville from client if not already set
    if (!form.value.ville && client.ville) {
        form.value.ville = client.ville
    }
}

const clearClientSelection = () => {
    form.value.client_livre = ''
    clientSearchQuery.value = ''
}

const getNextNumeroBon = async () => {
    try {
        const response = await fetch('/api/bon-achat-fournisseur/next-numero')
        if (response.ok) {
            const data = await response.json()
            form.value.numero_bon = data.numero_bon
        }
    } catch (error) {
        console.error('Erreur lors de la génération du numéro:', error)
    }
}

// Form actions
const openCreateForm = () => {
    resetForm()
    getNextNumeroBon()
    formMode.value = 'create'
    showForm.value = true
}

const addBon = () => {
    // Switch from view to create mode
    if (formMode.value === 'view') {
        openCreateForm()
    }
}

const modifyBon = () => {
    formMode.value = 'edit'
}

const saveBon = async () => {
    // Validate
    if (!form.value.fournisseur_id) {
        alert('Veuillez sélectionner un fournisseur')
        return
    }
    
    if (form.value.articles.length === 0) {
        alert('Veuillez ajouter au moins un article')
        return
    }
    
    // Check if all articles are filled
    for (const article of form.value.articles) {
        if (!article.ref_article || !article.designation_article || !article.qte || !article.prix_unitaire_ttc) {
            alert('Veuillez remplir tous les champs des articles')
            return
        }
    }
    
    try {
        const url = formMode.value === 'edit' 
            ? `/api/bon-achat-fournisseur/${editingBonId.value}`
            : '/api/bon-achat-fournisseur'
        
        const method = formMode.value === 'edit' ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(form.value)
        })
        
        if (response.ok) {
            alert(formMode.value === 'edit' ? 'Bon d\'achat modifié avec succès' : 'Bon d\'achat créé avec succès')
            showForm.value = false
            loadBonAchats()
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

const cancelForm = () => {
    showForm.value = false
    resetForm()
}

const resetForm = () => {
    form.value = {
        numero_bon: '',
        date: new Date().toISOString().split('T')[0],
        fournisseur_id: '',
        type_paiement: 'Chèque',
        echeance: '30 jours',
        client_livre: '',
        famille: 'REV',
        ville: '',
        chauffeur: '',
        matricule: '',
        articles: []
    }
    editingBonId.value = null
    clientSearchQuery.value = ''
    showClientDropdown.value = false
}

// Articles management
const addArticle = () => {
    form.value.articles.push({
        ref_article: '',
        designation_article: '',
        qte: 1,
        prix_unitaire_ttc: 0
    })
}

const removeArticle = (index) => {
    form.value.articles.splice(index, 1)
}

const onFournisseurChange = () => {
    // Auto-fill ville from fournisseur if available
    if (form.value.fournisseur_id) {
        const fournisseur = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
        if (fournisseur && fournisseur.ville) {
            form.value.ville = fournisseur.ville
        }
    }
}

// View/Edit/Delete actions
const viewBon = async (bon) => {
    try {
        const response = await fetch(`/api/bon-achat-fournisseur/${bon.id}`)
        if (response.ok) {
            const data = await response.json()
            // Normalize date for HTML date input (expects YYYY-MM-DD)
            const normalizedDate = data.date 
                ? new Date(data.date).toISOString().split('T')[0] 
                : new Date().toISOString().split('T')[0]
            form.value = {
                numero_bon: data.numero_bon,
                date: normalizedDate,
                fournisseur_id: data.fournisseur_id,
                type_paiement: data.type_paiement,
                echeance: data.echeance,
                client_livre: data.client_livre || '',
                famille: data.famille,
                ville: data.ville || '',
                chauffeur: data.chauffeur || '',
                matricule: data.matricule || '',
                articles: data.articles || []
            }
            editingBonId.value = data.id
            formMode.value = 'view'
            showForm.value = true
        }
    } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur lors du chargement')
    }
}

const editBon = async (bon) => {
    await viewBon(bon)
    formMode.value = 'edit'
}

const validateBon = async (bon) => {
    if (confirm('Voulez-vous valider ce bon d\'achat ?')) {
        try {
            const response = await fetch(`/api/bon-achat-fournisseur/${bon.id}/validate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            
            if (response.ok) {
                alert('Bon d\'achat validé avec succès')
                loadBonAchats()
            }
        } catch (error) {
            console.error('Erreur:', error)
            alert('Erreur lors de la validation')
        }
    }
}

const deleteBon = async (bon) => {
    if (confirm('Voulez-vous vraiment supprimer ce bon d\'achat ?')) {
        try {
            const response = await fetch(`/api/bon-achat-fournisseur/${bon.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            
            if (response.ok) {
                alert('Bon d\'achat supprimé avec succès')
                loadBonAchats()
            }
        } catch (error) {
            console.error('Erreur:', error)
            alert('Erreur lors de la suppression')
        }
    }
}

// Calculations
const calculateTotal = () => {
    return form.value.articles.reduce((sum, article) => {
        return sum + (article.qte * article.prix_unitaire_ttc)
    }, 0)
}

const calculateTotalQte = () => {
    return form.value.articles.reduce((sum, article) => {
        return sum + (parseInt(article.qte) || 0)
    }, 0)
}

// Formatting
const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD'
    }).format(value)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR')
}

const getStatusClass = (statut) => {
    switch (statut) {
        case 'valide':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        case 'annule':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        default:
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    }
}

const getStatusText = (statut) => {
    switch (statut) {
        case 'valide':
            return 'Validé'
        case 'annule':
            return 'Annulé'
        default:
            return 'Brouillon'
    }
}

// Export single bon d'achat to Excel
const exportSingleToExcel = async (bon) => {
    try {
        // Fetch full details if articles not loaded
        let bonDetails = bon
        if (!bon.articles || bon.articles.length === 0) {
            const response = await fetch(`/api/bon-achat-fournisseur/${bon.id}`)
            if (response.ok) {
                bonDetails = await response.json()
            }
        }
        
        // Prepare header data
        const headerData = [
            ['BON D\'ACHAT FOURNISSEUR'],
            [''],
            ['N° de bon:', bonDetails.numero_bon],
            ['Date:', formatDate(bonDetails.date)],
            ['Fournisseur:', bonDetails.fournisseur?.nom_fournisseur || 'N/A'],
            ['Code Fournisseur:', bonDetails.fournisseur?.code_fournisseur || 'N/A'],
            ['Type Paiement:', bonDetails.type_paiement],
            ['Échéance:', bonDetails.echeance],
            ['Client livré:', bonDetails.client_livre || '-'],
            ['Famille:', bonDetails.famille],
            ['Ville:', bonDetails.ville || '-'],
            ['Chauffeur:', bonDetails.chauffeur || '-'],
            ['Matricule:', bonDetails.matricule || '-'],
            ['Statut:', getStatusText(bonDetails.statut)],
            [''],
            ['ARTICLES'],
            ['Réf Article', 'Désignation', 'Quantité', 'Prix U. TTC', 'Total']
        ]
        
        // Add articles
        const articlesData = bonDetails.articles?.map(article => [
            article.ref_article,
            article.designation_article,
            article.qte,
            article.prix_unitaire_ttc,
            article.total
        ]) || []
        
        // Add totals
        const totalsData = [
            [''],
            ['', '', 'Total Qté:', bonDetails.total_qte, ''],
            ['', '', 'Sous-total TTC:', '', bonDetails.sous_total_ttc],
            ['', '', 'Total TTC:', '', bonDetails.total_ttc]
        ]
        
        // Combine all data
        const allData = [...headerData, ...articlesData, ...totalsData]
        
        // Convert to CSV
        const csvContent = allData.map(row => 
            row.map(cell => {
                const value = cell?.toString() || ''
                return value.includes(',') || value.includes('"') 
                    ? `"${value.replace(/"/g, '""')}"` 
                    : value
            }).join(',')
        ).join('\n')
        
        // Download
        const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' })
        const link = document.createElement('a')
        link.href = URL.createObjectURL(blob)
        link.download = `bon-achat-${bonDetails.numero_bon}.csv`
        link.click()
    } catch (error) {
        console.error('Erreur lors de l\'export:', error)
        alert('Erreur lors de l\'export')
    }
}

// Export single bon d'achat to PDF
const exportSingleToPDF = async (bon) => {
    try {
        // Fetch full details if articles not loaded
        let bonDetails = bon
        if (!bon.articles || bon.articles.length === 0) {
            const response = await fetch(`/api/bon-achat-fournisseur/${bon.id}`)
            if (response.ok) {
                bonDetails = await response.json()
            }
        }
        
        const printWindow = window.open('', '_blank')
        
        const htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Bon d'achat ${bonDetails.numero_bon}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                        border-bottom: 3px solid #4CAF50;
                        padding-bottom: 10px;
                    }
                    .header h1 {
                        margin: 0;
                        color: #4CAF50;
                    }
                    .info-section {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 30px;
                    }
                    .info-box {
                        border: 1px solid #ddd;
                        padding: 15px;
                        border-radius: 5px;
                        background-color: #f9f9f9;
                    }
                    .info-box h3 {
                        margin-top: 0;
                        color: #4CAF50;
                        border-bottom: 2px solid #4CAF50;
                        padding-bottom: 5px;
                    }
                    .info-row {
                        margin: 8px 0;
                    }
                    .info-label {
                        font-weight: bold;
                        display: inline-block;
                        width: 140px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    th {
                        background-color: #4CAF50;
                        color: white;
                        padding: 12px;
                        text-align: left;
                        font-size: 13px;
                    }
                    td {
                        border: 1px solid #ddd;
                        padding: 10px;
                        font-size: 12px;
                    }
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .totals-section {
                        margin-top: 20px;
                        text-align: right;
                    }
                    .totals-section table {
                        margin-left: auto;
                        width: 400px;
                        border: 2px solid #4CAF50;
                    }
                    .totals-section td {
                        padding: 8px;
                        font-weight: bold;
                    }
                    .total-final {
                        background-color: #4CAF50;
                        color: white;
                        font-size: 16px;
                    }
                    .status-badge {
                        padding: 4px 12px;
                        border-radius: 4px;
                        font-weight: bold;
                        display: inline-block;
                    }
                    .status-valide { background-color: #d4edda; color: #155724; }
                    .status-brouillon { background-color: #fff3cd; color: #856404; }
                    .status-annule { background-color: #f8d7da; color: #721c24; }
                    .footer {
                        margin-top: 40px;
                        text-align: center;
                        font-size: 11px;
                        color: #777;
                        border-top: 1px solid #ddd;
                        padding-top: 20px;
                    }
                    @media print {
                        button { display: none; }
                        body { margin: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>BON D'ACHAT FOURNISSEUR</h1>
                    <p>N° ${bonDetails.numero_bon}</p>
                </div>
                
                <div class="info-section">
                    <div class="info-box">
                        <h3>Informations Générales</h3>
                        <div class="info-row">
                            <span class="info-label">Date:</span>
                            <span>${formatDate(bonDetails.date)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Type Paiement:</span>
                            <span>${bonDetails.type_paiement}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Échéance:</span>
                            <span>${bonDetails.echeance}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Famille:</span>
                            <span>${bonDetails.famille}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Statut:</span>
                            <span class="status-badge status-${bonDetails.statut}">${getStatusText(bonDetails.statut)}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <h3>Fournisseur & Livraison</h3>
                        <div class="info-row">
                            <span class="info-label">Fournisseur:</span>
                            <span>${bonDetails.fournisseur?.nom_fournisseur || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Code Fournisseur:</span>
                            <span>${bonDetails.fournisseur?.code_fournisseur || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Client livré:</span>
                            <span>${bonDetails.client_livre || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ville:</span>
                            <span>${bonDetails.ville || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Chauffeur:</span>
                            <span>${bonDetails.chauffeur || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Matricule:</span>
                            <span>${bonDetails.matricule || '-'}</span>
                        </div>
                    </div>
                </div>
                
                <h3>Articles</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Réf Article</th>
                            <th>Désignation</th>
                            <th style="text-align: center;">Quantité</th>
                            <th style="text-align: right;">Prix U. TTC</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bonDetails.articles?.map(article => `
                            <tr>
                                <td>${article.ref_article}</td>
                                <td>${article.designation_article}</td>
                                <td style="text-align: center;">${article.qte}</td>
                                <td style="text-align: right;">${formatCurrency(article.prix_unitaire_ttc)}</td>
                                <td style="text-align: right;">${formatCurrency(article.total)}</td>
                            </tr>
                        `).join('') || '<tr><td colspan="5" style="text-align: center;">Aucun article</td></tr>'}
                    </tbody>
                </table>
                
                <div class="totals-section">
                    <table>
                        <tr>
                            <td>Total Quantité:</td>
                            <td style="text-align: right;">${bonDetails.total_qte}</td>
                        </tr>
                        <tr>
                            <td>Sous-total TTC:</td>
                            <td style="text-align: right;">${formatCurrency(bonDetails.sous_total_ttc)}</td>
                        </tr>
                        <tr class="total-final">
                            <td>Total TTC:</td>
                            <td style="text-align: right;">${formatCurrency(bonDetails.total_ttc)}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="footer">
                    <p>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                    <button onclick="window.print()" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px; margin-top: 10px;">
                        Imprimer / Sauvegarder en PDF
                    </button>
                </div>
            </body>
            </html>
        `
        
        printWindow.document.write(htmlContent)
        printWindow.document.close()
    } catch (error) {
        console.error('Erreur lors de l\'export PDF:', error)
        alert('Erreur lors de l\'export PDF')
    }
}

// Export functions
const exportToExcel = () => {
    if (bonAchats.value.length === 0) {
        alert('Aucune donnée à exporter')
        return
    }
    
    // Prepare data for export
    const data = bonAchats.value.map(bon => ({
        'N° de bon': bon.numero_bon,
        'Date': formatDate(bon.date),
        'Fournisseur': bon.fournisseur?.nom_fournisseur || 'N/A',
        'Code Fournisseur': bon.fournisseur?.code_fournisseur || 'N/A',
        'Type Paiement': bon.type_paiement,
        'Échéance': bon.echeance,
        'Client livré': bon.client_livre || '-',
        'Famille': bon.famille,
        'Ville': bon.ville || '-',
        'Total Qté': bon.total_qte,
        'Total TTC': bon.total_ttc,
        'Statut': getStatusText(bon.statut)
    }))
    
    // Convert to CSV
    const headers = Object.keys(data[0])
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => {
            const value = row[header]
            // Escape commas and quotes
            return typeof value === 'string' && value.includes(',') 
                ? `"${value.replace(/"/g, '""')}"` 
                : value
        }).join(','))
    ].join('\n')
    
    // Create and download file
    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `bon-achat-fournisseur-${new Date().toISOString().split('T')[0]}.csv`
    link.click()
}

const exportToPDF = () => {
    if (bonAchats.value.length === 0) {
        alert('Aucune donnée à exporter')
        return
    }
    
    // Create a printable version
    const printWindow = window.open('', '_blank')
    
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Bons d'achat Fournisseur</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                h1 {
                    text-align: center;
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                    font-size: 12px;
                }
                th {
                    background-color: #4CAF50;
                    color: white;
                }
                tr:nth-child(even) {
                    background-color: #f2f2f2;
                }
                .status-badge {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-weight: bold;
                }
                .status-valide { background-color: #d4edda; color: #155724; }
                .status-brouillon { background-color: #fff3cd; color: #856404; }
                .status-annule { background-color: #f8d7da; color: #721c24; }
                @media print {
                    button { display: none; }
                }
            </style>
        </head>
        <body>
            <h1>Liste des Bons d'achat Fournisseur</h1>
            <p>Date d'export: ${new Date().toLocaleDateString('fr-FR')}</p>
            <table>
                <thead>
                    <tr>
                        <th>N° de bon</th>
                        <th>Date</th>
                        <th>Fournisseur</th>
                        <th>Type Paiement</th>
                        <th>Client livré</th>
                        <th>Ville</th>
                        <th>Total Qté</th>
                        <th>Total TTC</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    ${bonAchats.value.map(bon => `
                        <tr>
                            <td>${bon.numero_bon}</td>
                            <td>${formatDate(bon.date)}</td>
                            <td>${bon.fournisseur?.nom_fournisseur || 'N/A'}</td>
                            <td>${bon.type_paiement}</td>
                            <td>${bon.client_livre || '-'}</td>
                            <td>${bon.ville || '-'}</td>
                            <td>${bon.total_qte}</td>
                            <td>${formatCurrency(bon.total_ttc)}</td>
                            <td><span class="status-badge status-${bon.statut}">${getStatusText(bon.statut)}</span></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            <br>
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px;">
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
    loadBonAchats()
    loadFournisseurs()
    loadClients()
    loadCities()
    loadAllArticles()
})
</script>

