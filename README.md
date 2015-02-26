Angular [![Build Status](https://travis-ci.org/tienvx/tchess.svg?branch=master)](https://travis-ci.org/tienvx/tchess)
=========

Installation
=============

Setup virtual host:

 1. copy example.com.conf to /etc/apache2/sites-available/
 2. edit to make it specific to your wish
 3. sudo a2ensite example.com
 4. sudo a2enmod rewrite
 5. sudo service apache2 restart

Fix permission errors:

 1. sudo chown www-data:www-data -R server-logs/
 6. sudo chown www-data:www-data -R config/
 2. sudo chown www-data:www-data -R web/js-vendor/
 3. sudo chown www-data:www-data -R web/resources/
 4. sudo chown www-data:www-data -R cache/
 5. sudo chown www-data:www-data -R logs/
 6. sudo chown www-data:www-data -R db/
 7. sudo chmod g+w -R db/
 8. sudo chown www-data:www-data -R files/
 9. Add your user to www-data group

Run a web browser and open example.com, follow instruction to install Tchess.

Realtime checking opponent move
-------------------------------

If you want the realtime move checking feature, you need install ZeroMQ and
php-zmq. Follow these instructions:

 * http://zeromq.org/bindings:php
 * http://tienxuanvo.wordpress.com/

Restart the web server:

```
sudo service apache2 restart
```

Run websocket server:

```
cd /path/to/example.com/bin/
./run-server
```

Update doctrine entities
------------------------

If you updated entity classes, you should go to root dir, and run these commands
to update db schema:

 * vendor/bin/doctrine orm:schema-tool:update --dump-sql
 * vendor/bin/doctrine orm:schema-tool:update --force

Troubleshooting
=============

In case there is an error: Failed opening required '/tmp/__CG__TchessEntityRoom.php'

```
vendor/bin/doctrine orm:generate-proxies
```

Development
=============

 * The idea of backend is come from https://github.com/sagnew/Chess
 * Front end are combine of http://chessboardjs.com and
   https://github.com/jhlywa/chess.js
 * With various [components](tuts/) from [Symfony 2](http://symfony.com/)
 * Real-time updates feature is come from http://socketo.me/docs/push
 * Pull requests are welcome.
