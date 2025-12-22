<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            margin-bottom: 40px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-col h3 {
            color: #6B7280;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .info-col p {
            margin: 5px 0;
        }
        .info-col.text-right {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        thead {
            background-color: #F3F4F6;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #6B7280;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #E5E7EB;
        }
        th.text-right, td.text-right {
            text-align: right;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        .totals {
            width: 400px;
            margin-left: auto;
            margin-top: 20px;
        }
        .totals-row {
            display: table;
            width: 100%;
            padding: 8px 0;
        }
        .totals-row.total {
            border-top: 2px solid #333;
            padding-top: 12px;
            margin-top: 8px;
            font-size: 16px;
            font-weight: bold;
        }
        .totals-label {
            display: table-cell;
            width: 60%;
        }
        .totals-value {
            display: table-cell;
            width: 40%;
            text-align: right;
        }
        .notes-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
        }
        .notes-section h4 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #4F46E5;
        }
        .notes-section p {
            color: #6B7280;
            white-space: pre-wrap;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-draft { background-color: #F3F4F6; color: #4B5563; }
        .status-sent { background-color: #DBEAFE; color: #1E40AF; }
        .status-accepted { background-color: #D1FAE5; color: #065F46; }
        .status-rejected { background-color: #FEE2E2; color: #991B1B; }
        .status-expired { background-color: #FEF3C7; color: #92400E; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $quotation->quotation_number }}</h1>
        <p>
            <span class="status-badge status-{{ $quotation->status }}">{{ ucfirst($quotation->status) }}</span>
        </p>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <h3>Bill To</h3>
            <p><strong>{{ $quotation->customer_name }}</strong></p>
            @if($quotation->customer_email)
                <p>{{ $quotation->customer_email }}</p>
            @endif
            @if($quotation->customer_phone)
                <p>{{ $quotation->customer_phone }}</p>
            @endif
            @if($quotation->customer_address)
                <p>{{ $quotation->customer_address }}</p>
            @endif
        </div>

        <div class="info-col text-right">
            <h3>Quotation Details</h3>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('M d, Y') }}</p>
            <p><strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</p>
            <p><strong>Currency:</strong> {{ $quotation->currency }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Subtotal:</div>
            <div class="totals-value">{{ number_format($quotation->subtotal, 2) }} {{ $quotation->currency }}</div>
        </div>

        @if($quotation->tax_rate > 0)
        <div class="totals-row">
            <div class="totals-label">Tax ({{ $quotation->tax_rate }}%):</div>
            <div class="totals-value">{{ number_format($quotation->tax_amount, 2) }} {{ $quotation->currency }}</div>
        </div>
        @endif

        @if($quotation->discount_amount > 0)
        <div class="totals-row">
            <div class="totals-label">Discount:</div>
            <div class="totals-value">-{{ number_format($quotation->discount_amount, 2) }} {{ $quotation->currency }}</div>
        </div>
        @endif

        <div class="totals-row total">
            <div class="totals-label">Total:</div>
            <div class="totals-value">{{ number_format($quotation->total_amount, 2) }} {{ $quotation->currency }}</div>
        </div>
    </div>

    @if($quotation->notes || $quotation->terms)
    <div class="notes-section">
        @if($quotation->notes)
        <div style="margin-bottom: 20px;">
            <h4>Notes</h4>
            <p>{{ $quotation->notes }}</p>
        </div>
        @endif

        @if($quotation->terms)
        <div>
            <h4>Terms & Conditions</h4>
            <p>{{ $quotation->terms }}</p>
        </div>
        @endif
    </div>
    @endif
</body>
</html>
