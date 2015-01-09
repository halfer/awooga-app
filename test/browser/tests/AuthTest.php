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

		// Get the login time for the user record
		$timesBefore = $this->getLoginTimes();
		//print_r($timesBefore);

		// When we logon, let's check we now have a service record
		$loginUrl = self::DOMAIN . '/auth?provider=test';
		$this->visit($loginUrl);
		$this->waitUntilRedirected($loginUrl);
		$idsFirst = $this->getUserIdsFromUsername($this->getDriver(), self::TEST_USER);
		$this->assertTrue(is_array($idsFirst), "Check we have a service record after logging on");

		// @todo Check the login time is changed for the user record
		$timesAfterFirstLogin = $this->getLoginTimes();
		//print_r($timesAfterFirstLogin);

		// Log out and log back in again
		$redirectUrl = $this->current_url();
		$this->
			find('#auth-logout a')->
				click()->
			end()->
			visit($loginUrl);
		$this->waitUntilRedirected($redirectUrl);

		// @todo Check the login time is changed for the user record
		$timesAfterSecondLogin = $this->getLoginTimes();
		//print_r($timesAfterSecondLogin);
	}

	protected function getLoginTimes()
	{
		$sql = "
			SELECT
				u.last_login_at user_last_login_at,
				ua.last_login_at user_auth_last_login_at
			FROM
				user u
				LEFT JOIN user_auth ua ON (ua.user_id = u.id)
			WHERE
				/*
				We can't check the service table as it might not exist. So I'm making the
				assumption that the username is set as the service name, and that there is
				only zero or one related service provider.
				*/
				u.username = :service_name
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':service_name' => self::TEST_USER, ));

		return $statement->fetch(\PDO::FETCH_ASSOC);
	}
}