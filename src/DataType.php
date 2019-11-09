<?php


namespace SchemaDotOrgTree;


class DataType  implements Mappable {
	public $id;
	public $type;
	public $comment;
	public $label;
	public $purlSource;
	public $sameAs;

	/** @var string */
	public $version;


	CONST PROPERTY_MAP = [
		"@id" => 'id',
		"@type" => 'type',
		"rdfs:comment" => 'comment',
		"rdfs:label" => 'label',
		"http://purl.org/dc/terms/source" => 'purlSource',
		"http://schema.org/sameAs" => 'sameAs',
	];

	/**
	 * @param string $version
	 *
	 * @return void
	 */
	public function setVersion( $version ) {
		$this->version = $version;
	}

	public function map() {
		return self::PROPERTY_MAP;
	}
}