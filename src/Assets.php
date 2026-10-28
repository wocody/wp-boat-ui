<?php
/**
 * Stylesheet and script registration.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/** Registers the shared stylesheet and script once, whichever plugin asks first. */
final class Assets {

	const HANDLE = 'wocody-boat-ui';

	/**
	 * Whether the current screen uses the interface.
	 *
	 * @var bool
	 */
	private static $active = false;

	/**
	 * Enqueue the stylesheet and script and mark the screen.
	 *
	 * Call it from `admin_enqueue_scripts` for the screens that render the UI.
	 *
	 * @param array<string, mixed> $args `theme` (CSS custom properties for `.bui-root`, e.g. `array( 'primary-500' => '#0a7cbd' )`).
	 * @return void
	 */
	public static function enqueue( array $args = array() ) {
		self::register();
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
		if ( ! empty( $args['theme'] ) && is_array( $args['theme'] ) ) {
			wp_add_inline_style( self::HANDLE, self::theme_css( $args['theme'] ) );
		}
		if ( ! self::$active ) {
			self::$active = true;
			add_filter( 'admin_body_class', array( __CLASS__, 'body_class' ) );
		}
	}

	/**
	 * Register the assets without enqueuing them.
	 *
	 * @return void
	 */
	public static function register() {
		$version = UI::version();
		// A plugin may register its own build of the stylesheet under the same handle first.
		if ( ! wp_style_is( self::HANDLE, 'registered' ) ) {
			wp_register_style( self::HANDLE, self::url( 'dist/boat-ui.css' ), array(), $version );
		}
		if ( ! wp_script_is( self::HANDLE, 'registered' ) ) {
			Strings::load_textdomain();
			wp_register_script( self::HANDLE, self::url( 'dist/boat-ui.js' ), array(), $version, true );
			wp_add_inline_script( self::HANDLE, 'window.BoatUIConfig = ' . Html::json( array( 'strings' => Strings::script() ) ) . ';', 'before' );
		}
	}

	/**
	 * Add the screen marker class.
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public static function body_class( $classes ) {
		return trim( $classes . ' bui-admin-page' );
	}

	/**
	 * Public URL of a package file.
	 *
	 * @param string $relative Path relative to the package root.
	 * @return string
	 */
	public static function url( $relative ) {
		$root = wp_normalize_path( UI::root() );
		$url  = '';
		foreach ( array(
			defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : '',
			defined( 'WPMU_PLUGIN_DIR' ) ? WPMU_PLUGIN_DIR : '',
		) as $directory ) {
			if ( '' !== $directory && 0 === strpos( $root, trailingslashit( wp_normalize_path( $directory ) ) ) ) {
				$url = plugins_url( $relative, $root . '/bootstrap.php' );
				break;
			}
		}
		if ( '' === $url ) {
			$content = wp_normalize_path( WP_CONTENT_DIR );
			$abspath = wp_normalize_path( ABSPATH );
			if ( 0 === strpos( $root, trailingslashit( $content ) ) ) {
				$url = content_url( substr( $root, strlen( $content ) ) . '/' . $relative );
			} elseif ( 0 === strpos( $root, $abspath ) ) {
				$url = site_url( substr( $root, strlen( $abspath ) ) . '/' . $relative );
			}
		}
		/**
		 * Filters the URL of a Wocody Boat UI asset, for installs outside wp-content.
		 *
		 * @param string $url      Asset URL.
		 * @param string $relative Path relative to the package root.
		 * @param string $root     Package root.
		 */
		return (string) apply_filters( 'wocody_boat_ui_asset_url', $url, $relative, $root );
	}

	/**
	 * CSS overriding theme tokens on `.bui-root`.
	 *
	 * @param array<string, string> $theme Token => value, e.g. `primary-500 => #0a7cbd`.
	 * @return string
	 */
	public static function theme_css( array $theme ) {
		$declarations = '';
		foreach ( $theme as $token => $value ) {
			$token = preg_replace( '/[^a-z0-9-]/', '', strtolower( (string) $token ) );
			$value = preg_replace( '/[^#a-zA-Z0-9(),.%\s-]/', '', (string) $value );
			if ( '' === $token || '' === $value ) {
				continue;
			}
			$name          = 0 === strpos( $token, 'color-' ) ? $token : 'color-' . $token;
			$declarations .= '--bui-' . $name . ':' . $value . ';';
		}
		return '' === $declarations ? '' : '.bui-root,.bui-modal{' . $declarations . '}';
	}
}
