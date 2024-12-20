<?php

namespace b1u3s7\bedwars\game\task;

use b1u3s7\bedwars\game\utils\Game;
use pocketmine\scheduler\Task;

class GameProgressionTask extends Task
{
    private Game $game;
    private int $gameDuration;
    private int $countdown;
    private int $upgradeIron = 60; // intervals in which gen gets upgraded
    private int $upgradeGold = 200;
    private int $upgradeIronCountdown;
    private int $upgradeGoldCountdown;
    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->gameDuration = 20 * 60; // 20 min
        $this->countdown = $this->gameDuration;
        $this->upgradeIronCountdown = $this->upgradeIron;
        $this->upgradeGoldCountdown = $this->upgradeGold;
    }

    public function onRun() : void
    {
        if ($this->countdown == $this->gameDuration) {
            $this->game->broadcastMessage("The game will end in 20 minutes.");
        } else if ($this->countdown == 60) {
            $this->game->broadcastMessage("The game will end in one minute.");
        } else if ($this->countdown <= 0) {
            $this->game->broadcastMessage("The game ended!");
            $this->game->timeExpired();
        }

        if ($this->upgradeIronCountdown <= 0) {
            foreach ($this->game->getIronGenerators() as $gen) {
                $gen->changeInterval(intval($gen->getInterval() - $gen->getInterval() / 4));
            }
            $this->game->broadcastMessage("Iron generators have been upgraded!");
            $this->upgradeIronCountdown = $this->upgradeIron;
        }

        if ($this->upgradeGoldCountdown <= 0) {
            foreach ($this->game->getGoldGenerators() as $gen) {
                $gen->changeInterval(intval($gen->getInterval() - $gen->getInterval() / 4));
            }
            $this->game->broadcastMessage("Gold generators have been upgraded!");
            $this->upgradeGoldCountdown = $this->upgradeGold;
        }

        $this->upgradeIronCountdown--;
        $this->upgradeGoldCountdown--;

        $this->countdown--;
    }
}