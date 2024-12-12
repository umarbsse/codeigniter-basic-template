<?php
    
    function create_account(){
        $response = is_valid_reg_account_request();
        if ($response==true) {
            //print_arr($_POST);
            //die();
            $response = process_create_account();
            if ($response['status'] == false){
                $login_errors = $response['msg'];
                $msg = '';
                for ($i=0; $i <count($login_errors) ; $i++) { 
                    $msg.='<p>'.$login_errors[$i].'</p>';
                }
                //ci_set_session('error_msg',$msg);
                generate_crf_token_cus();
                set_session_error_msg($msg);
                insert_login_logs_record(array('text'=>"Acount register prevent [Invalid form data submitted]",'type'=>2));
                redirect(base_url().'account/register');
            }else{
                ci_set_session('successMsg',$response['msg']);
            $ci = &get_instance();
                insert_login_logs_record(array('text'=>"New account registered from ".$ci->input->post('email',true),'type'=>1)); 
                redirect(base_url().'account');
            }
        }else{
            generate_crf_token_cus();
        }
    }
    function process_create_account(){
        $array['status'] = false;
        $array['msg'] = '';
            $ci = &get_instance();

            $ci->form_validation->set_rules('firstname', 'First Name', 'trim|required|max_length[50]');

            $ci->form_validation->set_rules('lastname', 'Last Name', 'trim|max_length[50]');

            $ci->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]|email_unique');

            $ci->form_validation->set_rules('password', 'Password', 'required|min_length[8]|matches[repassword]|password_process');
            $ci->form_validation->set_rules('repassword', 'Password Confirmation', 'trim|required');
            if ($ci->form_validation->run() == FALSE){
                $login_errors = validation_error_process(validation_errors());
                $array['status'] = false;
                $array['msg'] = $login_errors;

            }else{

                $row['firstname'] = $ci->input->post('firstname',true);

                $row['lastname'] = $ci->input->post('lastname',true);

                $row['email'] = $ci->input->post('email',true);

                $row['email_validation_hash'] = generate_verify_email_hash($row['email']);
              //  $row['email_validation_hash'] = 'abc';

                $row['password'] = encrypt_password($ci->input->post('password',true));
                $row['is_encrypted'] = 2;

                



                $row['account_type'] = 2;

                $row['account_status'] = 2;
                $row['account_msg'] = 'Your account is under review for approval';

                $row['account_creation_time'] = get_current_time();

                $row['account_update_time'] = get_current_time();

                $ci->general_model->insert_record('users', $row);

               // send_signup_email($row['firstname'].' '.$row['lastname'],$row['email'],$row['email_validation_hash']);
                $array['status'] = true;
                $array['msg'] = "Account Created, login to continue!";
            }
            return $array;
    }

    function email_unique($input) {
        $ci = &get_instance();
        $where["email"] = $ci->input->post('email',true);
        if( (is_admin_user()==true  || is_reseller_user()==true) && isset($_POST['token']) && $_POST['token']!=""){
            $where["id<>"] = $ci->input->post('token',true);
        }else if (ci_get_session('id')!="") {
            $where["id<>"] = ci_get_session('id');
        }
        $total = get_total_count_db($ci->general_model->get('users', $where, NULL, NULL, NULL,'count(*) as total'));
        if ($total>0) {
            $ci->form_validation->set_message('email_unique', 'Email Already Exist.');
            return false;
        }
        return true;
    }

    function password_process($input) {
        $ci = &get_instance();
        if (check_for_strong_password($ci->input->post('password',true))) {
            return true;
        }else{
            $ci->form_validation->set_message('password_process', password_error_msg_secure());
            return false;
        }
    }
    function password_error_msg_secure(){
        return '<ul><li>Your Password length must be of 8 characters</li><li>Your password must contain at least one uppercase and one lowercase letter</li><li>Your password must contain at least one digits and one special character</li></ul>';
    }
    function tracking_id_unique($input) {
        $ci = &get_instance();

        //$email = $ci->input->post('email',true);
        $where["tracking_id"] = $ci->input->post('tracking_id',true);
        if((is_admin_user()==true  || is_reseller_user()==true) && isset($_POST['token']) && $_POST['token']!=""){
            $where["id<>"] = $ci->input->post('token',true);
        }else if (ci_get_session('id')!="") {
            $where["id<>"] = ci_get_session('id');
        }

        $total = get_total_count_db($ci->general_model->get('users', $where, NULL, NULL, NULL,'count(*) as total'));

        if ($total>0) {

            $ci->form_validation->set_message('tracking_id_unique', 'Tracking ID Already Exist.');

            return false;

        }

        return true;

    }
    function validation_error_process($string){
        $errors = array();
        $array = explode("<p>", $string);
        for ($i=0; $i <count($array) ; $i++) {
            $temp = trim($array[$i]);
            if ($temp!="") {
                $temp = str_replace("</p>", "", $temp);
                array_push($errors, $temp);
            }
        }
        return $errors;
    }

    function is_valid_reg_account_request(){
        $response = array('is_valid_request'=>false,'response_msg'=>'');
        $ci = &get_instance();
        if(isset($_POST['submit']) && $_POST['submit']!="" && isset($_POST['crf_token']) && $_POST['crf_token']!=""){
            $method = get_class__method_name();
            $method = $method['method'];
            if ($method=="forgot") {
                $temp_text = "Forgot password reset prevent ";
            }else{
                $temp_text = "Acount register prevent ";
            }
            if (is_google_recaptcha_enable()) {
                if (verify_google_recaptcha()==false){
                    $text = "Incorrect captcha challange!";
                    set_session_error_msg($text);
                    insert_login_logs_record(array('text'=>$temp_text."[".$text."]",'type'=>2));
                    return false;
                }
            }
            if (verify_crf_token()===true) {
                return true;
            }else{
                $text = "Invalid Token!";
                set_session_error_msg($text);
                insert_login_logs_record(array('text'=>$temp_text."[".$text."]",'type'=>2));
                return false;
            }
        }else{
            return false;
        }
    }
    function generate_verify_email_hash($email){

        $ip = get_real_ip();

        $data = get_user_agent_date();

        $time = microtime(true);

        $hash = md5(get_hash_key().$email.$ip.$time.$data['agent_string'],false);

        return $hash;

      }

    function forgot_password_form_handle(){
        $response = is_valid_reg_account_request();
        if ($response==true) {
            $ci = &get_instance();
            $result = $ci->general_model->get('users', array("email"=>$ci->input->post('email',true)), NULL, NULL, NULL,'*');
            if (count($result)==1) {
               // print_arr($result);
               // print_arr($_POST);
                $email = $result[0]['email'];
                $row['is_reset_password'] = 1;
                $row['reset_request_time'] = get_current_time();
                $row['reset_password_param'] = generate_password_reset_hash($result[0]['id'],$email);
                $ci->general_model->update_record('users', $row, $result[0]['id']);
                set_session_successMsg("Soon, you'll get password reset email, if your account exist!");
                insert_login_logs_record(array('text'=>"Forgot password reset success [From $email]",'type'=>1));
                //SEND RESET MAIL
                prepare_password_reset_mail($email, $result[0]['firstname'],$row['reset_password_param']);
            }else{
                generate_crf_token_cus();
                set_session_successMsg("Soon, you'll get password reset email, if your account exist!");
                insert_login_logs_record(array('text'=>"Forgot password reset prevent [Non existent email address submitted ".$ci->input->post('email',true)."]",'type'=>2));
            }
        }else{
            generate_crf_token_cus();
        }
    }
    function generate_password_reset_hash($id,$email){
      $ip = get_real_ip();
      $data = get_user_agent_date();
      $time = microtime(true);
      $hash = md5(get_hash_key().$id.$email.$ip.$time.$data['agent_string'],false);
      return $hash;
    }
    function prepare_password_reset_mail($email, $name,$hash){
        $expire_in_hour = password_expire_in_hour();
        $reset_code = $hash;
        $preheader_text = 'Use this link to reset your password. The link is only valid for '.$expire_in_hour.' hours.';
        $email = $email;
        $name = $name;
        $url['text']= 'Reset your password';
        $url['path']= base_url().'account/reset_password?param='.$reset_code;
        $html = '<p>You recently requested to reset your password for your account. Use the button below to reset it. <strong>This password reset is only valid for the next '.$expire_in_hour.' hours.</strong></p>';
        $body_html = get_email_universal_mail_html($preheader_text, $name,$html,$url );
        $body_plain_text = get_email_reset_mail_plain_text($url['path']);
        $to = $email;
        $body_html = $body_html;
        $body_plain_text = $body_plain_text;
        $subject='Your Password reset request recieved.';
        send_mail($to,$subject,$body_html,$body_plain_text);
        //echo $body_html;
        //die();
    }

    function validate_password_expire_param($token){
        $ci = &get_instance();
        $where = array('is_reset_password'=>1,'reset_password_param'=>$token);
        $result = $ci->general_model->get('users', $where);
        if (count($result)==0) {
            return false;
        }else if(password_link_time_expire($result[0]['reset_request_time'])==true){
            $row['is_reset_password'] = 0;
            return false;
        }else{
            return true;
        }
    }
    function password_link_time_expire($time_ago){
        $link_expire_in_hours = intval(password_expire_in_hour()*60); // 3 Hours
        $time_ago = strtotime($time_ago);
        $cur_time   = time();
        $time_elapsed   = $cur_time - $time_ago;
        $seconds    = $time_elapsed;
        $minutes    = intval(round($time_elapsed / 60 ));
        if ($minutes>=$link_expire_in_hours) {
            return true;
        }
        return false;
    }
    function password_expire_in_hour(){
        return 12;
    }
    function get_email_reset_mail_plain_text($reset_url){
        $html='If you’re having trouble with the button above, copy and paste the URL below into your web browser         '.$reset_url;
        return $html;
    }

    function is_valid_password_request(){
        $user_type=2;
        $response = array('is_valid_request'=>false,'response_msg'=>'');
        $ci = &get_instance();
        if (isset($_POST['submit']) && $_POST['submit']!="" && isset($_POST['crf_token']) && $_POST['crf_token']!="") {
            $temp_text = "Password reset prevent ";
            if (is_google_recaptcha_enable()) {
                if (verify_google_recaptcha()==false){
                    $text = "Incorrect captcha challange!";
                    set_session_error_msg($text);
                    insert_login_logs_record(array('text'=>$temp_text."[".$text."]",'type'=>2));
                    return false;
                }
            }
            if (verify_crf_token()===true) {
                    return true;
            }else{                    
                $text = "Invalid Token!";
                set_session_error_msg($text);
                insert_login_logs_record(array('text'=>$temp_text."[".$text."]",'type'=>2));
                return false;
            }
        }else{
            return false;
        }
    }

    function update_new_password(){
        $ci = &get_instance();
        $response = is_valid_password_request();
        if ($response==true && true==validate_password_expire_param($ci->input->post('reset_password_pram',true))) {
            $ci->form_validation->set_rules('password', 'Password', 'required|min_length[8]|matches[repassword]|password_process');
            $ci->form_validation->set_rules('repassword', 'Password Confirmation', 'trim|required');
                if ($ci->form_validation->run() == FALSE){
                    $login_errors = validation_errors();
                    $login_errors = str_replace_all("<p>", "<li>", $login_errors);
                    $login_errors = str_replace_all("</p>", "</li>", $login_errors);
                    $login_errors = "<ul>".$login_errors."</ul>";
                    set_session_error_msg($login_errors);
                    redirect(base_url().'account/reset_password?param='.$ci->input->post('reset_password_pram',true));
                }else{
                    $where = array('is_reset_password'=>1,'reset_password_param'=>$ci->input->post('reset_password_pram',true),'email'=>$ci->input->post('email',true) );
                    $result = $ci->general_model->get('users', $where);
                    if (count($result)==1) {
                        //$row['password'] = $ci->input->post('password',true);


                        $row['password'] = encrypt_password($ci->input->post('password',true));
                        $row['is_encrypted'] = 2;
                        $row['account_update_time'] = get_current_time();
                        $row['is_reset_password'] = 0;
                        $ci->general_model->update_record('users', $row, $result[0]['id']);
                        set_session_successMsg("Password updated successfully. Login to continue!");
                        redirect(base_url().'account');
                    }else{
                        set_session_error_msg("Error resetting password! Contact Support");
                        redirect(base_url().'account/forgot');
                    }
                }
        }else{
            generate_crf_token_cus();
            set_session_error_msg("Invalid password reset data");
            redirect(base_url().'account/reset_password?param='.$ci->input->post('reset_password_pram',true));
        }

    }
    function process_create_account_admin(){
        
        if (isset($_POST['submit'])) {
            //print_arr($_POST);
            //die();
            $ci = &get_instance();
            if (is_reseller_user()==true) {
                $_POST['account_type'] = 2;
            }
            $ci->form_validation->set_rules('firstname', 'First Name', 'trim|required|max_length[50]');
            $ci->form_validation->set_rules('lastname', 'Last Name', 'trim|max_length[50]');
            $ci->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]|email_unique');
            $ci->form_validation->set_rules('password', 'Password', 'required|min_length[8]|matches[repassword]|password_process');
            $ci->form_validation->set_rules('repassword', 'Password Confirmation', 'trim|required');



            $ci->form_validation->set_rules('account_type', 'Account type', 'required');
            if (isset($_POST['account_type']) && ($_POST['account_type']==2 || $_POST['account_type']==3)) {
                //$ci->form_validation->set_rules('tracking_id', 'tracking ID', 'required|tracking_id_unique');
                $ci->form_validation->set_rules('cpc', 'CPC rate', 'required');
                $ci->form_validation->set_rules('domain', 'Domain', 'required|valid_domain_name');
                if (isset($_POST['account_type']) && ($_POST['account_type']==2)) {
                    $ci->form_validation->set_rules('click_percentage', 'Click Percentage', 'required');
                }
            }
            if ($ci->form_validation->run() == FALSE){
                $login_errors = validation_error_process(validation_errors());
                $msg = '';
                for ($i=0; $i <count($login_errors) ; $i++) { 
                    $msg.='<p>'.$login_errors[$i].'</p>';
                }
                set_session_error_msg($msg);
                redirect(base_url().'users/add');

            }else{
                $row['firstname'] = $ci->input->post('firstname',true);
                $row['lastname'] = $ci->input->post('lastname',true);
                $row['email'] = $ci->input->post('email',true);
                $row['email_validation_hash'] = generate_verify_email_hash($row['email']);
                $row['password'] = encrypt_password($ci->input->post('password',true));
                $row['is_encrypted'] = 2;
                $row['account_type'] = $ci->input->post('account_type',true);
                $row['account_status'] = $ci->input->post('account_status',true);
                $row['account_msg'] = $ci->input->post('account_msg',true);
                $row['show_cpc'] = $ci->input->post('show_cpc',true);
                if ($ci->input->post('allow_click_update_approve',true)=="") {
                   $_POST['allow_click_update_approve']= 1;
                }
                if ($ci->input->post('allow_reseller_to_view_revenue',true)=="") {
                    $_POST['allow_reseller_to_view_revenue']= 1;
                }
                if ($ci->input->post('allow_reseller_to_view_client_clicks',true)=="") {
                    $_POST['allow_reseller_to_view_client_clicks']= 1;
                }
                $row['allow_click_update_approve'] = $ci->input->post('allow_click_update_approve',true);
                $row['allow_reseller_to_view_revenue'] = $ci->input->post('allow_reseller_to_view_revenue',true);
                $row['allow_reseller_to_view_client_clicks'] = $ci->input->post('allow_reseller_to_view_client_clicks',true);
                if (isset($_POST['account_type']) && ($_POST['account_type']==2 || $_POST['account_type']==3)) {
                    //$row['tracking_id'] = $ci->input->post('tracking_id',true);
                    $row['cpc'] = $ci->input->post('cpc',true);
                    $row['click_percentage'] = $ci->input->post('click_percentage',true);
                }
                if (is_reseller_user()==true) {
                    $row['is_managed_by_reseller'] = 1;
                    $row['reseller_user_id'] = ci_get_session('id');
                }
                $row['account_creation_time'] = get_current_time();
                $row['account_update_time'] = get_current_time();
                //print_arr($row);
                //die();
                $user_id =  $ci->general_model->insert_record('users', $row);
                $msg = "User account Created! ";
                if ($user_id>0) {
                    if (insert_domain_generate_tracking_id($user_id, $ci->input->post('domain',true))==true) {
                        $msg = "User account Created! & domain added ";
                    }
                }
                set_session_successMsg($msg);
                redirect(base_url().'users');
            }
        }
    }
    function insert_domain_generate_tracking_id($user_id, $domain){
        $ci = &get_instance();
        $domain = get_base_domain_from_url($domain);
        if ($domain['response']==true) {
            $row['domain'] = $domain['base_domain'];
            $row['publisher_id'] = $user_id;
            $row['status'] = get_user_domain_status_calculate($row['publisher_id']);
            $row['tracking_id_type'] = 2;
            $row['tracking_id'] = get_tracking_id();
            $domain_id = $ci->general_model->insert_record('publisher_domains', $row);
            if ($domain_id>0) {
                return true;
            }
        }
        return false;
    }
?>