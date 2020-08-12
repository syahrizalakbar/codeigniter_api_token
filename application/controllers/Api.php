<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    function __construct(Type $foo = null)
    {
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');
        ini_set('displays_errors', 1);
        error_reporting(E_ALL);

        // load library untuk generate token
        $this->load->helper('string');
    }


    function registerAccount(){
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $this->db->where('username', $username);
        $q = $this->db->get('users');

        if ($q->num_rows() > 0) { //sudah terdaftar
            $data['message'] = 'Username Sudah Terdaftar, Silahkan Login';
            $data['status'] = 400;
        } else {
            $simpan['username'] = $username;
            $simpan['password'] = md5($password);
            
            $q = $this->db->insert('users', $simpan);

            if ($q) {
                $data['message'] = 'success';
                $data['status'] = 200;
            } else {
                $data['message'] = 'error';
                $data['status'] = 404;
            }
        }

        echo json_encode($data);
    }

	function loginAccount(){
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $this->db->where('username', $username);
        $this->db->where('password', md5($password));
    
        $q=$this->db->get('users');

        if ($q->num_rows()>0) {
            // refresh user token setiap login
            $user = $q->row();
            $user->api_token = random_string('alpha', 100);

            $this->db->where('id', $user->id);
            $updated = $this->db->update('users', $user);

            if ($updated) {
                $data['message'] = 'success';
                $data['status'] = 200;
                $data['user'] = $user;
            } else {
                $data['message'] = 'false';
                $data['status'] = 404;
            }
        } else {
            $data['message'] = 'false';
            $data['status'] = 404;
        }

        echo json_encode($data);
    }


    function validateToken() {
        $headers = $this->input->request_headers();

        if ( isset($headers['Authorization']) ) {
            $tokenHeader = $headers['Authorization'];

            $splitted = explode(" ", $tokenHeader);
            if (count($splitted) > 1) {
                $this->db->where('api_token', $splitted[1]);
                $q=$this->db->get('users');
        
                if ($q->num_rows()>0) {
                    return $q->row();
                }
            }
        }
        
        $data['message'] = 'Unauthorized';
        $data['status'] = 401;
        echo json_encode($data);
        die();
    }

    function getNotes() {
        $user = $this->validateToken();
        
        $this->db->where('id_user', $user->id);
        $q = $this->db->get('notes');

        if ($q->num_rows()>0) {
            $data['message'] = 'success';
            $data['status'] = 200;
            $data['user'] = $q->result();
        } else {
            $data['message'] = 'false';
            $data['status'] = 404;
        }

        echo json_encode($data);
    }

}
