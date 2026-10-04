<?php

use Pdo\Mysql;

/*
|--------------------------------------------------------------------------
| Deployment Database Environment
|--------------------------------------------------------------------------
|
| This single file decides which database the application connects to:
|
|   local   Your own MySQL, using the DB_* values from your .env file.
|   hosted  The managed MySQL add-on configured under "credentials" below.
|
| config/database.php reads this file and swaps the "mysql" connection (and the
| default connection) accordingly, so nothing else needs to change when you
| move to a different host, port, database or provider.
|
| Three things you may want to change later:
|
|   1. $hostedEnvironments - APP_ENV values that count as "deployed".
|   2. $deploymentMarkers  - environment variables set by hosting platforms.
|   3. "credentials"       - the hosted MySQL connection details.
|
*/

$hostedEnvironments = ['production'];

$deploymentMarkers = ['VERCEL', 'RENDER', 'RAILWAY', 'FLY_APP_NAME', 'DYNO'];

/*
|--------------------------------------------------------------------------
| Local / Hosted Detection
|--------------------------------------------------------------------------
|
| Set DB_TARGET=local or DB_TARGET=hosted in your environment to override the
| automatic detection. Leave it unset and the rules below decide.
|
*/

$target = env('DB_TARGET');

$marked = count(array_filter($deploymentMarkers, fn ($marker) => (bool) env($marker))) > 0;

$deployed = $target === 'hosted'
    || ($target === null && (in_array((string) env('APP_ENV'), $hostedEnvironments, true) || $marked));

return [

    /*
    |----------------------------------------------------------------------
    | Resolved Environment
    |----------------------------------------------------------------------
    */

    'deployed' => $deployed,

    'target' => $deployed ? 'hosted' : 'local',

    /*
    |----------------------------------------------------------------------
    | Default Connection Name When Deployed
    |----------------------------------------------------------------------
    |
    | The connection name below receives the credentials from this file. Keep
    | it in sync with "driver" below.
    |
    */

    'connection' => 'mysql',

    /*
    |----------------------------------------------------------------------
    | Hosted MySQL Credentials
    |----------------------------------------------------------------------
    |
    | Every value below is overridable by an environment variable, so you can
    | keep this file committed and still manage the secrets from the Vercel
    | dashboard. Setting MYSQL_ADDON_URI overrides all of them at once.
    |
    */

    'credentials' => [
        'driver' => 'mysql',
        'url' => env('MYSQL_ADDON_URI'),
        'host' => env('MYSQL_ADDON_HOST', 'b2mc2djajez7bbhmhno4-mysql.services.clever-cloud.com'),
        'port' => env('MYSQL_ADDON_PORT', '3306'),
        'database' => env('MYSQL_ADDON_DB', 'b2mc2djajez7bbhmhno4'),
        'username' => env('MYSQL_ADDON_USER', 'ulvgbcilpd0crlwl'),
        'password' => env('MYSQL_ADDON_PASSWORD', 'GlUagzy0b0P4bETbYQ5c'),
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
    ],

];
