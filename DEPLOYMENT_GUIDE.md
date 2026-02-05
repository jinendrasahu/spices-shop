# 🚀 Deployment Guide - Anazah Essora Spices Shop

## Quick Deployment Options

### Option 1: Railway (Recommended - Easiest)

1. **Install Railway CLI:**
   ```bash
   npm install -g @railway/cli
   ```

2. **Login:**
   ```bash
   railway login
   ```

3. **Deploy:**
   ```bash
   railway init
   railway link
   railway up
   ```

4. **Get your URL:**
   ```bash
   railway status
   ```

**Or use Railway Dashboard:**
- Go to https://railway.app
- Click "New Project"
- Select "Deploy from GitHub repo"
- Connect: `https://github.com/jinendrasahu/spices-shop`
- Railway will auto-detect Docker setup
- Click "Deploy"

**Your app will be live at:** `https://your-project.railway.app`

---

### Option 2: Render

1. **Go to Render Dashboard:**
   - Visit https://render.com
   - Sign up/Login

2. **Create New Blueprint:**
   - Click "New +" → "Blueprint"
   - Connect GitHub: `https://github.com/jinendrasahu/spices-shop`
   - Render will detect `render.yaml`

3. **Deploy:**
   - Click "Apply"
   - Wait for deployment

**Your app will be live at:** `https://your-app-name.onrender.com`

---

### Option 3: Fly.io

1. **Install Fly CLI:**
   ```bash
   curl -L https://fly.io/install.sh | sh
   ```

2. **Login:**
   ```bash
   fly auth login
   ```

3. **Deploy:**
   ```bash
   fly launch
   fly deploy
   ```

**Your app will be live at:** `https://your-app-name.fly.dev`

---

### Option 4: DigitalOcean App Platform

1. **Go to DigitalOcean:**
   - Visit https://cloud.digitalocean.com
   - Navigate to "App Platform"

2. **Create App:**
   - Click "Create App"
   - Connect GitHub: `https://github.com/jinendrasahu/spices-shop`
   - Select Docker configuration
   - Deploy

**Your app will be live at:** `https://your-app-name.ondigitalocean.app`

---

## 📋 Pre-Deployment Checklist

- [x] Code pushed to GitHub
- [x] Docker configuration ready
- [x] Environment variables configured
- [x] Database setup included

## 🔗 GitHub Repository

**Repository URL:** https://github.com/jinendrasahu/spices-shop

## 📝 Environment Variables Needed

Make sure these are set in your deployment platform:

- `WORDPRESS_DB_HOST=db`
- `WORDPRESS_DB_NAME=wordpress`
- `WORDPRESS_DB_USER=wordpress_user`
- `WORDPRESS_DB_PASSWORD=<generate-secure-password>`
- `MYSQL_ROOT_PASSWORD=<generate-secure-password>`
- `MYSQL_DATABASE=wordpress`
- `MYSQL_USER=wordpress_user`
- `MYSQL_PASSWORD=<generate-secure-password>`

## 🎯 Recommended: Railway

**Why Railway?**
- ✅ Free tier available
- ✅ Automatic HTTPS
- ✅ Easy GitHub integration
- ✅ Built-in database
- ✅ Simple deployment

**Quick Start:**
```bash
npm install -g @railway/cli
railway login
railway init
railway up
```

Your deployment link will be shown after `railway up` completes!

