<?php
// Simple test page to show the OAuth URL
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\AdSenseService;

$service = new AdSenseService();
$authUrl = $service->getAuthUrl();

// Parse URL to show details
$parts = parse_url($authUrl);
parse_str($parts['query'] ?? '', $params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>OAuth Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .highlight { background: yellow; padding: 2px 5px; }
        .url { word-break: break-all; }
    </style>
</head>
<body>
    <h1>OAuth Debug Information</h1>

    <div class="box">
        <h2>Full OAuth URL:</h2>
        <div class="url"><?= htmlspecialchars($authUrl) ?></div>
    </div>

    <div class="box">
        <h2>Redirect URI Being Sent:</h2>
        <div class="highlight"><?= htmlspecialchars($params['redirect_uri'] ?? 'NOT SET') ?></div>
    </div>

    <div class="box">
        <h2>Client ID:</h2>
        <div><?= htmlspecialchars($params['client_id'] ?? 'NOT SET') ?></div>
    </div>

    <div class="box">
        <h2>Scope:</h2>
        <div><?= htmlspecialchars($params['scope'] ?? 'NOT SET') ?></div>
    </div>

    <div class="box">
        <h2>What you need in Google Cloud Console:</h2>
        <p>Go to: <strong>APIs & Services → Credentials → Your OAuth Client</strong></p>
        <p>Under "Authorized redirect URIs", add EXACTLY this:</p>
        <div class="highlight"><?= htmlspecialchars($params['redirect_uri'] ?? 'NOT SET') ?></div>
        <p style="color: red; margin-top: 10px;">
            ⚠️ Must be EXACT match - no extra spaces, slashes, or characters!
        </p>
    </div>

    <div class="box">
        <h2>Test the OAuth flow:</h2>
        <a href="<?= htmlspecialchars($authUrl) ?>" style="display: inline-block; padding: 10px 20px; background: #4285f4; color: white; text-decoration: none; border-radius: 5px;">
            Click Here to Test OAuth
        </a>
    </div>

    <div class="box">
        <h2>Current Time:</h2>
        <div><?= date('Y-m-d H:i:s') ?></div>
        <p><small>Wait at least 5 minutes after saving changes in Google Cloud Console</small></p>
    </div>
</body>
</html>
