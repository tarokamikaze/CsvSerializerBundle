<?php
/**
 * Created by PhpStorm.
 * User: rn-160
 * Date: 2016/09/02
 * Time: 16:52
 */

namespace Fullspeed\CsvSerializerBundle\Config;


class PropertyConfig
{
    /**
     * カラム名（key）
     *
     * @var string
     */
    private $columnName;

    /**
     * カラム表示名
     *
     * @var string
     */
    private $viewName;

    /**
     * upload時の備考
     *
     * @var string
     */
    private $remarksForUpload;

    /**
     * CLIENT READ ONLY 時の出力可否
     *
     * @var bool
     */
    private $clientReadOnly;

    /**
     *
     * @param string $columnName
     * @param string $viewName
     * @param string $remarksForUpload
     * @param bool $useForUpload
     */
    public function __construct($columnName, $viewName, $remarksForUpload = null, $clientReadOnly = false)
    {
        $this->columnName = $columnName;
        $this->viewName = $viewName;
        $this->remarksForUpload = $remarksForUpload;
        $this->clientReadOnly = $clientReadOnly;
    }

    /**
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     *
     * @return string
     */
    public function getViewName()
    {
        return $this->viewName;
    }

    /**
     * @return string
     */
    public function getRemarksForUpload()
    {
        return $this->remarksForUpload;
    }

    /**
     * @return boolean
     */
    public function isClientReadOnly()
    {
        return $this->clientReadOnly;
    }
}