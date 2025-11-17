# 🚂 Railway CLI Commands Guide

This guide explains how to run Railway CLI commands on your local machine.

## Prerequisites

1. **Node.js installed** on your computer (for npm)
2. **Railway account** created
3. **Project deployed** on Railway (at least the backend service)

## Step 1: Install Railway CLI

Open your terminal/command prompt (PowerShell on Windows) and run:

```bash
npm install -g @railway/cli
```

This installs the Railway CLI globally on your computer.

## Step 2: Login to Railway

In your terminal, run:

```bash
railway login
```

This will:
- Open your browser
- Ask you to authorize Railway CLI
- Complete the login process

## Step 3: Link Your Project

Navigate to your project directory:

```bash
cd "C:\Users\My Computer\poker"
```

Then link your local project to Railway:

```bash
railway link
```

This will:
- Show a list of your Railway projects
- Let you select the project (e.g., `king-club`)
- Link your local directory to that Railway project

## Step 4: Run Commands

Now you can run Railway commands from your terminal!

### Generate APP_KEY

```bash
railway run --service backend php artisan key:generate --show
```

This will:
- Connect to your Railway backend service
- Run the command in the Railway environment
- Display the generated key
- Copy the key and add it to Railway dashboard → Variables → `APP_KEY`

### Run Migrations

```bash
railway run --service backend php artisan migrate --force
```

### Create Admin User

```bash
railway run --service backend php artisan make:filament-user
```

### View Logs

```bash
railway logs --service backend
railway logs --service frontend
```

### Open Service in Browser

```bash
railway open --service backend
railway open --service frontend
```

## Alternative: Use Railway Dashboard

If you don't want to use CLI, you can run commands directly in Railway dashboard:

### Method 1: Railway Dashboard Terminal

1. Go to [railway.app](https://railway.app)
2. Select your project
3. Click on **Backend** service
4. Go to **Deployments** tab
5. Click on the latest deployment
6. Click **"View Logs"** or **"Shell"**
7. Run commands directly in the web terminal

### Method 2: Railway Dashboard Variables

For `APP_KEY` generation:

1. Go to Backend service → **Variables** tab
2. Click **"Add Variable"**
3. Name: `APP_KEY`
4. Value: Generate locally first:
   ```bash
   cd backend
   php artisan key:generate --show
   ```
5. Copy the output and paste it as the value
6. Save

## Quick Reference

### Local Terminal (PowerShell/CMD)

```powershell
# Install CLI
npm install -g @railway/cli

# Login
railway login

# Link project
cd "C:\Users\My Computer\poker"
railway link

# Run commands
railway run --service backend php artisan key:generate --show
railway run --service backend php artisan migrate --force
railway run --service backend php artisan make:filament-user
```

### Railway Dashboard

1. Go to https://railway.app
2. Select your project
3. Select Backend service
4. Use the web terminal or variables tab

## Troubleshooting

### "railway: command not found"

**Solution**: Make sure Railway CLI is installed:
```bash
npm install -g @railway/cli
```

### "Not logged in"

**Solution**: Run login command:
```bash
railway login
```

### "No project linked"

**Solution**: Link your project:
```bash
railway link
```

### "Service not found"

**Solution**: Make sure:
1. Service is deployed on Railway
2. Service name matches (e.g., `backend`)
3. You're in the correct project

## Summary

**Where to run commands:**
- ✅ **Your local terminal** (after installing Railway CLI and linking project)
- ✅ **Railway dashboard web terminal** (no CLI needed)

**Recommended approach:**
1. Use Railway dashboard for first-time setup (easier)
2. Use CLI for ongoing management (faster)

---

**Need help?** Check Railway docs: https://docs.railway.app/develop/cli

