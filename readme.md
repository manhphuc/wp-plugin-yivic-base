# Yivic Base plugin
We understand the pains it brings to WordPress developers so we create this plugin to allow to use [Laravel framework](https://laravel.com/).

This plugin would bring all the features of Laravel framework to WordPress development: Container concepts, Service Providers, ORMs, Queue system, Routing system ... (everything that works with Laravel will work with WordPress with Yivic Base plugin).

So from now on, you can use Laravel concepts to work with WordPress developments consistently. This Yivic Base plugin would help WordPress developers to create easy-maintainable code exactly the same way Laravel does.

Imagine, you can do this for the iconic template file `index.php` (using Blade template syntax)
```html
@extends('layouts/main')

@section('content')
	<h1><?php echo 'WP App'; ?></h1>
	<p>{{ 'Welcome to WP App from Yivic Base' }}</p>
@endsection
```
and on `layouts/main.blade.php`
```html
<html>
<body>
	<main class="site-body">
		@yield('content')
	</main>
</body>
</html>
```

Interesting!? Let's get started

## Installation
```
composer require yivic/yivic-base
```
or you can find it on WordPress plugin hub

Docs are here:
1. [Basic Concepts](dev-docs/01-basic-concepts.md)
2. We log our blockers, solutions and crazy things in [Development Logs](dev-docs/02-development-logs.md)
3. [Development Guides](dev-docs/03-development-guides.md)

## License
The Yivic Base plugin is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits
- Author [Phuc Nguyen](mailto:manhphucofficial@yahoo.com)
- [WordPress Team](https://wordpress.org/) and [WordPress VIP](https://wpvip.com/)
- Laravel team [Laravel framework](https://laravel.com/)
- [PHPCS team](https://github.com/squizlabs/PHP_CodeSniffer)
- [Codeception](https://github.com/Codeception/Codeception)
- [PHPUnit](https://phpunit.de/)
