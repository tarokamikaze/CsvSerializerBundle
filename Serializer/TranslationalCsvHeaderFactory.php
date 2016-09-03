<?php
namespace Fullspeed\CsvSerializerBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Metadata\PropertyMetadata;
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

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param $root
     * @param Context $context
     * @return array
     */
    public function generate($root, Context $context)
    {
        if (!is_object($root)) {
            throw new RuntimeException("The root argument must be an object, " . gettype($root) . " given.");
        }
        $metadata = $context->getMetadataFactory()->getMetadataForClass(get_class($root));

        $header = [];

        foreach ($metadata->propertyMetadata as $propertyMetadata) {
            $exclusionStrategy = $context->getExclusionStrategy();
            if ($exclusionStrategy && $exclusionStrategy->shouldSkipProperty($propertyMetadata, $context)) {
                continue;
            }

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