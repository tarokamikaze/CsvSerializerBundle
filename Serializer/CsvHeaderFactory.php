<?php
namespace Fullspeed\CsvSerializerBundle\Serializer;


interface CsvHeaderFactory
{
    /**
     * @param object $root
     * @param Context $context
     * @return string[]
     */
    public function generate($root, Context $context);
}