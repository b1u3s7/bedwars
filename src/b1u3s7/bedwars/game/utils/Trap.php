<?php

namespace b1u3s7\bedwars\game\utils;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Trap
{
    private Game $game;
    private int $type;

    public function __construct(Game $game, int $trapId)
    {
        $this->game = $game;
        $this->type = $trapId;
    }

    public function trigger(Player $player, Team $trapOwnerTeam): void
    {
        $this->game->removeTrap($trapOwnerTeam->getId());
        $player->sendMessage("trap id" . $this->type);
        switch ($this->type) {
            case 0:
                $trapOwnerTeam->broadcastMessage("AlARM!! TRAP TRIGGERED!!!");
                $invis = $player->getEffects()->get(VanillaEffects::INVISIBILITY());
                if ($invis !== null) {
                    $player->getEffects()->remove(VanillaEffects::INVISIBILITY());
                    $player->sendMessage(TextFormat::DARK_RED . "You triggered a trap! Your invisibility has disappeared.");
                }
                break;
            case 1:
                $player->getEffects()->add(new EffectInstance(VanillaEffects::MINING_FATIGUE(), 15 * 20));
                break;
            case 2:
                $player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 5 * 20, 1));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 5 * 20, 1));
                break;
            case 3:

                $players = $trapOwnerTeam->getPlayers();
                foreach ($players as $player) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 10 * 20, 1));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 10 * 20, 1));
                }
                break;
        }
    }
}

