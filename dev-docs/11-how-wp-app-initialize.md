# Initialization of the WP App

The `Yivic_Base_Helper` class is designed to initialize and configure the Yivic Base framework within a WordPress environment. It provides methods to manage both CLI and web mode operations, ensuring the correct setup of WordPress application hooks, redirects, and actions, while also offering various static helper methods to support the Yivic Base plugin.

The main goal of the initialization process is to prepare the necessary folder structure and manage database setup or migration tasks for the plugin. The `initialize` method is the entry point for this process, triggered early in the `yivic-base-init.php` file. It checks whether the WordPress content directory is loaded and then performs the appropriate setup actions based on whether the environment is in CLI or web mode.

This process will create a Laravel application `app()` (a [DI container](https://code.tutsplus.com/tutorials/digging-in-to-laravels-ioc-container--cms-22167)) containing everything needed.

## 1. Command-Line Interface (CLI) Mode Setup

In CLI mode, the setup process is managed through two specific command-line instructions:

```bash
wp yivic-base prepare       # Set up the correct folder structure
wp yivic-base wp-app:setup  # Run migrations and copy necessary assets
```

- `yivic-base prepare`: Prepares and sets up the required folder structure for the plugin.
- `yivic-base wp-app:setup`: Runs the necessary migrations and copies the required assets to their appropriate locations.

If the environment is running in CLI mode, the `Yivic_Base_Helper::is_console_mode()` method confirms it. The CLI initialization action is then registered via `Yivic_Base_Helper::register_cli_init_action()`, binding it to the `cli_init` hook with `Yivic_Base_Helper::wp_cli_init()` as the callback.

## 2. Web Mode Setup

In web mode, the plugin performs the following tasks:

- **Prepare the necessary folder structure**: The Yivic Base plugin requires a flexible folder structure, including the cache and storage directories located within the `wp-content/uploads/wp-app/` folder.
- **Redirect to the general setup page**: The plugin redirects the request to the general setup page when specific conditions are met, as this phase requires special permissions. After completing the setup, the plugin updates the `yivic_base_version` option in the database with the current plugin version. If the database is not updated with the current plugin version, the setup is considered to have failed.
- **Fallback to the Admin setup page**: If the setup cannot be completed on the general setup page, the plugin redirects the request to the Admin setup page. This page requires Admin access and provides more detailed error messages.

Steps to be executed in sequence:

1. **Perform WP App Check**:
    - If the environment is not in CLI mode, the `Yivic_Base_Helper::perform_wp_app_check()` method is used to verify that the necessary extensions and WordPress application setup steps are in place. If the check fails, the process exits early.

2. **Register Redirect Action**:
    - The setup process begins by registering a redirect action using `Yivic_Base_Helper::register_setup_app_redirect()`. This method ensures that users are redirected to the appropriate setup page.
    - This action is added to the `YIVIC_BASE_SETUP_HOOK_NAME` hook, with `Yivic_Base_Helper::maybe_redirect_to_setup_app()` as the callback function. The priority of this action is set to `-200`.

3. **Register WP App Setup Hooks**:
    - Next, the necessary hooks for the proper setup of the WP App are registered via `Yivic_Base_Helper::register_wp_app_setup_hooks()`.
    - This method ensures that the WP App instance is loaded during the setup process by adding an action to the `YIVIC_BASE_SETUP_HOOK_NAME` hook, using `\Yivic_Base\App\WP\WP_Application::load_instance()` as the callback function. The priority of this action is set to `-100`.

4. **Signal WP App Fully Loaded**:
    - Finally, the method `Yivic_Base_Helper::register_wp_app_loaded_action()` is called to signal that the WP App has fully loaded after all necessary setup actions are completed.
    - An action is added to the `\Yivic_Base\App\Support\App_Const::ACTION_WP_APP_LOADED` hook, with `Yivic_Base_Helper::handle_wp_app_loaded_action()` as the callback function. This function initializes the Yivic Base WordPress plugin by calling `\Yivic_Base\App\WP\Yivic_Base_WP_Plugin::init_with_wp_app()` with the plugin slug, directory, and URL parameters.

## Summary

The `Yivic_Base_Helper` class offers essential methods to initialize and configure the Yivic Base framework within a WordPress environment. It handles the setup for both CLI and web modes, ensuring proper configuration of WordPress application hooks, redirects, and actions. Additionally, it includes static helper methods that support the functionality of the Yivic Base plugin.