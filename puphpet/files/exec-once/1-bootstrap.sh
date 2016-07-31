#!/bin/bash
echo "------------ START SYSTEM BOOTSTRAP ------------"

### set up necessary variables

export INSTALL_DIR="/home/vagrant"
export VAGRANT_DIR="/vagrant"
export BOOTSTRAP_DIR="/vagrant/puphpet/files/exec-once"

### make sure all the shell files have the same mod date
### this is only necessary so that they start in the proper order

find /vagrant/puphpet -type f -name "*.sh" -exec touch '{}' \;

### make sure php error log file exists

touch /vagrant/logs/php_error.log
chmod 777 /vagrant/logs/php_error.log

### install notifier

sudo apt-add-repository ppa:izx/askubuntu
sudo apt-get update
sudo apt-get install libnotify-bin

echo "------------ END SYSTEM BOOTSTRAP ------------"