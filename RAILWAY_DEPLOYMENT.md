# 🚂 Railway Deployment Guide - Backend & Frontend with SQLite

This guide will help you deploy both the backend and frontend of the Poker Tournament Management System to Railway using SQLite.

## Prerequisites

1. **Railway Account**: Sign up at [railway.app](https://railway.app)
2. **GitHub Repository**: Your code should be pushed to GitHub (already done ✅)
3. **Railway CLI** (optional): For easier management

## Quick Start

1. Go to [railway.app](https://railway.app) and sign in
2. Click **"New Project"** → **"Deploy from GitHub repo"**
3. Select repository: `roltex/king-club`
4. Railway will detect the project structure
5. Create **TWO services**: Backend and Frontend
6. Configure each service as described below

## Step-by-Step Deployment

### Step 1: Create Backend Service

1. In Railway project, click **"+ New"** → **"GitHub Repo"**
2. Select `roltex/king-club`
3. **Service Name**: `backend` or `api`
4. **Root Directory**: `backend`
5. Railway will auto-detect PHP/Laravel

### Step 2: Configure Backend Service

#### Service Settings:
- **Root Directory**: `backend`
- **Build Command**: (Auto-detected from `backend/nixpacks.toml`)
- **Start Command**: (Auto-detected from `backend/nixpacks.toml`)

#### Environment Variables:

Go to Backend Service → **Variables** tab and add:

```env
APP_NAME="Kings Club"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-backend-service.railway.app

# SQLite Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
FILESYSTEM_DISK=local

# Frontend URL (update after deploying frontend)
FRONTEND_URL=https://your-frontend-service.railway.app

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@kingsclub.ge
MAIL_FROM_NAME="Kings Club"
```

#### Generate APP_KEY:

After first deployment, in Railway service terminal:
```bash
railway run php artisan key:generate --show
```

Copy the key and add it to `APP_KEY` in Railway variables.

Or use Railway CLI:
```bash
railway run --service backend php artisan key:generate --show
```

### Step 3: Run Backend Migrations

After setting `APP_KEY`, run migrations:

**Option A: Railway Dashboard**
1. Go to Backend Service → **Deployments** → Latest deployment
2. Click **"View Logs"** → **"Shell"**
3. Run: `php artisan migrate --force`

**Option B: Railway CLI**
```bash
railway run --service backend php artisan migrate --force
```

### Step 4: Create Admin User

```bash
railway run --service backend php artisan make:filament-user
```

### Step 5: Create Frontend Service

1. In same Railway project, click **"+ New"** → **"GitHub Repo"**
2. Select `roltex/king-club` again
3. **Service Name**: `frontend`
4. **Root Directory**: `frontend`
5. Railway will auto-detect Node.js

### Step 6: Configure Frontend Service

#### Service Settings:
- **Root Directory**: `frontend`
- **Build Command**: (Auto-detected from `frontend/nixpacks.toml`)
- **Start Command**: (Auto-detected from `frontend/nixpacks.toml`)

#### Environment Variables:

Go to Frontend Service → **Variables** tab and add:

```env
VITE_API_BASE_URL=https://your-backend-service.railway.app
VITE_APP_NAME="Kings Club"
VITE_TOURNAMENT_SEATS=54
VITE_TOURNAMENT_TABLES=6
VITE_SEATS_PER_TABLE=9
```

**Important**: Replace `your-backend-service.railway.app` with your actual backend Railway URL.

### Step 7: Update Backend CORS

After frontend is deployed, update backend CORS:

1. Go to Backend Service → **Variables**
2. Update `FRONTEND_URL` with your frontend Railway URL
3. Or manually edit `backend/config/cors.php`:

```php
'allowed_origins' => [
    'https://your-frontend-service.railway.app',
],
```

### Step 8: Configure Custom Domains (Optional)

1. **Backend Domain**:
   - Backend Service → **Settings** → **Domains** → **"Generate Domain"**
   - Copy the domain (e.g., `backend-production.up.railway.app`)
   - Update `APP_URL` in backend variables

2. **Frontend Domain**:
   - Frontend Service → **Settings** → **Domains** → **"Generate Domain"**
   - Copy the domain (e.g., `frontend-production.up.railway.app`)
   - Update `VITE_API_BASE_URL` in frontend variables with backend domain
   - Update `FRONTEND_URL` in backend variables with frontend domain

3. **Custom Domain** (if you have one):
   - Add your custom domain in Railway
   - Railway provides SSL automatically

## SQLite Database Persistence

**Important**: SQLite database file needs to persist between deployments.

### Option A: Railway Volume (Recommended)

1. In Backend Service → **Settings** → **Volumes**
2. Click **"Add Volume"**
3. Mount path: `/app/database`
4. This ensures `database.sqlite` persists

### Option B: Ephemeral Storage (Default)

- Database will reset on each deployment
- Fine for development/testing
- Not recommended for production

## Post-Deployment Checklist

### Backend
- [ ] Service is running and healthy
- [ ] `APP_KEY` is set
- [ ] Migrations completed successfully
- [ ] Admin user created
- [ ] Database file exists (`database/database.sqlite`)
- [ ] CORS configured with frontend URL
- [ ] Custom domain configured (if applicable)

### Frontend
- [ ] Service is running and healthy
- [ ] Build completed successfully
- [ ] `VITE_API_BASE_URL` points to backend URL
- [ ] Frontend can connect to backend API
- [ ] Custom domain configured (if applicable)

### Integration
- [ ] Frontend can make API calls to backend
- [ ] Authentication works
- [ ] CORS errors resolved
- [ ] Images/assets load correctly

## Troubleshooting

### Backend Issues

#### Service Won't Start
- Check logs: Backend Service → **Deployments** → **View Logs**
- Verify `APP_KEY` is set
- Check database file permissions
- Ensure `$PORT` is used in start command

#### Database Connection Error
- Verify `DB_CONNECTION=sqlite` is set
- Check `database/database.sqlite` file exists
- Ensure volume is mounted (if using volumes)
- Check file permissions: `chmod 664 database/database.sqlite`

#### Migration Errors
```bash
railway run --service backend php artisan migrate:fresh --force
```

### Frontend Issues

#### Build Fails
- Check Node.js version (should be 20+)
- Verify all dependencies in `package.json`
- Check build logs for specific errors

#### Can't Connect to Backend
- Verify `VITE_API_BASE_URL` is correct
- Check backend CORS configuration
- Ensure backend URL is accessible
- Check browser console for CORS errors

#### 404 Errors on Routes
- Ensure `serve` package is installed
- Check `dist/` folder exists after build
- Verify start command uses `serve -s dist`

### Integration Issues

#### CORS Errors
1. Update `FRONTEND_URL` in backend variables
2. Clear backend config cache:
   ```bash
   railway run --service backend php artisan config:clear
   ```
3. Redeploy backend service

#### Authentication Not Working
- Verify API base URL in frontend
- Check backend authentication endpoints
- Verify Sanctum configuration
- Check browser network tab for errors

## Railway CLI Commands

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link project
railway link

# View logs
railway logs --service backend
railway logs --service frontend

# Run commands
railway run --service backend php artisan migrate
railway run --service backend php artisan tinker
railway run --service backend php artisan make:filament-user

# Open services
railway open --service backend
railway open --service frontend
```

## Environment Variables Reference

### Backend Variables
| Variable | Description | Example |
|----------|-------------|---------|
| `APP_KEY` | Laravel encryption key | `base64:...` |
| `APP_URL` | Backend URL | `https://backend.railway.app` |
| `DB_CONNECTION` | Database type | `sqlite` |
| `DB_DATABASE` | SQLite file path | `/app/database/database.sqlite` |
| `FRONTEND_URL` | Frontend URL for CORS | `https://frontend.railway.app` |
| `CACHE_DRIVER` | Cache driver | `file` |
| `SESSION_DRIVER` | Session driver | `file` |

### Frontend Variables
| Variable | Description | Example |
|----------|-------------|---------|
| `VITE_API_BASE_URL` | Backend API URL | `https://backend.railway.app` |
| `VITE_APP_NAME` | Application name | `Kings Club` |
| `VITE_TOURNAMENT_SEATS` | Total tournament seats | `54` |
| `VITE_TOURNAMENT_TABLES` | Total tables | `6` |
| `VITE_SEATS_PER_TABLE` | Seats per table | `9` |

## Cost Optimization

- **Free Tier**: Railway offers free tier with $5 credit/month
- **SQLite**: Free (no database service needed)
- **Storage**: Railway volumes are included in free tier
- **Scaling**: Railway auto-scales based on traffic

## Production Recommendations

1. **Use PostgreSQL** for production (more reliable than SQLite)
2. **Enable Redis** for caching and sessions
3. **Set up backups** for database
4. **Monitor logs** regularly
5. **Set up alerts** for service failures
6. **Use custom domains** with SSL
7. **Configure CDN** for frontend assets

## Support

- Railway Docs: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- Project Issues: GitHub Issues

---

**Happy Deploying! 🚀**
