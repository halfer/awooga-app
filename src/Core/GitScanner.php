<?php

namespace Awooga\Core;

require_once 'BaseGitImporter.php';

use \Awooga\Exceptions\SeriousException;
use \Awooga\Exceptions\TrivialException;

/**
 * This class is in response to code analysis identifying GitImporter as being too unwieldy. This
 * takes out the scanning operations, but it might be worth splitting the importer into its
 * four constituent parts.
 */
class GitScanner extends BaseGitImporter
{
	// @todo Is this misnamed? Currently only throws exception, doesn't disable
	const MAX_FAILS_BEFORE_DISABLE = 5;
	const MAX_REPORT_SIZE = 60000;

	protected $runId;
	protected $repoId;
	protected $repoRoot;
	protected $searcher;

	use Database;

	/**
	 * Initialises the Git scanner
	 * 
	 * @param integer $runId
	 * @param integer $repoId
	 * @param string $repoRoot
	 */
	public function __construct($runId, $repoId, $repoRoot)
	{
		$this->runId = $runId;
		$this->repoId = $repoId;
		$this->repoRoot = $repoRoot;
		$this->debug = false;
	}

	/**
	 * Scans a folder for JSON reports
	 * 
	 * @throws Exception
	 */
	public function scanRepo($repoPath)
	{
		// Set up iterator to find JSON files
		$directory = new \RecursiveDirectoryIterator($this->repoRoot . '/' . $repoPath);
		$iterator = new \RecursiveIteratorIterator($directory);
		$regex = new \RegexIterator($iterator, '/^.+\.json$/i', \RecursiveRegexIterator::GET_MATCH);

		$this->writeDebug("Finding files in repo:");

		// Keep a log of reports we create/update
		$reportIds = array();

		try
		{
			foreach ($regex as $file)
			{
				$reportPath = $file[0];
				try
				{
					$reportIds[] = $this->scanReport($reportPath);
					$this->writeDebug("\tFound report ..." . substr($reportPath, -80));
				}
				catch (TrivialException $e)
				{
					// Counting trivial exceptions still contributes to failure/stop limit
					$this->repoLog($this->repoId, self::LOG_TYPE_SCAN, $e->getMessage(), self::LOG_LEVEL_ERROR_TRIVIAL);
					$this->doesErrorCountRequireHalting();
				}
				// For serious/other exceptions, rethrow to outer catch
				catch (\Exception $e)
				{
					throw $e;
				}
			}
		}
		catch (SeriousException $e)
		{
			// These errors are always OK to save directly into the log
			$this->repoLog($this->repoId, self::LOG_TYPE_SCAN, $e->getMessage(), self::LOG_LEVEL_ERROR_SERIOUS);
			$this->disableRepo();

			// Rethrow for benefit of caller
			throw $e;
		}

		return $reportIds;
	}

	/**
	 * Scans a single report and commits it to the database
	 * 
	 * Review the JSON recursion limit, is this OK?
	 * 
	 * @param string $reportPath
	 * @throws Exception
	 */
	protected function scanReport($reportPath)
	{
		// Unlikely to happen, we just scanned!
		if (!file_exists($reportPath))
		{
			throw new SeriousException('File cannot be found');
		}

		$size = filesize($reportPath);
		if ($size > self::MAX_REPORT_SIZE)
		{
			throw new \Awooga\Exceptions\FileException('Report of ' . $size . ' bytes is too large');
		}

		// Let's get this in array form
		$data = json_decode(file_get_contents($reportPath), true, 4);

		// If this is not an array, throw a trivial exception
		if (!is_array($data))
		{
			throw new TrivialException("Could not parse report into an array");
		}

		// Parse the data
		$version = $this->grabElement($data, 'version');
		$url = $this->grabElement($data, 'url');
		$issues = $this->grabElement($data, 'issues');
		// @todo Move these back to var names, or better still move them to setters directly
		$reportData = array(
			'title' => $this->grabElement($data, 'title'),
			'description' => $this->grabElement($data, 'description'),
			'notified_date' => $this->grabElement($data, 'author_notified_date'),
		);

		// Handle depending on version
		switch ($version)
		{
			case 1:
				$report = new Report($this->repoId);
				$report->setDriver($this->pdo);
				$report->setTitle($reportData['title']);
				$report->setUrl($url);
				$report->setDescription($reportData['description']);
				$report->setIssues($issues);
				$report->setAuthorNotifiedDate($reportData['notified_date']);
				$reportId = $report->save();
				
				// This will only be called if the above does not throw an exception
				$this->tryReindexing($report);
				break;
			default:
				throw new TrivialException("Unrecognised version number");
		}

		return $reportId;
	}

	/**
	 * Bomb out if there's been too many errors recently
	 */
	protected function doesErrorCountRequireHalting()
	{
		// If there are too many errors in this run, throw Exceptions\SeriousException
		$sql = "
			SELECT COUNT(*) count
			FROM repository_log
			WHERE
				repository_id = :repo_id
				AND run_id = :run_id
				AND log_level = :level_trivial
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(
				':repo_id' => $this->repoId,
				':run_id' => $this->runId,
				':level_trivial' => self::LOG_LEVEL_ERROR_TRIVIAL,
			)
		);

		// Make sure the query went ok
		if (!$ok)
		{
			throw new \Exception('Query to count trivial errors did not run');
		}

		if ($statement->fetchColumn() > self::MAX_FAILS_BEFORE_DISABLE)
		{
			throw new SeriousException(
				"Too many failures with this repo recently, please see log"
			);
		}
	}

	/**
	 * Disables the specified repo
	 */
	protected function disableRepo()
	{
		$sql = "
			UPDATE repository
			SET is_enabled = false
				WHERE id = :repo_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':repo_id' => $this->repoId, ));
		if (!$ok)
		{
			throw new \Exception('Failed disabling this repo');
		}

		return $ok;
	}

	/**
	 * Grabs a keyed value from a hash
	 * 
	 * @param array $data
	 * @param string $key
	 * @return mixed
	 */
	protected function grabElement(array $data, $key)
	{
		return isset($data[$key]) ? $data[$key] : null;
	}

	/**
	 * Reindexes a newly saved report, if an indexer is available
	 * 
	 * @param \Awooga\Core\Report $report
	 */
	protected function tryReindexing(Report $report)
	{
		if ($searcher = $this->getSearcher())
		{
			$report->index($searcher);
		}
	}

	/**
	 * Gets the currently set searcher
	 * 
	 * Fails silently if no searcher has been set
	 * 
	 * @return Searcher
	 */
	public function getSearcher()
	{
		return $this->searcher;
	}

	/**
	 * Sets the search module, so we can do document indexing
	 * 
	 * @param Searcher $searcher
	 */
	public function setSearcher(Searcher $searcher)
	{
		$this->searcher = $searcher;
	}
}