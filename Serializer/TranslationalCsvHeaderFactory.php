<?php
namespace Fullspeed\CsvSerializerBundle\Serializer;

use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TranslationalCsvHeaderFactory
 * @package Fullspeed\CsvSerializerBundle\Serializer
 */
class TranslationalCsvHeaderFactory implements CsvHeaderFactory
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;


    public function __construct(TranslatorInterface $translator, MetadataFactoryInterface $metadataFactory)
    {
        $this->translator = $translator;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param $root
     * @return string[]
     */
    public function generate($root)
    {
        if (!is_object($root)) {
            throw new RuntimeException("The root argument must be an object, " . gettype($root) . " given.");
        }
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($root));

        $header = [];

        foreach ($metadata->propertyMetadata as $propertyMetadata) {
            $header[] = $this->translate($propertyMetadata, $root);
        }

        return $header;
    }

    /**
     * @param PropertyMetadata $metadata
     * @param $root
     * @return string
     */
    private function translate(PropertyMetadata $metadata, $root)
    {
        $tmpNames = [
            $metadata->serializedName,
            $metadata->name
        ];

        $tmpNames = array_filter($tmpNames);

        if (is_object($root) && $root instanceof PropertyNameCoordinatable) {
            $tmpNames = array_merge(array_map(function ($name) use ($root) {
                return $root->coordinatePropertyName($name);
            }, $tmpNames), $tmpNames);
        }

        foreach ($tmpNames as $tmpName) {
            $translatedName = $this->translator->trans($tmpName);
            if ($translatedName != $tmpName) {
                return $translatedName;
            }
        }

        return ($metadata->serializedName) ? $metadata->serializedName : $metadata->name;
    }
}