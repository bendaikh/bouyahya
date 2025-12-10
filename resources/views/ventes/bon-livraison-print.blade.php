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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            color: #333;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1a4d1a;
        }
        
        .company-info h1 {
            color: #1a4d1a;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .document-info {
            text-align: right;
        }
        
        .document-info h2 {
            color: #1a4d1a;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .document-info .number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .document-info .date {
            color: #666;
            margin-top: 5px;
        }
        
        .reference-badge {
            display: inline-block;
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .info-section {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-box {
            flex: 1;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
        }
        
        .info-box.driver {
            background: linear-gradient(135deg, #e8f4fd 0%, #f8f9fa 100%);
            border-color: #b3d9f7;
        }
        
        .info-box h3 {
            color: #1a4d1a;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .info-box.driver h3 {
            color: #2563eb;
            border-color: #b3d9f7;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            width: 120px;
            font-size: 13px;
        }
        
        .info-value {
            color: #333;
            font-size: 13px;
        }
        
        .articles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .articles-table th {
            background: #1a4d1a;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .articles-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        
        .articles-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .articles-table .text-center {
            text-align: center;
        }
        
        .articles-table .text-right {
            text-align: right;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        
        .totals-box {
            width: 300px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .total-row:last-child {
            border-bottom: none;
            padding-top: 15px;
        }
        
        .total-row.final {
            font-size: 18px;
            font-weight: bold;
            color: #1a4d1a;
        }
        
        .observations {
            background: #fff9e6;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 40px;
        }
        
        .observations h3 {
            color: #856404;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .observations p {
            color: #333;
            font-size: 13px;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }
        
        .signature-label {
            font-size: 12px;
            color: #666;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1>BOUYAHYA</h1>
            <p>Gestion Commerciale</p>
        </div>
        <div class="document-info">
            <h2>BON DE LIVRAISON</h2>
            <div class="number">{{ $bonLivraison->numero_bon }}</div>
            <div class="date">Date: {{ $bonLivraison->date->format('d/m/Y') }}</div>
            @if($bonLivraison->bonCommande)
                <div class="reference-badge">Réf: {{ $bonLivraison->bonCommande->numero_bon }}</div>
            @endif
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-box">
            <h3>Client</h3>
            <div class="info-row">
                <span class="info-label">Code:</span>
                <span class="info-value">{{ $bonLivraison->client->code_client }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nom:</span>
                <span class="info-value">{{ $bonLivraison->client->raison_sociale }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ville:</span>
                <span class="info-value">{{ $bonLivraison->ville_livraison ?? $bonLivraison->client->ville ?? '-' }}</span>
            </div>
            @if($bonLivraison->adresse_livraison)
            <div class="info-row">
                <span class="info-label">Adresse:</span>
                <span class="info-value">{{ $bonLivraison->adresse_livraison }}</span>
            </div>
            @endif
        </div>
        
        <div class="info-box driver">
            <h3>Informations Livraison</h3>
            <div class="info-row">
                <span class="info-label">Chauffeur:</span>
                <span class="info-value">{{ $bonLivraison->chauffeur ?? 'Non assigné' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Véhicule:</span>
                <span class="info-value">{{ $bonLivraison->matricule_vehicule ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone:</span>
                <span class="info-value">{{ $bonLivraison->telephone_chauffeur ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mode Paiement:</span>
                <span class="info-value">{{ $bonLivraison->mode_paiement }}</span>
            </div>
        </div>
    </div>
    
    <table class="articles-table">
        <thead>
            <tr>
                <th>Code Article</th>
                <th>Désignation</th>
                <th class="text-center">Quantité</th>
                <th class="text-right">Prix Unitaire</th>
                <th class="text-right">Sous-Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bonLivraison->articles as $article)
            <tr>
                <td>{{ $article->code_article }}</td>
                <td>{{ $article->designation }}</td>
                <td class="text-center">{{ $article->quantite }}</td>
                <td class="text-right">{{ number_format($article->prix_unitaire, 2, ',', ' ') }} DH</td>
                <td class="text-right">{{ number_format($article->sous_total, 2, ',', ' ') }} DH</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        <div class="totals-box">
            <div class="total-row">
                <span>Total Quantités:</span>
                <span>{{ $bonLivraison->total_quantites }}</span>
            </div>
            <div class="total-row final">
                <span>TOTAL TTC:</span>
                <span>{{ number_format($bonLivraison->total_general, 2, ',', ' ') }} DH</span>
            </div>
        </div>
    </div>
    
    @if($bonLivraison->observations)
    <div class="observations">
        <h3>Observations</h3>
        <p>{{ $bonLivraison->observations }}</p>
    </div>
    @endif
    
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Signature Chauffeur</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Signature Client</div>
        </div>
    </div>
    
    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }} - BOUYAHYA - Gestion Commerciale</p>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>

