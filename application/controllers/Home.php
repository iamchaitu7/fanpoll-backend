<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Common_model', 'common');
	}

	public function index()
	{
		echo "Welcome to Fan Poll World";
	}
}
