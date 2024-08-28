# LibVapmPMMP
- This is Virion Async/Promise/Coroutine/Thread/GreenThread for PocketMine-PMMP

# Composer
```composer require vennv/vapm-pmmp```

# How to setup ?
- Download the Phar officially [here](https://poggit.pmmp.io/ci/VennDev/LibVapmPMMP/LibVapmPMMP)
- Take them and put them in your Virion folder. If you do not understand what Virion is, then [click here](https://poggit.pmmp.io/p/DEVirion/1.2.8)
- To implement and use methods, you must first use this library's method at the top of the plugin's onEnable function
- Example:
```php
protected function onEnable() : void
{
    VapmPMMP::init($this);
}
```
- What is VapmPMMP::init($this) ?
```php
/**
* @param PluginBase $plugin
*
* This method is called by VapmPMMP::init(), it will run event loop.
*/
public static function init(PluginBase $plugin) : void;
```
- Finally, here's a guide to the methods you can use: [here](https://venndev.gitbook.io/vapm/)
- This is plugin Example:
- [VFormOOPAPI](https://github.com/VennDev/VFormOOPAPI)
- [SimplifyLibasynql](https://github.com/VennDev/SimplifyLibasynql/tree/main/Examples/Test)
- [VSharedData](https://github.com/VennDev/VSharedData)
- [VJesusBucket](https://github.com/VennDev/VJesusBucket)
- [VBasket](https://github.com/VennDev/VBasket)
- [VOreSpawner](https://github.com/VennDev/VOreSpawner)

# LibVapmPMMP vs Await-Generator
- [Await-Generator](https://github.com/SOF3/await-generator)

|   Library       | Asynchronous polymorphism | Cocurrent | Asynchronous threads | Handles large systems |
| --------------- | ------------------------- | --------- | -------------------- | --------------------- |
| LibVapmPMMP     |           Yes             |    Yes    |          Yes         |          Yes          |
| Await-Generator |           Yes             |    Yes    |          No          |          No           |

Tests: [Code](https://gist.github.com/VennDev/4f7be83d55abfbbf44ff2d249e94968c)
![image](https://github.com/user-attachments/assets/07a39109-8db4-488d-a0db-6e3404edadf3)
