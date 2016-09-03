<?php
namespace Fullspeed\CsvSerializerBundle\Serializer;


interface PropertyNameCoordinatable
{
    /**
     * @param string $originalPropertyName
     * @return string
     */
    public function coordinatePropertyName($originalPropertyName);
}