#!/bin/bash
# Preview bootstrap: ensures PHP + MariaDB exist (pod restarts wipe /usr), then serves Laravel on :3000
if ! command -v php >/dev/null 2>&1 || ! command -v mysqld_safe >/dev/null 2>&1; then
  apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y -qq php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-intl php8.2-sqlite3 unzip mariadb-server > /tmp/apt.log 2>&1
fi
if ! mysqladmin ping -uroot --silent 2>/dev/null; then
  mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
  (mysqld_safe > /tmp/mysql.log 2>&1 &)
  for i in $(seq 1 30); do mysqladmin ping -uroot --silent 2>/dev/null && break; sleep 1; done
fi
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS esakip; CREATE USER IF NOT EXISTS 'esakip'@'localhost' IDENTIFIED BY 'esakip123'; CREATE USER IF NOT EXISTS 'esakip'@'127.0.0.1' IDENTIFIED BY 'esakip123'; GRANT ALL ON esakip.* TO 'esakip'@'localhost'; GRANT ALL ON esakip.* TO 'esakip'@'127.0.0.1'; FLUSH PRIVILEGES;"
cd /app/esakip
php artisan migrate --force --seed >/tmp/migrate.log 2>&1
export PHP_CLI_SERVER_WORKERS=6
exec php artisan serve --host=0.0.0.0 --port=3000 --no-reload
