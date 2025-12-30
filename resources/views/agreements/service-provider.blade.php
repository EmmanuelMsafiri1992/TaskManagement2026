<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Provider Agreement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 15px;
            letter-spacing: 2px;
        }
        .agreement-number {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        h2 {
            font-size: 13px;
            color: #4F46E5;
            margin: 25px 0 10px 0;
            text-transform: uppercase;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 12px;
            margin: 15px 0 8px 0;
        }
        p {
            margin-bottom: 10px;
            text-align: justify;
        }
        .parties-table {
            width: 100%;
            margin: 20px 0;
        }
        .parties-table td {
            width: 50%;
            vertical-align: top;
            padding: 15px;
        }
        .party-box {
            background: #F9FAFB;
            padding: 15px;
            border-radius: 5px;
        }
        .party-title {
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
            font-size: 12px;
        }
        .party-detail {
            margin-bottom: 5px;
        }
        .party-label {
            color: #666;
            font-size: 10px;
        }
        .terms-list {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .terms-list li {
            margin-bottom: 8px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .payment-table th,
        .payment-table td {
            border: 1px solid #D1D5DB;
            padding: 10px;
            text-align: left;
        }
        .payment-table th {
            background: #F3F4F6;
            font-weight: bold;
        }
        .highlight-box {
            background: #EEF2FF;
            border-left: 4px solid #4F46E5;
            padding: 15px;
            margin: 15px 0;
        }
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
        }
        .signature-table td {
            width: 45%;
            padding: 20px;
            vertical-align: top;
        }
        .signature-table td:nth-child(2) {
            width: 10%;
        }
        .signature-box {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 10px;
        }
        .signature-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .signature-name {
            color: #666;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .date-line {
            margin-top: 20px;
        }
        .witness-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #D1D5DB;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div>{{ $company['address'] }}</div>
        <div class="document-title">Service Provider Agreement</div>
        <div class="agreement-number">Agreement No: {{ $agreement_number }}</div>
    </div>

    <p>This Service Provider Agreement ("Agreement") is entered into as of <strong>{{ $effective_date }}</strong> by and between:</p>

    <table class="parties-table">
        <tr>
            <td>
                <div class="party-box">
                    <div class="party-title">THE COMPANY</div>
                    <div class="party-detail"><strong>{{ $company['name'] }}</strong></div>
                    <div class="party-detail">{{ $company['address'] }}</div>
                    <div class="party-detail">Email: {{ $company['email'] }}</div>
                    <div class="party-detail">Phone: {{ $company['phone'] }}</div>
                </div>
            </td>
            <td>
                <div class="party-box">
                    <div class="party-title">THE SERVICE PROVIDER</div>
                    <div class="party-detail"><strong>{{ $provider->name }}</strong></div>
                    <div class="party-detail">{{ $provider->address ?? 'Address on file' }}</div>
                    <div class="party-detail">Email: {{ $provider->email }}</div>
                    <div class="party-detail">Phone: {{ $provider->phone ?? 'N/A' }}</div>
                    <div class="party-detail">National ID: {{ $provider->national_id ?? 'N/A' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <h2>1. Scope of Services</h2>
    <p>The Service Provider agrees to provide educational content creation services including but not limited to:</p>
    <ul class="terms-list">
        <li>Recording educational video content for assigned subjects and topics</li>
        <li>Preparing lesson plans according to the Malawian curriculum</li>
        <li>Ensuring high-quality audio and video recordings</li>
        <li>Meeting recording schedules and deadlines as assigned</li>
        <li>Participating in quality review sessions when required</li>
    </ul>

    <div class="highlight-box" style="background: #FEF3C7; border-left-color: #D97706;">
        <h3 style="color: #92400E; margin-top: 0;">1.1 Full Syllabus Completion Commitment</h3>
        <p style="margin-bottom: 0;"><strong>The Service Provider expressly commits to completing the ENTIRE syllabus for each subject assigned.</strong> This includes recording all topics, lessons, and learning objectives as defined in the Malawian Secondary School curriculum. Partial completion of any assigned subject is not acceptable. The Service Provider acknowledges that signing this agreement constitutes a binding commitment to deliver complete curriculum coverage for all assigned subjects.</p>
    </div>

    <h2>2. Compensation</h2>
    <div class="highlight-box">
        <table class="payment-table">
            <tr>
                <th>Description</th>
                <th>Amount (MK)</th>
            </tr>
            <tr>
                <td>Total Agreed Amount</td>
                <td><strong>{{ number_format($provider->total_agreed_amount, 2) }}</strong></td>
            </tr>
            <tr>
                <td>Payment Preference</td>
                <td>{{ ucfirst(str_replace('_', ' ', $provider->payment_preference)) }}</td>
            </tr>
            @if($provider->payment_preference === 'monthly' && $provider->monthly_amount)
            <tr>
                <td>Monthly Payment Amount</td>
                <td>{{ number_format($provider->monthly_amount, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <h3>2.1 Payment Method</h3>
    @if($provider->payment_method === 'bank')
    <p>Payments will be made via <strong>Bank Transfer</strong> to:</p>
    <ul class="terms-list">
        <li>Bank: {{ $provider->bank_name ?? 'To be provided' }}</li>
        <li>Account Name: {{ $provider->bank_account_name ?? 'To be provided' }}</li>
        <li>Account Number: {{ $provider->bank_account_number ?? 'To be provided' }}</li>
        <li>Branch: {{ $provider->bank_branch ?? 'To be provided' }}</li>
    </ul>
    @elseif($provider->payment_method === 'mobile_money')
    <p>Payments will be made via <strong>Mobile Money</strong> to:</p>
    <ul class="terms-list">
        <li>Provider: {{ $provider->mobile_money_provider ?? 'To be provided' }}</li>
        <li>Name: {{ $provider->mobile_money_name ?? 'To be provided' }}</li>
        <li>Number: {{ $provider->mobile_money_number ?? 'To be provided' }}</li>
    </ul>
    @else
    <p>Payment method to be confirmed with the Service Provider.</p>
    @endif

    <h2>3. Term and Termination</h2>
    <p>This Agreement shall commence on the Effective Date and continue until the completion of all assigned work or until terminated by either party with 30 days written notice.</p>

    <h2>4. Intellectual Property</h2>
    <p>All content created under this Agreement shall be the exclusive property of the Company. The Service Provider agrees to assign all rights, title, and interest in such content to the Company.</p>

    <h2>5. Confidentiality</h2>
    <p>The Service Provider agrees to maintain confidentiality regarding all proprietary information, teaching methods, and materials provided by the Company.</p>

    <h2>6. Quality Standards</h2>
    <p>The Service Provider agrees to maintain professional standards in all deliverables. Content that does not meet quality requirements may be subject to revision requests or rejection.</p>

    <h2>7. General Provisions</h2>
    <ul class="terms-list">
        <li>This Agreement constitutes the entire agreement between the parties.</li>
        <li>Any amendments must be in writing and signed by both parties.</li>
        <li>This Agreement shall be governed by the laws of Malawi.</li>
        <li>Any disputes shall be resolved through mediation before pursuing legal action.</li>
    </ul>

    <div class="signature-section">
        <h2>Signatures</h2>
        <p>IN WITNESS WHEREOF, the parties have executed this Agreement as of the date first written above.</p>

        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-label">FOR THE COMPANY:</div>
                    <div class="signature-box">
                        <div class="signature-name">Authorized Representative</div>
                        <div>{{ $company['name'] }}</div>
                    </div>
                    <div class="date-line">Date: ____________________</div>
                </td>
                <td></td>
                <td>
                    <div class="signature-label">SERVICE PROVIDER:</div>
                    <div class="signature-box">
                        <div class="signature-name">{{ $provider->name }}</div>
                        <div>National ID: {{ $provider->national_id ?? '________________' }}</div>
                    </div>
                    <div class="date-line">Date: {{ $signed_date ?? '____________________' }}</div>
                </td>
            </tr>
        </table>

        <div class="witness-section">
            <h3>Witness (Optional)</h3>
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-box">
                            <div class="signature-name">Witness Name: ____________________</div>
                            <div>ID Number: ____________________</div>
                        </div>
                    </td>
                    <td></td>
                    <td>
                        <div class="signature-box">
                            <div class="signature-name">Witness Signature</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>This document was generated on {{ $generated_at }} | Agreement Reference: {{ $agreement_number }}</p>
        <p>{{ $company['name'] }} - {{ $company['address'] }} - {{ $company['email'] }}</p>
    </div>
</body>
</html>
