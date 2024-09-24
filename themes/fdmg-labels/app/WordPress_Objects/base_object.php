<?php
namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\ACFPages\ThemeOptions;
use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Objects;
use Clarkson_Core\Templates;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\Clarkson_Term;
use FDMGLabels\Comments\Comments;
use FDMGLabels\StaticPages\AuthController;
use WP_Term;
use WPSEO_Primary_Term;

class base_object extends Clarkson_Object {

	protected string|null $search_result_title = null;

	/**
	 * Custom function to get the search result title.
	 * This allows ElasticPress to add highlight tags, because it uses the_title().
	 * Function should only be used on the search template, or where the 's'
	 * query variable is set.
	 */
	public function get_search_result_title(): string {
		global $post;
		if ( ! isset( $this->search_result_title ) ) {
			if ( ! empty( $post ) ) {
				$oldpost = clone $post;
			} else {
				$oldpost = null;
			}
			$post = $this->_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $this->_post );
			ob_start();
			the_title();
			$this->search_result_title = ob_get_clean() ?: '';
			wp_reset_postdata();
			$post = $oldpost; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		return $this->search_result_title;
	}

	/**
	 * Get WPSEO (Yoast) primary term or first term from array
	 *
	 * @return null|Clarkson_Term
	 */
	public function get_primary_term( string $taxonomy ) {
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$core_objects       = Objects::get_instance();
			$wpseo_primary_term = new WPSEO_Primary_Term( $taxonomy, $this->get_id() );
			$primary_term_id    = $wpseo_primary_term->get_primary_term();

			if ( $primary_term_id ) {
				$term = get_term( (int) $primary_term_id );

				if ( $term instanceof WP_Term ) {
					return $core_objects->get_term( $term );
				}
			}
		}

		$terms = $this->get_terms( $taxonomy );
		$terms = is_array( $terms ) ? $terms : array();
		return array_shift( $terms );
	}

	/**
	 * Get estimate of reading time in minutes.
	 * The average reading speed of humans is 238 words per minute.
	 * @see https://www.sciencedirect.com/science/article/abs/pii/S0749596X19300786#:~:text=Based%20on%20the%20analysis%20of,and%20260%20wpm%20for%20fiction.
	 *
	 * It uses the raw content instead of the parsed content to determine the amount of words.
	 * This is needed to prevent an endless loop.
	 *
	 * @return int
	 */
	public function get_reading_time_in_minutes(): int {
		$content = wp_strip_all_tags( $this->get_raw_content() );
		$words   = (int) str_word_count( $content );
		$minutes = (int) ceil( $words / 238 );
		return $minutes < 1 ? 1 : $minutes;
	}

	/**
	 * We use the post_date_gmt variable to show the date on an article, this variable converts the time to GMT.
	 * We want to use the date/time in the timezone where we have published the article
	 *
	 * @param string $format
	 * @param boolean $gmt
	 * @return string
	 */
	public function get_date_i18n( $format = 'U', $gmt = false ): string {
		return date_i18n( $format, strtotime( $this->_post->post_date ), $gmt );
	}

	/**
	 * @return array<string,string>
	 */
	public function get_publish_date( string $date_format = '' ): array {
		$date_format = $date_format ?: get_option( 'date_format' );

		return array(
			'date'      => $this->get_date_i18n( $date_format ),
			'datetime'  => $this->get_date_i18n( 'Y-m-d' ),
			'date_full' => $this->get_date_i18n( 'j F Y' ),
			'readable'  => $this->get_human_readable_date(),
		);
	}

	/**
	 * Gets the comment form HTML for the current object.
	 * This does not check for custom user permissions.
	 */
	public function get_comment_form(): string {
		global $post;
		// Setup global post variable for comment_form() function
		$post_backup = $post;
		setup_postdata( $this->_post );
		$post = $this->_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Setup variables needed to render comment form
		$twig                = Templates::get_instance();
		$user                = wp_get_current_user();
		$user_identity       = $user->exists() ? $user->display_name : '';
		$login_required_html = ( new Comments() )->get_subscription_required_html( $this );

		$rules_page           = get_field( sprintf( '%s_%s', ThemeOptions::OPTION_GROUP, ThemeOptions::OPTION_DISCUSSION_RULES_PAGE ), 'options' );
		$rules_page_html      = $rules_page instanceof Clarkson_Object ? sprintf(
			_x( 'Comments must comply with our %s.', 'Comments', 'fdmg-labels' ),
			sprintf(
				'<a href="%s">%s</a>',
				$rules_page->get_permalink(),
				_x( 'commenting rules', 'Comments', 'fdmg-labels' ),
			),
		) : null;
		$comment_notes_before = array(
			_x( 'Your email address will not be published.', 'Comments', 'fdmg-labels' ),
			$rules_page_html,
			_x( 'Required fields are marked *.', 'Comments', 'fdmg-labels' ),
		);

		$must_log_in_as_notes = array_merge(
			array(
				sprintf(
					_x( 'Logged in as %s.', 'Comments', 'fdmg-labels' ),
					$user_identity,
				),
			), $comment_notes_before
		);

		$args = array(
			'class_form'           => 'form-group',
			'format'               => 'html5',
			'label_submit'         => _x( 'Add comment', 'Comments', 'fdmg-labels' ),
			'title_reply'          => _x( 'Leave a comment', 'Comments', 'fdmg-labels' ),
			'comment_field'        => $twig->render_twig(
				'styleguide/components/comments/form-fields/textarea.twig',
				array(), true
			),
			'must_log_in'          => $login_required_html,
			'logged_in_as'         => sprintf(
				'<p class="logged-in-as">%s</p>',
				implode( '<br/>', array_filter( $must_log_in_as_notes ) )
			),
			'comment_notes_before' => sprintf(
				'<p class="comment-notes">%s</p>',
				implode( '<br/>', array_filter( $comment_notes_before ) )
			),
		);

		ob_start();
		comment_form( $args, $this->_post );
		$html = ob_get_contents() ?: '';
		ob_end_clean();

		// Reset global post variable
		$post = $post_backup; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_reset_postdata();
		return $html;
	}

	/**
	 * Display the publish date of the post in a human readable format if it's been published for less than 24 hours
	 * Appends the post year if it's not already present in the formatted date and when it's from a previous year
	 */
	public function get_human_readable_date(): string {
		$date_str       = $this->get_date_i18n();
		$date           = (int) $date_str;
		$current_time   = time();
		$time_diff      = $current_time - $date;
		$formatted_date = date_i18n( get_option( 'date_format' ), $date );

		// If the post is older than 24 hours, show the date
		if ( $time_diff >= 86400 ) { // 86400 seconds = 24 hours
			// Check if the year is already in the formatted date
			$post_year    = date_i18n( 'Y', $date );
			$current_year = date_i18n( 'Y', $current_time );

			// Append the post year only if it's not already present and it's from a previous year
			if ( $post_year < $current_year && strpos( $formatted_date, $post_year ) === false ) {
				$formatted_date .= ' ' . $post_year;
			}
		} elseif ( gmdate( 'Y-m-d', $date ) === gmdate( 'Y-m-d', $current_time ) ) {
			// If the date is today, show absolute time
			$formatted_date = date_i18n( get_option( 'time_format' ), $date );
		}

		return $formatted_date;
	}

	public function get_imported_old_id(): ?string {
		return ( (string) $this->get_meta( 'fdmg_import_old_id', true ) ) ?: null;
	}

	public function get_thumbnail_url(){
		$post = get_post();
		$thumbnail_url = get_the_post_thumbnail_url( $post, 'thumbnail' );
		return $thumbnail_url;
	}
}
