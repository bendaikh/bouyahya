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
            padding: 20px 25px;
            color: #000;
            background: white;
            font-size: 11px;
        }
        
        /* Header Section */
        .header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .logo-section {
            flex-shrink: 0;
        }
        
        .logo-image {
            width: 100px;
            height: auto;
            object-fit: contain;
        }
        
        /* Houses icon styling */
        .houses-icon {
            width: 95px;
            height: 75px;
        }
        
        .title-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            text-align: center;
            padding-top: 10px;
        }
        
        .company-name {
            font-family: 'Times New Roman', Times, serif;
            font-size: 24px;
            font-weight: bold;
            color: #00A89D;
            letter-spacing: 2px;
            text-decoration: underline;
            text-decoration-color: #00A89D;
            text-underline-offset: 3px;
        }
        
        .company-subtitle {
            color: #E5A54B;
            font-size: 11px;
            font-style: italic;
            margin-top: 2px;
        }
        
        /* Info Section - Two boxes side by side */
        .info-section {
            display: flex;
            gap: 25px;
            margin-bottom: 15px;
        }
        
        .info-box-left {
            flex: 1;
            border: 1px solid #333;
            padding: 10px 12px;
            background: #fff;
        }
        
        .info-box-right {
            flex: 1;
            border: 1px solid #999;
            padding: 10px 12px;
            background: #fff;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 6px;
            font-size: 11px;
            line-height: 1.4;
            align-items: baseline;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #000;
            white-space: nowrap;
        }
        
        .info-label-right {
            color: #000;
            min-width: 100px;
            white-space: nowrap;
        }
        
        .info-value {
            color: #000;
            margin-left: 8px;
        }
        
        .info-value.underlined {
            border-bottom: 1px solid #333;
            padding-bottom: 1px;
            margin-left: 10px;
            flex: 1;
            min-width: 80px;
        }
        
        .info-value-bold {
            font-weight: bold;
            color: #000;
            margin-left: 10px;
        }
        
        .city-value {
            text-align: center;
            font-weight: bold;
            margin-top: 8px;
            font-size: 13px;
            padding-top: 5px;
        }
        
        /* Transport/Commercial Info Row with Teal Background */
        .transport-header {
            display: flex;
            background: linear-gradient(to bottom, #00A89D, #009688);
            margin-bottom: 0;
        }
        
        .transport-header-cell {
            flex: 1;
            padding: 8px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            color: white;
            border-right: 1px solid rgba(255,255,255,0.3);
        }
        
        .transport-header-cell:last-child {
            border-right: none;
        }
        
        .transport-values {
            display: flex;
            border: 1px solid #ddd;
            border-top: none;
            margin-bottom: 15px;
        }
        
        .transport-value-cell {
            flex: 1;
            padding: 8px 10px;
            text-align: center;
            font-size: 10px;
            border-right: 1px solid #ddd;
            min-height: 30px;
            background: #fff;
        }
        
        .transport-value-cell:last-child {
            border-right: none;
        }
        
        /* Articles Table */
        .articles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .articles-table th {
            background: linear-gradient(to bottom, #00A89D, #009688);
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        
        .articles-table th.text-center {
            text-align: center;
        }
        
        .articles-table th.text-right {
            text-align: right;
        }
        
        .articles-table td {
            padding: 10px;
            font-size: 10px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        
        .articles-table tbody tr:first-child td {
            padding-top: 12px;
        }
        
        .articles-table .text-right {
            text-align: right;
        }
        
        .articles-table .text-center {
            text-align: center;
        }
        
        .articles-table .ref-cell {
            color: #00A89D;
            font-weight: 500;
        }
        
        .articles-table .designation-cell {
            color: #00A89D;
        }
        
        /* Totals Box */
        .totals-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
        
        .totals-box {
            width: 250px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px 18px;
            background: #fff;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 11px;
            align-items: center;
        }
        
        .total-label {
            color: #666;
        }
        
        .total-value {
            color: #000;
            text-align: right;
        }
        
        .total-row.final {
            font-size: 13px;
            font-weight: bold;
            margin-top: 6px;
            padding-top: 10px;
            border-top: 1px solid #e5e5e5;
        }
        
        .total-row.final .total-label {
            color: #00A89D;
            font-weight: bold;
        }
        
        .total-row.final .total-value {
            color: #00A89D;
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
                background: #00A89D !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .transport-header-cell {
                background: #00A89D !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .articles-table th {
                background: #00A89D !important;
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
            @if($appLogo && file_exists(storage_path('app/public/' . $appLogo)))
                <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" class="logo-image">
            @else
                <!-- Houses icon SVG matching the design -->
                <svg class="houses-icon" viewBox="0 0 100 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Back house (teal) -->
                    <path d="M10 45 L35 22 L60 45 L60 70 L10 70 Z" fill="#00A89D"/>
                    <path d="M10 45 L35 22 L60 45" stroke="#00A89D" stroke-width="2" fill="none"/>
                    <!-- Roof overhang -->
                    <path d="M5 47 L35 18 L65 47" stroke="#00A89D" stroke-width="3" fill="none" stroke-linecap="round"/>
                    <!-- Windows -->
                    <rect x="18" y="50" width="10" height="12" fill="white"/>
                    <rect x="38" y="50" width="10" height="12" fill="white"/>
                    
                    <!-- Front house (green) -->
                    <path d="M40 50 L65 27 L90 50 L90 75 L40 75 Z" fill="#10a37f"/>
                    <path d="M35 52 L65 23 L95 52" stroke="#10a37f" stroke-width="3" fill="none" stroke-linecap="round"/>
                    <!-- Windows -->
                    <rect x="48" y="55" width="10" height="12" fill="white"/>
                    <rect x="68" y="55" width="10" height="12" fill="white"/>
                    <!-- Chimney -->
                    <rect x="80" y="32" width="6" height="15" fill="#10a37f"/>
                </svg>
            @endif
        </div>
        <div class="title-section">
            <div class="company-name">SERVICE COMMERCIAL</div>
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
                <span class="info-value underlined">{{ $bonLivraison->client->code_client ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label-right">Nom Client</span>
                <span class="info-value-bold">{{ $bonLivraison->client->raison_sociale }}</span>
            </div>
            <div class="info-row">
                <span class="info-label-right">Mode Paiement</span>
                <span class="info-value-bold">{{ $bonLivraison->mode_paiement ?? 'Chèque' }}</span>
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
                <th style="width: 70px;">REF</th>
                <th>Désignation</th>
                <th class="text-center" style="width: 90px;">Quantité</th>
                <th class="text-right" style="width: 90px;">prix U</th>
                <th class="text-right" style="width: 110px;">S-Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bonLivraison->articles as $article)
            <tr>
                <td class="ref-cell">{{ $article->code_article }}</td>
                <td class="designation-cell">{{ $article->designation }}</td>
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
