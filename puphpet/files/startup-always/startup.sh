#!/bin/bash

service php7.2-fpm start

### setup mongo pid file

touch /var/run/mongod.pid

chmod 777 /var/run/mongod.pid

chown mongodb:mongodb /var/run/mongod.pid

service mongod start