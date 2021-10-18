<?php
namespace Clarkson\Utilities;

use DOMDocument;

class ContentConverter {
	/**
	 * Convert content to Gutenberg blocks if possible
	 *
	 * @param string $content (HTML) content. Should not contain doctype, html or body tags.
	 * @return string
	 */
	public function content_to_blocks( string $content ): string {
		// First replace whitespace characters
		$content = str_replace( '&nbsp;', ' ', $content );

		// Convert to blocks
		if ( $content !== wp_strip_all_tags( $content ) ) {
			// Does not start with html tag, convert to paragraph
			if ( strpos( $content, '<' ) !== 0 ) {
				return $this->generate_paragraph( '<p>' . $content . '</p>' );
			}
			// Convert html to blocks
			return $this->html_to_blocks( $content );
		} else {
			// Is plaintext, convert to paragraph
			return $this->plaintext_to_paragraph( $content );
		}
	}

	/**
	 * Convert plaintext to a paragraph block
	 *
	 * @param string $content
	 * @return string
	 */
	protected function plaintext_to_paragraph( string $content ): string {
		$content = nl2br( $content );
		$content = $this->generate_paragraph( '<p>' . $content . '</p>' );
		return $content;
	}

	/**
	 * Convert html code to gutenberg blocks
	 *
	 * @param string $content
	 * @return string
	 */
	protected function html_to_blocks( string $content ): string {
		$content = html_entity_decode( $content, ENT_QUOTES );
		$content = str_replace( '&', '&amp;', $content ); // Fix error htmlParseEntityRef: no name in Entity

		$dom                     = new DOMDocument();
		$dom->preserveWhiteSpace = false; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$html_loaded             = $dom->loadHTML( '<!DOCTYPE html><html><head></head><body>' . mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) . '</body></html>' );
		if ( ! $html_loaded || ! $dom->hasChildNodes() ) {
			return $content;
		}

		$new_content = '';
		$body        = $dom->getElementsByTagName( 'body' )->item( 0 );
		if ( $body !== null && $body->hasChildNodes() ) {
			foreach ( $body->childNodes as $node ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$new_content .= $this->node_to_block( $node->nodeName, $dom->saveHTML( $node ) ); //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}
		}

		$new_content = html_entity_decode( $new_content, ENT_QUOTES );

		return $new_content;
	}

	/**
	 * Convert html node to gutenberg block
	 *
	 * @param string $node_name Node name
	 * @param string $html Complete Node HTML
	 * @return string
	 */
	protected function node_to_block( string $node_name, string $html ): string {
		$block = $html;
		// Quit early if #text nodename
		if ( $node_name === '#text' ) {
			return $block;
		}

		switch ( $node_name ) {
			case 'p':
				$block = $this->generate_paragraph( $html );
				break;
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
				$block = $this->generate_heading( $html );
				break;
			case 'ul':
				$block = $this->generate_list( $html );
				break;
			case 'ol':
				$block = $this->generate_list( $html, true );
				break;
			default:
				$block = $this->generate_classic( $html );
				break;
		}
		return $block;
	}

	/**
	 * Generate a classic block
	 *
	 * @param string $content HTML content
	 * @return string
	 */
	protected function generate_classic( string $content ): string {
		// If has no html, add paragraph tags
		if ( $content === wp_strip_all_tags( $content ) ) {
			$content = '<p>' . $content . '</p>';
		}
		return $content;
	}

	/**
	 * Generate a list block
	 *
	 * @param string $content HTML content
	 * @param boolean $ordered
	 * @return string
	 */
	protected function generate_list( string $content, bool $ordered = false ): string {
		$properties = '';
		if ( $ordered ) {
			$properties = (string) wp_json_encode( array( 'ordered' => true ) ) . ' ';
		}
		$string = '<!-- wp:list ' . $properties . '-->' . PHP_EOL . $content . PHP_EOL . '<!-- /wp:list -->';
		return $string;
	}

	/**
	 * Generate a heading block
	 *
	 * @param string $content HTML content
	 * @return string
	 */
	protected function generate_heading( string $content ): string {
		$string = '<!-- wp:heading -->' . PHP_EOL . $content . PHP_EOL . '<!-- /wp:heading -->';
		return $string;
	}

	/**
	 * Generate a paragraph block
	 *
	 * @param string $content HTML content
	 * @return string
	 */
	protected function generate_paragraph( string $content ): string {
		$string = '<!-- wp:paragraph -->' . PHP_EOL . $content . PHP_EOL . '<!-- /wp:paragraph -->';
		return $string;
	}
}
