<?php
// Creating the widget 
class tw_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        // Base ID of your widget
        'tw_widget', 
        
        // Widget name will appear in UI
        __('TW RSS Feed Widget', 'tw_widget_domain'), 
        
        // Widget description
        array( 'description' => __( 'TW Rss Feed Widget', 'tw_widget_domain' ), ) 
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        
        $string = '[feed_searches title_only="true" ';
        $category_url = '';
        foreach($instance as $k=>$a){
        	if($k == 'category'){
        		$k = 'category_name';
        		$category_url = get_page_by_title($a);
        	}
        	$string .= $k.'="'.$a.'" ';
        }
        
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
        if(get_option('tw_auto_create_pages') != 'true'){
            echo $args['before_title'] . $title . $args['after_title'];
        } else {
            if(isset($category_url->guid) && strlen($category_url->guid) > 3){
                echo $args['before_title'] .'<a href="'.$category_url->guid.'" style="padding: 5px; width: 100%; margin-bottom: 10px; font-size: 25px;">'. $title .'</a>'. $args['after_title'];
            } else {
                echo $args['before_title'] . $title . $args['after_title'];
            }
        }
        
        echo __( '', 'tw_widget_domain' );
        
        $string .= ' source="widget"';
        $string .= ']';
        echo do_shortcode($string);
        echo $args['after_widget'];
    }
		
    // Widget Backend 
    public function form( $instance ) {
    	$category_list = get_categories();
    	
        $fields = array('title','advertise','total_feeds','category','tw_image_size','images_only');
        foreach($instance as $k=>$t){
            ${$k} = $t;
        }
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'tw_widget_domain' );
        }
        $tw_unique = (isset($instance['tw_unique']))?$instance['tw_unique']:'';
        $tw_image_size = (isset($instance['tw_image_size']))?$instance['tw_image_size']:'';
        $images_only = (isset($instance['images_only']))?$instance['images_only']:'';
        // Widget admin form
        ?>
            <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            <input class="widefat" id="<?php echo $this->get_field_id( 'advertise' ); ?>" name="<?php echo $this->get_field_name('advertise'); ?>" type="checkbox" <?php if(isset($advertise) && esc_attr( $advertise ) == 'true'){ echo 'checked'; } ?> />
            <label for="<?php echo $this->get_field_id('advertise'); ?>"><?php _e('Advertise (check to start advertisements)'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name('category'); ?>">
            	<?php foreach($category_list as $k=>$a){ ?>
            	<option <?php if(isset($category) && $a->name == $category){ echo 'selected'; } ?> value="<?php echo $a->name; ?>"><?php echo $a->name; ?></option>
            	<?php } ?>
            </select>
            <label>
                Images Only
            </label>
            <div>
                <input class="widefat" id="<?php echo $this->get_field_id( 'images_only' ); ?>" name="<?php echo $this->get_field_name('images_only'); ?>" type="checkbox" <?php if(esc_attr( $images_only ) == 'on'){ echo 'checked'; } ?> />
            </div>
            <label for="<?php echo $this->get_field_id('tw_image_size'); ?>"><?php _e('Image Dimensions'); ?></label>
            <div style="margin-bottom: 10px;">
            <input class="widefat" id="<?php echo $this->get_field_id( 'tw_image_size' ); ?>" name="<?php echo $this->get_field_name('tw_image_size'); ?>" type="radio" <?php if(esc_attr( $tw_image_size ) == 'square'){ echo 'checked'; } ?> value="square" /> Square <br/>
            <input class="widefat" id="<?php echo $this->get_field_id( 'tw_image_size' ); ?>" name="<?php echo $this->get_field_name('tw_image_size'); ?>" type="radio" <?php if(esc_attr( $tw_image_size ) == 'elongated'){ echo 'checked'; } ?> value="elongated" /> Elongated (rectangle) 
            </div>
            <label for="<?php echo $this->get_field_id('total_feeds'); ?>"><?php _e('Total Feeds to Display'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'total_feeds' ); ?>" name="<?php echo $this->get_field_name('total_feeds'); ?>" type="number" value="<?php echo esc_attr( $total_feeds ); ?>" />
            <div>
            <label for="<?php echo $this->get_field_id('tw_unique'); ?>"><?php _e('Unique Posts'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'tw_unique' ); ?>" name="<?php echo $this->get_field_name('tw_unique'); ?>" type="checkbox" <?php if(esc_attr( $tw_unique ) == 'on'){ echo 'checked'; } ?> />
            </div>
            </p>
        <?php 
    }
	
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['images_only'] = ( ! empty( $new_instance['images_only'] ) ) ? strip_tags( $new_instance['images_only'] ) : '';
        $instance['tw_unique'] = ( ! empty( $new_instance['tw_unique'] ) ) ? strip_tags( $new_instance['tw_unique'] ) : '';
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['advertise'] = ( ! empty( $new_instance['advertise'] ) ) ? strip_tags( 'true' ) : 'false';
        $instance['total_feeds'] = ( ! empty( $new_instance['total_feeds'] ) ) ? strip_tags( $new_instance['total_feeds'] ) : '';
        $instance['category'] = ( ! empty( $new_instance['category'] ) ) ? strip_tags( $new_instance['category'] ) : '';
        $instance['tw_image_size'] = ( ! empty( $new_instance['tw_image_size'] ) ) ? strip_tags( $new_instance['tw_image_size'] ) : '';
        return $instance;
    }
} // Class wpb_widget ends here
