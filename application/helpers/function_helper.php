<?php

    function view_file_path($filename=NULL){

        $file = "";

        if ($filename!=NULL){

            $file = APPPATH.'views/'.$filename.'.php';

        }

        return $file;

    }
	
    function load_view($view_path,$data, $template_directory=NULL){
        if($template_directory==NULL){
            $file_name = TEMPLATE.$view_path;
        }else{
            $file_name = $template_directory.$view_path;
        }

        $file_path = view_file_path($file_name);

        if ( ! file_exists($file_path)){

            show_404();
            die();

        }
        $ci = &get_instance();
        $data = $ci->security->xss_clean($data);
        $ci->load->view($file_name,$data);
    }

    function execution_time_start(){

    	$name = "execution_start_time";

    	$value = microtime(true);

    	ci_set_session($name,$value);

    }

    function calculate_execution_time(){

        $name = "execution_start_time";

        $start_time = ci_get_session($name);

        $end_time = microtime(true);

        return round($end_time-$start_time,4);

    }

    function get_secret(){

        return "lkjflsdfdskhfusdhfiusdhfisduhilusdh2342387492837rfsdhfdshiolksdjflksdnf21342423432@#2343";

    }

    function create_hash($string){

        $secret = get_secret();

        $md5 = base64_encode(md5($string.$secret,true));

        return $md5;

    }

    function get_base_path_ci(){

    	return str_replace("application/", "", APPPATH);

    }


    function generate_login_hash(){

        $ip = get_real_ip();

        $data = get_user_agent_date();

        $hash = md5(get_hash_key().$ip.$data['agent_string']);

        return $hash;

    }

    function get_hash_key(){

        return "sdkjsdlfiuoiwerq948r0hsd#@eflvsmdfjdsafksdkfi@@@jdfvDDfeerww";

    }


    


    function print_last_db_query(){

        $ci = &get_instance();

        echo $ci->db->last_query();

    }

    function get_class__method_name(){

        $array = array();

        $ci = &get_instance();

        $array['controller'] = $ci->router->fetch_class();

        $array['method'] = $ci->router->fetch_method();

        return $array;

    }

    function only_alphabets($name){

        if (preg_match("/^[a-zA-Z ]*$/", $name) === 1) {

            return true;

        } else {

            return false;

        }

    }

    function is_valid_mac($mac)

    {

      // 01:23:45:67:89:ab

      if (preg_match('/^([a-fA-F0-9]{2}:){5}[a-fA-F0-9]{2}$/', $mac))

        return true;

      // 01-23-45-67-89-ab

      if (preg_match('/^([a-fA-F0-9]{2}\-){5}[a-fA-F0-9]{2}$/', $mac))

        return true;

      // 0123456789ab

      else if (preg_match('/^[a-fA-F0-9]{12}$/', $mac))

        return true;

      // 0123.4567.89ab

      else if (preg_match('/^([a-fA-F0-9]{4}\.){2}[a-fA-F0-9]{4}$/', $mac))

        return true;

      else

        return false;

    }
    function is_valid_ip_address($ip){
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        }
        return false;
    }

    function str_replace_all($search, $replace, $subject){

        $str = explode($search, $subject);

        return  implode($replace, $str);

    }

    function str_to_epoch_time($time){

        return strtotime($time);

    }

    function string_find($big, $small){

        $pos = strpos($big, $small);

        if ($pos === false) {

            return false;

        }

        else {

            return true;

        }

    }

    function string_start_with($big, $small){

        if (strpos($big, $small) === 0) {

            return true;

        }

        return false;

    }

    function parse_date_time($date){

        $date = str_replace("\n", "", $date);

        $date=  date_create($date);

        $date= date_format($date,"Y-m-d H:i:s");

        return $date;

    }

    function print_arr($array){

        echo "<pre>";print_r($array);echo "</pre>";

    }

    function check_empty_field($var){

        if ($var=="" || $var==NULL) {

            return "N/A";

        }

        return $var;

    }

    function generateRandomString($length = 10) {

	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';

	    $charactersLength = strlen($characters);

	    $randomString = '';

	    for ($i = 0; $i < $length; $i++) {

	        $randomString .= $characters[rand(0, $charactersLength - 1)];

	    }

	    return $randomString;

	}
    function generateRandomString_alphabets($length = 10) {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $charactersLength = strlen($characters);

        $randomString = '';

        for ($i = 0; $i < $length; $i++) {

            $randomString .= $characters[rand(0, $charactersLength - 1)];

        }

        return $randomString;

    }
    

    function get_user_agent_date(){

        $ci = &get_instance();

        $ci->load->library("user_agent");

        $data['platform'] = $ci->agent->platform();

        $data['browser'] = $ci->agent->browser();

        $data['version'] = $ci->agent->version();

        $data['mobile'] = $ci->agent->mobile();

        $data['robot'] = $ci->agent->robot();

        $data['referrer'] = $ci->agent->referrer();

        $data['url'] = getCurrentUrl();
        $data['agent_string'] = $ci->agent->agent_string();

        return $data;

        //print_arr($data);

    }

    function getCurrentUrl() {

         $pageURL = 'http';

         if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}

         $pageURL .= "://";

         if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443" ) {

             $pageURL .=

             $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

         }

         else {

             $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

         }

         return $pageURL;

    }

    function get_total_count_db($result){

        if (count($result)==0) {

            return 0;

        }

        $count=$result[0];

        return $count['total'];

    }

    function get_current_time($formate=NULL){

        if ($formate==NULL) {

            return date("Y-m-d H:i:s");

        }

        return date($formate);

    }
    function get_yesterday_date($formate=NULL){
        if ($formate==NULL) {
            return date("Y-m-d",strtotime("-1 days"));
        }
        return date($formate,strtotime("-1 days"));
    }
    function get_dynamic_date($formate=NULL, $days, $plus){
        if ($formate==NULL) {
            return date("Y-m-d",strtotime($plus.$days." days"));
        }
        return date($formate,strtotime($plus.$days." days"));
    }

    function get_real_ip(){

        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet

        {

        $ip=$_SERVER['HTTP_CLIENT_IP'];

        }

        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy

        {

        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];

        }

        // check if isset REMOTE_ADDR and != empty

        elseif(isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] != '') && ($_SERVER['REMOTE_ADDR'] != NULL))

        {

            $ip = $_SERVER['REMOTE_ADDR'];

        // you're probably on localhost

        } else {

        $ip = "127.0.0.1";

        }

        return $ip;

    }

    function formatSizeUnits($bytes){

        if ($bytes >= 1073741824)

        {

            $bytes = number_format($bytes / 1073741824, 2) . ' GB';

        }

        elseif ($bytes >= 1048576)

        {

            $bytes = number_format($bytes / 1048576, 2) . ' MB';

        }

        elseif ($bytes >= 1024)

        {

            $bytes = number_format($bytes / 1024, 2) . ' KB';

        }

        elseif ($bytes > 1)

        {

            $bytes = $bytes . ' Bytes';

        }

        elseif ($bytes == 1)

        {

            $bytes = $bytes . ' Byte';

        }

        else

        {

            $bytes = '0 Bytes';

        }



        return $bytes;

    }

    function get_site_logo_path(){

        return base_url().'assets/carrent/images/logo.svg';

    }
    function get_support_email(){
        return "mailto:support@".get_naked_domain_from_base_url();
    }
    function get_site_name(){

        $temp =  get_naked_domain_from_base_url();
        //print_arr($temp);

        $temp = explode('.', $temp);
        //print_arr($temp);

        //$temp = implode('', $temp);

        return ucfirst($temp[0]);

    }
    function get_naked_domain_from_base_url(){

        $domain = base_url();

        $domain = str_replace("https://", "", $domain);

        $domain = str_replace("http://", "", $domain);

        $domain = explode("/", $domain);

        $domain = $domain[0];

        return $domain;

    }
    function get_success_badge($txt){
        return '<span class="badge bg-green" style="background-color: green !important;"><i class="fas fa-lg fa-fw me-3px fa-flag-checkered"></i> '.$txt.'</span>';
    }
    function get_danger_badge($txt){
        return '<span class="badge bg-danger" style="background-color: red !important;"><i class="fas fa-lg fa-fw me-3px fa-circle-xmark"></i> '.$txt.'</span>';
    }
    function get_warning_badge($txt){
        return '<span class="badge bg-yellow text-black"><i class="fas fa-lg fa-fw me-3px fa-triangle-exclamation"></i> '.$txt.'</span>';
    }
    function getDomain($ip,$url){
        if ($ip==$url) {
            return "";
        }
        $domain = "";
        $url = explode(".", $url);
        if (isset($url[count($url)-2]) && isset($url[count($url)-1])) {
            return $url[count($url)-2].".".$url[count($url)-1];
        }
        return "";
        
    }
    function datatable_per_page_results(){
        return 100;
    }
    function get_user_domain_status_calculate($user_id){
        $allowe_pre_approved_domain = 2;
        //1=Pending Approval, 2=Rejected , 3=Approved   
        if ($user_id!="") {
            //echo $user_id;
            $ci = &get_instance();
            $total =  get_total_count_db($ci->general_model->get('publisher_domains', array('publisher_id'=>$user_id),'','','','count(id) as total'));
            //echo $total." ".$allowe_pre_approved_domain."<br>";
            //die();
            if ($allowe_pre_approved_domain>$total) {
                return 3; //1=Pending Approval
            }else{
                return 1; //1=Approved
            }
        }
        return 1; //1=Pending Approval
    }
    function get_current_class_method($classname, $method){
        return str_replace_all($classname."::", "", $method);
    }
?>