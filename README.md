# IMSTS - Integrated Market Surveillance Tracking System

A web-based application for FDA Upper West Region, Ghana to coordinate community visits, prevent duplicate visits, maintain historical records, and improve geographical coverage.

## Features

- **Dashboard** - Real-time statistics and quick actions
- **Community Management** - Register and manage communities
- **Visit Recording** - Record surveillance visits with duplicate prevention
- **Visit History** - View all recorded visits with filters
- **Administrative Areas** - Manage municipalities and districts
- **Team Management** - Manage surveillance teams
- **User Management** - Role-based access control
- **Reports** - Generate and export surveillance reports

## Technology Stack

- **Backend:** PHP 8+
- **Database:** PostgreSQL (production) / MySQL (local development)
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Bootstrap 5
- **Deployment:** Render.com

## Local Development

### Prerequisites
- XAMPP or WAMP with PHP 8+
- MySQL
- Composer (optional)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Talim20/FDA.WA1.git
   cd FDA.WA1
   ```

2. Import the database schema:
   ```bash
   mysql -u root -p < database/imsts_db.sql
   ```

3. Configure the database in `config/database.php` (already configured for local XAMPP)

4. Copy files to XAMPP htdocs:
   ```bash
   Copy files to C:\xampp\htdocs\IMSTS\
   ```

5. Access the application:
   ```
   http://localhost/IMSTS/
   ```

### Default Credentials

- **Username:** admin
- **Password:** admin123

## Deployment on Render

### Prerequisites
- Render account (free tier available)
- GitHub account

### Step-by-Step Deployment

1. **Push to GitHub** (if not already done)
   ```bash
   git add .
   git commit -m "Ready for Render deployment"
   git push origin master
   ```

2. **Create PostgreSQL Database on Render**
   - Go to https://dashboard.render.com/
   - Click "New" → "PostgreSQL"
   - Name: `imsts-db`
   - Select Free tier
   - Click "Create Database"

3. **Import Schema to PostgreSQL**
   - In Render dashboard, click on your database
   - Click "Connect" → "External Connection"
   - Copy the Internal Database URL
   - Use a PostgreSQL client (like DBeaver or pgAdmin) to connect
   - Run the schema: `database/schema_postgres.sql`

4. **Create Web Service**
   - Go to https://dashboard.render.com/
   - Click "New" → "Web Service"
   - Connect your GitHub repository
   - Select `FDA.WA1` repository
   - Configure:
     - **Name:** imsts
     - **Region:** Choose nearest region
     - **Branch:** master
     - **Runtime:** PHP
     - **Build Command:** composer install
     - **Start Command:** php -S 0.0.0.0:10000 -t .
   - Click "Advanced" → "Add Environment Variable"
     - Key: `DATABASE_URL`
     - Value: (paste your PostgreSQL connection string from step 3)
   - Click "Create Web Service"

5. **Access Your Application**
   - Wait for deployment to complete
   - Render will provide a URL like: `https://imsts.onrender.com`
   - Access the application using the default credentials

## Database Schema

The application uses the following main tables:
- `users` - System users with role-based access
- `administrative_areas` - Municipalities and districts
- `communities` - Registered communities
- `teams` - Surveillance teams
- `visit_outcomes` - Possible inspection outcomes
- `surveillance_visits` - Recorded visits with duplicate prevention
- `audit_log` - System audit trail

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control (Administrator, Supervisor, Field Officer)
- Audit logging for sensitive operations

## Support

For issues or questions, please open an issue on GitHub.

## License

MIT License
