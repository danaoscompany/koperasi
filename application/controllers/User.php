<?php

class User extends CI_Controller {

  
  public function get_post_value($postName) {
	  $value = trim($this->input->post($postName));
	  return $value;
	}
  
  public function upload_ktp_image() {
    $userID = intval($this->get_post_value('user_id'));
    $config['upload_path'] = './userdata/';
    $config['allowed_types'] = '*';
    $this->load->library('upload', $config);
    $this->upload->initialize($config);
    if ($this->upload->do_upload('file')) {
      $this->db->where('id', $userID);
      $this->db->update('users', array(
        'ktp' => $this->upload->data('file_name'),
        'ktp_status' => 1
      ));
    }
  }
  
  public function get_total_simpanan_pokok($userID) {
      $noAnggota = $this->db->get_where('nasabah', array(
        'user_id' => $userID
      ))->row_array()['no_anggota'];
      $query = $this->db->get_where('riwayat_simpanan', array(
          'no_anggota' => $noAnggota,
          'kode_trans' => 'SYPG-01-002'
      ))->result_array();
      $total = 0;
      for ($i=0; $i<sizeof($query); $i++) {
          $total += intval($query[$i]['debet']);
      }
      return $total;
  }
  
  public function top_up() {
    $userID = intval($this->get_post_value('user_id'));
    $kodeProject = $this->get_post_value('kode_project');
    $jumlah = intval($this->get_post_value('jumlah'));
    $tipe = intval($this->get_post_value('tipe'));
    $tipePembayaran = intval($this->get_post_value('tipe_pembayaran'));
    $tanggal = $this->get_post_value('tanggal');
    $bulan = intval($this->get_post_value('bulan'));
    $tahun = intval($this->get_post_value('tahun'));
    $noRek = $this->get_post_value('no_rek');
    $kodeSimpanan = $this->get_post_value('kode_simpanan');
    $noAnggota = $this->db->get_where('nasabah', array(
        'user_id' => $userID
    ))->row_array()['no_anggota'];
$config['upload_path'] = './userdata/';
$config['allowed_types'] = '*';

$this->load->library('upload', $config);

// Alternately you can set preferences by calling the ``initialize()`` method. Useful if you auto-load the class:
$this->upload->initialize($config);
    if ($this->upload->do_upload('file')) {
      $cmd = "MAX(no_urut)";
      $noUrut = intval($this->db->query("SELECT " . $cmd . " FROM riwayat_simpanan")->row_array()[$cmd])+1;
      $id = uniqid();
      if ($kodeProject == 'SYPG-01-002') {
        // Simpanan Pokok
        $simpananPokok = get_total_simpanan_pokok($userID);
        if ($simpananPokok >= 1000000) {
          echo -1;
          return;
        } else {
          // Insert Simpanan Pokok
          $topupSimpananPokok = 0;
          if ($jumlah >= (1000000-$simpananPokok)) {
            $topupSimpananPokok = 1000000-$simpananPokok;
          } else {
            $topupSimpananPokok = $jumlah;
          }
          $this->db->insert('riwayat_simpanan', array(
            'date_trans' => $tanggal,
            'kode_project' => 'SYPG-01-002',
            'no_anggota' => $noAnggota,
            'kode_trans' => $id,
            'debet' => $topupSimpananPokok,
            'credit' => 0,
            'saldo' => $topupSimpananPokok,
            'no_urut' => $noUrut,
            'paid' => 0,
            'synced_at' => $tanggal
          ));
          $simpananWajib = $jumlah-(1000000-$simpananPokok);
          // Insert Simpanan Wajib
          // Get last topup date
          $query2 = $this->db->query("SELECT * FROM riwayat_simpanan WHERE no_anggota='" . $noAnggota . "', kode_project='SYPG-01-003', paid=1 ORDER BY synced_at DESC LIMIT 1")->result_array();
          if (sizeof($query2) > 0) {
            $tanggal = $query2[0]['synced_at'];
            $tanggal = date('Y:m:d H:i:s', strtotime('+1 month', strtotime($tanggal)));
          }
          $totalMonths = $simpananWajib/50000;
          for ($i=0; $i<$totalMonths; $i++) {
            $this->db->insert('riwayat_simpanan', array(
            'date_trans' => $tanggal,
            'kode_project' => 'SYPG-01-003',
            'no_anggota' => $noAnggota,
            'kode_trans' => $id,
            'debet' => 50000,
            'credit' => 0,
            'saldo' => 50000,
            'no_urut' => $noUrut,
            'paid' => 0,
            'synced_at' => $tanggal
            ));
            $tanggal = date('Y:m:d H:i:s', strtotime('+1 month', strtotime($tanggal)));
          }
        }
      } else if ($kodeProject == 'SYPG-01-003') {
        $simpananWajib = $jumlah;
          // Insert Simpanan Wajib
          // Get last topup date
          $query2 = $this->db->query("SELECT * FROM riwayat_simpanan WHERE no_anggota='" . $noAnggota . "', kode_project='SYPG-01-003', paid=1 ORDER BY synced_at DESC LIMIT 1")->result_array();
          if (sizeof($query2) > 0) {
            $tanggal = $query2[0]['synced_at'];
            $tanggal = date('Y:m:d H:i:s', strtotime('+1 month', strtotime($tanggal)));
          }
          $totalMonths = $simpananWajib/50000;
          for ($i=0; $i<$totalMonths; $i++) {
            $this->db->insert('riwayat_simpanan', array(
            'date_trans' => $tanggal,
            'kode_project' => 'SYPG-01-003',
            'no_anggota' => $noAnggota,
            'kode_trans' => $id,
            'debet' => 50000,
            'credit' => 0,
            'saldo' => 50000,
            'no_urut' => $noUrut,
            'paid' => 0,
            'synced_at' => $tanggal
            ));
            $tanggal = date('Y:m:d H:i:s', strtotime('+1 month', strtotime($tanggal)));
          }
      }
    }
  }
  
  public function get_mutlaqoh_value($userID) {
      $noAnggota = $this->db->get_where('nasabah', array(
          'user_id' => $userID
      ))->row_array()['no_anggota'];
      $total = 0;
      $this->db->where('no_anggota', $noAnggota)->where('paid', 1)->where('kode_project', 'SYPG-01-002')->or_where('kode_project', 'SYPG-01-003');
      $query = $this->db->get('riwayat_simpanan')->result_array();
      for ($i=0; $i<sizeof($query); $i++) {
          $total += intval($query[$i]['debet']);
      }
      return $total;
  }
  
  public function get_mutlaqoh() {
      $userID = intval($this->get_post_value('user_id'));
      echo get_mutlaqoh_value($userID);
  }
  
  public function get_prosentase() {
      $userID = intval($this->get_post_value('user_id'));
      $mutlaqoh = doubleval(get_mutlaqoh_value($userID));
      $total = 0.0;
      $this->db->where('paid', 1)->where('kode_project', 'SYPG-01-002')->or_where('kode_project', 'SYPG-01-003');
      $query = $this->db->get('riwayat_simpanan')->result_array();
      for ($i=0; $i<sizeof($query); $i++) {
          $total += doubleval($query[$i]['debet']);
      }
      echo $mutlaqoh*100/$total;
  }
  
  public function get_total_nilai_project() {
      $userID = intval($this->get_post_value('user_id'));
      $noAnggota = $this->db->get_where('nasabah', array(
          'user_id' => $userID
      ))->row_array()['no_anggota'];
      $total = 0;
      $query = $this->db->get_where('investor', array(
          'no_anggota' => $noAnggota
      ))->result_array();
      for ($i=0; $i<sizeof($query); $i++) {
          $kodeProject = $query[$i]['kode_project'];
          $total += intval($this->db->get_where('project', array(
              'kode_project' => $kodeProject
          ))->row_array()['porsi_modal']);
      }
      echo $total;
  }
  
  public function get_tabungan() {
    $userID = intval($this->get_post_value('user_id'));
    $noAnggota = $this->db->get_where('nasabah', array(
      'user_id' => $userID
    ))->row_array()['no_anggota'];
    $totalNisbah = 0;
    $query = $this->db->get_where('nisbah', array(
      'no_anggota' => $noAnggota
    ))->result_array();
    for ($i=0; $i<sizeof($query); $i++) {
      $totalNisbah += intval($query[$i]['total_nisbah']);
    }
    $saldo = 0;
    $query = $this->db->get_where('riwayat', array(
      'user_id' => $userID,
      'tipe' => 1,
      'tipe_pembayaran' => 1,
      'dibayar' => 1
    ))->result_array();
    for ($i=0; $i<sizeof($query); $i++) {
      $saldo += intval($query[$i]['amount']);
    }
    $query = $this->db->get_where('withdraw', array(
      'user_id' => $userID,
      'type' => 2,
      'dibayar' => 1
    ))->result_array();
    $totalWithdraw = 0;
    for ($i=0; $i<sizeof($query); $i++) {
      $totalWithdraw += intval($query[$i]['amount']);
    }
    echo $totalNisbah+$saldo-$totalWithdraw;
  }
  
  public function get_riwayat_tabungan() {
    $userID = intval($this->get_post_value('user_id'));
    echo json_encode($this->db->get_where('riwayat', array(
      'user_id' => $userID,
      'tipe' => 1,
      'tipe_pembayaran' => 1,
      'dibayar' => 1
    ))->result_array());
  }
  
  public function get_modal_history() {
    $userID = intval($this->get_post_value('user_id'));
    echo json_encode($this->db->get_where('riwayat', array(
      'user_id' => $userID,
      'tipe' => 1,
      'tipe_pembayaran' => 2,
      'dibayar' => 1
    ))->result_array());
  }

public function signup() {
  $firstName = $this->get_post_value('first_name');
  $lastName = $this->get_post_value('last_name');
  $email = $this->get_post_value('email');
  $phone = $this->get_post_value('phone');
  $password = $this->get_post_value('password');
  $countryID = $this->get_post_value('country_id');
  $zoneID = intval($this->get_post_value('zone_id'));
  $cityID = intval($this->get_post_value('city_id'));
  $city = $this->get_post_value('city');
  $districtID = intval($this->get_post_value('district_id'));
  $district = $this->get_post_value('district');
  $address1 = $this->get_post_value('address_1');
  $address2 = $this->get_post_value('address_2');
  $lat = doubleval($this->get_post_value('lat'));
  $lng = doubleval($this->get_post_value('lng'));
  $newsletter = intval($this->get_post_value('newsletter'));
  $date = $this->get_post_value('date');
  $cmd = "MAX(customer_id)";
  $customerID = intval($this->db->query("SELECT " . $cmd . " FROM dkm_customer")->row_array()[$cmd])+1;
  $cmd = "MAX(address_id)";
  $addressID = intval($this->db->query("SELECT " . $cmd . " FROM dkm_address")->row_array()[$cmd])+1;
  $ipaddress = '';
    if (('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
  $this->db->insert('dkm_address', array(
    'address_id' => $addressID,
    'customer_id' => $customerID,
    'firstname' => $firstName,
    'lastname' => $lastName,
    'email' => $email,
    'telephone' => $phone,
    'geolocation' => '{lat:,lng:}',
    'lat' => $lat,
    'lng' => $lng,
    'address_1' => $address1,
    'address_2' => $address2,
    'city' => $city,
    'city_id' => $cityID,
    'district' => $district,
    'district_id' => $districtID,
    'country_id' => $countryID,
    'zone_id' => $zoneID,
    'custom_field' => '[]'
  ));
  echo json_encode($this->db->error());
  $salt = random_bytes(9);
  $this->db->insert('dkm_customer', array(
    'customer_id' => $customerID,
    'image' => null,
    'customer_group_id' => 1,
    'store_id' => 0,
    'language_id' => 1,
    'firstname' => $firstName,
    'lastname' => $lastName,
    'email' => $email,
    'telephone' => $phone,
    'password' => sha1($salt . sha1($salt . sha1($password))),
    'salt' => $salt,
    'newsletter' => $newsletter,
    'address_id' => $addressID,
    'custom_field' => '[]',
    'ip' => $ipaddress,
    'status' => 1,
    'safe' => 0,
    'date_added' => $date,
    'user_id' => null
  ));
  echo json_encode($this->db->error());
}

public function edit_user() {
  $id = intval($this->input->post('id'));
  $name = $this->input->post('name');
  $phone = urldecode($this->input->post('phone'));
  $email = $this->input->post('email');
  $emailChanged = intval($this->input->post('email_changed'));
  $phoneChanged = intval($this->input->post('phone_changed'));
  $menu1Active = intval($this->input->post('menu_1_active'));
  $menu2Active = intval($this->input->post('menu_2_active'));
  $menu3Active = intval($this->input->post('menu_3_active'));
  $menu4Active = intval($this->input->post('menu_4_active'));
  $menu5Active = intval($this->input->post('menu_5_active'));
  $menu6Active = intval($this->input->post('menu_6_active'));
  $menu7Active = intval($this->input->post('menu_7_active'));
  $menu8Active = intval($this->input->post('menu_8_active'));
  $menu9Active = intval($this->input->post('menu_9_active'));
  $menu10Active = intval($this->input->post('menu_10_active'));
  $menu11Active = intval($this->input->post('menu_11_active'));
  $this->db->where('id', $id);
  $this->db->update('users', array(
    'name' => $name,
    'menu_1_active' => $menu1Active,
    'menu_2_active' => $menu2Active,
    'menu_3_active' => $menu3Active,
    'menu_4_active' => $menu4Active,
    'menu_5_active' => $menu5Active,
    'menu_6_active' => $menu6Active,
    'menu_7_active' => $menu7Active,
    'menu_8_active' => $menu8Active,
    'menu_9_active' => $menu9Active,
    'menu_10_active' => $menu10Active,
    'menu_11_active' => $menu11Active
  ));
  $this->db->where('id', $id);
  if ($emailChanged == 1) {
    $this->db->update('users', array(
      'email' => $email
    ));
  }
  $this->db->where('id', $id);
  if ($phoneChanged == 1) {
    $this->db->update('users', array(
      'phone' => $phone
    ));
  }
}

public function is_article_liked() {
  $articleID = intval($this->input->post('article_id'));
  $userID = intval($this->input->post('user_id'));
  $query = $this->db->get_where('article_likes', array(
    'article_id' => $articleID,
    'user_id' => $userID
  ))->result_array();
  if (sizeof($query) > 0) {
    echo 1;
  } else {
    echo 0;
  }
}

public function view_article() {
  $articleID = intval($this->input->post('article_id'));
  $userID = intval($this->input->post('user_id'));
  $query = $this->db->get_where('article_viewers', array(
    'article_id' => $articleID,
    'user_id' => $userID
  ))->result_array();
  if (sizeof($query) == 0) {
    $this->db->insert('article_viewers', array(
      'article_id' => $articleID,
      'user_id' => $userID
    ));
  }
}

public function get_viewers_count() {
  $articleID = intval($this->input->post('article_id'));
  echo $this->db->get_where('article_viewers', array(
    'article_id' => $articleID
  ))->num_rows();
}

public function like() {
  $articleID = intval($this->input->post('article_id'));
  $userID = intval($this->input->post('user_id'));
  $this->db->insert('article_likes', array(
    'article_id' => $articleID,
    'user_id' => $userID
  ));
}

}
