<?php

class Common extends CI_Controller {

  
  private function get_post_value($postName) {
	  $value = trim($this->input->post($postName));
	  return $value;
	  
	}

public function delete_by_id() {
  $name = get_post_value('name');
  $id = intval(get_post_value('id'));
  $this->db->where('id', $id);
  $this->db->delete($name);
}

}
