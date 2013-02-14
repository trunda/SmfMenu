<?php
namespace Smf\Menu\Control;


use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\RendererInterface;

use Nette\Application\UI\Control;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Reflection\ClassType;
use Smf\Menu\Renderer\IManager;

class MenuControl extends Control
{
    /** @var FactoryInterface */
    private $menuFactory;

    /** @var ItemInterface */
    private $root;

    /** @var IManager */
    private $rendererManager;

    /**
     * List of registered renderers
     * @var array
     */
    private $renderers = array();

    function __construct(FactoryInterface $menuFactory, IManager $rendererManager)
    {
        $this->menuFactory = $menuFactory;
        $this->rendererManager = $rendererManager;
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
        $menu = $this->getRoot();
        if (isset($options['path'])) {
            $menu = $this->getMenuByPath($options['path']);
            unset($options['path']);
        }

        $renderer = $this->rendererManager->getRenderer($renderer);

        $renderer->setParentControl($this);
        $result = $renderer->render($menu, $options);
        $renderer->setParentControl(null);

        echo $result;
    }

    protected function getMenuByPath($path)
    {
        $path = explode('-', $path);
        $menu = $this->getRoot();
        foreach ($path as $name) {
            $menu = $menu->getChild($name);
            if ($menu === null) {
                throw new InvalidArgumentException("There is no menu with name '$name'");
            }
        }
        return $menu;
    }

    /**
     * Renders menu with default renderer
     */
    public function render(array $options = array())
    {
        $this->renderMenu(null, $options);
    }

    public function __call($name, $args)
    {
        if (strpos($name, 'render') === 0) {
            $options = array();
            if (count($args) === 1 && is_array($args[0])) {
                $options = $args[0];
            } elseif (count($args) !== 0) {
                throw new InvalidArgumentException('Render method expects one optional parameter and it should be an array.');
            }
            return $this->renderMenu(lcfirst(substr($name, 6)), $options);
        }
    }

}