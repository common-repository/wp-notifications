<?php
/*
Plugin Name:Wordpress Social Network
Plugin URI: http://vbsocial.com/wordpress-social-network
Description: This plugin will show notifications for all events.
Author: vBsocial.com
Version: 9.0
Author URI: http://www.vbsocial.com
*/
// ----------------------------------------------------------------------------------

//for avatar
define('BASE_FILE', 0);																
define('AVTR_FILE', 1);
define('CROP_FILE', 2);
define('TYPE_GLOBAL',	'G');
define('TYPE_LOCAL',	'L');
define('UNKNOWN', 'unknown@gravatar.com');		
define('FALLBACK', 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536');										
define('BLANK', 'blank');																
define('UPLOAD_TRIES', 4);		// Number of attempts to create a unique file name.
define('SCALED_SIZE', '50');
error_reporting(0);
//ajax 
add_action( 'wp_ajax_nopriv_ln_ajax_process', 'ln_ajax_process' );
add_action( 'wp_ajax_ln_ajax_process', 'ln_ajax_process' );

             
function ln_ajax_process(){	
	global $wpdb;

	$userinfo = wp_get_current_user();
	$options = get_option('ln_options');

	if($_REQUEST['a'] == 'pr'){
		ln_pm_reply_action($_REQUEST['i'],$_REQUEST['t']);
		print($_REQUEST['i'].",".$_REQUEST['h']);
		exit(0);
	}
	
	if ((!isset($userinfo) || empty($userinfo->ID)) && $_REQUEST['do']) { // User has been logged out
		print("logout");
		exit(0);
	}
	if ($_REQUEST['do'] == 'ln_getcount') {
		@error_reporting(E_NONE); // Turn off error reporting completely
	
	//	if (!is_member_of($userinfo, explode(",",$options['ln_allowed_usergroups']))) return;
	
		if ($options['enable_reply'] || $options['enable_award'] || $options['enable_comment'] || $options['enable_pm'] || 
			$options['enable_friend'] || $options['enable_moderation']) {
			
			if ($userinfo->ID < 1) die();
			
			if (isset($_REQUEST['act'])){
				if($_REQUEST['act'] == 'pm_delete'){
					ln_pm_delete_action($_REQUEST['pm_id']);
				}
				else if($_REQUEST['act'] == 'pm_reply'){
					ln_pm_reply_action($_REQUEST['pm_id'],$_REQUEST['pm_text']);
					print($_REQUEST['pm_id'].",".$_REQUEST['scrollpane_height']);
					exit(0);
				}
				else{
					ln_friendrequest_action($_REQUEST['act'],$userinfo->ID,$_REQUEST['userid_subj']);
				}
			}
			
			if ($_REQUEST['numonly']){
				ln_update_readflag($userinfo->ID, 0, $options['max_notifications_count'],false,$_REQUEST['type']);
				$op = ln_count_user_notifications($userinfo->ID,$_REQUEST['type']);
				if (isset($_REQUEST['act'])){
					$op .= "|".ln_fetch_notifications($userinfo->ID, 0, $options['max_notifications_count'],false,$_REQUEST['type']);
				}
				else{
					$op .= "|".$_REQUEST['type'];
				}
			}
			else{
				if($_REQUEST['count']){
					$type = $_REQUEST['type'];
					$count_comment = $count_pm = $count_friend = $count_moderation = $options['max_notifications_count'];
					$count_tmp = $_REQUEST['count'];
					if($type == 'comment')$count_comment = $count_tmp;
					if($type == 'pm')$count_pm = $count_tmp;
				 	if($type == 'friend')$count_friend = $count_tmp;
				 	if($type == 'moderation')$count_moderation = $count_tmp;
				}
				else{
					$count_comment = $count_pm = $count_friend = $count_moderation = $options['max_notifications_count'];
				}
				
				$friend_num = 0;
				$pm_num = 0;
				$moderation_num = 0;
				
				/*
				ln_remove_unnecessary_notifications($userinfo->ID);
				
				ln_update_notifications($userinfo->ID,0, $count_comment, 'vbseolike');
				ln_update_notifications($userinfo->ID,0, $count_comment, 'vbarcade');
				ln_update_notifications($userinfo->ID,0, $count_comment, 'thanks');
				ln_update_notifications($userinfo->ID,0, $count_comment, 'experience');
				ln_update_notifications($userinfo->ID,0, $count_comment, 'wall');
				*/
				
				if($options['enable_friend']){
					//ln_update_notifications($userinfo->ID,0, $count_friend, 'friend');
					$friend_num = ln_count_user_notifications($userinfo->ID,'friend');
				}
				if($options['enable_moderation']){
					//ln_update_notifications($userinfo->ID,0, $count_moderation, 'moderation');
					$moderation_num = ln_count_user_notifications($userinfo->ID,'moderation');
				}
				if($options['enable_pm']){
					$pm_num = ln_count_user_notifications($userinfo->ID,'pm');
				}
				
				$op = ln_count_user_notifications($userinfo->ID,'comment')."|".$pm_num."|".$friend_num."|".$moderation_num;
				
				$op .= "|".ln_fetch_notifications($userinfo->ID, 0, $count_comment,false,'comment');
				$op .= "|".ln_fetch_notifications($userinfo->ID, 0, $count_pm,false,'pm');
				$op .= "|".ln_fetch_notifications($userinfo->ID, 0, $count_friend,false,'friend');
				$op .= "|".ln_fetch_notifications($userinfo->ID, 0, $count_moderation,false,'moderation');
				
			}
			print($op);
			exit(0);
		}
	}
	else if ($_REQUEST['do'] == 'ln_userdropdown') {
		@error_reporting(E_NONE); 
	
		if ($userinfo->ID < 1) die();
			
		$op = ln_fetch_userdropdown($userinfo, $options);
			
		print($op);
		exit(0);
	}
	else if ($_REQUEST['do'] == 'ln_save_option') {
		@error_reporting(E_NONE); 
	
		if ($userinfo->ID < 1) die();
			
		$option =  str_replace("true","1",$_REQUEST['options']);
		$option =  str_replace("false","0",$option);
		
		$options = explode(",",$option);
		$ln_useroptions['enable_comment'] = $options[0];
		$ln_useroptions['enable_reply'] = $options[1];
		$ln_useroptions['enable_award'] = $options[2];
		$ln_useroptions['enable_friend'] = $options[3];
		$ln_useroptions['enable_moderation'] = $options[4];
		$ln_useroptions['enable_taguser'] = $options[5];
		$ln_useroptions['enable_pm'] = $options[6];
		
		
		ln_save_useroptions($userinfo->ID,$ln_useroptions);
			
		print("success");
		exit(0);
	}
	else if ($_REQUEST['do'] == 'avatars_manage') {
		$uid = $_GET['uid'];
		$user = get_userdata($uid);

		$img_insert = "<div style='width: 440px; height: 260px; -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px; border: 3px dashed gray;'></div>";

	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title>Edit Avatar</title>
<?php
					wp_enqueue_style('global');
					wp_enqueue_style('wp-admin');
					wp_enqueue_style('colors');
					wp_enqueue_style('ie');

					do_action('admin_print_styles');
					do_action('admin_print_scripts');

				?>
</head>
<body>
<?php
					$files = avatar_strip_suffix($user->avatar);
					$root = avatar_root();
$siteurl=substr(get_option('siteurl'),0,22);
					switch($_REQUEST['act']) {
						case 'DEL':
							if(!wp_verify_nonce($_REQUEST['n'], 'avatars_nonce')) die('(1) Security check.');

							foreach($files as $f) {
								if(file_exists($root . $f)) @unlink($root . $f);
							}

							delete_usermeta($uid, 'avatar');
						break;

						case 'SAVE':
							if(!wp_verify_nonce($_REQUEST['avatars_nonce'], 'avatars_nonce')) die('(3) Security check.');

							if($_POST['x1'] != '') {
								$files = avatar_strip_suffix($user->avatar);
								avatar_crop($user, $files[BASE_FILE]);
							}
							else {
								avatar_upload($uid);
								$files = avatar_strip_suffix($user->avatar);
							}
						break;
					}

					output_avatar_error_message($user);

					printf("<form enctype='multipart/form-data' action='%s?action=ln_ajax_process&do=avatars_manage&act=SAVE&uid=%s' method='post'>",
						admin_url('admin-ajax.php'),
						$uid
					);

					$nonce = wp_create_nonce('avatars_nonce');
					printf("<input type='hidden' name='%s' value='%s' />", 'avatars_nonce', $nonce);
				?>
<span style='width: 150px; float: left; padding:8px;'>
<h3>Current Avatar</h3>
<?php
						printf("<p><small>%s</small></p>", __('Current Avatar image and type.', 'ln_notifications'));
						printf("<p><span class='avatar_avatar'>%s</span>", ln_fetch_useravatar($uid));
						printf("<span class='avatar_text'><strong>%s</strong> %s.</span></p>",
							get_avatar_type(),
							__('avatar', 'ln_notifications')
						);

						global $avatar_type;

						if($avatar_type == TYPE_LOCAL) {
							printf("<p><a id='avatars_local_button' class='button' href='%s?action=ln_ajax_process&do=avatars_manage&act=DEL&uid=%s&n=%s'>%s</a></p>",
								admin_url('admin-ajax.php'),
								$uid,
								$nonce,
								__('Delete local Avatar', 'ln_notifications')
							);

							list($w, $h, $type, $attr) = getimagesize($root . $files[BASE_FILE]);

							$img_insert = sprintf("<div style='width: 440px; height: 260px;'><img id='avatar_upload' src='%s' height='100' width='100' /></div>", $siteurl.$files[BASE_FILE]);
						}
					?>
<script type="text/javascript">
						parent.avatar_refresh(jQuery('.avatar_avatar img.avatar').attr('src'));
					</script>
<h3 style='margin-top: 80px;'>New Avatar</h3>
<?php
						printf("<p><small>%s</small></p>", __('How the uploaded image will look after manual cropping.', 'avatars'));

						if($avatar_type == TYPE_LOCAL) {
							//$siteurl=substr(get_option('siteurl'),0,22);
							$scaled_size = (empty($options['ln_avatar_height']) ? SCALED_SIZE : $options['ln_avatar_height']);
							printf("<div id='avatar_preview_div' style='width: %spx; height: %spx;'><img id='avatar_preview_img'  src='%s' style='position: relative;' width='100' height='100'/></div>", $scaled_size, $scaled_size,$siteurl.$files[BASE_FILE],$scaled_size,$scaled_size);
						}
					?>
</span> <span style='width: 450px; padding:8px; float: right;'>
<h3>Avatar Upload</h3>
<?php printf("<p><small>%s</small></p>", __('Upload an image to use as an Avatar.', 'avatars')); ?>
<input type='file' name='avatar_file' id='avatar_file' style='width: 440px;' />
<p> <span class='field-hint '><small>
  <?php _e('Hints: Square images make better avatars.', 'avatars'); ?>
  <br />
  <?php _e('Small image files are best for avatars, e.g. approx. 10K or smaller.', 'avatars'); ?>
  </small></span> </p>
<?php
						printf("<p>%s</p>", $img_insert);
						printf("<p><input type='submit' value='Update Avatar' class='button' /></p>");
					?>
<input type="hidden" name="x1" value="" />
<input type="hidden" name="y1" value="" />
<input type="hidden" name="x2" value="" />
<input type="hidden" name="y2" value="" />
<input type="hidden" name="w" value="" />
<input type="hidden" name="h" value="" />
<script type="text/javascript">
						function avatar_preview(img, selection) {
							var scaleX = 100 / (selection.width || 1);
							var scaleY = 100 / (selection.height || 1);

							jQuery('#avatar_preview_img').css({
								width: Math.round(scaleX * <?php echo $w; ?>) + 'px',
								height: Math.round(scaleY * <?php echo $h; ?>) + 'px',
								marginLeft: '-' + (Math.round(scaleX * selection.x1) + 0) + 'px',
								marginTop: '-' + (Math.round(scaleY * selection.y1) + 0) + 'px'
							});
						}

						function avatar_update_sel(img, selection) {
							jQuery('input[name="x1"]').val(selection.x1);
							jQuery('input[name="y1"]').val(selection.y1);
							jQuery('input[name="x2"]').val(selection.x2);
							jQuery('input[name="y2"]').val(selection.y2);
							jQuery('input[name="w"]').val(selection.width);
							jQuery('input[name="h"]').val(selection.height);
						}

						function avatar_init_view(img, selection) {
							avatar_preview(img, selection);
							avatar_update_sel(img, selection);
						}

						jQuery(document).ready(function () {
							jQuery('#avatar_upload').imgAreaSelect({
								aspectRatio: '1:1',
								handles: true,
								x1: <?php echo ($w / 2) - 50; ?>, y1: <?php echo ($h / 2) - 50; ?>, x2: <?php echo ($w / 2) + 30; ?>, y2: <?php echo ($h / 2) + 30; ?>,
								imageWidth: <?php echo $w; ?>,
								imageHeight: <?php echo $h; ?>,
								onInit: avatar_init_view,
								onSelectChange: avatar_preview,
								onSelectEnd: avatar_update_sel
							});
						});
					</script> 
</span>
</form>
<?php do_action('admin_print_footer_scripts'); ?>
</body>
</html>
<?php 
		die();
	}
}

// ADD Styles and Script in head section
add_action('admin_init', 'ln_scripts');
add_action('wp_head', 'ln_scripts');

function send_new_topic_notification()
{	
				$selectpost=mysql_query("select * from wp_posts where post_type='forum'");
				if(mysql_num_rows($selectpost)>0)
				{
					
					while($selectpostrec=mysql_fetch_array($selectpost))
					{
						$fetchtopics=mysql_query("select * from wp_posts where post_parent='".$selectpostrec['ID']."' and post_type!='revision'");	
						if(mysql_num_rows($fetchtopics)>0)
				{
					while($fetchtopicsrec=mysql_fetch_array($fetchtopics))
					{
						$fetchusergrpwise=mysql_query("select * from wp_users where ID!='".$selectpostrec['post_author']."'");
							if(mysql_num_rows($fetchusergrpwise)>0)
							{
								
								while($fetchusergrpwiserec=mysql_fetch_array($fetchusergrpwise))
								{ 
									$selectlivenoti=mysql_query("select id from wp_livenotifications where userid='".$fetchusergrpwiserec['ID']."' and userid_subj='".$selectpostrec['post_author']."' and content_id='".$fetchtopicsrec['ID']."'");
									if(mysql_num_rows($selectlivenoti)==0)
									{
										
										$user_info = get_userdata($selectpostrec['post_author']);
							
mysql_query("insert into wp_livenotifications (`userid`,`userid_subj`,`content_type`,`content_id`,`parent_id`,`content_text`,`time`,`username`) values('".$fetchusergrpwiserec['ID']."','".$fetchtopicsrec['post_author']."','bbpressnotification','".$fetchtopicsrec['ID']."','".$fetchtopicsrec['post_parent']."','".$fetchtopicsrec['post_title']."','".time()."','".$user_info->display_name."')");
	
							}
					}
				}
					}
				}
			}
		}
	
	/*bbpress notification end*/
	
}

function send_new_reply_notification()
{
	
	
	
	/*bbpress notification start*/
		
	/*$selectforum=mysql_query("select * from wp_bp_groups where enable_forum='1'");
	if(mysql_num_rows($selectforum)>0)
	{
		
		while($selectforumrec=mysql_fetch_array($selectforum))
		{
			$forumid=groups_get_groupmeta( $selectforumrec['id'], $meta_key = 'forum_id');
			$countforum=count($forumid);
			
			for($i=0;$i<$countforum;$i++)
			{
				$selectpost=mysql_query("select * from wp_posts where post_parent='".$forumid[$i]."'");
				if(mysql_num_rows($selectpost)>0)
				{
					
					while($selectpostrec=mysql_fetch_array($selectpost))
					{
						$selectpostreply=mysql_query("select * from wp_posts where post_parent='".$selectpostrec['ID']."'");
				if(mysql_num_rows($selectpostreply)>0)
				{
					
					while($selectpostreplyrec=mysql_fetch_array($selectpostreply))
					{	
						
						$fetchusergrpwise=mysql_query("select * from wp_bp_groups_members where group_id='".$selectforumrec['id']."' and user_id!='".$selectpostrec['post_author']."'");
							if(mysql_num_rows($fetchusergrpwise)>0)
							{
								
								while($fetchusergrpwiserec=mysql_fetch_array($fetchusergrpwise))
								{
									$selectlivenoti=mysql_query("select id from wp_livenotifications where userid='".$fetchusergrpwiserec['user_id']."' and userid_subj='".$selectpostrec['post_author']."' and content_id='".$selectpostreplyrec['ID']."'");							if(mysql_num_rows($selectlivenoti)==0)
									{
										
										$user_info = get_userdata($selectpostreplyrec['post_author']);
							//echo "insert into wp_livenotifications (`userid`,`userid_subj`,`content_type`,`content_id`,`parent_id`,`content_text`,`time`,`username`) values('".$fetchusergrpwiserec['user_id']."','".$selectpostreplyrec['post_author']."','bbpressnotificationreply','".$selectpostreplyrec['ID']."','".$selectpostreplyrec['post_parent']."','".$selectpostreplyrec['post_title']."','".time()."','".$user_info->display_name."')";
							
//mysql_query("insert into wp_livenotifications (`userid`,`userid_subj`,`content_type`,`content_id`,`parent_id`,`content_text`,`time`,`username`) values('".$fetchusergrpwiserec['user_id']."','".$selectpostreplyrec['post_author']."','bbpressnotificationreply','".$selectpostreplyrec['ID']."','".$selectpostreplyrec['post_parent']."','".$selectpostreplyrec['post_title']."','".time()."','".$user_info->display_name."')");
	
									}
								}
							}
					}
				}
					}
				}
			}
		}
		
	}*/
	
	

		$selectpost=mysql_query("select * from wp_posts where post_type='topic'");
				if(mysql_num_rows($selectpost)>0)
				{
					
					while($selectpostrec=mysql_fetch_array($selectpost))
					{
						$fetchtopics=mysql_query("select * from wp_posts where post_parent='".$selectpostrec['ID']."' and post_type!='revision'");	
						if(mysql_num_rows($fetchtopics)>0)
				{
					while($fetchtopicsrec=mysql_fetch_array($fetchtopics))
					{
						$fetchusergrpwise=mysql_query("select * from wp_users where ID!='".$fetchtopicsrec['post_author']."'");
							if(mysql_num_rows($fetchusergrpwise)>0)
							{
								
								while($fetchusergrpwiserec=mysql_fetch_array($fetchusergrpwise))
								{ 
								$selectlivenoti=mysql_query("select id from wp_livenotifications where userid='".$fetchusergrpwiserec['ID']."' and userid_subj='".$fetchtopicsrec['post_author']."' and content_id='".$fetchtopicsrec['ID']."'");									if(mysql_num_rows($selectlivenoti)==0)
									{
										
										$user_info = get_userdata($fetchtopicsrec['post_author']);
//echo "insert into wp_livenotifications (`userid`,`userid_subj`,`content_type`,`content_id`,`parent_id`,`content_text`,`time`,`username`) values('".$fetchusergrpwiserec['ID']."','".$fetchtopicsrec['post_author']."','bbpressnotificationreply','".$fetchtopicsrec['ID']."','".$fetchtopicsrec['post_parent']."','".$fetchtopicsrec['post_title']."','".time()."','".$user_info->display_name."')<br>";
							
mysql_query("insert into wp_livenotifications (`userid`,`userid_subj`,`content_type`,`content_id`,`parent_id`,`content_text`,`time`,`username`) values('".$fetchusergrpwiserec['ID']."','".$fetchtopicsrec['post_author']."','bbpressnotificationreply','".$fetchtopicsrec['ID']."','".$fetchtopicsrec['post_parent']."','".$fetchtopicsrec['post_title']."','".time()."','".$user_info->display_name."')");
	
							}
					}
				}
					}
				}
			}
		}
		
	/*bbpress notification end*/
}

function ln_add_comment_notifications($comment_ID, $comment_approved){
	global $wpdb;
	
	
	if ( !$comment = get_comment($comment_ID) )
		return false;
	
	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

	$post_info = get_post($comment->comment_post_ID);

	
	$userid = $comment->user_id;
	$commenter_name = $comment->comment_author;
	
	$comment_date = strtotime($comment->comment_date);
	
	$options = get_option('ln_options');	

	if($options['enable_moderation'] ){
		$moderator_ids = $wpdb->get_results( "SELECT $wpdb->users.ID FROM $wpdb->users WHERE (SELECT $wpdb->usermeta.meta_value FROM $wpdb->usermeta WHERE $wpdb->usermeta.user_id = wp_users.ID AND $wpdb->usermeta.meta_key = 'wp_capabilities') LIKE '%administrator%'" );
		
		$is_noadmin = true;
		foreach($moderator_ids as $m_id){
			ln_add_user_notification(0, $m_id->ID, 'mod_comment', 0, $comments_waiting,$comment->comment_post_ID, $comment_date , $commenter_name ,$comment_approved);
			if($m_id->ID == $post_info->post_author) $is_noadmin = false;
		}
		
		if ( user_can( $post_info->post_author, 'edit_comment', $comment_ID) && $is_noadmin )
			ln_add_user_notification(0, $post_info->post_author, 'mod_comment', 0, $comments_waiting, $comment->comment_post_ID, $comment_date , $commenter_name ,$comment_approved);
	}
	
	if($options['enable_taguser'] ){
		//this part for @tag
		$post_text = $comment->comment_content;
	
		while($npos = strpos($post_text, "@")){
			if($npos){
				$username = substr($post_text,$npos + 1);
				$nendpos = strpos($username," ");
				if($nendpos) {
					$post_text = substr($username, $nendpos + 1);
					$username = substr($username,0,$nendpos);
					
				}
				else{
					$post_text = "";
				}
				$sql = "SELECT ID FROM  " . $wpdb->prefix . "users WHERE display_name = '".$username."'";
				$tagged_userinfo = $wpdb->get_row($sql);
				if($tagged_userinfo && !empty($tagged_userinfo) && $tagged_userinfo->ID != $userid){
					ln_add_user_notification($userid, $tagged_userinfo->ID, 'mention', $comment_ID, $post_info->post_title, $comment->comment_post_ID,$comment_date, $commenter_name ,$comment_approved);
				}
			}
		}
	}
	if ($options['enable_friend']) {//let user know his friend's actions
		$friends = ln_get_all_friends($userid);
		foreach ($friends as $friend){
			ln_add_user_notification($userid, $friend->ID, 'friend_comment', $comment_ID, $post_info->post_title, $comment->comment_post_ID, $comment_date , $commenter_name ,$comment_approved);
		}
	}
 	
	if ($options['enable_comment']) {
	
		if ($userid != $post_info->post_author) {
				
			ln_add_user_notification($userid, $post_info->post_author, 'comment', $comment_ID, $post_info->post_title, $comment->comment_post_ID, $comment_date , $commenter_name ,$comment_approved);
		}
		
	}
	
	if ($options['enable_reply'] && $_POST['comment_parent'] > 0) {
		
		$sql = "
				SELECT user_id
				FROM " . $wpdb->prefix . "comments
				WHERE comment_ID = ".$_POST['comment_parent']."
			";
		
		$ln_post = $wpdb->get_row($sql);

		if ($ln_post->user_id > 0 && $ln_post->user_id != $userid){
			ln_add_user_notification($userid, $ln_post->user_id, 'reply', $comment_ID, $post_info->post_title, $comment->comment_post_ID, $comment_date , $commenter_name, $comment_approved);
		}
		
	}
}
function ln_remove_post_notifications($post_ID){
	ln_add_post_notifications($post_ID, 0);
}
function ln_add_post_notifications($post_ID, $status = 1){
	global $wpdb;

	if(!$post_info = get_post($post_ID))
		return false;
	if($post_info->post_type != 'post') return; 
	$userid = $post_info->post_author;
	$poster_name = $wpdb->get_var("SELECT display_name FROM $wpdb->users WHERE ID = $userid");

	$post_date = strtotime($post_info->post_date);

	$options = get_option('ln_options');
	
 	if($options['enable_taguser'] ){
		//this part for @tag
		$post_text = $post_info->post_content;

		while($npos = strpos($post_text, "@")){
			if($npos){
				$username = substr($post_text,$npos + 1);
				$nendpos = strpos($username," ");
				if($nendpos) {
					$post_text = substr($username, $nendpos + 1);
					$username = substr($username,0,$nendpos);
						
				}
				else{
					$post_text = "";
				}
				$sql = "SELECT ID FROM  " . $wpdb->prefix . "users WHERE display_name = '".$username."'";
				$tagged_userinfo = $wpdb->get_row($sql);
				if($tagged_userinfo && !empty($tagged_userinfo) && $tagged_userinfo->ID != $userid){
					ln_add_user_notification($userid, $tagged_userinfo->ID, 'mention', $post_ID, $post_info->post_title, 0 ,$post_date, $poster_name ,$status);
				}
			}
		}
	}
	if ($options['enable_friend']) {//let user know his friend's actions
		$friends = ln_get_all_friends($userid);
		foreach ($friends as $friend){
			ln_add_user_notification($userid, $friend->ID, 'friend_post', $post_ID, $post_info->post_title, 0, $post_date , $poster_name ,$status);
		}
	}

}

//comment user detail jayesh

function ln_add_userdetail($post_ID)
{
	global $wpdb;

	if(!$post_info = get_post($post_ID))
		return false;
	if($post_info->post_type != 'post') return; 
	$userid = $post_info->post_author;
	$poster_name = $wpdb->get_var("SELECT display_name FROM $wpdb->users WHERE ID = $userid");

	$post_date = strtotime($post_info->post_date);
	$post_status=$post_info->post_status;
	//insert record in count_reading table jayesh
	if($post_status=='publish')
	 {
		 mysql_query("insert into ".$wpdb->prefix."count_reading (userid,postid,readtime,posttype) values('".$userid."','".$post_ID."','".$post_date."','post')");
	
	
	
	$count_post=mysql_num_rows(mysql_query("select * from ".$wpdb->prefix."count_reading where userid='".$userid."' and  posttype='post'"));
	
	//get reward system data
	$getpostreward=mysql_query("select * from ".$wpdb->prefix."rewardsystem where type='post' ORDER BY `reid` ASC");
	while($getpostrewardrec=mysql_fetch_array($getpostreward))
	{
		$numlist=$getpostrewardrec['numlist'];
		$repoint=$getpostrewardrec['repoint'];
		$reorder=$getpostrewardrec['reorder'];
		$type=$getpostrewardrec['type'];
		$retitle=$getpostrewardrec['retitle'];
		$remsg=$getpostrewardrec['remsg'];
		$reid=$getpostrewardrec['reid'];
	
		if($numlist==$count_post){
			
			
			
		//insert into point table
		$countpoints=mysql_query("insert into ".$wpdb->prefix."countpoints (cp_uid,cp_pmid,cp_points,cp_time,cp_tasklist) values('".$userid."','".$post_ID."','".$repoint."','".$post_date."','".$reorder."')");
		
		
		
		$selectorder=mysql_query("select cp_tasklist from ".$wpdb->prefix."countpoints where cp_uid='".$userid."'");
			if(mysql_num_rows($selectorder)>0)
			{
			$reclist=0;
			while($selectorderrec=mysql_fetch_array($selectorder))
			{
				if($reclist==0)
				{
					$order .="reorder!=".$selectorderrec['cp_tasklist'];
				}
				else
				{
					$order .=" and reorder!=".$selectorderrec['cp_tasklist'];
				}
				$reclist++;
			}
			}
			else
			{
				$order="1=1";
			}
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where ".$order." ORDER BY reorder ASC");
			$rank=mysql_fetch_array($selectdata1);
			$rank_next=$rank['numlist'].' '.$rank['type'];
			
			
			
		//insert into livenotification table
		$selectoption=mysql_query("select enable_award from ".$wpdb->prefix."livenotifications_usersettings where userid='".$userid."'");
			if(mysql_num_rows($selectoption)>0)
			{
				$selectoptionrec=mysql_fetch_array($selectoption);
				$award=$selectoptionrec['enable_award'];
			}
			else
			{
				$options = get_option('ln_options');
				$award=$options['enable_award'];
				if($award=='on')
				{
					$award='1';
				}
				else
				{
					$award='0';
				}
			}
						if($award=='1')
						{
		$livesnotificationtable=mysql_query("insert into ".$wpdb->prefix."livenotifications (userid,userid_subj,content_type,content_id,content_text,is_read,time,username) values('".$userid."','".$userid."','postaward','".$reid."','".$remsg."','0','".time()."','".$rank_next."')");
						}
		}
	}
	}
}
function ln_add_userdetail_comment($comment_ID)
{
	global $wpdb;

	if(!$post_info = get_comment($comment_ID))
		return false;
	$commmentpostid = $post_info->comment_post_ID;
	$commentauthor=$post_info->comment_author;
	
	 $current_user = wp_get_current_user();
	$userid=$current_user->ID;
	$post_date = strtotime($post_info->comment_date);
	$post_status=$post_info->post_status;
	//insert record in count_reading table jayesh
	
		 mysql_query("insert into ".$wpdb->prefix."count_reading (userid,postid,readtime,posttype) values('".$userid."','".$commmentpostid."','".$post_date."','comment')");
	$count_post=mysql_num_rows(mysql_query("select * from ".$wpdb->prefix."count_reading where userid='".$userid."' and posttype='comment'"));
	
	$getpostreward=mysql_query("select * from ".$wpdb->prefix."rewardsystem where type='comment' ORDER BY `reid` ASC");
	while($getpostrewardrec=mysql_fetch_array($getpostreward))
	{
		$numlist=$getpostrewardrec['numlist'];
		$repoint=$getpostrewardrec['repoint'];
		$reorder=$getpostrewardrec['reorder'];
		$type=$getpostrewardrec['type'];
		$retitle=$getpostrewardrec['retitle'];
		$remsg=$getpostrewardrec['remsg'];
		$reid=$getpostrewardrec['reid'];
		if($count_post==$numlist){
		//insert into point table
		$countpoints=mysql_query("insert into ".$wpdb->prefix."countpoints (cp_uid,cp_pmid,cp_points,cp_time,cp_tasklist) values('".$userid."','".$commmentpostid."','".$repoint."','".$post_date."','".$reorder."')");
		
			$selectorder=mysql_query("select cp_tasklist from ".$wpdb->prefix."countpoints where cp_uid='".$userid."'");
			if(mysql_num_rows($selectorder)>0)
			{
			$reclist=0;
			while($selectorderrec=mysql_fetch_array($selectorder))
			{
				if($reclist==0)
				{
					$order .="reorder!=".$selectorderrec['cp_tasklist'];
				}
				else
				{
					$order .=" and reorder!=".$selectorderrec['cp_tasklist'];
				}
				$reclist++;
			}
			}
			else
			{
				$order="1=1";
			}
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where ".$order." ORDER BY reorder ASC");
			$rank=mysql_fetch_array($selectdata1);
			$rank_next=$rank['numlist'].' '.$rank['type'];
			
		$selectoption=mysql_query("select enable_award from ".$wpdb->prefix."livenotifications_usersettings where userid='".$userid."'");
			if(mysql_num_rows($selectoption)>0)
			{
				$selectoptionrec=mysql_fetch_array($selectoption);
				$award=$selectoptionrec['enable_award'];
			}
			else
			{
				$options = get_option('ln_options');
				$award=$options['enable_award'];
				if($award=='on')
				{
					$award='1';
				}
				else
				{
					$award='0';
				}
			}
						if($award=='1')
						{
		//insert into livenotification table
		$livesnotificationtable=mysql_query("insert into ".$wpdb->prefix."livenotifications (userid,userid_subj,content_type,content_id,content_text,is_read,time,username) values('".$userid."','".$userid."','commentaward','".$reid."','".$remsg."','0','".time()."','".$rank_next."')");
						}
		}
	}
	
}
function ln_get_all_friends($userid){
	global $wpdb;
	$friends = $wpdb->get_results( "SELECT u.display_name,u.ID FROM ".$wpdb->prefix."userlist AS ul INNER JOIN ".$wpdb->prefix."users AS u ON u.ID = ul.relationid
					WHERE ul.userid = ".$userid. " AND ul.friend = 'yes'
					AND ul.type = 'buddy'
					ORDER BY u.display_name ASC" );
	return $friends;
}

function ln_is_friend($userid, $candidate){
	global $wpdb;
	$check = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix. "userlist WHERE userid = $userid AND relationid = $candidate AND type = 'buddy' AND friend = 'yes'");
	if($check != null) return true;
	return false;
}
function ln_scripts() {	
	$options = get_option('ln_options');
//	wp_enqueue_script('jquery');
	wp_enqueue_script('ln_frontend_scripts',plugins_url('js/ln_livenotifications.js',__FILE__), array('jquery'));
	
	$params = "?banner_bgcolor=";
	if (isset($options['banner_bgcolor'])) 
		$params .= urlencode($options['banner_bgcolor']);
	else 
		$params .= urlencode("#333");
	
	$params .= "&dropdown_border_color=";
	if (isset($options['dropdown_border_color']))
		$params .= urlencode($options['dropdown_border_color']);
	else 
		$params .= urlencode("#999");
	
	$params .= "&dropdown_bgcolor=";
	if (isset($options['dropdown_bgcolor']))
		$params .= urlencode($options['dropdown_bgcolor']);
	else
		$params .= urlencode("#C39500");
	
	$params .= "&dropdown_link_color=";
	if (isset($options['dropdown_link_color']))
		$params .= urlencode($options['dropdown_link_color']);
	else
		$params .= urlencode("#417394");
	$params .= "&dropdown_color=";
	if (isset($options['dropdown_color']))
		$params .= urlencode($options['dropdown_color']);
	else
		$params .= urlencode("#888888");
	
	$params .= "&dropdown_bit_bgcolor=";
	if (isset($options['dropdown_bit_bgcolor']))
		$params .= urlencode($options['dropdown_bit_bgcolor']);
	else
		$params .= urlencode("#FFFFFF");
	$params .= "&dropdown_hover_bgcolor=";
	if (isset($options['dropdown_hover_bgcolor']))
		$params .= urlencode($options['dropdown_hover_bgcolor']);
	else
		$params .= urlencode("#D69803");

	wp_enqueue_style('ln_frontend_scripts',plugins_url("css/ln_livenotifications_css.php".$params,__FILE__));
	wp_enqueue_style( 'ln_frontend_scripts1',plugins_url('css/jquery.mCustomScrollbar.css',__FILE__), false, '1.0.0' );
	wp_enqueue_style( 'ln_frontend_scripts4',plugins_url('css/font-awesome.min.css',__FILE__), false, '1.0.0' );
	wp_enqueue_script( 'ln_frontend_scripts2',plugins_url('js/jquery.mousewheel.min.js',__FILE__), array('jquery'));
 	wp_enqueue_script( 'ln_frontend_scripts3',plugins_url('js/jquery.mCustomScrollbar.js',__FILE__), array('jquery'));
	
 	
 	
 	if (is_admin()) {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox', null,  array('jquery'));
 		wp_enqueue_style('thickbox');
 		
 	}
 	else{
 		wp_enqueue_script('thickbox', null,  array('jquery'));
 		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
 	}
 	
 	
}
//-------------------------------------------------------------------------------------

// Hook for adding admin menus
add_action('admin_menu', 'ln_plugin_admin_menu');

function ln_plugin_admin_menu() {
     
     global $wpdb, $current_user;
     
     $num_unread = $wpdb->get_var( 'SELECT COUNT(`id`) FROM ' . $wpdb->prefix . 'pm WHERE `recipient` = "' . $current_user->user_login . '" AND `read` = 0 AND `deleted` != "2"' );
     
     if ( empty( $num_unread ) )
     	$num_unread = 0;
     
     
     $icon_url = WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/images/icon.png';
     
     add_menu_page( __('vBSocial Notifications Settings','ln_livenotifications'), __( 'vBSocial', 'ln_notifications' ), 'read', 'ln_fetch_notifications', 'ln_fetch_notifications', $icon_url );
	 
     add_submenu_page( 'ln_fetch_notifications', __( 'Notifications Overview', 'ln_notifications' ), __( 'Overview', 'ln_notifications' ), 'read', 'ln_fetch_notifications', 'ln_notifications_overview' );
     
     add_submenu_page( 'ln_fetch_notifications', __( 'Inbox', 'ln_notifications' ), __( 'Inbox', 'ln_notifications' )."<span class='update-plugins count-$num_unread'><span class='plugin-count'>$num_unread</span></span>", 'read', 'lnpm_inbox', 'lnpm_inbox' );
     
     add_submenu_page( 'ln_fetch_notifications', __( 'Outbox', 'ln_notifications' ), __( 'Outbox', 'ln_notifications' ), 'read', 'lnpm_outbox', 'lnpm_outbox' );
     
     add_submenu_page( 'ln_fetch_notifications', __( 'Send Private Message', 'ln_notifications' ), __( 'Send PM', 'ln_notifications' ), 'read', 'lnpm_send', 'lnpm_send' );
     
     add_submenu_page( 'ln_fetch_notifications', __( 'Friends & Contacts', 'ln_notifications' ), __( 'Friends&Contacts', 'ln_notifications' ), 'read', 'ln_friend', 'ln_friend' );
     
     if(ln_is_admin($current_user->ID)){
     	add_submenu_page( 'ln_fetch_notifications', __( 'vBSocial Notifications Settings', 'ln_notifications' ), __( 'Settings', 'ln_notifications' ), 'read', 'ln_backend_menu', 'ln_backend_menu' );
     }
}
function ln_is_admin($userid){
	global $wpdb;
	$check = $wpdb->get_var( "SELECT $wpdb->usermeta.meta_value FROM $wpdb->usermeta WHERE $wpdb->usermeta.user_id = $userid AND $wpdb->usermeta.meta_key = 'wp_capabilities' AND $wpdb->usermeta.meta_value LIKE '%administrator%'" );
	if($check == null) return false;
	return true;	
}

//This function will create new database fields with default values


//This function will create new database fields with default values
function ln_defaults(){
	    $default = array(
		'banner_bgcolor' => '#333333',
        'dropdown_bgcolor' => '#0F67A1',
	    'dropdown_border_color' => '#999999',
	    'dropdown_link_color' => '#417394',
	    'dropdown_color' => '#888888',
	    'dropdown_bit_bgcolor' => '#F7FCFE',
	    'dropdown_hover_bgcolor' => '#CAE6FF',
	    'logo_url' => 'http://vbsocial.com/images/logo9.png',
	    'update_interval' => 30,		
	    'max_notifications_count' => 5,	
	   	'cut_strlen' => 30,
	    'enable_comment' => true,
	    'enable_reply' => true,
		'enable_award' => true,
		'enable_taguser' => true,
    	'enable_pm' => true,
    	'enable_friend' => true,
    	'enable_moderation' => true,
		'ln_enable_search' => true,
    	'hide_avatar' => false,
	    'ln_avatar_height' => 30,
	    'ln_default_avatar' => '',
	    'max_age' => 7,
		'ln_swich_search'=>'wordpress',
	    'disable_default_bar' => true,
		'hide_wp_notification'=>true,
		'hide_wp_notification_user'=>false,
	    'ln_enable_userdropdown' => true,
	    'ln_enable_userdropdown_logout' => true,
		'ln_enable_award_link'=>true,
		'ln_fblink'=>'https://www.facebook.com/pages/vBSocialcom/176817612373656',
		'ln_bmpopup'=>'enable',
		'ln_udd_morelinks' => 'Notifications Settings => notification'
		//'ln_udd_morelinks' => 'Notifications Settings => wp-admin/admin.php?page=ln_backend_menu'
	    		
    );
return $default;
}

function ln_friend(){
	global $wpdb, $current_user;
	?>
<div class="wrap">
  <?php /*?><h2><?php _e( 'Friends List', 'ln_livenotifications' ); ?></h2><?php */?>
  <?php
	
	if ( $_REQUEST['page'] == 'ln_friend' && ($_POST['submit_friend_request'] || $_POST['submit_cancel_friend_request'] || $_POST['submit_remove_friend'] || 
			$_POST['submit_reject'] || $_POST['submit_accept'])) {
		$error = false;
		$status = array( );
		
		if($_POST['recipient'] == "") $recipient = array();
		else $recipient = $_POST['recipient'];
		
		$recipient = array_map( 'strip_tags', $recipient );
		if ( get_magic_quotes_gpc( ) ) {
			$recipient = array_map( 'stripslashes', $recipient );
		}
		$recipient = array_map( 'esc_sql', $recipient );

		$recipient = array_unique( $recipient );
		$recipient = array_filter( $recipient );

		if ( empty( $recipient ) ) {
			$error = true;
			if($_POST['submit_friend_request'])
				$status[] = __( 'Please select username you want to add friend', 'ln_livenotifications' );
			if($_POST['submit_cancel_friend_request'])
				$status[] = __( 'Please select username you want to cancel requests', 'ln_livenotifications' );
			if($_POST['submit_remove_friend'])
				$status[] = __( 'Please select username you want to remove friend', 'ln_livenotifications' );
			if($_POST['submit_reject'])
				$status[] = __( 'Please select username you want to reject requests', 'ln_livenotifications' );
			if($_POST['submit_accept'])
				$status[] = __( 'Please select username you want to accept requests', 'ln_livenotifications' );
		}
		
		if ( !$error ) {
			$numOK = $numError = 0;
			$status_message = "";
			foreach ( $recipient as $rec ) {
				$rec = $wpdb->get_row( "SELECT user_login, ID FROM $wpdb->users WHERE display_name = '$rec' LIMIT 1" );
				if(isset( $_POST['submit_friend_request'])){
					$new_request = array(
						'id' => NULL,
						'userid' => $current_user->ID,
						'relationid' => $rec->ID,
						'type' => 'buddy',
						'friend' => 'pending'
					);
					if ($wpdb->insert( $wpdb->prefix . 'userlist', $new_request, array( '%d', '%d', '%d', '%s', '%s' ) ) ) {
						$numOK++;
						ln_add_user_notification($current_user->ID, $rec->ID, 'friend', 0, '', 0, 0, $current_user->user_login);
						unset( $_REQUEST['recipient']);
						if($status_message == "") $status_message = '%d friend requests sent.';
					} else {
						$numError++;
					}
				}
				else if(isset( $_POST['submit_cancel_friend_request'])){
					$query = "DELETE FROM ".$wpdb->prefix . "userlist WHERE userid = " . $current_user->ID . " AND relationid = " . $rec->ID .
						" AND type = 'buddy' AND friend = 'pending' ";
					
					if ($wpdb->query($query) ) {
						$numOK++;
						ln_remove_notifications('friend', $current_user->ID, $rec->ID);
						unset( $_REQUEST['recipient']);
						if($status_message == "") $status_message = '%d friend requests canceled.';
					} else {
						$numError++;
					}
				}
				else if(isset( $_POST['submit_remove_friend'])){
					$query = "DELETE FROM ".$wpdb->prefix . "userlist WHERE (userid = " . $current_user->ID . " AND relationid = " . $rec->ID .
					") OR (relationid = " . $current_user->ID . " AND userid = " . $rec->ID .
					") AND type = 'buddy' AND friend = 'yes' ";
						
					if ($wpdb->query($query) ) {
						$numOK++;
						ln_remove_notifications('friend_comment',$current_user->ID, $rec->ID );
						ln_remove_notifications('friend_comment', $rec->ID ,$current_user->ID );
						unset( $_REQUEST['recipient']);
						if($status_message == "") $status_message = '%d friends removed.';
					} else {
						$numError++;
					}
				}
				else if(isset( $_POST['submit_reject'])){
					$query = "DELETE FROM ".$wpdb->prefix . "userlist WHERE relationid = " . $current_user->ID . " AND userid = " . $rec->ID .
					" AND type = 'buddy' AND friend = 'pending' ";
						
					if ($wpdb->query($query) ) {
						$numOK++;
						ln_remove_notifications('friend',$current_user->ID, $rec->ID );
						unset( $_REQUEST['recipient']);
						if($status_message == "") $status_message = '%d incoming friend requests rejected.';
					} else {
						$numError++;
					}
				}
				else if(isset( $_POST['submit_accept'])){
					$query = "DELETE FROM ".$wpdb->prefix . "userlist WHERE relationid = " . $current_user->ID . " AND userid = " . $rec->ID .
					" AND type = 'buddy' AND friend = 'pending' ";
					if ($wpdb->query($query) ) {
						$new_request = array(
								'id' => NULL,
								'userid' => $current_user->ID,
								'relationid' => $rec->ID,
								'type' => 'buddy',
								'friend' => 'yes'
						);
						if ($wpdb->insert( $wpdb->prefix . 'userlist', $new_request, array( '%d', '%d', '%d', '%s', '%s' ) ) ) {
							$new_request = array(
									'id' => NULL,
									'userid' => $rec->ID,
									'relationid' => $current_user->ID,
									'type' => 'buddy',
									'friend' => 'yes'
							);
							if ($wpdb->insert( $wpdb->prefix . 'userlist', $new_request, array( '%d', '%d', '%d', '%s', '%s' ) ) ) {
								$numOK++;
								ln_remove_notifications('friend',$current_user->ID, $rec->ID );
								unset( $_REQUEST['recipient']);
								if($status_message == "") $status_message = '%d incoming friend requests accepted.';	
							}
							else{
								$numError++;
							}
						}
						else{
							$numError++;
						}
					}
					else {
						$numError++;
					}
				}
			}

			$status[] = sprintf( _n( $status_message, $status_message, $numOK, 'ln_livenotifications' ), $numOK ) . ' ' . sprintf( _n( '%d error.', '%d errors.', $numError, 'ln_livenotifications' ), $numError );
			
		}

		echo '<div id="message" class="updated fade"><p>', implode( '</p><p>', $status ), '</p></div>';
	}
	?>
  <form method="post" action="" class="ln_friend_form">
    <table class="form-table">
      <tr>
        <th><?php _e( 'Available Members', 'ln_livenotifications' ); ?></th>
        <td><?php
	 		$recipient = !empty( $_POST['recipient'] ) ? $_POST['recipient'] : ( !empty( $_GET['recipient'] )
				? $_GET['recipient'] : '' );

			$users = $wpdb->get_results( "SELECT display_name, ID FROM $wpdb->users
						WHERE ID <> ".$current_user->ID." 
						ORDER BY display_name ASC" );
		?>
          <select name="recipient[]" multiple="multiple" size="5">
            <?php
					$has_available = false;
					foreach ( $users as $user ) {

						$check = $wpdb->get_var("SELECT COUNT(ul.userid) AS cnt FROM " . $wpdb->prefix . "userlist AS ul 
							WHERE ((ul.userid = ".$current_user->ID." AND ul.relationid = ".$user->ID.") 
							OR (ul.userid = ".$user->ID." AND ul.relationid = ".$current_user->ID.")) 
							AND (ul.friend = 'yes' OR ul.friend = 'pending') 
							AND ul.type = 'buddy' ");
						if($check > 0) continue;
						
						$has_available = true;
						$selected = ( $user->display_name == $recipient ) ? ' selected="selected"' : '';
						echo "<option value='$user->display_name'$selected>$user->display_name</option>";
					}
					?>
          </select></td>
      </tr>
    </table>
    <p class="submit" id="submit">
      <input type="hidden" name="page" value="ln_friend"/>
      <input type="submit" name="submit_friend_request" class="button-primary" value="<?php _e( 'Add Friend', 'ln_livenotifications' ) ?>" <?php if(!$has_available) echo " disabled";?>/>
    </p>
  </form>
  <form method="post" action="" class="ln_friend_form">
    <table class="form-table">
      <tr>
        <th><?php _e( 'Friend Requested Members', 'ln_livenotifications' ); ?></th>
        <td><?php
	 		$recipient = !empty( $_POST['recipient'] ) ? $_POST['recipient'] : ( !empty( $_GET['recipient'] )
				? $_GET['recipient'] : '' );
	
			$users = $wpdb->get_results( "SELECT u.display_name FROM ".$wpdb->prefix. "userlist AS ul INNER JOIN ".$wpdb->prefix."users AS u ON u.ID = ul.relationid
					WHERE ul.userid = ".$current_user->ID." AND ul.friend = 'pending' 
					AND ul.type = 'buddy'  
					ORDER BY u.display_name ASC" );

		?>
          <select name="recipient[]" multiple="multiple" size="5">
            <?php
					$has_available = false;
					foreach ( $users as $user ) {
						$has_available = true;
						$selected = ( $user->display_name == $recipient ) ? ' selected="selected"' : '';
						echo "<option value='$user->display_name'$selected>$user->display_name</option>";
					}
					?>
          </select></td>
      </tr>
    </table>
    <p class="submit" id="submit">
      <input type="hidden" name="page" value="ln_friend"/>
      <input type="submit" name="submit_cancel_friend_request" class="button-primary" value="<?php _e( 'Cancel Requests', 'ln_livenotifications' ) ?>" <?php if(!$has_available) echo " disabled";?>/>
    </p>
  </form>
  <form method="post" action="" class="ln_friend_form">
    <table class="form-table">
      <tr>
        <th><?php _e( 'Friends List', 'ln_livenotifications' ); ?></th>
        <td><?php
	 		$recipient = !empty( $_POST['recipient'] ) ? $_POST['recipient'] : ( !empty( $_GET['recipient'] )
				? $_GET['recipient'] : '' );
	
			$users = ln_get_all_friends($current_user->ID);
		?>
          <select name="recipient[]" multiple="multiple" size="5">
            <?php
					$has_available = false;
					foreach ( $users as $user ) {
						$has_available = true;
						$selected = ( $user->display_name == $recipient ) ? ' selected="selected"' : '';
						echo "<option value='$user->display_name'$selected>$user->display_name</option>";
					}
					?>
          </select></td>
      </tr>
    </table>
    <p class="submit" id="submit">
      <input type="hidden" name="page" value="ln_friend"/>
      <input type="submit" name="submit_remove_friend" class="button-primary" value="<?php _e( 'Remove Friends', 'ln_livenotifications' ) ?>" <?php if(!$has_available) echo " disabled";?>/>
    </p>
  </form>
  <form method="post" action="" class="ln_friend_form" id="ln_request">
    <table class="form-table">
      <tr>
        <th><?php _e( 'Incoming Friend Requests', 'ln_livenotifications' ); ?></th>
        <td><?php
	 		$recipient = !empty( $_POST['recipient'] ) ? $_POST['recipient'] : ( !empty( $_GET['recipient'] )
				? $_GET['recipient'] : '' );
	
			$users = $wpdb->get_results( "SELECT u.display_name FROM ".$wpdb->prefix."userlist AS ul INNER JOIN ".$wpdb->prefix."users AS u ON u.ID = ul.userid
					WHERE ul.relationid = ".$current_user->ID." AND ul.friend = 'pending'
					AND ul.type = 'buddy'
					ORDER BY u.display_name ASC" );
		?>
          <select name="recipient[]" multiple="multiple" size="5">
            <?php
					$has_available = false;
					foreach ( $users as $user ) {
						$has_available = true;
						$selected = ( $user->display_name == $recipient ) ? ' selected="selected"' : '';
						echo "<option value='$user->display_name'$selected>$user->display_name</option>";
					}
					?>
          </select></td>
      </tr>
    </table>
    <p class="submit" id="submit">
      <input type="hidden" name="page" value="ln_friend"/>
      <input type="submit" name="submit_accept" class="button-primary" value="<?php _e( 'Accept Request', 'ln_livenotifications' ) ?>" <?php if(!$has_available) echo " disabled";?>/>
      <input type="submit" name="submit_reject" class="button-primary" value="<?php _e( 'Reject Request', 'ln_livenotifications' ) ?>" <?php if(!$has_available) echo " disabled";?>/>
    </p>
  </form>
</div>
<?php

}
add_shortcode( 'ln_friend', 'ln_friend' );
function ln_notifications_overview(){
	global $current_user;
	?>
<!--<div class="wrap">
		<h2> Notifications Overview</h2>
	</div>-->

<div class="postbox" id="ln_admin">
  <?php 
	//echo ln_fetch_notifications($current_user->ID, 0, -1,true,'all');
	echo ln_fetch_notifications_only($current_user->ID, 0, -1,true,'comment');
	echo '<br>';
	echo ln_fetch_notifications($current_user->ID, 0, -1,false,'');
	?>
</div>
<?php 
}
add_shortcode( 'ln_notifications_overview', 'ln_notifications_overview' );
// Runs when plugin is activated and creates new database field
register_activation_hook(__FILE__,'ln_plugin_install');
register_deactivation_hook(__FILE__,'ln_plugin_uninstall');
function ln_plugin_install() {
    add_option('ln_options', ln_defaults());
    create_tables();
}	
function ln_plugin_uninstall() {
	//drop_tables();
	//delete_option( 'ln_options' );
	//delete_option( 'ln_options1' );
	
}
function create_tables(){
	global $wpdb;
	$wpdb->query("
		CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."livenotifications` (
		  `id` int(11) NOT NULL auto_increment,
		  `userid` int(11) NOT NULL,
		  `userid_subj` int(11) NOT NULL,
		  `content_type` varchar(64) NOT NULL,
		  `content_id` int(11) NOT NULL,
		  `parent_id` int(11) NOT NULL,
		  `content_text` varchar(200) NOT NULL,
		  `is_read` int(11) NOT NULL,
		  `time` varchar(32) NOT NULL,
		  `additional_subj` int(11) NOT NULL,
		  `username` varchar(64) NOT NULL,
		  PRIMARY KEY  (`id`)
		) ;
	");
	$wpdb->query("
		CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."livenotifications_usersettings` (
		  `userid` int(10) unsigned NOT NULL,
		  `enable_comment` tinyint(4) NOT NULL,
		  `enable_reply` tinyint(4) NOT NULL,
		  `enable_award` tinyint(4) NOT NULL,
		  `enable_pm` tinyint(4) NOT NULL,
		  `enable_friend` tinyint(4) NOT NULL,
		  `enable_moderation` tinyint(4) NOT NULL,
		  `enable_taguser` tinyint(4) NOT NULL,
		  PRIMARY KEY (`userid`)
		);
	");
	$wpdb->query('
		CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'pm (
			`id` bigint(20) NOT NULL auto_increment,
			`subject` text NOT NULL,
			`content` text NOT NULL,
			`sender` varchar(60) NOT NULL,
			`recipient` varchar(60) NOT NULL,
			`date` datetime NOT NULL,
			`read` tinyint(1) NOT NULL,
			`deleted` tinyint(1) NOT NULL,
			PRIMARY KEY (`id`)
		) COLLATE utf8_general_ci;'
	);
	$wpdb->query('
		CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'userlist (
			`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`userid` INT(10) UNSIGNED NOT NULL,
			`relationid` INT(10) UNSIGNED NOT NULL,
			`type` ENUM("buddy","ignore") NOT NULL DEFAULT "buddy",
			`friend` ENUM("yes","no","pending","denied") NOT NULL DEFAULT "no",
			PRIMARY KEY (`id`)
		) COLLATE utf8_general_ci;'
	);
	$wpdb->query('
		CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'eventodropdown (
			`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
			`logourl` text NOT NULL,
			`linkname` text NOT NULL,
			`order1` INT(10) UNSIGNED NOT NULL,
			`link` text NOT NULL ,
			PRIMARY KEY (`id`)
		) COLLATE utf8_general_ci;'
	);
	$wpdb->query('CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'adminnotification (`noti_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`notification` TEXT NOT NULL ,`noti_time` VARCHAR( 50 ) NOT NULL, `inserttime` VARCHAR( 100 ) NOT NULL,, `userid` VARCHAR( 10 ) NOT NULL) ENGINE = MYISAM ;');
	
	$wpdb->query('CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'rewardsystem  (`reid` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`rew_image` VARCHAR( 200 ) NOT NULL ,`numlist` VARCHAR( 50 ) NOT NULL ,`type` VARCHAR( 100 ) NOT NULL ,`retitle` VARCHAR( 200 ) NOT NULL ,`remsg` TEXT NOT NULL ,`repoint` VARCHAR( 100 ) NOT NULL ,`reorder` INT( 100 ) NOT NULL) ENGINE = MYISAM ;');


	//Auto insert rewards into reward system, point system upon installation. Kewords: default awards, default, default points
	$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/postaw/badge1.jpg' , __FILE__ ).'","1","post","Newbie Poster","Congrats your first post!","200","1")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/comment/badge8.jpg' , __FILE__ ).'","1","comment","Newbie Commenter","Congrats your first comment!","200","5")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/msg/badge15.jpg' , __FILE__ ).'","1","message","Newbie Messenger","Congrats your first Message!","200","7")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/friends/badge21.jpg' , __FILE__ ).'","1","friend","Newbie Friender","Congrats your first friend!","200","9")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/readposts/badge28.jpg' , __FILE__ ).'","1","readpost","Newbie Reader","Congrats your first read!","200","10")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/postaw/badge2.jpg' , __FILE__ ).'","10","post","Advanced Poster","You are getting good!","200","12")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/comment/badge9.jpg' , __FILE__ ).'","5","comment","Advanced Commenter","You are getting good!","200","15")');



$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/msg/badge16.jpg' , __FILE__ ).'","5","message","Advanced Messenger","You are getting good!","200","17")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/friends/badge22.jpg' , __FILE__ ).'","5","friend","Advanced Friender","You are getting good!","200","19")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/readposts/badge29.jpg' , __FILE__ ).'","5","readpost","Advanced Reader","You are getting good!","200","21")');



$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/postaw/badge3.jpg' , __FILE__ ).'","15","post"," Knight Poster","Impressive feat!","200","23")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/comment/badge10.jpg' , __FILE__ ).'","10","comment"," Knight Commenter","Impressive feat!","200","25")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/msg/badge17.jpg' , __FILE__ ).'","20","message"," Knight Messenger","Impressive feat!","200","27")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/friends/badge23.jpg' , __FILE__ ).'","10","friend"," Knight Friender","Impressive feat!","200","29")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/readposts/badge30.jpg' , __FILE__ ).'","10","readpost"," Knight Reader","Impressive feat!","200","31")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/postaw/badge4.jpg' , __FILE__ ).'","30","post","Prince Poster","One of the best!","200","33")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/comment/badge11.jpg' , __FILE__ ).'","20","comment","Prince Commenter","One of the best!","200","35")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/msg/badge18.jpg' , __FILE__ ).'","40","message","Prince Messenger","One of the best!","200","41")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/friends/badge24.jpg' , __FILE__ ).'","20","friend","Prince Friender","One of the best!","200","43")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/readposts/badge31.jpg' , __FILE__ ).'","30","readpost","Prince Reader","One of the best!","200","45")');



$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/postaw/badge5.jpg' , __FILE__ ).'","60","post","Honorary Poster","Congrats your almost maxed","200","47")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/comment/badge12.jpg' , __FILE__ ).'","30","comment","Honorary Commenter","Congrats your almost maxed","200","49")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/msg/badge19.jpg' , __FILE__ ).'","70","message","Honorary Messenger","Congrats your almost maxed","200","51")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/friends/badge25.jpg' , __FILE__ ).'","30","friend","Honorary Friender","Congrats your almost maxed","200","53")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/readposts/badge32.jpg' , __FILE__ ).'","60","readpost","Honorary Reader","Congrats your almost maxed","200","55")');



$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/postaw/badge6.jpg' , __FILE__ ).'","80","post","Enlightened Poster","An Epic Achievement","200","56")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/postaw/badge7.jpg' , __FILE__ ).'","100","post","King of Posts","The best of the best!","200","60")');
	
    

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/comment/badge13.jpg' , __FILE__ ).'","40","comment","Enlightened Commenter","An Epic Achievement","200","57")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/msg/badge20.jpg' , __FILE__ ).'","100","message","Enlightened Messenger","An Epic Achievement","200","58")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/comment/badge14.jpg' , __FILE__ ).'","100","comment","King of Comments","The best of the best!","200","61")');
	
    
$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/friends/badge26.jpg' , __FILE__ ).'","40","friend","Enlightened Friender","An Epic Achievement","200","63")');


$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/readposts/badge33.jpg' , __FILE__ ).'","100","readpost","Enlightened Reader","An Epic Achievement","200","64")');

$wpdb->query('insert into ' . $wpdb->prefix . 'rewardsystem(rew_image,numlist,type,retitle,remsg,repoint,reorder) values("'.plugins_url( 'images/friends/badge27.jpg' , __FILE__ ).'","70","friend","King of Friends","The best of the best!","200","65")');

	
	
	//Do not touch
	$wpdb->query('CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'count_reading (`count_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`userid` int( 10 ) NOT NULL ,`postid` int( 10 ) NOT NULL ,`readtime` VARCHAR( 50 ) NOT NULL ,`posttype` VARCHAR( 100 ) NOT NULL) ENGINE = MYISAM ;');
		
	$wpdb->query('CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'countpoints (`cp_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`cp_uid` int( 10 ) NOT NULL ,`cp_pmid` int( 10 ) NOT NULL ,`cp_points` VARCHAR( 10 ) NOT NULL ,`cp_time` VARCHAR( 50 ) NOT NULL,`cp_tasklist` VARCHAR( 200 ) NOT NULL) ENGINE = MYISAM ;');
		
		$wpdb->query('CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'rankcount (`id` INT( 4 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`start_date` VARCHAR( 100 ) NOT NULL ,`update_date` VARCHAR( 100 ) NOT NULL,`daily_count` int( 100 ) NOT NULL,`user_id` VARCHAR( 100 ) NOT NULL,`rank` VARCHAR( 100 ) NOT NULL default 0) ENGINE = MYISAM ;');
	
	$wpdb->query('CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'week_rankcount (`id` INT( 4 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`start_date` VARCHAR( 100 ) NOT NULL ,`week_count` int( 100 ) NOT NULL,`user_id` VARCHAR( 100 ) NOT NULL,`rank` VARCHAR( 100 ) NOT NULL default 0,`rank_diff` INT( 100 ) NOT NULL,`rank_diff1` INT( 100 ) NOT NULL) ENGINE = MYISAM ;');
	
	$wpdb->query('CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'all_rankcount (`id` INT( 4 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`start_date` VARCHAR( 100 ) NOT NULL ,`all_count` int( 100 ) NOT NULL,`user_id` VARCHAR( 100 ) NOT NULL,`rank` VARCHAR( 100 ) NOT NULL default 0) ENGINE = MYISAM ;');
	
	$wpdb->query("CREATE TRIGGER `livenotification` AFTER INSERT ON `".$wpdb->prefix."comments` FOR EACH ROW IF (INSTR(new.comment_agent, 'Disqus'))
    THEN  insert into ".$wpdb->prefix."livenotifications (userid,userid_subj,content_type,content_id,parent_id,content_text,is_read,time,username) 
	values ((select post_author from ".$wpdb->prefix."posts where ID=new.comment_post_ID),new.user_id,'comment',new.comment_ID,new.comment_post_ID,(select post_title from ".$wpdb->prefix."posts where ID=new.comment_post_ID),'0',UNIX_TIMESTAMP(),new.comment_author);
    END IF");
	//create dynamic pages at the time of plugin installation
	// Create post object
	
	$pageindex = get_page_by_title( 'Inbox' );
	if($pageindex=="")
	{
		$my_post1 = array(
		'post_title'    => 'Inbox',
		'post_content'  => '[lnpm_inbox]',
		'post_type'     => 'page',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_category' => '',
		'comment_status' => 'closed',
		'ping_status'    => 'closed'
		);
		
		// Insert the post into the database
		$pageindex=wp_insert_post( $my_post1 );
	}
	$permalink1 = get_permalink( $pageindex);	
	
	$pagesendmsg = get_page_by_title( 'Send Message' );
	if($pagesendmsg=="")
	{
		$my_post = array(
		  'post_title'    => 'Send Message',
		  'post_content'  => '[lnpm_send]',
		  'post_type'     => 'page',
		  'post_status'   => 'publish',
		  'post_author'   => 1,
		  'post_category' => ''
		);
		
		// Insert the post into the database
		$pagesendmsg=wp_insert_post( $my_post );
	}
	$permalink = get_permalink($pagesendmsg);
	
	
	$pagefriendlist = get_page_by_title( 'Friend List' );
	if($pagefriendlist=="")
	{
		$my_post2 = array(
		'post_title'    => 'Friend List',
		'post_content'  => '[ln_friend]',
		'post_type'     => 'page',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_category' => '',
		'comment_status' => 'closed',
		'ping_status'    => 'closed');
	// Insert the post into the database
	$pagefriendlist=wp_insert_post( $my_post2 );
	}
	$permalink2 = get_permalink($pagefriendlist);
	
	$pageoutbox = get_page_by_title( 'Outbox' );
	if($pageoutbox=="")
	{
		$my_post3 = array(
  'post_title'    => 'Outbox',
  'post_content'  => '[lnpm_outbox]',
  'post_type'     => 'page',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_category' => '',
  'comment_status' => 'closed',
  'ping_status'    => 'closed'

);
$pageoutbox=wp_insert_post( $my_post3 );
// Insert the post into the database


	}
$permalink3 = get_permalink( $pageoutbox);

	$pagenotification = get_page_by_title( 'Notification' );
	if($pagenotification=="")
	{
		$my_post4 = array(
  'post_title'    => 'Notification',
  'post_content'  => '[ln_notifications_overview]',
  'post_type'     => 'page',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_category' => '',
  'comment_status' => 'closed',
  'ping_status'    => 'closed'

);

// Insert the post into the database
$pagenotification=wp_insert_post( $my_post4 );
	}
	
$permalink4 = get_permalink( $pagenotification);
	
	
	
	$pageeditprof = get_page_by_title( 'Edit Profile' );
	if($pageeditprof=="")
	{
$my_post5 = array(
  'post_title'    => 'Edit Profile',
  'post_content'  => '[edit_profile]',
  'post_type'     => 'page',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_category' => '',
  'comment_status' => 'closed',
  'ping_status'    => 'closed'

);

// Insert the post into the database
$pageeditprof=wp_insert_post( $my_post5 );
	}
$permalink5 = get_permalink( $pageeditprof);


//award page

$pageaward = get_page_by_title( 'Award' );
	if($pageaward=="")
	{
$my_post6 = array(
  'post_title'    => 'Award',
  'post_content'  => '[award_list]',
  'post_type'     => 'page',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_category' => '',
  'comment_status' => 'closed',
  'ping_status'    => 'closed'

);

// Insert the post into the database
$pageaward=wp_insert_post( $my_post6 );
	}
$permalink6 = get_permalink( $pageaward);



$update_val = array('plink_sendmsg' => $permalink ,'plink_viewmsg' => $permalink1 ,'plink_friendlist' => $permalink2 ,'plink_outbox'=>$permalink3,'plink_noti' => $permalink4,'plink_editpro' => $permalink5,'plink_award' => $permalink6);
update_option('ln_options1', $update_val);


}
function drop_tables(){
	global $wpdb;
	$wpdb->query("
		DROP TABLE IF EXISTS `" .$wpdb->prefix. "livenotifications`
	");
	$wpdb->query("
		DROP TABLE IF EXISTS `" .$wpdb->prefix. "livenotifications_usersettings`
	");
	$wpdb->query("
		DROP TABLE IF EXISTS `" .$wpdb->prefix. "pm`
	");
	$wpdb->query("
		DROP TABLE IF EXISTS `" .$wpdb->prefix. "userlist`
	");
	$wpdb->query("
		DROP TABLE IF EXISTS `" .$wpdb->prefix. "eventodropdown`
	");
	$wpdb->query("
		DROP TABLE IF EXISTS `" .$wpdb->prefix. "rewardsystem`
	");
}
//add in database  
if(isset($_POST['ln_update1']))
{
	for($i=0;$i<count($_POST['eventologourl']);$i++)
	{
		$insert_record=mysql_query("insert into " .$wpdb->prefix. "eventodropdown (logourl,linkname,order1,link) values('".$_POST['eventologourl'][$i]."','".$_POST['linkname'][$i]."','".$_POST['order'][$i]."','".$_POST['logolink'][$i]."')");
	
	}
}

if(isset($_POST['ln_update4']))
{
	 
	 
		$selectuser=mysql_query("select * from " .$wpdb->prefix. "users where ID!='1'");
		if(mysql_num_rows($selectuser)>0)
		{
			while($selectuserrec=mysql_fetch_array($selectuser))
			{
				$insert_record1=mysql_query("insert into " .$wpdb->prefix. "livenotifications (userid,userid_subj,content_type,content_id,parent_id,content_text,is_read,time,additional_subj,username) values('".$selectuserrec['ID']."','1','adminnotification','0','0','".$_POST['notification']."','0','".time()."','".$_POST['noti_time']."','admin')");
			}
			$_SESSION['succ']="Notification Added Successfully";
		}
	
		
	
}

//update record

if(isset($_POST['ln_update2']) && $_POST['uprecord']=='uprecord')
{
	for($i=0;$i<count($_POST['eventologourl']);$i++)
	{
		$update_record=mysql_query("update " .$wpdb->prefix. "eventodropdown set logourl ='".$_POST['eventologourl'][$i]."',linkname='".$_POST['linkname'][$i]."',order1='".$_POST['order'][$i]."',link='".$_POST['logolink'][$i]."' where id='".$_POST['myid']."'");
		echo '<meta http-equiv="refresh" content="0; url='.admin_url( 'admin.php?page=ln_backend_menu', 'http' ).'/">';
	}
}

//add award detail
if(isset($_POST['ln_update6']))
{	
	$selectreward=mysql_query("select * from " .$wpdb->prefix. "rewardsystem where reorder='".$_POST['reorder']."'");
	if(mysql_num_rows($selectreward)==0)
	{
	$insert_record1=mysql_query("insert into " .$wpdb->prefix. "rewardsystem (rew_image,numlist,type,retitle,remsg,repoint,reorder) values('".$_POST['rew_image']."','".$_POST['numlist']."','".$_POST['type']."','".$_POST['retitle']."','".$_POST['remsg']."','".$_POST['repoint']."','".$_POST['reorder']."')");
	}
	else
	{
		$_SESSION['error']="please insert another order for reward system";
	}
}
//add award detail
if(isset($_POST['ln_update5']))
{	
	$insert_record1=mysql_query("update ".$wpdb->prefix. "rewardsystem set rew_image='".$_POST['rew_image']."',numlist='".$_POST['numlist']."',type='".$_POST['type']."',retitle='".$_POST['retitle']."',remsg='".$_POST['remsg']."',repoint='".$_POST['repoint']."',reorder='".$_POST['reorder']."' where reid='".$_POST['updateid']."'");
	
	echo '<meta http-equiv="refresh" content="0; url='.admin_url( 'admin.php?page=ln_backend_menu', 'http' ).'/">';
	exit;
}
// update the ln_livenotifications options
if(isset($_POST['ln_update'])){
	update_option('ln_options', ln_updates());
}


function ln_updates() {
	$options = $_POST['ln_options'];
	$update_val = array(
		'banner_bgcolor' => $options['banner_bgcolor'],
    	'dropdown_bgcolor' => $options['dropdown_bgcolor'],
		'dropdown_border_color' => $options['dropdown_border_color'],
		'dropdown_link_color' => $options['dropdown_link_color'],
		'dropdown_color' => $options['dropdown_color'],
		'dropdown_bit_bgcolor' => $options['dropdown_bit_bgcolor'],
		'dropdown_hover_bgcolor' => $options['dropdown_hover_bgcolor'],
	    'logo_url' => $options['logo_url'],
	    'update_interval' => $options['update_interval'],
	    'max_notifications_count' => $options['max_notifications_count'],
	   	'cut_strlen' => $options['cut_strlen'],
		'enable_comment' => $options['enable_comment'],
	    'enable_reply' => $options['enable_reply'],
		'enable_award' => $options['enable_award'],
		'enable_taguser' => $options['enable_taguser'],
    	'enable_pm' => $options['enable_pm'],
	    'enable_friend' => $options['enable_friend'],
	    'enable_moderation' => $options['enable_moderation'],
		'ln_enable_search' => $options['ln_enable_search'],
	    'ln_swich_search' => $options['ln_swich_search'],
	    'hide_avatar' => $options['hide_avatar'],
		'ln_avatar_height' => $options['ln_avatar_height'],
		'ln_default_avatar' => $options['ln_default_avatar'],
		'max_age' => $options['max_age'],
		'disable_default_bar' => $options['disable_default_bar'],
		'hide_wp_notification' =>$options['hide_wp_notification'],
		'ln_enable_userdropdown' => $options['ln_enable_userdropdown'],
		'ln_enable_userdropdown_logout' => $options['ln_enable_userdropdown_logout'],
		'ln_enable_award_link'=>$options['ln_enable_award_link'],
		'ln_udd_morelinks' => $options['ln_udd_morelinks'],
		'ln_fblink' => $options['ln_fblink'],
		'ln_email' => $options['ln_email'],
		'ln_bmpopup'=>$options['ln_bmpopup'],
		'ln_linked' => $options['ln_linked'],
		'ln_pinterest' => $options['ln_pinterest'],
		'ln_stumbleupon' => $options['ln_stumbleupon'],
		'ln_reddit' => $options['ln_reddit'],
		'ln_twlink' => $options['ln_twlink'],
		'ln_golink' => $options['ln_golink'],
		'ln_gocode' => $options['ln_gocode'],
		'ln_fbapi' => $options['ln_fbapi'],
		'ln_fbsecret' => $options['ln_fbsecret']
    );
	return $update_val;
}
function ln_backend_menu()
{

wp_nonce_field('update-options'); 
$options = get_option('ln_options'); 
$options1 = get_option('ln_options1'); 
if($options['disable_default_bar']=='on')
{
	echo '<style type="text/css">#wpadminbar { display:none !important; }html.wp-toolbar{padding-top: 0px !important;
}</style>';


function theme_styles()  
{ 
echo '<style type="text/css">#wpadminbar { display:none !important; }html.wp-toolbar{padding-top: 0px !important;
}</style>';
}
add_action('wp_enqueue_scripts', 'theme_styles');




}

?>
<div class="wrap">
  <div id="icon-themes" class="icon32"></div>
  <h2>Wordpress Notifications Settings - By vBSocial.com</h2>
  </td>
</div>
<h2>Drive activity, website addiction, and site performance with our premium features. <a href="http://vbsocial.com/buy-wordpress-notifications">**Go Pro Now &raquo;**</a></h2>
<div class="postbox" id="ln_admin">
  <form method="post">
    <table>
      <tr>
        <td><h2>Design and Logo</a></h2></td><p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p>
        </td>
      </tr>
      <tr>
        <td><?php _e("Logo (Change the logo here.)", 'ln_livenotifications'); ?>
          :</td>
        <td><label for="upload_logo_image">
            <input id="upload_logo_image" type="text" size="36" name="ln_options[logo_url]" value="<?php echo $options['logo_url'] ?>" />
            <input id="upload_logo_image_button" type="button" value="Upload Image" />
          </label>
          (Copy uploaded image url and paste into textbox)</td>
      </tr>
      <tr>
        <td><?php _e("Header background bar", 'ln_livenotifications'); ?>
          :</td>
        <td><select name="ln_options[banner_bgcolor]">
            <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Dark Gray</option>
        <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
        <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
        <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
        <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
        <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
        <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
        <option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
          </select></td>
      </tr>
      <tr>
        <td><?php _e("Dropdown Background Color", 'ln_livenotifications'); ?>
          :</td>
        <td><select name="ln_options[dropdown_bgcolor]">
            <option <?php selected('#333333', $options['dropdown_bgcolor']); ?> value="#333333">Dark Gray</option>
<option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
<option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
<option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
<option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
<option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
<option <?php selected('#333333', $options['banner_bgcolor']); ?> value="#333333">Locked</option>
          </select></td>
      </tr>
      <tr>
        <td><?php _e("Dropdown Boder Color", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[dropdown_border_color]" value="<?php echo $options['dropdown_border_color'] ?>" /></td>
      </tr>
      <tr>
        <td><?php _e("Dropdown Link Color", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[dropdown_link_color]" value="<?php echo $options['dropdown_link_color'] ?>" /></td>
      </tr>
      <tr>
        <td><?php _e("Dropdown fore Color", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[dropdown_color]" value="<?php echo $options['dropdown_color'] ?>" /></td>
      </tr>
      <tr>
        <td><?php _e("Dropdown bit Background Color", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[dropdown_bit_bgcolor]" value="<?php echo $options['dropdown_bit_bgcolor'] ?>" /></td>
      </tr>
      <tr>
        <td><?php _e("Dropdown bit hover Color", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[dropdown_hover_bgcolor]" value="<?php echo $options['dropdown_hover_bgcolor'] ?>" /></td>
      </tr>
      <tr>
        <td><h2>Optimization</a></h2></td>
        
      </tr>
      <tr>
        <td><?php _e("Optimization Setting: Update how often the system should ping your server for new notifications.", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[update_interval]" value="<?php echo $options['update_interval'] ?>"readonly /></td>
        
      </tr>
      <tr>
        <td><?php _e("Max Notifications Count", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[max_notifications_count]" value="<?php echo $options['max_notifications_count'] ?>" readonly/></td>
      </tr>
      <tr>
        <td><?php _e("String Length of Notification Links", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[cut_strlen]" value="<?php echo $options['cut_strlen'] ?>" readonly/></td>
      </tr>
      <tr>
        <td><?php _e("Enable Notification when someone posts a comment", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[enable_comment]" <?php if($options['enable_comment']) echo "checked"; ?> readonly /></td>
      </tr>
      <tr>
        <td><h2>Types of Notifications</a></h2></td>
      </tr>
      <tr>
        <td><?php _e("Enable Notification when someone posts a reply to a comment", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[enable_reply]" <?php if($options['enable_reply']) echo "checked"; ?> /></td>
      </tr>
       <tr>
        <td><?php _e("Enable Notification of awards", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[enable_award]" <?php if($options['enable_award']) echo "checked"; ?> /></td>
      </tr>
      
      <tr>
        <td><?php _e("Enable Notification to the tagged user", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[enable_taguser]" <?php if($options['enable_taguser']) echo "unchecked"; ?> onclick="return false"/></td>
      </tr>
      <tr>
        <td><?php _e("Enable Notification of private message", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[enable_pm]" <?php if($options['enable_pm']) echo "unchecked"; ?> onclick="return false"/></td>
      </tr>
      <tr>
        <td><?php _e("Enable Friend Request Notification", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[enable_friend]" <?php if($options['enable_friend']) echo "unchecked"; ?> onclick="return false" /></td>
      </tr>
      <tr>
        <td><?php _e("Enable Notification awaiting moderation", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[enable_moderation]" <?php if($options['enable_moderation']) echo "unchecked"; ?>onclick="return false" /></td>
      </tr>
      <tr>
        <td><?php _e("Enable Search", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[ln_enable_search]" <?php if($options['ln_enable_search']) echo "checked"; ?>onclick="return false" /></td>
      </tr>
      <tr>
        <td><h2>User Menu Settings</a><p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p></h2></td>
      </tr>
      <tr>
        <td><?php _e("Hide Avatar In Notification Dropdown box", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[hide_avatar]" <?php if($options['hide_avatar']) echo "checked"; ?> /></td>
      </tr>
      <tr>
        <td><?php _e("Avatar Height", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_avatar_height]" value="<?php echo $options['ln_avatar_height'] ?>" /></td>
      </tr>
      <tr>
        <td><?php _e("Default Avatar(Enter an URL or upload an image for default avatar)", 'ln_livenotifications'); ?>
          :</td>
        <td><label for="upload_default_avatar_image">
            <input id="upload_default_avatar_image" type="text" size="36" name="ln_options[ln_default_avatar]" value="<?php echo $options['ln_default_avatar'] ?>" />
            <input id="upload_default_avatar_image_button" type="button" value="Upload Image" />
          </label>
          (Copy uploaded image url and paste into textbox)</td>
      </tr>
      <tr>
        <td><?php _e("Max Notifications Age in days", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[max_age]" value="<?php echo $options['max_age'] ?>" /></td>
      </tr>
      <tr>
        <td><?php _e("Enable User Dropdown box", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[ln_enable_userdropdown]" <?php if($options['ln_enable_userdropdown']) echo "checked"; ?> /></td>
      </tr>
      <tr>
        <td><?php _e("Enable Logout in User Dropdown box", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[ln_enable_userdropdown_logout]" <?php if($options['ln_enable_userdropdown_logout']) echo "checked"; ?> /></td>
      </tr>
      <tr>
        <td><?php _e("Enable Award Link in User Dropdown box", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[ln_enable_award_link]" <?php if($options['ln_enable_award_link']) echo "checked"; ?> /></td>
      </tr>
      <tr>
        <td><?php _e("Add Custom Links to User Dropdown box", 'ln_livenotifications'); ?>
          :</td>
        <td>Premium Only</td>
      </tr>
      <tr>
        <td><h2>Social Virality Settings</a><p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p></h2></td>
      </tr>
      <tr>
        <td><?php _e("Facebook Page Link", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_fblink]" value="<?php echo($options['ln_fblink']); ?>" /></td>
      </tr>
      <tr>
        <td><?php _e("Twitter Page Link", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_twlink]" value="Premium Only" readonly /></td>
      </tr>
      <tr>
        <td><?php _e("Google Page Link", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_golink]" value="Premium Only" readonly /></td>
      </tr>
      <tr>
        <td><?php _e("Pinterest Page Link", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_pinterest]" value="Premium Only" readonly /></td>
      </tr>
      <tr>
        <td><?php _e("LinkedIn Page Link", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_linked]" value="Premium Only" readonly /> </td>
      </tr>
      <tr>
        <td><?php _e("Stumbleupon Page Link", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_stumbleupon]" value="Premium Only" readonly /></td>
      </tr>
      <tr>
        <td><?php _e("Reddit Page Link", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" name="ln_options[ln_reddit]" value="Premium Only" readonly /></td>
      </tr>
      <tr>
        <td><?php _e("Email Message content Link", 'ln_livenotifications'); ?>
          :</td>
        <td>
        
        <input type="radio" name="ln_options[ln_email]" value="yes" <?php if($options['ln_email']=='yes'){ ?> checked="checked" <?php } ?>/>Yes &nbsp;<input type="radio" name="ln_options[ln_email]" value="no" <?php if($options['ln_email']=='no'){ ?> checked="checked" <?php } ?>/>No</td>
      </tr>
      <tr>
        <td><?php _e("Bottom Left Popup Enable/Disable", 'ln_livenotifications'); ?>
          :</td>
        <td>
        
        <input type="radio" name="ln_options[ln_bmpopup]" value="enable" <?php if($options['ln_bmpopup']=='enable'){ ?> checked="checked" <?php } ?>>Enable
        <input type="radio" name="ln_options[ln_bmpopup]" value="disable" <?php if($options['ln_bmpopup']=='disable'){ ?> checked="checked" <?php } ?>>Disable</td>
      </tr>
     
      <tr>
      	<td><h2>Search Bar Selection<p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p></h2></td>
      </tr>
      <tr>
     	<td><input type="radio" name="ln_options[ln_swich_search]" value="google" <?php if($options['ln_swich_search']=='google'){ ?> checked="checked" <?php } ?>/>For Google</td>
      <td><input type="radio" name="ln_options[ln_swich_search]" value="wordpress" <?php if($options['ln_swich_search']=='wordpress'){ ?> checked="checked" <?php } ?>/>For Wordpress</td>
      </tr>
       <tr>
        <td colspan="2"><h2>Google Search Integration (please fill if you select google Search Bar )</a><p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p></h2></td>
      </tr>
      <tr>
        <td><?php _e("Google Custom Search code", 'ln_livenotifications'); ?></td>
        <td><textarea name="ln_options[ln_gocode]" cols="20" rows="3"> <?php echo stripslashes($options['ln_gocode']); ?> </textarea>
          <p>&nbsp;</p>
          Go here for <a href="https://www.google.com/cse/all">Google Search Snippet</a> and add in the above box.</td>
      </tr>
      
      <tr>
      	<td><h2>Facebook Api Detail For Login</h2></td>
      </tr>
     <tr>
        <td><?php _e("Facebook App Id", 'ln_livenotifications'); ?>
          :</td>
        <td>
        
        <input type="text" name="ln_options[ln_fbapi]" value="<?php echo $options['ln_fbapi']; ?>"></td>
      </tr>
           <tr>
        <td><?php _e("Facebook Secret Key", 'ln_livenotifications'); ?>
          :</td>
        <td>
        
        <input type="text" name="ln_options[ln_fbsecret]" value="<?php echo $options['ln_fbsecret']; ?>"> <p>&nbsp;</p>
          Go here for<a href="https://developers.facebook.com/apps" target="_blank"> Get Facebook Api</a> and add in the above boxes.
        </td>
      </tr>

      <?php /*?> <tr>
					<td><?php _e("Page Link For Viewing Friend List", 'ln_livenotifications'); ?> :</td>
					<td><input type="text" name="ln_options1[plink_friendlist]" value="<?php echo($options['plink_friendlist']); ?>" /> </td>
				</tr>
                <tr>
					<td><?php _e("Page Link For Inbox", 'ln_livenotifications'); ?> :</td>
					<td><input type="text" name="ln_options1[plink_viewmsg]" value="<?php echo($options['plink_viewmsg']); ?>" /> </td>
				</tr>
                 <tr>
					<td><?php _e("Page Link For OutBox", 'ln_livenotifications'); ?> :</td>
					<td><input type="text" name="ln_options1[plink_outbox]" value="<?php echo($options['plink_outbox']); ?>" /> </td>
				</tr>
                <tr>
					<td><?php _e("Page Link For Send Message", 'ln_livenotifications'); ?> :</td>
					<td><input type="text" name="ln_options1[plink_sendmsg]" value="<?php echo($options['plink_sendmsg']); ?>" /> </td>
				</tr><?php */?>
        <td><h2>Turn Header Bars On and Off</a><p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p></h2></td>
      <tr>
        <td><?php _e("Settings", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="checkbox" name="ln_options[disable_default_bar]" <?php if($options['disable_default_bar']) echo "checked"; ?>/>
          Disable the Wordpress Default Admin Bar<br />
          <p>&nbsp;</p>
          <input type="checkbox" name="ln_options[hide_wp_notification]" <?php if($options['hide_wp_notification']) echo "checked"; ?>/>
          Hide the WP Notifications bar when inside wp-admin. Sometimes it may conflict inside admin with other mods.<br /></td>
      </tr>
    </table>
    <p class="button-controls">
      <input type="submit" value="<?php _e('Save') ?>" class="button-primary" id="ln_update" name="ln_update"/>
    </p>
  </form>
</div>
<div class="wrap">
  <div id="icon-themes" class="icon32"></div>
  <h2>Logo Drop Down for Improved Navigation<p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p></h2>
  <h4>Inspired by Envato.com</h4>
</div>
<div class="postbox" id="ln_admin">

  <?php 
		if((isset($_GET['task'])=='delete') && isset($_GET['id1']))
{
	mysql_query("delete from wp_eventodropdown where id='".$_GET['id1']."'");	
	echo '<meta http-equiv="refresh" content="0; url='.admin_url( 'admin.php?page=ln_backend_menu', 'http' ).'/">';
}

				if((isset($_GET['task'])=='delete') && isset($_GET['deleteid']))
{
	mysql_query("delete from wp_rewardsystem where reid='".$_GET['deleteid']."'");	
	echo '<meta http-equiv="refresh" content="0; url='.admin_url( 'admin.php?page=ln_backend_menu', 'http' ).'/">';
	exit;
}

		if((isset($_GET['task'])=='update') && isset($_GET['id']))
{
	$selectrecforup=mysql_fetch_array(mysql_query("select * from wp_eventodropdown where id='".$_GET['id']."'"));
	
}
		if((isset($_GET['task'])=='update') && isset($_GET['editid']))
{
	$selectrecforreward=mysql_fetch_array(mysql_query("select * from wp_rewardsystem where reid='".$_GET['editid']."'"));
	
}
	

?>
  
  
  <br />
  <?php
		$selectdata=mysql_query("select * from wp_eventodropdown");
		if(mysql_num_rows($selectdata)>0)
		{
			echo '<table border="1"><tr>';
			echo '<td width="20%" align="center">Logo Url</td><td width="20%" align="center">Name For Link</td><td width="20%" align="center">Logo Order</td><td width="20%" align="center">Logo Link</td><td width="20%" align="center">Action</td></tr>';
			while($selectrec=mysql_fetch_array($selectdata))
			{
				echo '<tr><td align="center"><img src='.$selectrec['logourl'].' width="50"  height="50"></td><td align="center">'.$selectrec['linkname'].'</td><td align="center">'.$selectrec['order1'].'</td><td>'.$selectrec['link'].'</td><td><a href="?page=ln_backend_menu&id='.$selectrec['id'].'&task=update">Edit</a>&nbsp;&nbsp;<a href="?page=ln_backend_menu&id1='.$selectrec['id'].'&task=delete">Delete</a></td></tr>';
			}
			echo '</table>';
		}
	?>
</div>
<div class="wrap">
  <div id="icon-themes" class="icon32"></div>
  <h2>Send a Global Notification/Annoucement that all users will read.</h2>
  <h3>These get displayed in Global Dropdown<p style="color:red;font-size:12px;">Unlock all premium settings<a href="http://vbsocial.com/buy-wordpress-social-network"> here.</a></p></h3>
</div>
<div class="postbox" id="ln_admin">
  <?php 
		if(isset($_SESSION['succ']))
		{
			echo $_SESSION['succ'];
			unset($_SESSION['succ']);
		}

?>
  
</div>
<div class="wrap">
  <div id="icon-themes" class="icon32"></div>
  <h2>Reward and Point System</h2>
</div>
<div class="postbox" id="ln_admin">
 <strong style="color:#F00;"> <?php 
		if(isset($_SESSION['succ']))
		{
			echo $_SESSION['succ'];
			unset($_SESSION['succ']);
		}
			if(isset($_SESSION['error']))
		{
			echo $_SESSION['error'];
			unset($_SESSION['error']);
		}

?></strong>
  <form method="post">
    <table id="evento"  style="width:950px !important;">
      <tr>
        <td><?php _e("Reward Title", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" id="retitle" name="retitle" value="<?php echo $selectrecforreward['retitle']; ?>"/></td>
      </tr>
      <tr>
        <td><?php _e("Reward Accomplishment", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" id="numlist" name="numlist" value="<?php echo $selectrecforreward['numlist']; ?>"/>
          <select name="type" id="type">
            <option value="post" <?php if($selectrecforreward['type']=='post'){ ?> selected="selected" <?php }?>>post</option>
            <option value="message" <?php if($selectrecforreward['type']=='message'){ ?> selected="selected" <?php }?>>message sent</option>
            <option value="comment" <?php if($selectrecforreward['type']=='comment'){ ?> selected="selected" <?php }?>>comment</option>
            <option value="friends" <?php if($selectrecforreward['type']=='friends'){ ?> selected="selected" <?php }?>>friends</option>
            <option value="readpost" <?php if($selectrecforreward['type']=='readpost'){ ?> selected="selected" <?php }?>>readpost</option>
          </select></td>
      </tr>
      <tr>
        <td><?php _e("Reward Message", 'ln_livenotifications'); ?>
          :</td>
        <td><textarea name="remsg" id="remsg"><?php echo $selectrecforreward['remsg']; ?></textarea></td>
      </tr>
      <tr>
        <td><?php _e("Reward Point", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" id="repoint" name="repoint" value="<?php echo $selectrecforreward['repoint']; ?>"/></td>
      </tr>
      <tr>
        <td><?php _e("Reward Order", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" id="reorder" name="reorder" value="<?php echo $selectrecforreward['reorder']; ?>" <?php if(!empty($selectrecforreward['reorder'])) { ?> readonly="readonly" <?php } ?>/></td>
      </tr>
      <tr>
        <td><?php _e("Reward Image", 'ln_livenotifications'); ?>
          :</td>
        <td><input type="text" id="rew_image" name="rew_image" value="<?php echo $selectrecforreward['rew_image']; ?>"/>
          <input id="upload_rew_image" type="button" value="Upload Image" />
          (Copy uploaded image url and paste into textbox)</td>
      </tr>
    </table>
    <?php if(!empty($selectrecforreward['reid'])) { ?>
    <p class="button-controls">
      <input type="submit" value="<?php _e('Update') ?>" class="button-primary" id="ln_update5" name="ln_update5"/>
      <input type="hidden" value="<?php echo $selectrecforreward['reid']; ?>" name="updateid"/>
    </p>
    <?php } else { ?>
    <p class="button-controls">
      <input type="submit" value="<?php _e('Save') ?>" class="button-primary" id="ln_update6" name="ln_update6"/>
    </p>
    <?php } ?>
  </form>
  <?php
		$selectrewdata=mysql_query("select * from wp_rewardsystem");
		if(mysql_num_rows($selectrewdata)>0)
		{
			echo '<table border="1" style="width:950px;"><tr>';
			echo '<td width="10%" align="center">Reward Image</td>
				<td width="15%" align="center">Reward Title</td>
				<td width="20%" align="center">Reward Message</td>
				<td width="10%" align="center">Reward Point</td>
				<td width="10%" align="center">Reward Order</td>
				<td width="20%" align="center">Reward Achivement</td>
				<td width="35%" align="center">Action</td></tr>';
			while($selectrewrec=mysql_fetch_array($selectrewdata))
			{
				echo '<tr>
				<td align="center"><img src='.$selectrewrec['rew_image'].' width="50"  height="50"></td>
				<td align="center">'.$selectrewrec['retitle'].'</td>
				<td align="center">'.$selectrewrec['remsg'].'</td>
				<td align="center">'.$selectrewrec['repoint'].'</td>
				<td align="center">'.$selectrewrec['reorder'].'</td>
				<td align="center">'.$selectrewrec['numlist'].' '.$selectrewrec['type'].'</td>
				<td align="center"><a href="?page=ln_backend_menu&editid='.$selectrewrec['reid'].'&task=update">Edit</a>&nbsp;&nbsp;<a href="?page=ln_backend_menu&deleteid='.$selectrewrec['reid'].'&task=delete">Delete</a></td></tr>';
			}
			echo '</table>';
		}
	?>
</div>
<?php
}

//--------------------------------------------------------------------------------------------------------------------------------------
/* Get Current URL */
if ( !function_exists('ln_login_current_url') ) {
	function ln_login_current_url( $url = '' ) {

		$pageURL  = force_ssl_admin() ? 'https://' : 'http://';
		$pageURL .= esc_attr( $_SERVER['HTTP_HOST'] );
		$pageURL .= esc_attr( $_SERVER['REQUEST_URI'] );

		if ($url != "nologout") {
			if (!strpos($pageURL,'_login=')) {
				$rand_string = md5(uniqid(rand(), true));
				$rand_string = substr($rand_string, 0, 10);
				$pageURL = add_query_arg('_login', $rand_string, $pageURL);
			}
		}

		return strip_tags( $pageURL );
	}
}

function ln_livenotifications(){

	$options = get_option('ln_options');
	echo '<script type="text/javascript">
			var ln_timer;
			var update_interval = '.(max(20,$options['update_interval']) * 1000).'
			var base_url = "'.get_option("siteurl").'"	;
		</script>';
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$user_name = $current_user->user_login; 
	
	$ln_notificationcount = array();
	$ln_notificationcount['comment'] = ln_count_user_notifications($user_id,'comment');
	if($options['enable_pm']){
		$ln_notificationcount['pm'] = ln_count_user_notifications($user_id,'pm');
	}
	if($options['enable_friend']){
		$ln_notificationcount['friend'] = ln_count_user_notifications($user_id,'friend');
	}

	if($options['enable_moderation']){
		$ln_notificationcount['moderation'] = ln_count_user_notifications($user_id,'moderation');
	}
	
	?>
<div id="ln_livenotifications" class="run_once">
  <div class="ln_topsec">
    <?php if($options['logo_url']){ ?>
    <a href="<?php echo get_site_url(); ?>"><img class="ln_logo" src="<?php echo $options['logo_url'];?>" height="40" /></a>
    <?php
			}
			$selectevento=mysql_query("select * from wp_eventodropdown order by order1 ASC");
			 	if(mysql_num_rows($selectevento)>0)
			 {
				echo '<a href="javascript:void(0);" id="eventodropdown" onclick="eventodropdown();" ><img src="' . plugins_url( 'images/drop_down.png' , __FILE__ ) . '"  style="width:17px;position:relative;float:left;top:12px;margin-left:-18px;" > </a>';
			 }
			 ?>
    <div id="menuOrder" style="display:none">
      <!--<a href="javascript:void(0)" id="searchHeaderHide" onclick="eventodropdown(2);"><div style="display: inline;float: right;height: 15px;margin: 2px 9px 0; font-weight:bold;color:#FFF">X</div> </a>-->
      <ul>
        <?php 
			$selectevento=mysql_query("select * from wp_eventodropdown order by order1 ASC");
			 	if(mysql_num_rows($selectevento)>0)
			 { 
				 while($evantorec=mysql_fetch_array($selectevento))
				 {
					 echo '<li>';
					 echo '<a href="'.$evantorec['link'].'">';
					 echo '<div class="topProperties">
					 <div class="top_image">
					 	<img src="'.$evantorec['logourl'].'">
					</div>
						<div class="top_title">'.$evantorec['linkname'].'</div>
					</div>
					</a></li>';
				 }
			 }
			 ?>
      </ul>
     
    </div>
    
    <?php if (is_user_logged_in()) {?>
    <?php if($options['ln_enable_userdropdown']) {
		if(!is_admin())
{ ?>
 		<script type="text/javascript" language="javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
         <?php } ?>
     <script type="text/javascript">
				
					jQuery(document).ready(function(){
						ln_create_userpane();
					});
				</script>
    <?php } ?>
    <div class="welcomelink">
     
		<!--point counting of awards-->
				<?php 
				global $wpdb;
				$options1 = get_option('ln_options1'); 
				$url2 = $options1["plink_award"]; 
	
							 $currentuserid=get_current_user_id();
							 $countpoints=mysql_query("SELECT SUM(cp_points) FROM ".$wpdb->prefix."countpoints WHERE cp_uid='".$currentuserid."'");
							 while($row1243= mysql_fetch_array($countpoints)){        
									$cp = $row1243['SUM(cp_points)'];
								}
							 ?> <a href="<?php echo $url2; ?>" class="popupctrl">
              <div class="pointmain" style="margin-right:10px;margin-top:3px;">
							<div class="pointsub" style="width:52px;"><font color="#f6b70a" style="font-size:16px;font-weight:bold;"><?php if($cp!=0){echo $cp;}else {echo '0';} ?></font><br />
				  <font color="#FFFFFF"  style="font-weight:bold; font-size:10px;"><?php if($cp!=0){echo 'Points';}else {echo 'Points';} ?></font></div>
			  </div></a>

	  <!----------end award points ---->
       <?php if($options['ln_enable_userdropdown']){ ?>
      <div id="user-dropdown" class="popupbody popuphover"></div>
      <a id="userName1" onclick="ln_clickuser(event); return false;" class="popupctrl userdropdownlink" href="profile.php"><span><?php echo ln_display_useravatar($current_user->ID); ?></span></a>
      
      <a onclick="ln_clickuser(event); return false;" class="popupctrl userdropdownlink" href="profile.php" id="userName"><span style="position:absolute;margin-top:7px;color:#fff; text-transform: uppercase;" ><?php echo $user_name; ?></span></a>
      <?php }
						else{ echo $user_name; ?>
      <?php }?>
      <div class="socialdropdown2" style="margin-left:30px;">
		<div id="socialIcons" class="">
	<?php /*?><a href="<?php echo get_site_url(); ?>" class="popupctrl"><span>Home</span></a><?php */?>
	  
     	<?php if($options['ln_fblink']!='' || $options['ln_pinterest']!='' || $options['ln_linked']!='' || $options['ln_stumbleupon']!='' || $options['ln_reddit']!='' || $options['ln_email']!='' || $options['ln_twlink']!='' || $options['ln_golink']!=''){ ?>
        <a class="popupctrl" href="javascript:void(0);" onclick="customeSearch1();" id="afterloginsocial"><i class="icon-thumbs-up"></i><span id="title">Social</span></a>
        <?php } ?>
		
		<a class="popupctrl" href="javascript:void(0);" id="SearchTop" onclick="customeSearch();"><i class="icon-search"></i><span>Search</span></a>
		</div>
		
		<div id="mysearch" style="display:none" >
          <?php 
		  if($options['ln_swich_search']=='google'){
		  	if($options['ln_gocode']!=' '){
				 echo stripslashes($options['ln_gocode']);
				 }
		  }
		  else{
			  echo '<div class="test" style="float:right; margin-right:10%; padding:5px 10px 9px 10px;background:#FFFFFF; width:305px; border: 1px solid #c5c5c5; margin-top:-13px;-webkit-box-shadow: 0 3px 8px rgba(0, 0, 0, .25);border-radius: 0px 0px 3px 3px; ">';
			  get_search_form();
			  echo '</div>';
			  }
		  ?>
        </div>
		 
	  
        <div id="mysearch1" style="display:none;">
          <div style="float:right;margin-left:45px;padding: 1px;min-width:265px;width:93%;">
            <?php if($options['ln_fblink']!=''){
				$options = get_option('ln_options'); 
				$FB_APP_ID=$options['ln_fbapi'];
				if(empty($FB_APP_ID))
				{
					$FB_APP_ID='206818086031539';
				}
 ?>
            <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo $options['ln_fblink']; ?>&amp;width=450&amp;height=21&amp;colorscheme=light&amp;layout=box_count&amp;action=like&amp;show_faces=false&amp;send=false&amp;appId=<?php $FB_APP_ID; ?>" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:30px; height:67px;float:left;margin-top:3px" allowTransparency="true"></iframe>
            <?php } ?>
            
			<?php if($options['ln_twlink']!=''){ ?>
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $options['ln_twlink']; ?>" data-via="" data-count="vertical">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
            <?php } ?>
             <?php if($options['ln_golink']!=''){ ?>
						  
						  <!-- Place this tag where you want the +1 button to render. -->
						  <div class="g-plusone" data-size="tall" data-href="<?php echo $options['ln_golink']; ?>"></div>
						  
						  <!-- Place this tag after the last +1 button tag. -->
						  <script type="text/javascript">
							  (function() {
								var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
								po.src = 'https://apis.google.com/js/plusone.js';
								var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
							  })();
							</script>
						  <?php } ?>
            <?php if($options['ln_pinterest']!=''){ ?>
        <div id="pinit"><a href="http://www.pinterest.com/pin/create/button/
        ?url==<?php echo $options['ln_pinterest']; ?>&description=Next%20stop%3A%20Pinterest"
        data-pin-do="buttonPin"
        data-pin-config="above"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>
		<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script></div>
            <?php } ?>
            
            <?php if($options['ln_linked']!=''){ ?>
            <div id="linkedin"><script src="//platform.linkedin.com/in.js" type="text/javascript">
 				lang: en_US
				</script>
            <script type="IN/Share" data-url="<?php echo $options['ln_linked']; ?>" data-counter="top"></script></div>
            <?php } ?>
            <?php if($options['ln_stumbleupon']!=''){ ?>
            <!-- Place this tag where you want the su badge to render -->
            <su:badge layout="5" location="<?php echo $options['ln_stumbleupon']; ?>"></su:badge>
            
            <!-- Place this snippet wherever appropriate -->
            <script type="text/javascript">
				  (function() {
					var li = document.createElement('script'); li.type = 'text/javascript'; li.async = true;
					li.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + '//platform.stumbleupon.com/1/widgets.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(li, s);
				  })();
				</script>
            <?php } ?>
            <?php if($options['ln_reddit']!=''){ ?>
            <div id="redit"><a href="<?php echo $options['ln_reddit']; ?>" onclick="window.location = 'http://www.reddit.com/submit?url=' + encodeURIComponent(window.location); return false"><img src="http://www.reddit.com/static/spreddit7.gif" alt="submit to reddit" border="0" /></a></div>
            <?php } ?>
            <?php if($options['ln_email']=='yes'){ ?>
            <div class="emails" id="emails"><span>Email</span></div>
            <div id="email_form" style="display:none; border:1px solid #333; border-radius:2px; margin-top:10px;z-index;9999;right:0px;float:left;width:100%;max-width:295px;position:absolute;background:white;">
            <?php 
				if(isset($_POST['send_email'])){
				$to=$_POST['email'];
				$subject=$_POST['subject'];
				$message=$_POST['message'];
				$headers = "From: webmaster@example.com" . "\r\n" ;
				mail($to,$subject,$message,$headers);
				} 
			?>
        <table border="1" style="margin-left:10px;">
          <form method="post" style="float:right;background:white;">
            <tr>
            	<td>Email:</td>
        	    <td> <input type="text" name="email" placeholder="Enter Email Address for send info" /></td>
             </tr>
             <tr>
             	<td>Subject:</td>
                <td><input type="text" name="subject" value="Page Link" /></td>
             </tr>
             <tr>
				<td>Message:</td>
				<td><textarea name="message" style="width: 179px !important; height: 79px !important;"><?php echo get_permalink($ID); ?></textarea></td>
			</tr>
			<tr><br />
			<td colspan="2" align="center" style="text-align:center !important;">
                <input type="submit" class="btn lgrgButtons" name="send_email" value="Send Mail"  />
			</td></tr>          </form>
           </table> </div>
          
            <?php } ?>
           
            
            <!-- <a href="javascript:void(0);" id="searchHeaderHide" onclick="customeSearchcloase1();"><span style="margin:-8px 5px 0px 0px;color:#FFF;">X</span></a>-->
            
          </div>
          
        </div>
     
	  </div>
		<!----edit by vbsocial9xvers-->
		<div class="socialdropdownMenu">
	 
     
		<a class="popupctrl" href="javascript:void(0);" id="SearchTop" onclick="customeSearchmenu();"><i class="icon-search"></i><span>Search</span></a>
			<?php if($options['ln_fblink']!='' || $options['ln_pinterest']!='' || $options['ln_linked']!='' || $options['ln_stumbleupon']!='' || $options['ln_reddit']!='' || $options['ln_email']!='' || $options['ln_twlink']!='' || $options['ln_golink']!=''){ ?>
        <a class="popupctrl" href="javascript:void(0);" onclick="customeSearchsocailmenu();" id="afterloginsocial"><i class="icon-thumbs-up"></i><span id="title">Social</span></a>
        <?php } ?>
		<!--Added By vbsocial9xvers --><?php /*?>	<a href="<?php echo get_site_url(); ?>" class="popupctrl"><i class="icon-home"></i><span>Home</span></a><?php */?>
		<!-------points award system---->
		<?php 
		global $wpdb;
		$options1 = get_option('ln_options1'); 
				$url2 = $options1["plink_award"]; 
					 $currentuserid=get_current_user_id();
					 $countpoints=mysql_query("SELECT SUM(cp_points) FROM ".$wpdb->prefix."countpoints WHERE cp_uid='".$currentuserid."'");
					 while($row1243= mysql_fetch_array($countpoints)){        
							$cp = $row1243['SUM(cp_points)'];
						}
						
	  ?>
      <a href="<?php echo $url2; ?>" class="popupctrl">
      <div class="pointmain">
                    <div class="pointsub" style="width:52px;"><font color="#FFFF00" style="font-size:12px;font-weight:bold;"><?php if($cp!=0){echo $cp;}else {echo '0';} ?></font><br />
				  <font color="#FFFFFF"  style="font-weight:bold;"><?php if($cp!=0){echo 'points';}else {echo 'point';} ?></font></div>
      </div></a>
	
      <div id="mysearchMenu" style="display:none" >
          <?php 
		  if($options['ln_swich_search']=='google'){
		  	if($options['ln_gocode']!=' '){
				 echo stripslashes($options['ln_gocode']);
				 }
		  }
		  else{
			  echo '<div class="test" style="float:right; margin-right:10%; padding:5px 10px 9px 10px;background:#FFFFFF; width:305px; border: 1px solid #c5c5c5; margin-top:-13px;-webkit-box-shadow: 0 3px 8px rgba(0, 0, 0, .25);border-radius: 0px 0px 3px 3px; ">';
			  get_search_form();
			  echo '</div>';
			  }
		  ?>
        </div>
		 
	  
        <div id="mysearch1Menu" style="display:none;">
          <div style="float:right;margin-left:45px;padding: 1px;min-width:265px;width:93%;">
            <?php if($options['ln_fblink']!=''){
				$options = get_option('ln_options'); 
				$FB_APP_ID=$options['ln_fbapi'];
				if(empty($FB_APP_ID))
				{
					$FB_APP_ID='206818086031539';
				}
 ?>
            <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo $options['ln_fblink']; ?>&amp;width=450&amp;height=21&amp;colorscheme=light&amp;layout=box_count&amp;action=like&amp;show_faces=false&amp;send=false&amp;appId=<?php $FB_APP_ID; ?>" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:30px; height:67px;float:left;margin-top:3px" allowTransparency="true"></iframe>
            <?php } ?>
            
			<?php if($options['ln_twlink']!=''){ ?>
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $options['ln_twlink']; ?>" data-via="" data-count="vertical">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
            <?php } ?>
             <?php if($options['ln_golink']!=''){ ?>
						  
						  <!-- Place this tag where you want the +1 button to render. -->
						  <div class="g-plusone" data-size="tall" data-href="<?php echo $options['ln_golink']; ?>"></div>
						  
						  <!-- Place this tag after the last +1 button tag. -->
						  <script type="text/javascript">
							  (function() {
								var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
								po.src = 'https://apis.google.com/js/plusone.js';
								var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
							  })();
							</script>
						  <?php } ?>
            <?php if($options['ln_pinterest']!=''){ ?>
        <div id="pinit"><a href="http://www.pinterest.com/pin/create/button/
        ?url==<?php echo $options['ln_pinterest']; ?>&description=Next%20stop%3A%20Pinterest"
        data-pin-do="buttonPin"
        data-pin-config="above"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>
		<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script></div>
            <?php } ?>
            
            <?php if($options['ln_linked']!=''){ ?>
            <div id="linkedin"><script src="//platform.linkedin.com/in.js" type="text/javascript">
 				lang: en_US
				</script>
            <script type="IN/Share" data-url="<?php echo $options['ln_linked']; ?>" data-counter="top"></script></div>
            <?php } ?>
            <?php if($options['ln_stumbleupon']!=''){ ?>
            <!-- Place this tag where you want the su badge to render -->
            <su:badge layout="5" location="<?php echo $options['ln_stumbleupon']; ?>"></su:badge>
            
            <!-- Place this snippet wherever appropriate -->
            <script type="text/javascript">
				  (function() {
					var li = document.createElement('script'); li.type = 'text/javascript'; li.async = true;
					li.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + '//platform.stumbleupon.com/1/widgets.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(li, s);
				  })();
				</script>
            <?php } ?>
            <?php if($options['ln_reddit']!=''){ ?>
            <div id="redit"><a href="<?php echo $options['ln_reddit']; ?>" onclick="window.location = 'http://www.reddit.com/submit?url=' + encodeURIComponent(window.location); return false"><img src="http://www.reddit.com/static/spreddit7.gif" alt="submit to reddit" border="0" /></a></div>
            <?php } ?>
            <?php if($options['ln_email']=='yes'){ ?>
            <div class="emails" id="emails"><span>Email</span></div>
            <div id="email_form" style="display:none; border:1px solid #333; border-radius:2px; margin-top:10px;z-index;9999;right:0px;float:left;width:100%;max-width:295px;position:absolute;background:white;">
            <?php 
				if(isset($_POST['send_email'])){
				$to=$_POST['email'];
				$subject=$_POST['subject'];
				$message=$_POST['message'];
				$headers = "From: webmaster@example.com" . "\r\n" ;
				mail($to,$subject,$message,$headers);
				} 
			?>
        <table border="1" style="margin-left:10px;">
          <form method="post" style="float:right;background:white;">
            <tr>
            	<td>Email:</td>
        	    <td> <input type="text" name="email" placeholder="Enter Email Address for send info" /></td>
             </tr>
             <tr>
             	<td>Subject:</td>
                <td><input type="text" name="subject" value="Page Link" /></td>
             </tr>
             <tr>
				<td>Message:</td>
				<td><textarea name="message" style="width: 179px !important; height: 79px !important;"><?php echo get_permalink($ID); ?></textarea></td>
			</tr>
			<tr><br />
			<td colspan="2" align="center" style="text-align:center !important;">
                <input type="submit" class="btn lgrgButtons" name="send_email" value="Send Mail"  />
			</td></tr>          </form>
           </table> </div>
          
            <?php } ?>
           
            
            <!-- <a href="javascript:void(0);" id="searchHeaderHide" onclick="customeSearchcloase1();"><span style="margin:-8px 5px 0px 0px;color:#FFF;">X</span></a>-->
            
          </div>
          
        </div>
	  </div>
    <!----end script edit by vbsocial9xvers-->
	</div>
    <div id="toplinks" class="toplinks">
      <ul class="isuser">
        <input type="hidden" value="<?php echo plugins_url( '' , __FILE__ );?>" id="pluginURL"/>
        <?php if($options['ln_enable_search']){?>
        <?php /*?><div id="searchWeb" style="display:none">
                    <form method="get"  action="<?php bloginfo('home'); ?>/">
						<div id="sform">
							<input type="text" size="put_a_size_here" name="s" id="s" value="Search" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;"/>
						</div>
					</form>
					
                            </div>
                    <li id="searchWebImg">
							<a onclick="searchOpen();" href="javascript:void(0);">
								<img src="<?php echo plugins_url( 'images/Black_Search.png' , __FILE__ );?>" height="16" width="16" />
							</a>
                    
						</li><?php */?>
        <?php }?>
        <li class="ln_popupmenu " id="livenotifications"><a onclick="ln_fetchnotifications('comment',event); return false;" class="popupctrl " href="#"><img src="<?php echo plugins_url( 'images/world.png' , __FILE__ );?>" height="23" width="23" /><span class="livenotifications_num" style="<?php if ($ln_notificationcount['comment']) {?>visibility: visible;<?php }else{?>visibility: hidden;<?php }?>" id="livenotifications_num"><?php echo $ln_notificationcount['comment']; ?></span></a>
          <ul class="popupbody popuphover" id="livenotifications_list">
          </ul>
        </li>
        <li class="ln_popupmenu " id="livenotifications_pm" style="<?php if($options['enable_pm']){?>visibility: visible;<?php }else{?>visibility: hidden;width:0;<?php }?>"><a onclick="ln_fetchnotifications('pm',event); return false;" class="popupctrl" href="#"><img src="<?php echo plugins_url( 'images/message_notification.png' , __FILE__ );?>" height="23" width="23" /><span class="livenotifications_num_pm" style="<?php if($ln_notificationcount['pm']){?>visibility: visible;<?php }else{?>visibility: hidden;<?php }?>" id="livenotifications_num_pm"><?php echo $ln_notificationcount['pm']; ?></span></a>
          <ul class="popupbody popuphover" id="livenotifications_list_pm">
          </ul>
        </li>
        <li class="ln_popupmenu " id="livenotifications_friend" style="<?php if($options['enable_friend']){?>visibility: visible;<?php }else{?>visibility: hidden;width:0;<?php }?>"><a onclick="ln_fetchnotifications('friend',event); return false;" class="popupctrl" href="#"><img src="<?php echo plugins_url( 'images/friend_notification.png' , __FILE__ );?>" height="23" width="23" /><span class="livenotifications_num_friend" style="<?php if($ln_notificationcount['friend']){?>visibility: visible;<?php }else{?>visibility: hidden;<?php }?>" id="livenotifications_num_friend"><?php echo $ln_notificationcount['friend'];?></span></a>
          <ul class="popupbody popuphover " id="livenotifications_list_friend">
          </ul>
        </li>
        <li class="ln_popupmenu " id="livenotifications_moderation" style="<?php if($options['enable_moderation'] &&  current_user_can('manage_options')){?>visibility: visible;<?php }else{?>visibility: hidden;width:0;<?php }?>"><a onclick="ln_fetchnotifications('moderation',event); return false;" class="popupctrl" href="#"><img src="<?php echo plugins_url( 'images/moderation_notification.png' , __FILE__ );?>" height="23" width="23" /><span class="livenotifications_num_moderation" style="<?php if($ln_notificationcount['moderation']){?>visibility: visible;<?php }else{?>visibility: hidden;<?php }?>" id="livenotifications_num_moderation"><?php echo $ln_notificationcount['moderation'];?></span></a>
          <ul class="popupbody popuphover" id="livenotifications_list_moderation">
          </ul>
        </li>
      </ul>
      <?php if($options['ln_enable_search']){?>
      <?php /*?><form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
						<div id="sform">
							<input type="text" size="put_a_size_here" name="s" id="s" value="Search" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;"/>
						</div>
					</form><?php */?>
     <?php }?>
    </div>
   <!-----------------------------------------------------------Edit By vbsocial9xvers started----------------------------------------> 
    <?php } else{?>
    <div style="float:right" id="lv_socialLinks">
		
		<div class="ln_signin" style="float:right;"><!--edit by vbsocial9xvers <a href="javascript:ln_show_signin(event);">Sign In</a>-->
        <?php /*?><a href="<?php echo get_site_url(); ?>" class="popupctrl"><i class="icon-home"></i><span>Home</span></a><?php */?>
					<?php if($options['ln_fblink']!='' || $options['ln_pinterest']!='' || $options['ln_linked']!='' || $options['ln_stumbleupon']!='' || $options['ln_reddit']!='' || $options['ln_email']!='' || $options['ln_twlink']!='' || $options['ln_golink']!=''){ ?>
				<a class="popupctrl" href="javascript:void(0);" onclick="customeSearch2();" id="beforeloginsocial"><i class="icon-thumbs-up"></i><span id="title">Social</span></a>
				<?php } ?>	

			   <a class="popupctrl" href="javascript:void(0);" id="SearchTop" onclick="customeSearch4();"><?php /*?><img src="<?php echo plugins_url( 'images/search-icon.png' , __FILE__ );?>" height="24" width="24" /><?php */?><i class="icon-search"></i><span>search</span></a>
				<a href="javascript:void(0);" id="signIn"><i class="icon-user"></i><span id="title">Sign In or Register</span></a>	
					
          	
		</div>
			
		<?php /*?><div id="newMenuSearch" style="float:right;">
				<a href="javascript:void(0);" class="signIn"><i class="icon-user"></i><span id="title">Sign In</span></a>		
					<?php if($options['ln_fblink']!='' || $options['ln_pinterest']!='' || $options['ln_linked']!='' || $options['ln_stumbleupon']!='' || $options['ln_reddit']!='' || $options['ln_email']!='' || $options['ln_twlink']!='' || $options['ln_golink']!=''){ ?>
				<a id="afterloginsocial" class="popupctrl" href="javascript:void(0);" onclick="customeSearch1();"><i class="icon-thumbs-up"></i><span id="title">Social</span></a>
				<?php } ?>	
					<a href="<?php echo get_site_url(); ?>" class="popupctrl"><i class="icon-home"></i><span>Home</span></a>
		
			</div><?php */?>
	  
	  
	  <div class="socialdropdown1" style="margin-right:40px; margin-top:14px;">
       <div class="SignUpBox ln_dropdown_nouser" ><!-- edit by vbsocial9xvers add SignUpBox class-->
	  <form name="loginform" id="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post" style="padding:7px">
            <p>
              <label for="user_login">
                <?php _e('Username') ?>
                <br />
                <input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" tabindex="10" />
              </label>
            </p>
            <p>
              <label for="user_pass">
                <?php _e('Password') ?>
                <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" />
              </label>
            </p>
            <?php do_action('login_form'); ?>
            <p class="forgetmenot" style="margin-bottom:5px">
              <label for="rememberme">
                <input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php checked( $rememberme ); ?> />
                <?php esc_attr_e('Remember Me?'); ?>
              </label>
            </p>
            <p class="submit">
              <input type="submit" name="wp-submit" id="wp-submit" class="button-primary lgrgButtons" value="<?php esc_attr_e('Log In'); ?>" tabindex="100" />
              <input type="hidden" name="redirect_to" value="<?php echo esc_attr(ln_login_current_url()); ?>" />
              <input type="hidden" name="testcookie" value="1" />
              <a href="javascript:void(0)">
              <input type="button" name="wp-submit" id="wp-submit" class="button-primary lgrgButtons" value="Register" tabindex="100" />
              </a>
              <?php if(!empty($options['ln_fbapi']) && !empty($options['ln_fbsecret'])) {?>
        <a href="<?php echo plugins_url() . '/vbsocial-notifications/extensions/facebook.php'; ?>" style="clear: both; float: right; padding: 1px ! important;"><img src="<?php echo plugins_url() . '/vbsocial-notifications/images/login.gif';?>"></a>
<?php } ?>
</p><br />
              
		<?php /*?><a href="#" class="signIn" style="float:right !important;color:#000;">&times;</a><?php */?>
          </form>
        </div>
       <div id="mysearch">
          <?php 
		  if($options['ln_swich_search']=='google'){
		  	if($options['ln_gocode']!=' '){
				 echo stripslashes($options['ln_gocode']);
				 }
		  }
		  else{
			  echo '<div class="test2" style="float:right;margin-right:10%; padding:4px 10px 16px 10px;background:#FFF; width:300px;border: 1px solid #c5c5c5; -webkit-box-shadow: 0 3px 8px rgba(0, 0, 0, .25);-webkit-border-radius: 3px;border: 1px solid rgba(100, 100, 100, .4);border-radius: 0px 0px 3px 3px; margin-top:-1px; ">';
			  get_search_form();
			
			  echo '</div>';
			  }
		  ?>
          
        </div>
       
				<div id="mysearch1" style="display:none">
							<div id="iSocials">
						   <?php if($options['ln_fblink']!=''){ $options = get_option('ln_options'); 
							$FB_APP_ID=$options['ln_fbapi'];
							if(empty($FB_APP_ID))
							{
								$FB_APP_ID='206818086031539';
							}
				?>
						  <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo $options['ln_fblink']; ?>&amp;width=450&amp;height=21&amp;colorscheme=light&amp;layout=box_count&amp;action=like&amp;show_faces=false&amp;send=false&amp;appId=<?php echo $FB_APP_ID; ?>" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:30px; height:67px;float:left;margin-top:3px" allowTransparency="true"></iframe>
						  <?php } ?>
						  <?php if($options['ln_twlink']!=''){ ?>
						  <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $options['ln_twlink']; ?>" data-via=""  data-count="vertical">Tweet</a>
						  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
						  <?php } ?>
						  
						  <?php if($options['ln_golink']!=''){ ?>
						  
						  <!-- Place this tag where you want the +1 button to render. -->
						  <div class="g-plusone" data-size="tall" data-href="<?php echo $options['ln_golink']; ?>"></div>
						  
						  <!-- Place this tag after the last +1 button tag. -->
						  <script type="text/javascript">
							  (function() {
								var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
								po.src = 'https://apis.google.com/js/plusone.js';
								var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
							  })();
							</script>
						  <?php } ?>
						  <?php if($options['ln_pinterest']!=''){ ?>
							<div id="pinit"><a href="http://www.pinterest.com/pin/create/button/
						?url==<?php echo $options['ln_pinterest']; ?>&description=Next%20stop%3A%20Pinterest"
						data-pin-do="buttonPin"
						data-pin-config="above"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a><script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script></div>
							<?php } ?>
							  <?php if($options['ln_linked']!=''){ ?>
							<div id="linkedin"><script src="//platform.linkedin.com/in.js" type="text/javascript">
								lang: en_US
								</script>
							<script type="IN/Share" data-url="<?php echo $options['ln_linked']; ?>" data-counter="top"></script></div>
							<?php } ?>
							<?php if($options['ln_stumbleupon']!=''){ ?>
							<!-- Place this tag where you want the su badge to render -->
							<su:badge layout="5" location="<?php echo $options['ln_stumbleupon']; ?>"></su:badge>
							
							<!-- Place this snippet wherever appropriate -->
							<script type="text/javascript">
							  (function() {
								var li = document.createElement('script'); li.type = 'text/javascript'; li.async = true;
								li.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + '//platform.stumbleupon.com/1/widgets.js';
								var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(li, s);
							  })();
							</script>
						<?php } ?>
							<?php if($options['ln_reddit']!=''){ ?>
							<div id="redit"><a href="<?php echo $options['ln_reddit']; ?>" onclick="window.location = 'http://www.reddit.com/submit?url=' + encodeURIComponent(window.location); return false"><img src="http://www.reddit.com/static/spreddit7.gif" alt="submit to reddit" border="0" /></a></div>
							<?php } ?>
						 
						  <a href="javascript:void(0)" onclick="customeSearch2();" id="loginsocial"><div style="display: inline;float: right;height: 15px;margin: 22px 9px 0; font-weight:bold;color:#CCC">&times;</div> </a>
						  </div>
                          
				</div>
      </div>
   
		
  </div>
  <?php }?>
  <!-----------------------------------------------------------Edit By vbsocial9xvers Ended-------------------------------------------------------> 
  <a href="#"class="ln_close"><i class="icon-double-angle-up"></i></a></div>
<a href="JavaScript:void(0);"class="ln_botsec" style="background-color:<?php echo $options['banner_bgcolor'] ?>"></a>
</div>
<?php
}

add_action('bbp_new_topic', 'send_new_topic_notification');
add_action('bbp_new_reply','send_new_reply_notification');
add_action('wp_footer', 'ln_livenotifications');
add_action('admin_head', 'ln_livenotifications');
add_action('comment_post', 'ln_add_comment_notifications',10,2);
add_action('wp_set_comment_status', 'ln_add_comment_notifications',11,2);
add_action('after_delete_post', 'ln_remove_post_notifications',10,1);
add_action('trashed_post', 'ln_remove_post_notifications',10,1);
add_action('untrashed_post', 'ln_add_post_notifications',10,1);
add_action('wp_insert_post', 'ln_add_post_notifications',10,1);
add_action('wp_insert_post', 'ln_add_userdetail',10,1);
add_action('comment_post', 'ln_add_userdetail_comment',10,2);


function pinterest_post_page_pin_horiz() {
global $post;
/* HORIZONTAL PINTEREST BUTTON WITH COUNTER */
printf( '<div class="pinterest-posts"><a href="http://pinterest.com/pin/create/button/?url=%s&media=%s" class="pin-it-button" count-layout="horizontal">Pin It</a><script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script></div>', urlencode(get_permalink()), urlencode( get_post_meta($post->ID, 'thesis_post_image', true) ) );
}
add_action( 'thesis_hook_before_post_box', 'pinterest_post_page_pin_horiz' );
$ln_usersettings_cache=array();
function ln_count_user_notifications($userid,$type) {
	global $wpdb;

	if($type == 'pm'){
		$cond = " AND content_type = 'pm' ";
	}
	else if($type == 'friend'){
		$cond = " AND content_type!='friend_comment' AND content_type!='friend_post' AND substring(content_type,1,4) = 'frie' ";
	}
	else if($type == 'moderation'){
		$cond = " AND substring(content_type,1,4) = 'mod_'";
	}
	else {
		$cond = " AND content_type <> 'pm' AND substring(content_type,1,4) <> 'frie' AND substring(content_type,1,4) <> 'mod_'";
	}
	$sql = "SELECT COUNT(id) AS num FROM " . $wpdb->prefix . "livenotifications
		WHERE userid = " . (int)$userid . " AND is_read = 0 ".$cond;
	
	$res = $wpdb->get_row($sql);

	return (!$res || empty($res->num)) ? 0 : (int)$res->num;

}

function ln_add_user_notification($userid_cause, $userid_target, $content_type, $content_id, $content_text, $parent_id=0, $updatetime=0, $username_cause="",$status="") {
	global $wpdb, $ln_usersettings_cache;
	$prefix = "";
	if(strlen($content_type) > 4) $prefix = substr($content_type,0,4);
	
	
	if (!isset($ln_usersettings_cache[$userid_target])) $ln_usersettings_cache[$userid_target] = ln_fetch_useroptions($userid_target);

	switch ($content_type) {
		case 'comment':
			if (!$ln_usersettings_cache[$userid_target]['enable_comment']) return;
			break;

		case 'reply':
			if (!$ln_usersettings_cache[$userid_target]['enable_reply']){ return;}
			break;	
		case 'mention':
			if (!$ln_usersettings_cache[$userid_target]['enable_taguser']) return;
			break;
		case 'pm':
			if (!$ln_usersettings_cache[$userid_target]['enable_pm']) return;
			break;
		case 'friend':
		case 'friend_comment':
		case 'friend_post':
			if (!$ln_usersettings_cache[$userid_target]['enable_friend']) return;
			break;
		case 'mod_comment':
			if (!$ln_usersettings_cache[$userid_target]['enable_moderation']) return;
			break;
		default:
			return;
	}

	
	if($content_type == 'friend'){
		$where = " content_type = 'friend' AND ((userid = ".$userid_target." AND userid_subj = ".$userid_cause.")
			OR (userid = ".$userid_cause." AND userid_subj = ".$userid_target."))";
	}
	else{//mod_comment, comment, reply, mention, friend_comment, friend_post
		$where = " userid = ". (int)$userid_target." AND content_type = '".$content_type."' AND content_id = " . (int)$content_id." AND parent_id = " . (int)$parent_id;
	}
	/*
	else{
		$where = " AND content_type = '".$content_type."' " ;
		$where .= ($content_type=='reply'||$content_type=='comment' ? " AND parent_id = ".(int)$parent_id : " AND content_id = " . (int)$content_id);
	}
	*/
	$sql = "SELECT id, userid,userid_subj, additional_subj AS n , content_text FROM " . $wpdb->prefix . "livenotifications
		WHERE " ;

	$sql .= $where ;

	$check = $wpdb->get_row($sql);

	if($updatetime == 0) $updatetime = time();
	
	if ($check && !empty($check) && $check->id > 0) {

		// User already has a notification about this, lets add ours
		// if awaiting moderation count is 0 then remove this notification
		// else if old count is different with new count, then update
		if($content_type == "mod_comment" ){
			if((string)$content_text == "" || (string)$content_text == "0"){
				ln_delete_onenotification($check->id);
			}
			else if($content_text != $check->content_text){
				$sql = "UPDATE " . $wpdb->prefix . "livenotifications
					SET time = '" . time() . "',
					content_text = '" . htmlspecialchars(($content_text)) . "',
					is_read = 0
					WHERE id = " . (int)$check->id;
				$wpdb->query($sql);
				
			}
			
		}
		// if status is spam, trash or delete then remove this notification
		// else update table
		else if($content_type == "comment" || $content_type == "reply" || $content_type == "mention" || $content_type == "friend_comment" || $content_type == "friend_post"){
			if ((string)$status == "1" || (string) $status == "approve"){
				$sql = "UPDATE " . $wpdb->prefix . "livenotifications
					SET time = '" . $updatetime . ",
					content_text = '" . htmlspecialchars(($content_text)) . "'
					WHERE id = " . (int)$check->id;
				$wpdb->query($sql);
				
			}
			else{
				ln_delete_onenotification($check->id);
			}
		}
		
	} else {
		// Create new notification
		if($content_type == "mod_comment" && (string)$status != "0" && (string)$status != "hold") return;
		if($content_type == "comment" || $content_type == "reply" || $content_type == "mention" || $content_type == "friend_comment" || $content_type == "friend_post"){
			
			if((string)$status != "1" && (string)$status != "approve") {
				return;
			}
			
		}
		$is_red = 0;
		if($content_type == "pm"){
			$content_text = get_excerpt($content_text,0);
		}
		
		$sql = "INSERT INTO " . $wpdb->prefix . "livenotifications
			(`userid`, `userid_subj`, `content_type`, `content_id`, `parent_id`, `content_text`, `is_read`, `time`, `additional_subj`, `username`) VALUES
			(" . (int)$userid_target
			. ", " . (int)$userid_cause
			. ", '" . $content_type
			. "', " . (int)$content_id
			. ", " .  (int)$parent_id
			. ", '" . htmlspecialchars(($content_text))
			. "', " . $is_red
			. ", '" . $updatetime
			. "', " . "0,'".$username_cause . "');";

		$wpdb->query($sql);

	}

	return true;
}

function ln_update_notifications($userid,$start=0, $count=-1, $type){

}
function ln_remove_unnecessary_notifications($userid){
	
}

function ln_delete_onenotification($id){
	global $wpdb;
	$sql = "DELETE FROM " . $wpdb->prefix . "livenotifications WHERE id = ".$id ;
	return $wpdb->query($sql);
}
function ln_check_newinstall($comparetime){
	global $vbulletin;
	$sql = "SELECT * FROM " . $wpdb->prefix . "livenotifications ORDER BY time limit 0,1 ";

	$ln = $vbulletin->db->query_first($sql);

	if (!empty($ln) && ($ln['time']+120) < $comparetime) return true;
	return false;
}

function ln_fetch_notifications_only($userid, $start=0, $count=-1, $full=false,$type='all' ,$is_first=true) {
	global $wpdb;
	global $current_user1;
    get_currentuserinfo();
	  
	$site_url = get_option( 'siteurl' );
	$options = get_option('ln_options');
	$options1 = get_option('ln_options1');
	
	$output = '';
	
	$update_ids = array(); // ids which we will mark as read in the next step
	$override_status = array();

	if($type == 'all'){
		$cond = " ";
	}
	else if($type == 'comment'){
		$cond = " AND  l.content_type <> 'pm' AND substring(l.content_type,1,4) <> 'frie'  AND substring(l.content_type,1,4) <> 'mod_' ";
		//$output = "<li class='ln_title'>Notifications</li>";
		$output = "";
	}
	else if($type == 'pm'){
		$cond = " AND l.content_type = 'pm'  AND l.userid_subj > 0 ";
		$output = "<li class='ln_title'>Private Messages<a href='".$options1['plink_sendmsg']."'>Send New Messages</a> </li>";
		
	}
	else if($type == 'friend'){
		$cond = " AND content_type!='friend_comment' AND content_type!='friend_post' AND substring(l.content_type,1,4) = 'frie'  ";
		$output = "<li class='ln_title'>Friend Request</li>";
	}
	if($type == 'moderation'){
		$cond = " AND substring(l.content_type,1,4) = 'mod_'  ";
		$output = "<li class='ln_title'>Moderations</li>";
	}
	$sql = "
		SELECT
			l.*
		FROM
			" . $wpdb->prefix . "livenotifications AS l
		WHERE
			l.userid = " . (int)$userid . "
			".$cond."
		ORDER BY
			l.is_read, l.time DESC
	";
	
	$res = $wpdb->get_results($sql);
	$total_numrows = count($res);
	
	if ($start >= 0 && $count > 0) $sql .= " LIMIT ".(int)$start.", ".(int)$count;

	$res = $wpdb->get_results($sql);
	
	
	if ($full && isset($_REQUEST['lntransf']) && !empty($_REQUEST['lntransf'])) {
		$override_status = explode(",",$_REQUEST['lntransf']);
		array_walk($override_status, 'intval');
	} else {
		$override_status = array();
	}
	$scrollpane_height = 230;
	if(!$full) {
		$numrows = count($res);
		$output .= '<li style="width:330px;"><ul class="ln_scrollpane"';
		if($numrows > 4){
			$output .= ' style="height: '.$scrollpane_height.'px;"';
		}
		else{
			$scrollpane_height = 0;
		}
		$output .= ">";
	}

	foreach ($res as $notification) {
		if (!$notification->is_read) $update_ids[] = (int)$notification->id; // Set pulled notifications as red
		
		$is_read = ($full && in_array($notification->id, $override_status)) ? false : $notification->is_read;
		
		switch ($notification->content_type) {

			case 'comment':
				
				$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;

				$phrase = ($notification->additional_subj > 0)
				? sprintf("%s and %d more have commented on your article %s",
						$notification->username,
						$notification->additional_subj,
						ln_wrap_url($url, $notification->content_text))
						: sprintf("<strong>%s</strong> has commented on your article %s",
								$notification->username,
								ln_wrap_url($url, $notification->content_text));
				break;
					
					
			case 'reply':
				
				$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;

				$phrase = ($notification->additional_subj > 0)
				? sprintf("%s and %d more responsed to your comment",
						$notification->username,
						$notification->additional_subj)
						: sprintf("<strong>%s</strong> responsed to your comment",
								$notification->username);
				break;
			case 'mention':
				if($notification->parent_id == 0){
					$url = $site_url . '/' . "?p=" . $notification->content_id;
				}
				else{
					$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;
				}
				
				$phrase = ($notification->additional_subj > 0)
				? sprintf("%s and %d more mentioned you in article %s",
						$notification->username,
						$notification->additional_subj,ln_wrap_url($url, $notification->content_text))
						: sprintf("%s mentioned you in article %s",
								$notification->username, ln_wrap_url($url, $notification->content_text));
				break;
					
			case 'pm':
				if($full){
					$url = $site_url . '/wp-admin/admin.php?page=lnpm_inbox';
					
					$phrase = sprintf("%s sent you private message: %s",
							$notification->username,
							ln_wrap_url($url, $notification->content_text));
				}
				else{
						
					$notification_time = $notification->time;
					
					$phrase1 = $notification->username;
					$phrase2 = $notification->content_text;
				}
				break;
			case 'friend':

				$phrase = sprintf("%s has added you",
						$notification->username);

				break;
			
			/*case 'friend_comment':
			
				$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;
			
				$phrase = sprintf("%s has posted on %s",
								$notification->username,
								ln_wrap_url($url, $notification->content_text));
				break;
			case 'friend_post':
					
				$url = $site_url . '/' . "?p=" . $notification->content_id;
					
				$phrase = sprintf("%s has created %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;*/
			case 'mod_comment':
				$url = $site_url . '/wp-admin/edit-comments.php';
				$phrase = sprintf( __('%d comment(s) awaiting for your approval'), $notification->content_text );

				break;
				
			case 'adminnotification':
				$url = '';
				$myvariable='adminnotification';	
				$phrase = sprintf("<strong>Sitewide Message:</strong>  %s",
						ln_wrap_url($url, $notification->content_text));
				break;
				
			case 'bbpressnotification':
				$url = '';
				$myvariable='bbpressnotification';	
				$myid=$notification->content_id;
				$phrase = sprintf("<strong>%s</strong> Added new topic %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;
			case 'bbpressnotificationreply':
				$url = '';
				$myvariable='bbpressnotificationreply';	
				$myid=$notification->content_id;
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;
			case 'postaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;	
				case 'readpostaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;	
				case 'commentaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;	
				case 'messageaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;
				case 'newfriendaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;	
			default:
				
				$phrase = "Has performed an unknown operation...";
		}
		$time = ln_get_timeformat($notification->time);
		
		$right_width = 260;
		$request_status_class = "request_status";
		if($options['hide_avatar']) {
			$right_width = $right_width + $options['ln_avatar_height'];
			$request_status_class = "request_status_noavatar";
		}
		if(($type == "pm" || $type == "friend" || $type == "moderation") && !$full){
			
			if($type == "pm"){
				$output .= '<li onclick="ln_show_pm_other('.$notification->content_id.',event);" class="lnpmbit '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
					. ln_fetch_useravatar($notification->userid_subj)
					. '<div style="float:left;width:'.($right_width + 7).'px;"><div class="'.$request_status_class.'" ><p class="ln_sender_name">'
					. $phrase1
					. '</p>'
					. '<p class="ln_content">'
					. $phrase2
					. '</p>'
					. '<p class="ln_time">'.$time.'</p>'
					. '</div>
					<div class="actions"  >
						<input type="button" id="confirm" onclick="ln_pm_delete_action('.$notification->content_id.',event);" name="pm_delete" value="Delete">
					</div>
					</div><div style="clear:both;"></div>'
								. '</li></div>
					   <div style="clear:both;"></div>'
										. "\n";
				$output .= '<li class="lnpmbit livenotificationbit red ln_pm_inner_window" id="ln_pm_inner_window_'.$notification->content_id.'" style="display:none; " onclick="ln_pm_innerwindow_click(event);">'
						. '<div onclick="ln_back_to_messages('.$notification->content_id.','.$scrollpane_height.');" class="ln_link" id="ln_pm_back_'.$notification->content_id.'">Back to Messages</div>'
								. ln_fetch_useravatar($notification->userid_subj)
								. '<div style="float:left;width:'.$right_width.'px;">
								<div class="'.$request_status_class.'" ><p class="ln_sender_name">'
								. $phrase1
								. '</p>'
								. '<p class="ln_content">'
								. $phrase2
								. '</p>'

								. '<p class="ln_time">'.$time.'</p>'
								. '</div>
					</div><div style="clear:both;"></div>
					<div style="border-top: 1px solid #DDD; margin-top: 4px;padding-top: 4px;">'
								. ln_fetch_useravatar($userid)
								. '<div style="float: right; width: '.($right_width + 10).'px;">
								<textarea name="reply_'.$notification->content_id.'" id="reply_'.$notification->content_id.
								'" cols="40" rows="3" style="min-width:'.($right_width+5).'px;max-width:'.($right_width+5).'px;"></textarea>'
										. '<div style="clear:both;"></div>
						<div class="ln_pm_reply" >
							<input type="button" id="confirm" onclick="ln_pm_reply_action('.$notification->content_id.','.$scrollpane_height.');" name="pm_reply" value="Reply">
						</div>
						<div class="ln_pm_inbox" onclick="self.location.href=\''.$options1["plink_viewmsg"].'\';">View in Inbox</div>
						</div>'
								. '</div>
					<div style="clear:both;"></div>'
										. '</li></div>
				   <div style="clear:both;"></div>'
												. "\n";
			}
			else if($type == "friend"){
				
				if($notification->content_type == 'friend'){
					$output .= '<li onclick="self.location.href=\''.$url.'\';" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
					. ln_fetch_useravatar($notification->userid_subj)
					.'<div style="float:right;width:'.$right_width.'px;"><div class="'.$request_status_class.'" >'
					. '<p class="ln_content">'
					. $phrase
					. '</p>'
					. '<p class="ln_time">'.$time.'</p>'
					.'</div>';
					
					
					$output.='<div class="actions"  >
						<input type="button" id="confirm" onclick="ln_friend_actions(true,'.$notification->userid_subj.',event);" name="actions[accept]" value="Accept">
						<input type="button" id="reject"  onclick="ln_friend_actions(false,'.$notification->userid_subj.',event);" name="actions[reject]" value="Reject">
					</div>';
					
					
					$output.='</div><div style="clear:both;"></div>'
					. '</li>'
					. "\n";
				}
				else{
					$output .= '<li onclick="self.location.href=\''.$url.'\';" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
					. ln_fetch_useravatar($notification->userid_subj);
					if(!$full) $output .= '<div style="float:right;width:'.$right_width.'px;">';
					else $output .= '<div class="full_right" >';
					$output .= '<p class="ln_content">'
							. $phrase
							. '</p>'
							. '<p class="ln_time">'.$time.'</p>';
					$output .= '</div>';
					$output .= '</li>'
							. "\n";
				}
			}
			else if($type == "moderation"){
				//moderation

				$output .= '<li onclick="self.location.href=\''.$url.'\';"  style="min-height:20px;" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
				.'<div>'
				. '<p class="ln_content">'
				. $phrase
				. '</p>'
				.'</div>
				<div style="clear:both;"></div>'
				. '</li>'
				. "\n";
			}
		}
		else{
			
			if($notification->content_type=='adminnotification')
			{
				$mytime=($notification->time);
				$currtime=time();
				$timestamp = mktime(date('H',$mytime)+$notification->additional_subj, date('i',$mytime), date('s',$mytime), date('m',$mytime), date('d',$mytime), date('Y',$mytime));
				
				if($timestamp>$currtime)
				{
				$output1 .= '<li onclick="self.location.href=\''.$url.'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			if(!$full) $output1 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output1 .= '<div class="full_right" >';
			$output1 .= '<p class="ln_content">'
			.$phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output1 .= '</div>'; 
			$output1 .= '</li>'
			. "\n";
			}
			}
			
			elseif($notification->content_type=='bbpressnotificationreply')
			{
				$permalink = get_permalink( $notification->content_id );
				
				$output2 .= '<li onclick="self.location.href=\''.$permalink.'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			if(!$full) $output2 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output2 .= '<div class="full_right" >';
			$output2 .= '<p class="ln_content">'
			.$phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='bbpressnotification')
			{
				$permalink = get_permalink( $notification->content_id );
				
				$output2 .= '<li onclick="self.location.href=\''.$permalink.'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			
			if(!$full) $output2 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output2 .= '<div class="full_right" >';
			$output2 .= '<p class="ln_content">'
			.$phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
						elseif($notification->content_type=='postaward')
				{
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reorder != '".$reorders."' ORDER BY reid  ASC ");
			$rank=mysql_fetch_array($selectdata1);
			$rank_next=$rank['numlist'].' '.$rank['type'];
			$userinfo = wp_get_current_user();
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:35px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='readpostaward')
				{
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reorder != '".$reorders."' ORDER BY reid  ASC ");
			$rank=mysql_fetch_array($selectdata1);
			$rank_next=$rank['numlist'].' '.$rank['type'];
			$userinfo = wp_get_current_user();
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:35px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='commentaward')
				{
			$userinfo = wp_get_current_user();
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:30px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			
			elseif($notification->content_type=='messageaward')
				{
			$userinfo = wp_get_current_user();
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:30px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='newfriendaward')
				{
			$userinfo = wp_get_current_user();
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:30px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			

		
		else
		{	$output2 .= '<li onclick="self.location.href=\''.$url.'\';" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			if(!$full) $output2 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output2 .= '<div class="full_right" >';
			$output2 .= '<p class="ln_content">'
			. $phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			}
	

	}

	}
	$output .=$output1.$output2;
	

	return $output;

}

function ln_fetch_notifications($userid, $start=0, $count=-1, $full=false,$type='all' ,$is_first=true) {
	global $wpdb;
	global $current_user1;
    get_currentuserinfo();
	  
	$site_url = get_option( 'siteurl' );
	$options = get_option('ln_options');
	$options1 = get_option('ln_options1');
	
	$output = '';
	
	$update_ids = array(); // ids which we will mark as read in the next step
	$override_status = array();

	if($type == 'all'){
		$cond = " ";
		$sql = "SELECT l.* FROM " . $wpdb->prefix . "livenotifications AS l WHERE l.userid = " . (int)$userid . " ".$cond." ORDER BY l.is_read, l.time DESC";
	}
	else if($type == 'comment'){
		$cond = " AND  l.content_type <> 'pm' AND substring(l.content_type,1,4) <> 'frie'  AND substring(l.content_type,1,4) <> 'mod_' ";
		$sql = "SELECT l.* FROM " . $wpdb->prefix . "livenotifications AS l WHERE l.userid = " . (int)$userid . " ".$cond." ORDER BY l.is_read, l.time DESC";
		$output = "<li class='ln_title'>Notifications</li>";
	}
	else if($type == 'pm'){
		$cond = " AND l.userid_subj > 0 ";
		$sql = "SELECT l.*,count(id) as number FROM " . $wpdb->prefix . "livenotifications AS l WHERE l.content_type = 'pm'  AND l.userid = " . (int)$userid . " ".$cond." GROUP BY l.userid_subj ORDER BY l.is_read, l.time DESC";
		$output = "<li class='ln_title'>Private Messages
				 <a href='".$options1['plink_sendmsg']."'>Send New Messages</a> </li>";

	}
	else if($type == 'friend'){
		$cond = " AND content_type!='friend_comment' AND content_type!='friend_post' AND substring(l.content_type,1,4) = 'frie'  ";
		$sql = "SELECT l.* FROM " . $wpdb->prefix . "livenotifications AS l WHERE l.userid = " . (int)$userid . " ".$cond." ORDER BY l.is_read, l.time DESC";
		$output = "<li class='ln_title'>Friend Request</li>";
	}
	if($type == 'moderation'){
		$cond = " AND substring(l.content_type,1,4) = 'mod_'  ";
		$sql = "SELECT l.* FROM " . $wpdb->prefix . "livenotifications AS l WHERE l.userid = " . (int)$userid . " ".$cond." ORDER BY l.is_read, l.time DESC";
		$output = "<li class='ln_title'>Moderations</li>";
	}
	
	
	$res = $wpdb->get_results($sql);
	$total_numrows = count($res);
	
	if ($start >= 0 && $count > 0) $sql .= " LIMIT ".(int)$start.", ".(int)$count;

	$res = $wpdb->get_results($sql);
	
	
	if ($full && isset($_REQUEST['lntransf']) && !empty($_REQUEST['lntransf'])) {
		$override_status = explode(",",$_REQUEST['lntransf']);
		array_walk($override_status, 'intval');
	} else {
		$override_status = array();
	}
	$scrollpane_height = 230;
	if(!$full) {
		$numrows = count($res);
		$output .= '<li style="width:330px;"><ul class="ln_scrollpane"';
		if($numrows > 4){
			$output .= ' style="height: '.$scrollpane_height.'px;"';
		}
		else{
			$scrollpane_height = 0;
		}
		$output .= ">";
	}

	foreach ($res as $notification) {
		if (!$notification->is_read) $update_ids[] = (int)$notification->id; // Set pulled notifications as red
		
		$is_read = ($full && in_array($notification->id, $override_status)) ? false : $notification->is_read;
		
		switch ($notification->content_type) {

			case 'comment':
				
				$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;

				$phrase = ($notification->additional_subj > 0)
				? sprintf("<strong>%s</strong> and %d more have commented on your article %s",
						$notification->username,
						$notification->additional_subj,
						ln_wrap_url($url, $notification->content_text))
						: sprintf("<strong>%s</strong> has commented on your article %s",
								$notification->username,
								ln_wrap_url($url, $notification->content_text));
				break;
					
					
			case 'reply':
				
				$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;

				$phrase = ($notification->additional_subj > 0)
				? sprintf("<strong>%s</strong> and %d more responsed to your comment",
						$notification->username,
						$notification->additional_subj)
						: sprintf("<strong>%s</strong> responsed to your comment",
								$notification->username);
				break;
			case 'mention':
				if($notification->parent_id == 0){
					$url = $site_url . '/' . "?p=" . $notification->content_id;
				}
				else{
					$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;
				}
				
				$phrase = ($notification->additional_subj > 0)
				? sprintf("<strong>%s</strong> and %d more mentioned you in article %s",
						$notification->username,
						$notification->additional_subj,ln_wrap_url($url, $notification->content_text))
						: sprintf("<strong>%s</strong> mentioned you in article %s",
								$notification->username, ln_wrap_url($url, $notification->content_text));
				break;
					
			case 'pm':
				if($full){
					$url = $site_url . '/wp-admin/admin.php?page=lnpm_inbox';
					
					$phrase = sprintf("<strong>%s</strong> sent you private message: %s",
							$notification->username,
							ln_wrap_url($url, $notification->content_text));
				}
				else{
						
					$notification_time = $notification->time;
					
					$phrase1 = $notification->username.'('.$notification->number.')';
					$phrase2 = $notification->content_text;
				}
				break;
			case 'friend':

				$phrase = sprintf("<strong>%s</strong> has added you",
						$notification->username);

				break;
			
			/*case 'friend_comment':
			
				$url = $site_url . '/' . "?p=" . $notification->parent_id."#comment-".$notification->content_id;
			
				$phrase = sprintf("%s has posted on %s",
								$notification->username,
								ln_wrap_url($url, $notification->content_text));
				break;
			case 'friend_post':
					
				$url = $site_url . '/' . "?p=" . $notification->content_id;
					
				$phrase = sprintf("%s has created %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;*/
			case 'mod_comment':
				$url = $site_url . '/wp-admin/edit-comments.php';
				$phrase = sprintf( __('%d comment(s) awaiting for your approval'), $notification->content_text );

				break;
				
			case 'adminnotification':
				$url = '';
				$myvariable='adminnotification';	
				$phrase = sprintf("<strong>Sitewide Message:</strong> %s",
						ln_wrap_url($url, $notification->content_text));
				break;
				
			case 'bbpressnotification':
				$url = '';
				$myvariable='bbpressnotification';	
				$myid=$notification->content_id;
				$phrase = sprintf("<strong>%s</strong> Added new topic %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;
			case 'bbpressnotificationreply':
				$url = '';
				$myvariable='bbpressnotificationreply';	
				$myid=$notification->content_id;
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;
				case 'postaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;
				case 'readpostaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;	
				case 'commentaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;	
				case 'messageaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;
				case 'newfriendaward':
				$url = '';
				$myid=$notification->content_id;
				$usersid=$notification->userid;
				
				$phrase = sprintf("<strong>%s</strong> replied on %s",
						$notification->username,
						ln_wrap_url($url, $notification->content_text));
				break;	
			default:
				
				$phrase = "Has performed an unknown operation...";
		}
		$time = ln_get_timeformat($notification->time);
		
		$right_width = 260;
		$request_status_class = "request_status";
		if($options['hide_avatar']) {
			$right_width = $right_width + $options['ln_avatar_height'];
			$request_status_class = "request_status_noavatar";
		}
		if(($type == "pm" || $type == "friend" || $type == "moderation") && !$full){
			
			if($type == "pm"){
				$output .= '<li onclick="ln_show_pm_other('.$notification->content_id.',event);" class="lnpmbit '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
					. ln_fetch_useravatar($notification->userid_subj)
					. '<div style="float:left;width:'.($right_width + 7).'px;"><div class="'.$request_status_class.'" ><p class="ln_sender_name">'
					. $phrase1
					. '</p>'
					. '<p class="ln_content">'
					. $phrase2
					. '</p>'
					. '<p class="ln_time">'.$time.'</p>'
					. '</div>
					<div class="actions"  >
						<input type="button" id="confirm" onclick="ln_pm_delete_action('.$notification->content_id.',event);" name="pm_delete" value="Delete">
					</div>
					</div><div style="clear:both;"></div>'
								. '</li></div>
					   <div style="clear:both;"></div>'
										. "\n";
				$output .= '<li class="lnpmbit livenotificationbit red ln_pm_inner_window" id="ln_pm_inner_window_'.$notification->content_id.'" style="display:none; " onclick="ln_pm_innerwindow_click(event);">'
						.ln_getall_content($notification->userid,$notification->userid_subj,$notification->content_id,$right_width,$request_status_class,$phrase1,$phrase2,$time,$scrollpane_height,$full).
						'<div style="border-top: 1px solid #DDD; margin-top: 4px;padding-top: 4px;">'
								. ln_fetch_useravatar($userid)
								. '<div style="float: right; width: '.($right_width + 10).'px;">
								<textarea name="reply_'.$notification->content_id.'" id="reply_'.$notification->content_id.
								'" cols="40" rows="3" style="min-width:'.($right_width-15).'px;max-width:'.($right_width-20).'px;"></textarea>'
										. '<div style="clear:both;"></div>
						<div class="ln_pm_reply" >
							<input type="button" id="confirm" onclick="ln_pm_reply_action('.$notification->content_id.','.$scrollpane_height.');" name="pm_reply" value="Reply">
						</div>
						<div class="ln_pm_inbox" onclick="self.location.href=\''.$options1["plink_viewmsg"].'\';">View in Inbox</div>
						</div>'
								. '</div>
					<div style="clear:both;"></div>'
										. '</li></div>
				   <div style="clear:both;"></div>'
												. "\n";
			}
			else if($type == "friend"){
				
				if($notification->content_type == 'friend'){
					$output .= '<li onclick="self.location.href=\''.$url.'\';" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
					. ln_fetch_useravatar($notification->userid_subj)
					.'<div style="float:right;width:'.$right_width.'px;"><div class="'.$request_status_class.'" >'
					. '<p class="ln_content">'
					. $phrase
					. '</p>'
					. '<p class="ln_time">'.$time.'</p>'
					.'</div>';
					
					
					$output.='<div class="actions"  >
						<input type="button" id="confirm" onclick="ln_friend_actions(true,'.$notification->userid_subj.',event);" name="actions[accept]" value="Accept">
						<input type="button" id="reject"  onclick="ln_friend_actions(false,'.$notification->userid_subj.',event);" name="actions[reject]" value="Reject">
					</div>';
					
					
					$output.='</div><div style="clear:both;"></div>'
					. '</li>'
					. "\n";
				}
				else{
					$output .= '<li onclick="self.location.href=\''.$url.'\';" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
					. ln_fetch_useravatar($notification->userid_subj);
					if(!$full) $output .= '<div style="float:right;width:'.$right_width.'px;">';
					else $output .= '<div class="full_right" >';
					$output .= '<p class="ln_content">'
							. $phrase
							. '</p>'
							. '<p class="ln_time">'.$time.'</p>';
					$output .= '</div>';
					$output .= '</li>'
							. "\n";
				}
			}
			else if($type == "moderation"){
				//moderation

				$output .= '<li onclick="self.location.href=\''.$url.'\';"  style="min-height:20px;" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
				.'<div>'
				. '<p class="ln_content">'
				. $phrase
				. '</p>'
				.'</div>
				<div style="clear:both;"></div>'
				. '</li>'
				. "\n";
			}
		}
		else{
			
			if($notification->content_type=='adminnotification')
			{
				$mytime=($notification->time);
				$currtime=time();
				$timestamp = mktime(date('H',$mytime)+$notification->additional_subj, date('i',$mytime), date('s',$mytime), date('m',$mytime), date('d',$mytime), date('Y',$mytime));
				
				if($timestamp>$currtime)
				{
				$output1 .= '<li onclick="self.location.href=\''.$url.'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			if(!$full) $output1 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output1 .= '<div class="full_right" >';
			$output1 .= '<p class="ln_content">'
			.$phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output1 .= '</div>'; 
			$output1 .= '</li>'
			. "\n";
			}
			}
			
			elseif($notification->content_type=='bbpressnotificationreply')
			{
				$permalink = get_permalink( $notification->content_id );
				
				$output2 .= '<li onclick="self.location.href=\''.$permalink.'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			if(!$full) $output2 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output2 .= '<div class="full_right" >';
			$output2 .= '<p class="ln_content">'
			.$phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='bbpressnotification')
			{
				$permalink = get_permalink( $notification->content_id );
				
				$output2 .= '<li onclick="self.location.href=\''.$permalink.'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			if(!$full) $output2 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output2 .= '<div class="full_right" >';
			$output2 .= '<p class="ln_content">'
			.$phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			

						elseif($notification->content_type=='postaward')
				{
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reorder != '".$reorders."' ORDER BY reid  ASC ");
			$rank=mysql_fetch_array($selectdata1);
			$rank_next=$rank['numlist'].' '.$rank['type'];
			$userinfo = wp_get_current_user();
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:35px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='readpostaward')
				{
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reorder != '".$reorders."' ORDER BY reid  ASC ");
			$rank=mysql_fetch_array($selectdata1);
			$rank_next=$rank['numlist'].' '.$rank['type'];
			$userinfo = wp_get_current_user();
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:35px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='commentaward')
				{
			$userinfo = wp_get_current_user();
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:30px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			
			elseif($notification->content_type=='messageaward')
				{
			$userinfo = wp_get_current_user();
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:30px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}
			elseif($notification->content_type=='newfriendaward')
				{
			$userinfo = wp_get_current_user();
			$selectdata=mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$notification->content_id."'");
			while($getrewarddatas=mysql_fetch_array($selectdata))
			{
				$repoints=$getrewarddatas['repoint'];
				$reorders=$getrewarddatas['reorder'];
				$retitle=$getrewarddatas['retitle'];
				$images=$getrewarddatas['rew_image'];
				$remsg=$getrewarddatas['remsg'];
			}
				$output2 .= '<li onclick="self.location.href=\''.$options1["plink_award"].'\';" class="test '.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">');
				$output2 .= '<div style="float:left;width:100px;"><img src="'.$images.'" width="100" height="100"/></div>';
			if(!$full) $output2 .= '<div style="float:right;width:200px;">';
			else $output2 .= '<div class="full_right" >';
			
			
			//$options1 = get_option('ln_options1'); 
			
			$output2 .= '<p class="ln_content" style="font-size:26px; font-weight:bold;"><font color="green">+'
			.$repoints 
			. '  Points</font></p>'
			. '<p class="ln_time" style="margin-left:30px; font-size:16px; color:black;">'.$retitle.'</p>'
			. '<p class="ln_time" style="margin-left:10px; font-size:14px;">'.$remsg.'</p>'
			. '<p class="ln_time" style="float:right;margin-right:5px;">Next:'.$notification->username.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			
			}		
		else
		{	$output2 .= '<li onclick="self.location.href=\''.$url.'\';" class="'.($full ? 'livebit' : 'livenotificationbit').($is_read ? ' red">' : ' unread">')
			. ln_fetch_useravatar($notification->userid_subj);
			if(!$full) $output2 .= '<div style="float:right;width:'.$right_width.'px;">';
			else $output2 .= '<div class="full_right" >';
			$output2 .= '<p class="ln_content">'
			. $phrase
			. '</p>'
			. '<p class="ln_time">'.$time.'</p>';
			$output2 .= '</div>'; 
			$output2 .= '</li>'
			. "\n";
			}
	

	}

	}
	$output .=$output1.$output2;
	if (!$full) {
		global $current_user;
      get_currentuserinfo();
	  $options1 = get_option('ln_options1'); 

		$output .= "</ul></li>";
		if($numrows < $total_numrows){
			$output .= '<li onclick="ln_checknotifications_more(\''.$type.'\','.($numrows+10).',event);" class="livenotifications_more">'.sprintf( __('See More')).'</li>';
		}
		if($type == "pm"){
			$output .= '<li onclick="ln_transfer_overview(\''.$options1["plink_viewmsg"].'\');" class="livenotifications_link">'.sprintf( __('Show All Private Messages')).'</li>';
			//	$output .= '<li onclick="ln_transfer_overview(\''.$site_url.'/'.fetch_seo_url('member', $vbulletin->userinfo, array('tab'=>'livenotifications')).'\');" class="livenotifications_link">'.$vbphrase['ln_showall'].'</li>';
		}
		else if($type == "friend"){
			$output .= '<li onclick="ln_transfer_overview(\''.$options1["plink_friendlist"].'/#ln_request\' );" class="livenotifications_link">'.sprintf( __('Show All Friend Requests')).'</li>';
		}
		else if($type == "moderation"){
			$output .= '<li onclick="ln_transfer_overview(\''.$site_url . '/wp-admin/edit-comments.php\' );" class="livenotifications_link">'.sprintf( __('Show All Moderations')).'</li>';
		}
		else{
			$ln_useroptions = ln_fetch_useroptions($userid);
				
			$ln_checked = array(
					'ln_enable_comment'=>	$ln_useroptions['enable_comment']	? ' checked="checked"' : '',
					'ln_enable_reply'=>		$ln_useroptions['enable_reply']		? ' checked="checked"' : '',				
					'ln_enable_award'=>		$ln_useroptions['enable_award']		? ' checked="checked"' : '',
					'ln_enable_friend'=> 	$ln_useroptions['enable_friend']		? ' checked="checked"' : '',
					'ln_enable_moderation'=> 	$ln_useroptions['enable_moderation']		? ' checked="checked"' : '',
					'ln_enable_taguser'=> 	$ln_useroptions['enable_taguser']		? ' checked="checked"' : '',
					'ln_enable_pm'=> 		$ln_useroptions['enable_pm']			? ' checked="checked"' : ''
			);
				
			$option_img_url = plugins_url().'/vbsocial-notifications/images/settings.png';
				
			$output .= '<li class="livenotifications_link"> <div id="global_all_notification" onclick="ln_transfer_overview(\''.$options1["plink_noti"].'\');" class="livenotifications_link">Show all notifications</div>
						<img class="settings_option" src="'.$option_img_url.'" width="15" alt="Setting Options" onclick="ln_show_settings(event);" /></li>';
				
			$output .= '<li class="lnpmbit livenotificationbit red ln_settings_window" id="ln_settings_window" style="display:none; "  onclick="ln_pm_innerwindow_click(event);">'
					. '<div onclick="ln_back_to_notification('.$scrollpane_height.');" class="ln_link" id="ln_notification_back">'.sprintf( __('Back to Notifications')).'</div>'
							.'<ul class="checkradio group rightcol">';
			if ($options["enable_comment"]){
				$output .= '<li>
									<label for="ln_enable_comment"><input type="checkbox" name="options[ln_enable_comment]" id="ln_enable_comment" tabindex="1" '.$ln_checked['ln_enable_comment'].' /> '.sprintf( __('Enable Comment Notification')).'</label>
								</li>';
			}
			if($options["enable_reply"]){
				$output .= '<li>
									<label for="ln_enable_reply"><input type="checkbox" name="options[ln_enable_reply]" id="ln_enable_reply" tabindex="1" '.$ln_checked['ln_enable_reply'].' /> '.sprintf( __('Enable Reply Notification')).'</label>
								</li>';
			}
			if($options["enable_award"]){
				$output .= '<li>
									<label for="ln_enable_award"><input type="checkbox" name="options[ln_enable_award]" id="ln_enable_award" tabindex="1" '.$ln_checked['ln_enable_award'].' /> '.sprintf( __('Enable Award Notification')).'</label>
								</li>';
			}
			
			if($options["enable_taguser"]){
				$output .= '<li>
									<label for="ln_enable_taguser"><input type="checkbox" name="options[ln_enable_taguser]" id="ln_enable_taguser" tabindex="1" '.$ln_checked['ln_enable_taguser'].' /> '.sprintf( __('Enable Notification to tagged user')).'</label>
								</li>';
			}
			if($options["enable_pm"]){
				$output .= '<li>
									<label for="ln_enable_pm"><input type="checkbox" name="options[ln_enable_pm]" id="ln_enable_pm" tabindex="1" '.$ln_checked['ln_enable_pm'].' /> '.sprintf( __('Enable Notification of private message')).'</label>
								</li>';
			}
			if($options["enable_friend"]){
				$output .= '<li>
									<label for="ln_enable_friend"><input type="checkbox" name="options[ln_enable_friend]" id="ln_enable_friend" tabindex="1" '.$ln_checked['ln_enable_friend'].' /> '.sprintf( __('Enable Friend Request Notification')).'</label>
								</li>';
			}
			if($options["enable_moderation"]){
				$output .= '<li>
									<label for="ln_enable_moderation"><input type="checkbox" name="options[ln_enable_moderation]" id="ln_enable_moderation" tabindex="1" '.$ln_checked['ln_enable_moderation'].' /> '.sprintf( __('Enable Notification awaiting moderation')).'</label>
								</li>';
			}
			$output .= '</ul>
							<p class="description">'.sprintf( __('These settings let you control on which events you like to be notified.')).'</p>'
									.'<div class="ln_options_save" >
							<input type="button" id="save_option_settings" onclick="ln_options_save_action('.$userid.');" name="options_save" value="'.sprintf( __('Save')).'">
					  </div>'
									. '</li>
				   <div style="clear:both;"></div>'. "\n";
		}
	}

	return $output;

}


add_shortcode( 'ln_fetch_notifications', 'ln_fetch_notifications' );
/*function ln_fetch_userdropdown($userinfo,$options) {
 $options1 = get_option('ln_options1'); 

 $site_url=get_option('siteurl');
	$output = '';
	$url = $options1["plink_editpro"]; 
	
	$phrase = ln_wrap_url($url, sprintf( __('Edit My Profile')),false);

	$output .= '<div class="livenotificationbit ln_udd_maininfo">';
	if(!$options['hide_avatar']){
			$output .= sprintf('<a id="avatars_manage_button" class="button thickbox" href="%s?action=ln_ajax_process&do=avatars_manage&act=INIT&uid=%s&TB_iframe=true&width=635&height=500" title="%s" >%s</a>',
					admin_url('admin-ajax.php'),
					$userinfo->ID,
					__('Avatar Management', 'avatars'),
					ln_fetch_useravatar($userinfo->ID)
				);
	}
	$output .= '<div style="float:left;">'
			. '<p class="ln_content ln_udd_username">'
			. $userinfo->display_name
			. '</p>'
			
			. '<p class="ln_udd_link"> '.$phrase.'</p>'
			.'</div><div class="clear"></div>'
			. '</div>'
			. "\n";
	$output .= '<div class="ln_udd_pane">';
	$output .= '<div class="ln_udd_leftpane">';
	
	$udd_more_links = get_more_links($options['ln_udd_morelinks']);
	foreach($udd_more_links as $more_link){
		$url = $site_url . '/' . trim($more_link[1]);
		$phrase = ln_wrap_url($url, trim($more_link[0]),false);
			
		$output .= '<div class="ln_udd_bit">
						<p class="ln_udd_link"> '.$phrase.'</p>
					</div>';
	}
	$output .= '</div>';

	$output .= '<div class="clear"></div>';
	$output .= '<div class="ln_udd_bottompane">';
	if($options['ln_enable_userdropdown_logout']){
		$output .= '<div class="ln_udd_bit">
						<p class="ln_udd_link ln_udd_logout"> <a href="' . wp_logout_url() . '" >'.sprintf( __('Log Out')).'</a></p>
					</div>';
	}
	$output .= '</div>';
	$output .= '</div>';
	return $output;
}*/
/**************  edit by vbsocial9xvers start ************/
function ln_fetch_userdropdown($userinfo,$options) {
global $wpdb;
 
 $options1 = get_option('ln_options1'); 

 $site_url=get_option('siteurl');
	$output = '';
	$url = $options1["plink_editpro"]; 
	$url1 = $options1["plink_viewmsg"]; 
	$url2 = $options1["plink_award"]; 

	$phrase = ln_wrap_url($url, sprintf( __('Edit My Profile')),false);
	$phrase1 = ln_wrap_url($url1, sprintf( __('Inbox')),false);
	if($options['ln_enable_award_link']==true)
	{
	$phrase2 = ln_wrap_url($url2, sprintf( __('Awards and Acheivements')),false);
	}
	$output .= '<div class="livenotificationbit ln_udd_maininfo">';
	if(!$options['hide_avatar']){
			$avtarSettings .= sprintf('<a id="avatars_manage_button" class="thickbox" href="%s?action=ln_ajax_process&do=avatars_manage&act=INIT&uid=%s&TB_iframe=true&width=635&height=500" title="%s" >Avatar Settings</a>',
					admin_url('admin-ajax.php'),
					$userinfo->ID,
					__('Avatar Management', 'avatars')					
				);
				$output.='<a id="avatars_manage_button" class="thickbox" href="'.admin_url('admin-ajax.php').'?action=ln_ajax_process&do=avatars_manage&act=INIT&uid='.$userinfo->ID.'&TB_iframe=true&width=635&height=500" title="'.__('Avatar Management', 'avatars').'" >'.ln_fetch_useravatar($userinfo->ID).'</a>';
	}
	if($options['ln_enable_award_link']==true)
	{
		$editProfileLink='<p class="ln_udd_link">'.$phrase.'</p><p class="ln_udd_link">'.$phrase1.'</p><p class="ln_udd_link">'.$phrase2.'</p>';
	}else { 
		$editProfileLink='<p class="ln_udd_link">'.$phrase.'</p><p class="ln_udd_link">'.$phrase1.'</p>';
	}
	$output .= '<div style="float:left;">'
			. '<p class="ln_content ln_udd_username">'
			. $userinfo->display_name
			. '</p></div>';
			
	$currentuserid=get_current_user_id();
	$getimage=mysql_query("SELECT * FROM ".$wpdb->prefix."countpoints WHERE cp_uid='".$currentuserid."' ORDER BY cp_id");
	$output .= '<div style="clear:both;"></div>';
	while($row_image=mysql_fetch_array($getimage)){
		$task_image=$row_image['cp_tasklist'];
		$get_reward_image=mysql_query("SELECT * FROM ".$wpdb->prefix."rewardsystem WHERE reorder='".$task_image."' ORDER BY reorder ASC limit 0,5");
		
			while($row_image_reward=mysql_fetch_array($get_reward_image)){
				$image_award=$row_image_reward['rew_image'];
				$image_title=$row_image_reward['type'];
				$image_reorder=$row_image_reward['numlist'];
		$output .= '<div><p style="background:white;margin-top:5px;"><img src="'.$image_award.'" title="'.$image_reorder.' '.ucfirst($image_title).' Award" height="20" width="20"></p></div>';
			}
		}
			
		$output .='</div>';
			
			
	$output .='<div class="clear"></div>'
			. '</div>'
			. "\n";
	$output .= '<div class="ln_udd_pane">';
	$output .= '<div class="ln_udd_leftpane">';

	$udd_more_links = get_more_links($options['ln_udd_morelinks']);
	foreach($udd_more_links as $more_link){
		if(strpos($more_link[1], 'http')!== FALSE){
		$url=trim($more_link[1]);		
		
		}
		else{
		$url = $site_url . '/' . trim($more_link[1]);}
		$phrasemore .= '<p class="ln_udd_link"> '.ln_wrap_url($url, trim($more_link[0]),false).'</p>';
	}
		$addtoOut=$phrasemore.'</div>';
						
	
	$output .= '<div class="ln_udd_bit">
						<p class="ln_udd_link"> '.$avtarSettings.'</p>
						<p class="ln_udd_link"> '.$editProfileLink.'</p>';
		
	$output .= $addtoOut.'</div>';

	$output .= '<div class="clear"></div>';
	$output .= '<div class="ln_udd_bottompane">';
	if($options['ln_enable_userdropdown_logout']){
		$output .= '<div class="ln_udd_bit">
						<p class="ln_udd_link ln_udd_logout"> <a href="' . wp_logout_url(home_url()) . '" >'.sprintf( __('Log Out')).'</a></p>
					</div>';
	}
	$output .= '</div>';
	$output .= '</div>';
	return $output;
}
/******************end edit by vbsocial9xvers****************/
function get_more_links($morelinks){
	$return = array();
	if($morelinks != ""){
		//$morelinks_array = preg_split('#\s+#', str_replace(" ","",$morelinks), -1, PREG_SPLIT_NO_EMPTY);
		$morelinks_array = explode("\n",$morelinks);
		if(!empty($morelinks_array)){

			foreach($morelinks_array as $more_link){
				$nodes = explode("=>" , $more_link);
				if(count($nodes) == 2){
					$return[] = $nodes;
				}
			}
		}
	}
	return $return;
}
function ln_update_readflag($userid,  $start=0, $count=-1, $full=false,$type) {
	global $wpdb;

	$update_ids = array(); 

	if($type == 'all'){
		return;
	}
	else if($type == 'comment'){

		$cond = " AND  l.content_type <> 'pm' AND substring(l.content_type,1,4) <> 'frie' AND substring(l.content_type,1,4) <> 'mod_' ";
	}
	else if($type == 'pm'){
		$cond = " AND l.content_type = 'pm'  ";
	}
	else if($type == 'friend'){
		$cond = " AND substring(l.content_type,1,4) = 'frie'  ";
	}
	if($type == 'moderation'){
		$cond = " AND substring(l.content_type,1,4) = 'mod_'  ";
	}
	
	$sql = "
		SELECT
			l.*
		FROM
			" . $wpdb->prefix . "livenotifications AS l
		WHERE
			l.userid = " . (int)$userid . "
			".$cond."
		ORDER BY
			l.is_read, l.id DESC
	";
	
	
	
	if ($start >= 0 && $count > 0) $sql .= " LIMIT ".(int)$start.", ".(int)$count;

	$res = $wpdb->get_results($sql);

	if (!$res || empty($res)) return ;


	foreach ($res as $notification) {
		if (!$notification->is_read) $update_ids[] = (int)$notification->id; // Set pulled notifications as red
	}
	if (!empty($update_ids)) {
		$newids = implode(",",$update_ids);
		$sql = "UPDATE " . $wpdb->prefix . "livenotifications SET is_read = 1
			WHERE id IN (" . $newids . ")";
		$wpdb->query($sql);

	}
}

function ln_friendrequest_action($action,$userid,$userid_subj){
	global $wpdb;

					
					//end counting
	if($action == "accept"){
		$query = "DELETE FROM ".$wpdb->prefix . "userlist WHERE relationid = " . $userid . " AND userid = " . $userid_subj .
			" AND type = 'buddy' AND friend = 'pending' ";
		if ($wpdb->query($query) ) {
			$new_request = array(
					'id' => NULL,
					'userid' => $userid,
					'relationid' => $userid_subj,
					'type' => 'buddy',
					'friend' => 'yes'
			);
			if ($wpdb->insert( $wpdb->prefix . 'userlist', $new_request, array( '%d', '%d', '%d', '%s', '%s' ) ) ) {
				$new_request = array(
						'id' => NULL,
						'userid' => $userid_subj,
						'relationid' => $userid,
						'type' => 'buddy',
						'friend' => 'yes'
				);
				if ($wpdb->insert( $wpdb->prefix . 'userlist', $new_request, array( '%d', '%d', '%d', '%s', '%s' ) ) ) {
					//add notification for 
					
mysql_query("insert into ".$wpdb->prefix."count_reading (userid,postid,readtime,posttype) values('".$userid_subj."','','".time()."','friends')");
					 
					 $count_post=mysql_num_rows(mysql_query("select * from ".$wpdb->prefix."count_reading where userid='".$userid_subj."' and posttype='friends'"));
					 
				  $getpostreward=mysql_query("select * from ".$wpdb->prefix."rewardsystem where type='friends'");
				  while($getpostrewardrec=mysql_fetch_array($getpostreward))
					{
						$numlist=$getpostrewardrec['numlist'];
						$repoint=$getpostrewardrec['repoint'];
						$reorder=$getpostrewardrec['reorder'];
						$type=$getpostrewardrec['type'];
						$retitle=$getpostrewardrec['retitle'];
						$remsg=$getpostrewardrec['remsg'];
						$reid=$getpostrewardrec['reid'];
						if($count_post==$numlist){
						//insert into point table
						$countpoints=mysql_query("insert into ".$wpdb->prefix."countpoints (cp_uid,cp_pmid,cp_points,cp_time,cp_tasklist) values('".$userid_subj."','".$wpdb->insert_id."','".$repoint."','".time()."','".$reorder."')");
						
							$selectorder=mysql_query("select cp_tasklist from ".$wpdb->prefix."countpoints where cp_uid='".$userid_subj."'");
							if(mysql_num_rows($selectorder)>0)
							{
							$reclist=0;
							while($selectorderrec=mysql_fetch_array($selectorder))
							{
								if($reclist==0)
								{
									$order .="reorder!=".$selectorderrec['cp_tasklist'];
								}
								else
								{
									$order .=" and reorder!=".$selectorderrec['cp_tasklist'];
								}
								$reclist++;
							}
							}
							else
							{
								$order="1=1";
							}
							$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where ".$order." ORDER BY reorder ASC");
							$rank=mysql_fetch_array($selectdata1);
							$rank_next=$rank['numlist'].' '.$rank['type'];
							
						
						//insert into livenotification table
						$selectoption=mysql_query("select enable_award from ".$wpdb->prefix."livenotifications_usersettings where userid='".$userid."'");
			if(mysql_num_rows($selectoption)>0)
			{
				$selectoptionrec=mysql_fetch_array($selectoption);
				$award=$selectoptionrec['enable_award'];
			}
			else
			{
				$options = get_option('ln_options');
				$award=$options['enable_award'];
				if($award=='on')
				{
					$award='1';
				}
				else
				{
					$award='0';
				}
			}
						if($award=='1')
						{
						$livesnotificationtable=mysql_query("insert into ".$wpdb->prefix."livenotifications (userid,userid_subj,content_type,content_id,content_text,is_read,time,username) values('".$userid_subj."','".$userid_subj."','newfriendaward','".$reid."','".$remsg."','0','".time()."','".$rank_next."')");
						}
						
						}
					}


					ln_remove_notifications('friend', $userid, $userid_subj);
				}
			}
		}
	}
	else{

		$query = "DELETE FROM ".$wpdb->prefix . "userlist WHERE relationid = " . $userid . " AND userid = " . $userid_subj .
			" AND type = 'buddy' AND friend = 'pending' ";
		
		if ($wpdb->query($query) ) {
			ln_remove_notifications('friend', $userid, $userid_subj);
		}
	}
}
function ln_remove_notifications($content_type, $userid, $userid_subj){
	global $wpdb;
	$sql = "DELETE FROM " . $wpdb->prefix . "livenotifications WHERE content_type = '".$content_type."' AND userid = ".$userid." AND userid_subj = ".$userid_subj ;
	return $wpdb->query($sql);
}
function ln_pm_delete_action($pm_id){
	global $wpdb;
	
	// check if the sender has deleted this message
	$sender_deleted = $wpdb->get_var( 'SELECT `deleted` FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $pm_id . '" LIMIT 1' );

	// create corresponding query for deleting message
	if ( $sender_deleted == 1 ) {
		$query = 'DELETE from ' . $wpdb->prefix . 'pm WHERE `id` = "' . $pm_id . '"';
		
	} else {
		$query = 'UPDATE ' . $wpdb->prefix . 'pm SET `deleted` = "2" WHERE `id` = "' . $pm_id . '"';
	}
	$sql = "DELETE FROM " . $wpdb->prefix . "livenotifications WHERE content_type = 'pm' AND content_id = ".$pm_id ;
	if ($wpdb->query( $query ) ) {
		$wpdb->query( $sql);
	}
}
function ln_pm_reply_action($pm_id,$pm_text){
	global $wpdb, $current_user;

	$pm_parent = $wpdb->get_row("
			SELECT pm.subject, pm.sender, pm.recipient
			FROM " . $wpdb->prefix . "pm AS pm
			WHERE pm.id = " . $pm_id . "
		");
	$title = $pm_parent->subject;
	if(substr($title,0,3) != "Re:") $title = "Re:".$title;

	$userid_subj = $wpdb->get_var("SELECT ID FROM " . $wpdb->prefix . "users WHERE user_login = '".$pm_parent->sender."'");

	$new_message = array(
		'id' => NULL,
		'subject' => $title,
		'content' => $pm_text,
		'sender' => $pm_parent->recipient,
		'recipient' => $pm_parent->sender,
		'date' => current_time( 'mysql' ),
		'read' => 0,
		'deleted' => 0
	);
	// insert into database
	if ($wpdb->insert( $wpdb->prefix . 'pm', $new_message, array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) ) ) {
		ln_add_user_notification($current_user->ID, $userid_subj, 'pm', $wpdb->insert_id, $pm_text, 0, 0, $pm_parent->recipient);
	}

}

function get_excerpt($str, $startPos=0, $maxLength=140) {
	if(strlen($str) > $maxLength) {
		$excerpt   = substr($str, $startPos, $maxLength-3);
		$lastSpace = strrpos($excerpt, ' ');
		$excerpt   = substr($excerpt, 0, $lastSpace);
		$excerpt  .= '...';
	} else {
		$excerpt = $str;
	}

	return $excerpt;
}
function ln_filter_pm($str) {
	$strs = explode('[/QUOTE]',$str);
	return end($strs);
}
function ln_fetch_useroptions($userid) {
	global $wpdb;
	$options = get_option('ln_options');
	$q = "SELECT enable_comment, enable_reply,
			enable_award,enable_pm, enable_friend,enable_moderation, enable_taguser
			FROM " . $wpdb->prefix . "livenotifications_usersettings WHERE userid = " . $userid;
	$res = $wpdb->get_row($q);
	if (!$res) return array(
			'enable_comment' => $options['enable_comment'],
			'enable_award' => $options['enable_award'],
			'enable_reply' => $options['enable_reply'],
			'enable_taguser' => $options['enable_taguser'],
			'enable_friend' => $options['enable_friend'],
			'enable_moderation' => $options['enable_moderation'],
			'enable_pm' => $options['enable_pm']);
	else return array(
			'enable_comment' => $res->enable_comment,
			'enable_award' => $res->enable_award,
			'enable_reply' => $res->enable_reply,
			'enable_taguser' => $res->enable_taguser,
			'enable_friend' => $res->enable_friend,
			'enable_moderation' => $res->enable_moderation,
			'enable_pm' => $res->enable_pm);
}

function ln_save_useroptions($userid, $options) {
	global $wpdb;
	$check = $wpdb->get_row("SELECT userid FROM " . $wpdb->prefix . "livenotifications_usersettings WHERE userid = ".(int)$userid);
	if (isset($check->userid)) {
		$q = "UPDATE " . $wpdb->prefix . "livenotifications_usersettings SET
			enable_comment = ".(int)$options['enable_comment'].",
			enable_reply = ".(int)$options['enable_reply'].",
			enable_award = ".(int)$options['enable_award'].",
			enable_taguser = ".(int)$options['enable_taguser'].",
			enable_friend = ".(int)$options['enable_friend'].",
			enable_moderation = ".(int)$options['enable_moderation'].",
			enable_pm = ".(int)$options['enable_pm']."
		WHERE userid = ".(int)$userid;
		$wpdb->query($q);
	} else {
		$q = "INSERT INTO " . $wpdb->prefix . "livenotifications_usersettings
			(enable_comment, enable_reply,enable_award, 
			enable_taguser, enable_pm, enable_friend,enable_moderation,userid)
		VALUES (
			".(int)$options['enable_comment'].",
			".(int)$options['enable_reply'].",".(int)$options['enable_award'].",
			".(int)$options['enable_taguser'].",
			".(int)$options['enable_pm'].",
			".(int)$options['enable_friend'].",
			".(int)$options['enable_moderation'].",
			".(int)$userid."
		);";
		$wpdb->query($q);
	}
}

function ln_fetch_useravatar($userid) {
	global $avatar_type;
	$options = get_option("ln_options");
	
	if($options['hide_avatar']) return "";
	$size = $options['ln_avatar_height'];
	
	if($userid) {
		$local = get_usermeta($userid, 'avatar');
		if(!empty($local)) {
			$newsiteurl=substr(get_option('siteurl'),0,22);
			$local = $newsiteurl.$local;
			$avatar_type = TYPE_LOCAL;
			return "<img alt='' src='{$local}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
		}
		else if(empty($options['ln_default_avatar'])){
			$avatar_type = TYPE_GLOBAL;
		}
	}
	return get_avatar( $userid, $size,$options['ln_default_avatar'] );
}

function ln_display_useravatar($userid) {
	global $avatar_type;
	$options = get_option("ln_options");
	
	if($options['hide_avatar']) return "";
	$size = $options['ln_avatar_height'];
	
	if($userid) {
		$local = get_usermeta($userid, 'avatar');
		if(!empty($local)) {
			$local = $newsiteurl.$local;
			$avatar_type = TYPE_LOCAL;
			return "<img alt='' src='{$local}' class='avatar avatar-{$size} photo avatar-default' height='30' width='{$size}' />";
		}
		else if(empty($options['ln_default_avatar'])){
			$avatar_type = TYPE_GLOBAL;
		}
	}
	return get_avatar( $userid, $size,$options['ln_default_avatar'] );
}

function ln_wrap_url($url, $txt, $cut=true) {
	$options = get_option('ln_options');
	if ($cut && strlen($txt) > $options['cut_strlen']) {
		$txt = substr($txt, 0, $options['cut_strlen']) . '...';
	}
	return sprintf('<a onclick="event.stopPropagation();" href="%s">%s</a>', $url, $txt);
}

function ln_prune_notifications() {
	global $wpdb;
	$options = get_option('ln_options');
	$maxage = TIMENOW - (60*60*24* intval($options['max_age']));

	$sql = "DELETE FROM " . $wpdb->prefix . "livenotifications WHERE `time` < " . intval($maxage);
	return $wpdb->query($sql);

}

function ln_get_timeformat($timestamp) {
	$diff = time() - (double)$timestamp;

	switch ($diff) {
		case ($diff < 60):
			return sprintf( __('%d second(s) ago'), $diff);

		case ($diff < 3600):
			return sprintf(__('%d minute(s) ago'), ceil($diff/60));
				
		case ($diff < 86400):
			return sprintf(__('%d hour(s) ago'), ceil($diff/3600));

		case ($diff < 604800):
			return sprintf(__('%d day(s) ago'), ceil($diff/86400));
				
		case ($diff < 2419200):
			return sprintf(__('%d week(s) ago'), ceil($diff/604800));

		default:
			return date(get_option( 'date_format' )." - ".get_option( 'time_format' ), (double)$timestamp);
	}
}
function ln_getall_content($userid,$userid_subj,$content_id,$right_width,$request_status_class,$phrase1,$phrase2,$time1,$scrollpane_height,$full)
{
	global $wpdb;
	//$test="select * from " . $wpdb->prefix . "livenotifications where content_type='pm' and ((userid='".$userid."' and userid_subj='".$userid_subj."') or (userid='".$userid_subj."' and userid_subj='".$userid."'))";
	$sql_rec=mysql_query("select * from " . $wpdb->prefix . "livenotifications where content_type='pm' and ((userid='".$userid."' and userid_subj='".$userid_subj."') or (userid='".$userid_subj."' and userid_subj='".$userid."'))");
	$numrows1=mysql_num_rows($sql_rec);
	
	$myoutput='<div onclick="ln_back_to_messages('.$content_id.','.$scrollpane_height.');" class="ln_link" id="ln_pm_back_'.$content_id.'">Back to Messages</div>';
	if($numrows1>0){
		$scrollpane_height = 230;
		$myoutput .= '<ul id="ulScroll" class="ln_scrollpane" style="height: '.$scrollpane_height.'px !important;">';
		
		$var=1;
		while($numrecord=mysql_fetch_array($sql_rec))
		{
			if($var=='1')
			{
				$myoutput.='<li class="lnpmbit">';
			$myoutput.= ln_fetch_useravatar($numrecord['userid_subj'])
			. '<div style="float:left;width:'.$right_width.'px;">
			<div class="'.$request_status_class.'" ><p class="ln_sender_name">'
			. $numrecord['username']
			. '</p>'
			. '<p class="ln_content">'
			. $numrecord['content_text']
			. '</p>'
			. '<p class="ln_time">'.ln_get_timeformat($numrecord['time']).'</p>'
			. '</div></div><div style="clear:both;"></div></li>';
			}
			else
			{
				$myoutput.='<li class="lnpmbit">';
				$myoutput.= ln_fetch_useravatar($numrecord['userid_subj'])
			. '<div style="float:left;width:'.$right_width.'px;">
			<div class="'.$request_status_class.'" ><p class="ln_sender_name">'
			. $numrecord['username']
			. '</p>'
			. '<p class="ln_content">'
			. $numrecord['content_text']
			. '</p>'
			. '<p class="ln_time">'.ln_get_timeformat($numrecord['time']).'</p>'
			. '</div></div><div style="border-top: 1px solid #DDD; margin-top: 4px;padding-top: 4px;"></div><div style="clear:both;"></div></li>';
			
			}
			$var++;
		}
					$myoutput.='</ul>';

	}
	
	return $myoutput;
}
/**************avatar***********************/
function avatar_strip_suffix($file)
{
	$parts = pathinfo($file);
	$base = basename($file, '.' . $parts['extension']);

	if(substr($base, -(strlen("avatar") + 1)) == ('-' . "avatar")) {
		$base = substr($base, 0, strlen($base) - (strlen("avatar") + 1));
	}
	if(substr($base, -(strlen("cropped") + 1)) == ('-' . "cropped")) {
		$base = substr($base, 0, strlen($base) - (strlen("cropped") + 1));
	}

	$f[BASE_FILE] = $parts['dirname'] . '/' . $base . '.' . $parts['extension'];
	$f[AVTR_FILE] = $parts['dirname'] . '/' . $base . '-' . "avatar" . '.' . $parts['extension'];
	$f[CROP_FILE] = $parts['dirname'] . '/' . $base . '-' . "cropped" . '.' . $parts['extension'];

	return $f;
}
// Crop uploaded image.
function avatar_crop($user, $file)
{
	list($w, $h, $type, $attr) = getimagesize(avatar_root() . $file);

	$image_functions = array(
			IMAGETYPE_GIF => 'imagecreatefromgif',
			IMAGETYPE_JPEG => 'imagecreatefromjpeg',
			IMAGETYPE_PNG => 'imagecreatefrompng',
			IMAGETYPE_WBMP => 'imagecreatefromwbmp',
			IMAGETYPE_XBM => 'imagecreatefromxbm'
	);

	$src = $image_functions[$type](avatar_root() . $file);
	$options = get_option("ln_options");
	if($src) {
		$dst = imagecreatetruecolor($options['ln_avatar_height'], $options['ln_avatar_height']);
		imagesavealpha($dst, true);
		$trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
		imagefill($dst, 0, 0, $trans);
		$chk = imagecopyresampled($dst, $src, 0, 0, $_POST['x1'], $_POST['y1'], $options['ln_avatar_height'], $options['ln_avatar_height'], $_POST['w'], $_POST['h']);

		if($chk) {
			$parts = pathinfo($file);
			$base = basename($parts['basename'], '.' . $parts['extension']);
			$file = $parts['dirname'] . '/' . $base . '-' . 'cropped' . '.' . $parts['extension'];

			$image_functions = array(
					IMAGETYPE_GIF => 'imagegif',
					IMAGETYPE_JPEG => 'imagejpeg',
					IMAGETYPE_PNG => 'imagepng',
					IMAGETYPE_WBMP => 'imagewbmp',
					IMAGETYPE_XBM => 'imagexbm'
			);

			$image_functions[$type]($dst, avatar_root() . $file);

			// Save the new local avatar for this user.
			update_usermeta($user->ID, 'avatar', $file);

			imagedestroy($dst);
		}
	}
}
function output_avatar_error_message($usr)
{
	if($usr->avatar_error) {
		printf("<div id='message' class='error fade' style='width: 100%%;'><strong>%s</strong> %s</div>", __('Upload error:', 'avatars'), $usr->avatar_error);
	}
	delete_usermeta($usr->ID, 'avatar_error');
}


function get_avatar_type()
{
	global $avatar_type;

	switch($avatar_type) {
		case TYPE_GLOBAL:	return __('Global', 'ln_notifications');
		case TYPE_LOCAL:	return __('Local', 'ln_notifications');
		default:			return __('Default', 'ln_notifications');
	}
}

function avatar_root()
{
	return substr(ABSPATH, 0, -strlen(strrchr(substr(ABSPATH, 0, -1), '/')) - 1);
}
function avatar_upload($user_id)
{
	$info = '';

	// Make sure WP's media library is available.
	if(!function_exists('image_resize')) include_once(ABSPATH . '/wp-includes/media.php');

	// Make sure WP's filename sanitizer is available.
	if(!function_exists('sanitize_file_name')) include_once(ABSPATH . '/wp-includes/formatting.php');

	// Valid file types for upload.
	$valid_file_types = array(
			"image/jpeg" => true,
			"image/pjpeg" => true,
			"image/gif" => true,
			"image/png" => true,
			"image/x-png" => true
	);

	// The web-server root directory.  Used to create absolute paths.
	$root = avatar_root();


	
	// Upload a local avatar.
	if(isset($_FILES['avatar_file']) && @$_FILES['avatar_file']['name']) {	// Something uploaded?
		if($_FILES['avatar_file']['error']) $error = 'Upload error.';		// Any errors?
		else if(@$valid_file_types[$_FILES['avatar_file']['type']]) {		// Valid types?
			//$path = trailingslashit("/wp-content/wp_custom_avatar");
			$path = trailingslashit("/wp_custom_avatar");
			
			$file = sanitize_file_name($_FILES['avatar_file']['name']);
			// Directory exists?
			if(!file_exists($root . $path) && @!mkdir($root . $path, 0755)) $error = __("Upload directory doesn't exist.", 'ln_notifications');
			else {
				// Get a unique filename.
				// First, if already there, include the User's ID; this should be enough.
				if(file_exists($root . $path . $file)) {
					$parts = pathinfo($file);
					$file = basename($parts['basename'], '.' . $parts['extension']) . '-' . $user_id . '.' . $parts['extension'];
				}

				// Second, if required loop to create a unique file name.
				$i = 0;
				while(file_exists($root . $path . $file) && $i < UPLOAD_TRIES) {
					$i++;
					$parts = pathinfo($file);
					$file = substr(basename($parts['basename'], '.' . $parts['extension']), 0, strlen(basename($parts['basename'], '.' . $parts['extension'])) - ($i > 1 ? 2 : 0)) . '-' . $i . '.' . $parts['extension'];
				}
				if($i >= UPLOAD_TRIES) $error = __('Too many tries to find non-existent file.', 'ln_notifications');

				$file = strtolower($file);

				// Copy uploaded file.
				if(!move_uploaded_file($_FILES['avatar_file']['tmp_name'], $root . $path . $file)) $error = __('File upload failed.', 'ln_notifications');
				else chmod($root . $path . $file, 0644);

				// Remember uploaded file information.
				$info = getimagesize($root . $path . $file);
				$info[4] = $path . $file;

			}
		}
		else $error = __('Wrong type.', 'ln_notifications');

		// Save the new local avatar for this user.
		if(empty($error)) update_usermeta($user_id, 'avatar', $path . $file);
	}

	// If there was an an error, record the text for display.
	if(!empty($error)) update_usermeta($user_id, 'avatar_error', $error);

	return $info;
}
if(!function_exists('get_avatar')) :
function get_avatar($id_or_email, $size = '', $default = '', $post = false)
{
	if(!get_option('show_avatars')) return false;							// Check if avatars are turned on.
	$options = get_option("ln_options");
	if(!is_numeric($size) || $size == '') $size = $options['ln_avatar_height'];	// Check default avatar size.

	$email = '';															// E-mail key for Gravatar.com
	$url = '';																// Anchor.
	$id = '';																// User ID.
	$src = '';																// Image source;

	if(is_numeric($id_or_email)) {											// Numeric - user ID...
		$id = (int)$id_or_email;
		$user = get_userdata($id);
		if($user) {
			$email = $user->user_email;
			$url = $user->user_url;
		}
	}
	elseif(is_object($id_or_email)) {										// Comment object...
		if(!empty($id_or_email->user_id)) {									// Object has a user ID, commenter was registered & logged in...
			$id = (int)$id_or_email->user_id;
			$user = get_userdata($id);
			if($user) {
				$email = $user->user_email;
				$url = $user->user_url;
			}
		}
		else {																// Comment object...

			switch($id_or_email->comment_type) {
				case 'trackback':											// Trackback...
				case 'pingback':
					$url_array = parse_url($id_or_email->comment_author_url);
					$url = "http://" . $url_array['host'];
				break;

				case 'comment':												// Comment...
				case '':
					if(!empty($id_or_email->comment_author_email)) $email = $id_or_email->comment_author_email;
					$user = get_user_by_email($email);
					if($user) $id = $user->ID;								// Set ID if we can to check for local avatar.
					$url = $id_or_email->comment_author_url;
				break;
			}
		}
	}
	else {																	// Assume we have been passed an e-mail address...
		if(!empty($id_or_email)) $email = $id_or_email;
		$user = get_user_by_email($email);
		if($user) $id = $user->ID;											// Set ID if we can to check for local avatar.
	}

	// What class to apply to avatar images?
	$class = ($post ? 'post_avatar no-rate' : 'avatar');

	// Try to use local avatar.
	if($id) {
		$local = get_usermeta($id, 'avatar');
		if(!empty($local)) {
			$src = get_option('siteurl').$local;
		}
	}

	// No local avatar source, so build global avatar source...
	if(!$src) {
		if ( !empty($email) )
			$email_hash = md5( strtolower( $email ) );

		if ( is_ssl() ) {
			$src = 'https://secure.gravatar.com/avatar/';
		} else {
			if ( !empty($email) )
				$src = sprintf( "http://%d.gravatar.com/avatar/", ( hexdec( $email_hash{0} ) % 2 ) );
			else
				$src = 'http://0.gravatar.com/avatar/';
		}
		
		if(empty($email)) $src .= md5(strtolower((empty($default) ? UNKNOWN : BLANK)));
		else $src .= md5(strtolower($email));
		$src .= '?s=' . $size;

		$src .= '&amp;d=';
		if ($options['ln_default_avatar'] != "")
			$src .= check_switch("custom", $options['ln_default_avatar'], $size);
		else
			$src .= urlencode(FALLBACK);
		
		$rating = get_option('avatar_rating');
		if(!empty($rating)) $src .= "&amp;r={$rating}";

	}

	$avatar = "<img src='{$src}' class='{$class} avatar-{$size} avatar-default' height='{$size}' width='{$size}' style='width: {$size}px; height: {$size}px;' alt='avatar' />";

	// Return the filtered result.
	return apply_filters('get_avatar', $avatar, $id_or_email, $size, $default);
}
endif;
function check_switch($chk, $default, $size = SCALED_SIZE)
{
	switch ($chk) {
		case 'custom': return $default;
		case 'mystery': return urlencode(FALLBACK . "?s=" . $size);
		case 'blank': return includes_url('images/blank.gif');
		case 'gravatar_default': return "";
		default: return urlencode($chk);
	}
}

//private message
include_once plugin_dir_path( __FILE__ ) . 'vbsocial_pm.php';
 

//for dispaly notification jayesh
function before_post_content() {
    	do_action('before_post_content');
}	
if ( ! function_exists( 'post_content_info_box' ) ) {	
	function post_content_info_box() {
		global $wpdb;
		$options = get_option('ln_options'); 
		?>
        <input type="hidden" name="login_check" id="login_check" value="<?php if(is_user_logged_in()) {echo '1';} else {echo '0';} ?>" />
        <input type="hidden" name="login_valid" id="login_valid" value="<?php echo $options['ln_bmpopup']; ?>" />
         <input type="hidden" name="xbarvalid" id="xbarvalid" value="" />
        
        <?php
		
		if($options['ln_bmpopup']=='enable') 
	{
 ?>


 
  <?php
	     $current_user = wp_get_current_user();    
    	 $current_user->ID;
		 $query_last_notification=mysql_query("select * from ".$wpdb->prefix."livenotifications  where userid='".$current_user->ID."' ORDER BY id DESC LIMIT 1 "); 
		 while($row_time=mysql_fetch_array($query_last_notification)){				
			 $time=$row_time['time'];
			 $now = strtotime(now);
			 $new_timesecond=$now-$time;
			 $new_time=floor(($now-$time)/60);
			 $username1=$row_time['username'];
				
			 $content_text=$row_time['content_text'];
			 			 $content_id=$row_time['content_id'];
						 
			
				
echo '<div class="notification_box" id="notification_box" style="position:fixed; opacity:0.5; top:550px; z-index:9999; background: rgb(201, 204, 255); border:1px solid #000; border-radius:3px; left:20px;display:none;">
					 <div class="close2" id="close1" style="display:none;margin:0px; width:10px; height:10px;float:right;cursor:pointer;">x</div>';
				$user=$row_time['userid_subj'];
				$user_content=$row_time['content_type'];
				$content_id=$row_time['content_id'];
				$rewardimg=mysql_fetch_array(mysql_query("select * from ".$wpdb->prefix."rewardsystem where reid='".$content_id."'"));
				$select_user=mysql_query("select *from ".$wpdb->prefix."users where ID='".$user."'");
				$row_user=mysql_fetch_assoc($select_user);				
				
				if($user_content=='comment'){
					$phrases="has commented on your article";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='reply'){
					$phrases="responsed to your comment";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='mention'){
					$phrases="mentioned you in article";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='pm'){
					$phrases="sent you private message";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='friend'){
					$phrases="has added you";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='mod_comment'){
					$phrases="comment(s) awaiting for your approva";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='bbpressnotification'){
					$phrases="Added new topic ";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
					if($user_content=='adminnotification'){
					$phrases="Sitewide Message:";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
					
				}
				if($user_content=='bbpressnotificationreply'){
					$phrases="replied on ";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}	
									
				if($user_content=='postaward'){
					$phrases=" is your next target";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='commentaward'){
					$phrases="is your next target";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='friend_post'){
					$phrases="is your next target";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='messageaward'){
					$phrases="is your next target";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
				if($user_content=='readpostaward'){
					$phrases="Keep Learning";
					$phrases1='<p style="float:left;margin-top:10px;"><img alt="'.$rewardimg['numlist'].' '.$rewardimg['type'].'" src="'.$rewardimg['rew_image'].'" width="40" height="40"></li><li style="float:right; margin-left:5px; margin-top:15px;list-style:none;">'.$username1.' '.$phrases.'<br /><br /><a href="'.get_permalink($content_id).'">'.$content_text.'</a><br /><br />'.$new_timesecond.'seconds ago</p>';
				}
		 		
				$url = admin_url(); 
				echo $return=$phrases1.'</div>';			
			 
		 }
		?>
<?php
	}}		  
}
function post_read_content()
{
	global $wpdb;
	global $current_user;
 	get_currentuserinfo();
	if($current_user->ID!='0')
	{
		$posttype=get_post_type(get_the_ID());
		if($posttype=='post')
		{
			$count_post1=mysql_num_rows(mysql_query("select * from ".$wpdb->prefix."count_reading where userid='".$current_user->ID."' and postid='".get_the_ID()."' and  posttype='readpost'"));
			if($count_post1==0)
			{
				mysql_query("insert into ".$wpdb->prefix."count_reading (userid,postid,readtime,posttype) values('".$current_user->ID."','".get_the_ID()."','".time()."','readpost')");
				$count_post=mysql_num_rows(mysql_query("select * from ".$wpdb->prefix."count_reading where userid='".$current_user->ID."' and  posttype='readpost'"));
	
	//get reward system data
				$getpostreward=mysql_query("select * from ".$wpdb->prefix."rewardsystem where type='readpost' ORDER BY `reid` ASC");
			while($getpostrewardrec=mysql_fetch_array($getpostreward))
			{
		$numlist=$getpostrewardrec['numlist'];
		$repoint=$getpostrewardrec['repoint'];
		$reorder=$getpostrewardrec['reorder'];
		$type=$getpostrewardrec['type'];
		$retitle=$getpostrewardrec['retitle'];
		$remsg=$getpostrewardrec['remsg'];
		$reid=$getpostrewardrec['reid'];
	
		if($numlist==$count_post){
			
			
			
		//insert into point table
		$countpoints=mysql_query("insert into ".$wpdb->prefix."countpoints (cp_uid,cp_pmid,cp_points,cp_time,cp_tasklist) values('".$current_user->ID."','".get_the_ID()."','".$repoint."','".time()."','".$reorder."')");
		
		
		
		$selectorder=mysql_query("select cp_tasklist from ".$wpdb->prefix."countpoints where cp_uid='".$current_user->ID."'");
			if(mysql_num_rows($selectorder)>0)
			{
			$reclist=0;
			while($selectorderrec=mysql_fetch_array($selectorder))
			{
				if($reclist==0)
				{
					$order .="reorder!=".$selectorderrec['cp_tasklist'];
				}
				else
				{
					$order .=" and reorder!=".$selectorderrec['cp_tasklist'];
				}
				$reclist++;
			}
			}
			else
			{
				$order="1=1";
			}
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where ".$order." ORDER BY reorder ASC");
			$rank=mysql_fetch_array($selectdata1);
			$rank_next=$rank['numlist'].' '.$rank['type'];
			
			
			
		//insert into livenotification table
		$selectoption=mysql_query("select enable_award from ".$wpdb->prefix."livenotifications_usersettings where userid='".$userid."'");
			if(mysql_num_rows($selectoption)>0)
			{
				$selectoptionrec=mysql_fetch_array($selectoption);
				$award=$selectoptionrec['enable_award'];
			}
			else
			{
				$options = get_option('ln_options');
				$award=$options['enable_award'];
				if($award=='on')
				{
					$award='1';
				}
				else
				{
					$award='0';
				}
			}
						if($award=='1')
						{
		$livesnotificationtable=mysql_query("insert into ".$wpdb->prefix."livenotifications (userid,userid_subj,content_type,content_id,content_text,is_read,time,username) values('".$current_user->ID."','".$current_user->ID."','readpostaward','".$reid."','".$remsg."','0','".time()."','".$rank_next."')");
						}
		}
	}
	
			}
		}
	}
}
add_action('wp_footer','post_read_content');
add_action('wp_footer','post_content_info_box');



//WIDGET BY jayesh
//widget1
add_action( 'widgets_init', 'my_widget' );
add_action( 'widgets_init', 'my_widget1' );
add_action( 'widgets_init', 'my_widget2' );


function my_widget() {
	register_widget( 'MY_Widget' );
}

class MY_Widget extends WP_Widget {

	function MY_Widget() {
		$widget_ops = array( 'classname' => 'example', 'description' => __('A widget that displays Top Leaders of the Week  ', 'example') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'example-widget' );
		
		$this->WP_Widget( 'example-widget', __('My stats widget', 'example'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		//$name = $instance['name'];
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;

		echo $before_widget;

		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;
		//user status
		global $wpdb;
		 $currentuserid=get_current_user_id();
		
		 //count comments for all time
		  $date1=time();
 $yesterday=mktime(23, 59, 59, date("m", $date1), date("d", $date1)-1, date("Y", $date1)); 

 $tomorrow=mktime(23, 59, 59, date("m", $date1), date("d", $date1), date("Y", $date1)); 
		 $select_comment=mysql_query("select * from ".$wpdb->prefix."count_reading where userid='".$currentuserid."' AND posttype='comment' AND readtime BETWEEN '".$yesterday."' AND '".$tomorrow."'");
		$num_rows = mysql_num_rows($select_comment);
		//user register total days
		$select_registerdate=mysql_query("select user_registered from ".$wpdb->prefix."users where ID='".$currentuserid."'");
		while($register_date1=mysql_fetch_array($select_registerdate)){
		$register_date=strtotime($register_date1['user_registered']);
		
		$datediff = time() - $register_date;
     	$dd=floor($datediff/(60*60*24));
		if($dd==0)
		{
			$dd=1;
		}
		$dd1=$num_rows/$dd;
		}
		 //points per day
	
			$select_user_stat1=mysql_query("select * from ".$wpdb->prefix."week_rankcount where user_id='".$currentuserid."'" );
		while($row_user_status=mysql_fetch_assoc($select_user_stat1)){
			$points_user_dif=$row_user_status['rank'];
			$points_user_diff=$row_user_status['rank_diff'];
			$points_user_diff1=$row_user_status['rank_diff1'];
			if($points_user_diff1 > $points_user_dif){
				$position_diff=$points_user_diff1-$points_user_dif;
				$position='Moved  <font color="#660066" size="+2">  '.$position_diff.'</font>  Position Up this week';		
			}

			else{			
							$position_diff=$points_user_dif-$points_user_diff1;
		    $position='Moved  <font color="#660066" size="+2">  '.$position_diff.'</font>   Position Down this Week';			
			}
		}
		//Display the name 
		if ( $name )
		printf( '<div><p><font color="green" size="4">'.round($dd1,3).'</font> post/days</p></div>');
			if(!empty($points_user_dif))
			{
			printf( '<div>Ranked <font color="#333333" size="+2">#'.$points_user_dif. '</font> on site<br>'.$position.'</p></div>', $name );
			}
			
			

		
		if ( $show_info )
			printf( $name );

		
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		//$instance['name'] = strip_tags( $new_instance['name'] );
	//	$instance['show_info'] = $new_instance['show_info'];

		return $instance;
	}

	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('My Stats Record', 'My Stats Record'), 'name' => __('Bilal Shaheen', 'example'), 'show_info' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>">
    <?php _e('Title:', 'example'); ?>
  </label>
  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
</p>
<?php
	}
}
//widget2
function my_widget1() {
	register_widget( 'MY_Widget1' );
}

class MY_Widget1 extends WP_Widget {

	function MY_Widget1() {
		$widget_ops = array( 'classname' => 'example', 'description' => __('A widget that displays All Time Leaderboard  ', 'example') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'example-widget1' );
		
		$this->WP_Widget( 'example-widget1', __('All Time Leaderboard', 'example1'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		//$name = $instance['name'];
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;

		echo $before_widget;

		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;
		//user status
		global $wpdb;
		
			$select_user_stat1=mysql_query("select rank,user_id,all_count from ".$wpdb->prefix."all_rankcount where rank BETWEEN 1 AND 3 order by rank ASC" );
		while($row_user_status=mysql_fetch_assoc($select_user_stat1)){
			$points_user_diff=$row_user_status['user_id'];
			$points_user_rank=$row_user_status['rank'];
			$points_user_count=$row_user_status['all_count'];			

		
			$select_username=mysql_query("select user_nicename from ".$wpdb->prefix."users where ID='".$points_user_diff."'" );
		while($row_username=mysql_fetch_assoc($select_username)){
			$points_username=$row_username['user_nicename'];

		//Display the name 
		if ( $name )
			printf( '<div><p> #'.$points_user_rank.'  <font >' .$points_username.'</font>  '.$points_user_count.' Points</p></div>', $name );
		}
		}
		
		if ( $show_info )
			printf( $name );

		
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		//$instance['name'] = strip_tags( $new_instance['name'] );
	//	$instance['show_info'] = $new_instance['show_info'];

		return $instance;
	}

	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('All Time Leaderboard ', 'All Time Leaderboard '), 'name' => __('Bilal Shaheen', 'example'), 'show_info' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>">
    <?php _e('Title:', 'example'); ?>
  </label>
  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
</p>
<?php
	}
}
//widget3
function my_widget2() {
	register_widget( 'MY_Widget2' );
}

class MY_Widget2 extends WP_Widget {

	function MY_Widget2() {
		$widget_ops = array( 'classname' => 'example', 'description' => __('A widget that displays Top Leaders of the Week  ', 'example') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'example-widget2' );
		
		$this->WP_Widget( 'example-widget2', __('Top Leaders of the Week ', 'example2'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		//$name = $instance['name'];
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;

		echo $before_widget;

		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;
		//user status
		global $wpdb;
		 $currentuserid=get_current_user_id();
		
			$select_user_stat1=mysql_query("select user_id,rank,week_count from ".$wpdb->prefix."week_rankcount where rank BETWEEN 1 AND 3 order by rank ASC" );
		while($row_user_status=mysql_fetch_assoc($select_user_stat1)){
			$points_user_diff=$row_user_status['user_id'];
			$points_user_rank=$row_user_status['rank'];
			$points_user_count=$row_user_status['week_count'];

		
			$select_username=mysql_query("select user_nicename from ".$wpdb->prefix."users where ID='".$points_user_diff."'" );
		while($row_username=mysql_fetch_assoc($select_username)){
			$points_username=$row_username['user_nicename'];

		
		//Display the name 
		if ( $name )
			printf( '<div><p> #'.$points_user_rank.'  <font >' .$points_username.'</font>  '.$points_user_count.' Points</p></div>', $name );

		
		if ( $show_info )
			printf( $name );
	}
}
		
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		//$instance['name'] = strip_tags( $new_instance['name'] );
	//	$instance['show_info'] = $new_instance['show_info'];

		return $instance;
	}

	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('Top Leaders of the Week ', 'Top Leaders of the Week '), 'name' => __('Bilal Shaheen', 'example'), 'show_info' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>">
    <?php _e('Title:', 'example'); ?>
  </label>
  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
</p>
<?php
	}
}

function user_award_count(){
global $wpdb;
 $currentuserid=get_current_user_id();
  $date1=time();
  $yesterday=mktime(23, 59, 59, date("m", $date1), date("d", $date1)-1, date("Y", $date1)); 
 
  $tomorrow=mktime(23, 59, 59, date("m", $date1), date("d", $date1), date("Y", $date1)); 

  
	$query_point_per_day=mysql_query("SELECT SUM(cp_points) as total,cp_time,cp_uid FROM ".$wpdb->prefix."countpoints WHERE cp_time BETWEEN '".$yesterday."' AND '".$tomorrow."' group by cp_uid");
	
	while($row_post_per_day=mysql_fetch_array($query_point_per_day)){
		 $cp_total=$row_post_per_day['total'];
		 $cp_uid1=$row_post_per_day['cp_uid'];
	 	 $cpu1=date('Y-m-d h:i:s A',$row_post_per_day['cp_time']);
	
		$select_date=mysql_query("select * from ".$wpdb->prefix."rankcount where user_id='".$cp_uid1."'");
		 if(mysql_num_rows($select_date)==0 )
		 {
			 //insert
			 $insert_point=mysql_query("insert into ".$wpdb->prefix."rankcount (start_date,update_date,daily_count,user_id) values('".time()."','".time()."','".$cp_total."','".$cp_uid1."')");
		 }
		 else
		 {
			 //update
			 $update_point=mysql_query("update ".$wpdb->prefix."rankcount SET update_date='".time()."',daily_count='".$cp_total."' where user_id='".$cp_uid1."'");
		 }		
	
}
//daily rank
						$rank_find1=mysql_query("SELECT id, daily_count, user_id, FIND_IN_SET( daily_count, (
SELECT GROUP_CONCAT( daily_count ORDER BY daily_count DESC ) 
FROM ".$wpdb->prefix."rankcount )
) AS rank1
FROM ".$wpdb->prefix."rankcount order by daily_count DESC");
				
		while($rankfind1=mysql_fetch_array($rank_find1)){

				 $ranks=$rankfind1['rank1'];
				 $week_points=$rankfind1['daily_count'];
				 $week_user_id=$rankfind1['user_id'];

				$update_rank=mysql_query("update ".$wpdb->prefix."rankcount SET rank='".$ranks."' WHERE user_id='".$week_user_id."'");		 
			}

	
	
	//week point count
  $date2=time();
  $monday=date("D", $date2);
  if($monday=='Mon')
 	{
	  $yesterday1=mktime(23, 59, 59, date("m", $date2), date("d", $date2)-7, date("Y", $date2)); 
   $tomorrow1=mktime(23, 59, 59, date("m", $date2), date("d", $date2), date("Y", $date2)); 
		
		  $query_point_per_day_week=mysql_query("SELECT SUM(cp_points) as totals,cp_time,cp_uid FROM ".$wpdb->prefix."countpoints WHERE cp_time BETWEEN '".$yesterday1."' AND '".$tomorrow1."' group by cp_uid ");
	
		while($row_post_per_day_week=mysql_fetch_array($query_point_per_day_week)){
	    $cpu_week=$row_post_per_day_week['totals'];
		$userid_week=$row_post_per_day_week['cp_uid'];
		$cpu1_week=date('d',$row_post_per_day_week['cp_time']);
		$select_date_week=mysql_query("select * from ".$wpdb->prefix."week_rankcount where user_id='".$userid_week."'");
		if(mysql_num_rows($select_date_week)==0 ){
			 //insert
		 	$insert_point_week=mysql_query("insert into ".$wpdb->prefix."week_rankcount (start_date,week_count,user_id) values('".time()."','".$cpu_week."','".$userid_week."')");
		 }
		else {
			 //update
			 $update_point_week=mysql_query("update ".$wpdb->prefix."week_rankcount SET week_count='".$cpu_week."' where user_id='".$userid_week."'");
		 }
		 
		
	
				
		}
		//weekly rank
		$rank_find=mysql_query("SELECT id, week_count, user_id, FIND_IN_SET( week_count, (
SELECT GROUP_CONCAT( week_count ORDER BY week_count DESC ) FROM ".$wpdb->prefix."week_rankcount )
) AS rank2 FROM ".$wpdb->prefix."week_rankcount");
		
		
		while($rankfind=mysql_fetch_array($rank_find)){

				 $ranks=$rankfind['rank2'];
				 $week_points=$rankfind['week_count'];
				 $week_user_id=$rankfind['user_id'];	
				//move position check
				$selectuserrec=mysql_fetch_array(mysql_query("select * from ".$wpdb->prefix."week_rankcount where user_id='".$week_user_id."'"));
				if($selectuserrec['rank']!=$ranks)
				{
					$update_rank=mysql_query("update ".$wpdb->prefix."week_rankcount SET rank_diff='".$ranks."' WHERE user_id='".$week_user_id."'");
					
					$update_rank=mysql_query("update ".$wpdb->prefix."week_rankcount SET rank_diff1='".$selectuserrec['rank']."' WHERE user_id='".$week_user_id."'");				
					
					$update_rank=mysql_query("update ".$wpdb->prefix."week_rankcount SET rank='".$ranks."' WHERE user_id='".$week_user_id."'");				
				}
				else
				{
		 		$update_rank=mysql_query("update ".$wpdb->prefix."week_rankcount SET rank='".$ranks."' WHERE user_id='".$week_user_id."'");
				}
			}
	}

			 
			
	 
			//All time  rank insert
	$rank_find_all=mysql_query("SELECT sum(cp_points) as total2,cp_uid
FROM ".$wpdb->prefix."countpoints GROUP BY cp_uid order by total2 DESC ");
							
	while($rankfindall=mysql_fetch_array($rank_find_all)){			
		 $week_points=$rankfindall['total2'];			
	     $week_user_id=$rankfindall['cp_uid'];	
		 $rank_find_alls=mysql_query("SELECT  * FROM ".$wpdb->prefix."all_rankcount where user_id='".$week_user_id."'");	
		 if(mysql_num_rows($rank_find_alls)==0 )
		 {					
		 $insert_all_count=mysql_query("insert into ".$wpdb->prefix."all_rankcount (start_date,all_count,user_id) values ('".time()."','".$week_points."','".$week_user_id."')");
		 }
		 else{
			 $update_all_points=mysql_query("update ".$wpdb->prefix."all_rankcount SET all_count='".$week_points."' WHERE user_id='".$week_user_id."'");
			 }
	}
	
	 
			//all rank count
			$rank_count_all=mysql_query("SELECT  user_id, FIND_IN_SET( all_count, (
SELECT GROUP_CONCAT( all_count  ORDER BY all_count DESC)
 FROM ".$wpdb->prefix."all_rankcount) 
) AS rank3
FROM ".$wpdb->prefix."all_rankcount  ");
					
		while($rankfind_all=mysql_fetch_array($rank_count_all)){

				 $ranks=$rankfind_all['rank3'];
				 $week_points=$rankfind_all['all_count'];
				 $week_user_id=$rankfind_all['user_id'];	
				
				$update_rank=mysql_query("update ".$wpdb->prefix."all_rankcount SET rank='".$ranks."' WHERE user_id='".$week_user_id."'");				 
			}
			
	
	}
add_action('wp_footer','user_award_count');



//for admin to hide notification bar
function theme_styles_hide()  
{
	
$options = get_option('ln_options'); 
//for users
if($options['disable_default_bar']=='on')
{
	add_filter( 'show_admin_bar', '__return_false' );
}

if($options['hide_wp_notification']=='on' && is_admin())
{
	echo '<style type="text/css">#ln_livenotifications { display:none !important;};
}</style>';

if($options['disable_default_bar']=='on')
{
	echo '<style type="text/css">#wpadminbar { display:none !important; }html.wp-toolbar{padding-top: 0px !important;
}</style>';
}
}
}
add_action('admin_head', 'theme_styles_hide');
?>