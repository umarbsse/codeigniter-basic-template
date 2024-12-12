<?php
    function is_google_recaptcha_enable(){
        //"139.43.169.41"==google_recaptcha_get_real_ip() && 
        if (is_user_ip_in_white_list_ip()) {
            return false;
        }
        else if (get_value('is_google_recaptcha_enable')==2 && ready_to_show_google_recaptcha()==true) {
            return true;
        }
        return false;
    }
    function is_google_recaptcha_public_key(){
        return get_value('google_recaptcha_public_key');
    }
    function get_html_if_google_recaptcha_enable(){
        echo '<center><div class="g-recaptcha" data-sitekey="'.is_google_recaptcha_public_key().'"></div><br/></center>';
    }
    function ready_to_show_google_recaptcha(){
        return true; //Return true and always show captcha
        $ci = &get_instance();
        $result = get_total_count_db($ci->general_model->check_if_user_made_failed_account_access_attempt('count(id) as total',2,google_recaptcha_get_real_ip(),last_minutes_failed_time_google_recaptcha()));
        if ($result>0) {
            return true;
        }return false;
    }
    function last_minutes_failed_time_google_recaptcha(){
        return get_value('last_minutes_failed_time_google_recaptcha');
    }
    function google_recaptcha_whitelist_ips(){
        //return array("39.43.165.66");
        return array();
    }
    function is_user_ip_in_white_list_ip(){
        if (in_array(google_recaptcha_get_real_ip(), google_recaptcha_whitelist_ips())){
            return true;
        }return false;        
    }
    function verify_google_recaptcha(){
        if (isset($_POST['g-recaptcha-response'])) {
            $ci = &get_instance();
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret='.get_value('google_recaptcha_secret_key').'&response='.$ci->input->post('g-recaptcha-response',true);
            $response = file_get_contents($url);
            $response = json_decode($response);if ($response->success == true) {
                return true;
            }
        }
        return false;        
    }

    function google_recaptcha_get_real_ip(){

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
?>