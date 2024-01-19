<?php

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

namespace FDMGLabels\Import\Helpers;

use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMException;
use DOMNode;
use DOMNodeList;
use DOMXPath;

class ContentParser {

	public const WRAPPER_ID = 'fdmg-import-wrapper';

	public function format_value( string $value, string $default = '' ): string {
		if ( $value === 'NULL' ) {
			return $default;
		}
		return $value;
	}

	/**
	 * Given content, is the content free for all and intro paragraph.
	 * Then prepend the paywall block if the content is not free (payed content)
	 * Then prepend the content with the intro paragraph
	 * Then insert the content in DOMDocument to make replacement of custom tags easier.
	 * Then Replace all custom <FDMG> tags.
	 * Then Save DOMDocument to html and return html.
	 * @param string $content
	 * @param bool $is_free
	 * @param string $intro
	 * @return string
	 * @throws DOMException
	 */
	public function parse_article( string $content, bool $is_free, string $intro ): string {
		if ( ! $is_free ) {
			$content = $this->add_paywall_block( $content );
		}
		$content  = $this->prepend_intro( $content, $intro );
		$content  = $this->wrap_content( $content );
		$html_dom = $this->get_dom_document( $content );

		if ( empty( $html_dom ) ) {
			return $content;
		}

		$html_dom = $this->replace_fdmg_text_frame( $html_dom );
		$html_dom = $this->parse_fdmg_image_tag( $html_dom );
		$html_dom = $this->parse_fdmg_pdf_tag( $html_dom );
		$html_dom = $this->replace_fdmg_related_link( $html_dom );
		$html_dom = $this->replace_fdmg_quote( $html_dom );
		$html_dom = $this->replace_base64_images( $html_dom );
		$html_dom = $this->replace_fdmg_stack_frame( $html_dom );
		$html_dom = $this->wrap_fdmg_html_embed( $html_dom );
		$html_dom = $this->remove_fdmg_alignment_tag( $html_dom );

		$content = $html_dom->saveHTML();

		if ( empty( $content ) ) {
			$content = '';
		}

		return $this->dewrap_content( $content );
	}

	/**
	 * Given content and intro paragraph
	 * Prepend intro paragraph as <!-- wp:paragraph --> to the content.
	 * @param string $content
	 * @param string $intro
	 * @return string
	 */
	public function prepend_intro( string $content, string $intro ): string {
		if ( empty( $intro ) ) {
			return $content;
		}

		$block_html = '<!-- wp:paragraph {"fontSize":"intro"} --><p class="has-intro-font-size">%s</p><!-- /wp:paragraph -->';
		$block_html = sprintf( $block_html, $intro );

		return $block_html . $content;
	}

	/**
	 * Given content
	 * Then prepend the <!-- wp:acf/block-ll-paywall-paywall /--> element to the content.
	 * @param string $content
	 * @return string
	 */
	public function add_paywall_block( string $content ): string {
		$paywall_block_html = '<!-- wp:acf/block-ll-paywall-paywall {"name":"acf/block-ll-paywall-paywall","data":{"require_roles_for_access":"1","_require_roles_for_access":"field_5632706c","allowed_user_roles":["fdmg_subscriber"],"_allowed_user_roles":"field_a47006e0"},"align":"center","mode":"preview"} /-->';
		return $paywall_block_html . $content;
	}

	/**
	 * Given HTML content
	 * When there is a tag called <fdmg-html-embed> with <fdmg-html-content> in it.
	 * Then use DomDocument to extract the content from it, save it in a reference array and replace it with a reference <div id="uniqueID"></div> element
	 * When the fdmg-html-embed elements are replaced. Generate new HTML from DOMDocument and string replace the reference div with the original html.
	 * We do not use DOMDocument to insert the new <-- wp:html /--> block into the content because this html can be invalid and dom document then does not allow insertion.
	 *
	 * In the exceptional case
	 * @param DOMDocument $html_dom
	 */
	public function wrap_fdmg_html_embed( DOMDocument $html_dom ): DOMDocument {
		$xpath          = new DOMXPath( $html_dom );
		$fdmg_html_tags = $xpath->query( '//fdmg-html-embed' );

		if ( ! $fdmg_html_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		$html_contents = array( '' );
		foreach ( $fdmg_html_tags as $html_tag ) { // loop over fdmg-html-embed elements

			if ( ! $html_tag instanceof DOMElement ) {
				continue;
			}

			// an <fdmg-html-embed element contains a fdmg-html-content element.
			$content_element = $html_tag->getElementsByTagName( 'fdmg-html-content' );
			$content         = '';

			if ( $content_element->length > 0 ) {
				$html_content = $html_dom->saveHTML( $content_element->item( 0 ) ); // get the html output as a string from <fdmg-html-content>
				if ( is_string( $html_content ) ) {
					// strip element tags
					$content = str_replace( array( '<fdmg-html-content>', '</fdmg-html-content>' ), array( '', '' ), $html_content );

					// convert to regular html
					$content = html_entity_decode( $content );
					$content = stripslashes( $content );
				}
			}

			// Setup default html block
			// setup Gutenberg block
			$html_block = '<!-- wp:html -->%s<!-- /wp:html -->';
			// insert content of <fdmg-html-content> element into Gutenberg block
			$html_block = sprintf( $html_block, $content );

			// If is oembed, replace html block with oembed block
			$oembed_block = $this->get_embed_gutenberg_block( $content );
			if ( $oembed_block ) {
				$html_block = $oembed_block;
			}

			// set up a reference array to be able to remove the wrapper later on.
			$id                   = uniqid();
			$html_contents[ $id ] = $html_block;

			// set up a div to wrap the gutenberg block in.
			// dom html needs to have 1 parent element.
			$dom_html_block = $html_dom->createElement( 'div' );
			$dom_html_block->setAttribute( 'id', $id );

			// insert the new div before the original fdmg-html-embed and remove the-html-embed
			if ( $html_tag->parentNode ) {
				$html_tag->parentNode->insertBefore( $dom_html_block, $html_tag );
				$html_tag->parentNode->removeChild( $html_tag );
			}
		}

		$html = $html_dom->saveHTML();

		if ( $html === false ) {
			return $html_dom;
		}

		// we are replacing things by hand because DOMHtml would not allow the crappy html sometimes as new element.
		foreach ( $html_contents as $key => $content ) {
			$html = str_replace( '<div id="' . $key . '"></div>', $content, $html );
		}

		$new_html_dom = $this->get_dom_document( $html );
		if ( $new_html_dom instanceof DOMDocument ) {
			return $new_html_dom;
		}

		return $html_dom;
	}

	/**
	 * Generate a gutenberg block from the embed content.
	 */
	protected function get_embed_gutenberg_block( string $embed_content_html ): string {
		$embed_content_html = $this->wrap_content( $embed_content_html );
		$embed_dom          = $this->get_dom_document( $embed_content_html );
		if ( ! $embed_dom instanceof DOMDocument ) {
			return '';
		}

		$embed_url = $this->get_url_from_embed_content( $embed_dom );
		if ( ! $embed_url ) {
			return '';
		}

		if ( str_contains( $embed_url, 'localfocuswidgets.net' ) ) {
			return $this->get_localfocus_embed_gutenberg_block( $embed_url );
		}

		$oembed      = _wp_oembed_get_object();
		$oembed_data = $oembed->get_data( $embed_url );
		if ( ! $oembed_data ) {
			return '';
		}

		$provider   = sanitize_title_with_dashes( $oembed_data->provider_name ?? '' );
		$responsive = '';
		$classes    = '';
		$classname  = '';
		$type       = 'wp-embed';
		if ( $oembed_data->type === 'rich' && $provider === 'twitter' ) {
			$type       = 'rich';
			$responsive = ',"responsive":true';
		} elseif ( $oembed_data->type === 'video' ) {
			$responsive = ',"responsive":true';
			$classes    = 'wp-embed-aspect-16-9 wp-has-aspect-ratio';
			$classname  = ',"className":"' . $classes . '"';
			$type       = 'video';
		} elseif ( $oembed_data->type === 'photo' || $oembed_data->type === 'image' ) {
			$type = 'image';
		}

		$json = '<!-- wp:embed {"url":"%s","type":"%s","providerNameSlug":"%s"%s%s} --><figure class="wp-block-embed is-type-%s is-provider-%s wp-block-embed-%s%s"><div class="wp-block-embed__wrapper">%s</div></figure><!-- /wp:embed -->';

		$filled_json = sprintf(
			$json,
			$embed_url,
			$type,
			$provider,
			$responsive,
			$classname,
			$type,
			$provider,
			$provider,
			$classes ? ' ' . $classes : '',
			PHP_EOL . $embed_url . PHP_EOL,
		);
		return $filled_json;
	}

	protected function get_localfocus_embed_gutenberg_block( string $url ): string {
		$id_parts = explode( '/', $url );
		$id       = array_pop( $id_parts );
		return sprintf(
			'<!-- wp:acf/block-localfocus {"name":"acf/block-localfocus","data":{"title":"LocalFocus embed","_title":"field_75397267","embed_id":"%s","_embed_id":"field_d90b3c10"},"align":"center","mode":"preview"} /-->',
			$id,
		);
	}

	/**
	 * Attempt to extract an embed url from the content of an embed.
	 */
	protected function get_url_from_embed_content( DOMDocument $embed_dom ): string {
		$url   = '';
		$xpath = new DOMXPath( $embed_dom );

		// Search for elements of specific types
		$iframe     = ( $xpath->query( '//iframe' ) ?: new DOMNodeList() )->item( 0 );
		$blockquote = ( $xpath->query( '//blockquote' ) ?: new DOMNodeList() )->item( 0 );

		// Handle specific elements
		if ( $iframe instanceof DOMElement ) {
			$url = $iframe->getAttribute( 'src' );
		} elseif ( $blockquote instanceof DOMElement ) {
			$anchors = $blockquote->getElementsByTagName( 'a' );
			$anchor  = $anchors->item( $anchors->count() - 1 );
			$url     = $anchor instanceof DomElement ? $anchor->getAttribute( 'href' ) : '';
		}

		// Convert url if needed
		if ( str_contains( $url, 'omny.fm' ) ) {
			$url = str_replace( '/embed', '', $url );
		} elseif ( str_contains( $url, 'youtube.com' ) ) {
			$url = str_replace( '/embed/', '/watch?v=', $url );
		} elseif ( str_contains( $url, 'twitter.com' ) || str_contains( $url, 'x.com' ) ) {
			$url = explode( '?', $url )[0];
		}
		return $url;
	}

	/**
	 * Given html content
	 * When there is a tag called <fdmg-alignment>
	 * Then remove tag from content.
	 * @param DOMDocument $html_dom
	 * @return DOMDocument
	 * @throws DOMException
	 */
	public function remove_fdmg_alignment_tag( DOMDocument $html_dom ): DOMDocument {
		$xpath          = new DOMXPath( $html_dom );
		$alignment_tags = $xpath->query( '//fdmg-alignment' );

		if ( ! $alignment_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $alignment_tags as $alignment_tag ) {
			if ( $alignment_tag->parentNode ) {
				$alignment_tag->parentNode->removeChild( $alignment_tag );
			}
		}
		return $html_dom;
	}

	/**
	 * Given html content
	 * When there is a tag called <fdmg-quote>
	 * Then extract the 'message' and 'author' from it.
	 * Then replace tag with GB <!-- wp:quote /--> block and insert the message and author in it.
	 * @param DOMDocument $html_dom
	 * @return DOMDocument
	 */
	public function replace_fdmg_quote( DOMDocument $html_dom ): DOMDocument {
		$xpath      = new DOMXPath( $html_dom );
		$quote_tags = $xpath->query( '//fdmg-quote' );

		if ( ! $quote_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $quote_tags as $quote_tag ) {
			if ( ! $quote_tag instanceof DOMElement ) {
				continue;
			}

			$message_element = $quote_tag->getElementsByTagName( 'fdmg-message' );
			$message         = $message_element->item( 0 )->nodeValue ?? '';

			$author_element = $quote_tag->getElementsByTagName( 'fdmg-author' );
			$author         = $author_element->item( 0 )->nodeValue ?? '';
			$author_html    = $author ? sprintf( '<cite>%s</cite>', $author ) : '';

			$json = '<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph {"fontSize":"regular"} --><p class="has-regular-font-size">%s</p><!-- /wp:paragraph -->%s</blockquote><!-- /wp:quote -->';

			$filled_json = sprintf( $json, wp_json_encode( $message, JSON_UNESCAPED_UNICODE ), $author_html );
			$quote_block = $html_dom->createDocumentFragment();
			$quote_block->appendXML( $filled_json );

			if ( $quote_tag->parentNode ) {
				$quote_tag->parentNode->insertBefore( $quote_block, $quote_tag );
				$quote_tag->parentNode->removeChild( $quote_tag );
			}
		}
		return $html_dom;
	}

	/**
	 * Given html content
	 * When there is a tag called <img>
	 * Determine if it has a base64 src attribute
	 * Then if it does, remove the img tag from the content.
	 * @param DOMDocument $html_dom
	 * @return DOMDocument
	 */
	public function replace_base64_images( DOMDocument $html_dom ): DOMDocument {
		$xpath    = new DOMXPath( $html_dom );
		$img_tags = $xpath->query( '//img' );

		if ( ! $img_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $img_tags as $img_tag ) {
			if ( ! $img_tag instanceof DOMElement ) {
				continue;
			}

			$src_attribute = $img_tag->getAttribute( 'src' );
			if ( ! str_contains( $src_attribute, 'base64' ) ) {
				continue;
			}

			if ( $img_tag->parentNode ) {
				$img_tag->parentNode->removeChild( $img_tag );
			}
		}
		return $html_dom;
	}

	/**
	 * Given html content
	 * When there is a tag called <fdmg-stack-frame>
	 * Remove the fdmg-stack-frame tag from the content.
	 * @param DOMDocument $html_dom
	 * @return DOMDocument
	 */
	public function replace_fdmg_stack_frame( DOMDocument $html_dom ): DOMDocument {
		$xpath            = new DOMXPath( $html_dom );
		$stack_frame_tags = $xpath->query( '//fdmg-stack-frame' );

		if ( ! $stack_frame_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $stack_frame_tags as $stack_frame_tag ) {
			if ( ! $stack_frame_tag instanceof DOMElement ) {
				continue;
			}

			if ( $stack_frame_tag->parentNode ) {
				$stack_frame_tag->parentNode->removeChild( $stack_frame_tag );
			}
		}
		return $html_dom;
	}

	/**
	 * Given html content
	 * When there is a tag called <fdmg-text-frame>
	 * The extract the heading and content from it.
	 * Then replace the tag with GB <!-- wp:acf/block-highlighted-content /--> and insert the heading and content in it.
	 * @param DOMDocument $html_dom
	 * @return DOMDocument
	 */
	public function replace_fdmg_text_frame( DOMDocument $html_dom ): DOMDocument {
		$xpath           = new DOMXPath( $html_dom );
		$text_frame_tags = $xpath->query( '//fdmg-text-frame' );

		if ( ! $text_frame_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $text_frame_tags as $text_frame_tag ) {
			if ( ! $text_frame_tag instanceof DOMElement ) {
				continue;
			}

			$heading_element = $text_frame_tag->getElementsByTagName( 'fdmg-heading' );
			$heading_element = $heading_element->item( 0 );
			$heading         = $heading_element ? ( $html_dom->saveHTML( $heading_element ) ?: '' ) : '';
			$heading         = mb_convert_encoding( $heading, 'HTML-ENTITIES', 'UTF-8' ) ?: '';
			$heading         = str_replace( array( '<fdmg-heading>', '</fdmg-heading>' ), array( '', '' ), $heading );

			// Parse the content of the text frame just like a normal post
			$content_element = $text_frame_tag->getElementsByTagName( 'fdmg-content' );
			$content_element = $content_element->item( 0 );
			if ( ! $content_element ) {
				continue;
			}

			// Prepend image
			$figure_html   = '';
			$images        = $text_frame_tag->getElementsByTagName( 'fdmg-text-frame-image' );
			$image_element = $images->item( 0 );
			if ( $image_element instanceof DOMElement ) {
				$figure_html = $this->fdmg_image_to_figure_html( $html_dom, $image_element );
			}

			$content = $html_dom->saveHTML( $content_element ) ?: '';
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) ?: '';
			$content = str_replace( array( '<fdmg-content>', '</fdmg-content>' ), array( '', '' ), $content );
			$content = str_replace( array( "\r\n", "\n" ), array( "\n\n", "\n\n" ), $content );
			$content = wpautop( $content );
			$content = $figure_html . $content;

			// Determine alignment
			$align         = 'center';
			$align_element = $text_frame_tag->getElementsByTagName( 'fdmg-alignment' )->item( 0 );
			if ( $align_element && in_array( $align_element->nodeValue, array( 'left', 'right', 'center' ), true ) ) {
				$align = $align_element->nodeValue;
			}

			$json = '<!-- wp:acf/block-highlighted-content {"name":"acf/block-highlighted-content","data":{"title":%s,"_title":"field_91e9c2a3","content":%s,"_content":"field_d75e3e94"},"align":"%s","mode":"preview"} /-->';

			$filled_json = sprintf( $json, wp_json_encode( $heading, JSON_UNESCAPED_UNICODE ), wp_json_encode( $content, JSON_UNESCAPED_UNICODE ), $align );
			$filled_json = wp_json_encode( $filled_json ); // double encoding is needed te prevent unescaping from breaking code. (\n becomes n)
			if ( $filled_json === false ) {
				$filled_json = ''; // reset to empty string. Make sure the original non html tag is still removed.
			}
			$filled_json               = trim( $filled_json, '"' );
			$highlighted_content_block = $html_dom->createDocumentFragment();
			$highlighted_content_block->appendXML( $filled_json );

			if ( $text_frame_tag->parentNode ) {
				$text_frame_tag->parentNode->insertBefore( $highlighted_content_block, $text_frame_tag );
				$text_frame_tag->parentNode->removeChild( $text_frame_tag );
			}
		}
		return $html_dom;
	}

	/**
	 * Given html content
	 * When there is a tag called <fdmg-related-link>
	 * Then extract the prefix, leadtext and relatedurl from it.
	 * Then replace the tag with GB <!-- wp:acf/block-inline-link /--> and insert the prefix, leadtext and relatedurl in it.
	 * @param DOMDocument $html_dom
	 * @return DOMDocument
	 */
	public function replace_fdmg_related_link( DOMDocument $html_dom ): DOMDocument {

		$xpath             = new DOMXPath( $html_dom );
		$related_link_tags = $xpath->query( '//fdmg-related-link' );

		if ( ! $related_link_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $related_link_tags as $related_link_tag ) {
			if ( ! $related_link_tag instanceof DOMElement ) {
				continue;
			}

			$prefix_element = $related_link_tag->getElementsByTagName( 'fdmg-prefix' );
			$prefix         = $prefix_element->item( 0 )->nodeValue ?? '';

			$leadtext_element = $related_link_tag->getElementsByTagName( 'fdmg-leadtext' );
			$leadtext         = $leadtext_element->item( 0 )->nodeValue ?? '';

			$relatedurl_element = $related_link_tag->getElementsByTagName( 'fdmg-relatedurl' );
			$relatedurl         = $relatedurl_element->item( 0 )->nodeValue ?? '';

			$json = '<!-- wp:acf/block-inline-link {"name":"acf/block-inline-link","data":{"title":%s,"_title":"field_b158bbce","link":{"title":%s,"url":%s,"target":""},"_link":"field_5198ad58"},"align":"right","mode":"preview"} /-->';

			$filled_json = sprintf( $json, wp_json_encode( $prefix, JSON_UNESCAPED_UNICODE ), wp_json_encode( $leadtext, JSON_UNESCAPED_UNICODE ), wp_json_encode( $relatedurl ) );

			$related_link_block = $html_dom->createDocumentFragment();
			$related_link_block->appendXML( $filled_json );

			if ( $related_link_tag->parentNode ) {
				$related_link_tag->parentNode->insertBefore( $related_link_block, $related_link_tag );
				$related_link_tag->parentNode->removeChild( $related_link_tag );
			}
		}

		return $html_dom;
	}

	/**
	 * Given html content
	 * When there is a tag called <fdmg-pdf>
	 * Then extract the fdmg-id from it.
	 * Then find the already imported file in DownloadMonitor posts and get the post_id.
	 * Then replace the tag with GB <!-- wp:acf/block-downloads /--> and insert the post_ids in it
	 * @param DOMDocument $html_dom
	 * @return DOMDocument
	 */
	public function parse_fdmg_pdf_tag( DOMDocument $html_dom ): DOMDocument {
		$xpath    = new DOMXPath( $html_dom );
		$pdf_tags = $xpath->query( '//fdmg-pdf' );

		if ( ! $pdf_tags instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $pdf_tags as $pdf_tag ) {
			if ( ! $pdf_tag instanceof DOMElement ) {
				continue;
			}

			$binary_id_element = $pdf_tag->getElementsByTagName( 'fdmg-id' );
			$binary_id         = $binary_id_element->item( 0 )->nodeValue ?? '';

			$binary_filename_element = $pdf_tag->getElementsByTagName( 'fdmg-filename' );
			$binary_filename         = $binary_filename_element->item( 0 )->nodeValue ?? '';

			$imported_content_finder = new ImportedContentFinder();
			$found_id                = $imported_content_finder->get_post_id_from_old_id( 'binary_file_' . $binary_id, 'attachment' );
			$found_id                = $found_id ?: $imported_content_finder->get_download_id_from_old_path( $binary_filename );
			if ( ! $found_id ) {
				continue;
			}

			$download_block_html = '<!-- wp:acf/block-downloads {"id":"block_%s","name":"acf/block-downloads","data":{"downloads_title":"","_downloads_title":"field_8c0f89d2","downloads_downloads":%s,"_downloads_downloads":"field_4f49a38d","downloads":"","_downloads":"field_432ce813"},"align":"right","mode":"preview"} /-->';
			$dlm_ids_json        = wp_json_encode( array( $found_id ) );
			$download_block_html = sprintf( $download_block_html, uniqid(), $dlm_ids_json );
			$gb_download_block   = $html_dom->createDocumentFragment();
			$gb_download_block->appendXML( $download_block_html );

			// replace current <fdmg-pdf> with new $gb_download_block tag
			if ( $pdf_tag->parentNode ) {
				$pdf_tag->parentNode->insertBefore( $gb_download_block, $pdf_tag );
				$pdf_tag->parentNode->removeChild( $pdf_tag );
			}
		}

		return $html_dom;
	}

	/**
	 * Given html content
	 *  When there is a tag called <fdmg-image> of <fdmg-text-frame-image>
	 *  Then extract the fdmg-id, fdmg-alt-text, fdmg-caption and fdmg-credit from it.
	 *  Then find the already imported attachment by the fdmg-id.
	 *  Then replace the tag with GB <!-- wp:image /--> and insert the attachment_id, alt-text, caption and credit in it.
	 *  @param DOMDocument $html_dom
	 * @return DOMDocument
	 * @throws DOMException
	 */
	public function parse_fdmg_image_tag( DOMDocument $html_dom ): DOMDocument {
		$xpath  = new DOMXPath( $html_dom );
		$images = $xpath->query( '//fdmg-image | //fdmg-text-frame-image' );

		if ( ! $images instanceof DOMNodeList ) {
			return $html_dom;
		}

		foreach ( $images as $image_element ) {
			if ( ! $image_element instanceof DOMElement ) {
				continue;
			}

			/**
			 * desired output example
			 * <!-- wp:image {"id":$attachment_id,"sizeSlug":"large","linkDestination":"none"} -->
			 * <figure class="wp-block-image size-large">
			 *   <img src="http://fdmg-labels.test/wp-content/uploads/2023/10/dirkie-1024x734.jpg" alt="" class="wp-image-44640"/>
			 *   <figcaption class="wp-element-caption">
			 *     <span class="caption">{$caption}</span>
			 *     <span class="photo-credit">{$credit}</span>
			 *   </figcaption>
			 * </figure>
			 * <!-- /wp:image -->
			 */

			$fdmg_alignment = $image_element->getElementsByTagName( 'fdmg-alignment' )->item( 0 );
			$align          = $this->get_wp_alignment_from_fdmg_alignment( $fdmg_alignment ? (string) $fdmg_alignment->nodeValue : '' );
			$align_json     = $align ? ', "align":"' . $align . '"' : '';
			$size           = $this->get_image_size_from_wp_alignment( $align );

			$attachment_id = $this->get_picture_id_from_image_element( $html_dom, $image_element );
			$figure_html   = $this->fdmg_image_to_figure_html( $html_dom, $image_element, $align, $size );
			if ( empty( $figure_html ) ) {
				continue;
			}

			$json_start          = sprintf( '<!-- wp:image {"id": %s, "sizeSlug":"%s", "linkDestination":"none"%s} -->', $attachment_id, $size, $align_json );
			$json_end            = '<!-- /wp:image -->';
			$gb_image_block_html = sprintf( '%s%s%s', $json_start, htmlspecialchars( $figure_html ), $json_end );
			$gb_image_block      = $html_dom->createDocumentFragment();
			$gb_image_block->appendXML( $gb_image_block_html );

			// replace current <fdmg-image> with new $gb_image_block tag
			if ( $image_element->parentNode ) {
				$image_element->parentNode->insertBefore( $gb_image_block, $image_element );
				$image_element->parentNode->removeChild( $image_element );
			}
		}
		return $html_dom;
	}

	protected function get_wp_alignment_from_fdmg_alignment( string $fdmg_alignment ): string {
		if ( str_contains( $fdmg_alignment, 'right' ) ) {
			return 'right';
		}
		if ( str_contains( $fdmg_alignment, 'left' ) ) {
			return 'left';
		}
		if ( str_contains( $fdmg_alignment, 'center' ) ) {
			return 'center';
		}
		return 'wide';
	}

	protected function get_image_size_from_wp_alignment( string $wp_alignment ): string {
		if ( in_array( $wp_alignment, array( 'left', 'right' ), true ) ) {
			return 'content_medium';
		}
		return 'full';
	}

	protected function get_picture_id_from_image_element( DOMDocument $html_dom, DOMElement $image_element ): int {
		$picture_id_element = $image_element->getElementsByTagName( 'fdmg-id' );
		$picture_id         = $picture_id_element->item( 0 )->nodeValue ?? '';
		$picture_id         = str_replace( 'image-', '', $picture_id );
		return ( new ImportedContentFinder() )->get_post_id_from_old_id( 'picture_' . $picture_id, 'attachment' );
	}

	/**
	 * Convert an <fdmg-image> or <fdmg-text-frame-image> element to html for a <figure> element.
	 */
	protected function fdmg_image_to_figure_html( DOMDocument $html_dom, DOMElement $image_element, string $align = '', string $size = 'full' ): string {
		$attachment_id = $this->get_picture_id_from_image_element( $html_dom, $image_element );

		$alt_text_element = $image_element->getElementsByTagName( 'fdmg-alt-text' );
		$alt_text         = $alt_text_element->item( 0 )->nodeValue ?? '';

		$caption_element = $image_element->getElementsByTagName( 'fdmg-caption' );
		$caption         = $caption_element->item( 0 )->nodeValue ?? '';

		$credit_element = $image_element->getElementsByTagName( 'fdmg-credit' );
		$credit         = $credit_element->item( 0 )->nodeValue ?? '';

		// create <figure>
		$figure  = $html_dom->createElement( 'figure' );
		$classes = array( 'wp-block-image', 'size-' . $size, 'is-style-default' );
		if ( $align ) {
			$classes[] = 'align' . $align;
		}
		$figure->setAttribute( 'class', implode( ' ', $classes ) );

		$attachment_src = wp_get_attachment_image_src( $attachment_id, $size ) ?: array();
		$attachment_url = $attachment_src[0] ?? '';

		// create <img>
		$img = $html_dom->createElement( 'img' );
		$img->setAttribute( 'src', $attachment_url );
		$img->setAttribute( 'alt', $alt_text );
		$img->setAttribute( 'class', 'wp-image-' . $attachment_id );
		$figure->appendChild( $img );

		// Create figcaption
		$fig_caption = $html_dom->createElement( 'figcaption' );
		$fig_caption->setAttribute( 'class', 'wp-element-caption' );
		$fig_caption_text_parts = array( $caption, $credit );
		$fig_caption_text_parts = array_filter( $fig_caption_text_parts );
		$fig_caption_text       = implode( ' ', $fig_caption_text_parts );
		if ( ! empty( $fig_caption_text ) ) {
			$fig_caption->appendChild( $html_dom->createTextNode( $fig_caption_text ) );
			$figure->appendChild( $fig_caption );
		}

		return $html_dom->saveHTML( $figure ) ?: '';
	}

	public function wrap_content( string $content ): string {
		return $this->get_content_prefix() . $content . $this->get_content_suffix();
	}

	public function get_dom_document( string $content ): ?DOMDocument {
		$converted_content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		if ( ! is_string( $converted_content ) || empty( $converted_content ) ) {
			return null;
		}

		$html_dom = new DOMDocument();
		$html_dom->loadHTML( $converted_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ); //LIBXML_HTML_NODEFDTD
		return $html_dom;
	}

	public function dewrap_content( string $content ): string {
		if ( str_starts_with( $content, $this->get_content_prefix() ) ) {
			$content = substr( $content, strlen( $this->get_content_prefix() ) );
			$content = substr( $content, 0, -7 );
		}

		return $content;
	}

	public function get_content_prefix(): string {
		return '<div id="' . self::WRAPPER_ID . '">';
	}

	public function get_content_suffix(): string {
		return '</div>';
	}
}
