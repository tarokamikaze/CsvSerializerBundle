<?php
namespace Fullspeed\CsvSerializerBundle\Csv;

use Goodby\CSV\Export\Standard\CsvFileObject;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;

/**
 * Class ExporterFactory
 * @package Fullspeed\CsvSerializerBundle\Csv
 */
class ExporterFactory
{
    /**
     * @var ExporterConfig
     */
    private $config;

    /**
     * ExporterFactory constructor.
     * @param $delimiter
     * @param $enclosure
     * @param $escape
     * @param $toCharset
     * @param $fromCharset
     */
    public function __construct($delimiter = ',', $enclosure = '"', $escape = '\\', $toCharset = 'SJIS-win', $fromCharset = 'auto')
    {
        $this->config = new ExporterConfig();
        $this->config->setDelimiter($delimiter)
            ->setEnclosure($enclosure)
            ->setEscape($escape)
            ->setToCharset($toCharset)
            ->setFromCharset($fromCharset)
            ->setFileMode(CsvFileObject::FILE_MODE_APPEND);
    }

    /**
     * @return Exporter
     */
    public function generate()
    {
        return new Exporter($this->config);
    }
}