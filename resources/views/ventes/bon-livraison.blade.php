@extends('layouts.app')

@section('title', 'Bon de Livraison - Bouyahya')

@section('content')
<script>
    window.bonLivraisonArticles = @json($articles ?? []);
</script>

<div x-data="bonLivraisonApp()" class="space-y-6">

<script>
function bonLivraisonApp() {
    return {
        showForm: false,
        isSubmitting: false,
        editMode: false,
        editingId: null,
        formData: {
            date: new Date().toISOString().split('T')[0],
            numero: '',
            clientId: '',
            bonCommandeId: null,
            bonAchatFournisseurId: null,
            bonCommandeNumero: '',
            villeLivraison: '',
            modePaiement: 'Espèces',
            modeReglement: 'Crédit',
            delaiReglement: '0 Jours',
            transporteur: '',
            commercial: 'Commercial',
            situation: 'Livré',
            vehicule: '',
            echeance: '',
            dateEcheance: '',
            chauffeur: '',
            matriculeVehicule: '',
            observations: ''
        },
        items: [],
        articles: window.bonLivraisonArticles || [],
        searchArticle: '',
        filteredArticles: [],
        showImportModal: false,
        importType: '', // 'bon-commande' or 'bon-achat-fournisseur'
        availableBonCommandes: [],
        availableBonAchatFournisseurs: [],
        
        async init() {
            await this.fetchNextNumero();
        },
        
        async openImportModal(type) {
            this.importType = type;
            this.showImportModal = true;
            
            if (type === 'bon-commande') {
                try {
                    const response = await fetch('{{ route('ventes.bon-livraison.bon-commandes') }}');
                    this.availableBonCommandes = await response.json();
                } catch (error) {
                    console.error('Error fetching bon commandes:', error);
                    alert('Erreur lors du chargement des bons de commande');
                }
            } else if (type === 'bon-achat-fournisseur') {
                try {
                    const response = await fetch('{{ route('ventes.bon-livraison.bon-achat-fournisseurs') }}');
                    this.availableBonAchatFournisseurs = await response.json();
                } catch (error) {
                    console.error('Error fetching bon achat fournisseurs:', error);
                    alert('Erreur lors du chargement des bons d\'achat fournisseur');
                }
            }
        },
        
        closeImportModal() {
            this.showImportModal = false;
            this.importType = '';
            this.availableBonCommandes = [];
            this.availableBonAchatFournisseurs = [];
        },
        
        async importBonCommande(id) {
            try {
                const response = await fetch(`/ventes/bon-livraison/import-bon-commande/${id}`);
                const data = await response.json();
                
                this.formData.bonCommandeId = data.bon_commande_id;
                this.formData.bonCommandeNumero = data.numero_bon_commande;
                this.formData.clientId = data.client_id;
                this.formData.modePaiement = data.mode_paiement || 'Espèces';
                this.formData.echeance = data.echeance || '';
                this.formData.villeLivraison = data.ville_livraison || '';
                this.items = (data.items || []).map(item => ({
                    ...item,
                    quantite: parseFloat(item.quantite) || 0,
                    prix_unitaire: parseFloat(item.prix_unitaire) || 0,
                    sous_total: (parseFloat(item.quantite) || 0) * (parseFloat(item.prix_unitaire) || 0)
                }));
                
                this.closeImportModal();
                alert('Bon de commande importé avec succès!');
            } catch (error) {
                console.error('Error importing bon commande:', error);
                alert('Erreur lors de l\'importation du bon de commande');
            }
        },
        
        async importBonAchatFournisseur(id) {
            try {
                const response = await fetch(`/ventes/bon-livraison/import-bon-achat-fournisseur/${id}`);
                const data = await response.json();
                
                this.formData.bonAchatFournisseurId = data.bon_achat_fournisseur_id;
                this.formData.bonCommandeNumero = data.numero_bon_achat;
                if (data.numero_bon_commande) {
                    this.formData.bonCommandeNumero = data.numero_bon_commande;
                }
                this.formData.modePaiement = data.mode_paiement || 'Espèces';
                this.formData.echeance = data.echeance || '';
                this.formData.villeLivraison = data.ville_livraison || '';
                this.items = (data.items || []).map(item => ({
                    ...item,
                    quantite: parseFloat(item.quantite) || 0,
                    prix_unitaire: parseFloat(item.prix_unitaire) || 0,
                    sous_total: (parseFloat(item.quantite) || 0) * (parseFloat(item.prix_unitaire) || 0)
                }));
                
                this.closeImportModal();
                alert('Bon d\'achat fournisseur importé avec succès!');
            } catch (error) {
                console.error('Error importing bon achat fournisseur:', error);
                alert('Erreur lors de l\'importation du bon d\'achat fournisseur');
            }
        },
        
        async fetchNextNumero() {
            try {
                const response = await fetch('{{ route('ventes.bon-livraison.next-numero') }}');
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
            const quantite = parseFloat(item.quantite) || 0;
            const prixUnitaire = parseFloat(item.prix_unitaire) || 0;
            item.sous_total = quantite * prixUnitaire;
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
                const response = await fetch('{{ route('ventes.bon-livraison.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        date: this.formData.date,
                        numero_bon: this.formData.numero,
                        client_id: this.formData.clientId,
                        bon_commande_id: this.formData.bonCommandeId,
                        bon_achat_fournisseur_id: this.formData.bonAchatFournisseurId,
                        mode_paiement: this.formData.modePaiement,
                        mode_reglement: this.formData.modeReglement,
                        delai_reglement: this.formData.delaiReglement,
                        transporteur: this.formData.transporteur,
                        commercial: this.formData.commercial,
                        situation: this.formData.situation,
                        vehicule: this.formData.vehicule,
                        echeance: this.formData.echeance || null,
                        date_echeance: this.formData.dateEcheance || null,
                        ville_livraison: this.formData.villeLivraison,
                        chauffeur: this.formData.chauffeur,
                        matricule_vehicule: this.formData.matriculeVehicule,
                        observations: this.formData.observations,
                        items: this.items
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Bon de livraison créé avec succès!');
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
        
        loadBonLivraisonForEdit(id) {
            fetch(`/ventes/bon-livraison/${id}`)
                .then(response => response.json())
                .then(data => {
                    this.editMode = true;
                    this.editingId = id;
                    this.showForm = true;
                    this.formData.date = data.date ? data.date.split('T')[0] : '';
                    this.formData.numero = data.numero_bon;
                    this.formData.clientId = data.client_id;
                    this.formData.bonCommandeId = data.bon_commande_id;
                    this.formData.bonAchatFournisseurId = data.bon_achat_fournisseur_id;
                    this.formData.bonCommandeNumero = data.bon_commande ? data.bon_commande.numero_bon : (data.bon_achat_fournisseur ? data.bon_achat_fournisseur.numero_bon : '');
                    this.formData.modePaiement = data.mode_paiement;
                    this.formData.modeReglement = data.mode_reglement || 'Crédit';
                    this.formData.delaiReglement = data.delai_reglement || '0 Jours';
                    this.formData.transporteur = data.transporteur || '';
                    this.formData.commercial = data.commercial || 'Commercial';
                    this.formData.situation = data.situation || 'Livré';
                    this.formData.vehicule = data.vehicule || '';
                    this.formData.echeance = data.echeance || '';
                    this.formData.dateEcheance = data.date_echeance ? data.date_echeance.split('T')[0] : '';
                    this.formData.villeLivraison = data.ville_livraison || '';
                    this.formData.chauffeur = data.chauffeur || '';
                    this.formData.matriculeVehicule = data.matricule_vehicule || '';
                    this.formData.observations = data.observations || '';
                    this.items = data.articles.map(article => {
                        const quantite = parseFloat(article.quantite) || 0;
                        const prixUnitaire = parseFloat(article.prix_unitaire) || 0;
                        return {
                            code_article: article.code_article,
                            designation: article.designation,
                            quantite: quantite,
                            prix_unitaire: prixUnitaire,
                            sous_total: quantite * prixUnitaire
                        };
                    });
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
                const response = await fetch(`/ventes/bon-livraison/${this.editingId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        date: this.formData.date,
                        numero_bon: this.formData.numero,
                        client_id: this.formData.clientId,
                        bon_commande_id: this.formData.bonCommandeId,
                        bon_achat_fournisseur_id: this.formData.bonAchatFournisseurId,
                        mode_paiement: this.formData.modePaiement,
                        mode_reglement: this.formData.modeReglement,
                        delai_reglement: this.formData.delaiReglement,
                        transporteur: this.formData.transporteur,
                        commercial: this.formData.commercial,
                        situation: this.formData.situation,
                        vehicule: this.formData.vehicule,
                        echeance: this.formData.echeance || null,
                        date_echeance: this.formData.dateEcheance || null,
                        ville_livraison: this.formData.villeLivraison,
                        chauffeur: this.formData.chauffeur,
                        matricule_vehicule: this.formData.matriculeVehicule,
                        observations: this.formData.observations,
                        items: this.items
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Bon de livraison modifié avec succès!');
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
            this.formData.bonCommandeId = null;
            this.formData.bonAchatFournisseurId = null;
            this.formData.bonCommandeNumero = '';
            this.formData.villeLivraison = '';
            this.formData.modePaiement = 'Espèces';
            this.formData.modeReglement = 'Crédit';
            this.formData.delaiReglement = '0 Jours';
            this.formData.transporteur = '';
            this.formData.commercial = 'Commercial';
            this.formData.situation = 'Livré';
            this.formData.vehicule = '';
            this.formData.echeance = '';
            this.formData.dateEcheance = '';
            this.formData.chauffeur = '';
            this.formData.matriculeVehicule = '';
            this.formData.observations = '';
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
function editBonLivraison(id) {
    const component = Alpine.$data(document.querySelector('[x-data]'));
    component.loadBonLivraisonForEdit(id);
}

function deleteBonLivraison(id, numero) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le bon de livraison ${numero} ?`)) {
        return;
    }

    fetch(`/ventes/bon-livraison/${id}`, {
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

function printBonLivraison(id) {
    window.open(`/ventes/bon-livraison/${id}/print`, '_blank');
}

function markAsDelivered(id, numero) {
    if (!confirm(`Êtes-vous sûr de vouloir marquer le bon de livraison ${numero} comme livré ?`)) {
        return;
    }

    fetch(`/ventes/bon-livraison/${id}/mark-delivered`, {
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
            alert(data.error || data.message || 'Erreur');
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
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white" x-text="editMode ? 'Modifier bon de livraison' : 'Nouveau bon de livraison'"></h2>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">N° Bon de Livraison</label>
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

        <!-- Import Section -->
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-3">Importer depuis</h3>
            <div class="flex gap-3">
                <button @click="openImportModal('bon-commande')" type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Bon de Commande Vente
                </button>
                <button @click="openImportModal('bon-achat-fournisseur')" type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Bon d'Achat Fournisseur
                </button>
            </div>
            <div x-show="formData.bonCommandeNumero" class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                <span class="font-medium">Bon de Commande:</span> <span class="text-blue-600 dark:text-blue-400 font-mono" x-text="formData.bonCommandeNumero"></span>
            </div>
        </div>

        <!-- Informations du bon de livraison -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-4">Informations du bon de livraison</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bon de Commande</label>
                        <input type="text" x-model="formData.bonCommandeNumero" readonly class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode Règlement</label>
                        <select x-model="formData.modeReglement" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="Crédit">Crédit</option>
                            <option value="Espèces">Espèces</option>
                            <option value="Chèque">Chèque</option>
                            <option value="Virement">Virement</option>
                            <option value="Carte bancaire">Carte bancaire</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Délai de Règlement</label>
                        <input type="text" x-model="formData.delaiReglement" placeholder="0 Jours" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transporteur</label>
                        <input type="text" x-model="formData.transporteur" placeholder="Nom du transporteur" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Commercial</label>
                        <select x-model="formData.commercial" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="Commercial">Commercial</option>
                            <option value="Commercial 1">Commercial 1</option>
                            <option value="Commercial 2">Commercial 2</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Situation</label>
                        <select x-model="formData.situation" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="Livré">Livré</option>
                            <option value="En attente">En attente</option>
                            <option value="Annulé">Annulé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Véhicule</label>
                        <input type="text" x-model="formData.vehicule" placeholder="Numéro du véhicule" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
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
                <input type="date" x-model="formData.dateEcheance" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Chauffeur Information -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Informations Livraison
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom du Chauffeur</label>
                    <input type="text" x-model="formData.chauffeur" placeholder="Nom complet" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Matricule Véhicule</label>
                    <input type="text" x-model="formData.matriculeVehicule" placeholder="Ex: 12345-A-12" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
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
                                <span class="text-green-600 dark:text-green-400 font-semibold" x-text="article.prix_vente + ' DH'"></span>
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
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="(parseFloat(item.sous_total || 0)).toFixed(2) + ' DH'"></td>
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

        <!-- Observations -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observations</label>
            <textarea x-model="formData.observations" rows="2" placeholder="Notes ou instructions spéciales..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
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

    <!-- Import Modal -->
    <div x-show="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeImportModal()">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white" x-text="importType === 'bon-commande' ? 'Importer depuis Bon de Commande Vente' : 'Importer depuis Bon d\'Achat Fournisseur'"></h3>
                <button @click="closeImportModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                <div x-show="importType === 'bon-commande'">
                    <template x-for="bonCommande in availableBonCommandes" :key="bonCommande.id">
                        <div @click="importBonCommande(bonCommande.id)" class="p-4 mb-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white" x-text="bonCommande.numero_bon"></div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400" x-text="bonCommande.client ? bonCommande.client.raison_sociale : ''"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-500" x-text="new Date(bonCommande.date).toLocaleDateString('fr-FR')"></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-blue-600 dark:text-blue-400" x-text="parseFloat(bonCommande.total_general).toFixed(2) + ' DH'"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-500" x-text="bonCommande.statut"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="availableBonCommandes.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                        Aucun bon de commande disponible
                    </div>
                </div>
                
                <div x-show="importType === 'bon-achat-fournisseur'">
                    <template x-for="bonAchat in availableBonAchatFournisseurs" :key="bonAchat.id">
                        <div @click="importBonAchatFournisseur(bonAchat.id)" class="p-4 mb-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white" x-text="bonAchat.numero_bon"></div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400" x-text="bonAchat.fournisseur ? bonAchat.fournisseur.nom_fournisseur : ''"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-500" x-text="new Date(bonAchat.date).toLocaleDateString('fr-FR')"></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-green-600 dark:text-green-400" x-text="parseFloat(bonAchat.total_ttc).toFixed(2) + ' DH'"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-500" x-text="bonAchat.statut"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="availableBonAchatFournisseurs.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                        Aucun bon d'achat fournisseur disponible
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div x-show="!showForm" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Bon de livraison</h2>
            <p class="text-gray-600 dark:text-gray-400">Gérer les bons de livraison clients</p>
        </div>
        
        <div class="space-y-4">
            <div class="flex justify-end">
                <button @click="openNewForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Nouveau bon de livraison
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N°</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Réf. Commande</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Chauffeur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($bonLivraisons as $bonLivraison)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonLivraison->numero_bon }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($bonLivraison->bonCommande)
                                        <span class="px-2 py-1 text-xs font-mono bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded">{{ $bonLivraison->bonCommande->numero_bon }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonLivraison->client->raison_sociale }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if($bonLivraison->chauffeur)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $bonLivraison->chauffeur }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Non assigné</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bonLivraison->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($bonLivraison->total_general, 2, ',', ' ') }} DH</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($bonLivraison->statut === 'Livré') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($bonLivraison->statut === 'Annulé') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @endif">
                                        {{ $bonLivraison->statut }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="printBonLivraison({{ $bonLivraison->id }})" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200" title="Imprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </button>
                                        @if($bonLivraison->statut === 'En attente')
                                            <button onclick="editBonLivraison({{ $bonLivraison->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200" title="Modifier">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="markAsDelivered({{ $bonLivraison->id }}, '{{ $bonLivraison->numero_bon }}')" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200" title="Marquer comme livré">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteBonLivraison({{ $bonLivraison->id }}, '{{ $bonLivraison->numero_bon }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200" title="Supprimer">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Aucun bon de livraison trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
