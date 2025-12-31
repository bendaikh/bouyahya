<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charge - {{ $charge->reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #333;
            background: white;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #1f2937;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background: #1f2937;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.8;
        }
        .content {
            padding: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .info-item label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 5px;
        }
        .info-item span {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }
        .amount-box {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .amount-box label {
            font-size: 14px;
            opacity: 0.9;
            display: block;
            margin-bottom: 5px;
        }
        .amount-box .amount {
            font-size: 36px;
            font-weight: bold;
        }
        .observation-box {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .observation-box label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 10px;
        }
        .observation-box p {
            color: #374151;
            line-height: 1.6;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-box label {
            font-size: 12px;
            color: #6b7280;
            display: block;
            margin-bottom: 50px;
        }
        .signature-box .line {
            border-top: 1px solid #333;
        }
        @media print {
            body {
                padding: 0;
            }
            .container {
                border: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FICHE DE CHARGE</h1>
            <p>Référence: {{ $charge->reference }}</p>
        </div>

        <div class="content">
            <div class="info-grid">
                <div class="info-item">
                    <label>Date</label>
                    <span>{{ $charge->date->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <label>Heure</label>
                    <span>{{ $charge->heure }}</span>
                </div>
                <div class="info-item">
                    <label>Opérateur</label>
                    <span>{{ $charge->operateur ?: '-' }}</span>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <label>Type</label>
                    <span>{{ $charge->type?->libelle ?: '-' }}</span>
                </div>
                <div class="info-item">
                    <label>N°</label>
                    <span>{{ $charge->numero ?: '-' }}</span>
                </div>
                <div class="info-item">
                    <label>Compte Caisse</label>
                    <span>{{ $charge->compteCaisse?->libelle ?: '-' }}</span>
                </div>
            </div>

            <div class="info-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="info-item">
                    <label>Libellé</label>
                    <span>{{ $charge->libelle }}</span>
                </div>
                <div class="info-item">
                    <label>Bénéficiaire</label>
                    <span>{{ $charge->beneficiaire ?: '-' }}</span>
                </div>
            </div>

            <div class="amount-box">
                <label>Montant TTC</label>
                <div class="amount">{{ number_format($charge->montant, 2, ',', ' ') }} DH</div>
            </div>

            @if($charge->observation)
            <div class="observation-box">
                <label>Observation</label>
                <p>{{ $charge->observation }}</p>
            </div>
            @endif

            <div class="footer">
                <div class="signature-box">
                    <label>Signature Opérateur</label>
                    <div class="line"></div>
                </div>
                <div class="signature-box">
                    <label>Signature Responsable</label>
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

