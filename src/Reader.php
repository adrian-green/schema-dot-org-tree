<?php


namespace SchemaDotOrgTree;

use GuzzleHttp\Client;
use \Exception;

class Reader {
	const VERSIONS = [
		'3.1-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.1/schema.jsonld',
		'3.1-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.1/all-layers.jsonld',
		'3.2-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.2/schema.jsonld',
		'3.2-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.2/all-layers.jsonld',
		'3.3-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.3/schema.jsonld',
		'3.3-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.3/all-layers.jsonld',
		'3.4-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.4/schema.jsonld',
		'3.4-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.4/all-layers.jsonld',
		'3.5-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.5/schema.jsonld',
		'3.5-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.5/all-layers.jsonld',
		'3.6-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.6/schema.jsonld',
		'3.6-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.6/all-layers.jsonld',
		'3.7-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.7/schema.jsonld',
		'3.7-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.7/all-layers.jsonld',
		'3.8-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.8/schema.jsonld',
		'3.8-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.8/all-layers.jsonld',
		'3.9-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.9/schema.jsonld',
		'4.0-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/4.0/all-layers.jsonld',
		'4.0-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/4.0/schema.jsonld',
		'5.0-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/5.0/all-layers.jsonld',
		'5.0-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/5.0/schema.jsonld',
	];
	const LATEST = '5.0-core';

	/** @var \stdClass[]  */
	static private $json = [];

	private $url = "";
	private $version = "";

	/**
	 * Reader constructor.
	 *
	 * @param null|string $version use Null or "latest" for latest
	 *
	 * @throws \Exception
	 */
	public function __construct($version = null) {
		if( $version === null or strtolower($version) === "latest") {
			$version = self::LATEST;
		}
		if(!key_exists($version, self::VERSIONS)) {
			throw new Exception( "Version " . $version . " not defined in \\SchemaDotOrgTree\\Reader::VERSIONS. Versions are lower-case, fyi.", 500);
		}

		$this->version = $version;
		$this->url = self::VERSIONS[$this->version];
	}

	private function download() {
		if(!isset(self::$json[$this->version])) {
			$client = new Client();
			$response = $client->get($this->url);
			self::$json[$this->version] = json_decode($response->getBody()->getContents());
		}
		return self::$json[$this->version];
	}

	public function getJson() {
		return $this->download();
	}

	/**
	 * A helper function to show all the possible attributes of a class or property with the number of times that attribute is used.
	 * @return array
	 */
	public function allAttributes() {
		$classAttributes = [];
		$propertyAttributes = [];
		$valueAttributes = [];
		$valueTypes = [];
		foreach($this->getJson()->{'@graph'} as $item) {
			if($item->{'@type'} === "rdfs:Class") {
				foreach($item as $key => $value) {
					if(!isset($classAttributes[$key])) {
						$classAttributes[$key] = 0;
					}
					$classAttributes[$key]++;
				}
			} else if($item->{'@type'} === "rdf:Property") {
				foreach($item as $key => $value) {
					if(!isset($propertyAttributes[$key])) {
						$propertyAttributes[$key] = 0;
					}
					$propertyAttributes[$key]++;
				}
			} else {
				$type = $item->{'@type'};
				if(is_array($type)) {
					if($type[1] !== "rdfs:Class" && $type[0] !== "rdfs:Class") {
						var_dump($item);
						die();
					}
				}
				foreach($item as $key => $value) {
					if(!isset($valueAttributes[$key])) {
						$valueAttributes[$key] = 0;
					}
					$valueAttributes[$key]++;
				}
			}
		}
		return ['class' => $classAttributes,
		        'property' => $propertyAttributes,
				'value' => $valueAttributes,
				'valueTypes' => $valueTypes,
			];
	}
}