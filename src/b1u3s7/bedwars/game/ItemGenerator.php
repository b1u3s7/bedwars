<?php

namespace b1u3s7\bedwars\game;

use pocketmine\item\Item;
use pocketmine\world\Position;

class ItemGenerator {
    private Position $position;
    private Item $item;
    public function __construct(Position $position, Item $item)
    {
        $this->position = $position;
        $this->item = $item;
    }

    public function dropItem(int $amount = 1): void
    {
        $this->position->getWorld()->dropItem($this->position->asVector3(), $this->item->setCount($amount));
    }
}