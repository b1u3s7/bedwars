<?php

namespace b1u3s7\bedwars\game\item;

use b1u3s7\bedwars\game\utils\ShopHelper;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class UpgradeableTool
{
    public const PICKAXE = 0;
    public const AXE = 1;

    private int $type;
    private int $tier = -1;
    private const MAX_TIER = 3;

    private array $tierItems = [];
    private array $tierPrices = [];

    public function __construct(int $type)
    {
        $this->type = $type;
        if ($type === self::PICKAXE) {
            $this->tierItems = [VanillaItems::WOODEN_PICKAXE()->setUnbreakable(), VanillaItems::IRON_PICKAXE()->setUnbreakable(), VanillaItems::GOLDEN_PICKAXE()->setUnbreakable(), VanillaItems::DIAMOND_PICKAXE()->setUnbreakable()];
            $this->tierPrices = [VanillaItems::COPPER_INGOT()->setCount(1), VanillaItems::COPPER_INGOT()->setCount(4), VanillaItems::IRON_INGOT()->setCount(1), VanillaItems::IRON_INGOT()->setCount(4)];
        } else if ($type === self::AXE) {
            $this->tierItems = [VanillaItems::WOODEN_AXE()->setUnbreakable(), VanillaItems::IRON_AXE()->setUnbreakable(), VanillaItems::GOLDEN_AXE()->setUnbreakable(), VanillaItems::DIAMOND_AXE()->setUnbreakable()];
            $this->tierPrices = [VanillaItems::COPPER_INGOT()->setCount(1), VanillaItems::COPPER_INGOT()->setCount(4), VanillaItems::IRON_INGOT()->setCount(1), VanillaItems::IRON_INGOT()->setCount(4)];
        }
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getTier(): int
    {
        return $this->tier;
    }

    public function getNextTier(): int
    {
        return $this->tier;
    }

    public function getTierPrice($tier): Item
    {
        return $this->tierPrices[$tier];
    }

    public function getTierDisplayName(): string
    {
        if ($this->tier === self::MAX_TIER) {
            return TextFormat::RED . "MAX";
        } else {
            return TextFormat::GOLD . "Tier " . $this->tier + 2; // +2 for the next tier & that it's not 0 for the first
        }
    }

    public function canIncreaseTier(): bool
    {
        return $this->tier < self::MAX_TIER;
    }

    public function increaseTier(): void
    {
        if ($this->tier < self::MAX_TIER) {
            $this->tier++;
        }
    }

    public function decreaseTier(): void
    {
        if ($this->tier >= 1) {
            $this->tier--;
        }
    }

    public function getPrice(int $tier): Item
    {
        return $this->tierPrices[$tier];
    }

    public function getItem(): ?Item
    {
        if ($this->tier >= 0) {
            return $this->tierItems[$this->tier];
        }
        return null;
    }

    public function addItemToPlayerInv(Player $player): void
    {
        if ($this->tier >= 0) {
            $inv = $player->getInventory();
            $inv->addItem($this->tierItems[$this->tier]);
        }
    }

    public function addOrReplaceToPlayerInv(Player $player): void
    {
        // assumes that the previous tier exists in the players inventory
        $inv = $player->getInventory();
        if ($this->tier == 0) {
            $inv->addItem($this->tierItems[$this->tier]);
        } else {
            $contents = $inv->getContents();
            $index = -1;
            foreach ($contents as $key => $content) {
                if ($content instanceof $this->tierItems[$this->tier - 1]) {
                    $index = $key;
                }
            }
            if ($index !== -1) {
                ShopHelper::removeItems($player, $this->tierItems[$this->tier - 1]);
                $inv->setItem($index, $this->tierItems[$this->tier]);
            }
        }
    }
}