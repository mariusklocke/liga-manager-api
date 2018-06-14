## Requirements
A working installation of `docker` and `docker-compose`

## Get started
This application comes with an example configuration for running with `docker-compose`. To get started rename `.env.dist` to `.env` and `docker-compose.yml.dist` to `docker-compose.yml` and adjust both configuration files to your local needs.

Before you run the application for the first time, you need to build the docker images:
```bash
bash docker/build-images.sh
```

Now you are ready to start the containers
```bash
docker-compose up -d
```

If you run the `php` container for the first time, you need to run `bin/install.sh`
```bash
docker-compose exec php bin/install.sh
```

For more information on how to manage containers, please refer to the [docker-compose CLI reference](https://docs.docker.com/compose/reference/overview/#command-options-overview-and-help)
