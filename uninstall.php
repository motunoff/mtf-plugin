<?php


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

// delete all plugin data
$post_type = 'bets';
global $wpdb;
$result = $wpdb->query(
	$wpdb->prepare( "
            DELETE posts,pt,pm
            FROM wp_posts posts
            LEFT JOIN wp_term_relationships pt ON pt.object_id = posts.ID
            LEFT JOIN wp_postmeta pm ON pm.post_id = posts.ID
            WHERE posts.post_type = %s
            ",
		$post_type
	)
);

return $result !== FALSE;
