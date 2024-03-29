<?php

// autoload_real.php @generated by Composer

use Composer\Autoload\ClassLoader;
use Composer\Autoload\ComposerStaticInitb6044ea3093411a9442f0882dc811449;

class ComposerAutoloaderInitb6044ea3093411a9442f0882dc811449
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(['ComposerAutoloaderInitb6044ea3093411a9442f0882dc811449', 'loadClassLoader'], true, true);
        self::$loader = $loader = new ClassLoader();
        spl_autoload_unregister(['ComposerAutoloaderInitb6044ea3093411a9442f0882dc811449', 'loadClassLoader']);

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            require_once __DIR__ . '/autoload_static.php';

            call_user_func(ComposerStaticInitb6044ea3093411a9442f0882dc811449::getInitializer($loader));
        } else {
            $map = require __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $map = require __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }

            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->register(true);

        return $loader;
    }
}
