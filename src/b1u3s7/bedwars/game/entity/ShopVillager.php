<?php

namespace b1u3s7\bedwars\game\entity;

use b1u3s7\bedwars\Bedwars;
use b1u3s7\bedwars\game\form\ShopForm;
use b1u3s7\bedwars\game\GameManager;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\Position;

class ShopVillager extends Entity
{
    protected function initEntity($nbt): void
    {
        parent::initEntity($nbt);

        $this->setNameTag("Item Shop");
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
                    $damager->sendForm(new ShopForm(GameManager::getGameByPlayer($damager)->getTeamByPlayer($damager)->getId()));
                }
            }
        }
    }
}