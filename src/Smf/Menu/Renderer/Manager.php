<?php

namespace Smf\Menu\Renderer;

use Nette\InvalidArgumentException;
use Nette\Object;

class Manager extends Object implements IManager
{
    /** @var array */
    private $renderers = array();

    /** @var string */
    private $defaultRenderer = null;

    /**
     * Ads renderer with given name
     * @param string $name Name of the renderer
     * @param string|IRenderer $renderer Renderer class name or instance
     * @param bool $overwrite Should be overwritten renderer with this name if exists?
     * @return IManager Fluent interface
     * @throws InvalidArgumentException If the renderer with given name already exists and $overwrite is false
     */
    public function addRenderer($name, $renderer, $overwrite = false)
    {
        if (isset($this->renderers[$name]) && !$overwrite) {
            throw new InvalidArgumentException("Renderer with name '$name' is already registered.");
        }
        unset($this->renderers[$name]);
        // Is class? Exists?
        if (is_string($renderer) && !class_exists($renderer)) {
            throw new InvalidArgumentException("Renderer class '$renderer' doesn't exist.");
        }
        $this->renderers[$name] = $renderer;
        return $this;
    }

    /**
     * Removes renderer
     *
     * @param $name string Name of the renderer to be removed
     * @return IManager Fluent interface
     */
    public function removeRenderer($name)
    {
        unset($this->renderers[$name]);
        return $this;
    }

    /**
     * Returns instance of the renderer
     *
     * @param  string            $name Name of the renderer
     * @return IRenderer Renderer instance
     *
     * @throws InvalidArgumentException If the renderer with given name does not exist
     */
    public function getRenderer($name)
    {
        // default
        if ($name === null) {
            if ($this->defaultRenderer !== null) {
                $name = $this->defaultRenderer;
            } else if (count($this->renderers) > 0) {
                $name = key($this->renderers);
            }
        }

        if (!isset($this->renderers[$name])) {
            throw new InvalidArgumentException("Renderer with name '$name' doesn't exist.");
        }

        if (is_string($this->renderers[$name])) {
            $renderer = new $this->renderers[$name]();
            $reflection = ClassType::from($renderer);
            if (!$reflection->isSubclassOf('Smf\Menu\Renderer\IRenderer')) {
                throw new InvalidArgumentException("Renderer class '{$this->renderers[$name]}' is not subclass of Smf\\Menu\\Renderer\\IRenderer");
            }
            $this->renderers[$name] = $renderer;
        }

        return $this->renderers[$name];
    }

    /**
     * Sets the default renderer
     *
     * @param $name String
     * @return IManager
     */
    public function setDefaultRenderer($name)
    {
        $this->defaultRenderer = $name;
        return $this;
    }
}