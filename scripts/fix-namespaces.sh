#!/bin/bash

# Script to fix namespace issues in RestroSaaS controllers

echo "🔧 Fixing namespace issues in controllers..."

# Fix Addons namespace
echo "📁 Fixing Addons namespace..."
find app/Http/Controllers/Addons -name "*.php" -exec sed -i '' 's/namespace App\\Http\\Controllers\\addons;/namespace App\\Http\\Controllers\\Addons;/g' {} \;

# Fix Admin namespace
echo "📁 Fixing Admin namespace..."
find app/Http/Controllers/Admin -name "*.php" -exec sed -i '' 's/namespace App\\Http\\Controllers\\admin;/namespace App\\Http\\Controllers\\Admin;/g' {} \;

# Fix API to Api namespace
echo "📁 Fixing Api namespace..."
find app/Http/Controllers/Api -name "*.php" -exec sed -i '' 's/namespace App\\Http\\Controllers\\API;/namespace App\\Http\\Controllers\\Api;/g' {} \;

echo "✅ Namespace fixes completed!"

# Regenerate autoloader
echo "🔄 Regenerating autoloader..."
composer dump-autoload --quiet

echo "🎉 All namespace issues fixed!"