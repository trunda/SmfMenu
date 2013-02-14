<?php

namespace Smf\Menu\Control;

use Knp\Menu\FactoryInterface;

class Factory {

    /** @var FactoryInterface */
    private $menuFactory;

    /** @var array */
    private $renderers = array();

    /** @var string */
    private $defaultRenderer;

    /**
     * @param \Knp\Menu\FactoryInterface $menuFactory
     */
    function __construct(FactoryInterface $menuFactory)
    {
        $this->menuFactory = $menuFactory;
    }

    /**
     * @param string $defaultRenderer
     */
    public function setDefaultRenderer($defaultRenderer)
    {
        $this->defaultRenderer = $defaultRenderer;
    }

    /**
     * @return MenuControl
     */
    public function createControl()
    {
        $control = new MenuControl($this->menuFactory, $this->renderers);
        $control->setDefaultRenderer($this->defaultRenderer);
        return $control;
    }

    /**
     * @param $name string Name of the renderer
     * @param $renderer string|RendererInterface renderer class or instance
     */
    public function addRenderer($name, $renderer)
    {
        $this->renderers[$name] = $renderer;
    }

}