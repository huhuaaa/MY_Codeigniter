<?php
class user_model extends CI_Model{

	function get_list($where){
		return $this->db->where($where)->from('users')->get()->result_array();
	}
}