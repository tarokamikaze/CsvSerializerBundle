<?php
/*
 * Copyright 2016 Eiji Kuwata
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fullspeed\CsvSerializerBundle\Tests\Fixtures;

use Fullspeed\CsvSerializerBundle\Serializer\PropertyNameCoordinatable;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Person
 * @package Fullspeed\CsvSerializerBundle\Tests\Fixtures
 *
 * @Serializer\AccessorOrder("custom", custom = {"gender"})
 */
class Person implements PropertyNameCoordinatable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     * @Serializer\SerializedName("sex")
     */
    private $gender;

    /**
     * @var integer
     *
     * @Serializer\Exclude()
     */
    private $age;

    /**
     * @return string
     *
     * @Serializer\VirtualProperty()
     */
    public function getType()
    {
        return 'Person';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @param string $originalPropertyName
     * @return string
     */
    public function coordinatePropertyName($originalPropertyName)
    {
        return 'Person.' . $originalPropertyName;
    }
}