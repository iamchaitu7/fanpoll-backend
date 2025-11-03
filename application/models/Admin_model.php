<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model extends CI_Model
{
    // Dashboard Statistics Methods

    public function get_total_users()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('users');
        $this->db->where('status !=', 'deleted');
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_total_polls()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls');
        $this->db->where('status', 'active');
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_total_votes()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('poll_votes');
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_total_comments()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('poll_comments');
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_active_polls_count()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls');
        $this->db->where('status', 'active');
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    // Growth Calculation Methods

    public function get_users_growth()
    {
        $current_month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));

        $current_count = $this->get_monthly_users_count(date('m'), date('Y'));
        $last_count = $this->get_monthly_users_count(date('m', strtotime('-1 month')), date('Y', strtotime('-1 month')));

        if ($last_count == 0) return 100;
        return round((($current_count - $last_count) / $last_count) * 100, 1);
    }

    public function get_polls_growth()
    {
        $current_count = $this->get_monthly_polls_count(date('m'), date('Y'));
        $last_count = $this->get_monthly_polls_count(date('m', strtotime('-1 month')), date('Y', strtotime('-1 month')));

        if ($last_count == 0) return 100;
        return round((($current_count - $last_count) / $last_count) * 100, 1);
    }

    public function get_votes_growth()
    {
        $current_count = $this->get_monthly_votes_count(date('m'), date('Y'));
        $last_count = $this->get_monthly_votes_count(date('m', strtotime('-1 month')), date('Y', strtotime('-1 month')));

        if ($last_count == 0) return 100;
        return round((($current_count - $last_count) / $last_count) * 100, 1);
    }

    public function get_active_polls_change()
    {
        $today = $this->get_daily_active_polls(date('Y-m-d'));
        $yesterday = $this->get_daily_active_polls(date('Y-m-d', strtotime('-1 day')));

        if ($yesterday == 0) return 100;
        return round((($today - $yesterday) / $yesterday) * 100, 1);
    }

    // Monthly Count Methods

    public function get_monthly_users_count($month, $year)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('users');
        $this->db->where('MONTH(created_at)', $month);
        $this->db->where('YEAR(created_at)', $year);
        $this->db->where('status !=', 'deleted');
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_monthly_polls_count($month, $year)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls');
        $this->db->where('MONTH(created_at)', $month);
        $this->db->where('YEAR(created_at)', $year);
        $this->db->where('status', 'active');
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_monthly_votes_count($month, $year)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('poll_votes');
        $this->db->where('MONTH(created_at)', $month);
        $this->db->where('YEAR(created_at)', $year);
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_monthly_comments_count($month, $year)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('poll_comments');
        $this->db->where('MONTH(created_at)', $month);
        $this->db->where('YEAR(created_at)', $year);
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    private function get_daily_active_polls($date)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls');
        $this->db->where('status', 'active');
        $this->db->where('expires_at >', $date . ' 23:59:59');
        $this->db->where('created_at <=', $date . ' 23:59:59');
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    // User Activity Methods

    public function get_active_users_count()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('users');
        $this->db->where('status', 'active');
        $this->db->where('updated_at >', date('Y-m-d H:i:s', strtotime('-30 days')));
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_inactive_users_count()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('users');
        $this->db->where('status', 'active');
        $this->db->where('updated_at <=', date('Y-m-d H:i:s', strtotime('-30 days')));
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_new_users_count()
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('users');
        $this->db->where('status', 'active');
        $this->db->where('created_at >', date('Y-m-d H:i:s', strtotime('-7 days')));
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    // Recent Activity Methods

    public function get_recent_polls($limit = 5)
    {
        $this->db->select('p.id, p.title, p.created_at, p.total_votes, u.full_name as creator_name');
        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');
        $this->db->where('p.status', 'active');
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function get_top_users($limit = 5)
    {
        $this->db->select('u.id, u.full_name, u.email, u.profile_picture, u.polls_created_count as polls_created, u.total_votes_cast as total_votes');
        $this->db->from('users u');
        $this->db->where('u.status', 'active');
        $this->db->order_by('u.polls_created_count', 'DESC');
        $this->db->order_by('u.total_votes_cast', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    // Poll Management Methods

    public function get_polls_count($search = null, $status = null)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');

        if ($status && $status !== 'all') {
            $this->db->where('p.status', $status);
        } else {
            $this->db->where('p.status !=', 'deleted');
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('p.title', $search);
            $this->db->or_like('p.description', $search);
            $this->db->or_like('u.full_name', $search);
            $this->db->group_end();
        }

        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_polls_paginated($page = 1, $limit = 20, $search = null, $status = null)
    {
        $offset = ($page - 1) * $limit;

        $this->db->select('p.*, u.full_name as creator_name, u.email as creator_email');
        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');

        if ($status && $status !== 'all') {
            $this->db->where('p.status', $status);
        } else {
            $this->db->where('p.status !=', 'deleted');
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('p.title', $search);
            $this->db->or_like('p.description', $search);
            $this->db->or_like('u.full_name', $search);
            $this->db->group_end();
        }

        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    // User Management Methods

    public function get_users_count($search = null, $status = null)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('users');

        if ($status && $status !== 'all') {
            $this->db->where('status', $status);
        } else {
            $this->db->where('status !=', 'deleted');
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('full_name', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }

        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function get_users_paginated($page = 1, $limit = 20, $search = null, $status = null)
    {
        $offset = ($page - 1) * $limit;

        $this->db->select('*');
        $this->db->from('users');

        if ($status && $status !== 'all') {
            $this->db->where('status', $status);
        } else {
            $this->db->where('status !=', 'deleted');
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('full_name', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }

        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    // Analytics Methods

    public function get_poll_analytics($poll_id)
    {
        // Get poll details with vote counts per option
        $this->db->select('p.*, u.full_name as creator_name');
        $this->db->from('polls p');
        $this->db->join('users u', 'p.user_id = u.id');
        $this->db->where('p.id', $poll_id);
        $poll = $this->db->get()->row();

        if (!$poll) return false;

        // Get options with vote counts
        $this->db->select('po.*, COUNT(pv.id) as vote_count');
        $this->db->from('poll_options po');
        $this->db->join('poll_votes pv', 'po.id = pv.option_id', 'left');
        $this->db->where('po.poll_id', $poll_id);
        $this->db->group_by('po.id');
        $this->db->order_by('po.option_order');
        $options = $this->db->get()->result();

        return array(
            'poll' => $poll,
            'options' => $options
        );
    }

    public function get_trending_hashtags($limit = 10)
    {
        $this->db->select('hashtags, COUNT(*) as usage_count');
        $this->db->from('polls');
        $this->db->where('hashtags IS NOT NULL');
        $this->db->where('hashtags !=', '');
        $this->db->where('status', 'active');
        $this->db->where('created_at >', date('Y-m-d H:i:s', strtotime('-30 days')));
        $this->db->group_by('hashtags');
        $this->db->order_by('usage_count', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    public function get_poll_comments_detailed($poll_id, $limit = 50)
    {
        $this->db->select('pc.*, u.full_name, u.email, u.profile_picture');
        $this->db->from('poll_comments pc');
        $this->db->join('users u', 'pc.user_id = u.id');
        $this->db->where('pc.poll_id', $poll_id);
        $this->db->order_by('pc.created_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    public function get_poll_voting_timeline($poll_id)
    {
        $this->db->select('DATE(created_at) as vote_date, COUNT(*) as vote_count');
        $this->db->from('poll_votes');
        $this->db->where('poll_id', $poll_id);
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('vote_date', 'ASC');

        return $this->db->get()->result();
    }

    public function get_poll_engagement_data($poll_id)
    {
        // Get poll basic data
        $poll = $this->db->get_where('polls', array('id' => $poll_id))->row();

        if (!$poll) return false;

        // Calculate engagement metrics
        $total_votes = $this->db->get_where('poll_votes', array('poll_id' => $poll_id))->num_rows();
        $total_comments = $this->db->get_where('poll_comments', array('poll_id' => $poll_id))->num_rows();

        // Get unique voters
        $this->db->select('COUNT(DISTINCT user_id) as unique_voters');
        $this->db->from('poll_votes');
        $this->db->where('poll_id', $poll_id);
        $unique_voters = $this->db->get()->row()->unique_voters;

        // Calculate engagement rate (assuming views tracking exists)
        $engagement_rate = $total_votes > 0 ? ($total_votes / max(1, $total_votes)) * 100 : 0;

        return array(
            'total_votes' => $total_votes,
            'total_comments' => $total_comments,
            'unique_voters' => $unique_voters,
            'engagement_rate' => round($engagement_rate, 2)
        );
    }


    public function get_poll_hourly_votes($poll_id)
    {
        $this->db->select('HOUR(created_at) as vote_hour, COUNT(*) as vote_count');
        $this->db->from('poll_votes');
        $this->db->where('poll_id', $poll_id);
        $this->db->group_by('HOUR(created_at)');
        $this->db->order_by('vote_hour', 'ASC');

        return $this->db->get()->result();
    }

    public function get_poll_daily_votes($poll_id)
    {
        $this->db->select('DATE(created_at) as vote_date, COUNT(*) as vote_count');
        $this->db->from('poll_votes');
        $this->db->where('poll_id', $poll_id);
        $this->db->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('vote_date', 'ASC');

        return $this->db->get()->result();
    }

    public function get_poll_demographics($poll_id)
    {
        // Get votes by auth method
        $this->db->select('u.auth_method, COUNT(*) as count');
        $this->db->from('poll_votes pv');
        $this->db->join('users u', 'pv.user_id = u.id');
        $this->db->where('pv.poll_id', $poll_id);
        $this->db->group_by('u.auth_method');

        return $this->db->get()->result();
    }
    public function get_poll_geographic_data($poll_id)
    {
        // This would require storing user location data
        // For now, return empty array or implement based on IP tracking
        return array();
    }
}
