<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }
    .header { background: #0f1720; padding: 20px 28px; }
    .header table { width: 100%; }
    .header img { height: 34px; }
    .header .company { color: #ffffff; font-size: 10px; text-align: right; line-height: 1.6; }
    .content { padding: 28px; }
    .eyebrow { color: #2aa890; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 6px; }
    .title { font-size: 22px; font-weight: bold; color: #0f1720; margin: 0 0 8px; }
    .badge { display: inline-block; background: #fef3c7; color: #92400e; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; padding: 5px 12px; border-radius: 10px; }
    .info-table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 18px 0; background: #f3f4f6; }
    .info-table td { padding: 10px 16px; font-size: 10px; }
    .info-label { color: #6b7280; text-transform: uppercase; font-size: 8px; letter-spacing: .5px; display: block; margin-bottom: 4px; }
    .box-table { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 18px -8px; }
    .box-table td { width: 50%; border: 1px solid #e5e7eb; padding: 12px 16px; vertical-align: top; font-size: 10px; line-height: 1.6; }
    .items-table { width: 100%; border-collapse: collapse; margin: 20px 0 0; }
    .items-table th { background: #0f1720; color: #ffffff; text-align: left; padding: 9px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; }
    .items-table td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
    .text-right { text-align: right; }
    .total-row td { font-weight: bold; background: #0f1720; color: #ffffff; }
    .footer { text-align: center; color: #9ca3af; font-size: 9px; padding: 18px; border-top: 1px solid #e5e7eb; margin-top: 30px; }
</style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td><img src="{{ $logoDataUri }}" alt="4LivePro Latino"></td>
                <td class="company">
                    4LivePro Latino<br>
                    {{ $companyUrl }}
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p class="eyebrow">Documento de facturación</p>
        <p class="title">Factura N.&ordm;{{ $order->id }}</p>
        <span class="badge">{{ $statusLabel }}</span>

        <table class="info-table">
            <tr>
                <td>
                    <span class="info-label">Fecha de emisión</span>
                    {{ $order->created_at->format('d/m/Y') }}
                </td>
                <td>
                    <span class="info-label">Estado</span>
                    {{ $statusLabel }}
                </td>
                <td>
                    <span class="info-label">Método de pago</span>
                    {{ $order->paymentMethod?->name ?: '—' }}
                </td>
            </tr>
        </table>

        <table class="box-table">
            <tr>
                <td>
                    <span class="info-label">Emitida por</span>
                    <strong>4LivePro Latino</strong><br>
                    {{ $companyUrl }}
                </td>
                <td>
                    <span class="info-label">Facturada a</span>
                    <strong>{{ $order->user->name }}</strong><br>
                    @if ($order->user->address_line_1)
                        {{ $order->user->address_line_1 }}<br>
                    @endif
                    {{ implode(', ', array_filter([$order->user->city, $order->user->state, $order->user->postal_code, $order->user->country])) }}<br>
                    {{ $order->user->email }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <tr>
                <th>Descripción</th>
                <th class="text-right">Importe</th>
            </tr>
            <tr>
                <td>
                    <strong>{{ $order->package->name }}</strong><br>
                    Duración: {{ $order->package->durationLabel() }}
                </td>
                <td class="text-right">${{ number_format((float) $order->amount, 2) }} USD</td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">${{ number_format((float) $order->amount, 2) }} USD</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        4LivePro Latino &middot; Factura N.&ordm;{{ $order->id }} &middot; Generada el {{ now()->format('d/m/Y') }}
    </div>
</body>
</html>
