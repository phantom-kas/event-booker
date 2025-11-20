# Event Booking Backend

A Laravel-based backend for an Event Booking system. It handles events, tickets, bookings, payments, and notifications (email).  

---

## Requirements

- PHP >= 8.1  
- Composer  
- MySQL / MariaDB
- Email account (for sending emails)  

---

## Installation

1. **Clone the repository**

```bash
git clone <your-repo-url>
cd event-booking-backend
```

2. **Install dependencies**

```bash
composer install
```

3. **Set up environment variables**

```bash
cp .env.example .env
```

4. **Run migrations and seed the database**

```bash
php artisan migrate --seed
```

5. **Set up and run the queue worker (for emails/notifications)**

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

6. **Serve the application locally**

```bash
php artisan serve
```