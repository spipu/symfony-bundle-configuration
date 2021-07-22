<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spipu\ConfigurationBundle\Tests\Functional;

use Spipu\CoreBundle\Tests\WebTestCase;

class ConfigurationTest extends WebTestCase
{
    public function testAdmin()
    {
        $client = static::createClient();

        // Home page not logged
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Configurations")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Login
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'admin',
                '_password' => 'password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Home page logged with "Configuration" access
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Configurations")')->count());

        // Conf List
        $crawler = $client->clickLink('Configurations');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Advanced Search")')->count());

        // Conf List with filter
        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[code]' => 'app.website.url']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("1 item found")')->count());
        $this->assertEquals(1, $crawler->filter('td:contains("https://my-website.fr")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Edit")')->count());

        // Edit Page
        $crawler = $client->clickLink('Edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());
        $this->assertEquals(1, $crawler->filter('div[class=card-header]:contains("app.website.url")')->count());
        $this->assertCrawlerHasInputValue($crawler, 'generic_value', 'https://my-website.fr');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Form Submit - missing value
        $crawler = $client->submit( $crawler->filter('form#form_configuration')->form(), ['generic[value]' => '']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());
        $this->assertCrawlerHasAlert($crawler, 'is required');

        // Form Submit - bad value
        $crawler = $client->submit( $crawler->filter('form#form_configuration')->form(), ['generic[value]' => 'bad;']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());
        $this->assertCrawlerHasAlert($crawler, 'must be a valid url');

        // Form Submit - good value
        $client->submit( $crawler->filter('form#form_configuration')->form(), ['generic[value]' => 'http://goodurl.fr']);
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("1 item found")')->count());
        $this->assertEquals(1, $crawler->filter('td:contains("http://goodurl.fr")')->count());
    }
}
