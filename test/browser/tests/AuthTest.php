<?php

namespace Awooga\Testing\Browser;

class AuthTest extends TestCase
{
	use \Awooga\Traits\Runner;
	use \Awooga\Traits\AuthSession;

	const TEST_USER = 'testuser';

	/**
	 * Check page title
	 * 
	 * @driver simple
	 */
	public function testTitle()
	{
		$page = $this->visit(self::DOMAIN . '/auth');
		$this->assertEquals('Login — Awooga', $page->find('title')->text());
	}

	/**
	 * Check that Github is available
	 * 
	 * @driver phantomjs
	 */
	public function testProviders()
	{
		// @todo 
	}

	/**
	 * Checks the test-environment auth provider is working
	 * 
	 * @driver phantomjs
	 */
	public function testTestProvider()
	{
		$this->
			visit(self::DOMAIN . '/auth?provider=test');
		$this->encodedScreenshot("Screenshot of auth provider");

		$element = $this->
			visit(self::DOMAIN . '/auth?provider=test')->
			find('#auth-logout');
		$this->assertEquals('Logout ' . self::TEST_USER, $element->text());
	}

	/**
	 * Checks that the login times are updated for users and service provider records
	 * 
	 * @todo This needs finishing, do with #15
	 * 
	 * @driver phantomjs
	 */
	public function testLoginTimesUpdated()
	{
		// Ensure there is no service record to start with
		$idsEmpty = $this->getUserIdsFromUsername($this->getDriver(), self::TEST_USER);
		$this->assertFalse($idsEmpty, "Check there is no service record initially");

		// There's no user+service record yet, so this should be empty
		$timeBefore = $this->getLoginTimes();
		$this->assertFalse($timeBefore, "Check there is no service-user pair to start with");

		// When we logon, let's check we now have a service record
		$loginUrl = self::DOMAIN . '/auth?provider=test';
		$this->visit($loginUrl);
		$this->waitUntilRedirected($loginUrl);
		$idsFirst = $this->getUserIdsFromUsername($this->getDriver(), self::TEST_USER);
		$this->assertTrue(is_array($idsFirst), "Check we have a service record after logging on");

		// Check the login time now exists
		$timeAfterFirstLogin = $this->getLoginTimes();
		$this->assertTrue(
			is_array($timeAfterFirstLogin),
			"Ensure we have a login time for the first login"
		);

		// Log out, sleep, and log back in again
		sleep(1);
		$redirectUrl = $this->current_url();
		$this->
			find('#auth-logout a')->
				click()->
			end()->
			visit($loginUrl);
		$this->waitUntilRedirected($redirectUrl);

		// Check the login time is changed for the service record
		$timeAfterSecondLogin = $this->getLoginTimes();
		$pass =
			is_array($timeAfterSecondLogin) &&
			$timeAfterSecondLogin['last_login_at'] > $timeAfterFirstLogin['last_login_at'];
		$this->assertTrue($pass, "Ensure we have an updated login time for the second login");
	}

	protected function getLoginTimes()
	{
		$sql = "
			SELECT
				ua.last_login_at
			FROM
				user u
				INNER JOIN user_auth ua ON (ua.user_id = u.id)
			WHERE
				ua.username = :service_name
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':service_name' => self::TEST_USER, ));

		return $statement->fetch(\PDO::FETCH_ASSOC);
	}
}