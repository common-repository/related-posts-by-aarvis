<?php
/*
* Plugin Name: Related Posts by Aarvis
* Plugin URI: http://www.aarvis.com
* Description: This Plugin allows you to show the related posts as per your choice. You have the flexibility to select posts based on the same category or tag or both.
	Maximum number of posts and heading are customizable.
* Version: 1.0.9
* Author: Subhash Bhaskaran
* Author URI: http://www.aarvis.com
*/
	
// create deafault settings on plugin activation
function RPA_create_options()
{
	add_option( 'RPA_show_categ', 'Y' );
	add_option( 'RPA_show_tag', 'Y' );
	add_option( 'RPA_show_cnt', '4' );
	add_option( 'RPA_show_nm', 'Related Posts' );
	add_option( 'RPA_title_size', '16' );
	add_option( 'RPA_title_colour', '#333' );
	add_option( 'RPA_thumb_size', 'relatedthumbsmall' );
	
	add_option( 'RPA_divHeight', '280' );
	add_option( 'RPA_divWidth', '100' );
	add_option( 'RPA_imageHeight', '100' );
	add_option( 'RPA_imageWidth', '100' );
	
}

// Delete settings on plugin de-activation
function RPA_delete_options()
{
	delete_option( 'RPA_show_categ' );
	delete_option( 'RPA_show_tag' );
	delete_option( 'RPA_show_cnt' );
	delete_option( 'RPA_show_nm' );
	delete_option( 'RPA_title_size' );
	delete_option( 'RPA_title_colour' );
	delete_option( 'RPA_thumb_size' );
	
	delete_option( 'RPA_divHeight' );
	delete_option( 'RPA_divWidth' );
	delete_option( 'RPA_imageHeight' );
	delete_option( 'RPA_imageWidth' );
}

// register plugin ctivation and de-activation hooks
register_activation_hook( __FILE__, 'RPA_create_options' );
register_deactivation_hook( __FILE__, 'RPA_delete_options' );

//main function to display related posts.
function RPA_show_rel_post($content)
{
	global $post;
	if(!is_singular(post)){
        return $content;
	}
		
		$RPA_bytag = get_option('RPA_show_tag');
		$RPA_bycateg = get_option('RPA_show_categ');
		$RPAcnt = get_option('RPA_show_cnt');
		$RPA_title_size = get_option('RPA_title_size');
		$RPA_thumb_size = get_option('RPA_title_size');
		
		if ($RPA_bytag == 'Y')
		{
			$RPA_tags = get_the_terms( $post_id, 'category' );
			if ( empty( $RPA_tags ) ) $RPA_tags = array();
			$RPA_tags_list    = wp_list_pluck( $RPA_tags, 'slug' );
		}
		
		if ($RPA_bycateg == 'Y')
		{
			$RPA_categs = get_the_terms( $post_id, 'category' );
			if ( empty( $RPA_categs ) ) $RPA_categs = array();
			$RPA_categs_list    = wp_list_pluck( $RPA_categs, 'slug' );
		}
		
			$RPA_related_args = array(
			  'post_type'      => 'post',
			  'posts_per_page' => $RPAcnt,
			  'post_status'    => 'publish',
			  'post__not_in'   => array( get_the_ID() ),
			  'orderby'        => 'rand',
			  'tax_query'      => array(
			  'relation' => 'OR',
				array(
				  'taxonomy' => 'category',
				  'field'    => 'slug',
				  'terms'    => $RPA_categs_list
				),
				array(
				  'taxonomy' => 'post_tag',
				  'fields'   => 'slug',
				  'terms'    => $RPA_tags_list
				),
			  )
			);
 
  
		$RPA_post_cont = $content . RPA_generate_content($RPA_related_args) ;
		return $RPA_post_cont;
		
}
//supporting function to generate the display content 
add_filter( 'the_content', 'RPA_show_rel_post' );

	function RPA_generate_content($RPA_args)
	{
		$RPA_my_query = new wp_query( $RPA_args );
			if( $RPA_my_query->have_posts() ) 
			{
				$RPA_post_cont = $RPA_post_cont . '<div ><div style = "font-size: ' . get_option('RPA_title_size') . 'px; color : ' . get_option('RPA_title_colour') . ' ">'.  get_option('RPA_show_nm') .  '</div>';
				while( $RPA_my_query->have_posts() ) 
				{
					$RPA_my_query->the_post(); 
					$RPA_perma = get_permalink($post->ID);
					$RPA_title = get_the_title($post->ID);
					$RPA_thumb = get_the_post_thumbnail($post->ID, array( 500,500));
					$RPA_post_cont = $RPA_post_cont . '<div class="' . get_option('RPA_thumb_size')  . '" ><a href="' .   $RPA_perma	  . '" rel="bookmark" title="' ;
					$RPA_post_cont = $RPA_post_cont . $RPA_title . '">' . $RPA_thumb . $RPA_title . '</a>' . '</div>' ;
				}
				$RPA_post_cont = $RPA_post_cont . '</div>';
			}
				// custom thumbnail style
			if (trim(get_option('RPA_thumb_size')) == 'relatedthumbcustom')
			{
				
				echo('<style>.relatedthumbcustom {margin: 0 1px 0 1px; float: left; } .relatedthumbcustom img {margin: 0 0 3px 0; padding: 0; width: ' . get_option('RPA_imageWidth') . 'px; height: ' . get_option('RPA_imageHeight') . 'px;}	.relatedthumbcustom a {color :#333; text-decoration: none; display:block; padding: 4px; width: '. get_option('RPA_divWidth') .'px; height: '. get_option('RPA_divHeight') .'px;}.relatedthumbcustom a:hover {background-color: #ddd; color: #000;}</style>');
			
			}
		return 	$RPA_post_cont ;
	}
// register plugin style sheet
wp_register_style( 'RPA_style', plugins_url( 'css/style.css', __FILE__ ), false, time() );
wp_enqueue_style( 'RPA_style' );

//register color picker
add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'my-script-handle', plugins_url('js/my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}


// Display admin menu
function RPA_top_menu()
	{
		add_menu_page('Related Posts - Aarvis', 'Related Posts - Aarvis', 'manage_options', __FILE__, 'RPA_render_list_page', 'dashicons-grid-view');
	}
	add_action('admin_menu','RPA_top_menu');
	

	
	
// call back funtion to render the admin page
function RPA_render_list_page()
{

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	$RPA_bytag = get_option('RPA_show_tag');
	$RPA_bycateg = get_option('RPA_show_categ');
	$RPA_cnt = get_option('RPA_show_cnt');
	$RPA_titlename = get_option('RPA_show_nm');
	$RPA_titlename = get_option('RPA_show_nm');
	$RPA_title_size = get_option('RPA_title_size');
	$RPA_title_colour = get_option('RPA_title_colour');
	$RPA_thumb_size = get_option('RPA_thumb_size'); 
	
	$RPA_divHeight = get_option('RPA_divHeight'); 
	$RPA_divWidth = get_option('RPA_divWidth'); 
	$RPA_imageHeight = get_option('RPA_imageHeight'); 
	$RPA_imageWidth = get_option('RPA_imageWidth'); 
	
	
	if (isset($_POST ['RPA_hdn_submit']) && ($_POST ['RPA_hdn_submit'] == 'Y'))
	{
		if ( isset( $_REQUEST['RPA_options_nonce'] ) ) 
		{
			if ( isset( $_REQUEST[ 'RPA_options_nonce' ] ) && wp_verify_nonce( $_REQUEST[ 'RPA_options_nonce' ], 'RPA_options_save' ) ) 
			{
   
				$RPA_bycateg = ($_POST ['RPA_chkCateg'] == 'on') ? "Y" : "N" ;
				$RPA_bytag = ($_POST ['RPA_chkTag'] == 'on') ? "Y" : "N" ;
				$RPA_cnt = isset($_POST ['RPA_cnt']) && !empty($_POST ['RPA_cnt']) ? $_POST ['RPA_cnt'] : "4" ;
				$RPA_cnt = (absint($RPA_cnt) == 0 ) ? 4 : $RPA_cnt ;
				$RPA_titlename = sanitize_text_field(isset($_POST ['RPA_titlename']) && !empty($_POST ['RPA_titlename'])  ? $_POST ['RPA_titlename'] : 'Related Posts') ;
				$RPA_title_size = isset($_POST ['RPA_title_size']) && !empty($_POST ['RPA_title_size']) ? $_POST ['RPA_title_size'] : "16" ;
				$RPA_title_colour = isset($_POST ['RPA_title_colour']) && !empty($_POST ['RPA_title_colour']) ? $_POST ['RPA_title_colour'] : "#333" ;
				$RPA_thumb_size = isset($_POST ['RPA_thumb_size']) && !empty($_POST ['RPA_thumb_size']) ? $_POST ['RPA_thumb_size'] : "relatedthumbsmall" ;
				
				$RPA_divHeight = isset($_POST ['RPA_divHeight']) && !empty($_POST ['RPA_divHeight']) ? $_POST ['RPA_divHeight'] : "280" ;
				$RPA_divWidth = isset($_POST ['RPA_divWidth']) && !empty($_POST ['RPA_divWidth']) ? $_POST ['RPA_divWidth'] : "100" ;
				$RPA_imageHeight = isset($_POST ['RPA_imageHeight']) && !empty($_POST ['RPA_imageHeight']) ? $_POST ['RPA_imageHeight'] : "100" ;
				$RPA_imageWidth = isset($_POST ['RPA_imageWidth']) && !empty($_POST ['RPA_imageWidth']) ? $_POST ['RPA_imageWidth'] : "100" ;
				
				update_option( 'RPA_show_categ', $RPA_bycateg );
				update_option( 'RPA_show_tag', $RPA_bytag );
				update_option ('RPA_show_cnt' , $RPA_cnt);
				update_option ('RPA_show_nm', $RPA_titlename);
				update_option ('RPA_title_size', $RPA_title_size);
				update_option ('RPA_title_colour', $RPA_title_colour);
				update_option ('RPA_thumb_size', $RPA_thumb_size);
				
				update_option ('RPA_divHeight', $RPA_divHeight);
				update_option ('RPA_divWidth', $RPA_divWidth);
				update_option ('RPA_imageHeight', $RPA_imageHeight);
				update_option ('RPA_imageWidth', $RPA_imageWidth);
				
				?>
				<div class="updated"><p><strong><?php _e('settings saved.', 'RPA-plugin' ); ?></strong></p></div>
				<?php
			}
			else
			{
				// Nonce could not be verified - bail
				wp_die( __( 'Invalid session or request. Please try againg', 'related-posts-by-aarvis' ), __( 'Error', 'related-posts-by-aarvis'  ), array(
				'response' 	=> 403,
				'back_link' => 'admin.php?page=' . 'related-posts-by-aarvis',
				) );
			}
					
		}
	}
	
		?>
	<h3><?php esc_attr_e('Related Posts Settings' , 'RPA-plugin' ); ?>  </h3><hr>
	
	<div><form name = "frm_RPA" method = "post">
				<div class="divTable">
				<div class="divTableBody">
				<div class="divTableRow">
				<input type = "hidden" name ="RPA_hdn_submit" value = "Y">
				<div class="divTableCell"><strong><?php esc_attr_e('Related post title' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type = "text" name ="RPA_titlename" value = "<?php esc_attr_e( $RPA_titlename , 'RPA-plugin'); ?>" required/></div>
				<div class="divTableCell"><?php esc_attr_e('This text will be displayed as the heading for related post. You can use the existing one "Related post" or your own heading. Example`: "You may interested in", "What others are reading" etc.' , 'RPA-plugin' ); ?></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><strong><?php esc_attr_e('Title font size' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type="number" min="8" max="100" name ="RPA_title_size" value = "<?php esc_attr_e( $RPA_title_size , 'RPA-plugin'); ?>" required/></div>
				<div class="divTableCell"><?php esc_attr_e('Specify the related post heading font size. Minimum allowed is 1 and maximum is 100.' , 'RPA-plugin' ); ?></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><strong><?php esc_attr_e('Title font color' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type="text" class="my-color-field" name ="RPA_title_colour" value = "<?php esc_attr_e( $RPA_title_colour , 'RPA-plugin'); ?>" required/></div>
				<div class="divTableCell"><?php esc_attr_e('Select the related post heading font colour.' , 'RPA-plugin' ); ?></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><strong><?php esc_attr_e('Thumbnail size' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type="radio" name ="RPA_thumb_size" value ="relatedthumbsmall" <?php if($RPA_thumb_size == 'relatedthumbsmall') { echo 'checked'; }; ?>><?php esc_attr_e('Small 100px' , 'RPA-plugin' ); ?></div>
				<div class="divTableCell"></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><strong><?php esc_attr_e('' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type="radio" name ="RPA_thumb_size" value = "relatedthumbmedium" <?php if($RPA_thumb_size == 'relatedthumbmedium') { echo 'checked'; };?>><?php esc_attr_e('Medium 150px' , 'RPA-plugin' ); ?></div>
				<div class="divTableCell"></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><strong><?php esc_attr_e('' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type="radio" name ="RPA_thumb_size" value = "relatedthumblarge" <?php if($RPA_thumb_size == 'relatedthumblarge') { echo 'checked'; };?>><?php esc_attr_e('Large 200px' , 'RPA-plugin' ); ?></div>
				<div class="divTableCell"></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><strong><?php esc_attr_e('' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type="radio" name ="RPA_thumb_size" value = "relatedthumbexlarge" <?php if($RPA_thumb_size == 'relatedthumbexlarge') { echo 'checked'; }; ?>><?php esc_attr_e('Extra Large 250px' , 'RPA-plugin' ); ?></div>
				<div class="divTableCell"></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><strong><?php esc_attr_e('' , 'RPA-plugin' ); ?> </strong> </div><div class="divTableCell"> <input type="radio" name ="RPA_thumb_size" value = "relatedthumbcustom" <?php if($RPA_thumb_size == 'relatedthumbcustom') { echo 'checked'; }; ?>><?php esc_attr_e('Custom' , 'RPA-plugin' ); ?>
				<br><?php esc_attr_e('Block  Height' , 'RPA-plugin' ); ?><input type="number" min="1" max="1000" style="width: 5em" name ="RPA_divHeight" value = "<?php esc_attr_e( $RPA_divHeight , 'RPA-plugin')?>" required/>
				<br><?php esc_attr_e('Block  Width' , 'RPA-plugin' ); ?><input type="number" min="1" max="1000" style="width: 5em" name ="RPA_divWidth" value = "<?php esc_attr_e( $RPA_divWidth , 'RPA-plugin')?>" required/>
				<br><?php esc_attr_e('Image  Height' , 'RPA-plugin' ); ?><input type="number" min="1" max="1000" style="width: 5em" name ="RPA_imageHeight" value = "<?php esc_attr_e( $RPA_imageHeight , 'RPA-plugin')?>" required/>
				<br><?php esc_attr_e('Image  Width' , 'RPA-plugin' ); ?><input type="number" min="1" max="1000" style="width: 5em" name ="RPA_imageWidth" value = "<?php esc_attr_e( $RPA_imageWidth , 'RPA-plugin')?>" required/>
				</div>
				<div class="divTableCell"><?php esc_attr_e('Always set the height of display block grater than image size to accomodate the heading.', 'RPA-plugin' ); ?> </div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><b><?php esc_attr_e('Show posts by Tags' , 'RPA-plugin' ); ?> </b></div><div class="divTableCell"><input type = "checkbox" name ="RPA_chkTag" <?php if($RPA_bytag == 'Y') { echo 'checked';} ?>></div>
				<div class="divTableCell"><?php esc_attr_e('Check this option if you want to show related post based on post tags.', 'RPA-plugin' ); ?> </div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><b><?php esc_attr_e('Show posts by Category', 'RPA-plugin' ); ?> </b></div><div class="divTableCell"><input type = "checkbox" name ="RPA_chkCateg" <?php if($RPA_bycateg == 'Y') { echo 'checked';} ?>></div>
				<div class="divTableCell"><?php esc_attr_e('Check this option if you want to show related post based on post category.', 'RPA-plugin' ); ?></div>
				</div>
				<div class="divTableRow">
				<div class="divTableCell"><b><?php esc_attr_e('Number of Posts to show', 'RPA-plugin' ); ?></b></div><div class="divTableCell"><input type="number" min="1" max="10" name ="RPA_cnt" value = "<?php esc_attr_e( $RPA_cnt , 'RPA-plugin')?>" required/></div>
				<div class="divTableCell"><?php esc_attr_e('Specify the number of posts to be shown. Minimum allowed number is 1 and Maximum allowen number is 10.', 'RPA-plugin' ); ?></div>
				</div>
				<div class="divTableRow">
				<?php wp_nonce_field( 'RPA_options_save', 'RPA_options_nonce' ); ?>
				<div class="divTableCell"><input type = "submit" class="button-primary" id="RPA_btnSubmit" value = "<?php esc_attr_e('Update Record' , 'RPA-plugin'); ?>"></div>
				<div class="divTableCell"></div>
				</div>
				</div>
				</div>
		</form>
	</div>
	<?php
}
?>