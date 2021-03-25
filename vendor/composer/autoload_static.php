<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite3ecdb0398eeab1f572fcce929add428
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite3ecdb0398eeab1f572fcce929add428::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite3ecdb0398eeab1f572fcce929add428::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite3ecdb0398eeab1f572fcce929add428::$classMap;

        }, null, ClassLoader::class);
    }
}