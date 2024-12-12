<?php
    function news_data(){
        $news = array();
        $n1 = array(
                "title"=>"Russian ships move from Syria base amid doubts over future",
                "img"=>base_url()."assets/news/img/aed995b0-b7ba-11ef-88bd-350cd692a77a.jpg.webp",
                "url"=>base_url()."detail_news"
        );
        array_push($news, $n1);
        $n1 = array(
                    "title"=>"Israel seizing on Syria chaos to strike military assets",
                    "img"=>base_url()."assets/news/img/5bb4c870-b7db-11ef-a4bc-a7eaa92e4b88.jpg.webp",
                    "url"=>base_url()."detail_news"
        );
        array_push($news, $n1);
        $n1 = array(
                    "title"=>"Syria in maps: Who controls the country now Assad has gone?",
                    "img"=>base_url()."assets/news/img/15d690f0-b6f9-11ef-aff0-072ce821b6ab.png.webp",
                    "url"=>base_url()."detail_news"
        );
        array_push($news, $n1);
        $n1 = array(
                    "title"=>"Suicide bomb kills Taliban minister in Kabul",
                    "img"=>base_url()."assets/news/img/6928b110-b7db-11ef-a4bc-a7eaa92e4b88.jpg.webp",
                    "url"=>base_url()."detail_news"
        );
        array_push($news, $n1);
        shuffle($news);
        return $news;
    }
    function get_home_page_slider_news_list(){
        return news_data();
    }
    function get_home_page_box_news_list(){
        return news_data();
    }
    function get_home_page_latest_news(){
        return news_data();
    }
    function get_home_page_caragory_news(){
        $category_news = array();
        $category_new_list = get_news_category_list();
        for ($i=0; $i <count($category_new_list) ; $i++) { 
            $temp['name'] = $category_new_list[$i];
            $temp['news_list'] = get_home_page_box_news_list();
            array_push($category_news, $temp);
        }
        return $category_news;
    }
    function get_news_category_list(){
        return array("Sports","Technology","Business","Entertainment");
    }
?>