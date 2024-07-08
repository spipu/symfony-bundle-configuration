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
use Spipu\UiBundle\Tests\UiWebTestCaseTrait;

class ConfigurationTest extends WebTestCase
{
    use UiWebTestCaseTrait;

    public function testAdmin()
    {
        $client = static::createClient();

        $this->adminLogin($client, 'Configurations');

        // Conf List
        $crawler = $client->clickLink('Configurations');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Advanced Search")')->count());

        // Conf List with filter
        $crawler = $this->submitGridFilter($client, $crawler, ['fl[code]' => 'app.website.url']);
        $gridProperties = $this->getGridProperties($crawler, 'configuration');
        $this->assertSame(1, $gridProperties['count']['nb']);

        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
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
        $gridProperties = $this->getGridProperties($crawler, 'configuration');
        $this->assertSame(1, $gridProperties['count']['nb']);
        $this->assertEquals(1, $crawler->filter('td:contains("http://goodurl.fr")')->count());

        // Conf List - quick search
        $crawler = $this->submitGridQuickSearch($client, $crawler, 'code', 'test.type.');
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $gridProperties = $this->getGridProperties($crawler, 'configuration');
        $this->assertSame(5, $gridProperties['count']['nb']);

        // List - reset filter
        $this->submitGridFilter($client, $crawler, ['fl[code]' => null]);

        // List - Global scope
        $crawler = $client->request('GET', '/configuration/list');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $gridProperties = $this->getGridProperties($crawler, 'configuration');
        $this->assertSame(30, $gridProperties['count']['nb']);

        // List - Fr scope
        $crawler = $client->request('GET', '/configuration/list/fr');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Configurations")')->count());
        $gridProperties = $this->getGridProperties($crawler, 'configuration');
        $this->assertSame(1, $gridProperties['count']['nb']);

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

        // Show - Text scoped value
        $crawler = $client->request('GET', '/configuration/show/test.type.text');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[check_global]']->untick();
        $form['generic[value_global]']->setValue('My global');
        $form['generic[check_fr]']->untick();
        $form['generic[value_fr]']->setValue('My french');
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->request('GET', '/configuration/show/test.type.text');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('h1:contains("Edit Configuration")')->count());

        $this->assertCrawlerHasInputValue($crawler, 'generic_value_global', 'My global');
        $this->assertCrawlerHasInputValue($crawler, 'generic_value_fr', 'My french');

        $form = $crawler->filter('form#form_configuration')->form();
        $form['generic[value_global]']->setValue('');
        $form['generic[check_global]']->tick();
        $form['generic[value_fr]']->setValue('');
        $form['generic[check_fr]']->tick();
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
    }
}
