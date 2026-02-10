@extends('layouts.app')

@section('title', 'Bon de Commande - Bouyahya')

@section('content')
<script>
    window.bonCommandeArticles = @json($articles ?? []);
    // User permissions for frontend checks
    window.userPermissions = {
        canCreate: @json(auth()->user()?->can('ventes.bon-commande.create') ?? false),
        canEdit: @json(auth()->user()?->can('ventes.bon-commande.edit') ?? false),
        canDelete: @json(auth()->user()?->can('ventes.bon-commande.delete') ?? false),
        canValidate: @json(auth()->user()?->can('ventes.bon-commande.validate') ?? false),
        canConvert: @json(auth()->user()?->can('ventes.bon-commande.convert') ?? false),
        canPrint: @json(auth()->user()?->can('ventes.bon-commande.print') ?? false),
    };
</script>

<div x-data="bonCommandeApp()" @open-new-form.window="openNewForm()" class="space-y-6">

<script>
function bonCommandeApp() {
    return {
        showForm: false,
        isSubmitting: false,
        editMode: false,
        editingId: null,
        formData: {
            date: new Date().toISOString().split('T')[0],
            numero: '',
            clientId: '',
            fournisseurId: '',
            villeLivraison: '',
            modePaiement: 'Espèces',
            echeance: '',
            motifAnnulation: ''
        },
        items: [],
        articles: window.bonCommandeArticles || [],
        searchArticle: '',
        filteredArticles: [],
        
        async init() {
            await this.fetchNextNumero();
        },
        
        async fetchNextNumero() {
            try {
                const response = await fetch('{{ route('ventes.bon-commande.next-numero') }}');
                const data = await response.json();
                this.formData.numero = data.numero;
            } catch (error) {
                console.error('Error fetching next numero:', error);
            }
        },
        
        searchArticles() {
            if (this.searchArticle.length < 1) {
                this.filteredArticles = [];
                return;
            }
            const search = this.searchArticle.toLowerCase();
            this.filteredArticles = this.articles.filter(a => 
                a.reference.toLowerCase().includes(search) || 
                a.designation.toLowerCase().includes(search)
            ).slice(0, 10);
        },
        
        selectArticle(article) {
            this.items.push({
                code_article: article.reference,
                designation: article.designation,
                quantite: 1,
                prix_unitaire: parseFloat(article.prix_vente) || 0,
                sous_total: parseFloat(article.prix_vente) || 0
            });
            this.searchArticle = '';
            this.filteredArticles = [];
        },
        
        addEmptyItem() {
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
        
        formatCurrency(value) {
            return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
        },
        
        async submitForm() {
            if (this.items.length === 0) {
                alert('Veuillez ajouter au moins un article');
                return;
            }
            
            if (!this.formData.clientId) {
                alert('Veuillez sélectionner un client');
                return;
            }

            this.isSubmitting = true;

            try {
                const response = await fetch('{{ route('ventes.bon-commande.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        date: this.formData.date,
                        numero_bon: this.formData.numero,
                        client_id: this.formData.clientId,
                        fournisseur_id: this.formData.fournisseurId || null,
                        mode_paiement: this.formData.modePaiement,
                        echeance: this.formData.echeance || null,
                        ville_livraison: this.formData.villeLivraison,
                        motif_annulation: this.formData.motifAnnulation,
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
        
        loadBonCommandeForEdit(id) {
            fetch(`/ventes/bon-commande/${id}`)
                .then(response => response.json())
                .then(data => {
                    this.editMode = true;
                    this.editingId = id;
                    this.showForm = true;
                    this.formData.date = data.date ? data.date.split('T')[0] : '';
                    this.formData.numero = data.numero_bon;
                    this.formData.clientId = data.client_id;
                    this.formData.fournisseurId = data.fournisseur_id || '';
                    this.formData.modePaiement = data.mode_paiement;
                    this.formData.echeance = data.echeance || '';
                    this.formData.villeLivraison = data.ville_livraison || '';
                    this.formData.motifAnnulation = data.motif_annulation || '';
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
            
            if (!this.formData.clientId) {
                alert('Veuillez sélectionner un client');
                return;
            }

            this.isSubmitting = true;

            try {
                const response = await fetch(`/ventes/bon-commande/${this.editingId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        date: this.formData.date,
                        numero_bon: this.formData.numero,
                        client_id: this.formData.clientId,
                        fournisseur_id: this.formData.fournisseurId || null,
                        mode_paiement: this.formData.modePaiement,
                        echeance: this.formData.echeance || null,
                        ville_livraison: this.formData.villeLivraison,
                        motif_annulation: this.formData.motifAnnulation,
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
            this.formData.clientId = '';
            this.formData.fournisseurId = '';
            this.formData.villeLivraison = '';
            this.formData.modePaiement = 'Espèces';
            this.formData.echeance = '';
            this.formData.motifAnnulation = '';
            this.formData.date = new Date().toISOString().split('T')[0];
            this.fetchNextNumero();
        },
        
        openNewForm() {
            this.cancelEdit();
            this.showForm = true;
        }
    };
}
</script>

<script>
function editBonCommande(id) {
    const component = Alpine.$data(document.querySelector('[x-data]'));
    component.loadBonCommandeForEdit(id);
}

function deleteBonCommande(id, numero) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le bon de commande ${numero} ?`)) {
        return;
    }

    fetch(`/ventes/bon-commande/${id}`, {
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
}

function printBonCommande(id) {
    window.open(`/ventes/bon-commande/${id}/print`, '_blank');
}

function validateBonCommande(id, numero) {
    if (!confirm(`Êtes-vous sûr de vouloir valider le bon de commande ${numero} ?`)) {
        return;
    }

    fetch(`/ventes/bon-commande/${id}/validate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && !data.error) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.error || data.message || 'Erreur lors de la validation');
        }
    })
    .catch(error => {
        alert('Erreur: ' + error.message);
    });
}

function convertToBonLivraison(id, numero) {
    if (!confirm(`Êtes-vous sûr de vouloir convertir le bon de commande ${numero} en bon de livraison ?`)) {
        return;
    }

    fetch(`/ventes/bon-commande/${id}/convert-to-bon-livraison`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message && !data.error) {
            alert(data.message);
            if (confirm('Voulez-vous aller à la page des bons de livraison ?')) {
                window.location.href = '{{ route('ventes.bon-livraison') }}';
            } else {
                window.location.reload();
            }
        } else {
            alert(data.error || 'Erreur lors de la conversion');
        }
    })
    .catch(error => {
        alert('Erreur: ' + error.message);
    });
}
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
                <input type="text" x-model="formData.numero" readonly class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client</label>
                <select x-model="formData.clientId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionner un client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->raison_sociale }} ({{ $client->code_client }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Fournisseur -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fournisseur</label>
                <select x-model="formData.fournisseurId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionner un fournisseur</option>
                    @foreach($fournisseurs as $fournisseur)
                        <option value="{{ $fournisseur->id }}">{{ $fournisseur->nom_fournisseur }} ({{ $fournisseur->code_fournisseur }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Client Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ville de Livraison</label>
                <select x-model="formData.villeLivraison" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionner une ville</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode de paiement</label>
                <select x-model="formData.modePaiement" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option>Espèces</option>
                    <option>Chèque</option>
                    <option>Virement</option>
                    <option>Carte bancaire</option>
                    <option>Crédit</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Échéance</label>
                <input type="date" x-model="formData.echeance" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Article Search -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rechercher un article</label>
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchArticle" 
                    @input="searchArticles()"
                    @keydown.escape="filteredArticles = []"
                    placeholder="Rechercher par code ou désignation..."
                    class="w-full md:w-96 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                >
                <div x-show="filteredArticles.length > 0" class="absolute z-50 w-full md:w-96 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <template x-for="(article, index) in filteredArticles" :key="article.id">
                        <div 
                            @click="selectArticle(article)"
                            class="px-4 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-blue-600 dark:text-blue-400 font-mono text-sm" x-text="article.reference"></span>
                                    <span class="text-gray-700 dark:text-gray-300 ml-2" x-text="article.designation"></span>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-600 dark:text-green-400 font-semibold" x-text="article.prix_vente + ' DH'"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Stock: <span :class="article.stock_actuel <= 0 ? 'text-red-500 font-bold' : 'text-blue-500'" x-text="article.stock_actuel"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
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
                                <input type="text" x-model="item.code_article" class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" x-model="item.designation" class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" x-model="item.quantite" @input="updateSousTotal(item)" min="1" class="w-20 px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" x-model="item.prix_unitaire" @input="updateSousTotal(item)" min="0" step="0.01" class="w-28 px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="item.sous_total.toFixed(2) + ' DH'"></td>
                            <td class="px-4 py-3">
                                <button @click="removeItem(index)" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
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
            <button @click="addEmptyItem()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium text-sm flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Ajouter une ligne
            </button>
        </div>

        <!-- Motif d'annulation -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Motif d'annulation (si applicable)</label>
            <textarea x-model="formData.motifAnnulation" rows="2" placeholder="Précisez la raison de l'annulation..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
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
                    <span class="text-lg font-bold text-blue-600 dark:text-blue-400" x-text="formatCurrency(totalGeneral) + ' DH'"></span>
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
    <div x-show="!showForm" x-data="bonCommandeListApp()" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Bon de commande</h2>
            <p class="text-gray-600 dark:text-gray-400">Gérer les bons de commande clients</p>
        </div>
        
        <div class="space-y-4">
            <!-- Filters Row -->
            <div class="flex flex-wrap items-end gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <!-- Date From -->
                <div class="min-w-[140px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de</label>
                    <input 
                        type="date" 
                        x-model="filters.dateFrom"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Date To -->
                <div class="min-w-[140px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date au</label>
                    <input 
                        type="date" 
                        x-model="filters.dateTo"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                
                <!-- Status Filter -->
                <div class="min-w-[160px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                    <select 
                        x-model="filters.statut"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="En attente">En attente</option>
                        <option value="Validé">Validé</option>
                        <option value="Converti">Converti</option>
                        <option value="Annulé">Annulé</option>
                    </select>
                </div>
                
                <!-- Client Filter -->
                <div class="min-w-[180px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                    <select 
                        x-model="filters.clientId"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->raison_sociale }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Ville de Livraison Filter -->
                <div class="min-w-[180px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville de livraison</label>
                    <select 
                        x-model="filters.villeLivraison"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Toutes les villes</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Reset Filters Button -->
                <div class="flex items-end">
                    <button 
                        @click="resetFilters()"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm"
                        title="Réinitialiser les filtres"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
                
                <!-- Spacer -->
                <div class="flex-1"></div>
                
                <!-- New Bon de Commande Button -->
                @can('ventes.bon-commande.create')
                <div class="flex items-end">
                    <button @click="$dispatch('open-new-form')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Nouveau bon de commande
                    </button>
                </div>
                @endcan
            </div>
            
            <!-- Print Bon de Charge Button -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <button 
                        @click="printBonDeCharge()"
                        :disabled="selectedItems.length === 0"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimer bon de charge
                        <span x-show="selectedItems.length > 0" class="ml-1 bg-white text-green-600 rounded-full px-2 py-0.5 text-xs font-semibold" x-text="selectedItems.length"></span>
                    </button>
                </div>
                <div x-show="selectedItems.length > 0" class="text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="selectedItems.length"></span> élément(s) sélectionné(s)
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <input 
                                    type="checkbox" 
                                    @change="toggleSelectAll($event)"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N°</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Téléphone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($bonCommandes as $bonCommande)
                            <tr 
                                x-show="filterRow('{{ $bonCommande->date->format('Y-m-d') }}', '{{ $bonCommande->statut }}', '{{ (string)$bonCommande->client_id }}', '{{ addslashes($bonCommande->ville_livraison ?? '') }}')"
                                x-transition
                                class="bon-commande-row"
                                data-date="{{ $bonCommande->date->format('Y-m-d') }}"
                                data-statut="{{ $bonCommande->statut }}"
                                data-client-id="{{ $bonCommande->client_id }}"
                                data-ville-livraison="{{ $bonCommande->ville_livraison ?? '' }}"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input 
                                        type="checkbox" 
                                        value="{{ $bonCommande->id }}"
                                        @change="toggleItem({{ $bonCommande->id }}, $event)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonCommande->numero_bon }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonCommande->client->raison_sociale }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonCommande->client->telephone ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonCommande->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($bonCommande->total_general, 2, ',', ' ') }} DH</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($bonCommande->statut === 'Validé') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($bonCommande->statut === 'Converti') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                        @elseif($bonCommande->statut === 'Annulé') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @endif">
                                        {{ $bonCommande->statut }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- View Details Icon -->
                                        <button onclick="viewBonCommandeDetails({{ $bonCommande->id }})" class="text-cyan-600 hover:text-cyan-900 dark:text-cyan-400 dark:hover:text-cyan-200" title="Voir détails">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="printBonCommande({{ $bonCommande->id }})" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200" title="Imprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </button>
                                        @if($bonCommande->statut === 'En attente')
                                            @can('ventes.bon-commande.edit')
                                            <button onclick="editBonCommande({{ $bonCommande->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200" title="Modifier">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            @endcan
                                            @can('ventes.bon-commande.validate')
                                            <button onclick="validateBonCommande({{ $bonCommande->id }}, '{{ $bonCommande->numero_bon }}')" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200" title="Valider">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                            @endcan
                                            @can('ventes.bon-commande.delete')
                                            <button onclick="deleteBonCommande({{ $bonCommande->id }}, '{{ $bonCommande->numero_bon }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200" title="Supprimer">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            @endcan
                                        @elseif($bonCommande->statut === 'Validé')
                                            @can('ventes.bon-commande.convert')
                                            <button onclick="convertToBonLivraison({{ $bonCommande->id }}, '{{ $bonCommande->numero_bon }}')" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-200" title="Convertir en Bon de Livraison">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                </svg>
                                            </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Aucun bon de commande trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- No results message -->
            <div x-show="getVisibleRowsCount() === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                Aucun bon de commande ne correspond aux critères de recherche
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="viewDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" onclick="closeViewDetailsModal()"></div>
            <div class="relative inline-block bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full z-10">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Détails du bon de commande
                        </h3>
                        <button onclick="closeViewDetailsModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="viewDetailsContent" class="mt-4">
                        <!-- Content will be loaded here -->
                        <div class="flex justify-center items-center py-8">
                            <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="ml-2 text-gray-600 dark:text-gray-400">Chargement...</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button onclick="closeViewDetailsModal()" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function bonCommandeListApp() {
    return {
        filters: {
            dateFrom: '',
            dateTo: '',
            statut: '',
            clientId: '',
            villeLivraison: ''
        },
        selectedItems: [],
        
        filterRow(rowDate, rowStatut, rowClientId, rowVilleLivraison) {
            // Date from filter
            if (this.filters.dateFrom && rowDate < this.filters.dateFrom) {
                return false;
            }
            
            // Date to filter
            if (this.filters.dateTo && rowDate > this.filters.dateTo) {
                return false;
            }
            
            // Status filter
            if (this.filters.statut && rowStatut !== this.filters.statut) {
                return false;
            }
            
            // Client filter - convert both to strings for comparison
            if (this.filters.clientId && String(rowClientId) !== String(this.filters.clientId)) {
                return false;
            }
            
            // Ville de livraison filter
            if (this.filters.villeLivraison && rowVilleLivraison !== this.filters.villeLivraison) {
                return false;
            }
            
            return true;
        },
        
        resetFilters() {
            this.filters.dateFrom = '';
            this.filters.dateTo = '';
            this.filters.statut = '';
            this.filters.clientId = '';
            this.filters.villeLivraison = '';
        },
        
        toggleItem(id, event) {
            if (event.target.checked) {
                if (!this.selectedItems.includes(id)) {
                    this.selectedItems.push(id);
                }
            } else {
                this.selectedItems = this.selectedItems.filter(item => item !== id);
            }
        },
        
        toggleSelectAll(event) {
            const checkboxes = document.querySelectorAll('.bon-commande-row input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked !== event.target.checked) {
                    checkbox.checked = event.target.checked;
                    const id = parseInt(checkbox.value);
                    if (event.target.checked) {
                        if (!this.selectedItems.includes(id)) {
                            this.selectedItems.push(id);
                        }
                    } else {
                        this.selectedItems = this.selectedItems.filter(item => item !== id);
                    }
                }
            });
        },
        
        printBonDeCharge() {
            if (this.selectedItems.length === 0) {
                alert('Veuillez sélectionner au moins un bon de commande');
                return;
            }
            
            // Create a form to submit the selected items
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("ventes.bon-commande.print-charge") }}';
            form.target = '_blank';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add selected items
            this.selectedItems.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'bon_commande_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },
        
        getVisibleRowsCount() {
            const rows = document.querySelectorAll('.bon-commande-row');
            let visibleCount = 0;
            rows.forEach(row => {
                if (row.style.display !== 'none' && !row.hasAttribute('hidden')) {
                    visibleCount++;
                }
            });
            return visibleCount;
        }
    };
}

function viewBonCommandeDetails(id) {
    const modal = document.getElementById('viewDetailsModal');
    const content = document.getElementById('viewDetailsContent');
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Show loading
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600 dark:text-gray-400">Chargement...</span>
        </div>
    `;
    
    // Fetch details
    fetch(`/ventes/bon-commande/${id}`)
        .then(response => response.json())
        .then(data => {
            const date = data.date ? new Date(data.date).toLocaleDateString('fr-FR') : '-';
            const echeance = data.echeance ? new Date(data.echeance).toLocaleDateString('fr-FR') : '-';
            
            let articlesHtml = '';
            if (data.articles && data.articles.length > 0) {
                data.articles.forEach((article, index) => {
                    articlesHtml += `
                        <tr class="${index % 2 === 0 ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800'}">
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${article.code_article || '-'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${article.designation || '-'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-center">${article.quantite || 0}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-right">${formatCurrency(article.prix_unitaire)} DH</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-right">${formatCurrency(article.sous_total)} DH</td>
                        </tr>
                    `;
                });
            } else {
                articlesHtml = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Aucun article</td></tr>';
            }
            
            let statutClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
            if (data.statut === 'Validé') {
                statutClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            } else if (data.statut === 'Converti') {
                statutClass = 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
            } else if (data.statut === 'Annulé') {
                statutClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            }
            
            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Header Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">N° Bon de commande</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${data.numero_bon || '-'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${date}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Statut</p>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statutClass}">${data.statut || '-'}</span>
                        </div>
                    </div>
                    
                    <!-- Client Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Client</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${data.client ? data.client.raison_sociale : '-'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Mode de paiement</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${data.mode_paiement || '-'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ville de livraison</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${data.ville_livraison || '-'}</p>
                        </div>
                    </div>
                    
                    <!-- Articles Table -->
                    <div>
                        <h4 class="text-md font-semibold text-gray-800 dark:text-white mb-3">Articles</h4>
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-600">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Désignation</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qté</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">P.U. TTC</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sous-Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    ${articlesHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Totals -->
                    <div class="flex justify-end">
                        <div class="w-full md:w-1/3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total Quantités</span>
                                <span class="font-semibold text-gray-900 dark:text-white">${data.total_quantites || 0}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-base font-semibold text-gray-800 dark:text-gray-200">Total Général TTC</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">${formatCurrency(data.total_general)} DH</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <p>Erreur lors du chargement des détails</p>
                    <p class="text-sm mt-2">${error.message}</p>
                </div>
            `;
        });
}

function closeViewDetailsModal() {
    document.getElementById('viewDetailsModal').classList.add('hidden');
}

function formatCurrency(value) {
    if (value === null || value === undefined) return '0,00';
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}
</script>
@endsection
