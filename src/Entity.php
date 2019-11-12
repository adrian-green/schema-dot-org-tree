<?php


namespace SchemaDotOrgTree;

use \stdClass;

/**
 * Class Entity
 * Equivalent to any json row in the schema.jsonld which has type = rdfs:class
 * @package SchemaDotOrgTree
 */
class Entity implements Mappable {

	public $id; // from "@id"
	public $type; // from "@type"
	public $supersededBy; // from "http://schema.org/supersededBy"
	public $comment; // from "rdfs:comment"
	public $label; // from "rdfs:label"
	public $subClassOf; // from "rdfs:subClassOf"
	public $purlSource; // from "http://purl.org/dc/terms/source"
	public $owlEquivalentProperty; // from "http://www.w3.org/2002/07/owl#equivalentClass"
	public $category; // from "http://schema.org/category"
	public $closeMatch; // from "http://www.w3.org/2004/02/skos/core#closeMatch"

	/** @var string */
	public $version;

	/** @var Entity[] */
	public $children = [];

	/** @var Property[] */
	public $properties = [];

	/** @var Entity|null */
	public $parent;

	/**
	 * Stored so I don't have to recalculate it on subsequent requests.
	 * @see getProperties()
	 * @var null|Property[]
	 */
	private $inheritedProperties = null;


	CONST PROPERTY_MAP = [
		"@id" => 'id',
		"@type" => 'type',
		"http://schema.org/supersededBy" => 'supersededBy',
		"rdfs:comment" => 'comment',
		"rdfs:label" => 'label',
		"rdfs:subClassOf" => 'subClassOf',
		"http://purl.org/dc/terms/source" => 'purlSource',
		"http://www.w3.org/2002/07/owl#equivalentClass" => 'owlEquivalentProperty',
		"http://schema.org/category" => 'category',
		"http://www.w3.org/2004/02/skos/core#closeMatch" => 'closeMatch',
	];

	/**
	 * @return array
	 */
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

	/**
	 * @return Entity|null
	 */
	public function getParent() {
		return $this->parent;

	}

	public function getChildren() {
		return $this->children;
	}

	/**
	 * @return bool|string
	 */
	public function parentClassName() {
		if(!isset($this->subClassOf)) {
			return false;
		}
		$parentClass = $this->subClassOf;
		if(is_array($parentClass)
		   AND is_a($parentClass[0], stdClass::class)
		       AND isset($parentClass[0]->{'@id'})
		) {
			$parentClass = $parentClass[0]->{'@id'};
		}
		if(!is_string($parentClass)) {
			return false;
		}
		return $parentClass;
	}

	public function addProperty(Property $property) {
		$property->inherited = false;
		$this->properties[$property->id] = $property;
	}

	/**
	 * @param bool $includeInherited
	 *
	 * @return Property[]
	 */
	public function getProperties($includeInherited = true) {
		$properties = $this->properties;
		if(false === $includeInherited OR false === $this->parentClassName()) {
			return $properties;
		}
		if($this->inheritedProperties !== null) {
			return $this->inheritedProperties;
		}
		$parent = $this->getParent();
		if(!$parent) {
			return $properties;
		}
		$this->inheritedProperties = array_merge($properties, $parent->getProperties());
		return $this->inheritedProperties;
	}

	static public function jsonSchemaIsEntity(stdClass $jsonSchemaItem) {
		if(isset($jsonSchemaItem->{'rdfs:comment'})
			AND strtolower(substr($jsonSchemaItem->{'rdfs:comment'}, 0, 9)) === "data type"
		) {
			var_dump($jsonSchemaItem);
			echo "^^ thinks he's an entity";
			return false;
		}
		if(is_array($jsonSchemaItem->{'@type'})) {
			if(in_array("http://schema.org/DataType", $jsonSchemaItem->{'@type'})) {
				return false;
			}
		}
		if($jsonSchemaItem->{'@type'} === "rdfs:Class") {
			return true;
		}


		return false;
	}
}