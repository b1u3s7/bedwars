<?php

namespace b1u3s7\bedwars\utils;

use pocketmine\block\utils\DyeColor;

class TeamAsColor {
    private static array $color = [
        0 => DyeColor::RED,
        1 => DyeColor::BLUE,
        2 => DyeColor::GREEN,
        3 => DyeColor::YELLOW,
        4 => DyeColor::WHITE,
        5 => DyeColor::CYAN,
        6 => DyeColor::PINK,
        7 => DyeColor::GRAY
    ];

    public static function getColor(int $teamId): DyeColor
    {
        return self::$color[$teamId];
    }
}