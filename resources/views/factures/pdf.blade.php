{{-- resources/views/factures/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 30px 40px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .logo-section {
            display: table-cell;
            vertical-align: middle;
            width: 65%;
        }
        
        .logo-box {
            background: #14b8a6;
            width: 70px;
            height: 70px;
            display: inline-block;
            text-align: center;
            line-height: 70px;
            font-size: 32px;
            font-weight: bold;
            border-radius: 8px;
            margin-right: 15px;
            vertical-align: middle;
        }
        
        .company-info {
            display: inline-block;
            vertical-align: middle;
            max-width: calc(100% - 90px);
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }
        
        .company-tagline {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .company-details {
            font-size: 11px;
            line-height: 1.5;
            opacity: 0.95;
        }
        
        .facture-title {
            display: table-cell;
            vertical-align: middle;
            width: 35%;
            text-align: right;
        }
        
        .facture-title h1 {
            font-size: 38px;
            font-weight: bold;
            margin-bottom: 12px;
            letter-spacing: 2px;
        }
        
        .emission-date {
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 18px;
            border-radius: 5px;
            display: inline-block;
        }
        
        .emission-date-label {
            font-size: 11px;
            opacity: 0.85;
            margin-bottom: 2px;
        }
        
        .emission-date-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .content {
            padding: 0 40px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .client-info, .facture-details {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        
        .client-info {
            padding-right: 20px;
        }
        
        .facture-details {
            padding-left: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 3px solid #14b8a6;
        }
        
        .info-item {
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 140px;
        }
        
        .info-value {
            color: #333;
        }
        
        .articles-section {
            margin: 30px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        thead {
            background: #2c3e50;
            color: white;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: bold;
        }
        
        tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }
        
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        td {
            padding: 12px;
            font-size: 13px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .total-row {
            padding: 10px 0;
            font-size: 14px;
        }
        
        .total-label {
            display: inline-block;
            width: 200px;
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
            color: #555;
        }
        
        .total-value {
            display: inline-block;
            width: 150px;
            text-align: right;
            font-weight: bold;
        }
        
        .final-total {
            background: #14b8a6;
            color: white;
            padding: 15px 20px;
            margin-top: 10px;
            border-radius: 5px;
            display: inline-block;
            min-width: 370px;
        }
        
        .final-total .total-label,
        .final-total .total-value {
            color: white;
            font-size: 18px;
        }
        
        .conditions-section {
            margin-top: 40px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        
        .conditions-row {
            display: table;
            width: 100%;
        }
        
        .bank-info, .payment-terms {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            font-size: 12px;
        }
        
        .bank-info {
            padding-right: 20px;
        }
        
        .payment-terms {
            padding-left: 20px;
        }
        
        .subsection-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .conditions-item {
            margin-bottom: 6px;
            line-height: 1.6;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #777;
            padding: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-box">T</div>
                <div class="company-info">
                    <div class="company-name">TOUARA</div>
                    <div class="company-tagline">Menuiserie Aluminium</div>
                    <div class="company-details">
                        Chez Moussa TOUNKARA<br>
                        COMMERÇANT – FAUTEUILLE BUREAUTIQUE – ALUMINIUM ALTRADE,<br>
                        BRONZE, CHAMPAGNE – LAC BLANC – FER – BOIS & DIVERS<br>
                        HIPPODROME Rue 224 Tél : 79 06 44 89 – NIF : 0822099M<br>
                        Bamako – République du Mali
                    </div>
                </div>
            </div>
            <div class="facture-title">
                <h1>FACTURE</h1>
                <div class="emission-date">
                    <div class="emission-date-label">Date d'émission</div>
                    <div class="emission-date-value">{{ \Carbon\Carbon::parse($facture->date_emission)->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="info-section">
            <div class="info-row">
                <div class="client-info">
                    <div class="section-title">Informations Client</div>
                    <div class="info-item">
                        <span class="info-label">Nom du client:</span>
                        <span class="info-value">{{ $facture->client->nom }} {{ $facture->client->prenom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ $facture->client->adresse }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ville:</span>
                        <span class="info-value">{{ $facture->client->ville }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value">{{ $facture->client->telephone }}</span>
                    </div>
                    @if($facture->client->email)
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $facture->client->email }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="facture-details">
                    <div class="section-title">Détails de la Facture</div>
                    <div class="info-item">
                        <span class="info-label">Numéro de facture:</span>
                        <span class="info-value">{{ $facture->numero_facture }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date d'émission:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($facture->date_emission)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date d'échéance:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Statut:</span>
                        <span class="info-value">{{ $facture->statut }}</span>
                    </div>
                    @if($facture->mode_paiement)
                    <div class="info-item">
                        <span class="info-label">Mode de paiement:</span>
                        <span class="info-value">{{ $facture->mode_paiement }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="articles-section">
            <div class="section-title">Articles / Services</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 15%;" class="text-right">Quantité</th>
                        <th style="width: 17.5%;" class="text-right">Prix unitaire</th>
                        <th style="width: 17.5%;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facture->articles as $article)
                    <tr>
                        <td>{{ $article->designation }}</td>
                        <td class="text-right">{{ $article->quantite }}</td>
                        <td class="text-right">{{ number_format($article->prix_unitaire, 2, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ number_format($article->total, 2, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">Sous-total</span>
                <span class="total-value">{{ number_format($sousTotal, 2, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    <div class="footer">
        Merci pour votre confiance | TOUARA - Menuiserie Aluminium<br>
        Pour toute question concernant cette facture, contactez-nous au 79 06 44 89
    </div>
</body>
</html>