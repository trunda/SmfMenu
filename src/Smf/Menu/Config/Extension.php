<?php
namespace Smf\Menu\Config;

use Nette;
use Nette\Application\Application;
use Nette\DI\Container;
use Smf\Menu\Renderer\IManager;


if (!class_exists('Nette\DI\CompilerExtension')) {
    class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
    class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
    class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
    unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']); // fuck you
    class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * Menu extension
 */
class Extension extends Nette\DI\CompilerExtension {

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

        // Create instance of extension
        $menuFactory = $builder->addDefinition($this->prefix('extension'))
            ->setClass('Smf\Menu\MenuExtension');

        // Create instance of menufactory
        $menuFactory = $builder->addDefinition($this->prefix('factory'))
            ->addSetup('addExtension', $this->prefix('@extension'))
            ->setClass('Knp\Menu\MenuFactory');

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
    public static function register(Nette\Configurator $configurator, $name = self::DEFAULT_EXTENSION_NAME)
    {
        $class = get_called_class();
        $configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) use ($class, $name) {
            $compiler->addExtension($name, new $class);
        };
    }
}