# Gourmy Back

[![docker](https://img.shields.io/badge/Docker-2CA5E0?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

[![symfony](https://img.shields.io/badge/Symfony-000000?style=for-the-badge&logo=Symfony&logoColor=white)](https://symfony.com/doc/current/index.html)

[![PostGreSQL](https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)

[![Tailwind](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)



## Préréquis

Avant de commencer, assurez-vous que vous avez les éléments suivants :

- Installer [Docker](https://docs.docker.com/get-docker/)
- Installer [Docker compose](https://docs.docker.com/compose/)
- Installer [Just](https://just-install.github.io/)
- Avoir le container Docker [local_env](https://github.com/laura-lpn/local_env)

## installation

> Créez un fichier .env.local à la racine du projet et remplissez-le avec le fichier .env

Avec Docker et Just

```bash
  just up
```

```bash
  just composer install
```

### Sur Windows

Dans le fichier %SystemRoot%\System32\drivers\etc\hosts ouvert avec le bloc note en administrateur

Ajouter après les autres IPs :

```bash
127.0.0.1 db.gourmy.aaa
127.0.0.1 backend.gourmy.aaa
127.0.0.1 mailer.gourmy.aaa
```

## Convention de commits

- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)

## Auteur

- [Laura Lepannetier](https://github.com/laura-lpn)

## License

This project is proprietary and confidential. Code duplication and re-use without explicit permission is not allowed.