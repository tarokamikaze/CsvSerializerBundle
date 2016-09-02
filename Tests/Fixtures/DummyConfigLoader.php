<?php
namespace Fullspeed\CsvSerializerBundle\Tests\Fixtures;


use Fullspeed\CsvSerializerBundle\Config\Loader\ConfigLoaderInterface;

class DummyConfigLoader implements ConfigLoaderInterface
{

    /**
     * 設定を取得する。
     *
     * @param string|object $classOrObject
     * @param bool $isUpload upload用か。初期値false.
     */
    public function getConfig($classOrObject, $isUpload = false)
    {
        // TODO: Implement getConfig() method.
    }
}