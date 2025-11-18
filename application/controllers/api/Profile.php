<?php
//Jai Shree Ram
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{

    private $tokenHandler;
    private $cloudinary_config;
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->tokenHandler = new TokenHandler();

        // Cloudinary configuration
        $this->cloudinary_config = [
            'cloud_name' => 'dq9zl6oob', // Replace with your Cloudinary cloud name
            'upload_preset' => 'uploads' // Replace with your upload preset
        ];

        $allowed_origins = config_item('allowed_origins');

        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: " . $origin);
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
            header("Access-Control-Allow-Credentials: true");
        }
    }

    public function output($data)
    {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
    }

    /**
     * Get profile picture URL with Cloudinary fallback
     */
    private function get_profile_picture_url($profile_picture)
    {
        // If no profile picture, return default
        if (empty($profile_picture) || $profile_picture === 'default-profile.jpg') {
            return "https://fanpoll-backend-production.up.railway.app/serve_image.php?file=/uploads/profile_pictures/default.png";
        }

        // Check if it's a Cloudinary public_id (starts with 'avatars/' or 'profile_images/')
        if (strpos($profile_picture, 'avatars/') === 0 || strpos($profile_picture, 'profile_images/') === 0) {
            // It's a Cloudinary public_id - generate optimized URL
            $cloud_name = $this->cloudinary_config['cloud_name'];
            return "https://res.cloudinary.com/{$cloud_name}/image/upload/w_150,h_150,c_fill,q_auto,f_auto/{$profile_picture}";
        } else {
            // It's a local file - use existing local URL
            return "https://fanpoll-backend-production.up.railway.app/serve_image.php?file=/uploads/profile_pictures/default.png";
        }
    }

    public function index()
    {
        $headers_of_page = $this->input->request_headers();
        if (empty($headers_of_page['Authorization'])) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $token = str_replace('Bearer ', '', $headers_of_page['Authorization']);
        try {
            $decoded = $this->tokenHandler->decodeToken($token);
            if (isset($decoded->user_id)) {
                $user_id = $decoded->user_id;
                $where_data = array(
                    'id' => $user_id,
                    'status' => 'active'
                );
                $user_data = $this->common->getdatabytable('users', $where_data);
                if ($user_data) {
                    $response_data = array();
                    $response_data['id'] = $user_data->id;
                    $response_data['full_name'] = $user_data->full_name;
                    $response_data['email'] = $user_data->email;
                    $response_data['bio'] = $user_data->bio;
                    
                    // Use Cloudinary URL with fallback to default
                    $response_data['profile_picture'] = $this->get_profile_picture_url($user_data->profile_picture);
                    
                    $member_since = new DateTime($user_data->created_at);
                    $member_since_string = $member_since->format('F j, Y');
                    $response_data['member_since'] = $member_since_string;
                    $response_data['followers_count'] = $user_data->followers_count;
                    $response_data['following_count'] = $user_data->following_count;
                    $response_data['updated_at'] = $user_data->updated_at ? date('Y-m-d H:i:s', strtotime($user_data->updated_at)) : null;

                    $this->output(['status' => 200, 'data' => $response_data]);
                } else {
                    $this->output(['status' => 404, 'message' => 'User not found']);
                }
            } else {
                $this->output(['status' => 401, 'message' => 'Invalid token']);
            }
        } catch (Exception $e) {
            $this->output(['status' => 401, 'message' => 'Invalid token']);
        }
    }

    public function edit(){
        $headers_of_page = $this->input->request_headers();
        if (empty($headers_of_page['Authorization'])) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $token = str_replace('Bearer ', '', $headers_of_page['Authorization']);
        try {
            $decoded = $this->tokenHandler->decodeToken($token);
            if (isset($decoded->user_id)) {
                $user_id = $decoded->user_id;
                $where_data = array(
                    'id' => $user_id,
                    'status' => 'active'
                );
                $user_data = $this->common->getdatabytable('users', $where_data);
                if ($user_data) {
                    $post_data = $this->input->post(null, true);
                    $update_data = array();
                    if(!empty($post_data['full_name'])){
                        $update_data['full_name'] = $post_data['full_name'];
                    }

                    if(!empty($post_data['bio'])){
                        $update_data['bio'] = $post_data['bio'];
                    }

                    if (!empty($_FILES['profile_picture']['name'])) {
                        $file = $_FILES['profile_picture'];

                        // Validate file
                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            $this->output(['status' => 400, 'message' => 'Upload error occurred.']);
                            return;
                        }

                        // Check file size (5MB = 5242880 bytes)
                        if ($file['size'] > 5242880) {
                            $this->output(['status' => 400, 'message' => 'File too large. Maximum size is 5MB.']);
                            return;
                        }

                        // Validate extension
                        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                        if (!in_array($file_ext, $allowed_extensions)) {
                            $this->output(['status' => 400, 'message' => 'Invalid file type.']);
                            return;
                        }

                        // Validate that it's actually an image
                        $image_info = getimagesize($file['tmp_name']);
                        if (!$image_info) {
                            $this->output(['status' => 400, 'message' => 'File is not a valid image.']);
                            return;
                        }

                        // Upload to Cloudinary
                        $upload_result = $this->upload_to_cloudinary($file['tmp_name'], $file_ext, 'avatars');
                        
                        if ($upload_result['success']) {
                            $update_data['profile_picture'] = $upload_result['public_id'];
                        } else {
                            $this->output(['status' => 500, 'message' => 'Failed to upload profile picture to cloud storage.']);
                            return;
                        }
                    }

                    if(!empty($update_data)){
                        $update_data['updated_at'] = date('Y-m-d H:i:s');
                        $this->common->update('users', $update_data, ['id' => $user_id]);
                        $this->output(['status' => 200, 'message' => 'Profile updated successfully']);
                    } else {
                        $this->output(['status' => 400, 'message' => 'No data to update']);
                        return;
                    }
                } else {
                    $this->output(['status' => 404, 'message' => 'User not found']);
                }
            } else {
                $this->output(['status' => 401, 'message' => 'Invalid token']);
            }
        } catch (Exception $e) {
            $this->output(['status' => 401, 'message' => 'Invalid token']);
        }
    }

    /**
     * Upload image to Cloudinary
     */
    private function upload_to_cloudinary($file_path, $file_extension, $folder = 'avatars')
    {
        $cloud_name = $this->cloudinary_config['cloud_name'];
        $upload_preset = $this->cloudinary_config['upload_preset'];
        
        $url = "https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload";
        
        // Generate unique filename
        $unique_filename = uniqid() . '.' . $file_extension;
        
        $post_data = [
            'file' => new CURLFile($file_path),
            'upload_preset' => $upload_preset,
            'public_id' => $folder . '/' . pathinfo($unique_filename, PATHINFO_FILENAME),
            'folder' => $folder
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'image_url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } else {
            log_message('error', "Cloudinary upload failed - HTTP: $http_code, Error: $curl_error, Response: $response");
            return [
                'success' => false,
                'error' => "Upload failed with HTTP code: $http_code"
            ];
        }
    }

    /**
     * Delete old Cloudinary image (optional - for cleanup)
     */
    private function delete_cloudinary_image($public_id)
    {
        if (empty($public_id)) {
            return false;
        }
        
        $cloud_name = $this->cloudinary_config['cloud_name'];
        $api_key = 'your_api_key'; // You'll need API key/secret for deletion
        $api_secret = 'your_api_secret';
        
        $timestamp = time();
        $params = [
            'public_id' => $public_id,
            'timestamp' => $timestamp,
            'api_key' => $api_key
        ];
        
        // Generate signature for deletion
        $signature = sha1(http_build_query($params) . $api_secret);
        $params['signature'] = $signature;
        
        $url = "https://api.cloudinary.com/v1_1/{$cloud_name}/image/destroy";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return isset($result['result']) && $result['result'] === 'ok';
    }

    public function update_fcm_token(){
        $headers_of_page = $this->input->request_headers();
        if (empty($headers_of_page['Authorization'])) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $token = str_replace('Bearer ', '', $headers_of_page['Authorization']);
        try {
            $decoded = $this->tokenHandler->decodeToken($token);
            if (isset($decoded->user_id)) {
                $user_id = $decoded->user_id;
                $fcm_token = $this->input->post('fcm_token', true);
                if (!empty($fcm_token)) {
                    $update_data = array(
                        'fcm_device_token'  => $fcm_token,
                        'updated_at'        => date('Y-m-d H:i:s')
                    );
                    $this->common->update('users', $update_data, ['id' => $user_id]);
                    $this->output(['status' => 200, 'message' => 'FCM device token updated successfully']);
                } else {
                    $this->output(['status' => 400, 'message' => 'FCM device token is required']);
                }
            } else {
                $this->output(['status' => 401, 'message' => 'Invalid token']);
            }
        } catch (Exception $e) {
            $this->output(['status' => 401, 'message' => 'Invalid token']);
        }
    }
}