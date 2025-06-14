# Laravel Cloud - Fix Vite Manifest Error

## Problem
Error: `Vite manifest not found at: /var/www/html/public/build/manifest.json`

## Root Cause
The Vite build process is not running properly during Laravel Cloud deployment, causing the manifest.json file to be missing on the production server.

## Solution

### 1. Deployment Configuration Files Created

- **`.laravelcloud`** - Laravel Cloud build configuration
- **`cloud.yaml`** - Alternative Laravel Cloud configuration
- **`deploy.sh`** - Enhanced deployment script with Vite verification
- **`.env.production.example`** - Production environment variables template

### 2. Critical Laravel Cloud Environment Variables

Add these to your Laravel Cloud environment:

```env
# Essential for Vite
VITE_APP_NAME="${APP_NAME}"
VITE_APP_URL="${APP_URL}"
ASSET_URL="${APP_URL}"

# Production optimizations
APP_ENV=production
APP_DEBUG=false
NODE_ENV=production
```

### 3. Build Process Verification

The enhanced `deploy.sh` script now:
- ✅ Verifies Node.js and npm installation
- ✅ Runs `npm ci` for consistent installs
- ✅ Executes `npm run build` to generate Vite assets
- ✅ Checks that `public/build/manifest.json` exists after build
- ✅ Provides detailed error logging if build fails

### 4. Laravel Cloud Deployment Commands

In your Laravel Cloud deployment settings, ensure these commands run:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Build frontend assets
npm run build

# Verify build success
test -f public/build/manifest.json || exit 1

# Laravel optimizations
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 5. Troubleshooting Steps

If the error persists:

1. **Check Laravel Cloud Build Logs**:
   - Look for npm install errors
   - Check if `npm run build` completed successfully
   - Verify Node.js version (should be 20+)

2. **Verify Environment Variables**:
   ```bash
   echo $VITE_APP_NAME
   echo $NODE_ENV
   ```

3. **Manual Build Test**:
   ```bash
   # SSH into Laravel Cloud
   cd /home/forge/default
   npm install
   npm run build
   ls -la public/build/manifest.json
   ```

4. **Check Vite Configuration**:
   - Ensure `vite.config.ts` exists and is valid
   - Verify Laravel Vite plugin configuration
   - Check that input files exist in `resources/js/`

### 6. Alternative Solutions

If Vite continues to fail, you can temporarily disable Vite by:

1. **Replace `@vite()` directive** in `resources/views/app.blade.php`:
   ```blade
   {{-- Replace this: --}}
   @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
   
   {{-- With this: --}}
   <link rel="stylesheet" href="{{ asset('css/app.css') }}">
   <script src="{{ asset('js/app.js') }}" defer></script>
   ```

2. **Use Laravel Mix** instead of Vite (not recommended)

### 7. Production Optimizations

- Use `npm ci` instead of `npm install` for faster, reliable installs
- Set `NODE_ENV=production` for optimized builds
- Enable Laravel caching commands in production
- Use Redis for session/cache storage

## File Changes Summary

- ✅ **Enhanced `deploy.sh`** - Better error handling and verification
- ✅ **Added `.laravelcloud`** - Build configuration
- ✅ **Added `cloud.yaml`** - Laravel Cloud configuration
- ✅ **Added `.env.production.example`** - Environment template
- ✅ **Updated `package.json`** - Added production build script

## Next Steps

1. Commit and push these changes to your repository
2. Update environment variables in Laravel Cloud dashboard
3. Trigger a new deployment
4. Monitor build logs for any remaining issues

The enhanced deployment script will now provide detailed feedback about the build process and fail early if Vite assets aren't generated properly.