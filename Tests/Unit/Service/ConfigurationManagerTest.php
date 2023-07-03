<?php

namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use Spipu\ConfigurationBundle\Entity\Configuration;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;
use Spipu\ConfigurationBundle\Service\ConfigurationManager;
use Spipu\ConfigurationBundle\Service\Definitions;
use Spipu\ConfigurationBundle\Service\FieldList;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Service\Storage;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\CoreBundle\Service\HasherFactory;
use Spipu\CoreBundle\Service\Encryptor;
use Spipu\CoreBundle\Tests\SymfonyMock;

class ConfigurationManagerTest extends TestCase
{
    /**
     * @return array
     */
    public function getConfigurations(): array
    {
        return [
            'mock.test.string' => [
                'code' => 'mock.test.string',
                'type' => 'string',
                'required' => true,
                'scoped'   => false,
                'default'  => 'default value',
                'options'  => null,
                'unit' => null,
                'help' => null,
                'file_type' => [],
            ],
            'mock.test.integer' => [
                'code' => 'mock.test.integer',
                'type' => 'integer',
                'required' => false,
                'scoped'   => false,
                'default'  => '',
                'options'  => null,
                'unit' => null,
                'help' => null,
                'file_type' => [],
            ],
            'mock.test.file' => [
                'code' => 'mock.test.file',
                'type' => 'file',
                'required' => true,
                'scoped'   => false,
                'default'  => '',
                'options'  => null,
                'unit' => null,
                'help' => null,
                'file_type' => ['jpeg'],
            ]
        ];
    }

    public function testDefinitions()
    {
        $configurations = $this->getConfigurations();

        $container = SymfonyMock::getContainer($this, [], ['spipu_configuration' => $configurations]);

        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);
        $definitions = new Definitions($container);

        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList);

        $fileManager = FileManagerTest::getService();

        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $definitions = $manager->getDefinitions();
        self::assertSame(array_keys($configurations), array_keys($definitions));
        foreach ($definitions as $definition) {
            $this->assertInstanceOf(Definition::class, $definition);
        }

        foreach ($configurations as $configuration) {
            $definition = $manager->getDefinition($configuration['code']);
            $this->assertInstanceOf(Definition::class, $definition);
            $this->assertSame($configuration['code'], $definition->getCode());
            $this->assertSame($configuration['type'], $definition->getType());
            $this->assertSame($configuration['required'], $definition->isRequired());
            $this->assertSame($configuration['default'], $definition->getDefault());
            $this->assertSame($configuration['options'], $definition->getOptions());
            $this->assertSame($configuration['unit'], $definition->getUnit());
            $this->assertSame($configuration['help'], $definition->getHelp());
            $this->assertSame($configuration['file_type'], $definition->getFileTypes());

            $field = $manager->getField($configuration['code']);
            $this->assertInstanceOf(Field\FieldInterface::class, $field);
            $this->assertSame($configuration['type'], $field->getCode());
        }

        $this->expectException(ConfigurationException::class);
        $manager->getDefinition('wrong_code');
    }

    public function testGetValues()
    {
        $configurations = $this->getConfigurations();

        $container = SymfonyMock::getContainer($this, [], ['spipu_configuration' => $configurations]);
        $cachePool = SymfonyMock::getCachePool($this);
        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $repository = $this->createMock(ConfigurationRepository::class);
        $repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(
                [
                    (new Configuration())->setCode('mock.test.integer')->setValue('42'),
                    (new Configuration())->setCode('mock.test.wrong')->setValue('wrong'),
                ]
            );

        $definitions = new Definitions($container);

        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList, $repository, null, $cachePool);

        $fileManager = FileManagerTest::getService();

        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $expected = [
            'default' => [
                'mock.test.string'  => 'default value',
                'mock.test.integer' => null,
                'mock.test.file'    => '',
            ],
            'global' => [
                'mock.test.integer' => 42,
            ],
            'test' => [
            ],
        ];

        $expectedValues = [];
        foreach ($expected as $values) {
            foreach ($values as $key => $value) {
                $expectedValues[$key] = $value;
            }
        }

        // Fist Time : build
        $this->assertSame($expected, $manager->getAll());

        // Second Time : local cache
        $this->assertSame($expected, $manager->getAll());

        // Saved in cache pool
        $cacheItem = $cachePool->getItem(Storage::CACHE_KEY);
        $this->assertSame(true, $cacheItem->isHit());
        $this->assertSame($expected, unserialize($cacheItem->get())['values']);
        
        // Third Time : Cache Pool
        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);
        $this->assertSame($expected, $manager->getAll());

        foreach ($expectedValues as $key => $value) {
            $this->assertSame($value, $manager->get($key));
        }

        $this->expectException(ConfigurationException::class);
        $manager->get('wrong_key');
    }

    public function testSetValues()
    {
        $configurations = $this->getConfigurations();

        $container = SymfonyMock::getContainer($this, [], ['spipu_configuration' => $configurations]);
        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $entity = new Configuration();
        $entity
            ->setCode('mock.test.integer')
            ->setScope(null)
            ->setValue('42');

        $list = [$entity->getCode() => $entity];

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager
            ->expects($this->exactly(1))
            ->method('persist')
            ->willReturnCallback(
                function ($object) use (&$list) {
                    /** @var Configuration $object */
                    $this->assertInstanceOf(Configuration::class, $object);
                    $list[$object->getCode()] = $object;
                }
            );
        $entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        $repository = $this->createMock(ConfigurationRepository::class);
        $repository
            ->expects($this->exactly(2))
            ->method('findAll')
            ->willReturnCallback(
                function () use (&$list) {
                    return $list;
                }
            );

        $repository
            ->expects($this->exactly(2))
            ->method('loadConfig')
            ->willReturnMap(
                [
                    ['mock.test.integer', null, $entity],
                    ['mock.test.text',    null, null],
                ]
            );

        $definitions = new Definitions($container);
        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList, $repository, $entityManager);
        $fileManager = FileManagerTest::getService();
        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $this->assertSame('default value', $manager->get('mock.test.string'));
        $this->assertSame(42, $manager->get('mock.test.integer'));

        $manager->set('mock.test.string', 'new value');
        $manager->set('mock.test.integer', 43);

        $this->assertSame('new value', $manager->get('mock.test.string'));
        $this->assertSame(43, $manager->get('mock.test.integer'));

        $this->assertSame('43', $entity->getValue());
    }

    public function testSetFileNotAllowed()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'spipu_configuration' => $configurations,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $definitions = new Definitions($container);
        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList);
        $fileManager = FileManagerTest::getService(false);
        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $file = SymfonyMock::getUploadedFile($this);

        $this->expectException(ConfigurationException::class);
        $manager->setFile('mock.test.file', $file);
    }

    public function testSetFileBadFieldType()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'spipu_configuration' => $configurations,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $definitions = new Definitions($container);
        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList);
        $fileManager = FileManagerTest::getService();
        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $file = SymfonyMock::getUploadedFile($this);

        $this->expectException(ConfigurationException::class);
        $manager->setFile('mock.test.string', $file);
    }

    public function testSetFileBadFileType()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'spipu_configuration' => $configurations,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $definitions = new Definitions($container);
        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList);
        $fileManager = FileManagerTest::getService();
        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $file = SymfonyMock::getUploadedFile($this, '/tmp/f.pdf', 'document.pdf', 'pdf', 'application/pdf');

        $this->expectException(ConfigurationException::class);
        $manager->setFile('mock.test.file', $file);
    }

    public function testSetFileBadFolder()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'spipu_configuration' => $configurations,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $definitions = new Definitions($container);
        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList);
        $fileManager = FileManagerTest::getService();
        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $file = SymfonyMock::getUploadedFile($this);

        $this->expectException(ConfigurationException::class);
        $manager->setFile('mock.test.file', $file);
    }

    public function testSetFileGood()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'kernel.project_dir'             => '/tmp/',
            'spipu_configuration' => $configurations,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $hasherFactory = new HasherFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $list = [];

        $entityManager = SymfonyMock::getEntityManager($this);

        $repository = $this->createMock(ConfigurationRepository::class);
        $repository
            ->expects($this->exactly(1))
            ->method('findAll')
            ->willReturnCallback(
                function () use (&$list) {
                    return $list;
                }
            );


        $definitions = new Definitions($container);
        $storage = SpipuConfigurationMock::getStorageMock($this, $definitions, $fieldList, $repository, $entityManager);
        $fileManager = FileManagerTest::getService();
        $manager = new ConfigurationManager($hasherFactory, $encryptor, $fieldList, $definitions, $storage, $fileManager);

        $this->assertSame('', $manager->get('mock.test.file'));

        $file = SymfonyMock::getUploadedFile($this);
        $file->expects($this->never())->method('move');

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('The file path does not exist or is not writable: /mock/project/media/foo/');
        $manager->setFile('mock.test.file', $file);
    }
}
