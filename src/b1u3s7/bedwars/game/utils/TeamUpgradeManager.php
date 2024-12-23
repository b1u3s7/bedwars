<?php

namespace b1u3s7\bedwars\game\utils;

use b1u3s7\bedwars\game\utils\Game;
use b1u3s7\bedwars\game\utils\TeamUpgrade;
use b1u3s7\bedwars\game\utils\Upgrade;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TeamUpgradeManager
{
    private Game $game;
    private array $upgrades = [];
    private array $teamUpgrades;

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->teamUpgrades = array_keys($game->getTeams());

        foreach (array_keys($this->teamUpgrades) as $key) {
            $this->teamUpgrades[$key] = [];
        }
    }

    public function addUpgrade(string $name, int $maxTier, array $prices, callable $function): void
    {
        $upgrade = new Upgrade($name, $maxTier, $prices, $function);
        $this->upgrades[] = $upgrade;
        foreach (array_keys($this->teamUpgrades) as $key) {
            $this->teamUpgrades[$key][] = new TeamUpgrade($upgrade);
        }
    }

    public function getUpgrades(): array
    {
        return $this->upgrades;
    }

    public function getUpgradesForTeam(int $teamId): array
    {
        return $this->teamUpgrades[$teamId];
    }

    public function upgrade(Player $player, Upgrade $upgrade, int $teamId): void
    {
        foreach ($this->teamUpgrades[$teamId] as $teamUpgrade) {
            if ($teamUpgrade->getUpgrade() === $upgrade) {
                if ($teamUpgrade->getTier() < $teamUpgrade->getUpgrade()->getMaxTier()) {
                    $price = $upgrade->getPrices()[$teamUpgrade->getTier()];
                    if ($player->getInventory()->contains($price)) {
                        ShopHelper::removeItems($player, $price);
                        $teamUpgrade->increaseTier();
                        call_user_func($teamUpgrade->getUpgrade()->getCallback(), $player, $teamUpgrade);
                    } else {
                        $player->sendMessage(TextFormat::RED . "You don't have enough resource to buy this upgrade!");
                    }
                } else {
                    $player->sendMessage(TextFormat::RED . "Maximum level reached!");
                }
            }
        }
    }
}