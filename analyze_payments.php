<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ReglementFournisseur;
use App\Models\ReglementFournisseurLigne;
use Illuminate\Support\Facades\DB;

echo "Analyzing unallocated payments...\n\n";

// Get payments with unallocated amounts
$problematicPayments = DB::table('reglements_fournisseurs as rf')
    ->leftJoin('reglement_fournisseur_lignes as rfl', 'rf.id', '=', 'rfl.reglement_id')
    ->select('rf.id', 'rf.code_reglement', 'rf.montant', DB::raw('COUNT(rfl.id) as ligne_count'))
    ->groupBy('rf.id', 'rf.code_reglement', 'rf.montant')
    ->havingRaw('(rf.montant - COALESCE(SUM(rfl.montant_regle), 0)) != 0')
    ->get();

$stats = [
    'single_bon' => 0,
    'multiple_bons' => 0,
    'no_allocation' => 0,
];

$fixable = [];

foreach ($problematicPayments as $payment) {
    if ($payment->ligne_count == 0) {
        $stats['no_allocation']++;
    } elseif ($payment->ligne_count == 1) {
        $stats['single_bon']++;
        $fixable[] = $payment->code_reglement;
    } else {
        $stats['multiple_bons']++;
    }
}

echo "Payment Analysis:\n";
echo str_repeat("=", 60) . "\n";
echo "Total problematic payments: " . count($problematicPayments) . "\n";
echo "  - No allocation at all: {$stats['no_allocation']} payments\n";
echo "  - Single purchase order: {$stats['single_bon']} payments ← CAN AUTO-FIX\n";
echo "  - Multiple purchase orders: {$stats['multiple_bons']} payments ← NEEDS REVIEW\n";
echo str_repeat("=", 60) . "\n\n";

if ($stats['single_bon'] > 0) {
    echo "These {$stats['single_bon']} payments with SINGLE purchase orders can be auto-fixed:\n";
    echo "(Adding unallocated amount to the existing purchase order)\n\n";
    
    // Show first 20
    $showing = min(20, count($fixable));
    for ($i = 0; $i < $showing; $i++) {
        echo "  - {$fixable[$i]}\n";
    }
    
    if (count($fixable) > 20) {
        echo "  ... and " . (count($fixable) - 20) . " more\n";
    }
}

echo "\n";
