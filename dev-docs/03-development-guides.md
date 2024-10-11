# Development guides
- Composer packages are used but because we are having conflicts with other WordPress plugins so we have specific setup to the composer.
- In `composer.json` we put main packages that are required for the Yivic Base. And we put dev packages that are used for the CodeStyle checking and fixing only. They are not using new dependencies that would not cause the conflicts in WordPress ecosystem when we perform the development.
- In `composer-dev81.json`, we put extra packages to be able to build up a complete WordPress instance. It contains CodeStyle tools and testing tools (PHPUnit, Codeception)
- `composer-dev73.json` is simply a setup for running in PHP 7.3 to test the Yivic Base manually.

## Install Composer dependencies
- Use PHP 7.3 to install dependencies (composer73 = 'php73 composer')
```
XDEBUG=off composer73 install
```
or if you don't have PHP 7.3 locally, you can do
```
docker run --rm --interactive --tty -e XDEBUG_MODE=off -v $PWD:/app -v ~/.composer:/root/.composer manhphucofficial/php73_cli composer install
```
you can do `update` if you want to update new dependencies.

This would have Development tools like `phpcs`, `phpcbf`

If you face errors when running in legacy PHP versions, you can skip the dev dependencies
```
XDEBUG=off composer80 install -no-dev
```
and you can check [Troubleshooting docs](05-troubleshooting.md) for more details

## Development using PHP 8.1
There's a Docker environment for WordPress instance running on PHP 8.1. You need to have Docker installed then you can do the following:
- Install needed dependencies (using **composer**)
```
XDEBUG=off COMPOSER=composer-dev81.json composer install
```
or if you don't have PHP 8.1 locally, you can do
```
docker run --rm --interactive --tty -e XDEBUG_MODE=off -e COMPOSER=composer-dev81.json -v $PWD:/app -v ~/.composer:/root/.composer manhphucofficial/php81_cli composer install
```
This command will set up a WordPress instance in `dev-docker/wordpress` folder and load this **Yivic Base** plugin as a Must Use plugin in `dev-docker/wordpress/wp-content/mu-plugins/yivic-base`

- You can **up**
```
docker-compose up -d
```
then list the containers to see working port to use that in your browsers
```
docker-compose ps
```

## Codestyling (PHPCS)
- Fix all possible phpcs issues
```
php81 ./vendor/bin/phpcbf
```
or using docker if you don't have PHP 8.1 locally
```
docker run --rm --interactive --tty -v $PWD:/app manhphucofficial/php81_cli ./vendor/bin/phpcbf
```

- Fix possible phpcs issues on a specified folder
```
php81 ./vendor/bin/phpcbf <path/to/the/folder>
```

- Find all the phpcs issues
```
php81 ./vendor/bin/phpcs
```

- Suppress one or multible phpcs rules for the next below line
```
// phpcs:ignore <rule1>(, <rule2>...)
```
or at same line
```
$foo = "bar"; // phpcs:ignore
```

- Disable phpcs for a block of code
```
// phpcs:disable
/*
$foo = 'bar';
*/
// phpcs:enable
```

## Running Unit Test
We must run the composer and codecept run test using PHP 8.1 (considering `php81` is the alias to your PHP 8.1 executable file). We use PHPUnit 9 to be able to use `mockery/mockery`, `phpspec/prophecy`

If you don't have PHP 8.1 locally, you can use the docker:
```
docker pull manhphucofficial/php81_cli
```
and whenever you want to rin something, you can do something like this:
```
docker run --rm --interactive --tty -v $PWD:/app manhphucofficial/php81_cli ./vendor/bin/phpunit
```

- Running `phpunit`
```
php81 ./vendor/bin/phpunit
```
- Run Unit Test on a specific file (for development purposes)
```
php81 ./vendor/bin/phpunit --debug --verbose tests/Unit/Helpers_Test.php
```
- Create a Unit Test file
You can copy `tests/Unit/Sample_Test.php` file to your desired test file

### Using Coverage report
- Run Unit Test with PhpUnit (with coverage report)
```
XDEBUG_MODE=coverage php81 ./vendor/bin/phpunit --coverage-text
```
or
```
docker run --rm --interactive --tty -e XDEBUG_MODE=coverage -v $PWD:/app manhphucofficial/php81_cli ./vendor/bin/phpunit --coverage-text
```

- Run Unit Test with code coverage report on a single file
```
docker run --rm --interactive --tty -e XDEBUG_MODE=coverage -v $PWD:/app manhphucofficial/php81_cli php  -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --no-configuration --colors --bootstrap=tests/bootstrap-unit.php --debug --verbose ./tests/Unit --whitelist=<path/to/file/to-be-reported>
```
e.g
```
docker run --rm --interactive --tty -e XDEBUG_MODE=coverage -v $PWD:/app manhphucofficial/php81_cli php  -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --no-configuration --colors --bootstrap=tests/bootstrap-unit.php --debug --verbose ./tests/Unit --whitelist=src/App/Support/Yivic_Base_Helper.php
```

- You can have the html report working by adding `--coverage-html ./tests/_output` to specify the folder to output the report (the HTML report would give you details about the coverage)
```
docker run --rm --interactive --tty -e XDEBUG_MODE=coverage -v $PWD:/app manhphucofficial/php81_cli php  -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --no-configuration --colors --bootstrap=tests/bootstrap-unit.php --debug --verbose --coverage-html ./tests/_output ./tests/Unit --whitelist=<path/to/file/to-be-reported>
```

## Development using PHP 7.3
- Install needed packages
```
COMPOSER=composer-dev73.json composer73 update
```
or if you don't have PHP 7.3 locally, you can do
```
docker run --rm --interactive --tty -e XDEBUG_MODE=off -e COMPOSER=composer-dev73.json -v $PWD:/app -v ~/.composer:/root/.composer manhphucofficial/php73_cli composer install
```

- Start the Docker containers
```
docker compose -f docker-compose73.yml up -d
```
then check the containers
```
docker compose -f docker-compose73.yml ps
```
by default, it should work here http://127.0.0.1:10173/

- Once you complete the setup, you can check if Yivic Base plugin works here
```
docker compose -f docker-compose73.yml exec --user=webuser wordpress73 wp yivic-base info
```
