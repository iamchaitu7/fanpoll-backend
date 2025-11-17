<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cors_hook {
    
    public function handle_cors() {
        $allowed_origins = [
            'https://fanpoll-flutter-6z77.vercel.app',
            'https://fanpoll-flutter.vercel.app', 
            'http://localhost:8000',
            'http://localhost:53589',
            'http://localhost:63775', // Add your current port
            'http://127.0.0.1:8000'
        ];
        
        $http_origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        $is_allowed_origin = false;
        
        // Check exact matches
        if (in_array($http_origin, $allowed_origins)) {
            $is_allowed_origin = true;
        }
        // Allow any localhost port dynamically
        elseif (preg_match('/http:\/\/localhost:\d+/', $http_origin)) {
            $is_allowed_origin = true;
        }
        // Allow any 127.0.0.1 port dynamically  
        elseif (preg_match('/http:\/\/127\.0\.0\.1:\d+/', $http_origin)) {
            $is_allowed_origin = true;
        }
        
        if ($is_allowed_origin) {
            header("Access-Control-Allow-Origin: $http_origin");
            header("Access-Control-Allow-Credentials: true");
        } else {
            // Use specific domain instead of * when credentials are needed
            header("Access-Control-Allow-Origin: https://fanpoll-flutter-6z77.vercel.app");
        }
        
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-API-Key");
        header("Access-Control-Expose-Headers: Authorization, X-API-Key");
        header("Access-Control-Max-Age: 86400"); // 24 hours
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("HTTP/1.1 200 OK");
            exit();
        }
    }
}