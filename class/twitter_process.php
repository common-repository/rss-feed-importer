<?php
    // require codebird
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once('twitter.php');
    
    if(isset($_GET['page']) && $_GET['page'] == 'tw_send_social'){
        $wp = new WP_Query(
                array(
                        'p'         =>  $_GET['id'],
                        'post_type' =>  TW_FEED_TITLE
                    )
            );
    } else {
        $wp = new WP_Query(
                    array(
                            'post_type'     =>  TW_FEED_TITLE,
                            'meta_value'    =>  'true',
                            'meta_key'      =>  'tw_social_option',
                            'post_status'   =>  'publish',
                            'post_per_page' =>  5,
                        )
                    );
    }
    
    $cb = new Codebird();
    $cb->setConsumerKey($twitter['consumer'],$twitter['consumer_key']);
    $cb = $cb->getInstance();
    $cb->setToken($twitter['access'],$twitter['access_secret']);
    
    foreach($wp->posts as $f){
    	delete_post_meta($f->ID,'tw_social_option');
        update_post_meta($f->ID,'tw_social_option','false');
        $category = get_the_category($f->ID);
        
        $content = str_replace('&#039;', "'",str_replace('&#39;', "'",html_entity_decode(htmlspecialchars_decode(rawurldecode(rawurldecode($f->post_title)))))).' '.get_permalink($f->ID);

        $total_length = strlen($content);
        
        foreach($category as $c){
            $category_holder = ' #'.$c->name.' '; 
            if($total_length+strlen(preg_replace('/<a href="(.*?)">(.*?)<\/a>/is','1',$category_holder)) < 140){
                $content .= $category_holder;
            }
        }
        
        $attachment = $this->get_post_images($f->ID,$f->post_content,false,true);
        
        if(strpos($attachment,'<img') == 29){
            $attachment = '';
        }
        $reply = new stdClass();
        
        if(strlen($attachment) > 5){
            $params = array(
                'status' => $content.' #quanticpost',
                'media[]' => $attachment
            );
            
            $reply = @$cb->statuses_updateWithMedia($params);
        } else {
            $params = array(
                'status' => $content.' #quanticpost',
            );
            $reply = @$cb->statuses_update($params);
        }
        
        if($reply->httpstatus == '200' || $reply->httpstatus == '403'){
        	delete_post_meta($f->ID,'tw_social_option');
            update_post_meta($f->ID,'tw_social_option','false');
        } else {
        	delete_post_meta($f->ID,'tw_social_option');
            update_post_meta($f->ID,'tw_social_option','true');
        }
    }
?>