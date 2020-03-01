<?php

class User extends CI_Controller {

  
  private function get_post_value($postName) {
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
  
  public function top_up() {
    $userID = intval($this->get_post_value('user_id'));
    $jumlah = intval($this->get_post_value('jumlah'));
    $tipe = intval($this->get_post_value('tipe'));
    $tipePembayaran = intval($this->get_post_value('tipe_pembayaran'));
    $tanggal = $this->get_post_value('tanggal');
    $noRek = $this->get_post_value('no_rek');
    $kodeSimpanan = $this->get_post_value('kode_simpanan');
$config['upload_path'] = './userdata/';
$config['allowed_types'] = '*';

$this->load->library('upload', $config);

// Alternately you can set preferences by calling the ``initialize()`` method. Useful if you auto-load the class:
$this->upload->initialize($config);
    if ($this->upload->do_upload('file')) {
      $id = uniqid();
      $cmd = "MAX(no_urut)";
      $noUrut = intval($this->db->query("SELECT " . $cmd . " FROM tabungan")->row_array()[$cmd])+1;
      $this->db->insert('tabungan', array(
        'kode_trans' => $id,
        'user_id' => $userID,
        'tipe' => $tipe,
        'date_trans' => substr($tanggal, 0, 8),
        'debet' => $jumlah,
        'credit' => 0,
        'saldo' => $jumlah,
        'bukti_transfer' => $this->upload->data('file_name'),
        'no_urut' => $noUrut,
        'synced_at' => $tanggal
      ));
      $this->db->insert('riwayat', array(
        'user_id' => $userID,
        'tipe' => $tipe,
        'tipe_pembayaran' => $tipePembayaran,
        'id_pembayaran' => $id,
        'id_withdraw' => 0,
        'amount' => $jumlah,
        'date' => $tanggal
      ));
    }
  }
  
  public function get_tabungan() {
    $userID = intval($this->get_post_value('user_id'));
    $noAnggota = $this->db->get_where('nasabah', array(
      'user_id' => $userID
    ))->row_array()['no_anggota'];
    $totalNisbah = 0;
    $query = $this->db->get_where('nisbah', array(
      'no_anggota' => $noAnggota
    ));
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

}
