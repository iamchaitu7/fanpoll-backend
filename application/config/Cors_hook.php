<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cors_hook {
    
    public function handle_cors() {
        $allowed_origins = [
            'https://fanpoll-flutter-6z77.vercel.app',
            'https://fanpoll-flutter.vercel.app', 
            'http://localhost:8000',
            'http://localhost:53589',
            'http://127.0.0.1:8000'
        ];
        
        $http_origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Allow requests from any of the specified origins
        if (in_array($http_origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: $http_origin");
        } else {
            // Or allow all origins (less secure)
            header("Access-Control-Allow-Origin: *");
        }
        
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400"); // 24 hours
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("HTTP/1.1 200 OK");
            exit();
        }
    }
}