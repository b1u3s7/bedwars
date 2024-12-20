<?php

namespace b1u3s7\bedwars\command;

use b1u3s7\bedwars\command\subcommand\BedwarsJoinSubcommand;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class BedwarsCommand extends BaseCommand {
    private const BASE_PERMS = "bedwars.command";

    protected function prepare(): void
    {
        $this->registerSubCommand(new BedwarsJoinSubcommand("join", "Join subcommand", ["j"]));

        $this->setPermission(self::BASE_PERMS);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $sender->sendMessage("Usage: /bedwars join");
    }

    public function getPermission(): string
    {
        return self::BASE_PERMS;
    }
}