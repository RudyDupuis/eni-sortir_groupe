# Les commandes

## Initialisation de la base de donnée

Créer la base de donnée : php bin/console doctrine:database:create

Faire les migrations : php bin/console doctrine:migrations:migrate

Ajouter les fausses données : php bin/console doctrine:fixtures:load

### Supprimer toutes les tables

php bin/console doctrine:schema:drop --force

## Mettre à jour les états des sorties

Pour une mise du site sur serveur, il faudrait activer cette commande toutes les heures

php bin/console app:update-etats

## Activer la réinitialisation de mot de passe

Ajouter le mailer dans le .env.local
Changer les valeurs de identifiant, motdepasse, serveur et de port

`MAILER_DSN=smtp://identifaint:motdepasse@serveur:port` (voir les valeurs dans le notion)

### Lancer l'envoie de mail en async

php bin/console console messenger:consume async
