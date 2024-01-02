<?php
namespace Clarkson_Core\WordPress_Object;

class base_term extends Clarkson_Term {
	public function get_formatted_description(): string {
		return term_description( $this->get_id() ) ?: '';
	}

	public function get_imported_old_id(): ?string {
		$id = $this->get_meta( 'fdmg_import_old_id', true );
		if ( ! is_string( $id ) || empty( $id ) ) {
			return null;
		}
		return $id;
	}
}
