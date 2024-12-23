<?php

namespace b1u3s7\bedwars\game\utils;

use b1u3s7\bedwars\game\GameManager;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\player\Player;

class TeamUpgradeFunctions
{
    public static function protectionUpgrade(Player $player, TeamUpgrade $teamUpgrade): void
    {
        $team = GameManager::getGameByPlayer($player)->getTeamByPlayer($player);
        $tier = $teamUpgrade->getTier();
        if ($team !== null) {
            foreach ($team->getPlayers() as $p) {
                $armorInv = $p->getArmorInventory();
                $armorInv->setBoots($armorInv->getBoots()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), $tier)));
                $armorInv->setLeggings($armorInv->getLeggings()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), $tier)));
                $armorInv->setChestplate($armorInv->getChestplate()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), $tier)));
                $armorInv->setHelmet($armorInv->getHelmet()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), $tier)));
            }
            $team->broadcastMessage("Upgrade: Protection level increased to tier $tier!");
        }
    }

    public static function hasteUpgrade(Player $player, TeamUpgrade $teamUpgrade): void
    {
        $team = GameManager::getGameByPlayer($player)->getTeamByPlayer($player);
        $tier = $teamUpgrade->getTier();
        if ($team !== null) {
            foreach ($team->getPlayers() as $p) {
                $p->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), 999999, $tier - 1));
            }
            $team->broadcastMessage("Upgrade: Haste level increased to tier $tier!");
        }
    }

    public static function forgeUpgrade(Player $player, TeamUpgrade $teamUpgrade): void
    {
        $game = GameManager::getGameByPlayer($player);
        if ($game !== null) {
            $team = $game->getTeamByPlayer($player);
            $tier = $teamUpgrade->getTier();
            if ($team !== null) {
                $tickTask = $game->getTickTask();
                $interval = 40;
                switch ($tier) {
                    case 1:
                        $interval = 30;
                        break;
                    case 2:
                        $interval = 20;
                        break;

                }
                $tickTask->setTeamGenInterval($team->getId(), $interval);
                $team->broadcastMessage("Upgrade: Haste level increased to tier $tier!");
            }
        }
    }
}