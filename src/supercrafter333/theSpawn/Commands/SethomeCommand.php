<?php

namespace supercrafter333\theSpawn\Commands;

use JsonException;
use pocketmine\world\sound\AnvilFallSound;
use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\DoorBumpSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\Forms\HomeForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use function count;
use function implode;

/**
 * Class SethomeCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SethomeCommand extends theSpawnOwnedCommand
{



    /**
     * DelhomeCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.sethome.cmd");
        parent::__construct("sethome", "Set a new home!", "§4Use: §r/sethome <name>", ["addhome"]);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     * @throws JsonException
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $pl = theSpawn::getInstance();

        if (!$this->canUse($s)) return;

        if (count($args) < 1) {
            if ($pl->useForms()) {
                $warpForms = new HomeForms($s->getName());
                $warpForms->openSetHome($s);
            } else {
                $s->sendMessage($this->usageMessage);
            }
            return;
        }

        self::simpleExecute($s, $args);
    }

    /**
     * @throws JsonException
     */
    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.sethome.cmd", "sethome")) return;

        $x = $s->getPosition()->getX();
        $y = $s->getPosition()->getY();
        $z = $s->getPosition()->getZ();
        $level = $s->getWorld();
        $yaw = $s->getLocation()->getYaw();
        $pitch = $s->getLocation()->getPitch();
        $homes = $pl->getHomesOfPlayer($s) !== null ? count($pl->getHomesOfPlayer($s), COUNT_RECURSIVE) : 0;
        if (($maxHomes = $pl->getMaxHomesOfPlayer($s)) <= $homes) {
            $s->sendMessage(MsgMgr::getMsg("highest-home-count-reached", ["{max-homes}" => $maxHomes]));
            $s->getWorld()->addSound($s->getPosition(), new AnvilFallSound());
            return;
        }
        if ($pl->setHome($s, implode(" ", $args), $x, $y, $z, $level, $yaw, $pitch) == false) {
            $s->sendMessage($prefix . str_replace(["{home}"], [implode(" ", $args)], MsgMgr::getMsg("home-already-exists")));
        } else {
            $s->sendMessage($prefix . str_replace(["{home}"], [implode(" ", $args)], MsgMgr::getMsg("home-set")));
            $s->getWorld()->addSound($s->getPosition(), new DoorBumpSound());
        }
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}