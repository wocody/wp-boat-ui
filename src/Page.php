<?php
/**
 * Admin screen layout.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/** Admin screen layout: app bar, sidebar navigation, main column and optional aside. */
final class Page {

	/**
	 * Echo a full admin screen.
	 *
	 * @param array<string, mixed> $args See Page::html().
	 * @return void
	 */
	public static function render( array $args ) {
		echo self::html( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the component renderers.
	}

	/**
	 * Build a full admin screen.
	 *
	 * Arguments: `id` (root id); `brand` (`name` and `product` wordmark parts, `icon` for the brand mark (Phosphor name,
	 * default `anchor-simple`) or `logo` as image URL or trusted SVG, plus `href` and `label`); `navigation` (see
	 * Page::navigation()); `search` (quick search over the navigation with ⌘K / Ctrl+K, default true); `router` (show one
	 * `[data-bui-view]` at a time from the URL hash, default false); `actions` (trusted HTML at the end of the app bar);
	 * `before` and `content` (trusted HTML for the main column, usually UI::paper() blocks); `aside` (trusted HTML or list
	 * for the right column); `class` and `attrs` for the root element.
	 *
	 * @param array<string, mixed> $args Screen arguments.
	 * @return string
	 */
	public static function html( array $args ) {
		$navigation = isset( $args['navigation'] ) ? (array) $args['navigation'] : array();
		$search     = ( ! isset( $args['search'] ) || $args['search'] ) && $navigation;
		$has_aside  = ! empty( $args['aside'] );
		$brand      = ! empty( $args['brand'] ) ? self::brand( $args['brand'] ) : '';
		$attrs      = isset( $args['attrs'] ) && is_array( $args['attrs'] ) ? $args['attrs'] : array();

		$attrs['id']              = isset( $args['id'] ) ? $args['id'] : null;
		$attrs['class']           = Html::classes( 'bui-root', isset( $args['class'] ) ? $args['class'] : '' );
		$attrs['data-bui-router'] = ! empty( $args['router'] );

		// App bar: menu (phones), brand, search and actions.
		$appbar = '';
		if ( $navigation ) {
			$appbar .= '<button type="button" class="bui-appbar__menu" data-bui-modal-open="bui-mobile-navigation" aria-controls="bui-mobile-navigation"><span class="bui-sr-only">'
				. Html::esc( Strings::get( 'openNavigation' ) ) . '</span>' . Icon::render( 'list' ) . '</button>';
		}
		$appbar .= $brand . '<span class="bui-appbar__spacer"></span>';
		if ( $search ) {
			$appbar .= '<button type="button" class="bui-search-button" data-bui-search-open>' . Icon::render( 'magnifying-glass', array( 'class' => 'bui-search-button__icon' ) )
				. '<span class="bui-search-button__label">' . Html::esc( Strings::get( 'search' ) ) . '</span><kbd class="bui-search-button__kbd" data-bui-search-shortcut>Ctrl K</kbd></button>'
				. '<button type="button" class="bui-search-icon-button" data-bui-search-open><span class="bui-sr-only">' . Html::esc( Strings::get( 'searchLabel' ) ) . '</span>' . Icon::render( 'magnifying-glass' ) . '</button>';
		}
		if ( ! empty( $args['actions'] ) ) {
			$appbar .= '<div class="bui-appbar__actions">' . Html::render( $args['actions'] ) . '</div>';
		}

		$main = Html::render( isset( $args['before'] ) ? $args['before'] : '' ) . Html::render( isset( $args['content'] ) ? $args['content'] : '' );
		$body = '<div class="bui-layout__main">' . $main . '</div>';
		if ( $has_aside ) {
			$aside = is_array( $args['aside'] ) && ! is_callable( $args['aside'] ) ? $args['aside'] : array( $args['aside'] );
			$body .= '<aside class="bui-layout__aside"><div class="bui-layout__aside-grid">' . Html::render( $aside ) . '</div></aside>';
		}

		$sidebar = $navigation
			? '<aside class="bui-layout__sidebar"><div class="bui-layout__sidebar-inner">' . self::nav( $navigation, 'desktop' ) . '</div></aside>'
			: '';

		$overlays = '<div class="bui-notifications" aria-live="polite" aria-label="' . Html::esc_attr( Strings::get( 'notifications' ) ) . '" data-bui-notifications></div>';
		if ( $navigation ) {
			$overlays .= '<dialog id="bui-mobile-navigation" class="bui-mobile-navigation__dialog" aria-label="' . Html::esc_attr( Strings::get( 'mainNavigation' ) ) . '"><div class="bui-mobile-navigation__panel">'
				. '<div class="bui-mobile-navigation__header">' . $brand . '<button type="button" class="bui-mobile-navigation__close" data-bui-modal-close><span class="bui-sr-only">'
				. Html::esc( Strings::get( 'closeNavigation' ) ) . '</span>' . Icon::render( 'x' ) . '</button></div>'
				. self::nav( $navigation, 'mobile' ) . '</div></dialog>';
		}
		if ( $search ) {
			$overlays .= self::search_modal();
		}

		$root = Html::tag(
			'div',
			$attrs,
			'<header class="bui-appbar">' . $appbar . '</header>'
			. '<div class="' . Html::esc_attr( Html::classes( 'bui-layout', $has_aside ? 'bui-layout--has-aside' : '' ) ) . '">'
			. $sidebar . '<div class="bui-layout__body">' . $body . '</div></div>'
			. $overlays
		);

		// WordPress moves admin notices after .wp-header-end; keep them outside the scoped reset.
		return '<div class="bui-wp-notices"><hr class="wp-header-end"></div>' . $root;
	}

	/**
	 * Brand mark and wordmark, or a custom logo.
	 *
	 * @param array<string, mixed> $brand Brand arguments.
	 * @return string
	 */
	public static function brand( array $brand ) {
		$label = isset( $brand['label'] ) ? $brand['label'] : trim( ( isset( $brand['name'] ) ? $brand['name'] : '' ) . ' ' . ( isset( $brand['product'] ) ? $brand['product'] : '' ) );
		if ( ! empty( $brand['logo'] ) ) {
			$logo  = (string) $brand['logo'];
			$inner = 0 === strpos( ltrim( $logo ), '<svg' )
				? $logo
				: '<img class="bui-brand__logo" src="' . Html::esc_url( $logo ) . '" alt="">';
		} else {
			$inner = '<span class="bui-brand__mark">' . Icon::render( isset( $brand['icon'] ) ? $brand['icon'] : 'anchor-simple' ) . '</span>'
				. '<span class="bui-brand__text"><span class="bui-brand__name">' . Html::esc( isset( $brand['name'] ) ? $brand['name'] : '' ) . '</span>'
				. ( ! empty( $brand['product'] ) ? '<span class="bui-brand__product">' . Html::esc( $brand['product'] ) . '</span>' : '' ) . '</span>';
		}
		return Html::tag(
			'a',
			array(
				'class'      => 'bui-brand',
				'href'       => isset( $brand['href'] ) ? $brand['href'] : '#',
				'aria-label' => $label,
			),
			$inner
		);
	}

	/**
	 * Sidebar navigation.
	 *
	 * Each entry is either a link (`label`, `href`, `icon`, `id`, `active`, `badge`, `view`, `attrs`) or a group
	 * (`label` shown as a small heading, `items` (links), `limit` (links shown before "Show more")). Links inside groups
	 * show a dot when they have no icon. `view` links a `[data-bui-view]` element when the page router is on.
	 *
	 * @param array<int, mixed> $entries Navigation entries.
	 * @param string            $context desktop|mobile, used to keep ids unique.
	 * @return string
	 */
	public static function nav( array $entries, $context = 'desktop' ) {
		$out   = '';
		$loose = '';
		foreach ( $entries as $index => $entry ) {
			if ( empty( $entry['items'] ) ) {
				$loose .= self::nav_item( $entry, false );
				continue;
			}
			if ( '' !== $loose ) {
				$out  .= '<ul role="list" class="bui-nav__list">' . $loose . '</ul>';
				$loose = '';
			}
			$limit   = isset( $entry['limit'] ) ? (int) $entry['limit'] : 0;
			$items   = '';
			$extra   = '';
			$more_id = 'bui-nav-more-' . $context . '-' . $index;
			foreach ( array_values( $entry['items'] ) as $position => $item ) {
				if ( $limit > 0 && $position >= $limit && empty( $item['active'] ) ) {
					$extra .= self::nav_item( $item, true );
				} else {
					$items .= self::nav_item( $item, true );
				}
			}
			$label_id = 'bui-nav-group-' . $context . '-' . $index;
			$group    = '<span class="bui-nav__label" id="' . Html::esc_attr( $label_id ) . '">' . Html::esc( $entry['label'] ) . '</span>'
				. '<ul role="list" class="bui-nav__list" aria-labelledby="' . Html::esc_attr( $label_id ) . '">' . $items . '</ul>';
			if ( '' !== $extra ) {
				$group .= '<ul role="list" class="bui-nav__list" id="' . Html::esc_attr( $more_id ) . '" hidden>' . $extra . '</ul>'
					. '<button type="button" class="bui-nav__more-toggle" aria-expanded="false" aria-controls="' . Html::esc_attr( $more_id ) . '" data-bui-limiter>'
					. '<span data-bui-limiter-label>' . Html::esc( Strings::get( 'showMore' ) ) . '</span>' . Icon::render( 'caret-down' ) . '</button>';
			}
			$out .= '<div class="bui-nav__group">' . $group . '</div>';
		}
		if ( '' !== $loose ) {
			$out .= '<ul role="list" class="bui-nav__list">' . $loose . '</ul>';
		}
		return '<nav class="bui-nav" aria-label="' . Html::esc_attr( Strings::get( 'mainNavigation' ) ) . '">' . $out . '</nav>';
	}

	/**
	 * Alias of nav(), kept for 1.x callers.
	 *
	 * @param array<int, mixed> $entries Navigation entries.
	 * @param string            $context desktop|mobile.
	 * @return string
	 */
	public static function navigation( array $entries, $context = 'desktop' ) {
		return self::nav( $entries, $context );
	}

	/**
	 * One navigation link.
	 *
	 * @param array<string, mixed> $item    Item.
	 * @param bool                 $grouped Whether it sits inside a group (a dot replaces a missing icon).
	 * @return string
	 */
	private static function nav_item( array $item, $grouped ) {
		$active = ! empty( $item['active'] );
		if ( ! empty( $item['icon'] ) ) {
			$inner = Icon::render( $item['icon'], array( 'class' => 'bui-nav__icon' ) );
		} else {
			$inner = $grouped ? '<span class="bui-nav__dot" aria-hidden="true"></span>' : '';
		}
		$inner .= '<span class="bui-nav__text">' . Html::esc( $item['label'] ) . '</span>';
		if ( isset( $item['badge'] ) && '' !== $item['badge'] ) {
			$badge  = is_array( $item['badge'] ) ? $item['badge'] : array( $item['badge'], 'plain' );
			$inner .= UI::badge(
				$badge[0],
				isset( $badge[1] ) ? $badge[1] : 'plain',
				array(
					'size'  => 'small',
					'class' => 'bui-nav__badge',
				)
			);
		}
		$link = Html::tag(
			'a',
			( isset( $item['attrs'] ) && is_array( $item['attrs'] ) ? $item['attrs'] : array() ) + array(
				'class'         => array(
					'bui-nav__link',
					$active ? 'bui-is-active' : '',
				),
				'href'          => isset( $item['href'] ) ? $item['href'] : ( isset( $item['view'] ) ? '#' . $item['view'] : '#' ),
				'aria-current'  => $active ? 'page' : null,
				'data-bui-nav'  => isset( $item['view'] ) ? $item['view'] : null,
				'data-bui-item' => isset( $item['id'] ) ? $item['id'] : null,
			),
			$inner
		);
		return '<li>' . $link . '</li>';
	}

	/**
	 * Quick search dialog.
	 *
	 * @return string
	 */
	private static function search_modal() {
		return '<dialog id="bui-search" class="bui-modal bui-modal--top-center bui-search-modal" aria-label="' . Html::esc_attr( Strings::get( 'searchLabel' ) ) . '"><div class="bui-modal__layout"><div class="bui-modal__panel">'
			. '<div class="bui-search-modal__field">' . Icon::render( 'magnifying-glass' )
			. '<input type="search" class="bui-search-modal__input" role="combobox" aria-expanded="true" aria-controls="bui-search-results" aria-autocomplete="list" autocomplete="off" placeholder="' . Html::esc_attr( Strings::get( 'searchPlaceholder' ) ) . '" aria-label="' . Html::esc_attr( Strings::get( 'searchLabel' ) ) . '" data-bui-search-input></div>'
			. '<div id="bui-search-results" class="bui-search-modal__results" role="listbox" data-bui-search-results></div>'
			. '</div></div></dialog>';
	}
}
