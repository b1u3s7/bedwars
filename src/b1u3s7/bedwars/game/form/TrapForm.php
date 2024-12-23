<?php

namespace b1u3s7\bedwars\game\form;

use b1u3s7\bedwars\game\utils\Game;
use b1u3s7\bedwars\game\utils\ShopHelper;
use b1u3s7\bedwars\game\utils\TrapType;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\block\Button;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TrapForm extends SimpleForm
{
    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
        parent::__construct([$this, "handleResponse"]);

        $this->setTitle("Trap Shop");

        $this->addButton("Alarm Trap");
        $this->addButton("Mining Trap");
        $this->addButton("Counter-Offensive Trap");
        $this->addButton("Blindness Trap");
    }

    public function handleResponse(Player $player, $data): void
    {
        $team = $this->game->getTeamByPlayer($player);

        if ($team !== null) {
            $teamId = $team->getId();
            if ($this->game->canTeamAddTrap($teamId)) {
                $price = count($this->game->getTrapsFromTeam($teamId)) + 1;
                $item_price = VanillaItems::IRON_INGOT()->setCount($price);
                if ($player->getInventory()->contains($item_price)) {
                    ShopHelper::removeItems($player, $item_price);
                    switch ($data) {
                        case 0:
                            $this->game->addTrap($teamId, TrapType::$ALARM_TRAP);
                            $player->sendMessage(TextFormat::GREEN . "You bought an Alarm Trap!");
                            break;
                        case 1:
                            $this->game->addTrap($teamId, TrapType::$MINING_TRAP);
                            $player->sendMessage(TextFormat::GREEN . "You bought a Mining Trap!");
                            break;
                        case 2:
                            $this->game->addTrap($teamId, TrapType::$COUNTER_TRAP);
                            $player->sendMessage(TextFormat::GREEN . "You bought a Counter-Offensive Trap!");
                            break;
                        case 3:
                            $this->game->addTrap($teamId, TrapType::$BLINDNESS_TRAP);
                            $player->sendMessage(TextFormat::GREEN . "You bought an Blindness Trap!");
                            break;
                    }
                } else {
                    $player->sendMessage(TextFormat::RED . "Not enough resources to purchase trap!");
                }
            } else {
                $player->sendMessage(TextFormat::RED . "Maximum amount of traps reached!");
            }
        }
    }
}
