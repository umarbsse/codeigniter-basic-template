<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Users extends CI_Controller {



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
		$data['title'] = "Active Users";
		$data['user_status'] = 1;
		$data['page'] = 'index';
		load_view('/users/index', $data);
	}
	public function disabled_users(){
		$data['title'] = "Disabled Users";
		$data['user_status'] = 2;
		$data['page'] = 'disabled_users';
		load_view('/users/index', $data);
	}
	public function pending_approval_users(){
		$data['title'] = "Pending Approval Users";
		$data['user_status'] = 2;
		$data['page'] = 'pending_approval_users';
		load_view('/users/index', $data);
	}
	public function load_users(){
		$data = array();
		$requestData = $_REQUEST;
		$number_of_rows = intval($requestData['length']);
		$starting_index = intval($requestData['start']);
		$limit = array($number_of_rows,$starting_index);
		$where = NULL;
		if ( isset($requestData['status'])) {
			$where['account_status'] = $requestData['status'];
		}
		if ( isset($requestData['page'])) {
			if ($requestData['page']=="pending_approval_users") {
				$where['account_msg'] = 'Your account is under review for approval';
			}
			else if ($requestData['page']=="disabled_users") {
				$where['account_msg<>'] = 'Your account is under review for approval';
			}
			//echo $requestData['page'];
		}
		if (is_reseller_user()==true) {
			$where['is_managed_by_reseller'] = 1;
			$where['reseller_user_id'] = ci_get_session('id');
		}
		$where_in = NULL;
		$columns_name = array('id','firstname', 'email','tracking_id','cpc','click_percentage', 'account_type', 'account_status','account_creation_time');
		
		$totalData = get_total_count_db($this->general_model->get('users', $where,'','','','count(id) as total', NULL, $where_in));
		//echo $this->db->last_query();
		$like = array();
		if (isset($_REQUEST['search']) && isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!="") {
			for ($z=0; $z <count($columns_name) ; $z++) {
				array_push($like, array("LOWER(".$columns_name[$z].")", strtolower($_REQUEST['search']['value']),'both'));
			}
			array_push($like, array("LOWER(lastname)", strtolower($_REQUEST['search']['value']),'both'));
		}
		$orderby_column_name = "id";
		$orderby_sorting_type = "desc";
		if (isset($_REQUEST['order']) && isset($_REQUEST['order'][0])) {
			$orderby_column_name_live = $_REQUEST['order'][0]['column'];
			$orderby_sorting_type_live = $_REQUEST['order'][0]['dir'];
			$orderby_sorting_type = $orderby_sorting_type_live;
			for ($z=0; $z <count($columns_name) ; $z++) {
				if ($orderby_column_name_live==$z) {
					$orderby_column_name = $columns_name[$z];
				}
			}
		}
		$subquery = "(SELECT publisher_domains.tracking_id FROM `publisher_domains` WHERE publisher_domains.publisher_id=users.id AND tracking_id<>'' ORDER BY `publisher_domains`.`id` ASC LIMIT 1) as tracking_id";
		$select = "users.*,$subquery ";
		$result = $this->general_model->get('users', $where, $limit, array($orderby_column_name, $orderby_sorting_type), NULL, $select,$like, $where_in);
		//echo $this->db->last_query();
		if (count($like)==0) {
			$totalFiltered = $totalData;
		}else{
			$totalFiltered = get_total_count_db($this->general_model->get('users', $where,'','','','count(id) as total',$like,$where_in));
		}
		for ($i=0; $i <count($result) ; $i++) { 
			$tempdata = array();
			$tempdata[] = $starting_index+$i+1;
			for ($z=1; $z <count($columns_name) ; $z++) {
				if ($z==1) {
					$tempdata[] = $result[$i][$columns_name[$z]]." ".$result[$i]['lastname'];	
				}else if ($z==5) {
				    if($result[$i]["account_type"]=="2" || $result[$i]["account_type"]=="3"){
					$tempdata[] = $result[$i][$columns_name[$z]];
				    }
				    else{
				        $tempdata[] = '';
				    }
				}
				else if ($z==6) {
					$tempdata[] =  get_account_type_badge($result[$i][$columns_name[$z]],get_account_type_txt($result[$i][$columns_name[$z]]))   ;	
				}else if ($z==7) {
					$html=get_account_status_badge($result[$i][$columns_name[$z]],get_account_status_txt($result[$i][$columns_name[$z]]));
					if ($result[$i][$columns_name[$z]]=="2") {
						$html.='<br><span class="badge bg-yellow text-black">'.$result[$i]['account_msg'].'</span>';
					}
					$tempdata[] = $html;

				}else{
					$tempdata[] = $result[$i][$columns_name[$z]];	
				}
			}
			if ($result[$i]["account_type"]=="2" || $result[$i]["account_type"]=="3") {
				$tempdata[] = '<a  href="'.base_url().'switch_user/index/'.$result[$i]["id"].'" class="btn btn-secondary btn-icon btn-circle btn-sm"><i class="fa fa-chalkboard-user"></i></a>';
				//$tempdata[] = '<a  href="'.base_url().'switch_user/index/'.$result[$i]["id"].'" class="btn btn-green btn-icon btn-circle btn-sm"><i class="fa fa-code"></i></a>';
				//$tempdata[] = '<a onclick="process_update_modal('.$result[$i]["id"].',2)" href="#" class="btn btn-green btn-icon btn-circle btn-sm"><i class="fa fa-code"></i></a>';
			}else{
				$tempdata[] = '';
				$tempdata[] = '';
			}
			$tempdata[] = '<a onclick="process_update_modal('.$result[$i]["id"].',1)" href="#" class="btn btn-success btn-icon btn-circle btn-sm"><i class="fa fa-pencil"></i></a> <a onclick="delete_request(event)" href="'.base_url().'users/del/'.$result[$i]['id'].'" class="btn btn-danger btn-icon btn-circle btn-sm"><i class="fa fa-trash"></i></a> ';
			$data[] = $tempdata;
			
		}
		$json_date = array(
						"draw" => intval($requestData['draw']),
						"recordsTotal" => intval($totalData),
						"recordsFiltered" => intval($totalFiltered),
						"data" => $data
					);
		echo json_encode($json_date);
	}
	public function get_update_code($id=NULL,$type=NULL){
		if ($id!=NULL && $type!=NULL ) {
			$data['result'] =$this->general_model->get('users', array("id"=>$id),1);
			if ($type=="1") {
				$data['title'] = "Update User Profile";
				load_view('/users/update_code', $data);
			}else{
				$data['title'] = "Update User Ad Code";
				load_view('/users/update_code_ads', $data);
			}
		}
	}
	public function process_update_request(){
    	if (isset($_POST['submit']) && $_POST['submit']!="") {
    		if (is_reseller_user()==true) {
            	$test_user = $this->general_model->get('users', array("id"=>$this->input->post('token',true),'is_managed_by_reseller'=>1,'reseller_user_id'=>ci_get_session('id')));
            	if (count($test_user)==0) {
            		set_session_error_msg("Invalid request");
            		redirect(base_url().'users');
            	}
                $_POST['account_type'] = 2;
            }
            $this->form_validation->set_rules('firstname', 'First Name', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('lastname', 'Last Name', 'trim|max_length[50]');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]|email_unique');       
            if (isset($_POST['account_type']) && ($_POST['account_type']==2 || $_POST['account_type']==3)) {
               // $this->form_validation->set_rules('tracking_id', 'tracking ID', 'required|tracking_id_unique');
                $this->form_validation->set_rules('cpc', 'CPC rate', 'required');
                $this->form_validation->set_rules('click_percentage', 'Click Percentage', 'required');
            }
            if(isset($_POST['password']) && $_POST['password']!=""){
            	$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|matches[repassword]|password_process');
            	$this->form_validation->set_rules('repassword', 'Password Confirmation', 'trim|required');
            
            }
            if ($this->form_validation->run() == FALSE){
                    $login_errors = validation_errors();

                    $login_errors = str_replace_all("<p>", "<li>", $login_errors);

                    $login_errors = str_replace_all("</p>", "</li>", $login_errors);

                    $login_errors = "<ul>".$login_errors."</ul>";
                    set_session_error_msg($login_errors);

            }else{
            	$profile = $this->general_model->get('users', array("id"=>$this->input->post('token',true)));
            	$profile = $profile[0];            	
                $row['firstname'] = $this->input->post('firstname',true);
                $row['lastname'] = $this->input->post('lastname',true);
                $row['email'] = $this->input->post('email',true);
                $row['show_cpc'] = $this->input->post('show_cpc',true);
                $row['client_engaged_by'] = $this->input->post('client_engaged_by',true);
                $row['allow_click_update_approve'] = $this->input->post('allow_click_update_approve',true);
                $row['allow_reseller_to_view_revenue'] = $this->input->post('allow_reseller_to_view_revenue',true);
                $row['allow_reseller_to_view_client_clicks'] = $this->input->post('allow_reseller_to_view_client_clicks',true);
                $row['allow_reseller_to_view_cr_rate'] = $this->input->post('allow_reseller_to_view_cr_rate',true);
                $row['account_type'] = $this->input->post('account_type',true);
                if (isset($_POST['account_type']) && ($_POST['account_type']==2 || $_POST['account_type']==3)   ) {
                    //$row['tracking_id'] = $this->input->post('tracking_id',true);
                    $row['cpc'] = $this->input->post('cpc',true);
                    $row['click_percentage'] = $this->input->post('click_percentage',true);
                }
                if ($profile['email']!=$row['email']) {
                	$row['email_validation_hash'] = generate_verify_email_hash($row['email']);
                	$row['is_email_validated'] = 0;
                }
                if(isset($_POST['password']) && $_POST['password']!=""){
                	$row['password'] = encrypt_password($this->input->post('password',true));
                	$row['is_encrypted'] = 2;
                }
                if(isset($_POST['account_status']) && $_POST['account_status']=="1"){
                	$row['account_msg'] = '';
                }else if(isset($_POST['account_status']) && $_POST['account_status']=="2"){
                	$row['account_msg'] = $this->input->post('account_msg',true);
                }
                $row['account_status'] = $this->input->post('account_status',true);
                $row['account_update_time'] = get_current_time();
                $this->general_model->update_record('users', $row, $this->input->post('token',true));
                if ($row['show_cpc']!=$profile['cpc']) {
                	/*echo "cpc changed updated now <br>";
                	print_arr($_POST);
            		print_arr($profile);
            		die();*/
            		update_cpc_on_ads_tbl($profile['id'], $row['cpc']);
                }
                set_session_successMsg("Profile updated successfully.");
            }
    	}
    	redirect(base_url().'users');
	}
	public function process_update_ad_code_request(){
		//print_arr($_POST);
		//die();
    	if (isset($_POST['submit']) && $_POST['submit']!="") {
            $this->form_validation->set_rules('ad_code', 'First Name', 'trim|required');
            if ($this->form_validation->run() == FALSE){
                    $login_errors = validation_errors();

                    $login_errors = str_replace_all("<p>", "<li>", $login_errors);

                    $login_errors = str_replace_all("</p>", "</li>", $login_errors);

                    $login_errors = "<ul>".$login_errors."</ul>";
                    set_session_error_msg($login_errors);

            }else{                
                $row['ad_code'] = htmlspecialchars($_POST['ad_code']);
                $row['account_update_time'] = get_current_time();
                //print_arr($row);
                //die();
                $this->general_model->update_record('users', $row, $this->input->post('token',true));
                set_session_successMsg("Profile updated successfully.");
            }
    	}
    	redirect(base_url().'users');
	}
	public function del($id=NULL){
		if ($id!=NULL) {
			$tracking_ids = array();
			$result = $this->general_model->get('publisher_domains', array("publisher_id"=>$id),'','','','distinct(tracking_id) as tracking_id');
			for ($i=0; $i <count($result) ; $i++) { 
				$this->general_model->del_record('ads_logs', array("tracking_id"=>$result[$i]['tracking_id']));
				$this->general_model->del_record('ads_logs_history', array("tracking_id"=>$result[$i]['tracking_id']));
			}
			$this->general_model->del_record('ads_by_date', array("user_id"=>$id));
			$this->general_model->del_record('publisher_domains', array("publisher_id"=>$id));
			$this->general_model->del_record('payment_new', array("publisher_id"=>$id));
			$this->general_model->del_record('users', array("id"=>$id));
			set_session_successMsg("User deleted successfully.");
			set_session_sweetalert_delet_request('User is deleted.');
		}
		if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!="") {
    		redirect($_SERVER['HTTP_REFERER']);
		}else{
    		redirect(base_url().'users');
    	}
	}
	public function add(){
		process_create_account_admin();
		$data['bread_crumb'] = array('title'=>'User Management','url'=>base_url().'users');
		$data['title'] = "Add New User";
		load_view('/users/add', $data);
	}
}

