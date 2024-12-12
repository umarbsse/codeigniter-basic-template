<?php
    function ipVersion($txt) {
        return strpos($txt, ":") === false ? 4 : 6;
    }
    function raw_ip_feeding(){
        $ci = &get_instance();
        $select = 'id,ip';
        $where = array('is_ip_record_fetched'=>'1');
        $group_by='';
        $limit = 300;
        $result = $ci->general_model->get('ads_logs', $where, $limit, NULL, $group_by, $select);
        if (count($result)>0) {
            $where_in['column_name']="ip";
            $where_in['arr']=array();
            for ($i=0; $i <count($result) ; $i++) {
                array_push($where_in['arr'], $result[$i]['ip']);
            }
            $result_temp = $ci->general_model->get('ip_details', NULL, NULL, NULL, NULL, '*', NULL, $where_in);
            update_number_of_time_ip_checked($result_temp);
            for ($i=0; $i <count($result) ; $i++) {
                $is_found = ip_search_in_array($result[$i]['ip'], $result_temp);
                if ($is_found==true) {
                    //echo "found ip = ".$result[$i]['ip']."<br>";
                }else{
                    $row['ip_type'] = ipVersion($result[$i]['ip']);
                    $row['ip'] = $result[$i]['ip'];
                    $row['added_on'] = get_current_time();
                    $row['updated_on'] = get_current_time();
                    $ci->general_model->insert_record('ip_details', $row);
                }
                $data['is_ip_record_fetched'] = 2;
                $temp_where = array('id'=>$result[$i]['id']);
                $ci->general_model->upd_record('ads_logs', $data, $temp_where);
            }
        }
    }
    function update_number_of_time_ip_checked($result){
        $ci = &get_instance();
        for ($i=0; $i <count($result) ; $i++) {
            $row['number_of_time_ip_checked'] = $result[$i]['number_of_time_ip_checked']+1;
            $ci->general_model->upd_record('ip_details', $row, array('id'=>$result[$i]['id']));
        }
    }
    function ip_search_in_array($ads_logs_array, $ip_details_array){
        if (count($ip_details_array)==0) {
            return false;
        }else{
            for ($i=0; $i <count($ip_details_array) ; $i++) { 
                if ($ip_details_array[$i]['ip']==$ads_logs_array) {
                    return true;
                }
            }
            return false;
        }
    }
    function fetch_ip_details_from_api(){
        $ci = &get_instance();
        $ips_limit = get_value("ip-api.com_ip_limit_perbatch");
        $select = "id,ip";
        $result = $ci->general_model->get('ip_details', array('is_ip_record_fetched'=>1), $ips_limit, array('id','ASC'), NULL, $select);
        //print_arr($result);
        //die();
        $ips = array();
        for ($i=0; $i <count($result) ; $i++) { 
            array_push($ips, $result[$i]['ip']);
        }
        //print_arr($ips);
        //die();
        if (is_check_api_limit_filter_passed()==true) {
            $response = fetch_batch_ip($ips);
            //print_arr($response);
            insert_batch_ip_to_db($response);
        }
    }
    function fetch_batch_ip($ips){
        $endpoint = 'http://ip-api.com/batch?fields=status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,mobile,proxy,hosting,query';
        $options = [
            'http' => [
                'method' => 'POST',
                'user_agent' => 'Batch-Example/1.0',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($ips)
            ]
        ];
        $response = file_get_contents($endpoint, false, stream_context_create($options));
        $array = json_decode($response, true);
        return $array;
    }
    function get_api_last_run_time_in_seconds(){
        $ci = &get_instance();
        $last_time = "'".get_value('ip-api.com_last_request_run_time')."'";
        $current_time = "'".get_current_time()."'";
        $result = $ci->db->query("SELECT TIMESTAMPDIFF(SECOND, $last_time, $current_time) as seconds")->result_array();
        if (count($result)==1) {
            return $result[0]['seconds'];
        }
        return -1;
    }
    function is_check_api_limit_filter_passed(){
        $last_run_time_in_seconds = get_api_last_run_time_in_seconds();
        if ($last_run_time_in_seconds>0) {
            if ($last_run_time_in_seconds<=60) {
                $number_per_minute = get_value("ip-api.com_ip_request_number_per_minute");
                $limit_per_minute = get_value("ip-api.com_limit_per_minute");
                if ($number_per_minute<$limit_per_minute) {
                    $key="ip-api.com_ip_request_number_per_minute";
                    $val = intval($number_per_minute)+1;
                    insert_value($key,$val);
                    $key="ip-api.com_last_request_run_time";
                    insert_value($key,get_current_time());
                    return true;
                }
            }else{
                $key="ip-api.com_ip_request_number_per_minute";
                    $val = intval(1);
                    insert_value($key,$val);
                $key="ip-api.com_last_request_run_time";
                insert_value($key,get_current_time());
                return true;
            }
        }
        return false;
    }
    function insert_batch_ip_to_db($array){
        $ci = &get_instance();
        for ($i=0; $i <count($array) ; $i++) { 
            $row = $array[$i];
            unset($row['as']);
            if (isset($array[$i]['as'])) {
                $row['as_s'] = $array[$i]['as'];
            }else{
                $row['as_s'] = "";
            }
            $row['is_ip_record_fetched'] = 2;
            $row['updated_on'] = get_current_time();
            $temp_where = array('ip'=>$array[$i]['query']);
            //print_arr($row);
            $ci->general_model->upd_record('ip_details', $row, $temp_where );
        }
    }
    function get_total_ip(){
        $ci = &get_instance();
        return get_total_count_db($ci->general_model->get('ip_details', '','','','','count(id) as total'));
    }
    function fetch_ip_host_details_from_api(){
        $ci = &get_instance();
        $limit=50;
        $where =array("is_host_find"=>1);
        $select="id,ip";
        $result = $ci->general_model->get('ip_details', $where, $limit, NULL, NULL, $select);
        //print_arr($result);
        for ($i=0; $i <count($result) ; $i++) {
            $row['is_host_find'] = 2;
            $row['full_url_host'] = gethostbyaddr($result[$i]['ip']);
            $row['host'] = getDomain($result[$i]['ip'],$row['full_url_host']);
            $temp_where = array('id'=>$result[$i]['id']);
            $ci->general_model->upd_record('ip_details', $row, $temp_where );
        }
    }
    function feed_ip_details_to_ads_tbl(){
        $ci = &get_instance();
        $limit=1000;
        $where =array("is_ip_req_fileds_added"=>1);
        $select="id,ip";
        $result = $ci->general_model->get('ads_logs', $where, $limit, array('id','asc'), NULL, $select);
        //print_arr($result);

        //die();
        $ips =array();
        for ($i=0; $i <count($result) ; $i++) { 
            if (in_array($result[$i]['ip'], $ips)==false){
                array_push($ips, $result[$i]['ip']);
            }
        }
        $where_in['column_name']="ip";
        $where_in['arr'] = $ips;
        $ip_details = $ci->general_model->get('ip_details', array("is_host_find"=>2,"is_ip_record_fetched"=>2), $limit, NULL, NULL, "*", NULL, $where_in);
        //echo $ci->db->last_query();
        //print_arr($ip_details);
        //die();


        for ($i=0; $i <count($result) ; $i++) {
            for ($j=0; $j <count($ip_details) ; $j++) { 
                //print_arr($result[$i]);
                if ($result[$i]['ip']==$ip_details[$j]['ip']) {
                   // print_arr($result[$i]);
                    $row['city'] = $ip_details[$j]['city'];
                    $row['country'] =  $ip_details[$j]['country'];
                    $row['ip_host'] =  $ip_details[$j]['full_url_host'];
                    $row['countryCode'] =  $ip_details[$j]['countryCode'];
                    $row['is_ip_req_fileds_added'] =  2;
                    $row['ip_time_zone'] =  $ip_details[$j]['timezone'];;
                    if ($row['ip_host']==$ip_details[$j]['ip']) {
                        $row['is_ip_using_proxy'] =  1;   
                        //echo "Proxy not detected<br>";
                    }else{
                        $row['is_ip_using_proxy'] =  2;  
                        //echo "Proxy detected<br>"; 
                    }
                   // print_arr($row);
                    $temp_where = array('id'=>$result[$i]['id']);
                    $ci->general_model->upd_record('ads_logs', $row, $temp_where );
                    //echo $ci->db->last_query();

                    
                }
            }
        }
    }
    function get_tick_cross_icon($val){
        if ($val==1) {
            return '<i class="fa fa-circle-check fa-lg" style="color: green;"></i>';
        }
        return '<i class="fa fa-circle-xmark fa-lg" style="color: red;"></i>';
    }
    function is_user_using_proxybased_ip(){
        $ip =get_real_ip();
        $host = gethostbyaddr($ip);
        if ($host != $ip){
            return true;
        }
        return false;
    }
    function get_base_domain_from_url($str){
        $msg['response'] = true;
        $url_info = parse_url($str);
        if (isset($url_info['host']) && $url_info['host']!="") {
            $msg['base_domain'] = str_replace("www.", "", $url_info['host']);
        }else{
            $url_info = parse_url("https://".$str);
            if (isset($url_info['host']) && $url_info['host']!="") {
                $msg['base_domain'] = str_replace("www.", "", $url_info['host']);
            }else{
                $msg['response'] = false;
            }
        }
        return $msg;
    }
    function valid_domain_name(){
        $ci = &get_instance();
        $domain = $ci->input->post('domain',true);
        $is_valid_domain = get_base_domain_from_url($domain);
        if ($is_valid_domain['response'] == false) {
            $ci->form_validation->set_message('valid_domain_name', 'Domain is invalid');
            return false;
        }else{
            $domain = $is_valid_domain['base_domain'];
            $where['domain'] = $domain;
            if( is_admin_user()==true  || is_reseller_user()==true){
                $where['publisher_id'] = $ci->input->post('users_email',true);
            }else{
                $where['publisher_id'] = ci_get_session('id');
            }
            $total = get_total_count_db($ci->general_model->get('publisher_domains', $where, NULL, NULL, NULL,'count(*) as total'));
            if ($total>0) {
                $ci->form_validation->set_message('valid_domain_name', 'Domain is already Exist.');
                return false;
            }
            return true;
        }
    }
    function domain_name_unique() {
        $ci = &get_instance();
        $domain = $ci->input->post('domain',true);



        $where["tracking_id"] = $ci->input->post('tracking_id',true);
        if(ci_get_session('user_type')=="1" && isset($_POST['token']) && $_POST['token']!=""){
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
    function optimize_tbl(){
        $ci = &get_instance();

        $ips_array =array();
        $where['id >= '] = get_value('ip_details_ips_check_id');
        $where['ip_found '] = 1;
        $limit = 1000;
        $result_ip_details = $ci->general_model->get("ip_details", $where, $limit, array("id","asc"), "", "id,ip");
        if (count($result_ip_details)>0) {
            //echo $ci->db->last_query()."<br>";
            //echo count($result_ip_details)."<br>";
            $last_id = $result_ip_details[count($result_ip_details)-1]['id'];
            //echo $last_id."<br>";
            //die();
            for ($i=0; $i <count($result_ip_details) ; $i++) { 
                array_push($ips_array, $result_ip_details[$i]['ip']);
            }
            $where_in['column_name']="ip";
            $where_in['arr']=$ips_array;
            $result = $ci->general_model->get("ads_logs", NULL, NULL, NULL, NULL, "id,ip", NULL, $where_in);
            $ips_not_found_array =array();
            $ips_confirm_found_array =array();
            for ($i=0; $i <count($result_ip_details) ; $i++) {
                $is_ip_found=false;
                for ($xx=0; $xx <count($result) ; $xx++) { 
                    if ($result[$xx]['ip']==$result_ip_details[$i]['ip']) {
                        //echo $result[$xx]['ip']." => ".$result_ip_details[$i]['ip']."<br>";
                        array_push($ips_confirm_found_array, $result_ip_details[$i]['id']);
                        $is_ip_found=true;
                        break;
                    }
                }
                if ($is_ip_found==false) {
                    array_push($ips_not_found_array, $result_ip_details[$i]['id']);
                }
            }
            if (count($ips_not_found_array)>0) {
                //print_arr($ips_not_found_array);
                $ci->general_model->del_record_where_in("ip_details", array("column_name"=>"id","arr"=>$ips_not_found_array));
            }else{
                $row_data['ip_found'] = 2;
                $row_data['ip_checked'] = 2;
                $ci->general_model->update_record_where_in("ip_details", $row_data,  array("column_name"=>"id","arr"=>$ips_confirm_found_array));
            }
            insert_value('ip_details_ips_check_id',$last_id);
        }
    }
?>