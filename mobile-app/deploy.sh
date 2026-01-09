#!/bin/bash

# Deployment script for Familyledger Mobile App (Web)
# Run this on your Plesk server after git pull

set -e

echo "📦 Installing dependencies..."
npm install

echo "🔨 Building web app for production..."
EXPO_PUBLIC_RELEASE_CHANNEL=production npx expo export --platform web

echo "🔧 Fixing asset paths for /mobile subdirectory..."
find dist -name "*.html" -exec sed -i 's|src="/_expo|src="/mobile/_expo|g' {} \;
find dist -name "*.html" -exec sed -i 's|href="/_expo|href="/mobile/_expo|g' {} \;

echo "✅ Build complete! Files are in ./dist/"
echo ""
echo "Deploy with: cp -r dist/* /path/to/public/mobile/"
