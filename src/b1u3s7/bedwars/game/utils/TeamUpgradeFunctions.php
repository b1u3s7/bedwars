<?php

namespace b1u3s7\bedwars\game\utils;

use b1u3s7\bedwars\game\GameManager;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\player\Player;

class TeamUpgradeHelper
{
    private Game $game;

    public static function init(Game $game)
    {
        self::$game = $game;
    }
    public static function protectionUpgrade(Player $player, int $upgradeId): void
    {
        $team = GameManager::getTeamByPlayer($player);
        if ($team !== null) {
            foreach ($team->getPlayers() as $player) {
                $player->getArmorInventory()->getBoots()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
                $player->getArmorInventory()->getLeggings()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
                $player->getArmorInventory()->getChestplate()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
                $player->getArmorInventory()->getHelmet()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
            }
        }
    }
}