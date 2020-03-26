<?php

namespace bossbarapi;

use bossbarapi\bossbar\BossBar;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener
{

    public function onJoin(PlayerJoinEvent $event){
        BossBar::create($event->getPlayer());
    }

    public function onQuit(PlayerQuitEvent $event){
        BossBarAPI::getInstance()->unsetBossBar($event->getPlayer());
    }

}