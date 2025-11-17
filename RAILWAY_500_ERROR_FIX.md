# Fixing Railway 500 Error

## Common Causes of 500 Errors on Railway

### 1. Missing APP_KEY
**Symptom**: 500 error, logs show "No application encryption key has been specified"

**Fix**:
```bash
railway run --service king-club php artisan key:generate --show
```
Copy the output and add it to Railway Variables as `APP_KEY`

### 2. Database Not Migrated
**Symptom**: 500 error, database-related errors in logs

**Fix**:
```bash
railway run --service king-club php artisan migrate --force
```

### 3. Missing Environment Variables
**Symptom**: 500 error, configuration errors

**Required Variables** (check in Railway Dashboard → Variables):
- `APP_KEY` - Laravel encryption key
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` - Your Railway backend URL
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=/app/database/database.sqlite`
- `FRONTEND_URL` - Your frontend URL

### 4. Database File Doesn't Exist
**Symptom**: SQLite errors in logs

**Fix** (run in Railway Shell):
```bash
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite
php artisan migrate --force
```

### 5. Storage Permissions
**Symptom**: File permission errors

**Fix**:
```bash
railway run --service king-club php artisan storage:link
railway run --service king-club chmod -R 775 storage bootstrap/cache
```

## Step-by-Step Debugging

1. **Check Logs**:
   ```bash
   railway logs --service king-club --tail 100
   ```

2. **Check Environment Variables**:
   - Go to Railway Dashboard
   - Select `king-club` service
   - Go to Variables tab
   - Verify all required variables are set

3. **Test Database Connection**:
   ```bash
   railway run --service king-club php artisan tinker
   # Then in tinker:
   DB::connection()->getPdo();
   ```

4. **Clear Caches**:
   ```bash
   railway run --service king-club php artisan config:clear
   railway run --service king-club php artisan cache:clear
   railway run --service king-club php artisan route:clear
   railway run --service king-club php artisan view:clear
   ```

5. **Check Health Endpoint**:
   Visit: `https://king-club-backend.up.railway.app/up`
   Should return: `{"status":"ok"}`

## Quick Fix Checklist

- [ ] APP_KEY is set in Railway Variables
- [ ] Database file exists (`database/database.sqlite`)
- [ ] Migrations have been run
- [ ] All required environment variables are set
- [ ] Caches have been cleared
- [ ] Checked logs for specific error messages

