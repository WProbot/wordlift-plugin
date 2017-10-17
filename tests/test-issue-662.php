<?php
/**
 * Tests: Issue 662.
 *
 * Check that a post w/o any WordLift's taxonomy term associated doesn't show
 * in queries.
 *
 * @since      3.15.3
 * @package    Wordlift
 * @subpackage Wordlift/tests
 */

// Define the `get_current_screen` global function if it doesn't exist.
if ( ! function_exists( 'get_current_screen' ) ) {

	function get_current_screen() {
		global $current_screen;

		if ( ! isset( $current_screen ) ) {
			return null;
		}

		return $current_screen;
	}

}

/**
 * Define the {@link Wordlift_Issue_662} class.
 *
 * @since      3.15.3
 * @package    Wordlift
 * @subpackage Wordlift/tests
 */
class Wordlift_Issue_662 extends Wordlift_Unit_Test_Case {

	/**
	 * The {@link Wordlift_Entity_List_Service} instance to test.
	 *
	 * @since 3.15.3
	 *
	 * @var \Wordlift_Entity_List_Service $entity_list_service The {@link Wordlift_Entity_List_Service} instance to test.
	 */
	private $entity_list_service;

	/**
	 * Pretend we're a screen showing the `entity` post type.
	 *
	 * @since 3.15.3
	 *
	 * @var string $post_type The `entity` post type.
	 */
	public $post_type = Wordlift_Entity_Service::TYPE_NAME;

	/**
	 * @inheritdoc
	 */
	function setUp() {
		parent::setUp();

		// Pretend to be the current screen, so that we can tell the Entity
		// List Service that we're in admin.
		$GLOBALS['current_screen'] = $this;

		$this->entity_list_service = $this->get_wordlift_test()->get_entity_list_service();

	}

	/**
	 * @inheritdoc
	 */
	function tearDown() {
		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test the Admin Vocabulary screen, by creating 4 sample posts and checking
	 * that only the right ones show after the {@link Wordlift_Entity_List_Service}
	 * alters the query.
	 *
	 * @since 3.15.3
	 */
	public function test() {

		// Create a (1) post w/o terms, (2) post w/ terms, (3) post with entity
		// term and (4) entity.
		$this->create_and_remove_terms();
		$this->create_and_check_terms();
		$post_as_entity_id = $this->create_and_set_term( 'organization' );
		$entitiy_id        = $this->create_entity( 'person' );

		// Create a new query and have the entity list service manipulate it.
		$GLOBALS['wp_the_query'] = $query = new WP_Query( array() );
		$this->entity_list_service->pre_get_posts( $query );

		// Check that the Entity List service altered our query.
		$this->assertNotNull( $query->get( 'post_type' ) );
		$this->assertNotNull( $query->get( 'tax_query' ) );

		// Finally get the posts, expect no posts returned.
		$posts = $query->get_posts();
		$this->assertCount( 2, $posts );
		$this->assertEquals( $post_as_entity_id, $posts[0]->ID );
		$this->assertEquals( $entitiy_id, $posts[1]->ID );

		unset( $GLOBALS['wp_the_query'] );
	}

	private function create_entity( $slug ) {

		$post_id = $this->entity_factory->create();

		// Remove WordLift's taxonomy from the post, and clean up the cache
		// to pick up the changes.
		wp_delete_object_term_relationships( $post_id, Wordlift_Entity_Types_Taxonomy_Service::TAXONOMY_NAME );
		wp_cache_flush();

		$this->add_term( $post_id, $slug );

		return $post_id;
	}

	private function add_term( $post_id, $slug ) {

		wp_add_object_terms( $post_id, $slug, Wordlift_Entity_Types_Taxonomy_Service::TAXONOMY_NAME );

		// Get the terms bound to the post.
		$terms = get_the_terms( $post_id, Wordlift_Entity_Types_Taxonomy_Service::TAXONOMY_NAME );
		$this->assertCount( 1, $terms );

		// Check that the `Article` term has been bound automatically to the
		// post.
		$term = current( $terms );
		$this->assertEquals( $slug, $term->slug );

	}

	private function create_and_set_term( $slug ) {

		$post_id = $this->create_and_remove_terms();

		$this->add_term( $post_id, $slug );

		return $post_id;
	}

	private function create_and_remove_terms() {

		// Create a post.
		$post_id = $this->create_and_check_terms();

		// Remove WordLift's taxonomy from the post, and clean up the cache
		// to pick up the changes.
		wp_delete_object_term_relationships( $post_id, Wordlift_Entity_Types_Taxonomy_Service::TAXONOMY_NAME );
		wp_cache_flush();

		// Check that the taxonomy has been removed.
		$terms = get_the_terms( $post_id, Wordlift_Entity_Types_Taxonomy_Service::TAXONOMY_NAME );
		$this->assertFalse( $terms );

		return $post_id;
	}

	/**
	 * Create a {@link WP_Post} and check the associated terms.
	 *
	 * @since 3.15.3
	 * @return int The {@link WP_Post} id.
	 */
	private function create_and_check_terms() {

		// Create a post.
		$post_id = $this->factory->post->create();
		$this->assertGreaterThan( 0, $post_id );

		// Get the terms bound to the post.
		$terms = get_the_terms( $post_id, Wordlift_Entity_Types_Taxonomy_Service::TAXONOMY_NAME );
		$this->assertCount( 1, $terms );

		// Check that the `Article` term has been bound automatically to the
		// post.
		$term = current( $terms );
		$this->assertEquals( 'article', $term->slug );

		return $post_id;
	}

	/**
	 * Behave as a `screen` pretending we're in admin.
	 *
	 * @since 3.15.3
	 *
	 * @return bool Always true.
	 */
	public function in_admin() {

		return true;
	}

}
