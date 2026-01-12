## Base concepts
- This plugin will create a laravel application `app()` (a DI container https://code.tutsplus.com/tutorials/digging-in-to-laravels-ioc-container--cms-22167) contains everything we need.
- Each plugin or theme will act as a Service Provider https://laravel.com/docs/7.x/providers
- All Laravel Base Providers are loaded in WP_Application (similar to Applications)
- Main Service Providers (Configured Service Providers) should be loaded when Kernel do the bootstrapping all bootstrappers
- We don't load the configuration from the file so we skip the LoadConfiguration bootstrapper, and LoadEnvironmentVariables should be skipped as well as we don't need WP_Application to load environment variables from .env files
- On each Service Provider, we should put the configs for that Service Provider to the 'config' instance of the application to avoid a big array. Each should come with a filter to allow other plugins to tweak the configs.
- WP Application bootstrapping happens at the action `after_setup_theme`, to have the theme `functions.php` loaded as well. The theme functions.php is loaded right before the action `after_setup_theme` happens.
- `app()` (helper function for getting `WP_Application::$instance`) needs to skip the WordPress template render and skip the main query when the URL is working on **wp_app mode** (domain.com/wp-app)
- **app()** should skip the WordPress rest api as well on **wp_api mode** (domain.com/wp-api)

## How Yivic Base works
- Yivic Base is loaded as a MU plugin (this should be the choice), normal plugin or a dependency of plugins or themes.
- At the first time the site receive the web request, the plugin needs to:
    - Perform the folder prepare (the Yivic Base plugin needs the Laravel folder structure)
    - Redirect the request to the general setup page (with certain condition because the setup phase needs special permission).
    - If the setup cannot be done on general setup page, it would redirect the request to the Admin setup page (which requires the Admin access and provide better error messages)
- If the Admin have the WP CLI access, we need to perform the following command:
  ```
  wp yivic-base prepare # to setup the correct folder structure
  wp yivic-base wp-app:setup # to run the migrations, copy needed assets
  ```
- When Yivic Base plugin loaded, the WP_Application instance would be initialized and the Yivic_Base_WP_Plugin would be initialized next to work as the service provider for WP_Application. At Yivic_Base_WP_Plugin, we created several hooks for WP App based on the WP Hooks:
    1. The const `YIVIC_BASE_SETUP_HOOK_NAME` defines the moment when we set up the WP App.
    2. `yivic_base_wp_app_loaded` is the event when the WP App is loaded, we should use this event to init WP Plugins, WP Themes.
    3. The `manipulate_hooks` method of the WP_Plugin, WP_Theme would happen at this stage so hooks registered here can interfere
    4. `yivic_base_wp_app_registered` is the action happens when the WP App and all service providers registered. We use this event to register WP Plugins, WP Themes to the WP Application
    5. `yivic_base_wp_app_booted` is the action happens when the WP App and all service providers are booted
- Here are stages of the WP App via a request:
    1. `yivic_base_wp_app_bootstrap` is the action for the first event for putting the `sapp()` to the business (happens at `after_theme_setup`). At this stage, we add a handler to bootstrap the `app()`. We init the Kernel services (for Console and Http) and the Error Handler service and bind them to the Service Container (`WP_Application::$instance`).
    2. `yivic_base_wp_app_init` is the equivalent instance of the WP `init` action. We use another name to know that, this is for `app()`. At this stage, the `app()` should have all Service Providers registered and booted and of course, all available things from the action `init` of WordPress as well.
    3. `yivic_base_wp_app_complete_execution` is the action happens to complete the request handing. (We usually terminate the `app()` here)


Yivic Base plugin will split WordPress into 3 modes:
- Normal WordPress workflow
- WP App mode: use Laravel to handle the request and response with Laravel
- WP Api mode: same with WP App but for API only, no HTML rendering

### Normal WordPress workflow
1. All behaviors of WordPress must be kept
2. At `yivic_base_wp_app_init` action, we need to do the following:
1. Because we don't let the Laravel kernel to handle the request and use the Laravel response as the main response, but we need to have the `request` and `response` instance for some reason (especially for start the session via the middleware `StartSesssion`). We capture the current request and let it go through all needed middleware to have several Laravel features ready.
2. We need to synchronize the WP logged user to Laravel session (`Auth::user()` should have data).
3. We interfere the `template_include` filter skip the usage of WP `locate_template` to use Blade template to compile and render the HTML. Therefore, we can use Blade template syntax on the WP template file.
4. At `yivic_base_wp_app_complete_execution` action, we need to call the kernel's `terminate()` method for the `app()` to have needed events to trigger several actions (e.g. logging or Telescope logging, Telescope send the logging to database via the terminate event)

### WP App workflow
1. `app()` should be registered, booted and bootstrapped at the hook `yivic_base_wp_app_init`
2. We specify the WP App mode by the request uri prefix e.g. `<domain>/wp-app/abc`.
3. At the action `wp_loaded` we skip the WP handling for the request and started to use the Laravel Http kernel to handle the request and send Laravel headers and response. As we use the `wp_loaded` to switch to WP App mode (https://wp-kama.com/hooks/actions-order), so no WP loop working and the WP main query would not work either. The reason we use `wp_loaded` is to wait for WP widgets, navigations
4. The action `shutdown` would be invoked as WP register the callback to execute that hook via `register_shutdown_function` so we don't need to explicitly call that action
5. We use this action `App_Const::ACTION_WP_APP_REGISTER_ROUTES` to register WP App mode routes. All WP App mode routes are prefixed with `wp-app::`.


### WP Api workflow
1. Same workflow of WP App
2. Instead of using `wp_loaded` action, we use the very late handler on the action `init` (for other plugins to complete execution as `init` is the main action plugins use to start) and we skip several middleware as we don't want to use Cookie, Session as we want it stateless (we should keep `SubstituteBindings` middleware)
3. We use this action `App_Const::ACTION_WP_API_REGISTER_ROUTES` to register WP Api mode routes. All WP Api mode routes are prefixed with `wp-api::`.