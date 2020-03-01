<?php

class Test extends CI_Controller {

  
  public function get_post_value($postName) {
	  $value = trim($this->input->post($postName));
	  return $value;
	}

public function test3() {
  $name = $this->get_post_value('name');
  echo $name;
}

public function upload_test() {
        $config['upload_path'] = './userdata/';
        $config['allowed_types'] = 'gif|jpg|png|bmp';

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            echo "Error uploading, " . $this->upload->display_errors();
        } else {
            echo "Success uploading";
        }
    }

public function test_mail() {
    $email = 'mianadia3@gmail.com';
  $code = mt_rand(100000, 999999);
$to      = $email;
$subject = 'Atur ulang kata sandi';
$message = 'Mohon masukkan kode berikut di layar yang tersedia untuk mengatur ulang kata sandi Anda: <b>' . $code . '</b>';
$headers = 'From: admin@puputgrosir.org' . "\r\n" .
    'Reply-To: admin@puputgrosir.org' . "\r\n" .
    'Content-Type: text/html; charset=UTF-8\r\n' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
echo $code;
}

public function test2() {
    $this->load->library('email');
$code = mt_rand(100000, 999999);
$message = 'Mohon masukkan kode berikut di layar yang tersedia untuk mengatur ulang kata sandi Anda: <b>' . $code . '</b>';
// prepare email
$this->email
    ->from('admin@puputgrosir.org', 'PuputGrosir')
    ->to('puput.grosir@gmail.com')
    ->subject('Konfirmasi Email')
    ->message($message)
    ->set_mailtype('html');

// send email
$this->email->send();
echo $code;
}

}
