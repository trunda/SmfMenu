# Použití SmfMenu (KnpMenu integrace do Nette)

Tato dokumentace ukazuje jednotlivé možnosti, jak používat SmfMenu - menu již není otrava.

* **Základní dokumentace**
    * [Instalace do Nette](#instalace)
    * [Jak tvořit menu](#tvorba)
    * [Jak vykreslit menu](#vykreslovani)

<a name="instalace"></a>

## Instalace do Nette


### Stažení zdrojových kódů

Nejprve je potřeba stáhnout zdrojové kódy KnpMenu a SmfMenu. Doporučovaná cesta jak to provést, je pomocí `composeru`.
Do `composer.json` přidejte následující dva řádky do sekce `require`:

```
"trunda/smf-menu": "1.0.*@dev",
"knplabs/knp-menu": "2.0.*@dev"
```

Protože KnpMenu stále není ve stabilní verzi, je potřeba přidat závislost i na knp-menu (druhý řádek) se štítkem `@dev`,
kterým povolíme instalaci balíčku, který není ve stabilní verzi.

Pokud si přejeme povolit vývojové verze u všech balíků (nedoporučuji), lze nastavit v `composer.json` minimální stabilitu na `dev` a vypustit závislost na KnpMenu. Soubor `composer.json` by poté vypadal asi takto:

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

**Pozor:** tato změna nainstaluje všechny balíky ve vývojové verzi, což může vést k problémům se stabilitou aplikace. Proto je tento postup nedoporučován.

### Registrace do Nette

Registrace do Nette se provádí zaregistrovaním rožšíření do konfigurátoru. Do `bootstrap.php` přidáme před vytvoření kontejneru následující řádek:

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

Konfigurace umožňuje pouze nastavení výchozího rendereru. V `config.neon` můžeme použít (pokud jsme registroval rozšíření s původním názvem):

```
smfMenu:
    defaultRenderer: bootstrapNav # jaký použít výchozí renderer (úplně výchozí je list)
```

<a name="tvorba"></a>

## Jak tvořit menu

Existují dva způsoby jak vytvořit menu control.

### Pomocí továrny

Standardní a nejjednodušší způsob je pomocí továrny, kterou si necháme vložit (vstříknout) do presenteru:

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

Pokud je potřeba další menu, vytvoříme další továrničku:

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
        // ...
    }

    public function createComponentSideMenu()
    {
        $menu = $this->menuFactory->createControl();
        $root = $menu->getRoot();

        // plnění položek menu

        return $menu;
    }

}
```

a vykreslení:

```html
<div class="navbar">
    {control menu}
</div>
<div class="sidebar">
    {control sideMenu}
</div>
```


### Menu jako služba

Je možné, že pro tvorbu svého menu budete potřebovat různé závislosti. V tu chvíli je rozumné vytvořit menu jako službu.

Nejprve začneme vytvořením vlastního menu `control`:

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

Poté je zavedeme službu do `config.neon`:

```
services:
    connection:
        # ...
    myMenu:
        class: Foo\Bar\MyMenu(%connection%, %smfMenu.factory%, %smfMenu.rendererManager%)
        # ...
```

Dále již jen získáme službu v presenteru a vrátíme ji v metodě pro vytvoření komponenty:

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

A nakonec menu vykreslíme kdekoliv v šabloně (pro více informací vizte sekci [Jak menu vykreslit](#vykreslovani)):

```html
<div class="navbar">
    {control myMenu}
</div>
```

<a name="vykreslovani"></a>
## Jak vykreslit menu

Tato sekce se týká samotného vykreslování menu. Doporučuji, projít [dokumentaci k KnpMenu](https://github.com/KnpLabs/KnpMenu/blob/master/doc/01-Basic-Menus.markdown), kde jsou zajímavé informace ohledně nastavování různých možností a vlastností.

Samotné vykreslení komponenty je notoricky známé a jednoduché:

```html
<div class="navbar">
    {control menu}
</div>
```

Chceme-li změnit renderer, kterým se má vykreslovat, můžeme za název menu přidat název rendereru:

```html
<div class="navbar">
    {control menu:bootstrapNav}
</div>
```

Komponenta se v tomto případě bude snažit najít renderer `bootstrapNav` a pomocí něj menu vyrenderovat (lze jednoduše [přidat vlastní renderery](#vlastni-renderer)).

Dále je možné předat do procesu renderování několik nastavení ve formě asociativního pole:

```html
<div class="navbar">
    {control menu:bootstrapNav, depth => 1}
</div>
```

Nastavení jsou následující (a jejich výchozí hodnoty pro `ListRenderer`):

*   `depth => null`
    Jak hluboko se má menu vykreslit (`null` - celé menu, `1` - pouze první úroveň, `2` - první a druhá úroveň)
*   `path => null`
    Pomocí tohoto nastavení lze vykreslit pouze určitou část menu (např. `product-items` vykreslí pouze potomky z položky `items`, která náleŹí rodiči `product`)
*   `currentAsLink => true`
    Má být aktivní položko vykreslována jako odkaz `<a>` (`true`) nebo jako `<span>` (`false`)
*   `currentClass => 'current'`
    CSS třída použitá pro aktivní položku (v `ListRendereru` se umisťuje na přidružené `<li>`)
*   `ancestorClass => 'current_ancestor'`
    CSS třída použitá pro rodiče aktivní položky (v `ListRendereru` se umisťuje na přidružené `<li>`)
*   `firstClass => 'first'`
    CSS třída pro první položku v dané úrovní (v `ListRendereru` se umisťuje na přidružené `<li>`)
*   `lastClass => 'last'`
    CSS třída pro poslední položku v dané úrovní (v `ListRendereru` se umisťuje na přidružené `<li>`)
*   `allow_safe_labels => false`
    Informace zdali se mají (`false`) či nemají (`true`) escapovat titulky menu. Tuto vlastnost je možné nastavit i konkrétní položce:
    ```php
        // $item je instance Knp\Menu\ItemInterface
        $item->addExtra('safe_label', true);
    ```
*   `clear_matcher => true`
    Určuje, zda se má po vykreslení zavolat `matcher->clear();`. Většinou slouží k pročištění cache.


### Další možnosti, jak ovlivnit vykreslování

Rozhraní `Knp\Menu\ItemInterface` disponuje několika metodami, které mohou ovlivnit výsledek renderování. Všechny přidávájí attributy k výsledným HTML značkám. Rozdíl mezi nimi je ten, ke kterým značkám jsou atributy přidány. Jsou to (všechny popisky uvažují `ListRenderer`):

*   `addAttribute()` - tyto atributy jsou přidány k výslenému `<li>`
*   `addLinkAttribute()` - tyto atributy jsou přidány k výslenému `<a>`
*   `addChildrenAttribute()` - tyto atributy jsou přidány k výslenému `<ul>`, který obaluje děti daného prvku
*   `addLabelAttribute()` - tyto atributy jsou přidány k výslenému `<span>`

Chceme-li tedy přidat určitou třídu celému menu `<ul>` posupujeme následovně:

```php
final class FooPresenter extends BasePresenter
{
    // ...
    public function createComponentMenu()
    {
        $menu = $this->menuFactory->createControl();
        $root = $menu->getRoot();

        $root->addChildrenAttribute('class', 'moje-css-trida');

        // plnění položek menu
        return $menu;
    }
}
```

Root je nejvrchnější prvek (vždy) a všechny jeho děti (naše menu) je obaleno v `<ul>`, které dostane CSS třídu `moje-css-trida`.

Dále je možné předávat si do položky menu různé další hodnoty pomocí metody `setExtra()` a následně na ně při vykreslování vlastním rendererem reagovat.

Veškeré tyto atributy se dají přidávat již při vytváření položky:

```php
final class FooPresenter extends BasePresenter
{
    // ...
    public function createComponentMenu()
    {
        $menu = $this->menuFactory->createControl();
        $root = $menu->getRoot();

        $roor->addChild('item', array(
            'uri' => null, // Url, např. 'http://google.com'
            'link' => null, // Nette link, např. 'Product:show', nebo array('Product:show', array('id' => 1))
            'label' => null, // Popisek
            'attributes' => array(), // vizte výše
            'linkAttributes' => array(), // vizte výše
            'childrenAttributes' => array(), // vizte výše
            'labelAttributes' => array(), // vizte výše
            'extras' => array(), // vizte výše
            'display' => true, // zobrazit položku?
            'displayChildren' => true, // zobrazit potomky položky?
        ));

        return $menu;
    }
}
```