<?php

namespace b1u3s7\bedwars\game\entity;

use b1u3s7\bedwars\game\form\UpgradeForm;
use b1u3s7\bedwars\game\GameManager;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\GameMode;
use pocketmine\player\Player;


class UpgradeVillager extends Entity
{
    protected function initEntity($nbt): void
    {
        parent::initEntity($nbt);

        $this->setNameTag("Upgrade Shop");
        $this->setNameTagAlwaysVisible();
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.8, 0.6);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0;
    }

    protected function getInitialGravity(): float
    {
        return 0;
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::VILLAGER;
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                if (GameManager::getGameByPlayer($damager) != null && $damager->getGamemode() == GameMode::SURVIVAL)
                {
                    $game = GameManager::getGameByPlayer($damager);
                    $damager->sendForm(new UpgradeForm($game, $game->getTeamByPlayer($damager)->getId()));
                }
            }
        }
    }
}