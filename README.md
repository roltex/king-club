# 🎰 Poker Tournament Reservation & QR Check-In System

A modern, professional poker tournament management system with instant seat reservation, QR code generation, and seamless check-in experience.

## ✨ Features

### Customer Features
- 🎫 **Instant Seat Reservation** - Automatic random seat assignment
- 📱 **QR Code Generation** - Unique QR codes for each reservation
- ⏰ **Waiting List** - Automatic waiting list when tournament is full
- 🔍 **Find Reservation** - Lookup reservations by phone number
- ❌ **Cancel Reservation** - Easy cancellation with automatic waiting list promotion

### Staff Features
- 📷 **QR Code Scanner** - Fast check-in using camera or dedicated scanner
- ✅ **Manual Check-In** - Alternative check-in by reservation ID
- 👥 **Table Layout View** - Real-time view of all tables and seats
- 📊 **Statistics Dashboard** - Live tournament statistics

### Admin Features (Filament)
- 📋 **Reservation Management** - Full CRUD operations
- 🎯 **Table Overview** - Visual table and seat assignments
- 📈 **Statistics & Reports** - Comprehensive tournament analytics
- 🔄 **Status Management** - Manual status updates and check-ins
- 📤 **CSV Export** - Export reservation data

## 🏗️ Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **Filament 3** - Admin panel
- **SQLite** - Database (zero-config, file-based)
- **SimpleSoftwareIO/QrCode** - QR code generation

### Frontend
- **Vue 3** - JavaScript framework
- **Tailwind CSS** - Utility-first CSS
- **Vite** - Build tool
- **Pinia** - State management
- **Axios** - HTTP client
- **Lucide Icons** - Icon library
- **HTML5 QR Code** - QR scanner
- **QRCode.vue** - QR code display

### Design
- **Glassmorphic UI** - Modern glass-like design
- **Responsive Layout** - Mobile-first approach
- **Smooth Animations** - Enhanced user experience
- **Dark Theme** - Easy on the eyes

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- SQLite support in PHP (usually built-in)

### Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Create admin user for Filament
php artisan make:filament-user

# Start development server
php artisan serve
```

The backend will be available at `http://localhost:8000`

### Frontend Setup

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Create .env file
echo "VITE_API_URL=http://localhost:8000/api" > .env

# Start development server
npm run dev
```

The frontend will be available at `http://localhost:5173`

## 🚀 Usage

### For Customers

1. **Make a Reservation**
   - Visit the homepage
   - Click "Reserve Your Seat"
   - Fill in your details (First Name, Last Name, Phone, Email)
   - Submit to get instant confirmation

2. **View Your Reservation**
   - Click "Find My Reservation"
   - Enter your phone number
   - View your table, seat, and QR code

3. **Check-In at Venue**
   - Show your QR code to staff
   - Staff scans the code
   - Instant check-in confirmation

### For Staff

1. **QR Code Scanning**
   - Navigate to "Staff Check-In Scanner"
   - Allow camera access
   - Start scanner
   - Point camera at customer's QR code
   - Automatic check-in

2. **Manual Check-In**
   - Use manual check-in form
   - Enter reservation ID
   - Click "Check In"

3. **View Table Layout**
   - Navigate to "View Table Layout"
   - See all 6 tables with seat status
   - Green = Checked In
   - Blue = Reserved
   - Gray = Available

### For Administrators

1. **Access Admin Panel**
   - Visit `http://localhost:8000/admin`
   - Login with your credentials

2. **Manage Reservations**
   - View all reservations in organized tabs
   - Filter by status, table, name, phone
   - Manually check-in users
   - Edit or cancel reservations
   - Export to CSV

3. **View Dashboard**
   - See real-time statistics
   - View table layout widget
   - Monitor recent reservations

## 📁 Project Structure

```
poker/
├── backend/                    # Laravel Backend
│   ├── app/
│   │   ├── Filament/          # Admin Panel
│   │   │   ├── Resources/     # Resource definitions
│   │   │   ├── Widgets/       # Dashboard widgets
│   │   │   └── Pages/         # Custom pages
│   │   ├── Http/
│   │   │   ├── Controllers/   # API controllers
│   │   │   └── Middleware/    # Custom middleware
│   │   ├── Models/            # Eloquent models
│   │   └── Services/          # Business logic
│   ├── config/                # Configuration files
│   ├── database/
│   │   └── migrations/        # Database migrations
│   ├── resources/
│   │   └── views/             # Blade templates
│   └── routes/                # API routes
│
├── frontend/                  # Vue 3 Frontend
│   ├── src/
│   │   ├── components/        # Reusable components
│   │   ├── views/             # Page components
│   │   ├── stores/            # Pinia stores
│   │   ├── services/          # API services
│   │   ├── router/            # Vue Router
│   │   ├── composables/       # Composable functions
│   │   ├── style.css          # Global styles
│   │   └── config.yaml        # Configuration
│   ├── public/                # Static assets
│   └── index.html             # Entry HTML
│
├── instruction.md             # Original requirements
└── README.md                  # This file
```

## 🎨 Design Features

### Glassmorphic Design
- Frosted glass effect with backdrop blur
- Semi-transparent cards with subtle borders
- Soft shadows for depth
- Smooth color gradients

### Animations
- Floating background elements
- Smooth page transitions
- Hover effects on interactive elements
- Pulse animations for important elements

### Color Scheme
- Primary: Poker Blue (#0ea5e9)
- Accent: Purple/Magenta
- Background: Dark gradient (slate to purple)
- Glass: White with low opacity

## 🔧 Configuration

### Backend Configuration
Edit `backend/config/tournament.php`:
```php
'total_tables' => 6,
'seats_per_table' => 9,
'total_seats' => 54,
'frontend_url' => 'http://localhost:5173',
```

### Frontend Configuration
Edit `frontend/src/config.yaml`:
```yaml
api:
  baseUrl: "http://localhost:8000/api"
  timeout: 10000

tournament:
  totalTables: 6
  seatsPerTable: 9
  totalSeats: 54
```

## 🔒 Security Features

- UUID-based reservation IDs
- QR code checksum validation
- CORS protection
- Rate limiting on API endpoints
- Phone number uniqueness validation
- Input sanitization and validation

## 📱 Responsive Design

The application is fully responsive and works seamlessly on:
- 📱 Mobile phones (320px+)
- 📱 Tablets (768px+)
- 💻 Laptops (1024px+)
- 🖥️ Desktops (1920px+)

## 🧪 API Endpoints

### Public Endpoints

```
POST   /api/reserve              # Create reservation
GET    /api/reservation/{id}     # Get reservation by ID
GET    /api/reservation/phone/{phone}  # Get by phone
POST   /api/checkin              # Check-in user
POST   /api/reservation/{id}/cancel    # Cancel reservation
GET    /api/statistics           # Get tournament stats
GET    /api/tables               # Get table layout
GET    /api/waiting-list         # Get waiting list
```

## 🎯 Tournament Rules

- **Total Tables**: 6
- **Seats per Table**: 9
- **Total Available Seats**: 54
- **Seat Assignment**: Random
- **Waiting List**: Automatic when full
- **Check-In**: QR code or manual by ID

## 📊 Database Schema

### Reservations Table
- `id` - UUID (Primary Key)
- `first_name` - String
- `last_name` - String
- `phone` - String (Unique)
- `email` - String (Nullable)
- `status` - Enum (reserved, waiting, checked_in, cancelled)
- `table_number` - Integer (1-6)
- `seat_number` - Integer (1-9)
- `waiting_position` - Integer
- `qr_code` - Text
- `qr_checksum` - String
- `checkin_time` - Timestamp
- `created_at` - Timestamp
- `updated_at` - Timestamp

## 🚀 Production Deployment

### Railway Deployment (Recommended)

This project is configured for easy deployment on Railway. See the complete guide in [RAILWAY_DEPLOYMENT.md](./RAILWAY_DEPLOYMENT.md).

**Quick Start:**
1. Push code to GitHub (already done ✅)
2. Go to [railway.app](https://railway.app) and create a new project
3. Connect your GitHub repository: `roltex/king-club`
4. Railway will auto-detect and deploy your backend
5. Add PostgreSQL database service
6. Set environment variables (see RAILWAY_DEPLOYMENT.md)
7. Deploy frontend as separate service or use Vercel/Netlify

### Manual Production Build

#### Backend
```bash
# Build for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Frontend
```bash
# Build for production
npm run build

# The dist/ folder contains production files
```

## 🤝 Contributing

This is a custom tournament system. For modifications:
1. Backend: Modify Laravel files in `backend/`
2. Frontend: Modify Vue files in `frontend/src/`
3. Configuration: Update YAML files following user preferences

## 📄 License

MIT License - Feel free to use this system for your Kings Club!

## 🎉 Credits

- **Design**: Glassmorphic UI with Tailwind CSS
- **Icons**: Lucide Icons (per user preference)
- **QR Codes**: SimpleSoftwareIO & QRCode.vue
- **Framework**: Laravel 12 & Vue 3

## 📞 Support

For issues or questions:
1. Check the configuration files
2. Review API endpoints
3. Check browser console for frontend errors
4. Check Laravel logs for backend errors

---

Made with ❤️ for the ultimate poker tournament experience!

