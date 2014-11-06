Installation
=============

Setup virtual host:

 1. copy example.com.conf to /etc/apache2/sites-available/
 2. edit to make it specific to your wish
 3. sudo a2ensite example.com
 4. sudo a2enmod rewrite
 5. sudo service apache2 restart

Fix permission errors:

 1. sudo chown myaccount:www-data -R server-logs/
 2. sudo chown myaccount:www-data -R web/js-vendor/
 3. sudo chown myaccount:www-data -R web/resources/
 4. sudo chown myaccount:www-data -R cache/
 5. sudo chown myaccount:www-data -R logs/
 6. sudo chown myaccount:www-data -R db/
 7. chmod 777 -R db/
 8. sudo chown myaccount:www-data -R files/

Run a web browser and open example.com

Realtime checking opponent move
-------------------------------

If you want the realtime checking move feature, you need install ZeroMQ and
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

If you want to edit entity class, you should go to root dir, and run this command:

 * vendor/bin/doctrine orm:clear-cache:metadata
 * vendor/bin/doctrine orm:clear-cache:query
 * vendor/bin/doctrine orm:clear-cache:result

And then run:

 * vendor/bin/doctrine orm:schema-tool:update --dump-sql
 * vendor/bin/doctrine orm:schema-tool:update --force

To update db schema.

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
 * Pull requests are welcome.
