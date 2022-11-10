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
use Symfony\Component\Form\Extension\Core\Type;

class FieldEmail extends AbstractField implements FieldInterface
{
    public const MAIL_SEPARATOR = ',';

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
     * @param string $scopeCode
     * @param string $scopeName
     * @return Field
     * @throws FormException
     */
    public function getFormField(Definition $definition, string $scopeCode, string $scopeName): Field
    {
        return new Field(
            $this->buildFormFieldCode($scopeCode),
            Type\EmailType::class,
            $this->buildFormFieldPosition($scopeCode),
            $this->getFieldBuilderOptions($definition, $scopeCode, $scopeName)
        );
    }
}
