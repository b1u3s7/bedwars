<?php

namespace b1u3s7\bedwars\game\utils;

use pocketmine\player\Player;

class Team {
    private int $id;
    private array $players = [];

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addPlayer(Player $player): void
    {
        $this->players[] = $player;
    }

    public function removePlayer(Player $player): void
    {
        foreach ($this->players as $key => $value) {
            if ($value === $player) {
                unset($this->players[$key]);
            }
        }
        $this->players = array_values($this->players);
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function broadcastMessage(string $message): void
    {
        foreach ($this->players as $player) {
            $player->sendMessage($message);
        }
    }

    public function broadcastTitle(string $title, string $subtitle = ""): void
    {
        foreach ($this->players as $player) {
            $player->sendTitle($title, $subtitle, 5, 20, 5);
        }
    }
}