Install
=======
sudo chown tien:www-data -R server-logs/
sudo chown tien:www-data -R web/js-vendor/
sudo chown tien:www-data -R web/resources/
sudo chown tien:www-data -R cache/
sudo chown tien:www-data -R logs/
sudo chown tien:www-data -R db/
chmod 777 -R db/
sudo chown tien:www-data -R files/

In case there is an error: Failed opening required '/tmp/__CG__TchessEntityRoom.php'
vendor/bin/doctrine orm:generate-proxies

If you want to edit entity class, you should go to root dir, and run this command:
vendor/bin/doctrine orm:clear-cache:metadata
vendor/bin/doctrine orm:clear-cache:query
vendor/bin/doctrine orm:clear-cache:result

and then run:
vendor/bin/doctrine orm:schema-tool:update --dump-sql
vendor/bin/doctrine orm:schema-tool:update --force

