<?php

function is_loggedin_user()
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_user');
    if ($user) {
        return true;
    } else {
        return false;
    }
}

function get_userdetails($uid = null)
{
    if ($uid == null) {
        $uid = get_user_sessiondata('id');
    }

    $CI = &get_instance();
    $CI->load->model('Common_model', 'common');
    $where = array(
        'id' => $uid
    );
    $user = $CI->common->getdatabytable('users', $where);
    if (!empty($user)) {
        return $user;
    } else {
        return null;
    }
}

function get_user_sessiondata($row)
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_user');
    if ($user) {
        // print_r($user);
        return $user->$row;
    } else {
        return false;
    }
}


function is_loggedin_admin()
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_admin');
    if ($user) {
        return true;
    } else {
        return false;
    }
}

function get_admindetails($uid = null)
{
    if ($uid == null) {
        $uid = get_admin_sessiondata('id');
    }

    $CI = &get_instance();
    $CI->load->model('Common_model', 'common');
    $where = array(
        'id' => $uid
    );
    $user = $CI->common->getdatabytable('admin', $where);
    if (!empty($user)) {
        return $user;
    } else {
        return null;
    }
}

function get_admin_sessiondata($row)
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_admin');
    if ($user) {
        // print_r($user);
        return $user->$row;
    } else {
        return false;
    }
}

function character_limiter($str, $n = 500, $end_char = '...')
{
    if (strlen($str) < $n) {
        return $str;
    }
    $str = preg_replace('/\s+?(\S+)?$/', '', substr($str, 0, $n));
    return rtrim($str) . $end_char;
}


function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . ($diff->$k > 1 ? $v . 's' : $v);
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    return empty($string) ? 'just now' : implode(', ', $string) . ' ago';
}