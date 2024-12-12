<?php
    
    function is_blacklist_ip_request(){
        $ci = &get_instance();
        $is_exist = get_total_count_db($ci->general_model->get('black_list_ips', array('ip'=>get_real_ip()), NULL, NULL, NULL,"count(id) as total"));
        if ($is_exist==0) {
            return false;
        }
        return true;
    }
    
    function process_create_blacklist_ip(){
        if (isset($_POST['submit']) && isset($_POST['ip']) && $_POST['ip']!="") {
            $ci = &get_instance();
            $ci->form_validation->set_rules('ip', 'IP Address', 'trim|required');
            if ($ci->form_validation->run() == FALSE){
                $login_errors = validation_error_process(validation_errors());
                set_session_error_msg($login_errors);
                redirect(base_url().'black_list_ip/add');

            }else{
                $row['ip'] = $ci->input->post('ip',true);
                $row['added_on'] = get_current_time();
                $ci->general_model->insert_record('black_list_ips', $row);
                set_session_successMsg("IP added successfully!");
                redirect(base_url().'black_list_ip');
            }
        }
    }
?>