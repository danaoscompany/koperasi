<?php

class Project extends CI_Controller {

  
  private function get_post_value($postName) {
	  $value = trim(get_post_value($postName));
	  return $value;
	  
	}
  
  public function get_nilai_project() {
    $code = get_post_value('kode_project');
    $total = 0;
    $query = $this->db->get_where('investor', array(
        'kode_project' => $code
      ))->result_array();
    for ($i=0; $i<sizeof($query); $i++) {
      $total += intval($query[$i]['porsi_modal']);
    }
    echo $total;
  }
}
