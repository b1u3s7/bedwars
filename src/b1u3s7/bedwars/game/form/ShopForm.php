<?php

namespace b1u3s7\bedwars\game\form;

use b1u3s7\bedwars\game\utils\ShopHelper;
use b1u3s7\bedwars\utils\TeamAsColor;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class ShopForm extends SimpleForm
{
    private int $teamId;

    private array $items = [];

    public function __construct(int $teamId)
    {
        $this->teamId = $teamId;
        parent::__construct([$this, "handleResponse"]);

        $this->setTitle("Item Shop");

        $this->addButton("Wool");
        $this->addButton("Wood");
        $this->addButton("End Stone");
        $this->addButton("Wooden Sword");
        $this->addButton("Stone Sword");
        $this->addButton("Iron Sword");
        $this->addButton("Wooden Pickaxe");
        $this->addButton("Stone Pickaxe");
        $this->addButton("Iron Pickaxe");
        $this->addButton("Wooden Axe");
        $this->addButton("Stone Axe");
        $this->addButton("Iron Axe");
    }

    public function handleResponse(Player $player, $data): void
    {
        switch ($data) {
            case 0:
                ShopHelper::buyItem($player, VanillaBlocks::WOOL()->setColor(TeamAsColor::getColor($this->teamId))->asItem()->setCount(16), VanillaItems::COPPER_INGOT()->setCount(4));
                break;
            case 1:
                ShopHelper::buyItem($player, VanillaBlocks::OAK_PLANKS()->asItem()->setCount(4), VanillaItems::IRON_INGOT()->setCount(2));
                break;
            case 2:
                ShopHelper::buyItem($player, VanillaBlocks::END_STONE()->asItem()->setCount(4), VanillaItems::GOLD_INGOT()->setCount(2));
                break;
            case 3:
                ShopHelper::buyItem($player, VanillaItems::WOODEN_SWORD(), VanillaItems::COPPER_INGOT()->setCount(4));
                break;
            case 4:
                ShopHelper::buyItem($player, VanillaItems::STONE_SWORD(), VanillaItems::IRON_INGOT()->setCount(4));
                break;
            case 5:
                ShopHelper::buyItem($player, VanillaItems::IRON_SWORD(), VanillaItems::GOLD_INGOT()->setCount(4));
                break;
            case 6:
                ShopHelper::buyItem($player, VanillaItems::WOODEN_PICKAXE(), VanillaItems::COPPER_INGOT()->setCount(4));
                break;
            case 7:
                ShopHelper::buyItem($player, VanillaItems::STONE_PICKAXE(), VanillaItems::IRON_INGOT()->setCount(4));
                break;
            case 8:
                ShopHelper::buyItem($player, VanillaItems::IRON_PICKAXE(), VanillaItems::GOLD_INGOT()->setCount(4));
                break;
            case 9:
                ShopHelper::buyItem($player, VanillaItems::WOODEN_AXE(), VanillaItems::COPPER_INGOT()->setCount(4));
                break;
            case 10:
                ShopHelper::buyItem($player, VanillaItems::STONE_AXE(), VanillaItems::IRON_INGOT()->setCount(4));
                break;
            case 11:
                ShopHelper::buyItem($player, VanillaItems::IRON_AXE(), VanillaItems::GOLD_INGOT()->setCount(4));
                break;
        }
    }
}
