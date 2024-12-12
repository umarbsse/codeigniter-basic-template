<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Users_roles extends CI_Controller {



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
		process_form_submit();
		$data['title'] = "User Roles Configuration";
		$data['class_method_list'] = get_class_methods_list();
        $data['result'] = $this->general_model->get('user_class_access');
		load_view('/users_roles/index', $data);
	}
}

