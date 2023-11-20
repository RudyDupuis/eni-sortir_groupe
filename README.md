##Les commandes

### Initialisation de la base de donnée

Créer la base de donnée : php bin/console doctrine:database:create

Faire les migrations : php bin/console doctrine:migrations:migrate

Ajouter les fausses données : php bin/console doctrine:fixtures:load

### Supprrimer toutes les tables

php bin/console doctrine:schema:drop --force

### Effectuer cette commande pour mettre à jour les états

Pour une mise du site sur serveur, il faudrait activer cette commande toutes les heures

php bin/console app:update-etats
