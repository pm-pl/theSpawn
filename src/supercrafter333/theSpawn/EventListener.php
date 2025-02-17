<?php

namespace supercrafter333\theSpawn;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\sound\PopSound;

/**
 * Class EventListener.php
 * @package supercrafter333\theSpawn
 */
class EventListener implements Listener
{

    /**
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event)
    {
        $pl = theSpawn::getInstance();
        $player = $event->getPlayer();
        if ($pl->hasSpawnDelay($player)) {
            $pl->stopSpawnDelay($player);
            $player->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("delay-stopped-by-move"));
        }
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event)
    {
        $pl = theSpawn::getInstance();
        $player = $event->getPlayer();
        if ($pl->hasSpawnDelay($player)) {
            $pl->stopSpawnDelay($player);
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event): void
    {
        $pl = theSpawn::getInstance();
        $s = $event->getPlayer();
        $world = $s->getWorld();

        if ($pl->useHubTeleportOnDeath() && $pl->getHub() instanceof Position) {
            $event->setRespawnPosition($pl->getHub());
            return;
        }

        if ($world === null) { //Maybe unnecessary?
            if ($pl->getHub() instanceof Position) {
                $event->setRespawnPosition($pl->getHub());
            } else {
                $event->setRespawnPosition($pl->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            }
        }
        if ($pl->getSpawn($world) instanceof Position) {
            $event->setRespawnPosition($pl->getSpawn($world));
            $s->getWorld()->addSound($s->getPosition(), new PopSound());
        } elseif ($pl->getHub() instanceof Position) {
            $event->setRespawnPosition($pl->getHub());
            $s->getWorld()->addSound($s->getPosition(), new PopSound());
        } else {
            if ($world->getSafeSpawn() === null) {
                $event->setRespawnPosition($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            } else {
                $event->setRespawnPosition($world->getSafeSpawn());
            }
        }
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event)
    {
        $pl = theSpawn::getInstance();

        if ($this->getConfig()->get("hub-teleport-on-join") == "true") {
            $hub = $pl->getHub();
            if ($hub !== null && $hub !== false) {
                $event->getPlayer()->teleport($hub);
            } elseif ($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn() !== null) {
                $event->getPlayer()->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $ev): void
    {
        $pl = theSpawn::getInstance();
        $player = $ev->getPlayer();

        if ($pl->useBackCommand()) $pl->setLastDeathPosition($player, $player->getLocation());
    }

    /**
     * @return Server
     */
    private function getServer(): Server
    {
        return theSpawn::getInstance()->getServer();
    }

    /**
     * @return Config
     */
    private function getConfig(): Config
    {
        return theSpawn::getInstance()->getConfig();
    }
}