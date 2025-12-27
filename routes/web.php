<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\FournisseurController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\BonAchatFournisseurController;
use App\Http\Controllers\Api\ReglementFournisseurController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\BonCommandeClientController;
use App\Http\Controllers\BonCommandeFournisseurController;
use App\Http\Controllers\BonLivraisonClientController;
use App\Http\Controllers\CompteTresorerieController;
use App\Http\Controllers\StockController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirect root to login or dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Protected Routes (require authentication)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // La gestion des achats
    Route::prefix('achats')->group(function () {
        Route::get('/bon-achat-fournisseur', function () {
            return view('achats.bon-achat-fournisseur', ['page_title' => 'Bon d\'achat Fournisseur']);
        })->name('achats.bon-achat-fournisseur');
        
        Route::get('/bon-commande', [BonCommandeFournisseurController::class, 'index'])->name('achats.bon-commande');
        Route::post('/bon-commande', [BonCommandeFournisseurController::class, 'store'])->name('achats.bon-commande.store');
        Route::get('/bon-commande/next-numero', [BonCommandeFournisseurController::class, 'nextNumero'])->name('achats.bon-commande.next-numero');
        Route::get('/bon-commande/{id}', [BonCommandeFournisseurController::class, 'show'])->name('achats.bon-commande.show');
        Route::put('/bon-commande/{id}', [BonCommandeFournisseurController::class, 'update'])->name('achats.bon-commande.update');
        Route::delete('/bon-commande/{id}', [BonCommandeFournisseurController::class, 'destroy'])->name('achats.bon-commande.destroy');
        Route::get('/bon-commande/{id}/print', [BonCommandeFournisseurController::class, 'print'])->name('achats.bon-commande.print');
        Route::post('/bon-commande/{id}/validate', [BonCommandeFournisseurController::class, 'validate'])->name('achats.bon-commande.validate');
        Route::post('/bon-commande/{id}/convert-to-bon-achat', [BonCommandeFournisseurController::class, 'convertToBonAchat'])->name('achats.bon-commande.convert');
    
    Route::get('/bon-reception', function () {
        return view('achats.bon-reception', ['page_title' => 'Bon de réception']);
    })->name('achats.bon-reception');
    
    Route::get('/reglements-fournisseurs', function () {
        return view('achats.reglements-fournisseurs', ['page_title' => 'Règlements fournisseurs', 'vue_component' => 'ReglementsFournisseurs']);
    })->name('achats.reglements-fournisseurs');
    
    Route::get('/historique', function () {
        return view('achats.historique', ['page_title' => 'Historique achats']);
    })->name('achats.historique');
    
    Route::get('/releve-compte-fournisseurs', function () {
        return view('achats.releve-compte-fournisseurs', ['page_title' => 'Relevé compte fournisseurs', 'vue_component' => 'ReleveCompteFournisseurs']);
    })->name('achats.releve-compte-fournisseurs');
    
    Route::get('/echeancier-fournisseurs', function () {
        return view('achats.echeancier-fournisseurs', ['page_title' => 'Échéancier fournisseurs']);
    })->name('achats.echeancier-fournisseurs');
});

    // La gestion des ventes
    Route::prefix('ventes')->group(function () {
        // Bon de Commande Client routes
        Route::get('/bon-commande', [BonCommandeClientController::class, 'index'])->name('ventes.bon-commande');
        Route::post('/bon-commande', [BonCommandeClientController::class, 'store'])->name('ventes.bon-commande.store');
        Route::get('/bon-commande/next-numero', [BonCommandeClientController::class, 'nextNumero'])->name('ventes.bon-commande.next-numero');
        Route::get('/bon-commande/{id}', [BonCommandeClientController::class, 'show'])->name('ventes.bon-commande.show');
        Route::put('/bon-commande/{id}', [BonCommandeClientController::class, 'update'])->name('ventes.bon-commande.update');
        Route::delete('/bon-commande/{id}', [BonCommandeClientController::class, 'destroy'])->name('ventes.bon-commande.destroy');
        Route::get('/bon-commande/{id}/print', [BonCommandeClientController::class, 'print'])->name('ventes.bon-commande.print');
        Route::post('/bon-commande/{id}/validate', [BonCommandeClientController::class, 'validateBon'])->name('ventes.bon-commande.validate');
        Route::post('/bon-commande/{id}/cancel', [BonCommandeClientController::class, 'cancel'])->name('ventes.bon-commande.cancel');
        Route::post('/bon-commande/{id}/convert-to-bon-livraison', [BonCommandeClientController::class, 'convertToBonLivraison'])->name('ventes.bon-commande.convert');
        
        // Bon de Livraison Client routes
        Route::get('/bon-livraison', [BonLivraisonClientController::class, 'index'])->name('ventes.bon-livraison');
        Route::post('/bon-livraison', [BonLivraisonClientController::class, 'store'])->name('ventes.bon-livraison.store');
        Route::get('/bon-livraison/next-numero', [BonLivraisonClientController::class, 'nextNumero'])->name('ventes.bon-livraison.next-numero');
        Route::get('/bon-livraison/bon-commandes', [BonLivraisonClientController::class, 'getBonCommandes'])->name('ventes.bon-livraison.bon-commandes');
        Route::get('/bon-livraison/bon-achat-fournisseurs', [BonLivraisonClientController::class, 'getBonAchatFournisseurs'])->name('ventes.bon-livraison.bon-achat-fournisseurs');
        Route::get('/bon-livraison/import-bon-commande/{id}', [BonLivraisonClientController::class, 'importFromBonCommande'])->name('ventes.bon-livraison.import-bon-commande');
        Route::get('/bon-livraison/import-bon-achat-fournisseur/{id}', [BonLivraisonClientController::class, 'importFromBonAchatFournisseur'])->name('ventes.bon-livraison.import-bon-achat-fournisseur');
        Route::get('/bon-livraison/{id}', [BonLivraisonClientController::class, 'show'])->name('ventes.bon-livraison.show');
        Route::put('/bon-livraison/{id}', [BonLivraisonClientController::class, 'update'])->name('ventes.bon-livraison.update');
        Route::delete('/bon-livraison/{id}', [BonLivraisonClientController::class, 'destroy'])->name('ventes.bon-livraison.destroy');
        Route::get('/bon-livraison/{id}/print', [BonLivraisonClientController::class, 'print'])->name('ventes.bon-livraison.print');
        Route::post('/bon-livraison/{id}/mark-delivered', [BonLivraisonClientController::class, 'markDelivered'])->name('ventes.bon-livraison.mark-delivered');
        Route::post('/bon-livraison/{id}/cancel', [BonLivraisonClientController::class, 'cancel'])->name('ventes.bon-livraison.cancel');
        Route::post('/bon-livraison/{id}/update-status', [BonLivraisonClientController::class, 'updateStatus'])->name('ventes.bon-livraison.update-status');
        
        // Trésorerie (Ventes) - previously "Règlements clients"
        Route::get('/reglements-clients', [CompteTresorerieController::class, 'indexVentes'])->name('ventes.reglements-clients');
        Route::post('/reglements-clients', [CompteTresorerieController::class, 'store'])->name('ventes.reglements-clients.store');
        Route::get('/reglements-clients/next-code', [CompteTresorerieController::class, 'nextCode'])->name('ventes.reglements-clients.next-code');
        Route::delete('/reglements-clients/{id}', [CompteTresorerieController::class, 'destroy'])->name('ventes.reglements-clients.destroy');
        
        Route::get('/reglements-clients-gestion', function () {
            return view('ventes.reglements-clients-gestion', ['page_title' => 'Règlements clients']);
        })->name('ventes.reglements-clients-gestion');
        
        Route::get('/historique', function () {
            return view('ventes.historique', ['page_title' => 'Historique ventes']);
        })->name('ventes.historique');
        
        Route::get('/releve-compte-clients', function () {
            return view('ventes.releve-compte-clients', ['page_title' => 'Relevé compte clients', 'vue_component' => 'ReleveCompteClients']);
        })->name('ventes.releve-compte-clients');
});

    // La gestion du stock
    Route::prefix('stock')->group(function () {
        Route::get('/articles', function () {
            return view('stock.articles', ['page_title' => 'Articles', 'vue_component' => 'ArticlesList']);
        })->name('stock.articles');
        
        Route::get('/mouvement', function () {
            return view('stock.mouvement', ['page_title' => 'Mouvement stock']);
        })->name('stock.mouvement');
        
        Route::get('/stocks', [StockController::class, 'index'])->name('stock.stocks');
        Route::get('/stocks/data', [StockController::class, 'getStockData'])->name('stock.stocks.data');
});

    // La gestion trésorerie
    Route::prefix('tresorerie')->group(function () {
        Route::get('/etat-journalier', function () {
            return view('tresorerie.etat-journalier', ['page_title' => 'État journalier']);
        })->name('tresorerie.etat-journalier');
        
        Route::get('/releve-reglements', function () {
            return view('tresorerie.releve-reglements', ['page_title' => 'Relevé règlements']);
        })->name('tresorerie.releve-reglements');
        
        Route::get('/balance-caisse', function () {
            return view('tresorerie.balance-caisse', ['page_title' => 'Balance caisse']);
        })->name('tresorerie.balance-caisse');
        
        Route::get('/liste-impots', function () {
            return view('tresorerie.liste-impots', ['page_title' => 'Liste rég impôts']);
        })->name('tresorerie.liste-impots');
        
        Route::get('/compte-bancaire', [CompteTresorerieController::class, 'index'])->name('tresorerie.compte-bancaire');
        Route::post('/compte-bancaire', [CompteTresorerieController::class, 'store'])->name('tresorerie.compte-bancaire.store');
        Route::get('/compte-bancaire/next-code', [CompteTresorerieController::class, 'nextCode'])->name('tresorerie.compte-bancaire.next-code');
        Route::delete('/compte-bancaire/{id}', [CompteTresorerieController::class, 'destroy'])->name('tresorerie.compte-bancaire.destroy');
        
        Route::get('/encaissement-decaissement', function () {
            return view('tresorerie.encaissement-decaissement', ['page_title' => 'Encaissement / Décaissement']);
        })->name('tresorerie.encaissement-decaissement');
        
        Route::get('/types-charges', function () {
            return view('tresorerie.types-charges', ['page_title' => 'Types de charges']);
        })->name('tresorerie.types-charges');
});

    // Gestion des clients
    Route::prefix('clients')->group(function () {
        Route::get('/', function () {
            return view('clients.index', ['page_title' => 'Liste des clients']);
        })->name('clients.index');

        Route::get('/create', function () {
            return view('clients.create', ['page_title' => 'Nouveau client']);
        })->name('clients.create');
    });

    // Gestion des fournisseurs
    Route::prefix('fournisseurs')->group(function () {
        Route::get('/', function () {
            return view('fournisseurs.index', ['page_title' => 'Liste des fournisseurs']);
        })->name('fournisseurs.index');

        Route::get('/create', function () {
            return view('fournisseurs.create', ['page_title' => 'Nouveau fournisseur']);
        })->name('fournisseurs.create');
    });

    // API Routes for Clients
    Route::prefix('api/clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::get('/next-code', [ClientController::class, 'nextCode']);
        Route::get('/{id}', [ClientController::class, 'show']);
        Route::put('/{id}', [ClientController::class, 'update']);
        Route::delete('/{id}', [ClientController::class, 'destroy']);
    });

    // API Routes for Fournisseurs
    Route::prefix('api/fournisseurs')->group(function () {
        Route::get('/', [FournisseurController::class, 'index']);
        Route::post('/', [FournisseurController::class, 'store']);
        Route::get('/next-code', [FournisseurController::class, 'nextCode']);
        Route::get('/{id}', [FournisseurController::class, 'show']);
        Route::put('/{id}', [FournisseurController::class, 'update']);
        Route::delete('/{id}', [FournisseurController::class, 'destroy']);
    });
    
    // API Routes for Bon d'achat Fournisseur
    Route::prefix('api/bon-achat-fournisseur')->group(function () {
        Route::get('/', [BonAchatFournisseurController::class, 'index']);
        Route::get('/historique', [BonAchatFournisseurController::class, 'historique']);
        Route::post('/', [BonAchatFournisseurController::class, 'store']);
        Route::get('/next-numero', [BonAchatFournisseurController::class, 'nextNumeroBon']);
        Route::get('/{id}/payment-details', [BonAchatFournisseurController::class, 'getPaymentDetails']);
        Route::get('/{id}', [BonAchatFournisseurController::class, 'show']);
        Route::put('/{id}', [BonAchatFournisseurController::class, 'update']);
        Route::post('/{id}/validate', [BonAchatFournisseurController::class, 'validateBon']);
        Route::post('/{id}/cancel', [BonAchatFournisseurController::class, 'cancel']);
        Route::post('/{id}/update-status', [BonAchatFournisseurController::class, 'updateStatus']);
        Route::delete('/{id}', [BonAchatFournisseurController::class, 'destroy']);
    });

    // API Routes for Règlements Fournisseurs
    Route::prefix('api/reglements-fournisseurs')->group(function () {
        Route::get('/', [ReglementFournisseurController::class, 'index']);
        Route::post('/', [ReglementFournisseurController::class, 'store']);
        Route::get('/next-code', [ReglementFournisseurController::class, 'nextCode']);
        Route::get('/bons-achat/{fournisseurId}', [ReglementFournisseurController::class, 'getBonsAchatFournisseur']);
        Route::get('/{id}', [ReglementFournisseurController::class, 'show']);
        Route::put('/{id}', [ReglementFournisseurController::class, 'update']);
        Route::post('/{id}/mark-paid', [ReglementFournisseurController::class, 'markAsPaid']);
        Route::post('/{id}/mark-postponed', [ReglementFournisseurController::class, 'markAsPostponed']);
        Route::post('/{id}/update-status', [ReglementFournisseurController::class, 'updateStatus']);
        Route::delete('/{id}', [ReglementFournisseurController::class, 'destroy']);
    });

    // API Routes for Règlements Clients
    Route::prefix('api/reglements-clients')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ReglementClientController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\ReglementClientController::class, 'store']);
        Route::get('/next-code', [\App\Http\Controllers\Api\ReglementClientController::class, 'nextCode']);
        Route::get('/tresoreries', [\App\Http\Controllers\Api\ReglementClientController::class, 'getTresoreries']);
        Route::get('/bons-livraison/{clientId}', [\App\Http\Controllers\Api\ReglementClientController::class, 'getBonsLivraisonClient']);
        Route::get('/{id}', [\App\Http\Controllers\Api\ReglementClientController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\ReglementClientController::class, 'update']);
        Route::post('/{id}/mark-paid', [\App\Http\Controllers\Api\ReglementClientController::class, 'markAsPaid']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\ReglementClientController::class, 'destroy']);
    });

    // API Routes for Historique Ventes
    Route::prefix('api/ventes')->group(function () {
        Route::get('/historique', [\App\Http\Controllers\Api\HistoriqueVentesController::class, 'index']);
    });

    // Paramètres (Settings)
    Route::prefix('parametres')->group(function () {
        // Redirect /parametres to /parametres/application
        Route::get('/', function () {
            return redirect()->route('parametres.application');
        })->name('parametres');
        
        Route::get('/application', function () {
            return view('parametres.application', ['page_title' => 'Paramètres Application']);
        })->name('parametres.application');
        
        Route::get('/villes', function () {
            return view('parametres.villes', ['page_title' => 'Paramètres Villes']);
        })->name('parametres.villes');
        
        Route::get('/articles', function () {
            return view('parametres.articles', ['page_title' => 'Paramètres Articles']);
        })->name('parametres.articles');
        
        Route::get('/commerciales', function () {
            return view('parametres.commerciales', ['page_title' => 'Paramètres Commerciales']);
        })->name('parametres.commerciales');
        
        Route::get('/transports', function () {
            return view('parametres.transports', ['page_title' => 'Paramètres Transports']);
        })->name('parametres.transports');
        
        Route::get('/matricules', function () {
            return view('parametres.matricules', ['page_title' => 'Paramètres Matricules']);
        })->name('parametres.matricules');
        
        Route::get('/banques', function () {
            return view('parametres.banques', ['page_title' => 'Paramètres Banques']);
        })->name('parametres.banques');
    });

    // API Routes for Settings
    Route::prefix('api/settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::post('/app-name', [SettingsController::class, 'updateAppName']);
        Route::post('/logo', [SettingsController::class, 'uploadLogo']);
        Route::delete('/logo', [SettingsController::class, 'deleteLogo']);
        Route::post('/cities', [SettingsController::class, 'updateCities']);
        Route::post('/cities/add', [SettingsController::class, 'addCity']);
        Route::post('/cities/remove', [SettingsController::class, 'removeCity']);
        
        // Familles Article
        Route::get('/familles-article', [SettingsController::class, 'getFamillesArticle']);
        Route::post('/familles-article/add', [SettingsController::class, 'addFamilleArticle']);
        Route::post('/familles-article/update', [SettingsController::class, 'updateFamilleArticle']);
        Route::post('/familles-article/remove', [SettingsController::class, 'removeFamilleArticle']);
        
        // Sous-Familles Article
        Route::get('/sous-familles-article', [SettingsController::class, 'getSousFamillesArticle']);
        Route::post('/sous-familles-article/add', [SettingsController::class, 'addSousFamilleArticle']);
        Route::post('/sous-familles-article/update', [SettingsController::class, 'updateSousFamilleArticle']);
        Route::post('/sous-familles-article/remove', [SettingsController::class, 'removeSousFamilleArticle']);
        
        // Unités de Mesure
        Route::get('/unites-mesure', [SettingsController::class, 'getUnitesMesure']);
        Route::post('/unites-mesure/add', [SettingsController::class, 'addUniteMesure']);
        Route::post('/unites-mesure/update', [SettingsController::class, 'updateUniteMesure']);
        Route::post('/unites-mesure/remove', [SettingsController::class, 'removeUniteMesure']);
        
        // Commerciales routes
        Route::get('/commerciales', [SettingsController::class, 'getCommerciales']);
        Route::post('/commerciales/add', [SettingsController::class, 'addCommerciale']);
        Route::post('/commerciales/update', [SettingsController::class, 'updateCommerciale']);
        Route::post('/commerciales/remove', [SettingsController::class, 'removeCommerciale']);
        
        // Transports routes
        Route::get('/transports', [SettingsController::class, 'getTransports']);
        Route::post('/transports/add', [SettingsController::class, 'addTransport']);
        Route::post('/transports/update', [SettingsController::class, 'updateTransport']);
        Route::post('/transports/remove', [SettingsController::class, 'removeTransport']);
        
        // Matricules routes
        Route::get('/matricules', [SettingsController::class, 'getMatricules']);
        Route::post('/matricules/add', [SettingsController::class, 'addMatricule']);
        Route::post('/matricules/update', [SettingsController::class, 'updateMatricule']);
        Route::post('/matricules/remove', [SettingsController::class, 'removeMatricule']);
        
        // Banques routes
        Route::get('/banques', [SettingsController::class, 'getBanques']);
        Route::post('/banques/add', [SettingsController::class, 'addBanque']);
        Route::post('/banques/update', [SettingsController::class, 'updateBanque']);
        Route::post('/banques/remove', [SettingsController::class, 'removeBanque']);
    });

    // API Routes for Articles
    Route::prefix('api/articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::post('/', [ArticleController::class, 'store']);
        Route::get('/next-reference', [ArticleController::class, 'nextReference']);
        Route::get('/reference-data', [ArticleController::class, 'getReferenceData']);
        Route::get('/{id}', [ArticleController::class, 'show']);
        Route::put('/{id}', [ArticleController::class, 'update']);
        Route::delete('/{id}', [ArticleController::class, 'destroy']);
    });

    // Old routes (kept for backward compatibility)
    Route::get('/blade-example', function () {
        return view('blade-example');
    })->name('blade-example');

    Route::get('/vue-example', function () {
        return view('vue-example');
    })->name('vue-example');
});
