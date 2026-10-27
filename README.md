# SK Digital Governance

A web system for the Sangguniang Kabataan (SK) of Olongapo City. Youth join programs and events, check in with a QR digital ID, and read where the SK fund goes. SK officers manage plans (CBYDP, ABYIP), programs, sessions and announcements. The SK Federation monitors every barangay and exports reports.

Built with Laravel 13, PHP 8.4, Tailwind CSS 4 and SQLite.

---

## 1. What you need

| Tool | Version | Check with |
| --- | --- | --- |
| PHP | 8.3 or newer (8.4 recommended) | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js and npm | Node 20 or newer | `node -v` |
| Git | any | `git --version` |

PHP needs these extensions turned on: `pdo_sqlite`, `sqlite3`, `mbstring`, `xml`, `dom`, `zip`, `gd`, `fileinfo`. Run `php -m` to see which ones you have.

The easiest way to get PHP, Composer and the extensions on Windows or macOS is [Laravel Herd](https://herd.laravel.com). On Windows you can also use XAMPP; in its `php.ini`, remove the `;` in front of `extension=zip`, `extension=gd`, `extension=sqlite3` and `extension=pdo_sqlite`.

> **More than one PHP installed?** Make sure `php -v` shows 8.3 or newer in the terminal you use. If it doesn't, call the right one directly, for example with Herd on Windows: `~/.config/herd/bin/php84/php.exe artisan ...`

---

## 2. Set up the project

Run these from the `app` folder.

```bash
# 1. Get the code (skip if you already have it)
git clone https://github.com/EldrinDev/SK-digital-governance.git
cd SK-digital-governance/app

# 2. Install the PHP and JavaScript packages
composer install
npm install

# 3. Create your settings file and app key
cp .env.example .env          # Windows PowerShell: copy .env.example .env
php artisan key:generate

# 4. Create the database file
touch database/database.sqlite   # Windows PowerShell: New-Item database/database.sqlite

# 5. Create the tables and fill them with the barangays and demo data
php artisan migrate:fresh --seed

# 6. Build the CSS and JavaScript
npm run build
```

### Settings worth changing in `.env`

```dotenv
APP_NAME="SK Digital Governance"
APP_URL=http://127.0.0.1:8000
```

The app runs on UTC time (`timezone` in `config/app.php`). That is why the "Generated on" line in exports can look 8 hours behind Philippine time.

Emails (registration confirmations, password resets, reminders) are not sent in local development. They are written to `storage/logs/laravel.log` instead (`MAIL_MAILER=log`).

---

## 3. Run it

```bash
composer run dev
```

This starts the web server and the Vite asset watcher together. Open **http://127.0.0.1:8000**.

If you only need the site and already ran `npm run build`, this is enough:

```bash
php artisan serve
```

If you use Herd, it serves the folder for you at `http://app.test` (or the name of the folder).

---

## 4. Demo accounts

Every demo account uses the password **`password123`**.

| Role | Email | Log in at |
| --- | --- | --- |
| Youth | `youth@sk.test` (registered for an event, has an event pass) | `/login` |
| Youth | `youth1.santarita@sk.test` (has messages with SK replies) | `/login` |
| SK Chairperson, Santa Rita | `officer@sk.test` | `/sk-access` |
| SK Secretary, Santa Rita | `secretary.santarita@sk.test` | `/sk-access` |
| SK Treasurer, Santa Rita | `treasurer.santarita@sk.test` | `/sk-access` |
| SK Kagawad, Santa Rita | `kagawad1.santarita@sk.test`, `kagawad2.santarita@sk.test` | `/sk-access` |
| SK officer, Barretto | `officer.barretto@sk.test` | `/sk-access` |
| SK officer, Kalaklan | `officer.kalaklan@sk.test` | `/sk-access` |
| SK Federation | `federation@sk.test` | `/sk-access` |
| System administrator | `admin@sk.test` | `/sk-access` |

Youth log in at `/login`. Staff log in at `/sk-access` and pick their workspace (their barangay, Federation or Administration). There are 15 youth accounts in all, named like `youth8.santarita@sk.test`.

To start over with fresh demo data (this **erases everything** in your local database):

```bash
php artisan migrate:fresh --seed
```

What `--seed` does depends on the database, so it is safe on any branch:

- **Empty database:** adds the 17 barangays and all the demo data.
- **Database that already has accounts:** only adds any missing barangays. Your data is left alone.
- **Production:** only adds the barangays, never demo accounts. Create the first admin with `php artisan app:create-administrator`.

> **The SK access dropdown is empty or shows odd barangays?** The database was reset without the demo data. Run `php artisan migrate:fresh --seed`.

---

## 5. Barangay logos

Every barangay shows the national SK logo until it has its own. There are two ways to add the real ones:

- **In the app:** the administrator opens **Barangays**, edits the barangay and uploads its logo.
- **In the demo data:** put image files in `database/seeders/images/logos/`, named after the barangay, then reseed. Use `.png`, `.jpg`, `.jpeg` or `.webp`:

  `asinan`, `banicain`, `barretto`, `east-bajac-bajac`, `east-tapinac`, `gordon-heights`, `kalaklan`, `mabayuan`, `new-cabalan`, `new-ilalim`, `new-kababae`, `new-kalalake`, `old-cabalan`, `pag-asa`, `santa-rita`, `west-bajac-bajac`, `west-tapinac`

  For example: `database/seeders/images/logos/santa-rita.png`

---

## 6. Scheduled tasks

Three jobs run on a schedule:

| Command | When | What it does |
| --- | --- | --- |
| `app:send-event-reminders` | daily, 6:00 PM | Emails youth about events they registered for tomorrow |
| `app:send-progress-alerts` | weekdays, 7:00 AM | Sends officers a digest of delayed PPAs and due milestones |
| `app:backup` | daily, 2:00 AM | Backs up the database and uploaded files (keeps 14) |

Locally, keep them running with:

```bash
php artisan schedule:work
```

On a server, add one cron entry: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`

You can also run any of them by hand, for example `php artisan app:send-event-reminders`.

### Other useful commands

```bash
php artisan app:create-administrator   # create the first admin account on a fresh install
php artisan app:assign-role            # change an account's role and barangay workspace
php artisan app:backup                 # make a backup now
```

---

## 7. Tests and code style

```bash
php artisan test --compact                        # the whole suite
php artisan test --compact --filter=EventControllerTest   # one test class

vendor/bin/pint --dirty                           # format the PHP files you changed
```

Run the tests and Pint before you push.

---

## 8. Folder guide

| Folder | What's in it |
| --- | --- |
| `app/Http/Controllers` | Pages and actions, grouped by who uses them: `Youth`, `Officer`, `Federation`, `Admin`, `Account` |
| `app/Models` | Database models (Barangay, Event, Ppa, Abyip, Announcement, …) |
| `app/*.php` | Enums (`PpaStatus`, `EventKind`, …) and helpers (`QrCode`, `CalendarFile`, `DocumentExporter`) |
| `app/Policies` | Who is allowed to do what |
| `resources/views` | Blade pages; `components/` holds reusable pieces, `components/layouts/` the page shells |
| `resources/css/app.css` | Tailwind setup, brand colors and fonts |
| `resources/js/app.js` | Small enhancements: sidebar, dropdowns, share button |
| `routes/web.php` | Every URL, grouped by role |
| `database/migrations` | Table definitions |
| `database/seeders` | Demo data, photos and the logo drop-in folder |
| `tests/Feature` | Feature tests, grouped like the controllers |

---

## 9. Problems you might hit

| Problem | Fix |
| --- | --- |
| `Unable to locate file in Vite manifest` | Run `npm run build`, or keep `npm run dev` running |
| `could not find driver` | Turn on the `pdo_sqlite` and `sqlite3` PHP extensions |
| `Database file at path [...] does not exist` | Create it: `touch database/database.sqlite` |
| `The database already has plans` when seeding | Use `migrate:fresh`, not `db:seed`: `php artisan migrate:fresh --seed` |
| SK access dropdown has no barangays | Run `php artisan migrate:fresh --seed` |
| Composer says your PHP version is too low | Your terminal is using an older PHP. Check `php -v` and see section 1 |
| Excel or Word export fails | Turn on the `zip`, `xml` and `gd` PHP extensions |
| `No application encryption key` | Run `php artisan key:generate` |
| Page changes don't show | Hard refresh (Ctrl+F5), and run `php artisan view:clear` |
| Times look 8 hours off | The app runs on UTC; see the note in section 2 |
| Logged in but get "403" on a page | You're in the wrong workspace or role. Log out and pick the right workspace at `/sk-access` |

---

## 10. Working as a team

- Pull before you start: `git pull`
- After pulling, if `composer.json` or `package.json` changed: `composer install` and `npm install`
- If new migrations came in: `php artisan migrate` (or `php artisan migrate:fresh --seed` to reset the demo)
- Work on a branch and open a pull request into `main`
