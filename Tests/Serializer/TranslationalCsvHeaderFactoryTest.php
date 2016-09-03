<?php
namespace Fullspeed\CsvSerializerBundle\Tests\Serializer;

use Fullspeed\CsvSerializerBundle\Serializer\TranslationalCsvHeaderFactory;
use Fullspeed\CsvSerializerBundle\Tests\Fixtures\Person;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;

/**
 * Class TranslationalCsvHeaderFactoryTest
 * @package Fullspeed\CsvSerializerBundle\Tests\Serializer
 */
class TranslationalCsvHeaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslationalCsvHeaderFactory
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Metadata\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     *
     */
    protected function setUp()
    {
        $this->translator = self::createMock('Symfony\Component\Translation\TranslatorInterface');

        $className = get_class(new Person());

        $metadata = new ClassMetadata($className);
        //  metadata uses serializedName.
        $anotherNamePropertyMetadata = new PropertyMetadata($className, 'gender');
        $anotherNamePropertyMetadata->serializedName = 'sex';
        $metadata->addPropertyMetadata($anotherNamePropertyMetadata);

        // metadata uses virtualProperty.
        $virtualPropertyMetadata = new VirtualPropertyMetadata($className, 'type');
        $metadata->addPropertyMetadata($virtualPropertyMetadata);

        // normal metadata.
        $normalMetadata = new PropertyMetadata($className, 'name');
        $metadata->addPropertyMetadata($normalMetadata);

        $this->metadataFactory = self::createMock('Metadata\MetadataFactoryInterface');
        $this->metadataFactory->expects($this->once())->method('getMetadataForClass')->willReturn($metadata);

        $this->object = new TranslationalCsvHeaderFactory($this->translator, $this->metadataFactory);
    }


    /**
     *
     */
    public function test_generate()
    {
        $this->translator->expects($this->any())->method('trans')->willReturnCallback(function ($arg) {
            $master = [
                'Person.sex' => '性別',
                'type' => 'タイプ'
            ];

            return isset($master[$arg]) ? $master[$arg] : $arg;
        });

        $person = new Person();
        $person->setGender('male');
        $person->setName('foo');

        $actual = $this->object->generate($person);
        self::assertEquals(['性別', 'タイプ', 'name'], $actual);
    }
}