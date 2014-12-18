<?php

namespace Awooga\Controllers;

abstract class PaginatedController extends BaseController
{
	protected $baseTable;
	protected $menuSlug;

	// These are separated out, as some paginated controllers don't use PaginatedController
	use \Awooga\Traits\Pagination;

	/**
	 * Fetches the count for this table, based on the base table name
	 * 
	 * @todo Add exception if it is not set
	 */
	protected function setRowCount()
	{
		$this->baseSetRowCount($this->baseTable);
	}

	/**
	 * Gets the menu slug for this page
	 * 
	 * @todo Add exception if it is not set
	 * @todo Could this move to BaseController?
	 * 
	 * @return string
	 */
	protected function getMenuSlug()
	{
		return $this->menuSlug;
	}
}