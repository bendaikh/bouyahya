<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de CHARGE - {{ $numeroBonCharge }}</title>
    <style>
        @media print {
            @page {
                margin: 10mm;
                size: A4;
            }
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            max-width: 210mm;
            margin: 0 auto;
            padding: 15px;
            background: #fff;
        }
        
        /* Header */
        .header {
            text-align: right;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
        }
        
        .title {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .title-bon {
            color: #000;
        }
        
        .title-de {
            color: #000;
            font-weight: normal;
        }
        
        .title-charge {
            color: #000;
        }
        
        /* Info Header - Teal background */
        .info-header {
            background-color: #008080;
            color: white;
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .info-header-row {
            display: table-row;
        }
        
        .info-header-cell {
            display: table-cell;
            padding: 8px 15px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.3);
            width: 25%;
        }
        
        .info-header-cell:last-child {
            border-right: none;
        }
        
        .info-header-label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        /* Info Values Row */
        .info-values {
            background-color: #e8e8e8;
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .info-values-row {
            display: table-row;
        }
        
        .info-values-cell {
            display: table-cell;
            padding: 10px 15px;
            text-align: center;
            border-right: 1px solid #ccc;
            width: 25%;
            font-weight: bold;
            font-size: 13px;
        }
        
        .info-values-cell:last-child {
            border-right: none;
        }
        
        /* Print timestamp */
        .print-info {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
        
        /* Main Table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        /* Table Header - Light Blue */
        .main-table thead th {
            background-color: #87CEEB;
            color: #000;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #000;
        }
        
        .main-table thead th.text-center {
            text-align: center;
        }
        
        /* Emplacement Row */
        .emplacement-row td {
            background-color: #f5f5f5;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
        }
        
        /* Section Header - Blue */
        .section-header td {
            background-color: #4169E1;
            color: white;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 12px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-top: 1px solid #000;
        }
        
        /* Data Rows */
        .data-row td {
            padding: 6px 10px;
            font-size: 11px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #ddd;
            background-color: #fff;
        }
        
        .data-row:last-of-type td {
            border-bottom: 1px solid #000;
        }
        
        .data-row td.text-right {
            text-align: right;
        }
        
        .data-row td.text-center {
            text-align: center;
        }
        
        /* Section Total Row */
        .section-total td {
            background-color: #fff;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 12px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 2px solid #000;
        }
        
        .section-total td.total-label {
            text-align: left;
            color: #4169E1;
        }
        
        .section-total td.total-value {
            text-align: left;
            border-left: 2px solid #000;
        }
        
        /* Grand Total */
        .grand-total-container {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        
        .grand-total-box {
            border: 2px solid #000;
            padding: 12px 25px;
            font-size: 14px;
            font-weight: bold;
            background-color: #f8f8f8;
        }
        
        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #4169E1;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #3155c9;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Imprimer</button>
    
    <!-- Header -->
    <div class="header">
        <h1 class="title">
            <span class="title-bon">Bon</span> 
            <span class="title-de">de</span> 
            <span class="title-charge">CHARGE</span>
        </h1>
    </div>
    
    <!-- Info Header with Teal Background -->
    <div class="info-header">
        <div class="info-header-row">
            <div class="info-header-cell">
                <span class="info-header-label">DATE</span>
            </div>
            <div class="info-header-cell">
                <span class="info-header-label">N°</span>
            </div>
            <div class="info-header-cell">
                <span class="info-header-label">CHAUFFEUR</span>
            </div>
            <div class="info-header-cell">
                <span class="info-header-label">MATRICULE</span>
            </div>
        </div>
    </div>
    
    <!-- Info Values -->
    <div class="info-values">
        <div class="info-values-row">
            <div class="info-values-cell">{{ $date->format('d/m/Y') }}</div>
            <div class="info-values-cell">{{ $numeroBonCharge }}</div>
            <div class="info-values-cell">-</div>
            <div class="info-values-cell">-</div>
        </div>
    </div>
    
    <!-- Print Timestamp -->
    <div class="print-info">
        Imprimé le: <strong>{{ now()->format('d/m/Y') }}</strong> &nbsp;&nbsp; {{ now()->format('H:i') }}
    </div>
    
    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 10%;">Reference</th>
                <th style="width: 30%;">Désignation</th>
                <th style="width: 10%;" class="text-center">Qté</th>
                <th style="width: 8%;" class="text-center">Unité</th>
                <th style="width: 8%;" class="text-center">P/U</th>
                <th style="width: 17%;">VILLE</th>
                <th style="width: 17%;">TELEPHONE</th>
            </tr>
        </thead>
        <tbody>
            <!-- Emplacement -->
            <tr class="emplacement-row">
                <td colspan="7">Emplacement</td>
            </tr>
            
            @php $grandTotal = 0; @endphp
            
            @foreach($groupedItems as $group)
                <!-- Section Header -->
                <tr class="section-header">
                    <td colspan="7">{{ strtoupper($group['designation']) }}</td>
                </tr>
                
                <!-- Data Rows -->
                @foreach($group['items'] as $index => $item)
                    <tr class="data-row">
                        <td>{{ $group['reference'] }}</td>
                        <td>{{ $item['client'] }}</td>
                        <td class="text-right">{{ number_format($item['quantite'], 2, '.', '') }}</td>
                        <td class="text-center">{{ $item['unite'] }}</td>
                        <td class="text-center"></td>
                        <td>{{ $item['ville'] ?: '' }}</td>
                        <td>{{ $item['telephone'] ?: '' }}</td>
                    </tr>
                @endforeach
                
                <!-- Section Total -->
                <tr class="section-total">
                    <td class="total-label" colspan="2">{{ strtoupper($group['designation']) }}</td>
                    <td class="total-value">Total</td>
                    <td colspan="1"><strong>{{ number_format($group['total'], 2, '.', '') }}</strong></td>
                    <td colspan="3"></td>
                </tr>
                
                @php $grandTotal += $group['total']; @endphp
            @endforeach
        </tbody>
    </table>
    
    <!-- Grand Total -->
    <div class="grand-total-container">
        <div class="grand-total-box">
            Total: <strong>{{ number_format($grandTotal, 2, '.', '') }}</strong>
        </div>
    </div>
</body>
</html>
