<?php


add_action('admin_menu', 'add_settings_page');

function add_settings_page(){
	add_options_page('Facebook Pictures', 'Facebook Pictures', 'manage_options', 'facebook-pictures-settings', 'add_facebook_pictures_menu');
}

function add_facebook_pictures_menu(){
	wp_enqueue_style( 'fb-settings-style', plugin_dir_url( __FILE__ ) . '/../../css/fb-settings.css' );
	wp_enqueue_script( 'fb-chooser', plugin_dir_url( __FILE__ ) . '/../../js/fb-chooser.js', array('jquery') );

	global $fbdata;

	if( $_POST['app_id'] && $_POST['app_secret'] && $_POST['currentSourceId']){
		$app_id = $_POST['app_id'];
		$app_secret = $_POST['app_secret'];
		$current_id = $_POST['currentSourceId'];

		if( $app_id != $fbdata->appId ){
			update_option( 'fb_appId', $app_id);
			$fbdata->set_setting('appId', $app_id);
		}

		if( $app_secret != $fbdata->secret ){
			update_option( 'fb_appSecret', $app_secret);
			$fbdata->set_setting('secret', $app_secret);
		}

		if( $current_id != $fbdata->currentSourceId ){
			update_option( 'fb_currentSourceId', $current_id );
			$fbdata->set_setting('currentSourceId', $current_id);
		}
	}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Facebook Settings</h2>

	<div class="help">
		<b>How does it work?</b>
		<p>The plugin will pull the most liked and commented pictures on your selected account and show them on your sidebar or
		widgetized area. Eligible pictures come from your latest 5 albums (or less, if you don’t have that many). Pictures are
		refreshed every 24 hours.</p>
	</div>
	<div class="fb-app">
		You will need to create a Facebook App for this plugin to work. You can do that going to https://developers.facebook.com/apps/. Once the app is set up, fill the fields below, link your acount and you are ready to go. </div>
</div>

<div class="fb-wrapper">
	<form method="POST" action="">
		<table>
			<tr>
				<td>
					<label for="app_id">App ID</label>
				</td>
				<td>
					<input type="text" name="app_id" value="<?php echo $fbdata->appId ? $fbdata->appId : '' ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="app_secret">App Secret</label>
				</td>
				<td>
					<input type="text" name="app_secret" value="<?php echo $fbdata->secret ? $fbdata->secret : '' ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" id="currentId" name="currentSourceId" value="<?php echo $fbdata->currentSourceId ? $fbdata->currentSourceId : 'NULL' ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<input type="submit" value="Save" class="button-primary save" />
				</td>
			</tr>
		</table>
	</form>


<?php

//var_dump($fbdata->get_pictures(3));
$fbdata->display_users();

?>
</div><!-- end fb-wrapper -->
<?php
}
?>