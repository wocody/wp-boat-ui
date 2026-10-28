<?php
/**
 * Library strings and translations.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/** Interface strings owned by the library (text domain `wp-boat-ui`). */
final class Strings {

	/**
	 * Whether the text domain was loaded.
	 *
	 * @var bool
	 */
	private static $loaded = false;

	/**
	 * Load bundled translations once.
	 *
	 * @return void
	 */
	public static function load_textdomain() {
		if ( self::$loaded || ! function_exists( 'load_textdomain' ) ) {
			return;
		}
		self::$loaded = true;
		$locale       = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		$file         = UI::root() . '/languages/wp-boat-ui-' . $locale . '.mo';
		if ( is_readable( $file ) ) {
			load_textdomain( 'wp-boat-ui', $file );
		}
	}

	/**
	 * A translated library string.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	public static function get( $key ) {
		self::load_textdomain();
		$all = self::all();
		return isset( $all[ $key ] ) ? $all[ $key ] : $key;
	}

	/**
	 * Strings used by boat-ui.js.
	 *
	 * @return array<string, string>
	 */
	public static function script() {
		$all = self::all();
		return array_intersect_key( $all, array_flip( array( 'search', 'searchPlaceholder', 'noResults', 'close', 'dismiss', 'showMore', 'showLess', 'unsaved' ) ) );
	}

	/**
	 * Every library string.
	 *
	 * @return array<string, string>
	 */
	public static function all() {
		if ( ! function_exists( '__' ) ) {
			return array(
				'search'            => 'Quick search…',
				'searchLabel'       => 'Search',
				'searchPlaceholder' => 'Search…',
				'noResults'         => 'No results found.',
				'openNavigation'    => 'Open navigation',
				'closeNavigation'   => 'Close navigation',
				'mainNavigation'    => 'Main navigation',
				'close'             => 'Close',
				'dismiss'           => 'Dismiss',
				'showMore'          => 'Show more',
				'showLess'          => 'Show less',
				'newTab'            => '(Opens in a new browser tab)',
				'selectFile'        => 'Select a file',
				'dragDrop'          => 'or drag and drop',
				'saveChanges'       => 'Save changes',
				'discardChanges'    => 'Discard changes',
				'unsaved'           => 'You have unsaved changes. Leave this page?',
				'learnMore'         => 'Learn more',
				'notifications'     => 'Notifications',
			);
		}
		return array(
			'search'            => __( 'Quick search…', 'wp-boat-ui' ),
			'searchLabel'       => __( 'Search', 'wp-boat-ui' ),
			'searchPlaceholder' => __( 'Search…', 'wp-boat-ui' ),
			'noResults'         => __( 'No results found.', 'wp-boat-ui' ),
			'openNavigation'    => __( 'Open navigation', 'wp-boat-ui' ),
			'closeNavigation'   => __( 'Close navigation', 'wp-boat-ui' ),
			'mainNavigation'    => __( 'Main navigation', 'wp-boat-ui' ),
			'close'             => __( 'Close', 'wp-boat-ui' ),
			'dismiss'           => __( 'Dismiss', 'wp-boat-ui' ),
			'showMore'          => __( 'Show more', 'wp-boat-ui' ),
			'showLess'          => __( 'Show less', 'wp-boat-ui' ),
			'newTab'            => __( '(Opens in a new browser tab)', 'wp-boat-ui' ),
			'selectFile'        => __( 'Select a file', 'wp-boat-ui' ),
			'dragDrop'          => __( 'or drag and drop', 'wp-boat-ui' ),
			'saveChanges'       => __( 'Save changes', 'wp-boat-ui' ),
			'discardChanges'    => __( 'Discard changes', 'wp-boat-ui' ),
			'unsaved'           => __( 'You have unsaved changes. Leave this page?', 'wp-boat-ui' ),
			'learnMore'         => __( 'Learn more', 'wp-boat-ui' ),
			'notifications'     => __( 'Notifications', 'wp-boat-ui' ),
		);
	}
}
