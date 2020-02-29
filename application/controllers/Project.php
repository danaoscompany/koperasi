<?php

class Project extends CI_Controller {
  
  public function get_nilai_project() {
    $code = $this->input->post('kode_project');
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