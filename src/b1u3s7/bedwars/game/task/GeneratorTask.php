<?php

namespace b1u3s7\bedwars\game\task;

use b1u3s7\bedwars\Bedwars;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\Position;
use pocketmine\world\World;

class GeneratorTask
{
    private Item $item;
    private int $interval;
    private Position $position;
    private TaskHandler $taskHandler;
    public function __construct($item, $interval, $position)
    {
        $this->item = $item;
        $this->interval = $interval;
        $this->position = $position;
        // to center in block
        $this->position->x += 0.5;
        $this->position->z += 0.5;
        $this->scheduleTask();
    }

    private function scheduleTask(): void
    {
        $this->taskHandler = Bedwars::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(new DropItemTask($this->item, $this->position), $this->interval * 20, $this->interval * 20);
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function changeInterval(int $interval): void
    {
        $this->interval = $interval;
        $this->taskHandler->cancel();
        $this->scheduleTask();
    }

    public function cancel(): void
    {
        $this->taskHandler->cancel();
    }
}