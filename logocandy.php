<?php
/*
Plugin Name: Logo Candy
Plugin URI: http://wordpress.org/extend/plugins/logo-candy/
Description: A small plugin that allows one to change the logo, link and link description on the log-in page of WordPress.
Version: 1.0.1
Author: Sp0k0
Author URI: http://anotherviewonit.blogspot.com
*/

/*  Copyright 2008  sp0k0

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

if (!class_exists('LoginCandyPlugin')) {
	class LoginCandyPlugin {
		function __construct() {
			add_action('admin_menu', array ($this, 'LCAddOptions'));
			add_action('login_head', array($this, 'LCReplaceLogo'));
			add_action('admin_notices', array($this, 'LCAdminNotice'));
			add_filter('login_headerurl',array($this,'LCSiteURL'));
			add_filter('login_headertitle', array($this, 'LCSiteDescription'));
		}

		//Add Options page under the Settings menu
		function LCAddOptions() {
			add_theme_page('LogoCandy Options', 'LogoCandy', 10, 'logocandy', array ($this, 'LogoCandyOptions'));
		}

		//Return the current site url
		function LCSiteURL($url){
			return get_bloginfo('siteurl');
		}
		
		//Return the description of the current blog
		function LCSiteDescription($message){
			return get_bloginfo('description');
		}

		//Warn the user about the WP version. No testing has been carried out on versions lower then 2.7
		function LCWarn(){
			global $wp_version;
			echo "<div id='version-warning' class='updated fade-ff0000'><p><strong>".__('LogoCandy is tested with WordPress v2.7 and up.  You are currently using WordPress ', 'regplus').$wp_version."</strong></p></div>";
		}
		
		function LogoCandyOptions() {
			// variables for the field and option names 
			$hidden_field_name = 'logocandy';

			// Read in existing option value from database
			$logo_candy = get_option('logo_candy');

			if (isset($_POST[$hidden_field_name]) and $_POST[$hidden_field_name] == 'logocandy') {
				$upload_dir = get_option('upload_path');
				$upload_file = trailingslashit($upload_dir) . basename($_FILES['logo']['name']);
				if (!is_dir($upload_dir))
					wp_upload_dir();
				if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_file)) {
					chmod($upload_file, 0666);
					$logo = $_FILES['logo']['name'];
					$logocandy = trailingslashit( get_option('siteurl') ) . 'wp-content/uploads/' . $logo;
				}
				update_option('logo_candy', array('logo_location' => $logocandy));
			}

			// Now display the admin options editing screen
			echo '<div class="wrap">';

			// admin option header
			echo "<h2>" . __('Logo Candy', 'logo_candy') . "</h2>";
			echo '<div id="message"><p><strong>' . __('Important: ', 'logo_candy') . '</strong>' . __('Please upload an image 326px wide for optimal results.', 'logo_candy') . '</p></div>';

			// admin options form
?>

<form name="LCLogoUpload" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="logocandy">
<input type="hidden" name="notice" value="<?php echo __('Logo uploaded succeffully. Log out and try to log in again to see it.', 'logocandy');?>">

<table class="form-table">
<tr valign="top">
<th scope="row"><label for="Upload an image for the logo">Upload an image</label></th>
<td><input type="file" name="logo" id="logo"/></td>
</tr>
</table>

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Upload Logo', 'logo_candy' ) ?>" />
</p>

</form>
</div>
<?php
		}

		// Replace the logo on the admin log-in page. To do that we will redeclare the corresponding CSS property.
		function LCReplaceLogo(){
			$logocandy = get_option( 'logo_candy' );
			if( $logocandy['logo_location']){ 
				$logo = str_replace( trailingslashit( get_option('siteurl') ), ABSPATH, $logocandy['logo_location'] );
				list($width, $height, $type, $attr) = getimagesize($logo);
			?>
			<style type="text/css">
				#login h1 a {
					background: url(<?php echo $logocandy['logo_location'];?>) no-repeat top center;
					width: <?php echo $width; ?>px;
					height: <?php echo $height; ?>px;
					padding-bottom: 15px;
				}
			</style>
		<?php
			}
		}
		// Send a notice to the user if an old version of WP is found
		// TODO refactor this - it could be done in a better way - read docs!!!
		function LCAdminNotice(){
			global $wp_version;

			if( $wp_version < '2.7' )
				echo "<div id='version-warning' class='error'><p>". __('LogoCandy is only tested with WordPress 2.7 and up. ', 'logo_candy') . "<strong>" . __('You have been warned!', 'logo_candy') . "</strong></p></div>";

			if( isset($_POST['notice']) and isset($_POST['logocandy']) and $_POST['logocandy'] == 'logocandy')
				echo '<div id="message" class="updated fade"><p>' . $_POST['notice'] . '.</p></div>';			
		} 
	}
}
// Load the plug-in
if (class_exists('LoginCandyPlugin')) {
	$logocandy = new LoginCandyPlugin();
}
?>