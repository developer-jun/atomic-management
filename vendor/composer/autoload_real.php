<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitabae80a1cb8d8aeeae49572b260f2d9c
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitabae80a1cb8d8aeeae49572b260f2d9c', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitabae80a1cb8d8aeeae49572b260f2d9c', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitabae80a1cb8d8aeeae49572b260f2d9c::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
