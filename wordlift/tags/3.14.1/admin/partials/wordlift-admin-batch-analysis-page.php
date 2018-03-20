<?php
/**
 * Pages: Admin batch analysis page.
 *
 * @since   3.14.0
 * @package Wordlift/admin
 */
?>

<div class="wrap">
	<h2><?php esc_html_e( 'WordLift Batch Analyze Monitor', 'wordlift' ) ?></h2>
	<?php
	// If a form was submitted and the nonce checks out, process the list of URLS.
	if ( isset( $_FILES['urls'] ) && check_admin_referer( 'batch_analysis', 'wl_nonce' ) ) {
		// The file contains a list of urls to be analyzed, one per raw
		$pids     = array();
		$filename = $_FILES['urls']['tmp_name'];
		$file     = fopen( $filename, 'r' );
		while ( ! feof( $file ) ) {
			$url = trim( fgets( $file ) );
			if ( '' != $url ) {
				$pid = url_to_postid( $url );
				if ( 0 != $pid ) {
					$pids[] = $pid;
				}
			}
		}
		fclose( $file );
		$this->batch_analysis_service->enqueue_for_analysis( $pids, $_POST['wl_linktype'] );
		$this->batch_analysis_service->batch_analyze();
	}
	?>
	<form method="post" action="" enctype="multipart/form-data">
		<?php wp_nonce_field( 'batch_analysis', 'wl_nonce' ); ?>
		<p>
			<label>
				<?php esc_html_e( 'File containing URLs of posts to analyze', 'wordlift' ) ?>
			</label>
			<input type="file" name="urls">
			<br>
			<?php esc_html_e( 'The file should contain one URL in each line.', 'wordlift' ) ?>
		</p>
		<p>
			<label for="wl_linktype">
				<?php esc_html_e( 'Type of entity linking', 'wordlif' ); ?>
			</label>
			<select id="wl_linktype" name="wl_linktype">
				<option
					value="no"><?php esc_html_e( 'No Link', 'wordlift' ) ?></option>
				<option
					value="yes"><?php esc_html_e( 'Link', 'wordlift' ) ?></option>
			</select>
		</p>
		<?php submit_button( __( 'Start', 'wordlift' ) ); ?>
	</form>

	<h3><?php esc_html_e( 'Posts in queue', 'wordlift' ) ?></h3>
	<?php
	$queue = $this->batch_analysis_service->waiting_for_analysis();
	if ( empty( $queue ) ) {
		esc_html_e( 'Nothing is currently in queue', 'wordlift' );
	} else {
		echo '<ul>';
		foreach ( $queue as $item ) {
			$pid = $item['id'];
			echo '<li><a href="' . get_edit_post_link( $pid ) . '">' . get_the_title( $pid ) . '</a></li>';
		}
		echo '</ul>';
	}
	?>
	<h3><?php esc_html_e( 'Posts being processed', 'wordlift' ) ?></h3>
	<?php
	$queue = $this->batch_analysis_service->waiting_for_response();
	if ( empty( $queue ) ) {
		esc_html_e( 'Nothing is currently being processed', 'wordlift' );
	} else {
		echo '<ul>';
		foreach ( $queue as $item ) {
			$pid = $item['id'];
			echo '<li><a href="' . get_edit_post_link( $pid ) . '">' . get_the_title( $pid ) . '</a></li>';
		}
		echo '</ul>';
	}
	?>
</div>
