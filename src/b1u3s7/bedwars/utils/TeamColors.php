<?php

namespace b1u3s7\bedwars\utils;

use pocketmine\utils\TextFormat;

class TeamColors
{
    public static array $colors = array(
        0 => TextFormat::RED,
        1 => TextFormat::BLUE,
        2 => TextFormat::GREEN,
        3 => TextFormat::YELLOW,
        4 => TextFormat::WHITE,
        5 => TextFormat::AQUA,
        6 => TextFormat::LIGHT_PURPLE,
        7 => TextFormat::GREEN
    );

    public static function getColor(int $id): string
    {
        return self::$colors[$id] ?? "";
    }
}