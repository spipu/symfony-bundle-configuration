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
use Spipu\ConfigurationBundle\Service\Storage;
use Spipu\CoreBundle\Tests\WebTestCase;

class StorageTest extends WebTestCase
{
    public function testGetKoBadScope()
    {
        $storage = $this->getStorage();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Unknown configuration scope [foo]');

        $storage->getScopeValue('app.website.name', 'foo');
    }

    public function testGetKoBadKey()
    {
        $storage = $this->getStorage();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Unknown configuration key [app.foo.bar]');

        $storage->getScopeValue('app.foo.bar', 'fr');
    }

    public function testGetKoNoValue()
    {
        $storage = $this->getStorage();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('no value for key [app.website.name] on scope [fr]');

        $storage->getScopeValue('app.website.name', 'fr');
    }

    public function testGetOk()
    {
        $storage = $this->getStorage();
        $value = $storage->getScopeValue('app.website.name', 'default');
        $this->assertSame('Symfony Dev', $value);
    }

    private function getStorage(): Storage
    {
        static::bootKernel();

        /** @var Storage $storage */
        $storage = static::getContainer()->get(Storage::class);
        return $storage;
    }
}
