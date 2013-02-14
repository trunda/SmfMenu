<?php
namespace Smf\Menu\Config;

use Nette\Application\Application;
use Nette\Config\Compiler;
use Nette\Config\CompilerExtension;
use Nette\Config\Configurator;
use Nette\DI\Container;
use Smf\Menu\Renderer\IManager;

/**
 * Menu extension
 */
class Extension extends CompilerExtension {

    const DEFAULT_EXTENSION_NAME = 'smfMenu',
            RENDERER_TAG_NAME = 'menuRenderer',
            VOTER_TAG_NAME = 'menuVoter';

    public $defaults = array(
        'defaultRenderer' => 'list',
    );

    /**
     * Configuration - container building
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // Create instance of menufactory
        $menuFactory = $builder->addDefinition($this->prefix('factory'))
            ->setClass('Smf\Menu\MenuFactory');

        // Create renderers manager
        $rendererManager = $builder->addDefinition($this->prefix('rendererManager'))
            ->setClass('Smf\Menu\Renderer\Manager')
            ->addSetup(get_called_class() . '::setupRenderers', array('@self', '@container'))
            ->addSetup('setDefaultRenderer', array($config['defaultRenderer']));;

        // Create a factory for control
        $builder->addDefinition($this->prefix('controlFactory'))
            ->setClass('Smf\Menu\Control\Factory', array($menuFactory, $rendererManager));

        // Matcher
        $matcher = $builder->addDefinition($this->prefix('matcher'))
            ->addSetup(get_called_class() . '::setupVoters', array('@self', '@container'))
            ->setClass('Smf\Menu\Matcher\Matcher');

        // Create a default renderers
        $renderers = array(
            'Smf\Menu\Renderer\ListRenderer',
            'Smf\Menu\Renderer\BootstrapNavRenderer',
        );
        foreach ($renderers as $renderer) {
            $name = explode('\\', $renderer);
            $name = lcfirst(preg_replace('#Renderer$#', '', end($name)));
            $builder->addDefinition($this->prefix('renderer_' . $name))
                ->setClass($renderer, array($matcher))
                ->addTag(self::RENDERER_TAG_NAME, $name);
        }

        // Create default voter
        $builder->addDefinition($this->prefix('presenterVoter'))
            ->setClass('Smf\Menu\Matcher\Voter\PresenterVoter')
            ->addTag(self::VOTER_TAG_NAME);
    }

    /**
     * @param $manager IManager
     * @param $container Container
     */
    public static function setupRenderers(IManager $manager, Container $container)
    {
        foreach ($container->findByTag(static::RENDERER_TAG_NAME) as $name => $value) {
            $manager->addRenderer($value, $container->getService($name));
        }
    }

    /**
     * @param mixed $voterConsumer
     * @param Container $container
     */
    public static function setupVoters($voterConsumer, Container $container)
    {
        foreach ($container->findByTag(static::VOTER_TAG_NAME) as $name => $value) {
            $voterConsumer->addVoter($container->getService($name));
        }
    }

    /**
     * Register extension to compiler.
     *
     * @param Configurator $configurator
     * @param string $name
     */
    public static function register(Configurator $configurator, $name = self::DEFAULT_EXTENSION_NAME)
    {
        $class = get_called_class();
        $configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) use ($class, $name) {
            $compiler->addExtension($name, new $class);
        };
    }
}