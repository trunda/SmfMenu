<?php
/**
 * Created by JetBrains PhpStorm.
 * User: trunda
 * Date: 14.02.13
 * Time: 14:57
 * To change this template use File | Settings | File Templates.
 */

namespace Smf\Menu\Matcher;


use Knp\Menu\Matcher\MatcherInterface;
use Nette\Application\UI\Control;

interface IMatcher extends MatcherInterface{
    /**
     * @param \Nette\Application\UI\Control $control
     * @return void
     */
    public function setParentControl(Control $control = null);
}