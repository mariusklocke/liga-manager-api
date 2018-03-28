## Requirements
A working installation of `docker` and `docker-compose`

## Get started
This application comes with an example configuration for running with `docker-compose`. To get started rename `.env.dist` to `.env` and `docker-compose.yml.dist` to `docker-compose.yml` and adjust both configuration files to your local needs.

Before you run the application, you need to generate the secret for signing JSON Web Tokens (JWT) and adjust the permissions, so the container can read it:
```bash
ssh-keygen -t rsa -b 4096 -f config/jwt/secret.key -N ''
chmod 644 config/jwt/secret.key
```

Now you are ready to go and can build & start the containers
```bash
sh docker/build-images.sh
docker-compose up -d
```

After creating the `mariadb` container you need to create the schema first:
```bash
docker-compose exec php php bin/console.php orm:schema-tool:create
```

Use this command to load data fixtures:
```bash
docker-compose exec php php bin/console.php app:load-fixtures
```

For more information on how to manage containers, please refer to the [docker-compose CLI reference](https://docs.docker.com/compose/reference/overview/#command-options-overview-and-help)

## Install Swagger UI
```bash
npm install swagger-ui-dist
mv node_modules/swagger-ui-dist public/docs
```
