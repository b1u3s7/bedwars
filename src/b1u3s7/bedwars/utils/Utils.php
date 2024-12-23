<?php

namespace b1u3s7\bedwars\utils;

use pocketmine\player\Player;
use pocketmine\world\Position;

class Utils
{
    public static function isPositionInArea(Position $position, Position $areaCenter, int $radius): bool
    {
        $distance = $position->distance($areaCenter);

        return $distance <= $radius;
    }
}