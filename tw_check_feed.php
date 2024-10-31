<?php
    global $post;
    $feed_info = get_post_meta($_GET['ID'],'tw_rss_feed_options');
    
    $arg = array(
            'post_type'         => TW_FEED_TITLE,
            'meta_query'        => array(
                    array(
                        'value'         => $feed_info[0],
                        'meta_key'      => 'tw_rss_feed_options'
                    )
                ),
            'posts_per_page'    => -1
        );
    $query = new WP_Query($arg);
    
    $layout = get_option('rss_feeds');
    $layout = json_decode($layout,true);
    
    $info = explode("|",$feed_info[0]);
    
    foreach($layout as $f){
    	if(rawurlencode($f['feed_url']) == rawurlencode($info[1])){
    		$info = json_encode($f);
    		break;
    	}
    }
?>
<style>
    .row > div > div:nth-child(-n+2){
        float: left;
        padding: 5px;
        width: 45%;
        border-bottom: 1px solid #ababab;
        height: 25px;
    }
    .clear{
        clear: both;
    }
</style>
<h2>Checking feed article media</h2>
<?php
	extract_info($info);
    $get_info = array();
    $totals = array('total_posts_media' => 0, 'total_posts_no_media' => 0, 'total_posts' => 0);
    foreach($query->posts as $k=>$p){ 
        preg_match_all('/img.*?(src|url|href)=([\'"](.*?)[\'"]|(.*?)).*?/is',$p->post_content,$media);
       	
        $get_info[] = array(
                    'post_title'    =>  $p->post_title,
                    'permalink'     =>  get_permalink($p->ID),
                    'images'        =>  $media,
                );
       	unset($query->posts[$k]);
        
        if(sizeof($media) > 1 && $media[3][0] != ''){
        	$str = str_replace("'",'',str_replace("\"","",$media[3][0]));
        	$host = explode('|',$feed_info[0]);
        	
        	$domain_check = parse_url($host[1]);
		   	$host_holder = $domain_check['host'];
        	$id = upload_files($str,$p->ID,$host_holder);
        }
        if(sizeof($media) > 0){
            $totals['total_posts_media'] += 1;
        } else {
            $totals['total_posts_no_media'] += 1;
        }
    }
    unset($query);
    ?>
<strong>Total Posts With Media:</strong> <?php echo $totals['total_posts_media']; ?> out of <?php echo $totals['total_posts_media']+$totals['total_posts_no_media']; ?>
<div class="row">
<?php foreach($get_info as $g){ ?>
    <div>
        <div><a href="<?php echo $g['permalink']; ?>"><?php echo rawurldecode($g['post_title']); ?></a></div><div><?php echo sizeof($g['images']); ?></div>
    </div>
<?php } ?>
</div>
<h2></h2>