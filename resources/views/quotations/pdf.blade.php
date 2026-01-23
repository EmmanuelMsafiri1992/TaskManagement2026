<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quotation_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 30px;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .float-left { float: left; }
        .float-right { float: right; }
        .mb-0 { margin-bottom: 0; }
        .mb-5 { margin-bottom: 5px; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mb-30 { margin-bottom: 30px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
        .p-20 { padding: 20px; }
        .p-30 { padding: 30px; }

        .w-50 { width: 50%; }
        .w-100 { width: 100%; }

        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            text-align: left;
        }

        .items-table th {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table td {
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-table {
            width: 250px;
            margin-left: auto;
        }

        .totals-table td {
            padding: 5px 0;
        }

        .totals-table .total-row {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
        }

        /* Notes */
        .notes-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .notes-section h4 {
            font-size: 12px;
            margin-bottom: 8px;
        }

        .notes-section p {
            color: #6b7280;
            font-size: 10px;
            white-space: pre-wrap;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background-color: #f3f4f6; color: #4b5563; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-accepted { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-expired { background-color: #fef3c7; color: #92400e; }

        @php
            $color = $quotation->color ?? '#2568ef';
        @endphp

        /* ============================================
           STYLE 1 - Classic (Default)
           ============================================ */
        .style_1 .header-row {
            margin-bottom: 30px;
        }
        .style_1 .company-info {
            float: right;
            text-align: right;
        }
        .style_1 .logo-section {
            float: left;
        }
        .style_1 .divider {
            border-bottom: 2px solid {{ $color }};
            margin: 20px 0;
            clear: both;
        }
        .style_1 .items-table th {
            background-color: {{ $color }};
            color: #fff;
        }

        /* ============================================
           STYLE 2 - Modern (Side color bar)
           ============================================ */
        .style_2 {
            border-left: 5px solid {{ $color }};
            padding-left: 25px;
        }
        .style_2 .items-table th {
            background-color: {{ $color }};
            color: #fff;
        }
        .style_2 .quotation-title {
            font-size: 24px;
            color: {{ $color }};
            margin-bottom: 5px;
        }

        /* ============================================
           STYLE 3 - Colorful Header
           ============================================ */
        .style_3 .header-banner {
            background: {{ $color }};
            color: #fff;
            padding: 30px;
            margin: -30px -30px 30px -30px;
        }
        .style_3 .header-banner h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .style_3 .items-table th {
            background-color: {{ $color }};
            color: #fff;
        }

        /* ============================================
           STYLE 4 - Bordered
           ============================================ */
        .style_4 {
            border: 2px solid {{ $color }};
            padding: 20px;
        }
        .style_4 .header-section {
            border-bottom: 2px solid {{ $color }};
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .style_4 .items-table {
            border: 1px solid {{ $color }};
        }
        .style_4 .items-table th {
            background-color: {{ $color }};
            color: #fff;
        }
        .style_4 .items-table td {
            border: 1px solid #e5e7eb;
        }

        /* ============================================
           STYLE 5 - Info Banner
           ============================================ */
        .style_5 .info-banner {
            background: linear-gradient(135deg, {{ $color }} 0%, {{ $color }}dd 100%);
            color: #fff;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .style_5 .items-table th {
            background-color: {{ $color }};
            color: #fff;
            border-radius: 0;
        }
        .style_5 .items-table thead tr:first-child th:first-child {
            border-radius: 8px 0 0 0;
        }
        .style_5 .items-table thead tr:first-child th:last-child {
            border-radius: 0 8px 0 0;
        }

        /* ============================================
           STYLE 6 - Minimal
           ============================================ */
        .style_6 .header-row {
            margin-bottom: 40px;
        }
        .style_6 .items-table th {
            background-color: #f9fafb;
            color: #374151;
            border-bottom: 2px solid {{ $color }};
        }
        .style_6 .total-highlight {
            color: {{ $color }};
        }

        /* ============================================
           STYLE 7 - Split Header
           ============================================ */
        .style_7 .header-left {
            float: left;
            width: 50%;
            padding-right: 20px;
        }
        .style_7 .header-right {
            float: right;
            width: 50%;
            padding: 20px;
            background: {{ $color }};
            color: #fff;
        }
        .style_7 .items-table th {
            background-color: {{ $color }};
            color: #fff;
        }

        /* ============================================
           STYLE 8 - Elegant
           ============================================ */
        .style_8 {
            background: #fafafa;
        }
        .style_8 .inner-content {
            background: #fff;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .style_8 .elegant-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px double {{ $color }};
        }
        .style_8 .elegant-header h1 {
            font-size: 28px;
            color: {{ $color }};
            letter-spacing: 3px;
        }
        .style_8 .items-table th {
            background-color: {{ $color }};
            color: #fff;
        }
    </style>
</head>
<body class="{{ $quotation->template ?? 'style_1' }}">

    @php
        $template = $quotation->template ?? 'style_1';
        $color = $quotation->color ?? '#2568ef';
        $businessName = $quotation->business_name ?? config('app.name', 'Your Company');
    @endphp

    @if($template == 'style_1')
    <!-- STYLE 1 - Classic -->
    <div class="header-row clearfix">
        <div class="logo-section float-left">
            @if($quotation->logo && file_exists(public_path($quotation->logo)))
                <img src="{{ public_path($quotation->logo) }}" alt="{{ $businessName }}" style="max-width: 130px;">
            @else
                <h2 style="color: {{ $color }};">{{ $businessName }}</h2>
            @endif
        </div>
        <div class="company-info float-right">
            <p class="bold mb-0">QUOTATION</p>
            <p class="bold mb-5" style="color: {{ $color }};">{{ $businessName }}</p>
            @if($quotation->business_address)
                <p class="mb-0">{{ $quotation->business_address }}</p>
            @endif
            @if($quotation->business_phone)
                <p class="mb-0">{{ $quotation->business_phone }}</p>
            @endif
        </div>
    </div>
    <div class="divider"></div>

    @elseif($template == 'style_2')
    <!-- STYLE 2 - Modern -->
    <div class="header-row clearfix mb-30">
        <div class="float-left">
            @if($quotation->logo && file_exists(public_path($quotation->logo)))
                <img src="{{ public_path($quotation->logo) }}" alt="{{ $businessName }}" style="max-width: 130px;">
            @endif
            <h1 class="quotation-title">QUOTATION</h1>
            <p class="mb-0" style="font-size: 14px;">{{ $quotation->quotation_number }}</p>
        </div>
        <div class="float-right text-right">
            <p class="bold mb-5" style="font-size: 16px;">{{ $businessName }}</p>
            @if($quotation->business_address)
                <p class="mb-0">{{ $quotation->business_address }}</p>
            @endif
            @if($quotation->business_phone)
                <p class="mb-0">{{ $quotation->business_phone }}</p>
            @endif
        </div>
    </div>

    @elseif($template == 'style_3')
    <!-- STYLE 3 - Colorful Header -->
    <div class="header-banner clearfix">
        <div class="float-left">
            <h1>QUOTATION</h1>
            <p>{{ $quotation->quotation_number }}</p>
        </div>
        <div class="float-right text-right">
            <p class="bold" style="font-size: 18px;">{{ $businessName }}</p>
            @if($quotation->business_address)
                <p>{{ $quotation->business_address }}</p>
            @endif
        </div>
    </div>

    @elseif($template == 'style_4')
    <!-- STYLE 4 - Bordered -->
    <div class="header-section clearfix">
        <div class="float-left">
            @if($quotation->logo && file_exists(public_path($quotation->logo)))
                <img src="{{ public_path($quotation->logo) }}" alt="{{ $businessName }}" style="max-width: 130px;">
            @else
                <h2 style="color: {{ $color }};">{{ $businessName }}</h2>
            @endif
        </div>
        <div class="float-right text-right">
            <h2 style="color: {{ $color }};">QUOTATION</h2>
            <p>{{ $quotation->quotation_number }}</p>
        </div>
    </div>

    @elseif($template == 'style_5')
    <!-- STYLE 5 - Info Banner -->
    <div class="info-banner clearfix">
        <div class="float-left">
            <h2 style="margin-bottom: 5px;">QUOTATION</h2>
            <p>{{ $quotation->quotation_number }}</p>
        </div>
        <div class="float-right text-right">
            <p class="bold" style="font-size: 16px;">{{ $businessName }}</p>
            @if($quotation->business_phone)
                <p>{{ $quotation->business_phone }}</p>
            @endif
        </div>
    </div>

    @elseif($template == 'style_6')
    <!-- STYLE 6 - Minimal -->
    <div class="header-row clearfix">
        <div class="float-left">
            @if($quotation->logo && file_exists(public_path($quotation->logo)))
                <img src="{{ public_path($quotation->logo) }}" alt="{{ $businessName }}" style="max-width: 130px;">
            @else
                <h2>{{ $businessName }}</h2>
            @endif
        </div>
        <div class="float-right text-right">
            <h1 style="font-size: 24px; color: #6b7280; font-weight: 300;">QUOTATION</h1>
            <p style="color: {{ $color }};">{{ $quotation->quotation_number }}</p>
        </div>
    </div>
    <div style="border-bottom: 1px solid #e5e7eb; margin: 20px 0;"></div>

    @elseif($template == 'style_7')
    <!-- STYLE 7 - Split Header -->
    <div class="clearfix mb-30">
        <div class="header-left">
            @if($quotation->logo && file_exists(public_path($quotation->logo)))
                <img src="{{ public_path($quotation->logo) }}" alt="{{ $businessName }}" style="max-width: 130px; margin-bottom: 15px;">
            @endif
            <h2>{{ $businessName }}</h2>
            @if($quotation->business_address)
                <p>{{ $quotation->business_address }}</p>
            @endif
        </div>
        <div class="header-right text-right">
            <h2 style="margin-bottom: 10px;">QUOTATION</h2>
            <p class="bold">{{ $quotation->quotation_number }}</p>
            <p style="margin-top: 15px;">Date: {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('M d, Y') }}</p>
        </div>
    </div>
    <div style="clear: both;"></div>

    @elseif($template == 'style_8')
    <!-- STYLE 8 - Elegant -->
    <div class="inner-content">
        <div class="elegant-header">
            <h1>QUOTATION</h1>
            <p style="color: #6b7280; margin-top: 5px;">{{ $quotation->quotation_number }}</p>
            <p style="font-size: 14px; margin-top: 10px;"><strong>{{ $businessName }}</strong></p>
        </div>
    @endif

    <!-- Bill To & Quotation Details -->
    <div class="clearfix mb-30">
        <div class="float-left" style="width: 50%;">
            <h4 class="mb-10" style="color: {{ $color }}; font-size: 12px; text-transform: uppercase;">Bill To</h4>
            <p class="bold mb-5">{{ $quotation->customer_name }}</p>
            @if($quotation->customer_email)
                <p class="mb-0">{{ $quotation->customer_email }}</p>
            @endif
            @if($quotation->customer_phone)
                <p class="mb-0">{{ $quotation->customer_phone }}</p>
            @endif
            @if($quotation->customer_address)
                <p class="mb-0">{{ $quotation->customer_address }}</p>
            @endif
        </div>
        <div class="float-right text-right" style="width: 45%;">
            <table style="width: 100%;">
                <tr>
                    <td class="text-right" style="padding: 3px 10px 3px 0;"><strong>Quotation #:</strong></td>
                    <td style="padding: 3px 0;">{{ $quotation->quotation_number }}</td>
                </tr>
                <tr>
                    <td class="text-right" style="padding: 3px 10px 3px 0;"><strong>Date:</strong></td>
                    <td style="padding: 3px 0;">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td class="text-right" style="padding: 3px 10px 3px 0;"><strong>Valid Until:</strong></td>
                    <td style="padding: 3px 0;">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table mb-20">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-center" style="width: 15%;">Quantity</th>
                <th class="text-right" style="width: 17%;">Unit Price</th>
                <th class="text-right" style="width: 18%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>
                    {{ $item->description }}
                    @if($item->details)
                        <br><small style="color: #6b7280;">{{ $item->details }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td class="text-right" style="padding-right: 15px;"><strong>Subtotal:</strong></td>
            <td class="text-right">{{ $quotation->currency }} {{ number_format($quotation->subtotal, 2) }}</td>
        </tr>
        @if($quotation->tax_rate > 0)
        <tr>
            <td class="text-right" style="padding-right: 15px;"><strong>Tax ({{ number_format($quotation->tax_rate, 1) }}%):</strong></td>
            <td class="text-right">{{ $quotation->currency }} {{ number_format($quotation->tax_amount, 2) }}</td>
        </tr>
        @endif
        @if($quotation->discount_amount > 0)
        <tr>
            <td class="text-right" style="padding-right: 15px;"><strong>Discount:</strong></td>
            <td class="text-right" style="color: #dc2626;">-{{ $quotation->currency }} {{ number_format($quotation->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="text-right" style="padding-right: 15px; padding-top: 10px;"><strong>Total:</strong></td>
            <td class="text-right" style="padding-top: 10px; color: {{ $color }};">{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Notes & Terms -->
    @if($quotation->notes || $quotation->terms)
    <div class="notes-section">
        @if($quotation->notes)
        <div style="margin-bottom: 15px;">
            <h4 style="color: {{ $color }};">Notes</h4>
            <p>{{ $quotation->notes }}</p>
        </div>
        @endif

        @if($quotation->terms)
        <div>
            <h4 style="color: {{ $color }};">Terms & Conditions</h4>
            <p>{{ $quotation->terms }}</p>
        </div>
        @endif
    </div>
    @endif

    @if($template == 'style_8')
    </div><!-- Close inner-content for style 8 -->
    @endif

</body>
</html>
