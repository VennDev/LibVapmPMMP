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

|   Library       | Asynchronous polymorphism | Cocurrent | Asynchronous threads | Handles large systems | Multiple ways to support asynchrony |
| --------------- | ------------------------- | --------- | -------------------- | --------------------- |  ---------------------------------- |
| LibVapmPMMP     |           Yes             |    Yes    |          Yes         |          Yes          |                  Yes                |
| Await-Generator |           Yes             |    Yes    |          No          |          No           |                  No                 |

- Why handles large systems ?
``
Vapm uses a Task scheduler with tick-based execution and Await-Gennerator uses queue and execute instantly within a scope.
Comparing the two asynchronous models, one that uses queues to store and execute instantly in a specific scope, and one that saves to the task scheduler for tick execution, should be based on the following factors:

  1. Performance and Flexibility: Queue and execute instantly in a scope: This model is usually faster in processing because tasks are executed immediately as they appear in the queue. However, execution may be slower if the ticks are set up with long intervals.
  2. Resource Control: Queue and Instant Execution: More resources can be consumed in the event that there are too many tasks that need to be processed immediately, leading to the risk of resource exhaustion or increased system latency. Task scheduler: Helps you better manage and control your resources by executing only a limited number of tasks per tick, reducing the pressure on the system.
  3. Real-Time Response: Queue and execute instantly: It's better if real-time feedback is required, as tasks are processed instantly. Task scheduler: May not be suitable for applications that require instant response, as the task has to wait until the next tick to be processed.
  4. Complexity and Maintenance: Queue and execute instantly: Often simpler to deploy, but difficult to manage if the system is highly complex. Task scheduler: More complex, but offers more flexibility and ease of maintenance in the long run.
  5. Use cases: Queue and Instant Execution: Suitable for applications that require immediate processing and not too many simultaneous tasks. Task scheduler: Suitable for more complex systems where it is necessary to control the processing of tasks from time to time to ensure stable performance.

-> Conclude: Each model has its own pros and cons, and choosing between them depends on the specific requirements of the system you're developing. If you need real-time performance and the system can withstand heavy loads, the queue model may be a better choice. Conversely, if you need tight resource control and your system needs to handle a large volume of tasks on a regular basis, a task scheduler will be a better choice.
``

- Tests: [Code](https://gist.github.com/VennDev/4f7be83d55abfbbf44ff2d249e94968c)
![image](https://github.com/user-attachments/assets/07a39109-8db4-488d-a0db-6e3404edadf3)
