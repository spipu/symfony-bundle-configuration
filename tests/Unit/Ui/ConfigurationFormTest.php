<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Ui;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Service\Storage;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\ConfigurationBundle\Ui\ConfigurationForm;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ConfigurationForm::class)]
class ConfigurationFormTest extends TestCase
{
    protected function getForm(string $code): ConfigurationForm
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

    public function testFormClassic(): void
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
        $offsetMatcher = $this->exactly(2);
        $symfonyForm
            ->expects($offsetMatcher)
            ->method('offsetGet')
            ->willReturnCallback(function (mixed $key) use ($offsetMatcher, $symfonyForm): FormInterface {
                match ($offsetMatcher->numberOfInvocations()) {
                    1 => $this->assertSame('check_global', $key),
                    2 => $this->assertSame('value_global', $key),
                };
                return $symfonyForm;
            });

        $dataMatcher = $this->exactly(2);
        $symfonyForm
            ->expects($dataMatcher)
            ->method('getData')
            ->willReturnCallback(function () use ($dataMatcher): mixed {
                return match ($dataMatcher->numberOfInvocations()) {
                    1 => 0,
                    2 => 'new value',
                };
            });

        $form->setSpecificFields($symfonyForm, null);
    }

    public function testFormFile(): void
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

        $offsetMatcher = $this->exactly(2);
        $symfonyForm
            ->expects($offsetMatcher)
            ->method('offsetGet')
            ->willReturnCallback(function (mixed $key) use ($offsetMatcher, $symfonyForm): FormInterface {
                match ($offsetMatcher->numberOfInvocations()) {
                    1 => $this->assertSame('check_global', $key),
                    2 => $this->assertSame('value_global', $key),
                };
                return $symfonyForm;
            });

        $uploadedFile = $this->createMock(UploadedFile::class);
        $dataMatcher = $this->exactly(2);
        $symfonyForm
            ->expects($dataMatcher)
            ->method('getData')
            ->willReturnCallback(function () use ($dataMatcher, $uploadedFile): mixed {
                return match ($dataMatcher->numberOfInvocations()) {
                    1 => 0,
                    2 => $uploadedFile,
                };
            });

        $form->setSpecificFields($symfonyForm, null);
    }
}
