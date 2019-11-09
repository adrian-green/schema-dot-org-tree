<?php


namespace SchemaDotOrgTree;


class Tree {
	/** @var Reader */
	public $reader;

	/** @var FlattenedTree[] */
	static public $flattenedTrees;

	/** @var Tree[] */
	static public $trees;

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
	static public $leafIndexes = [];

	/** @var Entity[] */
	static public $orphanedEntities = [];

	static public $orphanedProperties = [];

	public $version;

	/**
	 * Reader constructor.
	 *
	 * @param null|string $version use Null or "latest" for latest
	 *
	 * @throws \Exception
	 */
	public function __construct($version = 'latest') {
		$this->version = $version;
		$this->reader = new Reader($this->version);
		self::$flattenedTrees[$version] = new FlattenedTree($this->reader->getJson(), $this->version);
		$this->createStructureFromFlattenedTree($version);
		$this->assignProperties($version);
	}

	/**
	 * @return Tree
	 */
	public function getTree() {
		return self::$trees[$this->version];
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
		self::$orphanedProperties = [];
		foreach(self::$flattenedTrees[$version]->properties as $property) {
			foreach($property->domainIncludes as $entityId) {
				if(self::isLocatableInVersion($version, $entityId)) {
					$entity = &self::getEntityReference($version, $entityId);
					if($entity) {
						$entity->addProperty($property);
					}
				} else {
					self::$orphanedProperties[] = $property;
				}
			}
		}
	}

	private function createStructureFromFlattenedTree($version) {
		$childEntities = [];
		self::$trees[$this->version] = [];
		self::$leafIndexes[$this->version] = [];
		foreach( self::$flattenedTrees[$version]->entities as $entity) {
			if($entity->subClassOf) {
				$childEntities[] = $entity;
			} else {
				self::addLevelZeroEntity($version, $entity);
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
		self::$orphanedEntities = $childEntities;
	}

	static private function addLevelZeroEntity($version, Entity &$childEntity) {
		self::$trees[$version][$childEntity->id] = $childEntity;
		self::$leafIndexes[$version][$childEntity->id] = [];
		return;
	}

	static public function isLocatableInVersion($version, $entityId) {
		return isset(self::$leafIndexes[$version][$entityId]);
	}

	/**
	 * @param $version
	 * @param $entityId
	 *
	 * @return Entity
	 */
	static public function &getEntityReference($version, $entityId) {
		$traversal = self::$leafIndexes[$version][$entityId];
		if(count($traversal) === 0) {
			return self::$trees[$version][$entityId];
		}
		$directParentId = array_pop($traversal);
		$directParent = self::getEntityReference($version, $directParentId);
		return $directParent->children[$entityId];
	}

	/**
	 * @param $version
	 * @param Entity $childEntity
	 *
	 * @return bool True if successful
	 */
	static private function addChildEntity($version, Entity &$childEntity) {
		$parentClass = $childEntity->parentClassName();
		if(false === $parentClass) {
			return false;
		}
		if(!self::isLocatableInVersion($version, $parentClass)) {
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
		self::$leafIndexes[$version][$childEntity->id] = array_reverse($backwardsPath);
		return true;
	}
}