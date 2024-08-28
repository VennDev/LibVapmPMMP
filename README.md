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

- **Why handles large systems ?**
Vapm uses a Task scheduler with tick-based execution and Await-Gennerator uses queue and execute instantly within a scope.
Comparing the two asynchronous models, one that uses queues to store and execute instantly in a specific scope, and one that saves to the task scheduler for tick execution, should be based on the following factors:
  1. **Performance and Flexibility:**
     - **Queue and execute instantly in a scope:** This model is usually faster in processing because tasks are executed immediately as they appear in the queue. However, this can lead to overload if there are too many tasks appearing at the same time, as they are all being processed at the same time.
     - **Task scheduler with tick-based execution:** Using a task scheduler allows you to control the speed at which tasks are processed by executing them in ticks. This helps to avoid overload and allows for a more streamlined allocation of system resources. However, execution may be slower if the ticks are set up with long intervals.
  3. **Resource Control:**
     - **Queue and Instant Execution:** More resources can be consumed in the event that there are too many tasks that need to be processed immediately, leading to the risk of resource exhaustion or increased system latency.
     - **Task scheduler:** Helps you better manage and control your resources by executing only a limited number of tasks per tick, reducing the pressure on the system.
  5. **Real-Time Response:**
     - **Queue and execute instantly:** It's better if real-time feedback is required, as tasks are processed instantly.
     - **Task scheduler:** May not be suitable for applications that require instant response, as the task has to wait until the next tick to be processed.
  7. **Complexity and Maintenance:**
     - **Queue and execute instantly:** Often simpler to deploy, but difficult to manage if the system is highly complex.
     - **Task scheduler:** More complex, but offers more flexibility and ease of maintenance in the long run.
  9. **Use cases:**
      - **Queue and Instant Execution:** Suitable for applications that require immediate processing and not too many simultaneous tasks.
      - **Task scheduler:** Suitable for more complex systems where it is necessary to control the processing of tasks from time to time to ensure stable performance.
- **So is there a way for you to use Vapm as a Wait-Generator?**
You can do it by running methods such as ```CoroutineGen::runBlocking()``` or ```AwaitGroup``` which are available in Vapm or more..
- **Is it okay to handle such asynchronous tasks on the Task-Scheduler?**
That's perfectly fine because the Task-Schedulers are only allowed to handle up to 20 asynchronous tasks and +1 of the scheduled CoroutineGens. (*Note that the processing here means that it will process each task only once, and if it is not completed, it will be skipped and processed for the next time!*)
- **Why is that okay?**
That's really stable when you know the processing they wait at the parts where you think it's a really heavy task!
Example for you: [Click](https://github.com/VennDev/VBasket/blob/main/src/vennv/vbasket/event/VBasketPlantEvent.php#L108)
As you can see that I stopped at that very moment to do a wait for a task to complete and the Tick-Scheduler would repeat and process again. This makes it possible for many other tasks to be processed simultaneously in the other tick.
- **Await-Generator Problem:**
The Await-Generator has a problem that if I create a promise and just ask it to run without waiting immediately after I declare it, it's like if you have a for loop for billions of numbers, if I wait and run the promise right below it, it will tell me that I'm synchronizing?
I've noticed that there is a queue in the library's Await processing class, however, assuming that if no promises are triggered, the promises that need to be fulfilled are when they are processed? and where is their real-time?
What if I want the promise of processing 1 billion tasks and needing to do it immediately after completing it will fulfill some parameter to do the next thing? Note that this is 1 billion.
- Await-Generator
```php
$channel = new Channel;
Await::f2c(function() use ($channel) {
    for ($i = 0; $i < 5000000; $i++) {
        yield from $channel->sendAndWait($i);
    }
});
```
- Vapm
```php
/**
 * @throws Throwable
 */
public function loadWorlds(): Channel
{
    $channel = new Channel();
    CoroutineGen::runNonBlocking(function () use (&$channel): Generator {
        $i = 0;
        foreach (scandir($this->plugin->getServer()->getDataPath() . "worlds") as $world) {
            if ($world === "." || $world === "..") continue;
            if ($this->plugin->getManager()->isIslandNether($world)) {
                $i++;
                yield from $channel->send($i);
                $this->applyIslandNether($world);
            } elseif ($this->plugin->getManager()->isIslandEnd($world)) {
                $i++;
                yield from $channel->send($i);
                $this->applyIslandEnd($world);
            } else {
                $this->applyIslandOverworld($world);
            }
        }
        return $i;
    });
    return $channel;
}

// Process with Task by PMMP
// Load worlds
if (
    self::$doneLoadWorlds === null &&
    (microtime(true) - self::$lastTimeLoadWorlds) >= 5.0
) {
    self::$lastTimeLoadWorlds = microtime(true);
    self::$doneLoadWorlds = $this->plugin->getWorldManager()->loadWorlds();
} elseif (self::$doneLoadWorlds !== null) {
    CoroutineGen::runNonBlocking(function (): Generator {
        $receive = self::$doneLoadWorlds->receiveGen();
        while ($receive !== null) {
            $receive = yield from self::$doneLoadWorlds->receiveGen();
        }
        self::$doneLoadWorlds = null;
    });
}
// Completely waits and processes each slow incoming content that Channel sends without over-load the server when too many things are sent and received at once.
```
The question arises that why do I have to?? wait a long time to handle a big disagreement like this without handling them asynchronously, quickly and slowly by ticks?

- **Speed test:** [Code](https://gist.github.com/VennDev/4f7be83d55abfbbf44ff2d249e94968c) with according to the inherent method, Await-Generator still wants to wait and process as usual without using the Task-Scheduler.
![image](https://github.com/user-attachments/assets/07a39109-8db4-488d-a0db-6e3404edadf3)
