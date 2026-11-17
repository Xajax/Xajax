#!/bin/bash
# Set up hypertrek fully on php 8.2 with the new xajax, load its DB dump.
set -u
APPDIR=/apps/luigirosa_hypertrek
DST=/var/www/html/hypertrek

# --- mariadb ---
mkdir -p /var/run/mysqld && chown mysql:mysql /var/run/mysqld
[ -d /var/lib/mysql/mysql ] || mysql_install_db --user=mysql --datadir=/var/lib/mysql >/dev/null 2>&1
mysqld_safe --user=mysql >/tmp/mariadb.log 2>&1 &
for i in $(seq 1 60); do mysqladmin ping >/dev/null 2>&1 && break; sleep 1; done
echo "[setup] mariadb up"

mysql -uroot <<'SQL'
DROP DATABASE IF EXISTS hypertrek;
CREATE DATABASE hypertrek DEFAULT CHARACTER SET utf8;
CREATE USER IF NOT EXISTS 'ht'@'localhost' IDENTIFIED BY 'htpass';
GRANT ALL ON hypertrek.* TO 'ht'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[setup] extracting SQL archives"
mkdir -p /tmp/htsql
tar xzf "$APPDIR/hypertrek.tgz" -C /tmp/htsql 2>/dev/null
tar xzf "$APPDIR/hypertrek2.tgz" -C /tmp/htsql 2>/dev/null
echo "[setup] loading hypertrek.sql (content)"
mysql -uroot hypertrek < /tmp/htsql/hypertrek.sql 2>/tmp/sqlload.err
echo "[setup] loading hypertrek2.sql (runtime tables: store, cerca, ...)"
mysql -uroot hypertrek < /tmp/htsql/hypertrek2.sql 2>>/tmp/sqlload.err
echo "[setup] tables: $(mysql -uroot -N -e 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema="hypertrek"')"

# --- app + xajax swap ---
rm -rf "$DST"; cp -r "$APPDIR" "$DST"
rm -rf "$DST/hypertrek.tgz" "$DST/hypertrek2.tgz" "$DST/hypertrek.sql"
rm -rf "$DST/xajax_core" "$DST/xajax_js" "$DST/xajax_plugins"
cp -r /src/xajax_core "$DST/xajax_core"
cp -r /src/xajax_js   "$DST/xajax_js"
cp -r /src/xajax_plugins "$DST/xajax_plugins"
cp /src/copyright.inc.php "$DST/copyright.inc.php"

cat > "$DST/setup.ini" <<INI
[sql]
host1 = localhost
database1 = hypertrek
user1 = ht
password1 = htpass
host2 = localhost
database2 = hypertrek
user2 = ht
password2 = htpass

[setup]
baseurl = "http://localhost/hypertrek"
crondailyfile = "cron.daily"
sitemapname = "sitemapinfo.xml"
rsslast = "ultimemodifiche.xml"
INI

chown -R www-data:www-data "$DST"
echo "[setup] done; entry:"
curl -s "http://127.0.0.1/hypertrek/index.php" | sed -e 's/<[^>]*>//g' | grep -vE '^\s*$' | head -20
