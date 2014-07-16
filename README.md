Installation
=============
Setup virtual host:
copy example.com.conf to /etc/apache2/sites-available/
edit to make it specific to your wish
sudo a2ensite example.com
sudo a2enmod rewrite
sudo service apache2 restart

Fix permission errors:
sudo chown myaccount:www-data -R server-logs/
sudo chown myaccount:www-data -R web/js-vendor/
sudo chown myaccount:www-data -R web/resources/
sudo chown myaccount:www-data -R cache/
sudo chown myaccount:www-data -R logs/
sudo chown myaccount:www-data -R db/
chmod 777 -R db/
sudo chown myaccount:www-data -R files/

Run a web browser and open example.com

Realtime checking opponent move
-------------
If you want the realtime checking move feature, you need install ZeroMQ and
php-zmq.
Follow these instructions:
http://zeromq.org/bindings:php
http://tienxuanvo.wordpress.com/

Restart the web server:
sudo service apache2 restart

Run websocket server:
cd /path/to/example.com/bin/
./run-server

Troubleshooting
=============
In case there is an error: Failed opening required '/tmp/__CG__TchessEntityRoom.php'
vendor/bin/doctrine orm:generate-proxies

If you want to edit entity class, you should go to root dir, and run this command:
vendor/bin/doctrine orm:clear-cache:metadata
vendor/bin/doctrine orm:clear-cache:query
vendor/bin/doctrine orm:clear-cache:result

And then run:
vendor/bin/doctrine orm:schema-tool:update --dump-sql
vendor/bin/doctrine orm:schema-tool:update --force
To update db schema.
