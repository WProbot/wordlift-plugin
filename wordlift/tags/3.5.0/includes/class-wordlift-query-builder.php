<?php

/**
 * A builder to build SPARQL queries.
 *
 * @since 3.1.7
 */
class Wordlift_Query_Builder {

	/**
	 * The INSERT statement template.
	 *
	 * @since 3.1.7
	 */
	const INSERT = 'INSERT DATA { %s };';

	/**
	 * The DELETE statement template (it repeats the statements in the WHERE clause.
	 *
	 * @since 3.1.7
	 */
	const DELETE = 'DELETE { %s } WHERE { %1$s };';

	/**
	 * Tell the statement function to guess the object type (URI, value or parameter).
	 *
	 * @since 3.1.7
	 */
	const OBJECT_AUTO = - 1;

	/**
	 * Tell the statement function that the object is a URI.
	 *
	 * @since 3.1.7
	 */
	const OBJECT_URI = 0;

	/**
	 * Tell the statement function that the object is a value.
	 *
	 * @since 3.1.7
	 */
	const OBJECT_VALUE = 1;

	/**
	 * Tell the statement function that the object is a parameter.
	 *
	 * @since 3.1.7
	 */
	const OBJECT_PARAMETER = 2;

	/**
	 * The RDFS type.
	 *
	 * @since 3.1.7
	 */
	const RDFS_TYPE_URI = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

	/**
	 * The schema.org/Person type.
	 *
	 * @since 3.1.7
	 */
	const SCHEMA_PERSON_URI = 'http://schema.org/Person';

	/**
	 * The schema.org given name predicate.
	 *
	 * @since 3.1.7
	 */
	const SCHEMA_GIVEN_NAME_URI = 'http://schema.org/givenName';

	/**
	 * The schema.org family name predicate.
	 *
	 * @since 3.1.7
	 */
	const SCHEMA_FAMILY_NAME_URI = 'http://schema.org/familyName';

	/**
	 * The schema.org url predicate.
	 *
	 * @since 3.1.7
	 */
	const SCHEMA_URL_URI = 'http://schema.org/url';

	/**
	 * The RDF label.
	 *
	 * @since 3.1.7
	 */
	const RDFS_LABEL_URI = 'http://www.w3.org/2000/01/rdf-schema#label';

	/**
	 * Hold the template (INSERT or DELETE).
	 *
	 * @since 3.1.7
	 * @access private
	 * @var string $template The query template.
	 */
	private $template;

	/**
	 * An array of statements (in the form of subject, predicate, object).
	 *
	 * @since 3.1.7
	 * @access private
	 * @var array $statements An array of statements.
	 */
	private $statements = array();

	/**
	 * Create a new instance of the Query builder (compatible with PHP 5.3).
	 *
	 * @since 3.1.7
	 * @return Wordlift_Query_Builder A new instance of the Query builder.
	 */
	public static function new_instance() {

		return new Wordlift_Query_Builder();
	}

	/**
	 * Set the query to INSERT.
	 *
	 * @since 3.1.7
	 * @return $this \Wordlift_Query_Builder The Query builder.
	 */
	public function insert() {

		$this->template = self::INSERT;

		return $this;
	}

	/**
	 * Set the query to DELETE.
	 *
	 * @since 3.1.7
	 * @return $this \Wordlift_Query_Builder The Query builder.
	 */
	public function delete() {

		$this->template = self::DELETE;

		return $this;
	}

	/**
	 * Add a statement.
	 *
	 * @since 3.1.7
	 *
	 * @param string $subject The subject of the statement (must be a URI).
	 * @param string $predicate The predicate (must be a URI).
	 * @param string $object The object, can be a URI or a value.
	 * @param int $object_type The object type, either a {@link OBJECT_URI} or a value {@link OBJECT_VALUE}. If set to {@link OBJECT_AUTO}, the Query builder will try to guess.
	 * @param string|null $data_type The data type (or null).
	 * @param string|null $language The language code (or null).
	 *
	 * @return $this \Wordlift_Query_Builder The Query builder.
	 */
	public function statement( $subject, $predicate, $object, $object_type = self::OBJECT_AUTO, $data_type = null, $language = null ) {

		// If no value has been provided, we don't set any statement.
		if ( empty( $object ) ) {
			return $this;
		}

		// Get the object type if set, otherwise try to guess it.
		$object_value_type = ( self::OBJECT_AUTO !== $object_type ?: $this->guess_object_type( $predicate, $object ) );

		// Prepare the statement template.
		$template = '<%1$s> <%2$s> ' . (
			self::OBJECT_URI === $object_value_type ? '<%3$s>' : (
			self::OBJECT_PARAMETER === $object_value_type ? '%3$s' :
				// self::OBJECT_VALUE === $object_value_type
				'"%3$s"' . ( isset( $data_type ) ? '^^%4$s' : '' ) . ( isset( $language ) ? '@%5$s' : '' ) ) );

		// Escape the subject, predicate and object.
		$escaped_subject   = $this->escape_uri( $subject );
		$escaped_predicate = $this->escape_uri( $predicate );
		$escaped_object    = ( self::OBJECT_URI === $object_value_type ? $this->escape_uri( $object ) : self::escape_value( $object ) );

		// Prepare the statement and add it to the list of statements.
		$this->statements[] = sprintf( $template, $escaped_subject, $escaped_predicate, $escaped_object, $data_type, $language );

		return $this;
	}

	/**
	 * Build the query.
	 *
	 * @since 3.1.7
	 * @return string The query string.
	 */
	public function build() {

		return sprintf( $this->template, implode( ' . ', $this->statements ) ) . "\n";
	}

	/**
	 * Guess the statement object type.
	 *
	 * @since 3.1.7
	 *
	 * @param string $predicate The predicate.
	 * @param string $object The object.
	 *
	 * @return int {@link Wordlift_Query_Builder::OBJECT_URI} if the Query builder thinks the object must be an URI, {@link Wordlift_Query_Builder::OBJECT_VALUE} otherwise.
	 */
	private function guess_object_type( $predicate, $object ) {

		// If the object starts with a question mark, it's a parameter.
		if ( 0 === strpos( $object, '?' ) ) {
			return self::OBJECT_PARAMETER;
		}

		// Guess based on the predicate.
		switch ( $predicate ) {

			case self::RDFS_TYPE_URI:
			case self::SCHEMA_URL_URI:
				return self::OBJECT_URI;

		}

		return self::OBJECT_VALUE;
	}

	/**
	 * Escape a URI.
	 *
	 * @since 3.1.7
	 *
	 * @param string $uri The URI to escape.
	 *
	 * @return string The escaped URI.
	 */
	private function escape_uri( $uri ) {

		// Should we validate the IRI?
		// http://www.w3.org/TR/sparql11-query/#QSynIRI

		$escaped_uri = str_replace( '<', '\<', $uri );
		$escaped_uri = str_replace( '>', '\>', $escaped_uri );

		return $escaped_uri;
	}

	/**
	 * Escape a value.
	 *
	 * @since 3.1.7
	 *
	 * @param string $value The value to escape.
	 *
	 * @return string The escaped value.
	 */
	public static function escape_value( $value ) {

		// see http://www.w3.org/TR/rdf-sparql-query/
		//    '\t'	U+0009 (tab)
		//    '\n'	U+000A (line feed)
		//    '\r'	U+000D (carriage return)
		//    '\b'	U+0008 (backspace)
		//    '\f'	U+000C (form feed)
		//    '\"'	U+0022 (quotation mark, double quote mark)
		//    "\'"	U+0027 (apostrophe-quote, single quote mark)
		//    '\\'	U+005C (backslash)

		$escaped_value = str_replace( '\\', '\\\\', $value );
		$escaped_value = str_replace( '\'', '\\\'', $escaped_value );
		$escaped_value = str_replace( '"', '\\"', $escaped_value );
		$escaped_value = str_replace( "\f", '\\f', $escaped_value );
		$escaped_value = str_replace( "\b", '\\b', $escaped_value );
		$escaped_value = str_replace( "\r", '\\r', $escaped_value );
		$escaped_value = str_replace( "\n", '\\n', $escaped_value );
		$escaped_value = str_replace( "\t", '\\t', $escaped_value );

		return $escaped_value;
	}

}
