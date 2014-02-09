<?php

namespace Saxulum\BundleProvider\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

abstract class AbstractBundleProvider implements ServiceProviderInterface
{
    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @param Application $app
     */
    protected function addCommands(Application $app)
    {
        $app['console.command.paths'] = $app->share($app->extend('console.command.paths', function ($paths) {
            $paths[] = $this->getPath() . '/Command';

            return $paths;
        }));
    }

    /**
     * @param Application $app
     */
    protected function addControllers(Application $app)
    {
        $app['route_controller_paths'] = $app->share($app->extend('route_controller_paths', function ($paths) {
            $paths[] = $this->getPath() . '/Controller';

            return $paths;
        }));
    }

    /**
     * @param Application $app
     */
    protected function addDoctrineOrmMappings(Application $app)
    {
        $namespace = $this->getNamespace();
        $path = $this->getPath();

        if (!isset($app['orm.ems.options'])) {
            $app['orm.ems.options'] = $app->share(function () use ($app) {
                $options = array(
                    'default' => $app['orm.em.default_options']
                );

                return $options;
            });
        }

        $app['orm.ems.options'] = $app->share($app->extend('orm.ems.options', function (array $options) use ($namespace, $path) {
            $options['default']['mappings'][] = array(
                'type' => 'annotation',
                'namespace' => $namespace . '\Entity',
                'path' => $path .'/Entity',
                'use_simple_annotation_reader' => false,
            );

            return $options;
        }));
    }

    /**
     * @param Application $app
     */
    protected function addTranslatorRessources(Application $app)
    {
        $app['translation_paths'] = $app->share($app->extend('translation_paths', function ($paths) {
            $paths[] = $this->getPath() . '/Resources/translations';

            return $paths;
        }));
    }

    /**
     * @param Application $app
     */
    protected function addTwigLoaderFilesystemPath(Application $app)
    {
        $path = $this->getPath();
        $twigNamespace = str_replace('\\', '', $this->getNamespace());

        $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem',
            function (\Twig_Loader_Filesystem $twigLoaderFilesystem) use ($path, $twigNamespace) {
                $twigLoaderFilesystem->addPath($path. '/Resources/views', $twigNamespace);

                return $twigLoaderFilesystem;
            }
        ));
    }

    /**
     * @return string
     */
    protected function getNamespace()
    {
        return $this->getReflectionClass()->getNamespaceName();
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return dirname($this->getReflectionClass()->getFileName());
    }

    /**
     * @return \ReflectionClass
     */
    protected function getReflectionClass()
    {
        if (is_null($this->reflectionClass)) {
            $this->reflectionClass = new \ReflectionClass(get_class($this));
        }

        return $this->reflectionClass;
    }
}
