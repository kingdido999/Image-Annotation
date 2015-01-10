<?php
/**
 * Plugin Name: Image Annotation
 * Plugin URI: https://github.com/kingdido999/Image-Annotation
 * Description: An image annotation tool based on Annotorious that allows you to crop an area of the image and add notes to it.
 * Version: 0.1.4
 * Author: Desmond Ding
 * Author URI: http://desmonding.com
 * License: GPL2
 */

/*
Copyright 2015  Desmond Ding  (email : kingdido999@gmail.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined('ABSPATH') or die("No script kiddies please!");

include_once(plugin_dir_path(__FILE__) . 'options.php');

// Add scripts
function annotorious_scripts() {
	wp_enqueue_style( 'annotorious-css', annotorious_get_theme_path() );
	wp_enqueue_script( 'annotorious-js', plugin_dir_url(__FILE__) . 'js/annotorious.min.js');
	wp_enqueue_script( 'script-js', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery', 'annotorious-js'));
	
	$translation_array = array(
		'imageSelector' => annotorious_get_image_selector(),
		'editable' => annotorious_get_editable()
		);

	// make this array usable in script.js
	wp_localize_script( 'script-js', 'annotorious_translation_array', $translation_array );
}
add_action( 'wp_enqueue_scripts', 'annotorious_scripts' );

// Create table
global $annotorious_db_version;
$annotorious_db_version = '1.0';

function annotorious_install() {
	global $wpdb;
	global $annotorious_db_version;

	$table_name = $wpdb->prefix . 'annotorious';
	
	/*
	 * We'll set the default character set and collation for this table.
	 * If we don't do this, some characters could end up being converted 
	 * to just ?'s when saved in our table.
	 */
	$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
	  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
	  $charset_collate .= " COLLATE {$wpdb->collate}";
	}

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		text text NOT NULL,
		url varchar(200) DEFAULT '' NOT NULL,
		height double NOT NULL,
		width double NOT NULL,
		coordinateX double NOT NULL,
		coordinateY double NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'annotorious_db_version', $annotorious_db_version );
}
register_activation_hook( __FILE__, 'annotorious_install' );


// Load existing annotations
add_action( 'wp_ajax_load', 'annotorious_load_annotation' );
add_action( 'wp_ajax_nopriv_load', 'annotorious_load_annotation' );
function annotorious_load_annotation() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'annotorious';
	$get = json_encode($_GET);
	$get = json_decode($get, true);
	$url = (string)$get['url'];

	$query = "SELECT text,coordinateX,coordinateY,height,width FROM $table_name WHERE url='" . $url . "'";
	$annotations = $wpdb->get_results($query);
	print_r(json_encode($annotations));

    die();
}

// Save new annotation to the database
add_action( 'wp_ajax_create', 'annotorious_create_annotation' );
add_action( 'wp_ajax_nopriv_create', 'annotorious_create_annotation' );
function annotorious_create_annotation() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'annotorious';
	$post = json_encode($_POST);
	$post = json_decode($post, true);

	$object = $post['object'];
	$geometry = $object['shapes'][0]['geometry'];
	$url = $object['src'];
	$note = $object['text'];

	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ),
			'text' => $note, 
			'url'  => $url,
			'height' => $geometry['height'],
			'width'  => $geometry['width'],
			'coordinateX' => $geometry['x'],
			'coordinateY' => $geometry['y'],
		) 
	);

    die();
}

// Update the annotation
add_action( 'wp_ajax_update', 'annotorious_update_annotation' );
add_action( 'wp_ajax_nopriv_update', 'annotorious_update_annotation' );
function annotorious_update_annotation() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'annotorious';
	$post = json_encode($_POST);
	$post = json_decode($post, true);

	$object = $post['object'];
	$geometry = $object['shapes'][0]['geometry'];
	$url = $object['src'];
	$note = $object['text'];

	$wpdb->update( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ),
			'text' => $note, 
		),
		array(
			'url'  => $url,
			'height' => $geometry['height'],
			'width'  => $geometry['width'],
			'coordinateX' => $geometry['x'],
			'coordinateY' => $geometry['y'],
		)
	);

    die();
}


// Delete the annotation
add_action( 'wp_ajax_delete', 'annotorious_delete_annotation' );
add_action( 'wp_ajax_nopriv_delete', 'annotorious_delete_annotation' );
function annotorious_delete_annotation() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'annotorious';
	$post = json_encode($_POST);
	$post = json_decode($post, true);

	$object = $post['object'];
	$geometry = $object['shapes'][0]['geometry'];
	$url = $object['src'];
	$note = $object['text'];

	$wpdb->delete( 
		$table_name, 
		array(
			'url'  => $url,
			'height' => $geometry['height'],
			'width'  => $geometry['width'],
			'coordinateX' => $geometry['x'],
			'coordinateY' => $geometry['y'],
		)
	);

    die();
}

add_action( 'wp_head','pluginname_ajaxurl' );
function pluginname_ajaxurl() {
?>
<script type="text/javascript">
	var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}
?>