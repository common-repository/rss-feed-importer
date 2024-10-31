<style>
    form input{
        padding: 10px;
        min-width: 500px;
    }
    form input[type=submit]{
        width: 100px;
        padding: 5px;
    }
    form > div{
        margin-bottom: 10px;
    }
    form > div > div{
        padding: 10px 5px;
        color: #fff;
    }
    form{
        background: #545454;
        padding: 10px;
        border-radius: 10px;
        width: 90%;
    }
    .category select{
        min-width: 500px;
        float: left;
    }
    .feed-lengths select{
    	width: 70px;
    	float: left;
    }
    .length-titles > div{
    	width: 75px;
    	float: left;
    }
    .clear{
        clear: both;
    }
</style>
<h1>Edit Feed Info for <span style="color: #ffa500"><?php echo $f['feed_name']; ?></span></h1>
<form action="" method="POST">
	<div>
		<div>Feed Name</div>
		<input type="text" name="feed_name" value="<?php echo $f['feed_name']; ?>"/>
	</div>
	<div>
		<div>Feed URL</div>
		<input type="text" name="feed_url" value="<?php echo $f['feed_url']; ?>"/>
	</div>
	<div class="category-select">
		<div>Category</div>
		<div>
		    <?php wp_dropdown_categories('selected='.$f['feed_category'].'&hierarchical=1&name=feed_category&hide_empty=0'); ?>
		    <div class="clear"></div>
		</div>
	</div>
	<div>
	    <div>Total Number of Articles to be stored (set to <strong>all</strong> to disable post deletes)</div>
	    <div>
	        <input type="text" name="total_feeds" value="<?php if(isset($f['total_feeds'])){ echo $f['total_feeds']; } else { echo 'all'; } ?>"/>
	    </div>
	</div>
	<div class="feed-lengths">
	    <div>Set total days to keep feeds</div>
	    <div class="length-titles">
	    	<div>Days</div>
	    	<div>Months</div>
	    	<div>Years</div>
	    </div>
	    <div>
    	    <select name="length-of-storage-days">
    	    <?php for($i = 0; $i <= 31; ++$i){?>
    	        <option value="<?php echo $i; ?>" <?php if(isset($f['length-of-storage-days']) && $i == $f['length-of-storage-days']) echo 'selected'; ?>><?php echo $i; ?></option>
    	    <?php }?>
    	    </select>
    	    <select name="length-of-storage-months">
    	       <?php for($i = 0; $i <= 12; ++$i){?>
    	        <option value="<?php echo $i; ?>" <?php if(isset($f['length-of-storage-months']) && $i == $f['length-of-storage-months']) echo 'selected'; ?>><?php echo $i; ?></option>
    	    <?php }?>
    	    </select>
    	    <select name="length-of-storage-years">
    	    <?php for($i = 0; $i <= 10; ++$i){?>
    	        <option value="<?php echo $i; ?>" <?php if(isset($f['length-of-storage-years']) && $i == $f['length-of-storage-years']) echo 'selected'; ?>><?php echo $i; ?></option>
    	    <?php }?>
    	    </select>
    	    <div class="clear"></div>
	    </div>
	</div>
	<div>
	    <div>Enable Images</div>
	    <div>
	        <input type="checkbox" name="feed_image_enabler" <?php if(isset($f['feed_image_enabler']) && $f['feed_image_enabler'] != 'false') echo 'checked'; ?> value="true"/>
	    </div>
	</div>
	<div>
	    <div>Keep best feeds</div>
	    <div>
	        <input type="checkbox" name="best_feeds" <?php if(isset($f['best_feeds']) && $f['best_feeds'] != 'false') echo 'checked'; ?> value="true"/>
	    </div>
	</div>
	<div>
	    <div>Active</div>
	    <div>
	        <input type="checkbox" name="active" <?php if(isset($f['active']) && $f['active'] == 'true') echo 'checked'; ?> value="true"/>
	    </div>
	</div>
	<input type="submit" name="submit" value="Submit Change"/>
</form>
<script>
	var days_info = document.getElementsByClassName('feed-lengths')[0].getElementsByTagName('select');
	
	for(var i = 0; i < days_info.length; ++i){
		days_info[i].id = i;
		days_info[i].onchange = function(el){
			var test_info = days_info[parseInt(el.currentTarget.id)+1];
			if(typeof test_info != 'undefined'){
				if(el.currentTarget.value > 30){
					el.currentTarget.value = 0;
					if(parseInt(test_info.value)+1 > 11){
						test_info.value = 0;
						days_info[parseInt(el.currentTarget.id)+2].value = parseInt(days_info[parseInt(el.currentTarget.id)+2].value)+1;
					    if(days_info[parseInt(el.currentTarget.id)+2].value == ''){
					        days_info[parseInt(el.currentTarget.id)+2].value = 10;
					    }
					} else {
						test_info.value = parseInt(test_info.value)+1;
					}
				} else if(el.currentTarget.value > 11 && el.currentTarget.getAttribute('name') == 'length-of-storage-months') {
					el.currentTarget.value = 0;
					if(parseInt(test_info.value)+1 > 10){
						test_info.value = 10;
					} else {
						++test_info.value;
					}
				}
			}
		}
	}
</script>