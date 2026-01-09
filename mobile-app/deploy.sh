#!/bin/bash

# Deployment script for Familyledger Mobile App (Web)
# Run this on your Plesk server after git pull

set -e

echo "📦 Installing dependencies..."
npm install

echo "🔨 Building web app for production..."
EXPO_PUBLIC_RELEASE_CHANNEL=production npx expo export --platform web

echo "✅ Build complete! Files are in ./dist/"
echo ""
echo "Configure Plesk to serve the 'dist' folder as the document root."
echo "Or copy dist/* to your web directory."
