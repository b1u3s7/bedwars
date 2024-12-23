<?php

namespace b1u3s7\bedwars\game\task;

use b1u3s7\bedwars\game\utils\Game;
use b1u3s7\bedwars\game\utils\Team;
use b1u3s7\bedwars\utils\TeamAsColor;
use b1u3s7\bedwars\utils\WorldUtils;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;

class GameEndTask extends Task
{
    private Game $game;
    private Team $team;
    private int $counter;

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->team = $this->game->getTeams()[0];
        $this->counter = 10;
    }

    public function onRun(): void
    {
        if ($this->counter == 10) {
            $this->game->broadcastMessage(TeamAsColor::getColor($this->team->getId())->name . " won the game!");
            $this->game->broadcastMessage("Leaving in " . $this->counter . " seconds.");
        } else if ($this->counter <= 5 && $this->counter > 0) {
            $this->game->broadcastMessage("Leaving in " . $this->counter . " seconds.");
        } else if ($this->counter == 0) {
            $this->game->broadcastMessage("Leaving...");

            $default_world = Server::getInstance()->getWorldManager()->getDefaultWorld();
            $spawn_vector = $default_world->getSpawnLocation()->asVector3();
            foreach ($this->game->getPlayers() as $player) {
                $player->teleport(new Position($spawn_vector->x, $spawn_vector->y, $spawn_vector->z, $default_world));
            }

            WorldUtils::unloadWorld($this->game->world->getFolderName());
            WorldUtils::deleteWorld($this->game->world->getFolderName());

            $this->getHandler()->cancel();
        }

        $this->counter--;
    }
}