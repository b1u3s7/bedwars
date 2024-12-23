<?php

namespace b1u3s7\bedwars\game\utils;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class Upgrade
{
    private string $name;
    private int $maxTier;
    private array $prices;
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param string $name
     * @param int $maxTier
     * @param array $prices Prices for all tiers
     * @param callable $callback
     */
    public function __construct(string $name, int $maxTier, array $prices, callable $callback)
    {
        $this->name = $name;
        $this->maxTier = $maxTier;
        $this->prices = $prices;
        $this->callback = $callback;
    }

    public function getDisplayName(int $currentTier = 0): string
    {
        if ($this->maxTier !== 1) {
            if ($this->maxTier > $currentTier) {
                return $this->name . " - Tier " . $currentTier + 1;
            } else {
                return $this->name . " - " . TextFormat::RED . TextFormat::BOLD . "MAX" . TextFormat::RESET;
            }
        } else {
            return $this->name;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getMaxTier(): int
    {
        return $this->maxTier;
    }

    public function setMaxTier(int $maxTier): void
    {
        $this->maxTier = $maxTier;
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    public function setPrices(array $prices): void
    {
        $this->prices = $prices;
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