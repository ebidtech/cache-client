# PHP component bootstrap
This projects serves as a base for PHP component projects.

## Usage

1. Create a repository for your project.
2. Clone this repository ```git clone git@github.com:ebidtech/php-component-bootstrap.git <MY_PROJECT>```.
3. Change this repository's remote to point to your repository ```git remote set-url origin <MY_REPOSITORY_URL>```.  

## Setup

### Composer

You can download and install composer running ```tools/install-composer.sh```. It will be installed to the repository's base directory, and can be executed with ```php composer.phar```.

You can install a specific composer version by running ```COMPOSER_VERSION=1.0.0-alpha10 tools/install-composer-sh```. By editing **tools/install-composer.sh** you can fix a specific version of composer to always be downloaded for your project.

### PHPUnit

By default **composer.json** is configured to download PHPUnit. Before using PHPUnit you should edit both **phpunit.xml.dist** and change ```<TESTS_DIRECTORY>``` to your tests folder.
