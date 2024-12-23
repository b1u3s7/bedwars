<?php

namespace b1u3s7\bedwars\game\task;

use b1u3s7\bedwars\game\utils\Game;
use pocketmine\scheduler\Task;

class GameTickTask extends Task {
    private Game $game;
    private array $teamGens;
    private int $teamGenInterval = 20;
    private int $teamGenCountdown;
    private array $ironGens;
    private int $ironGenInterval = 30 * 20;
    private int $ironGenCountdown;
    private array $goldGens;
    private int $goldGenInterval = 60 * 20;
    private int $goldGenCountdown;
    private int $playTime = 0;

    public function __construct(Game $game)
    {
        $this->game = $game;

        $this->teamGens = $game->getTeamGenerators();
        $this->ironGens = $game->getIronGenerators();
        $this->goldGens = $game->getGoldGenerators();

        $this->teamGenCountdown = $this->teamGenInterval;
        $this->ironGenCountdown = $this->ironGenInterval;
        $this->goldGenCountdown = $this->goldGenInterval;
    }

    public function onRun() : void {
        // info messages
        if ($this->playTime == 0) {
            $this->game->broadcastMessage("The game will end in 20 minutes.");
        } else if ($this->playTime / 20 == 20 * 60 - 60) {
            $this->game->broadcastMessage("The game will end in one minute.");
        } else if ($this->playTime / 20 <= 20 * 60) {
            $this->game->broadcastMessage("The game ended!");
            $this->game->timeExpired();
        }

        // generator upgrades
        if ($this->playTime / 20 == 60.0) {
            $this->ironGenInterval -= $this->ironGenInterval / 4;
        }

        if ($this->playTime / 20 == 90.0) {
            $this->goldGenInterval -= $this->goldGenInterval / 4;
        }
        // etc

        // generator drops
        if ($this->teamGenCountdown <= 0) {
            foreach ($this->teamGens as $gen) {
                $gen->dropItem();
            }

            $this->teamGenCountdown = $this->teamGenInterval;

        }

        if ($this->ironGenCountdown <= 0) {
            foreach ($this->ironGens as $gen) {
                $gen->dropItem();
            }

            $this->ironGenCountdown = $this->ironGenInterval;
        }

        if ($this->goldGenCountdown <= 0) {
            foreach ($this->goldGens as $gen) {
                $gen->dropItem();
            }

            $this->goldGenCountdown = $this->goldGenInterval;
        }

        $this->teamGenCountdown--;
        $this->ironGenCountdown--;
        $this->goldGenCountdown--;
        $this->playTime++;
    }
}