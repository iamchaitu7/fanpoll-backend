<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
*/

$active_group = 'default';
$query_builder = TRUE;

// Environment-based configuration
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    // Local development - keep your existing MySQL
    $db['default'] = array(
        'dsn'   => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'fan_poll_world',
        'dbdriver' => 'mysqli',
        'dbprefix' => '',
        'pconnect' => FALSE,
        'db_debug' => (ENVIRONMENT !== 'production'),
        'cache_on' => FALSE,
        'cachedir' => '',
        'char_set' => 'utf8mb4',
        'dbcollat' => 'utf8mb4_unicode_ci',
        'swap_pre' => '',
        'encrypt' => FALSE,
        'compress' => FALSE,
        'failover' => array(),
        'save_queries' => TRUE
    );
} else {
    // Production - Supabase PostgreSQL
    $db['default'] = array(
        'dsn'   => '',
        'hostname' => getenv('DB_HOST') ?: 'db.nksxxktaaiifforuvuol.supabase.co',
        'username' => getenv('DB_USER') ?: 'postgres',
        'password' => getenv('DB_PASS') ?: 'Naser@db1994',
        'database' => getenv('DB_NAME') ?: 'postgres',
        'dbdriver' => 'postgre', // CHANGED from mysqli to postgre
        'dbprefix' => '',
        'pconnect' => FALSE,
        'db_debug' => (ENVIRONMENT !== 'production'),
        'cache_on' => FALSE,
        'cachedir' => '',
        'char_set' => 'utf8',
        'dbcollat' => 'utf8_general_ci',
        'swap_pre' => '',
        'encrypt' => FALSE,
        'compress' => FALSE,
        'failover' => array(),
        'save_queries' => TRUE
    );
}
?>