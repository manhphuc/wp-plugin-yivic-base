# Working with Unit Tests

We use PHPUnit to perform the Unit Tests. We still use PHPUnit 9 because of several tools that only support PHPUnit 9 and this time like Mockery...

## Run Unit Test
- Run PHPUnit with Code coverage report (HTML)
```
php -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --verbose --coverage-html ./tests/_output/coverage-full
```
or using docker (without PHP8.1 locally)
```
docker run --rm --interactive --tty -e XDEBUG_MODE=coverage -v $PWD:/app manhphucofficial/php81_cli php  -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --coverage-html ./tests/_output
```

- Run PHPUnit on one single file, with method pattern to test and perform the coverage check for the file to check
```
php  -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --no-configuration --colors --bootstrap=tests/bootstrap-unit.php --verbose --coverage-html ./tests/_output <path/to/test/file> --filter='test_methods(.*)' --whitelist=<path/to/file-to-be-covered>
```

e.g
```
php  -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --no-configuration --colors --bootstrap=tests/bootstrap-unit.php --verbose --coverage-html ./tests/_output ./tests/Unit/Foundation/WP/WP_Theme_Test.php --filter='test_register_this_to_wp_app(.*)' --whitelist=src/Foundation/WP/WP_Theme.php
```
or use docker PHP
```
docker run --rm --interactive --tty -e XDEBUG_MODE=coverage -v $PWD:/app manhphucofficial/php81_cli php  -d xdebug.max_nesting_level=512 -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --no-configuration --colors --bootstrap=tests/bootstrap-unit.php --verbose --coverage-html ./tests/_output ./tests/Unit/Foundation/WP/WP_Theme_Test.php --filter='test_register_this_to_wp_app(.*)' --whitelist=src/Foundation/WP/WP_Theme.php
```

## Writing Unit Test

### Run a test in a separate process
We may want to specify the test on a separate process to avoid the conflicts with other tests
```
	/**
	 * @runInSeparateProcess
	 */
```

### Mock user functions (global functions)
We use WP_Mock to do this (WP_Mock is based on Mockery so some of the methods from Mockery can be used)

For example, we have the function `sanitize_text_field($text)` and in the testing method, it was called 2 times, and we need to return the correct results for each time it called based on the argument `$text` we can do like this

```
WP_Mock::userFunction( 'sanitize_text_field' )
	->times( 2 )
	->withAnyArgs()
	->andReturnUsing(
		function ( $text ) {
			return (string) $text === 'test-01' ? 'result-01' : 'result-02';
		}
	);
```