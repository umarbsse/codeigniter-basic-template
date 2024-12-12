<?php

  function get_class_methods_list(){
    $controller_path = APPPATH.'controllers/';
    $list = listFolderFiles($controller_path);
    $controllers_list = array();
    foreach ($list as $key => $value) {
      $filecontent = read_file($controller_path.$key);
      $controller = process_php_file($filecontent);
      array_push($controllers_list, $controller);
    }
    return $controllers_list;
  }
  function listFolderFiles($dir) { 
    $arr = array();
    $ffs = scandir($dir);
    foreach($ffs as $ff) {
        if($ff != '.' && $ff != '..') {
            $arr[$ff] = array();
            if(is_dir($dir.'/'.$ff)) {
                $arr[$ff] = listFolderFiles($dir.'/'.$ff);
            }
        }
    }
    return $arr;
  }
  function read_file($filename){
    $str="";
    if ($file = fopen($filename, "r")) {
        while(!feof($file)) {
            $line = fgets($file);
            $str.=$line;
            # do same stuff with the $line
        }
        fclose($file);
    }
    return $str;
  }
  function process_php_file($content){
    $controller['class'] = '';
    $controller['methods'] = array();
    $class = explode("class ", $content);
    if (count($class)>1) {
      $class = $class[1];
      $class = explode("extends", $class);
      $class = preg_replace("/\s+/", "", $class[0]);
      $controller['class'] = $class;
    }
    $temp = explode("function", $content);
    for ($i=1; $i <count($temp) ; $i++) { 
      $t1 = $temp[$i];
      $t1 = explode("(", $t1);
      if (count($t1)>1) {
        $str= preg_replace("/\s+/", "", $t1[0]);
        if ("__construct"!=$str) {
          array_push($controller['methods'], $str);
        }
      }
    }
    return $controller;
  }
  function process_form_submit(){
    if (isset($_POST['submit'])) {      
      foreach ($_POST as $key => $value) {





        $temp  = explode(",", $key);
        //print_arr($key);
        //print_arr($value);
        //die();
        if (count($temp)==2) {
          $row['all_allow'] = 1;
          $row['admin_allow'] = 1;
          $row['publisher_allow'] = 1;
          $row['reseller_allow'] = 1;
          for ($i=0; $i <count($value) ; $i++) {
            if ($value[$i]=="1") {
              $row['all_allow'] = 2;
            }else if ($value[$i]=="2") {
              $row['admin_allow'] = 2;
            }else if ($value[$i]=="3") {
              $row['publisher_allow'] = 2;
            }else if ($value[$i]=="4") {
              $row['reseller_allow'] = 2;
            }
          }
          $row['controller'] = $temp[0];
          $row['method'] = $temp[1];
          $ci = &get_instance();
          $result = $ci->general_model->get('user_class_access', array('controller'=>$row['controller'],'method'=>$row['method']));
         // echo $this->db->last_query();
         // print_arr($row);
          if (count( $result)==0) {
         //   echo "exist<br>";
            $ci->general_model->insert_record('user_class_access', $row);
          }else{
         // echo "not exist<br>";
            $ci->general_model->upd_record('user_class_access', $row, array('id'=>$result[0]['id']) );
          }
        }



      }
    //  die();
    }
  }
  function is_publisher_user_allowed(){
    $ci = &get_instance();
    $ci->load->library('session');
    $ci->load->model('general','general_model');
    $user_type = ci_get_session('user_type');
    //print_arr($_SESSION);
    //echo $user_type;
    $controller= get_class__method_name();
    $current_class = $controller['controller'];
    $current_method = $controller['method']; 
    $where = array('controller'=>$current_class,'method'=>$current_method);
    if ($user_type=="") {
      $where['all_allow'] = "2";
      $result = $ci->general_model->get('user_class_access', $where);
      if (count($result)==0) {
        //print_arr($_SESSION);
        //die("Redirecting NOW");
        redirect(base_url());
      }
    }else{
      force_user_to_update_password();
      if ($user_type=="2") {
        $where['publisher_allow'] = "2";
      }
      else if ($user_type=="3") {
        $where['reseller_allow'] = "2";
      }
      $result = $ci->general_model->get('user_class_access', $where);
      if (count($result)==0) {
        redirect(base_url().'dashboard');
      }
    }


    //echo $current_class." => ".$current_method." => ".$user_type;    
  }
  function force_user_to_update_password(){
      //if ("39.43.164.98"==get_real_ip()){
        //print_arr($_SESSION);
        if (check_if_remote_user_logined()==false) {
          check_if_password_secured_then_force_to_update();
        }
      //}
  }
  function check_if_remote_user_logined(){
    if (isset($_SESSION['switch_user']['id']) && $_SESSION['switch_user']['id']!="" && $_SESSION['switch_user']['id']!=$_SESSION['id']) {
      return true;
    }
    return false;
  }
  function check_if_password_secured_then_force_to_update(){
    $ci = &get_instance();
    $where = array('is_encrypted'=>1,'id'=>ci_get_session('id'));
    $result = $ci->general_model->get('users', $where, 1, NULL, NULL, "id,password,is_encrypted");
    if (count($result)==1) {
      if (check_for_strong_password($result[$i]['password'])==false) {
        $controller = get_class__method_name();
        if ($controller['controller']=='setting' && ($controller['method']=='password' || $controller['method']=='update_password')) {
          //print_arr($controller);
          //echo "not secured passsword process";
        }else{
          set_session_info_msg("You must update your password before continuing".password_error_msg_secure());
          redirect(base_url().'setting/password');
        }
      }
    }
    //print_arr($result);
    //die();
  }
  function check_if_password_is_secure_then_encrypt_it(){

    $ci = &get_instance();
    $ci->load->library('session');
    $ci->load->model('general','general_model');
    //$where = array('is_encrypted'=>1,'id'=>ci_get_session('id'));
    $where = array('is_encrypted'=>1);
    $result = $ci->general_model->get('users', $where, NULL, array("id","asc"), NULL, "id,password,is_encrypted");
    for ($i=0; $i <count($result) ; $i++) {
      $result[$i]['is_secure_password'] = 'no';
      if (check_for_strong_password($result[$i]['password'])) {
        $result[$i]['is_secure_password'] = 'yes';
      }
    }
    for ($i=0; $i <count($result) ; $i++) {
      if ($result[$i]['is_secure_password'] == 'yes') {
        $row['password'] = encrypt_password($result[$i]['password']);
        $row['is_encrypted'] = 2;
        $row['account_update_time'] = get_current_time();
        $result[$i]['is_password_verified']="password_NOT_VERIFIED";
        if (verify_password($result[$i]['password'], $row['password'])) {
          $result[$i]['is_password_verified']="password_VERIFIED";
          bulk_update_user_password_encrypt($row, $result[$i]['id']);
        }
      }
    }
    die();
  }
  function bulk_update_user_password_encrypt($row, $id){
    $ci = &get_instance();
    //print_arr($row);
    $ci->general_model->update_record('users', $row, $id);
  }
  function get_allowed_classes(){
    //$allowed_classes = array();
    //return $allowed_classes;
  }
  function is_admin_user(){
    if (ci_get_session("user_type")=="1") {
      return true;
    }
    return false;
  }
  function is_reseller_user(){
    if (ci_get_session("user_type")=="3") {
      return true;
    }
    return false;
  }
  function is_publisher_user(){
    if (ci_get_session("user_type")=="2") {
      return true;
    }
    return false;
  }
  function check_if_reseller_allowed_to_update_and_approve_clicks(){
    $array =array("show_cpc"=>false,"allow_click_update_approve"=>false,"allow_reseller_to_view_revenue"=>false,"allow_reseller_to_view_client_clicks"=>false,"allow_reseller_to_view_cr_rate"=>false);
    if (is_reseller_user()==true){
      $ci = &get_instance();
      $result = $ci->general_model->get('users', array("account_type"=>3,"id"=>ci_get_session('id')), NULL, NULL, NULL, 'show_cpc,allow_click_update_approve, allow_reseller_to_view_revenue,allow_reseller_to_view_client_clicks,allow_reseller_to_view_cr_rate');
      if (count($result)==1) {
        if ($result[0]['allow_click_update_approve']=="2") {
          $array["allow_click_update_approve"] = true;
        }
        if ($result[0]['allow_reseller_to_view_revenue']=="2") {
          $array["allow_reseller_to_view_revenue"] = true;
        }
        if ($result[0]['allow_reseller_to_view_client_clicks']=="2") {
          $array["allow_reseller_to_view_client_clicks"] = true;
        }
        if ($result[0]['allow_reseller_to_view_cr_rate']=="2") {
          $array["allow_reseller_to_view_cr_rate"] = true;
        }
        if ($result[0]['show_cpc']=="2") {
          $array["show_cpc"] = true;
        }
      }
    }
    return $array;
  }
?>