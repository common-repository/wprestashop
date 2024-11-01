<?php
function alertSetup(){
        global $error_wprestashop;
        if($error_wprestashop){?>
            <h4>
                <a href="<?php echo home_url('/wp-admin/admin.php?page=wprestashop/wprestashop.php'); ?>">
                    Can't connect prestashop, Plese setup first
                </a>
            </h4>
        <?php }
}

if(!get_option('wstyle')){
    add_action( 'wp_enqueue_scripts', 'wprestashop_style' );
}

add_action( 'wp_enqueue_scripts', 'wprestashop_widget' );
function wprestashop_widget(){
    wp_register_style( 'wprestashop', plugins_url( 'css/wprestashop.css', __FILE__ ));
    wp_enqueue_style( 'wprestashop' );  
	wp_enqueue_script( 'jquery' );
}
function wprestashop_style(){
    wp_register_style( 'wstyle', plugins_url( 'css/wstyle.css', __FILE__ ));
    wp_enqueue_style( 'wstyle' );  
}


function wprestashop_load_widgets() {
   	if (class_exists('PrestashopConnect')) {
        global $PrestashopConnect;
        $PrestashopConnect 	= new PrestashopConnect();
	}
    
    register_widget( 'wprestashop_category_Widget' );
    register_widget( 'wprestashop_product_Widget' );
    register_widget( 'wprestashop_userinfo_Widget' );
    register_widget( 'wprestashop_cart_Widget' );	
}



add_action( 'widgets_init', 'wprestashop_load_widgets' );
class wprestashop_category_Widget extends WP_Widget {
	function wprestashop_category_Widget() {
		$widget_ops = array( 'classname' => 'wprestashop', 'description' => __('Category prestashop widget', 'wprestashop') );
		$control_ops = array( 'id_base' => 'wprestashop-category-widget' );
		$this->WP_Widget( 'wprestashop-category-widget','WCategory', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
        global $PrestashopConnect,$error_wprestashop;
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		$type = $instance['type'];
        $root = $instance['root'];
        
        echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
        
        if($error_wprestashop){
            alertSetup();
        }else{
            $blockCategTree = $PrestashopConnect->GetWCategories();
            if(!count($blockCategTree)){
                echo "Can't load data";
            }else{
                
                if($type==1||$root=='yes'){
                    $this->renderList($blockCategTree,1);
                }elseif($type==1){
                    $this->renderList($blockCategTree);
                }
            }    
        }
		echo $after_widget;
	}
    
    function renderList($blockCategTree, $root='no'){ 
        
        ?>
  		<ul class="wcategories">
            <?php if($root=='yes'){?>
                <li class="root levelroot">
                    <a href="<?php echo $item['link'] ?>"><?php echo $blockCategTree['name'] ?></a>
                    <ul class="level0">
            <?php } ?>
            <?php foreach($blockCategTree['children'] as $item){ ?>
                <li>
                    <a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a>
                    <?php if($item['children']){ ?> 
                        <?php $this->CallbackList($item['children']) ?>
                    <?php } ?>
                </li>
            <?php } ?>
            <?php if($root=='yes'){?>
                    </ul>
                </li>
            <?php } ?>
            
		</ul>
        
    <?php }
    
    function CallbackList($lists, $level = 1){ 
        if(count($lists)){ ?>
            <ul class="level<?php echo $level ?>">
                <?php foreach($lists as $item){ ?>
                <li>
                    <a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a>
                    <?php if($item['children']){ $level++; ?> 
                        <?php $this->CallbackList($item['children'],$level) ?>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
        <?php }	
    }

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['type'] = (int)$new_instance['type'];
        $instance['root'] = $new_instance['root'];
        if(!$instance['type']) $instance['type'] =1;
		return $instance;
	}
	function form( $instance ) {
	   
        alertSetup();
		$defaults = array( 'title' => __('WCategory'),'type' => 1,'root' => 'no');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'root' ); ?>"><?php _e('Show Root:', 'example'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'root' ); ?>" name="<?php echo $this->get_field_name( 'root' ); ?>" class="widefat" style="width:100%;">
				<option value="yes" <?php if ( 'yes' == $instance['root'] ) echo 'selected="selected"'; ?>>Yes</option>
				<option value="no" <?php if ( 'no' == $instance['root'] ) echo 'selected="selected"'; ?>>No</option>
			</select>
		</p>

	<?php
	}
}
class wprestashop_userinfo_Widget extends WP_Widget {
	function wprestashop_userinfo_Widget() {
		$widget_ops = array( 'classname' => 'wprestashop', 'description' => __('Userinfo prestashop widget', 'wprestashop') );
		$control_ops = array( 'id_base' => 'wprestashop-userinfo-widget' );
		$this->WP_Widget( 'wprestashop-userinfo-widget','WUserinfo', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
        global $link,$error_wprestashop;
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
        
        if($error_wprestashop){
            alertSetup();
        }else{
        ?>
        <div class="wprestashop userinfo wloadding">

        </div>
        <script type="text/javascript">
            jQuery(window).load(function(){
                jQuery.ajax({
    				  type: "GET",
    				  url: '<?php echo $link->getPageLink('index.php'); ?>/wajax.php?task=callBlockUser',
    				  data: '',
    				  success:function(html){	
                            jQuery('.wprestashop.userinfo').removeClass('wloadding').html(html);
    				  },
                      error: function(){
                        //alert("Can't load data");
                      }
    		     });    
            });
        </script>
        <?php
        }
		echo $after_widget;
	}
    
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
	function form( $instance ) {
        alertSetup();
		$defaults = array( 'title' => __('WUserinfo'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
	<?php
	}
    
 }
class wprestashop_cart_Widget extends WP_Widget { 
	function wprestashop_cart_Widget() {
		$widget_ops = array( 'classname' => 'wprestashop', 'description' => __('Cart prestashop widget', 'wprestashop') );
		$control_ops = array( 'id_base' => 'wprestashop-cart-widget' );
		$this->WP_Widget( 'wprestashop-cart-widget','WCart', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
        global $link,$error_wprestashop;
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
        
        if($error_wprestashop){
            alertSetup();
        }else{ ?>
        
            <div class="wprestashop cart wloadding">
                
            </div>
            <script type="text/javascript">
                jQuery(window).load(function(){
                    jQuery.ajax({
        				  type: "GET",
        				  url: '<?php echo $link->getPageLink('index.php'); ?>/wajax.php?task=callBlockCart',
        				  data: '',
        				  success:function(html){
                                jQuery('.wprestashop.cart').removeClass('wloadding').html(html).removeClass('loading');
        				  },
                          error: function(){
                            //alert("Can't load data");
                          }
        		     });    
                });
            </script>
        <?php }
		echo $after_widget;
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
	function form( $instance ) {
        alertSetup();
		$defaults = array( 'title' => __('WCart'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
	<?php
	}
}





class wprestashop_product_Widget extends WP_Widget {
	function wprestashop_product_Widget() {
		$widget_ops = array( 'classname' => 'wprestashop', 'description' => __('Products prestashop widget', 'wprestashop') );
		$control_ops = array( 'id_base' => 'wprestashop-product-widget' );
		$this->WP_Widget( 'wprestashop-product-widget','WProducts', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
        global $PrestashopConnect,$link,$id_lang,$error_wprestashop;
		extract( $args );
        
        
		$title = apply_filters('widget_title', $instance['title'] );
		$limit = $instance['limit'];
        $images = $instance['images'];
        $desc = $instance['desc'];
        $type = $instance['type'];
        $price = $instance['price'];
        
        echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
        
        
        if($error_wprestashop){
            alertSetup();
        }else{
        
            if($type==0){
                $products = $PrestashopConnect->getWBestsellers();
                $page = 'best-sales.php';
                $text = 'All best sellers';
            }elseif($type==1){
                $products = $PrestashopConnect->GetWNewsproduct();
                $page = 'new-products.php';
                $text = 'All new products';
            }elseif($type==2){
                $products = $PrestashopConnect->getWFeatured();
            }
            if(!count($products)){
                echo "Can't load data";
            }else{
                echo '<ul class="wproducts">';
                foreach($products as $item){?>
                    <li class="wproduct-row">
                        <?php if($images){?>
                            <img src="<?php echo $link->getImageLink($item['link_rewrite'],$item['id_image'],'home') ?>" alt="<?php echo $item['name'] ?>" title="<?php echo $item['name'] ?>" />
                        <?php } ?>
                        <a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a>
                        <?php if($price){ ?>
                            <span class="wproduct-price"><?php echo $item['price']; ?></span>
                        <?php } ?>
                        <?php if($desc){?>
                            <div class="wprestashop-desc"><?php echo $item['description_short'] ?></div>
                        <?php } ?>
                        
                    </li>
                <?php }
                echo "</ul>";?>
                <?php if($page){ ?>
                    <p class="wprestashop-all"><a href="<?php echo $link->getPageLink($page,false,$id_lang); ?>" title="<?php echo $text ?>" class="button_large"><?php echo $text ?></a></p>
                <?php } 
                echo "<div class='clr'></div>";
                ?>
                
                <?php 
            }
        }
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = (int)$new_instance['limit'];
        $instance['images'] = (int)$new_instance['images'];
        $instance['desc'] = (int)$new_instance['desc'];
        $instance['type'] = (int)$new_instance['type'];
        $instance['price'] = (int)$new_instance['price'];
        
        
        if($instance['limit']<1) $instance['limit'] =5;
		return $instance;
	}
	function form( $instance ) {
        alertSetup();
		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('WProducts'),'limit' => 5,'type' => 0,'desc' => 0, 'images' => 1, 'price' => 1);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
        
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
            <label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e('Type products:'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat" style="width:100%;">
				<option value="0" <?php if ( 0 == $instance['type'] ) echo 'selected="selected"'; ?>>Best sellers</option>
				<option value="1" <?php if ( 1 == $instance['type'] ) echo 'selected="selected"'; ?>>New products</option>
                <option value="2" <?php if ( 2 == $instance['type'] ) echo 'selected="selected"'; ?>>Featured products</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Count:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $instance['limit']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'price' ); ?>"><?php _e('Show Price:'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'price' ); ?>" name="<?php echo $this->get_field_name( 'price' ); ?>" class="widefat" style="width:100%;">
				<option value="1" <?php if ( 1 == $instance['price'] ) echo 'selected="selected"'; ?>>Yes</option>
				<option value="0" <?php if ( 0 == $instance['price'] ) echo 'selected="selected"'; ?>>No</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'desc' ); ?>"><?php _e('Show description:'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" class="widefat" style="width:100%;">
				<option value="1" <?php if ( 1 == $instance['desc'] ) echo 'selected="selected"'; ?>>Yes</option>
				<option value="0" <?php if ( 0 == $instance['desc'] ) echo 'selected="selected"'; ?>>No</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'images' ); ?>"><?php _e('Show Image:'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'images' ); ?>" name="<?php echo $this->get_field_name( 'images' ); ?>" class="widefat" style="width:100%;">
				<option value="1" <?php if ( 1 == $instance['images'] ) echo 'selected="selected"'; ?>>Yes</option>
				<option value="0" <?php if ( 0 == $instance['images'] ) echo 'selected="selected"'; ?>>No</option>
			</select>
		</p>

	<?php
	}
}