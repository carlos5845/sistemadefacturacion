<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->series }}-{{ $document->number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            color: #1f2937;
            margin: 0;
            padding: 40px;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header Layout */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            max-height: 60px;
            margin-bottom: 10px;
            object-fit: contain;
        }

        .company-name {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .company-details {
            color: #4b5563;
            font-size: 12px;
        }

        .document-box {
            text-align: right;
            padding: 15px 0;
            min-width: 200px;
        }

        .document-type {
            font-size: 14px;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .document-number {
            font-size: 24px;
            font-weight: 400;
            color: #111827;
            margin-top: 5px;
        }

        /* Customer Info */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            font-weight: 600;
            margin: 0 0 8px 0;
        }

        .info-row {
            margin-bottom: 4px;
        }

        .info-label {
            color: #6b7280;
            margin-right: 5px;
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            text-align: left;
            padding: 12px 8px;
            font-weight: 500;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 16px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 300px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .totals-row.final {
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 12px;
            font-weight: 600;
            font-size: 15px;
            color: #111827;
        }

        .totals-label {
            color: #6b7280;
        }

        /* Footer */
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 11px;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="company-info">
                @if($company->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Logo" class="company-logo">
                @endif
                <div class="company-name">{{ $company->business_name }}</div>
                <div class="company-details">
                    <div>RUC: {{ $company->ruc }}</div>
                    <div>{{ $company->address }}</div>
                    <!-- <div>{{ $company->email ?? '' }}</div> -->
                </div>
            </div>
            
            <div class="document-box">
                <div class="document-type">{{ $document->documentType->name ?? 'COMPROBANTE' }}</div>
                <div class="document-number">{{ $document->series }}-{{ $document->number }}</div>
                <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">
                    {{ $document->issue_date->format('d/m/Y') }}
                </div>
            </div>
        </header>

        <!-- Details -->
        <div class="info-grid">
            <div class="info-block">
                @if($document->customer)
                    <h3>Cliente</h3>
                    <div class="info-row">
                        <strong>{{ $document->customer->name }}</strong>
                    </div>
                    <div class="info-row">
                        @switch($document->customer->identity_type)
                            @case('1') DNI @break
                            @case('6') RUC @break
                            @case('4') C.E. @break
                            @case('7') Pasaporte @break
                            @default Doc.
                        @endswitch: {{ $document->customer->identity_number }}
                    </div>
                    <div class="info-row">
                        {{ $document->customer->address ?? '' }}
                    </div>
                @endif
            </div>
            <div class="info-block" style="text-align: right;">
                <h3>Detalles</h3>
                <div class="info-row">
                    <span class="info-label">Moneda:</span> {{ $document->currency }}
                </div>
                <div class="info-row">
                    <span class="info-label">Forma de Pago:</span> Contado
                </div>
            </div>
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 80px;">Cant</th>
                    <th class="text-center" style="width: 80px;">Unid</th>
                    <th>Descripción</th>
                    <th class="text-right" style="width: 100px;">P. Unit</th>
                    <th class="text-right" style="width: 100px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($document->items as $item)
                <tr>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ $item->unit_type_id ?? 'NIU' }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="totals-row">
                    <span class="totals-label">Op. Gravada</span>
                    <span>{{ number_format($document->total_taxed, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span class="totals-label">IGV (18%)</span>
                    <span>{{ number_format($document->total_igv, 2) }}</span>
                </div>
                <div class="totals-row final">
                    <span>Total</span>
                    <span>{{ number_format($document->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Representación Impresa del Comprobante Electrónico | Autorizado mediante R.I. 034-005-0005315
        </div>
    </div>
</body>
</html>
