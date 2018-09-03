<?php
/**
 * Services: Entity Type Service.
 *
 * Define the Entity Type Service.
 *
 * @since      3.7.0
 * @package    Wordlift
 * @subpackage Wordlift/includes
 */

/**
 * The Wordlift_Entity_Type_Service provides functions to manipulate an entity
 * type.
 *
 * @since      3.7.0
 * @package    Wordlift
 * @subpackage Wordlift/includes
 */
class Wordlift_Entity_Type_Service {

	/**
	 * The {@link Wordlift_Schema_Service} instance.
	 *
	 * @since  3.7.0
	 * @access private
	 * @var \Wordlift_Schema_Service $schema_service The {@link Wordlift_Schema_Service} instance.
	 */
	private $schema_service;

	/**
	 * A {@link Wordlift_Log_Service} instance.
	 *
	 * @since  3.8.0
	 * @access private
	 * @var \Wordlift_Log_Service $log A {@link Wordlift_Log_Service} instance.
	 */
	private $log;

	/**
	 * The {@link Wordlift_Entity_Type_Service} singleton instance.
	 *
	 * @since  3.7.0
	 * @access private
	 * @var \Wordlift_Entity_Type_Service $instance The {@link Wordlift_Entity_Type_Service} singleton instance.
	 */
	private static $instance;

	/**
	 * Wordlift_Entity_Type_Service constructor.
	 *
	 * @since 3.7.0
	 *
	 * @param \Wordlift_Schema_Service $schema_service The {@link Wordlift_Schema_Service} instance.
	 */
	public function __construct( $schema_service ) {

		$this->log = Wordlift_Log_Service::get_logger( 'Wordlift_Entity_Type_Service' );

		$this->schema_service = $schema_service;

		self::$instance = $this;

	}

	/**
	 * Get the {@link Wordlift_Entity_Type_Service} singleton instance.
	 *
	 * @since 3.7.0
	 * @return \Wordlift_Entity_Type_Service The {@link Wordlift_Entity_Type_Service} singleton instance.
	 */
	public static function get_instance() {

		return self::$instance;
	}

	/**
	 * Get the types associated with the specified entity post id.
	 *
	 * We have a strategy to define the entity type, given that everything is
	 * an entity, i.e. also posts/pages and custom post types.
	 *
	 * @since 3.20.0 This function will **not** return entity types introduced with 3.20.0.
	 *
	 * @since 3.18.0 The cases are the following:
	 *  1. the post has a term from the Entity Types Taxonomy: the term defines
	 *     the entity type, e.g. Organization, Person, ...
	 *  2. the post doesn't have a term from the Entity Types Taxonomy:
	 *      a) the post is a `wl_entity` custom post type, then the post is
	 *           assigned the `Thing` entity type by default.
	 *      b) the post is a `post` post type, then the post is
	 *           assigned the `Article` entity type by default.
	 *      c) the post is a custom post type then it is
	 *          assigned the `WebPage` entity type by default.
	 *
	 * @since 3.7.0
	 *
	 * @param int $post_id The post id.
	 *
	 * @return array|null {
	 * An array of type properties or null if no term is associated
	 *
	 * @type string css_class     The css class, e.g. `wl-thing`.
	 * @type string uri           The schema.org class URI, e.g. `http://schema.org/Thing`.
	 * @type array  same_as       An array of same as attributes.
	 * @type array  custom_fields An array of custom fields.
	 * @type array  linked_data   An array of {@link Wordlift_Sparql_Tuple_Rendition}.
	 * }
	 */
	public function get( $post_id ) {

		$this->log->trace( "Getting the post type for post $post_id..." );

		// Get the post type.
		$post_type = get_post_type( $post_id );

		// Return `web-page` for non entities.
		if ( ! self::is_valid_entity_post_type( $post_type ) ) {
			$this->log->info( "Returning `web-page` for post $post_id." );

			return $this->schema_service->get_schema( 'web-page' );
		}

		// Get the type from the associated classification.
		$terms = wp_get_object_terms( $post_id, Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME );

		// Return the schema type if there is a term found.
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			// Cycle through the terms and return the first one with a valid schema.
			foreach ( $terms as $term ) {
				$this->log->debug( "Found `{$term->slug}` term for post $post_id." );

				// Try to get the schema for the term.
				$schema = $this->schema_service->get_schema( $term->slug );

				// If found, return it, ignoring the other types.
				if ( null !== $schema ) {
					// Return the entity type with the specified id.
					return $schema;
				}
			}
		}

		// If it's a page or post return `Article`.
		if ( in_array( $post_type, array( 'post', 'page' ) ) ) {
			$this->log->debug( "Post $post_id has no terms, and it's a `post` type, returning `Article`." );

			// Return "Article" schema type for posts.
			return $this->schema_service->get_schema( 'article' );
		}

		// Return "Thing" schema type for entities.
		$this->log->debug( "Post $post_id has no terms, but it's a `wl_entity` type, returning `Thing`." );

		// Return the entity type with the specified id.
		return $this->schema_service->get_schema( 'thing' );

	}

	/**
	 * Get the term ids of the entity types associated to the specified post.
	 *
	 * @since 3.20.0
	 *
	 * @param int $post_id The post id.
	 *
	 * @return array|WP_Error An array of entity types ids or a {@link WP_Error}.
	 */
	public function get_ids( $post_id ) {

		return wp_get_object_terms( $post_id, Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME, array( 'fields' => 'ids', ) );
	}

	/**
	 * Get the camel case names of the entity types associated to the specified post.
	 *
	 * @since 3.20.0
	 *
	 * @param int $post_id The post id.
	 *
	 * @return array|WP_Error An array of entity types camel case names or a {@link WP_Error}.
	 */
	public function get_names( $post_id ) {

		$ids = $this->get_ids( $post_id );

		$camel_case_names = array_map( function ( $id ) {
			return get_term_meta( $id, '_wl_name', true );
		}, $ids );

		return $camel_case_names;
	}

	/**
	 * Set the main type for the specified entity post, given the type URI.
	 *
	 * @since 3.8.0
	 *
	 * @param int    $post_id The post id.
	 * @param string $type_uri The type URI.
	 * @param bool   $replace Whether the provided type must replace the existing types, by default `true`.
	 */
	public function set( $post_id, $type_uri, $replace = true ) {

		// If the type URI is empty we remove the type.
		if ( empty( $type_uri ) ) {
			$this->log->debug( "Removing entity type for post $post_id..." );

			wp_set_object_terms( $post_id, null, Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME );

			return;
		}

		$this->log->debug( "Setting entity type for post $post_id..." );

		// if the `$type_uri` starts with `wl-`, we're looking at the class name, which is `wl-` + slug.
		$term = ( 0 === strpos( $type_uri, 'wl-' ) )
			// Get term by slug.
			? $this->get_term_by_slug( substr( $type_uri, 3 ) )
			// Get term by URI.
			: $this->get_term_by_uri( $type_uri );

		if ( false === $term ) {
			$this->log->warn( "No term found for URI $type_uri." );

			return;
		}

		$this->log->debug( "Setting entity type [ post id :: $post_id ][ term id :: $term->term_id ][ term slug :: $term->slug ][ type uri :: $type_uri ]..." );

		// `$replace` is passed to decide whether to replace or append the term.
		wp_set_object_terms( $post_id, $term->term_id, Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME, ! $replace );

	}

	/**
	 * Get an entity type term given its slug.
	 *
	 * @since 3.20.0
	 *
	 * @param string $slug The slug.
	 *
	 * @return false|WP_Term WP_Term instance on success. Will return false if `$taxonomy` does not exist
	 *                             or `$term` was not found.
	 */
	private function get_term_by_slug( $slug ) {

		return get_term_by( 'slug', $slug, Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME );
	}

	/**
	 * Get an entity type term given its URI.
	 *
	 * @since 3.20.0
	 *
	 * @param string $uri The uri.
	 *
	 * @return false|WP_Term WP_Term instance on success. Will return false if `$taxonomy` does not exist
	 *                             or `$term` was not found.
	 */
	private function get_term_by_uri( $uri ) {

		$terms = get_terms( Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME, array(
			'fields'     => 'all',
			'get'        => 'all',
			'number'     => 1,
			'meta_query' => array(
				array(
					// Don't use a reference to Wordlift_Schemaorg_Class_Service, unless
					// `WL_ALL_ENTITY_TYPES` is set to true.
					'key'   => '_wl_uri',
					'value' => $uri,
				),
			),
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		) );

		return is_array( $terms ) && ! empty( $terms ) ? $terms[0] : false;
	}

	/**
	 * Check whether an entity type is set for the {@link WP_Post} with the
	 * specified id.
	 *
	 * @since 3.15.0
	 *
	 * @param int    $post_id The {@link WP_Post}'s `id`.
	 * @param string $uri The entity type URI.
	 *
	 * @return bool True if an entity type is set otherwise false.
	 */
	public function has_entity_type( $post_id, $uri = null ) {

		$this->log->debug( "Checking if post $post_id has an entity type [ $uri ]..." );

		// If an URI hasn't been specified just check whether we have at least
		// one entity type.
		if ( null === $uri ) {

			// Get the post terms for the specified post ID.
			$terms = $this->get_post_terms( $post_id );

			$this->log->debug( "Post $post_id has " . count( $terms ) . ' type(s).' );

			// True if there's at least one term bound to the post.
			return ( 0 < count( $terms ) );
		}

		$has_entity_type = ( null !== $this->has_post_term_by_uri( $post_id, $uri ) );

		$this->log->debug( "Post $post_id has $uri type: " . ( $has_entity_type ? 'yes' : 'no' ) );

		// Check whether the post has an entity type with that URI.
		return $has_entity_type;
	}

	/**
	 * Get the list of entity types' terms for the specified {@link WP_Post}.
	 *
	 * @since 3.15.0
	 *
	 * @param int $post_id The {@link WP_Post} id.
	 *
	 * @return array|WP_Error An array of entity types' terms or {@link WP_Error}.
	 */
	private function get_post_terms( $post_id ) {

		return wp_get_object_terms( $post_id, Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME, array(
			'hide_empty' => false,
			// Because of #334 (and the AAM plugin) we changed fields from 'id=>slug' to 'all'.
			// An issue has been opened with the AAM plugin author as well.
			//
			// see https://github.com/insideout10/wordlift-plugin/issues/334
			// see https://wordpress.org/support/topic/idslug-not-working-anymore?replies=1#post-8806863
			'fields'     => 'all',
		) );
	}

	/**
	 * Get an entity type term given its URI.
	 *
	 * @since 3.20.0 function renamed to `has_post_term_by_uri` and return type changed to `bool`.
	 * @since 3.15.0
	 *
	 * @param int    $post_id The {@link WP_Post} id.
	 * @param string $uri The entity type URI.
	 *
	 * @return bool True if the post has that type URI bound to it otherwise false.
	 */
	private function has_post_term_by_uri( $post_id, $uri ) {

		// Get the post terms bound to the specified post.
		$terms = $this->get_post_terms( $post_id );

		// Look for a term if the specified URI.
		foreach ( $terms as $term ) {
			$term_uri = get_term_meta( $term->term_id, '_wl_uri', true );

			if ( $uri === $term_uri ) {
				return true;
			}
		}

		// Return null.
		return false;
	}

	/**
	 * Filter the terms for a given object or objects.
	 *
	 * @since 3.20.0
	 *
	 * @param array $terms An array of terms for the given object or objects.
	 * @param array $object_id_array Array of object IDs for which `$terms` were retrieved.
	 * @param array $taxonomy_array Array of taxonomies from which `$terms` were retrieved.
	 * @param array $args An array of arguments for retrieving terms for the given
	 *                               object(s). See wp_get_object_terms() for details.
	 *
	 * @return array $terms An array of terms for the given object or objects.
	 */
	public function get_object_terms( $terms, $object_id_array, $taxonomy_array, $args ) {

		// Bail out, if the `wl_entity_type` taxonomy is not included.
		if ( ! in_array( Wordlift_Entity_Type_Taxonomy_Service::TAXONOMY_NAME, $taxonomy_array ) ) {
			return $terms;
		}

		// Bail out if dealing with more than one object instance (avoid performance issues).
		if ( 1 < count( $object_id_array ) ) {
			return $terms;
		}

		// Bail out if terms have been found.
		if ( 1 < count( $terms ) ) {
			return $terms;
		}

		// Get the default term for this post.
		$schema_term  = $this->get( $object_id_array[0] );
		$default_term = $this->get_term_by_uri( $schema_term['uri'] );

		/**
		 * Allow 3rd parties to override the default terms.
		 *
		 * @since 3.20.0
		 *
		 * @param array $default_terms The array of default terms.
		 * @param int   $object_id The object id.
		 *
		 * @return array An array of default terms.
		 */
		$default_terms = apply_filters( 'wl_default_entity_types_for_object', array( $default_term ), $object_id_array[0] );

		return $default_terms;
	}


	/**
	 * Determines whether a post type can be used for entities.
	 *
	 * Criteria is that the post type is public. The list of valid post types
	 * can be overridden with a filter.
	 *
	 * @since 3.15.0
	 *
	 * @param string $post_type A post type name.
	 *
	 * @return bool Return true if the post type can be used for entities, otherwise false.
	 */
	public static function is_valid_entity_post_type( $post_type ) {

		return in_array( $post_type, Wordlift_Entity_Service::valid_entity_post_types(), true );
	}

}
