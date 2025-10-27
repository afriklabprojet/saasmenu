#!/bin/bash

# RestroSaaS Addons Deployment Script
# Quick deployment script for addons

echo "🚀 RestroSaaS Addons Deployment"
echo "==============================="

# Check if in production mode
if [ "$APP_ENV" = "production" ]; then
    echo "📦 Running production deployment..."
    ./setup-production.sh
else
    echo "🛠️ Running development setup..."
    ./quick-start.sh
fi

echo "✅ Deployment completed!"
