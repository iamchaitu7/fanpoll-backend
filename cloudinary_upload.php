<?php
// File: application/libraries/Cloudinary_service.php
class Cloudinary_service {
    private $cloud_name;
    private $upload_preset;
    
    public function __construct() {
        // Configure these in your config file or constants
        $this->cloud_name = 'dq9zl6oob';
        $this->upload_preset = 'uploads'; // Create this in Cloudinary dashboard
    }
    
    public function upload_image($file_path, $options = []) {
        $url = "https://api.cloudinary.com/v1_1/{$this->cloud_name}/image/upload";
        
        $post_data = [
            'file' => new CURLFile($file_path),
            'upload_preset' => $this->upload_preset,
            'timestamp' => time()
        ];
        
        // Add any additional options
        if (isset($options['folder'])) {
            $post_data['folder'] = $options['folder'];
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            return [
                'success' => true,
                'data' => json_decode($response, true)
            ];
        } else {
            log_message('error', 'Cloudinary upload failed: ' . $response);
            return [
                'success' => false,
                'error' => 'Upload failed with HTTP code: ' . $http_code
            ];
        }
    }
    
    public function delete_image($public_id) {
        // You'll need API key/secret for deletion
        $url = "https://api.cloudinary.com/v1_1/{$this->cloud_name}/image/destroy";
        
        $timestamp = time();
        $params = [
            'public_id' => $public_id,
            'timestamp' => $timestamp,
            'api_key' => $this->api_key
        ];
        
        // Generate signature for deletion (requires API secret)
        $signature = sha1(http_build_query($params) . $this->api_secret);
        $params['signature'] = $signature;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}