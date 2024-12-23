<?php

namespace b1u3s7\bedwars\game\item;

use b1u3s7\bedwars\game\GameManager;
use b1u3s7\bedwars\game\utils\ShopHelper;
use b1u3s7\bedwars\utils\TeamAsColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\GoatHornType;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\GoatHornSound;

class RescuePlatform extends Item
{
    public function __construct(int $meta = 0)
    {
        parent::__construct(new ItemIdentifier(VanillaItems::BLAZE_ROD()->getTypeId()), $meta);
        $this->setCustomName("Rescue Platform");
        $this->setLore(["Deploys a rescue platform below you.", "You will still take fall damage!"]);
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $game = GameManager::getGameByPlayer($player);
        if ($game !== null) {
            $team = $game->getTeamByPlayer($player);
            if ($team !== null) {
                ShopHelper::removeItems($player, $this->setCount(1));

                $position = $player->getPosition();
                $world = $player->getWorld();
                $position->y -= 4;

                $platformSize = 3;
                $halfSize = (int)floor($platformSize / 2);

                for ($xOffset = -$halfSize; $xOffset <= $halfSize; $xOffset++) {
                    for ($zOffset = -$halfSize; $zOffset <= $halfSize; $zOffset++) {
                        $x = $position->getX() + $xOffset;
                        $y = $position->getY();
                        $z = $position->getZ() + $zOffset;
                        $blockPosition = new Vector3($x, $y, $z);

                        $block = VanillaBlocks::WOOL()->setColor(TeamAsColor::getColor($team->getId()));
                        $world->setBlock($blockPosition, $block);
                    }
                }
                $player->sendMessage(TextFormat::GREEN . "Rescue Platform has been deployed.");
            }
        }

        return ItemUseResult::SUCCESS;
    }
}