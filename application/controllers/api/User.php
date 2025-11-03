<?php
//Jai Shree Ram
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
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
     * Get user profile by user ID
     * GET /user/user_profile/123 or GET /user/user_profile (for own profile)
     */
    public function user_profile($user_id = null)
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        // If no user_id provided, show current user's profile
        if (!$user_id) {
            $user_id = $current_user_id;
        }

        // Get user data
        $where_data = array(
            'id' => $user_id,
            'status' => 'active'
        );

        $user_data = $this->common->getdatabytable('users', $where_data);

        if (!$user_data) {
            $this->output(['status' => 404, 'message' => 'User not found']);
            return;
        }

        // Prepare response data
        $response_data = array();
        $response_data['id'] = $user_data->id;
        $response_data['uuid'] = $user_data->uuid;
        $response_data['full_name'] = $user_data->full_name;
        $response_data['email'] = $user_data->email;
        $response_data['bio'] = $user_data->bio;
        $response_data['profile_picture'] = base_url('uploads/profile_pictures/' . $user_data->profile_picture);
        $response_data['auth_method'] = $user_data->auth_method;

        // Format member since date
        $member_since = new DateTime($user_data->created_at);
        $response_data['member_since'] = $member_since->format('F j, Y');

        // Social counts
        $response_data['followers_count'] = (int)$user_data->followers_count;
        $response_data['following_count'] = (int)$user_data->following_count;
        $response_data['polls_created_count'] = (int)$user_data->polls_created_count;
        $response_data['total_votes_cast'] = (int)$user_data->total_votes_cast;

        // Check if current user is following this profile (only if viewing someone else's profile)
        $response_data['is_following'] = false;
        $response_data['is_own_profile'] = ($current_user_id == $user_id);

        if ($current_user_id != $user_id) {
            $follow_check = array(
                'follower_id' => $current_user_id,
                'following_id' => $user_id
            );
            $is_following = $this->common->getdatabytable('user_follows', $follow_check);
            $response_data['is_following'] = !empty($is_following);
        }

        $this->output(['status' => 200, 'data' => $response_data]);
    }

    /**
     * Follow a user
     * POST /user/follow_user
     * Body: { "user_id": 123 }
     */
    public function follow_user()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $post_data = $this->input->post(null, true);

        if (empty($post_data['user_id'])) {
            $this->output(['status' => 400, 'message' => 'User ID is required']);
            return;
        }

        $target_user_id = $post_data['user_id'];

        // Check if trying to follow themselves
        if ($current_user_id == $target_user_id) {
            $this->output(['status' => 400, 'message' => 'You cannot follow yourself']);
            return;
        }

        // Check if target user exists and is active
        $target_user = $this->common->getdatabytable('users', array(
            'id' => $target_user_id,
            'status' => 'active'
        ));

        if (!$target_user) {
            $this->output(['status' => 404, 'message' => 'User not found']);
            return;
        }

        // Check if already following
        $existing_follow = $this->common->getdatabytable('user_follows', array(
            'follower_id' => $current_user_id,
            'following_id' => $target_user_id
        ));

        if ($existing_follow) {
            $this->output(['status' => 400, 'message' => 'You are already following this user']);
            return;
        }

        // Create follow relationship
        $follow_data = array(
            'follower_id' => $current_user_id,
            'following_id' => $target_user_id
        );

        $result = $this->common->insert($follow_data, 'user_follows');

        if ($result) {
            $this->output(['status' => 200, 'message' => 'User followed successfully']);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to follow user']);
        }
    }

    /**
     * Unfollow a user
     * POST /user/unfollow_user
     * Body: { "user_id": 123 }
     */
    public function unfollow_user()
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        $post_data = $this->input->post(null, true);

        if (empty($post_data['user_id'])) {
            $this->output(['status' => 400, 'message' => 'User ID is required']);
            return;
        }

        $target_user_id = $post_data['user_id'];

        // Check if trying to unfollow themselves
        if ($current_user_id == $target_user_id) {
            $this->output(['status' => 400, 'message' => 'You cannot unfollow yourself']);
            return;
        }

        // Check if currently following
        $existing_follow = $this->common->getdatabytable('user_follows', array(
            'follower_id' => $current_user_id,
            'following_id' => $target_user_id
        ));

        if (!$existing_follow) {
            $this->output(['status' => 400, 'message' => 'You are not following this user']);
            return;
        }

        // Remove follow relationship
        $where_data = array(
            'follower_id' => $current_user_id,
            'following_id' => $target_user_id
        );

        $result = $this->common->deleteWhere('user_follows', $where_data);

        if ($result) {
            $this->output(['status' => 200, 'message' => 'User unfollowed successfully']);
        } else {
            $this->output(['status' => 500, 'message' => 'Failed to unfollow user']);
        }
    }

    /**
     * Get followers list
     * GET /user/followers/123 or GET /user/followers (for own followers)
     */
    public function followers($user_id = null)
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        // If no user_id provided, show current user's followers
        if (!$user_id) {
            $user_id = $current_user_id;
        }

        // Get followers with user details
        $followers = $this->common->get_followers($user_id);

        // Format response
        foreach ($followers as &$follower) {
            $follower['profile_picture'] = base_url('uploads/profile_pictures/' . $follower['profile_picture']);
            $follower['followed_since'] = date('Y-m-d H:i:s', strtotime($follower['followed_since']));
        }

        $this->output([
            'status' => 200,
            'data' => $followers,
            'count' => count($followers)
        ]);
    }

    /**
     * Get following list
     * GET /user/following/123 or GET /user/following (for own following)
     */
    public function following($user_id = null)
    {
        $current_user_id = $this->get_authenticated_user();

        if (!$current_user_id) {
            $this->output(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }

        // If no user_id provided, show current user's following
        if (!$user_id) {
            $user_id = $current_user_id;
        }

        // Get following with user details
        $following = $this->common->get_following($user_id);

        // Format response
        foreach ($following as &$user) {
            $user['profile_picture'] = base_url('uploads/profile_pictures/' . $user['profile_picture']);
            $user['followed_since'] = date('Y-m-d H:i:s', strtotime($user['followed_since']));
        }

        $this->output([
            'status' => 200,
            'data' => $following,
            'count' => count($following)
        ]);
    }
}
