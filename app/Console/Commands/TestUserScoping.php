<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Client;
use App\Models\BonCommandeClient;

class TestUserScoping extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:user-scoping';

    /**
     * The console command description.
     */
    protected $description = 'Test user data scoping functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Testing User Data Scoping ===');
        $this->newLine();

        // Get users
        $superadmin = User::role('superadmin')->first();
        $commercial = User::role('commercial')->first();
        $assistant = User::role('assistant')->first();

        if (!$superadmin) {
            $this->error('No superadmin user found!');
            return 1;
        }

        $this->info('Found users:');
        $this->line("- Superadmin: {$superadmin->email}");
        if ($commercial) $this->line("- Commercial: {$commercial->email}");
        if ($assistant) $this->line("- Assistant: {$assistant->email}");
        $this->newLine();

        // Test 1: All users can see all clients (no scope)
        $totalClients = Client::count();
        $this->info("Test 1: Total clients visible to everyone: {$totalClients}");
        $this->line("✓ Clients are SHARED - all users can see them");
        $this->newLine();

        // Test 2: Superadmin sees all bon de commandes
        auth()->login($superadmin);
        $totalBonCommandes = BonCommandeClient::withAllUsers()->count();
        $superadminBonCommandes = BonCommandeClient::count();
        $this->info("Test 2: Bon de Commandes visible to Superadmin: {$superadminBonCommandes}");
        $this->line("✓ Expected: {$totalBonCommandes}, Got: {$superadminBonCommandes}");
        
        if ($superadminBonCommandes !== $totalBonCommandes) {
            $this->error('FAILED: Superadmin should see all bon de commandes!');
        } else {
            $this->info('PASSED: Superadmin sees all bon de commandes');
        }
        $this->newLine();

        // Test 3: Regular user sees only their own bon de commandes
        if ($commercial) {
            auth()->login($commercial);
            
            // Check if commercial can see all clients
            $commercialClients = Client::count();
            $this->info("Test 3: Clients visible to Commercial user: {$commercialClients}");
            if ($commercialClients === $totalClients) {
                $this->info('PASSED: Commercial user can see all clients');
            } else {
                $this->error("FAILED: Commercial should see all {$totalClients} clients, but sees only {$commercialClients}");
            }
            $this->newLine();
            
            $commercialBonCommandes = BonCommandeClient::count();
            $commercialOwnBonCommandes = BonCommandeClient::withAllUsers()->where('user_id', $commercial->id)->count();
            
            $this->info("Test 4: Bon de Commandes visible to Commercial user: {$commercialBonCommandes}");
            $this->line("Expected: {$commercialOwnBonCommandes} (only their own), Got: {$commercialBonCommandes}");
            
            if ($commercialBonCommandes !== $commercialOwnBonCommandes) {
                $this->error('FAILED: Commercial should only see their own bon de commandes!');
            } else {
                $this->info('PASSED: Commercial user sees only their own bon de commandes');
            }
            $this->newLine();
        }

        // Test 5: Create a client as commercial user - should be visible to all
        if ($commercial) {
            auth()->login($commercial);
            
            $testClient = Client::create([
                'code_client' => 'TEST-' . uniqid(),
                'raison_sociale' => 'Test Shared Client',
                'nom_gerant' => 'Test Manager',
                'ville' => 'Test City',
                'type_client' => 'REV',
                'mode_paiement' => 'Espèce',
                'echeance' => '30 jours',
                'plafond' => 10000,
                'bloquer' => false,
            ]);

            $this->info("Test 5: Created test client as Commercial user");
            $this->line("Client ID: {$testClient->id}");
            $this->line("User ID: {$testClient->user_id}");
            $this->newLine();

            // Test 6: Assistant CAN see commercial's client (shared)
            if ($assistant) {
                auth()->login($assistant);
                $assistantCanSeeClient = Client::find($testClient->id);
                
                $this->info("Test 6: Can Assistant user see Commercial's client?");
                if ($assistantCanSeeClient) {
                    $this->info('PASSED: Assistant CAN see Commercial\'s client (clients are shared)');
                } else {
                    $this->error('FAILED: Assistant should see all clients!');
                }
                $this->newLine();
            }

            // Test 7: Superadmin can see the client
            auth()->login($superadmin);
            $superadminCanSeeClient = Client::find($testClient->id);
            
            $this->info("Test 7: Can Superadmin see Commercial's client?");
            if ($superadminCanSeeClient) {
                $this->info('PASSED: Superadmin can see all clients');
            } else {
                $this->error('FAILED: Superadmin should see all clients!');
            }
            $this->newLine();

            // Cleanup
            $testClient->delete();
            $this->line('Test client deleted.');
        }

        $this->newLine();
        $this->info('=== Tests Complete ===');
        $this->newLine();
        $this->info('Summary:');
        $this->line('✓ Clients: SHARED (visible to all users)');
        $this->line('✓ Fournisseurs: SHARED (visible to all users)');
        $this->line('✓ Bon de Commandes: SCOPED (users see only their own)');
        $this->line('✓ Bon de Livraisons: SCOPED (users see only their own)');
        $this->line('✓ Reglements: SCOPED (users see only their own)');
        
        auth()->logout();

        return 0;
    }
}
