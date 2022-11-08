<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Scope;
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;

class ScopeTest extends TestCase
{
    public function testOk()
    {
        $entity = new Scope('code', 'Name');

        $this->assertSame('code', $entity->getCode());
        $this->assertSame('Name', $entity->getName());
    }

    public function testKoCodeEmpty()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - empty');
        new Scope('', 'Name');
    }

    public function testKoNameEmpty()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope name - empty');
        new Scope('code', '');
    }

    public function testKoCodeHtml()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('code<b>test</b>', 'Name');
    }

    public function testKoNameHtml()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope name - char not allowed');
        new Scope('code', 'Name<b>test</b>');
    }

    public function testKoCodeSpace()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('code ', 'Name');
    }

    public function testKoNameSpace()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope name - char not allowed');
        new Scope('code', 'Name ');
    }

    public function testKoCodeUpper()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('Code ', 'Name');
    }

    public function testKoCodeBadChar1()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to/ta', 'Name');
    }

    public function testKoCodeBadChar2()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to\\ta', 'Name');
    }

    public function testKoCodeBadChar3()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to*ta', 'Name');
    }

    public function testKoCodeBadChar4()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to.ta', 'Name');
    }

    public function testKoCodeGlobalNotAllowed()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - value not allowed');
        new Scope('global', 'Name');
    }

    public function testKoCodeDefaultNotAllowed()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - value not allowed');
        new Scope('default', 'Name');
    }

    public function testKoCodeScopedNotAllowed()
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - value not allowed');
        new Scope('scoped', 'Name');
    }

    public function testOkChar1()
    {
        $entity = new Scope('to-ta', 'Name');
        $this->assertSame('to-ta', $entity->getCode());
        $this->assertSame('Name', $entity->getName());
    }

    public function testOkChar2()
    {
        $entity = new Scope('to_ta', 'Name');
        $this->assertSame('to_ta', $entity->getCode());
        $this->assertSame('Name', $entity->getName());
    }
}
