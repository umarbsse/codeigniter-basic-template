<?php
     function generate_captcha(){
        $vals = array(
        'img_path'      => './'.get_captcha_store_path(),
        'img_url'       => base_url().get_captcha_store_path(),
        'font_path' => BASEPATH.'fonts/texb.ttf',
        'img_width'     => '300',
        'img_height'    => 100,
        'expiration'    => 7200,
        'word_length'   => 5,
        'font_size'     => 25,
        'img_id'        => 'Imageid',
        'pool'          => '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ',

        // White background and border, black text and red grid
        'colors'        => array(
                'background' => array(200, 255, 255),
                'border' => array(255, 255, 255),
                'text' => array(0, 0, 0),
                'grid' => array(19, 40, 20)
        )
        );
        $cap = create_captcha($vals);
        return $cap;
    }
    function get_captcha_store_path(){
        return 'assets/images/captcha/';
    }
    function get_captcha(){
        destroy_captcha();
        $captcha = generate_captcha();
        set_captcha_session($captcha);
        return $captcha;
    }
    function set_captcha_session($captcha){
        ci_unset_session('captcha_word');
        ci_set_session('captcha',$captcha['word']."__|__".$captcha['filename']);
    }
    function destroy_captcha(){
        $response = get_captcha_image();
        if ($response['response']==true) {
            $image = $response['image'];
            $path  = get_captcha_store_path().$image;
            delete_captcha_file($path);
            ci_unset_session('captcha');
        }
    }
    function validate_captcha(){
        if (is_captcha_enable()) {
            if (isset($_POST['captcha_word']) && $_POST['captcha_word']!="") {
                $ci = &get_instance();
                $word=$ci->input->post('captcha_word',true);
                $response = get_captcha_word();
                if ($response['response']==true) {
                    if (compare_strings($response['word'], $word)==true) {
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
        }else{
            return true;
        }
    }
    function get_captcha_word(){
        $array['response'] = false;
        $array['word'] = '';
        $temp = ci_get_session('captcha');
        $temp = explode("__|__", $temp);
        if (count($temp)==2) {
            $array['response'] = true;
            $array['word'] = $temp[0];
        }
        return $array;
    }
    function get_captcha_image(){
        $array['response'] = false;
        $array['image'] = '';
        $temp = ci_get_session('captcha');
        $temp = explode("__|__", $temp);
        if (count($temp)==2) {
            $array['response'] = true;
            $array['image'] = $temp[1];
        }
        return $array;
    }
    function compare_strings($str1, $str2){
        if (strcmp($str1, $str2) !== 0) {
            return false;
        }
        return true;
    }
    function delete_captcha_file($path){
        if (file_exists($path)) {
            if (!unlink($path)){
                return true;
            }else{
                return false;
            }
        }
    }
    function is_captcha_enable(){
        return get_value('is_captcha_enable')==2 ? true : false;
    }
?>