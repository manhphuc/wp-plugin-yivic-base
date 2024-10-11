<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport"
			content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

	<title>{{ yivic_base_wp_app_web_page_title() }}</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
	<style type="text/css">
		html { font-size: 10px; line-height: 1.5em; }
		body { margin: 0; padding: 0; font-family: "Plus Jakarta Sans", sans-serif; }
		a { text-decoration: none; }
		.site-toolbar { padding: 1.2rem 2rem; display: flex; font-size: 1.2rem; background-color: #1f2245; color: #e1e1e1; justify-content: space-between; align-items: center; }
		.site-toolbar a { color: #e1e1e1; }
		.site-toolbar a:hover { color: #e6b420; }
		.site-toolbar h2 { padding: 12px 4px; color: #e1e1e1; margin-bottom: 0; margin-top: 0; }
		.site-toolbar .guide-link { padding: 4px; }

		.site-body { font-size: 1.6rem; padding-top: 1em; padding-bottom: 1em; }
		.site-body h1, .site-body h2 { font-weight: 800; }
		.site-body h1, .site-body h2, .site-body h3, .site-body h4, .site-body h5, .site-body h6 { color: #353859; }
		.site-body h1 { font-size: 3rem; }
		.site-body a { color: #353859; }
		.site-body a:hover { color: #e6b420; }
		.container { width: 96vw; margin: 0 auto; max-width: 980px; }
		.message-content { line-height: 1.5em; }
		.message-content br { display: block; height: 0.8rem; content: ''; }
	</style>
</head>

<body>
	@include('yivic-base::layouts/blocks/site-toolbar')
	<main class="site-body">
		@yield('content')
	</main>
</body>
</html>
