### For pushing to WordPress.org repo
- To get dependencies for PHP 7.2.5 - 8.0
```
COMPOSER=composer-laravel7.json composer install --no-dev --ignore-platform-reqs
```
- Get all needed dependencies
```
composer update --ignore-platform-reqs
```
- For standalone plugin development, we need to setup docker and pull WordPress installation
```
COMPOSER=composer-standalone.json composer install --no-dev --ignore-platform-reqs
```

### For telescope
- Publish telescope assets
```
wp yivic-base artisan vendor:publish --tag=telescope-assets
```
For php 7.4 (wp74 should be the wp-cli running with php 7.4)
```
wp74 yivic-base artisan telescope:publish --force
wp74 yivic-base artisan --force vendor:publish --tag=telescope-assets # alternative way
```
- Publish telescope migrations
```
wp yivic-base artisan vendor:publish --tag=telescope-migrations
```

### For phpcs
- To suppress phpcs check for a single line, put this boefore that line and use the proper ruleset
```
// phpcs:ignore PHPCompatibility.Operators.NewOperators.t_coalesce_equalFound
```
