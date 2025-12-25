<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison {{ $bonLivraison->numero_bon }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #000;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #000;
        }
        
        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        
        .logo-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 8px;
            background: white;
        }
        
        .logo-placeholder {
            width: 120px;
            height: 120px;
            border: 1px solid #ddd;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #999;
        }
        
        .company-info {
            text-align: right;
        }
        
        .company-name {
            color: #006400;
            font-size: 22px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
            font-family: Arial, sans-serif;
            letter-spacing: 0.5px;
        }
        
        .company-subtitle {
            color: #000;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .info-section {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-box {
            flex: 1;
            border: 1px solid #006400;
            padding: 10px;
            background: #fff;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: normal;
            color: #000;
            min-width: 100px;
        }
        
        .info-value {
            color: #000;
            flex: 1;
        }
        
        .info-value.dotted {
            border-bottom: 1px dotted #000;
            flex: 1;
            margin-left: 5px;
            min-height: 15px;
        }
        
        .signature-cell {
            flex: 1;
            border-bottom: 1px solid #000;
            height: 30px;
            margin: 0 5px;
            padding: 5px;
            font-size: 11px;
        }
        
        .signature-headers {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .signature-header {
            flex: 1;
            text-align: center;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        
        .articles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .articles-table th {
            background: #808080;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #000;
        }
        
        .articles-table td {
            padding: 8px;
            border: 1px solid #000;
            font-size: 11px;
        }
        
        .articles-table .text-right {
            text-align: right;
        }
        
        .articles-table .text-center {
            text-align: center;
        }
        
        .totals-box {
            width: 250px;
            background: #e8e8e8;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin-left: auto;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
        }
        
        .total-row.final {
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            <div class="logo-container">
                @if($appLogo && file_exists(storage_path('app/public/' . $appLogo)))
                    <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" class="logo-image">
                @else
                    <div class="logo-placeholder">Logo</div>
                @endif
            </div>
        </div>
        <div class="company-info">
            <div class="company-name">{{ $appName }}</div>
            <div class="company-subtitle">Materiaux de construction</div>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Bon de Livraison N° :</span>
                <span class="info-value">{{ $bonLivraison->numero_bon }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date de Livraison :</span>
                <span class="info-value">{{ $bonLivraison->date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Affaire de Livraison :</span>
                <span class="info-value">{{ $bonLivraison->ville_livraison ?? '-' }}</span>
            </div>
        </div>
        
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Code Client</span>
                <span class="info-value dotted"></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nom Client</span>
                <span class="info-value">{{ $bonLivraison->client->raison_sociale }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mode Paiement</span>
                <span class="info-value">{{ $bonLivraison->mode_paiement }}</span>
            </div>
            <div class="info-row">
                <span class="info-value">{{ $bonLivraison->ville_livraison ?? $bonLivraison->client->ville ?? 'FES' }}</span>
            </div>
        </div>
    </div>
    
    <div class="signature-headers">
        <div class="signature-header">Bon de Commande</div>
        <div class="signature-header">Commercial</div>
        <div class="signature-header">Chauffeur</div>
        <div class="signature-header">Matricule</div>
    </div>
    
    <div class="signature-row">
        <div class="signature-cell">
            @if($bonLivraison->bonCommande)
                {{ $bonLivraison->bonCommande->numero_bon }}
            @endif
        </div>
        <div class="signature-cell">{{ $bonLivraison->commercial ?? '' }}</div>
        <div class="signature-cell">{{ $bonLivraison->chauffeur ?? '' }}</div>
        <div class="signature-cell">{{ $bonLivraison->matricule_vehicule ?? '' }}</div>
    </div>
    
    <table class="articles-table">
        <thead>
            <tr>
                <th>REF</th>
                <th>Désignation</th>
                <th class="text-center">Quantité</th>
                <th class="text-right">prix U</th>
                <th class="text-right">S-Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bonLivraison->articles as $article)
            <tr>
                <td>{{ $article->code_article }}</td>
                <td>{{ $article->designation }}</td>
                <td class="text-center">{{ number_format($article->quantite, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($article->prix_unitaire, 2, ',', ' ') }} DH</td>
                <td class="text-right">{{ number_format($article->sous_total, 2, ',', ' ') }} DH</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals-box">
        <div class="total-row">
            <span>Total Quantités:</span>
            <span>{{ number_format($bonLivraison->total_quantites, 0, ',', ' ') }}</span>
        </div>
        <div class="total-row final">
            <span>TOTAL TTC:</span>
            <span>{{ number_format($bonLivraison->total_general, 2, ',', ' ') }} DH</span>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>



