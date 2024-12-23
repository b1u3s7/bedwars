<?php

namespace b1u3s7\bedwars\game\form;

use b1u3s7\bedwars\game\utils\Game;
use b1u3s7\bedwars\game\utils\ShopHelper;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class UpgradeForm extends SimpleForm
{
    private array $items = [];
    private Game $game;

    public function __construct(Game $game, int $teamId)
    {
        $this->game = $game;
        parent::__construct([$this, "handleResponse"]);

        $this->setTitle("TeamUpgrade Shop");


        $upgrades = $game->getTeamUpgradeManager()->getUpgrades();
        $teamUpgrades = $game->getTeamUpgradeManager()->getUpgradesForTeam($teamId);

        foreach (array_keys($upgrades) as $key) {
            $this->addButton($upgrades[$key]->getDisplayName($teamUpgrades[$key]->getTier()));
        }
    }

    public function handleResponse(Player $player, $data): void
    {
        $teamUpgradeManager = $this->game->getTeamUpgradeManager();
        $upgrades = $teamUpgradeManager->getUpgrades();
        foreach (array_keys($upgrades) as $key) {
            if ($key === $data) {
                $upgrade = $upgrades[$key];
                $teamId = $this->game->getTeamByPlayer($player)->getId();

                $teamUpgradeManager->upgrade($player, $upgrade, $teamId);
            }
        }
    }
}
