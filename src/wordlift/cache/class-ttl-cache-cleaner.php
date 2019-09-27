<?php
/**
 * This file contains the Cache Cleaner class which will scan the cache folder and delete stale files.
 *
 * If after deleting stale files, the disk usage is over the specified limit (100 M by default), then also non-stale
 * older files are deleted.
 *
 * @author David Riccitelli <david@wordlift.io>
 * @since 3.22.5
 *
 * @package Wordlift
 * @subpackage Wordlift\Cache
 */

namespace Wordlift\Cache;

use Exception;

defined( 'WORDLIFT_CACHE_DEFAULT_TTL' ) || define( 'WORDLIFT_CACHE_DEFAULT_TTL', 86400 );  // 24 hours
defined( 'WORDLIFT_CACHE_DEFAULT_MAX_SIZE' ) || define( 'WORDLIFT_CACHE_DEFAULT_MAX_SIZE', 104857600 ); // 100 M

class Ttl_Cache_Cleaner {

	const PATH = 0;
	const MTIME = 1;
	const SIZE = 2;

	/**
	 * The max TTL in seconds.
	 *
	 * @access private
	 * @var int $ttl The max TTL in seconds.
	 */
	private $ttl;

	/**
	 * The max size in bytes.
	 *
	 * @access private
	 * @var int $ttl The max size in bytes.
	 */
	private $max_size;

	/**
	 * Ttl_Cache_Cleaner constructor.
	 *
	 * @param int $ttl The max TTL in seconds.
	 * @param int $max_size The max size in bytes.
	 */
	public function __construct( $ttl = WORDLIFT_CACHE_DEFAULT_TTL, $max_size = WORDLIFT_CACHE_DEFAULT_MAX_SIZE ) {

		$this->ttl      = $ttl;
		$this->max_size = $max_size;

		add_action( 'wp_ajax_wl_ttl_cache_cleaner__cleanup', array( $this, 'cleanup' ) );

	}

	public function cleanup() {

		// Get all the files, recursive.
		$files = $this->reduce( array(), Ttl_Cache::get_cache_folder() );


		// Get the max mtime.
		$max_mtime = time() - WORDLIFT_CACHE_DEFAULT_TTL;

		// Keep the original count for statistics that we're going to send the client.
		$original_count = count( $files );

		// Sort by size ascending.
		usort( $files, function ( $f1, $f2 ) {
			if ( $f1[ self::MTIME ] === $f2[ self::MTIME ] ) {
				return 0;
			}

			return ( $f1[ self::MTIME ] < $f2[ self::MTIME ] ) ? - 1 : 1;
		} );

		// Start removing stale files.
		for ( $i = 0; $i < count( $files ); $i ++ ) {
			$file = $files[ $i ];
			// Break if the mtime is within the range.
			if ( $file[ self::MTIME ] > $max_mtime ) {
				break;
			}

			unset( $files[ $i ] );
			@unlink( $file[ self::PATH ] );
		}

		// Calculate the size.
		$total_size = array_reduce( $files, function ( $carry, $item ) {

			return $carry + $item[ self::SIZE ];
		}, 0 );

		// Remove files until we're within the max size.
		while ( $total_size > $this->max_size ) {
			$file       = array_shift( $files );
			$total_size -= $file[ self::SIZE ];
			@unlink( $file[ self::PATH ] );
		}

		// Send back some stats.
		wp_send_json_success( array(
			'initial_count' => $original_count,
			'current_count' => count( $files ),
			'current_size'  => $total_size,
		) );
	}

	private function reduce( $accumulator, $path ) {

		// Open the dir handle.
		$handle = opendir( $path );

		// Catch exceptions to be sure to close the dir handle.
		try {
			$accumulator = @$this->_reduce( $accumulator, $path, $handle );
		} catch ( Exception $e ) {
			// Do nothing.
		}

		// Finally close the directory handle.
		closedir( $handle );

		return $accumulator;
	}

	/**
	 * @param $accumulator
	 * @param $path
	 * @param $handle
	 *
	 * @return array
	 */
	private function _reduce( $accumulator, $path, $handle ): array {

		while ( false !== ( $entry = readdir( $handle ) ) ) {

			// Skip to the next one.
			if ( 0 === strpos( $entry, '.' ) ) {
				continue;
			}

			// Set the full path to the entry.
			$entry_path = $path . DIRECTORY_SEPARATOR . $entry;

			// Handle directories.
			if ( is_dir( $entry_path ) ) {
				$accumulator = $this->reduce( $accumulator, $entry_path );

				continue;
			}

			// Store the file data.
			$accumulator[] = array(
				$entry_path,
				filemtime( $entry_path ),
				filesize( $entry_path ),
			);
		}

		return $accumulator;
	}

}
