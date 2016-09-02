<?php
namespace Fullspeed\CsvSerializerBundle\Serializer\Visitor;


use Fullspeed\CsvSerializerBundle\Config\Loader\ConfigLoaderInterface;
use JMS\Serializer\GenericSerializationVisitor;
use Symfony\Component\Translation\TranslatorInterface;

class CsvSerializationVisitor extends GenericSerializationVisitor
{
    /**
     * csv設定。
     *
     * @var CsvColumnConfig[]
     */
    protected $configs;

    /**
     *
     * @var ConfigLoaderInterface
     */
    protected $csvConfigLoader;

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * CsvSerializationVisitor constructor.
     * @param \JMS\Serializer\Naming\PropertyNamingStrategyInterface $namingStrategy
     * @param TranslatorInterface $translator
     * @param ConfigLoaderInterface $csvConfigLoader
     * @param $cacheDir
     */
    public function __construct(\JMS\Serializer\Naming\PropertyNamingStrategyInterface $namingStrategy, TranslatorInterface $translator, ConfigLoaderInterface $csvConfigLoader, $cacheDir)
    {
        parent::__construct($namingStrategy);
        $this->translator = $translator;
        $this->csvConfigLoader = $csvConfigLoader;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        // prepare() => (いろいろ) => getResult() で情報を取ってくる順番は
        // JMSSerializerで保証されている。
        // よってこのメソッドでも、その順番を前提に実行する。
        // 具体的には、prepare() メソッドで情報（$this->configs）がとれていること。
        // とれなければ既にExceptionが投げられているはず。

        // 念のためのチェック
        if (!$this->configs) {
            throw new UnsupportedFormatException();
        }

        // カラム定義の取得.
        $dir = $this->cacheDir . DIRECTORY_SEPARATOR . 'CsvSerializationVisitor';
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
            @chmod($dir, 0777);
        }

        // TODO FIX ME!
//        $csv = new CsvConvertor($dir . DIRECTORY_SEPARATOR);
//        $csv->setFilePath();
//
//        // create header.
//        $csv->convert($this->createHeader());

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
            $csv->convert($this->createRegulatedRow($row));
        }
        $result = file_get_contents($csv->getFilePath());

        // デストラクタ処理。
        @unlink($csv->getFilePath());
        $this->configs = null;

        return $result;
    }

    /**
     *
     * @return string[]
     */
    private function createRegulatedRow(array $row)
    {
        $result = [];
        foreach ($this->configs as $config) {
            $key = $config->getColumnName();
            $value = null;
            if (isset($row[$key])) {
                $value = $row[$key];
            }
            if (is_object($value) || is_array($value)) {
                throw new \InvalidArgumentException('The value is an object or an array.check your configuration. column: ' . $key . ' value: ' . serialize($value));
            }
            $result[] = (isset($row[$key])) ? $value : null;
        }

        return $result;
    }

    /**
     *
     * @return string[]
     */
    private function createHeader()
    {
        $result = [];
        foreach ($this->configs as $config) {
            $result[] = $this->translator->trans($config->getViewName());
        }

        return $result;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function prepare($data)
    {
        // 初期化
        $this->configs = null;

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

        // 最初のアイテムを取得
        $first = null;
        if (is_object($data)) {
            // オブジェクトが渡ってきた場合、コレクション系であれば取得
            foreach ($collectionTypes as $type) {
                if ($data instanceof $type || is_subclass_of($data, $type)) {
                    $first = $data->first();
                    break;
                }
            }
        } elseif (is_array($data)) {
            // 配列はreset関数で一発。
            $first = reset($data);
        }

        // 配列で、かつオブジェクトであれば,設定をとっておく。
        try {
            $this->configs = $this->csvConfigLoader->getConfig(get_class($first));
        } catch (\Exception $e) {
        }

        // ここまできて設定がとれていなければ、エラー。
        if (!$this->configs) {
            throw new UnsupportedFormatException();
        }

        return parent::prepare($data);
    }
}