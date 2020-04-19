<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->company_site = 'http://www.mycorporateinfo.com/';
		$this->error_codes = [1 => 200, 2 => 402, 3 => 403, 4 => 405, 5 => 500];
	}

	public function save_industries()
	{
		$res = $this->parselib->save_industry_details();
		$this->api_response($res['status'], $res['message']);
	}

	public function save_companies_by_section()
	{
		$this->parselib->check_industries();
		$section = trim($this->input->get('section'));
		$page = (int)$this->input->get('page');
		$page = (is_integer($page) && $page > 0) ? $page : 1;
		if($section != ""){
			$res = $this->parselib->save_section_details($section, $page);
			$this->api_response($res['status'], $res['message']);
		}else{
			$this->api_response(4, 'section is required');
		}
	}

	public function save_company()
	{
		$company_uri = trim($this->input->get('company_uri'));
		if($company_uri != ""){
			$this->parselib->check_industries();
			$res = $this->parselib->save_company_details($company_uri);
			$this->api_response($res['status'], $res['message']);
		}else{
			$this->api_response(4, 'company_uri is required');
		}
	}

	private function api_response($status, $msg, $data=[]){
		set_status_header($this->error_codes[$status]);
		echo json_encode(['status' => $status, 'message' => $msg] + $data);
		exit;
	}
}
