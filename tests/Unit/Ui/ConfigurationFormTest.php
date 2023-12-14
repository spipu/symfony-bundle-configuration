<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Service\Storage;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\ConfigurationBundle\Ui\ConfigurationForm;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ConfigurationFormTest extends TestCase
{
    /**
     * @param string $code
     * @return ConfigurationForm
     */
    protected function getForm(string $code)
    {
        $type = explode('.', $code)[1];
        $definition = [$code => $type];

        $manager = SpipuConfigurationMock::getManager($this, $definition);

        if ($type === 'file') {
            $manager->expects($this->never())->method('set');
            $manager->expects($this->once())->method('setFile')->with($code);

        } else {
            $manager->expects($this->never())->method('setFile');
            $manager->expects($this->once())->method('set')->with($code, 'new value');
        }

        $scopeService = SpipuConfigurationMock::getScopeServiceMock();

        $storage = $this->createMock(Storage::class);
        $translator = SymfonyMock::getTranslator($this);

        $form = new ConfigurationForm($manager, $scopeService, $translator, $storage);
        $form->setConfigurationCode($code);

        return $form;
    }

    public function testFormClassic()
    {
        $form = $this->getForm('mock.string');

        $definition = $form->getDefinition();

        $this->assertInstanceOf(Form\Form::class, $definition);

        $this->assertSame('configuration', $definition->getCode());

        $fieldSet = $definition->getFieldSet('configuration');
        $this->assertInstanceOf(Form\FieldSet::class, $fieldSet);
        $this->assertSame('mock.string', $fieldSet->getName());

        $field = $fieldSet->getField('value_global');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame('value_global', $field->getCode());
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\TextType::class, $field->getType());
        $this->assertSame(null, $field->getValue());

        $field = $fieldSet->getField('check_global');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame('check_global', $field->getCode());
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, $field->getType());
        $this->assertSame(false, $field->getValue());

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm
            ->expects($this->exactly(2))
            ->method('offsetGet')
            ->withConsecutive(['check_global'], ['value_global'])
            ->willReturn($symfonyForm);

        $symfonyForm
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(0, 'new value');

        $form->setSpecificFields($symfonyForm, null);
    }

    public function testFormFile()
    {
        $form = $this->getForm('mock.file');

        $definition = $form->getDefinition();
        $fieldSet = $definition->getFieldSet('configuration');
        $field = $fieldSet->getField('value_global');
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\FileType::class, $field->getType());
        $this->assertSame(null, $field->getValue());

        $field = $fieldSet->getField('check_global');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame('check_global', $field->getCode());
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, $field->getType());
        $this->assertSame(false, $field->getValue());

        $symfonyForm = $this->createMock(FormInterface::class);

        $symfonyForm
            ->expects($this->exactly(2))
            ->method('offsetGet')
            ->withConsecutive(['check_global'], ['value_global'])
            ->willReturn($symfonyForm);

        $symfonyForm
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(0, $this->createMock(UploadedFile::class));

        $form->setSpecificFields($symfonyForm, null);
    }
}
