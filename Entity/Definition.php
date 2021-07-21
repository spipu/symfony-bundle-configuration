<?php
declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Entity;

use Spipu\ConfigurationBundle\Exception\ConfigurationException;

class Definition
{
    /**
     * Category separator user in the code.
     */
    public const CATEGORY_SEPARATOR = '.';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $type;

    /**
     * @var boolean
     */
    private $required;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var string|null
     */
    private $unit;

    /**
     * @var string|null
     */
    private $help;

    /**
     * @var string|null
     */
    private $options;

    /**
     * @var array|null
     */
    private $fileTypes;

    /**
     * Definition constructor.
     * @param string $code
     * @param string $type
     * @param bool $required
     * @param mixed $default
     * @param null|string $options
     * @param null|string $unit
     * @param string|null $help
     * @param array|null $fileTypes
     * @throws ConfigurationException
     */
    public function __construct(
        string $code,
        string $type,
        bool $required,
        $default,
        ?string $options,
        ?string $unit,
        ?string $help,
        ?array $fileTypes
    ) {
        $this->code = $code;
        $this->type = $type;
        $this->required = $required;
        $this->default = $default;
        $this->options = $options;
        $this->unit = $unit;
        $this->help = $help;
        $this->fileTypes = $fileTypes;

        $this->validateCode();
    }

    /**
     * The code must have at least 2 parts
     * @return void
     * @throws ConfigurationException
     */
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

    /**
     * @return string
     */
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

    /**
     * Get the main category of the code
     * @return string
     */
    public function getMainCategory(): string
    {
        return $this->getCategories()[0];
    }

    /**
     * Get the sub categories of the code
     * @return string
     */
    public function getSubCategories(): string
    {
        $categories = $this->getCategories();
        array_shift($categories);
        return implode(self::CATEGORY_SEPARATOR, $categories);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return null|string
     */
    public function getOptions(): ?string
    {
        return $this->options;
    }

    /**
     * @return null|string
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @return null|string
     */
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
