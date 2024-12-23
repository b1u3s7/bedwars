<?php

namespace b1u3s7\bedwars\game\utils;

use b1u3s7\bedwars\Bedwars;
use pocketmine\utils\Config;

class GameUtils
{
    public static Config $config;
    public static function init(): void
    {
        self::$config = Bedwars::getInstance()->getConfig();
    }
}