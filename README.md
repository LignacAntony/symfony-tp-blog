# Instructions

1. Cloner le dépôt
2. Exécuter `docker-compose up -d` pour la db et adminer
3. Exécuter `composer install`
4. Exécuter `php bin/console doctrine:migrations:migrate`
5. Exécuter `php bin/console doctrine:fixtures:load`
6. Démarrer le serveur avec `symfony server:start`

## Connexion

   - Role Admin
    **Email**: admin@example.com
    **Mot de passe**: password
   - Role User
    **Email**: anto@example.com
    **Mot de passe**: password
   - Role Banned
    **Email**: hitler@example.com
    **Mot de passe**: password