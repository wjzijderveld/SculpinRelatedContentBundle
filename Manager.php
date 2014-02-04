<?php
/**
 * Created at 04/02/14 21:53
 */

namespace Wjzijderveld\Sculpin\RelatedContentBundle;


use Sculpin\Contrib\ProxySourceCollection\ProxySourceCollection;
use Sculpin\Contrib\ProxySourceCollection\ProxySourceCollectionDataProvider;
use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;
use Sculpin\Contrib\ProxySourceCollection\Sorter\MetaSorter;
use Sculpin\Contrib\Taxonomy\ProxySourceTaxonomyDataProvider;
use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\DataProvider\DataProviderManager;

class Manager
{
    /**
     * @var \Sculpin\Core\DataProvider\DataProviderManager
     */
    private $dataProviderManager;

    public function __construct(DataProviderManager $dataProviderManager)
    {
        $this->dataProviderManager = $dataProviderManager;
    }

    public function getRelatedContent(array $config)
    {
        $sourceCollection = new ProxySourceCollection(array(), new MetaSorter('date', 'asc')); // TODO: Configure sorter

        $dataProviders = $this->dataProviderManager->dataProviders();
        foreach ($config as $dataProvider => $providerConfig) {

            if (!in_array($dataProvider, $dataProviders)) {
                throw new \InvalidArgumentException(sprintf('Given dataProvider "%s" for related content isn\'t registered', $dataProvider));
            }

            $dataProvider = $this->dataProviderManager->dataProvider($dataProvider);
            $data = $this->flattenDataFromProvider($dataProvider, $providerConfig);


            $newSourceCollection = $this->generateProxySourceCollection($data);
            $this->mergeSourceCollections($sourceCollection, $newSourceCollection);
        }

        $sourceCollection->init();
        return $sourceCollection;
    }

    /**
     * Iterates over the data and creates an ProxySourceCollection from it
     *
     * @param array $data
     * @return ProxySourceCollection
     */
    public function generateProxySourceCollection(array $data)
    {
        $proxySourceCollection = new ProxySourceCollection();

        foreach ($data as $sourceItem) {
            if (!$sourceItem instanceof ProxySourceItem) {
                throw new \InvalidArgumentException(sprintf('All data should be an instanceof ProxySourceItem, but found a %s', is_object($sourceItem) ? get_class($sourceItem) : gettype($sourceItem)));
            }

            $proxySourceCollection[] = $sourceItem;
        }

        return $proxySourceCollection;
    }

    /**
     * Add items from collectionB to collectionA
     *
     * @param ProxySourceCollection $collectionA
     * @param ProxySourceCollection $collectionB
     */
    public function mergeSourceCollections(ProxySourceCollection $collectionA, ProxySourceCollection $collectionB)
    {
        foreach ($collectionB as $item) {
            $collectionA[] = $item;
        }
    }

    /**
     * @param DataProviderInterface $dataProvider
     * @param $config
     * @return array
     * @throws \RuntimeException
     */
    public function flattenDataFromProvider(DataProviderInterface $dataProvider, $config)
    {
        $data = $dataProvider->provideData();
        if ($dataProvider instanceof ProxySourceTaxonomyDataProvider) {

            if (null === $config) {
                return array_map('array_merge', array_values($data));
            }

            $config = (array)$config;
            $collection = array();
            foreach ($config as $tag) {
                if (isset($data[$tag])) {
                    $collection += $data[$tag];
                }
            }

            return $collection;
        }

        if (!$dataProvider instanceof ProxySourceCollectionDataProvider) {
            throw new \RuntimeException(sprintf('Given dataProvider "%s" doesn\'t return ProxySourceItems which is required for RelatedContent', get_class($dataProvider)));
        }

        // When we are not dealing with a ProxySourceCollectionDataProvider, we already have an array with ProxySourceItems
        return $data;
    }
} 