<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Setting extends CI_Controller {



	/**

	 * Index Page for this controller.

	 *

	 * Maps to the following URL

	 * 		http://example.com/index.php/welcome

	 *	- or -

	 * 		http://example.com/index.php/welcome/index

	 *	- or -

	 * Since this controller is set as the default controller in

	 * config/routes.php, it's displayed at http://example.com/

	 *

	 * So any other public methods not prefixed with an underscore will

	 * map to /index.php/welcome/<method_name>

	 * @see https://codeigniter.com/user_guide/general/urls.html

	 */

	public function __construct(){
		parent::__construct();
		//is_user_logined_redirect();
	}
	public function index(){
		$data['title'] = "Account setting";
		$data['profile'] = $this->general_model->get('users', array("id"=>ci_get_session('id')));
		$data['profile'] = $data['profile'][0];
		load_view('/setting/index', $data);
	}
    function update_setting(){
        if (is_admin_user()==true) {
        	if (isset($_POST['submit']) && $_POST['submit']!="") {
                $this->form_validation->set_rules('firstname', 'First Name', 'trim|required|max_length[50]');
                $this->form_validation->set_rules('lastname', 'Last Name', 'trim|max_length[50]');
                $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]|email_unique');
                if ($this->form_validation->run() == FALSE){
                        $login_errors = validation_errors();
                        $login_errors = str_replace_all("<p>", "<li>", $login_errors);
                        $login_errors = str_replace_all("</p>", "</li>", $login_errors);
                        $login_errors = "<ul>".$login_errors."</ul>";
                        set_session_error_msg($login_errors);
                }else{
                	$profile = $this->general_model->get('users', array("id"=>ci_get_session('id')));
                	$profile = $profile[0];
                    $row['firstname'] = $this->input->post('firstname',true);
                    $row['lastname'] = $this->input->post('lastname',true);
                    $row['email'] = $this->input->post('email',true);
                    if ($profile['email']!=$row['email']) {
                    	$row['email_validation_hash'] = generate_verify_email_hash($row['email']);
                    	$row['is_email_validated'] = 0;
                    }
                    $row['account_update_time'] = get_current_time();
                    $this->general_model->update_record('users', $row, ci_get_session('id'));
                    set_session_successMsg("Profile updated successfully.");
                }
        	}
        }
    	redirect(base_url().'setting');
    }
	public function password(){
		$data['title'] = "Change Password";
		$data['profile'] = $this->general_model->get('users', array("id"=>ci_get_session('id')));
		$data['profile'] = $data['profile'][0];
		load_view('/setting/password', $data);
	}
    function update_password(){
    	if (isset($_POST['submit']) && $_POST['submit']!="") {
            //print_arr($_POST);
            //die();
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|password_process');
            $this->form_validation->set_rules('repassword', 'Password Confirmation', 'trim|required|matches[password]');
            if ($this->form_validation->run() == FALSE){
                    $login_errors = validation_errors();
                    $login_errors = str_replace_all("<p>", "<li>", $login_errors);
                    $login_errors = str_replace_all("</p>", "</li>", $login_errors);
                    $login_errors = "<ul>".$login_errors."</ul>";
                    set_session_error_msg($login_errors);
            }else{
            	//$row['password'] = $this->input->post('password',true);
                $row['password'] = encrypt_password($this->input->post('password',true));
                $row['is_encrypted'] = 2;
                $row['account_update_time'] = get_current_time();
                $this->general_model->update_record('users', $row, ci_get_session('id'));
                set_session_successMsg("Password updated successfully.");
            }
    	}
    	redirect(base_url().'setting/password');
    }
    public function payment_method(){
        $data['title'] = "Payout Methods";
        $data['result'] = $this->general_model->get('payment_methods');
        $data['profile'] = $this->general_model->get('users', array("id"=>ci_get_session('id')));
        $data['profile'] = $data['profile'][0];
        $data['pending_payment'] = $this->general_model->get('payment_new', array("publisher_id"=>ci_get_session('id'), "payment_status"=>1));
        if (is_admin_user()==false) {     
            if (count($data['pending_payment'])==0) {
                $data['pending_payment'] = false;
            }else{
                $data['pending_payment'] = true;
            }
        }else{
            $data['pending_payment'] = false;
        }
        load_view('/setting/payment_method', $data);
    }
    function update_payment_method(){
        if (isset($_POST['submit']) && $_POST['submit']!="") {
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
            if(in_array($this->input->post('payment_method',true), array(1,6,7))==true){
                $this->form_validation->set_rules('account_number', 'Account Number', 'required');
            }else{
                $this->form_validation->set_rules('account_title', 'Account Title', 'required');
                $this->form_validation->set_rules('account_id', 'Account Number/ID/Email', 'required');
            }
            if ($this->form_validation->run() == FALSE){
                    $login_errors = validation_errors();
                    $login_errors = str_replace_all("<p>", "<li>", $login_errors);
                    $login_errors = str_replace_all("</p>", "</li>", $login_errors);
                    $login_errors = "<ul>".$login_errors."</ul>";
                    set_session_error_msg($login_errors);
            }else{
                $row['payment_method'] = $this->input->post('payment_method',true);
                if(in_array($this->input->post('payment_method',true), array(1,6,7))==true){
                    $row['account_number'] = $this->input->post('account_number',true);
                    $row['account_comment'] = $this->input->post('account_comment',true);
                }else{
                    $row['account_title'] = $this->input->post('account_title',true);
                    $row['account_id'] = $this->input->post('account_id',true);
                }
                $row['account_update_time'] = get_current_time();
                $this->general_model->update_record('users', $row, ci_get_session('id'));
                set_session_successMsg("Profile updated successfully.");
            }
        }
        redirect(base_url().'setting/payment_method');
    }
}