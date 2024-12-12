<?php 

class General_remote extends CI_Model {

        public $title;

        public $content;

        public $date;

        public function __construct(){

                parent::__construct();

                $this->load->database('payperinstall_db', TRUE);

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

        public function auth_user($login_pram, $pass){

                $this->db->select('*');

                $this->db->where('email',$login_pram);
                $this->db->where('password',$pass);

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

                        $this->db->where_in($where_in['column_name'], $where_in['arr']);

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
}

?>