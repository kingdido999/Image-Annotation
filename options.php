<?php
/*
 *	Plugin Options Page
 * 	Options: Theme, Annotable Image Selector
 */
add_action( 'admin_menu', 'annotorious_menu' );

function annotorious_menu() {
	add_options_page( 'Image Annotation Options', 'Image Annotation', 'manage_options', 'image-annotation', 'annotorious_options' );
	add_action( 'admin_init', 'annotorious_register_settings');
}

function annotorious_register_settings() {
	register_setting( 'annotorious-settings-group', 'theme' );
	register_setting( 'annotorious-settings-group', 'image-selector' );
}

// Options page layout
function annotorious_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
?>

<div class="wrap">
<h2>Image Annotation Options</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'annotorious-settings-group' ); ?>
    <?php do_settings_sections( 'annotorious-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Theme</th>
        <td>
        	<input type="radio" name="theme" value="White" <?php echo annotorious_check_theme('White'); ?> />White
        	<input type="radio" name="theme" value="Dark" <?php echo annotorious_check_theme('Dark'); ?> />Dark
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Annotatble Image Selector</th>
        <td>
        	<?php annotorious_get_image_selector(); ?>
        	<input type="text" name="image-selector" value="<?php echo annotorious_get_image_selector(); ?>" size="40" />
        </td>
        <td>
        	<p>This selector corresponds to the images that are annotatble on your website, the default should be working fine for most WordPress themes. Do <strong>NOT</strong> modify this unless you understand what you're doing.</p>
        </td>
        </tr>        
    </table>
    
    <?php submit_button(); ?>

</form>
</div>

<?php
}

// Get default option value
function annotorious_get_default_option($option) {
	$default_options = array(
		'theme' => 'White',
		'image-selector' => '.entry-content img',
	);

	if (array_key_exists($option, $default_options)) {
		return $default_options[$option];
	}
}

// Get theme css file path
function annotorious_get_theme_path() {
	//$default = $default_options['theme'];
	$default = annotorious_get_default_option('theme');
	$theme = get_option('theme', $default);
	$path = plugin_dir_url(__FILE__);
	if ($theme == 'White') {
		$path .= 'css/annotorious.css';
	}
	if ($theme == 'Dark') {
		$path .= 'css/theme-dark/annotorious-dark.css';
	}

	return $path;
}

// Return theme being selected or not
function annotorious_check_theme($theme) {
	$default = annotorious_get_default_option('theme');
	$White_status = 'unchecked';
	$Dark_status = 'unchecked';

	if (get_option('theme', $default) == 'White') {
		$White_status = 'checked';
	}
	else if (get_option('theme', $default) == 'Dark') {
		$Dark_status = 'checked';
	}

	return ($theme == 'White') ? $White_status : $Dark_status;
}

// Get image selector
function annotorious_get_image_selector() {
	$default = annotorious_get_default_option('image-selector');
	return esc_attr( get_option('image-selector', $default));
}

// Reset options
function annotorious_reset_options() {
	foreach ( $default_options as $option => $default_value ) {
		update_option( $option, $default_value );
	}
}
