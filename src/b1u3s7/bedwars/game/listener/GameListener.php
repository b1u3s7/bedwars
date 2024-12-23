<?php

namespace b1u3s7\bedwars\game\listener;

use b1u3s7\bedwars\game\GameManager;
use b1u3s7\bedwars\utils\BedIds;
use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Planks;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wool;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\Player;

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
        if ($player->getPosition()->getY() <= 10) {
            $game = GameManager::getGameByPlayer($player);
            if ($game != null) {
                $game->killPlayer($player);
            }
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $attacker = $event->getDamager();
        $entity = $event->getEntity();

        if ($attacker instanceof Player && $entity instanceof Player) {
            $attackerTeam = GameManager::getGameByPlayer($attacker)->getTeamByPlayer($attacker);
            if ($attackerTeam != null) {
                if (in_array($entity, $attackerTeam->getPlayers())) {
                    $event->cancel();
                }
            }
        }
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