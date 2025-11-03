<?php
//Jai Shree Ram
defined('BASEPATH') or exit('No direct script access allowed');

class Notification extends CI_Controller
{
    private $tokenHandler;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Notification_model', 'notification');
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

    /**
     * Get authenticated user from token
     */
    private function get_authenticated_user()
    {
        $headers = $this->input->request_headers();

        if (empty($headers['Authorization'])) {
            return false;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $decoded = $this->tokenHandler->decodeToken($token);
            if (isset($decoded->user_id)) {
                return $decoded->user_id;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Create pagination response
     */
    private function create_pagination_response($data, $page, $limit, $total_count)
    {
        $total_pages = ceil($total_count / $limit);

        return [
            'status' => 200,
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total_count' => (int)$total_count,
                'total_pages' => (int)$total_pages,
                'has_next_page' => $page < $total_pages,
                'has_previous_page' => $page > 1
            ]
        ];
    }

    /**
     * Get user notifications with pagination
     * GET /notification/list
     */
    public function list()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 20;
        $type = $this->input->get('type'); // Filter by notification type

        // Get total count for pagination
        $total_count = $this->notification->get_notifications_count($current_user_id, $type);

        // Get notifications
        $notifications = $this->notification->get_notifications($current_user_id, $page, $limit, $type);

        // Format notifications
        $formatted_notifications = [];
        foreach ($notifications as $notification) {
            $formatted_notifications[] = $this->format_notification($notification);
        }

        $response = $this->create_pagination_response($formatted_notifications, $page, $limit, $total_count);
        $this->output($response);
    }

    /**
     * Delete notification
     * POST /notification/delete
     */
    public function delete()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $post_data = $this->input->post(null, true);

        if (empty($post_data['notification_id'])) {
            $this->output(['status' => 400, 'message' => 'Notification ID is required']);
            return;
        }

        $notification_id = $post_data['notification_id'];

        // Check if notification belongs to current user
        $notification = $this->common->getdatabytable('notifications', [
            'id' => $notification_id,
            'user_id' => $current_user_id
        ]);

        if (!$notification) {
            $this->output(['status' => 404, 'message' => 'Notification not found']);
            return;
        }

        $result = $this->common->delete($notification_id, 'notifications');

        if ($result) {
            $this->output(['status' => 200, 'message' => 'Notification deleted successfully']);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to delete notification']);
        }
    }

    /**
     * Format notification response
     */
    private function format_notification($notification)
    {
        return [
            'id' => (int)$notification['id'],
            'uuid' => $notification['uuid'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'reference_id' => $notification['reference_id'] ? (int)$notification['reference_id'] : null,
            'reference_type' => $notification['reference_type'],
            'data' => $notification['data'] ? json_decode($notification['data'], true) : null,
            'created_at' => $notification['created_at'],
            'sender' => [
                'id' => (int)$notification['sender_id'],
                'name' => $notification['sender_name'],
                'avatar' => base_url('uploads/profile_pictures/' . $notification['sender_avatar'])
            ]
        ];
    }
}
