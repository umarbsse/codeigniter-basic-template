<?php
    function universal_login_validator(){
        $logined_checked_controllers = array('dashboard','setting','users','clicks','ip','csrf','captcha','auth_logs','brute_force','realtimelogin');
        $controller = get_class__method_name();
        if (in_array($controller['controller'], $logined_checked_controllers)){
            is_user_logined_redirect();
        }
    }
    function auth_user(){
        //echo check_if_user_allowed_to_login();
        $response = is_valid_auth_request();
        if ($response==true) {
            if (check_if_user_allowed_to_login()==true) {
                $ci = &get_instance();
                $login_param = $ci->input->post('login_param',true);
                $password = $ci->input->post('password',true);
                $result = $ci->general_model->auth_user($login_param, $password);
                if (count($result)==0) {
                    $text="Invalid Login Credentials!";
                    insert_login_logs_record(array('text'=>$text,'type'=>2)); 
                    set_session_error_msg($text);
                    generate_crf_token_cus();
                }else if(count($result)==1 && $result[0]['account_status']=="2"){
                    //$account_disable_msg = $result[0]['account_msg'];
                    $text = "Your account is disable contact administrator."; 
                    insert_login_logs_record(array('text'=>"Acount disable login prevent.[".$result[0]['account_msg'].']','type'=>2));
                    set_session_error_msg($text);
                    generate_crf_token_cus();
                }else{
                    $result = $result[0];
                    initiate_user_login_session($result);
                    ci_unset_session('crf_token');
                    destroy_captcha();
                    insert_login_logs_record(array('text'=>"$login_param logined into account",'type'=>1)); 
                    redirect(base_url()."dashboard");
                }
            }else{
                $text="Login disabled wait for few minutes before login again!";
                insert_login_logs_record(array('text'=>$text,'type'=>2)); 
                set_session_error_msg($text);
                generate_crf_token_cus();
            }
        }else{
            generate_crf_token_cus();
        }
    }
    function auth_user_new(){
        $response = is_valid_auth_request();
        if ($response==true) {
            if (check_if_user_allowed_to_login()==true) {
                $ci = &get_instance();
                $login_param = $ci->input->post('login_param',true);
                $password = $ci->input->post('password',true);
                $result = $ci->general_model->auth_user_email($login_param);
                if (count($result)==0) {
                    login_failed_process("Invalid Login Credentials!", "Invalid Login Credentials!");
                }else{
                    if( isset($result[0]['account_status']) && $result[0]['account_status']=="2"){
                        login_failed_process("Your account is disable contact administrator.", "Acount disable login prevent.[".$result[0]['account_msg'].']');
                    }else{
                        $is_password_encrypted = $result[0]['is_encrypted'];
                        if ($is_password_encrypted=="2") {
                            if (verify_password($password, $result[0]['password'])==true) {
                                $result = $result[0];
                                initiate_user_login_session($result);
                                ci_unset_session('crf_token');
                                destroy_captcha();
                                insert_login_logs_record(array('text'=>$login_param." logined into account",'type'=>1)); 
                                redirect(base_url()."dashboard");
                            }else{
                                login_failed_process("Invalid Login Credentials!", "Invalid Login Credentials!");
                            }
                        }else{
                            // VERIFY PLAIN PASSWORD
                            if ($result[0]['password']===$password) {
                                $result = $result[0];
                                initiate_user_login_session($result);
                                ci_unset_session('crf_token');
                                destroy_captcha();
                                insert_login_logs_record(array('text'=>$login_param." logined into account",'type'=>1)); 
                                redirect(base_url()."dashboard");
                            }else{
                                login_failed_process("Invalid Login Credentials!", "Invalid Login Credentials!");
                            }
                        }
                    }
                }
            }else{
                $text="Login disabled wait for few minutes before login again!";
                login_failed_process($text, $text);
            }
        }else{
            generate_crf_token_cus();
        }
    }
    function login_failed_process($public_text, $logs_text){
        //$text="Invalid Login Credentials!";
        insert_login_logs_record(array('text'=>$logs_text,'type'=>2)); 
        set_session_error_msg($public_text);
        generate_crf_token_cus();
    }
    function check_if_user_allowed_to_login(){
        if( get_value('disable_login_all_users')==2){
            return true;
        }
        return false;
    }
    function is_valid_auth_request(){
        $user_type=2;
        $response = array('is_valid_request'=>false,'response_msg'=>'');
        $ci = &get_instance();
        if (
            isset($_POST['login_param']) && $_POST['login_param']!="" && 
            isset($_POST['password']) && $_POST['password']!=""
        ) {
            if (is_google_recaptcha_enable()) {
                if (verify_google_recaptcha()==false) {
                    $text = "Incorrect captcha challange!";
                    set_session_error_msg($text);
                    insert_login_logs_record(array('text'=>"Acount login prevent.[".$text."]",'type'=>2));
                    return false;
                }
            }
            
            $is_blacklist = is_blacklist_ip_request();
            if ($is_blacklist==false) {
                $brutforce = is_request_brutforce();
                if ($brutforce['response']==true) {              
                    if (validate_captcha() == true) {
                        if (verify_crf_token()===true) { 
                            return true;                  
                        }else{
                            $text = "Invalid Token!";
                            set_session_error_msg($text);
                            insert_login_logs_record(array('text'=>"Acount login prevent.[".$text."]",'type'=>2));
                            return false;
                        }
                    }else{              
                        $text = "Incorrect captcha challange!";
                        set_session_error_msg($text);
                        insert_login_logs_record(array('text'=>"Acount login prevent.[".$text."]",'type'=>2));
                        return false;
                    } 
                }else{
                    $text = "Your account is locked retry with correct credential after 10 minutes!";
                    $text = $brutforce['msg'];
                    set_session_error_msg($text);
                    insert_login_logs_record(array('text'=>"Acount login prevent.[Brute Force attempt]",'type'=>2));
                    return false;
                }
            }else{
                    $text = "Your account is locked, contact system administrator!";
                    //$text = $brutforce['msg'];
                    set_session_error_msg($text);
                    insert_login_logs_record(array('text'=>"Acount login prevent.[Black List IP attempt]",'type'=>2));
                    return false;
            }




        }else{
            return false;
        }
    }
    function verify_crf_token(){
        if (get_value('is_crf_enable')=="1") {
            return true;
        }
        $hash = ci_get_session('crf_token');
        if ($hash=="" || $_POST['crf_token']=="" || $_POST['crf_token']!=$hash) {
            return false;
        }
        return true;
    }
    function generate_crf_token_cus(){
        $token = base64_encode(md5(generateRandomString(20),true));
        ci_set_session('crf_token',$token);
    }
    function get_csrf_token(){
        if (get_value('is_crf_enable')=="1") {
            return '';
        }
        return ci_get_session('crf_token');
    }
    function is_user_logined(){
        $ci = &get_instance();
        if(!$ci->load->is_loaded('session')){
            $ci->load->library('session');
        } 
        if ($ci->session->has_userdata('logged_in') && $ci->session->userdata('logged_in')==true && $ci->session->has_userdata('hash') && $ci->session->userdata('hash')==generate_login_hash()) {
               return true;
        }
        //set_session_error_msg('Login to Continue!');
        return false;

    }
    function is_user_logined_redirect(){
        if (is_user_logined()==false) {
            redirect(base_url().'account');
        }
    }
    function only_admin_allowed(){

        $ci = &get_instance();

        $ci->load->library('session');

        if ($ci->session->has_userdata('userType') && $ci->session->userdata('userType')=="admin") {

            #    ;

        }else{

            redirect(base_url());

        }

    }
    function sanitize_session_data($data){
        $filter_data = array();
        $data = explode(";", $data);
        $array = array('name','email','user_type','platform','browser','version','login_Timestamp');
        //print_arr($array);
        //print_arr($data);
        //die();
        for ($i=0; $i <count($array) ; $i++) {
            for ($j=0; $j <count($data) ; $j++) { 
                if (string_find($data[$j], $array[$i])) {
                    $temp = explode("|", $data[$j]);
                    if (count($temp)>1) {
                            // code...
                            //print_arr($temp );
                        if ($array[$i]=='login_Timestamp') {
                            $temp = explode(':"', $temp[1]);
                            //print_arr($temp );
                            $val = $temp[count($temp)-1];
                            //print_arr($temp );
                        }else{
                            //print_arr($temp );
                            $temp = explode(":", $temp[1]);
                            $val = $temp[count($temp)-1];
                        }
                        $val = str_replace('"', "", $val);
                        $filter_data[$array[$i]] = $val;
                    }
                }
            }
        }
        //print_arr($filter_data);//print_arr($data);
        return $filter_data;
    }
    function encrypt_password($plain_password){
        $pwd_peppered = hash_hmac("sha256", $plain_password, get_pepper_str());
        $pwd_hashed = password_hash($pwd_peppered, PASSWORD_ARGON2ID);
        return $pwd_hashed;
    }
    function verify_password($plain_password, $hased_password){
        // login.php
        //$pepper = getConfigVariable("pepper");
       // $pwd = $_POST['password'];
        $pwd_peppered = hash_hmac("sha256", $plain_password, get_pepper_str());
        //$pwd_hashed = get_pwd_from_db($username);
        if (password_verify($pwd_peppered, $hased_password)) {
            return true;
        }else {
            return false;
        }
    }
    function get_pepper_str(){
        return '45309jdfs0j234gSDGSdsdsd!@#2asdfga2423$@#$aewfzdszmdfsib,d.f,g.sagar234#@$@#%523456';
    }
    function get_encrypt_cost(){
        $timeTarget = 0.350; // 350 milliseconds
        $cost = 10;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);
        echo "Appropriate Cost Found: " . $cost;
    }
    function check_for_strong_password($password){
       # minimum length should be 8.
       # at least one uppercase letter.
       # at least one lowercase letter.
       # at least one digits, and
       # at least one special character.
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/'; 
        if (preg_match($pattern, $password)) { 
            return true;
        } else { 
            return false;
        }
    }
    function check_if_user_have_strong_password(){
        $ip = get_real_ip();
        //echo $ip;
        if ($ip=="39.43.208.29") {
            $temp = get_class__method_name();
            $controller = $temp['controller'];
            $method = $temp['method'];
            if ($controller=="setting" && ($method=="password" || $method=="update_password" )) {
                //echo "paasswrod page";
            }else{
                #set_session_error_msg('Your password is weak! Please update your password to proceed');
                //echo "not paasswrod page";
                redirect(base_url().'setting/password');
                //redirect("https://payperinstall.net/setting/password")
            }
        }
    }
?>