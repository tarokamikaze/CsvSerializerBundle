<?php
namespace Fullspeed\CsvSerializerBundle\Config\Loader;


interface ConfigLoaderInterface
{
    /**
     * 設定を取得する。
     *
     * @param string|object $classOrObject
     * @param bool $isUpload upload用か。初期値false.
     */
    public function getConfig($classOrObject, $isUpload = false);
}