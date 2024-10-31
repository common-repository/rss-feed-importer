<?php
    $url = parse_url(site_url());
    $url = str_replace($url['scheme'].'://','',site_url());
    $url = str_replace('/','_',str_replace('.','^',$url));
    
    if(isset($_POST['tw_advertising']) && $_POST['tw_advertising'] != ''){
        update_option('tw_advertising','true');
    } elseif(isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    	update_option('tw_advertising','');
    }
    
    $advertisements = get_option('tw_advertising');
    
    if(isset($_POST['submit']) && $_POST['submit'] == 'submit'){
        $auto_social = '';
        if(isset($_POST['tw_auto_social'])){
            foreach($_POST['tw_auto_social'] as $k=>$a){
                $auto_social .= $k.'|';
            }
        }
        update_option('tw_auto_social',$auto_social);
    }
    
    if(isset($_POST['feed_process']) && sizeof($_POST['feed_process'])){
        foreach($_POST['feed_process'] as $k=>$f){
            update_post_meta($k,'tw_social_option','false');
        }
    }
    
    if(!isset($_POST['tw_advertising'])){
        $this->api_call('http://quanticpost.com/advertisingshare/'.$url,array('remove'=>'true'));
    } else {
        $this->api_call('http://quanticpost.com/advertisingshare/'.$url);
    }
    
    $share = array();
    if(isset($_POST['social']) && sizeof($_POST['social']) > 0){
        $shared = '';
        foreach($_POST['social'] as $k=>$ae){
            $shared .= $k.',';
            $share[$k] = 'on';
        }
        update_option('tw_social',$shared);
    } elseif(isset($_POST['submit'])) {
        update_option('tw_social','');
    }
    
    if(isset($_POST['tw_auto_social_api']) && sizeof($_POST['tw_auto_social_api']) > 0){
    	$api = '[';
    	foreach($_POST['tw_auto_social_api'] as $k=>$a){
    		if($a != 'Enter Info' && !is_array($a)){
    			$api .= '{"'.$k.'":"'.$a.'"},';
    		}
    	}
    	$api = substr($api,0,-1);
    	$api .= ']';
    	update_option('tw_auto_social_api',$api);
    } elseif(isset($_POST['submit'])) {
    	update_option('tw_auto_social_api','');
    }
    
    $api = json_decode(get_option('tw_auto_social_api'),true);
    if(is_array($api)){
    	foreach($api as $a=>$p){
    		$api[$a] = $p;
    		foreach($p as $k=>$j){
    			$api[$k] = $j;
    		}
    		unset($api[$a]);
    	}
    }
    
    $string_holder = '';
    if(isset($_POST['tw_auto_social_api']) && sizeof($_POST['tw_auto_social_api'] > 0)){
        $tokens = '';
        $string = '';
        foreach($_POST['tw_auto_social_api'] as $k=>$a){
        	$string_holder .= '{"'.$k.'":{';
        	foreach($a as $j=>$f){
        	    $f = trim($f);
        	    if($f == 'Enter Info' || $f == ''){
        	        $string_holder = '';
        	        break;
        	    } else {
        	        $string_holder .= '"'.$j.'":"'.$f.'",';
        	    }
        	}
        	if($string_holder != ''){
        	    $string .= substr($string_holder,0,-1).'}},';
        	}
        }
        if($string != ''){
            $tokens .= substr($string,0,-1);
        }
        $tokens .= '';
        update_option('tw_social_tokens',$tokens);
    } elseif(isset($_POST['submit'])){
        update_option('tw_social_tokens','');
    }
    
    $tw_auto_social = json_decode(get_option('tw_social_tokens'),true);
    
    $share = explode(',',get_option('tw_social'));
    $d = array();
    
    foreach($share as $f){
        $d[$f] = 'on';
    }
    $share = $d;
    $d = null;
    
    $social = json_decode(get_option('tw_social_tokens'),true);
	if(isset($social['twitter']) && sizeof($social['twitter']) > 1 && strlen($social['twitter']['consumer_key']) > 5){
		$twitter = $social['twitter'];
		include_once(plugin_dir_path( __FILE__  ).'class/twitter_process.php');
	}
?>
<script>
    var image_src = '<?php echo plugins_url('images/facebook-icon.png',__FILE__); ?>; ?>';
</script>
<style>
    form{
        padding: 10px;
    }
    label{
        width: 200px;
        float: left;
    }
    .share{
        padding: 10px;
        font-weight: bold;
        padding-top: 90px;
        overflow-y: scroll;
        clear: both;
    }
    .top-share{
    	clear: both;
    }
    form > div > div{
        float: left;
        width: 30px;
        text-align: center;
        margin: 10px;
    }
    form > div > div > img{
        width: 100%;
    }
    .auto-share-options > div{
        float: left;
        width: 200px;
        border: 1px solid #ababab;
        padding: 10px;
        background: #FAFAFA;
        box-shadow: 0px 3px 5px rgba(0,0,0,.1);
    }
    .layout{
        width: 100%;
        clear: both;
    }
    .layout > div{
        width: 9%;
    }
    #show-information{
        float: left;
    }
    #show-information > div{
		width: 100%;
		text-align: left;
	}
    .auto-share-options{
    	clear: both;
    }
    .facebook-icon, .digg-icon, .twitter-icon, .reddit-icon, .linkedin-icon, .google-icon{
        width: 70px;
        height: 70px;
        margin: 10px auto;
        background: url(<?php echo plugins_url('images/preview-flat-icons.png',__FILE__); ?>);
    }
    .facebook-icon{
        background-position: -180px -200px;
    }
    .digg-icon{
        background-position: 95px -115px;
    }
    .twitter-icon{
        background-position: -183px 95px;
    }
    .google-icon{
        background-position: -25px -285px;
    }
    .reddit-icon{
        background-position: -265px -370px;
    }
    .linkedin-icon{
        background-position: 178px -285px;
    }
    .advertising{
        background: #121212;
        color: #fff;
        padding: 10px;
    }
    .tw_advertising{
    	clear: both;
    	width: 100%;
    }
    .clear{
        clear: both;
    }
    .monitization_records{
        height: 100%;
    }
</style>
<?php 
    $auto_social = explode('|',get_option('tw_auto_social'));
    foreach($auto_social as $f){
        $auto_social[$f] = $f;
    }   
?>
<h1>Monitization/Sharing Options</h1>
<div id="grab-advert-info"></div>
<div class="hint-info">
    Working closely with the below social networking companies, we're capable of making the most out of your social networking experiences while running our
    plugin. These features are currently being developed and will be released on a later date, but please feel free to test out the features on this page and 
    please feel free to <a href="http://quanticpost.com/donate" target="_blank">Donate</a> to speed up the development process.
</div>
<form action="" method="POST">
    <div class="top-share">
        <h3>Social Networking</h3>
        <div>
            <img src="<?php echo plugins_url('images/facebook-icon.png',__FILE__); ?>"/>
            <input type="checkbox" name="social[facebook]" <?php if(isset($share['facebook'])){ echo 'checked'; } ?>/>
        </div>
        <div>
            <img src="<?php echo plugins_url('images/twitter-icon.png',__FILE__); ?>"/>
            <input type="checkbox" name="social[twitter]" <?php if(isset($share['twitter'])){ echo 'checked'; } ?>/>
        </div>
        <div>
            <img src="<?php echo plugins_url('images/linkedin-icon.png',__FILE__); ?>"/>
            <input type="checkbox" name="social[linkedin]" <?php if(isset($share['linkedin'])){ echo 'checked'; } ?>/>
        </div>
        <input type="hidden" value="no" name="socialbuttons"/>
        <div class="clear"></div>
    </div>
    <div class="tw_advertising">
    	<h2>Advertisements</h2>
    	<span class="advertising">Become part of the advertising network! <strong>(Click Here to Signup <a href="http://quanticpost.com/advertisingshare/<?php echo htmlspecialchars(str_replace('.','^',str_replace('/','_',str_replace('http://','',get_bloginfo('wpurl'))))); ?>" target="_blank">Read More</a> )</strong> <input type="checkbox" name="tw_advertising" <?php if($advertisements != '') echo 'checked'; ?> /></span>
    </div>
    <div class="monitization_records">
    	<?php $url = str_replace('/', '_',str_replace('.','^',str_replace('http://','',str_replace('https://','',get_bloginfo('url'))))); ?>
    </div>
    <style>
        h2{
            margin-top: 40px;
        }
        .hint-info{
            background: #121212; 
            color: #fff; 
            box-shadow: 0px 2px 5px rgba(0,0,0,.2); 
            padding: 10px;
            margin-bottom: 10px;
        }
        .hint-info a{
            background: #ffa500;
            padding: 2px 10px; 
            color: #000;
            font-weight: bold;
            text-decoration: none;
        }
        .auto-share-options label:nth-child(-n+9){
        	border-bottom: 1px solid #000; 
        	text-align: left;
        	padding: 10px 0px;
        }
        input[type=text], input[type=number]{
        	background: #000;
        	border: none;
        	color: #fff;
        }
    </style>
    <style>
        .content_holder{
            height: 500px;
            overflow: hidden;
            overflow-y: scroll; 
        }
    </style>
<?php
    $domain = parse_url(get_bloginfo('url'));
    $json = json_decode($this->api_call('http://quanticpost.com/advert_info/'.str_replace('.','^',$domain['host'])),true);
    $estimated_earnings = 0.00;
    $table_info = '<div style="font-size: 11px; border-bottom: 1px solid #545454; height: 40px; clear: both; margin-top: 100px;">
            <div style="width: 75px; float: left; ">
                <a href="#adsense_info" ng-click="orderByField=\'clicks\'; reverseSort = !reverseSort">Clicks
                <span ng-show="orderByField == \'clicks\'">
                <span ng-show="!reverseSort">^</span>
                <span ng-show="reverseSort">v</span>
                </span></a>
            </div>
            <div style="width: 75px; float: left;">
                <a href="#adsense_info" ng-click="orderByField=\'impressions\'; reverseSort = !reverseSort">Impressions
                <span ng-show="orderByField == \'impressions\'">
                <span ng-show="!reverseSort">^</span>
                <span ng-show="reverseSort">v</span>
                </span></a>
            </div>
            <div style="width: 75px; float: left;">
                <a href="#adsense_info" ng-click="orderByField=\'page_views\'; reverseSort = !reverseSort">Page Views
                <span ng-show="orderByField == \'page_views\'">
                <span ng-show="!reverseSort">^</span>
                <span ng-show="reverseSort">v</span>
                </span></a>
            </div>
            <div style="width: 75px; float: left;">
                <a href="#adsense_info" ng-click="orderByField=\'page_rpm\'; reverseSort = !reverseSort">Page RPM
                <span ng-show="orderByField == \'page_rpm\'">
                <span ng-show="!reverseSort">^</span>
                <span ng-show="reverseSort">v</span>
                </span></a>
            </div>
            <div style="width: 75px; float: left;">
                <a href="#adsense_info" ng-click="orderByField=\'impression_rpm\'; reverseSort = !reverseSort">Impressions RPM
                <span ng-show="orderByField == \'impression_rpm\'">
                <span ng-show="!reverseSort">^</span>
                <span ng-show="reverseSort">v</span>
                </span></a>
            </div>
            <div style="width: 75px; float: left;">
                <a href="#adsense_info" ng-click="orderByField=\'estimated_earnings\'; reverseSort = !reverseSort">Estimated Earnings
                <span ng-show="orderByField == \'estimated_earnings\'">
                <span ng-show="!reverseSort">^</span>
                <span ng-show="reverseSort">v</span>
                </span></a>
            </div>
            <div style="width: 75px; float: left;">
                <a href="#adsense_info" ng-click="orderByField=\'created\'; reverseSort = !reverseSort">Created
                <span ng-show="orderByField == \'created\'">
                <span ng-show="!reverseSort">^</span>
                <span ng-show="reverseSort">v</span>
                </span></a>
            </div>
            <div style="clear: both;"></div>
        </div>';
    $json_info = 'info: [';
	 foreach($json as $j){ 
	     $json_info .= '{';
        foreach($j as $f=>$k){
            if($f != 'id'){
                if(!is_numeric($k)){
                    $json_info .= $f.':"'.$k.'",';
			    } else {
			        $json_info .= $f.':'.$k.',';
			    }
            }
        	if($f == 'estimated_earnings'){
        		$estimated_earnings = floatval($k)+$estimated_earnings;
        	}
        }
        $json_info = substr($json_info,0,-1);
        $json_info .= '},';
	} 
	$json_info = substr($json_info,0,-1);
	$json_info .= ']';
	?>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.8/angular.min.js"></script>
	<script>
	    var app = angular.module('app', []);
        
        app.controller('MainCtrl', function($scope) {
          $scope.orderByField = 'clicks';
          $scope.reverseSort = false;
          
          $scope.data = {
            <?php if($json_info != 'info: ]'){ echo $json_info; } else { echo 'info: [{site:"NA",clicks:0,impressions:0,page_views:0,page_rpm:0.00,impression_rpm:0.00,active_view_viewable:0.00,estimated_earnings:0.00,created:"0000-00-00 00:00:00"}]'; } ?>
          };
        });
	</script>
	<a name="adsense_info"></a>
	<div style="margin-top: 20px; font-size: 20px; background: #FFA500; padding: 10px; color: #fff;">Total Earnings $<span style="color: #000;"><?php echo $estimated_earnings; ?></span></div>
    <article ng-app="app" ng-controller="MainCtrl">
        <?php echo $table_info; ?>
        
        <div style="border-bottom: 1px solid #ababab;" ng-repeat="sites in data.info|orderBy:orderByField:reverseSort" class="clear">
            <div style="width: 75px; float: left;">{{ sites.clicks }}</div>
            <div style="width: 75px; float: left;">{{ sites.impressions }}</div>
            <div style="width: 75px; float: left;">{{ sites.page_views }}</div>
            <div style="width: 75px; float: left;">{{ sites.page_rpm }}</div>
            <div style="width: 75px; float: left;">{{ sites.impression_rpm }}</div>
            <div style="width: 75px; float: left;">${{ sites.estimated_earnings }}</div>
            <div style="width: 170px; float: left;">{{ sites.created }}</div>
            <div class="clear"></div>
        </div>
    </article>
    <div style="font-size: 11px; margin-top: 20px;">To contact quanticpost.com with inquiries, please feel free to email support at <a href="mailto:taurean.wooley@gmail.com" style="padding: 5px; background: #FFA500; color: #000; text-decoration: none;">taurean.wooley@gmail.com</a></div>
    <input type="submit" name="submit" value="submit" style="margin-top: 30px;"/>
    <h2>Social Sharing Settings</h2>
    <div class="auto-share-options">
        <div class="info">
            <label style="font-size: 25px; font-weight: 10px;">Twitter</label>
            <input type="checkbox" name="tw_auto_social[twitter]" <?php if(isset($auto_social['twitter'])) echo 'checked'; ?> />
            <label>
                <label>Consumer Key</label>
                <input type="text" style="width: 100%" name="tw_auto_social_api[twitter][consumer]" value="<?php if(isset($tw_auto_social['twitter']['consumer'])) echo $tw_auto_social['twitter']['consumer']; ?>" />
                <label>Consumer Secret</label>
                <input type="text" style="width: 100%" name="tw_auto_social_api[twitter][consumer_key]" value="<?php if(isset($tw_auto_social['twitter']['consumer_key'])) echo $tw_auto_social['twitter']['consumer_key']; ?>" />
                <label>Access Token</label>
                <input type="text" style="width: 100%" name="tw_auto_social_api[twitter][access]" value="<?php if(isset($tw_auto_social['twitter']['access'])) echo $tw_auto_social['twitter']['access']; ?>" />
                <label>Access Token Secret</label>
                <input type="text" style="width: 100%" name="tw_auto_social_api[twitter][access_secret]" value="<?php if(isset($tw_auto_social['twitter']['access_secret'])) echo $tw_auto_social['twitter']['access_secret']; ?>" />
            </label>
            <div style="text-align: center;">
                <a href="http://quanticpost.com/purchase" target="_blank">Donate Now!</a>
            </div>
            <div class="twitter-icon"></div>
        </div>
    </div>
    <div class="share-info">
    	<h2></h2>
    	<div class="description"></div>
    </div>
   	<div id="show-information">
   		<div class="facebook">
   			<h2>Facebook API Walkthrough</h2>
   			<ol>
   				<li><strong>Go to and login at the following link </strong> <a href="https://developers.facebook.com/apps/" target="_blank">Facebook Development</a></li>
   				<li><strong>Click or create a new application</strong></li>
   				<li><strong>Follow the on-screen directions</strong></li>
   				<li><strong>Copy the client-id and paste into this screen under facebook</strong></li>
   			</ol>
   		</div>
   		<div class="twitter">
   			<h2>Twitter API Walkthrough</h2>
   			<ol>
   				<li><strong>Go to and login at the following link </strong> <a href="https://apps.twitter.com/" target="_blank">Twitter Apps</a></li>
   				<li><strong>Click or create a new application</strong></li>
   				<li><strong>Follow the on-screen directions</strong></li>
   				<li><strong>Click on the keys and access tokens tab</strong></li>
   				<li><strong>Copy the keys into their alotted spaces</strong></li>
   				<li><strong><i>Make sure that you give twitter the ability to write the content and do not over post on twitter or your account can be limited</i></strong></li>
   			</ol>
   		</div>
   		<div class="linkedin">
   			<h2>Linkedin API Walkthrough</h2>
   			<ol>
   				<li><strong>Go to and login at the following link </strong> <a href="https://www.linkedin.com/secure/developer" target="_blank">LinkedIn Development</a></li>
   				<li><strong>Click or create a new application</strong></li>
   				<li><strong>Follow the on-screen directions</strong></li>
   				<li><strong>Copy the keys in their alotted spaces</strong></li>
   			</ol>
   		</div>
   		<div class="google">
   			<h2>Google+ API Walkthrough</h2>
   			<ol>
   				<li><strong>Go to and login at the following link </strong> <a href="https://console.developers.google.com/project" target="_blank">Google+ Development</a></li>
   				<li><strong>Click or create a new application</strong></li>
   				<li><strong>Follow the on-screen directions</strong></li>
   				<li><strong>Click on the keys and access tokens tab</strong></li>
   				<li><strong>Copy the consumer key</strong></li>
   			</ol>
   		</div>
   	</div>
<?php
    $query = array(
            'post_type'         => TW_FEED_TITLE,
            'orderby'           => 'date',
            'order'             => 'DESC',
            'posts_per_page'    => '10',
            'meta_query'        => array(
                    array(
                            'key'   =>  'tw_social_option',
                            'value' =>  'true',
                        )
                )
        );
    $wp = new WP_Query($query);
?>
<style>
    .posts-left-process{
        width: 100%;
        clear: both;
    }
    
    .post-holder{
        width: 100%;
    }
    .post-holder > div{
        width: 30%;
        float: left;
    }
</style>
<div class="posts-left-process">
    <h1><?php echo $wp->found_posts; ?> Unprocessed Feeds</h1>
    <span style="padding: 10px; background: #000; color: #fff;">check and submit to remove from auto post</span>
    <?php 
        foreach($wp->posts as $p){ ?>
        <div class="post-holder">
            <div style="text-align: left;"><input type="checkbox" name="feed_process[<?php echo $p->ID; ?>]"/><?php echo rawurldecode($p->post_title); ?></div>
            <div>Received <?php echo date('M d,Y h:i:s',strtotime($p->post_date)); ?></div>
            <div style="background: #000; padding: 10px; border-radius: 20px;"><a href="<?php echo admin_url(); ?>?page=tw_send_social&id=<?php echo $p->ID; ?>">POST</a></div>
            <div style="clear"></div>
        </div>
    <?php    }   ?>
    <input type="submit" name="submit" value="submit" style="margin-top: 30px;"/>
</div>
   	<script>
		var options = document.getElementsByClassName('auto-share-options')[0].getElementsByClassName('info');
		var layout = document.getElementById('show-information').getElementsByTagName('div');
		for(var i = 0; i < layout.length; ++i){
			layout[i].style.display = 'none';
		}
		for(var i = 0; i < options.length; ++i){
			options[i].onmouseover = function(el){
				var l = document.getElementById('show-information').getElementsByTagName('div');
				if(el.target.getElementsByTagName('label')[0]){
					for(var j = 0; j < l.length; ++j){
						if(l[j].getAttribute('class') != el.target.getElementsByTagName('label')[0].innerHTML.toLowerCase()){
							l[j].style.display = 'none';
						} else {
							l[j].style.display = 'block';
						}
					}
				}
			}
		}
    </script>
    <div class="clear"></div>
    <div class="clear"></div>
    <input type="submit" name="submit" value="submit" style="margin-top: 30px;"/>
    <script>
    	var share_info = [{
    		'title'	: 'facebook',
    		'description' : 'follow these easy steps to get autoposting up and running'
    	}];
        var shares = document.getElementsByClassName('auto-share-options')[0].getElementsByTagName('input');
        
        function write_init(info){  }
        
        for(a = 0; a < shares.length; ++a){
            if(shares[a].getAttribute('type') == 'checkbox'){
                shares[a].onclick = function(el){
                    if(el.currentTarget.checked == true){
                        var twitter_info = el.currentTarget.parentNode.getElementsByTagName('input');
                        for(var i = 0; i < twitter_info.length; ++i){
                            twitter_info[i].value = '';
                            twitter_info[i].style.color = '#fff';
                        }
                    } else {
                        var twitter_info = el.currentTarget.parentNode.getElementsByTagName('input');
                        for(var i = 0; i < twitter_info.length; ++i){
                            twitter_info[i].value = 'Enter Info';
                            twitter_info[i].style.color = '#545454';
                        }
                    }
                }
            } else {
                if(shares[a].value == ''){
                    shares[a].value = 'Enter Info';
                    shares[a].style.color = '#545454';
                    shares[a].onfocus = function(el){
                        if(el.target.value == 'Enter Info'){
                            el.target.value = '';
                            el.target.style.color = '#fff';
                        }
                    }
                    shares[a].onblur = function(el){
                        if(el.target.value == ''){
                            el.target.value = 'Enter Info';
                            el.target.style.color = '#545454';
                        }
                    }
                }
            }
        }
        
        var doc = document.getElementsByTagName('input');
        for(var i = 0; i < 3; ++i){
            doc[i].onclick = function(el){ checkVariables(el); }
        }
        var social = false;
        function checkVariables(vars){
            for(var i = 0; i < 3; ++i){
                if(social == false){
                    social = true;
                    break;
                }
            }
        }
    </script>
</form>