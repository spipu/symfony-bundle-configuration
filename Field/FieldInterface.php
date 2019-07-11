<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;

interface FieldInterface
{
    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     */
    public function prepareValue(Definition $definition, $value);

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value);

    /**
     * @param Definition $definition
     * @return Field
     */
    public function getFormField(Definition $definition): Field;
}
