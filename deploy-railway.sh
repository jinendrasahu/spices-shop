#!/bin/bash

echo "🚀 Deploying Anazah Spices Shop to Railway..."

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "📦 Installing Railway CLI..."
    npm install -g @railway/cli
fi

# Login to Railway (if not already logged in)
echo "🔐 Logging in to Railway..."
railway login

# Initialize Railway project
echo "🔧 Initializing Railway project..."
railway init

# Link to existing project or create new
echo "🔗 Linking project..."
railway link

# Deploy
echo "🚀 Deploying..."
railway up

echo "✅ Deployment complete!"
echo "🌐 Your app will be available at: https://your-project.railway.app"
echo "📋 Run 'railway status' to check deployment status"

