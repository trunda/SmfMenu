<?php
namespace Smf\Menu\Matcher;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Nette\Application\UI\Control;

class Matcher implements IMatcher
{
    /** @var Control */
    protected $parentControl;

    private $cache;

    /**
     * @var VoterInterface[]
     */
    private $voters = array();

    public function __construct()
    {
        $this->cache = new \SplObjectStorage();
    }

    /**
     * Adds a voter in the matcher.
     *
     * @param VoterInterface $voter
     */
    public function addVoter(Voter\IVoter $voter)
    {
        $this->voters[] = $voter;
    }

    public function isCurrent(ItemInterface $item)
    {
        $current = $item->isCurrent();
        if (null !== $current) {
            return $current;
        }

        if ($this->cache->contains($item)) {
            return $this->cache[$item];
        }

        foreach ($this->voters as $voter) {
            $current = $voter->matchItem($item);
            if (null !== $current) {
                break;
            }
        }

        $current = (boolean) $current;
        $this->cache[$item] = $current;

        return $current;
    }

    public function isAncestor(ItemInterface $item, $depth = null)
    {
        if (0 === $depth) {
            return false;
        }

        $childDepth = null === $depth ? null : $depth - 1;
        foreach ($item->getChildren() as $child) {
            if ($this->isCurrent($child) || $this->isAncestor($child, $childDepth)) {
                return true;
            }
        }

        return false;
    }

    public function clear()
    {
        $this->cache = new \SplObjectStorage();
    }

    /**
     * @param Control $parentControl
     */
    public function setParentControl(Control $parentControl = null)
    {
        $this->parentControl = $parentControl;
        foreach ($this->voters as $voter) {
            $voter->setParentControl($parentControl);
        }
    }


}