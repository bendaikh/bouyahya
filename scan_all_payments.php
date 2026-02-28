<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ReglementFournisseur;
use Illuminate\Support\Facades\DB;

echo "Scanning for payments with unallocated amounts...\n\n";

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
    echo "✓ No problematic payments found! All payments are fully allocated.\n";
    exit(0);
}

echo "Found " . $problematicPayments->count() . " payment(s) with unallocated amounts:\n";
echo str_repeat("=", 120) . "\n";
printf("%-12s %-12s %-25s %-12s %-12s %-12s %-10s\n", 
    "Code", "Date", "Supplier", "Total", "Allocated", "Unallocated", "Status");
echo str_repeat("=", 120) . "\n";

$totalUnallocated = 0;

foreach ($problematicPayments as $payment) {
    printf("%-12s %-12s %-25s %12.2f %12.2f %12.2f %-10s\n",
        $payment->code_reglement,
        date('Y-m-d', strtotime($payment->date_reglement)),
        substr($payment->nom_fournisseur, 0, 25),
        $payment->payment_total,
        $payment->allocated_total,
        $payment->unallocated,
        $payment->statut
    );
    $totalUnallocated += $payment->unallocated;
}

echo str_repeat("=", 120) . "\n";
echo "Total unallocated amount: " . number_format($totalUnallocated, 2) . " MAD\n";
