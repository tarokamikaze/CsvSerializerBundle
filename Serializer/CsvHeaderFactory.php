<?php
namespace Fullspeed\CsvSerializerBundle\Serializer;


use JMS\Serializer\Context;

interface CsvHeaderFactory
{
    /**
     * @param object $root
     * @param Context $context
     * @return string[]
     */
    public function generate($root, Context $context);
}