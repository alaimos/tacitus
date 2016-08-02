#!/bin/bash
echo "------------ START SYSTEM BOOTSTRAP ------------"

### set up necessary variables

export INSTALL_DIR="/home/vagrant"
export VAGRANT_DIR="/vagrant"
export BOOTSTRAP_DIR="/vagrant/puphpet/files/exec-once"

### make sure all the shell files have the same mod date
### this is only necessary so that they start in the proper order

find /vagrant/puphpet -type f -name "*.sh" -exec touch '{}' \;

### setup mongo pid file

touch /var/run/mongod.pid

chmod 777 /var/run/mongod.pid

echo "------------ END SYSTEM BOOTSTRAP ------------"