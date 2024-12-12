<?php
    function insert_login_logs_record($array){
        $ci = &get_instance();
        $array = array_merge($array,get_user_agent_date());
        $array['url'] = "/".str_replace(base_url(), "", $array['url']);
        $array['ip']=get_real_ip();
        $array['added_on']=get_current_time();
        $ci->general_model->insert_record('auth_logs', $array);
    }
    function logs_text_badges($text,$type){
        switch ($type) {
          case "1":
            return '<span class="badge bg-green">'.$text.'</span>';
            break;
          case "2":
            return '<span class="badge bg-danger">'.$text.'</span>';
            break;
          case "3":
            return '<span class="badge bg-yellow text-black">'.$text.'</span>';
            break;
          case "4":
            return '<span class="badge bg-primary">'.$text.'</span>';
            break;
          default:
            return '<span class="badge bg-black">'.$text.'</span>';
        }
    }
    
?>