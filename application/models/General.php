<?php 

class General extends CI_Model {

        public $title;

        public $content;

        public $date;

        public function __construct(){

                parent::__construct();

                //$this->load->database();
                $this->set_database();

        }
        public function set_database($database=NULL)
        {
            $CI =& get_instance();
            $CI->db = null;
            if ( $database==NULL) {
                $this->load->database();
            }else{
                $this->load->database($database);
            }
        }
        public function insert_record($table_name, $data){

                $this->db->insert($table_name, $data);

                return $this->db->insert_id();

        }

        public function get($table_name, $where=NULL, $limit=NULL, $order_by=NULL, $group_by=NULL, $select=NULL, $like=NULL, $where_in=NULL, $where_not_in=NULL,$not_like=NULL){

                if ( $select!=NULL) {

                        $this->db->select($select);

                }else{

                        $this->db->select('*');

                }

                if ( $like!=NULL) {

                        if ($where!=NULL) {

                                $this->db->group_start();

                        }                            

                        for ($i=0; $i <count($like) ; $i++) { 

                                $tmp_array = $like[$i];

                                $this->db->or_like( $tmp_array[0], $tmp_array[1], $tmp_array[2]);

                        }

                        if ($where!=NULL) {

                                $this->db->group_end();

                        }

                }
                
                if ( $not_like!=NULL) {

                        if ($where!=NULL) {

                                $this->db->group_start();

                        }                            

                        for ($i=0; $i <count($not_like) ; $i++) { 

                                $tmp_array = $not_like[$i];

                                $this->db->or_not_like( $tmp_array[0], $tmp_array[1], $tmp_array[2]);

                        }

                        if ($where!=NULL) {

                                $this->db->group_end();

                        }

                }

                if ( $where!=NULL) {

                        $this->db->where($where);

                }

                if ( $where_in!=NULL) {

                        if (isset($where_in['column_name']) && $where_in['column_name']!="") {
                                if (is_array($where_in['arr'])) {
                                        $this->db->where_in($where_in['column_name'], $where_in['arr']);
                                }else{
                                        $this->db->where_in($where_in['column_name'], $where_in['arr'],false);
                                }
                        }else{
                                for ($i=0; $i <count($where_in) ; $i++) {
                                        $this->db->where_in($where_in[$i]['column_name'], $where_in[$i]['arr']);
                                }
                        }

                }

                if ( $where_not_in!=NULL) {

                        if (

                                isset($where_not_in['values']) && count($where_not_in['values'])>0 &&

                                isset($where_not_in['column_name'])

                        ) {

                                $values = $where_not_in['values'];

                                $this->db->where_not_in($where_not_in['column_name'], $values);

                        }

                }

                if ( $order_by!=NULL) {
                        if (is_array($order_by)) {
                                $this->db->order_by($order_by[0], $order_by[1]);
                        }else{
                                $this->db->order_by($order_by);
                        }                       

                }

                if ( $limit!=NULL) {

                        if (is_array($limit)) {

                                $this->db->limit($limit[0], $limit[1]);

                        }else{

                                $this->db->limit($limit);

                        }

                }

                if ( $group_by!=NULL) {

                        $this->db->group_by($group_by);

                }

                $result = $this->db->get($table_name);

                $result = $result->result_array();

                return $result;

        }

        public function update_record($table, $data, $id ){

                $this->db->where('id', $id);

                $this->db->update($table, $data);

        }

        public function upd_record($table, $data, $where ){

                $this->db->where($where);

                $this->db->update($table, $data);

        }
        public function update_record_where_in($table, $data,  $where_in){
                $this->db->where_in($where_in['column_name'], $where_in['arr']);
                $this->db->update($table, $data);
        }

        public function delete_record($table, $column_name, $column_value){

                $this->db->delete($table, array($column_name => $column_value));

        }

        public function del_record($table, $where, $limit=NULL){

                $this->db->where($where);

                if ( $limit!=NULL) {
                         $this->db->limit($limit);
                }
                $this->db->delete($table);


        }
        public function del_record_where_in($table, $where_in){
                $this->db->where_in($where_in['column_name'], $where_in['arr']);
                $this->db->delete($table);

        }

        public function auth_user($login_pram, $pass){

                $this->db->select('*');

                $this->db->where('email',$login_pram);
                $this->db->where('password',$pass);

                $result = $this->db->get('users');

                $result = $result->result_array();

                return $result;

        }

        public function auth_user_email($login_pram){

                $this->db->select('*');

                $this->db->where('email',$login_pram);

                $result = $this->db->get('users');

                $result = $result->result_array();

                return $result;

        }

        public function get_user_earnings($select=NULL, $where=NULL, $limit=NULL,  $like=NULL,  $order_by=NULL, $group_by=NULL){
                if ( $select!=NULL) {
                        $this->db->select($select);
                }
                $this->db->join('users', 'users.tracking_id=ads_logs.tracking_id', 'left');
                if ( $where!=NULL) {
                        $this->db->where($where);
                }
                if ( $like!=NULL) {
                        if ($where!=NULL) {
                                $this->db->group_start();
                        }                            
                        for ($i=0; $i <count($like) ; $i++) { 
                                $tmp_array = $like[$i];
                                $this->db->or_like( $tmp_array[0], $tmp_array[1], $tmp_array[2]);
                        }
                        if ($where!=NULL) {
                                $this->db->group_end();
                        }
                }


                if ( $order_by!=NULL) {

                        $this->db->order_by($order_by[0], $order_by[1]);

                }

                if ( $limit!=NULL) {

                        if (is_array($limit)) {

                                $this->db->limit($limit[0], $limit[1]);

                        }else{

                                $this->db->limit($limit);

                        }

                }
                if ( $group_by!=NULL) {

                        $this->db->group_by($group_by);

                }
                $result = $this->db->get('ads_logs');
                $result = $result->result_array();

                return $result;

        }
        public function get_user_stats_by_day($select=NULL, $where=NULL, $limit=NULL,  $like=NULL,  $order_by=NULL,  $where_in=NULL,  $group_by=NULL){
                if ( $select!=NULL) {
                        $this->db->select($select);
                }
                $this->db->join('users', 'users.id=ads_by_date.user_id', 'left');
                if ( $where!=NULL) {
                        $this->db->where($where);
                }
                if ( $where_in!=NULL) {

                        if (isset($where_in['column_name']) && $where_in['column_name']!="") {
                                if (is_array($where_in['arr'])) {
                                        $this->db->where_in($where_in['column_name'], $where_in['arr']);
                                }else{
                                        $this->db->where_in($where_in['column_name'], $where_in['arr'],false);
                                }
                        }else{
                                for ($i=0; $i <count($where_in) ; $i++) {
                                        $this->db->where_in($where_in[$i]['column_name'], $where_in[$i]['arr']);
                                }
                        }

                }
                if ( $like!=NULL) {
                        if ($where!=NULL) {
                                $this->db->group_start();
                        }                            
                        for ($i=0; $i <count($like) ; $i++) { 
                                $tmp_array = $like[$i];
                                $this->db->or_like( $tmp_array[0], $tmp_array[1], $tmp_array[2]);
                        }
                        if ($where!=NULL) {
                                $this->db->group_end();
                        }
                }
                if ( $order_by!=NULL) {
                        $this->db->order_by($order_by[0], $order_by[1]);
                }
                if ( $limit!=NULL) {
                        if (is_array($limit)) {
                                $this->db->limit($limit[0], $limit[1]);
                        }else{
                                $this->db->limit($limit);
                        }
                }
                if ( $group_by!=NULL) {
                        $this->db->group_by($group_by);
                }
                $result = $this->db->get('ads_by_date');
                $result = $result->result_array();

                return $result;
        }
        public function get_user_payment($select=NULL, $where=NULL, $limit=NULL,  $like=NULL,  $order_by=NULL){
                if ( $select!=NULL) {
                        $this->db->select($select);
                }
                $this->db->join('users', 'users.id=payment.publisher_id', 'left');
                if ( $where!=NULL) {
                        $this->db->where($where);
                }
                if ( $like!=NULL) {
                        if ($where!=NULL) {
                                $this->db->group_start();
                        }                            
                        for ($i=0; $i <count($like) ; $i++) { 
                                $tmp_array = $like[$i];
                                $this->db->or_like( $tmp_array[0], $tmp_array[1], $tmp_array[2]);
                        }
                        if ($where!=NULL) {
                                $this->db->group_end();
                        }
                }
                if ( $order_by!=NULL) {
                        $this->db->order_by($order_by[0], $order_by[1]);
                }
                if ( $limit!=NULL) {
                        if (is_array($limit)) {
                                $this->db->limit($limit[0], $limit[1]);
                        }else{
                                $this->db->limit($limit);
                        }

                }
                $result = $this->db->get('payment');
                $result = $result->result_array();

                return $result;
        }
        public function get_user_comolative_stats_by_day($select=NULL, $where=NULL, $limit=NULL,  $like=NULL,  $order_by=NULL,  $where_in=NULL,  $group_by=NULL){
                if ( $select!=NULL) {
                        $this->db->select($select);
                }
                $this->db->join('users', 'users.id=ads_by_date.user_id', 'left');
                if ( $where!=NULL) {
                        $this->db->where($where);
                }
                if ( $where_in!=NULL) {

                        if (isset($where_in['column_name']) && $where_in['column_name']!="") {
                                if (is_array($where_in['arr'])) {
                                        $this->db->where_in($where_in['column_name'], $where_in['arr']);
                                }else{
                                        $this->db->where_in($where_in['column_name'], $where_in['arr'],false);
                                }
                        }else{
                                for ($i=0; $i <count($where_in) ; $i++) {
                                        $this->db->where_in($where_in[$i]['column_name'], $where_in[$i]['arr']);
                                }
                        }

                }
                if ( $like!=NULL) {
                        if ($where!=NULL) {
                                $this->db->group_start();
                        }                            
                        for ($i=0; $i <count($like) ; $i++) { 
                                $tmp_array = $like[$i];
                                $this->db->or_like( $tmp_array[0], $tmp_array[1], $tmp_array[2]);
                        }
                        if ($where!=NULL) {
                                $this->db->group_end();
                        }
                }
                if ( $order_by!=NULL) {
                        $this->db->order_by($order_by[0], $order_by[1]);
                }
                if ( $limit!=NULL) {
                        if (is_array($limit)) {
                                $this->db->limit($limit[0], $limit[1]);
                        }else{
                                $this->db->limit($limit);
                        }
                }
                if ( $group_by!=NULL) {
                        $this->db->group_by($group_by);
                }
                $result = $this->db->get('ads_by_date');
                $result = $result->result_array();

                return $result;
        }
        public function check_if_user_made_failed_account_access_attempt($select,$error_type,$ip, $minutes){
                //SELECT * FROM `auth_logs` WHERE type=2 AND ip="39.43.165.66" AND added_on > date_sub(now(), interval 5 minute) ORDER BY `type` DESC
                $this->db->select($select);
                $this->db->where('type',$error_type);
                $this->db->where('ip',$ip);
                $this->db->where('added_on>','date_sub(now(), interval '.$minutes.' minute)',false);
                $result = $this->db->get('auth_logs');
                $result = $result->result_array();
                return $result;

        }
        public function get_tracking_ids_list($where_ads_by_date=NULL, $where_in_ads_by_date=NULL, $where_publisher_domains=NULL, $where_in_publisher_domains=NULL){
                $this->db->select('DISTINCT(ads_by_date.tracking_id) as tracking_id');
                if ( $where_ads_by_date!=NULL) {
                        $this->db->where($where_ads_by_date);
                }
                if ( $where_in_ads_by_date!=NULL) {
                        if (isset($where_in_ads_by_date['column_name']) && $where_in_ads_by_date['column_name']!="") {
                                if (is_array($where_in_ads_by_date['arr'])) {
                                        $this->db->where_in($where_in_ads_by_date['column_name'], $where_in_ads_by_date['arr']);
                                }else{
                                        $this->db->where_in($where_in_ads_by_date['column_name'], $where_in_ads_by_date['arr'],false);
                                }
                        }else{
                                for ($i=0; $i <count($where_in_ads_by_date) ; $i++) {
                                        $this->db->where_in($where_in_ads_by_date[$i]['column_name'], $where_in_ads_by_date[$i]['arr']);
                                }
                        }

                }
                $query1 = $this->db->get_compiled_select("ads_by_date",FALSE);
                $this->db->reset_query();
                $this->db->select('DISTINCT (publisher_domains.tracking_id) as tracking_id');
                if ( $where_publisher_domains!=NULL) {
                        $this->db->where($where_publisher_domains);
                }
                if ( $where_in_publisher_domains!=NULL) {
                        if (isset($where_in_publisher_domains['column_name']) && $where_in_publisher_domains['column_name']!="") {
                                if (is_array($where_in_publisher_domains['arr'])) {
                                        $this->db->where_in($where_in_publisher_domains['column_name'], $where_in_publisher_domains['arr']);
                                }else{
                                        $this->db->where_in($where_in_publisher_domains['column_name'], $where_in_publisher_domains['arr'],false);
                                }
                        }else{
                                for ($i=0; $i <count($where_in) ; $i++) {
                                        $this->db->where_in($where_in_publisher_domains[$i]['column_name'], $where_in_publisher_domains[$i]['arr']);
                                }
                        }

                }
                $query2= $this->db->get_compiled_select("publisher_domains",FALSE);
                $this->db->reset_query();
                $result = $this->db->query("$query1 UNION $query2")->result_array();
                return $result;
        }
}

?>