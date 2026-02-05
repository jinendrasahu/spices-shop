# 🌱 Spices Shop - Modern Organic Store Platform

Dockerized WordPress e-commerce platform with modern product UI, deployed on Railway with automatic SSL/HTTPS.

## 📋 What's Included

### ✨ Features
- **Modern Product Cards** - Rounded corners, shadows, smooth transitions (Shop + Home)
- **Product Actions** - Add to Cart, Wishlist, Quick View, Compare, About, Sync buttons
- **About Us Page** - Auto-created with sample content
- **White Footer Text** - Visible on dark background
- **Responsive Design** - Mobile-optimized layout
- **WooCommerce Integration** - Full e-commerce functionality
- **TM Organik Theme** - Professional organic store theme
- **Auto SSL/HTTPS** - Railway provides free SSL certificates

### 🎨 Modern UI Updates
- Product cards with 12px border-radius and elevation
- Hover effects (lift + shadow enhancement)
- Action buttons in floating badge (top-right)
- Consistent styling across shop and home pages
- Automatic cache-busting for CSS updates

### 🔧 Backend
- MySQL 8.0 database
- WordPress latest version
- WooCommerce for product management
- All-in-One WP Migration plugin ready
- Optimized Docker setup for Railway

---

## 🚀 Quick Start - 3 Steps (45 minutes)

### Step 1️⃣: Push to GitHub (5 min)

**Just double-click this file:**
```
DEPLOY_NOW.bat
```

This will automatically:
- Initialize Git repository
- Stage all files
- Commit changes with timestamp
- Push to GitHub (`jinendrasahu/spices-shop`)

### Step 2️⃣: Deploy to Railway (10 min)

1. Go to https://railway.app
2. Click **"New Project"**
3. Select **"Deploy from GitHub repo"**
4. Find **`jinendrasahu/spices-shop`** → Click
5. Click **"Deploy Now"**
6. ⏳ Wait 5-10 minutes (Railway builds Docker image)
7. Copy your live URL (e.g., `spices-shop-production.up.railway.app`)

### Step 3️⃣: Migrate Data (15 min)

**Export from Localhost:**
- Go to `http://localhost/wp-admin`
- Plugins → Add New → "All-in-One WP Migration"
- Install & Activate
- All-in-One WP → **Export** → Download `.wpress` file

**Import to Railway:**
- Visit your Railway URL
- Complete WordPress setup (if first time)
- Plugins → Add New → "All-in-One WP Migration"
- Install & Activate
- All-in-One WP → **Import** → Upload your `.wpress` file
- ⏳ Wait 10 minutes

**Verify:**
- ✅ Shop displays modern product cards
- ✅ Home page has product widgets with rounded cards
- ✅ About Us page visible in menu
- ✅ Footer text is white and visible
- ✅ Product "About" button works

---

## 📁 Project Structure

```
tata-wp/
├── docker-compose.yml          # Railway-optimized Docker config
├── Dockerfile                  # WordPress image definition
├── deploy-export/              # WordPress files & database backup
├── tata-wp/                    # Theme customizations
│   ├── deploy-export/
│   │   └── html/
│   │       └── wp-content/
│   │           └── themes/
│   │               ├── tm-organik/          # Parent theme
│   │               └── tm-organik-child/    # Custom child theme
│   │                   ├── functions.php    # Auto-create About page
│   │                   ├── style.css        # Child theme styles
│   │                   ├── modern-products.css  # Modern UI CSS
│   │                   └── create-about-page.php
├── DEPLOY_NOW.bat              # ⭐ Main deployment script
├── RAILWAY_DEPLOYMENT.md       # Detailed deployment guide
└── README.md                   # This file

```

---

## ⚙️ Configuration

### Environment Variables (Railway auto-sets these)
```
MYSQL_DATABASE=wordpress
MYSQL_USER=wordpress_user
MYSQL_PASSWORD=wordpress_password
WORDPRESS_TABLE_PREFIX=wp_
```

### Docker Ports
- **Local:** `http://localhost:8080`
- **Railway:** `https://spices-shop-production.up.railway.app`

### Files Included
- Product images (anazah-product-images/)
- Product data (anazah-products.json)
- Database backup (deploy-export/backup.sql)
- Logos and hero images

---

## 🛠️ Local Development

### Run Locally
```bash
cd tata-wp
docker-compose up -d
```

Access: `http://localhost:8080`

### Stop Containers
```bash
docker-compose down
```

### Clear Cache & Restart
```bash
./clear-cache-restart.sh    # Linux/Mac
clear-cache-restart.bat     # Windows
```

---

## 📝 Theme Customization

### Change Product Card Colors
Edit: `deploy-export/html/wp-content/themes/tm-organik-child/modern-products.css`

```css
.product {
  box-shadow: 0 6px 18px rgba(31, 45, 61, 0.06);  /* Shadow */
  border-radius: 12px;                            /* Roundness */
}

.product:hover {
  transform: translateY(-8px);                   /* Lift effect */
  box-shadow: 0 18px 40px rgba(31, 45, 61, 0.12);
}
```

### Change Footer Colors
WP Admin → Appearance → Customize → Footer → Colors

### Edit About Us Page
WP Admin → Pages → About Us → Edit

---

## 🔒 Security

- ✅ Automatic SSL/HTTPS (Railway)
- ✅ Environment variables for secrets
- ✅ Docker container isolation
- ✅ Database credentials in docker-compose.yml (rotate in production)
- ✅ WordPress security best practices

**Production Tip:** Use Railway's environment variables feature to store sensitive data:
- `DB_PASSWORD`
- `WORDPRESS_AUTH_KEY`
- etc.

---

## 📊 Performance

### Build Time
- First build: ~5-10 minutes
- Subsequent pushes: ~2-5 minutes (layer caching)

### Deployment Size
- Docker image: ~500MB
- Database with products: ~50MB
- Total initial upload: ~550MB

### Recommended Upgrades
- **Free tier:** Fine for development/testing
- **$5/mo:** Good for small production sites
- **$12+/mo:** Recommended for high-traffic shops

---

## 🐛 Troubleshooting

### Push to GitHub Fails
**Error:** "Permission denied" or "Authentication failed"
- Verify you have git installed: `git --version`
- Check internet connection
- Ensure DEPLOY_NOW.bat runs with admin privileges

**Solution:**
```bash
git remote remove origin
git remote add origin https://github.com/jinendrasahu/spices-shop.git
git push -u origin main
```

### Railway Build Fails
**Check:**
- Railway logs in dashboard
- Dockerfile is valid
- docker-compose.yml is in root directory
- All required files are committed to git

### Import Fails on Railway
**Error:** `.wpress` file too large or import hangs
- Check file size (should be < 2GB)
- Verify All-in-One WP Migration is activated
- Try manual SQL import as fallback

### Site Shows "ERR_TOO_MANY_REDIRECTS"
**Cause:** Mixed http/https
**Solution:** 
- WP Admin → Settings → General
- Change both URLs to `https://your-domain.com`

---

## 📞 Support & Docs

| Topic | Link |
|-------|------|
| Railway Docs | https://docs.railway.app |
| WordPress Hosting | https://railway.app/templates/wordpress |
| WooCommerce | https://woocommerce.com/document/ |
| All-in-One Migration | https://wordpress.org/plugins/all-in-one-wp-migration/ |
| TM Organik Theme | https://thememove.com/ |

---

## 📜 License

This project includes:
- WordPress (GPL v2 or later)
- WooCommerce (GPL v3 or later)
- TM Organik Theme (GPL)
- Custom modifications (MIT)

---

## ✅ Deployment Checklist

### Before Pushing
- [ ] All custom CSS is in modern-products.css
- [ ] Product images are in anazah-product-images/
- [ ] Database backup is current (backup.sql)
- [ ] .gitignore is configured
- [ ] docker-compose.yml uses environment variables

### After Railway Deploy
- [ ] Build completes successfully (check logs)
- [ ] WordPress installer loads
- [ ] Complete WordPress setup
- [ ] Install All-in-One WP Migration
- [ ] Export from localhost
- [ ] Import on Railway
- [ ] Verify all pages load
- [ ] Test product cards
- [ ] Check footer styling
- [ ] Test responsive design

### Before Going Live
- [ ] Change admin password
- [ ] Update WordPress Site URL
- [ ] Install SSL certificate (auto on Railway)
- [ ] Set up custom domain
- [ ] Enable 2FA for admin account
- [ ] Configure backups
- [ ] Set up monitoring

---

## 🎉 You're Ready!

**Next Step:** Double-click `DEPLOY_NOW.bat` to start deployment!

Questions? Check `RAILWAY_DEPLOYMENT.md` for detailed instructions.

---

*Last Updated: February 5, 2026*
*Platform: Railway + Docker + WordPress + WooCommerce*
