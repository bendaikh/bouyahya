<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Commande - {{ $bonCommande->numero_bon }}</title>
    <style>
        @media print {
            @page {
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .info-box {
            width: 48%;
        }
        
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .info-box p {
            margin: 5px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        table td {
            border: 1px solid #000;
            padding: 8px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        
        .totals table {
            width: 300px;
            margin-left: auto;
        }
        
        .totals table td {
            border: none;
            padding: 5px 10px;
        }
        
        .totals .total-row {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Imprimer</button>
    
    <div class="header">
        <h1>Bon de Commande Client</h1>
        <p><strong>N°:</strong> {{ $bonCommande->numero_bon }}</p>
    </div>
    
    <div class="info-section">
        <div class="info-box">
            <h3>Informations du bon</h3>
            <p><strong>Date:</strong> {{ $bonCommande->date->format('d/m/Y') }}</p>
            <p><strong>Mode de paiement:</strong> {{ $bonCommande->mode_paiement }}</p>
            <p><strong>Échéance:</strong> {{ $bonCommande->echeance }}</p>
            <p><strong>Statut:</strong> {{ $bonCommande->statut }}</p>
        </div>
        
        <div class="info-box">
            <h3>Client</h3>
            <p><strong>Raison sociale:</strong> {{ $bonCommande->client->raison_sociale }}</p>
            <p><strong>Gérant:</strong> {{ $bonCommande->client->nom_gerant }}</p>
            <p><strong>Ville:</strong> {{ $bonCommande->client->ville }}</p>
            @if($bonCommande->client->ice)
                <p><strong>ICE:</strong> {{ $bonCommande->client->ice }}</p>
            @endif
        </div>
    </div>
    
    <table>
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
            @foreach($bonCommande->articles as $article)
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
        <table>
            <tr>
                <td><strong>Total Quantités:</strong></td>
                <td class="text-right">{{ $bonCommande->total_quantites }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Total Général TTC:</strong></td>
                <td class="text-right">{{ number_format($bonCommande->total_general, 2, ',', ' ') }} DH</td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <p>Ce document est généré automatiquement par le système de gestion Bouyahya</p>
        <p>Imprimé le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>

