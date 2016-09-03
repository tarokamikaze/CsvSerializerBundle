<?php
namespace Fullspeed\CsvSerializerBundle\Serializer\Visitor;


use Fullspeed\CsvSerializerBundle\Csv\ExporterFactory;
use Fullspeed\CsvSerializerBundle\Serializer\CsvHeaderFactory;
use Goodby\CSV\Export\Standard\CsvFileObject;
use Goodby\CSV\Export\Standard\Exporter;
use JMS\Serializer\GenericSerializationVisitor;

class CsvSerializationVisitor extends GenericSerializationVisitor
{

    /**
     * @var CsvHeaderFactory
     */
    private $headerFactory;

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
        CsvHeaderFactory $headerFactory,
        ExporterFactory $exporterFactory,
        $cacheDir
    ) {
        parent::__construct($namingStrategy);
        $this->headerFactory = $headerFactory;
        $this->exporterFactory = $exporterFactory;
        $this->dir = $cacheDir . DIRECTORY_SEPARATOR . 'csv_serializer';
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function prepare($data)
    {


        // 初期化
        $this->initialize();

        // 渡ってきている$dataが配列の類かを判別する。
        // 配列の類だったら、あとでカラム順等の設定を読み込むために
        // 要素のクラスを取得する。
        // visitArray() などでチェックしたいところだが,それだと
        // 最も上にある要素が配列か否かが判別できないので
        // prepare() のタイミングで実装する。
        // @see https://github.com/schmittjoh/serializer/blob/master/src/JMS/Serializer/Serializer.php#L91
        $collectionTypes = array(
            'ArrayCollection',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\ORM\PersistentCollection',
            'Doctrine\ODM\MongoDB\PersistentCollection',
            'Doctrine\ODM\PHPCR\PersistentCollection'
        );

        // Get the first item.
        $first = null;
        if (is_object($data)) {
            foreach ($collectionTypes as $type) {
                if ($data instanceof $type || is_subclass_of($data, $type)) {
                    $first = $data->first();
                    break;
                }
            }
        } elseif (is_array($data)) {
            $first = reset($data);
        }

        $csvHeader = $this->headerFactory->generate($first);
        $this->exporter->export($this->filePath, [$csvHeader]);

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