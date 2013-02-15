<?php

namespace Smf\Menu\Renderer;


use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\Renderer;
use Nette\Application\UI\Control;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Smf\Menu\Matcher\IMatcher;

/**
 * Renders basic <UL><LI> list menu.
 */
class ListRenderer extends Renderer implements IRenderer
{
    /** @var IMatcher */
    protected $matcher;
    /** @var array */
    protected $defaultOptions;
    /** @var Control */
    protected $parentControl;

    /**
     * @param IMatcher $matcher
     * @param array $defaultOptions
     */
    public function __construct(IMatcher $matcher, array $defaultOptions = array(), $charset = null)
    {
        $this->matcher = $matcher;
		$this->setDefaults($defaultOptions);

		parent::__construct($charset);
    }

	/**
	 * @param array $defaultOptions
	 */
	protected function setDefaults(array $defaultOptions = array())
	{
        $this->defaultOptions = array_merge(array(
			// rendering depth
            'depth' => null,
			// ancestor currency check depth
			// to set it current, when it's child is active, but not displayed)
			'ancestorCurrencyDepth' => null,
            'currentAsLink' => true,
            'currentClass' => 'current',
            'ancestorClass' => 'current_ancestor',
            'firstClass' => 'first',
            'lastClass' => 'last',
            'allow_safe_labels' => false,
            'clear_matcher' => true,
        ), $defaultOptions);
    }

    /**
     * Renders menu tree.
     *
     * Common options:
     *      - depth: The depth at which the item is rendered
     *          null: no limit
     *          0: no children
     *          1: only direct children
     *      - currentAsLink: whether the current item should be a link
     *      - currentClass: class added to the current item
     *      - ancestorClass: class added to the ancestors of the current item
     *      - firstClass: class added to the first child
     *      - lastClass: class added to the last child
     *
     * @param ItemInterface $item    Menu item
     * @param array         $options some rendering options
     *
     * @return string
     */
    public function render(ItemInterface $item, array $options = array())
    {
        $options = array_merge($this->defaultOptions, $options);
        $options['rootLevel'] = $item->getLevel();
        return $this->getMenu($item, $options) ?: '';
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|null
     */
    protected function getMenu(ItemInterface $item, array $options)
    {
        $list = $this->getList($item, $item->getChildrenAttributes(), $options);
        if ($options['clear_matcher']) {
            $this->matcher->clear();
        }
        return $list;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param $attributes
     * @param $options
     * @return \Nette\Utils\Html|null
     */
    protected function getList(ItemInterface $item, $attributes, $options)
    {
        if (!$item->hasChildren() || 0 === $options['depth'] || !$item->getDisplayChildren()) {
            return null;
        }

        $list = Html::el('ul', $attributes);
        foreach ($this->getChildren($item, $options) as $child) {
            $list->add($child);
        }
        return $list;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return array
     */
    protected function getChildren(ItemInterface $item, array $options)
    {
        if (null !== $options['depth']) {
            $options['depth'] = max(0, $options['depth'] - 1);
        }

        if (null !== $options['ancestorCurrencyDepth']) {
            $options['ancestorCurrencyDepth'] = max(0, $options['ancestorCurrencyDepth'] - 1);
        }

        $items = array();
        foreach ($item->getChildren() as $child) {
            if (($item = $this->getItem($child, $options)) !== null) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|null
     */
    protected function getItem(ItemInterface $item, array $options)
    {
        if (!$item->isDisplayed()) {
            return null;
        }

        // create an array than can be imploded as a class list
        $class = (array) $item->getAttribute('class');

        if ($this->matcher->isCurrent($item)) {
            $class[] = $options['currentClass'];
        } elseif ($this->matcher->isAncestor($item, $options['ancestorCurrencyDepth'])) {
            $class[] = $options['ancestorClass'];
        }

        if ($item->actsLikeFirst()) {
            $class[] = $options['firstClass'];
        }
        if ($item->actsLikeLast()) {
            $class[] = $options['lastClass'];
        }

        // retrieve the attributes and put the final class string back on it
        $attributes = $item->getAttributes();
        if (!empty($class)) {
            $attributes['class'] = $class;
        }

        $li = Html::el('li', $attributes);

        if (($link = $this->getLink($item, $options)) !== null) {
            $li->add($link);
        }

        // renders the embedded ul
        $childrenClass = (array) $item->getChildrenAttribute('class');
        $childrenClass[] = 'menu_level_'.$item->getLevel();

        $childrenAttributes = $item->getChildrenAttributes();
        $childrenAttributes['class'] = $childrenClass;

        if (($children = $this->getList($item, $childrenAttributes, $options)) !== null) {
            $li->add($children);
        }
        return $li;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return null|string
     */
    protected function getUri(ItemInterface $item, array $options)
    {
        if ($item->getUri()) {
            return $item->getUri();
        } elseif ($item->getExtra('link', false) && $this->parentControl) {
            $presenter = $this->parentControl->getPresenter(true);
            return call_user_func_array(array($presenter, 'link'), $item->getExtra('link'));
        }
        return null;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|null
     */
    protected function getLink(ItemInterface $item, array $options)
    {
        if (!$this->getUri($item, $options) && !$item->getLabel()) {
            return null;
        }
        if ($this->getUri($item, $options)
            && (!$item->isCurrent() || $options['currentAsLink'])
            && (!$this->matcher->isCurrent($item) || $options['currentAsLink'])) {
            return $this->getLinkElement($item, $options);
        } else {
            return $this->getSpanElement($item, $options);
        }
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return Html
     */
    protected function getSpanElement(ItemInterface $item, array $options)
    {
        return Html::el('span', $item->getLabelAttributes())
            ->setHtml($this->getText($item, $options));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return Html
     */
    protected function getLinkElement(ItemInterface $item, array $options)
    {
        return Html::el('a', $item->getLinkAttributes())
            ->setHref($this->getUri($item, $options))
            ->setHtml($this->getText($item, $options));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return string
     */
    protected function getText(ItemInterface $item, array $options)
    {
        if ($options['allow_safe_labels'] && $item->getExtra('safe_label', false)) {
            return $item->getLabel();
        }
        return $this->escape($item->getLabel());
    }


    /**
     * Sets the parent control - this is important for link generation
     * @param Control $parentControl
     */
    public function setParentControl(Control $parentControl = null)
    {
        $this->parentControl = $parentControl;
        $this->matcher->setParentControl($parentControl);
    }

    protected function getRealLevel(ItemInterface $item, array $options)
    {
        return $item->getLevel() - $options['rootLevel'];
    }
}