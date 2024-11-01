<?php
/*
Plugin Name: wp-postratings-my
Plugin URI: http://http://infinity.calenfretts.com/category/geek/wordpress/wp-postratings-my/
Description: Shows users their WP-PostRatings and allows filters.
Version: 3.6.1
Author: Calen Fretts
Author URI: http://infiniteschema.com
*/

/*  Copyright 2013  Calen Fretts  (email : calen@calenfretts.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

### Function: Get Ratings By User
/* [cmf 20090121]
*/
function get_ratings_user($rating_userid = 0, $r_rating_mr = null, $r_sortby_mr = 'rating_id', $r_orderby_mr = 'desc') {
	global $wpdb;

	$rating_userid = intval($rating_userid);

	$query_stmt = "";
	$query_stmt .= "SELECT rating_postid, rating_rating FROM $wpdb->ratings WHERE rating_userid = $rating_userid ";
	if($r_rating_mr) {
		$query_stmt .= "AND rating_rating = $r_rating_mr ";
	}
	$query_stmt .= "ORDER BY $r_sortby_mr $r_orderby_mr";
	//echo '<h2>' . $query_stmt . '</h2>'; // for testing
	// Check User ID From IP Logging Database
	$get_ratings = $wpdb->get_results($query_stmt);
	return ($get_ratings);
}

### Function: Show Ratings By Username Public Variables
/* [cmf 20090121]
*/
add_filter('query_vars', 'show_ratings_username_ratings_variables');
function show_ratings_username_ratings_variables($public_query_vars) {
	$public_query_vars[] = 'r_username_mr';
	$public_query_vars[] = 'r_rating_mr';
	$public_query_vars[] = 'r_sortby_mr';
	$public_query_vars[] = 'r_orderby_mr';
	return $public_query_vars;
}

### Function: Show Ratings By Username
/* [cmf 20090121]
*/
function wp_postratings_my($rating_username = null) {
	$returned = '';

	global $user_ID;
	$cur_filter = '';

	$r_username_mr = trim(addslashes(get_query_var('r_username_mr')));
	// validate/sanitize r_username_mr
	$r_username_mr = sanitize_user($r_username_mr);

	if ($rating_username || !$r_username_mr)
		$r_username_mr = null;
	else {
		$cur_filter .= '<b>Username:</b> ' . $r_username_mr . ' ';
		$rating_username = $r_username_mr;
	}

	$r_rating_mr = trim(addslashes(get_query_var('r_rating_mr')));
	//validate/sanitize r_rating_mr
	$r_rating_mr = intval($r_rating_mr);

	if (!$r_rating_mr)
		$r_rating_mr = null;
	else
		$cur_filter .= '<b>Rating:</b> ' . $r_rating_mr . ' ';

	$r_sortby_mr = trim(addslashes(get_query_var('r_sortby_mr')));
	// validate/sanitize r_sortby_mr
	$r_sortby_mr = sanitize_user($r_sortby_mr);

	if (!$r_sortby_mr)
		$r_sortby_mr = 'rating_id';
	else
		$cur_filter .= '<b>Sort by:</b> ' . $r_sortby_mr . ' ';

	$r_orderby_mr = trim(addslashes(get_query_var('r_orderby_mr')));
	// validate/sanitize r_orderby_mr
	$r_orderby_mr = sanitize_user($r_orderby_mr);

	if (!$r_orderby_mr)
		$r_orderby_mr = 'desc';
	else
		$cur_filter .= '<b>Order:</b> ' . $r_orderby_mr . ' ';


	if(!$rating_username) {
		if(!$user_ID || $user_ID == '' || $user_ID == 'Guest') {
			$returned .= '<h3>You must be <a href="../wp-login.php">logged in</a> to track your ratings.</h3>';
			return;
		}
		else {
			$rating_userid = $user_ID;
		}
	} else {
		$rating_userid = get_userdatabylogin($rating_username)->ID;
	}

	$get_ratings = get_ratings_user($rating_userid, $r_rating_mr, $r_sortby_mr, $r_orderby_mr);

	$returned .= '<h3>Your Ratings</h3>';
	$returned .= 'Filter Ratings: ';
	for ( $i = 1; $i <= 5; $i++) {
		$returned .= '<a href="?r_rating_mr=' . $i . '">' . $i . '</a> ';
	}
	$returned .= '<br /><i>Current Filters [<a href="?" title="clear filters">x</a>]:</i> ' . ($cur_filter ? $cur_filter : 'None') . '<br /><br />';

	if(!$get_ratings) {
		$returned .= 'None to display yet.';
	} else {
		foreach ($get_ratings as $get_rating) {
			$post_id = $get_rating->rating_postid;
			$post_link = get_permalink($post_id);
			$post_title = get_the_title($post_id);
			$rating_rating = $get_rating->rating_rating;
			$returned .= '<b><a href="' . $post_link . '">' . $post_title . '</a></b> [' . $rating_rating . ']<br />';
		}
	}

	return $returned;
}

function wp_postratings_my_shortcode_handler($atts, $content = null) {
	/*extract(shortcode_atts(array(
		'foo' => 'no foo',
		'bar' => 'default bar',
	), $atts));*/

	return wp_postratings_my();
}
add_shortcode('wp-postratings-my', 'wp_postratings_my_shortcode_handler');

?>