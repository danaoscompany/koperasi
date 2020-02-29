<?php

class User extends CI_Controller {
  
  public function upload_ktp_image() {
    $userID = intval($this->input->post('user_id'));
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
    $userID = intval($this->input->post('user_id'));
    $jumlah = intval($this->input->post('jumlah'));
    $tipe = intval($this->input->post('tipe'));
    $tipePembayaran = intval($this->input->post('tipe_pembayaran'));
    $tanggal = $this->input->post('tanggal');
    $noRek = $this->input->post('no_rek');
    $kodeSimpanan = $this->input->post('kode_simpanan');
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
    $userID = intval($this->input->post('user_id'));
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
    $userID = intval($this->input->post('user_id'));
    echo json_encode($this->db->get_where('riwayat', array(
      'user_id' => $userID,
      'tipe' => 1,
      'tipe_pembayaran' => 1,
      'dibayar' => 1
    ))->result_array());
  }
  
  public function get_modal_history() {
    $userID = intval($this->input->post('user_id'));
    echo json_encode($this->db->get_where('riwayat', array(
      'user_id' => $userID,
      'tipe' => 1,
      'tipe_pembayaran' => 2,
      'dibayar' => 1
    ))->result_array());
  }

public function signup() {
  $firstName = $this->input->post('first_name');
  $lastName = $this->input->post('last_name');
  $email = $this->input->post('email');
  $phone = $this->input->post('phone');
  $password = $this->input->post('password');
  $countryID = $this->input->post('country_id');
  $zoneID = intval($this->input->post('zone_id'));
  $cityID = intval($this->input->post('city_id'));
  $city = $this->input->post('city');
  $districtID = intval($this->input->post('district_id'));
  $district = $this->input->post('district');
  $address1 = $this->input->post('address_1');
  $address2 = $this->input->post('address_2');
  $lat = doubleval($this->input->post('lat'));
  $lng = doubleval($this->input->post('lng'));
  $newsletter = intval($this->input->post('newsletter'));
  $date = $this->input->post('date');
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