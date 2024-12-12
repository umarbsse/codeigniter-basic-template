<?php
    function is_request_brutforce(){
        $array = array('response' => true ,'msg'=>'' );
        $ci = &get_instance(); 
        $per_10_minutes_max_limit = get_value('brute_force_10_min_limit');
        $where["TIMESTAMPDIFF(MINUTE,added_on,'".get_current_time()."') <= "] = 10;
        $where["type"] = 2;
        $where["ip"] = get_real_ip();
        $per_10_minutes_requests = get_total_count_db($ci->general_model->get('auth_logs', $where, NULL, NULL, NULL,"count(id) as total"));
        //echo $ci->db->last_query();
        //die();
        if ($per_10_minutes_max_limit<=$per_10_minutes_requests) {
            $array['response'] = false;
            $array['msg'] = "Your account is locked retry with correct credential after 10 minutes!";
            //$array['msg'] = "[max_limit=$per_10_minutes_max_limit][current_requst=$per_10_minutes_requests]Your account is locked retry with correct credential after 10 minutes!";
        }
        $per_hour_max_limit = get_value('brute_force_1_hour_limit');
        $where["TIMESTAMPDIFF(MINUTE,added_on,'".get_current_time()."') <= "] = 60;
        $where["type"] = 2;
        $where["ip"] = get_real_ip();
        $per_hour_requests = get_total_count_db($ci->general_model->get('auth_logs', $where, NULL, NULL, NULL,"count(id) as total"));
        //echo $ci->db->last_query();
        //die();
        if ($per_hour_max_limit<=$per_hour_requests) {
            $array['response'] = false;
            $array['msg'] = 'Your account is locked retry with correct credential after 1 Hour!';
            //$array['msg'] = "[max_limit=$per_hour_max_limit][current_requst=$per_hour_requests]Your account is locked retry with correct credential after 1 Hour!";
        }
        return $array;
    }


    function process_brute_frcs_conf(){
        if (isset($_POST['submit']) && isset($_POST['brute_force_1_hour_limit']) && $_POST['brute_force_1_hour_limit']!="" && isset($_POST['brute_force_10_min_limit']) && $_POST['brute_force_10_min_limit']!="") {
            $ci = &get_instance(); 
            insert_value('brute_force_1_hour_limit',$ci->input->post('brute_force_1_hour_limit',true));
            insert_value('brute_force_10_min_limit',$ci->input->post('brute_force_10_min_limit',true));
            set_session_successMsg("IP added successfully!");
        }
    }
?>