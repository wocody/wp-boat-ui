<?php
/**
 * Component renderers.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/**
 * Server-rendered components. Every method returns escaped markup.
 *
 * Text arguments are escaped. Arguments documented as "trusted HTML"
 * (`content`, `footer`, `*_html`, ...) accept a markup string, a callable
 * that echoes or returns markup, or a list of those, and are output as is.
 */
final class UI {

	const HANDLE = Assets::HANDLE;

	/**
	 * Version of the copy in use.
	 *
	 * @return string
	 */
	public static function version() {
		return \Wocody_Boat_UI_Loader::version();
	}

	/**
	 * Directory of the copy in use.
	 *
	 * @return string
	 */
	public static function root() {
		return \Wocody_Boat_UI_Loader::root();
	}

	/**
	 * Enqueue the shared assets on the current admin screen.
	 *
	 * @param array<string, mixed> $args See Assets::enqueue().
	 * @return void
	 */
	public static function enqueue( array $args = array() ) {
		Assets::enqueue( $args );
	}

	/**
	 * Inline SVG icon.
	 *
	 * @param string               $name Heroicon name, optionally `solid:` prefixed.
	 * @param array<string, mixed> $args See Icon::render().
	 * @return string
	 */
	public static function icon( $name, array $args = array() ) {
		return Icon::render( $name, $args );
	}

	/**
	 * Standalone root wrapper, for components outside Page::render().
	 *
	 * @param mixed                $content Trusted HTML.
	 * @param array<string, mixed> $attrs   Extra attributes.
	 * @return string
	 */
	public static function root_wrapper( $content, array $attrs = array() ) {
		$attrs['class'] = Html::classes( 'bui-root', isset( $attrs['class'] ) ? $attrs['class'] : '' );
		return Html::tag( 'div', $attrs, Html::render( $content ) );
	}

	// Typography.

	/**
	 * Heading.
	 *
	 * @param string               $text  Text.
	 * @param int                  $size  Visual size 1–5.
	 * @param array<string, mixed> $args  `as` (tag, default h{size}), `id`, `class`, `attrs`.
	 * @return string
	 */
	public static function title( $text, $size = 1, array $args = array() ) {
		$size = max( 1, min( 5, (int) $size ) );
		$tag  = isset( $args['as'] ) ? $args['as'] : 'h' . $size;
		return Html::tag( $tag, self::attrs( $args, array( 'bui-title', 'bui-title--' . $size ) ), Html::esc( $text ) );
	}

	/**
	 * Link.
	 *
	 * @param string               $label Text.
	 * @param string               $href  URL.
	 * @param array<string, mixed> $args  `variant` (default|primary|error), `arrow`, `external`, `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function link( $label, $href, array $args = array() ) {
		$variant = isset( $args['variant'] ) ? $args['variant'] : 'default';
		$arrow   = ! empty( $args['arrow'] );
		$attrs   = self::attrs(
			$args,
			array(
				'bui-link',
				'default' !== $variant ? 'bui-link--' . $variant : '',
				$arrow ? 'bui-link--arrow' : '',
			)
		);

		$attrs['href'] = $href;
		$inner         = Html::esc( $label );
		if ( ! empty( $args['external'] ) ) {
			$attrs['target'] = '_blank';
			$attrs['rel']    = 'noopener noreferrer';
			$inner          .= '<span class="bui-sr-only"> ' . Html::esc( Strings::get( 'newTab' ) ) . '</span>';
		}
		if ( $arrow ) {
			$inner .= Icon::render( 'arrow-narrow-right' );
		}
		return Html::tag( 'a', $attrs, $inner );
	}

	/**
	 * Inline or block code.
	 *
	 * @param string $text  Code.
	 * @param bool   $block Block variant.
	 * @return string
	 */
	public static function code( $text, $block = false ) {
		return Html::tag( 'code', array( 'class' => array( 'bui-code', $block ? 'bui-code--block' : '' ) ), Html::esc( $text ) );
	}

	/**
	 * Badge.
	 *
	 * @param string               $label   Text.
	 * @param string               $variant info|upsell|plain|success|error|primary.
	 * @param array<string, mixed> $args    `size` (small|default|large), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function badge( $label, $variant = 'info', array $args = array() ) {
		$size = isset( $args['size'] ) ? $args['size'] : 'default';
		return Html::tag( 'span', self::attrs( $args, array( 'bui-badge', 'bui-badge--' . $variant, 'default' !== $size ? 'bui-badge--' . $size : '' ) ), Html::esc( $label ) );
	}

	// Actions.

	/**
	 * Button or button-styled link.
	 *
	 * @param string               $label Text.
	 * @param array<string, mixed> $args  `variant` (primary|secondary|tertiary|error|upsell), `size` (small|default|large|extra-large),
	 *                                    `href` (renders a link), `type` (button|submit|reset), `icon`, `icon_end`, `disabled`, `block`,
	 *                                    `loading`, `external`, `label_html` (trusted HTML replacing the label), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function button( $label, array $args = array() ) {
		$variant = isset( $args['variant'] ) ? $args['variant'] : 'primary';
		$size    = isset( $args['size'] ) ? $args['size'] : 'default';
		$attrs   = self::attrs(
			$args,
			array(
				'bui-button',
				'bui-button--' . $variant,
				'default' !== $size ? 'bui-button--' . $size : '',
				! empty( $args['block'] ) ? 'bui-button--block' : '',
				! empty( $args['disabled'] ) && ! empty( $args['href'] ) ? 'bui-button--disabled' : '',
			)
		);

		$inner = '';
		if ( ! empty( $args['loading'] ) ) {
			$inner .= self::spinner();
		} elseif ( ! empty( $args['icon'] ) ) {
			$inner .= Icon::render( $args['icon'] );
		}
		$inner .= isset( $args['label_html'] ) ? Html::render( $args['label_html'] ) : Html::esc( $label );
		if ( ! empty( $args['icon_end'] ) ) {
			$inner .= Icon::render( $args['icon_end'] );
		}

		if ( ! empty( $args['href'] ) ) {
			$attrs['href'] = $args['href'];
			if ( ! empty( $args['disabled'] ) ) {
				$attrs['aria-disabled'] = 'true';
				$attrs['tabindex']      = '-1';
			}
			if ( ! empty( $args['external'] ) ) {
				$attrs['target'] = '_blank';
				$attrs['rel']    = 'noopener noreferrer';
				$inner          .= '<span class="bui-sr-only"> ' . Html::esc( Strings::get( 'newTab' ) ) . '</span>';
			}
			return Html::tag( 'a', $attrs, $inner );
		}
		$attrs['type']     = isset( $args['type'] ) ? $args['type'] : 'button';
		$attrs['disabled'] = ! empty( $args['disabled'] );
		return Html::tag( 'button', $attrs, $inner );
	}

	/**
	 * Row of buttons.
	 *
	 * @param mixed                $buttons Trusted HTML (usually several UI::button()).
	 * @param array<string, mixed> $args    `tight`, `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function button_group( $buttons, array $args = array() ) {
		return Html::tag( 'div', self::attrs( $args, array( 'bui-button-group', ! empty( $args['tight'] ) ? 'bui-button-group--tight' : '' ) ), Html::render( $buttons ) );
	}

	/**
	 * Loading spinner.
	 *
	 * @param array<string, mixed> $args `class`, `label`.
	 * @return string
	 */
	public static function spinner( array $args = array() ) {
		$label = isset( $args['label'] ) ? $args['label'] : '';
		return '<svg' . Html::attrs(
			array(
				'class'       => array( 'bui-spinner', isset( $args['class'] ) ? $args['class'] : '' ),
				'xmlns'       => 'http://www.w3.org/2000/svg',
				'fill'        => 'none',
				'viewBox'     => '0 0 24 24',
				'role'        => $label ? 'img' : null,
				'aria-label'  => $label ? $label : null,
				'aria-hidden' => $label ? null : 'true',
			)
		) . '><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
	}

	// Surfaces.

	/**
	 * White surface with optional header and sticky footer (the main settings card).
	 *
	 * @param array<string, mixed> $args `title`, `description` (trusted HTML), `header_actions` (trusted HTML), `content` (trusted HTML),
	 *                                   `footer` (trusted HTML), `dirty_footer` (show the footer only after a field changes),
	 *                                   `as` (main|section|div|form), `title_tag`, `padded` (simple padded box), `content_class`, `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function paper( array $args = array() ) {
		$tag   = isset( $args['as'] ) ? $args['as'] : 'section';
		$attrs = self::attrs( $args, array( 'bui-paper', ! empty( $args['padded'] ) ? 'bui-paper--padded' : '' ) );
		if ( ! empty( $args['padded'] ) ) {
			return Html::tag( $tag, $attrs, Html::render( isset( $args['content'] ) ? $args['content'] : '' ) );
		}

		$inner = '';
		if ( isset( $args['title'] ) && '' !== $args['title'] ) {
			$title_id = isset( $args['id'] ) ? $args['id'] . '-title' : Html::id( 'bui-paper-title' );
			if ( ! isset( $attrs['aria-labelledby'] ) && 'main' !== $tag ) {
				$attrs['aria-labelledby'] = $title_id;
			}
			$header  = self::title(
				$args['title'],
				1,
				array(
					'as' => isset( $args['title_tag'] ) ? $args['title_tag'] : ( 'main' === $tag ? 'h1' : 'h2' ),
					'id' => $title_id,
				)
			);
			$header .= ! empty( $args['description'] ) ? '<div class="bui-paper__description">' . Html::render( $args['description'] ) . '</div>' : '';
			$header .= ! empty( $args['header_actions'] ) ? '<div class="bui-paper__header-actions">' . Html::render( $args['header_actions'] ) . '</div>' : '';
			$inner  .= '<header class="bui-paper__header"><div class="bui-paper__header-inner">' . $header . '</div></header>';
		}
		if ( isset( $args['content'] ) ) {
			$inner .= Html::tag( 'div', array( 'class' => array( 'bui-paper__content', isset( $args['content_class'] ) ? $args['content_class'] : '' ) ), '<div class="bui-paper__content-inner">' . Html::render( $args['content'] ) . '</div>' );
		}
		if ( ! empty( $args['footer'] ) ) {
			$inner .= Html::tag(
				'footer',
				array(
					'class'                 => 'bui-paper__footer',
					'data-bui-dirty-footer' => ! empty( $args['dirty_footer'] ),
				),
				'<div class="bui-paper__footer-inner">' . Html::render( $args['footer'] ) . '</div>'
			);
		}
		return Html::tag( $tag, $attrs, $inner );
	}

	/**
	 * Settings section: title and description on the left, fields on the right (3-column grid on wide screens).
	 *
	 * @param array<string, mixed> $args `title`, `description` (trusted HTML), `content` (trusted HTML), `fieldset` (wrap fields in a fieldset, default true), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function section( array $args = array() ) {
		$title    = isset( $args['title'] ) ? $args['title'] : '';
		$fieldset = ! isset( $args['fieldset'] ) || $args['fieldset'];
		$intro    = '';
		if ( '' !== $title ) {
			$intro = '<div class="bui-section__intro">' . self::title( $title, 4, array( 'as' => 'h2' ) )
				. ( ! empty( $args['description'] ) ? '<div class="bui-section__description">' . Html::render( $args['description'] ) . '</div>' : '' )
				. '</div>';
		}
		$stack  = '<div class="bui-section__stack">' . Html::render( isset( $args['content'] ) ? $args['content'] : '' ) . '</div>';
		$fields = $fieldset
			? '<fieldset class="bui-section__fields">' . ( '' !== $title ? '<legend class="bui-sr-only">' . Html::esc( $title ) . '</legend>' : '' ) . $stack . '</fieldset>'
			: '<div class="bui-section__fields">' . $stack . '</div>';
		return Html::tag( 'section', self::attrs( $args, 'bui-section' ), '<div>' . $intro . '</div>' . $fields );
	}

	/**
	 * Horizontal divider between sections.
	 *
	 * @return string
	 */
	public static function divider() {
		return '<hr class="bui-divider">';
	}

	/**
	 * Card with optional header area and footer.
	 *
	 * @param array<string, mixed> $args `header` (trusted HTML), `title`, `content` (trusted HTML), `footer` (trusted HTML), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function card( array $args = array() ) {
		$inner  = ! empty( $args['header'] ) ? '<div class="bui-card__header">' . Html::render( $args['header'] ) . '</div>' : '';
		$body   = ! empty( $args['title'] ) ? self::title( $args['title'], 3, array( 'as' => 'h3' ) ) : '';
		$body  .= Html::render( isset( $args['content'] ) ? $args['content'] : '' );
		$inner .= '<div class="bui-card__content">' . $body . '</div>';
		$inner .= ! empty( $args['footer'] ) ? '<div class="bui-card__footer">' . Html::render( $args['footer'] ) . '</div>' : '';
		return Html::tag( 'div', self::attrs( $args, 'bui-card' ), $inner );
	}

	/**
	 * Box for the right-hand column.
	 *
	 * @param array<string, mixed> $args `title`, `content` (trusted HTML), `variant` (default|highlight), `size` (default|small), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function aside_box( array $args = array() ) {
		$variant = isset( $args['variant'] ) ? $args['variant'] : 'default';
		$size    = isset( $args['size'] ) ? $args['size'] : 'default';
		$inner   = ! empty( $args['title'] ) ? '<h2 class="bui-aside-box__title">' . Html::esc( $args['title'] ) . '</h2>' : '';
		$inner  .= Html::render( isset( $args['content'] ) ? $args['content'] : '' );
		return Html::tag(
			'div',
			self::attrs( $args, array( 'bui-aside-box', 'default' !== $variant ? 'bui-aside-box--' . $variant : '', 'default' !== $size ? 'bui-aside-box--' . $size : '' ) ),
			$inner
		);
	}

	/**
	 * List with green check icons.
	 *
	 * @param string[]             $items Texts.
	 * @param array<string, mixed> $args  `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function check_list( array $items, array $args = array() ) {
		$inner = '';
		foreach ( $items as $item ) {
			$inner .= '<li>' . Icon::render( 'solid:check-circle' ) . '<span>' . Html::esc( $item ) . '</span></li>';
		}
		return Html::tag( 'ul', self::attrs( $args, 'bui-check-list' ), $inner );
	}

	// Feedback.

	/**
	 * Inline alert.
	 *
	 * @param mixed                $message Trusted HTML message.
	 * @param string               $variant info|warning|success|error.
	 * @param array<string, mixed> $args    `title`, `icon` (false hides it), `dismissible`, `role` (default alert for errors, status otherwise), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function alert( $message, $variant = 'info', array $args = array() ) {
		$icons = array(
			'info'    => 'solid:information-circle',
			'warning' => 'solid:exclamation',
			'success' => 'solid:check-circle',
			'error'   => 'solid:x-circle',
		);
		$attrs = self::attrs( $args, array( 'bui-alert', 'bui-alert--' . $variant ) );
		if ( ! isset( $attrs['role'] ) ) {
			$attrs['role'] = isset( $args['role'] ) ? $args['role'] : ( 'error' === $variant ? 'alert' : 'status' );
		}
		$icon   = isset( $args['icon'] ) ? $args['icon'] : ( isset( $icons[ $variant ] ) ? $icons[ $variant ] : '' );
		$inner  = $icon ? Icon::render( $icon, array( 'class' => 'bui-alert__icon' ) ) : '';
		$body   = ! empty( $args['title'] ) ? '<p class="bui-alert__title">' . Html::esc( $args['title'] ) . '</p>' : '';
		$body  .= Html::render( $message );
		$inner .= '<div class="bui-alert__message">' . $body . '</div>';
		if ( ! empty( $args['dismissible'] ) ) {
			$inner .= '<button type="button" class="bui-alert__dismiss" data-bui-dismiss><span class="bui-sr-only">' . Html::esc( Strings::get( 'dismiss' ) ) . '</span>' . Icon::render( 'solid:x' ) . '</button>';
		}
		return Html::tag( 'div', $attrs, $inner );
	}

	/**
	 * Announcement banner with a bordered white surface.
	 *
	 * @param array<string, mixed> $args `title`, `content` (trusted HTML), `icon`, `dismissible`, `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function banner( array $args = array() ) {
		$icon   = isset( $args['icon'] ) ? $args['icon'] : 'solid:information-circle';
		$inner  = $icon ? Icon::render( $icon, array( 'class' => 'bui-banner__icon' ) ) : '';
		$body   = ! empty( $args['title'] ) ? '<p class="bui-banner__title">' . Html::esc( $args['title'] ) . '</p>' : '';
		$body  .= ! empty( $args['content'] ) ? '<div class="bui-banner__message">' . Html::render( $args['content'] ) . '</div>' : '';
		$inner .= '<div>' . $body . '</div>';
		if ( ! empty( $args['dismissible'] ) ) {
			$inner .= '<button type="button" class="bui-banner__dismiss" data-bui-dismiss><span class="bui-sr-only">' . Html::esc( Strings::get( 'dismiss' ) ) . '</span>' . Icon::render( 'x' ) . '</button>';
		}
		return Html::tag( 'div', self::attrs( $args, 'bui-banner' ), $inner );
	}

	/**
	 * Native progress element styled as a progress bar.
	 *
	 * @param int|float|null       $value Current value, or null for indeterminate.
	 * @param int|float            $max   Maximum.
	 * @param array<string, mixed> $args  `label` (accessible name), `size` (default|large), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function progress( $value = 0, $max = 100, array $args = array() ) {
		$attrs               = self::attrs( $args, array( 'bui-progress', isset( $args['size'] ) && 'large' === $args['size'] ? 'bui-progress--large' : '' ) );
		$attrs['max']        = (string) $max;
		$attrs['value']      = null === $value ? null : (string) $value;
		$attrs['aria-label'] = isset( $args['label'] ) ? $args['label'] : null;
		return Html::tag( 'progress', $attrs, null === $value ? '' : Html::esc( round( 100 * $value / max( 1, $max ) ) . '%' ) );
	}

	/**
	 * Progress bar followed by its percentage.
	 *
	 * @param int|float            $value Current value.
	 * @param int|float            $max   Maximum.
	 * @param array<string, mixed> $args  Progress arguments plus `value_id` for the percentage element.
	 * @return string
	 */
	public static function progress_field( $value = 0, $max = 100, array $args = array() ) {
		$value_id = isset( $args['value_id'] ) ? $args['value_id'] : null;
		unset( $args['value_id'] );
		$percent = round( 100 * $value / max( 1, $max ) ) . '%';
		return '<div class="bui-progress-field">' . self::progress( $value, $max, $args ) . Html::tag(
			'span',
			array(
				'class' => 'bui-progress-field__value',
				'id'    => $value_id,
			),
			Html::esc( $percent )
		) . '</div>';
	}

	/**
	 * Tooltip with an information trigger.
	 *
	 * @param string               $text Tooltip text.
	 * @param array<string, mixed> $args `trigger_html` (trusted HTML replacing the icon trigger), `label` (trigger accessible name).
	 * @return string
	 */
	public static function tooltip( $text, array $args = array() ) {
		$id      = Html::id( 'bui-tooltip' );
		$trigger = isset( $args['trigger_html'] )
			? Html::render( $args['trigger_html'] )
			: '<button type="button" class="bui-tooltip-trigger" aria-describedby="' . Html::esc_attr( $id ) . '">' . Icon::render( 'solid:question-mark-circle' ) . '<span class="bui-sr-only">' . Html::esc( isset( $args['label'] ) ? $args['label'] : $text ) . '</span></button>';
		return '<span class="bui-tooltip-container">' . $trigger . '<span role="tooltip" id="' . Html::esc_attr( $id ) . '" class="bui-tooltip">' . Html::esc( $text ) . '</span></span>';
	}

	/**
	 * Loading placeholder.
	 *
	 * @param string $width  CSS width.
	 * @param string $height CSS height.
	 * @return string
	 */
	public static function skeleton( $width = '100%', $height = '1rem' ) {
		return '<span class="bui-skeleton" style="width:' . Html::esc_attr( $width ) . ';height:' . Html::esc_attr( $height ) . '"></span>';
	}

	// Form fields.

	/**
	 * Text input with label, description and validation.
	 *
	 * @param array<string, mixed> $args `id`, `name`, `label`, `value`, `type`, `placeholder`, `description` (trusted HTML), `required`,
	 *                                   `disabled`, `readonly`, `autocomplete`, `validation` (array `variant`, `message`), `label_suffix` (trusted HTML),
	 *                                   `input_class`, `input_attrs`, `class`, `attrs`.
	 * @return string
	 */
	public static function text_field( array $args = array() ) {
		$args  = self::field_defaults( $args, 'bui-text-field' );
		$input = Html::tag(
			'input',
			array_merge(
				array(
					'type'             => isset( $args['type'] ) ? $args['type'] : 'text',
					'id'               => $args['id'],
					'name'             => isset( $args['name'] ) ? $args['name'] : null,
					'value'            => isset( $args['value'] ) ? (string) $args['value'] : null,
					'placeholder'      => isset( $args['placeholder'] ) ? $args['placeholder'] : null,
					'autocomplete'     => isset( $args['autocomplete'] ) ? $args['autocomplete'] : null,
					'required'         => ! empty( $args['required'] ),
					'disabled'         => ! empty( $args['disabled'] ),
					'readonly'         => ! empty( $args['readonly'] ),
					'aria-describedby' => self::described_by( $args ),
					'aria-invalid'     => self::invalid( $args ),
					'class'            => array( 'bui-text-input', isset( $args['input_class'] ) ? $args['input_class'] : '' ),
				),
				isset( $args['input_attrs'] ) ? (array) $args['input_attrs'] : array()
			),
			null
		);
		return self::field( $args, $input, 'bui-text-field' );
	}

	/**
	 * Textarea with label and description.
	 *
	 * @param array<string, mixed> $args Same as text_field() plus `rows`.
	 * @return string
	 */
	public static function textarea_field( array $args = array() ) {
		$args  = self::field_defaults( $args, 'bui-textarea-field' );
		$input = Html::tag(
			'textarea',
			array_merge(
				array(
					'id'               => $args['id'],
					'name'             => isset( $args['name'] ) ? $args['name'] : null,
					'rows'             => isset( $args['rows'] ) ? (string) $args['rows'] : '4',
					'placeholder'      => isset( $args['placeholder'] ) ? $args['placeholder'] : null,
					'required'         => ! empty( $args['required'] ),
					'disabled'         => ! empty( $args['disabled'] ),
					'readonly'         => ! empty( $args['readonly'] ),
					'aria-describedby' => self::described_by( $args ),
					'aria-invalid'     => self::invalid( $args ),
					'class'            => array( 'bui-textarea', isset( $args['input_class'] ) ? $args['input_class'] : '' ),
				),
				isset( $args['input_attrs'] ) ? (array) $args['input_attrs'] : array()
			),
			function_exists( 'esc_textarea' ) ? esc_textarea( isset( $args['value'] ) ? (string) $args['value'] : '' ) : Html::esc( isset( $args['value'] ) ? $args['value'] : '' )
		);
		return self::field( $args, $input, 'bui-textarea-field' );
	}

	/**
	 * Native select with label and description.
	 *
	 * @param array<string, mixed> $args Same as text_field() plus `options` (value => label, or group label => options) and `multiple`.
	 * @return string
	 */
	public static function select_field( array $args = array() ) {
		$args     = self::field_defaults( $args, 'bui-select-field' );
		$selected = isset( $args['value'] ) ? (array) $args['value'] : array();
		$selected = array_map( 'strval', $selected );
		$options  = '';
		foreach ( isset( $args['options'] ) ? (array) $args['options'] : array() as $value => $label ) {
			if ( is_array( $label ) ) {
				$group = '';
				foreach ( $label as $group_value => $group_label ) {
					$group .= self::option( $group_value, $group_label, $selected );
				}
				$options .= Html::tag( 'optgroup', array( 'label' => $value ), $group );
				continue;
			}
			$options .= self::option( $value, $label, $selected );
		}
		$input = Html::tag(
			'select',
			array_merge(
				array(
					'id'               => $args['id'],
					'name'             => isset( $args['name'] ) ? $args['name'] : null,
					'multiple'         => ! empty( $args['multiple'] ),
					'required'         => ! empty( $args['required'] ),
					'disabled'         => ! empty( $args['disabled'] ),
					'aria-describedby' => self::described_by( $args ),
					'aria-invalid'     => self::invalid( $args ),
					'class'            => array( 'bui-select', isset( $args['input_class'] ) ? $args['input_class'] : '' ),
				),
				isset( $args['input_attrs'] ) ? (array) $args['input_attrs'] : array()
			),
			$options
		);
		return self::field( $args, $input, 'bui-select-field' );
	}

	/**
	 * Single checkbox with label.
	 *
	 * @param array<string, mixed> $args `id`, `name`, `value` (default 1), `label`, `label_html` (trusted HTML), `description`, `checked`,
	 *                                   `disabled`, `required`, `input_attrs`, `class`, `attrs`.
	 * @return string
	 */
	public static function checkbox( array $args = array() ) {
		return self::choice( 'checkbox', $args );
	}

	/**
	 * Group of checkboxes.
	 *
	 * @param array<string, mixed> $args `label` (legend), `name` (without []), `options` (value => label), `value` (checked values), `description`, `disabled`, `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function checkbox_group( array $args = array() ) {
		return self::choice_group( 'checkbox', $args );
	}

	/**
	 * Group of radios.
	 *
	 * @param array<string, mixed> $args Same as checkbox_group() plus `variant` (default|inline-block: square tiles) and `aria_labels` (value => accessible name).
	 * @return string
	 */
	public static function radio_group( array $args = array() ) {
		return self::choice_group( 'radio', $args );
	}

	/**
	 * Switch control on a native checkbox (`role="switch"`), so it submits like any checkbox.
	 *
	 * @param array<string, mixed> $args `id`, `name`, `value` (default 1), `checked`, `disabled`, `label` (accessible name when used alone),
	 *                                   `labelledby`, `describedby`, `input_attrs`, `class`, `attrs`.
	 * @return string
	 */
	public static function toggle( array $args = array() ) {
		$input = Html::tag(
			'input',
			array_merge(
				array(
					'type'             => 'checkbox',
					'role'             => 'switch',
					'class'            => 'bui-toggle__input',
					'id'               => isset( $args['id'] ) ? $args['id'] : null,
					'name'             => isset( $args['name'] ) ? $args['name'] : null,
					'value'            => isset( $args['value'] ) ? (string) $args['value'] : '1',
					'checked'          => ! empty( $args['checked'] ),
					'disabled'         => ! empty( $args['disabled'] ),
					'aria-label'       => isset( $args['label'] ) && empty( $args['labelledby'] ) ? $args['label'] : null,
					'aria-labelledby'  => isset( $args['labelledby'] ) ? $args['labelledby'] : null,
					'aria-describedby' => isset( $args['describedby'] ) ? $args['describedby'] : null,
				),
				isset( $args['input_attrs'] ) ? (array) $args['input_attrs'] : array()
			),
			null
		);
		$track = '<span class="bui-toggle__track" aria-hidden="true"><span class="bui-toggle__handle">'
			. Icon::render( 'solid:check', array( 'class' => 'bui-toggle__icon bui-toggle__icon--check' ) )
			. Icon::render( 'solid:x', array( 'class' => 'bui-toggle__icon bui-toggle__icon--x' ) )
			. '</span></span>';
		return Html::tag( 'span', self::attrs( $args, 'bui-toggle', array( 'id' ) ), $input . $track );
	}

	/**
	 * Toggle with label and description.
	 *
	 * @param array<string, mixed> $args toggle() arguments plus `label`, `label_suffix` (trusted HTML), `description` (trusted HTML), `wide`.
	 * @return string
	 */
	public static function toggle_field( array $args = array() ) {
		$id       = isset( $args['id'] ) ? $args['id'] : Html::id( 'bui-toggle' );
		$label_id = $id . '__label';
		$desc_id  = ! empty( $args['description'] ) ? $id . '__description' : null;
		$toggle   = self::toggle(
			array_merge(
				$args,
				array(
					'id'          => $id,
					'labelledby'  => $label_id,
					'describedby' => $desc_id,
					'class'       => null,
					'attrs'       => array(),
				)
			)
		);
		$header   = '<div class="bui-toggle-field__header"><div class="bui-toggle-field__label-wrapper">'
			. '<label class="bui-label bui-toggle-field__label" id="' . Html::esc_attr( $label_id ) . '" for="' . Html::esc_attr( $id ) . '">' . Html::esc( isset( $args['label'] ) ? $args['label'] : '' ) . '</label>'
			. ( isset( $args['label_suffix'] ) ? Html::render( $args['label_suffix'] ) : '' )
			. '</div>' . $toggle . '</div>';
		$desc     = $desc_id ? '<div class="bui-toggle-field__description" id="' . Html::esc_attr( $desc_id ) . '">' . Html::render( $args['description'] ) . '</div>' : '';
		return Html::tag(
			'div',
			self::attrs(
				$args,
				array(
					'bui-toggle-field',
					! empty( $args['wide'] ) ? 'bui-toggle-field--wide' : '',
					! empty( $args['disabled'] ) ? 'bui-toggle-field--disabled' : '',
				),
				array( 'id' )
			),
			$header . $desc
		);
	}

	/**
	 * File drop zone on a native file input.
	 *
	 * @param array<string, mixed> $args `id`, `name`, `label` (select text), `drag_label`, `description` (accepted types/size), `accept`, `multiple`,
	 *                                   `required`, `disabled`, `icon`, `input_attrs`, `class`, `attrs`.
	 * @return string
	 */
	public static function file_input( array $args = array() ) {
		$id      = isset( $args['id'] ) ? $args['id'] : Html::id( 'bui-file' );
		$desc_id = ! empty( $args['description'] ) ? $id . '__description' : null;
		$input   = Html::tag(
			'input',
			array_merge(
				array(
					'type'             => 'file',
					'id'               => $id,
					'class'            => 'bui-file-input__input',
					'name'             => isset( $args['name'] ) ? $args['name'] : null,
					'accept'           => isset( $args['accept'] ) ? $args['accept'] : null,
					'multiple'         => ! empty( $args['multiple'] ),
					'required'         => ! empty( $args['required'] ),
					'disabled'         => ! empty( $args['disabled'] ),
					'aria-describedby' => $desc_id,
				),
				isset( $args['input_attrs'] ) ? (array) $args['input_attrs'] : array()
			),
			null
		);
		$content = Icon::render( isset( $args['icon'] ) ? $args['icon'] : 'document-add', array( 'class' => 'bui-file-input__icon' ) )
			. '<div class="bui-file-input__labels"><label class="bui-file-input__select-label" for="' . Html::esc_attr( $id ) . '">' . Html::esc( isset( $args['label'] ) ? $args['label'] : Strings::get( 'selectFile' ) ) . '</label>'
			. '<span>' . Html::esc( isset( $args['drag_label'] ) ? $args['drag_label'] : Strings::get( 'dragDrop' ) ) . '</span></div>'
			. ( $desc_id ? '<p class="bui-file-input__description" id="' . Html::esc_attr( $desc_id ) . '">' . Html::esc( $args['description'] ) . '</p>' : '' )
			. '<p class="bui-file-input__selected" data-bui-file-name hidden></p>';
		return Html::tag(
			'div',
			self::attrs( $args, array( 'bui-file-input', ! empty( $args['disabled'] ) ? 'bui-is-disabled' : '' ), array( 'id' ) ) + array( 'data-bui-file-input' => true ),
			$input . '<div class="bui-file-input__content">' . $content . '</div>'
		);
	}

	/**
	 * Native disclosure (details/summary).
	 *
	 * @param string               $summary Summary text.
	 * @param mixed                $content Trusted HTML.
	 * @param array<string, mixed> $args    `open`, `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function disclosure( $summary, $content, array $args = array() ) {
		$attrs         = self::attrs( $args, 'bui-disclosure' );
		$attrs['open'] = ! empty( $args['open'] );
		return Html::tag(
			'details',
			$attrs,
			'<summary class="bui-disclosure__summary"><span>' . Html::esc( $summary ) . '</span>' . Icon::render( 'chevron-down' ) . '</summary><div class="bui-disclosure__content">' . Html::render( $content ) . '</div>'
		);
	}

	// Features, tables and overlays.

	/**
	 * Collapsible group of feature rows (icon, copy, learn-more link and switch).
	 *
	 * @param array<string, mixed> $args `title`, `open` (default true), `features` (list of arrays: `icon`, `icon_variant` (default|muted),
	 *                                   `title`, `description` (trusted HTML), `link` (array label, href), `toggle` (toggle() arguments),
	 *                                   `actions` (trusted HTML), `id`), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function feature_group( array $args = array() ) {
		$attrs         = self::attrs( $args, 'bui-feature-group' );
		$attrs['open'] = ! isset( $args['open'] ) || $args['open'];
		$items         = '';
		foreach ( isset( $args['features'] ) ? (array) $args['features'] : array() as $feature ) {
			$items .= self::feature( $feature );
		}
		$summary = '<summary class="bui-feature-group__toggle">' . self::title( isset( $args['title'] ) ? $args['title'] : '', 4, array( 'as' => 'h2' ) ) . Icon::render( 'solid:chevron-down' ) . '</summary>';
		return Html::tag( 'details', $attrs, $summary . '<ul class="bui-feature-group__items">' . $items . '</ul>' );
	}

	/**
	 * Several feature groups in a column of readable width.
	 *
	 * @param array<int, array<string, mixed>> $groups feature_group() arguments.
	 * @return string
	 */
	public static function feature_list( array $groups ) {
		return '<div class="bui-feature-list">' . implode( '', array_map( array( __CLASS__, 'feature_group' ), $groups ) ) . '</div>';
	}

	/**
	 * One feature row.
	 *
	 * @param array<string, mixed> $feature See feature_group().
	 * @return string
	 */
	public static function feature( array $feature ) {
		$id    = isset( $feature['id'] ) ? $feature['id'] : Html::id( 'bui-feature' );
		$icon  = '<div class="bui-feature__icon' . ( isset( $feature['icon_variant'] ) && 'muted' === $feature['icon_variant'] ? ' bui-feature__icon--muted' : '' ) . '">' . Icon::render( isset( $feature['icon'] ) ? $feature['icon'] : 'puzzle' ) . '</div>';
		$title = self::title(
			isset( $feature['title'] ) ? $feature['title'] : '',
			3,
			array(
				'as'    => 'h3',
				'id'    => $id . '__title',
				'class' => 'bui-feature__title',
			)
		);
		$copy  = $title . ( ! empty( $feature['description'] ) ? '<div class="bui-feature__description" id="' . Html::esc_attr( $id . '__description' ) . '">' . Html::render( $feature['description'] ) . '</div>' : '' );
		if ( ! empty( $feature['link'] ) ) {
			$copy .= self::link(
				$feature['link'][0],
				$feature['link'][1],
				array(
					'variant'  => 'primary',
					'arrow'    => true,
					'external' => ! empty( $feature['link'][2] ),
				)
			);
		}
		$body   = '<div class="bui-feature__body"><div class="bui-feature__copy">' . $copy . '</div>' . ( ! empty( $feature['actions'] ) ? '<div class="bui-feature__actions">' . Html::render( $feature['actions'] ) . '</div>' : '' ) . '</div>';
		$toggle = '';
		if ( isset( $feature['toggle'] ) && is_array( $feature['toggle'] ) ) {
			$toggle = '<div>' . self::toggle(
				array_merge(
					array(
						'id'          => $id . '__toggle',
						'labelledby'  => $id . '__title',
						'describedby' => ! empty( $feature['description'] ) ? $id . '__description' : null,
					),
					$feature['toggle']
				)
			) . '</div>';
		}
		return '<li class="bui-feature" id="' . Html::esc_attr( $id ) . '">' . $icon . $body . $toggle . '</li>';
	}

	/**
	 * Data table.
	 *
	 * @param array<string, mixed> $args `columns` (key => label, or key => array label, class), `rows` (list of key => trusted HTML cell),
	 *                                   `empty` (text when there are no rows), `caption`, `stack` (stack rows on phones, default true),
	 *                                   `striped`, `body_id`, `id_prefix` (header ids, for headers= associations), `class`, `id`, `attrs`.
	 * @return string
	 */
	public static function table( array $args = array() ) {
		$columns = isset( $args['columns'] ) ? (array) $args['columns'] : array();
		$prefix  = isset( $args['id_prefix'] ) ? $args['id_prefix'] : Html::id( 'bui-table' );
		$head    = '';
		foreach ( $columns as $key => $column ) {
			$label = is_array( $column ) ? $column['label'] : $column;
			$class = is_array( $column ) && isset( $column['class'] ) ? $column['class'] : null;
			$head .= Html::tag(
				'th',
				array(
					'id'    => $prefix . '-' . $key,
					'scope' => 'col',
					'class' => $class,
				),
				Html::esc( $label )
			);
		}
		$body = '';
		$rows = isset( $args['rows'] ) ? (array) $args['rows'] : array();
		foreach ( $rows as $row ) {
			$cells = '';
			$index = 0;
			foreach ( $columns as $key => $column ) {
				$label  = is_array( $column ) ? $column['label'] : $column;
				$cells .= Html::tag(
					'td',
					array(
						'headers'    => $prefix . '-' . $key,
						'data-label' => $index > 0 ? $label : null,
					),
					isset( $row[ $key ] ) ? Html::render( $row[ $key ] ) : ''
				);
				++$index;
			}
			$body .= '<tr>' . $cells . '</tr>';
		}
		if ( ! $rows && isset( $args['empty'] ) ) {
			$body = '<tr><td class="bui-table__empty" colspan="' . count( $columns ) . '">' . Html::esc( $args['empty'] ) . '</td></tr>';
		}
		$table = Html::tag(
			'table',
			array(
				'class' => array(
					'bui-table',
					! isset( $args['stack'] ) || $args['stack'] ? 'bui-table--stack' : '',
					! empty( $args['striped'] ) ? 'bui-table--striped' : '',
				),
			),
			( ! empty( $args['caption'] ) ? '<caption class="bui-sr-only">' . Html::esc( $args['caption'] ) . '</caption>' : '' )
			. '<thead><tr>' . $head . '</tr></thead>'
			. Html::tag( 'tbody', array( 'id' => isset( $args['body_id'] ) ? $args['body_id'] : null ), $body )
		);
		return Html::tag( 'div', self::attrs( $args, 'bui-table-wrapper' ), $table );
	}

	/**
	 * Modal dialog on the native dialog element. Open it with `data-bui-modal-open="{id}"` or `BoatUI.modal.open(id)`.
	 *
	 * @param array<string, mixed> $args `id` (required), `title`, `content` (trusted HTML), `footer` (trusted HTML), `size` (default|large),
	 *                                   `position` (center|top-center), `class`, `attrs`.
	 * @return string
	 */
	public static function modal( array $args ) {
		$id       = $args['id'];
		$title_id = $id . '__title';
		$attrs    = self::attrs(
			$args,
			array(
				'bui-modal',
				isset( $args['size'] ) && 'large' === $args['size'] ? 'bui-modal--large' : '',
				isset( $args['position'] ) && 'top-center' === $args['position'] ? 'bui-modal--top-center' : '',
			)
		);

		$attrs['aria-labelledby'] = ! empty( $args['title'] ) ? $title_id : null;
		$panel                    = '<div class="bui-modal__close"><button type="button" class="bui-modal__close-button" data-bui-modal-close><span class="bui-sr-only">' . Html::esc( Strings::get( 'close' ) ) . '</span>' . Icon::render( 'x' ) . '</button></div>';
		$panel                   .= ! empty( $args['title'] ) ? self::title(
			$args['title'],
			2,
			array(
				'id'    => $title_id,
				'class' => 'bui-modal__title',
			)
		) : '';
		$panel                   .= '<div class="bui-modal__description">' . Html::render( isset( $args['content'] ) ? $args['content'] : '' ) . '</div>';
		$panel                   .= ! empty( $args['footer'] ) ? '<div class="bui-modal__footer">' . Html::render( $args['footer'] ) . '</div>' : '';
		return Html::tag( 'dialog', $attrs, '<div class="bui-modal__layout"><div class="bui-modal__panel">' . $panel . '</div></div>' );
	}

	// Internals.

	/**
	 * Common attributes: merges base classes with `class`, copies `id` and `attrs`.
	 *
	 * @param array<string, mixed> $args    Arguments.
	 * @param string|array         $classes Base classes.
	 * @param string[]             $skip    Keys not copied to the wrapper.
	 * @return array<string, mixed>
	 */
	private static function attrs( array $args, $classes, array $skip = array() ) {
		$attrs = isset( $args['attrs'] ) && is_array( $args['attrs'] ) ? $args['attrs'] : array();
		if ( isset( $args['id'] ) && ! in_array( 'id', $skip, true ) ) {
			$attrs['id'] = $args['id'];
		}
		$attrs['class'] = Html::classes( $classes, isset( $args['class'] ) ? $args['class'] : '', isset( $attrs['class'] ) ? $attrs['class'] : '' );
		return $attrs;
	}

	/**
	 * Field defaults.
	 *
	 * @param array<string, mixed> $args   Arguments.
	 * @param string               $prefix Id prefix.
	 * @return array<string, mixed>
	 */
	private static function field_defaults( array $args, $prefix ) {
		if ( empty( $args['id'] ) ) {
			$args['id'] = Html::id( $prefix );
		}
		return $args;
	}

	/**
	 * Description ids for a field.
	 *
	 * @param array<string, mixed> $args Arguments.
	 * @return string|null
	 */
	private static function described_by( array $args ) {
		$ids = array();
		if ( ! empty( $args['validation']['message'] ) ) {
			$ids[] = $args['id'] . '__validation';
		}
		if ( ! empty( $args['description'] ) ) {
			$ids[] = $args['id'] . '__description';
		}
		return $ids ? implode( ' ', $ids ) : null;
	}

	/**
	 * Invalid state for a field.
	 *
	 * @param array<string, mixed> $args Arguments.
	 * @return string|null
	 */
	private static function invalid( array $args ) {
		return isset( $args['validation']['variant'] ) && 'error' === $args['validation']['variant'] ? 'true' : null;
	}

	/**
	 * Field wrapper with label, validation and description.
	 *
	 * @param array<string, mixed> $args  Arguments.
	 * @param string               $input Control markup.
	 * @param string               $wrapper_class Wrapper class.
	 * @return string
	 */
	private static function field( array $args, $input, $wrapper_class ) {
		$label = '';
		if ( isset( $args['label'] ) && '' !== $args['label'] ) {
			$label = '<div class="bui-field__label-row"><label class="bui-label" for="' . Html::esc_attr( $args['id'] ) . '">' . Html::esc( $args['label'] )
				. ( ! empty( $args['required'] ) ? '<span class="bui-required" aria-hidden="true">*</span>' : '' ) . '</label>'
				. ( isset( $args['label_suffix'] ) ? Html::render( $args['label_suffix'] ) : '' ) . '</div>';
		}
		$variant = isset( $args['validation']['variant'] ) ? $args['validation']['variant'] : '';
		$icons   = array(
			'error'   => 'solid:exclamation-circle',
			'warning' => 'solid:exclamation',
			'success' => 'solid:check-circle',
			'info'    => 'solid:information-circle',
		);
		$control = '<div class="bui-validation-input' . ( $variant ? ' bui-validation-input--' . Html::esc_attr( $variant ) : '' ) . '">' . $input
			. ( $variant && isset( $icons[ $variant ] ) ? Icon::render( $icons[ $variant ], array( 'class' => 'bui-validation-input__icon bui-validation-icon--' . $variant ) ) : '' ) . '</div>';
		$message = ! empty( $args['validation']['message'] )
			? '<p id="' . Html::esc_attr( $args['id'] . '__validation' ) . '" class="bui-validation-message bui-validation-message--' . Html::esc_attr( $variant ) . '">' . Html::esc( $args['validation']['message'] ) . '</p>'
			: '';
		$desc    = ! empty( $args['description'] ) ? '<div id="' . Html::esc_attr( $args['id'] . '__description' ) . '" class="bui-field__description">' . Html::render( $args['description'] ) . '</div>' : '';
		return Html::tag( 'div', self::attrs( $args, array( 'bui-field', $wrapper_class, ! empty( $args['disabled'] ) ? 'bui-field--disabled' : '' ), array( 'id' ) ), $label . $control . $message . $desc );
	}

	/**
	 * Option element.
	 *
	 * @param mixed    $value    Value.
	 * @param string   $label    Label.
	 * @param string[] $selected Selected values.
	 * @return string
	 */
	private static function option( $value, $label, array $selected ) {
		return Html::tag(
			'option',
			array(
				'value'    => (string) $value,
				'selected' => in_array( (string) $value, $selected, true ),
			),
			Html::esc( $label )
		);
	}

	/**
	 * Checkbox or radio with label.
	 *
	 * @param string               $type checkbox|radio.
	 * @param array<string, mixed> $args Arguments.
	 * @return string
	 */
	private static function choice( $type, array $args ) {
		$id      = isset( $args['id'] ) ? $args['id'] : Html::id( 'bui-' . $type );
		$variant = isset( $args['variant'] ) ? $args['variant'] : 'default';
		$desc_id = ! empty( $args['description'] ) ? $id . '__description' : null;
		$input   = Html::tag(
			'input',
			array_merge(
				array(
					'type'             => $type,
					'id'               => $id,
					'class'            => 'bui-' . $type . '__input',
					'name'             => isset( $args['name'] ) ? $args['name'] : null,
					'value'            => isset( $args['value'] ) ? (string) $args['value'] : '1',
					'checked'          => ! empty( $args['checked'] ),
					'disabled'         => ! empty( $args['disabled'] ),
					'required'         => ! empty( $args['required'] ),
					'aria-label'       => isset( $args['aria_label'] ) ? $args['aria_label'] : null,
					'aria-describedby' => $desc_id,
				),
				isset( $args['input_attrs'] ) ? (array) $args['input_attrs'] : array()
			),
			null
		);
		$text    = isset( $args['label_html'] ) ? Html::render( $args['label_html'] ) : Html::esc( isset( $args['label'] ) ? $args['label'] : '' );
		$label   = '<label class="bui-label bui-' . $type . '__label" for="' . Html::esc_attr( $id ) . '">' . $text
			. ( $desc_id ? '<span class="bui-checkbox__description" id="' . Html::esc_attr( $desc_id ) . '">' . Html::esc( $args['description'] ) . '</span>' : '' ) . '</label>';
		if ( 'radio' === $type && 'inline-block' === $variant ) {
			$label = '<span class="bui-radio__content">' . $label . Icon::render( 'solid:check-circle', array( 'class' => 'bui-radio__check' ) ) . '</span>';
		}
		return Html::tag(
			'div',
			self::attrs(
				$args,
				array(
					'bui-' . $type,
					'default' !== $variant ? 'bui-' . $type . '--' . $variant : '',
					! empty( $args['disabled'] ) ? 'bui-' . $type . '--disabled' : '',
				),
				array( 'id' )
			),
			$input . $label
		);
	}

	/**
	 * Checkbox or radio group.
	 *
	 * @param string               $type checkbox|radio.
	 * @param array<string, mixed> $args Arguments.
	 * @return string
	 */
	private static function choice_group( $type, array $args ) {
		$id      = isset( $args['id'] ) ? $args['id'] : Html::id( 'bui-' . $type . '-group' );
		$variant = isset( $args['variant'] ) ? $args['variant'] : 'default';
		$values  = array_map( 'strval', isset( $args['value'] ) ? (array) $args['value'] : array() );
		$name    = isset( $args['name'] ) ? $args['name'] : $id;
		$options = '';
		$index   = 0;
		foreach ( isset( $args['options'] ) ? (array) $args['options'] : array() as $value => $label ) {
			$options .= self::choice(
				$type,
				array(
					'id'         => $id . '-' . $index,
					'name'       => 'checkbox' === $type ? $name . '[]' : $name,
					'value'      => $value,
					'label'      => $label,
					'aria_label' => isset( $args['aria_labels'][ $value ] ) ? $args['aria_labels'][ $value ] : null,
					'checked'    => in_array( (string) $value, $values, true ),
					'disabled'   => ! empty( $args['disabled'] ),
					'variant'    => $variant,
				)
			);
			++$index;
		}
		$legend = isset( $args['label'] ) ? '<legend class="bui-label bui-' . $type . '-group__label">' . Html::esc( $args['label'] ) . '</legend>' : '';
		$desc   = ! empty( $args['description'] ) ? '<div class="bui-field__description bui-' . $type . '-group__description">' . Html::render( $args['description'] ) . '</div>' : '';
		$attrs  = self::attrs( $args, array( 'bui-' . $type . '-group', 'default' !== $variant ? 'bui-' . $type . '-group--' . $variant : '' ) );
		return Html::tag( 'fieldset', $attrs, $legend . '<div class="bui-' . $type . '-group__options">' . $options . '</div>' . $desc );
	}
}
