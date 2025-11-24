<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['auth_key'] = 'd14e5fe6050fda6693e8f658a80879f236e36da3173b4009d56b8100e3782646';
$config['application_name'] = 'Fan Poll World';
$config['author']           = 'ZooBit Infotech';
$config['author_link']      = 'https://zoobitinfotech.com/?utm_source=fan_poll_world&utm_medium=website&utm_campaign=fan_poll_world';

$config['img_extensions'] = array("jpeg", "jpg", "png", "svg", "webp");

$config['email_username'] = 'chandunaidu630@gmail.com';
$config['email_password'] = '*****';
$config['email_host'] = 'smtp.gmail.com';
$config['email_port'] = 465;

// CORS Configuration
$config['allowed_origins'] = [
    'https://fanpoll-flutter-6z77.vercel.app',
    'https://fanpoll-flutter.vercel.app',
    'http://localhost:8000',
    'http://localhost:53589',
    'http://localhost:63775', // Your current port
    'http://127.0.0.1:8000',
    'https://www.fanpollworld.com',
    'https://fanpollworld.com'
];

// Enable dynamic localhost port allowance for development
$config['cors_allow_dynamic_localhost'] = true;

// Remove old configuration to avoid conflicts
// $config['allowed_origins_removed'] = array();