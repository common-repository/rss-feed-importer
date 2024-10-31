<?php 
    $this->get_post_images($post->ID,$post->post_content);
    $info = get_post_meta($post->ID);
    
    $image_holder = json_decode($info['tw_images'][0],true);
?>
<style>
    .image-holder {
        width: 100px;
        float: left;
        margin: 10px;
    }
    .image-holder img {
        width: 100%;
    }
    .img-tag-holder{
        border: 5px solid #ababab;
        overflow: hidden;
        width: 100%;
        height: 100px;
    }
    .image-holder-featured{
        float: left;
    }
    .image-holder-featured .img-tag-holder{
        float: none;
        border: 5px solid #ababab;
        width: 250px; height: 250px;
    }
    .clear {
        clear: both;
    }
</style>
<div style="margin-bottom: 10px; padding: 10px; border-bottom: 3px solid #ababab;">
    Mark for non-auto-delete <input type="checkbox" name="tw_stored_feed" value="<?php echo $post->ID; ?>" <?php if(isset($info['tw_stored_feed'])) echo 'checked'; ?> />
</div>
<div>
    <?php 
    $carousel = (isset($info['tw_carousel'][0]))?explode(',',$info['tw_carousel'][0]):array();
    
    foreach($carousel as $k=>$f){
        $carousel[$k] = str_replace('|',',',$f);
    }
    
    if(isset($featured_image) && $featured_image != ''){ ?>
    <div class="image-holder-featured">
        <div class="img-tag-holder"><?php echo $this->get_post_image($post->ID,$post->post_content); ?></div>
        <div style="padding: 5px; background: #545454; color: #fff;">Carousel Image <input type="checkbox" name="tw_carousel_images[featured]" value="featured" <?php if(in_array('featured',$carousel)){ echo 'checked="true"'; } else { echo 'checked="false"'; } ?> style="float: right; margin-top: 2px;"/></div>
    </div>
<?php } 
    foreach($image_holder as $k=>$j){
        
        $url = parse_url($j);
        $dir = wp_upload_dir();
        $dir = $dir['baseurl'];
        $image_info = explode('/',$url['path']);
        $image_info = $image_info[sizeof($image_info)-1];
    ?>
    <div class="image-holder">
        <div class="img-tag-holder"><img src="<?php echo $dir.'/tw-rss-feeds/'.$url['host'].'/'.$image_info; ?>" style="width: 100%;"/></div>
        <div style="padding: 5px; background: #545454; color: #fff; width: 100px;">Delete <input type="checkbox" name="tw_images[<?php echo $k; ?>]" value="<?php echo $k; ?>" style="float: right; margin-top: 2px;" <?php if(isset($j['delete'])){ echo 'checked="true"'; } ?>/></div>
        <div style="padding: 5px; background: #ababab; color: #fff; width: 100px; height: 40px;">Carousel Image <input type="checkbox" name="tw_carousel_images[<?php echo $k; ?>]" value="<?php echo $k; ?>" <?php if(in_array($j,$carousel)){ echo 'checked="true"'; } ?> style="float: left; margin-top: 2px;"/></div>
    </div>
    <?php } ?>
    <div class="clear"></div>
    <div>
        <h3>Feed Tag</h3>
        <input type="text" name="tw_rss_feed_options" value="<?php if(isset($info['tw_rss_feed_options'])){ echo @$info['tw_rss_feed_options'][0]; } ?>" style="width: 100%;"/>
    </div>
    <div>
        <h3>Get All Images</h3>
        <input type="checkbox" name="tw_all_images" value="yes" <?php if(isset($info['tw_all_images'][0])){ echo 'checked'; } ?> />
    </div>
    <div>
        <h3>Tag for Adult Content <span style="color: #ff0000;">XXX</span> <div style="font-size: 11px;">(used in conjunction with seo and advertising)</div></h3>
        <input type="checkbox" name="tw_adult" value="yes" <?php if(isset($info['tw_adult'][0])){ echo 'checked'; } ?> />
    </div>
    <div style="padding: 10px; background: #000; color: #fff;">
        <a href="<?php echo admin_url(); ?>?page=tw_update_feed&ID=<?php echo $info['tw_rss_feed_options'][0]; ?>">Update RSS feed</a>
    </div>
    <input type="hidden" value="" id="undelete" name="tw_undelete_images"/>
</div>
<script>
    var image = document.getElementsByClassName('image-holder');
    var id = document.getElementById('undelete');
    for(var i = 0; i < image.length; ++i){
        image[i].id = i;
        image[i].onclick = function(el){
            var str = id.getAttribute('value');
            if(el.target.getAttribute('checked') == 'true'){
                el.target.setAttribute('checked','false');
                id.setAttribute('value',(el.currentTarget.id+','));
            } else {
                el.target.setAttribute('checked','true');
                id.setAttribute('value',str.replace(el.currentTarget.id+',',''));
            }
        }
    }
</script>