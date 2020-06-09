<?php
declare(strict_types=1);
namespace Webandco\Giphy\AssetSource;


use Neos\Eel\EelEvaluatorInterface;
use Neos\Eel\Utility;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\SupportsIptcMetadataInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class GiphyAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface, SupportsIptcMetadataInterface
{
    /**
     * @var mixed
     */
    private $gif;

    /**
     * @var GiphyAssetSource
     */
    private $assetSource;

    /**
     * @var ImportedAsset
     */
    private $importedAsset;

    /**
     * @var array
     */
    private $iptcProperties;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="defaultContext", package="Neos.Fusion")
     */
    protected $defaultContextConfiguration;

    /**
     * @var EelEvaluatorInterface
     * @Flow\Inject(lazy=false)
     */
    protected $eelEvaluator;

    /**
     * @var UriFactoryInterface
     * @Flow\Inject(lazy=false)
     */
    protected $uriFactory;

    /**
     * @param mixed $gif
     * @param GiphyAssetSource $assetSource
     */
    public function __construct($gif, GiphyAssetSource $assetSource)
    {
        $this->gif = $gif;
        $this->assetSource = $assetSource;
        $this->importedAsset = (new ImportedAssetRepository)->findOneByAssetSourceIdentifierAndRemoteAssetIdentifier($assetSource->getIdentifier(), $this->getIdentifier());
    }

    /**
     * @return AssetSourceInterface
     */
    public function getAssetSource(): AssetSourceInterface
    {
        return $this->assetSource;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->gif->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->gif->title;
    }

    public function getFilename(): string
    {
        return $this->gif->slug . '.gif';
    }

    public function getLastModified(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $this->gif->update_datetime ?? $this->gif->import_datetime);
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return intval($this->gif->images->original->size);
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return 'image/gif';
    }

    /**
     * @return int|null
     */
    public function getWidthInPixels(): ?int
    {
        return intval($this->gif->images->original->width);
    }

    /**
     * @return int|null
     */
    public function getHeightInPixels(): ?int
    {
        return intval($this->gif->images->original->height);

    }

    /**
     * @return null|UriInterface
     */
    public function getThumbnailUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->gif->images->fixed_width_small->url);
    }

    /**
     * @return null|UriInterface
     */
    public function getPreviewUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->gif->images->original->url);
    }

    /**
     * @return resource
     */
    public function getImportStream()
    {
        return fopen($this->gif->images->original->url, 'r');
    }

    public function getLocalAssetIdentifier(): ?string
    {
        return $this->importedAsset instanceof ImportedAsset ? $this->importedAsset->getLocalAssetIdentifier() : '';
    }

    public function isImported(): bool
    {
        return $this->importedAsset !== null;
    }

    /**
     * @param string $propertyName
     * @return bool
     * @throws \Neos\Eel\Exception
     */
    public function hasIptcProperty(string $propertyName): bool
    {
        return isset($this->getIptcProperties()[$propertyName]);
    }

    /**
     * @param string $propertyName
     * @return string
     * @throws \Neos\Eel\Exception
     */
    public function getIptcProperty(string $propertyName): string
    {
        return $this->getIptcProperties()[$propertyName] ?? '';
    }

    /**
     * @return array
     * @throws \Neos\Eel\Exception
     */
    public function getIptcProperties(): array
    {
        if ($this->iptcProperties === null) {
            $this->iptcProperties = [
                'Title' => $this->gif->title,
                'CopyrightNotice' => $this->compileCopyrightNotice(),
                'Creator' => $this->gif->username
            ];
        }

        return $this->iptcProperties;
    }

    /**
     * @return string
     * @throws \Neos\Eel\Exception
     */
    protected function compileCopyrightNotice(): string
    {
        if (isset($this->gif->user)) {
            $user = $this->gif->user;
        } else {
            $user = [
                'display_name' => $this->gif->username,
                'usernmae' => $this->gif->username
            ];
        }

        return Utility::evaluateEelExpression($this->assetSource->getCopyrightNoticeTemplate(), $this->eelEvaluator, ['user' => $user], $this->defaultContextConfiguration);
    }

}
