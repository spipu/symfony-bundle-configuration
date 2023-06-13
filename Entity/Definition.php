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

use Spipu\ConfigurationBundle\Exception\ConfigurationException;

class Definition
{
    /**
     * Category separator used in the code.
     */
    public const CATEGORY_SEPARATOR = '.';

    private string $code;
    private string $type;
    private bool $required;
    private bool $scoped;
    private mixed $default;
    private ?string $unit;
    private ?string $help;
    private ?string $options;
    private ?array $fileTypes;

    public function __construct(
        string $code,
        string $type,
        bool $required,
        bool $scoped,
        mixed $default,
        ?string $options,
        ?string $unit,
        ?string $help,
        ?array $fileTypes
    ) {
        $this->code = $code;
        $this->type = $type;
        $this->required = $required;
        $this->scoped = $scoped;
        $this->default = $default;
        $this->options = $options;
        $this->unit = $unit;
        $this->help = $help;
        $this->fileTypes = $fileTypes;

        $this->validateCode();
    }

    private function validateCode(): void
    {
        if (count($this->getCategories()) < 2) {
            throw new ConfigurationException(
                sprintf(
                    'The configuration code %s must have at least 2 parts',
                    $this->getCode()
                )
            );
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return explode(self::CATEGORY_SEPARATOR, $this->code);
    }

    public function getMainCategory(): string
    {
        return $this->getCategories()[0];
    }

    public function getSubCategories(): string
    {
        $categories = $this->getCategories();
        array_shift($categories);
        return implode(self::CATEGORY_SEPARATOR, $categories);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isScoped(): bool
    {
        return $this->scoped;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    /**
     * @return string[]|null
     */
    public function getFileTypes(): ?array
    {
        return $this->fileTypes;
    }
}
