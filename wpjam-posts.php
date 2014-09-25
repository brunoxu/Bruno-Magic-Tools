<?php
function wpjam_related_post_posts_join($posts_join){
	global $wpdb;
	return "INNER JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
}

function wpjam_related_post_posts_where($posts_where){
	global $wpdb;
	$term_taxonomy_ids = array();
	$term_cats = get_the_terms(get_the_ID(), 'category');
	if($term_cats==false) {
		$term_cats = array();
	}

	$term_tags = get_the_terms(get_the_ID(), 'post_tag');
	if($term_tags==false) {
		$term_tags = array();
	}

	$terms = array_merge($term_tags,$term_cats);

	if($terms){
		foreach ($terms as $term){
			$term_taxonomy_ids[]=$term->term_taxonomy_id;
		}
	}else{
		trigger_error(get_the_title(). '（ID:'.get_the_ID().'）没有任何 tag！',E_USER_NOTICE);
		return false;
	}

	$term_taxonomy_ids = array_unique($term_taxonomy_ids);
	$term_taxonomy_ids = implode(",",$term_taxonomy_ids);
	return $posts_where . " AND tr.term_taxonomy_id IN ({$term_taxonomy_ids}) AND {$wpdb->posts}.ID != ".get_the_ID();

}

function wpjam_related_post_posts_groupby($posts_groupby){
	return " tr.object_id";
}

function wpjam_related_post_posts_orderby($posts_orderby){
	global $wpdb;
	return " cnt DESC, {$wpdb->posts}.post_date_gmt DESC";
}

function wpjam_related_post_posts_fields($posts_fields){
	return $posts_fields.", count(tr.object_id) as cnt";
}

add_action('save_post','wpjam_delete_related_posts_cache');
function wpjam_delete_related_posts_cache($post_id){
	wp_cache_delete($post_id,'related_posts_query');
}

function wpjam_get_related_posts($number=5, $args=array()){

	$related_posts_query = wp_cache_get(get_the_ID(),'related_posts_query');
	if( $related_posts_query === false) {

		$post_types = apply_filters('wpjam_related_posts_post_types',array(get_post_type()));

		add_filter('posts_join',	'wpjam_related_post_posts_join');
		add_filter('posts_where',	'wpjam_related_post_posts_where');
		add_filter('posts_groupby',	'wpjam_related_post_posts_groupby');
		add_filter('posts_orderby',	'wpjam_related_post_posts_orderby');
		add_filter('posts_fields',	'wpjam_related_post_posts_fields');

		$related_posts_query = new WP_Query(array('post_type'=>$post_types,'posts_per_page'=>$number));

		remove_filter('posts_join',		'wpjam_related_post_posts_join');
		remove_filter('posts_where',	'wpjam_related_post_posts_where');
		remove_filter('posts_groupby',	'wpjam_related_post_posts_groupby');
		remove_filter('posts_orderby',	'wpjam_related_post_posts_orderby');
		remove_filter('posts_fields',	'wpjam_related_post_posts_fields'); 

		wp_cache_set(get_the_ID(), $related_posts_query, 'related_posts_query', 36000);
	}

	return wpjam_get_post_list($related_posts_query,$args);
}

function wpjam_related_posts($number=5, $args=array()){
	if($output = wpjam_get_related_posts($number, $args)){
		echo $output;
	}
}

function wpjam_get_post_list($wpjam_query,$args){
	
	$defaults = array('class'=>'', 'thumb' => true, 'size' => 'thumbnail', 'crop'=> true, 'thumb_class'=>'wp-post-image','number_per_row'=>5);
	$args = wp_parse_args($args, $defaults);
	extract($args, EXTR_SKIP );

	if($thumb)			$class		= $class.' has-thumb';
	if($class)			$class		= ' class="'.$class.'"';
	if(is_singular())	$post_id	= get_the_ID();

	$output = '';
	$i = 0;

	if($wpjam_query->have_posts()){
		while($wpjam_query->have_posts()){
			$wpjam_query->the_post();

			$i++;
			$li_class = '';

			if($i%$number_per_row == 0){
				$li_class = ' class="last"';
			}

			$li = '';

			if($thumb){ 
				$li .=	wpjam_get_post_thumbnail(null, $size, $crop, $thumb_class)."\n";		
				$li .=	'<span>'.get_the_title().'</span>';
			}else{
				$li .= get_the_title();
			}

			if(!is_singular() || (is_singular() && $post_id != get_the_ID())) {
				$li =	'<a href="'.get_permalink().'" title="'.the_title_attribute(array('echo'=>false)).'">'.$li.'</a>';
			}
			$output .=	'<li'.$li_class.'>'.$li.'</li>'."\n";
		}

		$output = '<ul'.$class.'>'."\n".$output.'</ul>'."\n";

	}else{
		$output = false;
	}

	wp_reset_query();
	return $output;	
}

