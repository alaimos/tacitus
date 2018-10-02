#!/bin/bash
#service php5.6-fpm start

### setup mongo pid file

touch /var/run/mongod.pid

chmod 777 /var/run/mongod.pid

service mongod start