<?php

namespace bossbarapi;

use bossbarapi\bossbar\BossBar;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class EventListener implements Listener
{

    public function onJoin(PlayerJoinEvent $event){
        //BossBar::create($event->getPlayer());//テスト用コード
    }

    public function onQuit(PlayerQuitEvent $event){
        BossBarAPI::getInstance()->unsetBossBar($event->getPlayer());
    }

    public function onTeleport(EntityTeleportEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player){
            $bossbar = BossBarAPI::getInstance()->getBossBar($player);
            if($bossbar !== null) $bossbar->moveToPlayer();
        }
    }

}