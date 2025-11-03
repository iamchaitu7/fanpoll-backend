<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model
{

    public function insert(array $details, $table)
    {
        if ($this->db->insert($table, $details)) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    public function delete($id, $table)
    {
        $this->db->where('id', $id);
        $this->db->delete($table);
        return $this->db->affected_rows();
    }

    public function update($table_name, $data, $where)
    {
        $this->db->update($table_name, $data, $where);
        return $this->db->affected_rows();
    }

    // Get Review Details
    public function getnumrows($tableName, array $where)
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        }

        $return = $this->db->get()->num_rows();

        // echo $this->db->last_query();
        return $return;
    }


    public function getdatabytable($tableName, array $where = null, $index = 'id', $order = 'ASC')
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if ($where != null) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        }

        $this->db->order_by($index, $order);
        $return = $this->db->get()->row();

        return $return;
    }

    public function getdatabytableBinary($tableName, array $where = null)
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if ($where != null) {
            foreach ($where as $key => $value) {
                // Use BINARY keyword to make the comparison case-sensitive
                $this->db->where("BINARY `$key` =", $value, false);
            }
        }

        return $this->db->get()->row();
    }


    public function getdatabytableall($tableName, array $where = null, $index = 'id', $order = 'ASC')
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if ($where != null) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        }

        $this->db->order_by($index, $order);
        $return = $this->db->get()->result();

        return $return;
    }


    public function deleteWhere($tableName, array $where)
    {
        foreach ($where as $key => $value) {
            $this->db->where($key, $value);
        }
        $this->db->delete($tableName);
        return $this->db->affected_rows();
    }

    public function check_admin(array $post_data)
    {
        $sql = "select * from admin where (phone_number = ? or email = ?) and password = ?";
        $result = $this->db->query($sql, array(
            $post_data['username'],
            $post_data['username'],
            md5($post_data['userpassword'])
        ))->row();
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getdatabyid($tableName, $id)
    {
        $this->db->select("*");
        $this->db->from($tableName);
        $this->db->where('id', $id);
        $return = $this->db->get()->row();
        return $return;
    }


    public function get_following($user_id)
    {
        $this->db->select('u.id, u.uuid, u.full_name, u.profile_picture, u.bio, uf.created_at as followed_since');
        $this->db->from('users u');
        $this->db->join('user_follows uf', 'u.id = uf.following_id');
        $this->db->where('uf.follower_id', $user_id);
        $this->db->where('u.status', 'active');
        $this->db->order_by('uf.created_at', 'DESC');

        $following = $this->db->get()->result_array();
        return $following;
    }

    public function get_followers($user_id)
    {
        $this->db->select('u.id, u.uuid, u.full_name, u.profile_picture, u.bio, uf.created_at as followed_since');
        $this->db->from('users u');
        $this->db->join('user_follows uf', 'u.id = uf.follower_id');
        $this->db->where('uf.following_id', $user_id);
        $this->db->where('u.status', 'active');
        $this->db->order_by('uf.created_at', 'DESC');

        $followers = $this->db->get()->result_array();
        return $followers;
    }

    // Count methods for pagination
    public function get_active_polls_count($search = null, $hashtag = null)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');
        $this->db->where('p.status', 'active');
        $this->db->where('p.expires_at >', date('Y-m-d H:i:s'));

        if ($search) {
            $this->db->group_start();
            $this->db->like('p.title', $search);
            $this->db->or_like('p.description', $search);
            $this->db->group_end();
        }

        if ($hashtag) {
            $this->db->like('p.hashtags', $hashtag);
        }

        $result = $this->db->get()->row();
        return $result->count;
    }

    public function get_completed_polls_count()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');
        $this->db->where('p.status', 'active');
        $this->db->where('p.expires_at <=', date('Y-m-d H:i:s'));
        $this->db->order_by('p.expires_at', 'DESC');
        $result = $this->db->get()->row();
        return $result->count;
    }

    public function get_user_polls_count($user_id)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'active');

        $result = $this->db->get()->row();
        return $result->count;
    }

    public function get_poll_comments_count($poll_id)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('poll_comments');
        $this->db->where('poll_id', $poll_id);

        $result = $this->db->get()->row();
        return $result->count;
    }

    // Updated user polls method with pagination
    public function get_user_polls_paginated($user_id, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        $this->db->select('*');
        $this->db->from('polls');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'active');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }


    public function get_active_polls($page, $limit = 10, $search = '', $hashtag = '', $current_user_id = null)
    {
        $offset = ($page - 1) * $limit;

        // Build query with LEFT JOIN to check if current user is following poll author
        if ($current_user_id) {
            $this->db->select('p.*, u.full_name as creator_name, u.profile_picture as creator_avatar, 
                           IF(uf.follower_id IS NOT NULL, 1, 0) as is_following', false);
        } else {
            $this->db->select('p.*, u.full_name as creator_name, u.profile_picture as creator_avatar, 
                           0 as is_following', false);
        }

        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');

        // LEFT JOIN to check if current user follows the poll author
        if ($current_user_id) {
            $this->db->join('user_follows uf', 'u.id = uf.following_id AND uf.follower_id = ' . (int)$current_user_id, 'left');
        }

        $this->db->where('p.status', 'active');
        $this->db->where('p.expires_at >', date('Y-m-d H:i:s'));

        if ($search) {
            $this->db->group_start();
            $this->db->like('p.title', $search);
            $this->db->or_like('p.description', $search);
            $this->db->group_end();
        }

        if ($hashtag) {
            $this->db->like('p.hashtags', $hashtag);
        }

        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        $polls = $this->db->get()->result_array();
        return $polls;
    }

    public function get_completed_polls($page, $limit = 10, $current_user_id = null)
    {
        $offset = ($page - 1) * $limit;

        // Build query for expired polls with following status
        if ($current_user_id) {
            $this->db->select('p.*, u.full_name as creator_name, u.profile_picture as creator_avatar, 
                           IF(uf.follower_id IS NOT NULL, 1, 0) as is_following', false);
        } else {
            $this->db->select('p.*, u.full_name as creator_name, u.profile_picture as creator_avatar, 
                           0 as is_following', false);
        }

        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');

        // LEFT JOIN to check if current user follows the poll author
        if ($current_user_id) {
            $this->db->join('user_follows uf', 'u.id = uf.following_id AND uf.follower_id = ' . (int)$current_user_id, 'left');
        }

        $this->db->where('p.status', 'active');
        $this->db->where('p.expires_at <=', date('Y-m-d H:i:s'));
        $this->db->order_by('p.expires_at', 'DESC');
        $this->db->limit($limit, $offset);

        $polls = $this->db->get()->result_array();
        return $polls;
    }

    public function get_poll_by_id($poll_id)
    {
        $this->db->select('p.*, u.full_name as creator_name, u.profile_picture as creator_avatar');
        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');
        $this->db->where('p.id', $poll_id);
        $this->db->where('p.status', 'active');

        $poll = $this->db->get()->row_array();
        return $poll;
    }

    public function get_poll_options($poll_id)
    {
        $this->db->select('*');
        $this->db->from('poll_options');
        $this->db->where('poll_id', $poll_id);
        $this->db->order_by('option_order', 'ASC');
        $options = $this->db->get()->result_array();
        return $options;
    }

    public function get_poll_comments($poll_id, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        $this->db->select('c.*, u.full_name as user_name, u.profile_picture as user_avatar');
        $this->db->from('poll_comments c');
        $this->db->join('users u', 'c.user_id = u.id');
        $this->db->where('c.poll_id', $poll_id);
        $this->db->order_by('c.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        $comments = $this->db->get()->result_array();
        return $comments;
    }

    public function save_email_otp_details(array $details)
    {
        if ($this->db->insert('email_otp_login', $details)) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    public function getValidatedEmail($otp, $email, $date)
    {
        $this->db->select('*');
        $this->db->from('email_otp_login');
        $this->db->where('otp =', $otp);
        $this->db->where('email =', $email);
        $this->db->where('status =', '0');
        $this->db->where('DATE_ADD(created_time,INTERVAL 5 MINUTE) >', '"' . $date . '"', FALSE);
        $this->db->order_by('id', 'desc');
        $this->db->limit('1');
        $result = $this->db->get()->row();
        return $result;
    }


    public function increasePollLikesCount($poll_id, $increment = 1)
    {
        // Ensure increment is either 1 or -1
        if ($increment !== 1 && $increment !== -1) {
            return false; // Invalid increment value
        }

        // Update likes count in polls table
        $this->db->set('likes_count', 'likes_count + ' . (int)$increment, FALSE);
        $this->db->where('id', $poll_id);
        return $this->db->update('polls');
    }
}
