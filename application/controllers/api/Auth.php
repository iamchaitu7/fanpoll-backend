<?php
//Jai Shree Ram
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
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

    public function create_account()
    {
        $post_data = $this->input->post(null, true);
        if (empty($post_data['email']) || empty($post_data['password'])) {
            $this->output(['status' => 400, 'message' => 'Email and password are required']);
            return;
        }

        if (empty($post_data['full_name'])) {
            $this->output(['status' => 400, 'message' => 'Full name is required']);
            return;
        }

        $where_data = array(
            'email' => $post_data['email']
        );

        $users = $this->common->getdatabytableall('users', $where_data);
        if (!empty($users)) {
            $this->output(['status' => 400, 'message' => 'Email already exists']);
            return;
        }

        $payload = array(
            'full_name'         => $post_data['full_name'],
            'email'             => $post_data['email'],
            'password'          => md5($post_data['password']),
            'profile_picture'   => 'default.png',
            'auth_method'       => 'email',
            'status'            => 'active',
        );

        $user_id = $this->common->insert($payload, 'users');
        if ($user_id) {
            $jwt_data =array(
                'user_id'   => $user_id,
                'email'     => $post_data['email'],
            );
            $token = $this->tokenHandler->generateToken($jwt_data);
            $this->output(['status' => 200, 'message' => 'Account created successfully', 'token' => $token]);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to create account']);
        }
    }

    public function login_with_email(){
        $post_data = $this->input->post(null, true);
        if (empty($post_data['email']) || empty($post_data['password'])) {
            $this->output(['status' => 400, 'message' => 'Email and password are required']);
            return;
        }

        $where_data = array(
            'email' => $post_data['email'],
            'password' => md5($post_data['password']),
            'status' => 'active'
        );

        $user = $this->common->getdatabytable('users', $where_data);
        if (!$user) {
            $this->output(['status' => 401, 'message' => 'Invalid email or password']);
            return;
        }

        $jwt_data = array(
            'user_id'   => $user->id,
            'email'     => $user->email,
        );
        $token = $this->tokenHandler->generateToken($jwt_data);
        $this->output(['status' => 200, 'message' => 'Login successful', 'token' => $token]);
    }

    public function login_with_oauth(){
        $post_data = $this->input->post(null, true);
        if (empty($post_data['email']) || empty($post_data['auth_method']) || empty($post_data['oauth_id'])) {
            $this->output(['status' => 400, 'message' => 'Email, auth method and OAuth ID are required']);
            return;
        }

        if (empty($post_data['full_name'])) {
            $this->output(['status' => 400, 'message' => 'Full name is required']);
            return;
        }

        if(!in_array($post_data['auth_method'], ['google', 'apple'])) {
            $this->output(['status' => 400, 'message' => 'Invalid auth method']);
            return;
        }

        $where_data = array(
            'email' => $post_data['email'],
        );

        $user = $this->common->getdatabytable('users', $where_data);

        if(!empty($user)){
            // User exists
            $jwt_data = array(
                'user_id'   => $user->id,
                'email'     => $user->email,
            );
            $token = $this->tokenHandler->generateToken($jwt_data);
            $this->output(['status' => 200, 'message' => 'Login successful', 'token' => $token]);
        } else {
            //new account creation
            $payload = array(
                'full_name'         => $post_data['full_name'],
                'email'             => $post_data['email'],
                'profile_picture'   => 'default.png',
                'auth_method'       => $post_data['auth_method'],
                'status'            => 'active',
            );

            if($post_data['auth_method'] == 'google') {
                $payload['google_id'] = $post_data['oauth_id'];
            } else if($post_data['auth_method'] == 'apple') {
                $payload['apple_id'] = $post_data['oauth_id'];
            }

            $user_id = $this->common->insert($payload, 'users');
            if ($user_id) {
                $jwt_data = array(
                    'user_id'   => $user_id,
                    'email'     => $post_data['email'],
                );
                $token = $this->tokenHandler->generateToken($jwt_data);
                $this->output(['status' => 200, 'message' => 'Account created successfully', 'token' => $token]);
            } else {
                $this->output(['status' => 500, 'message' => 'Failed to create account']);
            }
        }
    }


    public function send_forgot_otp(){
        $post_data = $this->input->post(null, true);
        if (empty($post_data['email'])) {
            $this->output(['status' => 400, 'message' => 'Email is required']);
            return;
        }

        $where_data = array(
            'email' => $post_data['email'],
            'status' => 'active'
        );

        $user = $this->common->getdatabytable('users', $where_data);
        if (!$user) {
            $this->output(['status' => 404, 'message' => 'User not found']);
            return;
        }

        $otp = rand(100000, 999999);
        $otp_data = array(
            "otp"               => $otp,
            "email"             => $post_data['email'],
            "created_time"      => date('Y-m-d H:i:s'),
            "ip"                => $this->input->ip_address(),
            "status"            => 0
        );

        $msg = 'Your Fan Poll World OTP is ' . $otp . '. Do not share with anyone.';
        $subject = 'Fan Poll World Email verification code: ' . $otp;
        $email_sent = $this->send_otp_email($post_data['email'], $subject, $msg);
        if (!$email_sent) {
            $this->output(['status' => 500, 'message' => 'Failed to send OTP email']);
            return;
        }

        $insert = $this->common->save_email_otp_details($otp_data);
        if ($insert) {
            $this->output(['status' => 200, 'message' => 'OTP sent successfully']);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to save OTP details']);
        }
    }


    private function send_otp_email($email, $subject, $message)
    {
        $this->load->library('email');

        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => config_item('email_host'),
            'smtp_user' => config_item('email_username'),
            'smtp_pass' => config_item('email_password'),
            'smtp_port' => config_item('email_port'),
            'smtp_crypto' => 'ssl',
            'crlf' => "\r\n",
            'newline' => "\r\n",
            'mailtype' => 'html'
        );

        $this->email->initialize($config);
        $this->email->from(config_item('email_username'), 'Fan Poll World');
        $this->email->to($email);
        $this->email->subject($subject);
        $this->email->message($message);
        if ($this->email->send()) {
            return true;
        } else {
            log_message('error', 'Email sending failed: ' . $this->email->print_debugger());
            return false;
        }
    }

    public function set_new_password(){
        $post_data = $this->input->post(null, true);
        if (empty($post_data['email']) || empty($post_data['otp']) || empty($post_data['new_password'])) {
            $this->output(['status' => 400, 'message' => 'Email, OTP and new password are required']);
            return;
        }

        $date = date('Y-m-d H:i:s');
        $otp = $post_data['otp'];
        $email = $post_data['email'];
        $otp_details = $this->common->getValidatedEmail($otp, $email, $date);
        if (empty($otp_details)){
            $this->output(['status' => 400, 'message' => 'Invalid or expired OTP']);
            return;
        }

        $where = array("id" => $otp_details->id);
        $data = array(
            "status" => 1
        );
        $this->common->update('email_otp_login', $data, $where);

        $user_data = array(
            'password' => md5($post_data['new_password'])
        );
        $where_data = array(
            'email' => $post_data['email']
        );
        $update = $this->common->update('users', $user_data, $where_data);
        if ($update) {
            $this->output(['status' => 200, 'message' => 'Password updated successfully']);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to update password']);
        }
    }
}
