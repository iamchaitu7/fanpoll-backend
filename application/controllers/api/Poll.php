<?php
//Jai Shree Ram
defined('BASEPATH') or exit('No direct script access allowed');

class Poll extends CI_Controller
{
    private $tokenHandler;

    function __construct()
{
    log_message('debug', 'Poll::__construct - Constructor called');
    parent::__construct();
    $this->load->model('Common_model', 'common');
    $this->tokenHandler = new TokenHandler();

    $allowed_origins = config_item('allowed_origins');
    
    // Add local development origins
    $allowed_origins[] = 'http://localhost:8000';
    $allowed_origins[] = 'https://localhost:8000';

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: " . $origin);
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Expose-Headers: Content-Length, Content-Range");
    }
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("HTTP/1.1 200 OK");
        exit();
    }
    
    log_message('debug', 'Poll::__construct - Constructor completed');
}

    public function output($data)
    {
        log_message('debug', 'Poll::output - Outputting JSON data');
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        log_message('debug', 'Poll::output - JSON data output completed');
    }

    /**
     * Get authenticated user from token
     */
    private function get_authenticated_user()
    {
        log_message('debug', 'Poll::get_authenticated_user - Getting authenticated user from token');
        $headers = $this->input->request_headers();

        if (empty($headers['Authorization'])) {
            log_message('debug', 'Poll::get_authenticated_user - No Authorization header found');
            return false;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        log_message('debug', 'Poll::get_authenticated_user - Token extracted: ' . substr($token, 0, 10) . '...');

        try {
            $decoded = $this->tokenHandler->decodeToken($token);
            if (isset($decoded->user_id)) {
                log_message('debug', 'Poll::get_authenticated_user - User authenticated: ' . $decoded->user_id);
                return $decoded->user_id;
            }
        } catch (Exception $e) {
            log_message('error', 'Poll::get_authenticated_user - Token decoding failed: ' . $e->getMessage());
            return false;
        }

        log_message('debug', 'Poll::get_authenticated_user - Token validation failed');
        return false;
    }

    /**
     * Create pagination response
     */
    private function create_pagination_response($data, $page, $limit, $total_count)
    {
        log_message('debug', 'Poll::create_pagination_response - Creating pagination response');
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
 * Create a new poll
 * POST /poll/create
 */
public function create()
{
    log_message('debug', 'Poll::create - Starting poll creation');
    $current_user_id = $this->get_authenticated_user();

    if (!$current_user_id) {
        log_message('debug', 'Poll::create - Unauthorized access attempt');
        $this->output(['status' => 401, 'message' => 'Unauthorized']);
        return;
    }

    log_message('debug', 'Poll::create - User authenticated: ' . $current_user_id);
    $post_data = $this->input->post(null, true);

    // Validate required fields
    if (empty($post_data['title'])) {
        log_message('debug', 'Poll::create - Validation failed: Poll title is required');
        $this->output(['status' => 400, 'message' => 'Poll title is required']);
        return;
    }

    if (empty($post_data['options']) || !is_array($post_data['options']) || count($post_data['options']) < 2) {
        log_message('debug', 'Poll::create - Validation failed: At least 2 poll options are required');
        $this->output(['status' => 400, 'message' => 'At least 2 poll options are required']);
        return;
    }

    if (empty($post_data['expires_in_days']) || $post_data['expires_in_days'] < 1 || $post_data['expires_in_days'] > 7) {
        log_message('debug', 'Poll::create - Validation failed: Invalid expiry days');
        $this->output(['status' => 400, 'message' => 'Expiry must be between 1-7 days']);
        return;
    }

    log_message('debug', 'Poll::create - Basic validation passed');

    // Handle image upload if provided
    $image_path = null;
    $image_url = null;
    
    if (!empty($_FILES['image']['name'])) {
        log_message('debug', 'Poll::create - Image upload detected');
        $file = $_FILES['image'];

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            log_message('debug', 'Poll::create - Upload error: ' . $file['error']);
            $this->output(['status' => 400, 'message' => 'Upload error occurred.']);
            return;
        }

        // Check file size (5MB = 5242880 bytes)
        if ($file['size'] > 5242880) {
            log_message('debug', 'Poll::create - File too large: ' . $file['size'] . ' bytes');
            $this->output(['status' => 400, 'message' => 'File too large. Maximum size is 5MB.']);
            return;
        }

        // Validate extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_ext, $allowed_extensions)) {
            log_message('debug', 'Poll::create - Invalid file extension: ' . $file_ext);
            $this->output(['status' => 400, 'message' => 'Invalid file type.']);
            return;
        }

        // Validate that it's actually an image
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            log_message('debug', 'Poll::create - File is not a valid image');
            $this->output(['status' => 400, 'message' => 'File is not a valid image.']);
            return;
        }

        // Upload to Cloudinary
        log_message('debug', 'Poll::create - Starting Cloudinary upload');
        $upload_result = $this->upload_to_cloudinary($file['tmp_name'], $file_ext);
        
        if ($upload_result['success']) {
            $image_url = $upload_result['image_url'];
            $image_path = $upload_result['public_id']; // Store Cloudinary public_id instead of local path
            log_message('debug', 'Poll::create - Image uploaded to Cloudinary successfully: ' . $image_url);
        } else {
            log_message('error', 'Poll::create - Cloudinary upload failed: ' . $upload_result['error']);
            $this->output(['status' => 500, 'message' => 'Failed to upload image to cloud storage.']);
            return;
        }
    } else {
        log_message('debug', 'Poll::create - No image uploaded');
    }

    // Calculate expiry date
    $expires_at = date('Y-m-d H:i:s', strtotime('+' . $post_data['expires_in_days'] . ' days'));
    log_message('debug', 'Poll::create - Expiry date calculated: ' . $expires_at);

    // Process hashtags to remove duplicates
    $hashtags = null;
    if (!empty($post_data['hashtags'])) {
        $hashtags_array = array_filter(array_map('trim', explode(',', $post_data['hashtags'])));
        $unique_hashtags = array_unique($hashtags_array, SORT_STRING);
        $hashtags = implode(',', $unique_hashtags);
        log_message('debug', 'Poll::create - Hashtags processed: ' . $hashtags);
    }

    // Create poll
    $poll_data = array(
        'user_id' => $current_user_id,
        'title' => $post_data['title'],
        'description' => !empty($post_data['description']) ? $post_data['description'] : null,
        'url' => !empty($post_data['url']) ? $post_data['url'] : null,
        'image_path' => $image_path, // Now stores Cloudinary public_id
        'hashtags' => $hashtags,
        'expires_at' => $expires_at,
        'status' => 'active'
    );

    log_message('debug', 'Poll::create - Inserting poll data into database');
    $poll_id = $this->common->insert($poll_data, 'polls');

    if (!$poll_id) {
        log_message('error', 'Poll::create - Failed to create poll in database');
        $this->output(['status' => 500, 'message' => 'Failed to create poll']);
        return;
    }

    log_message('debug', 'Poll::create - Poll created with ID: ' . $poll_id);

    // Create poll options
    log_message('debug', 'Poll::create - Creating poll options');
    foreach ($post_data['options'] as $index => $option_text) {
        if (!empty(trim($option_text))) {
            $option_data = array(
                'poll_id' => $poll_id,
                'option_text' => trim($option_text),
                'option_order' => $index + 1
            );
            $this->common->insert($option_data, 'poll_options');
            log_message('debug', 'Poll::create - Option created: ' . trim($option_text));
        }
    }

    log_message('debug', 'Poll::create - Poll creation completed successfully');
    $this->output([
        'status' => 200, 
        'message' => 'Poll created successfully', 
        'poll_id' => $poll_id,
        'image_url' => $image_url // This is the Cloudinary URL
    ]);
}

/**
 * Upload image to Cloudinary
 */
private function upload_to_cloudinary($file_path, $file_extension)
{
    // Cloudinary configuration - store these in config/constants.php
    $cloud_name = 'dq9zl6oob'; // Replace with your Cloudinary cloud name
    $upload_preset = 'uploads'; // Replace with your unsigned upload preset name
    
    $url = "https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload";
    
    // Generate unique filename
    $unique_filename = uniqid() . '.' . $file_extension;
    
    $post_data = [
        'file' => new CURLFile($file_path),
        'upload_preset' => $upload_preset,
        'public_id' => 'poll_images/' . pathinfo($unique_filename, PATHINFO_FILENAME),
        'folder' => 'poll_images'
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
     * Validate token and get user ID (your existing method)
     */
    private function validate_token_and_get_user($token) {
        log_message('debug', 'Poll::validate_token_and_get_user - Validating token');
        // Your existing token validation logic
        // Return user ID if valid, false if invalid
        $user = $this->common->get_where('users', ['auth_token' => $token]);
        $result = $user ? $user['id'] : false;
        log_message('debug', 'Poll::validate_token_and_get_user - Token validation result: ' . ($result ? 'valid' : 'invalid'));
        return $result;
    }

    /**
     * Get active polls
     * GET /poll/active_polls
     */
    public function active_polls()
    {
        log_message('debug', 'Poll::active_polls - Getting active polls');
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            log_message('debug', 'Poll::active_polls - Unauthorized access attempt');
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        log_message('debug', 'Poll::active_polls - User authenticated: ' . $current_user_id);

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = 10;
        log_message('debug', 'Poll::active_polls - Pagination - Page: ' . $page . ', Limit: ' . $limit);

        // Get search parameters
        $search = $this->input->get('search');
        $hashtag = $this->input->get('hashtag');
        log_message('debug', 'Poll::active_polls - Search params - Search: ' . $search . ', Hashtag: ' . $hashtag);

        // Get total count for pagination
        log_message('debug', 'Poll::active_polls - Getting total count');
        $total_count = $this->common->get_active_polls_count($search, $hashtag);
        log_message('debug', 'Poll::active_polls - Total count: ' . $total_count);

        log_message('debug', 'Poll::active_polls - Fetching active polls from database');
        $polls = $this->common->get_active_polls($page, $limit, $search, $hashtag, $current_user_id);
        log_message('debug', 'Poll::active_polls - Retrieved ' . count($polls) . ' polls');

        // Format polls with options and user vote status
        $formatted_polls = array();
        foreach ($polls as $poll) {
            $formatted_poll = $this->format_poll_response($poll, $current_user_id, false);
            $formatted_polls[] = $formatted_poll;
        }

        log_message('debug', 'Poll::active_polls - Formatting completed');
        $response = $this->create_pagination_response($formatted_polls, $page, $limit, $total_count);
        $this->output($response);
    }

    /**
     * Get completed polls
     * GET /poll/completed_polls
     */
    public function completed_polls()
    {
        log_message('debug', 'Poll::completed_polls - Getting completed polls');
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            log_message('debug', 'Poll::completed_polls - Unauthorized access attempt');
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        log_message('debug', 'Poll::completed_polls - User authenticated: ' . $current_user_id);

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = 10;
        log_message('debug', 'Poll::completed_polls - Pagination - Page: ' . $page . ', Limit: ' . $limit);

        // Get total count for pagination
        log_message('debug', 'Poll::completed_polls - Getting total count');
        $total_count = $this->common->get_completed_polls_count();
        log_message('debug', 'Poll::completed_polls - Total count: ' . $total_count);

        log_message('debug', 'Poll::completed_polls - Fetching completed polls from database');
        $polls = $this->common->get_completed_polls($page, $limit, $current_user_id);
        log_message('debug', 'Poll::completed_polls - Retrieved ' . count($polls) . ' polls');

        // Format polls with results
        $formatted_polls = array();
        foreach ($polls as $poll) {
            $formatted_poll = $this->format_poll_response($poll, $current_user_id, true);
            $formatted_polls[] = $formatted_poll;
        }

        log_message('debug', 'Poll::completed_polls - Formatting completed');
        $response = $this->create_pagination_response($formatted_polls, $page, $limit, $total_count);
        $this->output($response);
    }

    /**
     * View single poll
     * GET /poll/view/123
     */
    public function view($poll_id)
    {
        log_message('debug', 'Poll::view - Viewing poll: ' . $poll_id);
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            log_message('debug', 'Poll::view - Unauthorized access attempt');
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        log_message('debug', 'Poll::view - User authenticated: ' . $current_user_id);

        if (!$poll_id) {
            log_message('debug', 'Poll::view - Poll ID is required');
            $this->output(['status' => 400, 'message' => 'Poll ID is required']);
            return;
        }

        // Get poll data
        log_message('debug', 'Poll::view - Fetching poll data from database');
        $poll = $this->common->get_poll_by_id($poll_id);

        if (!$poll) {
            log_message('debug', 'Poll::view - Poll not found: ' . $poll_id);
            $this->output(['status' => 404, 'message' => 'Poll not found']);
            return;
        }

        log_message('debug', 'Poll::view - Poll found: ' . $poll['title']);

        // Check if poll is expired
        $is_expired = strtotime($poll['expires_at']) <= time();
        log_message('debug', 'Poll::view - Poll expired status: ' . ($is_expired ? 'yes' : 'no'));

        $formatted_poll = $this->format_poll_response($poll, $current_user_id, $is_expired);
        log_message('debug', 'Poll::view - Poll formatted successfully');

        $this->output(['status' => 200, 'data' => $formatted_poll]);
    }

    /**
     * Vote on a poll
     * POST /poll/vote
     */
    public function vote()
    {
        log_message('debug', 'Poll::vote - Processing vote');
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            log_message('debug', 'Poll::vote - Unauthorized access attempt');
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        log_message('debug', 'Poll::vote - User authenticated: ' . $current_user_id);
        $post_data = $this->input->post(null, true);

        if (empty($post_data['poll_id']) || empty($post_data['option_id'])) {
            log_message('debug', 'Poll::vote - Missing poll_id or option_id');
            $this->output(['status' => 400, 'message' => 'Poll ID and Option ID are required']);
            return;
        }

        $poll_id = $post_data['poll_id'];
        $option_id = $post_data['option_id'];
        log_message('debug', 'Poll::vote - Vote request - Poll: ' . $poll_id . ', Option: ' . $option_id);

        // Check if poll exists and is active
        log_message('debug', 'Poll::vote - Checking poll existence');
        $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'status' => 'active'));

        if (!$poll) {
            log_message('debug', 'Poll::vote - Poll not found: ' . $poll_id);
            $this->output(['status' => 404, 'message' => 'Poll not found']);
            return;
        }

        // Check if poll is expired
        if (strtotime($poll->expires_at) <= time()) {
            log_message('debug', 'Poll::vote - Poll expired: ' . $poll_id);
            $this->output(['status' => 400, 'message' => 'Poll has expired']);
            return;
        }

        // Check if option exists
        log_message('debug', 'Poll::vote - Checking option existence');
        $option = $this->common->getdatabytable('poll_options', array('id' => $option_id, 'poll_id' => $poll_id));

        if (!$option) {
            log_message('debug', 'Poll::vote - Option not found: ' . $option_id);
            $this->output(['status' => 404, 'message' => 'Poll option not found']);
            return;
        }

        // Check if user has already voted
        log_message('debug', 'Poll::vote - Checking existing vote');
        $existing_vote = $this->common->getdatabytable('poll_votes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

        if ($existing_vote) {
            log_message('debug', 'Poll::vote - User already voted: ' . $current_user_id);
            $this->output(['status' => 400, 'message' => 'You have already voted on this poll']);
            return;
        }

        // Cast vote
        log_message('debug', 'Poll::vote - Casting vote');
        $vote_data = array(
            'poll_id' => $poll_id,
            'option_id' => $option_id,
            'user_id' => $current_user_id,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        );

        try {
    $result = $this->common->insert($vote_data, 'poll_votes');

    if ($result) {
        log_message('debug', 'Poll::vote - Vote cast successfully');

        // Create notification for poll owner
        log_message('debug', 'Poll::vote - Creating vote notification');
        $this->load->model('Notification_model', 'notification');
        $this->notification->create_vote_notification($poll_id, $current_user_id, $poll->user_id);

        // Get updated poll with results
        log_message('debug', 'Poll::vote - Fetching updated poll data');
        $updated_poll = $this->common->get_poll_by_id($poll_id);
        $formatted_poll = $this->format_poll_response($updated_poll, $current_user_id, false);

        log_message('debug', 'Poll::vote - Vote process completed successfully');
        $this->output([
            'status' => 200,
            'message' => 'Vote cast successfully',
            'poll' => $formatted_poll
        ]);
    } else {
        // Get database error details
        $db_error = $this->db->error();
        log_message('error', 'Poll::vote - Database error: ' . json_encode($db_error));
        log_message('error', 'Poll::vote - Vote data: ' . json_encode($vote_data));
        
        $this->output([
            'status' => 500, 
            'message' => 'Failed to cast vote',
            'debug' => [
                'database_error' => $db_error,
                'vote_data' => $vote_data,
                'insert_result' => $result
            ]
        ]);
    }

} catch (Exception $e) {
    log_message('error', 'Poll::vote - Exception: ' . $e->getMessage());
    log_message('error', 'Poll::vote - Stack trace: ' . $e->getTraceAsString());
    
    $this->output([
        'status' => 500,
        'message' => 'Failed to cast vote',
        'debug' => [
            'exception' => $e->getMessage(),
            'exception_trace' => $e->getTraceAsString(),
            'vote_data' => $vote_data
        ]
    ]);
}
    }

    /**
     * Undo vote on a poll
     * POST /poll/undo_vote
     */
    public function undo_vote()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $post_data = $this->input->post(null, true);

        if (empty($post_data['poll_id'])) {
            $this->output(['status' => 400, 'message' => 'Poll ID is required']);
            return;
        }

        $poll_id = $post_data['poll_id'];

        // Check if poll exists and is active
        $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'status' => 'active'));

        if (!$poll) {
            $this->output(['status' => 404, 'message' => 'Poll not found']);
            return;
        }

        // Check if poll is expired
        if (strtotime($poll->expires_at) <= time()) {
            $this->output(['status' => 400, 'message' => 'Cannot undo vote on expired poll']);
            return;
        }

        // Check if user has voted
        $existing_vote = $this->common->getdatabytable('poll_votes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

        if (!$existing_vote) {
            $this->output(['status' => 400, 'message' => 'You have not voted on this poll']);
            return;
        }
         // Start transaction for data consistency
        $this->db->trans_start();

        // Delete vote
        $vote_deleted = $this->common->deleteWhere('poll_votes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

        if ($vote_deleted) {
            // Decrement total_votes in polls table
            $this->db->where('id', $poll_id);
            $this->db->set('total_votes', 'total_votes - 1', false);
            $this->db->update('polls');
            
            // Decrement vote_count in poll_options table for the specific option
            if (isset($existing_vote->option_id)) {
                $this->db->where('id', $existing_vote->option_id);
                $this->db->set('vote_count', 'vote_count - 1', false);
                $this->db->update('poll_options');
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE || !$vote_deleted) {
            $this->output(['status' => 500, 'message' => 'Failed to remove vote']);
            return;
        }

        // Get updated poll without user vote
        $updated_poll = $this->common->get_poll_by_id($poll_id);
        $formatted_poll = $this->format_poll_response($updated_poll, $current_user_id, false);

        $this->output([
            'status' => 200,
            'message' => 'Vote removed successfully',
            'poll' => $formatted_poll
        ]);
    }

    //     // Delete vote
    //     $result = $this->common->deleteWhere('poll_votes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

    //     if ($result) {

    //         // Get updated poll without user vote
    //         $updated_poll = $this->common->get_poll_by_id($poll_id);
    //         $formatted_poll = $this->format_poll_response($updated_poll, $current_user_id, false);

    //         $this->output([
    //             'status' => 200,
    //             'message' => 'Vote removed successfully',
    //             'poll' => $formatted_poll
    //         ]);
    //     } else {
    //         $this->output(['status' => 500, 'message' => 'Failed to remove vote']);
    //     }
    // }

    /**
     * Delete a poll (only by creator)
     * POST /poll/delete
     */
    public function delete()
    {
        log_message('debug', 'Poll::delete - Processing poll deletion');
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            log_message('debug', 'Poll::delete - Unauthorized access attempt');
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        log_message('debug', 'Poll::delete - User authenticated: ' . $current_user_id);
        $post_data = $this->input->post(null, true);

        if (empty($post_data['poll_id'])) {
            log_message('debug', 'Poll::delete - Missing poll_id');
            $this->output(['status' => 400, 'message' => 'Poll ID is required']);
            return;
        }

        $poll_id = $post_data['poll_id'];
        log_message('debug', 'Poll::delete - Delete request for poll: ' . $poll_id);

        // Check if poll exists and user is the creator
        log_message('debug', 'Poll::delete - Checking poll ownership');
        $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'user_id' => $current_user_id, 'status' => 'active'));

        if (!$poll) {
            log_message('debug', 'Poll::delete - Poll not found or unauthorized: ' . $poll_id);
            $this->output(['status' => 404, 'message' => 'Poll not found or you are not authorized to delete it']);
            return;
        }

        // Soft delete poll
        log_message('debug', 'Poll::delete - Soft deleting poll');
        $update_data = array(
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s')
        );

        $result = $this->common->update('polls', $update_data, array('id' => $poll_id));

        if ($result) {
            log_message('debug', 'Poll::delete - Poll deleted successfully: ' . $poll_id);
            $this->output(['status' => 200, 'message' => 'Poll deleted successfully']);
        } else {
            log_message('error', 'Poll::delete - Failed to delete poll: ' . $poll_id);
            $this->output(['status' => 500, 'message' => 'Failed to delete poll']);
        }
    }

/**
 * Format poll response with options and user vote status
 */
private function format_poll_response($poll, $user_id, $show_results = false)
{
    log_message('debug', 'Poll::format_poll_response - Formatting poll response for poll: ' . $poll['id']);
    
    // Get poll options
    log_message('debug', 'Poll::format_poll_response - Getting poll options');
    $options = $this->common->get_poll_options($poll['id']);
    log_message('debug', 'Poll::format_poll_response - Retrieved ' . count($options) . ' options');

    // Check if user has voted
    $user_vote = null;
    if ($user_id) {
        log_message('debug', 'Poll::format_poll_response - Checking user vote status');
        $vote = $this->common->getdatabytable('poll_votes', array('poll_id' => $poll['id'], 'user_id' => $user_id));
        $user_vote = $vote ? $vote->option_id : null;
        log_message('debug', 'Poll::format_poll_response - User vote: ' . ($user_vote ? $user_vote : 'none'));
    }

    // Check if user has liked this poll
    $user_liked = false;
    if ($user_id) {
        log_message('debug', 'Poll::format_poll_response - Checking user like status');
        $like = $this->common->getdatabytable('poll_likes', array('poll_id' => $poll['id'], 'user_id' => $user_id));
        $user_liked = !empty($like);
        log_message('debug', 'Poll::format_poll_response - User liked: ' . ($user_liked ? 'yes' : 'no'));
    }

    // Format options with vote data
    log_message('debug', 'Poll::format_poll_response - Formatting options');
    $formatted_options = array();
    foreach ($options as $option) {
        $formatted_option = array(
            'id' => (int)$option['id'],
            'text' => $option['option_text'],
            'order' => (int)$option['option_order'],
            'vote_count' => (int)$option['vote_count'],
            'percentage' => $poll['total_votes'] > 0 ? round(($option['vote_count'] / $poll['total_votes']) * 100, 1) : 0,
            'is_voted' => $user_vote == $option['id']
        );

        // Hide vote counts for active polls if user hasn't voted (unless showing results)
        if (!$show_results && !$user_vote && strtotime($poll['expires_at']) > time()) {
            $formatted_option['vote_count'] = 0;
            $formatted_option['percentage'] = 0;
        }

        $formatted_options[] = $formatted_option;
    }

    // Check if poll is expired
    $is_expired = strtotime($poll['expires_at']) <= time();
    log_message('debug', 'Poll::format_poll_response - Poll expired: ' . ($is_expired ? 'yes' : 'no'));

    // Handle image URL - Cloudinary integration
    $image_url = null;
    if (!empty($poll['image_path'])) {
        // Check if it's a Cloudinary public_id (starts with 'poll_images/') or a local path
        if (strpos($poll['image_path'], 'poll_images/') === 0) {
            // It's a Cloudinary public_id - generate Cloudinary URL
            $cloud_name = 'dq9zl6oob'; // Replace with your Cloudinary cloud name
            $public_id = $poll['image_path'];
            
            // Generate Cloudinary URL with optimizations
            $image_url = "https://res.cloudinary.com/{$cloud_name}/image/upload/q_auto,f_auto/{$public_id}";
            log_message('debug', 'Poll::format_poll_response - Cloudinary image URL generated: ' . $image_url);
        } else {
            // It's a local path - use the existing serve_image.php approach
            $base_domain = 'https://fanpoll-backend-production.up.railway.app/serve_image.php?file=';
            $image_path = rtrim($poll['image_path'], '.');
            $image_url = $base_domain . $image_path;
            log_message('debug', 'Poll::format_poll_response - Local image URL: ' . $image_url);
        }
    } else {
        log_message('debug', 'Poll::format_poll_response - No image path found');
    }

    // Handle avatar URL - ensure it's HTTPS and provide fallback
    $avatar_url = null;
    if (!empty($poll['creator_avatar']) && $poll['creator_avatar'] !== 'default-profile.jpg') {
        // Check if avatar is stored in Cloudinary or locally
        if (strpos($poll['creator_avatar'], 'avatars/') === 0) {
            // Cloudinary avatar - generate URL
            $cloud_name = 'dq9zl6oob'; // Replace with your Cloudinary cloud name
            $public_id = $poll['creator_avatar'];
            $avatar_url = "https://res.cloudinary.com/{$cloud_name}/image/upload/w_100,h_100,c_fill,q_auto,f_auto/{$public_id}";
        } else {
            // Local avatar
            $base_domain = 'https://fanpoll-backend-production.up.railway.app/serve_image.php?file=';
            $avatar_path = rtrim($poll['creator_avatar'], '.');
            $avatar_url = $base_domain . '/uploads/profile_pictures/default.png';
        }
    } else {
        // Fallback to default avatar - you can use Cloudinary for this too
        $base_domain = 'https://fanpoll-backend-production.up.railway.app/serve_image.php?file=';
        $avatar_url = $base_domain . '/uploads/profile_pictures/default.png';
        log_message('debug', 'Poll::format_poll_response - Using default avatar');
    }

    log_message('debug', 'Poll::format_poll_response - Formatting completed');
    return array(
        'id' => (int)$poll['id'],
        'uuid' => $poll['uuid'],
        'title' => $poll['title'],
        'description' => $poll['description'],
        'url' => $poll['url'],
        'image_url' => $image_url, // Now supports both Cloudinary and local images
        'hashtags' => $poll['hashtags'],
        'total_votes' => (int)$poll['total_votes'],
        'likes_count' => (int)$poll['likes_count'],
        'comments_count' => (int)$poll['comments_count'],
        'expires_at' => $poll['expires_at'],
        'is_expired' => $is_expired,
        'can_vote' => !$is_expired && !$user_vote,
        'has_voted' => !empty($user_vote),
        'is_liked' => $user_liked,
        'created_at' => $poll['created_at'],
        'creator' => array(
            'id'            => (int)$poll['user_id'],
            'name'          => $poll['creator_name'],
            'avatar'        => $avatar_url, // Supports both Cloudinary and local avatars
            'is_following'  => isset($poll['is_following']) ? (bool)$poll['is_following'] : false
        ),
        'options' => $formatted_options,
        'is_own_poll' => $poll['user_id'] == $user_id
    );
}
    public function user_polls($user_id = null)
    {
        log_message('debug', 'Poll::user_polls - Getting user polls');
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            log_message('debug', 'Poll::user_polls - Unauthorized access attempt');
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        if (!$user_id) {
            $user_id = $current_user_id;
            log_message('debug', 'Poll::user_polls - Using current user ID: ' . $user_id);
        } else {
            log_message('debug', 'Poll::user_polls - Using provided user ID: ' . $user_id);
        }

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 10;
        log_message('debug', 'Poll::user_polls - Pagination - Page: ' . $page . ', Limit: ' . $limit);

        // Get total count for pagination
        log_message('debug', 'Poll::user_polls - Getting total count');
        $total_count = $this->common->get_user_polls_count($user_id);
        log_message('debug', 'Poll::user_polls - Total count: ' . $total_count);

        log_message('debug', 'Poll::user_polls - Fetching user polls from database');
        $polls = $this->common->get_user_polls_paginated($user_id, $page, $limit);

        if (!$polls) {
            log_message('debug', 'Poll::user_polls - No polls found for user: ' . $user_id);
            $response = $this->create_pagination_response([], $page, $limit, 0);
            $response['message'] = 'No polls found for this user';
            $this->output($response);
            return;
        }

        log_message('debug', 'Poll::user_polls - Retrieved ' . count($polls) . ' polls');
        $polls_data = [];
        foreach ($polls as $poll) {
            $polls_data[] = array(
                'id'            => $poll->id,
                'uuid'          => $poll->uuid,
                'title'         => $poll->title,
                'description'   => $poll->description,
                'created_at'    => $poll->created_at,
                'expires_at'    => $poll->expires_at,
                'status'        => $poll->status
            );
        }

        $response = $this->create_pagination_response($polls_data, $page, $limit, $total_count);
        $response['message'] = 'Polls retrieved successfully';
        $this->output($response);
    }

   public function post_comment()
{
    $debug_info = []; // Initialize debug array
    $current_user_id = $this->get_authenticated_user();

    if (!$current_user_id) {
        $this->output(['status' => 401, 'message' => 'Unauthorized']);
        return;
    }

    $post_data = $this->input->post(null, true);

    if (empty($post_data['poll_id']) || empty($post_data['comment'])) {
        $this->output(['status' => 400, 'message' => 'Poll ID and comment are required']);
        return;
    }

    $poll_id = $post_data['poll_id'];
    $comment = trim($post_data['comment']);

    // Check if poll exists
    $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'status' => 'active'));
    if (!$poll) {
        $this->output(['status' => 404, 'message' => 'Poll not found']);
        return;
    }

    // Check poll is not expired
    if (strtotime($poll->expires_at) <= time()) {
        $this->output(['status' => 400, 'message' => 'Cannot comment on expired poll']);
        return;
    }

    // Start database transaction for data consistency
    $this->db->trans_start();

    // Insert comment
    $comment_data = array(
        'poll_id' => $poll_id,
        'user_id' => $current_user_id,
        'comment' => $comment,
        'created_at' => date('Y-m-d H:i:s')
    );

    $result = $this->common->insert($comment_data, 'poll_comments');
    
    if (!$result) {
        throw new Exception('Failed to insert comment into database');
    }

    // Update comments count in polls table
    $this->db->where('id', $poll_id);
    $this->db->set('comments_count', 'comments_count + 1', false);
    $update_result = $this->db->update('polls');
    
    if (!$update_result) {
        throw new Exception('Failed to update comments count in polls table');
    }

    // NOTIFICATION DEBUGGING
$debug_info['notification'] = [
    'poll_id' => $poll_id,
    'liker_id' => $current_user_id,
    'poll_owner_id' => $poll->user_id,
    'is_own_poll' => ($poll->user_id == $current_user_id),
    'model_file_exists' => false,
    'model_loaded' => false,
    'method_exists' => false,
    'notification_result' => false,
    'load_error' => null
];

// Check if model file exists
$model_path = APPPATH . 'models/Notification_model.php';
$debug_info['notification']['model_file_exists'] = file_exists($model_path);

if (file_exists($model_path)) {
    try {
        // Try to load the model
        $this->load->model('Notification_model');
        $debug_info['notification']['model_loaded'] = true;
        
        // Check if method exists
        if (method_exists($this->notificationmodel, 'create_like_notification')) {
            $debug_info['notification']['method_exists'] = true;
            
            // Create notification
            $notification_result = $this->notificationmodel->create_like_notification($poll_id, $current_user_id, $poll->user_id);
            $debug_info['notification']['notification_result'] = $notification_result;
            
            if (!$notification_result) {
                // Get database error
                $db_error = $this->db->error();
                $debug_info['notification']['db_error'] = $db_error;
            }
        }
    } catch (Exception $e) {
        $debug_info['notification']['load_error'] = $e->getMessage();
    }
} else {
    $debug_info['notification']['model_path'] = $model_path;
}

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
        throw new Exception('Database transaction failed');
    }

    $this->output([
        'status' => 200,
        'message' => 'Comment posted successfully',
        'data' => array(
            'comment_id' => $result,
            'comment' => $comment,
            'user_id' => $current_user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'poll_id' => $poll_id
        ),
        'debug_info' => $debug_info // Append debug info to response
    ]);
}
    

    public function comments()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $poll_id = $this->input->get('poll_id');
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = 10;

        if (empty($poll_id)) {
            $this->output(['status' => 400, 'message' => 'Poll ID is required']);
            return;
        }


        // Check if poll exists
        $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'status' => 'active'));
        if (!$poll) {
            $this->output(['status' => 404, 'message' => 'Poll not found']);
            return;
        }

        // Get total comments count for pagination
        $total_count = $this->common->get_poll_comments_count($poll_id);

        // Get comments
        $comments = $this->common->get_poll_comments($poll_id, $page, $limit);

        if (!empty($comments)) {
            $formatted_comments = [];
            foreach ($comments as $comment) {
                $formatted_comments[] = array(
                    'id' => (int)$comment['id'],
                    'user_id' => (int)$comment['user_id'],
                    'comment' => $comment['comment'],
                    'created_at' => $comment['created_at'],
                    'user_name' => $comment['user_name'],
                    'user_avatar' => base_url('uploads/profile_pictures/' . $comment['user_avatar'])
                );
            }

            $response = $this->create_pagination_response($formatted_comments, $page, $limit, $total_count);
            $this->output($response);
        } else {
            $response = $this->create_pagination_response([], $page, $limit, 0);
            $response['message'] = 'No comments found';
            $this->output($response);
        }
    }

        /**
     * Toggle like/unlike on a poll
     * POST /poll/toggle_like
     */
    public function toggle_like()
{
    $debug_info = []; // Initialize debug array
    
    try {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $post_data = $this->input->post(null, true);

        if (empty($post_data['poll_id'])) {
            $this->output(['status' => 400, 'message' => 'Poll ID is required']);
            return;
        }

        $poll_id = $post_data['poll_id'];

        // Validate poll_id is numeric
        if (!is_numeric($poll_id)) {
            $this->output(['status' => 400, 'message' => 'Invalid Poll ID']);
            return;
        }

        // Check if poll exists and is active
        $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'status' => 'active'));

        if (!$poll) {
            $this->output(['status' => 404, 'message' => 'Poll not found']);
            return;
        }

        // Start database transaction
        $this->db->trans_start();

        // Check if user has already liked this poll
        $existing_like = $this->common->getdatabytable('poll_likes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

        // NOTIFICATION DEBUGGING
        $debug_info['notification'] = [
            'poll_id' => $poll_id,
            'liker_id' => $current_user_id,
            'poll_owner_id' => $poll->user_id,
            'is_own_poll' => ($poll->user_id == $current_user_id),
            'notification_model_loaded' => false,
            'method_exists' => false,
            'notification_result' => false,
            'db_error' => null
        ];

        if ($existing_like) {
            // Unlike - remove the like
            $result = $this->common->deleteWhere('poll_likes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

            if (!$result) {
                throw new Exception('Failed to delete like record from poll_likes table');
            }

            // Decrease likes count in polls table
            $decrease_result = $this->common->increasePollLikesCount($poll_id, -1);
            if (!$decrease_result) {
                throw new Exception('Failed to decrease likes count in polls table');
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed during unlike operation');
            }

            // Get updated likes count
            $updated_poll = $this->common->getdatabytable('polls', array('id' => $poll_id));
            if (!$updated_poll) {
                throw new Exception('Failed to fetch updated poll data after unlike');
            }

            $response = [
                'status' => 200,
                'message' => 'Poll unliked successfully',
                'data' => array(
                    'is_liked' => false,
                    'likes_count' => (int)$updated_poll->likes_count,
                    'action' => 'unliked'
                )
            ];

        } else {
            // Like - add the like
            $like_data = array(
                'poll_id' => $poll_id,
                'user_id' => $current_user_id,
                'created_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->insert($like_data, 'poll_likes');

            if (!$result) {
                throw new Exception('Failed to insert like record into poll_likes table');
            }

            // Increase likes count in polls table
            $increase_result = $this->common->increasePollLikesCount($poll_id);
            if (!$increase_result) {
                throw new Exception('Failed to increase likes count in polls table');
            }

            // Create notification for poll owner (if not liking own poll)
            if ($poll->user_id != $current_user_id) {
                if (class_exists('Notification_model')) {
                    $debug_info['notification']['notification_model_loaded'] = true;
                    
                    try {
                        $this->load->model('Notification_model', 'notification');
                        
                        if (method_exists($this->notification, 'create_like_notification')) {
                            $debug_info['notification']['method_exists'] = true;
                            
                            $notification_result = $this->notification->create_like_notification($poll_id, $current_user_id, $poll->user_id);
                            $debug_info['notification']['notification_result'] = $notification_result;
                            
                            if (!$notification_result) {
                                // Get database error
                                $db_error = $this->db->error();
                                $debug_info['notification']['db_error'] = $db_error;
                            }
                        }
                    } catch (Exception $e) {
                        $debug_info['notification']['exception'] = $e->getMessage();
                    }
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed during like operation');
            }

            // Get updated likes count
            $updated_poll = $this->common->getdatabytable('polls', array('id' => $poll_id));
            if (!$updated_poll) {
                throw new Exception('Failed to fetch updated poll data after like');
            }

            $response = [
                'status' => 200,
                'message' => 'Poll liked successfully',
                'data' => array(
                    'is_liked' => true,
                    'likes_count' => (int)$updated_poll->likes_count,
                    'action' => 'liked'
                )
            ];
        }

        // Append debug info to response
        $response['debug_info'] = $debug_info;
        $this->output($response);

    } catch (Exception $e) {
        // Rollback transaction if active
        if ($this->db->trans_status() !== FALSE) {
            $this->db->trans_rollback();
        }

        $this->output([
            'status' => 500, 
            'message' => 'Failed to process like action',
            'error_details' => $e->getMessage(),
            'debug_info' => $debug_info // Include debug info even in error
        ]);
    }
}
}