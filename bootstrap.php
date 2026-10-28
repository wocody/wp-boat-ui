<?php
/**
 * Registers this copy of Wocody Boat UI.
 *
 * Several plugins can bundle their own copy through Composer. Every copy
 * registers its version here and the newest one is used for every plugin, so
 * a single stylesheet, script and class set is loaded per request.
 *
 * Keep Wocody_Boat_UI_Loader backwards compatible: the first copy loaded
 * defines it for all the others.
 *
 * @package Wocody\BoatUI
 */

if ( ! class_exists( 'Wocody_Boat_UI_Loader', false ) ) {
	/** Version election and autoloading for bundled copies. */
	final class Wocody_Boat_UI_Loader {

		/**
		 * Registered package roots keyed by version.
		 *
		 * @var array<string, string>
		 */
		private static $versions = array();

		/**
		 * Elected version, fixed once the first class is loaded.
		 *
		 * @var string|null
		 */
		private static $elected = null;

		/**
		 * Register a bundled copy.
		 *
		 * @param string $version Semantic version of the copy.
		 * @param string $root    Absolute package directory.
		 * @return void
		 */
		public static function register( $version, $root ) {
			if ( empty( self::$versions ) ) {
				spl_autoload_register( array( __CLASS__, 'autoload' ) );
			}
			if ( ! isset( self::$versions[ $version ] ) ) {
				self::$versions[ $version ] = rtrim( $root, '/\\' );
			}
		}

		/**
		 * Version in use.
		 *
		 * @return string
		 */
		public static function version() {
			if ( null === self::$elected ) {
				$versions = array_keys( self::$versions );
				usort( $versions, 'version_compare' );
				self::$elected = (string) end( $versions );
			}
			return self::$elected;
		}

		/**
		 * Directory of the copy in use.
		 *
		 * @return string
		 */
		public static function root() {
			return self::$versions[ self::version() ];
		}

		/**
		 * Every registered version, for diagnostics.
		 *
		 * @return array<string, string>
		 */
		public static function versions() {
			return self::$versions;
		}

		/**
		 * PSR-4 autoloader for the Wocody\BoatUI namespace.
		 *
		 * @param string $class_name Requested class.
		 * @return void
		 */
		public static function autoload( $class_name ) {
			$prefix = 'Wocody\\BoatUI\\';
			if ( 0 !== strpos( $class_name, $prefix ) ) {
				return;
			}
			$relative = substr( $class_name, strlen( $prefix ) );
			if ( ! preg_match( '/^[A-Za-z0-9_\\\\]+$/D', $relative ) ) {
				return;
			}
			$path = self::root() . '/src/' . str_replace( '\\', '/', $relative ) . '.php';
			if ( is_file( $path ) ) {
				require_once $path;
			}
		}
	}
}

Wocody_Boat_UI_Loader::register( '1.0.0', __DIR__ );

if ( defined( 'WOCODY_BOAT_UI_DEMO' ) && WOCODY_BOAT_UI_DEMO && function_exists( 'add_action' ) ) {
	// Identical static callbacks are deduplicated by WordPress when several copies are bundled.
	add_action( 'plugins_loaded', array( 'Wocody\\BoatUI\\Demo', 'register' ) );
}
