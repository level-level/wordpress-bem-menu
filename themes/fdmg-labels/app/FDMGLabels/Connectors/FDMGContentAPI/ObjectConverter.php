<?php
namespace FDMGLabels\Connectors\FDMGContentAPI;

use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\attachment;
use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\fdmg_section;
use Clarkson_Core\WordPress_Object\ii_team;
use Clarkson_Core\WordPress_Object\post;
use Clarkson_Core\WordPress_Object\user;
use DateTime;
use FDMGContentAPIClient\Model\Article;
use FDMGContentAPIClient\Model\Genre as ModelGenre;
use FDMGContentAPIClient\Model\Image;
use FDMGContentAPIClient\Model\ImageMediaDetails;
use FDMGContentAPIClient\Model\Section as ModelSection;
use FDMGContentAPIClient\Model\TeamMember;
use FDMGContentAPIClient\Model\User as ModelUser;
use WP_Error;

class ObjectConverter {

	/**
	 * Convert a post to a FDMG Content API article
	 */
	public function article( post $post ): Article {
		$featured_image       = get_post( $post->get_thumbnail_id() );
		$featured_image       = $featured_image instanceof \WP_Post ? Objects::get_instance()->get_object( $featured_image ) : null;
		$featured_image_model = $featured_image instanceof attachment ? $this->image( $featured_image ) : null;

		/** @var fdmg_genre[]|WP_Error */
		$genres       = $post->get_terms( Genre::TAXONOMY_NAME );
		$genres       = is_array( $genres ) ? $genres : array();
		$genre_models = array_map(
			function ( fdmg_genre $genre ): ModelGenre {
				return $this->genre( $genre );
			}, $genres
		);

		/** @var fdmg_section[]|WP_Error */
		$sections       = $post->get_terms( Section::TAXONOMY_NAME );
		$sections       = is_array( $sections ) ? $sections : array();
		$section_models = array_map(
			function ( fdmg_section $section ): ModelSection {
				return $this->section( $section );
			}, $sections
		);

		$author       = $post->get_author();
		$author_model = $author instanceof user ? $this->user( $author ) : null;

		$published_date = new DateTime( $post->get_object()->post_date, wp_timezone() );
		$modified_date  = new DateTime( $post->get_object()->post_modified, wp_timezone() );

		if ( $published_date >= $modified_date ) {
			$modified_date = $published_date;
		}

		$article = new Article();
		$article->setId( $post->get_id() );
		$article->setDate( $published_date );
		$article->setModified( $modified_date );
		$article->setSlug( $post->get_post_name() );
		$article->setStatus( $post->get_status() );
		$article->setType( $post->get_post_type() );
		$article->setLink( $post->get_permalink() ?: null );
		$article->setTitle( $post->get_title() );
		$article->setTeaserTitle( $post->get_alternative_title() );
		$article->setContent( $post->get_unpaywalled_content() );
		$article->setExcerpt( $post->get_excerpt() );
		$article->setAuthor( $author_model );
		$article->setFeaturedImage( $featured_image_model );
		$article->setCommentStatus( $post->get_object()->comment_status );
		$article->setGenres( $genre_models );
		$article->setSections( $section_models );
		$article->setPaywall( $post->has_paywall_block() );
		$article->setTeamMembers(
			array_map(
				function ( ii_team $team_member ): TeamMember {
					return $this->team( $team_member );
				}, $post->get_authors()
			)
		);
		$article->setImportedId( $post->get_imported_old_id() );
		return $article;
	}

	/**
	 * Convert an attachment to a FDMG Content API image
	 */
	public function image( attachment $image ): Image {
		global $post;
		$media_details = wp_get_attachment_metadata( $image->get_id() ) ?: array();

		$media_details_model = new ImageMediaDetails();
		$media_details_model->setWidth( $media_details['width'] ?? 0 );
		$media_details_model->setHeight( $media_details['height'] ?? 0 );
		$media_details_model->setFile( $media_details['file'] ?? '' );
		$media_details_model->setUrl( wp_get_attachment_url( $image->get_id() ) ?: '' );
		$media_details_model->setFilesize( $media_details['filesize'] ?? 0 );

		$author       = $image->get_author();
		$author_model = $author instanceof user ? $this->user( $author ) : null;

		// Overwrite the global post to the image post to make sure the_content() works.
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$image_object = $image->get_object();
		$old_post     = $post;
		$post         = $image_object;
		setup_postdata( $post );
		ob_start();
		the_content();
		$content = ob_get_clean() ?: '';
		wp_reset_postdata();
		$post = $old_post;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		// Create the image model.
		$image_model = new Image();
		$image_model->setId( $image->get_id() );
		$image_model->setDate( new DateTime( $image->get_object()->post_date, wp_timezone() ) );
		$image_model->setModified( new DateTime( $image->get_object()->post_date, wp_timezone() ) );
		$image_model->setSlug( $image->get_post_name() );
		$image_model->setStatus( $image->get_status() );
		$image_model->setType( $image->get_post_type() );
		$image_model->setTitle( $image->get_title() );
		$image_model->setDescription( $content );
		$image_model->setAuthor( $author_model );
		$image_model->setCaption( $image->get_excerpt() );
		$image_model->setAltText( $image->get_meta( '_wp_attachment_image_alt', true ) ?: '' );
		$image_model->setMediaType( wp_attachment_is_image( $image->get_object() ) ? 'image' : 'file' );
		$image_model->setMimeType( $image->get_object()->post_mime_type );
		$image_model->setMediaDetails( $media_details_model );
		$image_model->setImportedId( $image->get_imported_old_id() );
		return $image_model;
	}

	/**
	 * Convert a user to a FDMG Content API user
	 */
	public function user( user $user ): ModelUser {
		$user_model = new ModelUser();
		$user_model->setId( $user->get_id() );
		$user_model->setUsername( $user->get_login() );
		$user_model->setDisplayName( $user->get_display_name() );
		$user_model->setFirstName( $user->get_user()->first_name );
		$user_model->setLastName( $user->get_user()->last_name );
		$user_model->setEmail( $user->get_email() );
		$user_model->setNickname( $user->get_user()->nickname );
		return $user_model;
	}

	/**
	 * Convert a genre to a FDMG Content API genre
	 */
	public function genre( fdmg_genre $genre ): ModelGenre {
		$genre_model = new ModelGenre();
		$genre_model->setId( $genre->get_id() );
		$genre_model->setSlug( $genre->get_slug() );
		$genre_model->setName( $genre->get_name() );
		$genre_model->setDescription( $genre->get_description() );
		$genre_model->setImportedId( $genre->get_imported_old_id() );
		return $genre_model;
	}

	/**
	 * Convert a section to a FDMG Content API section
	 */
	public function section( fdmg_section $section ): ModelSection {
		$section_model = new ModelSection();
		$section_model->setId( $section->get_id() );
		$section_model->setSlug( $section->get_slug() );
		$section_model->setName( $section->get_name() );
		$section_model->setDescription( $section->get_description() );
		$section_model->setImportedId( $section->get_imported_old_id() );
		return $section_model;
	}

	public function team( ii_team $team_member ): TeamMember {
		$team_member_model = new TeamMember();
		$team_member_model->setId( $team_member->get_id() );
		$team_member_model->setSlug( $team_member->get_post_name() );
		$team_member_model->setName( $team_member->get_title() );
		$team_member_model->setContent( $team_member->get_content() );
		$team_member_model->setExcerpt( $team_member->get_excerpt() );
		$team_member_model->setEmail( $team_member->get_email_address() );
		$team_member_model->setLinkedin( $team_member->get_linkedin() );
		$team_member_model->setLocation( $team_member->get_location() );
		$team_member_model->setImportedId( $team_member->get_imported_old_id() );
		return $team_member_model;
	}
}
