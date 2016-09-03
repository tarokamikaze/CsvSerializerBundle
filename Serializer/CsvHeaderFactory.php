<?php
namespace Fullspeed\CsvSerializerBundle\Serializer;


interface CsvHeaderFactory
{
    /**
     * @param $root
     * @return string[]
     */
    public function generate($root);
}