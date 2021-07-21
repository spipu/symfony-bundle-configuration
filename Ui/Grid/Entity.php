<?php
declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Ui\Grid;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\UiBundle\Entity\EntityInterface;

class Entity extends Definition implements EntityInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return null;
    }

    /**
     * @param mixed $value
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
