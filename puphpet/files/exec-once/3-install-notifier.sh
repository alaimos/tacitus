#!/bin/bash
echo "------------ START INSTALL NOTIFIER ------------"

### install notifier

sudo apt-add-repository ppa:izx/askubuntu
sudo apt-get update
sudo apt-get install libnotify-bin -y

echo "------------ END INSTALL NOTIFIER ------------"