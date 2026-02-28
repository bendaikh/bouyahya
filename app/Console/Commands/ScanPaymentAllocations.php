<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanPaymentAllocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for payments with unallocated amounts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Scanning for payments with unallocated amounts...\n");

        $problematicPayments = DB::table('reglements_fournisseurs as rf')
            ->leftJoin('reglement_fournisseur_lignes as rfl', 'rf.id', '=', 'rfl.reglement_id')
            ->leftJoin('fournisseurs as f', 'rf.fournisseur_id', '=', 'f.id')
            ->select(
                'rf.id',
                'rf.code_reglement',
                'rf.date_reglement',
                'f.nom_fournisseur',
                'rf.montant as payment_total',
                DB::raw('COALESCE(SUM(rfl.montant_regle), 0) as allocated_total'),
                DB::raw('(rf.montant - COALESCE(SUM(rfl.montant_regle), 0)) as unallocated'),
                'rf.statut'
            )
            ->groupBy('rf.id', 'rf.code_reglement', 'rf.date_reglement', 'f.nom_fournisseur', 'rf.montant', 'rf.statut')
            ->havingRaw('(rf.montant - COALESCE(SUM(rfl.montant_regle), 0)) != 0')
            ->orderBy('rf.date_reglement', 'desc')
            ->get();

        if ($problematicPayments->isEmpty()) {
            $this->info("✓ No problematic payments found! All payments are fully allocated.");
            return 0;
        }

        $this->info("Found " . $problematicPayments->count() . " payment(s) with unallocated amounts:");
        $this->line(str_repeat("=", 120));
        
        // Header
        $this->line(sprintf(
            "%-12s %-12s %-25s %12s %12s %12s %-10s",
            "Code", "Date", "Supplier", "Total", "Allocated", "Unallocated", "Status"
        ));
        $this->line(str_repeat("=", 120));

        $totalUnallocated = 0;

        foreach ($problematicPayments as $payment) {
            $this->line(sprintf(
                "%-12s %-12s %-25s %12.2f %12.2f %12.2f %-10s",
                $payment->code_reglement,
                date('Y-m-d', strtotime($payment->date_reglement)),
                substr($payment->nom_fournisseur, 0, 25),
                $payment->payment_total,
                $payment->allocated_total,
                $payment->unallocated,
                $payment->statut
            ));
            $totalUnallocated += $payment->unallocated;
        }

        $this->line(str_repeat("=", 120));
        $this->info("Total unallocated amount: " . number_format($totalUnallocated, 2) . " MAD");
        $this->line("\nRun 'php artisan payments:fix-allocations' to auto-fix simple cases.");

        return 0;
    }
}
