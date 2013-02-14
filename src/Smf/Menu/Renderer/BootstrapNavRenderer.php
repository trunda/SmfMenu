<?php

namespace Smf\Menu\Renderer;


use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Nette\Utils\Html;

/**
 * Generates navigation by adding some classes, data attributes atc.
 */
class BootstrapNavRenderer extends ListRenderer
{
    /**
     * Overwriting some options - classes, etc.
     * @param \Knp\Menu\Matcher\MatcherInterface $matcher
     * @param array $defaultOptions
     */
    public function __construct(MatcherInterface $matcher, array $defaultOptions = array())
    {
        $defaultOptions = array_merge(array(
            'currentClass' => 'active',
            'ancestorClass' => 'active',
            'firstClass' => null,
            'lastClass' => null
        ), $defaultOptions);
        parent::__construct($matcher, $defaultOptions);
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|string
     */
    protected function getMenu(ItemInterface $item, array $options = array())
    {
        $menu = parent::getMenu($item, $options);
        if (!is_null($menu)) {
            $menu->class = (array) $menu->class;
            array_unshift($menu->class, 'nav');
        }

        return $menu;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param $attributes
     * @param $options
     * @return \Nette\Utils\Html|string
     */
    protected function getList(ItemInterface $item, $attributes, $options)
    {
        $list = parent::getList($item, $attributes, $options);
        // Dropdown
        if (!is_null($list)
            && $this->getRealLevel($item, $options) >= 1
            && $item->hasChildren()
            && $item->getDisplayChildren()) {
            $list->class = (array) $list->class;
            $list->class[] = 'dropdown-menu';
        }
        return $list;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|string
     */
    protected function getItem(ItemInterface $item, array $options)
    {
        $result = parent::getItem($item, $options);
        if (!is_null($item)
            && $options['depth'] !== 0
            && $item->hasChildren()
            && $item->getDisplayChildren()) {

            $result->class = (array) $result->class;
            if ($this->getRealLevel($item, $options) === 1) {
                $result->class[] = 'dropdown';
            } else {
                $result->class[] = 'dropdown-submenu';
            }
        }
        return $result;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html|string
     */
    protected function getLink(ItemInterface $item, array $options)
    {
        $link = parent::getLink($item, $options);

        // Carret
        if (!is_null($link)
            && $options['depth'] !== 0
            && $this->getRealLevel($item, $options) === 1
            && $item->hasChildren()
            && $item->getDisplayChildren()) {

            $link->add('&nbsp;')
                ->add(Html::el('b', array('class' => 'caret')));
            $link->class = (array) $link->class;
            $link->class[] = 'dropdown-toggle';
            $link->data['toggle'] = 'dropdown';
        }
        return $link;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return \Nette\Utils\Html
     */
    protected function getSpanElement(ItemInterface $item, array $options)
    {
        return Html::el('a', $item->getLabelAttributes())
            ->setHtml($this->getText($item, $options));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     * @return string
     */
    protected function getText(ItemInterface $item, array $options)
    {
        $text = parent::getText($item, $options);
        if ($item->getExtra("icon", false)) {
            $text = Html::el('i', array('class' => 'icon-' . $item->getExtra("icon"))) . '&nbsp;' . $text;
        }
        return $text;
    }


}