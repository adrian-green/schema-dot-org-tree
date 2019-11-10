<?php


namespace SchemaDotOrgTree;


use Exception;

class Tree {
	/** @var Reader */
	public $reader;

	/** @var FlattenedTree[] */
	public $flattenedTrees;

	/** @var Tree[] */
	public $trees;

	/**
	 * @var array Traversal Instructions
	 * Stored by version.
	 *
	 * @example
	 *  [
	 *    ParentA => [
	 *                  A,
	 *                  B [
	 *                      X
	 *                    ]
	 *               ]
	 *    ParentB => [ C ]
	 *  ]
	 * would yield an index:
	 *  ParentA => []
	 *  ParentB => []
	 *  A => [ParentA]
	 *  B => [ParentA]
	 *  C => [ParentB]
	 *  X => [ParentA, B]
	 *
	 */
	public $leafIndexes = [];

	/** @var Entity[] */
	public $orphanedEntities = [];

	public $orphanedProperties = [];

	public $version;

	/**
	 * Reader constructor.
	 *
	 * @param null|string $version use Null or "latest" for latest
	 *
	 * @throws Exception
	 */
	public function __construct($version = 'latest') {
		$this->version = $version;
		$this->reader = new Reader($this->version);
		$this->flattenedTrees[$version] = new FlattenedTree($this->reader->getJson(), $this->version);
		$this->createStructureFromFlattenedTree($version);
		$this->assignProperties($version);
	}

	/**
	 * @return Tree
	 */
	public function getTree() {
		return $this->trees[$this->version];
	}

	/**
	 * @param string $entityId
	 *
	 * @return Entity|null
	 */
	public function getEntity($entityId) {
		return self::getEntityReference($this->version, $entityId);
	}

	/**
	 * @param string $entityId
	 *
	 * @return bool
	 */
	public function isLocatable($entityId) {
		return self::isLocatableInVersion($this->version, $entityId);
	}

	private function assignProperties($version) {
		$this->orphanedProperties = [];
		foreach($this->flattenedTrees[$version]->properties as $property) {
			foreach($property->domainIncludes as $entityId) {
				if(self::isLocatableInVersion($version, $entityId)) {
					$entity = &self::getEntityReference($version, $entityId);
					if($entity) {
						$entity->addProperty($property);
					}
				} else {
					$this->orphanedProperties[] = $property;
				}
			}
		}
	}

	private function createStructureFromFlattenedTree($version) {
		$childEntities = [];
		$this->trees[$this->version] = [];
		$this->leafIndexes[$this->version] = [];
		foreach( $this->flattenedTrees[$version]->entities as $entity) {
			if($entity->subClassOf) {
				$childEntities[] = $entity;
			} else {
				$this->addLevelZeroEntity($version, $entity);
			}
		}

		$i = 0;
		while(count($childEntities) and $i++ < 99) {
			$orphanedChildren = [];
			foreach($childEntities as $entity) {
				if(!self::addChildEntity($version, $entity)) {
					$orphanedChildren[] = $entity;
				}
			}
			$childEntities = $orphanedChildren;
		}
		$this->orphanedEntities = $childEntities;
	}

	private function addLevelZeroEntity($version, Entity &$childEntity) {
		$this->trees[$version][$childEntity->id] = $childEntity;
		$this->leafIndexes[$version][$childEntity->id] = [];
		return;
	}

	public function isLocatableInVersion($version, $entityId) {
		return isset($this->leafIndexes[$version][$entityId]);
	}

	/**
	 * @param $version
	 * @param $entityId
	 *
	 * @return Entity
	 */
	public function &getEntityReference($version, $entityId) {
		$traversal = $this->leafIndexes[$version][$entityId];
		if(count($traversal) === 0) {
			return $this->trees[$version][$entityId];
		}
		$directParentId = array_pop($traversal);
		$directParent = $this->getEntityReference($version, $directParentId);
		return $directParent->children[$entityId];
	}

	/**
	 * @param $version
	 * @param Entity $childEntity
	 *
	 * @return bool True if successful
	 */
	private function addChildEntity($version, Entity &$childEntity) {
		$parentClass = $childEntity->parentClassName();
		if(false === $parentClass) {
			return false;
		}
		if(!$this->isLocatableInVersion($version, $parentClass)) {
			return false;
		}

		$parentEntity = &self::getEntityReference($version, $parentClass);
		$parentEntity->children[$childEntity->id] = $childEntity;

		// Add to Leaf Index
		$currentEntity = $childEntity;
		$backwardsPath = [];
		$i = 0;
		while($i++ < 99) {
			if(! isset($currentEntity->subClassOf)
			   OR !is_string($currentEntity->subClassOf)
			   OR strlen($currentEntity->subClassOf) === 0
			) {
				break;
			}
			$backwardsPath[] = $currentEntity->subClassOf;
			$currentEntity = self::getEntityReference($version, $currentEntity->parentClassName());
		}
		$this->leafIndexes[$version][$childEntity->id] = array_reverse($backwardsPath);
		return true;
	}
}