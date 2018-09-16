<?php
/**
 * This file contains support functions for the tests.
 */

// Define the JSON-LD contexts.
define( 'WL_ENHANCER_NAMESPACE', 'a' );
define( 'WL_DUBLIN_CORE_NAMESPACE', 'b' );
define( 'WL_JSON_LD_CONTEXT', serialize( array(
	WL_ENHANCER_NAMESPACE    => 'http://fise.iks-project.eu/ontology/',
	WL_DUBLIN_CORE_NAMESPACE => 'http://purl.org/dc/terms/',
) ) );

// Disable buffering.
define( 'WL_BUFFER_SPARQL_UPDATE_QUERIES', false );

if ( 'true' === getenv( 'WL_SSL_V1_FORCED' ) ) {
	add_action( 'http_api_curl', 'force_curl_ssl_v1' );
	function force_curl_ssl_v1( $handle ) {
		curl_setopt( $handle, CURLOPT_SSLVERSION, 1 );
	}
}

require_once( 'jsonld.php' );

/**
 * Compatibility for WP 4.4.
 *
 * @since 3.19.4
 */
function _wl_test_set_wp_die_handler() {
	global $wp_filter;
	if ( ! class_exists( 'WPDieException' ) ) {
		class WPDieException extends Exception {
		}
	}

	if ( ! function_exists( '_wl_test_wp_die_handler' ) ) {
		function _wl_test_wp_die_handler( $message ) {
			if ( ! is_scalar( $message ) ) {
				$message = '0';
			}

			throw new WPDieException( $message );
		}

	}

	unset( $wp_filter['wp_die_ajax_handler'] );
	add_filter( 'wp_die_ajax_handler', function () {
		return '_wl_test_wp_die_handler';
	});

	unset( $wp_filter['wp_die_handler'] );
	add_filter( 'wp_die_handler', function () {
		return '_wl_test_wp_die_handler';
	});

}

/**
 * Create a new post.
 *
 * @param string $content The post content.
 * @param string $slug    The post slug.
 * @param string $title   The post title.
 * @param string $status  The post status (e.g. draft, publish, pending, private, ...)
 * @param string $type    The post status (e.g. post, page, link, ...)
 *
 * @return int|WP_Error The post ID or a WP_Error instance.
 */
function wl_create_post( $content, $slug, $title, $status = 'draft', $type = 'post' ) {

	$args = array(
		'post_content' => $content,
		'post_name'    => $slug,
		'post_status'  => $status,
		'post_type'    => $type,
		'post_author'  => wl_test_create_user(),
	);

	if ( ! empty( $title ) ) {
		$args['post_title'] = $title;
	}

	$wp_error = null;
	$post_id  = wp_insert_post( $args, $wp_error );

	if ( is_wp_error( $wp_error ) ) {
		return $wp_error;
	}

	return $post_id;
}

/**
 * Delete the post and related attachments with the specified id (it's basically a proxy to wp_delete_post).
 *
 * @param int  $post_id      The post id.
 * @param bool $force_delete Whether to force delete.
 *
 * @return false|WP_Post False on failure and the post object for the deleted post success.
 */
function wl_delete_post( $post_id, $force_delete = false ) {

	// First delete the post attachments.
	wl_delete_post_attachments( $post_id );

	return wp_delete_post( $post_id, $force_delete );
}

/**
 * Delete the attachments related to the specified post.
 *
 * @param $post_id
 */
function wl_delete_post_attachments( $post_id ) {

	// Get all the attachments related to the post.
	$attachments = wl_get_attachments( $post_id );

	// Delete each attachment.
	foreach ( $attachments as $attachment ) {
		if ( false === wp_delete_attachment( $attachment->ID ) ) {
			wl_write_log( "wl_delete_post_attachments : error [ post id :: $post_id ]" );
		}
	}
}

/**
 * Update the content of the post with the specified ID.
 *
 * @param int    $post_id The post ID.
 * @param string $content The post content.
 *
 * @return int|WP_Error The post ID in case of success, a WP_Error in case of error.
 */
function wl_update_post( $post_id, $content ) {

	wl_write_log( "wl_update_post [ post id :: $post_id ][ content ::\n $content\n ]" );

	$wp_error = null;
	$args     = array(
		'ID'           => $post_id,
		'post_content' => $content,
	);

	// Return WP_Error in case of errors.
	return wp_update_post( $args, true );
}

/**
 * Get a post with the provided ID.
 *
 * @param int $post_id The post ID.
 *
 * @return null|WP_Post Returns a WP_Post object, or null on failure.
 */
function wl_get_post( $post_id ) {

	return get_post( $post_id );
}

/**
 * Delete permanently the provided posts.
 *
 * @param array $posts An array of posts.
 *
 * @return bool True if successful otherwise false.
 */
function wl_delete_posts( $posts ) {

	$success = true;

	foreach ( $posts as $post ) {
		$success &= ( false !== wl_delete_post( $post->ID, true ) );
	}

	return $success;
}

///**
// * Analyze the post with the specified ID. The analysis will make use of the method *wl_ajax_analyze_action*
// * provided by the WordLift plugin.
// *
// * @since 3.0.0
// *
// * @param int $post_id The post ID to analyze.
// *
// * @return string Returns null on failure, or the WP_Error, or a WP_Response with the response.
// */
//function wl_analyze_post( $post_id ) {
//
//	// Get the post contents.
//	$post = wl_get_post( $post_id );
//	if ( null === $post ) {
//		return null;
//	}
//	$content = $post->post_content;
//
//	return wl_analyze_content( $content );
//}

/**
 * Embed the analysis results in the post content. It should match what happens client-side with the related function
 * in the app.services.EditorService.coffee file.
 *
 * @param array  $results The analysis results.
 * @param string $content The post content.
 *
 * @return string The content with the annotations embedded.
 */
function wl_embed_text_annotations( $results, $content ) {

	// Then get the related entities via the entity-annotations.
	foreach ( $results['text_annotations'] as $item ) {
		$id         = $item['id'];
		$sel_prefix = wl_clean_up_regex( substr( $item['sel_prefix'], - 2 ) );
		$sel_suffix = wl_clean_up_regex( substr( $item['sel_suffix'], 0, 2 ) );
		$sel_text   = $item['sel_text'];

		$pattern = "/($sel_prefix(?:<[^>]+>){0,})($sel_text)((?:<[^>]+>){0,}$sel_suffix)(?![^<]*\"[^<]*>)/i";
		$replace = "$1<span class=\"textannotation\" id=\"$id\">$2</span>$3";
//        $replace    = "$1<span class=\"textannotation\" id=\"$id\" typeof=\"http://fise.iks-project.eu/ontology/TextAnnotation\">$2</span>$3";

//        echo "[ id :: $id ]\n";
//        echo "[ sel_prefix :: $sel_prefix ]\n";
//        echo "[ sel_suffix :: $sel_suffix ]\n";
//        echo "[ sel_text :: $sel_text ]\n";
//        echo "[ pattern :: $pattern ]\n";
//        echo "[ replace :: $replace ]\n";
//        echo "[ content length (before) :: " . strlen( $content ) . " ]\n";

		// Update the content.
		$content = preg_replace( $pattern, $replace, $content );

//        echo "[ content length (after) :: " . strlen( $content ) . " ]\n";
	}

	return $content;
}

/**
 * @param $results
 * @param $content
 *
 * @return null Null in case of failure.
 */
function wl_embed_entities( $results, $content ) {

	// Prepare the regex pattern.
	$pattern = '/<span class="textannotation" id="([^"]+)"[^>]*>([^<]+)<\/span>/im';
	// This var will contain the output matches.
	$matches = array();

	// Return null if no match found.
	if ( false === preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
		return null;
	}

	// For each match, embed the related entity.
	foreach ( $matches as $match ) {
		$full = $match[0];
		$id   = $match[1];
		$text = $match[2];

//        echo "[ id :: $id ][ text :: $text ]\n";

		$text_annotation = $results['text_annotations'][ $id ];
		$entities        = $text_annotation['entities'];

//        echo "[ text annotation :: " . $text_annotation['id'] . "][ entities count :: " . count( $entities ) . " ]\n";

		$entity_annotation = wl_get_entity_annotation_best_match( $entities );

		// Get the entity, its ID and type.
		$entity          = $entity_annotation['entity'];
		$entity_id       = $entity->{'@id'};
		$entity_type     = wl_get_entity_type( $entity );
		$entity_class    = $entity_type['class'];
		$entity_type_uri = $entity_type['uri'];

		// Create the new span with the entity reference.
		$replace = '<span class="textannotation ' . $entity_class . '" ' .
				   'id="' . $id . '" ' .
				   'itemid="' . $entity_id . '" ' .
				   'itemscope="itemscope" ' .
				   'itemtype="' . $entity_type_uri . '">' .
				   '<span itemprop="name">' . htmlentities( $text ) . '</span></span>';
		$content = str_replace( $full, $replace, $content );


//        echo "[ id :: $id ]\n";
//        echo "[ sel_prefix :: $sel_prefix ]\n";
//        echo "[ sel_suffix :: $sel_suffix ]\n";
//        echo "[ sel_text :: $sel_text ]\n";
//        echo "[ pattern :: $pattern ]\n";
//        echo "[ replace :: $replace ]\n";
//        echo "[ content length (before) :: " . strlen( $content ) . " ]\n";
//        echo "[ entity id :: $entity_id ][ entity type :: $entity_type ]\n";

	}

	return $content;
}

/**
 * Get the entity description from the available fields.
 *
 * @param object $entity An entity instance.
 *
 * @return string The entity description.
 */
function wl_get_entity_description( $entity ) {

	// Return the description from the rdfs:comment field.
	if ( isset( $entity->{'http://www.w3.org/2000/01/rdf-schema#comment'} ) ) {
		return $entity->{'http://www.w3.org/2000/01/rdf-schema#comment'}->{'@value'};
	}

	// Return the description from the Freebase common.topic.description field.
	if ( isset( $entity->{'http://rdf.freebase.com/ns/common.topic.description'} ) ) {
		return $entity->{'http://rdf.freebase.com/ns/common.topic.description'}->{'@value'};
	}

	return '';
}

/**
 * Get the entity thumbnails as an array of URLs.
 *
 * @param object $entity An entity instance.
 *
 * @return array An array of URLs.
 */
function wl_get_entity_thumbnails( $entity ) {

	$images = array();

	// Add the images from the foaf:depiction attribute.
	if ( isset( $entity->{'http://xmlns.com/foaf/0.1/depiction'} ) ) {
		if ( is_array( $entity->{'http://xmlns.com/foaf/0.1/depiction'} ) ) {
			foreach ( $entity->{'http://xmlns.com/foaf/0.1/depiction'} as $image ) {
				array_push( $images, $image->{'http://xmlns.com/foaf/0.1/depiction'}->{'@id'} );
			}
		} else {
			array_push( $images, $entity->{'http://xmlns.com/foaf/0.1/depiction'}->{'@id'} );
		}
	}

	// Convert the URL provided by Freebase to image URLs:
	// see https://developers.google.com/freebase/v1/topic-response#references-to-image-objects
	if ( isset( $entity->{'http://rdf.freebase.com/ns/common.topic.image'} ) ) {
		if ( is_array( $entity->{'http://rdf.freebase.com/ns/common.topic.image'} ) ) {
			foreach ( $entity->{'http://rdf.freebase.com/ns/common.topic.image'} as $image ) {

				$image_url = wl_freebase_image_url( $image->{'@id'} );

				if ( ! empty( $image_url ) ) {
					array_push( $images, $image_url );
				}
			}
		} else {
			$image_url = wl_freebase_image_url( $entity->{'http://rdf.freebase.com/ns/common.topic.image'}->{'@id'} );

			if ( ! empty( $image_url ) ) {
				array_push( $images, $image_url );
			}
		}
	}

	return $images;
}

/**
 * Get an image URL from a link (see https://developers.google.com/freebase/v1/topic-response#references-to-image-objects).
 *
 * @param string $image_link An image link.
 *
 * @return string|null The image URL or null in case of failure.
 */
function wl_freebase_image_url( $image_link ) {

	// http://rdf.freebase.com/ns/m.0kyblb5
	// https://usercontent.googleapis.com/freebase/v1/image/m/0kyblb5

	$matches = array();
	if ( 1 === preg_match( '/m\.([\w\d]+)$/i', $image_link, $matches ) ) {
		$id = $matches[1];

		return "https://usercontent.googleapis.com/freebase/v1/image/m/$id?maxwidth=4096&maxheight=4096";
	};

	return null;
}

/**
 * Clean up a string to be used for a regex fragment.
 *
 * @param string $fragment The fragment to cleanup.
 *
 * @return mixed The cleaned up fragment.
 */
function wl_clean_up_regex( $fragment ) {
	$fragment = str_replace( '\\', '\\\\', $fragment );
	$fragment = str_replace( '\(', '\\(', $fragment );
	$fragment = str_replace( '\)', '\\)', $fragment );
	$fragment = str_replace( '\n', '\\n?', $fragment );
	$fragment = str_replace( '-', '\\-', $fragment );
	$fragment = str_replace( '\x20', '\\s', $fragment );
	$fragment = str_replace( '\xa0', '&nbsp;', $fragment );

	return $fragment;
}

/**
 * Get a types array from an item.
 *
 * @param object|array|string $item An item with a '@type' property (if the property doesn't exist, an empty array is returned).
 *
 * @return array The items array (or an empty array if the '@type' property doesn't exist).
 */
function wl_type_to_types( $item ) {

	if ( is_string( $item ) ) {
		return array( $item );
	}

	if ( is_array( $item ) ) {
		return $item;
	}

	return ! isset( $item->{'@type'} )
		? array() // Set an empty array if type is not set on the item.
		: ( is_array( $item->{'@type'} ) ? $item->{'@type'} : array( $item->{'@type'} ) );
}

/**
 * Parse the string representation of the JSON-LD response from the analysis service.
 *
 * @param string $json A string representation in JSON-LD format.
 *
 * @return array|null Null in case of failure, otherwise an array with Text Annotations, Entity Annotations and
 * Entities.
 */
function wl_parse_response( $json ) {

	// Check that the provided param is an object.
	if ( ! is_object( $json ) ) {
		return null;
	}

	// Define the context for compacting.
	$context = (object) unserialize( WL_JSON_LD_CONTEXT );

	// Compact the JSON-LD.
	$jsonld = jsonld_compact( $json, $context );

	// Get the entity annotations indexed by the textannotation reference.
	$entity_annotations = array();
	// Text Annotations are index by their ID.
	$text_annotations = array();
	// Entities are indexed by their ID.
	$entities = array();
	foreach ( $jsonld->{'@graph'} as $item ) {
		$types = wl_type_to_types( $item );
		// Entity Annotation.
		if ( in_array( WL_ENHANCER_NAMESPACE . ':EntityAnnotation', $types ) ) {
			array_push( $entity_annotations, $item );
		} // Text Annotation.
		else if ( in_array( WL_ENHANCER_NAMESPACE . ':TextAnnotation', $types ) ) {

			// Skip Text Annotations that do not have the selection-prefix, -suffix and selected-text.
			if ( isset( $item->{WL_ENHANCER_NAMESPACE . ':selection-prefix'}->{'@value'} )
				 && isset( $item->{WL_ENHANCER_NAMESPACE . ':selection-suffix'}->{'@value'} )
				 && isset( $item->{WL_ENHANCER_NAMESPACE . ':selected-text'}->{'@value'} )
			) {

				$text_annotations[ $item->{'@id'} ] = array(
					'_'          => $item,
					'id'         => $item->{'@id'},
					'sel_prefix' => $item->{WL_ENHANCER_NAMESPACE . ':selection-prefix'}->{'@value'},
					'sel_suffix' => $item->{WL_ENHANCER_NAMESPACE . ':selection-suffix'}->{'@value'},
					'sel_text'   => $item->{WL_ENHANCER_NAMESPACE . ':selected-text'}->{'@value'},
					'entities'   => array()
					// will hold the entities referenced by this text-annotation.
				);
			}
		} // Entity
		else {
			$entities[ $item->{'@id'} ] = $item;
		}
	}

//    echo '[ $entity_annotations :: ' . count( $entity_annotations ) . ' ]';
//    echo '[ $text_annotations :: ' . count( $text_annotations ) . ' ]';
//    echo '[ $entities :: ' . count( $entities ) . ' ]';

	// Bind the entities to each text annotation via the entity annotation.
	foreach ( $entity_annotations as $item ) {
		// The relation to a Text Annotation.
		$relation = (string) $item->{WL_DUBLIN_CORE_NAMESPACE . ':relation'}->{'@id'};
		// The reference to an entity.
		$entity_reference = (string) $item->{WL_ENHANCER_NAMESPACE . ':entity-reference'}->{'@id'};
		// Get the confidence for the match.
		$confidence = $item->{WL_ENHANCER_NAMESPACE . ':confidence'}->{'@value'};

//        echo "[ relation :: $relation ][ reference :: $entity_reference ]\n";

		// Get the Text Annotation (by ref).
		$text_annotation = &$text_annotations[ $relation ];
		// Get the Entity (by ref)
		$entity = &$entities[ $entity_reference ];

//        echo "[ entity null :: " . is_null( $entity ) . " ]\n";

		// Add the entity to the text annotation entities array.
		array_push( $text_annotation['entities'], array(
			'entity'     => $entity,
			'confidence' => $confidence,
		) );
	}

	return array(
		'text_annotations'   => $text_annotations,
		'entity_annotations' => $entity_annotations,
		'entities'           => $entities,
	);
}

/**
 * Get an array of entity URIs given their post IDs.
 *
 * @param array $post_ids The post IDs.
 *
 * @return array An array of entity URIs.
 */
function wl_post_ids_to_entity_uris( $post_ids ) {

	$uris = array();
	foreach ( $post_ids as $id ) {
		array_push( $uris, wl_get_entity_uri( $id ) );
	}

	return $uris;
}

/**
 * Parse the analysis result from a file.
 *
 * @param string $filename The file containing a JSON-LD analysis response.
 *
 * @return array|null An array with the analysis result, or null in case of failure.
 */
function wl_parse_file( $filename ) {

	$analysis = file_get_contents( $filename );

	// Decode the string response to a JSON.
	$json = json_decode( $analysis );

	// Parse the JSON to get the analysis results.
	return wl_parse_response( $json );
}

/**
 * Get the entity annotation with the best match from the provided entity annotations array.
 *
 * @param array $entity_annotations An array of entities.
 *
 * @return array An entity annotation array.
 */
function wl_get_entity_annotation_best_match( $entity_annotations ) {

	// Sort array by confidence.
	usort( $entity_annotations, function ( $a, $b ) {
		if ( $a['confidence'] == $b['confidence'] ) {
			return 0;
		}

		return ( $a['confidence'] > $b['confidence'] ) ? - 1 : 1;
	} );

	return $entity_annotations[0];
}

/**
 * Erase all posts and entity posts from the blog.
 */
function wl_empty_blog() {

	// Delete existing pages, posts and entities.
	wl_delete_posts( get_posts( array(
		'posts_per_page' => - 1,
		'post_type'      => array(
			'post',
			'page',
			'entity',
		),
		'post_status'    => 'any',
	) ) );

	wl_delete_users();

}

/**
 * Delete all the users from the blog.
 */
function wl_delete_users() {

	$users = get_users();
	foreach ( $users as $user ) {
		wp_delete_user( $user->ID );
	}
}

/**
 * Get the attachments for the specified post ID.
 *
 * @param int $post_id The post ID.
 *
 * @return array An array of attachments.
 */
function wl_get_attachments( $post_id ) {

	return get_posts( array(
		'post_type'      => 'attachment',
		'posts_per_page' => - 1,
		'post_status'    => 'any',
		'post_parent'    => $post_id,
	) );
}

/**
 * Echo the log data to the terminal.
 *
 * @param $log The log data.
 */
function wl_test_write_log_handler( $log ) {

	if ( is_array( $log ) || is_object( $log ) ) {
		echo( print_r( $log, true ) . "\n" );
	} else {
		echo( wl_write_log_hide_key( $log ) . "\n" );
	}

}

/**
 * Set the log handler function name.
 *
 * @return string
 */
function wl_test_get_write_log_handler() {

	return 'wl_test_write_log_handler';

}

/**
 * Configure WordPress with the test settings (may vary according to the local PHP and WordPress versions).
 */
function wl_configure_wordpress_test() {

	$configuration_service = Wordlift_Configuration_Service::get_instance();

	// Ensure we don't have the `wl_db_version` set.
	delete_option( 'wl_db_version' );

	add_filter( 'wl_write_log_handler', 'wl_test_get_write_log_handler' );

	// Simulate WordLift activation.
	activate_wordlift();

	// If the WordLift key is set, then we'll configure it, otherwise we configure Redlink.
	if ( false === getenv( 'WORDLIFT_KEY' ) ) {
		echo( "WordLift's key is required, set the `WORDLIFT_KEY` environment." );
		die( 1 );
	}

	// When setting the WordLift Key, the Redlink dataset URI is provisioned by WordLift Server.
	$configuration_service->set_key( getenv( 'WORDLIFT_KEY' ) );
	$dataset_uri = $configuration_service->get_dataset_uri();
	if ( empty( $dataset_uri ) ) {
		echo( 'The dataset URI is not set (maybe the WordLift key is not valid?).' );
		die( 2 );
	}

}

/**
 * Create a test user.
 * @return int|WP_Error
 */
function wl_test_create_user() {

	return wp_insert_user( array(
		'user_login' => uniqid( 'user-' ),
		'user_pass'  => 'tmppass',
		'first_name' => 'Mario',
		'last_name'  => 'Rossi',
	) );
}


/**
 * Count the number of triples in the dataset.
 * @return array|WP_Error|null An array if successful, otherwise WP_Error or NULL.
 */
function rl_count_triples() {

	// Set the SPARQL query.
	$sparql = 'SELECT (COUNT(DISTINCT ?s) AS ?subjects) (COUNT(DISTINCT ?p) AS ?predicates) (COUNT(DISTINCT ?o) AS ?objects) ' .
			  'WHERE { ?s ?p ?o }';

	// Send the request.
	$response = rl_sparql_select( $sparql );

	// Return the error in case of failure.
	if ( is_wp_error( $response ) || 200 !== (int) $response['response']['code'] ) {

		$body = ( is_wp_error( $response ) ? $response->get_error_message() : $response['body'] );

		wl_write_log( "rl_count_triples : error [ response :: " );
		wl_write_log( "\n" . var_export( $response, true ) );
		wl_write_log( "][ body :: " );
		wl_write_log( "\n" . $body );
		wl_write_log( "]" );

		return $response;
	}

	// Get the body.
	$body = $response['body'];

	// Get the values.
	$matches = array();
	if ( 1 === preg_match( '/(\d+),(\d+),(\d+)/im', $body, $matches ) && 4 === count( $matches ) ) {

		// Return the counts.
		return array(
			'subjects'   => (int) $matches[1],
			'predicates' => (int) $matches[2],
			'objects'    => (int) $matches[3],
		);
	}

	// No digits found in the response, return null.
	wl_write_log( "rl_count_triples : unrecognized response [ body :: $body ]" );

	return null;
}


/**
 * Execute the provided query against the SPARQL SELECT Redlink end-point and return the response.
 *
 * @param string $query A SPARQL query.
 *
 * @return WP_Error|WP_Response A WP_Response instance in successful otherwise a WP_Error.
 */
function rl_sparql_select( $query ) {

	// Prepare the SPARQL statement by prepending the default namespaces.
	$sparql = rl_sparql_prefixes() . "\n" . $query;

	// Get the SPARQL SELECT URL.
	$url = wl_configuration_get_query_select_url() . urlencode( $sparql );

	// Prepare the request.
	$args = unserialize( WL_REDLINK_API_HTTP_OPTIONS );

	// Send the request.
	wl_write_log( "SPARQL Select [ sparql :: $sparql ][ url :: $url ][ args :: " . var_export( $args, true ) . " ]" );

	return wp_remote_get( $url, $args );
}

/**
 * Get relations for a given $subject_id as an associative array.
 *
 * @global type $wpdb
 *
 * @param type  $post_id
 * @param type  $predicate
 *
 * @return array in the following format:
 *              Array (
 *                  [0] => stdClass Object ( [id] => 140 [subject_id] => 17 [predicate] => what [object_id] => 47 ),
 *                  [1] => stdClass Object ( [id] => 141 [subject_id] => 17 [predicate] => what [object_id] => 14 ),
 *                  [2] => stdClass Object ( [id] => 142 [subject_id] => 17 [predicate] => where [object_id] => 16 ),
 *                  ...
 */
function wl_tests_get_relation_instances_for( $post_id, $predicate = null ) {

	// Prepare interaction with db
	global $wpdb;
	// Retrieve Wordlift relation instances table name
	$table_name = wl_core_get_relation_instances_table_name();
	// Sql Action
	$sql_statement = $wpdb->prepare( "SELECT * FROM $table_name WHERE subject_id = %d", $post_id );
	if ( null != $predicate ) {
		$sql_statement .= $wpdb->prepare( " AND predicate = %s", $predicate );
	}
	$results = $wpdb->get_results( $sql_statement );

	return $results;

}

/**
 * Replace the midnight in the format of +00:00 to .000Z to allow testing time results from WordLift Server / Redlink.
 *
 * @param $time string A time format with +00:00
 *
 * @return string A time format with .000Z
 *
 * @since 3.0.0
 */
function wl_tests_time_0000_to_000Z( $time ) {
	return preg_replace( '/\+00:00$/', '.000Z', $time );
}


/**
 * Return the time difference in seconds between two times (time2 is expected to be later than time1).
 *
 * @param $time1
 * @param $time2
 *
 * @return int
 */
function wl_tests_get_time_difference_in_seconds( $time1, $time2 ) {

	$date1 = date_create( $time1 );
	$date2 = date_create( $time2 );

	return ( $date2->getTimestamp() - $date1->getTimestamp() );
}

/**
 * Retrieves the property expected type, according to the schema.org specifications, where:
 *
 * @param $property_name string Name of the property (e.g. name, for the http://schema.org/name property)
 *
 * @return array of allowed types or NULL in case of property not found.
 *
 * The following types are supported (defined as constants):
 * - Wordlift_Schema_Service::DATA_TYPE_DATE
 * - WL_DATA_TYPE_INTEGER
 * - Wordlift_Schema_Service::DATA_TYPE_DOUBLE
 * - WL_DATA_TYPE_BOOLEAN
 * - Wordlift_Schema_Service::DATA_TYPE_STRING
 * - Wordlift_Schema_Service::DATA_TYPE_URI
 * - a schema.org URI when the property type supports a schema.org entity (e.g. http://schema.org/Place)
 */
function wl_schema_get_property_expected_type( $property_name ) {

	// This is the actual structure of a custom_field.
	/*
	 * Wordlift_Schema_Service::FIELD_LOCATION       => array(
	 *      'predicate'   => 'http://schema.org/location',
	 *      'type'        => Wordlift_Schema_Service::DATA_TYPE_URI,
	 *      'export_type' => 'http://schema.org/PostalAddress',
	 *      'constraints' => array(
	 *              'uri_type' => 'Place'
	 *      )
	 *  )
	 */

	// Build full schema uri if necessary
	$property_name = wl_build_full_schema_uri_from_schema_slug( $property_name );

	// Get all custom fields
	$all_types_and_fields = wl_entity_taxonomy_get_custom_fields();

	$expected_types = null;

	// Search for the entity type which has the requested name as uri
	$found = false;
	foreach ( $all_types_and_fields as $type_fields ) {
		foreach ( $type_fields as $field ) {
			if ( $field['predicate'] == $property_name ) {

				$expected_types = array();

				// Does the property accept a specific schema type?
				if ( isset( $field['constraints'] ) && isset( $field['constraints']['uri_type'] ) ) {

					// Take note of expected schema type
					$uri_types = ( is_array( $field['constraints']['uri_type'] ) ) ?
						$field['constraints']['uri_type'] :
						array( $field['constraints']['uri_type'] );

					foreach ( $uri_types as $uri_type ) {
						$expected_types[] = wl_build_full_schema_uri_from_schema_slug( $uri_type );
					}

				} else {
					// Take note of expected type
					$expected_types[] = $field['type'];
				}

				// We found the property
				return $expected_types;
			}
		}
	}

	return $expected_types;
}

/**
 * Retrieve entity property type, starting from the schema.org's property name
 * or from the WL_CUSTOM_FIELD_xxx name.
 *
 * @param string $property_name as defined by schema.org or WL internal constants
 *
 * @return array containing type(s) or null (in case of error or no types).
 */
function wl_get_meta_type( $property_name ) {

	// Property name must be defined.
	if ( ! isset( $property_name ) || is_null( $property_name ) ) {
		return null;
	}

	// store eventual schema name in  different variable
	$property_schema_name = wl_build_full_schema_uri_from_schema_slug( $property_name );

	// Loop over custom_fields
	$entity_terms = wl_entity_taxonomy_get_custom_fields();

	foreach ( $entity_terms as $term ) {

		foreach ( $term as $wl_constant => $field ) {

			// Is this the predicate we are searching for?
			if ( isset( $field['type'] ) ) {
				$found_predicate = isset( $field['predicate'] ) && ( $field['predicate'] == $property_schema_name );
				$found_constant  = ( $wl_constant == $property_name );
				if ( $found_predicate || $found_constant ) {
					return $field['type'];
				}
			}
		}
	}

	return null;
}

/**
 * Get the modified time of the provided post. If the time is negative, return the published date.
 *
 * @param object $post A post instance.
 *
 * @return string A datetime.
 */
function wl_get_post_modified_time( $post ) {

	$date_modified = get_post_modified_time( 'c', true, $post );

	if ( '-' === substr( $date_modified, 0, 1 ) ) {
		return get_the_time( 'c', $post );
	}

	return $date_modified;
}

/**
 * Remove a given relation instance
 * @uses   $wpdb->delete() to perform the query
 *
 * @param int    $subject_id The post ID | The entity post ID.
 * @param string $predicate  Name of the relation: 'what' | 'where' | 'when' | 'who'
 * @param int    $object_id  The entity post ID.
 *
 * @return boolean False for failure. True for success.
 */
function wl_core_delete_relation_instance( $subject_id, $predicate, $object_id ) {

	// Checks on subject and object
	if ( ! is_numeric( $subject_id ) || ! is_numeric( $object_id ) ) {
		return false;
	}

	// Checks on the given relation
	if ( ! wl_core_check_relation_predicate_is_supported( $predicate ) ) {
		return false;
	}

	// Prepare interaction with db
	global $wpdb;

	wl_write_log( "Going to delete relation instace [ subject_id :: $subject_id ] [ object_id :: $object_id ] [ predicate :: $predicate ]" );

	// @see ttps://codex.wordpress.org/it:Riferimento_classi/wpdb#DELETE_di_righe
	$wpdb->delete(
		wl_core_get_relation_instances_table_name(),
		array(
			'subject_id' => $subject_id,
			'predicate'  => $predicate,
			'object_id'  => $object_id,
		),
		array( '%d', '%s', '%d' )
	);

	return true;
}


/**
 * Create multiple relation instances
 * @uses   wl_add_relation_instance() to create each single instance
 *
 * @param int    $subject_id The post ID | The entity post ID.
 * @param string $predicate  Name of the relation: 'what' | 'where' | 'when' | 'who'
 * @param array  $object_ids The entity post IDs collection.
 *
 * @return integer|boolean Return the relation instances IDs or false
 */
function wl_core_add_relation_instances( $subject_id, $predicate, $object_ids ) {

	// Checks on subject and object
	if ( ! is_numeric( $subject_id ) ) {
		return false;
	}

	// Checks on the given relation
	if ( ! wl_core_check_relation_predicate_is_supported( $predicate ) ) {
		return false;
	}

	// Check $object_ids is an array
	if ( ! is_array( $object_ids ) || empty( $object_ids ) ) {
		return false;
	}

	// Call method to check and add each single relation
	$inserted_records_ids = array();
	foreach ( $object_ids as $object_id ) {
		$new_record_id          = wl_core_add_relation_instance( $subject_id, $predicate, $object_id );
		$inserted_records_ids[] = $new_record_id;
	}

	return $inserted_records_ids;
}

/**
 * Add a value of the specified property for the entity, where
 *
 * @param int    $post_id        The numeric post ID.
 * @param string $property_name  Name of the property (e.g. name, for the http://schema.org/name property).
 * @param mixed  $property_value Value to save into the property (adding to already saved).
 *
 * @return array An array of values or NULL in case of no values (or error).
 */
function wl_schema_add_value( $post_id, $property_name, $property_value ) {

	if ( ! is_array( $property_value ) ) {
		$property_value = array( $property_value );
	}

	// Get the old values or set an empty array.
	$old_values = wl_schema_get_value( $post_id, $property_name ) ?: array();

	$merged_property_value = array_unique( array_merge( $property_value, $old_values ) );

	wl_schema_set_value( $post_id, $property_name, $merged_property_value );

}

/**
 * Retrieves the list of supported properties for the specified type.
 * @uses wl_entity_taxonomy_get_custom_fields() to retrieve all custom fields (type properties)
 * @uses wl_build_full_schema_uri_from_schema_slug() to convert a schema slug to full uri
 *
 * @param $type_name string Name of the type (e.g. Type, for the http://schema.org/Type)
 *
 * @return array The method returns an array of supported properties for the type, e.g. (‘startDate’, ‘endDate’) for an Event.
 * You can call wl_schema_get_property_expected_type on each to know which data type they expect.
 */
function wl_schema_get_type_properties( $type_name ) {

	// Build full schema uri if necessary
	$type_name = wl_build_full_schema_uri_from_schema_slug( $type_name );

	// Get all custom fields
	$all_types_and_fields = wl_entity_taxonomy_get_custom_fields();

	$schema_root_address = 'http://schema.org/';
	$type_properties     = array();

	// Search for the entity type which has the requested name as uri
	if ( isset( $all_types_and_fields[ $type_name ] ) ) {
		foreach ( $all_types_and_fields[ $type_name ] as $field ) {
			// Convert to schema slug and store in array
			$type_properties[] = str_replace( $schema_root_address, '', $field['predicate'] );
		}
	}

	return $type_properties;
}
