<?php

namespace Awooga\Controllers;

trait Pagination
{
	protected $page;

	public function setPage($page)
	{
		$this->page = $page;
	}

	/**
	 * Gets a limit statement for a paginated screen
	 * 
	 * @param integer $limit
	 * @return string
	 */
	protected function getLimitClause($limit)
	{
		$start = (int) ($this->page - 1) * $limit;
		$limitSafe = (int) $limit;

		return "LIMIT {$start}, {$limitSafe}";
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
			$maxPage = ceil($rowCount / $pageSize);
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