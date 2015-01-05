<?php

namespace Awooga\Testing\Browser;

class AuthTest extends TestCase
{
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
		$this->assertEquals('Logout testuser', $element->text());
	}
}