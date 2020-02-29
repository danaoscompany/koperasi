<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	public function index() {
	}
	
	public function topup() {
	  $userID = intval($this->input->post('user_id'));
	  $noAnggota = $this->input->post('no_anggota');
	  $amount = intval($this->input->post('amount'));
	  $kodeProject = $this->input->post('kode_project');
	  $keterangan = $this->input->post('keterangan');
	  $date = $this->input->post('date');
	  $type = intval($this->input->post('type'));
	  $tahun = intval($this->input->post('tahun'));
	  if ($type == 0) {
	  $query = $this->db->get_where('investor', array(
	      'no_anggota' => $noAnggota,
	      'kode_project' => $kodeProject
	  ));
	  if ($query->num_rows() > 0) {
	    $row = $query->row_array();
	    $this->db->where('no_anggota', $noAnggota)->where('kode_project', $kodeProject);
	    $this->db->update('investor', array(
	      'jumlah_modal' => $amount+intval($row['jumlah_modal']),
        'porsi_modal' => $amount+intval($row['porsi_modal'])
	    ));
	  } else {
	    $this->db->insert('investor', array(
	      'no_anggota' => $noAnggota,
	      'kode_project' => $kodeProject,
	      'jumlah_modal' => $amount,
	      'porsi_modal' => $amount,
	      'awal_akad' => substr($date, 0, 8)
	    ));
	  }
	  } else if ($type == 1) {
	    $query = $this->db->get_where('nisbah', array(
	      'no_anggota' => $noAnggota,
	      'kode_project' => $kodeProject,
	      'tahun' => $tahun
	    ));
	    echo $query->num_rows();
	    if ($query->num_rows() > 0) {
	      $row = $query->row_array();
	      $totalNisbah = intval($row['total_nisbah']);
        $this->db->where('no_anggota', $noAnggota)->where('kode_project', $kodeProject)->where('tahun', $tahun);
	    $this->db->update('nisbah', array(
	      'total_nisbah' => $totalNisbah+$amount,
	      'synced_at' => $date
	    ));
	    } else {
	      echo "This line";
	      $this->db->insert('nisbah', array(
	        'no_anggota' => $noAnggota,
	        'kode_project' => $kodeProject,
	        'tahun' => $tahun,
	        'total_nisbah' => $amount,
	        'synced_at' => $date
	      ));
	    }
	  }
	  $cmd = "MAX(no_urut)";
	  $noUrut = intval($this->db->query("SELECT " . $cmd . " FROM tabungan")->row_array()[$cmd])+1;
	  $id = uniqid();
	  $tipe = 0;
	  if ($type == 0) {
	    $tipe = 2;
	  } else if ($type == 1) {
	    $tipe = 0;
	  } else if ($type == 2) {
	    $tipe = 1;
	  }
	  $this->db->insert('tabungan', array(
	    'kode_trans' => $id,
	    'user_id' => $userID,
	    'tipe' => $tipe,
	    'date_trans' => substr($date, 0, 8),
	    'debet' => $amount,
	    'credit' => 0,
	    'saldo' => $amount,
	    'keterangan' => $keterangan,
	    'no_urut' => $noUrut,
	    'synced_at' => $date
	  ));
	  $this->db->insert('riwayat', array(
	    'user_id' => $userID,
	    'tipe' => 1,
	    'tipe_pembayaran' => $tipe,
	    'id_pembayaran' => $id,
	    'id_withdraw' => 0,
	    'amount' => $amount,
	    'date' => $date,
	    'dibayar' => 1
	  ));
	  echo json_encode($this->db->error());
	}
	
  public function add_project() {
	  $namaProject = $this->input->post('nama_project');
	  $projectNumber = intval($this->db->get('project')->num_rows())+1;
	  $kodeProject = "SYPG-01-" . str_pad($projectNumber, 3, '0', STR_PAD_LEFT);
	  $kebutuhanModal = intval($this->input->post('kebutuhan_modal'));
	  $nisbahInvestor = $this->input->post('nisbah_investor');
	  $nisbahMudhorib = $this->input->post('nisbah_mudhorib');
	  $this->db->insert('project', array(
	    'kode_project' => $kodeProject,
	    'nama_project' => $namaProject,
	    'kebutuhan_modal' => $kebutuhanModal,
	    'nisbah_investor' => $nisbahInvestor,
	    'nisbah_mudhorib' => $nisbahMudhorib
	  ));
	}
	
	public function edit_project() {
	  $namaProject = $this->input->post('nama_project');
	  $kodeProject = $this->input->post('kode_project');
	  $kebutuhanModal = intval($this->input->post('kebutuhan_modal'));
	  $nisbahInvestor = $this->input->post('nisbah_investor');
	  $nisbahMudhorib = $this->input->post('nisbah_mudhorib');
	  $this->db->where('kode_project', $kodeProject);
	  $this->db->update('project', array(
	    'nama_project' => $namaProject,
	    'kebutuhan_modal' => $kebutuhanModal,
	    'nisbah_investor' => $nisbahInvestor,
	    'nisbah_mudhorib' => $nisbahMudhorib
	  ));
	}
	
	public function enable_comment() {
	  $id = intval($this->input->post('id'));
	  $enabled = intval($this->input->post('enabled'));
	  $this->db->where('id', $id);
	  $this->db->update('articles', array(
	    'comments_enabled' => $enabled
	  ));
	}
	
	public function update_user_password() {
	  $userID = intval($this->input->post('id'));
	  $password = $this->input->post('password');
	  $this->db->where('id', $userID);
	  $this->db->update('users', array(
	            'password' => password_hash($password, PASSWORD_BCRYPT)
	        ));
	}
	
	public function get_riwayat() {
	  $this->db->order_by('date', 'desc');
	  echo json_encode($this->db->get('riwayat')->result_array());
	}
	
	public function ubah_transaksi() {
	  $kodeTransaksi = $this->input->post('id');
	  $date = $this->input->post('tanggal');
	  $amount = intval($this->input->post('balance'));
	  $this->db->where('id_pembayaran', $kodeTransaksi);
	  $this->db->update('riwayat', array(
	      'amount' => $amount,
	      'date' => $date
	    ));
	  $this->db->where('kode_trans', $kodeTransaksi);
	  $this->db->update('tabungan', array(
	      'date_trans' => substr($date, 0, 10),
	      'debet' => $amount,
	      'saldo' => $amount
	    ));
	}
	
	public function get_nisbah() {
	  $kodeProject = $this->input->post('kode_project');
	  $noAnggota = $this->input->post('no_anggota');
	  $tahun = intval($this->input->post('tahun'));
	  echo json_encode($this->db->get_where('nisbah', array(
	      'kode_project' => $kodeProject,
	      'no_anggota' => $noAnggota,
	      'tahun' => $tahun
	    ))->row_array());
	}
	
  public function confirm_withdraw_paid() {
	  $id = intval($this->input->post('id'));
	  $this->db->where('id', $id);
	  $this->db->update('withdraw', array(
	      'dibayar' => 1
	    ));
	  echo json_encode($this->db->error());
	}
	
	public function confirm_paid() {
	  $id = intval($this->input->post('id'));
	  $this->db->where('id', $id);
	  $this->db->update('riwayat', array(
	      'dibayar' => 1
	    ));
	  $row = $this->db->get_where('riwayat', array(
	      'id' => $id
	    ))->row_array();
	  $userID = intval($row['user_id']);
	  $amount = intval($row['amount']);
	  $tipePembayaran = intval($row['tipe_pembayaran']);
	  if ($tipePembayaran == 2) {
	  $noAnggota = $this->db->get_where('nasabah', array(
	      'user_id' => $userID
	    ))->row_array()['no_anggota'];
	  $porsiModal = intval($this->db->get_where('investor', array(
	      'no_anggota' => $noAnggota
	    ))->row_array()['porsi_modal']);
	  $this->db->where('no_anggota', $noAnggota);
	  $this->db->update('investor', array(
	      'porsi_modal' => $porsiModal+$amount,
	      'jumlah_modal' => $porsiModal+$amount
	    ));
	  }
	  echo json_encode($this->db->error());
	}
	
	/*public function get_message_receivers() {
	  $messageID = intval($this->input->post('id'));
	  $userIDs = [];
	  $query = $this->db->get_where('inbox_messages', array(
	    'message_id' => $messageID
	    ))->result_array();
	  for ($i=0; $i<sizeof($query); $i++) {
	    array_push($userIDs, intval($query[$i]['user_id']));
	  }
	  echo json_encode($userIDs);
	}*/
	
	public function send_messages() {
	  $messageID = intval($this->input->post('id'));
	  $userIDs = json_decode($this->input->post('user_ids', true));
	  $date = $this->input->post('date');
	  $this->db->where('message_id', $messageID);
	  $this->db->delete('inbox_messages');
	  for ($i=0; $i<sizeof($userIDs); $i++) {
	    $this->db->insert('inbox_messages', array(
	        'user_id' => $userIDs[$i],
	        'message_id' => $messageID,
	        'created_at' => $date,
	        'updated_at' => $date
	      ));
	  }
	  echo json_encode($this->db->error());
	}
	
	public function get_message_receivers() {
	  $messageID = intval($this->input->post('id'));
	  $users = $this->db->get('users')->result_array();
	  for ($i=0; $i<sizeof($users); $i++) {
	    $checked = false;
	    if ($this->db->get_where('inbox_messages', array(
	        'message_id' => $messageID,
	        'user_id' => intval($users[$i]['id'])
	      ))->row_array() > 0) {
	        $checked = true;
	      }
	    $users[$i]['checked'] = "" . $checked;
	  }
	  echo json_encode($users);
	}
	
	public function get() {
	    $name = $this->input->post('name');
	    $query = $this->db->get($name)->result_array();
	    echo json_encode($query);
	}
	
	public function get_by_id() {
	    $name = $this->input->post('name');
	    $id = intval($this->input->post('id'));
	    $query = $this->db->get_where($name, array(
	        'id' => $id
	    ))->result_array();
	    echo json_encode($query);
	}
	
	public function get_by_id_name() {
	    $name = $this->input->post('name');
	    $idName = $this->input->post('id_name');
	    $id = intval($this->input->post('id'));
	    $query = $this->db->get_where($name, array(
	        $idName => $id
	    ))->result_array();
	    echo json_encode($query);
	}
	
	public function login() {
	    $phone = urldecode($this->input->post("phone"));
	    $password = $this->input->post('password');
	    $admins = $this->db->get_where('admins', array(
	        'phone' => $phone
	    ));
	    if ($admins->num_rows() > 0) {
	        $admin = $admins->row_array();
	        if (password_verify($password, $admin['password'])) {
	            echo json_encode($admin);
	        } else {
	            echo -1;
	            //echo $password . ", " . $admin['password'];
	        }
	    } else {
	        echo -2;
	    }
	}
	
	public function ubah_tabungan() {
    $kode = $this->input->post('kode');
    $name = $this->input->post('name');
    $tanggal = $this->input->post('tanggal');
    $noRek = $this->input->post('no_rek');
    $simpanan = $this->input->post('simpanan');
    $debit = intval($this->input->post('debit'));
    $credit = intval($this->input->post('credit'));
    $balance = intval($this->input->post('balance'));
    $description = $this->input->post('description');
    $this->db->where('kode_trans', $kode);
    $this->db->update('tabungan', array(
        'date_trans' => $tanggal,
        'no_rek' => $noRek,
        'kode_simpanan' => $simpanan,
        'debet' => $debit,
        'credit' => $credit,
        'saldo' => $balance,
        'keterangan' => $description
      ));
    echo json_encode($this->db->error());
}

	
	public function login_test_3() {
	    $phone = urldecode($this->input->get("phone"));
	    $password = $this->input->get('password');
	    $admins = $this->db->get_where('admins', array(
	        'phone' => $phone
	    ));
	    if ($admins->num_rows() > 0) {
	        $admin = $admins->row_array();
	        if (password_verify($password, $admin['password'])) {
	            echo json_encode($admin);
	        } else {
	            echo -1;
	            //echo $password . ", " . $admin['password'];
	        }
	    } else {
	        echo -2;
	    }
	}
	
	public function add_admin() {
	    $phone = utf8_decode(urldecode($this->input->post('phone')));
	    $email = $this->input->post('email');
	    $password = $this->input->post('password');
	        $admins = $this->db->get_where('admins', array(
	            'phone' => $phone
	        ));
	        if ($admins->num_rows() > 0) {
	            echo -1;
	            return;
	        }
	        $admins = $this->db->get_where('admins', array(
	            'email' => $email
	        ));
	        if ($admins->num_rows() > 0) {
	            echo -2;
	            return;
	        }
	    $this->db->insert('admins', array(
	        'phone' => $phone,
	        'email' => $email,
	        'password' => password_hash($password, PASSWORD_BCRYPT)
	    ));
	    echo 1;
	}
	
	public function edit_admin() {
	    $id = intval($this->input->post('id'));
	    $phone = utf8_decode(urldecode($this->input->post('phone')));
	    $email = $this->input->post('email');
	    $password = $this->input->post('password');
	    $phoneChanged = intval($this->input->post('phone_changed'));
	    $emailChanged = intval($this->input->post('email_changed'));
	    $passwordChanged = intval($this->input->post('password_changed'));
	    if ($phoneChanged == 1) {
	        $admins = $this->db->get_where('admins', array(
	            'phone' => $phone
	        ));
	        if ($admins->num_rows() <= 0) {
	            $this->db->where('id', $id);
	            $this->db->update('admins', array(
	                'phone' => $phone
	            ));
	        } else {
	            echo -1;
	            return;
	        }
	    }
	    if ($emailChanged == 1) {
	        $admins = $this->db->get_where('admins', array(
	            'email' => $email
	        ));
	        if ($admins->num_rows() <= 0) {
	            $this->db->where('id', $id);
	            $this->db->update('admins', array(
	                'email' => $email
	            ));
	        } else {
	            echo -2;
	            return;
	        }
	    }
	    if ($passwordChanged == 1) {
	        $this->db->where('id', $id);
	        $this->db->update('admins', array(
	            'password' => password_hash($password, PASSWORD_BCRYPT)
	        ));
	    }
	    echo 1;
	}
	
	public function delete() {
	    $id = intval($this->input->post('id'));
	    $this->db->where('id', $id);
	    $this->db->delete('admins');
	}
	
	public function test() {
	    echo $this->db->query("SELECT MAX(CAST(id AS UNSIGNED)) FROM nasabah")->row_array()['MAX(CAST(id AS UNSIGNED))'];
	}
	
	public function tambah_nasabah() {
	    $noAnggota = $this->input->post('no_anggota');
	    $nama = $this->input->post('nama');
	    $alamat = $this->input->post('alamat');
	    $kabupaten = $this->input->post('kabupaten');
	    $kecamatan = $this->input->post('kecamatan');
	    $provinsi = $this->input->post('provinsi');
	    $userID = intval($this->input->post('user_id'));
	    $gender = intval($this->input->post('gender'));
	    if ($gender == 0) {
	        $gender = "LAKI-LAKI";
	    } else if ($gender == 1) {
	        $gender = "PEREMPUAN";
	    }
	    if ($this->db->get_where('nasabah', array(
	        'no_anggota' => $noAnggota
	    ))->num_rows() > 0) {
	        echo -1;
	        return;
	    }
	    if ($this->db->get_where('nasabah', array(
	        'user_id' => $userID
	    ))->num_rows() > 0) {
	        echo -2;
	        return;
	    }
	    $id = intval($this->db->query("SELECT MAX(CAST(id AS UNSIGNED)) FROM nasabah")->row_array()['MAX(CAST(id AS UNSIGNED))'])+1;
	    $id = str_pad($id, 4, "0", STR_PAD_LEFT);
	    $this->db->insert('nasabah', array(
	        'id' => $id,
	        'no_anggota' => $noAnggota,
	        'nama_lengkap' => $nama,
	        'alamat' => $alamat,
	        'kabupaten' => $kabupaten,
	        'kecamatan' => $kecamatan,
	        'propinsi' => $provinsi,
	        'user_id' => $userID,
	        'jenis_kelamin' => $gender,
	        'synced_at' => date('Y:m:d H:i:s')
	    ));
	    echo 1;
	}
	
	public function tambah_rekening() {
	    $namaBank = $this->input->post('nama_bank');
	    $namaPemilik = $this->input->post('nama_pemilik');
	    $noRek = $this->input->post('no_rek');
	    $kodeSimpanan = $this->input->post('kode_simpanan');
	    $cmd = "MAX(id)";
	    $id = intval($this->db->query("SELECT " . $cmd . " FROM nasabah_tabungan")->row_array()[$cmd])+1;
	    $this->db->insert('nasabah_tabungan', array(
	        'id' => $id,
	        'nama' => $namaPemilik,
	        'nama_bank' => $namaBank,
	        'no_rek' => $noRek,
	        'kode_simpanan' => $kodeSimpanan,
	        'dt_entry' => date('Y:m:d'),
	        'synced_at' => date('Y:m:d H:i:s')
	    ));
	}
	
	public function hapus_tabungan() {
	    $id = intval($this->input->post('id'));
	    $this->db->where('id', $id);
	    $this->db->delete('nasabah_tabungan');
	}
	
	public function tambah_simpanan() {
	    $name = $this->input->post('name');
	    $code = $this->input->post('code');
	    $this->db->insert('simpanan', array(
	        'nama_simpanan' => $name,
	        'kode_simpanan' => $code
	    ));
	}
	
	public function hapus_simpanan() {
	    $id = intval($this->input->post('id'));
	    $this->db->where('id', $id);
	    $this->db->delete('simpanan');
	}
	
	public function get_investors() {
	    $investors = $this->db->get('investor')->result_array();
	    for ($i=0; $i<sizeof($investors); $i++) {
	        $noAnggota = $investors[$i]['no_anggota'];
	        $investors[$i]['name'] = $this->db->get_where('nasabah', array(
	            'no_anggota' => $noAnggota
	        ))->row_array()['nama_lengkap'];
	    }
	    echo json_encode($investors);
	}
	
	public function tambah_investor() {
	    $jumlahModal = intval($this->input->post('jumlah_modal'));
	    $porsiModal = intval($this->input->post('porsi_modal'));
	    $date = $this->input->post('date');
	    $noAnggota = $this->input->post('no_anggota');
	    $investors = $this->db->get_where('investor', array(
	        'no_anggota' => $noAnggota
	    ));
	    if ($investors->num_rows() > 0) {
	        echo -1;
	        return;
	    }
	    $kodeProject = $this->input->post('kode_project');
	    $this->db->insert('investor', array(
	        'no_anggota' => $noAnggota,
	        'kode_project' => $kodeProject,
	        'jumlah_modal' => $jumlahModal,
	        'porsi_modal' => $porsiModal,
	        'awal_akad' => $date
	    ));
	}
	
	public function ubah_investor() {
	    $jumlahModal = intval($this->input->post('jumlah_modal'));
	    $porsiModal = intval($this->input->post('porsi_modal'));
	    $date = $this->input->post('date');
	    $noAnggota = $this->input->post('no_anggota');
	    $kodeProject = $this->input->post('kode_project');
	    $this->db->where('no_anggota', $noAnggota);
	    $this->db->update('investor', array(
	        'kode_project' => $kodeProject,
	        'jumlah_modal' => $jumlahModal,
	        'porsi_modal' => $porsiModal,
	        'awal_akad' => $date
	    ));
	}
	
	public function get_latest_no_anggota() {
	    $cmd = "MAX(CAST(SUBSTR(no_anggota, 15, 4) AS UNSIGNED))";
	    $noAnggota = intval($this->db->query("SELECT " . $cmd . " FROM investor")->row_array()[$cmd])+1;
	    echo $noAnggota;
	}
	
	public function tambah_investor_get() {
	    $jumlahModal = intval($this->input->get('jumlah_modal'));
	    $porsiModal = intval($this->input->get('porsi_modal'));
	    $date = $this->input->get('date');
	    $noAnggota = $this->input->get('no_anggota');
	    $kodeProject = $this->input->get('kode_project');
	    /*$this->db->insert('investor', array(
	        'no_anggota' => $noAnggota,
	        'kode_project' => $kodeProject,
	        'jumlah_modal' => $jumlahModal,
	        'porsi_modal' => $porsiModal,
	        'awal_akad' => $date
	    ));*/
	    echo "OK2";
	    //echo json_encode($this->input->get());
	}
	
	public function hapus_investor() {
	    $noAnggota = $this->input->post('no_anggota');
	    $this->db->where('no_anggota', $noAnggota);
	    $this->db->delete('investor');
	}
	
	public function get_years() {
	    $this->db->select('tahun');
	    $this->db->group_by('tahun');
	    $this->db->order_by('tahun', 'asc');
	    $years = $this->db->get('nisbah')->result_array();
	    echo json_encode($years);
	}
	
	public function get_nisbah_by_year() {
	    $year = intval($this->input->post('year'));
	    $nisbah = $this->db->get_where('nisbah', array(
	        'tahun' => "" . $year
	    ))->result_array();
	    for ($i=0; $i<sizeof($nisbah); $i++) {
	        $nisbah[$i]['nama'] = $this->db->get_where('nasabah', array(
	            'no_anggota' => $nisbah[$i]['no_anggota']
	        ))->row_array()['nama_lengkap'];
	    }
	    echo json_encode($nisbah);
	}
	
	public function simpan_nisbah() {
	    $date = $this->input->post('date');
	    $noAnggota = $this->input->post('no_anggota');
	    $kodeProject = $this->input->post('kode_project');
	    $tahun = $this->input->post('tahun');
	    $omset1 = intval($this->input->post('omset_01'));
	    $laba1 = intval($this->input->post('laba_01'));
	    $biaya1 = intval($this->input->post('biaya_01'));
	    $nisbah1 = intval($this->input->post('nisbah_01'));
	    $omset2 = intval($this->input->post('omset_02'));
	    $laba2 = intval($this->input->post('laba_02'));
	    $biaya2 = intval($this->input->post('biaya_02'));
	    $nisbah2 = intval($this->input->post('nisbah_02'));
	    $omset3 = intval($this->input->post('omset_03'));
	    $laba3 = intval($this->input->post('laba_03'));
	    $biaya3 = intval($this->input->post('biaya_03'));
	    $nisbah3 = intval($this->input->post('nisbah_03'));
	    $omset4 = intval($this->input->post('omset_04'));
	    $laba4 = intval($this->input->post('laba_04'));
	    $biaya4 = intval($this->input->post('biaya_04'));
	    $nisbah4 = intval($this->input->post('nisbah_04'));
	    $omset5 = intval($this->input->post('omset_05'));
	    $laba5 = intval($this->input->post('laba_05'));
	    $biaya5 = intval($this->input->post('biaya_05'));
	    $nisbah5 = intval($this->input->post('nisbah_05'));
	    $omset6 = intval($this->input->post('omset_06'));
	    $laba6 = intval($this->input->post('laba_06'));
	    $biaya6 = intval($this->input->post('biaya_06'));
	    $nisbah6 = intval($this->input->post('nisbah_06'));
	    $omset7 = intval($this->input->post('omset_07'));
	    $laba7 = intval($this->input->post('laba_07'));
	    $biaya7 = intval($this->input->post('biaya_07'));
	    $nisbah7 = intval($this->input->post('nisbah_07'));
	    $omset8 = intval($this->input->post('omset_08'));
	    $laba8 = intval($this->input->post('laba_08'));
	    $biaya8 = intval($this->input->post('biaya_08'));
	    $nisbah8 = intval($this->input->post('nisbah_08'));
	    $omset9 = intval($this->input->post('omset_09'));
	    $laba9 = intval($this->input->post('laba_09'));
	    $biaya9 = intval($this->input->post('biaya_09'));
	    $nisbah9 = intval($this->input->post('nisbah_09'));
	    $omset10 = intval($this->input->post('omset_10'));
	    $laba10 = intval($this->input->post('laba_10'));
	    $biaya10 = intval($this->input->post('biaya_10'));
	    $nisbah10 = intval($this->input->post('nisbah_10'));
	    $omset11 = intval($this->input->post('omset_11'));
	    $laba11 = intval($this->input->post('laba_11'));
	    $biaya11 = intval($this->input->post('biaya_11'));
	    $nisbah11 = intval($this->input->post('nisbah_11'));
	    $omset12 = intval($this->input->post('omset_12'));
	    $laba12 = intval($this->input->post('laba_12'));
	    $biaya12 = intval($this->input->post('biaya_12'));
	    $nisbah12 = intval($this->input->post('nisbah_12'));
	    $omsetTotal = intval($this->input->post('tota_omset'));
	    $labaTotal = intval($this->input->post('total_laba'));
	    $biayaTotal = intval($this->input->post('total_biaya'));
	    $nisbahTotal = intval($this->input->post('total_nisbah'));
      $query = $this->db->get_where('nisbah', array(
	        'tahun' => $tahun,
	        'no_anggota' => $noAnggota
	      ))->result_array();
	    if (sizeof($query) > 0) {
        $this->db->where('no_anggota', $noAnggota)->where('kode_project', $kodeProject)->where('tahun', $tahun);
	      $this->db->update('nisbah', array(
	        'omset_01' => $omset1,
	        'laba_01' => $laba1,
	        'biaya_01' => $biaya1,
	        'nisbah_01' => $nisbah1,
	        'omset_02' => $omset2,
	        'laba_02' => $laba2,
	        'biaya_02' => $biaya2,
	        'nisbah_02' => $nisbah2,
	        'omset_03' => $omset3,
	        'laba_03' => $laba3,
	        'biaya_03' => $biaya3,
	        'nisbah_03' => $nisbah3,
	        'omset_04' => $omset4,
	        'laba_04' => $laba4,
	        'biaya_04' => $biaya4,
	        'nisbah_04' => $nisbah4,
	        'omset_05' => $omset5,
	        'laba_05' => $laba5,
	        'biaya_05' => $biaya5,
	        'nisbah_05' => $nisbah5,
	        'omset_06' => $omset6,
	        'laba_06' => $laba6,
	        'biaya_06' => $biaya6,
	        'nisbah_06' => $nisbah6,
	        'omset_07' => $omset7,
	        'laba_07' => $laba7,
	        'biaya_07' => $biaya7,
	        'nisbah_07' => $nisbah7,
	        'omset_08' => $omset8,
	        'laba_08' => $laba8,
	        'biaya_08' => $biaya8,
	        'nisbah_08' => $nisbah8,
	        'omset_09' => $omset9,
	        'laba_09' => $laba9,
	        'biaya_09' => $biaya9,
	        'nisbah_09' => $nisbah9,
	        'omset_10' => $omset10,
	        'laba_10' => $laba10,
	        'biaya_10' => $biaya10,
	        'nisbah_10' => $nisbah10,
	        'omset_11' => $omset11,
	        'laba_11' => $laba11,
	        'biaya_11' => $biaya11,
	        'nisbah_11' => $nisbah11,
	        'omset_12' => $omset12,
	        'laba_12' => $laba12,
	        'biaya_12' => $biaya12,
	        'nisbah_12' => $nisbah12,
	        'total_omset' => $omsetTotal,
	        'total_laba' => $labaTotal,
	        'total_biaya' => $biayaTotal,
	        'total_nisbah' => $nisbahTotal,
	        'synced_at' => $date
	    ));
	    } else {
	      $this->db->insert('nisbah', array(
	        'no_anggota' => $noAnggota,
	        'kode_project' => $kodeProject,
	        'tahun' => $tahun,
	        'omset_01' => $omset1,
	        'laba_01' => $laba1,
	        'biaya_01' => $biaya1,
	        'nisbah_01' => $nisbah1,
	        'omset_02' => $omset2,
	        'laba_02' => $laba2,
	        'biaya_02' => $biaya2,
	        'nisbah_02' => $nisbah2,
	        'omset_03' => $omset3,
	        'laba_03' => $laba3,
	        'biaya_03' => $biaya3,
	        'nisbah_03' => $nisbah3,
	        'omset_04' => $omset4,
	        'laba_04' => $laba4,
	        'biaya_04' => $biaya4,
	        'nisbah_04' => $nisbah4,
	        'omset_05' => $omset5,
	        'laba_05' => $laba5,
	        'biaya_05' => $biaya5,
	        'nisbah_05' => $nisbah5,
	        'omset_06' => $omset6,
	        'laba_06' => $laba6,
	        'biaya_06' => $biaya6,
	        'nisbah_06' => $nisbah6,
	        'omset_07' => $omset7,
	        'laba_07' => $laba7,
	        'biaya_07' => $biaya7,
	        'nisbah_07' => $nisbah7,
	        'omset_08' => $omset8,
	        'laba_08' => $laba8,
	        'biaya_08' => $biaya8,
	        'nisbah_08' => $nisbah8,
	        'omset_09' => $omset9,
	        'laba_09' => $laba9,
	        'biaya_09' => $biaya9,
	        'nisbah_09' => $nisbah9,
	        'omset_10' => $omset10,
	        'laba_10' => $laba10,
	        'biaya_10' => $biaya10,
	        'nisbah_10' => $nisbah10,
	        'omset_11' => $omset11,
	        'laba_11' => $laba11,
	        'biaya_11' => $biaya11,
	        'nisbah_11' => $nisbah11,
	        'omset_12' => $omset12,
	        'laba_12' => $laba12,
	        'biaya_12' => $biaya12,
	        'nisbah_12' => $nisbah12,
	        'total_omset' => $omsetTotal,
	        'total_laba' => $labaTotal,
	        'total_biaya' => $biayaTotal,
	        'total_nisbah' => $nisbahTotal,
	        'synced_at' => $date
	        ));
	    }
	    echo json_encode($this->db->error());
	}
	
	public function simpan_pesan() {
	    $id = intval($this->input->post('id'));
	    $subject = $this->input->post('subject');
	    $shortMessage = $this->input->post('short_message');
	    $longMessage = $this->input->post('long_message');
	    $this->db->where('id', $id);
	    $this->db->update('messages', array(
	        'subject' => $subject,
	        'message_short' => $shortMessage,
	        'message' => $longMessage
	    ));
	}
	
	public function tambah_pesan(){
	    $userID = intval($this->input->post('user_id'));
	    $subject = $this->input->post('subject');
	    $shortMessage = $this->input->post('short_message');
	    $longMessage = $this->input->post('long_message');
	    $this->db->insert('messages', array(
	        'user_id' => $userID,
	        'subject' => $subject,
	        'message_short' => $shortMessage,
	        'message' => $longMessage,
	        'created_at' => date('Y:m:d H:i:s'),
	        'updated_at' => date('Y:m:d H:i:s')
	    ));
	}
	
	public function hapus_pesan() {
	    $id = intval($this->input->post('id'));
	    $this->db->where('id', $id);
	    $this->db->delete('messages');
	}
	
  public function simpan_artikel() {
	    $id = intval($this->input->post('id'));
	    $subject = $this->input->post('subject');
	    $shortMessage = $this->input->post('short_message');
	    $longMessage = $this->input->post('long_message');
	    $this->db->where('id', $id);
	    $this->db->update('articles', array(
	        'subject' => $subject,
	        'message_short' => $shortMessage,
	        'message' => $longMessage
	    ));
	}
	
	public function tambah_artikel(){
	    $userID = intval($this->input->post('user_id'));
	    $subject = $this->input->post('subject');
	    $shortMessage = $this->input->post('short_message');
	    $longMessage = $this->input->post('long_message');
	    $this->db->insert('articles', array(
	        'user_id' => $userID,
	        'subject' => $subject,
	        'message_short' => $shortMessage,
	        'message' => $longMessage,
	        'created_at' => date('Y:m:d H:i:s'),
	        'updated_at' => date('Y:m:d H:i:s')
	    ));
	}
	
  public function tambah_article(){
  }
	
	public function hapus_artikel() {
	    $id = intval($this->input->post('id'));
	    $this->db->where('id', $id);
	    $this->db->delete('articles');
	}
}
