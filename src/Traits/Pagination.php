<?php

namespace Awooga\Traits;

trait Pagination
{
	protected $page;
	protected $rowCount;

	public function setPage($page)
	{
		$this->page = $page;
	}

	/**
	 * Gets a rendered paginated page
	 * 
	 * @param string $templateName
	 * @param integer $pageSize
	 * @return string Generated HTML
	 */
	public function getPaginatedRender($templateName, $pageSize)
	{
		// Redirects if the page number is invalid, fetches rows
		$rows = $this->validatePageAndGetRows($pageSize);

		// Render the rows
		return $this->render(
			$templateName,
			array(
				// We use the template name as the primary array name too
				$templateName => $rows,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($this->getRowCount(), $pageSize),
			)
		);
	}

	/**
	 * Must be provided by the client class
	 * 
	 * (In practice this is often provided by the parent e.g. BaseController
	 * 
	 * @param string $name
	 * @param array $values
	 */
	abstract protected function render($name, array $values = array());

	/**
	 * Gets page number
	 * 
	 * We check for less than 1, since null (no page number) is valid
	 * 
	 * @return integer
	 */
	public function getPage()
	{
		return (int) $this->page < 1 ? 1 : $this->page;
	}

	/**
	 * Redirects if the page number is invalid
	 * @param integer $pageSize
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
	 * 
	 * @param integer $pageSize
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

	/**
	 * Returns the largest permissible page number
	 * 
	 * @param integer $pageSize
	 */
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

			// @todo I think this can be stripped out, page numbers are verified in the router now
			if ($realPage !== $this->page)
			{
				// Redirect to cleaned version (001 -> 1, 1hello -> 1)
				return $realPage;
			}
		}

		return true;
	}

	/**
	 * A helper method for controllers of paginated tables, to set the number of rows
	 * 
	 * @param string $table
	 */
	protected function baseSetRowCount($table)
	{
		$escaped = $this->getDriver()->quote($table);
		$sql = "SELECT COUNT(*) FROM $escaped";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();

		$this->rowCount = $statement->fetchColumn();
	}

	/**
	 * A helper method to fetch the rows for a paginated screen
	 * 
	 * @param string $sql
	 * @param integer $pageSize
	 */
	protected function baseGetPaginatedRows($sql, $pageSize)
	{
		$limitClause = $this->getLimitClause($pageSize);
		$statement = $this->getDriver()->prepare($sql . ' ' . $limitClause);
		$statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);		
	}

	/**
	 * The database driver must be provided by the trait client
	 * 
	 * @return \PDO
	 */
	abstract protected function getDriver();

	/**
	 * Performs a browser redirect
	 * 
	 * @param string $path
	 */
	protected function pageRedirectAndExit($path)
	{
		$url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $path;
		header('Location: ' . $url);
		exit();
	}
}