<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['pre_controller'] = array(
    'class' => 'Cors_hook',
    'function' => 'handle_cors',
    'filename' => 'Cors_hook.php',
    'filepath' => 'hooks'
);