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

namespace Spipu\ConfigurationBundle\Ui;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\ConfigurationManager;
use Spipu\ConfigurationBundle\Service\ScopeService;
use Spipu\ConfigurationBundle\Service\Storage;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldConstraint;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class ConfigurationForm implements EntityDefinitionInterface
{
    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var Form
     */
    private $definition;

    /**
     * @var string
     */
    private $configurationCode;

    /**
     * @var ScopeService
     */
    private $scopeService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * ConfigurationForm constructor.
     * @param ConfigurationManager $configurationManager
     * @param ScopeService $scopeService
     * @param TranslatorInterface $translator
     * @param Storage $storage
     */
    public function __construct(
        ConfigurationManager $configurationManager,
        ScopeService $scopeService,
        TranslatorInterface $translator,
        Storage $storage
    ) {
        $this->configurationManager = $configurationManager;
        $this->scopeService = $scopeService;
        $this->translator = $translator;
        $this->storage = $storage;
    }

    /**
     * @param string $configurationCode
     * @return self
     */
    public function setConfigurationCode(string $configurationCode): self
    {
        $this->configurationCode = $configurationCode;

        return $this;
    }

    /**
     * @return Form
     * @throws ConfigurationException
     * @throws FormException
     */
    public function getDefinition(): Form
    {
        if (!$this->definition) {
            $this->prepareForm();
        }

        return $this->definition;
    }

    /**
     * @return void
     * @throws ConfigurationException
     * @throws FormException
     */
    private function prepareForm(): void
    {
        $definition = $this->getFieldDefinition();

        $fieldSet = new FieldSet('configuration', $definition->getCode(), 10);
        $fieldSet->setCssClass('col-xs-12 col-md-8 m-auto');

        $this->prepareScopeField($fieldSet, $definition, 'default');
        $this->prepareScopeField($fieldSet, $definition, 'global');

        if ($definition->isScoped() && $this->scopeService->hasScopes()) {
            foreach ($this->scopeService->getScopes() as $scope) {
                $this->prepareScopeField($fieldSet, $definition, $scope->getCode());
            }
        }

        $this->definition = new Form('configuration');
        $this->definition->addFieldSet($fieldSet);
    }

    /**
     * @param FieldSet $fieldSet
     * @param Definition $definition
     * @param string $scopeCode
     * @return void
     * @throws ConfigurationException
     * @throws FormException
     */
    private function prepareScopeField(FieldSet $fieldSet, Definition $definition, string $scopeCode): void
    {
        try {
            $currentValue = $this->storage->getScopeValue($this->configurationCode, $scopeCode);
            $hasValue = true;
        } catch (ConfigurationException $e) {
            $currentValue = null;
            $hasValue = false;
        }

        $valueField = $this->prepareScopeFieldValue($definition, $scopeCode);
        if (!in_array($definition->getType(), ['file', 'encrypted', 'password']) && $hasValue) {
            $valueField->setValue($currentValue);
        }

        $fieldSet->addField($valueField);

        if ($scopeCode === 'default') {
            return;
        }

        $checkField = $this->prepareScopeFieldCheck($valueField, $scopeCode);
        $checkField->setValue(!$hasValue);
        $fieldSet->addField($checkField);

        $valueField->addConstraint(new FieldConstraint('use', $checkField->getCode(), ''));
    }

    /**
     * @param Definition $definition
     * @param string $scopeCode
     * @return Field
     * @throws ConfigurationException
     */
    private function prepareScopeFieldValue(Definition $definition, string $scopeCode): Field
    {
        switch ($scopeCode) {
            case 'default':
                $scopeLabel = $this->translator->trans('spipu.configuration.scope.default');
                break;

            case 'global':
                $scopeLabel = $this->translator->trans('spipu.configuration.scope.global');
                break;

            default:
                $scopeLabel = $this->scopeService->getScope($scopeCode)->getName();
                break;
        }

        return $this->configurationManager->getField($this->configurationCode)->getFormField(
            $definition,
            $scopeCode,
            $scopeLabel
        );
    }

    /**
     * @param Field $valueField
     * @param string $scopeCode
     * @return Field
     * @throws FormException
     */
    private function prepareScopeFieldCheck(Field $valueField, string $scopeCode): Field
    {
        $checkLabel = 'spipu.configuration.scope.' . (($scopeCode === 'global') ? 'use_default' : 'use_global');

        return new Field(
            'check_' . $scopeCode,
            Type\CheckboxType::class,
            $valueField->getPosition() + 1,
            [
                'label'     => $this->translator->trans($checkLabel),
                'required'  => false,
            ]
        );
    }

    /**
     * @return Definition
     * @throws ConfigurationException
     */
    private function getFieldDefinition(): Definition
    {
        return $this->configurationManager->getDefinition($this->configurationCode);
    }

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     * @throws ConfigurationException
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void
    {
        $this->saveConfigurationValue($form, 'global');

        $definition = $this->getFieldDefinition();
        if ($definition->isScoped() && $this->scopeService->hasScopes()) {
            foreach ($this->scopeService->getScopes() as $scope) {
                $this->saveConfigurationValue($form, $scope->getCode());
            }
        }
    }

    /**
     * @param FormInterface $form
     * @param string $scopeCode
     * @return void
     * @throws ConfigurationException
     */
    private function saveConfigurationValue(FormInterface $form, string $scopeCode): void
    {
        $check = (int) $form['check_' . $scopeCode]->getData();
        $value = $form['value_' . $scopeCode]->getData();

        if ($scopeCode === 'global') {
            $scopeCode = null;
        }

        if ($check > 0) {
            $this->configurationManager->delete($this->configurationCode, $scopeCode);
            return;
        }

        switch ($this->getFieldDefinition()->getType()) {
            case 'file':
                $this->configurationManager->setFile($this->configurationCode, $value, $scopeCode);
                break;

            case 'password':
                $this->configurationManager->setPassword($this->configurationCode, $value, $scopeCode);
                break;

            case 'encrypted':
                $this->configurationManager->setEncrypted($this->configurationCode, $value, $scopeCode);
                break;

            default:
                $this->configurationManager->set($this->configurationCode, $value, $scopeCode);
                break;
        }
    }
}
