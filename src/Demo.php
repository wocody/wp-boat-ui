<?php
/**
 * Component showcase screen for development.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/**
 * Showcase of every component under Tools → Boat UI.
 *
 * Enabled only when `WOCODY_BOAT_UI_DEMO` is true, e.g. in wp-config.php of a development site.
 */
final class Demo {

	const SLUG = 'wocody-boat-ui-demo';

	/**
	 * Hook the showcase screen.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Add the Tools page.
	 *
	 * @return void
	 */
	public static function menu() {
		add_management_page( 'Boat UI', 'Boat UI', 'manage_options', self::SLUG, array( __CLASS__, 'render' ) );
	}

	/**
	 * Enqueue the assets on the showcase screen.
	 *
	 * @param string $hook Screen hook.
	 * @return void
	 */
	public static function assets( $hook ) {
		if ( 'tools_page_' . self::SLUG === $hook ) {
			UI::enqueue();
		}
	}

	/**
	 * Echo the showcase.
	 *
	 * @return void
	 */
	public static function render() {
		Page::render( self::config() );
	}

	/**
	 * Showcase configuration (also used by tools/preview.php).
	 *
	 * @return array<string, mixed>
	 */
	public static function config() {
		$features = array(
			array(
				'title'    => 'Content tools',
				'features' => array(
					array(
						'icon'        => 'sparkles',
						'title'       => 'Smart suggestions',
						'description' => '<p>Suggestions help you create better content by providing optimization hints that you can apply as you wish.</p>',
						'link'        => array( 'Learn more', '#' ),
						'toggle'      => array(
							'name'    => 'features[suggestions]',
							'checked' => true,
						),
					),
					array(
						'icon'         => 'document-text',
						'icon_variant' => 'muted',
						'title'        => 'llms.txt',
						'description'  => '<p>Generate a file that points to your website\'s most relevant content. Designed to help AI assistants understand your website better.</p>',
						'link'         => array( 'Learn more', '#' ),
						'actions'      => UI::button(
							'Customize llms.txt file',
							array(
								'variant' => 'secondary',
								'size'    => 'small',
							)
						),
						'toggle'       => array( 'name' => 'features[llms]' ),
					),
				),
			),
			array(
				'title'    => 'Site structure',
				'features' => array(
					array(
						'icon'        => 'link',
						'title'       => 'Text link counter',
						'description' => '<p>Count the number of internal links from and to your posts to improve your site structure.</p>',
						'link'        => array( 'Learn more', '#' ),
						'toggle'      => array(
							'name'    => 'features[links]',
							'checked' => true,
						),
					),
					array(
						'icon'        => 'map',
						'title'       => 'XML sitemaps',
						'description' => '<p>Enable the XML sitemaps. A sitemap is a file that lists a website\'s essential pages to make sure search engines can find and crawl them.</p>',
						'link'        => array( 'Learn more', '#' ),
						'actions'     => UI::button(
							'View the XML sitemap',
							array(
								'variant'  => 'secondary',
								'size'     => 'small',
								'href'     => '#',
								'icon_end' => 'external-link',
							)
						),
						'toggle'      => array(
							'name'    => 'features[sitemaps]',
							'checked' => true,
						),
					),
				),
			),
		);

		$basics = UI::section(
			array(
				'title'       => 'Site info',
				'description' => '<p>Set the basic info for your website. You can use <em>tagline</em> and <em>separator</em> as <a href="#">replacement variables</a> when configuring the search appearance of your content.</p>',
				'content'     => array(
					UI::text_field(
						array(
							'id'          => 'demo-website-name',
							'name'        => 'website_name',
							'label'       => 'Website name',
							'placeholder' => 'Brazil Production Services',
						)
					),
					UI::text_field(
						array(
							'id'          => 'demo-alternate-name',
							'name'        => 'alternate_name',
							'label'       => 'Alternate website name',
							'description' => 'Use the alternate website name for acronyms, or a shorter version of your website\'s name.',
						)
					),
					UI::text_field(
						array(
							'id'          => 'demo-tagline',
							'name'        => 'tagline',
							'label'       => 'Tagline',
							'value'       => 'THE #1 FIXER SERVICE FOR FILMING IN BRAZIL',
							'description' => 'This field updates the <a href="#">tagline in your WordPress settings</a>.',
						)
					),
					UI::radio_group(
						array(
							'label'       => 'Title separator',
							'name'        => 'separator',
							'variant'     => 'inline-block',
							'value'       => 'sc-dash',
							'options'     => array(
								'sc-dash'   => '-',
								'sc-ndash'  => '–',
								'sc-mdash'  => '—',
								'sc-colon'  => ':',
								'sc-middot' => '·',
								'sc-bull'   => '•',
								'sc-star'   => '*',
								'sc-smstar' => '⋆',
								'sc-pipe'   => '|',
								'sc-tilde'  => '~',
								'sc-laquo'  => '«',
								'sc-raquo'  => '»',
								'sc-lt'     => '>',
								'sc-gt'     => '<',
							),
							'aria_labels' => array(
								'sc-dash'  => 'Dash',
								'sc-ndash' => 'En dash',
							),
						)
					),
				),
			)
		)
		. UI::divider()
		. UI::section(
			array(
				'title'   => 'Site preferences',
				'content' => array(
					UI::toggle_field(
						array(
							'id'          => 'demo-restrict',
							'name'        => 'restrict',
							'label'       => 'Restrict advanced settings for authors',
							'description' => 'By default only editors and administrators can access the Advanced and Schema section of the sidebar or metabox. Disabling this allows access to all users.',
							'checked'     => true,
						)
					),
					UI::toggle_field(
						array(
							'id'          => 'demo-tracking',
							'name'        => 'tracking',
							'label'       => 'Usage tracking',
							'description' => 'Usage tracking allows us to track some data about your site to improve our plugin. <a href="#">Learn more about which data we track and why.</a>',
						)
					),
				),
			)
		)
		. UI::divider()
		. UI::section(
			array(
				'title'       => 'Policies',
				'description' => '<p>Select the pages on your website which contain information about your organizational and publishing policies.</p>',
				'content'     => array(
					UI::select_field(
						array(
							'id'          => 'demo-policy',
							'name'        => 'policy',
							'label'       => 'Publishing principles',
							'options'     => array(
								''  => 'None',
								'2' => 'About us',
								'3' => 'Editorial guidelines',
							),
							'description' => 'Select a page which describes the editorial principles of your organization.',
							'class'       => 'bui:max-w-sm',
						)
					),
					UI::textarea_field(
						array(
							'id'          => 'demo-notes',
							'name'        => 'notes',
							'label'       => 'Notes',
							'placeholder' => 'Anything else?',
						)
					),
					UI::checkbox_group(
						array(
							'label'   => 'Post types',
							'name'    => 'post_types',
							'value'   => array( 'post' ),
							'options' => array(
								'post' => 'Posts',
								'page' => 'Pages',
							),
						)
					),
					UI::text_field(
						array(
							'id'         => 'demo-invalid',
							'label'      => 'Email',
							'value'      => 'not-an-email',
							'required'   => true,
							'validation' => array(
								'variant' => 'error',
								'message' => 'Enter a valid email address.',
							),
						)
					),
				),
			)
		);

		$components = UI::section(
			array(
				'title'       => 'Buttons',
				'description' => '<p>Primary, secondary, tertiary, error and upsell variants in every size.</p>',
				'fieldset'    => false,
				'content'     => array(
					UI::button_group(
						array(
							UI::button( 'Save changes' ),
							UI::button( 'Discard changes', array( 'variant' => 'secondary' ) ),
							UI::button( 'Tertiary', array( 'variant' => 'tertiary' ) ),
							UI::button( 'Delete', array( 'variant' => 'error' ) ),
							UI::button(
								'Buy now',
								array(
									'variant'  => 'upsell',
									'icon_end' => 'arrow-narrow-right',
								)
							),
						)
					),
					UI::button_group(
						array(
							UI::button( 'Small', array( 'size' => 'small' ) ),
							UI::button( 'Large', array( 'size' => 'large' ) ),
							UI::button( 'Extra large', array( 'size' => 'extra-large' ) ),
							UI::button( 'Loading', array( 'loading' => true ) ),
							UI::button( 'Disabled', array( 'disabled' => true ) ),
						)
					),
				),
			)
		)
		. UI::divider()
		. UI::section(
			array(
				'title'    => 'Feedback',
				'fieldset' => false,
				'content'  => array(
					UI::alert( '<p>Oops! You can\'t see the overview of your insights right now because you\'re in a non-production environment.</p>', 'info' ),
					UI::alert( '<p>Some settings need your attention.</p>', 'warning', array( 'title' => 'Warning' ) ),
					UI::alert( '<p>Your settings were saved.</p>', 'success', array( 'dismissible' => true ) ),
					UI::alert( '<p>The request failed. Check the connection and try again.</p>', 'error' ),
					'<div class="bui:flex bui:flex-wrap bui:gap-2">' . UI::badge( 'Info' ) . UI::badge( 'Premium', 'upsell' ) . UI::badge( 'Plain', 'plain' ) . UI::badge( 'Success', 'success' ) . UI::badge( 'Error', 'error' ) . UI::badge( 'Local SEO', 'primary', array( 'size' => 'small' ) ) . '</div>',
					UI::progress_field( 64, 100, array( 'label' => 'Export progress' ) ),
					UI::button_group(
						array(
							UI::button(
								'Show notification',
								array(
									'variant' => 'secondary',
									'attrs'   => array( 'onclick' => "BoatUI.toast({title:'Settings saved',description:'Your changes are live.'})" ),
								)
							),
							UI::button(
								'Open modal',
								array(
									'variant' => 'secondary',
									'attrs'   => array( 'data-bui-modal-open' => 'demo-modal' ),
								)
							),
							UI::tooltip( 'Tooltips explain a setting in one sentence.' ),
						)
					),
				),
			)
		)
		. UI::divider()
		. UI::section(
			array(
				'title'    => 'Data',
				'fieldset' => false,
				'content'  => array(
					UI::table(
						array(
							'caption' => 'Backups',
							'columns' => array(
								'name'    => 'Backup',
								'created' => 'Created',
								'size'    => 'Size',
								'actions' => array(
									'label' => 'Actions',
									'class' => 'bui:text-end',
								),
							),
							'rows'    => array(
								array(
									'name'    => '<span class="bui-table__strong">site-2026-10-27.wpboat</span>',
									'created' => '27/10/2026 18:40',
									'size'    => '412.08 MiB',
									'actions' => '<div class="bui-table__actions">' . UI::button(
										'Download',
										array(
											'variant' => 'secondary',
											'size'    => 'small',
										)
									) . UI::button(
										'Delete',
										array(
											'variant' => 'error',
											'size'    => 'small',
										)
									) . '</div>',
								),
							),
						)
					),
					UI::file_input(
						array(
							'id'          => 'demo-file',
							'accept'      => '.wpboat',
							'description' => '.wpboat files only',
						)
					),
					UI::disclosure( 'Always excluded', '<ul class="bui:list-disc bui:ms-4"><li>WordPress core and wp-config.php.</li><li>Cache folders.</li></ul>' ),
					'<p>Storage: ' . UI::code( '/var/www/private/backups' ) . '</p>',
				),
			)
		);

		return array(
			'id'           => 'wocody-boat-ui-demo',
			'brand'        => array(
				'name'    => 'boat',
				'product' => 'UI',
				'href'    => '#site-features',
			),
			'router'       => true,
			'mobile_title' => 'Boat UI',
			'navigation'   => array(
				array(
					'label' => 'General',
					'icon'  => 'desktop-computer',
					'items' => array(
						array(
							'label'  => 'Site features',
							'view'   => 'site-features',
							'active' => true,
						),
						array(
							'label' => 'Site basics',
							'view'  => 'site-basics',
						),
						array(
							'label' => 'Components',
							'view'  => 'components',
						),
					),
				),
				array(
					'label' => 'Content types',
					'icon'  => 'newspaper',
					'limit' => 3,
					'items' => array(
						array(
							'label' => 'Homepage',
							'href'  => '#',
						),
						array(
							'label' => 'Posts',
							'href'  => '#',
						),
						array(
							'label' => 'Pages',
							'href'  => '#',
						),
						array(
							'label' => 'Galleries',
							'href'  => '#',
						),
						array(
							'label' => 'Portfolio',
							'href'  => '#',
						),
					),
				),
				array(
					'label' => 'Advanced',
					'icon'  => 'adjustments',
					'open'  => false,
					'items' => array(
						array(
							'label' => 'Crawl optimization',
							'href'  => '#',
						),
						array(
							'label' => 'Breadcrumbs',
							'href'  => '#',
						),
					),
				),
			),
			'content'      => array(
				UI::paper(
					array(
						'as'             => 'main',
						'title'          => 'Site features',
						'description'    => 'Tell us which features you want to use.',
						'header_actions' => UI::button(
							'Collapse all',
							array(
								'variant' => 'secondary',
								'size'    => 'small',
								'icon'    => 'chevron-up',
							)
						),
						'attrs'          => array( 'data-bui-view' => 'site-features' ),
						'content'        => '<form>' . UI::feature_list( $features ) . '</form>',
					)
				),
				'<form data-bui-view="site-basics" hidden>' . UI::paper(
					array(
						'as'           => 'main',
						'title'        => 'Site basics',
						'description'  => 'Configure the basics for your website.',
						'content'      => $basics,
						'dirty_footer' => true,
						'footer'       => UI::button( 'Save changes', array( 'type' => 'submit' ) ) . UI::button(
							'Discard changes',
							array(
								'variant' => 'secondary',
								'type'    => 'reset',
							)
						),
					)
				) . '</form>',
				UI::paper(
					array(
						'as'          => 'main',
						'title'       => 'Components',
						'description' => 'Every Boat UI component in one place.',
						'content'     => $components,
						'attrs'       => array(
							'data-bui-view' => 'components',
							'hidden'        => true,
						),
					)
				),
				UI::modal(
					array(
						'id'      => 'demo-modal',
						'title'   => 'Restore this backup?',
						'content' => '<p>Restoring replaces the destination database and users.</p>',
						'footer'  => UI::button(
							'Cancel',
							array(
								'variant' => 'secondary',
								'attrs'   => array( 'data-bui-modal-close' => true ),
							)
						) . UI::button( 'Restore', array( 'variant' => 'error' ) ),
					)
				),
			),
			'aside'        => array(
				UI::aside_box(
					array(
						'title'   => 'Boat UI',
						'variant' => 'highlight',
						'content' => '<p class="bui:font-medium bui:text-slate-800">One admin interface for every Wocody plugin.</p><p>Shared layout, navigation and form controls.</p>'
							. UI::check_list( array( 'Tailwind CSS v4 utilities with the bui: prefix', 'Server-rendered PHP components', 'No build step in plugins' ) )
							. UI::button(
								'Documentation',
								array(
									'variant'  => 'upsell',
									'size'     => 'large',
									'block'    => true,
									'href'     => 'https://github.com/wocody/wp-boat-ui',
									'icon_end' => 'arrow-narrow-right',
								)
							),
					)
				),
				UI::aside_box(
					array(
						'title'   => 'Support',
						'size'    => 'small',
						'content' => '<p>Questions or bugs? Open an issue on <a href="https://github.com/wocody/wp-boat-ui">GitHub</a>.</p>',
					)
				),
			),
		);
	}
}
