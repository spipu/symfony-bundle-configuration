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
        $this->assertCrawlerHasInputValue($crawler, 'generic_value_default', 'https://my-website.fr');
        $this->assertCrawlerHasInputValue($crawler, 'generic_value_global', null);
        $this->assertCrawlerHasInputValue($crawler, 'generic_check_global', '1');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Form Submit - missing value
        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[check_global]']->untick();
        $form['generic[value_global]']->setValue('');
        $crawler = $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());
        $this->assertCrawlerHasAlert($crawler, 'is required');

        // Form Submit - bad value
        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[check_global]']->untick();
        $form['generic[value_global]']->setValue('bad;');
        $crawler = $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());
        $this->assertCrawlerHasAlert($crawler, 'must be a valid url');

        // Form Submit - good value
        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[check_global]']->untick();
        $form['generic[value_global]']->setValue('http://goodurl.fr');
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());

        // List - check new value
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("1 item found")')->count());
        $this->assertEquals(1, $crawler->filter('td:contains("http://goodurl.fr")')->count());

        // List - reset filter
        $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[code]' => null]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // List - Global scope
        $crawler = $client->request('GET', '/configuration/list');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("21 items found")')->count());

        // List - Fr scope
        $crawler = $client->request('GET', '/configuration/list/fr');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("1 item found")')->count());

        // List - Bad scope
        $client->request('GET', '/configuration/list/foo');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        // Show - Global scope
        $crawler = $client->request('GET', '/configuration/show/test.type.text');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        // Show - Fr scope
        $crawler = $client->request('GET', '/configuration/show/test.type.text/fr');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        // Show - Bad scope
        $client->request('GET', '/configuration/show/test.type.text/foo');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        // Show - Encrypted value
        $crawler = $client->request('GET', '/configuration/show/test.type.encrypted');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[check_global]']->untick();
        $form['generic[value_global]']->setValue('Foo Bar');
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->request('GET', '/configuration/show/test.type.encrypted');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        $this->assertCrawlerHasInputValue($crawler, 'generic_value_global', '**********');

        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[value_global]']->setValue('');
        $form['generic[check_global]']->tick();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show - Password value
        $crawler = $client->request('GET', '/configuration/show/test.type.password');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[check_global]']->untick();
        $form['generic[value_global]']->setValue('Foo Bar');
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->request('GET', '/configuration/show/test.type.password');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        $this->assertCrawlerHasInputValue($crawler, 'generic_value_global', '**********');

        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[value_global]']->setValue('');
        $form['generic[check_global]']->tick();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
    }
}
