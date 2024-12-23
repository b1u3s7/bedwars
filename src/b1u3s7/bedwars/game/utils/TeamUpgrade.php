<?php

namespace b1u3s7\bedwars\game\utils;

use pocketmine\item\Item;

class Upgrade
{
    private string $name;
    private Item $price;
    /**
     * @var callable
     */
    private $callback;

    public function __construct(string $name, Item $price, callable $callback)
    {
        $this->name = $name;
        $this->price = $price;
        $this->callback = $callback;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): Item
    {
        return $this->price;
    }

    public function setPrice(Item $price): void
    {
        $this->price = $price;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback): void
    {
        $this->callback = $callback;
    }
}