# 🚂 Railway Deployment Guide

This guide will help you deploy the Poker Tournament Management System to Railway.

## Prerequisites

1. **Railway Account**: Sign up at [railway.app](https://railway.app)
2. **GitHub Repository**: Your code should be pushed to GitHub (already done ✅)
3. **Railway CLI** (optional): For easier management

## Deployment Steps

### Step 1: Create New Project on Railway

1. Go to [railway.app](https://railway.app) and sign in
2. Click **"New Project"**
3. Select **"Deploy from GitHub repo"**
4. Choose your repository: `roltex/king-club`
5. Railway will automatically detect the project

### Step 2: Configure Backend Service

Railway will create a service for your backend. Configure it:

1. **Service Name**: `backend` (or `api`)
2. **Root Directory**: Set to `backend/`
3. **Build Command**: 
   ```bash
   composer install --optimize-autoloader --no-dev
   ```
4. **Start Command**:
   ```bash
   php artisan serve --host=0.0.0.0 --port=$PORT
   ```

### Step 3: Set Environment Variables

In Railway dashboard, go to your service → **Variables** tab and add:

#### Required Backend Variables

```env
APP_NAME="Kings Club"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-backend.railway.app

# Database (Railway provides PostgreSQL by default)
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Or use SQLite (simpler, but less scalable)
# DB_CONNECTION=sqlite
# DB_DATABASE=/app/database/database.sqlite

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file

# Frontend URL (update after deploying frontend)
FRONTEND_URL=https://your-frontend.railway.app

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@kingsclub.ge
MAIL_FROM_NAME="Kings Club"
```

#### Generate APP_KEY

In Railway service terminal, run:
```bash
php artisan key:generate
```

Or generate locally and add to Railway:
```bash
php artisan key:generate --show
```

### Step 4: Add PostgreSQL Database (Recommended)

1. In Railway dashboard, click **"+ New"** → **"Database"** → **"Add PostgreSQL"**
2. Railway will automatically provide connection variables
3. Use the variables in your backend service (as shown above)

### Step 5: Run Migrations

1. In Railway service, go to **"Deployments"** tab
2. Click on the latest deployment
3. Open **"View Logs"** or use **"Deploy Logs"**
4. Or use Railway CLI:
   ```bash
   railway run php artisan migrate --force
   ```

### Step 6: Create Admin User

```bash
railway run php artisan make:filament-user
```

Or manually create via tinker:
```bash
railway run php artisan tinker
```

### Step 7: Deploy Frontend (Separate Service)

#### Option A: Deploy Frontend as Static Site

1. **Build Frontend Locally**:
   ```bash
   cd frontend
   npm install
   npm run build
   ```

2. **Create New Service** in Railway:
   - Service Name: `frontend`
   - Root Directory: `frontend/`
   - Build Command: `npm install && npm run build`
   - Start Command: Use a static file server or Railway's static site option

3. **Set Environment Variables**:
   ```env
   VITE_API_BASE_URL=https://your-backend.railway.app
   VITE_APP_NAME="Kings Club"
   ```

#### Option B: Use Railway Static Site

1. Create new service → **"Static Site"**
2. Root Directory: `frontend/`
3. Build Command: `npm install && npm run build`
4. Output Directory: `frontend/dist`

#### Option C: Deploy Frontend Separately (Vercel/Netlify)

1. Push frontend to separate repo or use monorepo
2. Deploy to Vercel/Netlify
3. Set `VITE_API_BASE_URL` to your Railway backend URL

### Step 8: Update CORS Configuration

In `backend/config/cors.php`, ensure your frontend URL is allowed:

```php
'allowed_origins' => [
    'https://your-frontend.railway.app',
    'https://your-frontend.vercel.app', // if using Vercel
],
```

### Step 9: Configure Storage

For file uploads (tournament images), configure storage:

1. **Option A: Railway Volume** (Recommended)
   - Add volume in Railway dashboard
   - Mount to `/app/storage/app/public`
   - Update `FILESYSTEM_DISK=local` in env

2. **Option B: S3/Cloud Storage**
   - Use AWS S3, Cloudflare R2, or similar
   - Update `FILESYSTEM_DISK=s3` in env
   - Add AWS credentials

### Step 10: Set Up Custom Domain (Optional)

1. In Railway service → **Settings** → **Domains**
2. Add your custom domain
3. Update `APP_URL` and `FRONTEND_URL` accordingly

## Post-Deployment Checklist

- [ ] Backend service is running
- [ ] Database migrations completed
- [ ] Admin user created
- [ ] Environment variables set
- [ ] CORS configured correctly
- [ ] Frontend deployed and connected to backend
- [ ] Storage configured for file uploads
- [ ] Custom domain configured (if applicable)
- [ ] SSL certificate active (Railway provides automatically)

## Troubleshooting

### Backend Not Starting

1. Check logs in Railway dashboard
2. Verify `APP_KEY` is set
3. Check database connection
4. Ensure `$PORT` is used in start command

### Database Connection Issues

1. Verify PostgreSQL service is running
2. Check environment variables match Railway's provided values
3. Test connection: `railway run php artisan tinker`

### Frontend Can't Connect to Backend

1. Verify `VITE_API_BASE_URL` is correct
2. Check CORS configuration
3. Ensure backend URL is accessible
4. Check browser console for errors

### File Upload Issues

1. Verify storage directory is writable
2. Check volume mount (if using Railway volume)
3. Update `FILESYSTEM_DISK` configuration

## Railway CLI Commands

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link project
railway link

# View logs
railway logs

# Run commands
railway run php artisan migrate
railway run php artisan tinker

# Open service
railway open
```

## Environment Variables Reference

### Backend (.env)
- `APP_KEY` - Laravel encryption key
- `APP_URL` - Backend URL
- `DB_*` - Database connection
- `FRONTEND_URL` - Frontend URL for CORS
- `CACHE_DRIVER` - Cache driver (file/redis)
- `SESSION_DRIVER` - Session driver

### Frontend (.env)
- `VITE_API_BASE_URL` - Backend API URL
- `VITE_APP_NAME` - Application name

## Cost Optimization

- **Free Tier**: Railway offers free tier with limited resources
- **Database**: PostgreSQL is included, SQLite is free
- **Storage**: Use Railway volumes for file storage
- **Scaling**: Railway auto-scales based on traffic

## Support

- Railway Docs: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- Project Issues: GitHub Issues

---

**Happy Deploying! 🚀**

