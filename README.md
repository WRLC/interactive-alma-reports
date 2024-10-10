# interactive-alma-reports

![PHPstan](https://github.com/WRLC/interactive-alma-reports/actions/workflows/phpstan.yml/badge.svg?branch=main)&nbsp;
![PHPCS](https://github.com/WRLC/interactive-alma-reports/actions/workflows/phpcs.yml/badge.svg?branch=main)&nbsp;
![PHPMD](https://github.com/WRLC/interactive-alma-reports/actions/workflows/phpmd.yml/badge.svg?branch=main)&nbsp;

Interactive reports utilizing Alma APIs

# Local Development

## Dependencies

* Docker Desktop

## Getting Started

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
4. Copy .env file:
    ```bash
    cp .env.template .env
    ```
5. Replace the placeholder `API_KEY_INTERACTIVE` value in the .env file with a working Alma API key.
6. Visit the application in your browser at `https://interactive-alma-reports.wrlc.localhost`

## Running Tests

Whenever code is pushed to Github, several automated code scans are performed on files in the `/public` folder (the application's web root). To ensure the code will pass these scans, you can run them locally before pushing code to Github:

* PHPStan: `vendor/bin/phpstan analyse public`
* PHPCS: `vendor/bin/phpcs --standard=PSR12 --warning-severity=0 public`
* PHPMD: `vendor/bin/phpmd public text cleancode,codesize,controversial,design,naming,unusedcode`

Among other things, these scans expect the code to be properly indented, have no unused variables, include file/class/function docblocks, and follow the PSR-12 coding standard.

## Adding Reports

To add a new report to the application, create a new PHP file in the `/public` folder.

The application's home page (`/public/index.php`) requires each report's display name to be the first line in its file's docblock.