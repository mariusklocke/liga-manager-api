#!/usr/bin/env bash
set -ex

cleanup() {
    docker compose down -v
}

generate_secret() {
    head -c "$1" /dev/urandom | xxd -ps | tr -d '\n'
}

init_artifacts_dir() {
    rm -rf build/artifacts
    mkdir -m 777 build/artifacts
}

install_dev_dependencies() {
    docker run --rm -v "$PWD:/app" -u "$(id -u):$(id -g)" --userns host \
        composer install --ignore-platform-reqs --no-cache --no-progress
}

login_to_docker_hub() {
    if [[ -n "${DOCKER_TOKEN}" ]]; then
        echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
    fi
}

run_tests() {
    docker compose exec php phpunit -c config/phpunit-isolated.xml --display-deprecations --display-warnings
}

run_tests_with_coverage() {
    docker compose exec -e LOG_PATH=artifacts/app-xdebug.log php \
        php -d zend_extension=xdebug vendor/bin/phpunit -c config/phpunit-xdebug.xml
}

start_containers() {
    DB_PASSWORD=$(generate_secret 16) \
    DB_ROOT_PASSWORD=$(generate_secret 16) \
    JWT_SECRET=$(generate_secret 32) \
    docker compose up --wait --quiet-pull
}

validate_architecture() {
    docker compose exec php deptrac analyse --config-file config/deptrac.yaml --no-progress
}

validate_api_spec() {
    docker compose exec php php-openapi validate openapi.yml
}

trap cleanup EXIT

login_to_docker_hub
init_artifacts_dir
install_dev_dependencies
start_containers
validate_architecture
validate_api_spec
run_tests
run_tests_with_coverage
