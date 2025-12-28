<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #666;
        }
        .receipt-title {
            text-align: center;
            margin: 20px 0;
        }
        .receipt-title h2 {
            font-size: 18px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .receipt-number {
            background: #F3F4F6;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 5px;
            margin-top: 10px;
        }
        .receipt-number span {
            font-weight: bold;
            color: #4F46E5;
        }
        .section {
            margin: 25px 0;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 5px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .info-table .label {
            width: 40%;
            color: #666;
        }
        .info-table .value {
            width: 60%;
            font-weight: 500;
        }
        .amount-box {
            background: #4F46E5;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .amount-box .label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .amount-box .amount {
            font-size: 28px;
            font-weight: bold;
        }
        .two-columns {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
            padding-right: 20px;
        }
        .column:last-child {
            padding-right: 0;
            padding-left: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background: #DEF7EC;
            color: #03543F;
        }
        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .signature-section {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
        }
        .balance-info {
            background: #F9FAFB;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .balance-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .balance-row:last-child {
            margin-bottom: 0;
        }
        .balance-label {
            display: table-cell;
            width: 60%;
        }
        .balance-value {
            display: table-cell;
            width: 40%;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div class="company-details">
            {{ $company['address'] }}<br>
            Phone: {{ $company['phone'] }} | Email: {{ $company['email'] }}
        </div>
    </div>

    <div class="receipt-title">
        <h2>Payment Receipt</h2>
        <div class="receipt-number">
            Receipt No: <span>{{ $payment->receipt_number }}</span>
        </div>
    </div>

    <div class="two-columns">
        <div class="column">
            <div class="section">
                <div class="section-title">Paid To</div>
                <table class="info-table">
                    <tr>
                        <td class="label">Name:</td>
                        <td class="value">{{ $payment->serviceProvider->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td class="value">{{ $payment->serviceProvider->email }}</td>
                    </tr>
                    <tr>
                        <td class="label">Phone:</td>
                        <td class="value">{{ $payment->serviceProvider->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">National ID:</td>
                        <td class="value">{{ $payment->serviceProvider->national_id ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="column">
            <div class="section">
                <div class="section-title">Payment Details</div>
                <table class="info-table">
                    <tr>
                        <td class="label">Payment Date:</td>
                        <td class="value">{{ $payment->payment_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Payment For:</td>
                        <td class="value">{{ $payment->month_for ?? 'Service Payment' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Method:</td>
                        <td class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value">
                            <span class="status-badge status-{{ $payment->status }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($payment->reference_number)
                    <tr>
                        <td class="label">Reference:</td>
                        <td class="value">{{ $payment->reference_number }}</td>
                    </tr>
                    @endif
                    @if($payment->transaction_id)
                    <tr>
                        <td class="label">Transaction ID:</td>
                        <td class="value">{{ $payment->transaction_id }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">Amount Paid</div>
        <div class="amount">MK {{ number_format($payment->amount, 2) }}</div>
    </div>

    <div class="balance-info">
        <div class="balance-row">
            <div class="balance-label">Total Agreed Amount:</div>
            <div class="balance-value">MK {{ number_format($payment->serviceProvider->total_agreed_amount, 2) }}</div>
        </div>
        <div class="balance-row">
            <div class="balance-label">Total Paid to Date:</div>
            <div class="balance-value">MK {{ number_format($payment->serviceProvider->total_paid, 2) }}</div>
        </div>
        <div class="balance-row" style="border-top: 1px solid #D1D5DB; padding-top: 8px; margin-top: 8px;">
            <div class="balance-label"><strong>Balance Remaining:</strong></div>
            <div class="balance-value" style="color: #DC2626;">MK {{ number_format($payment->serviceProvider->balance_remaining, 2) }}</div>
        </div>
    </div>

    @if($payment->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <p>{{ $payment->notes }}</p>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                Authorized Signature
            </div>
        </div>
        <div class="signature-box" style="width: 10%;"></div>
        <div class="signature-box">
            <div class="signature-line">
                Recipient Signature
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated receipt. Generated on {{ $generated_at }}</p>
        <p>{{ $company['name'] }} - {{ $company['address'] }}</p>
    </div>
</body>
</html>
