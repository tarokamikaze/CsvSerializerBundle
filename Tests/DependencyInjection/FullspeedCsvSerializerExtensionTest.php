<?php
namespace Fullspeed\CsvSerializerBundle\Tests\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Fullspeed\CsvSerializerBundle\FullspeedCsvSerializerBundle;
use Fullspeed\CsvSerializerBundle\Tests\Fixtures\Person;
use Goodby\CSV\Export\Standard\CsvFileObject;
use JMS\Serializer\Serializer;
use JMS\SerializerBundle\JMSSerializerBundle;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

class FullspeedCsvSerializerExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->clearTempDir();
    }

    protected function tearDown()
    {
        $this->clearTempDir();
    }

    private function clearTempDir()
    {
        // clear temporary directory
        $dir = sys_get_temp_dir() . '/serializer';
        if (is_dir($dir)) {
            foreach (new \RecursiveDirectoryIterator($dir) as $file) {
                $filename = $file->getFileName();
                if ('.' === $filename || '..' === $filename) {
                    continue;
                }
                @unlink($file->getPathName());
            }
            @rmdir($dir);
        }
    }

    public function testLoad()
    {
        $container = $this->getContainerForConfig(array(array()));
        $serializer = $container->get('serializer');

        $person1 = new Person();
        $person1->setAge(20);
        $person1->setName('Taro Kamikaze');
        $person1->setGender('Male');

        $person2 = new Person();
        $person2->setAge(30);
        $person2->setName('Eiji Kuwata');
        $person2->setGender('Female');

        /** @var CsvFileObject $csv */
        $csv = $serializer->serialize([$person1, $person2], 'csv');

        $actual = file_get_contents($csv->getRealPath());
        $actual = mb_convert_encoding($actual, 'utf-8', 'SJIS-win');

        self::assertEquals(3, substr_count($actual, "\r\n"));
    }

    private function getContainerForConfig(array $configs, KernelInterface $kernel = null)
    {
        if (null === $kernel) {
            $kernel = self::createMock('Symfony\Component\HttpKernel\KernelInterface');
            $kernel
                ->expects($this->any())
                ->method('getBundles')
                ->will($this->returnValue(array()));
        }
        $bundle = new FullspeedCsvSerializerBundle($kernel);
        $extension = $bundle->getContainerExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array());
        $container->set('annotation_reader', new AnnotationReader());

        $translator = self::createMock('Symfony\\Component\\Translation\\TranslatorInterface');
        $translator->expects(self::any())->method('trans')->willReturnArgument(0);
        $container->set('translator', $translator);

        $container->set('debug.stopwatch', self::createMock('Symfony\\Component\\Stopwatch\\Stopwatch'));

        $container->registerExtension($extension);
        $extension->load($configs, $container);
        $bundle->build($container);

        $jmsSerializerBundle = new JMSSerializerBundle($kernel);
        $jmsSerializerExtension = $jmsSerializerBundle->getContainerExtension();
        $container->registerExtension($jmsSerializerExtension);
        $jmsSerializerExtension->load($configs, $container);
        $jmsSerializerBundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array(
            new ResolveParameterPlaceHoldersPass(),
            new ResolveDefinitionTemplatesPass(),
        ));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}