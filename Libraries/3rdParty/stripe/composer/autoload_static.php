<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6d9d22676f4bdeeb465c9a109badfb75
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6d9d22676f4bdeeb465c9a109badfb75::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6d9d22676f4bdeeb465c9a109badfb75::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
