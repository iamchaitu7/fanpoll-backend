<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Polls extends CI_Controller
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
            $header_data['title'] = 'Manage Polls || ' . config_item('application_name');

            // Get pagination parameters
            $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
            $limit = 20;
            $search = $this->input->get('search');
            $status = $this->input->get('status');

            // Get polls with pagination
            $total_polls = $this->admin->get_polls_count($search, $status);
            $polls = $this->admin->get_polls_paginated($page, $limit, $search, $status);

            $data['polls'] = $polls;
            $data['total_polls'] = $total_polls;
            $data['current_page'] = $page;
            $data['total_pages'] = ceil($total_polls / $limit);
            $data['search'] = $search;
            $data['status'] = $status;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/polls/index', $data);
            $this->load->view('admin/common/footer', $footer_data);
            $this->load->view('admin/validation/polls');
        } else {
            // Load login page
            $data['title'] = 'Admin Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function view($poll_id)
    {
        if (is_loggedin_admin()) {
            $header_data['title'] = 'Poll Details || ' . config_item('application_name');

            // Get poll details with analytics
            $poll_data = $this->admin->get_poll_analytics($poll_id);

            if (!$poll_data) {
                show_404();
                return;
            }

            // Get poll comments
            $comments = $this->admin->get_poll_comments_detailed($poll_id);

            // Get voting timeline
            $voting_timeline = $this->admin->get_poll_voting_timeline($poll_id);

            // Get poll shares and engagement
            $engagement_data = $this->admin->get_poll_engagement_data($poll_id);

            $data['poll'] = $poll_data['poll'];
            $data['options'] = $poll_data['options'];
            $data['comments'] = $comments;
            $data['voting_timeline'] = $voting_timeline;
            $data['engagement'] = $engagement_data;

            $footer_data['apex_chart'] = true;

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/polls/view', $data);
            $this->load->view('admin/common/footer', $footer_data);
            $this->load->view('admin/polls/view_js', $data);
        } else {
            redirect('admin/login');
        }
    }

    public function analytics($poll_id = null)
    {
        if (is_loggedin_admin()) {
            $header_data['title'] = 'Poll Analytics || ' . config_item('application_name');

            if ($poll_id) {
                // Single poll analytics
                $poll_data = $this->admin->get_poll_analytics($poll_id);

                if (!$poll_data) {
                    show_404();
                    return;
                }

                // Get detailed analytics for this poll
                $data['poll'] = $poll_data['poll'];
                $data['options'] = $poll_data['options'];
                $data['hourly_votes'] = $this->admin->get_poll_hourly_votes($poll_id);
                $data['daily_votes'] = $this->admin->get_poll_daily_votes($poll_id);
                $data['demographic_data'] = $this->admin->get_poll_demographics($poll_id);
                $data['geographic_data'] = $this->admin->get_poll_geographic_data($poll_id);

                $view_file = 'admin/polls/single_analytics';
            } else {
                // Overall poll analytics
                $data['total_polls'] = $this->admin->get_total_polls();
                $data['total_votes'] = $this->admin->get_total_votes();
                $data['avg_votes_per_poll'] = $this->admin->get_avg_votes_per_poll();
                $data['poll_completion_rate'] = $this->admin->get_poll_completion_rate();

                // Chart data
                $data['monthly_polls_chart'] = $this->prepare_monthly_polls_chart();
                $data['category_distribution'] = $this->admin->get_poll_category_distribution();
                $data['top_performing_polls'] = $this->admin->get_top_performing_polls(10);
                $data['trending_hashtags'] = $this->admin->get_trending_hashtags(10);

                $view_file = 'admin/polls/overall_analytics';
            }

            $footer_data['apex_chart'] = true;

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view($view_file, $data);
            $this->load->view('admin/common/footer', $footer_data);
            $this->load->view('admin/polls/analytics_js', $data);
        } else {
            redirect('admin/login');
        }
    }


    public function delete_poll(){
        if (is_loggedin_admin()) {
            $post_data = $this->input->post(null, true);
            if(!empty($post_data['poll_id'])){
                $where_data = [
                    'id' => $post_data['poll_id']
                ];

                $payload = [
                    'status' => 'deleted',
                    'deleted_at' => date('Y-m-d H:i:s')
                ];

                $update = $this->common->update('polls', $payload, $where_data);
                if ($update) {
                    $response = [
                        'status' => 200,
                        'message' => 'Poll deleted successfully'
                    ];
                } else {
                    $response = [
                        'status' => 500,
                        'message' => 'Failed to delete poll'
                    ];
                }
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'Poll ID is required'
                ];
            }
        } else {
            $response = [
                'status' => 401,
                'message' => 'Unauthorized access'
            ];
        }

        echo json_encode($response);
    }
}
