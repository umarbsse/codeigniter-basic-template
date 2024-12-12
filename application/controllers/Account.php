<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Account extends CI_Controller {



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

	}
	public function index(){
		if (is_user_logined()==true) {
			redirect(base_url().'dashboard');
		}
		auth_user_new();
		$data['title'] = "Login to your Account";
		load_view('/account/index', $data);
	}
	public function register(){
		create_account();
		$data['title'] = "Create your account";
		if (is_captcha_enable()) {
			$data['captcha'] = get_captcha();
		}
		load_view('/account/register', $data);
	}
	public function forgot(){
		forgot_password_form_handle();
		if (is_captcha_enable()) {
			$data['captcha'] = get_captcha();
		}
		$data['title'] = "Reset your password";
		load_view('/account/forgot', $data);
	}
	public function logout(){
		insert_login_logs_record(array('text'=>"User [".ci_get_session('name').'] logout Successfully!','type'=>4));
		destory_user_login_session();
		set_session_successMsg("Logout Successfully");
		redirect(base_url().'account');
	}
	public function setting(){

		user_login_check();
		$file_name = TEMPLATE_ADMIN.'/account/setting';
		$file_path = view_file_path($file_name);
		//echo $file_path;
		if ( ! file_exists($file_path)){
			show_404();
		}
		$data['title'] = "Account List";
		$data['profile'] = $this->general_model->get('users', array("id"=>ci_get_session('id')));
		$data['profile'] = $data['profile'][0];
		$this->load->view($file_name,$data);
	}
	public function save_setting(){

		user_login_check();

		only_admin_allowed();

		if (isset($_POST['save_setting'])) {

			$this->load->helper(array('form', 'url'));

			$this->load->library('form_validation');

			$this->form_validation->set_rules('username', 'Username', 'required');
			//$this->form_validation->set_rules('cnic', 'CNIC', 'required');

			$this->form_validation->set_rules('password', 'Password', 'required|callback__valid_admin_password');

			if ((isset($_POST['new_password']) && $_POST['new_password']!="") || 

				(isset($_POST['conf_new_password']) && $_POST['conf_new_password']!="")

				) {

				$this->form_validation->set_rules('new_password', 'New Password', 'required');

				$this->form_validation->set_rules('conf_new_password', 'Password Confirmation', 'required|matches[new_password]');

			}

			if ($this->form_validation->run() == FALSE){

				$validation_errors = process_validation_error(validation_errors());

				insert_logs_record(get_user_type().'had submitted invalid account setting update form data');

				ci_set_session('errorMsg',$validation_errors);

			}

			else{

				if ((isset($_POST['new_password']) && $_POST['new_password']!="") || 

				(isset($_POST['conf_new_password']) && $_POST['conf_new_password']!="")

				) {

					$where = array("key"=>'password');

					$temp['value']=create_hash($this->input->post('new_password',true));

					insert_logs_record(get_user_type().'had updated the IAS application admin password');

					$this->general_model->upd_record('ias_config', $temp, $where);

				}

				$where = array("key"=>'username');

				$temp['value']=$this->input->post('username',true);

				$this->general_model->upd_record('ias_config', $temp, $where);

				ci_set_session('username',$this->input->post('username',true));

				insert_logs_record(get_user_type().'had updated the account setting');

				ci_set_session('successMsg',"Account Setting Updated Successfully");

			}

		}

		header('Location: '.base_url()."account/setting");

		exit;
	}
	public function reset_password(){
		if (isset($_GET['param']) && $_GET['param']!="" && validate_password_expire_param($this->input->get('param',true))==true) {
			$data['captcha'] = get_captcha();
			$data['title'] = "Enter your password";
			$data['reset_password_pram'] = $this->input->get('param',true);
			load_view('/account/reset_password', $data);
		}else{
            redirect(base_url().'account');
		}
	}
	public function update_new_password(){
		update_new_password();
	}
	public function _email_unique($input) {

		$email = $this->input->post('email',true);

		$total = get_total_count_db($this->general_model->get('users', array("email"=>$email), NULL, NULL, NULL,'count(*) as total'));

		if ($total>0) {

			$this->form_validation->set_message('_email_unique', 'Email Already Exist.');

			return false;

		}

		return true;
	}
	public function test(){
		set_time_limit ( 5 );
		$to='umarbsse@gmail.com';
		//$to='johnpay@mailinator.com';


		$subject='subject';
		$body_html='<h1>body_html<h1><h2>body_html<h2>';
		$body_plain_text='body_plain_text';
		send_mail($to,$subject,$body_html,$body_plain_text);
	}
	public function email_template(){
		$url['path']="domain.com";$url['text']="Reset your password";
		$preheader_text="preheader_text"; $name="umer";$html="<p>Paragrpah</p>";
		echo get_email_universal_mail_html($preheader_text, $name,$html,$url);
	}
}

?>