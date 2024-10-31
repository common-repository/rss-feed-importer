<?php global $wpdb; ?>
<style>
    #new_features{
        background: #fff;
        padding: 10px;
    }
    .layout{
        background: #ffa500;
        padding: 5px;
        border-radius: 5px;
        color: #fff;
    }
    .layout a{
        color: #fff;
        text-decoration: none;
    }
    .animated-layout{
        background: #000;
        padding: 10px;
        color: #fff;
    }
    .schedule-layout{
        padding: 10px;
    }
    form{
        margin-top: 20px;
    }
    .feed_pull_schedule{
        padding: 10px;
    }
    .pagination{
        background: #000;
        color: #fff;
        padding: 10px;
    }
    .pagination_bottom{
        padding: 10px;
    }
    .auto_page_creation{
        padding: 10px;
        color: #fff;
        background: #ababab;
    }
    #custom_css_holder{
        padding: 10px;
        border-radius: 10px;
        background: #545454;
        color: #fff;
        border: 1px solid #000;
        box-shadow: 0px 1px 5px rgba(0,0,0,.8);
        margin-bottom: 10px;
    }
    #custom_css{
        border-radius: 10px;
        padding: 10px;
    }
    #custom_css_button_holder{
        padding: 10px; 
        background: #000; 
        border-radius: 10px; 
        margin-bottom: 10px;
    }
    #custom_css_holder .revert_button{
        background: rgba(255,0,0,.8);
        float: left;
        padding: 10px;
        color: #fff;
        margin-top: 10px;
        border-radius: 10px;
    }
    .save_status{
        float: left;
        margin-top: 10px;
        background: rgba(0,0,255,.8);
        border-radius: 10px;
        margin-left: 10px;
    }
    .save_button{
    	float: left;
    	margin-left: 10px;
    	padding: 10px;
    	background: rgba(0,255,0,.3);
    	border-radius: 10px;
    	margin-top: 10px;
    }
    .button_holder{
    	display: none;
    }
    .get-best-content{
        padding: 10px;
    }
    .clear{
        clear: both;
    }
</style>
<h1>Settings</h1>
<?php
    $event = get_option('tw_schedule_event');
    $pagination = get_option('tw_pagination');
    $custom_css = get_option('tw_custom_css');
    $auto_create_pages = get_option('tw_auto_create_pages');
    $feed_option = get_option('tw_feed_title');
    $curr_time = get_option('tw_curr_schedule');
    
    $set_feeds = get_option('tw_set_feeds');
    $top_content = get_option('tw_get_top_content');
	
	$carousel_posts = $wpdb->get_results('SELECT p.ID,p.guid,p.post_title,m.meta_value AS image FROM '.$wpdb->posts.' AS p JOIN '.$wpdb->postmeta.' AS m ON p.ID=m.post_id WHERE m.meta_key = "tw_carousel" and m.meta_value != "" AND p.post_type="'.TW_FEED_TITLE.'"');

    if($event == false){
        update_option('tw_schedule_event',$_POST['schedule']);
        $event = $_POST['schedule'];
    }
    
    $top_content_info = array();
    if($top_content == 'true'){
        $blog = parse_url(get_bloginfo('url'));
	    $top_content_info = json_decode(file_get_contents('http://quanticpost.com/getdata/get_best_feeds/'.str_replace('.','^',str_replace('/','|',$blog['host']))),true);
    }
    
    $string = '';
    $pagination_footer = get_option('tw_pagination_footer');
?>
<?php $this->advertisements(); ?>
<form action="" method="POST" style="margin-bottom: 10px; border-bottom: 2px solid #000; padding: 10px;">
    <div class="schedule-layout">
        <div class="feed_pull_schedule">
            Schedule Feed Pulls:
            <select name="schedule">
                <option value="daily-off">Off (will still pull info daily for our servers)</option>
                <option value="15-minutes" <?php if($event == '15-minutes'){ echo 'selected'; }?>>15 minutes</option>
                <option value="30-minutes" <?php if($event == '30-minutes'){ echo 'selected'; }?>>30 Minutes</option>
                <option value="hourly" <?php if($event == 'hourly'){ echo 'selected'; }?>>Hourly</option>
                <option value="twicedaily" <?php if($event == 'twicedaily'){ echo 'selected'; }?>>Twice Daily</option>
                <option value="daily" <?php if($event == 'daily'){ echo 'selected'; }?>>Daily</option>
            </select>
            <?php 
                $minutes = (strtotime(date('Y-m-d H:i:s',$curr_time))-strtotime(date('Y-m-d H:i:s')))/60; 
                $hours = ($minutes > 60)?floor($minutes/60):0;
                if($minutes < floor($hours*60)){
                	$minutes = floor($hours*60)-$minutes;
                } else {
                	$minutes = $minutes-floor($hours*60);
                }
            ?>
            <div style="margin-bottom: 10px; min-width: 350px; max-width: 500px; float: right; padding: 5px 10px; color: #fff; background: #009900;">
                <?php echo 'Cron will run in '.number_format($minutes,2).' minutes and '.number_format($hours,2).' hours'; ?>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div id="custom_css_holder">
		    <div id="custom_css_button_holder">Enable Custom CSS <input type="checkbox" name="tw_custom_css" id="custom_css_button" <?php if($custom_css == 'true'){ ?>checked<?php } ?> /></div>
		    <textarea name="custom_css" id="custom_css" style="height: 300px; width: 100%;"><?php echo file_get_contents(plugin_dir_path( __FILE__ ) . 'css/feeds/style_custom_'.str_replace(' ','_',get_bloginfo('name')).'.css'); ?></textarea>
		    <div id="button_holder">
			    <div class="revert_button">Revert to default</div>
			    <div class="save_status"></div>
			    <input type="submit" value="submit" name="submit" style="margin: 10px; margin-top: 10px; border-radius: 10px; background: rgba(0,255,0,.8); border: none; color: #fff; padding: 9px; font-size: 14px;">
			    <div class="clear"></div>
		    </div>
		</div>
		<style>
			.row{
				border-bottom: 1px solid #ababab;
				padding: 10px;
				overflow: hidden;
			}
			.row div{
			    float: Left;
			    width: 100px;
			    padding-right: 10px;
			}
			.row div:nth-child(5){
			    widtH: 100%;
			}
			.row div:nth-child(3){
			    widtH: 600px;
			}
			.row div:nth-child(1){
				width: 100px;
				float: left;
			}
			.disabled{
			    background: #ababab;
			    color: #fff;
			    border-bottom: 1px solid #000;
			}
			.clear{
				clear: both;
			}
		</style>
		<div style="padding: 10px;">
		    <div class="row" style="background: #000; color: #fff;">
		        <div style="float: right">Remove</div>
		        <div style="float: left; width: 50px;">ID</div>
                <div style="float: left;">Title</div>
                <div class="clear"></div>
		    </div>
		    <?php foreach($carousel_posts as $p){ ?>
            <div class="row">
            	<div><?php echo $p->ID; ?></div>
            	<div style="float: right;"><input type="checkbox" name="tw_featured_id[]" value="<?php echo $p->ID; ?>" /></div>
                <div><a href="<?php echo $p->guid; ?>" target="_blank"><?php echo str_replace("\'",'\'',html_entity_decode(rawurldecode($p->post_title))); ?></a></div>
                <div class="clear"></div>
            </div>
		    <?php } ?>
		</div>
		<div style="padding: 10px;">
		    <div style="float: left; width: 300px; padding-right: 10px;">Feed Title (used for naming the feed type)</div>
		    <div><input type="text" name="tw_feed_title" value="<?php echo $feed_option; ?>"/></div>
		</div>
        <div class="pagination">
            <div style="float: left; width: 300px; padding-right: 10px;">Pagination (page numbering for feed display)</div>
            <div><input type="checkbox" name="tw_pagination" <?php if($pagination != 'false'){ echo 'checked'; } ?>/></div>
            <div style="clear: both;"></div>
        </div>
        <div class="pagination_bottom">
            <div style="float: left; width: 470px; padding-right: 10px;">Pagination Footer (place next and previous at the bottom of every feed)</div>
            <div><input type="checkbox" name="tw_pagination_footer" <?php if($pagination_footer != 'false'){ echo 'checked'; } ?> /></div>
            <div style="clear: both;"></div>
        </div>
        <div class="auto_page_creation">
            <div style="float: left; width: 470px; padding-right: 10px;">Auto Page Creation (create pages for each feed category automatically)</div>
            <div><input type="checkbox" name="auto_create_pages" <?php if($auto_create_pages != 'false'){ echo 'checked'; } ?>/></div>
            <div style="clear: both;"></div>
        </div>
        <div class="get-best-content">
            <div style="float: left; width: 470px; padding-right: 10px;">Get Best Content (display the top feed post for your content)</div>
            <div><input type="checkbox" name="top_content" <?php if($top_content != '' && $top_content != 'false'){ echo 'checked'; } ?>/></div>
            <div style="clear: both;"></div>
        </div>
        <div>
                <?php $count = 0; ?>
                <?php 
                    if(isset($top_content) && $top_content != 'false' && sizeof($top_content_info) > 0 ){ ?>
                    <div style="clear: both;">
                        <div style="float: left; margin-right: 10px;">Deleted <div style="float: right; margin-top: 5px; margin-left: 10px; width: 10px; height: 10px; background: #ababab;"></div></div>
                        <div style="float: left;">Active <div style="float: right; margin-top: 5px; margin-left: 10px; width: 10px; height: 10px; background: #fff;"></div>
                        <div style="clear: both;"></div>
                    </div>
                <?php   
                        $this->best_information($top_content_info);
                    }
                ?>
        </div>
    </div>
    <input type="hidden" value="" name="unchecked_carousel" class="unchecked_carousel"/>
    <input type="submit" value="submit" name="submit" style="margin: 10px;">
</form>
<script>
    var disable = document.getElementsByClassName('featured_info');
    for(var i = 0; i < disable.length; ++i){
        if(disable[i].getElementsByTagName('input')[0].checked == true){
            disable[i].getElementsByTagName('input')[0].onclick = function(el){
                var info_carousel = document.getElementsByClassName('unchecked_carousel')[0];
                console.log(info_carousel);
                if(info_carousel.value.search(el.currentTarget.value) == -1){
                    info_carousel.value += el.currentTarget.value+',';
                } else {
                    info_carousel.value = info_carousel.value.replace(el.currentTarget.value+',','');
                }
            }
        }
    }
	var ChangeClasses = function(){
		this.show_layout = document.getElementById('show_layout');
		this.show_layout_square = document.getElementById('show_layout_rss');
		this.get_layout_info = ['tw-content-holder','rss-search','layout-holder','tw-read-more','rss-feed-holder','rss-search|h1'];
		
		this.update_layouts = function(){
			var custom_css = document.getElementById('custom_css');
			var new_string = custom_css.value.replace(/\n/g," ");
			new_string = new_string.match(/(.*?)\{(.*?)\}/gi);
			for(var a = 0; a < new_string.length; ++a){
				if(typeof new_string[new_string[a].match(/(.*?)\{/gi)] == 'undefined'){
					new_string[new_string[a].match(/(.*?)\{/gi)] = new_string[a].match(/\{(.*?)\}/gi);
				}
			}
			for(a in this.get_layout_info){
				this.show_layout.getElementsByClassName(this.get_layout_info[a])[0];
			}
		}
	}
    var SetupCss = function(){
        this.custom_css = document.getElementById('custom_css');
        this.custom_css_button = document.getElementById('custom_css_button');
        this.current_key = '';
        this.revert_button = document.getElementsByClassName('revert_button');
        this.revisions = [];
        this.day = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        this.month = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        this.auto_saves = [{'string':'','date':new Date}];
        if(localStorage.getItem('twLayout.autosave') != undefined){
            this.autosaves = JSON.parse(localStorage.getItem('twLayout.<?php echo str_replace(' ','_',get_bloginfo('name')); ?>.autosave'))
            localStorage.removeItem('twLayout.autosave');
        }
        console.log(localStorage);
        if(localStorage.getItem('twLayout.<?php echo str_replace(' ','_',get_bloginfo('name')); ?>.autosave') != undefined){
            this.auto_saves = JSON.parse(localStorage.getItem('twLayout.<?php echo str_replace(' ','_',get_bloginfo('name')); ?>.autosave'));
        }
        this.timeout = setTimeout(0);
        self = this;
        
        this.revert_button[0].onclick = function(){
            var string = self.start_css.replace(/\|/g,"\n");
            self.custom_css.value = string;
        }
        
        document.getElementsByClassName('save_status')[0].onclick = function(){
            if(typeof self.auto_saves[self.auto_saves.length-1] != 'undefined'){
                var testing = self.auto_saves.pop();
                self.custom_css.value = testing.string;
                if(self.auto_saves.length-1 > 0){
                    localStorage.setItem('twLayout.<?php echo str_replace(' ','_',get_bloginfo('name')); ?>.autosave',JSON.stringify(self.auto_saves));
                    var date = new Date(self.auto_saves[self.auto_saves.length-1].date);
                    document.getElementsByClassName('save_status')[0].innerHTML = '<div style="padding: 10px;"><span class="revert_button_prev" style="font-size: 10px;">revert to '+self.day[date.getDay()]+' '+self.month[date.getMonth()]+' '+date.getDate()+', 20'+date.getYear().toString().substr(1,2)+' '+date.getHours()+':'+date.getMinutes()+':'+date.getSeconds()+'</span> auto saves: '+(self.auto_saves.length-1)+'</div>';
                } else {
                    localStorage.setItem('twLayout.<?php echo str_replace(' ','_',get_bloginfo('name')); ?>.autosave',JSON.stringify(self.auto_saves));
                    console.log(localStorage.getItem('twLayout.<?php echo str_replace(' ','_',get_bloginfo('name')); ?>.autosave'));
                    document.getElementsByClassName('save_status')[0].innerHTML = '';
                }
            }
		}
        
        this.save_timer = function(el){
            clearTimeout(self.timeout);
            if(self.auto_saves[self.auto_saves.length-1].string != self.custom_css.value){
                self.auto_saves.push({'string':self.custom_css.value,'date':new Date});
                localStorage.setItem("twLayout.<?php echo str_replace(' ','_',get_bloginfo('name')); ?>.autosave", JSON.stringify(self.auto_saves));
            }
            var date = new Date(self.auto_saves[self.auto_saves.length-1].date);
            document.getElementsByClassName('save_status')[0].innerHTML = '<div style="padding: 10px;"><span class="revert_button_prev" style="font-size: 10px;">revert to '+self.day[date.getDay()]+' '+self.month[date.getMonth()]+' '+date.getDate()+', 20'+date.getYear().toString().substr(1,2)+' '+date.getHours()+':'+date.getMinutes()+':'+date.getSeconds()+'</span> auto saves: '+(self.auto_saves.length-1)+'</div>';
            self.timeout = setTimeout(self.save_timer,5000);
        }
        <?php if($custom_css != 'true'){ ?>
        	this.custom_css.style.display = 'none';
        	document.getElementById('button_holder').style.display = 'none';
        <?php } else { ?>
        	this.timeout = setTimeout(self.save_timer,500);
        	document.getElementById('button_holder').style.display = 'block';
        <?php } ?>
        
        this.start_css = '<?php echo str_replace("\n","|",file_get_contents(plugin_dir_url( __FILE__ ) . 'css/feeds/style.css')); ?>';
        
        this.custom_css_button.onclick = function(el){
            self.timeout = setTimeout(self.save_timer,500);
            if(el.currentTarget.checked != true){
                self.custom_css.style.display = 'none';
                document.getElementById('button_holder').style.display = 'none';
            } else {
                self.custom_css.style.display = 'block';
                document.getElementById('button_holder').style.display = 'block';
            }
        }
        
        this.custom_css.onfocus = function(el){
            document.addEventListener('keydown',self.check_keyvariables);
            document.addEventListener('click',self.check_element);
        }
        
        this.check_element = function(el){
            if(el.target.getAttribute('id') != 'custom_css'){
                self.current_key = 'click';
                self.custom_css.blur();
            }
        }
        
        this.format_code = function(number){
        	var string = self.custom_css.value.toString();
       		var pos = self.custom_css.selectionStart;
       		var char = '';
       		
       		if(number == '9'){
       		    var character = "    ";
       		    pos += 4;
       		} else {
       		    var character = "";
       		}
       		
       		string = string.slice(0,self.custom_css.selectionStart)+character+string.slice(self.custom_css.selectionEnd);
       		self.custom_css.value = string;
       		ctrl = self.custom_css;
       		if(ctrl.setSelectionRange){
		        ctrl.focus();
		        ctrl.setSelectionRange(pos,pos);
		    } else if (ctrl.createTextRange) {
		        var range = ctrl.createTextRange();
		        range.collapse(true);
		        range.moveEnd('character', pos);
		        range.moveStart('character', pos);
		        range.select();
		    }
        }
        
        this.check_keyvariables = function(el){
            self.current_key = el.keyCode;
            switch(self.current_key){
                case 13:
               		self.format_code(13);
                    break;
               	case 9:
               		self.format_code(9);
               		break;
                default:
                    break;
            }
        }
        
        this.custom_css.onblur = function(el){
            if(self.current_key == 9 && self.current_key != 'click'){
                self.custom_css.focus();
            } else {
                if(document.removeEventListener){
                    document.removeEventListener('keydown',self.check_keyvariables);
                } else {
                    document.detachEvent('onkeydown',self.check_keyvariables);
                }
            }
        }
    }
    
    var new_layout = new SetupCss();
</script>
<h2>Impressions Report (temporary cleared <?php echo get_option('tw_schedule_event'); ?>)</h2>
<div class="layout"><a href="admin.php?page=tw_impressions" style="background: #000; color: #fff; padding: 10px;">Click here</a> to view full report</div>
<?php
    $f = $wpdb->get_results('SELECT meta_value FROM '.$wpdb->postmeta.' WHERE meta_key = "tw_rss_feed_impression"');
    if(!class_exists('WP_List_Table')){
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }
    include_once('class/table_creator.php');
    $table = new Table_Creator();
    $feed_info = new stdClass();
    $array = array();
    
    foreach($f as $a){
            $info_holder = new stdClass();
            $p = json_decode($a->meta_value,true);
        if($p['feed_name'] != ''){
            $info_holder->feed_name = $p['feed_name'];
            $info_holder->feed_url = urldecode('<a href="'.$p['feed_url'].'">'.$p['feed_url'].'</a>');
            $info_holder->current_page = urldecode('<a href="'.$p['current_page'].'" target="_blank">'.$p['current_page'].'</a>');
            $info_holder->impression = $p['impression'];
            
            $array[] = $info_holder;
        }
    }
    $feed_array = array();
    $feed_info->posts = $array;
    $table->setTemplate(array('feed_name','feed_url','current_page','impression'));
    $table->setActions(array(array('title'=>'edit','type'=>'feed','page'=>'','action'=>'trash')));
    $table->getFeeds($feed_info);
    $table->prepare_items();
    $table->display();
    $this->advertisements(); ?>