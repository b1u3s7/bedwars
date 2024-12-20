<?php

namespace b1u3s7\bedwars\command\subcommand;

use b1u3s7\bedwars\Bedwars;
use b1u3s7\bedwars\game\GameManager;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;

class BedwarsJoinSubcommand extends BaseSubcommand {

    protected function prepare(): void
    {
        $this->setPermission("bedwars.command.join");
        $this->registerArgument(0, new RawStringArgument("mode", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $result = GameManager::addPlayer($sender, $args["mode"]);
        if ($result != null) {
            $sender->sendMessage($result);
        }
    }
}