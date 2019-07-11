<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Ui;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\Manager as ConfigurationManager;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Users Creation
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
     * ConfigurationForm constructor.
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
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
     */
    private function prepareForm(): void
    {
        $definition = $this->getFieldDefinition();

        $field = $this->configurationManager->getField($this->configurationCode)->getFormField($definition);
        if ($definition->getType() !== 'file') {
            $field->setValue($this->configurationManager->get($this->configurationCode));
        }

        $this->definition = new Form('configuration');
        $this->definition
            ->addFieldSet(
                (new FieldSet('configuration', $definition->getCode(), 10))
                    ->setCssClass('col-xs-12 col-md-8 m-auto')
                    ->addField($field)
            );
        ;
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
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     * @throws ConfigurationException
     */
    public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void
    {
        $value = $form['value']->getData();
        if ($this->getFieldDefinition()->getType() === 'file') {
            $this->configurationManager->setFile($this->configurationCode, $value);
            return;
        }

        $this->configurationManager->set($this->configurationCode, $value);
    }
}
