<?php

namespace b1u3s7\bedwars\game\task;

use b1u3s7\bedwars\game\utils\Game;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

class GameTickTask extends Task
{
    private Game $game;
    private array $teamGens;
    private array $teamGenIntervals = [];
    private array $teamGenCountdowns = [];
    private array $ironGens;
    private int $ironGenInterval = 30 * 20;
    private int $ironGenCountdown;
    private array $goldGens;
    private int $goldGenInterval = 60 * 20;
    private int $goldGenCountdown;
    private int $playTime = 0;
    private array $respawningPlayers = [];

    public function __construct(Game $game)
    {
        $this->game = $game;

        $this->teamGens = $game->getTeamGenerators();
        $this->ironGens = $game->getIronGenerators();
        $this->goldGens = $game->getGoldGenerators();

        foreach (array_keys($game->getTeams()) as $key) {
            $this->teamGenIntervals[$key] = 2 * 20;
            $this->teamGenCountdowns[$key] = $this->teamGenIntervals[$key];
        }

        $this->ironGenCountdown = $this->ironGenInterval;
        $this->goldGenCountdown = $this->goldGenInterval;
    }

    public function setTeamGenInterval(int $teamId, int $interval): void
    {
        if (isset($this->teamGenIntervals[$teamId])) {
            $this->teamGenIntervals[$teamId] = $interval;
        }
    }

    public function addRespawningPlayer(Player $player, Position $respawnPos, array $tools, int $respawnTime): void
    {
        $this->respawningPlayers[$player->getName()] = [
            'player' => $player,
            'respawnPos' => $respawnPos,
            'tools' => $tools,
            'remainingTicks' => $respawnTime * 20,
            'remainingSeconds' => $respawnTime
        ];

        $player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), $respawnTime * 20, visible: false));
    }

    public function onRun(): void
    {
        // Info messages
        if ($this->playTime == 0) {
            $this->game->broadcastMessage("The game will end in 20 minutes.");
        } else if ($this->playTime / 20 == 20 * 60 - 60) {
            $this->game->broadcastMessage("The game will end in one minute.");
        } else if ($this->playTime / 20 >= 20 * 60) {
            $this->game->broadcastMessage("The game ended!");
            $this->game->timeExpired();
        }

        // Generator upgrades
        if ($this->playTime / 20 == 60.0) {
            $this->ironGenInterval -= $this->ironGenInterval / 4;
        }

        if ($this->playTime / 20 == 90.0) {
            $this->goldGenInterval -= $this->goldGenInterval / 4;
        }

        // Generator drops
        foreach ($this->teamGens as $team => $gen) {
            if ($this->teamGenCountdowns[$team] <= 0) {
                $gen->dropItem();
                $this->teamGenCountdowns[$team] = $this->teamGenIntervals[$team];
            }
            $this->teamGenCountdowns[$team]--;
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

        $this->ironGenCountdown--;
        $this->goldGenCountdown--;
        $this->playTime++;

        foreach ($this->respawningPlayers as $playerName => $data) {
            /** @var Player $player */
            $player = $data['player'];
            $remainingTicks = $data['remainingTicks'];
            $remainingSeconds = $data['remainingSeconds'];

            if ($remainingTicks % 20 == 0) {
                $player->sendTitle($remainingSeconds, "", 0, 10, 0);
                $this->respawningPlayers[$playerName]['remainingSeconds']--;
            }

            if ($remainingTicks <= 0) {
                $player->getEffects()->remove(VanillaEffects::BLINDNESS());
                $player->setGamemode(GameMode::SURVIVAL);
                $player->teleport($data['respawnPos']);
                $player->setHealth($player->getMaxHealth());

                $tools = $data['tools'];
                $tools[0]->decreaseTier();
                $tools[0]->addItemToPlayerInv($player);
                $tools[1]->decreaseTier();
                $tools[1]->addItemToPlayerInv($player);

                unset($this->respawningPlayers[$playerName]);
            } else {
                $this->respawningPlayers[$playerName]['remainingTicks']--;
            }
        }
    }
}