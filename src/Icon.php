<?php
/**
 * Phosphor icons renderer.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/** Inline SVG icons from Phosphor Icons, regular weight (https://phosphoricons.com). */
final class Icon {

	/**
	 * Icon map, loaded on first use.
	 *
	 * @var array<string, string>|null
	 */
	private static $icons = null;

	/**
	 * Render an icon. Unknown names render nothing.
	 *
	 * @param string               $name Phosphor icon name as listed on phosphoricons.com, e.g. `gear-six` or `cloud-arrow-up`.
	 * @param array<string, mixed> $args `class`, `label` (accessible name) and extra `attrs`.
	 * @return string
	 */
	public static function render( $name, array $args = array() ) {
		$name  = self::normalize( $name );
		$icons = self::icons();
		if ( ! isset( $icons[ $name ] ) ) {
			return '';
		}
		$attrs = array(
			'xmlns'   => 'http://www.w3.org/2000/svg',
			'viewBox' => '0 0 256 256',
			'fill'    => 'currentColor',
		);
		if ( ! empty( $args['label'] ) ) {
			$attrs['role']       = 'img';
			$attrs['aria-label'] = $args['label'];
		} else {
			$attrs['aria-hidden'] = 'true';
			$attrs['focusable']   = 'false';
		}
		$attrs['class'] = isset( $args['class'] ) ? $args['class'] : null;
		if ( isset( $args['attrs'] ) && is_array( $args['attrs'] ) ) {
			$attrs = array_merge( $attrs, $args['attrs'] );
		}
		return '<svg' . Html::attrs( $attrs ) . '>' . $icons[ $name ] . '</svg>';
	}

	/**
	 * Whether an icon exists.
	 *
	 * @param string $name Icon name.
	 * @return bool
	 */
	public static function exists( $name ) {
		$icons = self::icons();
		return isset( $icons[ self::normalize( $name ) ] );
	}

	/**
	 * Available icon names.
	 *
	 * @return string[]
	 */
	public static function names() {
		return array_keys( self::icons() );
	}

	/**
	 * Drop an optional `regular:` weight prefix.
	 *
	 * @param string $name Icon name.
	 * @return string
	 */
	private static function normalize( $name ) {
		$name = (string) $name;
		return 0 === strpos( $name, 'regular:' ) ? substr( $name, 8 ) : $name;
	}

	/**
	 * Load the icon map.
	 *
	 * @return array<string, string>
	 */
	private static function icons() {
		if ( null === self::$icons ) {
			self::$icons = require __DIR__ . '/icons.php';
		}
		return self::$icons;
	}
}
