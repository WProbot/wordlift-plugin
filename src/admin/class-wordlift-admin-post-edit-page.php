<?php
/**
 * Pages: Post Edit Page.
 *
 * A 'ghost' page which loads additional scripts and style for the post edit page.
 *
 * @since      3.11.0
 * @package    Wordlift
 * @subpackage Wordlift/admin
 */

use Wordlift\FAQ\Faq_Content_Filter;
use Wordlift\FAQ\FAQ_Metabox;
use Wordlift\Scripts\Scripts_Helper;

/**
 * Define the {@link Wordlift_Admin_Post_Edit_Page} page.
 *
 * @since      3.11.0
 * @package    Wordlift
 * @subpackage Wordlift/admin
 */
class Wordlift_Admin_Post_Edit_Page {

	const GUTENBERG = 'gutenberg';
	const TINY_MCE = 'tiny_mce';

	/**
	 * The {@link Wordlift} plugin instance.
	 *
	 * @since 3.11.0
	 *
	 * @var \Wordlift $plugin The {@link Wordlift} plugin instance.
	 */
	private $plugin;

	/**
	 * A {@link Wordlift_Log_Service} instance.
	 *
	 * @since 3.15.4
	 *
	 * @var \Wordlift_Log_Service $log A {@link Wordlift_Log_Service} instance.
	 */
	private $log;

	/**
	 * Create the {@link Wordlift_Admin_Post_Edit_Page} instance.
	 *
	 * @param \Wordlift $plugin The {@link Wordlift} plugin instance.
	 *
	 * @since 3.11.0
	 *
	 */
	function __construct( $plugin ) {

		$this->log = Wordlift_Log_Service::get_logger( get_class() );

		// Bail out if we're in the UX Builder editor.
		if ( $this->is_ux_builder_editor() ) {
			$this->log->info( 'WordLift will not show, since we are in UX Builder editor.' );

			return;
		}

		// Define the callbacks.
		$callback                  = array( $this, 'enqueue_scripts', );

		// Set a hook to enqueue scripts only when the edit page is displayed.
		add_action( 'admin_print_scripts-post.php', $callback );
		add_action( 'admin_print_scripts-post-new.php', $callback );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_scripts_gutenberg', ) );

		$this->plugin = $plugin;
	}

	/**
	 * Check whether the current post opens with G'berg or not.
	 *
	 * @return bool True if G'berg is used otherwise false.
	 * @since 3.22.3
	 */
	function is_gutenberg_page() {
		if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
			// The Gutenberg plugin is on.
			return true;
		}

		$current_screen = get_current_screen();
		if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			// Gutenberg page on 5+.
			return true;
		}

		return false;
	}

	/**
	 * Check if we're in UX builder.
	 *
	 * @see   https://github.com/insideout10/wordlift-plugin/issues/691
	 *
	 * @since 3.15.4
	 *
	 * @return bool True if we're in UX builder, otherwise false.
	 */
	private function is_ux_builder_editor() {

		return function_exists( 'ux_builder_is_editor' )
		       && ux_builder_is_editor();
	}

	/**
	 * Enqueue scripts and styles for the edit page.
	 *
	 * @since 3.11.0
	 */
	public function enqueue_scripts() {

		// Bail out if this is G'berg.
		if ( $this->is_gutenberg_page() ) {
			return;
		}

		// Dequeue potentially conflicting ontrapages angular scripts which any *are not* used on the edit screen.
		//
		// @see https://github.com/insideout10/wordlift-plugin/issues/832
		wp_dequeue_script( 'ontrapagesAngular' );
		wp_dequeue_script( 'ontrapagesApp' );
		wp_dequeue_script( 'ontrapagesController' );

		// If Gutenberg is enabled for the post, do not load the legacy edit.js.
		if ( function_exists( 'use_block_editor_for_post' ) && use_block_editor_for_post( get_post() ) ) {
			return;
		}


		/*
		 * Enqueue the edit screen JavaScript. The `wordlift-admin.bundle.js` file
		 * is scheduled to replace the older `wordlift-admin.min.js` once client-side
		 * code is properly refactored.
		 *
		 * @link https://github.com/insideout10/wordlift-plugin/issues/761
		 *
		 * @since 3.20.0 edit.js has been migrated to the new webpack configuration.
		 */
		// plugin_dir_url( __FILE__ ) . 'js/1/edit.js'
		$script_name = plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/edit';

		/**
		 * Scripts_Helper introduced.
		 *
		 * @since 3.25.0 Scripts are loaded using script helper to ensure WP 4.4 compatibiility.
		 */
		Scripts_Helper::enqueue_based_on_wordpress_version(
			'wordlift-admin-edit-page',
			$script_name,
			array(
				$this->plugin->get_plugin_name(),
				'jquery',
				// Require wp.ajax.
				'wp-util',
				// @@todo: provide the following dependencies when we're in WP < 5.0 (i.e. when these dependencies aren't already defined).
				'react',
				'react-dom',
				'wp-element',
				'wp-polyfill',
				/*
				* Angular isn't loaded anymore remotely, but it is loaded within wordlift-reloaded.js.
				*
				* See https://github.com/insideout10/wordlift-plugin/issues/865.
				*
				* @since 3.19.6
				*/
				//				// Require Angular.
				//				'wl-angular',
				//				'wl-angular-geolocation',
				//				'wl-angular-touch',
				//				'wl-angular-animate',
				/**
				 * We need the `wp.hooks` global to allow the edit.js script to send actions.
				 *
				 * @since 3.23.0
				 */
				'wp-hooks',
			)
		);

		wp_enqueue_style( 'wordlift-admin-edit-page', "$script_name.css", array(), $this->plugin->get_version() );
		// This method is reached only if editor is classic editor.
		$this->load_faq_settings( self::TINY_MCE );

	}

	/**
	 * Load FAQ settings to the add/edit post page
	 *
	 * @param $editor string specifying which text editor needed to be used.
	 */
	private function load_faq_settings( $editor ) {
		// Register the api endpoints.
		// Enqueue the FAQ style
		wp_enqueue_style(
			'faq-metabox-style',
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/faq.css',
			array()
		);
		Scripts_Helper::enqueue_based_on_wordpress_version(
			'faq-metabox-script',
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/faq',
			array('wp-polyfill'),
			true
		);

		if ( $editor === self::GUTENBERG ) {
			Scripts_Helper::enqueue_based_on_wordpress_version(
				'faq-gutenberg-plugin',
				plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/gutenberg-faq-plugin',
				array('wp-polyfill'),
				true
			);
		}

		wp_localize_script('faq-metabox-script', '_wlFaqSettings', array(
			'restUrl' => get_rest_url( null, WL_REST_ROUTE_DEFAULT_NAMESPACE.'/faq' ),
			'textEditor' => $editor,
			'listBoxId' => FAQ_Metabox::FAQ_LIST_BOX_ID,
			'modalId' => FAQ_Metabox::FAQ_MODAL_ID,
			'addQuestionText' => __( 'Add', 'wordlift' ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'postId' => get_the_ID(),
			// Use this hightlight class to highlight question and answer.
			'faqHighlightClass' => Faq_Content_Filter::FAQ_HIGHLIGHT_CLASS_NAME
		));
		// Load the FAQ meta box.
		new FAQ_Metabox();
	}

	/**
	 * Enqueue scripts and styles for the gutenberg edit page.
	 *
	 * @since 3.21.0
	 */
	public function enqueue_scripts_gutenberg() {

		wp_register_script(
			'wl-block-editor',
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/block-editor.js',
			array(
				'react',
				'wordlift',
				'wp-hooks',
				'wp-data',
				'wp-rich-text',
				'wp-blocks',
				'wp-plugins',
				'wp-edit-post',
			),
			$this->plugin->get_version()
		);
		wp_localize_script( 'wl-block-editor', '_wlBlockEditorSettings', array(
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );

		wp_enqueue_style(
			'wl-block-editor',
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/block-editor.css',
			array(),
			$this->plugin->get_version()
		);

		wp_enqueue_script(
			'wl-autocomplete-select',
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/autocomplete-select.js',
			array(),
			$this->plugin->get_version(),
			true
		);

		wp_enqueue_style(
			'wl-autocomplete-select',
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/dist/autocomplete-select.css',
			array(),
			$this->plugin->get_version()
		);

		// Load FAQ settings.
		$this->load_faq_settings(self::GUTENBERG);
	}

}
