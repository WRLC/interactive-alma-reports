# interactive-alma-reports

Interactive reports utilizing Alma APIs

## Local Development

### Dependencies

* [Docker Desktop](https://www.docker.com/products/docker-desktop/)
* [WRLC/local-dev-traefik](https://github.com/WRLC/local-dev-traefik) reverse proxy (for local networking of Docker containers)
* Local SSH key for git functionality in the PHP container (`~/.ssh/id_rsa`)
* Local git configuration file for git functionality in the PHP container (`~/.gitconfig`)

### Getting Started

1. Clone the repository:
    ```bash
    git clone git@github.com:WRLC/interactive-alma-reports.git
    ```
2. Start the Docker containers:
    ```bash
    cd interactive-alma-reports
    docker-compose up -d
    ```
3. SSH into the `interactive_alma_reports` container:
    ```bash
    docker exec -i -t interactive_alma_reports /bin/bash
    ```
4. Copy local .env file from .env.template:
    ```bash
    cp .env.template .env
    ```
5. Replace the placeholder `API_KEY_INTERACTIVE` value in the .env file with a working Alma API key:
    ```bash
    API_KEY_INTERACTIVE=alma_api_key_goes_here
    ```

6. Update `SSH_KEY_FILE` and `GIT_CONFIG_FILE` in the .env file to match the paths of your local SSH key and git configuration file—if they don't match the values in the template:
    ```bash
    SSH_KEY_FILE=~/.ssh/id_rsa
    GITCONFIG=~/.gitconfig
    ```

7. Visit the application in your browser at [https://interactive-alma-reports.wrlc.localhost](https://interactive-alma-reports.wrlc.localhost)

### Running Tests

Whenever code is pushed to Github, several automated code scans are performed on files in the `/public` folder (the application's web root). To ensure the code will pass these scans, you can run them locally before pushing code to Github:

* PHPStan: `vendor/bin/phpstan analyse public`
* PHPCS: `vendor/bin/phpcs --standard=PSR12 --warning-severity=0 public`
* PHPMD: `vendor/bin/phpmd public text cleancode,codesize,controversial,design,naming,unusedcode`

Among other things, these scans expect the code to be properly indented, have no unused variables, include file/class/function docblocks, and follow the PSR-12 coding standard.

### Adding Reports

To add a new report to the application, create a new PHP file in the `/public` folder.

The application's home page (`/public/index.php`) requires each report's display name to be the first line in its file's docblock.