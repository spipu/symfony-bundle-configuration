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

use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\ConfigurationManager;
use Spipu\CoreBundle\Tests\WebTestCase;

class ManagerTest extends WebTestCase
{
    public function testText()
    {
        $manager = $this->getManager();
        $this->assertSame('My text', $manager->get('test.type.text'));
        $manager->set('test.type.text', 'My global');
        $this->assertSame('My global', $manager->get('test.type.text'));
        $manager->delete('test.type.text');
        $this->assertSame('My text', $manager->get('test.type.text'));
    }

    public function testSetKoNotScoped()
    {
        $manager = $this->getManager();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('This configuration key is not scoped');

        $manager->set('app.website.name', 'My app', 'fr');
    }

    public function testGetKoNotScoped()
    {
        $manager = $this->getManager();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('This configuration key is not scoped');

        $manager->get('app.website.name', 'fr');
    }

    public function testEncrypted()
    {
        $manager = $this->getManager();
        $this->assertSame('', $manager->getEncrypted('test.type.encrypted'));
        $manager->setEncrypted('test.type.encrypted', 'Foo Bar');
        $this->assertSame('Foo Bar', $manager->getEncrypted('test.type.encrypted'));
        $this->assertNotSame('Foo Bar', $manager->get('test.type.encrypted'));
        $manager->delete('test.type.encrypted');
        $this->assertSame('', $manager->getEncrypted('test.type.encrypted'));
    }

    public function testPassword()
    {
        $manager = $this->getManager();
        $this->assertSame('', $manager->get('test.type.password'));
        $manager->setPassword('test.type.password', 'Foo Bar');
        $this->assertNotSame('Foo Bar', $manager->get('test.type.password'));
        $this->assertTrue($manager->isPasswordValid('test.type.password', 'Foo Bar'));
        $this->assertFalse($manager->isPasswordValid('test.type.password', 'Bar Foo'));
        $manager->delete('test.type.password');
        $this->assertSame('', $manager->get('test.type.password'));
    }

    public function testDeleteKo()
    {
        $manager = $this->getManager();
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('This configuration key is not scoped');
        $manager->delete('app.website.name', 'fr');
    }

    private function getManager(): ConfigurationManager
    {
        static::bootKernel();

        /** @var ConfigurationManager $manager */
        $manager = static::getContainer()->get(ConfigurationManager::class);
        return $manager;
    }
}
