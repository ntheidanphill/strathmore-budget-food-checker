# Group 8 Team Guide: Git, Setup and Module Integration

Repo: https://github.com/ntheidanphill/strathmore-budget-food-checker

---------------------------------------------------------------------
## PART A: One-time setup on EVERY member's laptop
---------------------------------------------------------------------

1. **XAMPP** with **PHP 8.2 or higher** (Laravel 11/12 will not run on older PHP).
   Check in a terminal: `php -v`. If the command is not found, add `C:\xampp\php`
   to your Windows PATH (Search "Environment Variables" -> Path -> New).
2. **Composer** (https://getcomposer.org): check with `composer -V`.
3. **Git** (https://git-scm.com): check with `git --version`.
4. **VS Code**.
5. Tell Git who you are (use the SAME email as your GitHub account so your commits
   count as YOUR contribution - the lecturer will check this):
   ```
   git config --global user.name "Your Full Name"
   git config --global user.email "you@example.com"
   ```

---------------------------------------------------------------------
## PART B: Danphill (repo owner) - do this first
---------------------------------------------------------------------

1. On GitHub: repo -> **Settings -> Collaborators -> Add people**. Add the other 5 members
   by GitHub username. Without this they cannot push, even though the repo is public.
2. Make the project use MySQL. Open `.env.example` AND `.env` and set:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=budget_food_checker
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   Only `.env.example` is committed. **Never commit `.env`** (it is already in .gitignore).
3. Copy the contents of `module-0-foundation/` into the project root (merge folders;
   `routes/web.php` replaces the default one). Do NOT copy the `module-0-foundation`
   folder itself, only what is inside it.
4. Register the role middleware. Open `bootstrap/app.php`, find `->withMiddleware(...)`
   and make it look like this:
   ```php
   ->withMiddleware(function (Middleware $middleware) {
       // 'role:admin' etc. in routes/web.php points to this class (made in Module 2)
       $middleware->alias([
           'role' => \App\Http\Middleware\RoleMiddleware::class,
       ]);
   })
   ```
5. Commit and push:
   ```
   git add .
   git commit -m "Add shared layout, theme, routes, README and team guide"
   git push origin main
   ```
6. Tell the group on WhatsApp that Module 0 is on `main`.

---------------------------------------------------------------------
## PART C: Every other member - clone the repository in VS Code
---------------------------------------------------------------------

**Option 1 (VS Code buttons)**
1. Open VS Code -> press `Ctrl+Shift+P` -> type **Git: Clone** -> Enter.
2. Paste `https://github.com/ntheidanphill/strathmore-budget-food-checker.git`.
3. Choose a folder (for example `C:\xampp\htdocs\` or `Documents\Projects`) -> **Open**.
   Sign in to GitHub in the browser window if VS Code asks.

**Option 2 (terminal)**
```
git clone https://github.com/ntheidanphill/strathmore-budget-food-checker.git
cd strathmore-budget-food-checker
code .
```

Then in the VS Code terminal (`Ctrl+` backtick):
```
composer install            # downloads the vendor/ folder (it is NOT stored on GitHub)
cp .env.example .env        # your own private settings file
php artisan key:generate    # makes your app key
```
Then:
1. Open the XAMPP Control Panel -> **Start Apache and MySQL**.
2. Open http://localhost/phpmyadmin -> **New** -> database name `budget_food_checker`,
   collation `utf8mb4_unicode_ci` -> Create.
   (Every member has their OWN local database. The seeder gives everybody identical data.)
3. Check `.env` has the MySQL lines from Part B.
4. `php artisan migrate:fresh --seed` then `php artisan serve` -> http://127.0.0.1:8000

---------------------------------------------------------------------
## PART D: Build order and who does what
---------------------------------------------------------------------

Each `module-X` folder in the zip is laid out exactly like the Laravel project.
**Copy the CONTENTS of your module folder into the project root** (accept "merge folders").

| Order | Module | Pair | What you add |
|---|---|---|---|
| 0 | Foundation | Danphill | layout, CSS, JS, routes, README |
| 1 | Database | Benson + Victor | migrations, models, seeder |
| 2 | Auth + Admin | Emma + Danphill | login, role middleware, admin pages (R2, R4) |
| 3 | Vendor Dashboard | Joel + Shanice | dashboard, menu item CRUD (R3) |
| 4 | Student Search + JSON | Benson + Victor | search page, JSON endpoint (R1) |
| 5 | Testing | Danphill + Joel | automated tests |

**Dependencies:** Module 1 must be merged first, because every other module needs the
tables. Modules 2, 3 and 4 can then be built at the same time by their pairs.
Module 5 comes last.

**Module 1 only:** the new `User.php` REPLACES `app/Models/User.php`. Delete the default
`tests/Feature/ExampleTest.php` (it loads the home page, which now needs a database, so it fails).

---------------------------------------------------------------------
## PART E: The daily Git routine (branch -> commit -> push -> pull request)
---------------------------------------------------------------------

Work on your own branch, never directly on `main`. This avoids overwriting each other.

**1. Start of every work session - refresh your copy**
```
git checkout main
git pull origin main                  # get everybody's latest work
git checkout -b module-3-vendor       # first time only: create your branch
# later sessions: git checkout module-3-vendor, then  git merge main
```
In VS Code: bottom-left shows your branch; the Source Control tab (`Ctrl+Shift+G`)
has Pull / Push / Sync buttons.

**2. Copy your module files in, run the site, check your pages work.**

**3. Commit small and often (one feature = one commit, with a clear message)**
```
git add .
git commit -m "Add MenuItemController with ownership check"
```
Good messages: "Add vendor dashboard stat cards", "Fix price validation range".
Bad messages: "update", "stuff", "final".

**4. Push your branch**
```
git push -u origin module-3-vendor     # first push of this branch
git push                               # every later push
```

**5. Pull Request:** on GitHub click **Compare & pull request** -> write what you did ->
**Create**. A pair-mate or Danphill reviews and clicks **Merge**.

**6. After a merge, EVERYONE refreshes:**
```
git checkout main
git pull origin main
php artisan migrate        # if someone added a migration
```
If `composer.json` changed, also run `composer install`.

**Merge conflict?** VS Code shows "Accept Current / Accept Incoming / Accept Both".
Read both versions, keep what is right, save, then `git add .` and `git commit`.
Conflicts should be rare because each pair owns different files.

---------------------------------------------------------------------
## PART F: Checking that everything is integrated
---------------------------------------------------------------------

After all modules are merged:
```
php artisan migrate:fresh --seed
php artisan route:list          # every route should appear with no errors
php artisan serve
```
Click-through test (matches the demo in the API brief, section 9):
1. Home page -> tap **KES 200** -> results appear, cheapest first. Try a location pill.
2. Visit http://127.0.0.1:8000/api/items?budget=200 -> JSON appears.
3. Log in as `mgr_siwaka` -> Dashboard -> My Menu -> change a price -> search again as a guest.
4. As `mgr_siwaka`, try http://127.0.0.1:8000/admin -> you get **403**.
5. Log in as `admin` -> deactivate a vendor -> that vendor's food disappears from search.

### Running the automated tests safely
Tests wipe the tables they use. **They must NOT run on your real database.**
Open `phpunit.xml` in the project root and make sure these two lines are present and NOT
commented out:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
(Needs the `pdo_sqlite` extension; it is on by default in XAMPP.) Then run `php artisan test`.
Alternative: create a second phpMyAdmin database `budget_food_checker_test` and set it as
`DB_DATABASE` in `phpunit.xml` with `DB_CONNECTION` = `mysql`.

---------------------------------------------------------------------
## PART G: Common problems
---------------------------------------------------------------------

| Problem | Fix |
|---|---|
| `SQLSTATE[HY000] [1049] Unknown database` | Create `budget_food_checker` in phpMyAdmin |
| `SQLSTATE[HY000] [2002] Connection refused` | Start MySQL in XAMPP |
| `Your Composer dependencies require PHP >= 8.2` | Update XAMPP to a PHP 8.2+ build |
| `Target class [role] does not exist` | Step B4 (alias in `bootstrap/app.php`) was missed |
| `Class "App\Http\Controllers\..." not found` | The pair that owns that module has not merged yet; `git pull origin main` |
| `Route [x] not defined` | Same as above, or you are on an old branch: `git merge main` |
| 419 Page Expired | Form is missing `@csrf` |
| Page looks unstyled | Hard refresh `Ctrl+F5`; make sure `public/css/style.css` exists |
| `php artisan migrate` fails on `users` | Run `php artisan migrate:fresh --seed` (first time only) |

---------------------------------------------------------------------
## PART H: Make sure EVERY member can explain their module
---------------------------------------------------------------------

The API brief (section 8) says marks depend on being able to explain, trace and modify
your own code, and on each member having visible Git contributions. For your demo:

- Read every file in your module top to bottom. The comments explain the "why".
- Type or paste the files in YOURSELF and commit them under your own name.
- Be ready to answer: "Which route reaches this controller method? Which model does it
  use? Which table does that model map to? What does the validation reject?"
- Practise one small live change: e.g. change the price limit from 5000 to 3000 in
  `MenuItemController` and prove it with the matching test.

| Pair | Be ready to explain |
|---|---|
| Benson + Victor | migrations and foreign keys, model relationships, scopes `active` / `withinBudget`, the seeder, the search query, the JSON endpoint |
| Emma + Danphill | `Auth::attempt`, session regeneration, `RoleMiddleware`, hashed passwords, why we deactivate instead of delete |
| Joel + Shanice | `MenuItemController` CRUD, `authorizeItem` (business rule 1), unique-name rule, dashboard statistics |
| Danphill + Joel | what each test proves, how the 5 required test types are covered, how to run them |
