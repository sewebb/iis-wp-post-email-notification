<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4120a8baaf6881fd2a387a4f0ea79ec6
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Component\\Templating\\' => 29,
            'Symfony\\Component\\HttpFoundation\\' => 33,
        ),
        'N' => 
        array (
            'Nstaeger\\WpPostEmailNotification\\' => 33,
            'Nstaeger\\CmsPluginFramework\\' => 28,
        ),
        'I' => 
        array (
            'Illuminate\\Contracts\\' => 21,
            'Illuminate\\Container\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Component\\Templating\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/templating',
        ),
        'Symfony\\Component\\HttpFoundation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/http-foundation',
        ),
        'Nstaeger\\WpPostEmailNotification\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Nstaeger\\CmsPluginFramework\\' => 
        array (
            0 => __DIR__ . '/..' . '/nstaeger/cms-plugin-framework/src',
        ),
        'Illuminate\\Contracts\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/contracts',
        ),
        'Illuminate\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/container',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4120a8baaf6881fd2a387a4f0ea79ec6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4120a8baaf6881fd2a387a4f0ea79ec6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
