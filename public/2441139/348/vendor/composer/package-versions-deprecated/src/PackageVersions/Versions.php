<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;

class_exists(InstalledVersions::class);

/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'phpmyadmin/phpmyadmin';

    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS          = array (
  'google/recaptcha' => '1.2.4@614f25a9038be4f3f2da7cbfd778dc5b357d2419',
  'nikic/fast-route' => 'v1.3.0@181d480e08d9476e61381e04a71b34dc0432e812',
  'phpmyadmin/motranslator' => '5.2.0@cea68a8d0abf5e7fabc4179f07ef444223ddff44',
  'phpmyadmin/shapefile' => '2.1@e23b767f2a81f61fee3fc09fc062879985f3e224',
  'phpmyadmin/sql-parser' => '5.4.2@b210e219a54df9b9822880780bb3ba0fffa1f542',
  'phpmyadmin/twig-i18n-extension' => 'v3.0.0@1f509fa3c3f66551e1f4a346e4477c6c0dc76f9e',
  'phpseclib/phpseclib' => '2.0.33@fb53b7889497ec7c1362c94e61d8127ac67ea094',
  'psr/cache' => '2.0.0@213f9dbc5b9bfbc4f8db86d2838dc968752ce13b',
  'psr/container' => '1.1.1@8622567409010282b7aeebe4bb841fe98b58dcaf',
  'psr/log' => '2.0.0@ef29f6d262798707a9edd554e2b82517ef3a9376',
  'symfony/cache' => 'v5.3.7@864867b13bd67347497ce956f4b253f8fe18b80c',
  'symfony/cache-contracts' => 'v2.4.0@c0446463729b89dd4fa62e9aeecc80287323615d',
  'symfony/config' => 'v4.4.30@d9ea72de055cd822e5228ff898e2aad2f52f76b0',
  'symfony/dependency-injection' => 'v4.4.27@52866e2cb314972ff36c5b3d405ba8f523e56f6e',
  'symfony/deprecation-contracts' => 'v2.4.0@5f38c8804a9e97d23e0c8d63341088cd8a22d627',
  'symfony/expression-language' => 'v4.4.30@78a014771042079cca30716c8471e7a0b985bd22',
  'symfony/filesystem' => 'v5.3.4@343f4fe324383ca46792cae728a3b6e2f708fb32',
  'symfony/polyfill-ctype' => 'v1.23.0@46cd95797e9df938fdd2b03693b5fca5e64b01ce',
  'symfony/polyfill-mbstring' => 'v1.23.1@9174a3d80210dca8daa7f31fec659150bbeabfc6',
  'symfony/polyfill-php73' => 'v1.23.0@fba8933c384d6476ab14fb7b8526e5287ca7e010',
  'symfony/polyfill-php80' => 'v1.23.1@1100343ed1a92e3a38f9ae122fc0eb21602547be',
  'symfony/polyfill-php81' => 'v1.23.0@e66119f3de95efc359483f810c4c3e6436279436',
  'symfony/service-contracts' => 'v2.4.0@f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb',
  'symfony/var-exporter' => 'v5.3.7@2ded877ab0574d8b646f4eb3f716f8ed7ee7f392',
  'twig/twig' => 'v3.3.2@21578f00e83d4a82ecfa3d50752b609f13de6790',
  'williamdes/mariadb-mysql-kbs' => '1.2.12@b5d4b498ba3d24ab7ad7dd0b79384542e37286a1',
  'amphp/amp' => 'v2.6.0@caa95edeb1ca1bf7532e9118ede4a3c3126408cc',
  'amphp/byte-stream' => 'v1.8.1@acbd8002b3536485c997c4e019206b3f10ca15bd',
  'bacon/bacon-qr-code' => '2.0.4@f73543ac4e1def05f1a70bcd1525c8a157a1ad09',
  'composer/package-versions-deprecated' => '1.11.99.3@fff576ac850c045158a250e7e27666e146e78d18',
  'composer/semver' => '3.2.5@31f3ea725711245195f62e54ffa402d8ef2fdba9',
  'composer/xdebug-handler' => '2.0.2@84674dd3a7575ba617f5a76d7e9e29a7d3891339',
  'dasprid/enum' => '1.0.3@5abf82f213618696dda8e3bf6f64dd042d8542b2',
  'dealerdirect/phpcodesniffer-composer-installer' => 'v0.7.1@fe390591e0241955f22eb9ba327d137e501c771c',
  'dnoegel/php-xdg-base-dir' => 'v0.1.1@8f8a6e48c5ecb0f991c2fdcf5f154a47d85f9ffd',
  'doctrine/coding-standard' => '8.2.1@f595b060799c1a0d76ead16981804eaa0bbcd8d6',
  'doctrine/instantiator' => '1.4.0@d56bf6102915de5702778fe20f2de3b2fe570b5b',
  'felixfbecker/advanced-json-rpc' => 'v3.2.1@b5f37dbff9a8ad360ca341f3240dc1c168b45447',
  'felixfbecker/language-server-protocol' => '1.5.1@9d846d1f5cf101deee7a61c8ba7caa0a975cd730',
  'myclabs/deep-copy' => '1.10.2@776f831124e9c62e1a2c601ecc52e776d8bb7220',
  'netresearch/jsonmapper' => 'v4.0.0@8bbc021a8edb2e4a7ea2f8ad4fa9ec9dce2fcb8d',
  'nikic/php-parser' => 'v4.12.0@6608f01670c3cc5079e18c1dab1104e002579143',
  'openlss/lib-array2xml' => '1.0.0@a91f18a8dfc69ffabe5f9b068bc39bb202c81d90',
  'paragonie/constant_time_encoding' => 'v2.4.0@f34c2b11eb9d2c9318e13540a1dbc2a3afbd939c',
  'phar-io/manifest' => '2.0.3@97803eca37d319dfa7826cc2437fc020857acb53',
  'phar-io/version' => '3.1.0@bae7c545bef187884426f042434e561ab1ddb182',
  'php-webdriver/webdriver' => '1.11.1@da16e39968f8dd5cfb7d07eef91dc2b731c69880',
  'phpdocumentor/reflection-common' => '2.2.0@1d01c49d4ed62f25aa84a747ad35d5a16924662b',
  'phpdocumentor/reflection-docblock' => '5.2.2@069a785b2141f5bcf49f3e353548dc1cce6df556',
  'phpdocumentor/type-resolver' => '1.4.0@6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0',
  'phpmyadmin/coding-standard' => '2.1.1@28b0eb2f8a902f29affab157cc118136086587c3',
  'phpspec/prophecy' => '1.14.0@d86dfc2e2a3cd366cee475e52c6bb3bbc371aa0e',
  'phpstan/extension-installer' => '1.1.0@66c7adc9dfa38b6b5838a9fb728b68a7d8348051',
  'phpstan/phpdoc-parser' => '0.4.9@98a088b17966bdf6ee25c8a4b634df313d8aa531',
  'phpstan/phpstan' => '0.12.98@3bb7cc246c057405dd5e290c3ecc62ab51d57e00',
  'phpstan/phpstan-phpunit' => '0.12.22@7c01ef93bf128b4ac8bdad38c54b2a4fd6b0b3cc',
  'phpunit/php-code-coverage' => '9.2.6@f6293e1b30a2354e8428e004689671b83871edde',
  'phpunit/php-file-iterator' => '3.0.5@aa4be8575f26070b100fccb67faabb28f21f66f8',
  'phpunit/php-invoker' => '3.1.1@5a10147d0aaf65b58940a0b72f71c9ac0423cc67',
  'phpunit/php-text-template' => '2.0.4@5da5f67fc95621df9ff4c4e5a84d6a8a2acf7c28',
  'phpunit/php-timer' => '5.0.3@5a63ce20ed1b5bf577850e2c4e87f4aa902afbd2',
  'phpunit/phpunit' => '9.5.9@ea8c2dfb1065eb35a79b3681eee6e6fb0a6f273b',
  'pragmarx/google2fa' => '8.0.0@26c4c5cf30a2844ba121760fd7301f8ad240100b',
  'pragmarx/google2fa-qrcode' => 'v1.0.3@fd5ff0531a48b193a659309cc5fb882c14dbd03f',
  'samyoul/u2f-php-server' => 'v1.1.4@0625202c79d570e58525ed6c4ae38500ea3f0883',
  'sebastian/cli-parser' => '1.0.1@442e7c7e687e42adc03470c7b668bc4b2402c0b2',
  'sebastian/code-unit' => '1.0.8@1fc9f64c0927627ef78ba436c9b17d967e68e120',
  'sebastian/code-unit-reverse-lookup' => '2.0.3@ac91f01ccec49fb77bdc6fd1e548bc70f7faa3e5',
  'sebastian/comparator' => '4.0.6@55f4261989e546dc112258c7a75935a81a7ce382',
  'sebastian/complexity' => '2.0.2@739b35e53379900cc9ac327b2147867b8b6efd88',
  'sebastian/diff' => '4.0.4@3461e3fccc7cfdfc2720be910d3bd73c69be590d',
  'sebastian/environment' => '5.1.3@388b6ced16caa751030f6a69e588299fa09200ac',
  'sebastian/exporter' => '4.0.3@d89cc98761b8cb5a1a235a6b703ae50d34080e65',
  'sebastian/global-state' => '5.0.3@23bd5951f7ff26f12d4e3242864df3e08dec4e49',
  'sebastian/lines-of-code' => '1.0.3@c1c2e997aa3146983ed888ad08b15470a2e22ecc',
  'sebastian/object-enumerator' => '4.0.4@5c9eeac41b290a3712d88851518825ad78f45c71',
  'sebastian/object-reflector' => '2.0.4@b4f479ebdbf63ac605d183ece17d8d7fe49c15c7',
  'sebastian/recursion-context' => '4.0.4@cd9d8cf3c5804de4341c283ed787f099f5506172',
  'sebastian/resource-operations' => '3.0.3@0f4443cb3a1d92ce809899753bc0d5d5a8dd19a8',
  'sebastian/type' => '2.3.4@b8cd8a1c753c90bc1a0f5372170e3e489136f914',
  'sebastian/version' => '3.0.2@c6c1022351a901512170118436c764e473f6de8c',
  'slevomat/coding-standard' => '6.4.1@696dcca217d0c9da2c40d02731526c1e25b65346',
  'squizlabs/php_codesniffer' => '3.6.0@ffced0d2c8fa8e6cdc4d695a743271fab6c38625',
  'symfony/console' => 'v4.4.30@a3f7189a0665ee33b50e9e228c46f50f5acbed22',
  'symfony/finder' => 'v4.4.30@70362f1e112280d75b30087c7598b837c1b468b6',
  'symfony/process' => 'v5.3.7@38f26c7d6ed535217ea393e05634cb0b244a1967',
  'symfony/translation-contracts' => 'v2.4.0@95c812666f3e91db75385749fe219c5e494c7f95',
  'symfony/twig-bridge' => 'v4.4.27@533c37d310d59ab84df8ca6815a581f92e7ffcf6',
  'tecnickcom/tcpdf' => '6.4.2@172540dcbfdf8dc983bc2fe78feff48ff7ec1c76',
  'theseer/tokenizer' => '1.2.1@34a41e998c2183e22995f158c581e7b5e755ab9e',
  'vimeo/psalm' => '4.10.0@916b098b008f6de4543892b1e0651c1c3b92cbfa',
  'webmozart/assert' => '1.10.0@6964c76c7804814a842473e0c8fd15bab0f18e25',
  'webmozart/path-util' => '2.3.0@d939f7edc24c9a1bb9c0dee5cb05d8e859490725',
  'phpmyadmin/phpmyadmin' => '5.2.x-dev@f670e2c60210ad407501a49a968fc34060cc22d3',
);

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!self::composer2ApiUsable()) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName): string
    {
        if (self::composer2ApiUsable()) {
            return InstalledVersions::getPrettyVersion($packageName)
                . '@' . InstalledVersions::getReference($packageName);
        }

        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }

    private static function composer2ApiUsable(): bool
    {
        if (!class_exists(InstalledVersions::class, false)) {
            return false;
        }

        if (method_exists(InstalledVersions::class, 'getAllRawData')) {
            $rawData = InstalledVersions::getAllRawData();
            if (count($rawData) === 1 && count($rawData[0]) === 0) {
                return false;
            }
        } else {
            $rawData = InstalledVersions::getRawData();
            if ($rawData === []) {
                return false;
            }
        }

        return true;
    }
}
