<?php


namespace SchemaDotOrgTree;

interface Mappable {
	public function map();

	/**
	 * @param string $version
	 * @return void
	 */
	public function setVersion($version);
}