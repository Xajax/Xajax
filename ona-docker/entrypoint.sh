#!/bin/bash
set -u

# --- start MariaDB ---------------------------------------------------------
mkdir -p /var/run/mysqld && chown mysql:mysql /var/run/mysqld
if [ ! -d /var/lib/mysql/mysql ]; then
    echo "[entrypoint] initializing MariaDB data dir"
    mysql_install_db --user=mysql --datadir=/var/lib/mysql >/dev/null 2>&1
fi
echo "[entrypoint] starting MariaDB"
mysqld_safe --user=mysql >/tmp/mariadb.log 2>&1 &

# wait for the server to accept connections
for i in $(seq 1 60); do
    if mysqladmin ping >/dev/null 2>&1; then break; fi
    sleep 1
done
echo "[entrypoint] MariaDB is up"

# --- install ONA if not already installed ---------------------------------
DBCONF=/opt/ona/www/local/config/database_settings.inc.php
if [ ! -f "$DBCONF" ]; then
    echo "[entrypoint] running ONA installer"
    # Answers to the prompts (one per line):
    #   license n | dbhost localhost | admin root | admin pw (blank)
    #   app user ona_sys | app pw changeme | db name default | domain example.com
    printf '\n\n\n\n\n\n\n\n' | php /opt/ona/install/installcli.php
    echo "[entrypoint] installer finished"
fi

# --- hand off to Apache ----------------------------------------------------
echo "[entrypoint] starting Apache"
exec apache2-foreground
