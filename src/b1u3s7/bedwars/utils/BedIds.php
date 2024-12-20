<?php

namespace b1u3s7\bedwars\utils;

use pocketmine\block\utils\DyeColor;

class BedIds
{
    public static array $ids = array(
        DyeColor::RED->name => 0,
        DyeColor::BLUE->name => 1,
        DyeColor::GREEN->name => 2,
        DyeColor::YELLOW->name => 3,
        DyeColor::CYAN->name => 4,
        DyeColor::WHITE->name => 5,
        DyeColor::PINK->name => 6,
        DyeColor::PURPLE->name => 6,
        DyeColor::MAGENTA->name => 6,
        DyeColor::GRAY->name => 7,
        DyeColor::LIGHT_GRAY->name => 7
    );

    public static function getId(string $color_name): string
    {
        return self::$ids[$color_name] ?? -1;
    }
}