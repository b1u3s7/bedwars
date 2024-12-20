<?php

namespace b1u3s7\bedwars\game\task;

use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

class DropItemTask extends Task
{
    private Item $item;
    private Position $position;

    public function __construct($item, $position)
    {
        $this->item = $item;
        $this->position = $position;
    }

    public function onRun(): void
    {
        $this->position->getWorld()->dropItem($this->position->asVector3(), $this->item);
    }
}