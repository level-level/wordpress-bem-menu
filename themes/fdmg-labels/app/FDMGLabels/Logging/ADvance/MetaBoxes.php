<?php
namespace FDMGLabels\Logging\ADvance;

use FDMGLabels\Logging\ADvanceLogger;
use GFAPI;

class MetaBoxes {

	public function register_hooks(): void {
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'add_entry_meta_box' ), 10, 3 );
	}

	/**
	 * Add ADvance logbook meta box to GravityForms entry detail page
	 *
	 * @param array<string,mixed> $meta_boxes
	 * @param array<string,mixed> $entry
	 * @param array<string,mixed> $form
	 * @return array<string,mixed>
	 */
	public function add_entry_meta_box( array $meta_boxes, array $entry, array $form ): array {
		// Check if this entry has an ADvance feed
		$feeds = GFAPI::get_feeds( null, $form['id'], 'fdmg_advance_newsletter', true );
		if ( empty( $feeds ) ) {
			return $meta_boxes;
		}

		$meta_boxes['advance_logbook'] = array(
			'title'    => _x( 'ADvance Newsletter Logbook', 'ADvance', 'fdmg-labels-admin' ),
			'callback' => array( $this, 'render_entry_meta_box' ),
			'context'  => 'side',
		);

		return $meta_boxes;
	}

	/**
	 * Render the ADvance logbook meta box
	 *
	 * @param array<string,mixed> $args
	 * @return void
	 */
	public function render_entry_meta_box( array $args ): void {
		$entry    = $args['entry'];
		$entry_id = (int) $entry['id'];

		$logger    = new ADvanceLogger();
		$log_lines = array_reverse( $logger->get_gravityforms_entry_logs( $entry_id ) );

		// Use WordPress native rendering instead of Twig for simplicity in admin context
		$this->render_logbook_html( $log_lines );
	}

	/**
	 * Render the logbook HTML
	 *
	 * @param array<\FDMGLabels\Logging\LogLine> $log_lines
	 * @return void
	 */
	private function render_logbook_html( array $log_lines ): void {
		?>
		<div class="advance-logbook">
			<div class="advance-logbook__inner" style="max-height: 400px; overflow: auto;">
				<?php if ( ! empty( $log_lines ) ) : ?>
					<table class="widefat striped">
						<tbody>
							<?php foreach ( $log_lines as $log_line ) : ?>
								<tr>
									<td>
										<p>
											<?php
											switch ( $log_line->get_level() ) {
												case 'success':
													echo '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>';
													break;
												case 'error':
													echo '<span class="dashicons dashicons-warning" style="color: #d63638;"></span>';
													break;
												case 'warning':
													echo '<span class="dashicons dashicons-warning" style="color: #dba617;"></span>';
													break;
												default:
													echo '<span class="dashicons dashicons-info" style="color: #72aee6;"></span>';
													break;
											}
											?>
											<strong><?php echo esc_html( gmdate( 'Y-m-d H:i:s', $log_line->get_timestamp() ) ); ?></strong>
										</p>
										<p>
											<?php echo wp_kses_post( $log_line->get_message() ); ?>
										</p>

										<?php $extra = $log_line->get_extra(); ?>
										<?php if ( ! empty( $extra ) ) : ?>
											<details>
												<summary><?php echo esc_html_x( 'Extra information', 'Log', 'fdmg-labels' ); ?></summary>
												<pre style="white-space: pre-wrap; word-wrap: break-word;"><?php echo esc_html( wp_json_encode( $extra, JSON_PRETTY_PRINT ) ); ?></pre>
											</details>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p>
						<span class="dashicons dashicons-info" style="color: #72aee6;"></span>
						<strong><?php echo esc_html( gmdate( 'Y-m-d H:i:s' ) ); ?></strong>
					</p>
					<p>
						<?php echo esc_html_x( 'The logbook is empty.', 'Log', 'fdmg-labels' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
