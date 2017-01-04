<?php
/*
  Plugin Name: Give a Beer One
  Plugin URI: http://ciprianturcu.com/give-a-beer-one/
  Description: A plugin that allows user to appreciate the current blog by clicking a beer widget
  Version: 1.0
  Author: turcuciprian
  Author URI: http://ciprianturcu.com
  License: GPLv2 or later
  Text Domain: countdown-timer-one
 */
require_once 'abExport.php';
   //Admin scripts and styles
   add_action('wp_enqueue_scripts', 'gabo_enqueueAll');
   //Admin scripts and styles callback
   function gabo_enqueueAll()
   {
     gabo_Exists('gabo_customStyle', 'style.css', 'style',array(),'plugin');
     wp_enqueue_script('jquery');
       gabo_Exists('gabo_customScript', 'script.js', 'script',array(),'plugin');
       $inlineScript ="
       var siteUrl = '".site_url()."';
       var pluginUrl = '".plugin_dir_url(__FILE__)."';
       var userIP = '';
       jQuery.getJSON(\"http://jsonip.com/?callback=?\", function (data) {
        userIP = data.ip;
    });
       ";
       wp_add_inline_script( 'gabo_customScript', $inlineScript);
   }



   if(!function_exists('gabo_Exists')){
     function gabo_Exists($name, $path, $type,$dependencies = array(),$exportType)
     {
       $fileExists = false;

       if($exportType==='theme'){
         $file = get_template_diregabory_uri().'/'.$path;
       }else{
         $file = plugin_dir_url(__FILE__).$path;
       }
         $plugin_file_headers = @get_headers($file);
         if (!$plugin_file_headers || strpos($plugin_file_headers[0], '404') > 0) {
             //file does not exist
           $fileExists = false;
         } else {
             //file exists if a plugin path
           $fileExists = true;
         }
       //inside theme path file existance ?
       // Custom Script
       if ($fileExists) {
           if ($type === 'style') {
               wp_register_style($name, $file);
               wp_enqueue_style($name);
           } else {
               wp_register_script($name, $file, $dependencies);
               wp_enqueue_script($name);
           }
       }
     }
   }




 /**
  * Adds gabo_widget widget.
  */
 class gabo_widget extends WP_Widget {

 	/**
 	 * Register widget with WordPress.
 	 */
 	function __construct() {
 		parent::__construct(
 			'gabo_widget', // Base ID
 			esc_html__( 'Give a Beer One', 'text_domain' ), // Name
 			array( 'description' => esc_html__( 'Settings for the give a beer widget', 'text_domain' ), ) // Args
 		);
 	}

 	public function widget( $args, $instance ) {
 		echo $args['before_widget'];
 		if ( ! empty( $instance['title'] ) ) {
 			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
 		}
    $pluginPath = plugin_dir_url(__FILE__);
    $beerImg =$pluginPath."/img/style1.png";
    ?>
    <div class="gaboWidget">
      <img src="<?php echo $beerImg;?>" alt="Beer image">
      <p>
        Click Beer to
        <br/>
        Give beer
      </p>

    </div>
    <?php
 		echo $args['after_widget'];
 	}

 	/**
 	 * Back-end widget form.
 	 *
 	 * @see WP_Widget::form()
 	 *
 	 * @param array $instance Previously saved values from database.
 	 */
 	public function form( $instance ) {

    $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'text_domain' );
 		?>
 		<p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
 		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
 		</p>

 		<?php
 	}

 	/**
 	 * Sanitize widget form values as they are saved.
 	 *
 	 * @see WP_Widget::update()
 	 *
 	 * @param array $new_instance Values just sent to be saved.
 	 * @param array $old_instance Previously saved values from database.
 	 *
 	 * @return array Updated safe values to be saved.
 	 */
 	public function update( $new_instance, $old_instance ) {



 		$instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

 		return $instance;
 	}

 } // class Foo_Widget

 // register Foo_Widget widget
function gabo_register_widget() {
    register_widget( 'gabo_widget' );
}
add_action( 'widgets_init', 'gabo_register_widget' );
//
//
// REST
//
//
if(!function_exists('gaboRoutesInit')){
  add_action('rest_api_init', 'gaboRoutesInit');
  function gaboRoutesInit($generalArr){
    register_rest_route('gabo', '/update',array('methods' => 'POST','callback' => 'gaboRestCallback','args' => array()));
  }
}
function gaboRestCallback(){
  $userIp = getenv('HTTP_CLIENT_IP')?:
getenv('HTTP_X_FORWARDED_FOR')?:
getenv('HTTP_X_FORWARDED')?:
getenv('HTTP_FORWARDED_FOR')?:
getenv('HTTP_FORWARDED')?:
getenv('REMOTE_ADDR');

  //check if ip already gave beers:
  $args = ['post_title'=>$userIp,'post_type'=>'beers'];
  $the_query = new WP_Query( $args );
  if($the_query->found_posts===0){
    //add beer data
    $content = "Info about when the beer was given:
    Date:".date('Y-m-d')."
    Time:".date('H:i:s');

    $my_post = array(
      'post_title'    => wp_strip_all_tags( $userIp ),
      'post_content'  => $content,
      'post_type'   => 'beers',
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_category' => array( 8,39 )
    );

    // Insert the post into the database
    $postID = wp_insert_post( $my_post );

    $returnArr['total'] = $the_query->found_posts;
  }else{
    $returnArr['total'] = 'aaa';
  }
  return $returnArr;
}
