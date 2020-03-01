<?php

class Common extends CI_Controller {

  
  public function get_post_value($postName) {
	  $value = trim($this->input->post($postName));
	  return $value;
	}

public function delete_by_id() {
  $name = $this->get_post_value('name');
  $id = intval($this->get_post_value('id'));
  $this->db->where('id', $id);
  $this->db->delete($name);
}

}
