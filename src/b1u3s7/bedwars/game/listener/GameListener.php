<?php

namespace b1u3s7\bedwars\game\listener;

use b1u3s7\bedwars\game\GameManager;
use b1u3s7\bedwars\utils\BedIds;
use b1u3s7\bedwars\utils\Utils;
use pocketmine\block\Bed;
use pocketmine\block\Planks;
use pocketmine\block\TNT;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wool;
use pocketmine\entity\Location;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class GameListener implements Listener
{
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();

        $game = GameManager::getGameByPlayer($player);
        if ($game != null) {
            if ($block instanceof Bed) {
                if ($game->getTeamByPlayer($player)->getId() == BedIds::getId($block->getColor()->name)) {
                    $event->cancel();
                    $player->sendMessage("You can not break your own bed!");
                    return;
                }
                $event->setDrops([]);
                $game->bedBroken($block, $player);
                return;
            }
            if (!$block instanceof Wool && !$block instanceof Planks && $block->getTypeId() != VanillaBlocks::END_STONE()->getTypeId()) {
                $event->cancel();
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $transaction = $event->getTransaction();
        $blocks = $transaction->getBlocks();

        $player = $event->getPlayer();
        $game = GameManager::getGameByPlayer($player);

        if ($game != null) {
            foreach ($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]) {
                if ($block instanceof TNT) {
                    $position = $block->getPosition();
                    $world = $position->getWorld();
                    $primedTNT = new PrimedTNT(new Location($position->getX() + 0.5, $position->getY() + 0.5, $position->getZ() + 0.5, $world, 0, 0));
                    $primedTNT->setFuse(50);
                    $primedTNT->spawnToAll();
                    $event->getTransaction()->addBlockAt($position->getX(), $position->getY(), $position->getZ(), VanillaBlocks::AIR());
                } else if ($game->isPositionInProtectedArea($block->getPosition())) {
                    $event->cancel();
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You can not place blocks in this area!" . TextFormat::RESET);
                }
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $packet = new GameRulesChangedPacket();
        $packet->gameRules = [
            "ShowCoordinates" => new BoolGameRule(true, false)
        ];
        $player->getNetworkSession()->sendDataPacket($packet);
    }

    public function onLeave(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $queue = GameManager::getQueueByPlayer($player);
        if ($queue != null) {
            $queue->removePlayer($player);
        }
        $game = GameManager::getGameByPlayer($player);
        if ($game != null) {
            $game->removePlayer($player);
        }
    }

    public function onBedEnter(PlayerBedEnterEvent $event): void
    {
        $game = GameManager::getGameByPlayer($event->getPlayer());
        if ($game != null) {
            $event->cancel();
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $game = GameManager::getGameByPlayer($player);

        if ($game != null) {
            $team = $game->getTeamByPlayer($player);
            if ($team != null) {
                $teamId = $team->getId();
                $position = $player->getPosition();

                // if player in void
                if ($position->getY() <= 10 && $player->getGamemode() == GameMode::SURVIVAL) {
                    $game = GameManager::getGameByPlayer($player);
                    $game?->killPlayer($player);
                }

                // traps trigger
                $teamSpawns = $game->getTeamSpawns();
                foreach (array_keys($teamSpawns) as $key) {
                    if (!$key == $team->getId()) {
                        if (Utils::isPositionInArea($position, $teamSpawns[$key], $game->getTrapTriggerRadius())) {
                            $traps = $game->getTrapsFromTeam($key);
                            if (count($traps) > 0) {
                                $trap = $traps[0];
                                $trap->trigger($player, $game->getTeamById($key));
                            }
                        }
                    }
                }
            }
        }
    }

    public function onEntityMove(EntityMotionEvent $event): void
    {
        $event->cancel();
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $damager = $event->getDamager();
        $entity = $event->getEntity();

        if ($damager instanceof Player && $entity instanceof Player) {
            $game = GameManager::getGameByPlayer($damager);
            if ($game != null) {
                $attackerTeam = $game->getTeamByPlayer($damager);
                if ($attackerTeam != null) {
                    if (in_array($entity, $attackerTeam->getPlayers())) {
                        $event->cancel();
                    }
                }
            }
        }
    }

    public function onExplosion(EntityExplodeEvent $event): void
    {
        $blockList = [];
        foreach ($event->getBlockList() as $block) {
            if (!$block instanceof Wool && !$block instanceof Planks && $block->getTypeId() != VanillaBlocks::END_STONE()->getTypeId()) {
                continue;
            }
            $blockList[] = $block;
        }
        $event->setBlockList($blockList);
        $event->setYield(0);
    }

    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if ($player->getHealth() - $event->getFinalDamage() <= 0) {
                $game = GameManager::getGameByPlayer($player);
                if ($game != null) {
                    $event->cancel();
                    $game->killPlayer($player);
                }
            }
        }
    }
}