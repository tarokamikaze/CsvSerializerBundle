<?php
namespace Fullspeed\CsvSerializerBundle\Serializer\Visitor;


use Fullspeed\CsvSerializerBundle\Csv\ExporterFactory;
use Fullspeed\CsvSerializerBundle\Serializer\EventSubscriber\CsvHeaderEventSubscriber;
use Goodby\CSV\Export\Standard\CsvFileObject;
use Goodby\CSV\Export\Standard\Exporter;
use JMS\Serializer\GenericSerializationVisitor;

class CsvSerializationVisitor extends GenericSerializationVisitor
{

    /**
     * @var CsvHeaderEventSubscriber
     */
    private $headerSubscriber;

    /**
     * @var ExporterFactory
     */
    private $exporterFactory;

    /**
     *
     * @var string
     */
    protected $dir;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Exporter
     */
    private $exporter;

    /**
     * CsvSerializationVisitor constructor.
     * @param \JMS\Serializer\Naming\PropertyNamingStrategyInterface $namingStrategy
     * @param CsvHeaderFactory $headerFactory
     * @param ExporterFactory $exporterFactory
     * @param $cacheDir
     */
    public function __construct(
        \JMS\Serializer\Naming\PropertyNamingStrategyInterface $namingStrategy,
        CsvHeaderEventSubscriber $headerSubscriber,
        ExporterFactory $exporterFactory,
        $cacheDir
    ) {
        parent::__construct($namingStrategy);
        $this->headerSubscriber = $headerSubscriber;
        $this->exporterFactory = $exporterFactory;
        $this->dir = $cacheDir . DIRECTORY_SEPARATOR . 'csv_serializer';
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function prepare($data)
    {
        $this->initialize();
        return parent::prepare($data);
    }

    private function initialize()
    {
        do {
            $this->filePath = $this->dir . DIRECTORY_SEPARATOR . $this->generateFileName();
        } while (file_exists($this->filePath));

        $dirName = dirname($this->filePath);
        if (!file_exists($dirName)) {
            @mkdir($dirName, 0777, true);
        }

        $this->exporter = $this->exporterFactory->generate();

        $this->headerSubscriber->initialize($this->exporter,$this->filePath);
    }

    private function clear()
    {
        $this->filePath = $this->exporter = null;
    }

    private function generateFileName()
    {
        $basename = md5(md5(mt_rand()));
        $splitName = str_split($basename);
        $result = '';
        for ($i = 0; $i < 5; $i++) {
            if ($result !== '') {
                $result .= DIRECTORY_SEPARATOR;
            }
            $result .= $splitName[$i];
        }

        return $result . DIRECTORY_SEPARATOR . $basename;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        // prepare() => (いろいろ) => getResult() で情報を取ってくる順番は
        // JMSSerializerで保証されている。
        // よってこのメソッドでも、その順番を前提に実行する。

        // rootの特定。ViewDataCollection には、実際のアイテムはelements の中に入っているので
        // それだけをeachで回す。
        $root = $this->getRoot();
        if (isset($root['elements'])) {
            $root = $root['elements'];
        }
        // create body.
        foreach ($root as $row) {
            if (!is_array($row)) {
                continue;
            }
            $this->exporter->export($this->filePath, [$row]);
        }

        $response = new CsvFileObject($this->filePath);
        $this->clear();

        return $response;
    }
}