<?php

namespace Admin\Http\Controllers\Api;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderAgreement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ServiceProviderAgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceProviderAgreement::query()
            ->with('serviceProvider');

        if ($request->filled('service_provider_id')) {
            $query->where('service_provider_id', $request->service_provider_id);
        }

        $agreements = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($agreements);
    }

    public function show(ServiceProviderAgreement $agreement)
    {
        $agreement->load('serviceProvider');

        return response()->json([
            'data' => $agreement,
        ]);
    }

    public function template()
    {
        $template = $this->getAgreementTemplate();

        return response()->json([
            'content' => $template,
        ]);
    }

    public function updateTemplate(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        // Store agreement template in settings or a config table
        // For now, we'll use cache or a file
        file_put_contents(
            storage_path('app/agreement_template.html'),
            $validated['content']
        );

        return response()->json([
            'message' => 'Agreement template updated successfully',
        ]);
    }

    protected function getAgreementTemplate()
    {
        $templatePath = storage_path('app/agreement_template.html');

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        // Default template
        return <<<HTML
<h1>SERVICE PROVIDER AGREEMENT</h1>

<p>This Agreement is entered into between:</p>

<p><strong>The Company:</strong> [Company Name]<br>
<strong>The Service Provider:</strong> {{provider_name}}</p>

<h2>1. SERVICES</h2>
<p>The Service Provider agrees to provide educational content recording services including but not limited to:</p>
<ul>
    <li>Recording video lessons according to the Malawian Secondary School Syllabus</li>
    <li>Preparing lesson plans as required</li>
    <li>Following quality standards set by the Company</li>
</ul>

<h2>2. INTELLECTUAL PROPERTY</h2>
<p>The Service Provider hereby acknowledges and agrees that:</p>
<ul>
    <li>All content created during the engagement belongs exclusively to the Company</li>
    <li>The Service Provider retains no rights to the recorded materials</li>
    <li>The Company may use, modify, distribute, and commercialize all content without restriction</li>
    <li>The Service Provider waives any moral rights to the content</li>
</ul>

<h2>3. COMPENSATION</h2>
<p>The Service Provider will be compensated at the agreed hourly rate for:</p>
<ul>
    <li>Time spent recording lessons</li>
    <li>Time spent preparing lesson plans (if applicable)</li>
</ul>

<h2>4. CONFIDENTIALITY</h2>
<p>The Service Provider agrees to maintain confidentiality of all proprietary information, methods, and materials.</p>

<h2>5. TERMINATION</h2>
<p>Either party may terminate this agreement with written notice. Upon termination, all intellectual property rights remain with the Company.</p>

<h2>6. ACKNOWLEDGMENT</h2>
<p>By signing below, the Service Provider acknowledges that they have read, understood, and agree to all terms of this Agreement.</p>

<p><strong>Date:</strong> {{current_date}}</p>
HTML;
    }
}
