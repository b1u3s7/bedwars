<?php

namespace b1u3s7\bedwars\game\entity;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ProjectileItem;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FireballItem extends ProjectileItem
{
    public function __construct()
    {
        parent::__construct(new ItemIdentifier(ItemTypeIds::FIRE_CHARGE), "FireballItem");

        $this->setCustomName(TextFormat::RED . "FireballItem");
    }

    public function getThrowForce(): float
    {
        return 2;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new Fireball($location, $thrower);
    }
}