#!/usr/bin/env bash

#
# Script Paths
SCRIPT_PATH=$(dirname "$(readlink -f "$0")");
ROOT_PATH=$(realpath "$SCRIPT_PATH/../");

#
# Remote Config
SSH_USER="neueseiteadmin";
SSH_SERVER="neueseite.eu";
DB_USER="catchaguide";
DB_HOST="localhost";
DB_NAME="catchaguide";

#
# Read in MySQL Password
read -s -p "MySQL-Password for user $DB_USER: " DB_PASS;
echo "";


# Create .env
if [[ ! -f $ROOT_PATH/.env ]]; then
  echo "Creating .env file with default values";
  cp $ROOT_PATH/.env.local $ROOT_PATH/.env;
fi

#
# Run Composer Install
echo "Running Composer Install";
composer install

#
## npm
echo "Running npm"
npm ci && npm run development

#
# Sync Database
echo "Running Database Sync";
ssh ${SSH_USER}@${SSH_SERVER} mysqldump --user=${DB_USER} --password=${DB_PASS} ${DB_NAME} > $ROOT_PATH/db.sql;
mysql --host=db --user=db --password=db db < $ROOT_PATH/db.sql;
rm $ROOT_PATH/db.sql

#
# Sync
echo "Running File Sync";
rsync -uvre "ssh -l $SSH_USER" ${SSH_USER}@${SSH_SERVER}:/var/www/vhosts/neueseite.eu/subdomains/catchaguide.neueseite.eu/public/images $ROOT_PATH/public
rsync -uvre "ssh -l $SSH_USER" ${SSH_USER}@${SSH_SERVER}:/var/www/vhosts/neueseite.eu/subdomains/catchaguide.neueseite.eu/public/storage $ROOT_PATH/public

echo "Finished! Have a nice day!"

echo " _   _                                         _ _               _ ";
echo "| | | |                                       | (_)             | |";
echo "| |_| | __ _ _ __  _ __  _   _    ___ ___   __| |_ _ __   __ _  | |";
echo "|  _  |/ _\` | '_ \| '_ \| | | |  / __/ _ \ / _\` | | '_ \ / _\` | | |";
echo "| | | | (_| | |_) | |_) | |_| | | (_| (_) | (_| | | | | | (_| | |_|";
echo "\_| |_/\__,_| .__/| .__/ \__, |  \___\___/ \__,_|_|_| |_|\__, | (_)";
echo "            | |   | |     __/ |                           __/ |    ";
echo "            |_|   |_|    |___/                           |___/     ";