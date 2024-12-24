<?php

namespace b1u3s7\bedwars\game\utils;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ShopHelper
{
    public static function buyItem(Player $player, Item $purchasedItem, Item $price): void
    {
        $inv = $player->getInventory();

        if (self::transaction($player, $price)) {
            $inv->addItem($purchasedItem);
        }
    }

    public static function transaction(Player $player, Item $price): bool
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
            return true;
        } else {
            $player->sendMessage(TextFormat::RED . "Too few resources to complete transaction!");
            return false;
        }
    }

    private static function getMaterialIndex(Item $material, array $armorMaterials): int
    {
        foreach ($armorMaterials as $index => $armorMaterial) {
            if ($armorMaterial->equals($material)) {
                return $index;
            }
        }
        return -1; // if material not found
    }

    private static function isWearingBetterArmor(Player $player, Item $material, array $armorMaterials): bool
    {
        $materialIndex = self::getMaterialIndex($material, $armorMaterials);
        if ($materialIndex === -1) {
            return false; // material is not in armorMaterials array
        }

        $armorInventory = $player->getArmorInventory();
        $leggings = $armorInventory->getLeggings();

        if ($leggings !== null) {
            $leggingsIndex = self::getMaterialIndex($leggings, $armorMaterials);
            if ($leggingsIndex >= $materialIndex) {
                return true;
            }
        }
        return false;
    }

    public static function buyArmor(Player $player, Item $leggings, Item $boots, Item $price): void
    {
        $armorMaterials = [VanillaItems::LEATHER_PANTS(), VanillaItems::CHAINMAIL_LEGGINGS(), VanillaItems::IRON_LEGGINGS(), VanillaItems::DIAMOND_LEGGINGS()];

        if (!self::isWearingBetterArmor($player, $leggings, $armorMaterials)) {
            if (self::transaction($player, $price)) {
                $inv = $player->getArmorInventory();
                $inv->setLeggings($leggings);
                $inv->setBoots($boots);
            }
        } else {
            $player->sendMessage("You can't buy this item!");
        }
    }

    public static function removeItems(Player $player, Item $price): void
    {
        $inv = $player->getInventory();
        $amount = 0;

        foreach ($inv->getContents() as $slot => $item) {
            if ($item->getTypeId() === $price->getTypeId()) {
                $amount += $item->getCount();
            }
        }

        $inv->remove($price);
        $inv->addItem($price->setCount($amount - $price->getCount()));
    }
}