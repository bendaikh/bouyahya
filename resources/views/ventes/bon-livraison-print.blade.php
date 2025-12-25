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
            padding: 25px 30px;
            color: #000;
            background: white;
            font-size: 12px;
        }
        
        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        
        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
        }
        
        .logo-image {
            width: 100px;
            height: auto;
            object-fit: contain;
        }
        
        .logo-placeholder {
            width: 100px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Houses icon styling */
        .houses-icon {
            width: 90px;
            height: auto;
        }
        
        .service-text {
            font-family: 'Brush Script MT', 'Lucida Handwriting', cursive;
            font-size: 28px;
            color: #0891b2;
            font-weight: normal;
            margin-top: -5px;
        }
        
        .commercial-text {
            font-size: 11px;
            color: #000;
            margin-left: 25px;
            margin-top: -3px;
            font-weight: normal;
        }
        
        .company-info {
            text-align: right;
        }
        
        .company-name {
            color: #0d6943;
            font-size: 26px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
            font-family: Arial, sans-serif;
            letter-spacing: 1px;
        }
        
        .company-subtitle {
            color: #d97706;
            font-size: 14px;
            font-weight: 500;
            margin-top: 2px;
        }
        
        /* Info Section - Two boxes side by side */
        .info-section {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .info-box-left {
            flex: 0.9;
            border: 1px solid #000;
            padding: 12px 15px;
            background: #fff;
        }
        
        .info-box-right {
            flex: 1.1;
            padding: 12px 15px;
            background: #fff;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #000;
            min-width: 130px;
        }
        
        .info-label-right {
            font-weight: bold;
            color: #000;
            min-width: 110px;
        }
        
        .info-value {
            color: #000;
            flex: 1;
        }
        
        .info-value.dotted {
            border-bottom: 1px dotted #000;
            flex: 1;
            margin-left: 10px;
            min-height: 14px;
        }
        
        .info-value-bold {
            font-weight: bold;
            color: #000;
        }
        
        .city-value {
            text-align: center;
            font-weight: bold;
            margin-top: 5px;
            font-size: 13px;
        }
        
        /* Transport/Commercial Info Row with Green Background */
        .transport-header {
            display: flex;
            background: linear-gradient(to bottom, #10a37f, #0d8a6c);
            margin-bottom: 0;
            border: 1px solid #0d8a6c;
        }
        
        .transport-header-cell {
            flex: 1;
            padding: 8px 12px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            color: white;
            border-right: 1px solid rgba(255,255,255,0.3);
        }
        
        .transport-header-cell:last-child {
            border-right: none;
        }
        
        .transport-values {
            display: flex;
            border: 1px solid #ccc;
            border-top: none;
            margin-bottom: 20px;
        }
        
        .transport-value-cell {
            flex: 1;
            padding: 10px 12px;
            text-align: center;
            font-size: 11px;
            border-right: 1px solid #ccc;
            min-height: 35px;
        }
        
        .transport-value-cell:last-child {
            border-right: none;
        }
        
        /* Articles Table */
        .articles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .articles-table th {
            background: linear-gradient(to bottom, #10a37f, #0d8a6c);
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #0d8a6c;
        }
        
        .articles-table th.text-center {
            text-align: center;
        }
        
        .articles-table th.text-right {
            text-align: right;
        }
        
        .articles-table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 11px;
            border-left: none;
            border-right: none;
        }
        
        .articles-table tbody tr {
            border-bottom: 1px solid #ddd;
        }
        
        .articles-table tbody tr:last-child {
            border-bottom: 1px solid #ddd;
        }
        
        .articles-table .text-right {
            text-align: right;
        }
        
        .articles-table .text-center {
            text-align: center;
        }
        
        /* Totals Box */
        .totals-container {
            display: flex;
            justify-content: flex-end;
        }
        
        .totals-box {
            width: 280px;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px 20px;
            background: #fff;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 12px;
            align-items: center;
        }
        
        .total-label {
            color: #666;
        }
        
        .total-value {
            font-weight: normal;
            color: #000;
        }
        
        .total-row.final {
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
            padding-top: 12px;
            border-top: 1px solid #e5e5e5;
        }
        
        .total-row.final .total-label {
            color: #0d8a6c;
            font-weight: bold;
        }
        
        .total-row.final .total-value {
            color: #0d8a6c;
            font-weight: bold;
        }
        
        @media print {
            body {
                padding: 15px 20px;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print {
                display: none;
            }
            
            .transport-header {
                background: #10a37f !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .articles-table th {
                background: #10a37f !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Logo and Company Name -->
    <div class="header">
        <div class="logo-section">
            <div class="logo-container">
                @if($appLogo && file_exists(storage_path('app/public/' . $appLogo)))
                    <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" class="logo-image">
                @else
                    <!-- Default houses icon SVG -->
                    <svg class="houses-icon" viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Back house -->
                        <path d="M25 50 L55 25 L85 50 L85 80 L25 80 Z" fill="#0891b2"/>
                        <path d="M25 50 L55 25 L85 50" stroke="#0891b2" stroke-width="3" fill="none"/>
                        <rect x="35" y="55" width="12" height="15" fill="white"/>
                        <rect x="55" y="55" width="12" height="15" fill="white"/>
                        <!-- Front house -->
                        <path d="M50 55 L80 30 L110 55 L110 85 L50 85 Z" fill="#10a37f"/>
                        <path d="M50 55 L80 30 L110 55" stroke="#10a37f" stroke-width="3" fill="none"/>
                        <rect x="60" y="60" width="12" height="15" fill="white"/>
                        <rect x="80" y="60" width="12" height="15" fill="white"/>
                        <!-- Chimney -->
                        <rect x="95" y="38" width="8" height="15" fill="#10a37f"/>
                    </svg>
                @endif
            </div>
            <div class="service-text">Service</div>
            <div class="commercial-text">Commercial</div>
        </div>
        <div class="company-info">
            <div class="company-name">{{ $appName }}</div>
            <div class="company-subtitle">Materiaux de construction</div>
        </div>
    </div>
    
    <!-- Info Section - Two boxes -->
    <div class="info-section">
        <!-- Left Box - Delivery Info -->
        <div class="info-box-left">
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
                <span class="info-value">{{ $bonLivraison->affaire_livraison ?? '' }}</span>
            </div>
        </div>
        
        <!-- Right Box - Client Info -->
        <div class="info-box-right">
            <div class="info-row">
                <span class="info-label-right">Code Client</span>
                <span class="info-value dotted">{{ $bonLivraison->client->code_client ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label-right">Nom Client</span>
                <span class="info-value-bold">{{ $bonLivraison->client->raison_sociale }}</span>
            </div>
            <div class="info-row">
                <span class="info-label-right">Mode Paiement</span>
                <span class="info-value-bold">{{ $bonLivraison->mode_paiement ?? 'Chq 90 jrs' }}</span>
            </div>
            <div class="city-value">{{ strtoupper($bonLivraison->ville_livraison ?? $bonLivraison->client->ville ?? 'FES') }}</div>
        </div>
    </div>
    
    <!-- Transport/Commercial Header Row -->
    <div class="transport-header">
        <div class="transport-header-cell">Bon de Commande</div>
        <div class="transport-header-cell">Commercial</div>
        <div class="transport-header-cell">Chauffeur</div>
        <div class="transport-header-cell">Matricule</div>
    </div>
    
    <div class="transport-values">
        <div class="transport-value-cell">
            @if($bonLivraison->bonCommande)
                {{ $bonLivraison->bonCommande->numero_bon }}
            @endif
        </div>
        <div class="transport-value-cell">{{ $bonLivraison->commercial ?? '' }}</div>
        <div class="transport-value-cell">{{ $bonLivraison->chauffeur ?? '' }}</div>
        <div class="transport-value-cell">{{ $bonLivraison->matricule_vehicule ?? '' }}</div>
    </div>
    
    <!-- Articles Table -->
    <table class="articles-table">
        <thead>
            <tr>
                <th style="width: 80px;">REF</th>
                <th>Désignation</th>
                <th class="text-center" style="width: 100px;">Quantité</th>
                <th class="text-right" style="width: 100px;">prix U</th>
                <th class="text-right" style="width: 120px;">S-Total</th>
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
    
    <!-- Totals Box -->
    <div class="totals-container">
        <div class="totals-box">
            <div class="total-row">
                <span class="total-label">Total Quantités:</span>
                <span class="total-value">{{ number_format($bonLivraison->total_quantites, 0, ',', ' ') }}</span>
            </div>
            <div class="total-row final">
                <span class="total-label">TOTAL TTC:</span>
                <span class="total-value">{{ number_format($bonLivraison->total_general, 2, ',', ' ') }} DH</span>
            </div>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
