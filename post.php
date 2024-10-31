<?php
/*
  Plugin Name: Po.st
  Plugin URI: http://www.po.st/
  Description: Po.st makes your site social by letting your users share posts and pages with others. Po.st supports several social networks, email and languages. Check the README file for configuration options and our support site at <a href="http://support.po.st/">http://support.po.st/</a> for other inquiries.
  Author: Po.st
  Version: 2.0.1
  Author URI: http://www.po.st/
 */

load_plugin_textdomain( 'po.st' );

add_filter( 'the_content', 'post_add_widget_content' );
add_action( 'wp_head', 'post_add_js_init' );
add_action( 'admin_menu', 'post_menu_items' );
add_action( 'wp_ajax_post_ajax_preview', 'post_ajax_preview' );
add_action( 'init', 'post_options_form_save', 9999 );
add_action( 'admin_notices', 'post_warning' );
add_action( 'save_post', 'post_meta_box_save' );

$positionType = array(
	'above' => __( 'Above content', 'po.st' ),
	'below' => __( 'Below content', 'po.st' ),
);

$showOn = array(
	'list'  => __( 'Lists of posts', 'po.st' ),
	'posts' => __( 'Single posts', 'po.st' ),
	'pages' => __( 'Pages', 'po.st' ),
);

$displayTypes = array(
	'SHARING' => __( 'Standard sharing buttons', 'po.st' ),
	'NATIVE'  => __( 'Native sharing buttons', 'po.st' )
);


function post_admin_scripts(){
    wp_register_script( 'post-constructor-script', plugins_url('/post.js', __FILE__) );
    wp_enqueue_script('post-constructor-script');
}

function post_admin_styles(){
    wp_enqueue_style('post-plugin.css', plugins_url('/post-plugin.css', __FILE__));
}

function post_menu_items() {
	$page = add_options_page( __( 'Po.st Options', 'po.st' ), __( 'Po.st', 'po.st' ), 'manage_options', basename( __FILE__ ), 'post_options_form' );
	// add_action('admin_print_scripts-' . $page, 'post_admin_scripts');

	add_action( 'admin_print_styles-' . $page, 'post_admin_styles' );
	add_action( 'admin_print_styles-' . $page, 'post_admin_scripts' );
}

function post_warning() {
	$p_key = trim( get_option( 'post_p_key', '' ) );
	if ( empty( $p_key ) ) {
		echo "<div class='error fade' id='post-pubkeyerror'><p>" . sprintf( __( 'You must <a href="%1$s">enter your Po.st publisher key</a> for this plugin to work properly.' ), get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page=post.php' ) . "</p></div>";
	}
}

function post_options_form_save() {
	$plugin_location = WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );

	if ( $_POST && isset( $_POST['post_action'] ) ) {
		$options = get_data_from_post();
		@session_start();

		if ( check_admin_referer( 'update-po.st-settings' ) ) {
			if ( $_POST['post_action'] == 'save' ) {
				update_option( 'post_p_key', trim( $options['post_p_key'] ) );
				update_option( 'post_w_id', trim( $options['post_w_id'] ) );
				update_option( 'post_display_pages', $options['post_display_pages'] );
				update_option( 'post_display_position', $options['post_display_position'] );
				update_option( 'post_display_types', $options['post_display_types'] );
				update_option( 'post_display_type', $options['post_display_type'] );

				wp_redirect( get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page=post.php&updated=true' );
				exit;
			}
		}
	}
}

function post_options_form() {
	@session_start();

	$plugin_location = WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );

	$p_key            = get_option( 'post_p_key', '' );
	$display_pages    = get_option( 'post_display_pages', 'list,posts' );
	$display_position = get_option( 'post_display_position', 'above' );
	$display_types    = get_option( 'post_display_types', '' );
	$display_type     = get_option( 'post_display_type', '' );

	if ( ! empty( $display_pages ) ) {
		$display_pages = explode( ',', $display_pages );
	} else {
		$display_pages = array();
	}
	if ( ! empty( $display_position ) ) {
		$display_position = explode( ',', $display_position );
	} else {
		$display_position = array();
	}
	if ( ! empty( $display_types ) ) {
		$display_types = explode( ',', $display_types );
	} else {
		$display_types = array();
	}

	rsort($display_types);

	$_SESSION['_token'] = $_token = base64_encode( openssl_random_pseudo_bytes( 32 ) );

	include dirname( __FILE__ ) . '/tpl/form.tpl.php';
}

function get_data_from_post() {
	$options               = array();
	$options['post_p_key'] = isset( $_POST['p_key'] ) ? $_POST['p_key'] : '';

	if ( strlen( $options['post_p_key'] ) > 250 ) {
		$options['post_p_key'] = '';
	}

	$options['post_display_pages'] = isset( $_POST['show_on'] ) ? $_POST['show_on'] : array();
	$options['post_display_pages'] = implode( ',', array_keys( $options['post_display_pages'] ) );

	$options['post_display_position'] = isset( $_POST['display_position'] ) ? $_POST['display_position'] : array();
	$options['post_display_position'] = implode( ',', array_keys( $options['post_display_position'] ) );

	$options['post_display_type']  = isset( $_POST['display_type'] ) ? $_POST['display_type'] : '';
	$options['post_display_types'] = array();

	if ( ! empty( $options['post_p_key'] ) ) {
		$response = wp_remote_get( 'https://po.st/v1/profiles/' . $options['post_p_key'] . '/widgets' );
		$response = json_decode( $response['body'] );

		if ( ! $response->error ) {
			foreach ( $response->widgets as $widget ) {
				if ( ! $widget->d ) {
					$options['post_display_types'][ $widget->t ] = $widget->id;
				}
			}
		} elseif ( $response->error->status == 401 ) {
			$options['post_p_key'] = '';
		}
	}

	if ( ! empty( $options['post_p_key'] ) ) {
		if ( empty( $options['post_display_type'] ) and ! empty( $options['post_display_types'] ) ) {
			$options['post_display_type'] = array_keys( $options['post_display_types'] );
			$options['post_display_type'] = $options['post_display_type'][0];
		}

		if ( ! in_array( $options['post_display_type'], array_keys( $options['post_display_types'] ) ) ) {
			$options['post_display_type'] = '';
			$options['post_w_id']         = '';
		} else {
			$options['post_w_id'] = $options['post_display_types'][ $options['post_display_type'] ];
		}

		$options['post_display_types'] = implode( ',', array_keys( $options['post_display_types'] ) );
	}

	return $options;
}

function post_add_js_init() {
	$p_key   = get_option( 'post_p_key', '' );
	$w_id    = get_option( 'post_w_id', '' );
	$options = NULL;
	if ( isset( $_GET['preview'] ) && $_GET['preview'] ) {
		$options = get_transient( 'post_settings' );
		if ( $options ) {
			$p_key = $options['post_p_key'];
			$w_id  = $options['post_w_id'];
		}
	}
	if ( ! empty( $p_key ) && ! empty( $w_id ) ) {
		print "<script type=\"text/javascript\">
		    (function () {
		        var s = document.createElement('script');
		        s.type = 'text/javascript';
		        s.async = true;
		        s.src = ('https:' == document.location.protocol ? 'https://s' : 'http://i')
		          + '.po.st/static/v4/post-widget.js#publisherKey={$p_key}';
		        var x = document.getElementsByTagName('script')[0];
		        x.parentNode.insertBefore(s, x);
		     })();
		</script>";
	}
}

function post_add_widget_content( $content ) {
	$options = NULL;
	if ( isset( $_GET['preview'] ) && $_GET['preview'] ) {
		$options = get_transient( 'post_settings' );
	}

	if ( empty( $options ) ) {
		$p_key            = get_option( 'post_p_key', '' );
		$w_id             = get_option( 'post_w_id', '' );
		$display_pages    = get_option( 'post_display_pages', 'pages,posts' );
		$display_position = get_option( 'post_display_position', 'above' );
	} else {
		$p_key            = $options['post_p_key'];
		$w_id             = $options['post_w_id'];
		$display_pages    = $options['post_display_pages'];
		$display_position = $options['post_display_position'];
	}

	if ( ! empty( $p_key ) && ! empty( $w_id ) ) {

		$display_pages    = explode( ',', $display_pages );
		$display_position = explode( ',', $display_position );

		$add_widget = FALSE;

		foreach ( $display_pages as $page ) {
			switch ( $page ) {
				case 'list':
					if ( ! is_singular() ) {
						$add_widget = TRUE;
					}
					break;
				case 'pages':
					if ( is_page() && is_singular() ) {
						$add_widget = TRUE;
					}
					break;
				case 'posts':
					if ( is_singular() && get_post_type() == 'post' ) {
						$add_widget = TRUE;
					}
					break;
			}
		}

		if ( $add_widget ) {
			if ( count( $display_position ) > 1 ) {
				$content = post_make_widget( $w_id ) . $content . post_make_widget( $w_id );
			} else if ( $display_position[0] == 'above' ) {
				$content = post_make_widget( $w_id ) . $content;
			} else if ( $display_position[0] == 'below' ) {
				$content .= post_make_widget( $w_id );
			}
		}
	}

	return $content;
}

function post_make_widget( $w_id ) {
	return '<div class="pw-server-widget" data-id="' . $w_id . '"></div>';
}

function post_ajax_preview() {
	if ( $_POST && isset( $_POST['post_action'] ) && $_POST['post_action'] == 'preview' ) {
		$options = get_data_from_post();

		if ( FALSE !== get_transient( 'post_settings' ) ) {
			delete_transient( 'post_settings' );
		}

		$eh = set_transient( 'post_settings', $options, 120 );

		die();
	}
}

if ( ! function_exists( 'ak_can_update_options' ) ) {
	function ak_can_update_options() {
		if ( function_exists( 'current_user_can' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return TRUE;
			}
		} else {
			global $user_level;
			get_currentuserinfo();
			if ( $user_level >= 8 ) {
				return TRUE;
			}
		}

		return FALSE;
	}
}
