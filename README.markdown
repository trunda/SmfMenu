SmfMenu is just integration library for KnpMenu.

#Installation

Easiest way to install the addon is via `composer`. Add this to your `composer.json`:

    "trunda/smf-menu": "1.0.*@dev",
    "knplabs/knp-menu": "2.0.*@dev"

and then register the extension by adding this lines to your `bootstrap.php` before container creation:

```php
Smf\Menu\Config\Extension::register($configurator);
```


# What is KnpMenu?

The KnpMenu library provides object oriented menus for PHP 5.3. With following API.
```php
<?php

use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;

$factory = new MenuFactory();
$menu = $factory->createItem('My menu');
$menu->addChild('Home', array('uri' => '/'));
$menu->addChild('Comments', array('uri' => '#comments'));
$menu->addChild('Symfony2', array('uri' => 'http://symfony-reloaded.org/'));
$menu->addChild('Coming soon');

$renderer = new ListRenderer();
echo $renderer->render($menu);
```

The above menu would render the following HTML:

```html
<ul>
  <li class="first">
    <a href="/">Home</a>
  </li>
  <li class="current">
    <a href="#comments">Comments</a>
  </li>
  <li>
    <a href="http://symfony-reloaded.org/">Symfony2</a>
  </li>
  <li class="last">
    <span>Coming soon</span>
  </li>
</ul>
```

This way you can finally avoid writing an ugly template to show the selected item,
the first and last items, submenus, ...

# Usage

Extension adds factory for the menu control, so in presenter is usable like this:

```php
abstract class BasePresenter extends Presenter
{
    //...
    protected function createComponentMenu()
    {
        $menu = $this->context->menu->createMenuControl();
        $root = $menu->getRoot();

        $root->addChild('catalog', array(
           'label' => 'Katalog',
           'icon'  => 'book'
        ));

        $root['catalog']->addChild('categories', array(
            'label' => 'Categories',
            'link'  => 'Category:list',
            'icon'  => 'table',
        ));

        $root['catalog']->addChild('new', array(
            'label' => 'Product',
            'link'  => 'Product:list',
            'icon'  => 'gift',
        ));
         
        // ....

        return $menu;
    }
    // ...
}
``` 

and then in your template you can use obligate `control` macro:

```latte
{control menu}
```

## Renderers

You can register as many renderers as you want. Some default renderers are registered by defaul:

```
list          => Smf\Menu\Renderer\ListRenderer
bootstrapNav  => Smf\Menu\Renderer\BootstrapNavRenderer
```

You can use them in template

```latte
{control menu:list}
{control menu:bootstrapNav}
```

Registration of new renderer is as easy as adding new service to your config with tag `menuRenderer`:

```
services:
    myRenderer: 
         class: My\Super\Renderer
         tags: 
             menuRenderer: mySuperRenderer
```

And then you can use it:

```latte
{control menu:mySuperRenderer}
```

# Credits

Credits goes to original authors of `KnpMenu` library as to authors of `KnpMenuBundle`.
