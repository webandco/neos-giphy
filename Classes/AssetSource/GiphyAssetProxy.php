<?php


namespace Webco\Giphy\AssetSource;


use GPH\Model\Gif;
use GPH\Model\User;
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
     * @var Gif
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
     * @param Gif $gif
     * @param GiphyAssetSource $assetSource
     */
    public function __construct(Gif $gif, GiphyAssetSource $assetSource)
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
        return $this->gif->getId();
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->gif->getSlug();
    }

    public function getFilename(): string
    {
        return $this->getIdentifier() . '.gif';
    }

    public function getLastModified(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $this->gif->getUpdateDatetime() ?? $this->gif->getImportDatetime());
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->gif->getImages()->getOriginal()->getSize();
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
        return intval($this->gif->getImages()->getOriginal()->getWidth());
    }

    /**
     * @return int|null
     */
    public function getHeightInPixels(): ?int
    {
        return intval($this->gif->getImages()->getOriginal()->getHeight());

    }

    /**
     * @return null|UriInterface
     */
    public function getThumbnailUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->gif->getImages()->getFixedWidthSmall()->getUrl());
    }

    /**
     * @return null|UriInterface
     */
    public function getPreviewUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->gif->getImages()->getOriginal()->getUrl());
    }

    /**
     * @return resource
     */
    public function getImportStream()
    {
        return fopen($this->gif->getImages()->getOriginal()->getUrl(), 'r');
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
                'Title' => $this->getLabel(),
                'CopyrightNotice' => $this->compileCopyrightNotice($this->gif->getUser()),
                'Creator' => $this->gif->getUsername()
            ];
        }

        return $this->iptcProperties;
    }

    /**
     * @param User $user
     * @return string
     * @throws \Neos\Eel\Exception
     */
    protected function compileCopyrightNotice(User $user): string
    {
        $userProperties = [
            'displayName' => $user->getDisplayName(),
            'username' => $user->getUsername(),
            'avatarUrl' => $user->getAvatarUrl(),
            'profileUrl' => $user->getProfileUrl(),
            'bannerUrl' => $user->getBannerUrl(),
            'twitter' => $user->getTwitter(),
        ];
        return Utility::evaluateEelExpression($this->assetSource->getCopyrightNoticeTemplate(), $this->eelEvaluator, ['user' => $userProperties], $this->defaultContextConfiguration);
    }

}
