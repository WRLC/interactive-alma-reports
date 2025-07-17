PHP configuration changes should be placed in this folder in a `.ini` file.

For Docker to load them automatically on startup, you need to add a `volume` to `docker-compose.yml` mapping the `.ini` file to `/usr/local/etc/php/conf.d/`. For example:

```yaml
services:
  interactive_alma_reports:
    [...]
    volumes:
      [...]
      - ./ini_local/xdebug.ini:/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
      - ./ini_local/error_reporting.ini:/usr/local/etc/php/conf.d/error_reporting.ini
```
