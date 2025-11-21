<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create vote notification
     */
    public function create_vote_notification($poll_id, $voter_id, $poll_owner_id)
    {
        log_message('debug', 'Notification_model::create_vote_notification - Creating vote notification');
        
        // Don't create notification if user is voting on their own poll
        if ($voter_id == $poll_owner_id) {
            log_message('debug', 'Notification_model::create_vote_notification - Skipping self-vote notification');
            return true;
        }

        // Get voter details for the notification message
        $voter = $this->get_user_by_id($voter_id);
        $poll = $this->get_poll_by_id($poll_id);

        if (!$voter || !$poll) {
            log_message('error', 'Notification_model::create_vote_notification - Voter or poll not found');
            return false;
        }

        $voter_name = $voter['full_name'] ?: 'Someone';
        $poll_title = $poll['title'] ?: 'your poll';

        $notification_data = [
            'uuid' => bin2hex(random_bytes(16)),
            'user_id' => $poll_owner_id,
            'sender_id' => $voter_id,
            'type' => 'vote',
            'title' => 'New Vote',
            'message' => $voter_name . ' voted on your poll: "' . $poll_title . '"',
            'reference_id' => $poll_id,
            'reference_type' => 'poll',
            'data' => json_encode([
                'poll_id' => $poll_id,
                'voter_id' => $voter_id,
                'voter_name' => $voter['full_name'],
                'poll_title' => $poll['title']
            ]),
            'created_at' => date('Y-m-d H:i:s')
        ];

        log_message('debug', 'Notification_model::create_vote_notification - Inserting notification: ' . json_encode($notification_data));
        
        $result = $this->db->insert('notifications', $notification_data);
        
        if ($result) {
            log_message('debug', 'Notification_model::create_vote_notification - Notification created successfully, ID: ' . $this->db->insert_id());
            
            // Send push notification if user has FCM token
            $this->send_push_notification($poll_owner_id, $notification_data);
        } else {
            $error = $this->db->error();
            log_message('error', 'Notification_model::create_vote_notification - Failed to create notification: ' . json_encode($error));
        }
        
        return $result;
    }

    /**
     * Get user by ID
     */
    private function get_user_by_id($user_id)
    {
        $this->db->where('id', $user_id);
        $this->db->where('deleted_at IS NULL', null, false);
        $query = $this->db->get('users');
        return $query->row_array();
    }

    /**
     * Get poll by ID
     */
    private function get_poll_by_id($poll_id)
    {
        $this->db->where('id', $poll_id);
        $query = $this->db->get('polls');
        return $query->row_array();
    }

    /**
     * Send push notification
     */
    private function send_push_notification($user_id, $notification_data)
    {
        // Get user's FCM token
        $this->db->select('fcm_device_token');
        $this->db->where('id', $user_id);
        $this->db->where('fcm_device_token IS NOT NULL', null, false);
        $this->db->where('fcm_device_token !=', '');
        $query = $this->db->get('users');
        $user = $query->row_array();

        if (!$user || empty($user['fcm_device_token'])) {
            log_message('debug', 'Notification_model::send_push_notification - No FCM token found for user: ' . $user_id);
            return false;
        }

        // Prepare FCM payload
        $fcm_data = [
            'to' => $user['fcm_device_token'],
            'notification' => [
                'title' => $notification_data['title'],
                'body' => $notification_data['message'],
                'sound' => 'default',
                'badge' => '1'
            ],
            'data' => [
                'type' => $notification_data['type'],
                'reference_id' => (string)$notification_data['reference_id'],
                'reference_type' => $notification_data['reference_type'],
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        ];

        // Send FCM request (you'll need to implement this based on your FCM setup)
        return $this->send_fcm_request($fcm_data);
    }

    /**
     * Send FCM request
     */
    private function send_fcm_request($fcm_data)
    {
        // Implement your FCM server key and request logic here
        // This is a placeholder - you need to implement actual FCM integration
        
        log_message('debug', 'Notification_model::send_fcm_request - Would send FCM: ' . json_encode($fcm_data));
        
        // Example implementation:
        /*
        $headers = [
            'Authorization: key=' . $this->config->item('fcm_server_key'),
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcm_data));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        log_message('debug', 'Notification_model::send_fcm_request - FCM response: ' . $result);
        return $result;
        */
        
        return true; // Temporarily return true until FCM is implemented
    }

    /**
     * Get notifications for user
     */
    public function get_notifications($user_id, $page = 1, $limit = 20, $type = null)
    {
        $offset = ($page - 1) * $limit;
        
        $this->db->select('n.*, u.full_name as sender_name, u.profile_picture as sender_avatar');
        $this->db->from('notifications n');
        $this->db->join('users u', 'u.id = n.sender_id', 'left');
        $this->db->where('n.user_id', $user_id);
        
        if ($type) {
            $this->db->where('n.type', $type);
        }
        
        $this->db->order_by('n.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get total notifications count
     */
    public function get_notifications_count($user_id, $type = null)
    {
        $this->db->where('user_id', $user_id);
        
        if ($type) {
            $this->db->where('type', $type);
        }
        
        return $this->db->count_all_results('notifications');
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $user_id)
    {
        $this->db->where('id', $notification_id);
        $this->db->where('user_id', $user_id);
        
        return $this->db->update('notifications', ['is_read' => 1]);
    }

    /**
     * Mark all notifications as read
     */
    public function mark_all_as_read($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('is_read', 0);
        
        return $this->db->update('notifications', ['is_read' => 1]);
    }

    /**
     * Delete notification
     */
    public function delete_notification($notification_id, $user_id)
    {
        $this->db->where('id', $notification_id);
        $this->db->where('user_id', $user_id);
        
        return $this->db->delete('notifications');
    }

    /**
     * Get unread notifications count
     */
    public function get_unread_count($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('is_read', 0);
        
        return $this->db->count_all_results('notifications');
    }

    /**
     * Create follow notification
     */
    public function create_follow_notification($follower_id, $following_id)
    {
        $follower = $this->get_user_by_id($follower_id);

        if (!$follower) {
            log_message('error', 'Notification_model::create_follow_notification - Follower not found');
            return false;
        }

        $follower_name = $follower['full_name'] ?: 'Someone';

        $notification_data = [
            'uuid' => bin2hex(random_bytes(16)),
            'user_id' => $following_id,
            'sender_id' => $follower_id,
            'type' => 'follow',
            'title' => 'New Follower',
            'message' => $follower_name . ' started following you',
            'reference_id' => $follower_id,
            'reference_type' => 'user',
            'data' => json_encode([
                'follower_id' => $follower_id,
                'follower_name' => $follower['full_name']
            ]),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('notifications', $notification_data);
    }
}