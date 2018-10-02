#!/bin/bash
echo "------------ START SETTING UP PHP LOGS ------------"

### make sure php error log file exists

touch /vagrant/logs/php_error.log
chmod 777 /vagrant/logs/php_error.log

echo "------------ END SETTING UP PHP LOGS ------------"