<?php
    function get_account_type_badge($type,$txt){
        if ($type=="1") {
            return '<span class="badge bg-dark rounded-pill">'.$txt.'</span>';
        }else if ($type=="2") {
            return '<span class="badge bg-primary rounded-pill">'.$txt.'</span>';
        }else if ($type=="3") {
            return '<span class="badge bg-yellow rounded-pill">'.$txt.'</span>';
        }
    }
    function get_account_type_txt($type){
        if ($type=="1") {
            return 'Administrator';
        }else if ($type=="2") {
            return 'Publisher';
        }else if ($type=="3") {
            return 'Reseller';
        }
    }
    function get_account_status_txt($type){
        if ($type=="1") {
            return 'Active';
        }else if ($type=="2") {
            return 'Disable';
        }
    }
    function get_account_status_badge($type,$txt){
        if ($type=="1") {
            return '<span class="badge bg-green">'.$txt.'</span>';
        }else if ($type=="2") {
            return '<span class="badge bg-danger">'.$txt.'</span>';
        }
    }
    function get_total_users(){
        $ci = &get_instance();
        return get_total_count_db($ci->general_model->get('users', '','','','','count(id) as total'));
    }
?>