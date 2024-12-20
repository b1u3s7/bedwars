<?php

namespace b1u3s7\bedwars\game\utils;

use b1u3s7\bedwars\Bedwars;
use b1u3s7\bedwars\game\entity\ShopVillager;
use b1u3s7\bedwars\game\GameManager;
use b1u3s7\bedwars\game\task\GameEndTask;
use b1u3s7\bedwars\game\task\GameProgressionTask;
use b1u3s7\bedwars\game\task\GeneratorTask;
use b1u3s7\bedwars\game\task\RespawnTask;
use b1u3s7\bedwars\utils\BedIds;
use b1u3s7\bedwars\utils\TeamColors;
use b1u3s7\bedwars\utils\WorldUtils;
use OverflowException;
use pocketmine\block\Bed;
use pocketmine\data\bedrock\EffectIds;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\EffectManager;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;

class Game
{
    private string $mode;
    private int $mapId;
    private int $teamAmount;
    private int $teamSize;
    public World $world;
    public World $world_empty;
    private array $players = [];
    private array $teams = [];
    private array $shops = [];
    private array $teamGens = [];
    private array $ironGens = [];
    private array $goldGens = [];
    private array $beds = [];
    private array $teamSpawns = [];
    private TaskHandler $progressionTask;

    public function __construct(string $mode, array $players, int $mapId)
    {
        $this->mode = $mode;
        $this->mapId = $mapId;
        $this->teamAmount = GameUtils::$config->getNested("mode.$mode.team_amount");
        $this->teamSize = GameUtils::$config->getNested("mode.$mode.team_size");
        $this->players = $players;

        $this->setupWorld(GameUtils::$config->getNested("mode.$mode.map.$mapId.world"));
        $this->setupShops();
        $this->setupGenerators();
        $this->progressionTask = Bedwars::getInstance()->getScheduler()->scheduleRepeatingTask(new GameProgressionTask($this), 20);

        $this->prepPlayers();
    }

    private function setupWorld($worldName): void
    {
        $game_world_name = $this->mode . time();
        $game_world_name_empty = $game_world_name . "-empty";
        WorldUtils::duplicateWorld($worldName, $game_world_name);
        WorldUtils::duplicateWorld($worldName, $game_world_name_empty);
        WorldUtils::loadWorld($game_world_name);
        WorldUtils::loadWorld($game_world_name_empty);

        $this->world = Server::getInstance()->getWorldManager()->getWorldByName($game_world_name);
        $this->world_empty = Server::getInstance()->getWorldManager()->getWorldByName($game_world_name_empty);
    }

    private function setupShops(): void
    {
        $teams = array_keys(GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team"));

        foreach ($teams as $team) {
            $x = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$team.shop.x");
            $y = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$team.shop.y");
            $z = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$team.shop.z");
            $shop = new ShopVillager(new Location($x, $y, $z, $this->world, 0, 0));
            $this->spawnEntity($shop);
            $this->shops[] = $shop;
        }
    }

    private function spawnEntity(Entity $entity): void
    {
        $pos = $entity->getPosition()->floor();
        $this->world->requestChunkPopulation($pos->getX() >> Chunk::COORD_BIT_SIZE, $pos->getZ() >> Chunk::COORD_BIT_SIZE, null)->onCompletion(
            function () use ($entity) {
                $entity->spawnToAll();
            },
            function () {
            }
        );
    }

    private function setupGenerators(): void
    {
        // team gens
        $teams = array_keys(GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team"));
        foreach ($teams as $team) {
            $x = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$team.gen.x");
            $y = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$team.gen.y");
            $z = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$team.gen.z");
            $this->teamGens[] = new GeneratorTask(VanillaItems::COPPER_INGOT(), 1, new Position($x, $y, $z, $this->world));
        }

        // iron gens
        $iron_gens = array_keys(GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.iron"));
        foreach ($iron_gens as $gen) {
            $x = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.iron.$gen.x");
            $y = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.iron.$gen.y");
            $z = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.iron.$gen.z");
            $this->ironGens[] = new GeneratorTask(VanillaItems::IRON_INGOT(), 10, new Position($x, $y, $z, $this->world));
        }

        // gold gens
        $gold_gens = array_keys(GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.gold"));
        foreach ($gold_gens as $gen) {
            $x = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.gold.$gen.x");
            $y = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.gold.$gen.y");
            $z = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".gen.gold.$gen.z");
            $this->goldGens[] = new GeneratorTask(VanillaItems::GOLD_INGOT(), 10, new Position($x, $y, $z, $this->world));
        }
    }

    public function getIronGenerators(): array
    {
        return $this->ironGens;
    }

    public function getGoldGenerators(): array
    {
        return $this->goldGens;
    }

    private function prepPlayers(): void
    {
        $this->teams = $this->distributePlayersIntoTeams($this->players, $this->teamAmount, $this->teamSize);

        foreach ($this->teams as $team) {
            $teamId = $team->getId();
            $x = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$teamId.spawn.x");
            $y = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$teamId.spawn.y");
            $z = GameUtils::$config->getNested("mode.$this->mode.map." . $this->mapId . ".team.$teamId.spawn.z");
            $this->teamSpawns[$teamId] = new Position($x, $y, $z, $this->world);
            foreach ($team->getPlayers() as $playerIndex => $player) {
                $player->getInventory()->clearAll();
                $player->setNameTag(TeamColors::getColor($teamId) . $player->getNameTag() . TextFormat::RESET);
                $player->teleport(new Position($x, $y, $z, $this->world));
                $player->setGamemode(GameMode::SURVIVAL);
            }
            $this->beds[$teamId] = true;
        }
    }

    private function distributePlayersIntoTeams($players, $maxTeams, $teamSize): array
    {
        $teams = [];
        for ($i = 0; $i < $maxTeams; $i++) {
            $teams[] = new Team($i);
        }

        $teamIndex = 0;

        foreach ($players as $player) {
            $currentTeam = $teams[$teamIndex];

            $currentTeam->addPlayer($player);

            if (count($currentTeam->getPlayers()) >= $teamSize) {
                $teamIndex++;
                if ($teamIndex >= $maxTeams) {
                    throw new OverflowException("Not enough teams to accommodate all players.");
                }
            }
        }

        return $teams;
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function getTeams(): array
    {
        return $this->teams;
    }

    public function getTeamById(int $id): ?Team
    {
        foreach ($this->teams as $team) {
            if ($team->getId() == $id) {
                return $team;
            }
        }
        return null;
    }

    public function killPlayer(Player $player): void
    {
        if (in_array($player, $this->players)) {
            $player->setGamemode(GameMode::SPECTATOR);
            $player->getInventory()->clearAll();
            if ($this->canPlayerRespawn($player)) {
                $this->respawnPlayer($player);
            } else {
                $player->sendTitle("Eliminated");
                $team = GameManager::getTeamByPlayer($player);
                if ($team !== null) {
                    $team->removePlayer($player);
                    $this->broadcastMessage($player->getNameTag() . " was eliminated.");
                    if (empty($team->getPlayers())) {
                        $this->eliminateTeam($team);
                    }
                }
            }
        }
    }

    public function bedBroken(Bed $bed, Player $player): void
    {
        $id = BedIds::getId($bed->getColor()->name);
        $this->beds[$id] = false;
        $this->broadcastMessage($bed->getColor()->name . " bed got destroyed by " . $player->getNameTag());
        $team = $this->getTeamById($id);
        $team->broadcastTitle("Bed Destroyed");
        if (empty($team->getPlayers())) {
            $this->eliminateTeam($team);
        }
    }

    public function eliminateTeam(Team $teamToEliminate): void
    {
        foreach ($this->teams as $key => $object) {
            if ($object->getId() == $teamToEliminate->getId()) {
                unset($this->teams[$key]);
                break;
            }
        }
        $this->teams = array_values($this->teams);
        $this->checkForWinner();
    }

    public function checkForWinner(): void
    {
        if (count($this->teams) == 1) {
            $winner = $this->teams[0];
            $winner->broadcastTitle(TextFormat::GOLD . TextFormat::BOLD . "Victory!");

            $this->endGame();
        }
    }

    public function timeExpired(): void
    {
        foreach ($this->teams as $team) {
            $team->broadcastTitle("Tie");
        }

        $this->endGame();
    }

    public function endGame(): void
    {
        foreach ($this->teams as $team) {
            foreach ($team->getPlayers() as $player) {
                $player->setGamemode(GameMode::SPECTATOR);
                $player->getInventory()->clearAll();
            }
        }

        $this->progressionTask->cancel();

        foreach ($this->teamGens as $gen) {
            $gen->cancel();
        }

        foreach ($this->ironGens as $gen) {
            $gen->cancel();
        }

        foreach ($this->goldGens as $gen) {
            $gen->cancel();
        }

        Bedwars::getInstance()->getScheduler()->scheduleRepeatingTask(new GameEndTask($this), 20);
    }

    public function canPlayerRespawn(Player $player): bool
    {
        if (in_array($player, $this->players)) {
            $team = GameManager::getTeamByPlayer($player);
            if ($this->beds[$team->getId()]) {
                return true;
            }
        }
        return false;
    }

    public function respawnPlayer(Player $player): void
    {
        if (in_array($player, $this->players)) {
            $team = GameManager::getTeamByPlayer($player);
            $respawn_task = new RespawnTask($player, $this->teamSpawns[$team->getId()]);
            Bedwars::getInstance()->getScheduler()->scheduleRepeatingTask($respawn_task, 20);
        }
    }

    public function removePlayer(Player $player): void
    {
        $team = GameManager::getTeamByPlayer($player);

        if ($team !== null) {
            if (in_array($team, $this->teams)) {
                foreach ($this->players as $key => $value) {
                    if ($value === $player) {
                        unset($this->players[$key]);
                    }
                }
                $this->players = array_values($this->players);

                $team->removePlayer($player);
                if (empty($team->getPlayers())) {
                    $this->eliminateTeam($team);
                }
            }

        }
    }

    public function broadcastMessage(string $message): void
    {
        foreach ($this->players as $player) {
            $player->sendMessage($message);
        }
    }
}