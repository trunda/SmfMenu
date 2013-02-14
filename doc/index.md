# Použití SmfMenu (KnpMenu integrace do Nette)

Tato dokumentace ukazuje jednotlivé možnosti, jak používat SmfMenu - menu již není otrava.

* **Základní dokumentace**
    * [Instalace do Nette](#instalace)
    * [Tvoříme první menu](#prnvni-menu)
    * [Jak menu vykreslit](#vykrslovani)
* **Pokročilé techniky**

<a name="instalace"></a>

## Instalace do Nette


### Stažení zdrojových kódů

Nejprve je potřeba stáhnout zdrojové kódy KnpMenu a SmfMenu. Doporučovaná cesta jak to provést, je pomocí `composeru`.
Do `composer.json` přidejte následující dva řádky do sekce `require`:

```json
"trunda/smf-menu": "1.0.*@dev",
"knplabs/knp-menu": "2.0.*@dev"
```

Protože KnpMenu stále není ve stabilní verzi, je potřeba přidat závislost i na knp-menu (druhý řádek) s štítkem `@dev`,
kterým povolíme instalaci balíčku, který není ve stabilní verzi.

Pokud si přejeme povolit vývojové verze u všech balíků (nedoporučuji), lze nastavi v `composer.json` minimální stabilitu na `dev` a vypustit závislost na KnpMenu. Soubor `composer.json` by poté vypadal asi takto:

```json
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

// ... někde pod tímto řádkem je $container = $configurator->createContainer();
```

Tato registrace vytvoří několik služeb:

1.  Instanci třídy `Smf\Menu\MenuFactory`

    Tato služba slouží k vytváření jednotlivých položek menu.

2.  Instance třídy `Smf\Menu\Matcher\Matcher`

    Služba je slouží pro výběr aktivní položky pomocí `Voter`ů

3.  Instance všech výchozí rendererů, ty jsou v tomto okamžiku 2.

    ```php
    Smf\Menu\Renderer\ListRenderer
    Smf\Menu\Renderer\BootstrapNavRenderer
    ```