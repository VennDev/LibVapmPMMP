# VapmPMMP
- This is Virion Async/Promise for PocketMine-PMMP

# How to setup ?
- Download the Phar officially [here](https://poggit.pmmp.io/ci/VennDev/VapmPMMP/VapmPMMP)
- Take them and put them in your Virion folder. If you do not understand what Virion is, then [click here](https://poggit.pmmp.io/p/DEVirion/1.2.8)
- To implement and use methods, you must first use this library's method at the top of the plugin's onEnable function.
- Example:
```php
protected function onEnable() : void
{
		VapmPMMP::init($this);
}
```
- Finally, just use the methods in this: [here](https://github.com/VennDev/Vapm/blob/main/README.md)
- This is plugin Example: [plugin](https://github.com/VennDev/SimplifyLibasynql/tree/main/Examples/Test)
