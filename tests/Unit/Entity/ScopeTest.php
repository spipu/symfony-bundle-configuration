<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Scope;
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;

class ScopeTest extends TestCase
{
    public function testOk(): void
    {
        $entity = new Scope('code', 'Name');

        $this->assertSame('code', $entity->getCode());
        $this->assertSame('Name', $entity->getName());
    }

    public function testKoCodeEmpty(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - empty');
        new Scope('', 'Name');
    }

    public function testKoNameEmpty(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope name - empty');
        new Scope('code', '');
    }

    public function testKoCodeHtml(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('code<b>test</b>', 'Name');
    }

    public function testKoNameHtml(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope name - char not allowed');
        new Scope('code', 'Name<b>test</b>');
    }

    public function testKoCodeSpace(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('code ', 'Name');
    }

    public function testKoNameSpace(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope name - char not allowed');
        new Scope('code', 'Name ');
    }

    public function testKoCodeUpper(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('Code ', 'Name');
    }

    public function testKoCodeBadChar1(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to/ta', 'Name');
    }

    public function testKoCodeBadChar2(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to\\ta', 'Name');
    }

    public function testKoCodeBadChar3(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to*ta', 'Name');
    }

    public function testKoCodeBadChar4(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to.ta', 'Name');
    }

    public function testKoCodeBadChar5(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to[ta', 'Name');
    }

    public function testKoCodeBadChar6(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to]ta', 'Name');
    }

    public function testKoCodeBadChar7(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to(ta', 'Name');
    }

    public function testKoCodeBadChar8(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to)ta', 'Name');
    }

    public function testKoCodeBadChar9(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to{ta', 'Name');
    }

    public function testKoCodeBadChar10(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - char not allowed');
        new Scope('to}ta', 'Name');
    }

    public function testKoCodeTooLong(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - too long');

        $code = '0123456789abcdef';
        $code .= $code;
        $code .= $code;
        $code .= $code;
        $code .= '0';

        new Scope($code, 'Name');
    }

    public function testKoCodeGlobalNotAllowed(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - value not allowed');
        new Scope('global', 'Name');
    }

    public function testKoCodeDefaultNotAllowed(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - value not allowed');
        new Scope('default', 'Name');
    }

    public function testKoCodeScopedNotAllowed(): void
    {
        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('Invalid scope code - value not allowed');
        new Scope('scoped', 'Name');
    }

    public function testOkChar1(): void
    {
        $entity = new Scope('to-ta', 'Name');
        $this->assertSame('to-ta', $entity->getCode());
        $this->assertSame('Name', $entity->getName());
    }

    public function testOkChar2(): void
    {
        $entity = new Scope('to_ta', 'Name');
        $this->assertSame('to_ta', $entity->getCode());
        $this->assertSame('Name', $entity->getName());
    }
}
