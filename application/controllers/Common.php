<?php

class Common extends CI_Controller {

public function delete_by_id() {
  $name = $this->input->post('name');
  $id = intval($this->input->post('id'));
  $this->db->where('id', $id);
  $this->db->delete($name);
}

}