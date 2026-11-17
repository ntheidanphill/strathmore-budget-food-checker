# 🍛 Strathmore Budget Food Checker (Group 8)

A Laravel web application that helps Strathmore University students find meals
they can afford. A student types a maximum budget (KES) as a **guest** and sees every
in-stock menu item at or below that price, filtered by campus location
(Student Centre, Siwaka, Madaraka). Vendor Managers keep their own menus up to date and
System Administrators manage vendors, locations, categories and accounts.

**Unit:** Application Programming for the Internet (Laravel Group Project) combined with
the Software Engineering group project.
**Team:** Benson Kyalo, Danphill Nthei, Emma Naomi, Joel Gayo, Shanice Njoki, Victor Muigai

## User roles

| Role | Login? | What they can do |
|---|---|---|
| Student | No (guest) | Search food by budget, location and food type |
| Vendor Manager | Yes | Add items, change prices, toggle in/out of stock, view dashboard - **own vendor only** |
| System Administrator | Yes | Manage vendors, manager accounts, locations, categories; correct any item or price |

## Business rules enforced by the server

1. A Vendor Manager can never view, edit, toggle or delete another vendor's items (HTTP 403).
2. Students only see items that are in stock, from an active vendor, in an active location.
3. A vendor cannot list two items with the same name; prices must be KES 1 - 5,000.

## Requirements traceability

| Req | Where it is implemented |
|---|---|
| R1 Budget search | `SearchController`, `resources/views/search.blade.php`, `MenuItem` scopes |
| R2 Login | `AuthController`, `RoleMiddleware` |
| R3 Vendor menu | `Vendor\DashboardController`, `MenuItemController`, `resources/views/items` |
| R4 Admin management | `app/Http/Controllers/Admin/*`, `resources/views/admin` |
| R5 Security | hashed passwords (User model cast), `auth` + `role` middleware, CSRF, ownership check |
| R6 Performance / mobile | DB index on `(is_active, price)`, eager loading, responsive CSS |

## Setup (XAMPP + MySQL)

Requirements: PHP 8.2+, Composer, Git, XAMPP (Apache + MySQL).

```bash
git clone https://github.com/ntheidanphill/strathmore-budget-food-checker.git
cd strathmore-budget-food-checker
composer install
cp .env.example .env
php artisan key:generate
```

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open http://localhost/phpmyadmin and create an empty database named `budget_food_checker`
   (collation `utf8mb4_unicode_ci`).
3. In `.env` make sure these lines are set:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=budget_food_checker
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Build the tables and load the demo data, then start the site:
   ```bash
   php artisan migrate:fresh --seed
   php artisan serve
   ```
5. Open http://127.0.0.1:8000

## Demo login accounts (created by the seeder)

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `Admin@1234` |
| Vendor Manager (Student Centre Cafe) | `mgr_studentcentre` | `Vendor@1234` |
| Vendor Manager (Chapati Corner) | `mgr_chapati` | `Vendor@1234` |
| Vendor Manager (Siwaka Grill) | `mgr_siwaka` | `Vendor@1234` |
| Vendor Manager (Madaraka Bites) | `mgr_madaraka` | `Vendor@1234` |

Students need no account: just open the home page.

## JSON endpoint

`GET /api/items?budget=200&location=2&category=3` (`location` and `category` optional)

```json
{ "count": 1, "budget": 200, "items": [
  { "id": 4, "name": "Chapati", "price": 20, "vendor": "Chapati Corner",
    "location": "Student Centre", "category": "Snacks" } ] }
```
A missing or invalid budget returns HTTP 422 with an `errors` object.

## Running the tests

```bash
php artisan test
```
Tests cover: a valid operation, invalid data, an unauthenticated request, a wrong-role
request, the business rules and the JSON endpoint.

## Project structure (where to look)

- `routes/web.php` - every route, grouped by who may use it
- `app/Models` - User, Location, Category, Vendor, MenuItem
- `app/Http/Controllers` - request logic (`Admin/` and `Vendor/` sub-folders)
- `app/Http/Middleware/RoleMiddleware.php` - role-based access
- `database/migrations` and `database/seeders` - table structure and demo data
- `resources/views` - Blade pages; `public/css/style.css` and `public/js/app.js` - look and feel
- `tests/Feature` - automated tests
