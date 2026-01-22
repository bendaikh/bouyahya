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
        viewMode: false,
        editingId: null,
        showPaymentModal: false,
        hasPayment: false,
        paymentData: {
            tresorerieId: '',
            modeReglement: 'Espèces',
            montant: 0,
            reference: '',
            banque: '',
            echeance: '',
            nomTire: '',
            totalAVentiler: false
        },
        paymentLines: [],
        banques: [],
        tresoreries: [],
        formData: {
            date: new Date().toISOString().split('T')[0],
            numero: '',
            clientId: '',
            codeClient: '',
            nomClient: '',
            plafondAutorise: '',
            typeReglement: 'Crédit',
            commercial: '',
            echeance: '',
            fournisseurId: '',
            codeFournisseur: '',
            nomFournisseur: '',
            bonFournisseurNumero: '',
            chauffeur: '',
            matriculeVehicule: '',
            bonCommandeId: null,
            bonAchatFournisseurId: null,
            bonCommandeNumero: '',
            villeLivraison: '',
            modePaiement: 'Espèces',
            modeReglement: 'Crédit',
            delaiReglement: '0 Jours',
            transporteur: '',
            situation: 'Livré',
            vehicule: '',
            dateEcheance: '',
            observations: ''
        },
        items: [],
        articles: window.bonLivraisonArticles || [],
        searchArticle: '',
        filteredArticles: [],
        clientSearch: '',
        showClientDropdown: false,
        filteredClients: [],
        showImportModal: false,
        importType: '', // 'bon-commande' or 'bon-achat-fournisseur'
        availableBonCommandes: [],
        availableBonAchatFournisseurs: [],
        searchImport: '',
        commerciales: [],
        transports: [],
        matricules: [],
        typesReglement: [],
        echeancesOptions: [],
        bonLivraisonsList: [],
        filteredBonLivraisonsList: [],
        searchFilters: {
            annee: new Date().getFullYear().toString(),
            mois: '',
            code: '',
            nomClient: '',
            commercial: '',
            ville: ''
        },
        showStatusModal: false,
        selectedBonLivraisonId: null,
        selectedBonLivraisonNumero: '',
        availableStatuses: ['En attente', 'Livré', 'Annulé'],
        openStatusDropdownId: null,

        formatDate(dateStr) {
            if (!dateStr) return '';
            try {
                const d = new Date(dateStr);
                if (Number.isNaN(d.getTime())) return dateStr;
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                return `${day}/${month}/${year}`;
            } catch (e) {
                return dateStr;
            }
        },

        statutBadgeClass(statut) {
            const s = (statut || '').toLowerCase();
            if (s === 'validé' || s === 'valide') return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            if (s === 'annulé' || s === 'annule') return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            if (s === 'converti') return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        },
        
        async init() {
            // Initialize bon livraisons list, filtering out invalid entries
            const rawData = @json($bonLivraisons);
            this.bonLivraisonsList = rawData.filter(bl => bl && bl.numero_bon);
            this.filteredBonLivraisonsList = [...this.bonLivraisonsList];
            
            // Initialize clients list - filter out any null/undefined entries
            const rawClients = @json($clients);
            this.clients = rawClients.filter(c => c && c.id && (c.code_client || c.raison_sociale));
            this.filteredClients = this.clients;
            
            await this.fetchNextNumero();
            await this.fetchCommerciales();
            await this.fetchTransports();
            await this.fetchMatricules();
            await this.fetchBanques();
            await this.fetchTresoreries();
            await this.fetchTypesReglement();
            await this.fetchEcheancesOptions();
        },

        async fetchBanques() {
            try {
                const response = await fetch('/api/settings/banques');
                const data = await response.json();
                this.banques = data.banques || [];
            } catch (error) {
                console.error('Error fetching banques:', error);
            }
        },

        async fetchTresoreries() {
            try {
                const response = await fetch('/api/reglements-clients/tresoreries');
                const data = await response.json();
                this.tresoreries = data || [];
            } catch (error) {
                console.error('Error fetching tresoreries:', error);
            }
        },

        async fetchTypesReglement() {
            try {
                const response = await fetch('/api/settings/types-reglement');
                const data = await response.json();
                this.typesReglement = data.types_reglement || [];
            } catch (error) {
                console.error('Error fetching types reglement:', error);
            }
        },

        async fetchEcheancesOptions() {
            try {
                const response = await fetch('/api/settings/echeances');
                const data = await response.json();
                this.echeancesOptions = data.echeances || [];
            } catch (error) {
                console.error('Error fetching echeances:', error);
            }
        },

        openPaymentModal() {
            if (!this.formData.clientId) {
                alert('Veuillez d\'abord sélectionner un client');
                return;
            }
            if (this.items.length === 0) {
                alert('Veuillez ajouter au moins un article');
                return;
            }
            
            // Set default values if not already set
            if (!this.paymentData.montant || this.paymentData.montant === 0) {
                this.paymentData.montant = this.totalGeneral;
            }
            if (!this.paymentData.nomTire) {
                this.paymentData.nomTire = this.formData.nomClient;
            }
            if (!this.paymentData.echeance) {
                this.paymentData.echeance = this.formData.date;
            }
            
            this.showPaymentModal = true;
        },

        savePaymentData() {
            if (!this.paymentData.tresorerieId && this.paymentData.modeReglement !== 'Crédit') {
                alert('Veuillez sélectionner un compte de trésorerie');
                return;
            }
            // Add to payment lines array
            const newPaymentLine = {
                id: Date.now(),
                reference: this.paymentData.reference || '',
                type: this.paymentData.modeReglement,
                numero: this.paymentData.reference || '',
                montant: this.paymentData.montant,
                banque: this.paymentData.banque || '',
                nomTire: this.paymentData.nomTire || '',
                echeance: this.paymentData.echeance || '',
                tresorerieId: this.paymentData.tresorerieId
            };
            this.paymentLines.push(newPaymentLine);
            this.hasPayment = true;
            this.showPaymentModal = false;
            // Reset payment data for next entry
            this.paymentData = {
                tresorerieId: '',
                modeReglement: 'Espèces',
                montant: 0,
                reference: '',
                banque: '',
                echeance: '',
                nomTire: this.formData.nomClient || '',
                totalAVentiler: false
            };
        },

        removePaymentLine(index) {
            this.paymentLines.splice(index, 1);
            if (this.paymentLines.length === 0) {
                this.hasPayment = false;
            }
        },

        get totalPayments() {
            return this.paymentLines.reduce((sum, line) => sum + parseFloat(line.montant || 0), 0);
        },
        
        searchClients() {
            if (!this.clientSearch || this.clientSearch.length < 1) {
                this.filteredClients = this.clients.filter(c => c && c.id && (c.code_client || c.raison_sociale));
                return;
            }
            const search = this.clientSearch.toLowerCase();
            this.filteredClients = this.clients.filter(c => {
                if (!c || !c.id) return false;
                const codeClient = (c.code_client || '').toLowerCase();
                const raisonSociale = (c.raison_sociale || '').toLowerCase();
                return codeClient.includes(search) || raisonSociale.includes(search);
            });
        },
        
        selectClient(client) {
            this.formData.clientId = client.id;
            this.clientSearch = `${client.code_client} - ${client.raison_sociale || ''}`;
            this.showClientDropdown = false;
            this.updateClientInfo();
        },
        
        closeClientDropdown() {
            setTimeout(() => {
                this.showClientDropdown = false;
            }, 200);
        },
        
        async fetchCommerciales() {
            try {
                const response = await fetch('/api/settings/commerciales');
                const data = await response.json();
                // Handle both old format (strings) and new format (objects)
                const rawCommerciales = data.commerciales || [];
                this.commerciales = rawCommerciales.map(c => {
                    if (typeof c === 'string') {
                        // Legacy format - return as is for backward compatibility
                        return c;
                    }
                    // New format - return nom_commercial for display
                    return c.nom_commercial || c.code_commercial || '';
                }).filter(c => c);
                // Set default commercial if available
                if (this.commerciales.length > 0 && !this.formData.commercial) {
                    this.formData.commercial = this.commerciales[0];
                }
            } catch (error) {
                console.error('Error fetching commerciales:', error);
            }
        },
        
        async fetchTransports() {
            try {
                const response = await fetch('/api/settings/transports');
                const data = await response.json();
                this.transports = data.transports || [];
            } catch (error) {
                console.error('Error fetching transports:', error);
            }
        },
        
        async fetchMatricules() {
            try {
                const response = await fetch('/api/settings/matricules');
                const data = await response.json();
                this.matricules = data.matricules || [];
            } catch (error) {
                console.error('Error fetching matricules:', error);
            }
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
            this.searchImport = '';
        },
        
        get filteredBonCommandes() {
            if (!this.searchImport) {
                return this.availableBonCommandes;
            }
            const search = this.searchImport.toLowerCase();
            return this.availableBonCommandes.filter(bc => 
                bc.numero_bon?.toLowerCase().includes(search) ||
                bc.client?.raison_sociale?.toLowerCase().includes(search) ||
                bc.fournisseur?.nom_fournisseur?.toLowerCase().includes(search) ||
                (bc.statut || '').toLowerCase().includes(search)
            );
        },
        
        get filteredBonAchatFournisseurs() {
            if (!this.searchImport) {
                return this.availableBonAchatFournisseurs;
            }
            const search = this.searchImport.toLowerCase();
            return this.availableBonAchatFournisseurs.filter(ba => 
                ba.numero_bon?.toLowerCase().includes(search) ||
                ba.fournisseur?.nom_fournisseur?.toLowerCase().includes(search) ||
                (ba.client_livre || '').toLowerCase().includes(search) ||
                (ba.statut || '').toLowerCase().includes(search)
            );
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
                this.updateClientInfo();
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
                this.formData.bonFournisseurNumero = data.numero_bon_achat;
                this.formData.bonCommandeNumero = data.numero_bon_achat;
                if (data.numero_bon_commande) {
                    this.formData.bonCommandeNumero = data.numero_bon_commande;
                }
                this.formData.modePaiement = data.mode_paiement || 'Espèces';
                this.formData.echeance = data.echeance || '';
                this.formData.villeLivraison = data.ville_livraison || '';
                // Set fournisseur fields from imported data
                this.formData.fournisseurId = data.fournisseur_id ? String(data.fournisseur_id) : '';
                this.formData.codeFournisseur = data.code_fournisseur || '';
                this.formData.nomFournisseur = data.nom_fournisseur || '';
                this.formData.typeReglement = data.type_reglement || data.mode_paiement || '';
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
        
        updateClientInfo() {
            if (!this.formData.clientId) {
                this.formData.codeClient = '';
                this.formData.nomClient = '';
                this.formData.plafondAutorise = '';
                return;
            }
            const client = this.clients.find(c => c.id == this.formData.clientId);
            if (client) {
                this.formData.codeClient = client.code_client || '';
                this.formData.nomClient = client.raison_sociale || '';
                this.formData.plafondAutorise = client.plafond ? parseFloat(client.plafond).toFixed(2) + ' DH' : '';
                if (!this.clientSearch || !this.clientSearch.includes(client.code_client)) {
                    this.clientSearch = `${client.code_client} - ${client.raison_sociale || ''}`;
                }
            }
        },
        
        updateFournisseurInfo() {
            if (!this.formData.fournisseurId) {
                this.formData.codeFournisseur = '';
                this.formData.nomFournisseur = '';
                return;
            }
            const fournisseur = @json($fournisseurs ?? []).find(f => f.id == this.formData.fournisseurId);
            if (fournisseur) {
                this.formData.codeFournisseur = fournisseur.code_fournisseur || '';
                this.formData.nomFournisseur = fournisseur.nom_fournisseur || '';
            }
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
                        fournisseur_id: this.formData.fournisseurId || null,
                        code_fournisseur: this.formData.codeFournisseur || null,
                        nom_fournisseur: this.formData.nomFournisseur || null,
                        bon_fournisseur_numero: this.formData.bonFournisseurNumero || null,
                        mode_paiement: this.formData.modePaiement,
                        mode_reglement: this.formData.modeReglement,
                        type_reglement: this.formData.typeReglement || null,
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
                        items: this.items,
                        payment: this.paymentLines.length > 0 ? this.paymentLines[0] : null,
                        paymentLines: this.paymentLines
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
            return fetch(`/ventes/bon-livraison/${id}`)
                .then(response => response.json())
                .then(data => {
                    this.editMode = true;
                    this.editingId = id;
                    this.showForm = true;
                    this.formData.date = data.date ? data.date.split('T')[0] : '';
                    this.formData.numero = data.numero_bon;
                    this.formData.clientId = data.client_id;
                    const client = this.clients.find(c => c.id == data.client_id);
                    if (client) {
                        this.clientSearch = `${client.code_client} - ${client.raison_sociale || ''}`;
                    }
                    this.updateClientInfo();
                    this.formData.bonCommandeId = data.bon_commande_id;
                    this.formData.bonAchatFournisseurId = data.bon_achat_fournisseur_id;
                    this.formData.bonCommandeNumero = data.bon_commande ? data.bon_commande.numero_bon : (data.bon_achat_fournisseur ? data.bon_achat_fournisseur.numero_bon : '');
                    // Load fournisseur fields - convert to string for dropdown matching
                    this.formData.fournisseurId = data.fournisseur_id ? String(data.fournisseur_id) : '';
                    this.formData.codeFournisseur = data.code_fournisseur || '';
                    this.formData.nomFournisseur = data.nom_fournisseur || '';
                    this.formData.bonFournisseurNumero = data.bon_fournisseur_numero || (data.bon_achat_fournisseur ? data.bon_achat_fournisseur.numero_bon : '');
                    this.formData.modePaiement = data.mode_paiement;
                    this.formData.modeReglement = data.mode_reglement || 'Crédit';
                    this.formData.typeReglement = data.type_reglement || data.mode_reglement || 'Crédit';
                    this.formData.delaiReglement = data.delai_reglement || '0 Jours';
                    this.formData.transporteur = data.transporteur || '';
                    this.formData.commercial = data.commercial || (this.commerciales.length > 0 ? this.commerciales[0] : '');
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

                    // Load payment details
                    this.paymentLines = (data.reglement_lignes || []).map(ligne => {
                        const reg = ligne.reglement;
                        return {
                            id: reg ? reg.id : Date.now() + Math.random(),
                            tresorerieId: reg ? reg.tresorerie_id : '',
                            modeReglement: reg ? reg.type_reglement : '',
                            montant: parseFloat(ligne.montant_regle) || 0,
                            reference: reg ? reg.numero_piece : '',
                            banque: reg ? reg.banque : '',
                            echeance: reg ? (reg.date_encaissement ? reg.date_encaissement.split('T')[0] : '') : '',
                            nomTire: reg ? reg.nom_tire : '',
                            date: reg ? (reg.date_reglement ? reg.date_reglement.split('T')[0] : '') : ''
                        };
                    });
                    this.hasPayment = this.paymentLines.length > 0;
                })
                .catch(error => {
                    alert('Erreur lors du chargement: ' + error.message);
                });
        },
        
        viewBonLivraison(id) {
            this.loadBonLivraisonForEdit(id).then(() => {
                this.editMode = false;
                this.viewMode = true;
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
                        fournisseur_id: this.formData.fournisseurId || null,
                        code_fournisseur: this.formData.codeFournisseur || null,
                        nom_fournisseur: this.formData.nomFournisseur || null,
                        bon_fournisseur_numero: this.formData.bonFournisseurNumero || null,
                        mode_paiement: this.formData.modePaiement,
                        mode_reglement: this.formData.modeReglement,
                        type_reglement: this.formData.typeReglement || null,
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
                        items: this.items,
                        payment: this.paymentLines.length > 0 ? this.paymentLines[0] : null,
                        paymentLines: this.paymentLines
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
            this.viewMode = false;
            this.editingId = null;
            this.showForm = false;
            this.items = [];
            this.formData.clientId = '';
            this.formData.codeClient = '';
            this.formData.nomClient = '';
            this.formData.plafondAutorise = '';
            this.formData.typeReglement = 'Crédit';
            this.formData.commercial = this.commerciales.length > 0 ? this.commerciales[0] : '';
            this.formData.echeance = '';
            this.formData.fournisseurId = '';
            this.formData.codeFournisseur = '';
            this.formData.nomFournisseur = '';
            this.formData.bonFournisseurNumero = '';
            this.formData.chauffeur = '';
            this.formData.matriculeVehicule = '';
            this.formData.transporteur = '';
            this.formData.bonCommandeId = null;
            this.formData.bonAchatFournisseurId = null;
            this.formData.bonCommandeNumero = '';
            this.formData.villeLivraison = '';
            this.formData.modePaiement = 'Espèces';
            this.formData.modeReglement = 'Crédit';
            this.formData.delaiReglement = '0 Jours';
            this.formData.transporteur = '';
            this.formData.situation = 'Livré';
            this.formData.vehicule = '';
            this.formData.dateEcheance = '';
            this.formData.observations = '';
            this.formData.date = new Date().toISOString().split('T')[0];
            this.clientSearch = '';
            this.showClientDropdown = false;
            this.filteredClients = this.clients.filter(c => c && c.id && (c.code_client || c.raison_sociale));
            this.fetchNextNumero();
        },
        
        openNewForm() {
            this.cancelEdit();
            this.showForm = true;
        },
        
        filterBonLivraisons() {
            this.filteredBonLivraisonsList = this.bonLivraisonsList.filter(bl => {
                const annee = this.searchFilters.annee;
                const mois = this.searchFilters.mois.toLowerCase();
                const code = this.searchFilters.code.toLowerCase();
                const nomClient = this.searchFilters.nomClient.toLowerCase();
                const commercial = this.searchFilters.commercial.toLowerCase();
                const ville = this.searchFilters.ville.toLowerCase();
                
                // Check year filter
                if (annee) {
                    const blDate = new Date(bl.date);
                    const blYear = blDate.getFullYear().toString();
                    if (blYear !== annee) {
                        return false;
                    }
                }
                
                // Check month filter
                if (mois) {
                    const blDate = new Date(bl.date);
                    const blMonth = (blDate.getMonth() + 1).toString().padStart(2, '0');
                    if (blMonth !== mois) {
                        return false;
                    }
                }
                
                // Check code filter (client code or bon number)
                if (code && 
                    !(bl.client?.code_client || '').toLowerCase().includes(code) &&
                    !(bl.numero_bon || '').toLowerCase().includes(code)) {
                    return false;
                }
                
                // Check client name filter
                if (nomClient && !(bl.client?.raison_sociale || '').toLowerCase().includes(nomClient)) {
                    return false;
                }
                
                // Check commercial filter
                if (commercial && !(bl.commercial || '').toLowerCase().includes(commercial)) {
                    return false;
                }
                
                // Check ville filter
                if (ville && !(bl.ville_livraison || '').toLowerCase().includes(ville)) {
                    return false;
                }
                
                return true;
            });
        },
        
        printBonLivraisonFunc(id) {
            window.open(`/ventes/bon-livraison/${id}/print`, '_blank');
        },
        
        deleteBonLivraisonFunc(id, numero) {
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
        },
        
        copyBonLivraison(id) {
            this.loadBonLivraisonForEdit(id).then(() => {
                this.editMode = false;
                this.viewMode = false;
                this.editingId = null;
                this.formData.numero = '';
                this.fetchNextNumero();
            });
        },
        
        openStatusModal(id, numero) {
            this.selectedBonLivraisonId = id;
            this.selectedBonLivraisonNumero = numero;
            this.showStatusModal = true;
        },
        
        closeStatusModal() {
            this.showStatusModal = false;
            this.selectedBonLivraisonId = null;
            this.selectedBonLivraisonNumero = '';
        },
        
        toggleStatusDropdown(bonLivraisonId) {
            if (this.openStatusDropdownId === bonLivraisonId) {
                this.openStatusDropdownId = null;
            } else {
                this.openStatusDropdownId = bonLivraisonId;
            }
        },
        
        async updateStatusDirect(bonLivraisonId, newStatus) {
            try {
                const response = await fetch(`/ventes/bon-livraison/${bonLivraisonId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        statut: newStatus
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Update the status in the local list
                    const bonLivraison = this.bonLivraisonsList.find(bl => bl.id === bonLivraisonId);
                    if (bonLivraison) {
                        bonLivraison.statut = newStatus;
                    }
                    // Also update in filtered list
                    const filteredBonLivraison = this.filteredBonLivraisonsList.find(bl => bl.id === bonLivraisonId);
                    if (filteredBonLivraison) {
                        filteredBonLivraison.statut = newStatus;
                    }
                    this.openStatusDropdownId = null;
                } else {
                    alert('Erreur: ' + (data.error || data.message || 'Erreur lors de la mise à jour'));
                }
            } catch (error) {
                alert('Erreur: ' + error.message);
            }
        },
        
        async updateStatus(newStatus) {
            if (!this.selectedBonLivraisonId) return;
            
            try {
                const response = await fetch(`/ventes/bon-livraison/${this.selectedBonLivraisonId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        statut: newStatus
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Update the status in the local list
                    const bonLivraison = this.bonLivraisonsList.find(bl => bl.id === this.selectedBonLivraisonId);
                    if (bonLivraison) {
                        bonLivraison.statut = newStatus;
                    }
                    // Also update in filtered list
                    const filteredBonLivraison = this.filteredBonLivraisonsList.find(bl => bl.id === this.selectedBonLivraisonId);
                    if (filteredBonLivraison) {
                        filteredBonLivraison.statut = newStatus;
                    }
                    
                    this.closeStatusModal();
                    alert('Statut mis à jour avec succès!');
                } else {
                    alert('Erreur: ' + (data.error || data.message || 'Erreur lors de la mise à jour'));
                }
            } catch (error) {
                alert('Erreur: ' + error.message);
            }
        },

        getEtatRowBgClass(bon) {
            const totalTtc = parseFloat(bon.total_general) || 0;
            const montantPaye = parseFloat(bon.montant_paye) || 0;
            
            if (montantPaye >= totalTtc && totalTtc > 0) return ''; // Payé
            if (montantPaye > 0 && montantPaye < totalTtc) return 'bg-orange-100 dark:bg-orange-900/30'; // En cours
            return 'bg-red-100 dark:bg-red-900/30'; // Impayé
        },

        getBonEtat(bon) {
            const totalTtc = parseFloat(bon.total_general) || 0;
            const montantPaye = parseFloat(bon.montant_paye) || 0;
            
            if (montantPaye >= totalTtc && totalTtc > 0) return 'paye';
            if (montantPaye > 0 && montantPaye < totalTtc) return 'encours';
            return 'impaye';
        },

        getEtatText(bon) {
            const etat = this.getBonEtat(bon);
            if (etat === 'paye') return 'Payé';
            if (etat === 'encours') return 'En cours';
            return 'Impayé';
        },

        getEtatClass(bon) {
            const etat = this.getBonEtat(bon);
            if (etat === 'paye') return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            if (etat === 'encours') return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        },
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

function exportToExcel() {
    const component = Alpine.$data(document.querySelector('[x-data]'));
    const data = component.filteredBonLivraisonsList;
    
    // Create CSV content
    let csv = 'N° de Bon,Date,Code Client,Nom Client,Ville,Commercial,Quantité,Montant TTC,Statut\n';
    
    data.forEach(bl => {
        csv += `${bl.numero_bon || ''},`;
        csv += `${component.formatDate(bl.date) || ''},`;
        csv += `${bl.client?.code_client || ''},`;
        csv += `${bl.client?.raison_sociale || ''},`;
        csv += `${bl.ville_livraison || ''},`;
        csv += `${bl.commercial || ''},`;
        csv += `${bl.total_quantites || 0},`;
        csv += `${bl.total_general || 0},`;
        csv += `${bl.statut || 'En attente'}\n`;
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'bon_livraison_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportToPDF() {
    alert('Export PDF sera implémenté prochainement');
    // TODO: Implement PDF export functionality
}
</script>

    <!-- Form Section -->
    <div x-show="showForm" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <fieldset :disabled="viewMode" class="border-0 p-0 m-0 min-w-0 w-full">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6 bg-gray-800 dark:bg-gray-800 p-4 rounded-lg">
            <h2 class="text-xl font-semibold text-white dark:text-white">Nouveau bon de livraison</h2>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-white dark:text-white">Date</label>
                    <input type="date" x-model="formData.date" class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-white dark:text-white">N° Bon de Livraison</label>
                    <input type="text" x-model="formData.numero" readonly class="px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white w-40">
                </div>
                <div class="flex gap-2">
                    <button @click="openPaymentModal()" type="button" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Payer
                    </button>
                    <button type="button" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Envoyer
                    </button>
                </div>
            </div>
        </div>

        </fieldset>
        <!-- Import Section and Action Buttons Row - Outside fieldset so buttons work in view mode -->
        <div class="flex justify-between items-start mb-6">
            <!-- Import Section -->
            <div x-show="!viewMode">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Importateur depuis</p>
                <div class="flex gap-3">
                    <button @click="openImportModal('bon-commande')" type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        Bon de Commande Vente
                    </button>
                    <button @click="openImportModal('bon-achat-fournisseur')" type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        Bon d'Achat Fournisseur
                    </button>
                </div>
            </div>

            <!-- Action Buttons - Positioned on the right -->
            <div class="flex gap-3 ml-auto">
                <button @click="editMode ? updateForm() : submitForm()" type="button" :disabled="isSubmitting" x-show="!viewMode" class="px-5 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold">
                    <span x-show="!isSubmitting">Valider</span>
                    <span x-show="isSubmitting">...</span>
                </button>
                <template x-if="viewMode && !(parseFloat(bonLivraisonsList.find(bl => bl.id === editingId)?.montant_paye || 0) >= parseFloat(bonLivraisonsList.find(bl => bl.id === editingId)?.total_general || 0) && parseFloat(bonLivraisonsList.find(bl => bl.id === editingId)?.total_general || 0) > 0)">
                    <button @click="viewMode = false; editMode = true" type="button" class="px-5 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-semibold">
                        Modifier
                    </button>
                </template>
                <button @click="updateForm()" type="button" x-show="editMode && !viewMode" :disabled="isSubmitting" class="px-5 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold">
                    Modifier
                </button>
                <button @click="deleteBonLivraisonFunc(editingId, formData.numero)" type="button" x-show="(editMode || viewMode) && editingId" class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-semibold">
                    Supprimer
                </button>
                <button @click="cancelEdit()" type="button" class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-semibold">
                    Annuler
                </button>
            </div>
        </div>
        <fieldset :disabled="viewMode" class="border-0 p-0 m-0 min-w-0 w-full">

        <!-- Info Client Livré and Info Livraison Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Info Client Livré Section -->
            <div class="bg-gray-100 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <h3 class="text-sm font-semibold text-amber-600 dark:text-yellow-400 mb-4">Info Client Livré</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Code Client</label>
                        <select x-model="formData.clientId" @change="updateClientInfo()" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->code_client }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom Client</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="clientSearch"
                                @input="searchClients()"
                                @focus="showClientDropdown = true"
                                @blur="closeClientDropdown()"
                                placeholder="Rechercher par code ou nom..."
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm"
                            />
                            <!-- Dropdown -->
                            <div 
                                x-show="showClientDropdown && filteredClients.length > 0"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            >
                                <template x-for="c in filteredClients" :key="c.id">
                                    <div 
                                        x-show="c && c.id && (c.code_client || c.raison_sociale)"
                                        @click="selectClient(c)"
                                        class="px-3 py-2 hover:bg-blue-100 dark:hover:bg-blue-900 cursor-pointer text-sm"
                                    >
                                        <span class="font-medium text-blue-600 dark:text-blue-400" x-text="c.code_client || ''"></span>
                                        <span class="text-gray-600 dark:text-gray-300" x-show="c.raison_sociale"> - <span x-text="c.raison_sociale"></span></span>
                                    </div>
                                </template>
                            </div>
                            <!-- No results -->
                            <div 
                                x-show="showClientDropdown && clientSearch && filteredClients.length === 0"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-3 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Aucun client trouvé
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Plafond Autorisé</label>
                        <input type="text" x-model="formData.plafondAutorise" readonly class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type Réglement</label>
                        <select x-model="formData.typeReglement" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            <template x-for="type in typesReglement" :key="type.id">
                                <option :value="type.libelle" x-text="type.libelle"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Commercial</label>
                        <select x-model="formData.commercial" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            <template x-for="commerciale in commerciales" :key="commerciale">
                                <option :value="commerciale" x-text="commerciale"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Echéance</label>
                        <select x-model="formData.echeance" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            <template x-for="ech in echeancesOptions" :key="ech.id">
                                <option :value="ech.libelle" x-text="ech.libelle"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Info Livraison Section -->
            <div class="bg-gray-100 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <h3 class="text-sm font-semibold text-green-600 dark:text-green-400 mb-4">Info Livraison</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Code Fournisseur</label>
                        <select x-model="formData.fournisseurId" @change="updateFournisseurInfo()" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            @foreach($fournisseurs ?? [] as $fournisseur)
                                <option value="{{ $fournisseur->id }}">{{ $fournisseur->code_fournisseur }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom Fournisseur</label>
                        <select x-model="formData.fournisseurId" @change="updateFournisseurInfo()" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            @foreach($fournisseurs ?? [] as $fournisseur)
                                <option value="{{ $fournisseur->id }}">{{ $fournisseur->nom_fournisseur }} ({{ $fournisseur->code_fournisseur }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bon Fournisseur Numèro</label>
                        <input type="text" x-model="formData.bonFournisseurNumero" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Chauffeur</label>
                        <select x-model="formData.chauffeur" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            <template x-for="transport in transports" :key="transport.id">
                                <option :value="transport.nom + ' ' + transport.prenom" x-text="transport.nom + ' ' + transport.prenom"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Matricule</label>
                        <select x-model="formData.matriculeVehicule" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            <template x-for="matricule in matricules" :key="matricule.id">
                                <option :value="matricule.matricule" x-text="matricule.vehicule_name + ' - ' + matricule.matricule"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transport</label>
                        <select x-model="formData.transporteur" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Sélectionner</option>
                            <option value="DEPART">DEPART</option>
                            <option value="RENDU">RENDU</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détail Commande and Détails Paiement Sections in Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
            <!-- Détail Commande Section (Left - 3 columns) -->
            <div class="lg:col-span-3">
                <h3 class="text-sm font-semibold text-amber-600 dark:text-yellow-400 mb-4">Détail Commande</h3>
                
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
                            class="w-full md:w-80 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        >
                        <div x-show="filteredArticles.length > 0" class="absolute z-50 w-full md:w-80 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
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
                    <!-- Totals Row -->
                    <div class="flex items-center gap-4 mt-3">
                        <span class="text-sm font-semibold text-yellow-500 dark:text-yellow-400">Quantité Total</span>
                        <span class="px-4 py-1 bg-blue-500 rounded-lg text-white font-bold text-sm" x-text="totalQuantites"></span>
                        <span class="text-sm font-semibold text-yellow-500 dark:text-yellow-400">Total TTC</span>
                        <span class="px-4 py-1 bg-yellow-400 rounded-lg text-gray-900 font-bold text-sm" x-text="formatCurrency(totalGeneral) + ' DH'"></span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">CODE ARTICLE</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">DÉSIGNATION</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">QTÉ</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">P.U. TTC</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SOUS-TOTAL</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="px-3 py-2">
                                        <input type="text" x-model="item.code_article" class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" x-model="item.designation" class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" x-model="item.quantite" @input="updateSousTotal(item)" min="1" class="w-16 px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" x-model="item.prix_unitaire" @input="updateSousTotal(item)" min="0" step="0.01" class="w-24 px-2 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm">
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white whitespace-nowrap" x-text="(parseFloat(item.sous_total || 0)).toFixed(2) + ' DH'"></td>
                                    <td class="px-3 py-2">
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
                <div class="mb-4">
                    <button @click="addEmptyItem()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium text-sm flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        + Ajouter une ligne
                    </button>
                </div>
            </div>

            <!-- Détails Paiement Section (Right - 2 columns) -->
            <div class="lg:col-span-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">Détails Paiement</h3>
                
                <div class="bg-gray-100 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-200 dark:bg-gray-600">
                                <tr>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Réf</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">N°</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Banque</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tiré</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Échéance</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(payment, index) in paymentLines" :key="payment.id">
                                    <tr>
                                        <td class="px-2 py-2 text-xs text-gray-900 dark:text-white text-center" x-text="payment.reference || '-'"></td>
                                        <td class="px-2 py-2 text-xs text-gray-900 dark:text-white text-center" x-text="payment.type"></td>
                                        <td class="px-2 py-2 text-xs text-gray-900 dark:text-white text-center" x-text="payment.numero || '-'"></td>
                                        <td class="px-2 py-2 text-xs text-gray-900 dark:text-white text-center font-semibold" x-text="formatCurrency(payment.montant) + ' DH'"></td>
                                        <td class="px-2 py-2 text-xs text-gray-900 dark:text-white text-center" x-text="payment.banque || '-'"></td>
                                        <td class="px-2 py-2 text-xs text-gray-900 dark:text-white text-center" x-text="payment.nomTire || '-'"></td>
                                        <td class="px-2 py-2 text-xs text-gray-900 dark:text-white text-center" x-text="payment.echeance || '-'"></td>
                                        <td class="px-2 py-2">
                                            <button @click="removePaymentLine(index)" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="paymentLines.length === 0">
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">Aucun paiement ajouté</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Add Payment Button -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-600" x-show="!viewMode">
                        <button @click="openPaymentModal()" type="button" class="w-full flex items-center justify-center text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium text-sm py-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            + Ajouter un paiement
                        </button>
                    </div>
                    
                    <!-- Payment Total -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700" x-show="paymentLines.length > 0">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Paiements:</span>
                            <span class="text-sm font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(totalPayments) + ' DH'"></span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Reste à payer:</span>
                            <span class="text-sm font-bold" :class="(totalGeneral - totalPayments) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'" x-text="formatCurrency(Math.max(0, totalGeneral - totalPayments)) + ' DH'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </fieldset>

        <!-- Additional Action Buttons at bottom -->
        <div class="flex justify-end gap-3" x-show="editingId">
            <button @click="window.open(`/ventes/bon-livraison/${editingId}/print`, '_blank')" type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                Imprimer
            </button>
        </div>
    </div>

    <!-- Import Modal - Compact Popup -->
    <div x-show="showImportModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" @click.self="closeImportModal()">
        <div class="bg-gray-800 dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-gray-700 dark:bg-gray-700 px-4 py-2.5 flex items-center justify-between border-b border-gray-600">
                <div class="text-white text-sm font-medium">
                    <span x-text="importType === 'bon-commande' ? 'Importation - Bon de commande vente (Validés)' : 'Importation - Bon d\'achat fournisseur (Validés)'"></span>
                </div>
                <button @click="closeImportModal()" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Content Area with Panel -->
            <div class="flex-1 flex gap-3 p-3 overflow-hidden">
                <!-- Panel: Bon De Commandes Validés or Bon Fournisseur -->
                <div class="flex-1 bg-gray-700 dark:bg-gray-700 rounded border border-gray-600 flex flex-col overflow-hidden">
                    <div class="bg-gray-800 dark:bg-gray-800 px-3 py-2 border-b border-gray-600 space-y-2">
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="searchImport" 
                                placeholder="Rechercher (N°, fournisseur, client, statut)..." 
                                class="w-full px-3 py-1.5 bg-gray-700 dark:bg-gray-700 border border-gray-600 rounded text-white text-xs placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <svg class="w-4 h-4 absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-600">
                            <thead class="bg-gray-800 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-200 uppercase tracking-wider">N° bon</th>
                                    <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-200 uppercase tracking-wider">Date</th>
                                    <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-200 uppercase tracking-wider">Fournisseur</th>
                                    <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-200 uppercase tracking-wider">Client livré</th>
                                    <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-200 uppercase tracking-wider">Quantité</th>
                                    <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-200 uppercase tracking-wider">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <!-- Bon de commande rows -->
                                <template x-for="bonCommande in filteredBonCommandes" :key="'bc-'+bonCommande.id">
                                    <tr x-show="importType === 'bon-commande'" class="hover:bg-gray-600 cursor-pointer transition-colors" @click="importBonCommande(bonCommande.id)">
                                        <td class="px-3 py-2 text-xs text-white font-mono" x-text="bonCommande.numero_bon"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200" x-text="formatDate(bonCommande.date)"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200" x-text="bonCommande.fournisseur ? bonCommande.fournisseur.nom_fournisseur : '-'"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200" x-text="bonCommande.client ? bonCommande.client.raison_sociale : '-'"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200 text-right" x-text="bonCommande.total_quantites ?? 0"></td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full" :class="statutBadgeClass(bonCommande.statut)" x-text="bonCommande.statut"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="importType === 'bon-commande' && filteredBonCommandes.length === 0">
                                    <td colspan="6" class="px-3 py-8 text-center text-gray-400 text-xs">
                                        <span x-show="availableBonCommandes.length === 0">Aucun bon de commande validé disponible</span>
                                        <span x-show="availableBonCommandes.length > 0">Aucun résultat trouvé</span>
                                    </td>
                                </tr>

                                <!-- Bon d'achat fournisseur rows -->
                                <template x-for="bonAchat in filteredBonAchatFournisseurs" :key="'ba-'+bonAchat.id">
                                    <tr x-show="importType === 'bon-achat-fournisseur'" class="hover:bg-gray-600 cursor-pointer transition-colors" @click="importBonAchatFournisseur(bonAchat.id)">
                                        <td class="px-3 py-2 text-xs text-white font-mono" x-text="bonAchat.numero_bon"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200" x-text="formatDate(bonAchat.date)"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200" x-text="bonAchat.fournisseur ? bonAchat.fournisseur.nom_fournisseur : '-'"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200" x-text="bonAchat.client_livre || '-'"></td>
                                        <td class="px-3 py-2 text-xs text-gray-200 text-right" x-text="bonAchat.total_qte ?? 0"></td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full" :class="statutBadgeClass(bonAchat.statut)" x-text="bonAchat.statut"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="importType === 'bon-achat-fournisseur' && filteredBonAchatFournisseurs.length === 0">
                                    <td colspan="6" class="px-3 py-8 text-center text-gray-400 text-xs">
                                        <span x-show="availableBonAchatFournisseurs.length === 0">Aucun bon d'achat fournisseur validé disponible</span>
                                        <span x-show="availableBonAchatFournisseurs.length > 0">Aucun résultat trouvé</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Central Column with Arrow Button -->
                <div class="w-24 flex flex-col items-center justify-center gap-3">
                    <button class="w-12 h-12 bg-blue-600 hover:bg-blue-700 rounded-lg flex items-center justify-center transition-colors" @click="importType === 'bon-commande' ? (filteredBonCommandes.length > 0 ? importBonCommande(filteredBonCommandes[0].id) : null) : (filteredBonAchatFournisseurs.length > 0 ? importBonAchatFournisseur(filteredBonAchatFournisseurs[0].id) : null)">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                    </button>
                    <div class="text-[10px] text-gray-300 text-center">Importer le 1er résultat</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fiche Règlement Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-[60] p-4" @click.self="showPaymentModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-gray-800 dark:bg-gray-700 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Fiche Règlement</h3>
                <button @click="showPaymentModal = false" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Compte Trésorerie -->
                    <div class="flex items-center gap-4">
                        <label class="w-1/3 text-sm font-bold text-gray-700 dark:text-gray-300">Compte Trésorerie</label>
                        <select x-model="paymentData.tresorerieId" class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Sélectionner trésorerie</option>
                            <template x-for="t in tresoreries" :key="t.id">
                                <option :value="t.id" x-text="t.libelle + ' (' + t.code + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Mode Règlement -->
                    <div class="flex items-center gap-4">
                        <label class="w-1/3 text-sm font-bold text-gray-700 dark:text-gray-300">Mode Règlement</label>
                        <select x-model="paymentData.modeReglement" class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="Espèces">Espèces</option>
                            <option value="Chèque">Chèque</option>
                            <option value="Virement">Virement</option>
                            <option value="Traite">Traite</option>
                            <option value="VERSEMENT">VERSEMENT</option>
                        </select>
                    </div>

                    <!-- Montant Règlement -->
                    <div class="flex items-center gap-4">
                        <label class="w-1/3 text-sm font-bold text-gray-700 dark:text-gray-300">Montant Règlement</label>
                        <input type="number" x-model.number="paymentData.montant" step="0.01" class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm text-center font-bold focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Référence & Banque -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Référence</label>
                            <input type="text" x-model="paymentData.reference" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Banque</label>
                            <select x-model="paymentData.banque" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Sélectionner</option>
                                <template x-for="b in banques" :key="b.id">
                                    <option :value="b.nom" x-text="b.nom"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Echéance -->
                    <div class="flex items-center gap-4">
                        <label class="w-1/3 text-sm font-bold text-gray-700 dark:text-gray-300">Echéance</label>
                        <input type="date" x-model="paymentData.echeance" class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Nom Tiré -->
                    <div class="flex items-center gap-4">
                        <label class="w-1/3 text-sm font-bold text-gray-700 dark:text-gray-300">Nom Tiré</label>
                        <input type="text" x-model="paymentData.nomTire" class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Total à ventiler -->
                    <div class="flex items-center justify-end gap-4 mt-4">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Total à ventiler</label>
                        <input type="checkbox" x-model="paymentData.totalAVentiler" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <div class="bg-orange-600 text-white px-4 py-2 rounded font-bold min-w-[120px] text-center" x-text="formatCurrency(paymentData.montant) + ' DH'"></div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-600">
                <button @click="showPaymentModal = false" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                    Annuler
                </button>
                <button @click="savePaymentData()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    Valider
                </button>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div x-show="!showForm" class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Bon de livraison</h2>
            <p class="text-gray-600 dark:text-slate-300">Gérer les bons de livraison clients</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Quantité Card -->
            <div class="bg-gradient-to-br from-lime-400 to-lime-500 rounded-lg shadow-lg p-6 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Total Quantité</h3>
                        <p class="text-3xl font-bold text-gray-900" x-text="@json($bonLivraisons->sum('total_quantites'))"></p>
                    </div>
                    <div class="text-gray-900 opacity-30">
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total TTC Card -->
            <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg shadow-lg p-6 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white mb-1">Total TTC</h3>
                        <p class="text-3xl font-bold text-white" x-text="formatCurrency(@json($bonLivraisons->sum('total_general'))) + ' DH'"></p>
                    </div>
                    <div class="text-white opacity-30">
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Solde TTC Card -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white mb-1">Solde TTC</h3>
                        <p class="text-3xl font-bold text-white" x-text="formatCurrency(@json($bonLivraisons->sum('total_general') - $bonLivraisons->sum('montant_paye'))) + ' DH'"></p>
                    </div>
                    <div class="text-white opacity-30">
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Button Card -->
            <div class="flex items-center justify-center">
                <button @click="openNewForm()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-lg font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nouveau bon de livraison
                </button>
            </div>
        </div>

        <!-- Search Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Année Filter -->
                <div>
                    <div class="relative">
                        <select 
                            x-model="searchFilters.annee"
                            @change="filterBonLivraisons()"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none"
                        >
                            <option value="">Toutes les années</option>
                            <option value="2026">2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-slate-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 7.293 8.172 5.858 9.607l3.435 3.343z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Mois Filter -->
                <div>
                    <div class="relative">
                        <select 
                            x-model="searchFilters.mois"
                            @change="filterBonLivraisons()"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none"
                        >
                            <option value="">Tous les mois</option>
                            <option value="01">Janvier</option>
                            <option value="02">Février</option>
                            <option value="03">Mars</option>
                            <option value="04">Avril</option>
                            <option value="05">Mai</option>
                            <option value="06">Juin</option>
                            <option value="07">Juillet</option>
                            <option value="08">Août</option>
                            <option value="09">Septembre</option>
                            <option value="10">Octobre</option>
                            <option value="11">Novembre</option>
                            <option value="12">Décembre</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-slate-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 7.293 8.172 5.858 9.607l3.435 3.343z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Code Filter -->
                <div>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchFilters.code"
                            @input="filterBonLivraisons()"
                            placeholder="Code" 
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <svg class="w-5 h-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Nom Client Filter -->
                <div>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchFilters.nomClient"
                            @input="filterBonLivraisons()"
                            placeholder="Nom Client" 
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <svg class="w-5 h-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Commercial Filter -->
                <div>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchFilters.commercial"
                            @input="filterBonLivraisons()"
                            placeholder="Commercial" 
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <svg class="w-5 h-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Ville Filter -->
                <div>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchFilters.ville"
                            @input="filterBonLivraisons()"
                            placeholder="Ville" 
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <svg class="w-5 h-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="flex justify-end gap-3">
            <button onclick="exportToExcel()" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-lg font-semibold">
                Excel
            </button>
            <button onclick="exportToPDF()" class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-lg font-semibold">
                PDF
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow flex flex-col h-[calc(100vh-450px)] overflow-hidden">
            <!-- Scrollable Table Section -->
            <div class="flex-1 overflow-x-auto overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-gray-50 dark:bg-slate-900 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">N° de Bon</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Code Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Nom Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Ville</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Commercial</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Quantité</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Montant TTC</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Etat</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                        <template x-for="bonLivraison in filteredBonLivraisonsList" :key="bonLivraison.id">
                            <tr x-show="bonLivraison.numero_bon" 
                                :class="getEtatRowBgClass(bonLivraison)"
                                class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono" x-text="bonLivraison.numero_bon"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300" x-text="formatDate(bonLivraison.date)"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300" x-text="bonLivraison.client?.code_client || '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300" x-text="bonLivraison.client?.raison_sociale || '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300" x-text="bonLivraison.ville_livraison || '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300" x-text="bonLivraison.commercial || '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300" x-text="bonLivraison.total_quantites || 0"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300" x-text="formatCurrency(bonLivraison.total_general || 0)"></td>
                                <td class="px-6 py-4 whitespace-nowrap relative">
                                    <!-- Status Badge - Click to toggle dropdown -->
                                    <span 
                                        @click.stop="toggleStatusDropdown(bonLivraison.id)"
                                        class="px-3 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-80 transition-opacity inline-flex items-center gap-1" 
                                        :class="{
                                            'bg-green-500 text-white': bonLivraison.statut === 'Livré',
                                            'bg-red-500 text-white': bonLivraison.statut === 'Annulé',
                                            'bg-yellow-500 text-gray-900': bonLivraison.statut === 'En attente'
                                        }"
                                        title="Cliquer pour modifier le statut">
                                        <span x-text="bonLivraison.statut || 'En attente'"></span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </span>
                                    
                                    <!-- Status Dropdown -->
                                    <div 
                                        x-show="openStatusDropdownId === bonLivraison.id"
                                        @click.away="openStatusDropdownId = null"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-50 mt-1 left-0 w-36 rounded-lg shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 overflow-hidden"
                                        style="display: none;">
                                        
                                        <!-- En attente Option -->
                                        <button 
                                            @click.stop="updateStatusDirect(bonLivraison.id, 'En attente')"
                                            class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="bonLivraison.statut === 'En attente' ? 'bg-yellow-50 dark:bg-yellow-900/30' : ''">
                                            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                            <span class="text-yellow-700 dark:text-yellow-400 font-medium">En attente</span>
                                        </button>
                                        
                                        <!-- Livré Option -->
                                        <button 
                                            @click.stop="updateStatusDirect(bonLivraison.id, 'Livré')"
                                            class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="bonLivraison.statut === 'Livré' ? 'bg-green-50 dark:bg-green-900/30' : ''">
                                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                            <span class="text-green-700 dark:text-green-400 font-medium">Livré</span>
                                        </button>
                                        
                                        <!-- Annulé Option -->
                                        <button 
                                            @click.stop="updateStatusDirect(bonLivraison.id, 'Annulé')"
                                            class="w-full px-4 py-2 text-left text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="bonLivraison.statut === 'Annulé' ? 'bg-red-50 dark:bg-red-900/30' : ''">
                                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                            <span class="text-red-700 dark:text-red-400 font-medium">Annulé</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getEtatClass(bonLivraison)" class="px-3 py-1 text-xs font-semibold rounded-full" x-text="getEtatText(bonLivraison)">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button @click="viewBonLivraison(bonLivraison.id)" class="text-cyan-400 hover:text-cyan-300 transition-colors" title="Voir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button @click="printBonLivraisonFunc(bonLivraison.id)" class="text-gray-400 dark:text-slate-400 hover:text-gray-600 dark:hover:text-white transition-colors" title="Imprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </button>
                                        <template x-if="!(bonLivraison.statut === 'Livré' && parseFloat(bonLivraison.montant_paye || 0) >= parseFloat(bonLivraison.total_general || 0) && parseFloat(bonLivraison.total_general || 0) > 0)">
                                            <button @click="loadBonLivraisonForEdit(bonLivraison.id)" class="text-blue-400 hover:text-blue-300 transition-colors" title="Modifier">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                        </template>
                                        <button @click="copyBonLivraison(bonLivraison.id)" class="text-purple-400 hover:text-purple-300 transition-colors" title="Copier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                        <template x-if="!(bonLivraison.statut === 'Livré' && parseFloat(bonLivraison.montant_paye || 0) >= parseFloat(bonLivraison.total_general || 0) && parseFloat(bonLivraison.total_general || 0) > 0)">
                                            <button @click="deleteBonLivraisonFunc(bonLivraison.id, bonLivraison.numero_bon)" class="text-red-400 hover:text-red-300 transition-colors" title="Supprimer">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredBonLivraisonsList.length === 0">
                            <td colspan="11" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">Aucun bon de livraison trouvé</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div x-show="showStatusModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" @click.self="closeStatusModal()">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-md">
            <!-- Header -->
            <div class="bg-gray-800 dark:bg-gray-700 px-6 py-4 flex items-center justify-between border-b border-gray-600">
                <h3 class="text-lg font-semibold text-white">Modifier le statut</h3>
                <button @click="closeStatusModal()" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    Bon de livraison: <span class="font-semibold" x-text="selectedBonLivraisonNumero"></span>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Sélectionnez un nouveau statut:
                </p>
                
                <div class="space-y-3">
                    <template x-for="status in availableStatuses" :key="status">
                        <button
                            @click="updateStatus(status)"
                            class="w-full px-4 py-3 text-left rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="{
                                'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200': status === 'Livré',
                                'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200': status === 'Annulé',
                                'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200': status === 'En attente'
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-semibold" x-text="status"></span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end border-t border-gray-200 dark:border-gray-600">
                <button @click="closeStatusModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
