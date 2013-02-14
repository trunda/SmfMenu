<?php
/**
 * Created by JetBrains PhpStorm.
 * User: trunda
 * Date: 29.01.13
 * Time: 16:02
 * To change this template use File | Settings | File Templates.
 */

namespace Smf\Menu\Renderer;


use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Renderer\RendererInterface;
use Nette\Application\UI\Control;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class ListRenderer implements RendererInterface
{
    /** @var \Smf\Menu\Renderer\MatcherInterface */
    protected $matcher;
    /** @var array */
    protected $defaultOptions;
    /** @var Control */
    protected $parentControl;

    /**
     * @param MatcherInterface $matcher
     * @param array            $defaultOptions
     */
    public function __construct(MatcherInterface $matcher, array $defaultOptions = array())
    {
        $this->matcher = $matcher;
        $this->defaultOptions = array_merge(array(
            'depth' => null,
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
        return (string) $this->getMenu($item, $options);
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|string
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
     * @return \Nette\Utils\Html|string
     */
    protected function getList(ItemInterface $item, $attributes, $options)
    {
        if (!$item->hasChildren() || 0 === $options['depth'] || !$item->getDisplayChildren()) {
            return '';
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
            $options['depth'] = $options['depth'] - 1;
        }

        $items = array();
        foreach ($item->getChildren() as $child) {
            $items[] = $this->getItem($child, $options);
        }
        return $items;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|string
     */
    protected function getItem(ItemInterface $item, array $options)
    {
        if (!$item->isDisplayed()) {
            return '';
        }

        // create an array than can be imploded as a class list
        $class = (array) $item->getAttribute('class');

        if ($this->matcher->isCurrent($item)) {
            $class[] = $options['currentClass'];
        } elseif ($this->matcher->isAncestor($item, $options['depth'])) {
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

        $li = Html::el('li', $attributes)
            ->add($this->getLink($item, $options));

        // renders the embedded ul
        $childrenClass = (array) $item->getChildrenAttribute('class');
        $childrenClass[] = 'menu_level_'.$item->getLevel();

        $childrenAttributes = $item->getChildrenAttributes();
        $childrenAttributes['class'] = $childrenClass;

        return $li->add($this->getList($item, $childrenAttributes, $options));
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
            return $this->parentControl->getPresenter(true)->link($item->getExtra('link'));
        }
        return null;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|string
     */
    protected function getLink(ItemInterface $item, array $options)
    {
        if (!$this->getUri($item, $options) && !$item->getLabel()) {
            return '';
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
     * @return \Nette\Utils\Html
     */
    protected function getSpanElement(ItemInterface $item, array $options)
    {
        return Html::el('span', $item->getLabelAttributes())
            ->setHtml($this->getText($item, $options));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return mixed
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
     * Escapes an HTML value
     *
     * @param string $value
     * @return string
     */
    protected function escape($value)
    {
        return $this->fixDoubleEscape(htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'));
    }

    /**
     * Fixes double escaped strings.
     *
     * @param string $escaped string to fix
     * @return string A single escaped string
     */
    protected function fixDoubleEscape($escaped)
    {
        return preg_replace('/&amp;([a-z]+|(#\d+)|(#x[\da-f]+));/i', '&$1;', $escaped);
    }

    /**
     * Sets the parent control - this is important for link generation
     * @param Control $parentControl
     */
    public function setParentControl(Control $parentControl)
    {
        $this->parentControl = $parentControl;
        if (method_exists($this->matcher, 'setParentControl')) {
            $this->matcher->setParentControl($parentControl);
        }
    }

    protected function getRealLevel(ItemInterface $item, array $options)
    {
        return $item->getLevel() - $options['rootLevel'];
    }
}