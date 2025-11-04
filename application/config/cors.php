<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// CORS Configuration
$config['cors_allowed_origins'] = [
    'https://fanpoll-flutter-6z77.vercel.app',
    'https://fanpoll-flutter.vercel.app',
    'http://localhost:8000', // For local development
];

$config['cors_allowed_methods'] = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
$config['cors_allowed_headers'] = ['Content-Type', 'Authorization', 'X-Requested-With'];
$config['cors_allow_credentials'] = true;