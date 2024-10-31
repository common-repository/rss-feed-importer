<?php
class Layout{
    var $query;
    var $layout;
    var $info;
    var $layout_dir = 'layout/';
    var $json = '';
    var $xml = '';
    var $post_type = 'post';
    var $upload_dir = '';
    var $parent_class = null;
    
    //constructor for the layout function, used for various information gathering and setting up
    //numerous other information used for various other html layout compositions
    public function __construct($args = null,$parent_class = null){
        if($args != null){
            extract($args);
            $queryString = "post_type=".$post;
            if(isset($num)){
                $queryString .= '&post_per_page='.$num;
            }
            $this->info = new WP_Query($queryString);
            $this->post_type = $post;
        }
        $this->parent_class = $parent_class;
        $this->upload_dir = wp_upload_dir();
    }
    //get public function that allows for information to be passed through to various other modules when needed
    //there are numerous options in this function to allow returning information to numerous modules and other
    //string types which include xml as well as json
    public function getInfo(){
        $this->info = $this->query->posts;
    }
    //used to return xml information
  	public function getJSON(){
  		return json_encode($this->info->posts);
  	}
    //used to return json information
  	public function getXML(){
  	    $json = $this->getJSON();
  	    $json = json_decode($json,true);
        $this->createXML($json);
  	}
  	
  	public function convertXML($string,$array=array()){
  		if(sizeof($array) < 1){
  			$array = explode("\r\n", $string);
  		}
  	}
  	
  	public function remove_tag_content($string,$tag,$remove_tag=false){
  	    if($remove_tag){
  	        $string = preg_replace('/<'.$tag.'.*?>/i','',$string);
  	        $string = preg_replace('/<\/'.$tag.'>/i','',$string);
  	        return $string;
  	    } else {
  	        $string = preg_replace('/<'.$tag.'.*?>.*?<\/'.$tag.'>/i','',$string);
  	        return $string;
  	    }
  	    return null;
  	}
  	
  	public function fix_tags($string,$tag){
  	    $string = preg_replace('/'.$tag.'(.*?)>/i',' '.$tag.'$1>',$string);
  	    return $string;
  	}
  	
  	public function createXML($json,$string=""){
  	    if(is_array($json)){
  	        $string .= '<post>';
      	    foreach($json as $v=>$c){
                if(!is_array($c)){
                  $string .= '<'.$v.'>'.$c.'</'.$v.'>';
                } else {
                  $string .= '<'.$v.'>'.$this->createXML($c,$string).'</'.$v.'>';
                }
      	    }
      	    $string .= '</post>';
  	    }
  	    return $string;
  	}
    //search function used for various searching queries used for various populating information with
    //the WP_Query Object
    public function search($query){
        $queryString = '';
        foreach($query as $k=>$q){
            $queryString .= $k.'='.$q.'&';
        }
        $queryString = substr($queryString,0,-1);
        $this->query = new WP_Query($queryString);
        $this->info = $this->query->posts;
    }
    //get layout is used to create a layout that will allow the population of the template file
    //if there are issues with the template, it will be fixed through the populate_layout function
    public function get_layout($layout){
        $this->layout = file_get_contents($layout);
    }
    
    public function get_css(){
        return '<link href="'.plugins_url('css/'.$this->post_type.'/style.css',__FILE__).'" rel="stylesheet" type="text/css" media="all" />';
    }
    
    public function get_js(){
        return '<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.isotope/2.2.2/isotope.pkgd.min.js"></script>';
    }
    //populates the template to make sure that there are variables need to complete the layout
    //this will allow for various layouts to be created dynamically if needed.
    /*the options being passed are 
   		$template which holds the template string
    	$info which holds the info to be pulled into the template
    */
    //if there is information in the template but not in the information string, then there will be an ommiting of that information
    //until it is otherwise validated through the plugin
    public function populate_layout($info,$options=array(),$advertisements,$image_size,$source,$images_only=false){
        //extract($options);
        global $wpdb;
	    $advert = file_get_contents(plugin_dir_path( __FILE__ ).'/layout/advertisements.php');
	    
        $templateHolder = "";
        $template = $this->layout;
        
        if($this->layout == ''){
        	foreach($info as $k=>$i){
        		$templateHolder .= '<div class="'.$k.'">'.$i.'</div>';
        	}
        } else {
            if($images_only){
                foreach($info as $k=>$p){
                    $content = $p->post_content;
            	    $template_double = $template;
            	    preg_match_all("/\[(\w+(\:\d+)?)\]/is", $template_double, $str_result);
            	    $p->post_title = str_replace("\'",'\'',strip_tags(html_entity_decode(rawurldecode($p->post_title))));
            	    $p->post_content = strip_tags(html_entity_decode($p->post_content));
            	    $images = $this->parent_class->get_post_images($p->ID,$content);
            	    
            	    foreach($str_result[0] as $s){
            	        preg_match("/(\w+)(\:(\d+))?/is", $s, $strHolder);
                	    if(sizeof($strHolder) < 4){
                	        if($strHolder[0] == 'link'){
                	            $template_double = str_replace('['.$strHolder[0].']', get_permalink($p->ID), $template_double);
                	        } elseif($strHolder[0] == 'images'){
                	           if($images != ''){
                	                if($image_size == 'elongated' || $source == 'widget'){
                	                    $images = '<a href="[link]"><div class="tw_small_layout">'.$images.'</div></a>';
                	                } else {
                	                   $images = '<a href="[link]"><div class="tw_small_layout_square">'.$images.'</div></a>';
                	                }
                	            } else {
                	                $images = '';
                	            }
                	            $template_double = str_replace('['.$strHolder[0].']', $images,rawurldecode($template_double));
                	        } elseif($strHolder[0] == 'post_content') {
                	            $template_double = '';
                	        } else {
                		        $template_double = str_replace('['.$strHolder[0].']', $p->{$strHolder[0]}, rawurldecode($template_double));
                	        }
                	    } else {
                	        $title_only_check = (isset($title_only))?1:0;
                	        if($strHolder[1] == 'post_content'){
                    	        if($title_only_check == 0){
                    	            $template_double = str_replace('['.$strHolder[0].']', substr(strip_tags($p->{$strHolder[1]}),0,$strHolder[3]).'...', htmlspecialchars_decode(str_replace('â',"'",rawurldecode($template_double))));
                    	        } else {
                    	            $template_double = str_replace('['.$strHolder[0].']', '', htmlspecialchars_decode(rawurldecode($template_double)));
                    	        }
                	        } else {
                	            $template_double = str_replace('['.$strHolder[0].']','',htmlspecialchars_decode(rawurldecode($template_double)));
                	        }
                	    }
        	        }
            	    $templateHolder .= $template_double;
            	}
            } else {
            	foreach($info as $k=>$p){
            	    $template_double = $template;
            	    
            	    $content = $p->post_content;
            	    $images = $this->parent_class->get_post_images($p->ID,$content);
            	    
            	    $images_check = '';
            	    
                    $url = parse_url($images[3]);
                    
            	    preg_match_all("/\[(\w+(\:\d+)?)\]/is", $template_double, $str_result);
            	    $p->post_title = str_replace("\'",'\'',strip_tags(html_entity_decode(urldecode($p->post_title))));
            	    $p->post_content = strip_tags(html_entity_decode($p->post_content));
            	    
            	    $image_holder = '';
            	    
            	    foreach($str_result[0] as $s){
            	        preg_match("/(\w+)(\:(\d+))?/is", $s, $strHolder);
            	        
                	    if(sizeof($strHolder) < 4){
                	        if($strHolder[0] == 'link'){
                	            $template_double = str_replace('['.$strHolder[0].']', get_permalink($p->ID), $template_double);
                	        } elseif($strHolder[0] == 'images'){
                	            if($image_size == '') $image_size = 'square';
                	            
                	            if(!is_file($images_check)){
    	            	            $crop = wp_get_image_editor($images);
                	           	}
                	           	
                	           	$image_holder = explode('?',$image_holder);
                	           	$image_holder = $image_holder[0];
                	           	
                	           	if(!is_file($image_holder) == 1){
                	           	    $post_info = $wpdb->get_results('SELECT p.post_content FROM '.$wpdb->posts.' as p WHERE p.ID='.$p->ID);
                                    if(sizeof($post_info) > 0){
                	           	        $images = $images;
                                    }
                	           	} else {
                	           	    $images = '<a href="[link]">'.$images.'</a>';
                	           	}
                	           	
                	            if($images != ''){
                	                if($image_size == 'elongated' || $source == 'widget'){
                	                    $images = '<a href="[link]"><div class="tw_small_layout">'.$images.'</div></a>';
                	                } else {
                	                   $images = '<a href="[link]"><div class="tw_small_layout_square">'.$images.'</div></a>';
                	                }
                	            } else {
                	                $images = '';
                	            }
                	            
                	            $template_double = str_replace('['.$strHolder[0].']', $images,$template_double);
                	        } else {
                		        $template_double = str_replace('['.$strHolder[0].']', $p->{$strHolder[0]}, $template_double);
                	        }
                	    } else {
                	        $title_only_check = (isset($title_only))?1:0;
                	        if($title_only_check == 0){
                	            $template_double = str_replace('['.$strHolder[0].']', substr(strip_tags($p->{$strHolder[1]}),0,$strHolder[3]).'...', htmlspecialchars_decode(str_replace('â',"'",rawurldecode($template_double))));
                	        } else {
                	            $template_double = str_replace('['.$strHolder[0].']', '', htmlspecialchars_decode(rawurldecode($template_double)));
                	        }
                	    }
            	    }
            	    
            	    $templateHolder .= $template_double;
            	}
            }
        }
        return $templateHolder;
    }
}
?>