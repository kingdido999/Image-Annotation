<?php
/*
 *	Plugin Options Page
 * 	Options: Theme, Annotable Image Selector
 */
add_action( 'admin_menu', 'annotorious_menu' );

// Add option page and notes page
function annotorious_menu() {
	add_options_page( 'Image Annotation Options', 'Image Annotation', 'manage_options', 'image-annotation', 'annotorious_options' );
	add_menu_page( 'Image Annotation Notes', 'Image Notes', 'manage_options', 'image-notes', 'annotorious_notes', 'dashicons-format-image', 26);
	add_action( 'admin_init', 'annotorious_register_settings');
}

// Register options
function annotorious_register_settings() {
	register_setting( 'annotorious-settings-group', 'theme' );
	register_setting( 'annotorious-settings-group', 'image-selector' );
	wp_enqueue_style( 'annotorious-options-css', plugin_dir_url(__FILE__) . 'css/options.css' );
	wp_enqueue_script( 'options-js', plugin_dir_url(__FILE__) . 'js/options.js', array('jquery'));
}


/*
 * Options page
 */
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

/*
 * Image notes menu page
 */
function annotorious_notes() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
?>
	<div class="wrap">
		<h2>Image Annotation Notes</h2>
		<form method="post" action="">
			<?php annotorious_show_notes(); ?>
			<?php annotorious_delete_note(); ?>
		</form>
	</div>
<?php
}

// Get image notes
function annotorious_show_notes() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'annotorious';
	$results = $wpdb->get_results( "SELECT id, time, text, url FROM $table_name ORDER BY time DESC" );

	echo '<table class="image-notes-table">';
	echo '<th>ID</th>';
	echo '<th>Image</th>';
	echo '<th>Notes</th>';
	echo '<th>AddTime</th>';
	echo '<th>More</th>';

	foreach ($results as $row) {
		$id = $row->id;
		$time = $row->time;
		$note = $row->text;
		$url = $row->url;
		$delete = '<input type="button" name="delete" value="Delete" />';

		echo '<tr>';
		echo '<td class="note-id">' . $id . '</td>';
		echo '<td><img src="' . $url . '"></td>';
		echo '<td>' . $note . '</td>';
		echo '<td>' . $time . '</td>';
		echo '<td>' . $delete . '</td>';
		echo '</tr>';
	}

	echo '</table>';
}

// Delete an image note
add_action( 'wp_ajax_delete_note', 'annotorious_delete_note' );
add_action( 'wp_ajax_nopriv_delete_note', 'annotorious_delete_note' );
function annotorious_delete_note() {
	$post = json_encode($_POST);
	$post = json_decode($post, true);
	$id = $post['object'];

	global $wpdb;
	$table_name = $wpdb->prefix . 'annotorious';
	$where = array( 'id' => $id);

	$wpdb->delete($table_name, $where);
}
