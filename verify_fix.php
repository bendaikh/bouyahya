use App\Models\BonAchatFournisseur;

$bons = BonAchatFournisseur::has('reglementLignes')->with('fournisseur')->take(5)->get();

if ($bons->isEmpty()) {
    echo "No bons with payments found.\n";
}

foreach ($bons as $bon) {
    echo "Bon: {$bon->numero_bon}\n";
    echo "Total TTC: {$bon->total_ttc}\n";
    echo "Montant Payé Check: " . ($bon->montant_paye) . "\n";
    echo "Statut: {$bon->statut}\n";
    echo "-------------------\n";
}
