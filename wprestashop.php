<?php
/**
 * Plugin Name: WPrestashop Widget
 * Plugin URI: http://websideas.com
 * Description: Integrate Wordpress and Prestashop  
 * Version: 1.0
 * Author: Websideas 
 * Author URI: http://websideas.com
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
 

define( 'DS', DIRECTORY_SEPARATOR );
global $path,$error_wprestashop;

$pathreal = str_replace("/", DS, get_option('wsite'));
if(substr($pathreal, -1)==DS ){
    $pathreal = substr($pathreal,0,strlen($pathreal)-1);
}

if(!file_exists('..'.DS.''.'wp-directory.php')){
    if (!copy(dirname (__FILE__).DS.'wp-directory.php','..'.DS.''.'wp-directory.php')){
        admin_error_copy();
    }
}

$path = dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.$pathreal;

$pathcheck = $pathreal.DS.'config'.DS.'config.inc.php';



if(file_exists(dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.$pathcheck)){
    $path = dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.$pathreal;
}elseif(file_exists(dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.$pathcheck)){
    $path = dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.$pathreal;
}elseif(file_exists(dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.$pathcheck)){
    $path = dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.$pathreal ;
}else{
    $error_wprestashop = 1;
}



include_once(dirname (__FILE__).DS.'widget.php');

if(!$error_wprestashop){
    include_once(dirname (__FILE__).DS.'connect.php');       
}else{
    add_action('admin_notices', 'my_admin_notice');
}



function admin_error_copy(){?>
    <div class="error fade">
       <p>
            <b>Can't copy or read file, Please check permission </b>
       </p>
    </div>
<?php }
function my_admin_notice(){ ?>
    <div class="error fade">
       <p>
            <b>Can't connect with prestashop, Please check path prestashop. </b>
            <a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=wprestashop/wprestashop.php">Settings</a>
       </p>
    </div>
<?php }

if(is_admin()){
    add_action('admin_head','wprestashop_admin');
    session_start();
    $_SESSION['token'] = md5('wprestashop'.rand());

}


function wprestashop_admin(){
	wp_register_style( 'wadmin', plugins_url( 'css/wadmin.css', __FILE__));
    wp_enqueue_style( 'wadmin' );
    wp_enqueue_script( 'jquery' );
    wp_register_script( 'wconfirm_js', plugins_url( 'js/wconfirm/jquery.wconfirm.js', __FILE__ ));
    wp_enqueue_script( 'wconfirm_js' );
    wp_register_script( 'wconfirm_effects', plugins_url( 'js/wconfirm/jquery.effects-1.8.17.js', __FILE__ ));
    wp_enqueue_script( 'wconfirm_effects' );
    wp_register_script( 'wconfirm_effects', plugins_url( 'js/wconfirm/jquery.effects-1.8.17.js', __FILE__ ));
    wp_enqueue_script( 'wconfirm_effects' );
    wp_register_style( 'wconfirm_style', plugins_url( 'js/wconfirm/jquery.wconfirm.css', __FILE__));
    wp_enqueue_style( 'wconfirm_style' );
    
    wp_register_script( 'mousewheel', plugins_url( 'js/fancybox/jquery.mousewheel-3.0.2.pack.js', __FILE__ ));
    wp_enqueue_script( 'mousewheel' );
	wp_register_script( 'fancybox', plugins_url( 'js/fancybox/jquery.fancybox-1.3.1.js', __FILE__ ));
    wp_enqueue_script( 'fancybox' );
    wp_register_style( 'fancybox_style', plugins_url( 'js/fancybox/jquery.fancybox-1.3.1.css', __FILE__));
    wp_enqueue_style( 'fancybox_style' );    
    
    
}



// create custom plugin settings menu
add_action('admin_menu', 'wprestashop_create_menu');

function wprestashop_create_menu() {
	add_menu_page('WPrestashop Plugin Settings', 'WPrestashop', 'administrator', __FILE__, 'wprestashop_settings_page',plugins_url('/images/icon.png', __FILE__));
	add_action( 'admin_init', 'wprestashop_settings' );
}


function wprestashop_settings() {
	register_setting( 'wprestashop-settings-group', 'wsite' );
    register_setting( 'wprestashop-settings-group', 'wstyle' );
}

function wprestashop_settings_page() { 
    
?>




<div class="wrap">
    <h2 class="wprestashop-heading">WPrestashop <small><a target="_blank" href="http://websideas.com/">websideas</a></small></h2>
    <style type="text/css">
        #wprestashop{float: right;}
        .form-table img{vertical-align: middle;}
        form#wprestashop-form{float: left;}
        h2.wprestashop-heading{padding-left: 75px;background: url(<?php echo plugins_url('/images/wi.png', __FILE__) ?>) no-repeat center left ;}
        p.gotit{padding-left: 30px; background: url(<?php echo plugins_url('/images/icon-rating.png', __FILE__) ?>) no-repeat center left ;}
        p.gotit a{text-decoration: none;}
        img#selectpath{cursor: pointer;}
    </style>
    <?php 
        if(!$error_wprestashop){?>
            <div class="updated below-h2" id="message">
                <p>Connect successful</p>
            </div>
        <?php } ?>
        <div id="wprestashop" class="metabox-holder has-right-sidebar">
			<div id="side-info-column" class="inner-sidebar">
				<div id="categorydiv" class="postbox ">
					<div class="handlediv" title="Click to toggle"></div>
					<h3 class="hndle">Do you like this Plugin?</h3>
					<div class="inside"> 
                        <p>This plugin is primarily developed, maintained, supported and documented by <a href="http://websideas.com">websideas</a> with a lot of love & effort. Any kind of contribution would be highly appreciated. Thanks!</p>
                        
                        <p class="gotit">
                            <a target="_blank" href="http://wordpress.org/extend/plugins/wprestashop/">
                                Give it a good rating on WordPress.org.
                            </a>
                        </p>
                        
						<div style="text-align:center;">
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                <input type="hidden" name="cmd" value="_s-xclick"/>
                                <input type="hidden" name="hosted_button_id" value="6GGNLFJ96AMY6"/>
                                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
                                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"/>
                            </form>
						</div>
					</div>
				</div>
			</div>
            
			<div id="side-info-column" class="inner-sidebar">
				<div id="categorydiv" class="postbox ">
					<div class="handlediv" title="Click to toggle"></div>
					<h3 class="hndle">Support</h3>
					<div class="inside"> 
                        <p>If you have problem can contact me via</p>
                        <p>
                            Skype: <a href="skype:cuongdv.ict?chat">cuongdv.ict</a><br />
                            Gtalk: <a href="mailto:websideas.corp@gmail.com">websideas.corp@gmail.com</a>
                        </p>
					</div>
				</div>
			</div>
            
            
        </div>
    <form method="post" id="wprestashop-form" action="options.php">
        <?php settings_fields('wprestashop-settings-group'); ?>
        <?php $options = get_option('ozh_sample'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Path Prestashop</th>
                <td>
                    <input type="text" name="wsite" id="wsite" value="<?php echo get_option('wsite'); ?>" size="30" />
                    <a id="selectpath" href="<?php echo home_url('/wp-directory.php') ?>?token=<?php echo $_SESSION['token'] ?>" title="Directory select">
                        <img src="<?php echo plugins_url('/images/directory.png', __FILE__) ?>" />  
                    </a>
                    <a class="button rbutton" id="testpath" href="#">Test path</a>
                    <img class="wloadding" style="display: none;" alt="loading" src="<?php echo home_url('/') ?>wp-admin/images/wpspin_light.gif"/>

                </td>
            </tr> 
            <tr valign="top">
                <th scope="row">Style in Frontpage</th>
                <td>
                    <select name="wstyle" style="width: 100px;">
                        <option value="0" <?php if(!get_option('wstyle')){ echo ' selected="selected" ';} ?>>Yes</option>
                        <option value="1" <?php if(get_option('wstyle')){ echo ' selected="selected" ';} ?>>No</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
        <script type="text/javascript">
            jQuery(document).ready(function(){
     			jQuery("#selectpath").fancybox({
    				'autoScale'			: true,
                    'scrolling'         : 'auto',
    				'transitionIn'		: 'none',
    				'transitionOut'		: 'none',
    				'type'				: 'iframe'
    			});

                
                jQuery("a#testpath").click(function(){
                    jQuery('img.wloadding').show();
                    var data = {
                		action: 'checkpath_action',
                		url: jQuery('input#wsite').val()
                	};
                    jQuery.ajax({
        				  type: "POST",
        				  url: ajaxurl,
        				  data: data,
        				  success:function(html){
        				        jQuery('img.wloadding').hide();
                                if(html=='1')   
                                    msg = 'Connect successful';
                                else
                                    msg = "<span class='errorconect'>Can not contect prestashop !</span>";
                                jQuery("body").wconfirm({
                                    showclose:      false,
                                    width:          "500px",
                                    showheader:     true,
                					message: 		msg,
                                    showok:         true,
                                    ok:             "OK",
                                    title:          "Message",
                					callbackFinish: function(){
                						jQuery('.box-message').effect("clip", { mode:'show' });
                					}
                                });
        				  }
        		     });
                    return false;
                });
            });
        </script>
    </form>
</div>
<?php } 

add_action('wp_ajax_checkpath_action', 'checkpath_action_callback');
function checkpath_action_callback() {

    $pathreal = $_POST['url'];
    $pathreal = str_replace("/", DS,$pathreal);
    if(substr($pathreal, -1)==DS ){
        $pathreal = substr($pathreal,0,strlen($pathreal)-1);
    }
    $path = dirname (__FILE__) .DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS.$pathreal;
      
    if(file_exists($path.DS.'config'.DS.'config.inc.php')){
        echo 1;
    }else{echo 0;}
	die();
}