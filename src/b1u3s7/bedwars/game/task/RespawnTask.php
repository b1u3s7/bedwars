<?php

namespace b1u3s7\bedwars\game\task;

use b1u3s7\bedwars\Bedwars;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

class RespawnTask extends Task
{
    private Player $player;
    private Position $respawnPos;
    private int $counter = 3;

    public function __construct($player, $respawnPos)
    {
        $this->player = $player;
        $this->respawnPos = $respawnPos;
        $this->player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), duration: 200, visible: false));
    }

    public function onRun(): void
    {
        $this->player->sendTitle($this->counter, "", 0, 10, 0);

        if ($this->counter <= 0) {
            $this->player->getEffects()->remove(VanillaEffects::BLINDNESS());
            $this->player->setGamemode(GameMode::SURVIVAL);
            $this->player->teleport($this->respawnPos);
            $this->getHandler()->cancel();
        }

        $this->counter--;
    }
}