<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReglementFournisseur;
use App\Models\ReglementFournisseurLigne;
use Illuminate\Support\Facades\DB;

class FixPaymentAllocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:fix-allocations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix payments with unallocated amounts by allocating full payment to single purchase orders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Auto-fixing payments with single purchase order allocations...\n");

        // Find all payments with unallocated amounts and only 1 ligne
        $problematicPayments = DB::table('reglements_fournisseurs as rf')
            ->leftJoin('reglement_fournisseur_lignes as rfl', 'rf.id', '=', 'rfl.reglement_id')
            ->select(
                'rf.id',
                'rf.code_reglement',
                'rf.montant',
                DB::raw('COUNT(rfl.id) as ligne_count'),
                DB::raw('COALESCE(SUM(rfl.montant_regle), 0) as allocated_total')
            )
            ->groupBy('rf.id', 'rf.code_reglement', 'rf.montant')
            ->havingRaw('COUNT(rfl.id) = 1')
            ->havingRaw('(rf.montant - COALESCE(SUM(rfl.montant_regle), 0)) != 0')
            ->get();

        if ($problematicPayments->isEmpty()) {
            $this->info("✓ No payments to fix!");
            return 0;
        }

        $this->info("Found " . $problematicPayments->count() . " payment(s) to fix");
        $this->line(str_repeat("=", 100));

        $fixed = 0;
        $failed = 0;

        foreach ($problematicPayments as $payment) {
            try {
                // Get the single ligne
                $ligne = ReglementFournisseurLigne::where('reglement_id', $payment->id)->first();
                
                if (!$ligne) {
                    $this->error("❌ {$payment->code_reglement}: No ligne found");
                    $failed++;
                    continue;
                }
                
                $unallocated = $payment->montant - $payment->allocated_total;
                $oldAmount = $ligne->montant_regle;
                $newAmount = $payment->montant;
                
                DB::beginTransaction();
                
                // Update the ligne to include the unallocated amount
                $ligne->montant_regle = $newAmount;
                $ligne->save();
                
                DB::commit();
                
                $this->info("✓ {$payment->code_reglement}: {$oldAmount} → {$newAmount} MAD (+{$unallocated})");
                $fixed++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("❌ {$payment->code_reglement}: ERROR - {$e->getMessage()}");
                $failed++;
            }
        }

        $this->line("\n" . str_repeat("=", 100));
        $this->info("Results:");
        $this->info("  ✓ Fixed: {$fixed}");
        if ($failed > 0) {
            $this->error("  ❌ Failed: {$failed}");
        }
        $this->line("\nRun 'php artisan payments:scan' to verify the fixes.");

        return 0;
    }
}
