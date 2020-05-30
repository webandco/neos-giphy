<?php


namespace Webco\Giphy\AssetSource;


use GPH\Api\DefaultApi;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;

class GiphyAssetProxyQuery implements AssetProxyQueryInterface
{

    /**
     * @var GiphyAssetSource
     */
    private $assetSource;

    /**
     * @var int
     */
    private $limit = 20;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var string
     */
    private $searchTerm = '';

    /**
     * @param GiphyAssetSource $assetSource
     */
    public function __construct(GiphyAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param string $searchTerm
     */
    public function setSearchTerm(string $searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     * @throws \Exception
     */
    public function execute(): AssetProxyQueryResultInterface
    {
        $giphyApi = new DefaultApi();

        if ($this->searchTerm === '') {
            $gifs =$giphyApi->gifsTrendingGet($this->assetSource->getApiKey(), $this->limit);
        } else {
            $gifs = $giphyApi->gifsSearchGet($this->assetSource->getApiKey(), $this->searchTerm, $this->limit, $this->offset);
        }

        return new GiphyAssetProxyQueryResult($this, $gifs, $this->assetSource);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function count(): int
    {
        return $this->execute()->count();
    }

}
