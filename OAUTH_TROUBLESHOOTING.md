# OAuth Troubleshooting Guide

## Issue: "Something went wrong" after clicking "Continue"

This error occurs when the scope requested by your app is not configured in the OAuth consent screen.

## Solution:

### Step 1: Verify AdSense Management API is Enabled

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Navigate to **APIs & Services** → **Library**
3. Search for: **"AdSense Management API"**
4. If you see **"ENABLE"** button, click it
5. If you see **"MANAGE"**, it's already enabled ✓

### Step 2: Add AdSense Scope to OAuth Consent Screen

1. Go to **APIs & Services** → **OAuth consent screen**
2. Click **"EDIT APP"**
3. Click **"SAVE AND CONTINUE"** on App information page
4. On **Scopes** page:
   - Click **"ADD OR REMOVE SCOPES"**
   - Search for: `adsense`
   - Check the box for: `https://www.googleapis.com/auth/adsense.readonly`
   - Click **"UPDATE"**
   - Click **"SAVE AND CONTINUE"**
5. Click through remaining pages and save

### Step 3: Verify OAuth Credentials

1. Go to **APIs & Services** → **Credentials**
2. Click on your OAuth 2.0 Client ID
3. Verify **Authorized redirect URIs** contains:
   ```
   http://127.0.0.1:8000/auth/google/adsense/callback
   ```
4. Click **"SAVE"** if you made any changes

### Step 4: Try Again

1. Wait 30 seconds for changes to propagate
2. Go to: `http://127.0.0.1:8000/settings/adsense`
3. Click **"Connect with Google"**
4. Click **"Continue"** on the warning screen
5. Click **"Allow"** on the permissions screen

## Current Configuration

Your OAuth URL is requesting this scope:
- `https://www.googleapis.com/auth/adsense.readonly`

This scope MUST be added to your OAuth consent screen for the authorization to work.

## If You Still Get Errors

Check the browser's Developer Console (F12) → Console tab for any JavaScript errors.
