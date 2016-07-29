#!/bin/bash
echo "------------ RUNNING STARTUP COMMANDS -------------"

### Set variables
export ORIENT_VERSION="2.2.6"

export INSTALL_DIR="/home/vagrant"
export VAGRANT_DIR="/vagrant"

export _JAVA_OPTIONS="-Djava.net.preferIPv4Stack=true"

## start the orient server
sudo nohup $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/bin/orientdb.sh start

sleep 15

echo "------------ END: RUNNING STARTUP COMMANDS ------------"