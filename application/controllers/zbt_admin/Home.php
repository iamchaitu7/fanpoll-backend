<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_model', 'admin');
        $this->load->model('Common_model', 'common');
    }

    public function index()
    {
        if (is_loggedin_admin()) {
            
            $header_data['title'] = 'Poll Dashboard || ' . config_item('application_name');
            $footer_data['apex_chart'] = true;

            // Get current statistics
            $data['total_users'] = $this->admin->get_total_users();
            $data['total_polls'] = $this->admin->get_total_polls();
            $data['total_votes'] = $this->admin->get_total_votes();
            $data['total_comments'] = $this->admin->get_total_comments();
            $data['active_polls'] = $this->admin->get_active_polls_count();

            // Get growth percentages
            $data['users_growth'] = $this->admin->get_users_growth();
            $data['polls_growth'] = $this->admin->get_polls_growth();
            $data['votes_growth'] = $this->admin->get_votes_growth();
            $data['active_polls_change'] = $this->admin->get_active_polls_change();
            $data['active_polls_trend'] = $data['active_polls_change'] >= 0 ? 'success' : 'danger';

            // Get chart data for the last 12 months
            $chart_data = $this->prepare_chart_data();
            $data = array_merge($data, $chart_data);

            // Get user activity data for donut chart
            $data['active_users_count'] = $this->admin->get_active_users_count();
            $data['inactive_users_count'] = $this->admin->get_inactive_users_count();
            $data['new_users_count'] = $this->admin->get_new_users_count();

            // Get recent activity data
            $data['recent_polls'] = $this->admin->get_recent_polls(5);
            $data['top_users'] = $this->admin->get_top_users(5);

            // Load views
            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/home/index', $data);
            $this->load->view('admin/common/footer', $footer_data);
            $this->load->view('admin/validation/home', $chart_data);
            
        } else {
            // Load login page
            $data['title'] = 'Admin Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    private function prepare_chart_data()
    {
        $total_polls_chart = array();
        $total_votes_chart = array();
        $total_comments_chart = array();
        $months = array();

        // Get last 12 months data
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i months"));
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));
            
            // Get monthly statistics
            $polls_count = $this->admin->get_monthly_polls_count($month, $year);
            $votes_count = $this->admin->get_monthly_votes_count($month, $year);
            $comments_count = $this->admin->get_monthly_comments_count($month, $year);
            
            array_push($total_polls_chart, (int)$polls_count);
            array_push($total_votes_chart, (int)$votes_count);
            array_push($total_comments_chart, (int)$comments_count);
            
            $month_label = date('M-Y', strtotime($date));
            array_push($months, $month_label);
        }

        return array(
            'months' => json_encode($months),
            'total_polls_chart' => json_encode($total_polls_chart, JSON_NUMERIC_CHECK),
            'total_votes_chart' => json_encode($total_votes_chart, JSON_NUMERIC_CHECK),
            'total_comments_chart' => json_encode($total_comments_chart, JSON_NUMERIC_CHECK)
        );
    }

    public function get_stats()
    {
        // Ajax endpoint for real-time updates
        if (is_loggedin_admin()) {
            $stats = array(
                'total_users' => $this->admin->get_total_users(),
                'total_polls' => $this->admin->get_total_polls(),
                'total_votes' => $this->admin->get_total_votes(),
                'active_polls' => $this->admin->get_active_polls_count()
            );

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 200, 'data' => $stats]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 401, 'message' => 'Unauthorized']));
        }
    }

    public function users()
    {
        if (is_loggedin_admin()) {
            $header_data['title'] = 'Manage Users || ' . config_item('application_name');

            // Get pagination parameters
            $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
            $limit = 20;
            $search = $this->input->get('search');
            $status = $this->input->get('status');

            // Get users with pagination
            $total_users = $this->admin->get_users_count($search, $status);
            $users = $this->admin->get_users_paginated($page, $limit, $search, $status);

            $data['users'] = $users;
            $data['total_users'] = $total_users;
            $data['current_page'] = $page;
            $data['total_pages'] = ceil($total_users / $limit);
            $data['search'] = $search;
            $data['status'] = $status;

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/users/index', $data);
            $this->load->view('admin/common/footer');
        } else {
            redirect('admin/login');
        }
    }
    
}