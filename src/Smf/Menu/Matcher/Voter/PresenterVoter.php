<?php
/**
 * Created by JetBrains PhpStorm.
 * User: trunda
 * Date: 29.01.13
 * Time: 21:21
 * To change this template use File | Settings | File Templates.
 */

namespace Smf\Menu\Matcher\Voter;


use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Nette\Application\Request;
use Nette\Application\UI\Control;

class PresenterVoter implements IVoter
{
    /** @var Control */
    protected $parentControl;

    /**
     * @param Control $parentControl
     */
    public function setParentControl(Control $parentControl = null)
    {
        $this->parentControl = $parentControl;
    }

    /**
     * Checks whether an item is current.
     *
     * If the voter is not able to determine a result,
     * it should return null to let other voters do the job.
     *
     * @param ItemInterface $item
     *
     * @return boolean|null
     */
    public function matchItem(ItemInterface $item)
    {
        if ($item->getExtra('link', false) && $this->parentControl) {
            return $this->parentControl->getPresenter()->isLinkCurrent($item->getExtra('link'));
        }
        return null;
    }
}