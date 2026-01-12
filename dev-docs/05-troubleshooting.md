## Yivic Base troubleshooting
- We may face some errors/issues when having Yivic Base plugin working on your application.

1. Symfony Console issue
- When using Laravel 10 or any package that requires `symfony/console` >= 6.0, the declaration `Symfony\Component\Console\Application::run` would be
```
Symfony\Component\Console\Application::run(?Symfony\Component\Console\Input\InputInterface $input = null, ?Symfony\Component\Console\Output\OutputInterface $output = null): int
```
while in older version < 6.0.0, it has not return type
```
Symfony\Component\Console\Application::run(?Symfony\Component\Console\Input\InputInterface $input = null, ?Symfony\Component\Console\Output\OutputInterface $output = null)
```
therefore, errors happens

- Affected classes/methods:
    - `Symfony\Component\Console\Application::run()`
    - `Symfony\Component\Console\Style\SymfonyStyle::writeln()`


- **Solution**: try to have `symfony/console` < 6.0.0 or >= 6.0.0 for all packages that requires `symfony/console`

1. PSR Logger interface issue
- Laravel 10+ requires `psr/log` 3.0.0+ (https://github.com/php-fig/log/blob/3.0.0/src/LoggerInterface.php#L30) and the declaration is not compatible with 2.0- (https://github.com/php-fig/log/blob/2.0.0/src/LoggerInterface.php#L30) and it caused errors on PHP 8.1+ when using Laravel 10+ with other packages