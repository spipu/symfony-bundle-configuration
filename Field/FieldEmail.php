<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;
use Symfony\Component\Form\Extension\Core\Type;

class FieldEmail extends AbstractField implements FieldInterface
{
    const MAIL_SEPARATOR = ',';

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'email';
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
    {
        $value = $this->isRequired($definition, $value);

        if ($value !== null) {
            $emails = explode(self::MAIL_SEPARATOR, $value);

            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new ConfigurationException(
                        sprintf(
                            'Configuration "%s" must be a valid %s',
                            $definition->getCode(),
                            $definition->getType()
                        )
                    );
                }
            }
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @return Field
     */
    public function getFormField(Definition $definition): Field
    {
        $field = new Field(
            'value',
            Type\EmailType::class,
            10,
            $this->getFieldBuilderOptions($definition)
        );

        return $field;
    }
}
