<?php

namespace b1u3s7\bedwars;

use b1u3s7\bedwars\command\BedwarsCommand;
use b1u3s7\bedwars\game\entity\ShopVillager;
use b1u3s7\bedwars\game\GameManager;
use b1u3s7\bedwars\game\listener\GameListener;
use b1u3s7\bedwars\game\utils\GameUtils;
use b1u3s7\bedwars\utils\WorldUtils;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

class Bedwars extends PluginBase
{
    private static Bedwars $instance;

    protected function onLoad(): void
    {
        Bedwars::$instance = $this;
        WorldUtils::init();
        GameUtils::init();
        GameManager::init();
    }

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new GameListener($this), $this);

        EntityFactory::getInstance()->register(ShopVillager::class, function (World $world, CompoundTag $nbt): ShopVillager {
            return new ShopVillager(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['ShopVillager']);

        $commands = [
            "bedwars" => new BedwarsCommand($this, "bedwars", "Bedwars commands", ["bw"]),
        ];

        foreach ($commands as $command) {
            $this->getServer()->getCommandMap()->register("Bedwars", $command);
        }

        $this->getLogger()->info(TextFormat::GREEN . "Bedwars enabled.");
    }

    protected function onDisable(): void
    {
        $this->getLogger()->info(TextFormat::RED . "Bedwars disabled.");
    }

    public static function getInstance(): Bedwars
    {
        return Bedwars::$instance;
    }
}