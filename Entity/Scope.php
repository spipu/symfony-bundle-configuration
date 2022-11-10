<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Entity;

use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;

class Scope
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $code
     * @param string $name
     * @throws ConfigurationScopeException
     */
    public function __construct(string $code, string $name)
    {
        $this->validateCode($code);
        $this->validateName($name);

        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $code
     * @return void
     * @throws ConfigurationScopeException
     */
    private function validateCode(string $code): void
    {
        if ($code !== mb_strtolower(trim(strip_tags($code)))) {
            throw new ConfigurationScopeException('Invalid scope code - char not allowed');
        }

        if (preg_match('/[.*\/\\\\\[\]\(\)\{\}]/', $code)) {
            throw new ConfigurationScopeException('Invalid scope code - char not allowed');
        }

        if ($code === '') {
            throw new ConfigurationScopeException('Invalid scope code - empty');
        }

        if (in_array($code, ['global', 'default', 'scoped'])) {
            throw new ConfigurationScopeException('Invalid scope code - value not allowed');
        }

        if (mb_strlen($code) > 128) {
            throw new ConfigurationScopeException('Invalid scope code - too long');
        }
    }

    /**
     * @param string $name
     * @return void
     * @throws ConfigurationScopeException
     */
    private function validateName(string $name)
    {
        if ($name !== trim(strip_tags($name))) {
            throw new ConfigurationScopeException('Invalid scope name - char not allowed');
        }

        if ($name === '') {
            throw new ConfigurationScopeException('Invalid scope name - empty');
        }
    }
}
