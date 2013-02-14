<?php

namespace Smf\Menu\Control;

use Knp\Menu\FactoryInterface;
use Smf\Menu\Renderer\IManager;

class Factory {

    /** @var FactoryInterface */
    private $menuFactory;

    /** @var IManager */
    private $rendererManager;

    /**
     * @param \Knp\Menu\FactoryInterface $menuFactory
     * @param \Smf\Menu\Renderer\IManager $rendererManager
     */
    function __construct(FactoryInterface $menuFactory, IManager $rendererManager)
    {
        $this->menuFactory = $menuFactory;
        $this->rendererManager = $rendererManager;
    }


    /**
     * @return MenuControl
     */
    public function createControl()
    {
        $control = new MenuControl($this->menuFactory, $this->rendererManager);
        return $control;
    }

}