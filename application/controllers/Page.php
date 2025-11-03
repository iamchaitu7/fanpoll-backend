<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');

class Page extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
    }


    public function delete_account()
    {
        $this->load->view('app/delete_account');
    }


    public function submit_delete_account()
    {
        $email = $this->input->post('email');
        if (!empty($email)) {
            $response = array(
                'status' => 200,
                'message' => 'Your delete account request accepted successfully, your account will be deleted automatically'
            );
        } else {
            $response = array(
                'status' => 400,
                'message' => 'Failed to send delete request. Please try again.'
            );
        }

        echo json_encode($response);
    }
}