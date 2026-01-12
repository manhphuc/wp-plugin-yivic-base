## Yivic Base Development Logs
We record our milestones here for people to get to know more.

### Earlier Stage
- We see WordPress development is a pain for people who need to do the maintenance job because WordPress doesn't require any development constrains.
- Then, we created a base plugin that we want it would be assist the development works robust and maintainable. We call it Yivic Base plugin.
- First, we want to use Yii2 framework. We included the Yii2 Framework but weeks later, we saw that few people were familiar with Yii2 so we decided to use Laravel.
- We decided to go with Laravel after seeing big communities in the ecosystem. We want to bring as many Laravel's features as possible to WordPress development.
- Because WordPress is a long-live ecosystem so we need to deal with legacy PHP. We decided to use Laravel 7 (for PHP 7.2.5 - 8.0) and Laravel 10 (for PHP 8.1+). We also want the plugin to be able to work as a Must Use plugin, a normal plugin and a dependency package so our scenarios are:
    - Works with PHP 7.2.5+
    - Works as the MU plugin, normal plugin and a composer package.
      With all of these vast requirements, all of our problems started.
    - Can use all possible features of Laravel.
    - Since 23 May 2024, we decided to use Laravel 8 only and only support PHP `^7.3|^8.0`

### Started to write the code
- First, we think using dependencies for Laravel, we may face the conflicts with other plugins that use the same namespace but differ in version. Therefore we try to include the dependencies to the plugin repo itself and use https://github.com/coenjacobs/mozart to replace the namespace.
    - We have to manually change the namespace everywhere to make everything works. That's a crazy task because the amount of files are huge.
- As we want to support PHP 7.2.5+, we decide to go with Laravel 7. But we soon found out that, Laravel 7 doesn't work well with PHP8.1 so we have to tweak the Laravel 7 to work with PHP 8.1+. That's another crazy task because of the huge amount of files.
- Then after several weeks, we saw that many Laravel package that didn't work well when we change the Laravel namespace (e.g. Telescope, Tinker) and we thought, we need to keep all the packages works so ... we decided to use the default namespace of Laravel. We revert all the changes for replacing namespace. Crazy thing!
- This time, we decided to use 2 Laravel versions, Laravel 7 (for PHP ^7.2.5 - ~8.0.0) and Laravel 10 (for PHP 8.1+), we created 2 composer files for that.
- After testing on several sites, we find it complicated to make Laravel 10 working with WordPress ecosystem. So since 23 May 2024, we decide to use 1 single version of Laravel 8 and PHP 8.0 should be used for development. That's ... awesome!
- To ensure the Git hook runs automatically on the server at the post-commit stage—executing phpcbf to fix PHP CodeSniffer (PHPCS) errors, running phpstan for verification, and staging modified files—contributors need to clone the repository and set up the `core.hooksPath` configuration. If `core.hooksPath` isn’t already configured, make sure it is set up by running:
```
git config core.hooksPath .githooks
```
### Gradually build the plugin
- When included Laravel to WordPress, we bumped into the first issues. Laravel used the filesystem to read the configs because it has the fixed base path but in WordPress, we would not know how the Yivic Base plugin works, so we cannot use a fixed base path and therefore, we can not use files to store the configs. The solution was creating a fake base path (mainly for generated files) and use array to store configs in memory (we tweaked the `config` instance of the app).
- Laravel uses the `Container` instance for the app(). We create a class called `WP_Application` on top of that and we use the `app()` helper function instead of app() from default Laravel. This will use the `WP_Application::$instance` for the app().
- We tweak the WP_Application to load base Service Providers of Laravel and allow other plugins to hook into the loading of main Service Providers (the `providers` value of `config.app`). We log the array config to each provider when we register the provider to avoid a huge array for all the configs.
- We approach DDD (Domain Driven Development) structure to enhance the readability but we combine it with the Laravel structure so you can call that Lara-domain Driven Development (LaraDD)
- We try to split the implementations to Jobs and Queries (CQRS - Command and Query Responsibility Segregation approach) to enhance the readability and reusability.
- We created wrappers for all Laravel Service Providers to ensure that they would work with WordPress as well.
- We created the `WpdbConnection` that inherited from `MysqlConnection` for the case we want to use WordPress `$wpdb` object to work with Laravel eloquent.
- We load routes dynamically using the filters from WordPress to allow plugins, themes can hook into the routes of the app()
- We created 2 new endpoints for the wp_app:
    - domain.com/**wp-app** for normal web requests that need to have UI rendered
    - domain.com/**wp-api** for serving requests for API consuming.
      both are configurable.
- Allow to use the artful Laravel `php artisan <command> <options>` with the usage of wp-cli `wp yivic-base artisan <command> <options>`
- Adding Telescope and Tinker to the wp_app to provides the wonderful debug tools from Laravel.
- We use the event plugin activation to fire an ajax request on the `wp-admin` to setup everything we need to use wp_app, from copying the assets, running the migrations, creating needed folders ...
- We found a way to make Yivic Base load only once even when there's a Yivic Base plugin activated and many other plugins, themes use Yivic Base as a composer package. We need to keep a very good compatibility when we update the plugin Yivic Base later.
- Ideally, we want the WordPress setup to have Yivic Base as a Must-Use plugin (mu plugin) then other plugins and themes use classes, tools from it.
- We use phpcs rules from WordPress VIP team and use the WordPress code style and naming convention (snake_eyes).

### Adding more Laravel features to the plugin
- Use Session flash messages to display messages on WordPress application.
- We want to have the Laravel queue to WordPress and use database connection as the queue connection.
- We can add a http endpoint then use the code for the queue and scheduler, we call it the web worker
```
Artisan::call('queue:work', [
	...
]);
```
and
```
Artisan::call('schedule:run', [
	...
]);
```
- To be able to have the schedule console working correctly, you need to define the constant `ARTISAN_BINARY` to the value 'wp yivic-base artisan'. Because, when running the schedule, Laravel execute the console command like
```
'/opt/homebrew/Cellar/php@8.0/8.0.30_1/bin/php' 'artisan' wp-app:hello > '/dev/null' 2>&1
```
therefore, we need to have the correct ARTISAN_BINARY value to the second section of the above console command. So we skip the scheduler for now.


to perform the queue execution with the timeout set to 60 seconds.
- Then we need to write a js script to have ajax request to that http endpoint to perform the queue execution when someone access the website.

### Notes
#### Migrations
- We need to put migrations rules to a src folders then user the command `vendor:publish` (remember to assign the assets tags for migrations rules) to publish migrations to fake base path. If we use the command to create rules, new rules will be created with current date therefore it would affect the migration rule time and cause the confusion.
- For Laravel 7, we need to specify the migration class name clearly, and we need to use CamelCase naming to match Laravel convention e.g. 'CreateActivityLogs' (not using `return new class extends Migration` like Laravel 8+)

#### Setup App
- To ensure all migrations rules are executed and assets are published, we need to set a flag in the option table, if the flag is not matching the current situation (exists or version not up-to-date), we need to redirect to the setup app page `wp-app/wp-admin/admin/setup-app?force_app_running_in_console=1&return_url=` to perform all the needed things for setup app.

### Blockers and solutions
- We tried to use Job to put to Laravel queue but Telescope cannot record the Job. After several days, we found out that, on normal WP request, we didn't use the `$kernel->terminate()`, and Telescope used the event `terminating` of app() to send all entries to the DB, therefore, Telescope didn't log the entries for Jobs. We need to apply the `$kernel->terminate()` to the shutdown action.
- Laravel 7 has many difference than Laravel 10 so we decided to switch to Laravel 8 and support PHP 7.3+
- Laravel Session cannot be saved correctly, therefore the Session ID is not persistent per request. It turns out that the WP sent headers before and it causes the Laravel headers cannot be sent correctly.
- Controller cannot inject the custom request class, we need to add alias of the custom request class to the request instance for the WP App to make the correct one to inject to the Controller.
- On multisite mode, if we enqueue a job, it may being handle incorrectly because the different site so we need to enqueue the job to it's site queue, the queue name should be `default_queue_for_site_<site_id>`