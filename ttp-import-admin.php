<?php
	global $wpdb;
	
	$status_str = '';
	$deleted = '';
	
	//tw_create_rss_feed();
	if(isset($_POST['remove'])){
	    $deleted = '<div class="total-post-deleted">Total Articles deleted '.flush_feeds().'</div>';
	}
	
	$this->delete_from_table();
    
    $validate = array();
    
    foreach($_POST as $k=>$p){
        if($k == 'feed_name'){
            $_POST['feed_name'] = trim($_POST['feed_name']);
            if($_POST['feed_name'] == ''){
                $validate[$k]['error'] = 'Cannot be left blank';
            }
        }
        if($k == 'feed_url'){
            $_POST['feed_url'] = trim($_POST['feed_url']);
            if($_POST['feed_url'] == ''){
                $validate[$k]['error'] = 'Cannot be left blank';
            } else if(preg_match("/(http(s)?:\/\/)?(\w+).(\w+)\.?(\w+)?/i",$_POST['feed_url']) == 0) {
                $validate[$k]['error'] = 'Invalid URL';
            }
        }
    }
    
	if(sizeof($validate) < 1 && isset($_POST['feed_name']) && $_POST['feed_name'] != ''){
		extract($_POST);
		
        $feed = get_option('rss_feeds');
        $feed = $this->sanatize($feed);
        
        //$feed_content = $_POST['feed_content'];
        //$feed_title = $_POST['feed-title'];
        //$feed_image = $_POST['feed-image'];
        //$feed_content .= '/'.$feed_title.'/'.$feed_image;
        
        if(strpos($feed,'"feed_name":"'.$feed_name.'"') === false && strpos($feed,'"feed_url":"'.str_replace('/','\/',$feed_url).'"') === false){
            if(isset($full_content) && $full_content != ''){
                $feed_layout = '{"feed_name":"'.$feed_name.'","feed_url":"'.$feed_url.'","feed_category":"'.$feed_category.'","full-content":"'.$full_content.'","feed-content":"'.$feed_content.'","active":"true"}';
            } else {
                $feed_layout = '{"feed_name":"'.$feed_name.'","feed_url":"'.$feed_url.'","feed_category":"'.$feed_category.'","active":"true"}';
            }
            
            if(isset($feed_image_enabler) && $feed_image_enabler == 'on'){
                $feed_layout = substr($feed_layout,0,-1).',"feed_image_enabler":"true"}';
            }
            
            $feed_layout = json_decode($feed_layout,true);
            $feed = json_decode($feed,true);
            
            $feed_holder = array();
            foreach($feed as $f){
                if($f['feed_name'] != ''){
                    $feed_holder[] = $f;
                }
            }
            
            $feed = $feed_holder;
            
            array_push($feed,$feed_layout);
            $feed = json_encode($feed);
            
            $feed_layout = json_encode($feed_layout);
            
            update_option('rss_feeds',$feed);
            
            $this->extract_info($feed_layout,$_POST);
            $status_str .= '<div style="padding: 10px; background: #fff;">All new feeds have been entered</div>';
        } else {
            $validate['feed_name']['error'] = 'Duplicate Feed';
            $validate['feed_url']['error'] = 'Duplicate Feed';
            $feeds = get_option('rss_feeds');
        }
	} else {
	    
	    if(isset($_POST['auto_delete']) && sizeof($_POST['auto_delete']) > 0){
            $json = json_decode(get_option('rss_feeds'),true);
            foreach($json as $k=>$f){
                if(isset($_POST['auto_delete'][$f['feed_name']])){
                    $json[$k]['auto_delete'] = 'true';
                } else {
                    $json[$k]['auto_delete'] = 'false';
                }
            }
            update_option('rss_feeds',json_encode($json));
        }
        
		$feeds = get_option('rss_feeds');
		update_option('rss_feeds',$this->sanatize($feeds));
		$feeds = get_option('rss_feeds');
	}
?>
<style>
    #tw-content-layout {
    	overflow: scroll;
    	height: 400px;
    	width: 100%;
    }
    
    .total-post-deleted {
    	margin-top: 5px;
    	background: #fff;
    	color: #545454;
    	border-left: #C70000 solid 5px;
    	padding: 5px;
    }
    
    #content-div {
    	display: none;
    }
    
    .donate {
    	padding: 90px;
    	clear: both;
    	text-align: center;
    }
    
    .feed-hint {
    	font-size: 11px;
    	padding: 3px;
    	background: #545454;
    	padding: 5px;
    	color: #fff;
    	margin-bottom: 10px;
    	border-radius: 5px;
    	clear: both;
    }
    
    form>div>div {
    	font-size: 14px;
    	margin-bottom: 10px;
    }
    
    .feed-option-holder {
    	clear: both;
    }
    
    .feed-option {
    	float: left;
    	width: 30%;
    }
    
    #feed_category {
    	font-size: 20px;
    	height: 45px;
    }
    
    .feed-option-holder {
    	background: #fff;
    	padding: 10px;
    	border-radius: 10px;
    }
    .error{
        color: #ff0000;
        font-size: 11px;
    }
    #savefeed{
        background: #000;
        color: #fff;
        font-weight: bold;
    }
    input[name="submit"] {
		border-radius: 10px;
		border: none;
		text-transform: uppercase;
		padding: 10px;
		color: #545454;
		margin-top: 10px;
	}
	input[name="get-content"] {
		background: #CF5300;
		color: #fff;
	}
	
	input {
		padding: 10px;
		font-size: 15px;
		border: none;
	}
	
	select {
		padding: 20px;
		font-size: 15px;
		border: none;
	}
	
	.header {
		clear: both;
	}
	
	.header>div {
		float: left;
		width: 25%;
	}
	
	.layout {
		clear: both;
	}
	
	.row {
		clear: both;
	}
	
	.row>div {
		float: left;
		width: 25%;
	}
	
	.header {
		background: #000;
		color: #fff;
		padding: 10px;
		height: 20px;
		margin-top: 15px;
	}
	#clicking{
	    margin: 20px 0px;
	}
	#clicking input{
	    border-radius: 10px;
	    box-shadow: 2px 2px 0px rgba(0,0,0,.5);
	}
	#content-div {
		clear: both;
		width: 100%;
		margin-bottom: 10px;
	}
	.full-content{
        background: #000;
        padding: 10px;
        margin-top: 10px;
    }
    .categories{
        padding: 5px;
        color: #fff;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 10px;
    }
    .expander .button{
        background: #fff;
        width: 100%;
        margin: 10px 0px;
        border: none;
        color: #000;
        font-weight: bold;
        font-size: 14px;
    }
    .tw-content-layout{
    	padding: 10px;
    }
    #content_select{
    	clear: both;
    }
    .menu_button{
    	float: left;
    	padding: 10px;
    }
    #layout-info{
    	display: none;
    	height: 200px;
    	overflow-y: scroll;
    }
    #layout-info div{
    	padding: 10px;
    }
    #layout-info > div:hover{
    	background: #545454;
    	color: #fff;
    }
    .options{
        margin-top: 10px; 
        border-radius: 5px;
        text-shadow: 0px 0px 7px rgba(0,0,0,.2);
        padding: 10px;
        border: 1px solid #545454;
        box-shadow: 0px 1px 5px rgba(0,0,0,.5);
    }
    .options-button{
        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#939393+0,474747+49,474747+49,000000+51,444444+100 */
        background: #939393; /* Old browsers */
        background: -moz-linear-gradient(top,  #939393 0%, #474747 49%, #474747 49%, #000000 51%, #444444 100%); /* FF3.6-15 */
        background: -webkit-linear-gradient(top,  #939393 0%,#474747 49%,#474747 49%,#000000 51%,#444444 100%); /* Chrome10-25,Safari5.1-6 */
        background: linear-gradient(to bottom,  #939393 0%,#474747 49%,#474747 49%,#000000 51%,#444444 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#939393', endColorstr='#444444',GradientType=0 ); /* IE6-9 */
        color: #fff;
    }
    .options-button-feeds{
        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#657a32+0,4a7c1d+50,135400+51,9ecb2d+100 */
        background: #657a32; /* Old browsers */
        background: -moz-linear-gradient(top, #657a32 0%, #4a7c1d 50%, #135400 51%, #9ecb2d 100%); /* FF3.6-15 */
        background: -webkit-linear-gradient(top, #657a32 0%,#4a7c1d 50%,#135400 51%,#9ecb2d 100%); /* Chrome10-25,Safari5.1-6 */
        background: linear-gradient(to bottom, #657a32 0%,#4a7c1d 50%,#135400 51%,#9ecb2d 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#657a32', endColorstr='#9ecb2d',GradientType=0 ); /* IE6-9 */        
        color: #fff;
    }
    .clear{
    	clear: both;
    }
</style>
<div style="padding: 10px;">
	<div>
	    <?php echo $deleted; ?>
		<?php echo $status_str; ?>
	</div>
	<h1>RSS Feed Importer</h1>
	<?php $this->basic_advertisements(); ?>
	<form action="" method="POST" id="rss-function">
		<div class="feed-option-holder">
			<div class="feed-option">
				<div>Feed Name <span class="error"><?php echo @$validate['feed_name']['error']; ?></span></div>
				<div>
					<input type="text" name="feed_name"
						value="<?php echo @$feed_name; ?>" />
				</div>
			</div>
			<div class="feed-option">
				<div>Feed Category</div>
				<div>
					<?php wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'feed_category', 'hierarchical' => true)); ?>
				</div>
			</div>
			<div class="feed-option">
				<div>Feed URL <span class="error"><?php echo @$validate['feed_url']['error']; ?></span></div>
				<div>
					<input type="text" name="feed_url" value="<?php echo @$feed_url; ?>" id="feed_url"/>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div>
			<div class="options options-button-feeds">Feed Image Enabler (download up to 2 images from feed | may be slow on some servers) <input type="checkbox" name="feed_image_enabler" <?php if(isset($feed_image_enabler) && sizeof($feed_image_enabler) > 0) echo 'checked'; ?>/></div>
		</div>
		<div>
		    
		    <div id="layout-info"></div>
			<div class="options options-button">Get Full Content (images on page downloaded as well) <input type="checkbox" name="full_content" id="full_content" <?php if(isset($feed_full_content) && sizeof($feed_full_content) > 0) echo 'checked'; ?>/></div>
            <div id="links"></div>
            <div id="content-div" style="display: none;"><div id="tw-content-layout"></div></div>
		</div>
</div>
<div style="clear: both;">
	<div>
		<input id="savefeed" type="submit" name="submit" value="Save Feed" />
	</div>
</div>
</form>
<?php
    $table = $this->create_feed_table($feeds);
?>
<div>
    <form action="" method="post">
    	<?php $table->display(); ?>
    	<input type="hidden" name="table_info" value="table"/>
    </form>
</div>
<?php $this->advertisements(); ?>
<script>
    function animate(obj,timeout,open){
    	var table = obj.parentNode.getElementsByClassName('table-holder')[0];
    	if(open == true){
    		table.style.height = (parseInt(table.style.height.replace('px',''))-20)+'px';
    		if(table.style.height.replace('px','') < 20){
    			table.style.height = '0px';
    		}
    		if(table.style.height.replace('px','') > 0){
    			setTimeout(function(){ animate(obj,timeout,true); },1);
    		} else {
    			clearTimeout(timeout);
    		}
    	} else {
    		table.style.height = (parseInt(table.style.height.replace('px',''))+20)+'px';
    		if(table.style.height.replace('px','') < table.getElementsByTagName('div')[0].offsetHeight){
    			setTimeout(function(){ animate(obj,timeout,false); },1);
    		} else {
    			clearTimeout(timeout);
    		}
    	}
    }
    
    var expander_show = function(obj){
    	this.obj = obj;
    	
    	this.obj.getElementsByClassName('button')[0].onclick = function(el){
    		var display = el.target.parentNode.getElementsByClassName('table-holder')[0];
    		display.style.overflow = 'hidden';
    		display.style.height = display.offsetHeight+'px';
    		
    		if(display.style.height.replace('px','') > 0){
    			animate(el.target,500,true);
    		} else {
    			animate(el.target,500,false);
    		}
    		return false;
    	}
    }
    
    var info = [];
    for(i = 0; i < document.getElementsByClassName('expander').length; ++i){
    	document.getElementsByClassName('expander')[i].getElementsByClassName('table-holder')[0].style.overflow = 'hidden';
    	document.getElementsByClassName('expander')[i].getElementsByClassName('table-holder')[0].style.height = '0px';
    	info[i] = new expander_show(document.getElementsByClassName('expander')[i]);
    }
    
    function removeElement(){
    	document.getElementById('rss-function').getElementsByTagName('input')[5].value = document.getElementById('rss-function').getElementsByTagName('input')[5].value.replace(this.parentNode.parentNode.getAttribute('data-info'),'');
    	document.getElementById('rss-function').getElementsByTagName('input')[5].value = document.getElementById('rss-function').getElementsByTagName('input')[5].value.replace('&'+this.parentNode.parentNode.getAttribute('data-info'),'');
    	
    	document.getElementById('rss-function').getElementsByTagName('input')[6].value += this.parentNode.parentNode.getAttribute('data-info')+'&';
    	var a = this.parentNode.parentNode.getElementsByTagName('div');
    	for(var i = 0; i < a.length; ++i){
    		a[i].style.textDecoration = 'line-through';
    	}
    	return false;
    }
    
    function createCORSRequest(method, url) {
        if (window.XMLHttpRequest){ // code for IE7+, Firefox, Chrome, Opera, Safari
            xhr=new XMLHttpRequest();
        } else { // code for IE6, IE5
            xhr=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhr.open(method, '<?php echo plugins_url().'/rss-feed-importer/curl_functions.php?url='; ?>'+url,true);
        return xhr;
    }
    
    function httpGet(theUrl,type,lay){
        var xmlhttp = createCORSRequest('GET',theUrl);
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                fetch_options(xmlhttp,type,lay);
            }
        }
        xmlhttp.send();
    }
    
    var menu_select = null;
    
    function StringToXML(oString) {
    	//code for IE
    	if (window.DOMParser){
            parser=new DOMParser();
            xmlDoc=parser.parseFromString(oString,"text/xml");
        } else {
            xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
            xmlDoc.async=false;
            xmlDoc.loadXML(oString);
        }
        return xmlDoc;
    }
    
    var selected_menu = 'title';
    var selected_page_url;
    function fetch_options(xmlhttp,type,lay){
    	if(type == 'initial'){
    		var lay = document.getElementById('tw-content-layout');
    		xml = StringToXML(xmlhttp.responseText);
    		lay.innerHTML = xml.getElementsByTagName('link')[2].innerHTML;
    		var total_length = xml.getElementsByTagName('link').length;
    		var total_xml = xml.getElementsByTagName('link');
    		
    		document.getElementById('layout-info').style.display = 'block';
    		
    		for(var i = 0; i < total_length+3; ++i){
    		    if(total_xml[i] != undefined){
    		        document.getElementById('layout-info').innerHTML += '<div>'+total_xml[i].innerHTML+'</div>';
    		    }
    		}
    		
    		var layout_info = document.getElementById('layout-info').getElementsByTagName('div');
    		for(var i = 0; i < layout_info.length; ++i){
    			layout_info[i].onclick = function(el){
    				if(selected_page_url != undefined && selected_page_url.innerHTML == el.currentTarget.innerHTML){
		        		el.currentTarget.style.background = 'none';
		        		el.currentTarget.style.color = '#000';
    				} else {
    					el.currentTarget.style.background = '#000';
    					el.currentTarget.style.color = '#fff';
    				}
    				if(selected_page_url != undefined){
    					selected_page_url.style.background = 'none';
    					selected_page_url.style.color = '#000';
    				}
		        	selected_page_url = el.currentTarget;
        			httpGet(el.currentTarget.innerHTML,'final');
		        }
    		}
    		httpGet(encodeURIComponent(xml.getElementsByTagName('link')[5].innerHTML),'finish');
    	} else {
    		var info = '';
    		var lay = document.getElementById('tw-content-layout');
    		lay.innerHTML = xmlhttp.responseText;
    		
    		if(xmlhttp.responseText.search('Moved Permanently') != -1){
    			var resp = xmlhttp.responseText.match(/(http:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?)\"/);
    			httpGet(encodeURIComponent(resp[0].replace('"','')),'finish');
    		} else {
    			lay.onmouseover = function(e){ selectContent(e,this,'in'); }
    			lay.onmouseout = function(e){ selectContent(e,this,'out'); }
    			
			    lay.onclick = function(e){
			    	info = getContentInfo(e,this);
				    document.getElementById('feed-content').value = info;
				}
    			
    			selected_menu = 'content';
    		}
    	}
    }
    
    function parseFunction(element,nodeName){
    	if(element.parentNode.getAttribute('id') == 'tw-content-layout'){
    		return nodeName;
    	} else {
    		className = element.parentNode.getAttribute('class');
    		if(element.parentNode.getAttribute('id') != null){ 
    			className = 'id|'+element.parentNode.getAttribute('id');
    		} else if(className != null) {
    			className = 'class|'+className;
    		} else {
    			className = element.tagName;
    		}
    		nodeName += "`"+parseFunction(element.parentNode,className);
    	}
    	return nodeName;
    }
    
    function getContentInfo(e,info){
    	return parseFunction(e.target,e.target.getAttribute('class'));
    }
    
    function selectContent(e,info,type){
    	var border = 'none';
    	if(type == 'in') border = '3px solid #90c8d3';
    	e.target.style.border = border;
    }
    
    function getLayout(url){
    	httpGet(encodeURIComponent(url),'initial');
    }
    
    var info = document.getElementById('rss-function').getElementsByClassName('row');
</script>