<?php
    $categories = get_categories('hide_empty=0');
    
    $category_names = array();
    foreach($categories as $f){
        $category_names[] = array('slug'=>$f->slug,"name"=>$f->name);
    }
    
    $json = json_encode($category_names);
    
   	$feeds = get_option('rss_feeds');
    if(isset($_POST['tw_rss_feed_info'])){
        $feed_options = json_decode(get_option('rss_feeds'),true);
        
        foreach($feed_options as $f){
            $feed_options[$f['feed_name']] = $f;
        }
        
    	$feeds = substr($feeds,0,-1);
    	$feed_string = explode(',',$_POST['tw_rss_feed_info']);
    	
    	$get_feeds = json_decode(get_option('tw_rss_feed_options'),true);
    	
    	foreach($feed_string as $f){
    		if($f != ''){
    			$f = explode('%7C',$f);
    			$f[0] = str_replace('%3F','?',$f[0]);
    			$f[1] = substr($f[1],0,strpos($f[1],'|'));
    			
    			$already_exist = false;
    			if(isset($feed_options[$f[0]])){
    			    $already_exist = true;
    			}
    			
    			if(!$already_exist){
        			if($f[1]){
            			$categories = get_category_by_slug($f[1]);
            			if(!isset($categories->cat_ID)){
            				$temp_holder = wp_create_category($f[1]);
            				$categories->cat_ID = $temp_holder;
            			}
            			
            			if(isset($f[2])){
                			$feed_info = '{"feed_name":"'.$f[0].'","feed_url":"'.$f[0].'","feed_category":"'.$categories->cat_ID.'","feed_image_enabler":"true","active":"true"}';
                			if(strpos($feeds,$feed_info) === false){
                				$feeds .= ','.$feed_info;
                			}
            			} else {
            			    $feed_info = '{"feed_name":"'.$f[0].'","feed_url":"'.$f[0].'","feed_category":"'.$categories->cat_ID.'","active":"true"}';
                			if(strpos($feeds,$feed_info) === false){
                				$feeds .= ','.$feed_info;
                			}
            			}
        			}
    			}
    		}
    	}
    	$feeds .= ']';
    	
    	$feeds = str_replace('[,','[',$feeds);
    	
    	if(get_option('rss_feeds') == ''){
    		add_option('rss_feeds',$feeds);
    	} else {
    		update_option('rss_feeds',$feeds);
    	}
    }
    
    $server = str_replace('/','_',str_replace('.','^',str_replace('http://','',get_bloginfo('url'))));
    $json = $this->api_call('http://quanticpost.com/getdata/search_categories/'.$server.'/'.str_replace(' ','%20',$json).'/'.get_locale());
    
    $json = json_decode($json,true);
    
    $total_info = array();
    foreach($json as $j){
        if(!isset($total_info[strtolower($j['name'])])){
            $total_info[strtolower($j['name'])] = array('total'=>0,'feeds'=>array());
        }
        if(!isset($total_info[strtolower($j['name'])]['feeds'][$j['feed_url']])){
            $total_info[strtolower($j['name'])]['total'] += $j['SUM(i.impression)'];
            $total_info[strtolower($j['name'])]['feeds'][$j['feed_url']] = array($j['feed_url'],$j['SUM(i.impression)']);
        }
    }
?>
<style>
    .column1{
        width: 150px;
        float: left;
    }
    .column2{
        float: left;
    }
    .header{
        margin-top: 10px;
        background: #000;
        color: #fff;
        height: 20px;
        padding: 10px;
    }
    .feeds{
        clear: both;
    }
    .row .header{
        background: #545454;
    }
    .feed_info{
        border-bottom: 1px solid #545454;
    }
    .feed_info{
    	background: #ababab;
    	color: #fff;
    }
    .feed_info:hover{
    	background: #545454;
    }
    .feed_info .columns{
    	color: #fff;
    	float: left;
    	width: 45%;
    	height: 20px;
    	overflow: hidden;
    	padding: 5px;
    }
    .feeds{
    	display: none;
    }
    .layout{
    	background: #545454;
    	color: #fff;
    	margin-bottom: 1px;
    }
    .feed_url{
		float: left;
    	padding: 10px;
    }
    .tw_remove{
    	float: right;
    	padding: 10px;
    	background: #78AB46;
    	text-shadow: 0px 1px 5px rgba(0,0,0,.3);
    	border: 1px solid #000;
    }
    .clear{
        clear: both;
    }
    p{
    	background: #000;
    	color: #fff;
    	padding: 5px;
    	margin-bottom: 0px;
    }
    .get_images{
    	margin-top: 5px;
    	float: left;
    }
    #potential_click_additions{
        padding: 10px;
        font-size: 17px;
        font-weight: bold;
    }
    #potential_clicks{
    	padding: 5px;
    	background: #000;
    	color: #fff;
    	clear: both;
    }
</style>
    <h2>Analytics</h2>
    <p>Current categories inside network that match your categories (be sure to add rss feeds by clicking categories then clicking add rss feeds)</p>
    <div class="header">
        <div class="column1">Name</div>
        <div class="column2">Impression</div>
        <div class="clear"></div>
    </div>
<?php foreach($total_info as $k=>$j){ ?>
    <div class="row">
        <div class="header">
            <div class="column1"><?php echo $k; ?></div>
            <div class="column2"><?php echo number_format($total_info[$k]['total']); ?> | <?php echo number_format($total_info[$k]['total']/sizeof($j['feeds'])); ?> avg. impressions per site</div>
            <div class="clear"></div>
        </div>
        <div class="feeds">
        <?php foreach($j['feeds'] as $f){ ?>
            <div class="feed_info">
            	<div class="columns"><?php echo $f[0]; ?></div>
            	<div class="columns"><?php echo number_format($f[1]); ?></div>
            	<div class="clear"></div>
            </div>
        <?php } ?>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
<?php } ?>
	<form action="" method="POST">
		<input type="hidden" name="tw_rss_feed_info" value="" id="tw_rss_feed_info" style="width: 100%; height: 200px;"/>
		<input type="submit" name="submit" value="Submit"/>
	
    	<div id="tw_display_rss_feeds">
    	</div>
        <p>Potential click additions:</p>
        <div id="potential_click_additions">
            0
        </div>
        <div id="potential_clicks">
        	Sign Up for SEO training for your website <a href="http://quanticpost.com/seo_training" target="_blank" style="padding: 10px; background: #F07900; color: #fff; text-decoration: none">SEO Training</a>
        </div>
        <div>
        <style>
            .words{
                display: none;
            }
            .words div{
                padding: 5px;
            }
            .word-count{
                border-bottom: 1px solid #ababab; 
                padding: 10px;
            }
            .link-button{
            	background: #fff;
            	color: #545454;
            }
            .links{
                display: none;
            }
        </style>
        <?php
        
        $blog = parse_url(get_bloginfo('url'));
        $top_content_info = $this->api_call('http://quanticpost.com/getdata/get_best_feeds/'.str_replace('.','^',str_replace('/','|',$blog['host'])));
        
        $top_content_info = json_decode($top_content_info,true);
        
        $this->best_information($top_content_info); ?>
    </form>
<?php 
    global $wpdb;
    /*$query = $wpdb->get_results('SELECT post_title,post_content,guid FROM '.$wpdb->posts.' WHERE post_type="'.TW_FEED_TITLE.'" ORDER BY post_date DESC LIMIT 2000');
    $word_info = array();
    $top_reoccuring_words = array();
    $total_word_counts = array();
    
    $dir = scandir(plugin_dir_path( __FILE__ ).'../dictionary');
   	$word = array();
   	foreach($dir as $d){
   		if($d != '.' && $d != '..'){
       		if(is_dir(plugin_dir_path( __FILE__ ).'../dictionary/'.$d)){
       			$subdir = scandir(plugin_dir_path( __FILE__ ).'../dictionary/'.$d);
       			foreach($subdir as $v){
       				if($v != '.' && $v != '..'){
       					$fh = fopen(plugin_dir_path( __FILE__ ).'../dictionary/'.$d.'/'.$v, 'r+');
       					$words[$d] = explode("\n",fread($fh,filesize(plugin_dir_path( __FILE__ ).'../dictionary/'.$d.'/'.$v)));
       					fclose($fh);
       				}
       			}
       		} else {
       			$fh = fopen(plugin_dir_path( __FILE__ ).'../dictionary/'.$d,'r+');
       			$words[$d] = explode("\n",fread($fh,filesize(plugin_dir_path( __FILE__ ).'../dictionary/'.$d)));
       			fclose($fh);
       		}
   		}
   	}
    
    $total_negativity = array('negative'=>array(),'positive'=>array(),'neutral'=>array());
    foreach($query as $f){
        $title = explode(' ',$f->post_title);
        
        $content = str_replace(',','',str_replace(':','',strtolower((strip_tags(str_replace('Read More:','',$f->post_content))))));
        $content_words = explode(' ',$content);
        $content_words = array_unique($content_words);
        
        $negativity = array('negative'=>array('total'=>0,'words'=>array()),'positive'=>array('total'=>0,'words'=>array()),'neutral'=>array('total'=>0,'words'=>array()));
        $expression = array('?','!',':',',');
        $total_words = sizeof($content_words);
        $word_position = array();
        
        $omit_words = array('d','de','do','this','that','they','&','—','me','when','so','are','out','no','go','can','a','but','was','the','be','and','at','is','to','an','as','or','on','in','if','for','of','it','with','while','your','i','our','their','he','she','them','us','we','you','her','his');
        
        foreach($content_words as $k=>$h){
            if(strlen($f->post_content) > strlen($f->post_title)+20 && strlen(str_replace($f->post_title,'',$content)) > 10 && $h != '' && !in_array($h,$omit_words) && strlen($h) > 1){
              	$total = substr_count($content,$h);
              	
                $word_info[$h][] = array('link'=>$f->guid,'total_occurence'=>$total);
                $total_word_counts[$h][] = $total;
                $top_reoccuring_words[$total][$h] .= $f->guid.',';
                
                if(in_array($h,$words['negative-words.txt'])){
                    ++$negativity['negative']['total'];
                    $total_negativity['negative'][$h] += 1;
                    $negativity['negative']['words'][$h][] = $k;
                } elseif(in_array($h,$words['positive-words.txt'])) {
                    ++$negativity['positive']['total'];
                    $total_negativity['positive'][$h] += 1;
                    $negativity['positive']['words'][$h][] = $k;
                } else {
                    ++$negativity['neutral']['total'];
                    $total_negativity['neutral'][$h] += 1;
                    $negativity['neutral']['words'][$h][] = $k;
                }
            }
        }
    }
    ksort($top_reoccuring_words);
    
    arsort($total_negativity['positive']);
    arsort($total_negativity['negative']);
    arsort($total_negativity['neutral']);
    
    $top_words = array();
    foreach($total_word_counts as $k=>$f){
    	$top_words[sizeof($f)][] = $k;
    }
    krsort($top_words);*/ ?>
<canvas width="900" height="900">
</canvas>
<script>
    /*var canvas = document.getElementsByTagName('canvas')[0];
    ctx = canvas.getContext('2d');
    
    var json = {'positive':<?php echo str_replace("'",'\'',json_encode($total_negativity['positive'])); ?>};
    
    ctx.beginPath();
    ctx.fillStyle = 'white';
    ctx.rect(10,10,canvas.width,canvas.height);
    ctx.fill();
    
    var beginning_position = {};
    var link = [];
    
    for(i in json){
    	switch(i){
    		case 'positive':
    			beginning_position = {'x':30,'y':30,'area':0,'fill':'rgba(0,100,0,.5)'};
    			break;
    		case 'negative':
    			beginning_position = {'x':canvas.width - 30,'y':30,'area':0,'fill':'rgba(100,0,0,.5)'};
    			break;
    		case 'neutral':
    			beginning_position = {'x':(canvas.width/2) - 30,'y':canvas.height-30,'area':0,'fill':'rgba(100,100,100,.5)'};
    			break;
    	}
    	
    	for(f in json[i]){
    		ctx.strokeStyle = 'rgba(100,100,100,.5)';
    		
    		ctx.beginPath();
    		ctx.fillStyle = beginning_position.fill;
    		ctx.arc((json[i][f]),(json[i][f]),json[i][f]/4,0,Math.PI*3060);
    		ctx.fill();
    		ctx.fillStyle = 'black';
    		ctx.fillText(f,beginning_position.x+(json[i][f]),beginning_position.y+(json[i][f]));
    	}
    }*/
</script>
<?php /*foreach($top_reoccuring_words as $k=>$f){ ?>
<div style="padding: 10px; background: #000; color: #fff;">
<?php foreach($f as $k=>$p){ 
        $l = explode(',',$p);
        $links[]  = $l;
    }
    ?>
    <div class="word-count">Word Count <?php echo $k;?></div>
    <div><?php echo $f;?></div>
    <div>
    	<div class="link-button">Links</div>
    	<div class="links">
        <?php foreach($links as $a){ 
        		foreach($a as $h){
        	?>
            <div><a href="<?php echo $h; ?>" target="_blank"><?php echo 'testing'; ?></a></div>
        <?php } } ?>    
        </div>
    </div>
</div>
<?php }*/ ?>
</div>
<script>
    function click_info(accordian_button,open){
        for(var i = 0; i < accordian_button.length; ++i){
            accordian_button[i].onclick = function(el){
                var child_button = el.currentTarget.parentNode.getElementsByClassName(open)[0];
                if(child_button.style.display == 'block'){
                    child_button.style.display = 'none';
                } else {
                    child_button.style.display = 'block';
                }
            }
        }
    }
    
    var accordian = new click_info(document.getElementsByClassName('word-count'),'words');
    var link = new click_info(document.getElementsByClassName('link-button'),'links');
    
	var buttons = document.getElementsByClassName('row');
	
	function get_info_layout(){
	    document.getElementById('tw_display_rss_feeds').innerHTML = '';
		var info = document.getElementById('tw_rss_feed_info').getAttribute('value').toString().split(',');
		for(var l = 0; l < info.length; l++){
			if(info[l] != ''){
				if(info[l].search('%7Cimage_pull') < 0){
					document.getElementById('tw_display_rss_feeds').innerHTML += '<div class="feed_info"><div class="feed_url">'+info[l]+'</div> <div class="get_images"><input type="checkbox" name="checkbox"/></div><div class="tw_remove">Remove</div><div class="clear"></div></div>';
				} else {
					document.getElementById('tw_display_rss_feeds').innerHTML += '<div class="feed_info"><div class="feed_url">'+info[l]+'</div> <div class="get_images"><input type="checkbox" name="checkbox" checked/></div><div class="tw_remove">Remove</div><div class="clear"></div></div>';
				}
			}
		}
		
		var remove_length = document.getElementsByClassName('tw_remove').length;
        for(var v = 0; v < remove_length; ++v){
        	document.getElementsByClassName('get_images')[v].addEventListener('click',function(el){
        		var feed_url_holder = el.target.parentNode.parentNode.getElementsByClassName('feed_url')[0].innerHTML;
	            if(feed_url_holder.search('%7Cimage_pull') > -1){
	               	document.getElementById('tw_rss_feed_info').setAttribute('value',
						document.getElementById('tw_rss_feed_info').getAttribute('value').replace(','+feed_url_holder,','+feed_url_holder.replace('%7Cimage_pull',''))
					);
					el.target.parentNode.parentNode.getElementsByClassName('feed_url')[0].innerHTML = feed_url_holder.replace('%7Cimage_pull','');
	            } else {
	            	document.getElementById('tw_rss_feed_info').setAttribute('value',
						document.getElementById('tw_rss_feed_info').getAttribute('value').replace(','+feed_url_holder,','+feed_url_holder+'%7Cimage_pull')
					);
					el.target.parentNode.parentNode.getElementsByClassName('feed_url')[0].innerHTML = feed_url_holder+'%7Cimage_pull';
	            }
        	})
            document.getElementsByClassName('tw_remove')[v].addEventListener('click',function(el){
	            var feed_url_holder = el.currentTarget.parentNode.getElementsByClassName('feed_url')[0].innerHTML;
	            var potential_clicks = document.getElementById('potential_click_additions');
	            if(document.getElementById('tw_rss_feed_info').getAttribute('value').search(feed_url_holder) > -1){
	                document.getElementById('tw_rss_feed_info').setAttribute('value',
						document.getElementById('tw_rss_feed_info').getAttribute('value').replace(','+feed_url_holder,'')
					);
	            }
	            var impressions = el.currentTarget.parentNode.getElementsByClassName('feed_url')[0].innerHTML.split('|')[1];
	            potential_clicks.innerHTML = parseInt(potential_clicks.innerHTML)-parseInt(impressions);
	            el.currentTarget.parentNode.remove();
	            get_info_layout();
	        })
        }
	}
	
	for(var i = 0; i < buttons.length; ++i){
		buttons[i].onclick = function(el){
			if(el.currentTarget.getElementsByClassName('feeds')[0].style.display != 'block'){
				el.currentTarget.getElementsByClassName('feeds')[0].style.display = 'block';
				var button_info = el.currentTarget.getElementsByClassName('feed_info');
				for(var f = 0; f < button_info.length; ++f){
					button_info[f].onclick = function(el){
					    var feed_url = el.target.parentNode.getElementsByClassName('columns')[0].innerHTML;
					    var impressions = el.target.parentNode.getElementsByClassName('columns')[1].innerHTML.replace(',','');
						var category_id = el.target.parentNode.parentNode.parentNode.getElementsByClassName('column1')[0].innerHTML;
						if(document.getElementById('tw_rss_feed_info').getAttribute('value').search(encodeURI(feed_url).replace('?','%3F')+"%7C"+category_id) < 0){
							document.getElementById('tw_rss_feed_info').setAttribute('value',
								document.getElementById('tw_rss_feed_info').getAttribute('value')+','+encodeURI(feed_url).replace('?','%3F')+"%7C"+category_id+'|'+impressions
							);
							var potential_clicks = document.getElementById('potential_click_additions');
                            potential_clicks.innerHTML = parseInt(potential_clicks.innerHTML)+parseInt(impressions);
						} else {
						    document.getElementById('tw_rss_feed_info').setAttribute('value',
								document.getElementById('tw_rss_feed_info').getAttribute('value').replace(','+encodeURI(feed_url).replace('?','%3F')+'%7C'+category_id+'%7Cimage_pull|'+impressions,'')
							);
							document.getElementById('tw_rss_feed_info').setAttribute('value',
								document.getElementById('tw_rss_feed_info').getAttribute('value').replace(','+encodeURI(feed_url).replace('?','%3F')+'%7C'+category_id+'|'+impressions,'')
							);
							var potential_clicks = document.getElementById('potential_click_additions');
				            potential_clicks.innerHTML = parseInt(potential_clicks.innerHTML)-parseInt(impressions);
						}
						
						get_info_layout();
					}
				}
			} else {
				el.currentTarget.getElementsByClassName('feeds')[0].style.display = 'none';
			}
		}
	}
</script>