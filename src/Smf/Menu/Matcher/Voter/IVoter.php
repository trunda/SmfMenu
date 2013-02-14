<?php
/**
 * Created by JetBrains PhpStorm.
 * User: trunda
 * Date: 14.02.13
 * Time: 14:59
 * To change this template use File | Settings | File Templates.
 */

namespace Smf\Menu\Matcher\Voter;


use Knp\Menu\Matcher\Voter\VoterInterface;
use Nette\Application\UI\Control;

interface IVoter extends VoterInterface
{
    /**
     * @param \Nette\Application\UI\Control $control
     * @return void
     */
    public function setParentControl(Control $control = null);
}