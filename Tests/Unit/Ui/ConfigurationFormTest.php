<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Service\Manager;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\ConfigurationBundle\Ui\ConfigurationForm;
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

        $manager = SpipuConfigurationMock::getManager($this, [$code => $type]);

        if ($type === 'file') {
            $manager->expects($this->never())->method('set');
            $manager->expects($this->once())->method('setFile')->with($code);

        } else {
            $manager->expects($this->never())->method('setFile');
            $manager->expects($this->once())->method('set')->with($code, 'new value');
        }


        /** @var Manager $manager */
        $form = new ConfigurationForm($manager);
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

        $field = $fieldSet->getField('value');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame('value', $field->getCode());
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\TextType::class, $field->getType());
        $this->assertSame('mock.string', $field->getValue());

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm
            ->expects($this->once())
            ->method('offsetGet')
            ->with('value')
            ->willReturn($symfonyForm);

        $symfonyForm
            ->expects($this->once())
            ->method('getData')
            ->willReturn('new value');

        $form->setSpecificFields($symfonyForm, null);
    }

    public function testFormFile()
    {
        $form = $this->getForm('mock.file');

        $definition = $form->getDefinition();
        $fieldSet = $definition->getFieldSet('configuration');
        $field = $fieldSet->getField('value');
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\FileType::class, $field->getType());
        $this->assertSame(null, $field->getValue());

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm->expects($this->once())->method('offsetGet')->with('value')->willReturn($symfonyForm);
        $symfonyForm->expects($this->once())->method('getData')->willReturn($this->createMock(UploadedFile::class));

        $form->setSpecificFields($symfonyForm, null);
    }
}
