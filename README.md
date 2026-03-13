# 1. Create an entity
php bin/console make:entity Task

# 2. Generate the migration
php bin/console make:migration

# 3. Apply it to the database
php bin/console doctrine:migrations:migrate

pensebete