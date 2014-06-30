If you want to edit entity class, you should go to root dir, and run this command:
vendor/bin/doctrine orm:clear-cache:metadata

and then run:
vendor/bin/doctrine orm:schema-tool:update --dump-sql
vendor/bin/doctrine orm:schema-tool:update --force

