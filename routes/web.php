<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\FournisseurController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\BonAchatFournisseurController;
use App\Http\Controllers\BonCommandeClientController;
use App\Http\Controllers\BonCommandeFournisseurController;

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
    
    Route::get('/bon-reception', function () {
        return view('achats.bon-reception', ['page_title' => 'Bon de réception']);
    })->name('achats.bon-reception');
    
    Route::get('/reglements-fournisseurs', function () {
        return view('achats.reglements-fournisseurs', ['page_title' => 'Règlements fournisseurs']);
    })->name('achats.reglements-fournisseurs');
    
    Route::get('/historique', function () {
        return view('achats.historique', ['page_title' => 'Historique achats']);
    })->name('achats.historique');
    
    Route::get('/releve-compte-fournisseurs', function () {
        return view('achats.releve-compte-fournisseurs', ['page_title' => 'Relevé compte fournisseurs']);
    })->name('achats.releve-compte-fournisseurs');
    
    Route::get('/echeancier-fournisseurs', function () {
        return view('achats.echeancier-fournisseurs', ['page_title' => 'Échéancier fournisseurs']);
    })->name('achats.echeancier-fournisseurs');
});

    // La gestion des ventes
    Route::prefix('ventes')->group(function () {
        Route::get('/bon-commande', [BonCommandeClientController::class, 'index'])->name('ventes.bon-commande');
        Route::post('/bon-commande', [BonCommandeClientController::class, 'store'])->name('ventes.bon-commande.store');
        Route::get('/bon-commande/next-numero', [BonCommandeClientController::class, 'nextNumero'])->name('ventes.bon-commande.next-numero');
        Route::get('/bon-commande/{id}', [BonCommandeClientController::class, 'show'])->name('ventes.bon-commande.show');
        Route::put('/bon-commande/{id}', [BonCommandeClientController::class, 'update'])->name('ventes.bon-commande.update');
        Route::delete('/bon-commande/{id}', [BonCommandeClientController::class, 'destroy'])->name('ventes.bon-commande.destroy');
        Route::get('/bon-commande/{id}/print', [BonCommandeClientController::class, 'print'])->name('ventes.bon-commande.print');
        
        Route::get('/bon-livraison', function () {
            return view('ventes.bon-livraison', ['page_title' => 'Bon de livraison']);
        })->name('ventes.bon-livraison');
        
        Route::get('/reglements-clients', function () {
            return view('ventes.reglements-clients', ['page_title' => 'Règlements clients']);
        })->name('ventes.reglements-clients');
        
        Route::get('/reglements-recouvrement', function () {
            return view('ventes.reglements-recouvrement', ['page_title' => 'Règlements recouvrement']);
        })->name('ventes.reglements-recouvrement');
        
        Route::get('/historique', function () {
            return view('ventes.historique', ['page_title' => 'Historique ventes']);
        })->name('ventes.historique');
        
        Route::get('/releve-compte-clients', function () {
            return view('ventes.releve-compte-clients', ['page_title' => 'Relevé compte clients']);
        })->name('ventes.releve-compte-clients');
});

    // La gestion du stock
    Route::prefix('stock')->group(function () {
        Route::get('/articles', function () {
            return view('stock.articles', ['page_title' => 'Articles']);
        })->name('stock.articles');
        
        Route::get('/familles', function () {
            return view('stock.familles', ['page_title' => 'Familles']);
        })->name('stock.familles');
        
        Route::get('/sous-familles', function () {
            return view('stock.sous-familles', ['page_title' => 'Sous-familles']);
        })->name('stock.sous-familles');
        
        Route::get('/unites-mesure', function () {
            return view('stock.unites-mesure', ['page_title' => 'Unités de mesure']);
        })->name('stock.unites-mesure');
        
        Route::get('/mouvement', function () {
            return view('stock.mouvement', ['page_title' => 'Mouvement stock']);
        })->name('stock.mouvement');
        
        Route::get('/stocks', function () {
            return view('stock.stocks', ['page_title' => 'Les stocks']);
        })->name('stock.stocks');
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
        
        Route::get('/compte-bancaire', function () {
            return view('tresorerie.compte-bancaire', ['page_title' => 'Compte bancaire']);
        })->name('tresorerie.compte-bancaire');
        
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
        Route::post('/', [BonAchatFournisseurController::class, 'store']);
        Route::get('/next-numero', [BonAchatFournisseurController::class, 'nextNumeroBon']);
        Route::get('/{id}', [BonAchatFournisseurController::class, 'show']);
        Route::put('/{id}', [BonAchatFournisseurController::class, 'update']);
        Route::post('/{id}/validate', [BonAchatFournisseurController::class, 'validateBon']);
        Route::post('/{id}/cancel', [BonAchatFournisseurController::class, 'cancel']);
        Route::delete('/{id}', [BonAchatFournisseurController::class, 'destroy']);
    });

    // Paramètres (Settings)
    Route::get('/parametres', function () {
        return view('parametres', ['page_title' => 'Paramètres']);
    })->name('parametres');

    // API Routes for Settings
    Route::prefix('api/settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::post('/app-name', [SettingsController::class, 'updateAppName']);
        Route::post('/logo', [SettingsController::class, 'uploadLogo']);
        Route::delete('/logo', [SettingsController::class, 'deleteLogo']);
        Route::post('/cities', [SettingsController::class, 'updateCities']);
        Route::post('/cities/add', [SettingsController::class, 'addCity']);
        Route::post('/cities/remove', [SettingsController::class, 'removeCity']);
    });

    // Old routes (kept for backward compatibility)
    Route::get('/blade-example', function () {
        return view('blade-example');
    })->name('blade-example');

    Route::get('/vue-example', function () {
        return view('vue-example');
    })->name('vue-example');
});
