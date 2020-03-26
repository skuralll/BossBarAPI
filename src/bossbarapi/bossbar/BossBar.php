<?php

namespace bossbarapi\bossbar;

use bossbarapi\BossBarAPI;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\Player;

class BossBar
{

    /* @var $owner Player*/
    protected $owner;
    /* @var $eid int*/
    protected $eid;
    /* @var $title string*/
    protected $title = "";
    /* @var $percentage float*/
    protected $percentage = 1.0;

    public static function create(Player $player, ...$args){
        $bossBar = new static($player, ...$args);
        BossBarAPI::getInstance()->setBossBar($player, $bossBar);
        return $bossBar;
    }

    public function __construct(Player $player){
        $this->owner = $player;
        $this->eid = Entity::$entityCount++;
    }

    public function init(){
        $this->show();
    }

    public function fin(){
        $this->hide();
    }

    public function onUpdate(int $currentTick){
        if($currentTick % 20 === 0){//20秒ごとに位置更新
            $this->moveToPlayer();
        }
    }

    public function show(){
        $apk = new AddActorPacket();
        $apk->entityRuntimeId = $this->eid;
        $apk->type = EntityIds::SLIME;
        $apk->position = $this->owner;
        $apk->metadata = [
            Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1],
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title],
            Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]
        ];
        $this->owner->dataPacket($apk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $this->eid;
        $bpk->eventType = BossEventPacket::TYPE_SHOW;
        $bpk->title = $this->title;
        $bpk->healthPercent = $this->percentage;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $this->owner->dataPacket($bpk);
    }

    public function hide(){
        $rpk = new RemoveActorPacket();
        $rpk->entityUniqueId = $this->eid;

        $this->owner->dataPacket($rpk);
    }

    public function setPercentage(float $percentage){
        $this->percentage = $percentage;

        $attribute = Attribute::getAttribute(Attribute::HEALTH);
        $attribute->setMaxValue(1000);
        $attribute->setValue(1000 * $this->percentage);
        $upk = new UpdateAttributesPacket();
        $upk->entries = [$attribute];
        $upk->entityRuntimeId = $this->eid;
        $this->owner->dataPacket($upk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $this->eid;
        $bpk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
        $bpk->title = $this->title;
        $bpk->healthPercent = $this->percentage;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;
        $this->owner->dataPacket($bpk);

        $this->owner->dataPacket($bpk);
    }

    public function setTitle(string $title){
        $this->title = $title;

        $spk = new SetActorDataPacket();
        $spk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title]];
        $spk->entityRuntimeId = $this->eid;

        $this->owner->dataPacket($spk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $this->eid;
        $bpk->eventType = BossEventPacket::TYPE_TITLE;
        $bpk->title = $this->title;
        $bpk->healthPercent = $this->percentage;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $this->owner->dataPacket($bpk);
    }

    public function moveToPlayer(){
        $mpk = new MoveActorAbsolutePacket();
        $mpk->entityRuntimeId = $this->eid;
        $mpk->flags |= MoveActorAbsolutePacket::FLAG_TELEPORT;
        $mpk->position = $this->owner;
        $mpk->xRot = 0;
        $mpk->yRot = 0;
        $mpk->zRot = 0;

        $this->owner->dataPacket($mpk);
    }

}