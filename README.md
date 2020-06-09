# Neos Giphy Assetsource

## Installation

```
composer require webandco/neos-giphy
```

## How to use

1. Get an APi ket from https://developers.giphy.com/
2. Configure the API Key in Settings.yaml

## Configuration

```yaml
Neos:
  Media:
    assetSources:
      giphy:
        assetSource: 'Webandco\Giphy\AssetSource\GiphyAssetSource'
        assetSourceOptions:
          apiKey: 'yourAPIKey'
          copyrightNoticeTemplate: '${"Photo by <a href=\"" + user.profile_url + "\">" + user.display_name + "</a>"}'
```

The following data can be used in `copyrightNoticeTemplate`

- user.display_name
- user.username
- user.avatar_url
- user.banner_url
- user.profile_url
