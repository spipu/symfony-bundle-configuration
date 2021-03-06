<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Form\Options\OptionsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type;

class FieldSelect extends AbstractField implements FieldInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var OptionsInterface[]
     */
    private $options = [];

    /**
     * FieldSelect constructor.
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'select';
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function prepareValue(Definition $definition, $value)
    {
        $options = $this->getOptions($definition);
        if (!$options->hasKey($value)) {
            $value = null;
        }

        return parent::prepareValue($definition, $value);
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
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

    /**
     * @param Definition $definition
     * @return Field
     * @throws ConfigurationException
     */
    public function getFormField(Definition $definition): Field
    {
        $options = $this->getFieldBuilderOptions($definition);
        $options['choices'] = $this->getOptions($definition);

        $field = new Field(
            'value',
            Type\ChoiceType::class,
            10,
            $options
        );

        return $field;
    }

    /**
     * @param Definition $definition
     * @return OptionsInterface
     * @throws ConfigurationException
     */
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
