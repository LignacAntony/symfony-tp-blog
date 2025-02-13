# Instructions

1. Cloner le dépôt
2. Modifier `OPENAI_API_KEY` dans le fichier `.env`
3. Exécuter `docker-compose up -d` pour la db et adminer
4. Exécuter `composer install`
5. Exécuter `php bin/console doctrine:migrations:migrate`
6. Exécuter `php bin/console doctrine:fixtures:load`
7. Démarrer le serveur avec `symfony server:start`

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

## Fonctionnalités IA

  - En tant qu'admin, générer un article sur la route `/article` et cliquer sur le bouton `Générer avec l'IA`.
  - En tante qu'admin, traduire un article sur la route `/article/{id}`, cliquer sur le bouton `Traduire` et choisir la langue.
  - En tant qu'admin, je peux vérifier le contenu d'un commentaire s'il n'est pas approprié sur la route `/review/{id}` et bannir l'utilisateur en cliquant sur le bouton `Vérifier le contenu`.
  - En tant qu'utilisateur, je peux créer un compte et saisir un username sur la route `/register`, sur le username est innaproprié, l'utilisateur est redirigé vers la route `/register` avec un message d'erreur.