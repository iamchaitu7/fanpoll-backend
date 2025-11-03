<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Common extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
    }


    public function check_login()
    {
        if(!is_loggedin_admin()) {

            $post_data = html_escape($this->input->post());
            if (!empty($post_data)) {
                $user_details = $this->common->check_admin($post_data);
                if (!empty($user_details)) {
                    $this->session->set_userdata('user_details_admin', $user_details);

                    $message = array(
                        "status" => 200,
                        "message" => "Successfully logged in..."
                    );
                } else {
                    $message = array(
                        "status" => 400,
                        "message" => "Incorrect Username/Email or Password..!"
                    );
                }
            } else {
                $message = array(
                    "status" => 400,
                    "message" => "Incorrect Username/Email or Password..!"
                );
            }

        } else {
            $message = array(
                'status'  => 400,
                'message' => 'Alredy logedin..'
            );
        }
        echo json_encode(@$message);
    }

    public function logout(){
        $this->session->unset_userdata('user_details_admin');
        redirect("zbt_admin");
    }
}
