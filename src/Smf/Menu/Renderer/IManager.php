<?php
/**
 * Created by JetBrains PhpStorm.
 * User: trunda
 * Date: 14.02.13
 * Time: 14:31
 * To change this template use File | Settings | File Templates.
 */

namespace Smf\Menu\Renderer;

use Knp\Menu\Renderer\RendererInterface;
use Nette\InvalidArgumentException;

interface IManager {

    /**
     * Ads renderer with given name
     * @param string $name Name of the renderer
     * @param string|RendererInterface $renderer Renderer class name or instance
     * @param bool $overwrite Should be overwritten renderer with this name if exists?
     * @return IManager Fluent interface
     * @throws InvalidArgumentException If the renderer with given name already exists and $overwrite is false
     */
    public function addRenderer($name, $renderer, $overwrite = false);

    /**
     * Removes renderer
     *
     * @param $name string Name of the renderer to be removed
     * @return IManager Fluent interface
     */
    public function removeRenderer($name);

    /**
     * Returns instance of the renderer
     *
     * @param  string|null $name Name of the renderer if null is given returns the first one
     * @return RendererInterface Renderer instance
     *
     * @throws InvalidArgumentException If the renderer with given name does not exist
     */
    public function getRenderer($name);

    /**
     * Sets the default renderer
     *
     * @param $name String
     * @return IManager
     */
    public function setDefaultRenderer($name);

}