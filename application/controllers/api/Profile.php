<?php
//Jai Shree Ram
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{

    private $tokenHandler;
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->tokenHandler = new TokenHandler();

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
                    $response_data['profile_picture'] = base_url('uploads/profile_pictures/' . $user_data->profile_picture);
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
                        $upload_path = './uploads/profile_pictures/';
                        $file = $_FILES['profile_picture'];

                        // Validate file
                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            $this->output(['status' => 400, 'message' => 'Upload error occurred.']);
                            return;
                        }

                        // Check file size (2MB = 2097152 bytes)
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

                        // Generate unique filename
                        $new_filename = uniqid() . '.' . $file_ext;
                        $destination = $upload_path . $new_filename;

                        // Create directory if it doesn't exist
                        if (!is_dir($upload_path)) {
                            mkdir($upload_path, 0755, true);
                        }

                        // Move uploaded file
                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $update_data['profile_picture'] = $new_filename;
                        } else {
                            $this->output(['status' => 400, 'message' => 'Failed to save uploaded file.']);
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
