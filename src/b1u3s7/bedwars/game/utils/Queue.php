<?php

namespace b1u3s7\bedwars\game\utils;

use b1u3s7\bedwars\game\GameManager;
use pocketmine\player\Player;

class Queue
{
    private string $mode;
    private int $maximumPlayers;
    private int $requiredPlayer;
    private array $players = [];
    private string $modeName;
    private int $mapId;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
        $this->maximumPlayers = GameUtils::$config->getNested("mode.$mode.team_size") * GameUtils::$config->getNested("mode.$mode.team_amount");
        $this->requiredPlayer = GameUtils::$config->getNested("mode.$mode.min_req_players");
        $this->modeName = GameUtils::$config->getNested("mode.$mode.name");
        $this->mapId = array_rand(GameUtils::$config->getNested("mode.$mode.map"));

        $this->maximumPlayers = 1;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getMapId(): int
    {
        return $this->mapId;
    }

    public function addPlayer(Player $player): void
    {
        $this->notifyPlayers($player->getNameTag() . " joined (" . count($this->players) + 1 . "/" . $this->maximumPlayers . ")");
        $this->players[] = $player;
        $player->sendMessage("You joined $this->modeName (" . count($this->players) . "/" . $this->maximumPlayers . ")");

        if (sizeof($this->players) == $this->maximumPlayers) {
            $this->notifyPlayers("Starting game");
            GameManager::fullQueue($this);
        }

        if (sizeof($this->players) >= $this->requiredPlayer) {
            // todo
            // start game in 30 secs if not starting already
        }
    }

    public function notifyPlayers(string $message): void
    {
        foreach ($this->players as $player) {
            $player->sendMessage($message);
        }
    }

    public function removePlayer(Player $player): void
    {
        $this->players = array_diff($this->players, [$player]);
    }

    public function getPlayers(): array
    {
        return $this->players;
    }
}