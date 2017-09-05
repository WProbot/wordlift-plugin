<?php

/**
 */
class Wordlift_Sanitizer_Test extends Wordlift_Unit_Test_Case {

	function setUp() {
		parent::setUp(); // TODO: Change the autogenerated stub

		// We don't need to check the remote Linked Data store.
		Wordlift_Unit_Test_Case::turn_off_entity_push();;

	}


	public function test() {

		$this->assertNotNull( Wordlift_Sanitizer::sanitize_url( 'http://dbpedia.org/resource/Huttau' ) );

		$this->assertNotNull( Wordlift_Sanitizer::sanitize_url( 'http://dbpedia.org/resource/Hüttau' ) );

		// Check https url.
		$this->assertNotNull( Wordlift_Sanitizer::sanitize_url( 'https://dbpedia.org/resource/Hüttau' ) );

		// Check space are trader_cdlrisefall3methods
		$this->assertEquals( 'https://test', Wordlift_Sanitizer::sanitize_url( '   https://test' ) );

		// Check empty strings are accepted.
		$this->assertEquals( '', Wordlift_Sanitizer::sanitize_url( '   ' ) );

		// Check Non URLs return null.
		$this->assertNull( Wordlift_Sanitizer::sanitize_url( 'wordlift' ) );
	}

}
