<?php
function wpjam_get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $class="wp-post-image"){

	$post_thumbnail_src = wpjam_get_post_thumbnail_src($post, $size, $crop);

	if($post_thumbnail_src){

		$size = wpjam_get_dimensions($size);
		extract($size);

		$hwstring = image_hwstring($width, $height);

		return '<img src="'.$post_thumbnail_src.'" alt="'.the_title_attribute(array('echo'=>false)).'" class="'.$class.'"'.$hwstring.' />';
	}else{
		return false;
	}
}

function wpjam_get_post_thumbnail_src($post=null, $size='thumbnail', $crop=1){

	if($post_thumbnail_uri = wpjam_get_post_thumbnail_uri($post)){
		$size = wpjam_get_dimensions($size);
		extract($size);
		$post_thumbnail_src = apply_filters('wpjam_thumbnail', $post_thumbnail_uri, $width, $height, $crop);
		
		return $post_thumbnail_src;
	}else{
		return false;
	}
}

//清理缓存
add_action('save_post','wpjam_clear_thumb_cache');
function wpjam_clear_thumb_cache($post_id){
	wp_cache_delete($post_id,'post_thumbnail_uri');
}

function wpjam_get_post_thumbnail_uri($post=null){
	$post = get_post($post);
	if(!$post)	return false;
	
	$post_id = $post->ID;

	$post_thumbnail_uri = wp_cache_get($post_id,'post_thumbnail_uri');

	if($post_thumbnail_uri === false){
		if(has_post_thumbnail($post_id)){
			$post_thumbnail_uri =  wp_get_attachment_url(get_post_meta($post_id,'_thumbnail_id',true));
		}elseif(false && $post_thumbnail_uri = apply_filters('wpjam_pre_post_thumbnail_uri',false, $post)){
			// do nothing
		}elseif($first_img = get_post_first_image(do_shortcode($post->post_content))){
			/*$pre = apply_filters('pre_qiniu_remote',false,$first_img);
			$img_type = strtolower(pathinfo($first_img, PATHINFO_EXTENSION));
			if($pre == false && wpjam_qiniutek_get_setting('remote') && get_option('permalink_structure') && strpos($first_img, LOCAL_HOST)===false && strpos($first_img, CDN_HOST) === false){
				$img_type = ($img_type == 'png')?$img_type:'jpg';
				$first_img = CDN_HOST.'/qiniu/'.$post_id.'/image/'.md5($first_img).'.'.$img_type;
			}*/
			$post_thumbnail_uri = $first_img;
		}elseif(false && $post_thumbnail_uri = apply_filters('wpjam_post_thumbnail_uri',false, $post)){
			//do nothing
		}else{
			$post_thumbnail_uri = wpjam_get_default_thumbnail_uri();
		}
		wp_cache_set($post_id, $post_thumbnail_uri, 'post_thumbnail_uri', 6000);
	}
	return $post_thumbnail_uri;
}

function wpjam_get_default_thumbnail_uri(){
	//return apply_filters('wpjam_default_thumbnail_uri',wpjam_qiniutek_get_setting('default'));

	// refer http://www.16sucai.com/tag.php?tag=%E7%A7%91%E6%8A%80%E8%83%8C%E6%99%AF
	//$ind = rand(1, 17);
	$ind = rand(1, 8);
	return BRUNO_MAGIC_TOOLS_PLUGIN_URL."post_default_covers/$ind.jpg";
}

//copy from image_constrain_size_for_editor
function wpjam_get_dimensions($size){
	global $content_width, $_wp_additional_image_sizes;

	$width = 0;
	$height = 0;

	if ( is_array($size) ) {
		$width = $size[0];
		$height = $size[1];
	}
	elseif ( $size == 'thumb' || $size == 'thumbnail' || $size == 'post-thumbnail' ) {
		$width = intval(get_option('thumbnail_size_w'));
		$height = intval(get_option('thumbnail_size_h'));

		// last chance thumbnail size defaults
		if ( !$width && !$height ) {
			$width = 128;
			$height = 96;
		}
	}
	elseif ( $size == 'medium' ) {
		$width = intval(get_option('medium_size_w'));
		$height = intval(get_option('medium_size_h'));
		// if no width is set, default to the theme content width if available
	}
	elseif ( $size == 'large' ) {
		// We're inserting a large size image into the editor. If it's a really
		// big image we'll scale it down to fit reasonably within the editor
		// itself, and within the theme's content width if it's known. The user
		// can resize it in the editor if they wish.
		$width = intval(get_option('large_size_w'));
		$height = intval(get_option('large_size_h'));
		if ( intval($content_width) > 0 )
			$width = min( intval($content_width), $width );
	} elseif ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) && in_array( $size, array_keys( $_wp_additional_image_sizes ) ) ) {
		$width = intval( $_wp_additional_image_sizes[$size]['width'] );
		$height = intval( $_wp_additional_image_sizes[$size]['height'] );
		if ( intval($content_width) > 0 && 'edit' == $context ) // Only in admin. Assume that theme authors know what they're doing.
			$width = min( intval($content_width), $width );
	}
	// $size == 'full' has no constraint
	else {
		//没了
	}

	return compact('width','height');
}

if(!function_exists('get_post_first_image')){
	function get_post_first_image($post_content){
		preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', $post_content, $matches);
		if($matches && $matches[1]){
			return $matches[1][0];
		}else{
			return false;
		}
	}
}


//使用七牛缩图 API 进行裁图
add_filter('wpjam_thumbnail','wpjam_get_qiniu_thumbnail',10,4);
function wpjam_get_qiniu_thumbnail($img_url, $width=0, $height=0, $crop=1, $quality='',$format=''){
	$timthumb_url = BRUNO_MAGIC_TOOLS_PLUGIN_URL.'lib/timthumb.php';

	if($width || $height){
		$arg = array();
		$arg['src']	= $img_url;
		$arg['zc']	= 0;
		if($crop)	$arg['zc']	= 1;
		if($width)	$arg['w']	= $width;
		if($height)	$arg['h']	= $height;

		$img_url = add_query_arg($arg,$timthumb_url);
	}

	return $img_url;
}