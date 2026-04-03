# Tests running
For local development and testing different PHP versions.

Required:
* Docker
* Docker-compose


### Testing single PHP version
* cd to repository root
* Run `docker compose up php-8.2_intl --build`
* See output

### Testing all PHP versions
* cd to repository root
* Run `docker compose up --build`

### Teardown/cleanup
* Run
```
docker-compose down --volumes --remove-orphans
```
