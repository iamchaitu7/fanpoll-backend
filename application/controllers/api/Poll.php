<?php
//Jai Shree Ram
defined('BASEPATH') or exit('No direct script access allowed');

class Poll extends CI_Controller
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
     * Create a new poll
     * POST /poll/create
     */
    public function create()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $post_data = $this->input->post(null, true);

        // Validate required fields
        if (empty($post_data['title'])) {
            $this->output(['status' => 400, 'message' => 'Poll title is required']);
            return;
        }

        if (empty($post_data['options']) || !is_array($post_data['options']) || count($post_data['options']) < 2) {
            $this->output(['status' => 400, 'message' => 'At least 2 poll options are required']);
            return;
        }

        if (empty($post_data['expires_in_days']) || $post_data['expires_in_days'] < 1 || $post_data['expires_in_days'] > 7) {
            $this->output(['status' => 400, 'message' => 'Expiry must be between 1-7 days']);
            return;
        }

        // Handle image upload if provided
        $image_path = null;
        if (!empty($_FILES['image']['name'])) {
            $upload_path = './uploads/poll_images/';
            $file = $_FILES['image'];

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

            // Generate unique filename
            $new_filename = uniqid() . '.' . $file_ext;
            $destination = $upload_path . $new_filename;

            // Create directory if it doesn't exist
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $image_path = $new_filename;
            } else {
                $this->output(['status' => 400, 'message' => 'Failed to save uploaded file.']);
                return;
            }
        }

        // Calculate expiry date
        $expires_at = date('Y-m-d H:i:s', strtotime('+' . $post_data['expires_in_days'] . ' days'));

        // Process hashtags to remove duplicates
        $hashtags = null;
        if (!empty($post_data['hashtags'])) {
            // Split by comma, trim whitespace, remove empty values, and get unique values
            $hashtags_array = array_filter(array_map('trim', explode(',', $post_data['hashtags'])));
            $unique_hashtags = array_unique($hashtags_array, SORT_STRING);
            // Convert back to comma-separated string
            $hashtags = implode(',', $unique_hashtags);
        }

        // Create poll
        $poll_data = array(
            'user_id' => $current_user_id,
            'title' => $post_data['title'],
            'description' => !empty($post_data['description']) ? $post_data['description'] : null,
            'url' => !empty($post_data['url']) ? $post_data['url'] : null,
            'image_path' => $image_path,
            'hashtags' => $hashtags,
            'expires_at' => $expires_at,
            'status' => 'active'
        );

        $poll_id = $this->common->insert($poll_data, 'polls');

        if (!$poll_id) {
            $this->output(['status' => 500, 'message' => 'Failed to create poll']);
            return;
        }

        // Create poll options
        foreach ($post_data['options'] as $index => $option_text) {
            if (!empty(trim($option_text))) {
                $option_data = array(
                    'poll_id' => $poll_id,
                    'option_text' => trim($option_text),
                    'option_order' => $index + 1
                );
                $this->common->insert($option_data, 'poll_options');
            }
        }

        $this->output(['status' => 200, 'message' => 'Poll created successfully', 'poll_id' => $poll_id]);
    }

    /**
     * Get active polls
     * GET /poll/active_polls
     */
    public function active_polls()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = 10;

        // Get search parameters
        $search = $this->input->get('search');
        $hashtag = $this->input->get('hashtag');

        // Get total count for pagination
        $total_count = $this->common->get_active_polls_count($search, $hashtag);

        $polls = $this->common->get_active_polls($page, $limit, $search, $hashtag, $current_user_id);

        // Format polls with options and user vote status
        $formatted_polls = array();
        foreach ($polls as $poll) {
            $formatted_poll = $this->format_poll_response($poll, $current_user_id, false);
            $formatted_polls[] = $formatted_poll;
        }

        $response = $this->create_pagination_response($formatted_polls, $page, $limit, $total_count);
        $this->output($response);
    }

    /**
     * Get completed polls
     * GET /poll/completed_polls
     */
    public function completed_polls()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = 10;

        // Get total count for pagination
        $total_count = $this->common->get_completed_polls_count();

        $polls = $this->common->get_completed_polls($page, $limit, $current_user_id);

        // Format polls with results
        $formatted_polls = array();
        foreach ($polls as $poll) {
            $formatted_poll = $this->format_poll_response($poll, $current_user_id, true);
            $formatted_polls[] = $formatted_poll;
        }

        $response = $this->create_pagination_response($formatted_polls, $page, $limit, $total_count);
        $this->output($response);
    }

    /**
     * View single poll
     * GET /poll/view/123
     */
    public function view($poll_id)
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        if (!$poll_id) {
            $this->output(['status' => 400, 'message' => 'Poll ID is required']);
            return;
        }

        // Get poll data
        $poll = $this->common->get_poll_by_id($poll_id);

        if (!$poll) {
            $this->output(['status' => 404, 'message' => 'Poll not found']);
            return;
        }

        // Check if poll is expired
        $is_expired = strtotime($poll['expires_at']) <= time();

        $formatted_poll = $this->format_poll_response($poll, $current_user_id, $is_expired);

        $this->output(['status' => 200, 'data' => $formatted_poll]);
    }

    /**
     * Vote on a poll
     * POST /poll/vote
     */
    public function vote()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $post_data = $this->input->post(null, true);

        if (empty($post_data['poll_id']) || empty($post_data['option_id'])) {
            $this->output(['status' => 400, 'message' => 'Poll ID and Option ID are required']);
            return;
        }

        $poll_id = $post_data['poll_id'];
        $option_id = $post_data['option_id'];

        // Check if poll exists and is active
        $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'status' => 'active'));

        if (!$poll) {
            $this->output(['status' => 404, 'message' => 'Poll not found']);
            return;
        }

        // Check if poll is expired
        if (strtotime($poll->expires_at) <= time()) {
            $this->output(['status' => 400, 'message' => 'Poll has expired']);
            return;
        }

        // Check if option exists
        $option = $this->common->getdatabytable('poll_options', array('id' => $option_id, 'poll_id' => $poll_id));

        if (!$option) {
            $this->output(['status' => 404, 'message' => 'Poll option not found']);
            return;
        }

        // Check if user has already voted
        $existing_vote = $this->common->getdatabytable('poll_votes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

        if ($existing_vote) {
            $this->output(['status' => 400, 'message' => 'You have already voted on this poll']);
            return;
        }

        // Cast vote
        $vote_data = array(
            'poll_id' => $poll_id,
            'option_id' => $option_id,
            'user_id' => $current_user_id,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        );

        $result = $this->common->insert($vote_data, 'poll_votes');

        if ($result) {
            // Create notification for poll owner
            $this->load->model('Notification_model', 'notification');
            $this->notification->create_vote_notification($poll_id, $current_user_id, $poll->user_id);

            // Get updated poll with results
            $updated_poll = $this->common->get_poll_by_id($poll_id);
            $formatted_poll = $this->format_poll_response($updated_poll, $current_user_id, false);

            $this->output([
                'status' => 200,
                'message' => 'Vote cast successfully',
                'poll' => $formatted_poll
            ]);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to cast vote']);
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

        // Delete vote
        $result = $this->common->deleteWhere('poll_votes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

        if ($result) {
            // Get updated poll without user vote
            $updated_poll = $this->common->get_poll_by_id($poll_id);
            $formatted_poll = $this->format_poll_response($updated_poll, $current_user_id, false);

            $this->output([
                'status' => 200,
                'message' => 'Vote removed successfully',
                'poll' => $formatted_poll
            ]);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to remove vote']);
        }
    }

    /**
     * Delete a poll (only by creator)
     * POST /poll/delete
     */
    public function delete()
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

        // Check if poll exists and user is the creator
        $poll = $this->common->getdatabytable('polls', array('id' => $poll_id, 'user_id' => $current_user_id, 'status' => 'active'));

        if (!$poll) {
            $this->output(['status' => 404, 'message' => 'Poll not found or you are not authorized to delete it']);
            return;
        }

        // Soft delete poll
        $update_data = array(
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s')
        );

        $result = $this->common->update('polls', $update_data, array('id' => $poll_id));

        if ($result) {
            $this->output(['status' => 200, 'message' => 'Poll deleted successfully']);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to delete poll']);
        }
    }

    /**
     * Format poll response with options and user vote status
     */
    private function format_poll_response($poll, $user_id, $show_results = false)
    {
        // Get poll options
        $options = $this->common->get_poll_options($poll['id']);

        // Check if user has voted
        $user_vote = null;
        if ($user_id) {
            $vote = $this->common->getdatabytable('poll_votes', array('poll_id' => $poll['id'], 'user_id' => $user_id));
            $user_vote = $vote ? $vote->option_id : null;
        }


        // Check if user has liked this poll
        $user_liked = false;
        if ($user_id) {
            $like = $this->common->getdatabytable('poll_likes', array('poll_id' => $poll['id'], 'user_id' => $user_id));
            $user_liked = !empty($like);
        }

        // Format options with vote data
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

        return array(
            'id' => (int)$poll['id'],
            'uuid' => $poll['uuid'],
            'title' => $poll['title'],
            'description' => $poll['description'],
            'url' => $poll['url'],
            'image_url' => $poll['image_path'] ? base_url('uploads/poll_images/' . $poll['image_path']) : null,
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
                'avatar'        => base_url('uploads/profile_pictures/' . $poll['creator_avatar']),
                'is_following'  => isset($poll['is_following']) ? (bool)$poll['is_following'] : false
            ),
            'options' => $formatted_options,
            'is_own_poll' => $poll['user_id'] == $user_id
        );
    }

    public function user_polls($user_id = null)
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        if (!$user_id) {
            $user_id = $current_user_id; // If no user ID provided, use current user
        }

        // Get pagination parameters
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 10;

        // Get total count for pagination
        $total_count = $this->common->get_user_polls_count($user_id);

        $polls = $this->common->get_user_polls_paginated($user_id, $page, $limit);

        if (!$polls) {
            $response = $this->create_pagination_response([], $page, $limit, 0);
            $response['message'] = 'No polls found for this user';
            $this->output($response);
            return;
        }

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

        //check poll is not expired
        if (strtotime($poll->expires_at) <= time()) {
            $this->output(['status' => 400, 'message' => 'Cannot comment on expired poll']);
            return;
        }

        // Insert comment
        $comment_data = array(
            'poll_id' => $poll_id,
            'user_id' => $current_user_id,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        );

        $result = $this->common->insert($comment_data, 'poll_comments');
        if ($result) {
            // Create notification for poll owner
            $this->load->model('Notification_model', 'notification');
            $this->notification->create_comment_notification($poll_id, $current_user_id, $poll->user_id, $result);

            $this->output([
                'status' => 200,
                'message' => 'Comment posted successfully',
                'data' => array(
                    'comment_id' => $result,
                    'comment' => $comment,
                    'user_id' => $current_user_id,
                    'created_at' => date('Y-m-d H:i:s')
                )
            ]);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to post comment']);
        }
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

        // Check if user has already liked this poll
        $existing_like = $this->common->getdatabytable('poll_likes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

        if ($existing_like) {
            // Unlike - remove the like
            $result = $this->common->deleteWhere('poll_likes', array('poll_id' => $poll_id, 'user_id' => $current_user_id));

            if ($result) {
                // Decrease likes count in polls table
                $this->common->increasePollLikesCount($poll_id, -1);

                // Get updated likes count
                $updated_poll = $this->common->getdatabytable('polls', array('id' => $poll_id));

                $this->output([
                    'status' => 200,
                    'message' => 'Poll unliked successfully',
                    'data' => array(
                        'is_liked' => false,
                        'likes_count' => (int)$updated_poll->likes_count,
                        'action' => 'unliked'
                    )
                ]);
            } else {
                $this->output(['status' => 500, 'message' => 'Failed to unlike poll']);
            }
        } else {
            // Like - add the like
            $like_data = array(
                'poll_id' => $poll_id,
                'user_id' => $current_user_id
            );

            $result = $this->common->insert($like_data, 'poll_likes');

            if ($result) {
                // Increase likes count in polls table
                $this->common->increasePollLikesCount($poll_id);

                // Create notification for poll owner (if not liking own poll)
                if ($poll->user_id != $current_user_id) {
                    $this->load->model('Notification_model', 'notification');
                    $this->notification->create_like_notification($poll_id, $current_user_id, $poll->user_id);
                }

                // Get updated likes count
                $updated_poll = $this->common->getdatabytable('polls', array('id' => $poll_id));

                $this->output([
                    'status' => 200,
                    'message' => 'Poll liked successfully',
                    'data' => array(
                        'is_liked' => true,
                        'likes_count' => (int)$updated_poll->likes_count,
                        'action' => 'liked'
                    )
                ]);
            } else {
                $this->output(['status' => 500, 'message' => 'Failed to like poll']);
            }
        }
    }
}
