# Použití SmfMenu (KnpMenu integrace do Nette)

Tato dokumentace ukazuje jednotlivé možnosti, jak používat SmfMenu - menu již není otrava.

* **Základní dokumentace**
    * [Instalace do Nette](#instalace)
    * [Jak tvořit menu](#tvorba)

<a name="instalace"></a>

## Instalace do Nette


### Stažení zdrojových kódů

Nejprve je potřeba stáhnout zdrojové kódy KnpMenu a SmfMenu. Doporučovaná cesta jak to provést, je pomocí `composeru`.
Do `composer.json` přidejte následující dva řádky do sekce `require`:

```
"trunda/smf-menu": "1.0.*@dev",
"knplabs/knp-menu": "2.0.*@dev"
```

Protože KnpMenu stále není ve stabilní verzi, je potřeba přidat závislost i na knp-menu (druhý řádek) s štítkem `@dev`,
kterým povolíme instalaci balíčku, který není ve stabilní verzi.

Pokud si přejeme povolit vývojové verze u všech balíků (nedoporučuji), lze nastavi v `composer.json` minimální stabilitu na `dev` a vypustit závislost na KnpMenu. Soubor `composer.json` by poté vypadal asi takto:

```
{
    ...
    "require": {
        ...
        "trunda/smf-menu": "1.0.*@dev"
    },
    "minimum-stability": "dev"
}
```

**Pozor:** tato změna nainstaluje všechny balíky ve vývojové verzi, což může vést k problémům se stabilitou aplikace.Proto je tento postup nedoporučován.

### Registrace do Nette

Registrace do Nette se provádí zaregistrovaním rožšíření do konfigurátoru. Do `bootstrap.php` je potřeba před vytvoření kontejneru přidat následující řádek:

```php
Smf\Menu\Config\Extension::register($configurator);

// ... někde pod tímto řádkem se volá $configurator->createContainer();
```

Tato registrace vytvoří několik služeb:

1.  Instanci třídy `Smf\Menu\MenuFactory`

    Tato služba slouží k vytváření jednotlivých položek menu.

2.  Instance třídy `Smf\Menu\Matcher\Matcher`

    Služba je slouží pro výběr aktivní položky pomocí `Voter`ů

3.  Instance všech výchozí rendererů, ty jsou v tomto okamžiku dva.

    ```php
    Smf\Menu\Renderer\ListRenderer
    Smf\Menu\Renderer\BootstrapNavRenderer
    ```

4.  Instance továrny pro menu control `Smf\Menu\Control\Factory`

    Slouží k snadnému vytváření menu control a lze ji v presenteru získat pomocí metody `inject...`.

### Konfigurace

Konfigurace umožňuje nastavení výchozího rendereru. V `config.neon` můžeme použít (pokud jsme registroval rozšíření s původním názvem):

```
smfMenu:
    defaultRenderer: bootstrapNav # jaký použit výchozí renderer, výchozí je vždy list
```

<a name="tvorba"></a>

## Tvoříme první menu

Vzhledem k mocnému DI mechanismu lze menu tvořit několika způsoby.

### Pomocí továrny

Standardní a nejjednodušší způsob, jak vytvořit menu je pomocí továrny, kterou si necháme vložit (vstříknout) do presenteru:

```php

use Smf\Menu;

final class FooPresenter extends BasePresenter
{
    /** @var Menu\Control\Factory */
    private $menuFactory;

    public function injectMenuFactory(Menu\Control\Factory $factory)
    {
        $this->menuFactory = $factory;
    }

    public function createComponentMenu()
    {
        $menu = $this->menuFactory->createControl();
        $root = $menu->getRoot();

        $root->addChild('products', array(
            'label' => 'Produkty',
            'link'  => 'BarPresenter:default'
        ));

        // plnění dalsích položek menu

        return $menu;
    }

}
```

Pro vykreslení menu se používá obligátní makro `control` (pro více informací vizte sekci [Jak menu vykreslit](#vykreslovani)) kdekoliv v Latte šabloně:

```html
<div class="navbar">
    {control menu}
</div>
```

### Menu jako služba

Je možné, že pro tvorbu svého menu budete potřebovat různé závislosti a v tu chvíli je rozumné vytvořit menu jako službu.

Nejprve začneme s vytvořím vlastní menu `control`:

```php

namespace Foo\Bar;

class MyMenu extends \Smf\Menu\Control\MenuControl
{
    private $connection;

    public function __construct(Nette\Database\Connection $connection,
        Knp\Menu\FactoryInterface $factory, Knp\Menu\Renderer\IManager $manager)
    {
        parent::__construct($factory, $manager);
        $this->connection = $connection;
    }

    public function getRoot()
    {
        // ... načtení menu z DB
    }
}
```

Poté je potřeba zavést službu do `config.neon`:

```
services:
    connection:
        # ...
    myMenu:
        class: Foo\Bar\MyMenu(%connection%, %smfMenu.factory%, %smfMenu.rendererManager%)
        # ...
```

Dále již jen získat službu v presenteru pro metodu vytvářející komponentu:

```php

use Smf\Menu;

final class FooPresenter extends BasePresenter
{
    /** @var Foo\Bar\MyMenu */
    private $myMenu;

    public function injectMyMenu(Foo\Bar\MyMenu $myMenu)
    {
        $this->myMenu = $myMenu;
    }

    public function createComponentMyMenu()
    {
        return $this->myMenu;
    }

}
```

A nakonec menu menu vykreslit (pro více informací vizte sekci [Jak menu vykreslit](#vykreslovani)):

```html
<div class="navbar">
    {control myMenu}
</div>
```