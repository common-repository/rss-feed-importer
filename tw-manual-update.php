<?php
    $feeds = get_option('rss_feeds');
    $table = $this->create_feed_table($feeds,true,$string_holder);
    
    include_once('social_autopost.php');
    
    if(get_option('tw_social_tokens')){
    	$social = new Social(get_option('tw_social_tokens'),get_option('tw_auto_social_api'));
    	
    	$api['tokens'] = json_decode(get_option('tw_social_tokens'),true);
	    $api['api'] = json_decode(get_option('tw_auto_social_api'),true);
    }
   	
   	if(isset($api['tokens'])){
        unset($api['tokens']);
   	}
   	if(isset($api['api'])){
        unset($api['api']);
   	}
?>
<h1>Manual Update</h1>
<?php $this->advertisements(); ?>
<form action="" method="post">
<?php
    $table->display();
?>
    <input type="hidden" name="table_info" value="table"/>
</form>
<?php $this->advertisements(); ?>