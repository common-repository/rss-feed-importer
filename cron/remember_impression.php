<?php
    global $post, $wpdb;
    $l = get_post_meta($post->ID, 'tw_rss_feed_impression');
    
    if($post->post_type != 'post'){
        $p = get_post_meta($post->ID,'tw_rss_feed_options');
    }
    
    function curPageURL() {
		$pageURL = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
    }
    
    $currPage = curPageURL();
    
    if(!preg_match('/bot|crawl|slurp|spider|\+metauri.com|Mediapartners\-Google|Java\/1\.8\.0_45|vegi.style\/Nutch-2.3|Go-http-client\/1.1|Dispatch\/0.11.1\-SNAPSHOT/i', $_SERVER['HTTP_USER_AGENT'])){
        if(isset($p[0]) && $p[0] != '' && $post->post_type != 'post'){
            if(!isset($l[0]) || $l[0] == ''){
                $f = explode('|',$p[0]);
                $f[2] = get_cat_name($f[2]);
                $a = array('feed_name'=>urlencode($f[0]),'feed_url'=>urlencode($f[1]),'feed_category'=>urlencode($f[2]),'current_page'=>urlencode($currPage),'impression'=>1,'ip_address'=>$_SERVER['REMOTE_ADDR'],'processed'=>'processed-false','user_agent'=>$_SERVER['HTTP_USER_AGENT']);
                $a = json_encode($a);
                update_post_meta($post->ID,'tw_rss_feed_impression',$a);
            } else {
                $l = $l[0];
                $json = json_decode($l,true);
                ++$json['impression'];
                $json['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $json['ip_address'] = $_SERVER['REMOTE_ADDR'];
                
                $l = json_encode($json);
                update_post_meta($post->ID,'tw_rss_feed_impression',$l);
            }
        } else {
            if(!isset($l[0]) || $l[0] == ''){
                $a = array('feed_name'=>'tw_outside_network','feed_url'=>'tw_outside_network','feed_category'=>'tw_outside_network','current_page'=>urlencode($currPage),'impression'=>1,'ip_address'=>$_SERVER['REMOTE_ADDR'],'processed'=>'processed-false','user_agent'=>$_SERVER['HTTP_USER_AGENT']);
                $a = json_encode($a);
                update_post_meta($post->ID,'tw_rss_feed_impression',$a);
            } else {
                $l = $l[0];
                $json = json_decode($l,true);
                ++$json['impression'];
                $json['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $json['ip_address'] = $_SERVER['REMOTE_ADDR'];
                
                $l = json_encode($json);
                update_post_meta($post->ID,'tw_rss_feed_impression',$l);
            }
        }
    }
?>