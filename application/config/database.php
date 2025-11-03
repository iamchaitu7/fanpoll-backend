<?php
defined('BASEPATH') or exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

// For Railway MySQL Production
$db['default'] = array(
    'dsn'   => '',
    'hostname' => getenv('MYSQLHOST') ?: 'shortline.proxy.rlwy.net',
    'username' => getenv('MYSQLUSER') ?: 'root',
    'password' => getenv('MYSQLPASSWORD') ?: 'IHoVsWvFtPkuMedSPfpCMRRxQFldLXpK',
    'database' => getenv('MYSQLDATABASE') ?: 'railway',
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
    'save_queries' => TRUE,
    'port' => getenv('MYSQLPORT') ?: 45424  // Add port configuration
);
?>