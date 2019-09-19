<?php
/**
 * Plugin Name: mtf plugin
 * Description: Plugins demo
 * Plugin URI:  https://github.com/motunoff/mtf-plugin
 * Author URI:  https://motunoff.com
 * Author:      Motunoff
 * Version:     1.0.0
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */


if ( ! function_exists( 'mtf_setup_post_type_taxonomy' ) ) {
	
	function mtf_setup_post_type_taxonomy () {
		
		register_taxonomy( 'bets_type', [ 'bets' ], [
			'labels' => [
				'name' => 'Тип ставки',
				'singular_name' => 'Тип ставки',
				'search_items' => 'Найти тип ставки',
				'all_items' => 'Все типы ставок',
				'parent_item' => 'Родительская типа ставки',
				'parent_item_colon' => 'Родительская типа ставки',
				'edit_item' => 'Редатировать тип ставки',
				'update_item' => 'Обновить тип ставки',
				'add_new_item' => 'Редатировать тип ставки',
				'new_item_name' => 'Новый тип ставки',
				'menu_name' => 'Тип ставки',
			],
			'rewrite' => TRUE,
			'meta_box_cb' => FALSE,
			'show_admin_column' => TRUE,
		
		] );
		
		register_post_type( 'bets', [
			'labels' => [
				'name' => 'Ставки',
				'singular_name' => 'Ставки',
				'add_new' => 'Добавить ставку',
				'add_new_item' => 'Добавить ставку',
				'edit_item' => 'Редактировать ставку',
				'new_item' => 'Новая ставка',
				'view_item' => 'Посмотреть ставку',
				'search_items' => 'Найти ставку',
				'not_found' => 'Ставок не найдено',
				'not_found_in_trash' => 'В корзине ставок не найдено',
				'parent_item_colon' => '',
				'menu_name' => 'Ставки',
			
			],
			'public' => TRUE,
			'publicly_queryable' => TRUE,
			'show_ui' => TRUE,
			'show_in_menu' => TRUE,
			'has_archive' => FALSE,
			'show_in_nav_menus' => TRUE,
			'hierarchical' => TRUE,
			'menu_position' => 6,
			'menu_icon' => 'dashicons-sos',
			'query_var' => TRUE,
			'rewrite' => TRUE,
			'capability_type' => [ 'bet', 'bets' ],
			'map_meta_cap' => TRUE,
			'taxonomies' => [ 'bets_type' ],
			'supports' => [ 'title', 'editor', 'author' ],
		] );
		
	}
	
	
	add_action( 'init', 'mtf_setup_post_type_taxonomy' );
} // end mtf_setup_post_type_taxonomy()


/**
 * Add new terms to created taxonomy
 */
if ( ! function_exists( 'add_terms_bet_type_tax' ) ) {
	
	function add_terms_bet_type_tax () {
		
		wp_insert_term( 'ординар', 'bets_type', [
			'description' => '',
			'parent' => 0,
			'slug' => 'ordinar',
		] );
		
		wp_insert_term( 'экспресс', 'bets_type', [
			'description' => '',
			'parent' => 0,
			'slug' => 'express',
		] );
	}
	
} // end add_terms_bet_type_tax()


/**
 * Add new users roles with custom capabilities
 */
if ( ! function_exists( 'add_new_user_roles' ) ) {
	
	function add_new_user_roles () {
		
		// add capper cap, capper can add bets,edit own bets
		add_role( 'capper', 'Каппер', [
			'read' => TRUE,
			"level_0" => TRUE,
			'edit_bet' => TRUE,
			'edit_bets' => TRUE,
			'read_bet' => FALSE,
			'read_private_bets' => FALSE,
			'read_post' => FALSE,
			'read_private_posts' => FALSE,
		
		
		] );
		
		// add bets_moderator cap, bets_moderator can add bets,edit own bets,edit any other bets
		add_role( 'bets_moderator', 'Модератор', [
			'read' => TRUE,
			"level_0" => TRUE,
			'edit_bet' => TRUE,
			'edit_bets' => TRUE,
			'edit_others_bets' => TRUE,
			'edit_published_bets' => TRUE,
		
		
		] );
		
		// set cap to admin, admin have full cap
		$role = get_role( 'administrator' );
		
		$role->add_cap( 'edit_bet' );
		$role->add_cap( 'read_bet' );
		$role->add_cap( 'delete_bet' );
		$role->add_cap( 'edit_bets' );
		$role->add_cap( 'edit_others_bets' );
		$role->add_cap( 'publish_bets' );
		$role->add_cap( 'read_private_bets' );
		$role->add_cap( 'delete_bets' );
		$role->add_cap( 'delete_private_bets' );
		$role->add_cap( 'delete_published_bets' );
		$role->add_cap( 'delete_others_bets' );
		$role->add_cap( 'edit_private_bets' );
		$role->add_cap( 'edit_published_bets' );
		
	}
	
} // end add_new_user_roles()


/**
 * Remove the 'all', 'publish', 'future', 'sticky', 'draft', 'pending', 'trash'
 * views for non-admins
 */
add_filter( 'views_edit-bets', function ( $views ) {
	
	if ( current_user_can( 'manage_options' ) ) {
		
		return $views;
	}
	if ( current_user_can( 'edit_others_bets' ) ) {
		
		//bets_moderator user role btns
		$remove_views = [   'future', 'sticky',  'trash' ];
		
	} else {
		
		//capper user role btns
		$remove_views = [ 'all', 'publish','future', 'sticky', 'pending', 'trash', ];
	}
	
	
	foreach ( (array) $remove_views as $view ) {
		
		if ( isset( $views[ $view ] ) )
			unset( $views[ $view ] );
	}
	
	return $views;
	
} );


/**
 * Allow capper view only his bets
 */
if ( ! function_exists( 'set_query_bets_only_author' ) ) {
	
	function set_query_bets_only_author ( $wp_query ) {
		
		global $current_user;
		
		if ( is_admin() && ! current_user_can( 'edit_bets' ) ) {
			
			$wp_query->set( 'author', $current_user->ID );
			
		}
	}
	
	
	add_action( 'pre_get_posts', 'set_query_bets_only_author' );
} // end set_query_bets_only_author()


/* disable capper\moderator user role see view bets btn */
if ( ! function_exists( 'remove_row_actions_view_bets' ) ) {
	
	function remove_row_actions_view_bets ( $actions, $post ) {
		
		if ( $post->post_type === 'bets' && ! current_user_can( 'manage_options' ) ) {
			
			unset( $actions[ 'view' ] );
			
		}
		
		return $actions;
	}
	
	
	add_filter( 'page_row_actions', 'remove_row_actions_view_bets', 10, 2 );
	
} // end remove_row_actions_view_bets()


/* hide  preview-action bets btn  message, add and update link  for capper\moderator user role  */
if ( ! function_exists( 'hide_preview_bet_btn' ) ) {
	
	function hide_preview_bet_btn_css () {
		
		global $post;
		
		if ( $post->post_type === 'bets' && ! current_user_can( 'manage_options' ) ) {
			
			echo '<style type="text/css">#preview-action, #message a{display: none;}</style>';
			
		}
	}
	
	
	add_action( 'admin_head-post-new.php', 'hide_preview_bet_btn_css' );
	add_action( 'admin_head-post.php', 'hide_preview_bet_btn_css' );
	
} // end hide_preview_bet_btn_css()


/* disable change post type to publish for moderator user role  */
if ( ! function_exists( 'hide_publish_button' ) ) {
	
	function hide_publish_button () {
		
		global $post;
		if ( $post->post_type === 'bets' && ! current_user_can( 'manage_options' ) ) {
			?>
			<script type="text/javascript">
                window.onload = function () {
                    document.getElementById('post_status').disabled = true;
                }
			</script>
			<?php
			
		}
		
	}
	
	
	add_action( 'admin_head', 'hide_publish_button' );
}//end hide_publish_button()

/* activate plugin, run core functions */
if ( ! function_exists( 'mtf_install' ) ) {
	
	function mtf_install () {
		
		// register post type and taxonomy
		mtf_setup_post_type_taxonomy();
		
		// add new terms to bets_type taxonomy
		add_terms_bet_type_tax();
		
		// add new user roles
		add_new_user_roles();
		
		// flush rewrite rules for enabled post_type\taxonomy permalinks
		flush_rewrite_rules();
		
	}
	
	
	register_activation_hook( __FILE__, 'mtf_install' );
}

/* deactivate plugin, delete core functions */
if ( ! function_exists( 'mtf_deactivate' ) ) {
	
	function mtf_deactivate () {
		
		// delete bets post_type
		unregister_post_type( 'bets' );
		
		// delete bets_type taxonomy
		unregister_taxonomy( 'bets_type' );
		
		// delete user role capper
		remove_role( 'capper' );
		
		// delete user role bets_moderator
		remove_role( 'bets_moderator' );
		
		flush_rewrite_rules();
		
	}
	
	
	register_deactivation_hook( __FILE__, 'mtf_deactivate' );
} // end mtf_deactivate()
	