<?php

namespace Smf\Menu;

use Knp\Menu\Factory\ExtensionInterface;
use Knp\Menu\ItemInterface;

class MenuExtension implements ExtensionInterface
{

    /**
     * Builds the full option array used to configure the item.
     *
     * @param array $options The options processed by the previous extensions
     *
     * @return array
     */
    public function buildOptions(array $options)
    {
        $options = array_merge(
            array(
                'link' => null,
                'icon' => null,
            ),
            $options
        );

        if (isset($options['link']) && $options['link'] !== null) {
            $options['extras']['link'] = (array) $options['link'];
        }
        $options['extras']['icon'] = $options['icon'];
        return $options;
    }

    /**
     * Configures the item with the passed options
     *
     * @param ItemInterface $item
     * @param array $options
     */
    public function buildItem(ItemInterface $item, array $options)
    {

    }
}