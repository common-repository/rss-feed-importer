<?php
    global $wpdb;
    
    $info = $wpdb->get_results('SELECT meta_value,post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "tw_rss_feed_impression" AND meta_value LIKE "%processed-false%"');
    
    $variables = '[';
    
    print_r($info);
    
    foreach($info as $k=>$f){
        $variables .= $f->meta_value.',';
        $f->meta_value = str_replace('processed-false','processed-true',$f->meta_value);
        delete_post_meta($f->post_id,'tw_rss_feed_impression');
        update_post_meta($f->post_id,'tw_rss_feed_impression',$f->meta_value);
    }
    $variables = (strlen($variables) > 1)?substr($variables,0,-1).']':'[]';echo $variables."<br/>";
    
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://quanticpost.com/info_pull/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'info='.$variables.'&host='.site_url());
    $data = curl_exec($ch);
    curl_close($ch);
    
    $processed = false;
		
	$info = $wpdb->get_results('DELETE FROM '.$wpdb->postmeta.' WHERE meta_key = "tw_rss_feed_impression" AND meta_value LIKE "%processed-true%"');
	$processed = true;
    
    $variables = get_categories();
    $variables = json_encode($variables);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://quanticpost.com/info_pull/categories/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'info='.$variables.'&host='.site_url());
    $data = curl_exec($ch);
    curl_close($ch);
?>