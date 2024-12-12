<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Home extends CI_Controller {



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
		$data['title'] = 'Home Page';
		$data['slider_news'] = get_home_page_slider_news_list();
		$data['box_news'] = get_home_page_box_news_list();
		$data['caragory_news'] = get_home_page_caragory_news();
		$data['latest_news'] = array_merge(get_home_page_latest_news(),get_home_page_latest_news(),get_home_page_latest_news());
		load_view('/home/index', $data,PUBLIC_TEMPLATE);
	}
}

?>