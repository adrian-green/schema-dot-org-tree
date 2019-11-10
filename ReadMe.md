About
=============
This library grabs and parses Schema.org into a structured array.
If you're looking for a library that can help developers work with Schema in php, checkout out
https://github.com/spatie/schema-org as it includes goodies and shortcuts for working with schema 
within your IDE. This repo does NOT include the actual schema.org json - it will download it from 
https://github.com/schemaorg/schemaorg/tree/master/data/releases on demand.

Cache/Storage of the resulting structured tree should be handled by your application. 
This library downloads and parses the json data into a structured array 
with some object normalization - nothing more.  


Install
=======
```php
composer require melting-server\schema-dot-org-tree
```

Usage
========
Initialize:
```php
include('./vendor/autoload.php');
use \schemaDotOrgTree\Tree;
```

Structured Schema Tree
------------------------------------
Download a version of schema.org and process it into a structure:
```php
//For the latest schema.org (default):
$tree = new Tree(); 

//For "all" of the latest schemas:
$tree = new Tree('5.0-all');

//For a specific version:
$tree = new Tree('3.7-core');
```

You can now access the structured tree:
```php
var_dump($tree->getTree()); //Structured tree 
```

Entities
------------------------------------
Get an entity:
```php
$entity = $tree->getEntity("http://schema.org/Thing");
```

Entities can have a parent Entity and may have children Entities:
```php
$parent = $entity->getParent(); // returns Entity or null
$children = $entity->getChildren(); // returns Entity[] (the array might be empty)
```

You can test to see if an Entity exists anywhere in the tree:
```php
$tree->isLocatable("http://schema.org/Thing");
```

Entities provide these public properties 
(not to be confused with the Entity's Schema Properties):
```php
// The Entity Property            The schema.org "field"
public $id;                    // from "@id"
public $type;                  // from "@type"
public $supersededBy;          // from "http://schema.org/supersededBy"
public $comment;               // from "rdfs:comment"
public $label;                 // from "rdfs:label"
public $subClassOf;            // from "rdfs:subClassOf"
public $purlSource;            // from "http://purl.org/dc/terms/source"
public $owlEquivalentProperty; // from "http://www.w3.org/2002/07/owl#equivalentClass"
public $category;              // from "http://schema.org/category"
public $closeMatch;            // from "http://www.w3.org/2004/02/skos/core#closeMatch"

/** @var string $version */
public $version;

/** @var Entity[] */
public $children = [];

/** @var Property[] */
public $properties = [];
```


Properties
------------------------------------
Get an entity's properties
```php
$properties = $entity->getProperties();      //with inherited properties
$properties = $entity->getProperties(false); //without inherited properties
```

Properties provide these public properties
```php
// The Entity Property            The schema.org "field"
public $id;                       // from @id
public $type;                     // from @type
public $domainIncludes = [];      // from http://schema.org/domainIncludes
public $rangeIncludes = [];       // from http://schema.org/rangeIncludes
public $comment = "";             // from rdfs:comment
public $label = "";               // from rdfs:label
public $purlSource;               // from http://purl.org/dc/terms/source
public $owlEquivalentProperty;    // from http://www.w3.org/2002/07/owl#equivalentProperty
public $subPropertyOf;            // from rdfs:subPropertyOf
public $category;                 // from http://schema.org/category
public $inverseOf;                // from http://schema.org/inverseOf
public $supersededBy;             // from http://schema.org/supersededBy

/** @var string */
public $version;

/** @var bool */
public $inherited; 
```

Advanced Usage
============================
Most developers should probably not need these things, but you're your own person.

Reader Data
----------------------------
Access the underlying json data from schema.org via 
```php
$tree->reader->getJson();
```


Multiple Version Support
------------------------------------
You can get a list of the available versions with:
```php
$tree->reader::VERSIONS
```

Currently supported versions:
 - '3.1-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.1/schema.jsonld',
 - '3.1-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.1/all-layers.jsonld',
 - '3.2-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.2/schema.jsonld',
 - '3.2-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.2/all-layers.jsonld',
 - '3.3-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.3/schema.jsonld',
 - '3.3-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.3/all-layers.jsonld',
 - '3.4-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.4/schema.jsonld',
 - '3.4-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.4/all-layers.jsonld',
 - '3.5-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.5/schema.jsonld',
 - '3.5-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.5/all-layers.jsonld',
 - '3.6-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.6/schema.jsonld',
 - '3.6-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.6/all-layers.jsonld',
 - '3.7-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.7/schema.jsonld',
 - '3.7-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.7/all-layers.jsonld',
 - '3.8-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.8/schema.jsonld',
 - '3.8-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.8/all-layers.jsonld',
 - '3.9-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/3.9/schema.jsonld',
 - '4.0-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/4.0/all-layers.jsonld',
 - '4.0-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/4.0/schema.jsonld',
 - '5.0-all' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/5.0/all-layers.jsonld',
 - '5.0-core' => 'https://github.com/schemaorg/schemaorg/raw/master/data/releases/5.0/schema.jsonld',

```

With multiple trees in memory, you can retrieve specfic entities from specific versions.
```php
$tree->getEntityReference('latest', 'http://schema.org/Thing');
```

Each class/property/dataType is loaded with knowledge about which version from which it was loaded.
```php
$entity->version;
```