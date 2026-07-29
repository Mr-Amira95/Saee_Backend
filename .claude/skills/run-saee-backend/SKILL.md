---
name: run-saee-backend
description: Build, run, and smoke-test the SAEE Logistics Laravel backend (this repo). Use when asked to run/start/serve the app, hit its routes, verify it boots, or check that a change didn't break the server.
---

This is a Laravel 12 (PHP 8.2) API/web backend. Drive it via
`.claude/skills/run-saee-backend/smoke.sh`, which launches
`php artisan serve` in the background and verifies it with `curl`. All
paths below are relative to the repo root (this skill's directory is
`.claude/skills/run-saee-backend/`).

This was authored and verified on Windows with XAMPP (Git Bash), not
Ubuntu — adjust the "Prerequisites" section if your box differs.

## Prerequisites

- PHP 8.2 with `gd`, `sodium`, and `zip` extensions enabled. On this
  machine that's XAMPP's `C:\xampp\php\php.ini` — the shipped ini has
  them present but commented out:
  ```
  extension=gd
  extension=sodium
  extension=zip
  ```
  Uncomment those three lines (leave `;extension=openssl` commented —
  openssl is already loaded another way here, and enabling it too
  throws a "Module already loaded" warning).
- Composer 2.x (`composer --version`).
- A running MySQL (XAMPP's, listening on `127.0.0.1:3306`, `root` /
  empty password) with the `saee_logistics` database already migrated.
  This repo's `.env.example` defaults to `DB_CONNECTION=sqlite`, but
  one migration (`2026_06_22_000002_update_cliq_alias_type_in_client_bank_details`)
  runs raw `ALTER TABLE ... MODIFY COLUMN ... ENUM(...)` syntax that
  only MySQL understands — sqlite is not a viable option for this repo.

## Setup

`smoke.sh start` does this for you (idempotent — safe to call every
time), but the steps are:

```bash
cp .env.example .env             # only if .env is missing
composer install --no-interaction  # only if vendor/ is missing
php artisan key:generate           # only if APP_KEY is empty
```

Then edit `.env` so the DB block points at the existing MySQL database
instead of sqlite:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saee_logistics
DB_USERNAME=root
DB_PASSWORD=
```

`smoke.sh` does NOT rewrite `.env` for you (it only creates it from the
example if missing) — if you're setting up fresh, apply the DB block
above by hand once.

## Build

No separate build step for the API surface (no asset compilation
needed to serve routes/JSON).

## Run (agent path)

```bash
.claude/skills/run-saee-backend/smoke.sh start
```

This installs deps if needed, generates `APP_KEY` if needed, verifies
the DB is reachable, launches `php artisan serve --port=8000` in the
background, polls until it responds, and prints its status/PID/log
path. If port 8000 is already occupied (e.g. a prior run), it reuses
that instance instead of erroring.

```bash
.claude/skills/run-saee-backend/smoke.sh status   # → "UP: ..." or "DOWN: ..." (exit 1)
.claude/skills/run-saee-backend/smoke.sh stop     # kills the listener on port 8000
```

Log: `storage/logs/dev-serve.log`. Once up, hit routes directly:

```bash
curl -s http://127.0.0.1:8000/                 # public homepage, → HTTP 200, SAEE Logistics HTML
curl -s http://127.0.0.1:8000/portal/login      # → HTTP 200 (admin/login redirects here)
```

Override the port with `PORT=8001 .claude/skills/run-saee-backend/smoke.sh start`.

## Run (human path)

```bash
php artisan serve
```

Blocks in the foreground; `Ctrl-C` to stop. Useless for an agent
(nothing to run the next command in the same shell) — use the agent
path above instead.

## Test

```bash
php artisan test
```

## Gotchas

- **`php artisan db:show` fails even with a healthy DB connection** —
  on this XAMPP MySQL, `performance_schema.session_status` isn't
  populated, and `db:show` queries it for connection stats. It throws
  `SQLSTATE[42S02]: Base table or view not found`. Use
  `php artisan migrate:status` (or any real query) as the
  connectivity check instead — that's what `smoke.sh` does.
- **`$!` after `php artisan serve &` is not always the PID actually
  holding the port.** Observed the wrapper PID and the port's real
  listener PID differ across runs. `smoke.sh stop` handles this by
  falling back to whatever PID `netstat` reports as `LISTENING` on the
  port if the stored PID doesn't work, not by trusting the stored PID
  alone.
- **The default `.env.example` (`DB_CONNECTION=sqlite`) will run
  migrations most of the way then hard-fail** on the
  `client_bank_details` migration's raw MySQL `MODIFY COLUMN ENUM`
  syntax, which SQLite's parser rejects outright. Don't try to make
  sqlite work here — point at MySQL as shown in Setup.
- **`set -e` + `set -o pipefail` + a `grep` with no match inside a
  `$(...)` assignment silently kills the whole script** with no error
  output (bash treats a failed command substitution used as a plain
  assignment as fatal under `-e`, even though the assignment itself
  "succeeds"). `smoke.sh`'s `port_pid()` ends its pipeline with
  `|| true` for exactly this reason — reuse rather than
  reintroducing it if you touch that function.

## Troubleshooting

- **Composer error: `lcobucci/jwt ... requires ext-sodium` /
  `simplesoftwareio/simple-qrcode ... requires ext-gd`**: the two PHP
  extensions aren't enabled. Uncomment them in `php.ini` (see
  Prerequisites) and retry `composer install`.
- **`SQLSTATE[HY000]: General error: 1 near "MODIFY": syntax error`
  during `php artisan migrate`**: you're on sqlite. Switch `.env` to
  the MySQL block in Setup — don't try to patch the migration or the
  sqlite driver.
