<?php
    /*
    Plugin Name: RSS Feed System
    Plugin URI: http://www.taureanwooley.com
    Description: Plugin that allows for uploading rss-feeds into wordpress along with uploading full content from certain rss feeds when available (still in development)
    Author: Taurean Wooley
    Version: 4.0.1
    Author URI: http://www.taureanwooley.com
    */
    
    ini_set('memory_limit','20000000000000000000000000000M');
    
    if ( !function_exists('tw_rss_admin_actions') ) {
        
        class tw_rss_feed{
            public function __construct(){
                add_action( 'admin_menu', array($this,'tw_rss_admin_actions'));
                add_action( 'wp_title',array($this,'tw_edit_head_title'));
                add_action( 'wp_before_admin_bar_render',array($this,'tw_add_menu_bar'));
                add_filter( 'next_post_link', array($this,'tw_posts_link'));
                add_filter( 'previous_post_link', array($this,'tw_posts_link'));
                add_shortcode('tw_carousel_slider', array($this,'tw_carousel_slider'));
                add_action( 'wp_loaded',array($this,'tw_load_info_action'));
                add_action( 'init',array($this,'tw_get_feed_registration'),10);
                add_filter( 'post_type_link', array($this,'tw_remove_cpt_slug'), 10, 3 );
                add_action( 'pre_get_posts', array($this,'tw_parse_request_trick') );
                add_filter( 'document_title_parts',array($this,'tw_edit_title'),0);
                add_filter( 'widget_text', 'do_shortcode');
                add_filter( 'parse_query', array($this,'parse_wordpress'));
                add_action( 'manage_posts_custom_column' , array($this,'display_posts_stickiness'), 10, 2 );
                add_action( 'wp_insert_post_data',array($this,'restruct_title_to_readable'),'99', 2);
                add_action( 'widgets_init', array($this,'tw_load_widget') );
                add_action( 'wp_head', array($this,'tw_rss_feed_impression'));
                add_action( 'wp_footer', array($this,'tw_rss_feed_custom_style'), 1 );
                add_filter( 'wp_head',array($this,'footer') );
                add_action( 'admin_init',array($this,'tw_custom_meta_boxes'));
                add_action( 'trash_post',array($this,'tw_delete_post'));
    		    add_action( 'before_delete_post',array($this,'tw_delete_post'));
    		    add_action( 'wp_footer',array($this,'process_cron'));
    		    add_action( 'wp_ajax_nopriv_tw_process_cron', array($this,'tw_process_cron') );
    		    add_action( 'wp_ajax_tw_process_cron', array($this,'tw_process_cron') );
    		    add_filter( 'wp_head', array($this,'tw_special_cleanup'));
    		    add_action( 'wp', array($this,'tw_setup_schedule') );
    		    add_filter( 'the_content', array($this,'tw_sharing') );
                add_shortcode('feed_searches',array($this,'feed_searches'));
                add_shortcode('feed_group',array($this,'feed_group'));
                add_shortcode('feed_info',array($this,'feed_info'));
            }
            
            public function tw_rss_admin_actions() {
    			add_menu_page(
    				'TW RSS Feed',
    				'TW RSS Feed',
    				'manage_options',
    				'twp_admin',
    				array($this,'twp_admin'),
    				plugins_url('Feed_25x25.png', __FILE__)
    			);
    			add_submenu_page(
    				'twp_admin',
    				'Impressions',
    				'Impressions',
    				'manage_options',
    				'tw_impressions',
    				array($this,'tw_impressions')
    			);
    			add_submenu_page(
    				'twp_admin',
    				'Analytics',
    				'Analytics',
    				'manage_options',
    				'tw_analytics',
    				array($this,'tw_analytics')
    			);
    			add_submenu_page(
    				'twp_admin',
    				'Automated Categories',
    				'Automated Categories',
    				'manage_options',
    				'tw_categories',
    				array($this,'tw_categories')
    			);
    			add_submenu_page(
    				'twp_admin',
    				'Monitizing and Sharing',
    				'Monitizing and Sharing',
    				'manage_options',
    				'tw_monitization',
    				array($this,'tw_monitization')
    			);
    			add_submenu_page(
    				'twp_admin',
    				'Settings',
    				'Settings',
    				'manage_options',
    				'tw_theme_options',
    				array($this,'tw_theme_options')
    			);
    			add_submenu_page(
    				'twp_admin',
    				'View Feed Content',
    				'View Feed Content',
    				'manage_options',
    				'tw_view_content',
    				array($this,'tw_view_content')
    			);
    			add_submenu_page(
    				null,
    				'Send Social',
    				'Send Social',
    				'manage_options',
    				'tw_send_social',
    				array($this,'tw_send_social')
    			);
    			add_submenu_page(
    				null,
    				'Process Delete Button',
    				'Process Delete Button',
    				'manage_options',
    				'tw_process_delete_button_press',
    				array($this,'tw_process_delete_button_press')
    			);
    			add_submenu_page(
    				null,
    				'Edit Feed',
    				'Edit Feed',
    				'manage_options',
    				'tw_edit_feed_data',
    				array($this,'tw_edit_feed_data')
    			);
    			add_submenu_page(
    				null,
    				'Edit Feed',
    				'Edit Feed',
    				'manage_options',
    				'tw_delete',
    				array($this,'tw_delete')
    			);
    			add_submenu_page(
    				null,
    				'Edit Feed',
    				'Edit Feed',
    				'manage_options',
    				'tw_update_feed',
    				array($this,'tw_update_feed')
    			);
    			add_submenu_page(
    				null,
    				'Check Feed',
    				'Check Feed',
    				'manage_options',
    				'tw_check_feed',
    				array($this,'tw_check_feed')
    			);
    			add_submenu_page(
    				'twp_admin',
    				'Manual Update',
    				'Manual Update',
    				'manage_options',
    				'tw_manual_update',
    				array($this,'tw_manual_update')
    			);
    			delete_option('tw_rss_feed_animation');
    		}
            
            public function check_duplicate_images($dir=''){
    			if($dir == ''){
    				$dir = wp_upload_dir();
    				$dir_array = scandir($dir['basedir']);
    				$dir = $dir['basepath'];
    			}
    			
    			$image_holder['duplicates'] = array();
    			
    			foreach($dir_array as $d){
    				if($d != '.' && $d != '..'){
    					check_if_image_exist($dir.'/'.$d, $dir,$image_holder);
    				}
    			}
            }
            
            public function tw_send_social($arg){
            	$id = $_GET['id'];
            	
            	$social = json_decode(get_option('tw_social_tokens'),true);
    				
    			if(isset($social['twitter']) && sizeof($social['twitter']) > 1 && strlen($social['twitter']['consumer_key']) > 5){
    				$twitter = $social['twitter'];
    			}
            	
            	include_once('class/twitter_process.php');
            	
            	?>
            	<div>
            	</div>
    <?php	}
            
            public function tw_update_feed(){
            	
                $rss = get_option('rss_feeds');
                
                $rss = json_decode($rss,true);
                $feed_info = explode('|',rawurldecode($_GET['ID']));
                $feed_url = '';
                
                $info = array();
                
                foreach($rss as $f){
                    if(sizeof($feed_info) > 1){
                        if($f['feed_name'] == $feed_info[0]){
                        	$feed_url = '/'.str_replace('.','^',str_replace('/','|',$f['feed_url']));
                        	echo '<h1>Updating '.$f['feed_name'].'</h1>';
                            $info = $this->extract_feed($f);
                        }
                    } else {
                        if($f['feed_url'] == $feed_info[0]){
                        	$feed_url = '/'.str_replace('.','^',str_replace('/','|',$f['feed_url']));
                        	echo '<h1>Updating '.$f['feed_name'].'</h1>';
                            $info = $this->extract_feed($f);
                        }
                    }
                }
                
                foreach($info as $k=>$f){
                	echo '<b>'.$k.'</b>: '.$f.'<br/>';
                }
                
                $blog = parse_url(get_bloginfo('url'));
    			$top_content = json_decode(file_get_contents('http://quanticpost.com/getdata/get_best_feeds/'.str_replace('.','^',str_replace('/','_',$blog['host'])).$feed_url),true);
            	
            	$feed_url = str_replace('^','.',str_replace('|','/',substr($feed_url,1,strlen($feed_url))));
            	echo 'Feed url: <a href="'.$feed_url.'" target="_blank">'.$feed_url.'</a><br/>';
                echo '<br/>';
            	
            	$this->best_information($top_content);
            }
            
            public function tw_edit_head_title($title){
                $title = rawurldecode(rawurldecode(rawurldecode(htmlspecialchars_decode($title))));
                return $title;
            }
            
            public function tw_add_menu_bar(){
                global $wp_admin_bar,$post;
                if(isset($post)){
                    if($post->post_type == TW_FEED_TITLE){
                        $wp_admin_bar->add_menu(
                                array(
                                        'ID'    => 'tw_admin_bar',
                                        'title' => 'TW check feed status',
                                        'href'  => admin_url().'?page=tw_check_feed&ID='.$post->ID
                                    )
                            );
                    }
                }
            }
            
            public function tw_posts_link($link){
                return urldecode($link);
            }
                        
            public function tw_carousel_slider($args = array()){
    			global $wpdb;
    			$category_name = '';
    			$post = $wpdb->get_results('SELECT p.ID,p.guid,p.post_title,p.post_content,m.meta_value AS image FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON p.ID=m.post_id WHERE m.meta_key = "tw_carousel" AND m.meta_value != "" AND p.post_type="'.TW_FEED_TITLE.'" AND post_status="publish"');
    			
    			$carousel = '<style>'."\r\n";
    			$carousel .= '.hint-point:after{ 
    							content: ""; 
    							background: #fff; 
    							padding: 10px; 
    							height: 10px; 
    							width: 10px; 
    							left: 28px; 
    							transform: rotate(45deg); 
    							position: absolute; 
    							bottom: -10px; } 
    						.hint-point{ 
    							display: none; 
    							position: absolute; 
    							bottom: 110px; 
    							left: 90px; 
    							width: 100px; 
    							height: 100px; 
    							padding: 10px; 
    							background: #fff; } 
    						.carousel_nav{ 
    							background: #e96656; 
    							box-shadow: 0px 0px 15px rgba(0,0,0,.5); 
    							border: 2px solid #fff; 
    							width: 15px; 
    							height: 15px; 
    							float: left; 
    							margin-right: 10px; 
    							margin-top: 2px; 
    							border-radius: 100%; }
    						.carousel_image_holder{
    						    height: 400px; 
    						    width: 100%;
    						    overflow: hidden;
    						}
    						.carousel_image_holder img{
    							width: 100%; 
    							margin-top: -100px; 
    							border: none; }
    						.selected{ 
    							background: #fff; }
    						.carousel_holder h2{
    							position: absolute; 
    							box-shadow: 0px 5px 10px rgba(0,0,0,.3); 
    							right: 0px; 
    							top: 120px; 
    							padding: 30px; 
    							background: rgba(0,0,0,.5); 
    							width: 400px;
    						}
    						@media all and (max-width: 800px){
    							.carousel_image_holder img{
    								margin-top: 0px;
    							}
    							.carousel_holder h2{
    								width: 100%;
    								size: 10px;
    							}
    							.carousel_holder h2 a{
    								font-size: 10px;
    							}
    						}
    						</style>';
    			$carousel .= '<div class="carousel" style="height: 400px;">';
    			
    			$carousel_nav = '';
    			$carousel .= '<div class="delay" style="position: absolute; bottom: 5px; right: 10px; width: 40px; height: 40px; z-index: 10000000000; background: #fff; border-radius: 100%;"></div>';
    			foreach($post as $p){
    			    $images = explode(',',$p->image);
    				$image = $this->get_post_images($p->ID,$p->post_content,true);
    				
    				$category = get_the_category($p->ID);
    				$category_link = get_category_link(get_cat_ID($category[0]->name));
    				
    				if(strpos($image,'fotoyok3.jpg') === false){
    					$carousel .= '<div class="carousel_holder" style="position: absolute; width: 100%;">
    								<div class="carousel_image_holder">
    									<a href="'.$p->guid.'">'.$image.'</a>
    								</div>
    							<h2><a href="'.$p->guid.'">'.rawurldecode($p->post_title).'</a></h2>
    							<h3 style="position: absolute; left: 0px; bottom: 70px; padding: 10px; background: #e96656; color: #fff; font-size: 17px;"><a href="'.$category_link.'" style="color: #fff;">'.ucwords($category[0]->name).'</a></h3>
    						</div>';
    					$carousel_nav .= '<div class="carousel_nav"></div>';
    				}
            	}
            	
            	$carousel .= '<div class="carousel_nav_button" style="background: rgba(0,0,0,.7); padding: 5px; padding-left: 40px; height: 30px; position: absolute; z-index: 1000000; width: 100%; bottom: 10px;">'.$carousel_nav.'</div>';
            	$carousel .= '</div>';
            	$carousel .= '<div class="hint-point"></div>';
            	$carousel .= '<script>function Slider(delay,carousel,nav_buttons){
            					var self = this;
            					this.info = nav_buttons;
            					this.current_button = 0;
            					this.current_hover = 0;
            					this.delay = delay;
            					this.hint_point = document.getElementsByClassName("hint-point")[0];
            					
            					this.layout = carousel;
            					this.info[0].className = "carousel_nav selected";
            					
            					this.animate = function(id){
            						var carousel = document.getElementsByClassName("carousel_holder")[id];
            						var current_selected = document.getElementsByClassName("carousel_holder")[this.current_button];
            						if(carousel.innerHTML == current_selected.innerHTML){
            						    if(parseFloat(carousel.style.opacity) < 1){
            							    carousel.style.opacity = parseFloat(carousel.style.opacity)+.01;
            							    setTimeout(function(){ self.animate(id); },5);
            						    }
            						} else {
            						    if(parseFloat(carousel.style.opacity) > 0){
            							    carousel.style.opacity = parseFloat(carousel.style.opacity)-.01;
            							    setTimeout(function(){ self.animate(id); },5);
            						    } else {
            						        carousel.style.display = "none";
            						    }
            						}
            					}
            					
            					this.change_nav = function(el){
            					    if(self.current_selected != el.id){
    					                self.info[self.current_hover].className = "carousel_nav";
    					                el.className = "carousel_nav selected";
    					                self.current_hover = el.id;
    					            } else {
    					            	if(self.info[self.current_button].id != self.info[self.current_hover].id){
    					                	self.info[self.current_hover].className = "carousel_nav";
    					            	}
    					            }
    					            self.info[self.current_button].className = "carousel_nav selected";
            					}
            					
    							this.hover_effect = function(){
    								for(var i = 0; i < this.info.length; ++i){
    									/*this.info[i].onmouseover = function(el){
    										if(el.currentTarget.id == 0){
    											self.hint_point.style.left = parseInt(el.currentTarget.offsetLeft+10)+"px";
    										} else {
    											self.hint_point.style.left = parseInt(el.currentTarget.offsetLeft)+"px";
    										}
    										self.hint_point.style.display = "block";
    										self.change_nav(el.currentTarget);
    									}
    									this.info[i].onmouseout = function(el){
    										self.hint_point.style.display = "none";
    										if(el.currentTarget.id != self.current_button){
    											el.currentTarget.className = "carousel_nav";
    										}
    									}*/
    								}
    							}
    							
    							this.change_button = function(el){
    							    if(self.current_button != null){
    									var prev_button = self.current_button;
    									self.info[prev_button].className = "carousel_nav";
    									self.current_button = el.id;
    									document.getElementsByClassName("carousel_holder")[prev_button].style.zIndex = 1;
    									self.animate(prev_button);
    								} else {
    									self.current_button = el.id;
    								}
    								clearTimeout(self.rotation_timer);
    								self.info[self.current_button].className = "carousel_nav selected";
    								self.rotation_timer = setTimeout(function(){ self.auto_change(); },delay);
    								self.animate(self.current_button);
    								document.getElementsByClassName("carousel_holder")[el.id].style.display = "block";
    						    }
    							
    							this.auto_change = function(current){
    								var button = parseInt(this.current_button)+1;
    								if(button > this.info.length-1){
    									button = 0;
    								}
    								this.info[this.current_button].className = "carousel_nav";
    								this.info[button].className = "carousel_nav selected";
    								this.change_button(this.info[button]);
    								//this.info[button].click();
    							}
    							
    							for(var i = 0; i < this.layout.length; ++i){
    								if(i != 0){
    								    this.layout[i].style.display = "none";
    								    this.layout[i].style.opacity = 0;
    								} else {
    								    this.layout[i].style.display = "display";
    								    this.layout[i].style.opacity = 1;
    								}
    							}
    							
    							for(var i = 0; i < this.info.length; ++i){
    								this.info[i].id = i;
    								this.info[i].onclick = function(el){
    									if(self.current_button != null){
    										var prev_button = self.current_button;
    										self.info[prev_button].className = "carousel_nav";
    										self.current_button = el.currentTarget.id;
    										document.getElementsByClassName("carousel_holder")[prev_button].style.zIndex = 1;
    										self.animate(prev_button);
    									} else {
    										self.current_button = el.currentTarget.id;
    									}
    									clearTimeout(self.rotation_timer);
    									self.info[self.current_button].className = "carousel_nav selected";
    									self.rotation_timer = setTimeout(function(){ self.auto_change(); },delay);
    									self.animate(self.current_button);
    									document.getElementsByClassName("carousel_holder")[el.currentTarget.id].style.display = "block";
    								}
    							}
    							
    							this.hover_effect();
    							this.rotation_timer = setTimeout(function(){ self.auto_change(); },delay);
    						}
    					
    					var carousel = document.getElementsByClassName("carousel_holder");
    					var nav_buttons = document.getElementsByClassName("carousel_nav_button")[0].getElementsByTagName("div");
    					var create_slider = new Slider(7000,carousel,nav_buttons);
    					</script>';
            	return $carousel;
            }
            
            public function tw_check_feed(){
                include_once('tw_check_feed.php');
            }
            
            public function check_if_image_exist($file,$dir='',&$duplicates,$count=0){
    			if($dir == ''){
    				$dir = wp_upload_dir();
    				$dir_array = scandir($dir['basedir']);
    				$dir = $dir['basedir'];
    			} else {
    				$dir_array = scandir($dir);
    			}
    			
    			foreach($dir_array as $d){
    				if($d != '.' && $d != '..'){
    					$jpeg = explode('.',$d);
    					if(strpos($jpeg[1],'jp') !== false){
    						if(sizeof($jpeg) < 3){
    						    $d_update = $jpeg[0].'.jpg';
    						}
    						rename($dir.'/'.$d,$dir.'/'.$d_update);
    						$d = $d_update;
    					}
    					if(is_dir($dir.'/'.$d)){
    						check_if_image_exist($file,$dir.'/'.$d,++$count,$duplicates);
    					} else {
    						$content = sha1_file($dir.'/'.$d);
    						if(similar_text($content,sha1_file($dir.'/'.$d)) > 90){
    							++$count;
    							$duplicates[$d][] = $dir.'/'.$d;
    						}
    					}
    				}
    			}
    			return $count;
            }
            
            public function tw_load_info_action(){
            	if(isset($_GET['page']) && $_GET['page'] == 'tw_edit_feed_data'){
            		$options = json_decode(get_option('rss_feeds'),true);
            		for($i = 0; $i < sizeof($options); ++$i){
            			if(!isset($options[$i]['active'])){
            				$options[$i]['active'] = 'true';
            			}
            		}
            	}
            	
            	if(isset($_POST['feed_name']) && $_POST['feed_name'] != '' && $_GET['page'] == 'tw_edit_feed_data'){
    				$feeds = json_decode(get_option('rss_feeds'),true);
    				$json = array();
    				
    				$ex = explode('|',$_GET['ID']);
    				
                    foreach($_POST as $k=>$f){
    					if($k != 'submit'){
    						if($k == 'length-of-storage-days'){
    							if($_POST['length-of-storage-days'] > 30){
    								$_POST['length-of-storage-days'] = 0;
    								++$_POST['length-of-storage-months'];
    							}
    							if($_POST['length-of-storage-months'] > 11){
    								$_POST['length-of-storage-months'] = 0;
    								++$_POST['length-of-storage-years'];
    							}
    							if($_POST['length-of-storage-years'] > 10){
    								$_POST['length-of-storage-years'] = 10;
    							}
    							$f = $_POST['length-of-storage-days'];
    						}
    					    $json[$k] = $f;
    					}
                    }
                    
                    if(!isset($json['feed_image_enabler'])){
                        $json['feed_image_enabler'] = 'false';
                    }
                    
                    if(!isset($json['best_feeds'])){
                        $json['best_feeds'] = "false";
                    }
                    
                    foreach($feeds as $k=>$c){
    					if($c['feed_name'] == $_POST['feed_name'] || $c['feed_url'] == $_POST['feed_url']){
    						global $wpdb;
    						$string = substr($_GET['ID'],0,strrpos($_GET['ID'],'|')-strlen($_GET['ID']));
    						$explode = explode('|',$_GET['ID']);
    						$results = $wpdb->get_results('SELECT p.ID FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id = p.ID WHERE m.meta_value LIKE "'.$string.'%" OR m.meta_value LIKE "%'.$explode[0].'%"');
    						
    						$string = $_POST['feed_name'].'|'.$_POST['feed_url'].'|'.$_POST['feed_category'];
    						
    						$ids = '';
    						
    						foreach($results as $c){
    						    $ids .= $c->ID.',';
    						}
    						
    						$ids = explode(',',substr($ids,0,-1));
    						$ids = array_chunk($ids,100);
    						
    						foreach($ids as $f){
    						    $f = implode(',',$f);
    						    
    						    $results = $wpdb->get_results('UPDATE '.$wpdb->postmeta.' SET meta_value = "'.$string.'" WHERE meta_key="tw_rss_feed_options" AND post_id IN('.$f.')');
    						    $results = $wpdb->get_results('UPDATE '.$wpdb->term_relationships.' SET term_taxonomy_id = "'.$_POST['feed_category'].'" WHERE term_id IN ('.$f.')');
    						}
    						$feeds[$k] = $json;
    					}
    					
    					if(is_array($c) && is_array($ex)){
    						if($c['feed_name'] == $ex[0]){
    							$feeds[$k] = $json;
    						}
    					}
                    }
                    
                   	//$feeds[] = $json;
                   	update_option('rss_feeds',json_encode($feeds));
                   	wp_redirect(admin_url().'?page=twp_admin');
                }
            }
            
            public function tw_edit_feed_data(){
                $feeds = json_decode(get_option('rss_feeds'),true);
                
                if(isset($_GET['ID'])){
                    $info = explode('|',$_GET['ID']);
                } else {
                    $info = array('','');
                }
                
                global $wpdb;
                
    			foreach($feeds as $f){
    				if(sizeof($info) == 1){
    					if($f['feed_name'] == $info[0]){
    						$this->tw_update($f,$wpdb);
    						include_once(plugin_dir_path( __FILE__ ).'edit_post.php');
    					} elseif($f['feed_url'] == $info[0]){
    					    $this->tw_update($f,$wpdb);
    						include_once(plugin_dir_path( __FILE__ ).'edit_post.php');
    					}
    				} else {
    					if($f['feed_name'] == $info[0] && $f['feed_url'] == $info[1]){
    						$this->tw_update($f,$wpdb);
    						include_once(plugin_dir_path( __FILE__ ).'edit_post.php');
    					}
    				}
    			}
            }
            
            public function check_images(){
    			$args = array(
    				'post_type'			=>	TW_FEED_TITLE,
    				'posts_per_page'	=>	-1
    				);
    			$query = new WP_Query($args);
            }
            
            public function tw_get_feed_registration(){
    			global $wpdb;
    			
    			$title = get_option('tw_feed_title');
    			if($title == 'feeds'){
                   	define('TW_FEED_TITLE','tw_feeds');
    			} else {
                	define('TW_FEED_TITLE',$title);
    			}
    			
                global $wp_rewrite,$wpdb;
                $wp_rewrite->flush_rules( true );
                
                $query_layout = array();
                
                if($title == '' || $title == 'feeds' || $title == 'post' || $title == 'category' 
                || $title == 'tag' || $title == 'type' || $title == 'search' || $title == 'author'){
                    $results = $wpdb->get_results('SELECT m.post_id FROM '.$wpdb->postmeta.' as m JOIN '.$wpdb->posts.' as p ON p.ID=m.post_id WHERE m.meta_key = "tw_rss_feed_options" AND p.post_type != "'.TW_FEED_TITLE.'"');
                    if(sizeof($results) > 0){
                        foreach($results as $f){
                        	wp_update_post(array('ID'=>$f->post_id,'post_type'=>TW_FEED_TITLE));
                        }
                    }
                    
                    $page = new WP_Query('post_type=page&title=feeds');
                    
                    $array_layout = $page->found_posts;
                    if($array_layout < 1){
    					$page = array(
    						'post_title'=>'Feeds',
    						'post_content'=>'[feed_searches][/feed_searches]',
    						'post_category'=>array($feed_category),
    						'post_author'=>1,
    						'post_status'=>'publish',
    						'post_type'=>'page',
    						'post_name'=>'tw_feed_page',
    					);
    					wp_insert_post($page);
                    }
                    
                    update_option('tw_feed_title','tw_feeds');
                } else {
    				global $wpdb;
                    $results = $wpdb->get_results('SELECT m.post_id FROM '.$wpdb->postmeta.' as m JOIN '.$wpdb->posts.' as p ON p.ID=m.post_id WHERE m.meta_key = "tw_rss_feed_options" AND p.post_type != "'.get_option('tw_feed_title').'"');
                    if(sizeof($results) > 0){
    					foreach($results as $f){
                    		wp_update_post(array('ID'=>$f->post_id,'post_type'=>get_option('tw_feed_title')));
                    	}
                    }
                }
                
                if(isset($_POST['tw_feed_title'])){
    				global $wp_query;
                    update_option('tw_feed_title',$_POST['tw_feed_title']);
                }
                
                $array = array(
    				'public' 		=> true,
    				'label'  		=> ucwords(str_replace('_',' ',TW_FEED_TITLE)),
    				'taxonomies'  => array('category'),
    				'supports'	=> array('title','editor','author','thumbnail','excerpt','comments'),
    				'rewrite'   => true,
    				'slug'      => ''
    			);
    			
    			register_post_type(strtolower(TW_FEED_TITLE),$array);
    			add_filter( 'manage_'.strtolower(TW_FEED_TITLE).'_posts_columns' , array($this,'edit_title_column') );
            }
            
            public function tw_remove_cpt_slug( $post_link, $post, $leavename ) {
                if ( $post->post_type == TW_FEED_TITLE || 'publish' != $post->post_status ) {
                    return $post_link;
                }
                return $post_link;
            }
            
            public function tw_parse_request_trick( $query ) {
                // Only noop the main query
                if ( ! $query->is_main_query() )
                    return;
             
                // Only noop our very specific rewrite rule match
                if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
                    return;
                }
             
                // 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
                if ( ! empty( $query->query['name'] ) ) {
                    $query->set( 'post_type', array( 'post', 'page', TW_FEED_TITLE ) );
                }
            }
            
            public function tw_edit_title($parts){
    			$parts['title'] = rawurldecode(rawurldecode(rawurldecode(htmlspecialchars_decode($parts['title']))));
    			return $parts;
            }
            
            public function parse_wordpress($wp_query){
    			global $post;
    			if(isset($post)){
    				$post->post_title = str_replace("\'","'",htmlspecialchars_decode(rawurldecode($post->post_title)));
    				return $post;
    			}
            }
    		
    		public function edit_title_column( $columns ) {
    		return array_merge( $columns, 
    			array( 'custom_title' => __( 'Title', 'your_text_domain' ) ) );
    		}
    		
    		public function display_posts_stickiness( $column, $post_id ) {
    			if($column == 'custom_title'){
    				echo '<b style="font-size: 15px;">'.str_replace("\'","'",htmlspecialchars_decode(rawurldecode(get_the_title($post_id)))).'</b> <div><a href="'.admin_url( 'post.php?post='.$post_id.'&action=edit').'">Edit</a> | <a href="'.get_permalink($post_id).'" target="_blank">Views</a></div>';
    			}
    		}
            
            public function restruct_title_to_readable($data,$postarr){
            	$post = get_post();
    			if(isset($post->ID) && isset($_POST)){
    				$info = '';
    				
    				$images = get_post_meta($post->ID,'tw_images');
    				if(isset($images[0])){
        				$info_holder = json_decode($images[0],true);
        				if(isset($_POST['tw_carousel_images'])){
        				    $info = '';
            				foreach($_POST['tw_carousel_images'] as $k=>$f){
            					if($info_holder[$f] != ''){
            						$info .= str_replace(',','|',$info_holder[$f]).',';
            					}
            				}
            				
            				delete_post_meta($post->ID,'tw_carousel');
            				add_post_meta($post->ID,'tw_carousel',$info) || update_post_meta($post->ID,'tw_carousel',$info);
        				} else {
        				    delete_post_meta($post->ID,'tw_carousel');
        				}
        				
        				$images = get_post_meta($post->ID,'tw_images');
        				
    					$json = json_decode($images[0],true);
    					
    					foreach($json as $k=>$p){
    					    if(isset($_POST['tw_images'][$k])){
    					        if(!is_array($json[$k])){
    						        $json[$k] = array('delete'=>$json[$k]);
    					        }
    					    } else {
    					        if(is_array($json[$k])){
    					            $json[$k] = $json[$k]['delete'];
    					        }
    					    }
    					}
    					
    					delete_post_meta($post->ID,'tw_images');
    					add_post_meta($post->ID,'tw_images',json_encode($json)) || update_post_meta($post->ID,'tw_images',json_encode($json));
        				
        				if(isset($_POST['tw_all_images'])){
        					delete_post_meta($post->ID,'tw_all_images');
        					add_post_meta($post->ID,'tw_all_images','true');
        				} else {
        					delete_post_meta($post->ID,'tw_all_images');
        				}
        				
        				if(isset($_POST['tw_undelete_images'])){
        				    $info = explode(',',$_POST['tw_undelete_images']);
        				    $images = get_post_meta($post->ID,'tw_images');
        				    $json = json_decode('{"images":'.$images[0].'}',true);
        				    
        				    preg_match_all('/<img.*?(href|src)=[\'"](.*?)[\'"].*?>/is',$post->post_content,$p);
        				    
        				    foreach($info as $f){
        				        if(isset($p[2][$f]) && $p[2][$f] != ''){
        				            $image_holder = explode('/',$p[2][$f]);
        				            $image_holder = explode('?',$image_holder[sizeof($image_holder)-1]);
        				            $json[$f] = $image_holder[0];
        				        }
        				    }
        				    
        				    delete_post_meta($post->ID,'tw_images');
        				    add_post_meta($post->ID,'tw_images',json_encode($json['images']));
        				}
        				
        				if(isset($_POST['tw_stored_feed'])){
        					add_post_meta($post->ID,'tw_stored_feed','store') || update_post_meta($p[0]->ID,'tw_stored_feed','store');
        				} else {
        				    delete_post_meta($post->ID,'tw_stored_feed');
        				}
        				
        				if(isset($_POST['tw_adult'])){
        					add_post_meta($post->ID,'tw_adult','true') || update_post_meta($p[0]->ID,'tw_adult','true');
        				} else {
        				    delete_post_meta($post->ID,'tw_stored_feed');
        				}
        				
        				if($post->post_type == TW_FEED_TITLE && strpos($data['post_title'],"%20") === false){
        					$data['post_title'] = str_replace("\"",'"',str_replace("\'","'",$data['post_title']));
        					$postarr['post_title'] = str_replace("\"",'"',str_replace("\'","'",$postarr['post_title']));
        					$data['post_title'] = htmlspecialchars_decode(rawurlencode($data['post_title']));
        					$postarr['post_title'] = htmlspecialchars_decode(rawurlencode($postarr['post_title']));
        				}
    				}
            	}
    			return $data;
            }

            // Register and load the widget
            public function tw_load_widget() {
                include_once('tw_tp-admin.php');
                include_once('tw_widget.php');
                
    			register_widget( 'tw_widget' );
            }
            
            public function tw_impressions(){
                include_once(plugin_dir_path( __FILE__ ).'tw_impressions.php');
            }
            
            public function tw_monitization(){
                include_once(plugin_dir_path( __FILE__ ).'tw_blogging_options.php');
            }
            
            public function tw_feed_update(){
                update_option('tw_rss_feed_update_'.date('Y-m-d_h:i:s'),$data);
                $data = json_decode($data,true);
                include_once(plugin_dir_path( __FILE__  ).'cron/blog_updated.php');
            }
            
            public function best_feeds($f){
            	echo $f['feed_url'];
            }
            
            public function tw_update($f,&$wpdb){
            	$query = array();
            	
    			if(isset($f['best_feeds'])){
    				
    				$json = json_decode($this->tw_best_feeds($f['feed_url']),true);
           	
    	           	$guid = '';
    	           	foreach($json as $j){
    	           		$guid .= '"'.$j['current_page'].'",';
    	           	}
    	           	$query = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' WHERE guid IN ('.substr($guid,0,-1).')');
    			}
    			
    			if(is_object($f)){
    			    $feed_info_layout = $f['feed_name'].'|'.$f['feed_url'].'|'.$f['feed_category'];
    			    $offset_date = ($f['length-of-storage-days'] == 0 || !isset($f['length-of-storage-days']))?'':'-'.$f['length-of-storage-days'].' days ';
    		    	$offset_date .= ($f['length-of-storage-months'] == 0 || !isset($f['length-of-storage-months']))?'':'-'.$f['length-of-storage-months'].' months ';
    		    	$offset_date .= ($f['length-of-storage-years'] ==0 || !isset($f['length-of-storage-years']))?'':'-'.$f['length-of-storage-years'].' years ';
    			}
    			$count = 0;
    			
    			$exclude = '';
    			foreach($query as $p){
    				$exclude .= $p->ID.',';
    			}
    			$exclude = substr($exclude,0,-1);
    			
    			if(isset($offset_date) && $offset_date != ''){
    				$offset_date = date('Y-m-d 00:00:00',strtotime(date('Y-m-d ').' '.$offset_date));
    				$exclude =  'AND ID NOT IN ('.$exclude.')';
    				
    				$delete_info = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' as p JOIN '.$wpdb->postmeta.' as m ON m.post_id=p.ID WHERE p.post_date <= "'.$offset_date.'" AND p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$feed_info_layout.'" '.$exclude.' GROUP BY ID');
    				
    				foreach($delete_info as $p){
    					tw_delete_post($p->ID);
    				}
    			}
    			
    			$feed_info = $f['feed_name'].'|'.$f['feed_url'].'|'.$f['feed_category'];
                $query = $wpdb->get_results('SELECT COUNT(ID) FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' as m ON p.ID=m.post_id WHERE m.meta_value="'.$feed_info.'"');
    			
                if(isset($f['total_feeds']) && $query[0]->{'COUNT(ID)'} > $f['total_feeds'] && isset($f['total_feeds']) &&$f['total_feeds'] != ''){
                    $limit = ($query[0]->{'COUNT(ID)'}-$f['total_feeds']);
                }
            }
            
            public function tw_best_feeds($url,$all=false){
            	$best_feeds = array();
            	if(!is_object($url)){
    			    $url = parse_url($url);
    			    $url['host'] = (!isset($url['host']))?'':$url['host'];
    			    if($all!=true){
    			        return $this->api_call('http://quanticpost.com/getdata/get_best_feeds/'.str_replace(".",'^',str_replace('/','_',str_replace("http://",'',str_replace("https://",'',site_url())))).'/'.str_replace('.','^',$url['host']).str_replace('.','^',str_replace('/','|',$url['path'])));
    			    } else {
    			        return $this->api_call('http://quanticpost.com/getdata/get_best_feeds/all/'.str_replace('.','^',$url['host']));
    			    }
    			} else {
                    return '';
                }
            }
            
            public function tw_rss_feed_impression() {
    			if(!file_exists(plugin_dir_path( __FILE__ ) . 'css/feeds/style_custom_'.str_replace(' ','_',get_bloginfo('name')).'.css')){
    				$style_content = get_option('tw_rss_feed_style_content');
    				if($style_content == ''){
    					$style_content = file_get_contents(plugin_dir_url( __FILE__ ).'css/feeds/style.css');
    				}
    				$fh = fopen(plugin_dir_path( __FILE__ ) . 'css/feeds/style_custom_'.str_replace(' ','_',get_bloginfo('name')).'.css','w+');
                    fwrite($fh,$style_content);
                    fclose($fh);
    			}
                if(!is_user_logged_in()){
                    $user = wp_get_current_user();
                    
                    if(isset($user->roles[0]) && $user->roles[0] != 'administrator'){
                        include_once(plugin_dir_path( __FILE__  ).'cron/remember_impression.php');
                    } else {
                        include_once(plugin_dir_path( __FILE__  ).'cron/remember_impression.php');
                    }
                }
                
                global $post;
    		    if(isset($post) && $post->post_type == TW_FEED_TITLE){
        		    $post_adult = get_post_meta($post->ID,'tw_adult');
    		        if(isset($post_adult[0]) && $post_adult[0] == 'true'){ 
    		            define('TW_ADULT','true'); ?>
    		            <script>var tw_adult_content = 'true'; </script>
    			<?php } 
    			}
                $options = get_option('rss_feeds');
                $var = json_decode($options,true);
                global $wpdb;
                foreach($var as $f){
                    if(is_object($f)){
    				    $length = (!isset($f->{'length-of-storage-days'}) && $f->{'length-of-storage-days'} == '0')?' ':' -'.($f->{'length-of-storage-days'}).' days ';
    				    $length .= (!isset($f->{'length-of-storage-month'}) && $f->{'length-of-storage-months'} == '0')?' ':' -'.($f->{'length-of-storage-months'}).' months ';
    				    $length .= (!isset($f->{'length-of-storage-year'}) && $f->{'length-of-storage-years'} == '0')?' ':' -'.($f->{'length-of-storage-years'}).' years ';
    				    $result = $wpdb->get_results('SELECT COUNT(p.ID) FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON p.ID=m.post_id WHERE m.meta_value="'.$i['feed_url'].'" AND p.post_date <= "'.date('Y-m-d 00:00:00',strtotime(date('Y-m-d').' '.$length)).'"');
                    }
                }
            }
            
            public function tw_rss_feed_custom_style(){
                $string = '';
                if(get_option('tw_rss_feed_animation') == 'true'){
    				$string .= '<script src="http://quanticpost.com/js/roll_over_animation.js"></script>';
    				$string .= '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url( __FILE__ ) . 'css/feeds/style_animation.css"></link>';
    			} else {
    				if(get_option('tw_custom_css') == 'true'){
    				    $string .= '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url( __FILE__ ) . 'css/feeds/style_custom_'.str_replace(' ','_',get_bloginfo('name')).'.css"></link>';
    				} else {
    				    $string .= '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url( __FILE__ ) . 'css/feeds/style.css"></link>';
    				}
    			}
    			echo $string;
            }
            
            public function get_post_images($id,$content,$keep_width = false,$image_url_only=false,$dir_only=false,$delete=false,$thumnail=true){
                $content = preg_replace("/<img.*?(src|href)=[\"'](.*?)[\"'] .*?width=[\"']1[\"'].*?\/>/is", "", $content);
                preg_match_all('/img.*?(src|url|href)=([\'"](.*?)[\'"]|(.*?)).*?/is',$content,$image_array);
                
                $all_images = get_post_meta($id,'tw_all_images');
                $all_images = (isset($all_images[0]) && $all_images[0] != '' && $all_images[0] == 'true')?true:false;
                
                $all_images = false;
                
                $images = get_post_meta($id,'tw_images');
                if(isset($images[0])){
                    $images = json_decode($images[0],true);
                } else {
                    $images = array();
                }
                
                if(sizeof($images) != sizeof($image_array[3])){
                    update_post_meta($id,'tw_images',json_encode($image_array[3]));
                    $images = get_post_meta($id,'tw_images');
                }
                
                if(isset($image_array[3]) && sizeof($image_array[3]) > 0){
                    foreach($image_array[3] as $k=>$image){
                        $first_image = '';
                    	$image = str_replace('https','http',$image);
                    	
                        $image = explode('http://',$image);
                        $strlen = 0;
                        foreach($image as $f){
                        	if(strlen($f) > $strlen){
                        		if(is_numeric(strpos($f,'//')) && strpos($f,'//') == 0){
                        			$image_display = 'http:'.$f;
                        		} else {
                        			$image_display = 'http://'.$f;
                        		}
                        		$strlen = strlen($f);
                        	}
                        }
                        
                        $image = $image_display;
                        $image_holder = $image;
                        
                        $url = parse_url($image);
                        $upload_dir = wp_upload_dir();
                        
                        if($dir_only == true){
                            $upload_dir['baseurl'] = $upload_dir['basedir'];
                        }
                        
                        $image = explode('/',$image);
                        $image = explode('?',$image[sizeof($image)-1]);
                        
    	                if(!isset($url['scheme'])){
    	                    $image_holder = 'http:'.$image_holder;
    	                }
    	                
    	                $delete = (isset($delete))?$delete:false;
    	                
    	                $check_image = preg_match('/.(jpg|jpeg|bmp|tiff|gif|png|targ)/is',$image[0]);
    	                
    	                if($check_image == 1 && !is_file($upload_dir['basedir'].'/tw-rss-feeds/'.$url['host'].'/'.$image[0]) && !$delete){
    	                	$image = $this->tw_upload_files($image_holder,$id,$url['host']);
    	                	
    	                	switch($image){
    	                		case 'image too small':
    	                			$image = 'http://quanticpost.com/images/small.gif';
    	                			break;
    	                		case 'not uploaded':
    	                			$image = 'http://quanticpost.com/images/fotoyok3.jpg';
    	                			break;
    	                		default:
    	                		    if(strpos($image,'video') !== false){
    	                		        $first_image = $image_holder;
    	                		        break;
    	                		    }
    	                			if($first_image == ''){
    			                        $first_image = $image;
    			                    }
    	                			break;
    	                	}
    	                } else {
    	                    if(isset($delete) && $delete){
    	                        unlink($upload_dir['basedir'].'/tw-rss-feeds/'.$url['host'].'/'.$image[0]);
    	                    }
    	                    if(isset($image_url_only) && $image_url_only){
    	                	    $first_image = $upload_dir['baseurl'].'/tw-rss-feeds/'.$url['host'].'/'.$image[0];
    	                    } else {
    	                        $first_image = $upload_dir['baseurl'].'/tw-rss-feeds/'.$url['host'].'/'.$image[0];
    	                    }
    	                    if(isset($first_image) && $first_image == '' && $check_image == 1){
    	                        $first_image = $image;
    	                    }
    	                }
    	                
    	                $first_image_holder = explode('/',$first_image);
    	                
    	                if($all_images == false){
        	                if($k == sizeof($image_array[3])-1 && ($image != 'http://quanticpost.com/images/fotoyok3.jpg' || $image != 'http://quanticpost.com/images/small.gif')){
        	                    if($image_url_only){
        	                        if($image != 'http://quanticpost.com/images/fotoyok3.jpg' && $image != 'http://quanticpost.com/images/small.gif'){
        	                            return $first_image;
        	                        }
        	                    }
        	                    
        	                    if($first_image == ''){
        	                    	$first_image = 'http://quanticpost.com/images/small.gif';
        	                    }
        	                    
        	                    if($keep_width){
        	                        if($check_image == 0) $first_image = 'http://quanticpost.com/images/fotoyok3.jpg';
        	                    	return '<div class="tw_small_layout" style="height: auto;"><img src="'.$first_image.'" style="width: 100%;"/></div>';	
        	                    } else {
        	                        if($check_image == 0) $first_image = 'http://quanticpost.com/images/fotoyok3.jpg';
        	                    	return '<div class="tw_small_layout"><img src="'.$first_image.'" style="width: 100%;"/></div>';
        	                    }
        	                }
    	                } else {
    	                	if(sizeof($image_array[3])-1 == $k){
    	                		return '<div class="tw_small_layout" style="height: auto;"><img src="'.$first_image.'" style="width: 100%;"/></div>';
    	                	}
    	                }
                    }
                }
                return '<div class="tw_small_layout"><img src="http://quanticpost.com/images/fotoyok3.jpg" style="width: 100%;"/></div>';
            }
            
            public function footer($content){
    			global $wpdb, $post;
    			$footer = get_option('tw_pagination_footer');
    			
    			$query = new WP_Query('ID='.$post->ID);
    			$meta_info = get_post_meta($post->ID);
    			
    			preg_match("/Read More: <a href=[\"'](.*?)[\"'].*?>(.*?)<\/a>/is", $post->post_content, $link);
    			
    			if(isset($post->post_content) && isset($link[1])){
    			    $post->post_content = preg_replace("/<img(.*?)(href|src)=['\"](.*?)['\"](.*?)>/is",'<a href="'.$link[1].'" target="_blank">'.$this->get_post_images($post->ID,$post->post_content,true).'</a>',$post->post_content,1);
    			}
    			
    			if($footer == 'true' && isset($post->ID) && $post->post_type == TW_FEED_TITLE){
    				$category = get_the_category ($post->ID);
    				
    				$post_previous = $wpdb->get_results('SELECT p.post_content,p.post_title,p.ID FROM '.$wpdb->posts.' as p JOIN '.$wpdb->term_relationships.' as t ON t.object_id = p.ID WHERE p.ID < '.$post->ID.' AND p.post_type = "'.TW_FEED_TITLE.'" AND p.post_status != "trash"  AND t.term_taxonomy_id = '.$category[0]->term_id.' ORDER BY p.ID DESC LIMIT 1');
    				$post_next = $wpdb->get_results('SELECT p.post_content,p.post_title,p.ID FROM '.$wpdb->posts.' as p JOIN '.$wpdb->term_relationships.' as t ON t.object_id = p.ID WHERE p.ID > '.$post->ID.' AND p.post_type = "'.TW_FEED_TITLE.'" AND p.post_status != "trash" AND t.term_taxonomy_id = "'.$category[0]->term_id.'" ORDER BY p.ID ASC LIMIT 1');
    				$post->post_content .= '<div class="tw_pagination_holder">';
    				
    				if(isset($post_previous[0]->post_title)){
    				    $image = $this->get_post_images($post_previous[0]->ID,$post_previous[0]->post_content);
    					$post->post_content .= '<div class="tw_pagination_left"><a href="'.get_permalink($post_previous[0]->ID).'"><div class="background_holder">'.$image.'</div></a><a href="'.get_permalink($post_previous[0]->ID).'">'.html_entity_decode(trim(rawurldecode($post_previous[0]->post_title))).'</a></div>';
    				}
    				if(isset($post_next[0]->post_title)){
    				    $image = $this->get_post_images($post_next[0]->ID,$post_next[0]->post_content);
    					$post->post_content .= '<div class="tw_pagination_right"><a href="'.get_permalink($post_next[0]->ID).'"><div class="background_holder">'.$image.'</div></a><a href="'.get_permalink($post_next[0]->ID).'">'.html_entity_decode(trim(rawurldecode($post_next[0]->post_title))).'</a></div>';
    				}
    				$post->post_content .= '</div>';
    			}
            }
            
    		public function tw_custom_meta_boxes() {
    			add_meta_box('text_info',__( 'Feed Options', 'myplugin_textdomain' ),array($this,'tw_rssfeed_meta_box_callback'),TW_FEED_TITLE);
    		}
    		
    		public function tw_categories(){
    			if(isset($_POST['feeds']) && $_POST['feeds'] != ''){
    				$feeds = get_option('rss_feeds');
    				if($feeds != ''){
    					$feeds = str_replace('[','',str_replace(']','',$feeds)).',';
    				}
    				$x = explode(',',$_POST['feeds']);
    			    
    				foreach($x as $i){
    					$feed_info = explode('|',$i);
    					if($feed_info[0] != ''){
    						if(sizeof($feed_info) < 3){
    							$id = get_terms( 'category', array( 'search' => $feed_info[1] ) );
    							if(sizeof($id) < 1){
    								$id = wp_create_category($feed_info[1]);
    							} else {
    						        $id = $id[0]->name;
    							}
    							$feeds .= '{"feed_name":"'.$feed_info[0].'","feed_url":"'.$feed_info[0].'","feed_category":"'.$id.'","active":"true"},';
    						} else {
    						    $id = get_terms( 'category', array( 'search' => $feed_info[2] ) );
    							if(sizeof($id) < 1){
    								$id = wp_create_category($feed_info[2]);
    							} else {
    						        $id = $id[0]->name;
    							}
    							$feeds .= '{"feed_name":"'.$feed_info[0].'","feed_url":"'.$feed_info[0].'","feed_category":"'.$id.'","feed_image_enabler":"true","active":"true"},';
    						}
    					}
    				}
    				
    				if(strpos($feeds,',{') == 0){
    					$feeds = substr($feeds,1,strlen($feeds)-1);
    				}
    				update_option('rss_feeds',$this->sanatize('['.substr($feeds,0,-1).']'));
    			}
    		    ?>
    		        <iframe src="http://quanticpost.com/views/client_products/categories/basic_category_search.php?language=english&host=<?php echo str_replace(":",'_',str_replace("/","|",get_bloginfo('url'))); ?>" style="width: 100%; height: 1500px;"></iframe>
    		        <form action="" method="POST" id="submit">
    		            <input type="hidden" name="feeds" class="feed_info"/>
    		        </form>
    		        <script>
    		            function get_feeds(el){
    		                document.getElementsByClassName('feed_info')[0].setAttribute('value',el.data);
    		                document.getElementById('submit').submit();
    		            }
    		            window.addEventListener('message',get_feeds,true);
    		        </script>
    		    <?php
    		}
    		
    		public function tw_analytics(){
    			$this->tw_edit_feed_data();
    			include_once('class/analytics.php');
    		}
    		
    		public function tw_rssfeed_meta_box_callback($args){
    			global $post;
    			wp_nonce_field( 'tw_rssfeed_inner_custom_box', 'tw_rssfeed_inner_custom_box_nonce' );
    			include_once('feed_meta_box.php');
    		}
            
    		public function tw_theme_options(){
    			if(isset($_POST) && sizeof($_POST) > 0){
    				
    				wp_clear_scheduled_hook( 'tw_hourly_event' );
    				
    				if(isset($_POST['tw_feed_title'])){
    	                update_option('tw_feed_title',$_POST['tw_feed_title']);
    	                wp_redirect( $_SERVER['HTTP_REFERER'] );
    	            }
    				
    				if(isset($_POST['schedule'])){
    					if(get_option('tw_schedule_event') != $_POST['schedule']){
    						update_option('tw_schedule_event',$_POST['schedule']);
    						$curr_schedule = get_option('tw_schedule_event');
    						
    						switch($_POST['schedule']){
    							case '30-minutes':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +30 minutes');
    								break;
    							case '15-minutes':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +15 minutes');
    								break;
    							case 'hourly':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +1 hours');
    								break;
    							case 'twicedaily':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +12 hours');
    								break;
    							default:
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +24 hours');
    								break;
    						}
    						
    						update_option('tw_curr_schedule', $curr_schedule);
    					}
    				}
    				
    				if(isset($_POST['top_content'])){
    					update_option('tw_get_top_content','true');
    				} else {
    					update_option('tw_get_top_content','false');
    				}
    				
    				if(isset($_POST['tw_rss_feed_animation'])){
    					update_option('tw_rss_feed_animation','true');
    				} else {
    					update_option('tw_rss_feed_animation','false');
    				}
    				
    				if(isset($_POST['tw_pagination'])){
    					update_option('tw_pagination','true');
    				} else {
    					update_option('tw_pagination','false');
    				}
    				
    				if(isset($_POST['tw_pagination_footer'])){
    					update_option('tw_pagination_footer','true');
    				} else {
    					update_option('tw_pagination_footer','false');
    				}
    				
    				if(isset($_POST['auto_create_pages'])){
    					update_option('tw_auto_create_pages','true');
    				} else {
    					update_option('tw_auto_create_pages','false');
    				}
    				
    				if(isset($_POST['tw_custom_css'])){
    					update_option('tw_custom_css','true');
    					update_option('tw_rss_feed_style_content',$_POST['custom_css']);
    					$fh = fopen(plugin_dir_path( __FILE__ ) . 'css/feeds/style_custom_'.str_replace(' ','_',get_bloginfo('name')).'.css','w+');
    					fwrite($fh,$_POST['custom_css']);
    					fclose($fh);
    				} else {
    					update_option('tw_custom_css','false');
    				}
    				
    				global $wpdb;
    				if(isset($_POST['unchecked_carousel'])){
        				$psw = explode(',',$_POST['unchecked_carousel']);
        				foreach($psw as $f){
        				    if(isset($f[0]) && is_object($f[0])){
        				        delete_post_meta($f[0]->ID,'tw_carousel');
        				    }
        				}
    				}
    				
    				if(isset($_POST['tw_featured'])){
    					$featured = '';
    					foreach($_POST['tw_featured'] as $f){
    						if($f != ''){
    							$featured .= $f.','; 
    						}
    					}
    					
    					delete_option('tw_featured');
    					update_option('tw_featured',$featured);
    				}
    				
    				if(isset($_POST['tw_featured'])){
    					foreach($_POST['tw_featured'] as $f){
    						$p = $wpdb->get_results('SELECT ID,post_content FROM '.$wpdb->posts.' AS p WHERE guid LIKE "%'.$f.'%"');
    						$carousel = get_post_meta($p[0]->ID,'tw_carousel');
    						
    						if(!isset($carousel[0])){
    							$image_check = $this->get_post_images($p[0]->ID,$p[0]->post_content,true,true);
    							if($image_check != 'not uploaded'){
    								add_post_meta($p[0]->ID,'tw_carousel','featured') || update_post_meta($p[0]->ID,'tw_carousel','featured');
    							}
    						}
    					}
    				}
    				
    				if(isset($_POST['tw_featured_id'])){
    					foreach($_POST['tw_featured_id'] as $p){
    						delete_post_meta($p,'tw_carousel');
    					}
    				}
    				
    				$this->tw_setup_schedule();
    			}
    			
    			if(!file_exists(plugin_dir_path( __FILE__ ) . 'css/feeds/style_custom_'.str_replace(' ','_',get_bloginfo('name')).'.css')){
    				$style_content = get_option('tw_rss_feed_style_content');
    				if($style_content == ''){
    					$style_content = file_get_contents(plugin_dir_url( __FILE__ ).'css/feeds/style.css');
    				}
    				$fh = fopen(plugin_dir_path( __FILE__ ) . 'css/feeds/style_custom_'.str_replace(' ','_',get_bloginfo('name')).'.css','w+');
    				fwrite($fh,$style_content);
    				fclose($fh);
    			}
    			
    			$server = '{"server":"'.site_url().'"}';
    			$data = $this->api_call("http://quanticpost.com/info_pull/blog_update/?info=".$server);
    			
    			include_once('ttp-import-settings.php');
    		}
    		
    		public function api_call($url,$post_fields = array()){
    		    if(!is_dir(plugin_dir_path( __FILE__ ).'api_cache/')){
    		        mkdir(plugin_dir_path( __FILE__ ).'api_cache/',0777);
    		    }
    		    
    			$data = '';
    			$cache_url = plugin_dir_path( __FILE__ ).'api_cache/'.md5(rawurlencode($url));
    			
    			if(is_file($cache_url) && filemtime($cache_url) > strtotime('-1 minutes')){
    			    $fh = fopen($cache_url,'r+');
        			$content = fread($fh,filesize($cache_url));
        			fclose($fh);
        			
        			$data = json_decode($content,true);
        			$data = json_decode($data['data']);
    			} else {
        			$ch = curl_init();
        			curl_setopt($ch, CURLOPT_URL, $url);
        			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        			curl_setopt($ch, CURLOPT_HEADER, false); 
        			if(sizeof($post_fields) > 0){
        				curl_setopt($ch, CURLOPT_POST, 1);
        				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        			}
        			if(strpos($data,'Lost Connection') === false){
            			$data = curl_exec($ch);
            			$data_array = array('data'=>json_encode($data));
            			
            			$fh = fopen($cache_url,'w+');
            			fwrite($fh,json_encode($data_array));
            			fclose($fh);
        			} else {
        			    $fh = fopen($cache_url,'r+');
            			$content = fread($fh,filesize($cache_url));
            			fclose($fh);
            			
            			$data = json_decode($content,true);
            			$data = json_decode($data['data']);
        			}
    			}
    			return $data;
    		}
    		
    		public function hints(){
    			return $this->api_call('http://quanticpost.com/info_pull/hints');
    		}
    		
    		public function get_feed_hints(){
    			return $this->api_call('http://quanticpost.com/info_pull/feed_hints');
    		}
    		
    		public function get_category_hints(){
    		    return $this->api_call('http://quanticpost.com/info_pull/category_hints_1');
    		}
    		
    		public function tw_upload_files($url,$id,$host,$alt="TW RSS Feed Importer",$delete=false){
    			$tmp = $this->api_call($url);
    			$desc = $alt;
    			$file_array = array();
    			
    			if(strpos($url,'jpg')){
    			    $matches = array($url);
    			} else {
    			    preg_match("/[^\?]+\.(jpg|jpe|jpeg|gif|png)(.*)?/i", $url, $matches);
    			}
    			
    			if(isset($matches) && sizeof($matches) > 0 && strlen($tmp) > 10){
    			    $file_array['name'] = basename($matches[0]);
    			    
    				$upload_url = wp_upload_dir();
    				
    				if(!is_dir($upload_url['basedir'].'/tw-rss-feeds/')){
                        mkdir($upload_url['basedir'].'/tw-rss-feeds/',0777);
    				}
    				
    				if(!is_dir($upload_url['basedir'].'/tw-rss-feeds/'.$host)){
                        mkdir($upload_url['basedir'].'/tw-rss-feeds/'.$host,0777);
    				}
    				
    				$tmp_file = false;
    				
    				$file_array['name'] = explode('?',$file_array['name']);
    				$file_array['name'] = str_replace(' ','_',urldecode($file_array['name'][0]));
    				
    				if(is_file($upload_url['basedir'].'/tw-rss-feeds/'.$host.'/'.str_replace(' ','_',urldecode($file_array['name'])))){
    				    if($delete == true){
    				        unlink($upload_url['basedir'].'/tw-rss-feeds/'.$host.'/'.str_replace(' ','_',urldecode($file_array['name'])));
    				        return 'deleted';
    				    }
    				    $file_size = filesize($upload_url['basedir'].'/tw-rss-feeds/'.$host.'/'.str_replace(' ','_',urldecode($file_array['name'])));
    					if($file_size != 0){
    					    $fh = fopen($upload_url['basedir'].'/tw-rss-feeds/'.$host.'/'.str_replace(' ','_',urldecode($file_array['name'])),'r+') or $tmp_file = true;
    					    $img_content = fread($fh,$file_size);
    					    fclose($fh);
    					}
    				} else {
    					$img_content = '';
    				}
    				
    				if(isset($img_content) && $tmp != $img_content && strlen($tmp) > 1800 && sizeof($matches) > 0){
    					$images = get_post_meta($id,'tw_images');
    					
    					if(is_array($images) && is_array($file_array['name']) && !strpos($images,$file_array['name'])){
    						$images = json_decode($images,true);
    						$images[] = $file_array['name'];
    					}
    					delete_post_meta($id,'tw_images');
    					add_post_meta($id,'tw_images',json_encode($images));
    					
    					$fh = fopen($upload_url['basedir'].'/tw-rss-feeds/'.$host.'/'.str_replace(' ','_',urldecode($file_array['name'])),'w+');
    					fwrite($fh,$tmp);
    					fclose($fh);
    					
    					$this->resize_image($upload_url['basedir'].'/tw-rss-feeds/'.$host.'/'.str_replace(' ','_',urldecode($file_array['name'])));
    				}
    				
    				return $upload_url['baseurl'].'/tw-rss-feeds/'.$host.'/'.$file_array['name'];
    			} else {
    				if(strlen($tmp) < 1800 && $url != ''){
    					return 'image too small';
    				} else {
    					return 'not uploaded';
    				}
    			}
    		}
    		
    		public function resize_image($image_file){
    			$image_size = getimagesize($image_file);
    			if(isset($image_size['width']) && ($image_size['width'] > 1000 || $image_size['height'] > 1000)){
    				if($image_size['width'] > $image_size['height']){
    					$percent = $image_size['height']/$image_size['width'];
    					$new_height = 1000;
    					$new_width = 1000 * $percent;
    				} else {
    					$percent = $image_size['width']/$image_size['height'];
    					$new_width = 1000;
    					$new_height = 1000 * $percent;
    				}
    				$new_file = preg_replace('/.(jpg|gif|bmp|jpeg|svg|tiff)/is','_twitter.$1',$image_file);
    				imagecopyresized($new_file,$image_file, 0, 0, 0, 0,$new_width,$new_height,$image_size['width'],$image_size['height']);
    			}
    		}
    		
    		public function query_feed_group($feed){
    		    $args = array(
    				'post_type'		=>	TW_FEED_TITLE,
    				'meta_query'	=>	array(
    						array(
    								'meta_key'  => 'tw_rss_feed_options',
    								'value'     => $feed,
    							)
    					),
    				'posts_per_page'    => -1,
    			);
    			
    			$my_query = new WP_Query( $args );
    			return $my_query;
    		}
    		
    		public function tw_view_content(){
    			include_once('tw_view_feed.php');
    		}
    		
    		public function display_array($array){
    			print '<pre style="display: none;">'.print_r($array,true).'</pre>';
    		}
    		
    		public function tw_delete_post($id,&$count_delete=0){
    		    global $wpdb;
    			
    			$option = get_option('tw_delete_only');
    			if(isset($option) && sizeof($option) > 0){
    				if(is_numeric($count_delete)){
    					++$count_delete;
    				}
    				
    				$m = get_post_meta($id);
    				@wp_delete_attachment( get_post_thumbnail_id($id),true);
    				
    				$result = $wpdb->get_results('SELECT post_content FROM '.$wpdb->posts.' WHERE id='.$id);
    				
    				$this->tw_delete_images_from_upload_dir($result[0]->post_content);
    				
    				foreach($m as $k=>$f){
    				    preg_match('/rss-feed-image-/i',$k,$r);
    				    if(sizeof($r) == 1){
    				        wp_delete_attachment($f[0],true);
    				    } else {
    				    	delete_post_meta($id,$k);
    				    }
    				}
    			} else {
    				echo $id.'<br/>';
    				wp_trash_post($id);
    			}
    			
    			$this->tw_delete_directories();
    		}
    		
    		public function delete_from_table(){
    			if((isset($_POST['action']) && $_POST['action'] == 'trash') || (isset($_POST['action2']) && $_POST['action2'] == 'trash') && isset($_POST['feed']) && sizeof($_POST['feed']) > 0){
    				$feeds = get_option('rss_feeds');
    				$feeds = $this->sanatize($feeds);
    				
    				$feeds = json_decode($feeds,true);
    				
    				if(sizeof($_POST['feed']) > 0){
    					foreach($_POST['feed'] as $q){
    						$q = str_replace('&','&amp;',$q);
    						$count_delete = 0;
    						$args = array(
    							'post_type'		=>	TW_FEED_TITLE,
    							'meta_query'	=>	array(
    									array(
    											'meta_key'  => 'tw_rss_feed_options',
    											'value'     => $q
    										)
    								),
    							'posts_per_page' => -1,
    						);
    						
    						$my_query = new WP_Query( $args );
    						$p = explode('|',$q);
    						
    						foreach($my_query->posts as $p){
    						    $this->tw_delete_post($p->ID,$count_delete);
    						}
    						
    						$status_str = '<div class="total-post-deleted">'.substr($q,0,strpos($q,'|')).' Total Posts to be: '.$count_delete.'</div>';
    						$p = explode('|',$q);
    						$f = array('feed_name','feed_url','feed_category','full_content','feed_content','feed_image_enabler');
    						
    						$test = array();
    						foreach($p as $k=>$v){
    							if($v == 'full-content'){
    								$test[$f[$k]] = 'true';
    							} else {
    								$test[$f[$k]] = $v;
    							}
    						}
    						
    						$reorder = array();
    						
    						foreach($feeds as $k=>$f){
    						    if($f['feed_name'] != $p[0] && $f['feed_url'] != $p[1]){
    						        $reorder[] = $f;
    						    }
    						}
    						
    						$feeds = $reorder;
    					}
    				}
    				
    				$feeds = json_encode($feeds);
    				$j = json_decode($feeds,true);
    				
    				if($j[0]['feed_name'] != ''){
    					update_option('rss_feeds',$feeds);
    				} else {
    				    update_option('rss_feeds','');
    				}
    			}
    		}
    		
    		public function tw_manual_update(){
    			$this->delete_from_table();
    			
    			$string_holder = $this->tw_create_rss_feed(true);
                
                $social = json_decode(get_option('tw_social_tokens'),true);
    			
    			if(isset($social['twitter']) && sizeof($social['twitter']) > 1 && strlen($social['twitter']['consumer_key']) > 5){
    				$twitter = $social['twitter'];
    				include_once(plugin_dir_path( __FILE__  ).'class/twitter_process.php');
    			}
    			
    			$this->process_cron('true');
    			
    			if(!class_exists('WP_List_Table')){
                    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
                }
    			
    			include_once("tw-manual-update.php");
    		}
    		
    		public function process_cron($manual = 'false'){
    		    $schedule = get_option('tw_schedule_event');
    			$curr_schedule = get_option('tw_curr_schedule');
    			
    			global $post;
    			
    			if($curr_schedule == ''){
    				update_option('tw_curr_schedule',strtotime('-15 minutes'));
    			}
    			
    			if(($curr_schedule != '' && strtotime(date('Y-m-d H:i:s')) >= $curr_schedule) || $manual == 'true'){
                ?>
    			<script>
    			    var ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (xhttp.readyState == 4 && xhttp.status == 200) {
                            console.log(xhttp.responseText);
                        }
                    };
                    xhttp.open("GET", ajax_url+'?action=tw_process_cron&manual=<?php echo $manual; ?>', true);
                    xhttp.send();
    			</script>
    			<?php
    			}
            }
    		
    		public function process_feeds_update(){
                $feeds = json_decode(get_option('rss_feeds'),true);
                foreach($feeds as $k=>$f){
                    if(!isset($feeds[$k]['active'])){
                        $feeds[$k]['active'] = "true";
                    }
                    if(!isset($feeds[$k]['best_feeds'])){
                        $feeds[$k]['best_feeds'] = "true";
                    }
                    if(!isset($feeds[$k]['image_enabler'])){
                        $feeds[$k]['image_enabler'] = "false";
                    }
                }
                update_option('rss_feeds',json_encode($feeds));
            }
    		
    		public function tw_process_cron(){
    		    $schedule = get_option('tw_schedule_event');
    		    $curr_schedule = get_option('tw_curr_schedule');
    		    
    		    $processed = true;
    		    if(isset($_GET['manual']) && $_GET['manual'] == 'true'){
    		        include_once('cron/cron.php');
    		    } else {
    		        if($curr_schedule != '' && $curr_schedule <= strtotime(date('Y-m-d H:i:s'))){
    		            include_once('cron/cron.php');
    		        }
    		    }
    		    
    		    if($processed){
        		    switch($schedule){
        				case '30-minutes':
        					$curr_schedule = strtotime(date('Y-m-d H:i:s').' +30 minutes');
        					break;
        				case '15-minutes':
        					$curr_schedule = strtotime(date('Y-m-d H:i:s').' +15 minutes');
        					break;
        				case 'hourly':
        					$curr_schedule = strtotime(date('Y-m-d H:i:s').' +1 hours');
        					break;
        				case 'twicedaily':
        					$curr_schedule = strtotime(date('Y-m-d H:i:s').' +12 hours');
        					break;
        				default:
        					$curr_schedule = strtotime(date('Y-m-d H:i:s').' +24 hours');
        					break;
        			}
        			update_option('tw_curr_schedule',$curr_schedule);
        			
        			$social = json_decode(get_option('tw_social_tokens'),true);
    				
        			if(isset($social['twitter']) && sizeof($social['twitter']) > 1 && strlen($social['twitter']['consumer_key']) > 5){
        				$twitter = $social['twitter'];
        				include_once(plugin_dir_path( __FILE__  ).'class/twitter_process.php');
        			}
        			
    		    } else {
    				if($curr_schedule == '' && $schedule == ''){
    					$curr_schedule = strtotime(date('Y-m-d h:i:s'),'+24 hours');
    					update_option('tw_schedule_event','daily');
    					update_option('tw_curr_schedule',$curr_schedule);
    				} else {
    					if($schedule == ''){
    						$schedule = 'daily';
    						update_option('tw_schedule_event','daily');
    					}
    					
    					if($curr_schedule == ''){
    						switch($schedule){
    							case '30-minutes':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +30 minutes');
    								break;
    							case '15-minutes':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +15 minutes');
    								break;
    							case 'hourly':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +1 hours');
    								break;
    							case 'twicedaily':
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +12 hours');
    								break;
    							default:
    								$curr_schedule = strtotime(date('Y-m-d H:i:s').' +24 hours');
    								break;
    						}
    						
    						update_option('tw_curr_schedule',$curr_schedule);
    					}
    				}
    			}
    			
    			$string_holder = $this->tw_create_rss_feed();
    		}
    		
    		public function convert_feeds_to_new_layout(){
    			$post = new WP_Query('post_type=feeds');
    			foreach($post->posts as $p){
    				$post_info = array('ID'=>$p->ID,'post_type'=>TW_FEED_TITLE);
    				wp_update_post($post_info);
    			}
    		}
    		
    		public function delete_feeds($query_string,$length){
    			global $wpdb;
    			
    			$query = $wpdb->get_results('SELECT p.ID FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id=p.ID WHERE m.meta_value="'.$query_string.'"');
    		}
           	
    		public function tw_create_rss_feed(){
    		    /*global $wpdb;
    		    $result = $wpdb->get_results('SELECT * FROM '.$wpdb->postmeta.' as m WHERE m.meta_key="tw_rss_feed_options" GROUP BY m.meta_value');

    		    $json = '';
    		    foreach($result as $f){
    		        $explode = explode('|',$f->{'meta_value'});
    		        $feed_info = '{"feed_name":"'.$explode[0].'","feed_url":"'.$explode[1].'","feed_category":"'.$explode[2].'"}';
    		        $json .= $feed_info.',';
    		    }
    		    
    		    $json = '['.substr($json,0,-1).']';
    		    
    		    update_option('rss_feeds',$json);*/
    			$feeds = get_option('rss_feeds');
    			
    			$feeds = $this->sanatize($feeds);
    			
    			$feeds = json_decode($feeds,true);
    			$feed_info = array();
    			
    			
    			foreach($feeds as $k=>$f){
    			    if(!isset($f['active']) || $f['active'] != 'false'){
        			    $l = $this->extract_feed($f);
        			    $feed_info[] = $l;
    			    }
    			}
    			
    			$feeds = json_encode($feeds);
    			$total_feeds = 0;
    			
    			$this->create_feed_table($feeds,true,$info);
    		}
    		
    		public function getAdvertPushInfo(){
    			$c = file_get_contents('http://quanticpost.com/checkadvert/'.str_replace('http://','',str_replace('_','',siteurl())));
    			if($c == 'successful'){
    				update_option('tw_advertisements',$c);
    			} else {
    				update_option('tw_advertisements','unsuccessful');
    			}
    		}
    		
    		public function advertisements(){ ?>
    			<div style="text-align: center;"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    			<ins class="adsbygoogle"
    				style="display:inline-block;width:728px;height:90px"
    				data-ad-client="ca-pub-3792069331807752"
    				data-ad-slot="4672427566"></ins>
    			<script>
    			(adsbygoogle = window.adsbygoogle || []).push({});
    			</script></div>
    			
    			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="text-align: center;">
    			    <input type="hidden" name="cmd" value="_s-xclick">
    			    <input type="hidden" name="hosted_button_id" value="TSXTKEMPLXGCN">
    			    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    			    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
    			</form>
    <?php	}
    		
    		public function basic_advertisements(){	?>
    			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="text-align: center">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="TSXTKEMPLXGCN">
                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                </form>
    <?php	}
    		
    		//sanatize arrays for previous version which creates options to use for json instead of custom string which was originally used for
    		//security purposes for certain servers whithout json_decode and json_encode enables. Serialized arrays will still be used, but json_encode
            //and json_decode will be used for unless disabled, then custom json_decode and json_encode will be used. There will be a warning if
            //json_decode and json_encode are disabled.
            //------------------------------------------------------------
    		public function sanatize($feed_info){
    			$json = json_decode($feed_info);
    			$feeds = $feed_info;
    			$check_string = preg_match_all('/(.*?)&/i',$feeds,$l);
    			if(sizeof($json) < 1){
    				$json = '';
    				preg_match_all("/{(.*?)}/i", $feeds,$l);
                    
                    $string_array = '';
                    foreach($l as $v){
                        if(!is_array($v)){
                            $json_string_to_array = json_decode('{'.$v.'}',true);
                            if(is_array($json_string_to_array) && sizeof($json_string_to_array) > 2){
                                $string_array .= '{'.$v.'},';
                            } else {
                            	$string_array .= $v[0].',';
                            }
                        }
                    }
                    
                    $json .= $string_array;
                    $json = '['.substr($json,0,-1).']';
                    $feeds = get_option('rss_feeds');
                }
                
                $json = json_decode($feeds,true);
                $array = array();
                if(is_array($json)){
                    foreach($json as $j){
                        if($j['feed_name'] != ''){
                            $array[] = $j; 
                        }
                    }
                }
                
                return json_encode($array);
    		}
    		
    		public function update_no_meta($args){
    			extract($args);
    			add_post_meta($id,$feed_name.'|'.$feed_url.'|'.$feed_category) || update_post_meta($id,$feed_name.'|'.$feed_url.'|'.$feed_category);
    		}
    		
    		public function extract_info($feed_info,$args=array()){
    			extract($args);
    			$feed_settings = new stdClass();
    			
    			$json = json_decode($feed_info,true);
    			
    			$feed_settings = $this->extract_feed($json,$args);
    			
    			if(isset($feed_settings->after_info) && sizeof($feed_settings->after_input['no_meta']->posts) > 0){
    				foreach($feed_settings->after_input['no_meta']->posts as $b){
    					update_no_meta(array('id'=>$b->ID,'feed_name'=>$json['feed_name'],'feed_url'=>$json['feed_url'],'feed_category'=>$json['feed_category']));
    				}
    			}
    			
    			$query_string = $json['feed_name'].'|'.$json['feed_url'].'|'.$json['feed_category'];
    			
    			if(isset($json['full-content'])){
    				$query_string .= '|'.$json['full-content'];
    				$feed_settings->{'full-content'} = $json['full-content'];
    			}
    			
    			$post = new WP_Query(
    				array(
    						'post_type'			=>	TW_FEED_TITLE,
    						'meta_query'		=>	array(
    							array(
    								'value'		=> 	$query_string,
    							)
    						)
    					));
    			
    			$feed_settings->total_feeds = $post->found_posts;
    			$feed_settings->title = $json['feed_name'];
    			
    			return $feed_settings;
    		}
    		
    		public function tw_special_cleanup(){
    			global $post;
    			
    			if($post->post_title){
    				$post->post_title = rawurldecode($post->post_title);
    				$decode = get_option('tw_rss_feed_decode');
    				$post->post_title = htmlspecialchars_decode($post->post_title);
    				if($decode == 'true'){
    					$post->post_title = utf8_decode($post->post_title);
    				}
    				
    				if($post->post_type == 'feed'){
    					if($decode == 'true'){
    					    $post->post_content = utf8_decode($post->post_content);
    					}
    					$post->post_content = str_replace(' ','  ',$post->post_content);
    				}
    				$post->post_content = htmlspecialchars_decode($post->post_content);
    			}
    		}
    		
    		public function get_full_content($feed_content,$link){
    			$content = file_get_contents($this->remove_cdata($link));
    			$content = str_replace(array("\r","\n"),'',$content);
    			$content = substr($content,strpos($content,'<body'));
    			$start = strpos($content,'>')+1;
    			$end = strpos($content,'</body>');
    			
    			$content = substr($content,$start,$end);
    			$replace = new Layout(null,$this);
    			$content = $replace->remove_tag_content($content,'script');
    			$content = $replace->remove_tag_content($content,'style');
    			$content = $replace->remove_tag_content($content,'nav');
    			$content = $replace->remove_tag_content($content,'form');
    			
    			$content = $replace->fix_tags($content,'class',$true);
    			$content = $replace->fix_tags($content,'id',$true);
    			$content = $replace->fix_tags($content,'style',$true);
    			$content = $replace->fix_tags($content,'src',$true);
    			$content = $replace->fix_tags($content,'href',$true);
    			
    			$content .= '<br/>This content was pulled from <a href="'.$link.'">'.$link.'</a>';
    			
                return $content;
    		}
    		
    		public function remove_cdata($string){
    			return str_replace('<![CDATA[','',str_replace(']]>','',$string));
    		}
    		
    		public function tw_delete(){
    			global $wpdb;
    			
    			$id = $_GET['ID'];
    			$result = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' as p JOIN '.$wpdb->postmeta.' as m ON p.ID = m.post_id WHERE p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$id.'"');
    			$ids = '';
    			foreach($result as $p){
    				$ids .= $p->ID.',';
    			}
    			
    			echo $this->tw_mass_delete_posts($ids).' Feed Posts Deleted';
    			
    			$this->tw_rss_feed = get_option('rss_feeds');
    			if(is_array($this->tw_rss_feed)){
    				$this->tw_rss_feed = json_decode($this->tw_rss_feed[0],true);
    			} else {
    				$this->tw_rss_feed = json_decode($this->tw_rss_feed,true);
    			}
    			
    			$new_feeds = array();
    			$id = explode('|',$id);
    			foreach($this->tw_rss_feed as $k=>$f){
    				if($f['feed_name'] != $id[0]){
    					$new_feeds[$k] = $f;
    				}
    			}
    			delete_option('rss_feeds');
    			update_option('rss_feeds',json_encode($new_feeds));
    		}
    		
    		public function extract_feed($arg=array(),$settings=array()){
    			set_time_limit(0);
    			
    			global $wpdb;
    			extract($settings);
    			extract($arg);
    			
    			$full_content = @$arg['full-content'];
    			$feed_content = (isset($arg['feed-content']))?$arg['feed-content']:'';
    			
    			$query_string = '';
    			foreach($arg as $a){
    			    $query_string .= $a.'|';
    			}
    			
    			$query_string = substr($query_string,0,-1);
    			
    			if(!isset($feed_image_enabler)){
    				$feed_image_enabler = '';
    			}
    			
    			$update = new stdClass();
    			
    			if(strlen($feed_name) > 0){
    				$ch = curl_init();
    				
    				$feed_url = (strpos($feed_url,'http') !== false || strpos($feed_url,'://') !== false)?$feed_url:'http://'.$feed_url;
    	            $output = @file_get_contents($feed_url);
    	            
                    if(strlen($output) > 100){
        				curl_setopt($ch, CURLOPT_URL, $feed_url);
        				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/1.0");
        				curl_setopt($ch, CURLOPT_HEADER, 0);
        				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        				$output = curl_exec($ch);
        				curl_close($ch);
                    }
    				
    				if(strlen($feed_category) < 1){
    					$feed_category = 'uncategorized';
    				}
    				
    				$count = 0;
    				
    				$images = array();
    				
    				$xml = @simplexml_load_string($output);
    				
    				$count = 0;
    				
    				if(strpos(strtolower($output),'moved permanently') > -1 && strpos(strtolower($output),'moved permanently') < 100){
    				   $output = '';
    				}
    				
    				if($output == ''){
    					$get_contents = @file_get_contents($feed_url);
    				} else {
    					$get_contents = $output;
    				}
    				
    				$get_contents =  str_replace(array("\n","\r"),' ',$get_contents);
    				
    				preg_match_all('/<entry.*?>(.*?)<\/entry>/i',$get_contents,$feed_output);
    				if(sizeof($feed_output[0]) < 1){
    					preg_match_all('/<item.*?>(.*?)<\/item>/i',$get_contents,$feed_output);
    				}
    				
    				$update->updated = 'Feeds Added: 0<br/>';
    				$update->update_count = 0;
    				$update->ID = $feed_name;
    				$update->error = "<div style='background: #ff0000; color: #fff; padding: 10px; text-align: center;'>Yes no rss feed</div>";
    				
    				if(sizeof($feed_output[0]) > 5){
    					$feed_output[0] = array_slice($feed_output[0],5);
    					
    				}
    				
    				foreach($feed_output[0] as $k=>$f){
    					$display = '';
    					$description = '';
    					$f_holder = '';
    					$link = '';
    					
    					$f = preg_replace('/media:(\w+)?/is','media medium="$1"',$f);
    					
    					preg_match_all("/(<title.*?>(.*?)<\/title>|<link.*?((src|href)=[\"'](.*?)[\"'])?>(.*?)<\/link>|<description.*?>(.*?)<\/description>|<content.*?>(.*?)<\/content>|<summary.*?>(.*?)<\/summary>)|<media.*?(medium=[\"'](.*?)[\"'])?.*?(url|src|href)=[\"'](.*?)[\"'].*?\>|<thumbnail>(<url>(.*?)<\/url>)?(.*?)<\/thumbnail>|<id>(.*?)<\/id>|<guid>(.*?)<\/guid>|<enclosure.*?(url|src|href)=['\"](.*?)['\"]\/>/is",$f, $output_array);
    					$output_array_holder = array();
    					
    					foreach($output_array as $k=>$f){
    						$output_array_holder[$k] = array_filter($output_array[$k]);
    					}
    					unset($output_array);
    					
    					$layout_info = array(
    							'title'			=> $this->remove_cdata($output_array_holder[2]),
    							'title2'		=> $this->remove_cdata($output_array_holder[5]),
    							'link'			=> $this->remove_cdata($output_array_holder[3]),
    							'link2'			=> $this->remove_cdata($output_array_holder[6]),
    							'link3'			=> $this->remove_cdata($output_array_holder[18]),
    							'link4'			=> $this->remove_cdata($output_array_holder[17]),
    							'description'	=> $this->remove_cdata($output_array_holder[7]),
    							'description2'	=> $this->remove_cdata($output_array_holder[8]),
    							'description3'	=> $this->remove_cdata($output_array_holder[9]),
    							'medium_type'	=> $this->remove_cdata($output_array_holder[10]),
    							'medium'		=> $this->remove_cdata($output_array_holder[11]),
    							'image'			=> $this->remove_cdata($output_array_holder[13]),
    							'image2'		=> $this->remove_cdata($output_array_holder[15]),
    							'image3'		=> $this->remove_cdata($output_array_holder[16]),
    							'image4'		=> $this->remove_cdata($output_array_holder[20]),
    						);
    					
    					unset($output_array_holder);
    					
    					foreach($layout_info['title'] as $f){
    						if(trim($f) != ''){
    							$title = trim($f);
    						}
    					}
    					if($title == ''){
    						foreach($layout_info['title2'] as $f){
    							$title = trim($f);
    						}
    					}
    					
    					foreach($layout_info['link'] as $f){
    						if(trim($f) != ''){
    							$link = $f;
    						}
    					}
    					if(strlen(trim($link)) < 5){
    						foreach($layout_info['link2'] as $f){
    							if(strpos('http',$f) == 0){
    								if(trim($f) != ''){
    									$link = $f;
    								}
    							}
    						}
    	        		}
    					foreach($layout_info['link3'] as $f){
    						if(strpos('http',$f) == 0){
    							if(trim($f) != ''){
    								$link = $f;
    							}
    						}
    					}
    					foreach($layout_info['link4'] as $f){
    						if(strpos('http',$f) == 0){
    							if(trim($f) != ''){
    								$link = $f;
    							}
    						}
    					}
    					
    					foreach($layout_info['description'] as $f){
    						if(strip_tags(trim($f)) != '' || strpos($f,'img')){
    							$description = $f;
    						}
    					}
    					
    					if($description == ''){
    						foreach($layout_info['description2'] as $f){
    							if(strip_tags(trim($f)) != '' || strpos($f,'img')){
    								$description = $f;
    							}
    						}
    					}
    					
    					if($description == ''){
    						foreach($layout_info['description3'] as $f){
    							if(strip_tags(trim($f)) != '' || strpos($f,'img')){
    								$description = $f;
    							}
    						}
    					}
    					
    					if(isset($full_content) && $full_content != ''){
    						$description = $this->get_full_content($feed_content,$link);
    					}
    					
    					$title = $this->remove_cdata($title);
    					$link = $this->remove_cdata($link);
    					
    					if(mb_detect_encoding($title) == 'UTF-8'){
    						$title_holder = html_entity_decode(htmlentities($title));
    						if($title_holder == ''){
    							$title = utf8_encode($title);
    						} else {
    							$title = $title_holder;
    						}
    					} else {
    						$title = htmlentities($title);
    					}
    					
    					$title = str_replace('&apos;','&#39;',html_entity_decode($title));
    					$images = '';
    					
    					if(isset($layout_info['image'])){
    						foreach($layout_info['image'] as $k=>$f){
    							if(isset($layout_info['media_type'][$k])){
    								switch($layout_info['medium_type'][$k]){
    									case 'image':
    										$images .= '<img src="'.$f.'"/><br/>';
    										break;
    									default:
    										if(strpos($f,'.jpg') !== false || strpos($f,'.png') !== false || strpos($f,'.bmp') !== false){
    											$images .= '<img src="'.$f.'" /><br/>';
    										} else {
    											$images .= '<media medium="'.$layout_info['image'][$k].'" src="'.$f.'"/><br/>';
    										}
    										break;
    								}
    							} else {
    								$images .= '<img src="'.$f.'"/></br/>';
    							}
    						}
    					}
    					
    					foreach($layout_info['image2'] as $f){
    						$images .= '<img src="'.$f.'"/>';
    					}
    					foreach($layout_info['image3'] as $f){
    						$images .= '<img src="'.$f.'"/>';
    					}
    					foreach($layout_info['image4'] as $f){
    						$images .= '<img src="'.$f.'"/>';
    					}
    					
    					unset($layout_info);
    					
    					$description .= $images;
    					$description = html_entity_decode($description);
    					
    					$description = str_replace('src="//','src="http://',$description);
    					
    					if($title != ''){
    						if(!isset($full_content)){
    							$id = $this->save_posts(array(
    								'title'=>$title,
    								'description'=>$description,
    								'feed_name'=>$feed_name,
    								'feed_url'=>$link,
    								'feed_provider'=>$feed_url,
    								'feed_category'=>$feed_category,
    								'feed_image_enabler'=>$feed_image_enabler));
    						} else {
    							$id = $this->save_posts(array(
    								'title'=>$title,
    								'description'=>$description,
    								'feed_name'=>$feed_name,
    								'feed_url'=>$link,
    								'feed_provider'=>$feed_url,
    								'feed_category'=>$feed_category,
    								'full_content'=>true,
    								'feed_content'=>$feed_content,
    								'feed_image_enabler'=>$feed_image_enabler));
    						}
    					}
    					
    					if(is_array($id)){
    					    $limit = (isset($total_feeds) && is_numeric($total_feeds))?$total_feeds:10;
    					    if($count > $limit) break;
    						++$count;
    					}
    					
    					$update->updated = 'Feeds Added: '.$count.'<br/>';
    					$update->update_count = $count;
    					$update->ID = $feed_name;
    					$update->error = "No";
    					unset($feed_output[0][$k]);
    				}
    			}
    			
    			return $update;
    		}
    		
    		public function tw_setup_schedule() {
    			if(get_option('tw_schedule_event') == false){
    				update_option('tw_schedule_event','hourly');
    			}
    			if ( ! wp_next_scheduled( 'tw_hourly_event' ) ) {
    				wp_schedule_event( time(), get_option('tw_schedule_event'), 'tw_hourly_event');
    			}
    		}
    		
    		public function check_social_posts(){
    		    if(get_option('tw_social_tokens') != ''){
                    $token = json_decode(get_option('tw_social_tokens'),true);
                    $api = json_decode(get_option('tw_auto_social_api'),true);
                }
    		}
    		
    		public function tw_sharing( $content ) {
    			global $post;
    			
    			$feed_info = '';
    			if($post->post_type == TW_FEED_TITLE){
    				$header = '<div class="main_share_holder">';
    				
    				$feed_info_holder = get_post_meta($post->ID,'tw_rss_feed_options');
    				if(isset($feed_info_holder[0])){
        				$feed = explode('|',$feed_info_holder[0]);
        				$feed = parse_url($feed[1]);
        				if(isset($feed['host'])){
        				    $feed_info = $feed['host'];
        				}
        				
        				foreach(explode(',',get_option('tw_social')) as $p){
        					if($p != ''){
        						$header .= file_get_contents(plugin_dir_path( __FILE__  ).'layout/share/'.$p.'.php');
        						$header = str_replace('[url]',$post->guid,$header);
        					}
        				}
    				}
    				
    				$content = $header.'</div>'.$content;
    				$content = do_shortcode($content);
    				$content = $content.$header;
    			} else {
    				$content = do_shortcode($content);
    			}
    			if($post->post_type == TW_FEED_TITLE){
    			    if(isset($feed_info_holder) && sizeof($feed_info_holder) > 0){
    				    $content = '<div style="color: #000; border-bottom: 1px solid #000; padding: 10px;">Received from: <a href="'.$feed_info.'" target="_blank">'.$feed_info.'</a></div>'.$content;
    			    }
    			}
    			return $content;
            }
    		
    		public function create_content_string($description,$link,$title){
    			$title = urldecode($title);
    			return str_replace('"', "'", $description.'<br/><br/>Read More: <a href="'.$link.'" target="_blank">'.$title.'</a><br/>');
    		}
    		
    		public function clean_database(){
    		    global $wpdb;
    		    $result = $wpdb->get_results('SELECT p.post_title,p1.ID,p.ID FROM '.$wpdb->posts.' as p JOIN '.$wpdb->posts.' as p1 ON p1.post_title = p.post_title WHERE p.ID != p1.ID ORDER BY p.post_title LIMIT 0,10') or exit(mysql_error());
    		    
    		    $post_title = '';
    		    foreach($result as $k=>$f){
    		    	if($post_title == $f->post_title){
    		    		echo $post_title.' - delete <br/>';
    					wp_delete_post($f->ID);
    		    	} else {
    		    		$post_title = $f->post_title;
    		    	}
    		    }
    		}
    		
            public function save_posts($arg){
            	global $wpdb,$post_type;
                extract($arg);
                
                $title = trim($title);
                $check_title = $title;
                
                $description = html_entity_decode($description);
                
                $slug = $title;
                $title = htmlspecialchars_decode(rawurlencode(str_replace('+','%2B',$title)));
                
                $description = $this->create_content_string($description,$feed_url,$title,$feed_category);
                
                $post = array(
    					'post_title'=>$title,
    					'post_content'=>$description,
    					'post_category'=>array($feed_category),
    					'post_author'=>1,
    					'post_name'=>$slug,
    					'post_status'=>'publish',
    					'post_type'=>TW_FEED_TITLE
    				);
    			
    			if(get_option('tw_auto_create_pages') == 'true'){
    				$category_name = get_cat_name($feed_category);
    				$query = $wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE '.$wpdb->posts.'.post_type="page" AND '.$wpdb->posts.'.post_title="'.$category_name.'" AND '.$wpdb->posts.'.post_status != "trash"');
    				
    				if(sizeof($query) < 1){
    					$page = array(
    						'post_title'=>ucwords($category_name),
    						'post_content'=>'[feed_searches category_name="'.$category_name.'"][/feed_searches]',
    						'post_category'=>array($feed_category),
    						'post_author'=>1,
    						'post_status'=>'publish',
    						'post_type'=>'page'
    					);
    					wp_insert_post($page);
    				}
    			}
    			
    			$post_type = TW_FEED_TITLE;
    			$query = $wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE '.$wpdb->posts.'.post_type="'.TW_FEED_TITLE.'" AND '.$wpdb->posts.'.post_title = "'.trim($title).'" AND '.$wpdb->posts.'.post_status != "trash" LIMIT 0,1000');
    			
    			if(sizeof($query) < 1){
    				$id = wp_insert_post($post);
    				
    				if(isset($feed_image_enabler) && $feed_image_enabler != ''){
    					$this->get_post_images($id,$description);
    				}
    				
    				add_post_meta( $id, 'tw_rss_feed_options', $feed_name.'|'.$feed_provider.'|'.$feed_category, true ) || update_post_meta( $id, 'tw_rss_feed_options', $feed_name.'|'.$feed_provider.'|'.$feed_category);
    				add_post_meta( $id, 'tw_social_option','true');
    				
    				$p = get_post_meta($id,'tw_rss_feed_options');
    				
    				if(isset($deleted)){
    					return array('id'=>$id,'count'=>1,'deleted'=>$deleted);
    				} else {
    					return array('id'=>$id,'count'=>1);
    				}
    			} else {
    				/*$post = $query[0];
    				$deleted = 0;
    				if(sizeof($query) > 1){
    					$deleted = remove_duplicated_feeds($query);
    				}
    				
    				if($post->post_content != $description && strlen($description) > strlen($post->post_content)){
    					wp_update_post(array('ID'=>$post->ID,'post_content'=>$description,'post_title'=>$post->post_title));
    					$this->get_post_images($post->ID,$description);
    					$updated = array('id'=>$post->ID,'count'=>1,'updated'=>1,'deleted'=>$deleted);
    				}*/
    			}
            }
            
            public function remove_duplicated_feeds($query,$all_flag=false){
    			$deleted = 0;
    			$start = 1;
    			
    			if($all_flag){
    				$start = 0;
    			}
    			
    			for($i = $start; $i < sizeof($query); ++$i){
    				if(isset($query[$i])){
    					wp_delete_post($query[$i]->ID);
    					++$deleted;
    				}
    			}
    			return $deleted;
            }
            
            public function flush_feeds(){
                $feeds = new WP_Query(array('post_type'=>TW_FEED_TITLE,'posts_per_page'=>-1));
                $feed_cache = array();
                
    			foreach($feeds->posts as $f){
    			    $s = new stdClass();
    			    $s->ID = $f->ID;
    			    $feed_cache[$f->post_title][] = $s;
    			}
    			
    			$args = array(
    				'post_type'		=>	TW_FEED_TITLE,
    				'orderby'     	=> 'meta_value',
    			    'order'       	=> 'ASC',
    				'posts_per_page' => -1,
    			);
    			$my_query = new WP_Query( $args );
    			
    			$no_meta_info = array();
                
                foreach($my_query->posts as $p){
                    $to = get_post_meta($p->ID);
                    $data = array();
                    if(!isset($to['tw_rss_feed_options'])){
                        $data['id'] = wp_delete_post($p->ID);
                        $data['post_info'] = $p;
                        $no_meta_info[] = $data;
                    }
                }
                
                $count = 0;
                foreach($feed_cache as $k=>$f){
                    if(sizeof($f) > 1){
                        $count += remove_duplicated_feeds($f);
                    }
                }
                $info = array('count'=>$count,'no_meta'=>$no_meta_info);
                
                return $info;
            }
            
            public function feed_searches($args = array()){
                global $post, $exclude;
                
                preg_match("/page\/(\d+)/is", $_SERVER['REQUEST_URI'], $page);
                
                if(is_array($args) && sizeof($args) > 0){
                    extract($args);
                }
                $string = '';
                
                if(isset($args['total_feeds']))	{
                    $args['posts_per_page'] = $args['total_feeds'];
                } elseif(isset($args['posts_per_page'])) {
                    $args['posts_per_page'] = $args['posts_per_page'];
                } else {
                	$args['posts_per_page'] = 10;
                }
                
                $rss_feed_options = json_decode(get_option('rss_feeds'),true);
                $exclude_feed = '';
                foreach($rss_feed_options as $f){
                    if(isset($f['active']) && $f['active'] == 'false'){
                        $exclude_feed .= $f['feed_name'].'|'.$f['feed_url'].'|'.$f['feed_category'].',';
                    }
                }
    			
    			$count = 0;
    			$options = '';
    			$disabled_categories = array();
    			
    			$query_args = array();
    			foreach($args as $k=>$a){
    				if($k != 'images_only' && $k != 'source' && $k != 'title_only' && $k != 'tw_image_size' && 
    				$k != 'tw_show_image' && $k != 'total_feeds' && $k != 'advertise' && $k != 'title'){
    					$query_args[$k]	= $a;
    				}
    			}
    			
    			if(isset($args['images_only']) && ($args['images_only'] == 'on' || $args['images_only'] == 'true')){
    				$query = array(
    						'post_type'		=>	TW_FEED_TITLE,
    						'post_status'	=> 'publish',
    						'meta_query'	=> array(
    								array(
    										'key'		=> 'tw_images',
    										'compare'	=> 'NOT LIKE',
    										'value'		=> 'deleted'
    									),
    								array(
    										'key'		=> 'tw_images',
    										'compare'	=> 'NOT LIKE',
    										'value'		=> '[]'
    									)
    							),
    						'orderby'		=> 'ID',
    						'order'			=> 'DESC'
    					);
    			} else {
    				$query = array(
    						'post_type'			=>	TW_FEED_TITLE,
    						'post_status'	=> 'publish',
    						'orderby'		=> 'ID',
    						'order'			=> 'DESC'
    					);
    			}
    			
    			$exclude_feed = explode(',',$exclude_feed);
    			foreach($exclude_feed as $a){
    			    if($a != ''){
        			    $query['meta_query'][] = array(
        			                                    'key'		=> 'tw_rss_feed_options',
            										    'compare'	=> 'NOT LIKE',
        										        'value'		=> $a
        										    );
    			    }
    			}
    			
    			$query['post__not_in'] = array();
    			$args['tw_unique'] = (isset($args['tw_unique']))?$args['tw_unique']:'';
    			
    			if($args['tw_unique'] == 'on'){
    			    if(!isset($exclude)) $exclude = array();
    				$query['post__not_in'] = array_merge($exclude,$query['post__not_in']);
    			}
    			
    			if(sizeof($page) > 0){
    				$query_args['paged'] = $page[1];
    			} else {
    				$page = 1;
    			}
    			
    			$query = array_merge($query,$query_args);
    			$query = new WP_Query($query);
    			
    			$total_posts = $query->found_posts;
    			$query = $query->posts;
                
    			$layout = new Layout(array('post'=>TW_FEED_TITLE),$this);
                $source = (isset($args['source']))?$args['source']:'';
                
                foreach($query as $p){
                    $exclude[] = $p->ID;
                }
                
                $tw_image_size = (isset($args['tw_image_size']))?$args['tw_image_size']:'';
                if($tw_image_size == 'elongated'){
                    $layout->get_layout(plugin_dir_path( __FILE__  ).'/layout/layout.php');
                } else {
                    $layout->get_layout(plugin_dir_path( __FILE__ ).'/layout/layout_square.php');
                }
                
                include_once('tw_tp-admin.php');
                if(isset($args['images_only']) && $args['images_only'] != ''){
                	$string .= $layout->populate_layout($query,$options,get_option('tw_advertising'),$tw_image_size,$source,true);
                } else {
                    $string .= $layout->populate_layout($query,$options,get_option('tw_advertising'),$tw_image_size,$source);
                }
                
    			$string = '<div id="tw-container">'.rawurldecode($string).'<div style="clear: both;"></div><div style="clear: both;"></div></div>';
                $string .= '<script src="http://quanticpost.com/images/image_resize.js"></script>';
                $string .= '<script>document.onload = function(){ var container = document.getElementById("tw-container"); var iso = new Isotope( container, { itemSelector: ".tw-content-holder", layoutMode: "masonry" }); }</script>';
    			
    			if(isset($args['advertise']) && $args['advertise'] == 'true'){
    				$string .= '<script src="http://quanticpost.com/js/advertisement.js"></script>';
    				$string = file_get_contents(plugin_dir_path( __FILE__ ).'/layout/advertisements.php').$string;
    			}
    			
    			if(get_option('tw_pagination') == 'true' && $total_posts > 0 && $args['posts_per_page'] > 0 && $source != 'widget'){
    				if($post->post_type == TW_FEED_TITLE){
    					$string .= $this->tw_pagination(ceil($total_posts/$args['posts_per_page']));
    					$string .= '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url( __FILE__ ) . 'css/feeds/pagination.css"></link>';
    				} else {
    					$string .= $this->tw_pagination(ceil($total_posts/$args['posts_per_page']),4,true);
    					$string .= '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url( __FILE__ ) . 'css/feeds/pagination.css"></link>';
    				}
    			}
    			
                return $string;
            }
            
            public function tw_pagination($pages = '', $range = 4,$outside_network){
                $string = '';
                $showitems = ($range * 2)+1;  
                
                if($outside_network == true){
                	$actual_page_link = '';
                }
                
                global $wp_query;
                $paged = $wp_query->query_vars;
                $paged = $paged['paged'];
                
    			if(empty($paged)){
    				if(!isset($wp_query->query_vars['page'])){
    					$paged = 1;
    				} else {
    					$paged = $wp_query->query_vars['page'];
    				}
    				if($paged == 0) $paged = 1;
    			}
                
                if($pages == ''){
                    global $wp_query;
                    $pages = $wp_query->max_num_pages;
                    if(!$pages){
                        $pages = 1;
                    }
                }
                
                if(1 != $pages){
                    $string .= "<div class=\"pagination\"><span>Page ".$paged." of ".$pages."</span>";
                    if($paged > 2 && $paged > $range+1 && $showitems < $pages) $string .= "<a href='".get_pagenum_link(1)."'>&laquo; First</a>";
                    if($paged > 1 && $showitems < $pages) $string .= "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo; Previous</a>";
                    
                    for ($i=1; $i <= $pages; $i++){
                        if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
                            $string .= ($paged == $i)? "<span class=\"current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
                        }
                    }
                    
                    if ($paged < $pages && $showitems < $pages) $string .= "<a href=\"".get_pagenum_link($paged + 1)."\">Next &rsaquo;</a>";  
                    if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) $string .= "<a href='".get_pagenum_link($pages)."'>Last &raquo;</a>";
                    $string .= "</div>\n";
                }
                return $string;
    		}
            
            public function setup_header(){
            	$string = '';
                if(get_option('layout_animation') == 'true'){
                    $string .= '<script src="http://quanticpost.com/js/roll_over_animation.js"></script>';
                }
                $string = $layout->get_css();
               	$string .= $layout->get_js();
            }
            
            public function get_length_of_storage($f){
                $length = '';
                if(is_array($f) && sizeof($f) > 4){
            	    $length = (!isset($f['length-of-storage-days']) || $f['length-of-storage-days'] == '0')?' ':' -'.($f['length-of-storage-days']).' days ';
    			    $length .= (!isset($f['length-of-storage-months']) || $f['length-of-storage-months'] == '0')?' ':' -'.($f['length-of-storage-months']).' months ';
    			    $length .= (!isset($f['length-of-storage-years']) || $f['length-of-storage-years'] == '0')?' ':' -'.($f['length-of-storage-years']).' years ';
                }
    			return $length;
            }
            
            //Functions used for multi-deletion of files
            //Starts with the call from create_feed_table or the cron job function
            //and finishes with tw_delete_images_from_upload_dir which deletes all images from the upload directory
            //created using the upload_files function
            public function tw_delete_images_from_upload_dir($content){
                preg_match_all('/img.*?(src|url|href)=([\'"](.*?)[\'"]|(.*?)).*?/is',$content,$images);
                foreach($images[3] as $f){
                    $url = parse_url($f);
                    $upload_url = wp_upload_dir();
                    $file_array['name'] = basename($f);
                    $file = $upload_url['basedir'].'/tw-rss-feeds/'.$url['host'].'/'.str_replace(' ','_',urldecode($file_array['name']));
                    if(is_file($file)){
                        unlink($file);
                    }
                }
            }
            
            public function tw_delete_directories(){
                $upload_url = wp_upload_dir();
    		    $scan_dir = @scandir($upload_url['basedir'].'/tw-rss-feeds/');
    		    
    		    if(is_array($scan_dir)){
        		    foreach($scan_dir as $f){
        		        if(is_dir($upload_url['basedir'].'/tw-rss-feeds/'.$f) && $f != '.' && $f != '..'){
        		            $is_empty = scandir($upload_url['basedir'].'/tw-rss-feeds/'.$f);
        		            if(sizeof($is_empty) == 2){
        		                rmdir($upload_url['basedir'].'/tw-rss-feeds/'.$f);
        		            }
        		        }
        		    }
    		    }
            }
            
            public function tw_mass_delete_posts($ids){
                global $wpdb;
                
                $size_of_delete = 0;
                if($ids != ''){
                    $ids = explode(',',$ids);
                    if($ids[0] != ''){
                        $size_of_delete = sizeof($ids);
                    }
                } else {
                    $ids = array();
                    $size_of_delete = 0;
                }
                
                if(sizeof($ids) > 0){
                    $ids = array_chunk($ids, 100);
                    foreach($ids as $f){
                        $f = implode(',',$f);
                        $posts = $wpdb->get_results('SELECT ID,post_content FROM '.$wpdb->posts.' WHERE ID IN('.$f.')');
                        foreach($posts as $p){
                            $this->tw_delete_images_from_upload_dir($p->post_content);
                        }
                        if(substr($f,-1) == ','){ 
                            $f = substr($f,0,-1);
                        }
                        $wpdb->get_results('UPDATE '.$wpdb->posts.' SET post_status="trash" WHERE ID IN('.$f.')');
                    }
                } else {
                    return '<div style="background: #A9BCCA; padding: 5px; color: #fff;">formatted incorrectly</div>';
                }
                
                return $size_of_delete;
            }
            
            public function tw_process_delete_button_press(){
                $json_info = $_GET['ID'];
                $total_feeds = json_decode(rawurldecode(str_replace("\\",'',$json_info)));
                
                $query_string = '';
                if(isset($total_feeds->{'full_content'})){
					$query_string = $total_feeds->{'feed_name'}.'|'.$total_feeds->{'feed_url'}.'|'.$total_feeds->{'feed_category'}.'|'.$total_feeds->{'full-content'};
				} else {
					$query_string = $total_feeds->{'feed_name'}.'|'.$total_feeds->{'feed_url'}.'|'.$total_feeds->{'feed_category'};
				}
                
                $f_array = get_object_vars($total_feeds);
                $length = '';
                $length = $this->get_length_of_storage($f_array);
                unset($f_array);
                
                $deleted = $this->tw_process_delete($total_feeds,$length,$query_string,false);
            }
            
            public function tw_process_delete($f,$length,$query_string,$count_only=true){
                global $wpdb;
                
    			$post_range = array();
    			$string = '';
    			$total_deleted = 0;
    			
    		    $limit = '';
    		    $result = $wpdb->get_results('SELECT COUNT(ID) FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id = p.ID WHERE p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'" AND p.post_status = "publish"');
    		    
    		    $offset = 0;
    		    
    		    if(isset($f->total_feeds) && is_numeric($f->total_feeds) && $f->total_feeds > 0){
    		        $offset = $result[0]->{'COUNT(ID)'}-$f->total_feeds;
    		        
    		        if($offset > 0){
    		            $limit = ' LIMIT 0,'.$offset.' ';
    		        } else {
    		            $limit = ' LIMIT 0,0 ';
    		        }
    		    } else {
    		        if(is_numeric($f->total_feeds) && $f->total_feeds == 0){
    		            $limit = ' LIMIT 0,0 ';
    		        }
    		    }
    		    
    		    $query = '';
    		    
    		    if(strlen($length) > 5){
    		        $query = 'p.post_date < "'.date('Y-m-d 00:00:00',strtotime(date('Y-m-d').' '.$length)).'" AND ';
    		    }
    		    
    		    $result_l = $wpdb->get_results('SELECT COUNT(ID) FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' as m ON m.post_id=p.ID WHERE '.$query.' p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'" AND p.post_status = "publish"');
    		    
    		    if(is_object($result_l[0]) && $result_l[0]->{'COUNT(ID)'} != 0){
    		        if(isset($f->total_feeds) && is_numeric($f->total_feeds)){
    			        if($result[0]->{'COUNT(ID)'}-$result_l[0]->{'COUNT(ID)'} < $f->total_feeds){
    			            $limit = $limit;
    			        } else {
    			            if(isset($result_1[0]) && is_object($result_1[0]) && $result_1[0]->{'COUNT(ID)'} < $f->total_feeds){
    			                $limit = ' LIMIT 0,'.($f->total_feeds-$result_l[0]->{'COUNT(ID)'});
    			            }
    			        }
    		        }
    		    } else {
    		        $query = '';
    		    }
    		    
    		    $ids = '';
    		    
    		    if($query != '' || $limit != ''){
        		    if($limit != 'LIMIT 0,0' && $f->total_feeds != 'all'){
        		        if($query != ''){
        		            $query = '(p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'" AND p.post_status = "publish") OR ('.$query.' p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'" AND p.post_status = "publish")';
        		        } else {
        		            $query = '(p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'" AND p.post_status = "publish")';
        		        }
        		        $post_info = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id = p.ID WHERE '.$query.' ORDER BY p.post_date ASC '.$limit );
        		    } else {
        		        if($f->total_feeds == 'all'){
        		            $query = '('.$query.' p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'" AND p.post_status = "publish")';
        		            $post_info = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id = p.ID WHERE '.$query.' ORDER BY p.post_date ASC '.$limit );
        		        } else {
        		            $post_info = array();
        		        }
        		    }
        		    
        			foreach($post_info as $p){
        			    $ids .= $p->ID.',';
        			}
    		    }
    			
    			$ids = substr($ids,0,-1);
    			
    			$ids_exclude = '';
    			$total_excluded = 0;
    			
    			if(strlen($ids) > 0){
    			    if(isset($f->best_feeds) && $f->best_feeds == 'true'){
    					$best_feeds = json_decode($this->tw_best_feeds($f->{'feed_url'}),true);
    					
    					$guid = '';
        	           	foreach($best_feeds as $j){
        	           		$guid .= '"'.$j['current_page'].'",';
        	           	}
        	           	$query = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' WHERE guid IN ('.substr($guid,0,-1).')');

        	           	foreach($query as $v){
        	           	    $ids_exclude .= $v->ID.',';
        	           	}
    					
    					$total_excluded = substr_count($ids_exclude,',');
    					
    					if(strlen($ids_exclude) > 0){
    					    $ids_exclude = substr($ids_exclude,0,-1);
    					    $ids_exclude = explode(',',$ids_exclude);
    					    $total_exclude = sizeof($ids_exclude);
    					    
    					    foreach($ids_exclude as $g){
    					        if($g != ''){
                                    //check variables that exist
                                    $ids = str_replace(','.$g.',',',',$ids);
                                    
                                    //check first
                                    if(strpos($ids,$g.',') !== false){
                                        $ids = substr($ids,strlen($g)+1);
                                    }
                                    
                                    //check last
                                    if(strrpos($ids,','.$g)){
                                        if(strlen($ids)-strrpos($ids,','.$g) == strlen($ids)){
                                            if(strrpos($ids,',') >= strlen($ids)-strlen($g)-1){
                                                $ids = substr($ids,0,-strlen($g)+1);
                                            }
                                        }
                                    }
                                    $ids = str_replace(','.$g.',',',',$ids);
    					        }
    					    }
    					}
    				} else {
    				    $total_excluded = 0;
    				}
    				
    				if($count_only == false){
    				    $total_deleted = $this->tw_mass_delete_posts($ids);
    				} else {
    				    $total_deleted = substr_count($ids,',')+1;
    				}
    			}
    			
    			if($_GET['page'] != 'tw_process_delete_button_press'){
    			    return array($total_deleted,$ids,$total_excluded);
                } else {
                    echo '<h1>'.$f->feed_name.'</h1>';
                    echo '<div>Total Feeds Deleted - '.$total_deleted.'</div>';
                    echo '<div>Total Feeds Excluded - '.$total_excluded.'</div>';
                    echo $f->feed_url;
                    $this->best_information(json_decode($this->tw_best_feeds($f->feed_url),true));
                }
            }
            
            public function best_information($info){ ?>
                 <style>
        			#best_information .row{
        				border-bottom: 1px solid #ababab;
        				padding: 10px;
        				overflow: hidden;
        			}
        			#best_information .row div{
        			    float: Left;
        			    width: 100px;
        			    padding-right: 10px;
        			    overflow: hidden;
        			    margin-right: 10px;
        			}
        			#best_information .row div:nth-child(3){
        			    widtH: 200px;
        			}
        			#best_information .row div:nth-child(4){
        			    width: 150px;
        			}
        			#best_information .row div:nth-child(1){
        				width: 100px;
        				float: left;
        			}
        			#best_information .disabled{
        			    background: #ababab;
        			    color: #fff;
        			    border-bottom: 1px solid #000;
        			}
        			#best_information .clear{
        				clear: both;
        			}
        		</style>
        		<div id="best_information">
                <div style="clear: both;"><input type="submit" name="submit" value="submit"/></div>
                <div class="row" style="background: #000; color: #fff;">
                    <div>Images</div>
                    <div>URL</div>
                    <div>Impressions</div>
                    <div>Date</div>
                    <div class="clear"></div>
                </div>
                <?php $count = 0; ?>
                <?php 
                    $top_info = '';
                    $bottom_info = '';
                        foreach($info as $p){ 
                            global $wpdb;
                            $g = $wpdb->get_results('SELECT ID,guid,post_content FROM '.$wpdb->posts.' AS p WHERE guid LIKE "%'.$p['current_page'].'%"');
                            
                            if(isset($g[0])){
                                $image_id = $this->get_post_images($g[0]->ID,$g[0]->post_content);
                            }  else {
                                $image_id = 'http://quanticpost.com/images/fotoyok3.jpg';
                            }
                            
                            if(isset($g[0])){
                                $top_info .= '
                                <div class="row" style="background: #fff; border-botton: 1px solid #000;">
                                    <div><a href="'.$g[0]->guid.'" target="_blank">'.$image_id.'</a></div>
                                    <div style="text-align: center; line-height: 60px; font-size: 20px;">'.number_format($p['impressions']).'</div>
                                    <div><a href="'.$g[0]->guid.'" target="_blank">'.$g[0]->guid.'</a></div>
                                    <div>Feed Url: <a href="'.$p['feed_url'].'">'.$p['feed_url'].'</a></div>
                                    <div>'.date('M d, Y',strtotime($p['pull_date'])).'</div>
                                    <div class="clear"></div>
                                </div> ';
                            } else {
                                $bottom_info .= '<div class="row" style="background: #ababab; border-botton: 1px solid #000;">
                                    <div style="width: 80px; margin-right: 20px; height: 50px; background: #000; color: #fff; text-align: center; line-height: 50px;">deleted</div>
                                    <div style="text-align: center; line-height: 60px; font-size: 20px;">'.number_format($p['impressions']).'</div>
                                    <div>'.$p['current_page'].'</div>
                                    <div style="background: #545454; color: #fff; padding: 10px; width: 100%;">Feed URL: <a href="'.$p['feed_url'].'" style="color: #fff;" target="_blank">'.$p['feed_url'].'</a></div>
                                    <div class="clear"></div>
                                </div>';
                            }
                        }
                    echo $top_info;
                    echo $bottom_info;
                ?>
                </div>
                </div>
            <?php   
            }
            
            public function create_feed_table($feeds,$manual=false,$manual_info=array()){
                if(!class_exists('WP_List_Table')){
                    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
                }
                
                include_once('class/table_creator.php');
                $table = new Table_Creator();
                
                $feed_info = new stdClass();
                
                global $wpdb;
                
                
                $feed_array = array();
                $count = 1;
                
                $feed_holder_url = parse_url(get_bloginfo('url'));
                
                if($feeds != '' && !isset($feeds->posts)){
    				$json = @json_decode($feeds);
    				
    				if(sizeof($json) > 0){
    					global $wpdb;
    					
    					foreach($json as $k=>$f){
    						if(isset($f->{'feed_name'}) && $f->{'feed_name'} != '' && $f->{'feed_url'} != ''){
    							if(isset($f->{'full_content'})){
    								$query_string = $f->{'feed_name'}.'|'.$f->{'feed_url'}.'|'.$f->{'feed_category'}.'|'.$f->{'full-content'};
    							} else {
    								$query_string = $f->{'feed_name'}.'|'.$f->{'feed_url'}.'|'.$f->{'feed_category'};
    							}
    							
    							if(isset($_GET['ID']) && $_GET['ID'] == $f->{'feed_name'}){
    								if((!isset($f->active)) || $_GET['active'] == 'false'){
    									$f->active = 'false';
    								} else {
    									$f->active = 'true';
    								}
    								$json[$k] = $f;
    							}
    							
    							if(!isset($f->active)){
    							    $f->active = 'true';
    							    $json[$k] = $f;
    							}
    							
    							if(!isset($f->best_feeds)){
    							    $f->best_feeds = 'true';
    							    $json[$k] = $f;
    							}
    							
    							if(!isset($f->feed_image_enabler)){
    							    $f->feed_image_enabler = 'true';
    							    $json[$k] = $f;
    							}
    							
    							if(isset($_POST['table_info'])){
        							if(isset($_POST['auto_delete'][$query_string])){
                                        $f->auto_delete = 'true';
                                        $json[$k] = $f;
                                    } else {
                                        $f->auto_delete = 'false';
                                        $json[$k] = $f;
                                    }
    							}
    							
    							$result = $wpdb->get_results('SELECT COUNT(p.ID) FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id = p.ID WHERE p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'" AND p.post_status != "trash"');
    							
    							$my_query = new stdClass();
    							$my_query->found_posts = (isset($result[0]))?$result[0]->{'COUNT(p.ID)'}:0;
    							
    							$f_array = get_object_vars($f);
    			                $length = $this->get_length_of_storage($f_array);
    			                if(!isset($f->total_feeds)){
    			                    $f->total_feeds = 'all';
    			                }
    			                
    							if(strlen($length) > 11 && isset($f) && isset($f->{'length-of-storage-days'})){
    								$post_range = $wpdb->get_results('SELECT COUNT(p.ID) FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id = p.ID WHERE p.post_date < "'.date('Y-m-d 00:00:00',strtotime(date('Y-m-d').' '.$length)).'" AND p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'"');
    								$string = '<div style="padding: 10px; background: #545454; color: #fff; border-radius: 5px; font-size: 11px;">Storage Length<br/> '.$f->{'length-of-storage-days'}.' days<br/> '.$f->{'length-of-storage-months'}.' months<br/> '.$f->{'length-of-storage-years'}.' years</div>';
    							} else {
    								if(strlen($length) == 11 && strpos($length,'-0 days') !== false){
    									$post_range[0] = new stdClass();
    									$post_range[0]->{'COUNT(p.ID)'} = $my_query->found_posts;
    								} else {
    								    if(isset($f->{'length-of-storage-days'})){
    									    $post_range = $wpdb->get_results('SELECT COUNT(p.ID) FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON m.post_id = p.ID WHERE p.post_date < "'.date('Y-m-d 00:00:00',strtotime(date('Y-m-d').' '.$length)).'" AND p.post_type="'.TW_FEED_TITLE.'" AND m.meta_value="'.$query_string.'"');
    									    $string = '<div style="padding: 10px; background: #545454; color: #fff; border-radius: 5px; font-size: 11px;">Storage Length <br/>'.$f->{'length-of-storage-days'}.' days<br/> '.$f->{'length-of-storage-months'}.' months<br/> '.$f->{'length-of-storage-years'}.' years</div>';
    								    }
    								}
    							}
    							
    							if(isset($f->feed_name)){
    							    $inactive = (isset($f->{'active'}) && $f->{'active'} == 'true')?'<a href="'.admin_url().'?page=twp_admin&ID='.rawurlencode($f->{'feed_name'}).'&active=false">active</a> (click to deactivate)':'<a href="'.admin_url().'?page=twp_admin&ID='.rawurlencode($f->{'feed_name'}).'&active=true">inactive</a> (click to activate)';
    							    $inactive_class = (isset($f->{'active'}) && $f->{'active'} == 'true')?'active':'inactive';
    							}
    							
    							if(!isset($post_range[0]->{'COUNT(p.ID)'})){
    							    $post_range[0] = new stdClass();
    								$post_range[0]->{'COUNT(p.ID)'} = 0;
    							}
    							
    							$mass_deleted = 0;
    							if(isset($f->auto_delete) && $f->auto_delete != 'false'){
    							    $total_deleted = $this->tw_process_delete($f,$length,$query_string);
    							    $mass_deleted = $this->tw_mass_delete_posts($total_deleted[1]);
    							    echo 'Posts Deleted '.$f->feed_name.': '.$mass_deleted.'<br/>';
    							} else {
    							    if(isset($f->best_feeds) && $f->best_feeds == true){
    							        $exclude = sizeof(json_decode($this->tw_best_feeds($f),true));
    							    } else {
    							        $exclude = 0;
    							    }
    							    $total_deleted = $this->tw_process_delete($f,$length,$query_string);
    							}
    							
    							if($total_deleted[0] > 0){
    							    $post_range[0]->{'COUNT(p.ID)'} = $post_range[0]->{'COUNT(p.ID)'}.' - '.$total_deleted[0];
    							    $my_query->found_posts = '<div style="font-size: 30px; margin-top: 10px;">'.number_format($my_query->found_posts-$mass_deleted).'</div><div style="margin-top: 10px;"><span style="color: #ff0000;">'.$total_deleted[0].' Deleted</span><br/> '.($my_query->found_posts-$total_deleted[0]).' Remaining</div>';
    							} else {
    							    $my_query->found_posts = '<div style="font-size: 30px; margin-top: 10px;">'.number_format($my_query->found_posts-$mass_deleted).'</div>';
    							}
    							
    							$auto_delete = (isset($f->auto_delete) && $f->auto_delete != 'false')?'checked':'';
    							
    							if($total_deleted[2] != 0){
    							    $my_query->found_posts .= '<div style="margin-top: 5px;">'.$total_deleted[2].' Excluded<br/><a href="http://quanticpost.com/excluded_post_tracking/'.str_replace('.','^',$feed_holder_url['host']).'" target="_blank">Add More Excluded Posts</a></div>';
    							} else {
    							    if(isset($f->best_feeds) && $f->best_feeds == 'true'){
    							        $excluded = sizeof(json_decode($this->tw_best_feeds($f->feed_url)));
    							    } else {
    							        $excluded = 0;
    							    }
    							    $my_query->found_posts .= '<div style="margin-top: 5px;">'.$excluded.' Excluded <br/><a href="http://quanticpost.com/excluded_post_tracking/'.str_replace('.','^',$feed_holder_url['host']).'" target="_blank">Add More Excluded Posts</a></div>';
    							}
    							
    							$my_query->found_posts .= '<div style="border-top: 1px solid #000; padding-top: 10px; margin-top: 5px;">Auto Delete <input type="checkbox" name="auto_delete['.$query_string.']" value="'.$query_string.'" '.$auto_delete.' /></div>';
    							
    							if(!isset($string)){
    							    $string = '';
    							}
    							
    							if($manual == true){
    							    if(isset($manual_info[$k]->updated)){
    							        $my_query->found_posts .= $manual_info[$k]->updated;
    							    } else {
    							        $my_query->found_posts .= '0 Updated';
    							    }
    							    $info_holder = new stdClass();
        							$info_holder->ID = $query_string;
        							$info_holder->storage_length = $string;
        							$info_holder->storage_length .= '<div style="color: #545454; padding: 5px;">Total Posts to be Deleted<strong> '.$total_deleted[0].'</strong><div style="background: #006600; padding: 10px; margin-top: 10px; border-radius: 5px; font-size: 11px;"><a style="color: #fff;" href="'.admin_url().'?page=tw_process_delete_button_press&ID='.rawurlencode(json_encode($f)).'">Click to Delete Processed Posts</a></div></div>';
        							$info_holder->title = $f->feed_name;
        							$info_holder->category = get_cat_name($f->feed_category);
        							$info_holder->active = '<div>'.$inactive.' <div class="'.$inactive_class.'"></div></div>';
        							$info_holder->total_feeds = $my_query->found_posts;
        							$info_holder->error = (isset($f->errors))?$f->errors:'none';
        							$info_holder->edit = '<a href="'.admin_url().'?page=tw_edit_feed_data&ID='.rawurlencode($query_string).'">Edit</a> | <a href="'.admin_url().'?page=tw_delete&ID='.rawurlencode($query_string).'">Delete</a> | <a href="'.admin_url().'?page=tw_update_feed&ID='.rawurlencode($query_string).'">Update</a>';
    							} else {
    							    $info_holder = new stdClass();
        							$info_holder->ID = $query_string;
        							$info_holder->storage_length = $string;
        							$info_holder->storage_length .= '<div style="color: #545454; padding: 5px;">Total Posts to be Deleted<strong> '.$total_deleted[0].'</strong><div style="background: #006600; padding: 10px; margin-top: 10px; border-radius: 5px; font-size: 11px;"><a style="color: #fff;" href="'.admin_url().'?page=tw_process_delete_button_press&ID='.rawurlencode(json_encode($f)).'">Click to Delete Processed Posts</a></div></div>';
        							$info_holder->title = $f->feed_name;
        							$info_holder->category = get_cat_name($f->feed_category);
        							$info_holder->active = '<div>'.$inactive.' <div class="'.$inactive_class.'"></div></div>';
        							$info_holder->total_feeds = $my_query->found_posts;
        							$info_holder->error = (isset($f->errors))?$f->errors:'none';
        							$info_holder->edit = '<a href="'.admin_url().'?page=tw_edit_feed_data&ID='.rawurlencode($query_string).'">Edit</a> | <a href="'.admin_url().'?page=tw_delete&ID='.rawurlencode($query_string).'">Delete</a> | <a href="'.admin_url().'?page=tw_update_feed&ID='.rawurlencode($query_string).'">Update</a>';
    							}
    							
    							if(strlen($info_holder->title) > 0){
    							    $feed_array[] = $info_holder;
    							}
    						}
    						
    						++$count;
    					}
    					
    					echo '<style>td .active{ background: #00ff00; height: 10px; width: 100%; border-radius: 10px; margin-top: 10px; } td .inactive{ background: #545454; height: 10px; width: 100%; margin-top: 10px; border-radius: 10px; }</style>';
    					update_option('rss_feeds',json_encode($json));
    				}
                }
                
                if(!isset($feeds->posts)){
                    $feed_info->posts = array();
                    if(isset($feed_array[0]) && $feed_array[0]->title != ''){
                        $feed_info->posts = $feed_array;
                    }
                } else {
                    $feed_info->posts = $feeds->posts;
                    foreach($feed_info->posts as $k=>$p){
    					$inactive = (!isset($p->{'active'}) || $p->{'active'} == 'true')?'<a href="'.admin_url().'?page=twp_admin&ID='.$p->{'title'}.'&active=false">active</a> (click to deactivate)':'<a href="'.admin_url().'?page=twp_admin&ID='.rawurlencode($p->{'title'}).'&active=true">inactive</a> (click to activate)';
    					$inactive_class = (!isset($p->{'active'}) || $p->{'active'} == 'true')?'active':'inactive';
    					
    					if(!isset($p->ID)){
    						$query_string = 0;
    						$p->ID = 0;
    					} else {
    						$query_string = $p->ID;
    					}
    					
    					$p->active = '<div>'.$inactive.' <div class="'.$inactive_class.'"></div></div>';
    					$p->edit = '<a href="'.admin_url().'?page=tw_edit_feed_data&ID='.rawurlencode($query_string).'">Edit</a> | <a href="'.admin_url().'?page=tw_delete&ID='.rawurlencode($query_string).'">Delete</a> | <a href="'.admin_url().'?page=tw_update_feed&ID='.rawurlencode($query_string).'">Update</a>';
    					
    					$length = (!isset($p->{'length-of-storage-days'}) || $p->{'length-of-storage-days'} == '0')?' ':' -'.($p->{'length-of-storage-days'}).' days ';
    					$length .= (!isset($p->{'length-of-storage-month'}) || $p->{'length-of-storage-months'} == '0')?' ':' -'.($p->{'length-of-storage-months'}).' months ';
    					$length .= (!isset($p->{'length-of-storage-year'}) || $f->{'length-of-storage-years'} == '0')?' ':' -'.($p->{'length-of-storage-years'}).' years ';
    					
    					$p->storage_length = $length;
    					$feed_info->posts[$k] = $p;
                    }
                }
                
                echo '<style>td .active{ background: #00ff00; height: 10px; width: 100%; border-radius: 10px; } td .inactive{ background: #545454; height: 10px; width: 100%; }</style>';
    			
                if(isset($feeds->posts)){
                    $table->setTemplate(array('ID','title','updated','total_feeds','error','edit','active'));
                } else {
                    $table->setTemplate(array('ID','title','category','total_feeds','storage_length','error','edit','active'));
                    $table->setActions(array(array('title'=>'edit','type'=>'feed','page'=>'','action'=>'trash')));
                }
                
                $table->getFeeds($feed_info);
                $table->prepare_items();
                return $table;
            }
            
            public function feed_group($id){
                global $post;
                $layout = new Layout(array('post'=>TW_FEED_TITLE),$this);
                $query = 'post_type=testing&post_per_page=5';
                $layout->get_layout(plugin_dir_path( __FILE__  ).'/layout/layout.php');
                
                $posts = new WP_Query($query);
                
                include_once('tw_tp-admin.php');
                
                $string = '<div class="tw-feed-content">';
                $string .= $layout->get_css();
                $string .= $layout->populate_layout($posts->posts);
                $string .= '</div>';
                return $string;
            }
            
            public function feed_info($id){
                $feed = new WP_Query('post_type=testing&ID='.$id);
            }
            
            public function twp_admin() {
    			delete_option('tw_rss_feed_animation');
                include_once('ttp-import-admin.php');
            }
        }
        
        global $post;
        
        if(!isset($post)){
            $tw_rss_feed = new tw_rss_feed();
        }
    }
?>
