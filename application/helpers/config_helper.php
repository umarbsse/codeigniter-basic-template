<?php
    function insert_value($key,$val){
        $ci = &get_instance();
        $result = $ci->general_model->get('config', array('attrib'=>$key));
        if (count($result)==0) {
            $row['attrib'] = $key;
            $row['val'] = $val;
            $row['updated_on'] = get_current_time();
            $ci->general_model->insert_record('config', $row);
        }else{
            $row['attrib'] = $key;
            $row['val'] = $val;
            $row['updated_on'] = get_current_time();
            $ci->general_model->upd_record('config', $row, array('id'=>$result[0]['id']) );
        }
    }
    function get_value($key){
        $ci = &get_instance();
        $result = $ci->general_model->get('config', array('attrib'=>$key));
        if (count($result)==0) {
            return '';
        }else{
            return $result[0]['val'];
        }
    }
?>