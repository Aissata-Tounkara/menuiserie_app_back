{{-- resources/views/factures/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 9mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.28;
            font-size: 12px;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 18px 22px;
            margin-bottom: 16px;
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
            width: 52px;
            height: 52px;
            display: inline-block;
            text-align: center;
            line-height: 52px;
            font-size: 24px;
            font-weight: bold;
            border-radius: 8px;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        .company-info {
            display: inline-block;
            vertical-align: middle;
            max-width: calc(100% - 90px);
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }
        
        .company-tagline {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .company-details {
            font-size: 10px;
            line-height: 1.35;
            opacity: 0.95;
        }
        
        .facture-title {
            display: table-cell;
            vertical-align: middle;
            width: 35%;
            text-align: right;
        }
        
        .facture-title h1 {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 2px;
        }
        
        .emission-date {
            background: rgba(255, 255, 255, 0.15);
            padding: 7px 12px;
            border-radius: 5px;
            display: inline-block;
        }
        
        .emission-date-label {
            font-size: 11px;
            opacity: 0.85;
            margin-bottom: 2px;
        }
        
        .emission-date-value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .content {
            padding: 0 6px;
        }
        
        .info-section {
            margin-bottom: 14px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .client-info, .facture-details {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        
        .client-info {
            padding-right: 10px;
        }
        
        .facture-details {
            padding-left: 10px;
        }
        
        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #14b8a6;
        }
        
        .info-item {
            margin-bottom: 4px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 116px;
        }
        
        .info-value {
            color: #333;
        }
        
        .articles-section {
            margin: 14px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        
        thead {
            background: #2c3e50;
            color: white;
        }
        
        th {
            padding: 8px 6px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }
        
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        td {
            padding: 7px 6px;
            font-size: 11px;
            vertical-align: top;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals-section {
            margin-top: 12px;
            text-align: right;
        }
        
        .total-row {
            padding: 6px 0;
            font-size: 12px;
        }
        
        .total-label {
            display: inline-block;
            width: 170px;
            text-align: right;
            padding-right: 10px;
            font-weight: bold;
            color: #555;
        }
        
        .total-value {
            display: inline-block;
            width: 120px;
            text-align: right;
            font-weight: bold;
        }
        
        .final-total {
            background: #14b8a6;
            color: white;
            padding: 10px 14px;
            margin-top: 8px;
            border-radius: 5px;
            display: inline-block;
            min-width: 300px;
        }
        
        .final-total .total-label,
        .final-total .total-value {
            color: white;
            font-size: 15px;
        }

        .amount-in-words {
            margin-top: 14px;
            padding: 10px 12px;
            font-size: 11px;
            color: #2c3e50;
            background: #f5f7f8;
            border: 1px solid #d9e2e7;
            border-radius: 4px;
        }

        .footer {
            margin-top: 14px;
            text-align: center;
            font-size: 10px;
            color: #777;
            padding: 10px 4px 0;
            border-top: 1px solid #ddd;
        }

        .compact body,
        body.compact {
            font-size: 10.5px;
            line-height: 1.18;
        }

        body.compact .header {
            padding: 14px 18px;
            margin-bottom: 12px;
        }

        body.compact .company-details,
        body.compact td,
        body.compact th,
        body.compact .info-item,
        body.compact .total-row {
            font-size: 10px;
        }

        body.compact td,
        body.compact th {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        body.compact .section-title {
            font-size: 13px;
            margin-bottom: 6px;
        }

        body.compact .articles-section,
        body.compact .totals-section,
        body.compact .footer {
            margin-top: 10px;
        }

        body.ultra-compact {
            font-size: 9.5px;
            line-height: 1.08;
        }

        body.ultra-compact .header {
            padding: 12px 15px;
            margin-bottom: 10px;
        }

        body.ultra-compact .logo-box {
            width: 42px;
            height: 42px;
            line-height: 42px;
            font-size: 20px;
        }

        body.ultra-compact .company-name {
            font-size: 18px;
        }

        body.ultra-compact .company-tagline,
        body.ultra-compact .company-details,
        body.ultra-compact td,
        body.ultra-compact th,
        body.ultra-compact .info-item,
        body.ultra-compact .total-row,
        body.ultra-compact .amount-in-words,
        body.ultra-compact .footer {
            font-size: 9px;
        }

        body.ultra-compact td,
        body.ultra-compact th {
            padding: 4px;
        }

        body.ultra-compact .section-title {
            font-size: 12px;
            margin-bottom: 5px;
        }

        body.ultra-compact .info-section,
        body.ultra-compact .articles-section,
        body.ultra-compact .totals-section,
        body.ultra-compact .footer {
            margin-top: 8px;
            margin-bottom: 8px;
        }

        body.ultra-compact .final-total {
            padding: 8px 12px;
        }
    </style>
</head>
<body class="{{ $densityClass ?? 'normal' }}">
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
                    <!-- <div class="info-item">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ $facture->client->adresse }}</span>
                    </div> -->
                    <div class="info-item">
                        <span class="info-label">Ville:</span>
                        <span class="info-value">{{ $facture->client->ville }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value">{{ $facture->client->telephone }}</span>
                    </div>
                    <!-- @if($facture->client->email)
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $facture->client->email }}</span>
                    </div>
                    @endif -->
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
                    <!-- <div class="info-item">
                        <span class="info-label">Date d'échéance:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}</span>
                    </div> -->
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

        {{-- 📊 SECTION PAIEMENTS ET SOLDE --}}
<div class="totals-section">
    @if($facture->tva > 0)
    <div class="total-row">
        <span class="total-label">TVA ({{ $facture->tva }}%)</span>
        <span class="total-value">{{ number_format($facture->montant_ht * $facture->tva / 100, 2, ',', ' ') }} FCFA</span>
    </div>
    @endif
    <div class="total-row">
        <span class="total-label">Total TTC</span>
        <span class="total-value">{{ number_format($facture->montant_ttc, 2, ',', ' ') }} FCFA</span>
    </div>
    
    {{-- 💰 NOUVEAU : Historique des paiements --}}
    @if($facture->paiements->isNotEmpty())
    <div class="total-row" style="margin-top: 20px; padding-top: 15px; border-top: 2px dashed #ccc;">
        <span class="total-label" style="color: #2c3e50;">Total payé</span>
        <span class="total-value" style="color: #27ae60;">{{ number_format($facture->total_paye, 2, ',', ' ') }} FCFA</span>
    </div>

    
   <div class="final-total" 
        @if($facture->reste_a_payer > 0)
            style="background: #e67e22;"
        @else
            style="background: #27ae60;"
        @endif
    >
        <span class="total-label">Reste à payer</span>
        <span class="total-value">{{ number_format($facture->reste_a_payer, 2, ',', ' ') }} FCFA</span>
   </div>
    
    {{-- 📋 Tableau des paiements --}}
    <div style="margin-top: 25px;">
        <div class="section-title" style="font-size: 16px; margin-bottom: 10px;">Historique des paiements</div>
        <table style="font-size: 11px;">
            <thead>
                <tr style="background: #ecf0f1;">
                    <th style="padding: 8px; text-align: left;">Date</th>
                    <th style="padding: 8px; text-align: left;">Heure</th>
                    <th style="padding: 8px; text-align: left;">Mode</th>
                    <th style="padding: 8px; text-align: right;">Montant</th>
                    <th style="padding: 8px; text-align: left;">Réf.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture->paiements as $paiement)
                <tr>
                    <td style="padding: 8px;">{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                    <td style="padding: 8px;">
                        {{ $paiement->created_at ? $paiement->created_at->copy()->timezone('Africa/Bamako')->format('H:i:s') : '-' }}
                    </td>
                    <td style="padding: 8px;">{{ $paiement->mode_paiement }}</td>
                    <td style="padding: 8px; text-align: right; color: #27ae60; font-weight: bold;">
                        {{ number_format($paiement->montant, 2, ',', ' ') }} FCFA
                    </td>
                    <td style="padding: 8px; font-size: 10px;">{{ $paiement->reference ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    {{-- Aucun paiement enregistré --}}
    <div class="final-total" style="background: #e74c3c; margin-top: 10px;">
        <span class="total-label">Reste à payer</span>
        <span class="total-value">{{ number_format($facture->montant_ttc, 2, ',', ' ') }} FCFA</span>
    </div>
    <p style="margin-top: 15px; font-size: 12px; color: #7f8c8d; font-style: italic;">
        ⚠️ Aucun paiement enregistré pour cette facture
    </p>
    @endif
</div>
    </div>

    <div class="content">
        <div class="amount-in-words">
            <strong>Arrêté la présente facture à la somme de :</strong>
            {{ number_format($facture->montant_ttc, 2, ',', ' ') }} FCFA
            ({{ $montantEnLettres }})
        </div>
    </div>

    <div class="footer">
        Merci pour votre confiance | TOUARA - Menuiserie Aluminium<br>
        Pour toute question concernant cette facture, contactez-nous au 79 06 44 89
    </div>
</body>
</html>
