<?php
namespace Smf\Menu\Control;


use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\RendererInterface;

use Nette\Application\UI\Control;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Reflection\ClassType;

class MenuControl extends Control
{
    /** @var FactoryInterface */
    private $menuFactory;

    /** @var ItemInterface */
    private $root;

    /** @var string */
    private $defaultRenderer;

    /**
     * List of registered renderers
     * @var array
     */
    private $renderers = array();

    function __construct(FactoryInterface $menuFactory, array $renderers = array())
    {
        $this->menuFactory = $menuFactory;
        foreach ($renderers as $name => $renderer) {
            $this->registerRenderer($name, $renderer);
        }
    }


    /**
     * Registers renderer under given name. It is possible to register class name or instance of
     * RendererInterface class
     *
     * @param $name Name of the renderer
     * @param $renderer string|RendererInterface renderer class or instance
     * @throws InvalidArgumentException
     */
    public function registerRenderer($name, $renderer, $rewrite = false)
    {
        if (isset($this->renderers[$name]) && !$rewrite) {
            throw new InvalidArgumentException("Renderer with name '$name' is already registered.");
        }
        unset($this->renderers[$name]);
        // Is class? Exists?
        if (is_string($renderer) && !class_exists($renderer)) {
            throw new InvalidArgumentException("Renderer class '$renderer' doesn't exist.");
        } elseif (method_exists($renderer, 'setParentControl')) {
            $renderer->setParentControl($this);
        }

        $this->renderers[$name] = $renderer;
    }

    /**
     * Returns instace of renderer by given name
     *
     * @param $name Name of the renderer
     * @return RendererInterface
     * @throws InvalidArgumentException
     */
    public final function getRenderer($name)
    {
        if (!isset($this->renderers[$name])) {
            throw new InvalidArgumentException("Renderer with name '$name' doesn't exist.");
        }
        if (is_string($this->renderers[$name])) {
            $renderer = new $this->renderers[$name]();
            $reflection = ClassType::from($renderer);
            if (!$reflection->isSubclassOf('Knp\Menu\Renderer\RendererInterface')) {
                throw new InvalidArgumentException("Renderer class '{$this->renderers[$name]}' is not subclass of Knp\\Menu\\Renderer\\RendererInterface");
            }
            $this->registerRenderer($name, $renderer, true);
        }
        return $this->renderers[$name];
    }

    /**
     * Removes renderer by its name
     *
     * @param $name Name of the renderer
     * @throws InvalidArgumentException
     */
    public function removeRenderer($name)
    {
        if (!isset($this->renderers[$name])) {
            throw new InvalidArgumentException("There is not registered renderer under the '$name name");
        }
        unset($this->renderers[$name]);
    }

    /**
     * Returns root item
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getRoot()
    {
        if ($this->root === null) {
            $this->root = $this->menuFactory->createItem($this->getName());
        }
        return $this->root;
    }

    public function renderMenu($renderer, array $options = array())
    {
        echo $this->getRenderer($renderer)->render($this->getRoot(), $options);
    }

    /**
     * Renders menu with default renderer
     */
    public function render()
    {
        if (empty($this->renderers)) {
            throw new InvalidStateException("There is no renderer.");
        }
        // Get default or first renderer
        reset($this->renderers);
        $name = $this->defaultRenderer ?: (key($this->renderers));
        $this->renderMenu($name);
    }

    public function __call($name, $args)
    {
        if (strpos($name, 'render') === 0) {
            return $this->renderMenu(lcfirst(substr($name, 6)));
        }
    }

    /**
     * Sets the default renderer
     * @param $name
     */
    public function setDefaultRenderer($name)
    {
        $this->defaultRenderer = $name;
    }
}