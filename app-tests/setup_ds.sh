#!/bin/bash
set -u
APPDIR=/apps/daveseff_DS_Scheduler
DST=/var/www/html/ds

service mariadb start >/dev/null 2>&1
for i in $(seq 1 30); do mysqladmin ping >/dev/null 2>&1 && break; sleep 1; done
mysql -uroot <<'SQL'
CREATE DATABASE IF NOT EXISTS ds_scheduler;
CREATE USER IF NOT EXISTS 'dbuser'@'localhost' IDENTIFIED BY 'dbpassword';
GRANT ALL ON ds_scheduler.* TO 'dbuser'@'localhost';
FLUSH PRIVILEGES;
SQL
mysql -uroot ds_scheduler <<'SQL'
DROP TABLE IF EXISTS jobs;
CREATE TABLE jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  host VARCHAR(30) NOT NULL,
  user VARCHAR(31) NOT NULL,
  start_time DATETIME DEFAULT NULL,
  end_time DATETIME DEFAULT NULL,
  depends INT NOT NULL DEFAULT 0,
  depend_mode INT NOT NULL DEFAULT 0,
  event_trigger INT NOT NULL DEFAULT 0,
  reoccur VARCHAR(30) DEFAULT NULL,
  status INT NOT NULL DEFAULT 99999,
  rc INT DEFAULT 99999,
  command VARCHAR(300) NOT NULL,
  update_flag TINYINT(1) NOT NULL DEFAULT 1,
  comment TEXT NOT NULL,
  UNIQUE KEY dedup_jobs (name,host)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS results;
CREATE TABLE results (
  id INT NOT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  output TEXT,
  uid VARCHAR(32) NOT NULL,
  rc INT NOT NULL,
  KEY id (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS dep_modes;
CREATE TABLE dep_modes (mode_id INT NOT NULL, mode VARCHAR(25) NOT NULL, PRIMARY KEY (mode_id)) ENGINE=MyISAM DEFAULT CHARSET=latin1;
INSERT INTO dep_modes VALUES (0,'--'),(1,'Success'),(2,'Failure');
INSERT INTO jobs (id,name,host,user,command,comment,status,rc) VALUES
 (1,'backup_home','localhost','root','tar czf /tmp/home.tgz /home','nightly backup',0,0),
 (2,'log_rotate','localhost','root','logrotate -f /etc/logrotate.conf','rotate logs',0,0);
SQL
echo "[setup] db ready; jobs=$(mysql -uroot -N -e 'SELECT COUNT(*) FROM ds_scheduler.jobs')"

rm -rf "$DST"; cp -r "$APPDIR/web" "$DST"
rm -rf "$DST/xajax"
cp -r /src/xajax_core "$DST/xajax_core"
cp -r /src/xajax_js   "$DST/xajax_js"
mkdir -p "$DST/xajax"
cp -r /src/xajax_core "$DST/xajax/xajax_core"
cp -r /src/xajax_js   "$DST/xajax/xajax_js"
cp /src/copyright.inc.php "$DST/copyright.inc.php"
cat > "$DST/dbconfig.php" <<'CFG'
<?php
$username="dbuser";
$password="dbpassword";
$database="ds_scheduler";
$dbserver="127.0.0.1";
?>
CFG
# --- app-side compatibility fix (NOT xajax) ---
# The bundled JpGraph 2.x hard-blocks any PHP major != 5 ("not compatible with
# this release of the library").  This is a stale version guard in the charting
# dependency, unrelated to xajax.  Relax it so the app can run under PHP 8.2.
sed -i "s/if (\$majorC != \$majorR) return false;/if (false) return false;/" "$DST/jpgraph/src/jpgraph.php"
grep -q "if (false) return false;" "$DST/jpgraph/src/jpgraph.php" && echo "[setup] patched JpGraph PHP version guard (app-side)"
# --- more app-side PHP 8 fixes (NOT xajax) ---
# functions.php interpolates array keys without quotes (e.g. {$jobs[id]}), which
# PHP 8 rejects as an undefined constant (fatal Error).  Quote them.
php /fix_ds.php "$DST/functions.php"
# The app logs to /var/log/scheduler_web.log; make it writable by the web user.
touch /var/log/scheduler_web.log; chmod 666 /var/log/scheduler_web.log
echo "[setup] done; entry: http://127.0.0.1/ds/index.php"
