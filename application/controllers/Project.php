<?php

class Project extends CI_Controller {

  
  public function get_post_value($postName) {
	  $value = trim($this->input->post($postName));
	  return $value;
	}
	
	public function get_nilai_project() {
		$kodeProject = $this->get_post_value('kode_project');
		echo intval($this->db->get_where('project', array(
			'kode_project' => $kodeProject
		))->row_array()['kebutuhan_modal']);
	}
  
  public function get_nilai_project_2() {
    $code = $this->get_post_value('kode_project');
    $userID = intval($this->get_post_value('user_id'));
    $noAnggota = $this->db->get_where('nasabah', array(
        'user_id' => $userID
    ))->row_array()['no_anggota'];
    $total = 0;
    $query = $this->db->get_where('investor', array(
        'kode_project' => $code,
        'no_anggota' => $noAnggota
      ))->result_array();
    for ($i=0; $i<sizeof($query); $i++) {
      $total += intval($query[$i]['porsi_modal']);
    }
    echo $total;
  }
}
