If you want to edit entity class, you should go to root dir, and run this command:
vendor/bin/doctrine orm:clear-cache:metadata
vendor/bin/doctrine orm:clear-cache:query
vendor/bin/doctrine orm:clear-cache:result

In case there is an error: Failed opening required '/tmp/__CG__TchessEntityRoom.php' (include_path='.:/usr/share/php:/usr/share/pear') in /home/tien/Projects/learning/tchess/vendor/doctrine/common/lib/Doctrine/Common/Proxy/AbstractProxyFactory.php on line 207
vendor/bin/doctrine orm:generate-proxies

and then run:
vendor/bin/doctrine orm:schema-tool:update --dump-sql
vendor/bin/doctrine orm:schema-tool:update --force

