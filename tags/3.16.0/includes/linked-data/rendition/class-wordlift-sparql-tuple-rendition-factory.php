<?php
/**
 * Factories: Rendition Factory.
 *
 * A factory which creates renditions which translate source contents into output
 * statements (JSON-LD or SPARQL).
 *
 * @since      3.15.0
 * @package    Wordlift
 * @subpackage Wordlift/includes/linked-data/rendition
 */

/**
 * Define the {@link Wordlift_Sparql_Tuple_Rendition_Factory} class.
 *
 * @since      3.15.0
 * @package    Wordlift
 * @subpackage Wordlift/includes/linked-data/rendition
 */
class Wordlift_Sparql_Tuple_Rendition_Factory {

	/**
	 * The {@link Wordlift_Entity_Service} instance.
	 *
	 * @since  3.15.0
	 * @access private
	 * @var \Wordlift_Entity_Service $entity_service The {@link Wordlift_Entity_Service} instance.
	 */
	private $entity_service;

	/**
	 * Create a a {@link Wordlift_Sparql_Tuple_Rendition_Factory} instance.
	 *
	 * @since 3.15.0
	 *
	 * @param \Wordlift_Entity_Service $entity_service The {@link Wordlift_Entity_Service} instance.
	 */
	public function __construct( $entity_service ) {

		$this->entity_service = $entity_service;

	}

	/**
	 * Create a rendition.
	 *
	 * @since 3.15.0
	 *
	 * @param \Wordlift_Storage $storage   A {@link Wordlift_Storage} instance.
	 * @param string            $predicate A predicate (e.g. `http://schema.org/name`).
	 * @param string|null       $data_type A data-type.
	 * @param string|null       $language  A language code (e.g. `en`).
	 *
	 * @return \Wordlift_Sparql_Tuple_Rendition A {@link Wordlift_Sparql_Tuple_Rendition} instance.
	 */
	public function create( $storage, $predicate, $data_type = null, $language = null ) {

		return new Wordlift_Sparql_Tuple_Rendition( $this->entity_service, $storage, $predicate, $data_type, $language );
	}

}
