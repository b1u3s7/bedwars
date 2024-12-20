<?php

namespace b1u3s7\bedwars\game;

use b1u3s7\bedwars\Bedwars;
use b1u3s7\bedwars\game\utils\Game;
use b1u3s7\bedwars\game\utils\GameUtils;
use b1u3s7\bedwars\game\utils\Queue;
use b1u3s7\bedwars\game\utils\Team;
use pocketmine\player\Player;

class GameManager
{
    private static array $games = [];
    private static array $queues = [];
    private static array $modes = [];

    public static function init(): void
    {
        foreach (array_keys(GameUtils::$config->get("mode")) as $key) {
            self::$modes[] = $key;
        }
    }

    public static function getGameByPlayer(Player $player): ?Game
    {
        foreach (self::$games as $game) {
            if (in_array($player, $game->getPlayers())) {
                return $game;
            }
        }
        return null;
    }

    public static function getTeamByPlayer(Player $player): ?Team
    {
        $game = self::getGameByPlayer($player);
        if ($game !== null) {
            foreach ($game->getTeams() as $team) {
                if (in_array($player, $team->getPlayers())) {
                    return $team;
                }
            }
        }
        return null;
    }

    public static function getQueueByPlayer(Player $player): ?Queue
    {
        foreach (self::$queues as $queue) {
            if (in_array($player, $queue->getPlayers())) {
                return $queue;
            }
        }
        return null;
    }

    public static function newQueue(string $mode): Queue
    {
        self::$queues[] = new Queue($mode, 2);
        return self::$queues[count(self::$queues) - 1];
    }

    public static function removeQueue(Queue $queue): void
    {
        $filteredQueues = array_filter(self::$queues, function ($object) use ($queue) {
            return $object !== $queue;
        });

        self::$queues = array_values($filteredQueues);
    }

    public static function getOpenQueue(string $mode): Queue
    {
        foreach (self::$queues as $queue) {
            if ($queue->getMode() === $mode) {
                return $queue;
            }
        }
        return self::newQueue($mode);
    }

    public static function fullQueue(Queue $queue): void
    {
        self::removeQueue($queue);
        self::newGame($queue->getMode(), $queue->getPlayers(), $queue->getMapId());
    }

    public static function newGame(string $mode, array $players, int $mapId): void
    {
        self::$games[] = new Game($mode, $players, $mapId);
    }

    public static function addPlayer(Player $player, string $mode): ?string
    {
        if (in_array($mode, self::$modes)) {
            self::getOpenQueue($mode)->addPlayer($player);
            return null;
        }
        return "Unknown mode. Modes: " . implode(", ", self::$modes);
    }
}