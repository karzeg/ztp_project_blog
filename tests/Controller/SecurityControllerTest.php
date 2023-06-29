<?php
/**
 * Security controller tests.
 */

namespace App\Tests\Controller;

use App\Tests\WebBaseTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest.
 */
class SecurityControllerTest extends WebBaseTestCase
{
    /**
     * Test client.
     */
    protected KernelBrowser $httpClient;

    /**
     * @return void void
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test '/login' route.
     */
    public function testLoginRoute(): void
    {
        $expectedStatusCode = 200;

        $this->httpClient->request('GET', '/login');

        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $resultHttpStatusCode);
    }

    /**
     * Test '/logout' route.
     */
    public function testLogoutRoute(): void
    {
        $expectedStatusCode = 302;

        $this->httpClient->request('GET', '/logout');

        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $resultHttpStatusCode);
    }
}