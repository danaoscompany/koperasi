<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

	
	public function index() {
		echo "Halo dunia";
	}
	
	public function get_post_value($postName) {
	  $value = trim($this->input->post($postName));
	  return $value;
	}
	
	public function save_terms() {
	    $terms = $this->get_post_value('terms');
	    $query = $this->db->get('terms')->result_array();
	    if (sizeof($query) > 0) {
	        $this->db->update('terms', array(
	        	'terms' => $terms
	    	));
	    } else {
	    	$this->db->insert('terms', array(
	    		'terms' => $terms
	    	));
	    }
	}
	
	public function testname() {
	  echo $this->get_post_value('name');
	}
	
	public function delete_by_id_name() {
	  $name = $this->get_post_value('name');
	  $idName = $this->get_post_value('id_name');
	  $id = $this->get_post_value('id');
	  $this->db->where($idName, $id);
	  $this->db->delete($name);
	}
	
	public function upload_image() {
      $config['upload_path'] = './userdata/';
      $config['allowed_types'] = '*';
      $this->load->library('upload', $config);
      if ($this->upload->do_upload('file')) {
        echo $this->upload->data('file_name');
      }
	}
	
	public function reset_password() {
  $email = $this->get_post_value('email');
  $password = $this->get_post_value('password');
  $newPassword = $this->get_post_value('new_password');
  $users = $this->db->get_where('users', array(
      'email' => $email
    ));
  if ($users->num_rows() > 0) {
    $user = $users->row_array();
    if (password_verify($password, $user['password'])) {
      $this->db->where('email', $email);
      $this->db->update('users', array(
          'password' => password_hash($newPassword, PASSWORD_BCRYPT)
        ));
      echo 1;
    } else {
      echo -1;
    }
  }
}
	
	public function check_email() {
	    $email = $this->get_post_value('email');
	    $query = $this->db->get_where('users', array(
	        'email' => $email
	    ));
	    echo $query->num_rows();
	}
	
	public function send_confirmation_code() {
    $email = $this->get_post_value('email');
  $this->load->library('email');
$code = mt_rand(100000, 999999);
$message = 'Mohon masukkan kode berikut di layar yang tersedia untuk mengatur ulang kata sandi Anda: <b>' . $code . '</b>';
// prepare email
$this->email
    ->from('admin@puputgrosir.org', 'PuputGrosir')
    ->to($email)
    ->subject('Konfirmasi Email')
    ->message($message)
    ->set_mailtype('html');

// send email
$this->email->send();
echo $code;
}
	
	public function get_saldo() {
    $userID = intval($this->get_post_value('user_id'));
    $month = intval($this->get_post_value('month'));
    $year = intval($this->get_post_value('year'));
  $noAnggota = $this->db->get_where('nasabah', array(
      'user_id' => $userID
    ))->row_array()['no_anggota'];
  $query = $this->db->get_where('nisbah', array(
      'no_anggota' => $noAnggota
    ))->result_array();
    $totalLaba = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $year2 = intval($query[$i]['tahun']);
      $totalLaba += intval($query[$i]['total_nisbah']);
      /*for ($j=0; $j<12; $j++) {
        $l = intval($query[$i]['nisbah_' . str_pad($j+1, 2, '0', STR_PAD_LEFT)]);
        $totalLaba += $l;
        if (($month-1) == $j && $year == $year2) {
          break 2;
        }
      }*/
    }
    $saldo = $totalLaba;
    // Get tabungan
    $query = $this->db->get_where('tabungan', array(
        'user_id' => $userID,
        'tipe' => 1
      ))->result_array();
    $totalTabungan = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $kodeTransaksi = $query[$i]['kode_trans'];
      $dibayar = intval($this->db->get_where('riwayat', array(
          'id_pembayaran' => $kodeTransaksi
        ))->row_array()['dibayar']);
      if ($dibayar == 1) {
      $debit = intval($query[$i]['debet']);
      $credit = intval($query[$i]['credit']);
      if ($debit != 0 && $credit == 0) {
        $totalTabungan += $debit;
      }
      }
    }
    $saldo += $totalTabungan;
    
    // Get penarikan
    $query = $this->db->get_where('withdraw', array(
        'user_id' => $userID
      ))->result_array();
    $totalPenarikan = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $totalPenarikan += intval($query[$i]['amount']);
    }
    $saldo -= $totalPenarikan;
    echo $saldo;
}










public function get_saldo_tersedia() {
    $userID = intval($this->get_post_value('user_id'));
    $month = intval($this->get_post_value('month'));
    $year = intval($this->get_post_value('year'));
    $noAnggota = $this->db->get_where('nasabah', array(
      'user_id' => $userID
    ))->row_array()['no_anggota'];
  $query = $this->db->get_where('nisbah', array(
      'no_anggota' => $noAnggota
    ))->result_array();
    $totalLaba = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $year2 = intval($query[$i]['tahun']);
      $totalLaba += intval($query[$i]['total_nisbah']);
      /*for ($j=0; $j<12; $j++) {
        $l = intval($query[$i]['nisbah_' . str_pad($j+1, 2, '0', STR_PAD_LEFT)]);
        $totalLaba += $l;
        if (($month-1) == $j && $year == $year2) {
          break 2;
        }
      }*/
    }
    $saldo = $totalLaba;
    // Get tabungan
    $query = $this->db->get_where('tabungan', array(
        'user_id' => $userID,
        'tipe' => 1
      ))->result_array();
    $totalTabungan = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $kodeTransaksi = $query[$i]['kode_trans'];
      $dibayar = intval($this->db->get_where('riwayat', array(
          'id_pembayaran' => $kodeTransaksi
        ))->row_array()['dibayar']);
      if ($dibayar == 1) {
      $debit = intval($query[$i]['debet']);
      $credit = intval($query[$i]['credit']);
      if ($debit != 0 && $credit == 0) {
        $totalTabungan += $debit;
      }
      }
    }
    $saldo += $totalTabungan;
    
    // Get penarikan
    $query = $this->db->get_where('withdraw', array(
        'user_id' => $userID
      ))->result_array();
    $totalPenarikan = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $totalPenarikan += intval($query[$i]['amount']);
    }
    $saldo -= $totalPenarikan;
    
    // Pendapatan 6 bulan terakhir
    $lastBalance = 0;
    for ($i=0; $i<6; $i++) {
      $lastBalance += intval($this->db->get_where('nisbah', array(
          'no_anggota' => $noAnggota,
          'tahun' => $year
        ))->row_array()['nisbah_' . str_pad($month, 2, '0', STR_PAD_LEFT)]);
      $month--;
    }
    $saldo -= $lastBalance;
    echo $saldo;
}
	
	function get_laba($userID, $month, $year) {
	    $users = $this->db->get_where('nasabah', array(
	        'user_id' => $userID
	    ));
	    if ($users->num_rows() > 0) {
	        $user = $users->row_array();
	        $kodeProject = $user['kode_project'];
	        $noAnggota = $user['no_anggota'];
	        $row = $this->db->get_where('nisbah', array(
	            'no_anggota' => $noAnggota,
	            'tahun' => "" . $year
	        ))->row_array();
	        $laba = intval($row['laba_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '']);
	        $laba = 40*$laba/100;
	        $porsiModal = intval($this->db->get_where('investor', array(
	            'no_anggota' => $noAnggota
	        ))->row_array()['porsi_modal']);
	        $totalModal = intval($this->db->get_where('project', array(
	            'kode_project' => $kodeProject
	        ))->row_array()['kebutuhan_modal']);
	        $percentage = $porsiModal*100/$totalModal;
	        $amount = intval($percentage*$laba);
	        return $amount;
	    }
	    return 0;
	}
	
	
	public function get_kebutuhan_modal() {
	  $userID = intval($this->get_post_value('user_id'));
	  $noAnggota = $this->db->get_where('nasabah', array(
	      'user_id' => $userID
	    ))->row_array()['no_anggota'];
	  $kebutuhanModal = intval($this->db->get_where('investor', array(
	      'no_anggota' => $noAnggota
	    ))->row_array()['kebutuhan_modal']);
	  echo $kebutuhanModal;
	}
	
	public function get_total_withdraw() {
	  $userID = intval($this->get_post_value('user_id'));
	  $query = $this->db->get_where('withdraw', array(
	      'user_id' => $userID,
	      'dibayar' => 1
	    ))->result_array();
	  $withdraw = 0;
	  for ($i=0; $i<sizeof($query); $i++) {
	    $withdraw += intval($query[$i]['amount']);
	  }
	  echo $withdraw;
	}
	
	public function withdraw() {
      $kodeProject = $this->get_post_value('kode_project');
	    $userID = intval($this->get_post_value('user_id'));
	    $nama = $this->get_post_value('nama');
      $type = intval($this->get_post_value('type'));
	    $namaBank = $this->get_post_value('nama_bank');
	    $noRek = $this->get_post_value('no_rek');
	    $date = $this->get_post_value('date');
	    $day = intval($this->get_post_value('day'));
	    $month = intval($this->get_post_value('month'));
	    $year = intval($this->get_post_value('year'));
	    $automaticMonth = intval($this->get_post_value('automatic_month'));
	    $amount = intval($this->get_post_value('amount'));
	    $users = $this->db->get_where('nasabah', array(
	        'user_id' => $userID
	    ));
	    $lastMonth = $month;
	    $lastYear = $year;
	    for ($i=0; $i<6; $i++) {
	      if ($lastMonth == 1) {
	        $lastMonth = 12;
	        $lastYear--;
	      } else {
	        $lastMonth--;
	      }
	    }
    $totalLaba = 0;
    $noAnggota = $this->db->get_where('nasabah', array(
      'user_id' => $userID
    ))->row_array()['no_anggota'];
    $query = $this->db->get_where('nisbah', array(
      'no_anggota' => $noAnggota
    ))->result_array();
    for ($i=0; $i<sizeof($query); $i++) {
      $year2 = intval($query[$i]['tahun']);
      //$totalLaba += intval($query[$i]['total_nisbah']);
      for ($j=0; $j<12; $j++) {
        $l = intval($query[$i]['nisbah_' . str_pad($j+1, 2, '0', STR_PAD_LEFT)]);
        $totalLaba += $l;
        if (($lastMonth-1) == $j && $lastYear == $year2) {
          break 2;
        }
      }
    }
    $saldo = $totalLaba;
    // Get tabungan
    $query = $this->db->get_where('tabungan', array(
        'user_id' => $userID,
        'tipe' => 1
      ))->result_array();
    $totalTabungan = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $kodeTransaksi = $query[$i]['kode_trans'];
      $dibayar = intval($this->db->get_where('riwayat', array(
          'id_pembayaran' => $kodeTransaksi
        ))->row_array()['dibayar']);
      if ($dibayar == 1) {
      $debit = intval($query[$i]['debet']);
      $credit = intval($query[$i]['credit']);
      if ($debit != 0 && $credit == 0) {
        $totalTabungan += $debit;
      }
      }
    }
    $saldo += $totalTabungan;
    if ($amount > $saldo) {
      echo -1;
      return;
    }
	    if ($automaticMonth > 0) {
	    for ($i=0; $i<$automaticMonth; $i++) {
        $this->db->insert('withdraw', array(
	            'user_id' => $userID,
	            'nama' => $nama,
	            'nama_bank' => $namaBank,
	            'no_rek' => $noRek,
	            'amount' => $amount,
	            'date' => '' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT) . ' 00:00:00'
	        ));
	      if ($month >= 12) {
	        $month = 1;
	        $year++;
	      } else {
	        $month++;
	      }
	    }
	    } else {
        $this->db->insert('withdraw', array(
	            'user_id' => $userID,
	            'type' => $type,
	            'nama' => $nama,
	            'nama_bank' => $namaBank,
	            'no_rek' => $noRek,
	            'amount' => $amount,
	            'date' => '' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT) . ' 00:00:00'
	        ));
	    }
	    /*$this->db->insert('riwayat', array(
	        'user_id' => $userID,
	        'tipe' => 1,
	        'id_withdraw' => intval($this->db->insert_id()),
	        'id_pembayaran' => 0,
	        'amount' => $amount,
	        'date' => $date
	        ));*/
	    //echo json_encode($this->db->error());
	    echo 1;
	}
	
	public function get_percentage() {
	    /*$userID = intval($this->get_post_value('user_id'));
	    $noAnggota = $this->db->get_where('nasabah', array(
	        'user_id' => $userID
	    ))->row_array()['no_anggota'];
	    $porsiModal = intval($this->db->get_where('investor', array(
	        'no_anggota' => $noAnggota
	    ))->row_array()['porsi_modal']);
	    $cmd = "SUM(porsi_modal)";
	    $totalModal = intval($this->db->query("SELECT " . $cmd . " FROM investor")->row_array()[$cmd]);
	    echo intval($porsiModal*100/$totalModal);*/
      $id = intval($this->get_post_value('user_id'));
	    $nasabah = $this->db->get_where('nasabah', array(
	        'user_id' => $id
	    ))->row_array();
	    $noAnggota = $nasabah['no_anggota'];
	    $investor = $this->db->get_where('investor', array(
	        'no_anggota' => $noAnggota
	    ))->row_array();
	    $modal = intval($investor['porsi_modal']);
      $cmd = "SUM(porsi_modal)";
	    $totalModal = intval($this->db->query("SELECT " . $cmd . " FROM investor")->row_array()[$cmd]);
	    //echo "" . $modal . "," . $totalModal;
	    echo intval($modal*100/$totalModal);
	}
	
	public function cek_user() {
		$this->load->database();
		$uid = $this->get_post_value("uid");
		$query = $this->db->get_where("users", array(
			"google_id" => $uid
		))->result();
		if ($query->num_rows() > 0) {
			echo 1;
		} else {
			echo 0;
		}
	}
	
	public function login() {
		$email = $this->get_post_value("email");
		$kataSandi = $this->get_post_value("password");
		$query = $this->db->get_where("users", array(
			"email" => $email
		));
		if ($query->num_rows() > 0) {
			$user = $query->row_array();
			if ($kataSandi == $user['password']) {
			    if (intval($user['active']) != 1) {
			        $response = array(
				    "id" => intval($user["id"]),
				    "response" => -3
			        );
			        echo json_encode($response);
			        return;
			    }
			    $response = array(
				"id" => intval($user["id"]),
				"response" => 1
			    );
			    echo json_encode($response);
			} else {
			    if (password_verify($kataSandi, $user['password'])) {
			        if (intval($user['active']) != 1) {
			            $response = array(
				        "id" => intval($user["id"]),
				        "response" => -3
			            );
			            echo json_encode($response);
			            return;
			        }
				$response = array(
					"id" => intval($user["id"]),
					"response" => 1
				);
				echo json_encode($response);
				//echo "Correct";
			    } else {
				$response = array(
					"id" => 0,
					"response" => -2
				);
				echo json_encode($response);
				//echo "Incorrect";
			    }
			}
		} else {
			// Email not exists
			$response = array(
				"id" => 0,
				"response" => -1
			);
			echo json_encode($response);
		}
	}
	
	public function activate_user() {
	    $userID = intval($this->get_post_value('id'));
	    $this->db->where('id', $userID);
	    $this->db->update('users', array(
	        'active' => 1
	    ));
	}
	
	public function deactivate_user() {
	    $userID = intval($this->get_post_value('id'));
	    $this->db->where('id', $userID);
	    $this->db->update('users', array(
	        'active' => 0
	    ));
	}
	
	public function get_by_id() {
	    $name = $this->get_post_value('name');
	    $id = intval($this->get_post_value('id'));
	    $query = $this->db->get_where($name, array(
	        'id' => $id
	    ))->result_array();
	    echo json_encode($query);
	}
	
  public function get_column_by_id() {
      $columnName = $this->get_post_value('column_name');
	    $name = $this->get_post_value('name');
	    $id = intval($this->get_post_value('id'));
	    $query = $this->db->get_where($name, array(
	        'id' => $id
	    ))->result_array();
	    echo $query[0][$columnName];
	}
	
  public function get_column_by_id_name() {
      $columnName = $this->get_post_value('column_name');
	    $name = $this->get_post_value('name');
	    $idName = $this->get_post_value('id_name');
	    $id = intval($this->get_post_value('id'));
	    $query = $this->db->get_where($name, array(
	        $idName => $id
	    ))->result_array();
	    echo $query[0][$columnName];
	}
	
  public function get_by_id_string() {
	    $name = $this->get_post_value('name');
	    $id = $this->get_post_value('id');
	    $query = $this->db->get_where($name, array(
	        'id' => $id
	    ))->result_array();
	    echo json_encode($query);
	}
	
	public function get_by_id_name() {
	    $name = $this->get_post_value('name');
	    $idName = $this->get_post_value('id_name');
	    $id = intval($this->get_post_value('id'));
	    $query = $this->db->get_where($name, array(
	        $idName => $id
	    ))->result_array();
	    echo json_encode($query);
	}
	
  public function get_by_id_name_string() {
	    $name = $this->get_post_value('name');
	    $idName = $this->get_post_value('id_name');
	    $id = $this->get_post_value('id');
	    $query = $this->db->get_where($name, array(
	        $idName => $id
	    ))->result_array();
	    echo json_encode($query);
	}
	
	public function get_penyertaan_modal() {
	    $id = intval($this->get_post_value('user_id'));
	    $nasabah = $this->db->get_where('nasabah', array(
	        'user_id' => $id
	    ))->row_array();
	    $noAnggota = $nasabah['no_anggota'];
	    $investor = $this->db->get_where('investor', array(
	        'no_anggota' => $noAnggota
	    ))->row_array();
	    $modal = intval($investor['porsi_modal']);
	    echo $modal;
	}
	
	public function get_porsi_modal() {
	    $id = intval($this->get_post_value('id'));
	    $kodeProject = $this->get_post_value('kode_project');
	    $nasabah = $this->db->get_where('nasabah', array(
	        'user_id' => $id
	    ))->row_array();
	    $noAnggota = $nasabah['no_anggota'];
	    $investor = $this->db->get_where('investor', array(
	        'no_anggota' => $noAnggota,
	        'kode_project' => $kodeProject
	    ))->row_array();
	    $modal = intval($investor['porsi_modal']);
	    echo $modal;
	}
	
	public function get_bagi_hasil() {
	    $id = intval($this->get_post_value('id'));
	    $nasabah = $this->db->get_where('nasabah', array(
	        'user_id' => $id
	    ))->row_array();
	    $noAnggota = $nasabah['no_anggota'];
	    $nisbah = $this->db->get_where('nisbah', array(
	        'no_anggota' => $noAnggota
	    ))->result();
	    $total = 0;
	    foreach ($nisbah as $a) {
	        $total += intval($a->total_nisbah);
	    }
	    echo $total;
	}
	
	public function get() {
	    $name = $this->get_post_value('name');
	    $query = $this->db->get($name)->result_array();
	    echo json_encode($query);
	}
	
	public function get_akad_awal() {
	    $kodeProject = $this->get_post_value('kode_project');
	    $id = intval($this->get_post_value('id'));
	    $nasabah = $this->db->get_where('nasabah', array(
	        'user_id' => $id
	    ))->row_array();
	    $noAnggota = $nasabah['no_anggota'];
	    $investor = $this->db->get_where('investor', array(
	        'no_anggota' => $noAnggota,
	        'kode_project' => $kodeProject
	    ))->row_array();
	    echo $investor['awal_akad'];
	}
	
	public function update_email() {
	    $id = intval($this->get_post_value('id'));
	    $email = $this->get_post_value('email');
	    $password = $this->get_post_value('password');
	    $user = $this->db->get_where('users', array(
	        'id' => $id
	    ))->row_array();
	    if (password_verify($password, $user['password'])) {
	         $this->db->where('id', $id);
	         $this->db->update('users', array(
	             'email' => $email
	         ));
	         echo 1;
	    } else {
	         echo -1;
	    }
	}
	
	public function update_profile() {
	    $id = intval($this->get_post_value('id'));
	    $email = $this->get_post_value('email');
	    $password = $this->get_post_value('password');
	    $newPassword = $this->get_post_value('new_password');
	    $user = $this->db->get_where('users', array(
	        'id' => $id
	    ))->row_array();
	    if (password_verify($password, $user['password'])) {
	         $this->db->where('id', $id);
	         $this->db->update('users', array(
	             'email' => $email,
	             'password' => password_hash($newPassword, PASSWORD_BCRYPT)
	         ));
	         echo 1;
	    } else {
	         echo -1;
	    }
	}
	
	public function get_messages() {
	    $userID = intval($this->get_post_value('user_id'));
	    $this->db->where('deleted_at', NULL);
	    $query = $this->db->get_where('inbox_messages', array(
	        'user_id' => $userID
	      ))->result_array();
	    $messages = [];
	    for ($i=0; $i<sizeof($query); $i++) {
	        $query[$i]['user'] = $this->db->get_where('users', array(
	            'id' => intval($query[$i]['user_id'])
	        ))->row_array()['name'];
	        $query[$i]['message'] = json_encode($this->db->get_where('messages', array(
	            'id' => intval($query[$i]['message_id'])
	          ))->row_array());
	        array_push($messages, $query[$i]);
	    }
	    echo json_encode($messages);
	}
	
	public function delete_message() {
	    $id = intval($this->get_post_value('id'));
	    $this->db->where('id', $id);
	    $this->db->update('messages', array(
	        'deleted_at' => date('Y:m:d H:i:s')
	    ));
	}
	
	public function undelete_message() {
	    $id = intval($this->get_post_value('id'));
	    $this->db->where('id', $id);
	    $this->db->update('messages', array(
	        'deleted_at' => NULL
	    ));
	}
	
	public function get_deleted_messages() {
      $userID = intval($this->get_post_value('user_id'));
	    $this->db->where('deleted_at IS NOT NULL');
	    $query = $this->db->get_where('inbox_messages', array(
	        'user_id' => $userID
	      ))->result_array();
	    $messages = [];
	    for ($i=0; $i<sizeof($query); $i++) {
	        $query[$i]['user'] = $this->db->get_where('users', array(
	            'id' => intval($query[$i]['user_id'])
	        ))->row_array()['name'];
          $query[$i]['message'] = json_encode($this->db->get_where('messages', array(
	            'id' => intval($query[$i]['message_id'])
	          ))->row_array());
	        array_push($messages, $query[$i]);
	    }
	    echo json_encode($messages);
	}
	
	public function signup() {
	    $name = $this->get_post_value('name');
	    $phone = urldecode($this->get_post_value("phone"));
	    $email = $this->get_post_value('email');
	    $password = $this->get_post_value('password');
	    $noAnggotaDate = $this->get_post_value('no_anggota_date');
	    $users = $this->db->get_where('users', array(
	        'phone' => $phone
	      ));
	    if ($users->num_rows() > 0) {
	      echo -1;
	      return;
	    }
      $users = $this->db->get_where('users', array(
	        'email' => $email
	      ));
	    if ($users->num_rows() > 0) {
	      echo -2;
	      return;
	    }
	    $cmd = "MAX(CAST(SUBSTR(no_anggota, 15, 4) AS UNSIGNED))";
	    $a = $this->db->query("SELECT " . $cmd . " FROM nasabah");
	    $b = intval($a->row_array()[$cmd]);
	    $c = $b+1;
	    $noAnggota = "IPG-1001-" . $noAnggotaDate . "-" . str_pad($c, 4, '0', STR_PAD_LEFT);
      $config['upload_path'] = './userdata/';
      $config['allowed_types'] = '*';
      $this->load->library('upload', $config);
      if ($this->upload->do_upload('file')) {
        $ktp = $this->upload->data('file_name');
	    $this->db->insert('users', array(
	        'name' => $name,
	        'email' => $email,
	        'phone' => $phone,
	        'password' => password_hash($password, PASSWORD_BCRYPT),
	        'role' => 1,
	        'created_at' => date('Y:m:d H:i:s'),
	        'updated_at' => date('Y:m:d H:i:s'),
	        'active' => 1,
	        'ktp' => $ktp,
	        'ktp_status' => 1
	    ));
	    $userID = intval($this->db->insert_id());
	    $cmd = "MAX(CAST(id AS UNSIGNED))";
	    $id = intval($this->db->query('SELECT ' . $cmd . ' FROM nasabah')->row_array()[$cmd])+1;
	    $id = str_pad($id, 4, '0', STR_PAD_LEFT);
	    $this->db->insert('nasabah', array(
	        'id' => $id,
	        'no_anggota' => $noAnggota,
	        'user_id' => $userID,
	        'nama_lengkap' => $name,
	        'synced_at' => date('Y:m:d H:i:s')
	    ));
	    $kodeProject = $this->db->get('project')->row_array()['kode_project'];
	    $this->db->insert('investor', array(
	        'no_anggota' => $noAnggota,
	        'kode_project'=> $kodeProject,
	        'awal_akad' => date('Y:m:d'),
	        'synced_at' => date('Y:m:d')
	    ));
	    echo $userID;
      }
	}
	
	public function topup() {
	    $userID = intval($this->get_post_value('user_id'));
	    $nominal = intval($this->get_post_value('nominal'));
	    $tipe = intval($this->get_post_value('tipe'));
	    $idTabungan = intval($this->get_post_value('id_tabungan'));
	    $tanggal = $this->get_post_value('tanggal');
	    $tabungan = $this->db->get_where('nasabah_tabungan', array(
	        'id' => $idTabungan
	    ))->row_array();
	    $noRek = $tabungan['no_rek'];
	    $kodeSimpanan = $tabungan['kode_simpanan'];
	    $rekAsal = $this->get_post_value('rek_asal');
	    $namaPengirim = $this->get_post_value('nama_pengirim');
	    $externalID = $this->get_post_value('external_id');
	    $this->db->insert('pembayaran', array(
	        'user_id' => $userID,
	        'tipe' => $tipe,
	        'jumlah' => $nominal,
	        'pajak' => 0,
	        'tanggal' => $tanggal,
	        'no_rek' => $noRek,
	        'rek_asal' => $rekAsal,
	        'nama_pengirim' => $namaPengirim,
	        'kode_simpanan' => $kodeSimpanan,
	        'external_id' => $externalID,
	        'dibayar' => 0
	    ));
	    $this->db->insert('riwayat', array(
	        'user_id' => $userID,
	        'tipe' => 0,
	        'id_pembayaran' => intval($this->db->insert_id()),
	        'id_withdraw' => 0,
	        'amount' => $nominal,
	        'date' => $tanggal
	    ));
	}
	
	public function get_pembayaran() {
	    $userID = intval($this->get_post_value('user_id'));
	    $dibayar = intval($this->get_post_value('dibayar'));
	    $query = $this->db->get_where('riwayat', array(
	        'user_id' => $userID,
	        'dibayar' => $dibayar
	    ))->result_array();
	    for ($i=0; $i<sizeof($query); $i++) {
	      $query[$i]['tabungan'] = json_encode($this->db->get_where('tabungan', array(
	          'kode_trans' => $query[$i]['id_pembayaran']
	        ))->row_array());
	    }
	    echo json_encode($query);
	}
	
	
}
