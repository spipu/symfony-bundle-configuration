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

abstract class AbstractField implements FieldInterface
{
    public function prepareValue(Definition $definition, mixed $value): mixed
    {
        if (!$definition->isRequired() && ($value === null || $value === '')) {
            return null;
        }

        return (string) $value;
    }

    protected function isRequired(Definition $definition, mixed $value): mixed
    {
        if ($value !== null) {
            $value = (string) $value;
        }
        if ($value === '') {
            $value = null;
        }

        if ($definition->isRequired() && $value === null) {
            throw new ConfigurationException(
                sprintf(
                    'Configuration "%s" is required',
                    $definition->getCode()
                )
            );
        }

        return $value;
    }

    protected function validateValueType(Definition $definition, mixed $value, ?int $validator): mixed
    {
        $value = $this->isRequired($definition, $value);

        if ($validator && $value !== null && !filter_var($value, $validator)) {
            throw new ConfigurationException(
                sprintf(
                    'Configuration "%s" must be a valid %s',
                    $definition->getCode(),
                    $definition->getType()
                )
            );
        }

        return $value;
    }

    protected function getFieldBuilderOptions(Definition $definition, string $scopeCode, string $scopeName): array
    {
        $options = [
            'label'    => $scopeName,
            'required' => $definition->isRequired(),
        ];

        $helps = [];
        if ($definition->getUnit()) {
            $helps[] = 'Unit: ' . $definition->getUnit();
        }

        if ($definition->getHelp()) {
            $helps[] = $definition->getHelp();
        }

        if (count($helps) > 0) {
            $options['help'] = implode(' | ', $helps);
        }

        if ($scopeCode === 'default') {
            $options['disabled'] = true;
        }

        return $options;
    }

    protected function buildFormFieldCode(string $scope): string
    {
        return 'value_' . $scope;
    }

    protected function buildFormFieldPosition(string $scope): int
    {
        if ($scope === 'default') {
            return 1;
        }

        if ($scope === 'global') {
            return 2;
        }

        $position = 0;
        $max = 4;
        $length = min(strlen($scope), $max);
        for ($pos = 0; $pos < $length; $pos++) {
            $position = $position * 255 + ord(substr($scope, $pos));
        }
        for ($pos = $length; $pos < $max; $pos++) {
            $position = $position * 255;
        }

        return $position * 10;
    }
}
