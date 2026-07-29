#!/usr/bin/env bash
# Driver for the SAEE Logistics Laravel backend.
# Usage:
#   ./smoke.sh start   - install/build if needed, launch php artisan serve, smoke-test it
#   ./smoke.sh stop    - kill the server started by this script
#   ./smoke.sh status  - check whether it's up
set -euo pipefail

SKILL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SKILL_DIR/../../.." && pwd)"
PORT="${PORT:-8000}"
LOG="$ROOT/storage/logs/dev-serve.log"
PIDFILE="$ROOT/storage/dev-serve.pid"

cmd="${1:-start}"

port_pid() {
  netstat -ano 2>/dev/null | grep "127.0.0.1:$PORT" | grep LISTENING | awk '{print $5}' | head -1 || true
}

stop() {
  local pid
  pid="$(cat "$PIDFILE" 2>/dev/null || true)"
  if [ -z "$pid" ]; then pid="$(port_pid)"; fi
  if [ -n "$pid" ]; then
    taskkill //F //PID "$pid" >/dev/null 2>&1 || kill "$pid" 2>/dev/null || true
    echo "Stopped PID $pid"
  else
    echo "Nothing listening on port $PORT"
  fi
  rm -f "$PIDFILE"
}

status() {
  if curl -sf -o /dev/null "http://127.0.0.1:$PORT/"; then
    echo "UP: http://127.0.0.1:$PORT/ (HTTP 200)"
  else
    echo "DOWN: nothing responding on port $PORT"
    return 1
  fi
}

start() {
  cd "$ROOT"

  if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env from .env.example (defaults to sqlite - see Gotchas in SKILL.md re: MySQL)"
  fi

  if [ ! -d vendor ]; then
    composer install --no-interaction
  fi

  if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate
  fi

  # Fail fast with a clear message if DB isn't reachable, instead of a wall of
  # migration/query errors from whichever route is hit first. (`db:show` fails
  # on this XAMPP MySQL because performance_schema.session_status isn't
  # populated - migrate:status is a reliable connectivity check instead.)
  if ! php artisan migrate:status >/dev/null 2>&1; then
    echo "ERROR: database not reachable. Check DB_* vars in .env (see SKILL.md Gotchas)." >&2
    exit 1
  fi

  existing="$(port_pid)"
  if [ -n "$existing" ]; then
    echo "Port $PORT already in use by PID $existing - reusing it."
    status
    return
  fi

  mkdir -p "$(dirname "$LOG")"
  nohup php artisan serve --port="$PORT" > "$LOG" 2>&1 &
  echo $! > "$PIDFILE"

  for _ in $(seq 1 30); do
    curl -sf -o /dev/null "http://127.0.0.1:$PORT/" && break
    sleep 0.5
  done

  status
  echo "Log: $LOG"
  echo "PID: $(cat "$PIDFILE")"
}

case "$cmd" in
  start) start ;;
  stop) stop ;;
  status) status ;;
  *) echo "Usage: $0 {start|stop|status}" >&2; exit 1 ;;
esac
