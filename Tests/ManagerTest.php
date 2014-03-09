<?php
/**
 * Test for Wjzijderveld\Sculpin\RelatedContentBundle\Manager
 */

namespace Wjzijderveld\Sculpin\RelatedContentBundle;

use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Contrib\ProxySourceCollection\ProxySourceCollection;
use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $proxySourceTaxonomy;

    protected $dataProviderManager;

    protected $manager;

    /**
     * Setup a default ProxySourceCollection
     * Setup a dataprovidermanager with a ProxySourceDataProvider Mock
     * Initiate the manager with the dataprovidermanager
     *
     * @return void
     */
    public function setUp()
    {
        $this->proxySourceTaxonomy = array(
            'foo' => array(
                $this->getProxySourceItemMock('foo.md', '2014-03-01 20:00:00'), 
                $this->getProxySourceItemMock('bar.md', '2014-03-02 21:30:15'),
                
            ),
            'bar' => array(
                $this->getProxySourceItemMock('foobar.md', '2014-03-03 19:30:00'),
            )
        );

        $this->dataProviderManager = new DataProviderManager();
        $this->dataProviderManager->registerDataProvider('post_tags', $this->getProxySourceTaxonomyDataProviderMock());
        $this->manager = new \Wjzijderveld\Sculpin\RelatedContentBundle\Manager($this->dataProviderManager);
    }

    public function getProxySourceItems()
    {
        $items = array();

        return $items;
    }

    public function getProxySourceTaxonomyDataProviderMock()
    {
        $mock = $this->getMockBuilder('Sculpin\Contrib\Taxonomy\ProxySourceTaxonomyDataProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('provideData'))
            ->getMock();

        $mock->expects($this->any())
            ->method('provideData')
            ->will($this->returnValue($this->proxySourceTaxonomy));

        return $mock;
    }

    /**
     * Tests if the method generateProxySourceCollections returns the expected result
     *
     * @test
     * @return void
     */
    public function it_should_create_a_proxy_source_collection()
    {
        $items = array(
            $this->getProxySourceItemMock('foobar.md', '2014-03-01 20:00:00'),
            $this->getProxySourceItemMock('bardoo.md', '2014-02-02 20:00:00'),
        ); 
        $hashes = array_map('spl_object_hash', $items);

        $collection = $this->manager->generateProxySourceCollection($items);
        $this->assertCount(2, $collection);
        $this->assertEquals($hashes[0], spl_object_hash($items[0]));
    }

    /**
     * Test weither the method getRelatedContent returns what it should return
     *
     * @test
     * @return void
     */
    public function it_should_return_related_sources_for_single_tag()
    {
        // Temporary disable error_handler, Mock objects don't work well with u(a)sort
        // @link https://stackoverflow.com/questions/19632417/uasort-array-was-modified-by-the-user-comparison-function-with-mock-objects
        set_error_handler(function($errnr, $errstr) {});
        $collection = $this->manager->getRelatedContent(array('post_tags' => 'foo'));
        restore_error_handler();
        $this->assertCount(2, $collection);
        $this->assertEquals('foo.md', $collection[0]['filename']);
        $this->assertEquals('bar.md', $collection[1]['filename']);
    }

    public function getProxySourceItemMock($filename, $date)
    {
        $data = new \Dflydev\DotAccessConfiguration\Configuration();
        $data->set('filename', $filename);
        $data->set('date', $date);
        $fileSource = $this->getMockBuilder('Sculpin\Core\Source\SourceInterface')->setMethods(array('data', 'filename'))->getMockForAbstractClass();
        $fileSource->expects($this->any())
            ->method('data')
            ->will($this->returnValue($data));
        $fileSource->expects($this->any())
            ->method('filename')
            ->will($this->returnValue($filename));
        $proxy = new \Sculpin\Contrib\ProxySourceCollection\ProxySourceItem($fileSource);

        return $proxy;
    }
}
