<?php

namespace b1u3s7\bedwars\game\form;

use b1u3s7\bedwars\game\entity\FireballItem;
use b1u3s7\bedwars\game\GameManager;
use b1u3s7\bedwars\game\item\RescuePlatform;
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
    private array $tools = [];

    public function __construct(Player $player, int $teamId)
    {
        $this->teamId = $teamId;
        parent::__construct([$this, "handleResponse"]);
        $game = GameManager::getGameByPlayer($player);
        if ($game !== null) {
            $this->setTitle("Item Shop");

            $this->addButton("Wool");
            $this->addButton("Wood");
            $this->addButton("End Stone");
            $this->addButton("Wooden Sword");
            $this->addButton("Stone Sword");
            $this->addButton("Iron Sword");
            $this->addButton("Fireball");
            $this->addButton("TNT");
            $this->addButton("Rescue Platform");

            $tools = $game->getPlayerTools($player);
            $this->tools = $tools;

            $this->addButton("Pickaxe - " . $tools[0]->getTierDisplayName());
            $this->addButton("Axe - " . $tools[1]->getTierDisplayName());
        }
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
                ShopHelper::buyItem($player, new FireballItem(), VanillaItems::GOLD_INGOT()->setCount(1));
                break;
            case 7:
                ShopHelper::buyItem($player, VanillaBlocks::TNT()->asItem(), VanillaItems::GOLD_INGOT()->setCount(1));
                break;
            case 8:
                ShopHelper::buyItem($player, new RescuePlatform(), VanillaItems::GOLD_INGOT()->setCount(1));
                break;
            case 9:
                if ($this->tools[0]->canIncreaseTier()) {
                    $price = $this->tools[0]->getPrice($this->tools[0]->getTier() + 1);
                    if ($player->getInventory()->contains($price)) {
                        ShopHelper::removeItems($player, $price);
                        $this->tools[0]->increaseTier();
                        $this->tools[0]->addOrReplaceToPlayerInv($player);
                    } else {
                        $player->sendMessage("Too broke");
                    }
                } else {
                    $player->sendMessage("Can't increase tier");
                }
                break;
            case
            10:
                if ($this->tools[1]->canIncreaseTier()) {
                    $price = $this->tools[1]->getPrice($this->tools[1]->getTier() + 1);
                    if ($player->getInventory()->contains($price)) {
                        ShopHelper::removeItems($player, $price);
                        $this->tools[1]->increaseTier();
                        $this->tools[1]->addOrReplaceToPlayerInv($player);
                    } else {
                        $player->sendMessage("Too broke");
                    }
                } else {
                    $player->sendMessage("Can't increase tier");
                }
                break;
        }
    }
}
