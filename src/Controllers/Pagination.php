<?php

namespace Awooga\Controllers;

trait Pagination
{
	protected $page;
	protected $rowCount;

	public function setPage($page)
	{
		$this->page = $page;
	}

	public function getPage($untaint = true)
	{
		return $untaint ? (int) $this->page : $this->page;
	}

	/**
	 * Redirects if the page number is invalid
	 */
	public function validatePageAndGetRows($pageSize)
	{
		$this->setRowCount();
		$pageNumber = $this->verifyPageNumber($this->getRowCount(), $pageSize);
		if ($pageNumber !== true)
		{
			$slug = $this->getMenuSlug();
			$this->pageRedirectAndExit($pageNumber ? $slug . '/' . $pageNumber : $slug);
		}

		return $this->getPaginatedRows($pageSize);
	}

	/**
	 * Required for validatePageAndGetRows
	 */
	abstract protected function getPaginatedRows($pageSize);

	/**
	 * Required for validatePageAndGetRows
	 */
	abstract protected function setRowCount();

	/**
	 * Required for validatePageAndGetRows
	 */
	abstract protected function getMenuSlug();

	protected function getRowCount()
	{
		return $this->rowCount;
	}

	/**
	 * Gets a limit statement for a paginated screen
	 * 
	 * @param integer $limit
	 * @return string
	 */
	protected function getLimitClause($limit)
	{
		$start = ($this->getPage() - 1) * $limit;
		$limitSafe = (int) $limit;

		return "LIMIT {$start}, {$limitSafe}";
	}

	protected function getMaxPage($rowCount, $pageSize)
	{
		return ceil($rowCount / $pageSize);
	}

	/**
	 * Returns true if the page number passes validation
	 * 
	 * @param integer $rowCount
	 * @param integer $pageSize
	 * @return boolean|string
	 */
	protected function verifyPageNumber($rowCount, $pageSize)
	{
		if (!is_null($this->page))
		{
			$realPage = (string) (int) $this->page;

			if ($realPage < 1)
			{
				// Redirect to page without number
				return false;
			}

			// Redirect if page is too big
			$maxPage = $this->getMaxPage($rowCount, $pageSize);
			if ($realPage > $maxPage)
			{
				return $maxPage;
			}

			if ($realPage !== $this->page)
			{
				// Redirect to cleaned version (001 -> 1, 1hello -> 1)
				return $realPage;
			}
		}

		return true;
	}

	protected function pageRedirectAndExit($path)
	{
		$url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $path;
		header('Location: ' . $url);
		exit();
	}
}