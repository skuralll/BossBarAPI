<?php

namespace bossbarapi;

use bossbarapi\bossbar\BossBar;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\scheduler\ClosureTask;

class BossBarAPI extends PluginBase
{

    /* @var $instance BossBarAPI*/
    private static $instance;
    /* @var $bars BossBar[]*/
    private $bossbar = [];//playerName => BossBar

    public static function getInstance() : self{
        return self::$instance;
    }

    public function onEnable(){
        self::$instance = $this;

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function (int $currentTick) : void {
                foreach(BossBarAPI::getInstance()->getAllBossBar() as $bossBar){
                    $bossBar->onUpdate($currentTick);
                }
            }
        ), 1);

        Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    /* @return BossBar[]*/
    public function getAllBossBar() : array {
        return $this->bossbar;
    }

    public function setBossBar(Player $player, BossBar $bossBar){
        $this->unsetBossBar($player);
        $this->bossbar[$player->getName()] = $bossBar;
        $bossBar->init();
    }

    public function getBossBar(Player $player) : ?BossBar{
        return isset($this->bossbar[$player->getName()]) ? $this->bossbar[$player->getName()] : null;
    }

    public function unsetBossBar(Player $player){
        if(isset($this->bossbar[$player->getName()])){
            $this->bossbar[$player->getName()]->fin();
            unset($this->bossbar[$player->getName()]);
        }
    }

}