#!/bin/bash
set -u
APPDIR=/apps/nwollmann_coach_challenges
DST=/var/www/html/coach

# --- db ---
service mariadb start >/dev/null 2>&1
for i in $(seq 1 30); do mysqladmin ping >/dev/null 2>&1 && break; sleep 1; done
mysql -uroot <<'SQL'
CREATE DATABASE IF NOT EXISTS coach_challenges;
CREATE USER IF NOT EXISTS 'ChallengeData'@'localhost' IDENTIFIED BY '479zXScPR9LbEHAU';
GRANT ALL ON coach_challenges.* TO 'ChallengeData'@'localhost';
FLUSH PRIVILEGES;
SQL
mysql -uroot coach_challenges <<'SQL'
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  userid INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  salt VARCHAR(32) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  name VARCHAR(255) DEFAULT NULL,
  registrationToken VARCHAR(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS questions;
CREATE TABLE questions (
  questionid INT NOT NULL,
  lessonid INT NOT NULL,
  form VARCHAR(255) DEFAULT NULL,
  params TEXT DEFAULT NULL,
  PRIMARY KEY (questionid, lessonid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
echo "[setup] db ready; users=$(mysql -uroot -N -e 'SELECT COUNT(*) FROM coach_challenges.users')"

# --- app + xajax swap ---
rm -rf "$DST"; cp -r "$APPDIR" "$DST"
rm -rf "$DST/xajax_core" "$DST/xajax_js" "$DST/copyright.inc.php"
cp -r /src/xajax_core "$DST/xajax_core"
cp -r /src/xajax_js "$DST/xajax_js"
cp /src/copyright.inc.php "$DST/copyright.inc.php"

# app uses absolute paths like /model/ajaxLibrary.php and DOCUMENT_ROOT-based includes.
# Provide them at the filesystem root so the app works unmodified.
ln -sfn "$DST/model"    /var/www/html/model
ln -sfn "$DST/views"    /var/www/html/views
ln -sfn "$DST/requests" /var/www/html/requests
ln -sfn "$DST/scripts"  /var/www/html/scripts
ln -sfn "$DST/css"      /var/www/html/css
ln -sfn "$DST/xajax_core" /var/www/html/xajax_core
ln -sfn "$DST/xajax_js"   /var/www/html/xajax_js
ln -sfn "$DST/model" /model; ln -sfn "$DST/views" /views; ln -sfn "$DST/requests" /requests
echo "[setup] done; entry: http://127.0.0.1/coach/index.php"
