<?php
    function initiate_user_login_session($result){
        $ci = &get_instance();
        $ci->load->library('session');
        $session_data = get_user_agent_date();
        $session_data['name'] = $result['firstname']." ".$result['lastname'];
        $session_data['user_type'] = $result['account_type'];
        $session_data['id'] = $result['id'];
        $session_data['tracking_id'] = $result['tracking_id'];
        $session_data['client_engaged_by'] = $result['client_engaged_by'];
        $session_data['logged_in'] = TRUE;
        $session_data['login_Timestamp'] = get_current_time();
        $session_data['email']=$result['email'];
        $session_data['hash'] = generate_login_hash();
        $ci->session->set_userdata($session_data);
    }
    function initiate_switch_user_login_session($result){
        $ci = &get_instance();
        $ci->load->library('session');
        $admin_user_data = $_SESSION;
        $session_data = get_user_agent_date();
        $session_data['name'] = $result['firstname']." ".$result['lastname'];
        $session_data['user_type'] = $result['account_type'];
        $session_data['id'] = $result['id'];
        $session_data['tracking_id'] = $result['tracking_id'];
        $session_data['client_engaged_by'] = $result['client_engaged_by'];
        $session_data['logged_in'] = TRUE;
        $session_data['login_Timestamp'] = get_current_time();
        $session_data['email']=$result['email'];
        $session_data['hash'] = generate_login_hash();
        $session_data['switch_user'] = $admin_user_data;
        $ci->session->set_userdata($session_data);
    }
    function destory_switch_user_login_session(){
        $ci = &get_instance();
        $ci->load->library('session');
        if (isset($_SESSION['switch_user']['user_type']) &&  ($_SESSION['switch_user']['user_type']=="1" || $_SESSION['switch_user']['user_type']=="3")) {
            $session_data = $_SESSION['switch_user'];
            $ci->session->set_userdata($session_data);
            ci_unset_session('switch_user');
        }

        //print_arr($_SESSION);


        //die();
        //$session_data = $_SESSION['switch_user'];
        //$ci->session->set_userdata($session_data);
        //ci_unset_session('switch_user');
        //print_arr($_SESSION);
        //echo "string";
        //die();
    }

    function destory_user_login_session(){
        $array = array();
        foreach ($_SESSION as $key => $value) {
            array_push($array, $key);
        }
        //die();
        $ci = &get_instance();
        //$ci->session->unset_userdata(array('name','user_type','logged_in', 'hash'));
        $ci->session->unset_userdata($array);
    }

    function ci_unset_session($name){
        $ci = &get_instance();
        $ci->session->unset_userdata($name);

    }

    function ci_get_session($name){

        $ci = &get_instance();


        if ($ci->session->has_userdata($name) && $ci->session->userdata($name)!="" && $ci->session->userdata($name)!=NULL) {

            return $ci->session->userdata($name);

        }

        return "";

    }

    function ci_set_session($name,$value){

        $ci = &get_instance();

        $ci->session->set_userdata($name, $value);

    }

    function get_session_info_msg_var_name(){
        return 'info_msg';
    }
    function set_session_info_msg($msg){
        ci_set_session(get_session_info_msg_var_name(),$msg);
    }
    function display_session_info_msg(){
        $val = ci_get_session(get_session_info_msg_var_name());
        if ($val!="") {
            ci_unset_session(get_session_info_msg_var_name());
            return display_session_info_alert($val);
        }
    }

    function get_session_error_msg_var_name(){
        return 'error_msg';
    }
    function set_session_error_msg($msg){
        ci_set_session(get_session_error_msg_var_name(),$msg);
    }
    function display_session_error_msg(){
        $val = ci_get_session(get_session_error_msg_var_name());
        if ($val!="") {
            ci_unset_session(get_session_error_msg_var_name());
            return display_session_error_alert($val);
        }
    }
    function get_session_successMsg_var_name(){
        return 'successMsg';
    }
    function set_session_successMsg($msg){
        ci_set_session(get_session_successMsg_var_name(),$msg);
    }
    function display_session_successMsg(){
        $val = ci_get_session(get_session_successMsg_var_name());
        if ($val!="") {
            ci_unset_session(get_session_successMsg_var_name());
            return display_session_successMsg_alert($val);
        }
    }
    function display_session_info_alert($msg=null){
        return '<div class="alert alert-warning alert-dismissible fade show h-100 mb-15px"><b><i class="fa fa-times"></i> Info:</b> '.$msg.'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    function display_session_error_alert($msg=null){
        return '<div class="alert alert-danger alert-dismissible fade show h-100 mb-15px"><b><i class="fa fa-times"></i> Error</b> '.$msg.'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    function display_session_successMsg_alert($msg=null){
        return '<div class="alert alert-success alert-dismissible fade show h-100 mb-15px"><b><i class="fa fa-check"></i> Success</b> '.$msg.'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    function display_session_infoMsg_alert($msg=null){
        return '<div class="alert alert-info alert-dismissible fade show h-100 mb-15px"><b><i class="fa fa-bullhorn"></i> Announcement</b> '.$msg.'</div>';
    }
    function get_session_sweetalert_delet_var_name(){
        return 'sweet_alert_delete_req';
    }
    function set_session_sweetalert_delet_request($msg){
        ci_set_session(get_session_sweetalert_delet_var_name(),$msg);
    }
    function display_session_sweetalert_delet_request(){
        $val = ci_get_session(get_session_sweetalert_delet_var_name());
        if ($val!="") {
            ci_unset_session(get_session_sweetalert_delet_var_name());
            return $val;
        }
    }
    function clean_ci_session_tbl(){
        $ci = &get_instance();
        $time = date('Y-m-d H:i:s', (time() - 60 * 10));
        $time_empty_session = date('Y-m-d H:i:s', (time() - 60 * 2));
        $query= "DELETE FROM `ci_sessions` WHERE ";
        $query.= "( ( LOWER(data) NOT LIKE '%platform|%' ESCAPE '!') AND (from_unixtime(ci_sessions.timestamp)<'".$time_empty_session."') ) OR ";
        $query.= "( ( LOWER(data) LIKE '%logged_in|%' ESCAPE '!') AND (from_unixtime(ci_sessions.timestamp)<'".$time."') ) ";
        $ci->db->query($query);
    }
?>