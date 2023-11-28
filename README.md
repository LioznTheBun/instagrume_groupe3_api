Installation
composer install

Structure de la base de données
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate

Données de la base de données
php bin/console doctrine:fixtures:load

Lancement
symfony server:start -d --no-tls --port=3000

Arrêt
symfony server:stop