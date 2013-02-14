<?php
/**
 * Created by JetBrains PhpStorm.
 * User: trunda
 * Date: 14.02.13
 * Time: 14:46
 * To change this template use File | Settings | File Templates.
 */

namespace Smf\Menu\Renderer;


use Knp\Menu\Renderer\RendererInterface;
use Nette\Application\UI\Control;

interface IRenderer extends RendererInterface {
    /**
     * @param \Nette\Application\UI\Control $control
     * @return void
     */
    public function setParentControl(Control $control = null);
}