<?php

namespace b1u3s7\bedwars\utils;

use pocketmine\Server;
use pocketmine\world\WorldManager;

class WorldUtils
{
    private static WorldManager $wm;
    private static string $worldDir;

    public static function init(): void
    {
        self::$wm = Server::getInstance()->getWorldManager();
        self::$worldDir = Server::getInstance()->getDataPath() . "worlds/";
    }

    /**
     * Loads world by its folder name.
     * Returns null if successful or string with error message.
     * @param string $worldName Folder name of world
     * @return null|string
     */
    public static function loadWorld(string $worldName): ?string
    {
        if (is_dir(self::$worldDir . $worldName)) {
            if (self::$wm->getWorldByName($worldName) != null) {
                if (self::$wm->getWorldByName($worldName)->isLoaded()) {
                    return null;
                }
            }

            if (self::$wm->loadWorld($worldName)) {
                return null;
            } else {
                return "Could not load world";
            }
        } else {
            return "World not found";
        }
    }

    /**
     * Unloads world by its folder name.
     * @param string $worldName Folder name of world
     * @return string|null
     */
    public static function unloadWorld(string $worldName): ?string
    {
        if (self::$wm->getWorldByName($worldName) != null) {
            $world = self::$wm->getWorldByName($worldName);
            if (!$world->isLoaded()) {
                return null;
            }

            if (self::$wm->unloadWorld($world)) {
                return null;
            } else {
                return "Could not unload world";
            }
        } else {
            return "World not found";
        }
    }

    /**
     * Deletes world
     * @param string $worldName
     * @return string|null
     */
    public static function deleteWorld(string $worldName): ?string
    {
        if (self::$wm->getWorldByName($worldName) != null) {
            $world = self::$wm->getWorldByName($worldName);

            if ($world->getId() == self::$wm->getDefaultWorld()->getId()) {
                return "Can not delete default world";
            }

            if ($world->getPlayers() != []) {
                return "Can not delete world with players in it";
            }

            if ($world->isLoaded()) {
                self::$wm->unloadWorld($world);
            }

            $worldPath = self::$worldDir . $worldName;

            if (self::recurse_delete($worldPath)) {
                return null;
            } else {
                return "Could not delete world";
            }
        } else {
            return "World not found";
        }
    }

    /**
     * Duplicates world
     * @param string $originWorldName
     * @param string $duplicateWorldName
     * @return string|null
     */
    public static function duplicateWorld(string $originWorldName, string $duplicateWorldName): ?string
    {
        if (is_dir(self::$worldDir . $originWorldName)) {
            if (self::$wm->getWorldByName($originWorldName) != null) {
                if (self::$wm->getWorldByName($originWorldName)->isLoaded()) {
                    self::unloadWorld($originWorldName);
                }
            }
        } else {
            return "World not found";
        }

        if (is_dir(Server::getInstance()->getDataPath() . "/worlds/$duplicateWorldName")) {
            return "World with name '" . $duplicateWorldName . "' already exists.";
        }

        self::recurse_copy(self::$worldDir . $originWorldName, self::$worldDir . $duplicateWorldName);
        return null;
    }

    private static function recurse_delete($folderPath): bool
    {
        if (!is_dir($folderPath)) {
            return false;
        }

        $items = array_diff(scandir($folderPath), ['.', '..']);

        foreach ($items as $item) {
            $itemPath = $folderPath . DIRECTORY_SEPARATOR . $item;

            if (is_dir($itemPath)) {
                self::recurse_delete($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        return rmdir($folderPath);
    }

    private static function recurse_copy($src, $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}