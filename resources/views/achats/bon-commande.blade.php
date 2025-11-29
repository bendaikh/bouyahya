@extends('layouts.app')

@section('title', 'Bon de commande - Bouyahya')

@section('content')
<div x-data="{
    showForm: false,
    isSubmitting: false,
    formData: {
        date: new Date().toISOString().split('T')[0],
        numero: 'BC-' + new Date().getFullYear() + '-' + String(Math.floor(Math.random() * 10000)).padStart(4, '0'),
        fournisseurId: '',
        codeFournisseur: '',
        nomFournisseur: '',
        modePaiement: 'Espèces',
        echeance: '30 jours'
    },
    items: [],
    async init() {
        await this.fetchNextNumero();
    },
    async fetchNextNumero() {
        try {
            const response = await fetch('{{ route('achats.bon-commande.next-numero') }}');
            const data = await response.json();
            this.formData.numero = data.numero;
        } catch (error) {
            console.error('Error fetching next numero:', error);
        }
    },
    addItem() {
        this.items.push({
            code_article: '',
            designation: '',
            quantite: 1,
            prix_unitaire: 0,
            sous_total: 0
        });
    },
    removeItem(index) {
        this.items.splice(index, 1);
    },
    updateSousTotal(item) {
        item.sous_total = item.quantite * item.prix_unitaire;
    },
    get totalQuantites() {
        return this.items.reduce((sum, item) => sum + parseFloat(item.quantite || 0), 0);
    },
    get totalGeneral() {
        return this.items.reduce((sum, item) => sum + parseFloat(item.sous_total || 0), 0);
    },
    async submitForm() {
        if (this.items.length === 0) {
            alert('Veuillez ajouter au moins un article');
            return;
        }
        
        if (!this.formData.fournisseurId) {
            alert('Veuillez sélectionner un fournisseur');
            return;
        }

        this.isSubmitting = true;

        try {
            const response = await fetch('{{ route('achats.bon-commande.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    date: this.formData.date,
                    numero_bon: this.formData.numero,
                    fournisseur_id: this.formData.fournisseurId,
                    mode_paiement: this.formData.modePaiement,
                    echeance: this.formData.echeance,
                    items: this.items
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Bon de commande créé avec succès!');
                window.location.reload();
            } else {
                alert('Erreur: ' + (data.error || JSON.stringify(data.errors)));
            }
        } catch (error) {
            alert('Erreur lors de la création: ' + error.message);
        } finally {
            this.isSubmitting = false;
        }
    },
    editMode: false,
    editingId: null,
    loadBonCommandeForEdit(id) {
        fetch(`/achats/bon-commande/${id}`)
            .then(response => response.json())
            .then(data => {
                this.editMode = true;
                this.editingId = id;
                this.showForm = true;
                this.formData.date = data.date;
                this.formData.numero = data.numero_bon;
                this.formData.fournisseurId = data.fournisseur_id;
                this.formData.modePaiement = data.mode_paiement;
                this.formData.echeance = data.echeance;
                this.items = data.articles.map(article => ({
                    code_article: article.code_article,
                    designation: article.designation,
                    quantite: article.quantite,
                    prix_unitaire: parseFloat(article.prix_unitaire),
                    sous_total: parseFloat(article.sous_total)
                }));
            })
            .catch(error => {
                alert('Erreur lors du chargement: ' + error.message);
            });
    },
    async updateForm() {
        if (this.items.length === 0) {
            alert('Veuillez ajouter au moins un article');
            return;
        }
        
        if (!this.formData.fournisseurId) {
            alert('Veuillez sélectionner un fournisseur');
            return;
        }

        this.isSubmitting = true;

        try {
            const response = await fetch(`/achats/bon-commande/${this.editingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    date: this.formData.date,
                    numero_bon: this.formData.numero,
                    fournisseur_id: this.formData.fournisseurId,
                    mode_paiement: this.formData.modePaiement,
                    echeance: this.formData.echeance,
                    items: this.items
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Bon de commande modifié avec succès!');
                window.location.reload();
            } else {
                alert('Erreur: ' + (data.error || JSON.stringify(data.errors)));
            }
        } catch (error) {
            alert('Erreur lors de la modification: ' + error.message);
        } finally {
            this.isSubmitting = false;
        }
    },
    cancelEdit() {
        this.editMode = false;
        this.editingId = null;
        this.showForm = false;
        this.items = [];
        this.formData.fournisseurId = '';
        this.formData.date = new Date().toISOString().split('T')[0];
        this.fetchNextNumero();
    },
    searchQuery: '',
    isVisible(fournisseurNom, fournisseurCode) {
        if (!this.searchQuery.trim()) {
            return true;
        }
        const query = this.searchQuery.toLowerCase().trim();
        return fournisseurNom.toLowerCase().includes(query) || fournisseurCode.toLowerCase().includes(query);
    },
    hasVisibleRows() {
        const rows = document.querySelectorAll('tbody tr[x-show]');
        for (let row of rows) {
            if (row.style.display !== 'none' && !row.hasAttribute('x-cloak')) {
                return true;
            }
        }
        return false;
    },
    editBonCommande(id) {
        this.loadBonCommandeForEdit(id);
    },
    deleteBonCommande(id, numero) {
        if (!confirm(`Êtes-vous sûr de vouloir supprimer le bon de commande ${numero} ?`)) {
            return;
        }

        fetch(`/achats/bon-commande/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(error => {
            alert('Erreur: ' + error.message);
        });
    },
    printBonCommande(id) {
        window.open(`/achats/bon-commande/${id}/print`, '_blank');
    }
}" class="space-y-6">

<script>
// Keep for backward compatibility if needed
</script>

    <!-- Form Section -->
    <div x-show="showForm" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white" x-text="editMode ? 'Modifier bon de commande' : 'Nouveau bon de commande'"></h2>
            <button @click="cancelEdit()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Header Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                <input type="date" x-model="formData.date" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">N° Bon de Commande</label>
                <input type="text" x-model="formData.numero" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fournisseur</label>
                <select x-model="formData.fournisseurId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionner un fournisseur</option>
                    @foreach($fournisseurs as $fournisseur)
                        <option value="{{ $fournisseur->id }}">{{ $fournisseur->nom_fournisseur }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Supplier Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Code Fournisseur</label>
                <input type="text" x-model="formData.codeFournisseur" placeholder="Rechercher par code..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom du Fournisseur</label>
                <input type="text" x-model="formData.nomFournisseur" placeholder="Remplissage automatique" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Payment Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode de paiement</label>
                <select x-model="formData.modePaiement" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option>Espèces</option>
                    <option>Chèque</option>
                    <option>Virement</option>
                    <option>Carte bancaire</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Échéance</label>
                <select x-model="formData.echeance" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option>Immédiat</option>
                    <option>15 jours</option>
                    <option>30 jours</option>
                    <option>60 jours</option>
                    <option>90 jours</option>
                </select>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code Article</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Désignation</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">QTÉ</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">P.U. TTC</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sous-Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(item, index) in items" :key="index">
                        <tr>
                            <td class="px-4 py-3">
                                <input type="text" x-model="item.code_article" placeholder="REF001" class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" x-model="item.designation" placeholder="Description de l'article" class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" x-model="item.quantite" @input="updateSousTotal(item)" min="1" class="w-20 px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" x-model="item.prix_unitaire" @input="updateSousTotal(item)" min="0" step="0.01" class="w-28 px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="item.sous_total.toFixed(2) + ' DH'"></td>
                            <td class="px-4 py-3">
                                <button @click="removeItem(index)" class="text-red-600 hover:text-red-900 dark:text-red-400 text-sm font-medium">delete</button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="items.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucun article ajouté</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add Item Button -->
        <div class="mb-6">
            <button @click="addItem()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium text-sm flex items-center">
                <span class="mr-2">add</span> Ajouter une ligne
            </button>
        </div>

        <!-- Totals -->
        <div class="flex justify-end space-y-2">
            <div class="w-full md:w-1/3 space-y-2">
                <div class="flex justify-between items-center pb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Quantités</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="totalQuantites"></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-base font-semibold text-gray-800 dark:text-gray-200">Total Général TTC</span>
                    <span class="text-lg font-bold text-blue-600 dark:text-blue-400" x-text="totalGeneral.toFixed(2) + ' DH'"></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button @click="cancelEdit()" type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Annuler
            </button>
            <button @click="editMode ? updateForm() : submitForm()" type="button" :disabled="isSubmitting" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!isSubmitting" x-text="editMode ? 'Modifier' : 'Enregistrer'"></span>
                <span x-show="isSubmitting" x-text="editMode ? 'Modification...' : 'Enregistrement...'"></span>
            </button>
        </div>
    </div>

    <!-- List View -->
    <div x-show="!showForm" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Bon de commande</h2>
            <p class="text-gray-600 dark:text-gray-400">Gérer les bons de commande fournisseurs</p>
        </div>
        
        <div class="space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <!-- Search Filter -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Rechercher par nom ou code fournisseur..."
                        class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                    <button 
                        x-show="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <button @click="showForm = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Nouveau bon de commande
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N°</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fournisseur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($bonCommandes as $bonCommande)
                            @if($bonCommande->id && $bonCommande->numero_bon)
                            <tr x-show="isVisible('{{ $bonCommande->fournisseur->nom_fournisseur ?? '' }}', '{{ $bonCommande->fournisseur->code_fournisseur ?? '' }}')" 
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonCommande->numero_bon }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonCommande->fournisseur->nom_fournisseur ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonCommande->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($bonCommande->total_general, 2, ',', ' ') }} DH</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($bonCommande->statut === 'Validé') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @endif">
                                        {{ $bonCommande->statut }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <button onclick="window.open('/achats/bon-commande/{{ $bonCommande->id }}/print', '_blank')" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200" title="Imprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </button>
                                        <button @click="loadBonCommandeForEdit({{ $bonCommande->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200" title="Modifier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteBonCommande({{ $bonCommande->id }}, '{{ $bonCommande->numero_bon }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200" title="Supprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Aucun bon de commande trouvé</td>
                            </tr>
                        @endforelse
                        <tr x-show="searchQuery && !hasVisibleRows()">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Aucun bon de commande trouvé pour "<span x-text="searchQuery"></span>"
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

