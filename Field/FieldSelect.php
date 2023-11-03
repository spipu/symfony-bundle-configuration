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

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\Options\OptionsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type;

class FieldSelect extends AbstractField implements FieldInterface
{
    private ContainerInterface $container;
    private array $options = [];

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    public function getCode(): string
    {
        return 'select';
    }

    public function prepareValue(Definition $definition, mixed $value): mixed
    {
        $options = $this->getOptions($definition);
        if (!$options->hasKey($value)) {
            $value = null;
        }

        return parent::prepareValue($definition, $value);
    }

    public function validateValue(Definition $definition, mixed $value): mixed
    {
        $value = parent::validateValueType($definition, $value, null);

        $options = $this->getOptions($definition);
        if ($value !== null && !$options->hasKey($value)) {
            throw new ConfigurationException(
                sprintf(
                    'Configuration "%s" has an unauthorized value for "%s"',
                    $definition->getCode(),
                    $definition->getOptions()
                )
            );
        }

        return $value;
    }

    public function getFormField(Definition $definition, string $scopeCode, string $scopeName): Field
    {
        $options = $this->getFieldBuilderOptions($definition, $scopeCode, $scopeName);
        $options['choices'] = $this->getOptions($definition);

        return new Field(
            $this->buildFormFieldCode($scopeCode),
            Type\ChoiceType::class,
            10,
            $options
        );
    }

    private function getOptions(Definition $definition): OptionsInterface
    {
        if (array_key_exists($definition->getCode(), $this->options)) {
            return $this->options[$definition->getCode()];
        }

        $class = $this->container->get($definition->getOptions());
        if (!($class instanceof OptionsInterface)) {
            throw new ConfigurationException(
                sprintf(
                    '%s of configuration "%s" must implements OptionsInterface',
                    $definition->getOptions(),
                    $definition->getCode()
                )
            );
        }

        $this->options[$definition->getCode()] = $class;

        return $this->options[$definition->getCode()];
    }
}
