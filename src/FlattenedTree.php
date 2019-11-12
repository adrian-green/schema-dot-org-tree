<?php


namespace SchemaDotOrgTree;


use stdClass;

class FlattenedTree {
	/** @var Entity[] */
	public $entities;
	/** @var Property[] */
	public $properties;
	/** @var DataType[] */
	public $dataTypes;

	public function __construct($json, $version) {
		$this->entities = [];
		$this->properties = [];
		$this->dataTypes = [];
		foreach($json->{"@graph"} as $item) {
			/** @var $obj Mappable */
			if(Entity::jsonSchemaIsEntity($item)) {
				$obj = new Entity();
			} else if($item->{'@type'} === "rdf:Property") {
				$obj = new Property();
			} else {
				$obj = new DataType();
			}

			$obj->setVersion($version);
			foreach($obj->map() as $jsonKey => $classProperty) {
				if(isset($item->{$jsonKey})) {
					if(is_a($item->{$jsonKey}, stdClass::class)
						AND isset($item->{$jsonKey}->{'@id'})
					) {
						//flatten sub object to just the id
						$obj->{$classProperty} = $item->{$jsonKey}->{'@id'};
					} else {
						$obj->{$classProperty} = $item->{$jsonKey};
					}
				}
			}

			if(is_a($obj, Entity::class)) {
				if(is_array($obj->subClassOf)
					AND is_a($obj->subClassOf[0], stdClass::class)
					AND isset($obj->subClassOf[0]->{'@id'})
			    ) {
					$obj->subClassOf = $obj->subClassOf[0]->{'@id'};
				}
				$this->entities[ $item->{'@id'} ] = $obj;
			} else if(is_a($obj, Property::class)) {
				//clean up for properties' properties
				if(is_string($obj->domainIncludes)) {
					$obj->domainIncludes = [$obj->domainIncludes];
				} else if(is_array($obj->domainIncludes)) {
					$newDomainIncludes = [];
					foreach($obj->domainIncludes as $stdClass) {
						$newDomainIncludes[] = $stdClass->{'@id'};
					}
					$obj->domainIncludes= $newDomainIncludes;
				}
				if(is_string($obj->rangeIncludes)) {
					$obj->rangeIncludes = [$obj->rangeIncludes];
				} else if(is_array($obj->rangeIncludes)) {
					$newRangeIncludes = [];
					foreach($obj->rangeIncludes as $stdClass) {
						$newRangeIncludes[] = $stdClass->{'@id'};
					}
					$obj->rangeIncludes= $newRangeIncludes;
				}

				$this->properties[ $item->{'@id'} ] = $obj;
			} else {
				$this->dataTypes[ $item->{'@id'}] = $obj;
			}
		}

		//Now that each entry is in the flattenedTree, we can fetch parent references
		foreach($this->entities as $key => $entity) {
			if($entity->parentClassName()) {
				if(!isset($this->entities[$entity->parentClassName()])) {
					// Orphaned Reference. Will be handled by the tree maker
					continue;
				}
				$parent = $this->entity($entity->parentClassName());
				$this->entities[$key]->parent = $parent;
			}
		}
	}

	public function entity($schemaId) {
		return $this->entities[$schemaId];
	}
}