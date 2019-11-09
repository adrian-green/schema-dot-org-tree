<?php


namespace SchemaDotOrgTree;


class Property implements Mappable {
	public $id; // from @id
	public $type; //from @type
	public $domainIncludes = []; //from http://schema.org/domainIncludes
	public $rangeIncludes = []; //from http://schema.org/rangeIncludes
	public $comment = ""; // from rdfs:comment
	public $label = ""; // from rdfs:label
	public $purlSource; // from http://purl.org/dc/terms/source
	public $owlEquivalentProperty; // from http://www.w3.org/2002/07/owl#equivalentProperty
	public $subPropertyOf; //from rdfs:subPropertyOf
	public $category; // from http://schema.org/category
	public $inverseOf; // from http://schema.org/inverseOf
	public $supersededBy; // from http://schema.org/supersededBy

	/** @var string */
	public $version;

	public $inherited;

	CONST PROPERTY_MAP = [
		'@id' => 'id',
		'@type' => 'type',
		'http://schema.org/domainIncludes' => 'domainIncludes',
		'http://schema.org/rangeIncludes' => 'rangeIncludes',
		'rdfs:comment' => 'comment',
		'rdfs:label' => 'label',
		'http://purl.org/dc/terms/source' => 'purlSource',
		'http://www.w3.org/2002/07/owl#equivalentProperty' => 'owlEquivalentProperty',
		'rdfs:subPropertyOf' => 'subPropertyOf',
		'http://schema.org/category' => 'category',
		'http://schema.org/inverseOf' => 'inverseOf',
		'http://schema.org/supersededBy' => 'supersededBy',
	];

	public function map() {
		return self::PROPERTY_MAP;
	}

	/**
	 * @param string $version
	 *
	 * @return void
	 */
	public function setVersion( $version ) {
		$this->version = $version;
	}
}