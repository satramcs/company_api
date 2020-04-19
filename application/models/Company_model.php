<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	public function save_details($table, $data)
	{
		$insert_query = $this->db->insert_string($table, $data);
		$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
		$insert = $this->db->query($insert_query);
		return $this->db->insert_id();
	}

	public function get_details_count($table)
	{
		$this->db->get($table)->num_rows();
	}

	public function get_table_row($table, $where, $select){
		if(!empty($where)){
			$get_res = $this->db->where($where);
		}
		if($select){
			$get_res = $this->db->select($select);
		}
		return $this->db->get($table)->row();
	}

}
