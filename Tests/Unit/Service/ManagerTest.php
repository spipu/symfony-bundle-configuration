<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use Spipu\ConfigurationBundle\Entity\Configuration;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;
use Spipu\ConfigurationBundle\Service\FieldList;
use Spipu\ConfigurationBundle\Service\Manager;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Service\EncoderFactory;
use Spipu\CoreBundle\Service\Encryptor;
use Spipu\CoreBundle\Tests\SymfonyMock;

class ManagerTest extends TestCase
{
    /**
     * @param array $fileType
     * @return array
     */
    public function getConfigurations()
    {
        return [
            'mock.test.string' => [
                'code' => 'mock.test.string',
                'type' => 'string',
                'required' => true,
                'default' => 'default value',
                'options' => null,
                'unit' => null,
                'file_type' => [],
            ],
            'mock.test.integer' => [
                'code' => 'mock.test.integer',
                'type' => 'integer',
                'required' => false,
                'default' => '',
                'options' => null,
                'unit' => null,
                'file_type' => [],
            ],
            'mock.test.file' => [
                'code' => 'mock.test.file',
                'type' => 'file',
                'required' => true,
                'default' => '',
                'options' => null,
                'unit' => null,
                'file_type' => ['jpeg'],
            ]
        ];
    }

    public function testBase()
    {
        $parameters = [
            'kernel.project_dir'             => '/mock/project/',
            'spipu.configuration.file.allow' => true,
            'spipu.configuration.file.path'  => '/file_path/',
            'spipu.configuration.file.url'   => 'file_url/',
            'spipu_configuration'            => [],
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $entityManager = SymfonyMock::getEntityManager($this);
        $cachePool = SymfonyMock::getCachePool($this);
        $repository = $this->createMock(ConfigurationRepository::class);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

        $this->assertSame(
            $parameters['spipu.configuration.file.url'],
            $manager->getFileUrl()
        );

        $this->assertSame(
            $parameters['kernel.project_dir'] . DIRECTORY_SEPARATOR . $parameters['spipu.configuration.file.path'],
            $manager->getFilePath()
        );
    }

    public function testDefinitions()
    {
        $configurations = $this->getConfigurations();

        $container = SymfonyMock::getContainer($this, [], ['spipu_configuration' => $configurations]);
        $entityManager = SymfonyMock::getEntityManager($this);
        $cachePool = SymfonyMock::getCachePool($this);
        $repository = $this->createMock(ConfigurationRepository::class);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

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
        $entityManager = SymfonyMock::getEntityManager($this);
        $cachePool = SymfonyMock::getCachePool($this);
        $encoderFactory = new EncoderFactory();
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

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

        $expected = [
            'mock.test.string'  => 'default value',
            'mock.test.integer' => 42,
            'mock.test.file'    => '',
        ];

        // Fist Time : build
        $this->assertSame($expected, $manager->getAll());

        // Second Time : local cache
        $this->assertSame($expected, $manager->getAll());

        // Saved in cache pool
        $cacheItem = $cachePool->getItem($manager::CACHE_KEY);
        $this->assertSame(true, $cacheItem->isHit());
        $this->assertSame($expected, unserialize($cacheItem->get()));
        
        // Third Time : Cache Pool
        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);
        $this->assertSame($expected, $manager->getAll());

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $manager->get($key));
        }

        $this->expectException(ConfigurationException::class);
        $manager->get('wrong_key');
    }

    public function testSetValues()
    {
        $configurations = $this->getConfigurations();

        $container = SymfonyMock::getContainer($this, [], ['spipu_configuration' => $configurations]);
        $cachePool = SymfonyMock::getCachePool($this);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $entity = new Configuration();
        $entity
            ->setCode('mock.test.integer')
            ->setValue('42');

        $list = [$entity->getCode() => $entity];

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager
            ->expects($this->exactly(2))
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
            ->method('findOneBy')
            ->willReturnMap(
                [
                    [['code' => 'mock.test.integer'], null, $entity],
                    [['code' => 'mock.test.text'],    null, null],
                ]
            );

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

        $this->assertSame('default value', $manager->get('mock.test.string'));
        $this->assertSame(42, $manager->get('mock.test.integer'));

        $manager->set('mock.test.string', 'new value');
        $manager->set('mock.test.integer', 43);

        $this->assertSame('new value', $manager->get('mock.test.string'));
        $this->assertSame('43', $entity->getValue());
        $this->assertSame(43, $manager->get('mock.test.integer'));
    }

    public function testSetFileNotAllowed()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'spipu_configuration' => $configurations,
            'spipu.configuration.file.allow' => false,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $cachePool = SymfonyMock::getCachePool($this);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);
        $entityManager = SymfonyMock::getEntityManager($this);
        $repository = $this->createMock(ConfigurationRepository::class);

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

        $file = SymfonyMock::getUploadedFile($this);

        $this->expectException(ConfigurationException::class);
        $manager->setFile('mock.test.file', $file);
    }

    public function testSetFileBadFieldType()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'spipu_configuration' => $configurations,
            'spipu.configuration.file.allow' => true,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $cachePool = SymfonyMock::getCachePool($this);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);
        $entityManager = SymfonyMock::getEntityManager($this);
        $repository = $this->createMock(ConfigurationRepository::class);

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

        $file = SymfonyMock::getUploadedFile($this);

        $this->expectException(ConfigurationException::class);
        $manager->setFile('mock.test.string', $file);
    }

    public function testSetFileBadFileType()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'spipu_configuration' => $configurations,
            'spipu.configuration.file.allow' => true,
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $cachePool = SymfonyMock::getCachePool($this);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);
        $entityManager = SymfonyMock::getEntityManager($this);
        $repository = $this->createMock(ConfigurationRepository::class);

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

        $file = SymfonyMock::getUploadedFile($this, '/tmp/f.pdf', 'document.pdf', 'pdf', 'application/pdf');

        $this->expectException(ConfigurationException::class);
        $manager->setFile('mock.test.file', $file);
    }

    public function testSetFileBadFolder()
    {
        $configurations = $this->getConfigurations();

        $parameters = [
            'kernel.project_dir'             => '/mock/project',
            'spipu_configuration' => $configurations,
            'spipu.configuration.file.allow' => true,
            'spipu.configuration.file.path'  => 'file_path/',
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $cachePool = SymfonyMock::getCachePool($this);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);
        $entityManager = SymfonyMock::getEntityManager($this);
        $repository = $this->createMock(ConfigurationRepository::class);

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);

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
            'spipu.configuration.file.allow' => true,
            'spipu.configuration.file.path'  => '/',
        ];

        $container = SymfonyMock::getContainer($this, [], $parameters);
        $cachePool = SymfonyMock::getCachePool($this);
        $encoderFactory = new EncoderFactory();
        $encryptor = new Encryptor('my_secret_phrase');
        $fieldList = new FieldList([new Field\FieldString(), new Field\FieldInteger(), new Field\FieldFile()]);

        $list = [];

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
            ->expects($this->exactly(1))
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
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $manager = new Manager($container, $entityManager, $cachePool, $repository, $encoderFactory, $encryptor, $fieldList);


        $this->assertSame('', $manager->get('mock.test.file'));

        $file = SymfonyMock::getUploadedFile($this);
        $file->expects($this->once())->method('move');

        $manager->setFile('mock.test.file', $file);

        $this->assertSame(md5('mock.test.file') . '.' . $file->guessExtension(), $manager->get('mock.test.file'));
    }
}
