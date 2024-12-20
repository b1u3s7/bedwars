<?php

namespace b1u3s7\bedwars\game\utils;

use pocketmine\item\Item;
use pocketmine\player\Player;

class ShopHelper
{
    public static function buyItem(Player $player, Item $purchasedItem, Item $price): void
    {
        $inv = $player->getInventory();
        $amount = 0;

        if ($inv->contains($price)) {
            foreach ($inv->getContents() as $slot => $item) {
                if ($item->getTypeId() === $price->getTypeId()) {
                    $amount += $item->getCount();
                }
            }

            $inv->remove($price);
            $inv->addItem($price->setCount($amount - $price->getCount()));
            $inv->addItem($purchasedItem);
        } else {
            $player->sendMessage("Too few resources to purchase item!");
        }
    }
}