<?php

function lnpm_send( ) {
	require_once("ln_livenotifications.php");
	global $wpdb, $current_user;
	?>
<div class="wrap">
	<?php
	if ( $_REQUEST['page'] == 'lnpm_send' && isset( $_POST['submit'] ) ) {
		
		$error = false;
		$status = array( );
		$sender = $current_user->user_login;
		$total = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'pm WHERE `sender` = "' . $sender . '" OR `recipient` = "' . $sender . '"' );
		
		$subject = strip_tags( $_POST['subject'] );
		$content = strip_tags( $_POST['content'] );
		if($_POST['recipient1'] == "") {$recipient = array();}
		else {$recipient = $_POST['recipient1'];}
		
		$recipient = array_map( 'strip_tags', $recipient );
		if ( get_magic_quotes_gpc( ) ) {
			$subject = stripslashes( $subject );
			$content = stripslashes( $content );
			$recipient = array_map( 'stripslashes', $recipient );
		}
		$subject = esc_sql( $subject );
		$content = esc_sql( $content );
		$recipient = array_map( 'esc_sql', $recipient );

		// remove duplicate and empty recipient
		$recipient = array_unique( $recipient );
		$recipient = array_filter( $recipient );

		if ( empty( $recipient ) ) {
			$error = true;
			$status[] = __( 'Please select username(s) of recipient.', 'ln_livenotifications' );
		}
		if ( empty( $subject ) ) {
			$error = true;
			$status[] = __( 'Please enter subject of message.', 'ln_livenotifications' );
		}
		if ( empty( $content ) ) {
			$error = true;
			$status[] = __( 'Please enter content of message.', 'ln_livenotifications' );
		}

		if ( !$error ) {
			$numOK = $numError = 0;
			foreach ( $recipient as $rec ) {
				// get user_login field
				$rec = $wpdb->get_row( "SELECT user_login, ID FROM $wpdb->users WHERE display_name = '$rec' LIMIT 1" );
				$new_message = array(
					'id' => NULL,
					'subject' => $subject,
					'content' => $content,
					'sender' => $sender,
					'recipient' => $rec->user_login,
					'date' => current_time( 'mysql' ),
					'read' => 0,
					'deleted' => 0
				);
				// insert into database
				if ($wpdb->insert( $wpdb->prefix . 'pm', $new_message, array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) ) ) {
					$numOK++;
					ln_add_user_notification($current_user->ID, $rec->ID, 'pm', $wpdb->insert_id, $content,0,0,$sender);
					 mysql_query("insert into ".$wpdb->prefix."count_reading (userid,postid,readtime,posttype) values('".$current_user->ID."','".$wpdb->insert_id."','".time()."','message')");
					 
					 $count_post=mysql_num_rows(mysql_query("select * from ".$wpdb->prefix."count_reading where userid='".$current_user->ID."' and posttype='message'"));
					 
				  $getpostreward=mysql_query("select * from ".$wpdb->prefix."rewardsystem where type='message'");
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
						$countpoints=mysql_query("insert into ".$wpdb->prefix."countpoints (cp_uid,cp_pmid,cp_points,cp_time,cp_tasklist) values('".$current_user->ID."','".$wpdb->insert_id."','".$repoint."','".time()."','".$reorder."')");
						
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
						$livesnotificationtable=mysql_query("insert into ".$wpdb->prefix."livenotifications (userid,userid_subj,content_type,content_id,content_text,is_read,time,username) values('".$current_user->ID."','".$current_user->ID."','messageaward','".$reid."','".$remsg."','0','".time()."','".$rank_next."')");
						}
						}
					}
					
					 
					unset( $_REQUEST['recipient'], $_REQUEST['subject'], $_REQUEST['content'] );
				
				} else {
					$numError++;
				}
			}

			$status[] = sprintf( _n( '%d message sent.', '%d messages sent.', $numOK, 'ln_livenotifications' ), $numOK ) . ' ' . sprintf( _n( '%d error.', '%d errors.', $numError, 'ln_livenotifications' ), $numError );
			
		}

		echo '<div id="message" class="updated fade"><p>', implode( '</p><p>', $status ), '</p></div>';
	}
	?>
<form method="post" action="" id="send-form">
	<table class="form-table">
		<tr>
			<th><?php _e( 'Recipient', 'ln_livenotifications' ); ?></th>
			<td>
	<?php
 				// if message is not sent (by errors) or in case of replying, all input are saved

		$recipient = !empty( $_POST['recipient1'] ) ? $_POST['recipient1'] : ( !empty( $_GET['recipient1'] )
			? $_GET['recipient1'] : '' );

		// strip slashes if needed
		$subject = isset( $_REQUEST['subject'] ) ? ( get_magic_quotes_gpc( ) ? stripcslashes( $_REQUEST['subject'] )
			: $_REQUEST['subject'] ) : '';
		$subject = urldecode( $subject );  // for some chars like '?' when reply
		$content = isset( $_REQUEST['content'] ) ? ( get_magic_quotes_gpc( ) ? stripcslashes( $_REQUEST['content'] )
			: $_REQUEST['content'] ) : '';

		// Get all users of blog
		$users = $wpdb->get_results("SELECT display_name FROM $wpdb->users WHERE ID <> ".$current_user->ID." ORDER BY display_name ASC");

		
			?>
			<select name="recipient1[]" multiple="multiple" size="5">
				<?php
							foreach ( $users as $user ) {
				$selected = ( $user->display_name == $recipient ) ? ' selected="selected"' : '';
				echo "<option value='$user->display_name'$selected>$user->display_name</option>";
			}
				?>
                
			</select>
				
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Subject', 'ln_livenotifications' ); ?></th>
			<td><input type="text" name="subject" value="<?php echo $subject; ?>"/></td>
		</tr>
		<tr>
			<th><?php _e( 'Content', 'ln_livenotifications' ); ?></th>
			<td><textarea cols="50" rows="10" name="content"><?php echo $content; ?></textarea></td>
		</tr>
	</table>
	<p class="submit" id="submit">
		<input type="hidden" name="page" value="lnpm_send"/>
		<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Send', 'ln_livenotifications' ) ?>"/>
	</p>

</form>
</div>
	<?php

}

add_shortcode( 'lnpm_send', 'lnpm_send' );
//Inbox page
 
function lnpm_inbox( ) {
	global $wpdb, $current_user;
$options1 = get_option('ln_options1');
	// if view message
	if ( isset( $_GET['action'] ) && 'view' == $_GET['action'] && !empty( $_GET['id'] ) ) {
		$id = $_GET['id'];

		check_admin_referer( "lnpm-view_inbox_msg_$id" );

		// mark message as read
		$wpdb->update( $wpdb->prefix . 'pm', array( 'read' => 1 ), array( 'id' => $id ) );

		// select message information
		$msg = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $id . '" LIMIT 1' );
		$msg->sender = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->sender'" );
		?>
	<div class="wrap">
		<h2><?php _e( 'Inbox \ View Message', 'ln_livenotifications' ); ?></h2>

		<p><a href="?page=lnpm_inbox"><?php _e( 'Back to inbox', 'ln_livenotifications' ); ?></a></p>
		<table class="widefat fixed" cellspacing="0">
			<thead>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'ln_livenotifications' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'ln_livenotifications' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'ln_livenotifications' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php printf( __( '<b>Sender</b>: %s<br /><b>Date</b>: %s', 'ln_livenotifications' ), $msg->sender, $msg->date ); ?></td>
				<td><?php printf( __( '<p><b>Subject</b>: %s</p><p>%s</p>', 'ln_livenotifications' ), stripcslashes( $msg->subject ), nl2br( stripcslashes( $msg->content ) ) ); ?></td>
				<td>
						<span class="delete">
							<a class="delete" href="<?php echo wp_nonce_url( "?page=lnpm_inbox&action=delete&id=$msg->id", 'lnpm-delete_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Delete', 'ln_livenotifications' ); ?></a>
						</span>
						<span class="reply">
							| <a class="reply" href="<?php echo wp_nonce_url( $options1['plink_sendmsg']."?page=lnpm_send&recipient1=$msg->sender&subject=Re: " . stripcslashes( $msg->subject ), 'lnpm-reply_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Reply', 'ln_livenotifications' ); ?></a>
						</span>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'ln_livenotifications' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'ln_livenotifications' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'ln_livenotifications' ); ?></th>
			</tr>
			</tfoot>
		</table>
	</div>
	<?php
		return;
	}

	// if mark messages as read
	if ( isset( $_GET['action'] ) && 'mar' == $_GET['action'] && !empty( $_GET['id'] ) ) {
		$id = $_GET['id'];

		if ( !is_array( $id ) ) {
			check_admin_referer( "lnpm-mar_inbox_msg_$id" );
			$id = array( $id );
		} else {
			check_admin_referer( "lnpm-bulk-action_inbox" );
		}
		$n = count( $id );
		$id = implode( ',', $id );
		if ( $wpdb->query( 'UPDATE ' . $wpdb->prefix . 'pm SET `read` = "1" WHERE `id` IN (' . $id . ')' ) ) {
			$status = _n( 'Message marked as read.', 'Messages marked as read', $n, 'ln_livenotifications' );
		} else {
			$status = __( 'Error. Please try again.', 'ln_livenotifications' );
		}
	}

	// if delete message
	if ( isset( $_GET['action'] ) && 'delete' == $_GET['action'] && !empty( $_GET['id'] ) ) {
		$id = $_GET['id'];

		if ( !is_array( $id ) ) {
			check_admin_referer( "lnpm-delete_inbox_msg_$id" );
			$id = array( $id );
		} else {
			check_admin_referer( "lnpm-bulk-action_inbox" );
		}

		$error = false;
		foreach ( $id as $msg_id ) {
			// check if the sender has deleted this message
			$sender_deleted = $wpdb->get_var( 'SELECT `deleted` FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '" LIMIT 1' );

			// create corresponding query for deleting message
			if ( $sender_deleted == 1 ) {
				$query = 'DELETE from ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '"';
				
			} else {
				$query = 'UPDATE ' . $wpdb->prefix . 'pm SET `deleted` = "2" WHERE `id` = "' . $msg_id . '"';
			}
			$sql = "DELETE FROM " . $wpdb->prefix . "livenotifications WHERE content_type = 'pm' AND content_id = ".$msg_id ;
			if ( !$wpdb->query( $query ) ) {
				$error = true;
			}
			else{
				$wpdb->query( $sql);
			}
		}
		if ( $error ) {
			$status = __( 'Error. Please try again.', 'ln_livenotifications' );
		} else {
			$status = _n( 'Message deleted.', 'Messages deleted.', count( $id ), 'ln_livenotifications' );
		}
	}

	// show all messages which have not been deleted by this user (deleted status != 2)
	$msgs = $wpdb->get_results( 'SELECT `id`, `sender`, `subject`, `read`, `date` FROM ' . $wpdb->prefix . 'pm WHERE `recipient` = "' . $current_user->user_login . '" AND `deleted` != "2" ORDER BY `date` DESC' );
	?>
<div class="wrap">
	<?php /*?><h2><?php _e( 'Inbox', 'ln_livenotifications' ); ?></h2><?php */?>
	<?php
	if ( !empty( $status ) ) {
	echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
}
	if ( empty( $msgs ) ) {
		echo '<p>', __( 'You have no items in inbox.', 'ln_livenotifications' ), '</p>';
	} else {
		$n = count( $msgs );
		$num_unread = 0;
		foreach ( $msgs as $msg ) {
			if ( !( $msg->read ) ) {
				$num_unread++;
			}
		}
		echo '<p>', sprintf( _n( 'You have %d private message (%d unread).', 'You have %d private messages (%d unread).', $n, 'ln_livenotifications' ), $n, $num_unread ), '</p>';
		?>
		<form action="" method="get">
			<?php wp_nonce_field( 'lnpm-bulk-action_inbox' ); ?>
			<input type="hidden" name="page" value="lnpm_inbox"/>

			<div class="tablenav">
				<select name="action">
					<option value="-1" selected="selected"><?php _e( 'Bulk Action', 'ln_livenotifications' ); ?></option>
					<option value="delete"><?php _e( 'Delete', 'ln_livenotifications' ); ?></option>
					<option value="mar"><?php _e( 'Mark As Read', 'ln_livenotifications' ); ?></option>
				</select> <input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'ln_livenotifications' ); ?>"/>
			</div>

			<table class="widefat fixed" cellspacing="0">
				<thead>
				<tr>
					<th class="manage-column check-column"></th>
					<th class="manage-column" width="10%"><?php _e( 'Sender', 'ln_livenotifications' ); ?></th>
					<th class="manage-column"><?php _e( 'Subject', 'ln_livenotifications' ); ?></th>
					<th class="manage-column" width="20%"><?php _e( 'Date', 'ln_livenotifications' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php
	 			foreach ( $msgs as $msg ) {
					$msg->sender = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->sender'" );
					?>
				<tr>
					<th class="check-column"><input type="checkbox" name="id[]" value="<?php echo $msg->id; ?>"/></th>
					<td><?php echo $msg->sender; ?></td>
					<td>
						<?php
						if ( $msg->read ) {
						echo '<a href="', wp_nonce_url( "?page=lnpm_inbox&action=view&id=$msg->id", 'lnpm-view_inbox_msg_' . $msg->id ), '">', stripcslashes( $msg->subject ), '</a>';
					} else {
						echo '<a href="', wp_nonce_url( "?page=lnpm_inbox&action=view&id=$msg->id", 'lnpm-view_inbox_msg_' . $msg->id ), '"><b>', stripcslashes( $msg->subject ), '</b></a>';
					}
						?>
						<div class="row-actions">
							<span>
								<a href="<?php echo wp_nonce_url( "?page=lnpm_inbox&action=view&id=$msg->id", 'lnpm-view_inbox_msg_' . $msg->id ); ?>"><?php _e( 'View', 'ln_livenotifications' ); ?></a>
							</span>
						<?php
	  							if ( !( $msg->read ) ) {
							?>
							<span>
								| <a href="<?php echo wp_nonce_url( "?page=lnpm_inbox&action=mar&id=$msg->id", 'lnpm-mar_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Mark As Read', 'ln_livenotifications' ); ?></a>
							</span>
							<?php

						}
							?>
							<span class="delete">
								| <a class="delete" href="<?php echo wp_nonce_url( "?page=lnpm_inbox&action=delete&id=$msg->id", 'lnpm-delete_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Delete', 'ln_livenotifications' ); ?></a>
							</span>
							<span class="reply">
								| <a class="reply" href="<?php echo wp_nonce_url( $options1['plink_sendmsg']."?page=lnpm_send&recipient1=$msg->sender&subject=Re: " . stripcslashes( $msg->subject ), 'lnpm-reply_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Reply', 'ln_livenotifications' ); ?></a>
							</span>
						</div>
					</td>
					<td><?php echo $msg->date; ?></td>
				</tr>
						<?php

				}
					?>
				</tbody>
				<tfoot>
				<tr>
					<th class="manage-column check-column"></th>
					<th class="manage-column"><?php _e( 'Sender', 'ln_livenotifications' ); ?></th>
					<th class="manage-column"><?php _e( 'Subject', 'ln_livenotifications' ); ?></th>
					<th class="manage-column"><?php _e( 'Date', 'ln_livenotifications' ); ?></th>
				</tr>
				</tfoot>
			</table>
		</form>
					<?php

	}
	?>
</div>
	<?php

}
add_shortcode( 'lnpm_inbox', 'lnpm_inbox' );
/**
 * Outbox page
 */
function lnpm_outbox( ) {
	global $wpdb, $current_user;

	// if view message
	if ( isset( $_GET['action'] ) && 'view' == $_GET['action'] && !empty( $_GET['id'] ) ) {
		$id = $_GET['id'];

		check_admin_referer( "lnpm-view_outbox_msg_$id" );

		// select message information
		$msg = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $id . '" LIMIT 1' );
		$msg->recipient = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->recipient'" );
		?>
	<div class="wrap">
		<h2><?php _e( 'Outbox \ View Message', 'ln_livenotifications' ); ?></h2>

		<p><a href="?page=lnpm_outbox"><?php _e( 'Back to outbox', 'ln_livenotifications' ); ?></a></p>
		<table class="widefat fixed" cellspacing="0">
			<thead>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'ln_livenotifications' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'ln_livenotifications' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'ln_livenotifications' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php printf( __( '<b>Recipient</b>: %s<br /><b>Date</b>: %s', 'ln_livenotifications' ), $msg->recipient, $msg->date ); ?></td>
				<td><?php printf( __( '<p><b>Subject</b>: %s</p><p>%s</p>', 'ln_livenotifications' ), stripcslashes( $msg->subject ), nl2br( stripcslashes( $msg->content ) ) ); ?></td>
				<td>
						<span class="delete">
							<a class="delete" href="<?php echo wp_nonce_url( "?page=lnpm_outbox&action=delete&id=$msg->id", 'lnpm-delete_outbox_msg_' . $msg->id ); ?>"><?php _e( 'Delete', 'ln_livenotifications' ); ?></a>
						</span>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'ln_livenotifications' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'ln_livenotifications' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'ln_livenotifications' ); ?></th>
			</tr>
			</tfoot>
		</table>
	</div>
	<?php
  // don't need to do more!
		return;
	}

	// if delete message
	if ( isset( $_GET['action'] ) && 'delete' == $_GET['action'] && !empty( $_GET['id'] ) ) {
		$id = $_GET['id'];

		if ( !is_array( $id ) ) {
			check_admin_referer( "lnpm-delete_outbox_msg_$id" );
			$id = array( $id );
		} else {
			check_admin_referer( "lnpm-bulk-action_outbox" );
		}
		$error = false;
		foreach ( $id as $msg_id ) {
			// check if the recipient has deleted this message
			$recipient_deleted = $wpdb->get_var( 'SELECT `deleted` FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '" LIMIT 1' );
			// create corresponding query for deleting message
			if ( $recipient_deleted == 2 ) {
				$query = 'DELETE from ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '"';
			} else {
				$query = 'UPDATE ' . $wpdb->prefix . 'pm SET `deleted` = "1" WHERE `id` = "' . $msg_id . '"';
			}

			if ( !$wpdb->query( $query ) ) {
				$error = true;
			}
		}
		if ( $error ) {
			$status = __( 'Error. Please try again.', 'ln_livenotifications' );
		} else {
			$status = _n( 'Message deleted.', 'Messages deleted.', count( $id ), 'ln_livenotifications' );
		}
	}

	// show all messages
	$msgs = $wpdb->get_results( 'SELECT `id`, `recipient`, `subject`, `date` FROM ' . $wpdb->prefix . 'pm WHERE `sender` = "' . $current_user->user_login . '" AND `deleted` != 1 ORDER BY `date` DESC' );
	?>
<div class="wrap">
	<?php /*?><h2><?php _e( 'Outbox', 'ln_livenotifications' ); ?></h2><?php */?>
	<?php
	if ( !empty( $status ) ) {
	echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
}
	if ( empty( $msgs ) ) {
		echo '<p>', __( 'You have no items in outbox.', 'ln_livenotifications' ), '</p>';
	} else {
		$n = count( $msgs );
		echo '<p>', sprintf( _n( 'You wrote %d private message.', 'You wrote %d private messages.', $n, 'ln_livenotifications' ), $n ), '</p>';
		?>
		<form action="" method="get">
			<?php wp_nonce_field( 'lnpm-bulk-action_outbox' ); ?>
			<input type="hidden" name="action" value="delete"/> <input type="hidden" name="page" value="lnpm_outbox"/>

			<div class="tablenav">
				<input type="submit" class="button-secondary" value="<?php _e( 'Delete Selected', 'ln_livenotifications' ); ?>"/>
			</div>

			<table class="widefat fixed" cellspacing="0">
				<thead>
				<tr>
					<th class="manage-column check-column"></th>
					<th class="manage-column" width="10%"><?php _e( 'Recipient', 'ln_livenotifications' ); ?></th>
					<th class="manage-column"><?php _e( 'Subject', 'ln_livenotifications' ); ?></th>
					<th class="manage-column" width="20%"><?php _e( 'Date', 'ln_livenotifications' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php
	 			foreach ( $msgs as $msg ) {
					$msg->recipient = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->recipient'" );
					?>
				<tr>
					<th class="check-column"><input type="checkbox" name="id[]" value="<?php echo $msg->id; ?>"/></th>
					<td><?php echo $msg->recipient; ?></td>
					<td>
						<?php
						echo '<a href="', wp_nonce_url( "?page=lnpm_outbox&action=view&id=$msg->id", 'lnpm-view_outbox_msg_' . $msg->id ), '">', stripcslashes( $msg->subject ), '</a>';
						?>
						<div class="row-actions">
							<span>
								<a href="<?php echo wp_nonce_url( "?page=lnpm_outbox&action=view&id=$msg->id", 'lnpm-view_outbox_msg_' . $msg->id ); ?>"><?php _e( 'View', 'ln_livenotifications' ); ?></a>
							</span>
							<span class="delete">
								| <a class="delete" href="<?php echo wp_nonce_url( "?page=lnpm_outbox&action=delete&id=$msg->id", 'lnpm-delete_outbox_msg_' . $msg->id ); ?>"><?php _e( 'Delete', 'ln_livenotifications' ); ?></a>
							</span>
						</div>
					</td>
					<td><?php echo $msg->date; ?></td>
				</tr>
						<?php

				}
					?>
				</tbody>
				<tfoot>
				<tr>
					<th class="manage-column check-column"></th>
					<th class="manage-column"><?php _e( 'Recipient', 'ln_livenotifications' ); ?></th>
					<th class="manage-column"><?php _e( 'Subject', 'ln_livenotifications' ); ?></th>
					<th class="manage-column"><?php _e( 'Date', 'ln_livenotifications' ); ?></th>
				</tr>
				</tfoot>
			</table>
		</form>
					<?php

	}
	?>
</div>
	<?php

}
add_shortcode( 'lnpm_outbox', 'lnpm_outbox' );

function edit_profile( ) {
	
	global $current_user, $wp_roles;
get_currentuserinfo();

/* Load the registration file. */
require_once( ABSPATH . WPINC . '/registration.php' );
$error = array();    
if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'update-user' ) {



	 /* Update user information. */
    if ( !empty( $_POST['url'] ) )
		{
	    wp_update_user(array ( 'ID' => $current_user->ID, 'user_url' => esc_url( $_POST['url'] ) ));
		}
    if ( !empty( $_POST['email'] ) ){
        if (!is_email(esc_attr( $_POST['email'] )))
            $error[] = __('The Email you entered is not valid.  please try again.', 'profile');
        elseif(email_exists(esc_attr( $_POST['email'] )) != $current_user->id )
            $error[] = __('This email is already used by another user.  try a different one.', 'profile');
        else{
            wp_update_user( array ('ID' => $current_user->ID, 'user_email' => esc_attr( $_POST['email'] )));
        }
    }

    if ( !empty( $_POST['display_name'] ))
	   wp_update_user( array ( 'ID' => $current_user->ID, 'display_name' => esc_attr($_POST['display_name'])) ) ;
	if ( !empty( $_POST['first-name'] ) )
       update_user_meta( $current_user->ID, 'first_name', esc_attr( $_POST['first-name'] ) );
    if ( !empty( $_POST['last-name'] ) )
	if(!empty($_POST['nickname']))
	{
		update_user_meta( $current_user->ID, 'user_nicename', esc_attr( $_POST['nickname'] ) );
	}
        update_user_meta($current_user->ID, 'last_name', esc_attr( $_POST['last-name'] ) );
    if ( !empty( $_POST['description'] ) )
        update_user_meta( $current_user->ID, 'description', esc_attr( $_POST['description'] ) );

    /* Redirect so the page will show updated info.*/
  /*I am not Author of this Code- i dont know why but it worked for me after changing below line to if ( count($error) == 0 ){ */
    if ( count($error) == 0 ) {
        //action hook for plugins and extra fields saving
        do_action('edit_user_profile_update', $current_user->ID);
    }
}
if ( !is_user_logged_in() ) : 
?>
                    <p class="warning">
                        <?php _e('You must be logged in to edit your profile.', 'profile'); ?>
                    </p><!-- .warning -->
            <?php else : ?>
                <?php if ( count($error) > 0 ) echo '<p class="error">' . implode("<br />", $error) . '</p>'; ?>
                <div class="form">
<table>
<form method="POST" id="adduser" action="<?php the_permalink(); ?>">
       			<tr>            
                     <td>   <label for="first-name"><?php _e('First Name', 'profile'); ?></label></td>
                     <td>   <input class="text-input" name="first-name" type="text" id="first-name" value="<?php the_author_meta( 'first_name', $current_user->ID ); ?>" /></td>
                 </tr>  
                   <tr></tr>
                 <tr><td>       <label for="last-name"><?php _e('Last Name', 'profile'); ?></label></td>
                   <td>     <input class="text-input" name="last-name" type="text" id="last-name" value="" /></td>
				</tr>
                    <tr></tr>
                  <tr> 
                      <td>  <label for="email"><?php _e('E-mail *', 'profile'); ?></label></td>
                       <td> <input class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" readonly="readonly" disabled="disabled"/></td>
                   </tr>
                    <tr></tr>
                   <tr>
                    <td>    <label for="nickname"><?php _e('Nickname *', 'profile'); ?></label></td>
                    <td>    <input class="text-input" name="nickname" type="text" id="nickname" value="<?php the_author_meta( 'nickname', $current_user->ID ); ?>" /></td>
               </tr>
                  <tr></tr>   
                 <tr><td>       <label for="display_name"><?php _e('Display Name *', 'profile'); ?></label></td>
                 <td>       <select name="display_name" id="display_name">
                 <?php
			$profileuser = wp_get_current_user();
			$public_display = array();
			$public_display['display_nickname']  = $profileuser->nickname;
		
			if ( !empty($profileuser->first_name) )
				$public_display['display_firstname'] =$profileuser->first_name;

			if ( !empty($profileuser->last_name) )
				$public_display['display_lastname'] = $profileuser->last_name;

			
			if ( !in_array( $profileuser->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
				$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;

			$public_display = array_map( 'trim', $public_display );
			$public_display = array_unique( $public_display );

			foreach ( $public_display as $id => $item ) {
		?>
			<option<?php selected( $profileuser->display_name, $item ); ?> value="<?php echo $item; ?>"><?php echo $item; ?></option>
			<?php
			}
		?>
        </select>
        		</td></tr>   
                 <tr></tr>         
                   <tr>
                   <td>     <label for="url"><?php _e('Website', 'profile'); ?></label></td>
                   <td>     <input class="text-input" name="url" type="text" id="url" value="<?php the_author_meta( 'user_url', $current_user->ID ); ?>" /></td>
                  </tr>
                    <tr></tr>
                    <tr><td>    <label for="description"><?php _e('Biographical Information', 'profile') ?></label></td>
                       <td> <textarea name="description" id="description" rows="3" cols="50"><?php the_author_meta( 'description', $current_user->ID ); ?></textarea></td>
						</tr>
                        
                          <?php 
                        //action hook for plugin and extra fields
                        do_action('edit_user_profile',$current_user); 
                    ?>
 					<tr></tr>
                  <tr><td></td><td>    <?php echo $referer; ?>   <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update', 'profile'); ?>" />
                   <?php wp_nonce_field( 'update-user' ) ?> <input name="action" type="hidden" id="action" value="update-user" /></td>
                 </tr>
                </form>
     </table>          
               
                </div><!-- #adduser -->
            <?php endif; ?>
<?php
 } 
add_shortcode( 'edit_profile', 'edit_profile' );

//awrad page list page

function award_list($post_ID){
	
			
			
			
			//display award page
			global $wpdb;
		 $currentuserid=get_current_user_id();
		
		echo '<div style="float:left;width:280px;"><strong>My Achievements</strong><div style="clear:both;"></div>';
		

	$getimage=mysql_query("SELECT * FROM ".$wpdb->prefix."countpoints WHERE cp_uid='".$currentuserid."' ORDER BY cp_id");
	$output .= '<div style="clear:both;"></div>';
	while($row_image=mysql_fetch_array($getimage)){
		$task_image=$row_image['cp_tasklist'];
		$get_reward_image=mysql_query("SELECT * FROM ".$wpdb->prefix."rewardsystem WHERE reorder='".$task_image."' ORDER BY reorder ASC limit 0,5");
		
			while($row_image_reward=mysql_fetch_array($get_reward_image)){
				$image_award=$row_image_reward['rew_image'];
				$image_title=$row_image_reward['type'];
				$image_reorder=$row_image_reward['numlist'];
		$outputach .= '<div style="float:left;margin-right:10px;><p style="background:white;"><img src="'.$image_award.'" title="'.$image_reorder.' '.ucfirst($image_title).' Award" height="40" width="40"></p></div>';
			}
		}
			
			
		echo $outputach.'<div style="clear:both;"></div></div>';
		
		
		echo '<div style="float:left;width:280px;"><strong>My Stats</strong>';
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
			$output_points1='<div><p><font color="green" size="4">'.round($dd1,3).'</font> post/days</p></div>';
			if(!empty($points_user_dif))
			{
			$output_points1 .='<div><p>Ranked <font color="#333333" size="+2">#'.$points_user_dif. '</font > on site<br>'.$position.'</p></div>';
			}
			
		echo $output_points1.'</div>';
		
		echo '<div style="clear:both;"></div>';
		
		echo '<div><strong>Unlockables</strong><div style="clear:both;">';
		$selectorder=mysql_query("select cp_tasklist from ".$wpdb->prefix."countpoints where cp_uid='".$currentuserid."'");
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
			$selectdata1=mysql_query("select * from ".$wpdb->prefix."rewardsystem where ".$order." ORDER BY reid  ASC LIMIT 0,3");
			
			while($rank=mysql_fetch_array($selectdata1))
			{
			$image_award=$rank['rew_image'];
			$image_title=$rank['type'];
			$image_reorder=$rank['numlist'];
		
			$outputach1 .= '<div style="float:left;margin-right:10px;"><p style="background:white;"><img src="'.$image_award.'" title="'.$image_reorder.' '.ucfirst($image_title).' Award" height="40" width="40"></p></div>';
			}
		echo $outputach1.'<div style="clear:both;"></div></div>';

		 //count comments for all time
		
		//Display the name 
			
		
		//for week leader
		$select_user_stat1=mysql_query("select rank,user_id,all_count from ".$wpdb->prefix."all_rankcount where rank BETWEEN 1 AND 3 order by rank ASC" );
		$output_points .='<font style="font-weight:bold;">All time Leadership</font>';
		while($row_user_status=mysql_fetch_assoc($select_user_stat1)){
			$points_user_diff=$row_user_status['user_id'];
			$points_user_rank=$row_user_status['rank'];
			$points_user_count=$row_user_status['all_count'];			

		
			$select_username=mysql_query("select user_nicename from ".$wpdb->prefix."users where ID='".$points_user_diff."'" );
		while($row_username=mysql_fetch_assoc($select_username)){
			$points_username=$row_username['user_nicename'];
$output_points .='<div><p> #'.$points_user_rank.'  <font >' .$points_username.'</font>  '.$points_user_count.' Points</p></div>';

		//Display the name 
		
		}
		}
		
		//for week all time
			$select_user_stat2=mysql_query("select user_id,rank,week_count from ".$wpdb->prefix."week_rankcount where rank BETWEEN 1 AND 3 order by rank ASC" );
			$output_points .='<font style="font-weight:bold;">Week Leadership</font>';
		while($row_user_status2=mysql_fetch_assoc($select_user_stat2)){
			$points_user_diff11=$row_user_status2['user_id'];
			$points_user_rank1=$row_user_status2['rank'];
						
			$points_user_count1=$row_user_status2['week_count'];

			$select_username=mysql_query("select user_nicename from ".$wpdb->prefix."users where ID='".$points_user_diff11."'" );
		while($row_username=mysql_fetch_assoc($select_username)){
			$points_username1=$row_username['user_nicename'];
			$output_points .='<div><p> #'.$points_user_rank1.'  <font >' .$points_username1.'</font>  '.$points_user_count1.' Points</p></div>';
//Display the name 		
}
		}

			echo $output_points;	
				
}

add_shortcode( 'award_list', 'award_list' );


