<?php
/**
 * Shortcodes: Glossary Shortcode.
 *
 * `wl_vocabulary` implementation.
 *
 * @since      3.16.0
 * @package    Wordlift
 * @subpackage Wordlift/includes
 */

/**
 * Define the {@link Wordlift_Glossary_Shortcode} class.
 *
 * @since      3.16.0
 * @package    Wordlift
 * @subpackage Wordlift/includes
 */
class Wordlift_Vocabulary_Shortcode extends Wordlift_Shortcode {

	/**
	 * The shortcode.
	 *
	 * @since  3.17.0
	 */
	const SHORTCODE = 'wl_vocabulary';

	/**
	 * The {@link Wordlift_Configuration_Service} instance.
	 *
	 * @since  3.11.0
	 * @access private
	 * @var \Wordlift_Configuration_Service $configuration_service The {@link Wordlift_Configuration_Service} instance.
	 */
	private $configuration_service;

	/**
	 * A {@link Wordlift_Log_Service} instance.
	 *
	 * @since  3.17.0
	 * @access private
	 * @var \Wordlift_Log_Service $log A {@link Wordlift_Log_Service} instance.
	 */
	private $log;

	/**
	 * Create a {@link Wordlift_Glossary_Shortcode} instance.
	 *
	 * @since 3.16.0
	 *
	 * @param \Wordlift_Configuration_Service $configuration_service The {@link Wordlift_Configuration_Service} instance.
	 */
	public function __construct( $configuration_service ) {
		parent::__construct();

		$this->log = Wordlift_Log_Service::get_logger( get_class() );

		$this->configuration_service = $configuration_service;

	}

	/**
	 * Check whether the requirements for this shortcode to work are available.
	 *
	 * @since 3.17.0
	 * @return bool True if the requirements are satisfied otherwise false.
	 */
	private static function are_requirements_satisfied() {

		return function_exists( 'mb_strlen' ) &&
			   function_exists( 'mb_substr' ) &&
			   function_exists( 'mb_convert_case' );
	}

	/**
	 * Render the shortcode.
	 *
	 * @since 3.16.0
	 *
	 * @param array $atts An array of shortcode attributes as set by the editor.
	 *
	 * @return string The output html code.
	 */
	public function render( $atts ) {

		// Bail out if the requirements aren't satisfied: we need mbstring for
		// the vocabulary widget to work.
		if ( ! self::are_requirements_satisfied() ) {
			$this->log->warn( "The vocabulary widget cannot be displayed because this WordPress installation doesn't satisfy its requirements." );

			return '';
		}

		wp_enqueue_style( 'wl-vocabulary-shortcode', dirname( plugin_dir_url( __FILE__ ) ) . '/public/css/wordlift-vocabulary-shortcode.css' );

		// Extract attributes and set default values.
		$atts = shortcode_atts( array(
			// The entity type, such as `person`, `organization`, ...
			'type'         => 'all',
			// Limit the number of posts to 100 by default. Use -1 to remove the limit.
			'limit'        => 100,
			// Sort by title.
			'orderby'      => 'title',
		), $atts );

		// Get the posts. Note that if a `type` is specified before, then the
		// `tax_query` from the `add_criterias` call isn't added.
		$posts = $this->get_posts( $atts );

		// Get the alphabet.
		$language_code = $this->configuration_service->get_language_code();
		$alphabet      = Wordlift_Alphabet_Service::get( $language_code );

		// Add posts to the alphabet.
		foreach ( $posts as $post ) {
			$this->add_to_alphabet( $alphabet, $post->ID );
		}

		// Generate the header.
		$header = array_reduce( array_keys( $alphabet ), function ( $carry, $item ) use ( $alphabet ) {
			$template = ( 0 === count( $alphabet[ $item ] )
				? '<span class="wl-vocabulary-widget-disabled">%s</span>'
				: '<a href="#wl-vocabulary-widget-%2$s">%1$s</a>' );

			return $carry . sprintf( $template, esc_html( $item ), esc_attr( $item ) );
		}, '' );

		// Generate the sections.
		$that     = $this;
		$sections = array_reduce( array_keys( $alphabet ), function ( $carry, $item ) use ( $alphabet, $that ) {
			return $carry . $that->get_section( $item, $alphabet[ $item ] );
		}, '' );

		// Return HTML template.
		return "
<div class='wl-vocabulary'>
	<nav class='wl-vocabulary-alphabet-nav'>
		$header
	</nav>
	<div class='wl-vocabulary-grid'>
		$sections
	</div>
</div>
		";

	}

	/**
	 * Generate the html code for the section.
	 *
	 * @since 3.17.0
	 *
	 * @param string $letter The section's letter.
	 * @param array  $posts  An array of `$post_id => $post_title` associated with
	 *                       the section.
	 *
	 * @return string The section html code (or an empty string if the section has
	 *                no posts).
	 */
	private function get_section( $letter, $posts ) {

		// Return an empty string if there are no posts.
		if ( 0 === count( $posts ) ) {
			return '';
		}

		return sprintf( '
			<div class="wl-vocabulary-letter-block" id="wl-vocabulary-widget-%s">
				<aside class="wl-vocabulary-left-column">%s</aside>
				<div class="wl-vocabulary-right-column">
					<ul class="wl-vocabulary-items-list">
						%s
					</ul>
				</div>
			</div>
		', esc_attr( $letter ), esc_html( $letter ), $this->format_posts_as_list( $posts ) );
	}

	/**
	 * Format an array post `$post_id => $post_title` as a list.
	 *
	 * @since 3.17.0
	 *
	 * @param array $posts An array of `$post_id => $post_title` key, value pairs.
	 *
	 * @return string A list.
	 */
	private function format_posts_as_list( $posts ) {

		return array_reduce( array_keys( $posts ), function ( $carry, $item ) use ( $posts ) {
			return $carry . sprintf( '<li><a href="%s">%s</a></li>', esc_attr( get_permalink( $item ) ), esc_html( $posts[ $item ] ) );
		}, '' );
	}

	/**
	 * Get the posts from WordPress using the provided attributes.
	 *
	 * @since 3.17.0
	 *
	 * @param array $atts The shortcode attributes.
	 *
	 * @return array An array of {@link WP_Post}s.
	 */
	private function get_posts( $atts ) {

		// The default arguments for the query.
		$args = array(
			'numberposts'            => intval( $atts['limit'] ),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			// Exclude the publisher.
			'post__not_in' => array( $this->configuration_service->get_publisher_id() ),
		);

		// Limit the based entity type if needed.
		if ( 'all' !== $atts['type'] ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => Wordlift_Entity_Types_Taxonomy_Service::TAXONOMY_NAME,
					'field'    => 'slug',
					'terms'    => $atts['type'],
				),
			);
		}

		// Get the posts. Note that if a `type` is specified before, then the
		// `tax_query` from the `add_criterias` call isn't added.
		return get_posts( Wordlift_Entity_Service::add_criterias( $args ) );

	}

	/**
	 * Populate the alphabet with posts.
	 *
	 * @since 3.17.0
	 *
	 * @param array $alphabet An array of letters.
	 * @param int   $post_id  The {@link WP_Post} id.
	 */
	private function add_to_alphabet( &$alphabet, $post_id ) {

		// Get the title without accents.
		$title = remove_accents( get_the_title( $post_id ) );

		// Get the initial letter.
		$letter = $this->get_first_letter_in_alphabet_or_hash( $alphabet, $title );

		// Add the post.
		$alphabet[ $letter ][ $post_id ] = $title;

	}

	/**
	 * Find the first letter in the alphabet.
	 *
	 * In some alphabets a letter is a compound of letters, therefore this function
	 * will look for groups of 2 or 3 letters in the alphabet before looking for a
	 * single letter. In case the letter is not found a # (hash) key is returned.
	 *
	 * @since 3.17.0
	 *
	 * @param array  $alphabet An array of alphabet letters.
	 * @param string $title    The title to match.
	 *
	 * @return string The initial letter or a `#` key.
	 */
	private function get_first_letter_in_alphabet_or_hash( $alphabet, $title ) {

		// Need to handle letters which consist of 3 and 2 characters.
		for ( $i = 3; $i > 0; $i -- ) {
			$letter = mb_convert_case( mb_substr( $title, 0, $i ), MB_CASE_UPPER );
			if ( isset( $alphabet[ $letter ] ) ) {
				return $letter;
			}
		}

		return '#';
	}

}
