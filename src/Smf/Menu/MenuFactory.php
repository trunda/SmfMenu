<?php

namespace Smf\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory as BaseMenuFactory;

/**
 * Factory to create a menu from a tree
 */
class MenuFactory extends BaseMenuFactory
{
    /**
     * Overwritten for some extra options
     * @param \Knp\Menu\ItemInterface $item
     * @param array $options
     */
    protected function configureItem(ItemInterface $item, array $options)
    {
        parent::configureItem($item, $options);

        // Extra options
        $extraOptions = array('link', 'icon');
        foreach ($extraOptions as $option) {
            if (isset($options[$option])) {
                if ($option == 'link') {
                    $options[$option] = (array) $options[$option];
                }
                $item->setExtra($option, $options[$option]);
            }
        }
    }

}