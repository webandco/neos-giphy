<?php
declare(strict_types=1);

namespace Webco\Giphy\AssetSource;


use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;

class GiphyAssetProxyQueryResult implements AssetProxyQueryResultInterface
{
    /**
     * @var GiphyAssetSource
     */
    private $assetSource;

    /**
     * @var mixed
     */
    private $giphyQueryResult;

    /**
     * @var \Iterator
     */
    private $giphyQueryResultIterator;

    /**
     * @var GiphyAssetProxyQuery
     */
    private $giphyAssetProxyQuery;

    /**
     * GiphyAssetProxyQueryResult constructor.
     * @param GiphyAssetProxyQuery $query
     * @param mixed $giphyQueryResult
     * @param GiphyAssetSource $assetSource
     */
    public function __construct(GiphyAssetProxyQuery $query, $giphyQueryResult, GiphyAssetSource $assetSource)
    {
        $this->giphyAssetProxyQuery = $query;
        $this->assetSource = $assetSource;
        $this->giphyQueryResult = $giphyQueryResult;
        $this->giphyQueryResultIterator = (new \ArrayObject($this->giphyQueryResult->data))->getIterator();
    }

    /**
     * @return AssetProxyQueryInterface
     */
    public function getQuery(): AssetProxyQueryInterface
    {
        return clone $this->giphyAssetProxyQuery;
    }

    /**
     * @return AssetProxyInterface|null
     */
    public function getFirst(): ?AssetProxyInterface
    {
        return $this->offsetGet(0);
    }

    /**
     * @return AssetProxyInterface[]
     */
    public function toArray(): array
    {
        $assetProxies = [];
        foreach ($this->giphyQueryResult->data as $gif) {
            array_push($assetProxies, new GiphyAssetProxy($gif, $this->assetSource));
        }
        return $assetProxies;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return new GiphyAssetProxy($this->giphyQueryResultIterator->current(), $this->assetSource);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->giphyQueryResultIterator->next();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->giphyQueryResultIterator->key();
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->giphyQueryResultIterator->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->giphyQueryResultIterator->rewind();
    }

    /**
     * Returns true if offset exists. False otherwise.
     * @param integer $offset Offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->giphyQueryResultIterator->offsetExists($offset);
    }

    /**
     * Gets offset.
     * @param integer $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return new GiphyAssetProxy($this->giphyQueryResultIterator->offsetGet($offset), $this->assetSource);
    }

    /**
     * Sets value based on offset.
     * @param integer $offset Offset
     * @param mixed $value Value to be set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->giphyQueryResultIterator->offsetSet($offset, $value);
    }

    /**
     * Unsets offset.
     * @param integer $offset Offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->giphyQueryResultIterator->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     * @return int
     */
    public function count()
    {
        return $this->giphyQueryResult->pagination->total_count;
    }

}
