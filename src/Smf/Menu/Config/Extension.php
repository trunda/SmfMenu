<?php
namespace Smf\Menu\Config;

use Nette\Application\Application;
use Nette\Config\Compiler;
use Nette\Config\CompilerExtension;
use Nette\Config\Configurator;
use Nette\DI\Container;
use Smf\Menu\Control\MenuControl;

/**
 * Menu extension
 */
class Extension extends CompilerExtension {

    const DEFAULT_EXTENSION_NAME = 'menu',
            RENDERER_TAG_NAME = 'menuRenderer',
            VOTER_TAG_NAME = 'menuVoter';

    /**
     * Configuration - container building
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        // Create instance of menufactory
        $menuFactory = $builder->addDefinition($this->prefix('factory'))
            ->setClass('Smf\Menu\MenuFactory');

        // Create a factory for control
        $builder->addDefinition($this->prefix('control'))
            ->setClass('Smf\Menu\Control\MenuControl', array($menuFactory))
            ->setParameters(array('defaultRenderer' => null))
            ->addSetup('setDefaultRenderer', array('%defaultRenderer%'))
            ->addSetup(get_called_class() . '::setupRenderers', array('@self', '@container'))
            ->setShared(false);

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
     * @param $menuControl  MenuControl
     * @param $container    \Nette\DI\Container
     */
    public static function setupRenderers(MenuControl $menuControl, Container $container)
    {
        foreach ($container->findByTag(static::RENDERER_TAG_NAME) as $name => $value) {
            $menuControl->registerRenderer($value, $container->getService($name));
        }
    }

    /**
     * @param mixed
     * @param \Nette\DI\Container
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
     * @param \Nette\Config\Configurator
     * @param string
     */
    public static function register(\Nette\Config\Configurator $configurator, $name = self::DEFAULT_EXTENSION_NAME)
    {
        $class = get_called_class();
        $configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) use ($class, $name) {
            $compiler->addExtension($name, new $class);
        };
    }
}