<?php

declare(strict_types=1);

namespace abstractkits\listener;

use abstractkits\AbstractKits;
use abstractkits\object\Kit;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;

final class PlayerJoinListener implements Listener {

    /**
     * @param PlayerJoinEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $ev): void {
        $player = $ev->getPlayer();

        $player->getArmorInventory()->getListeners()->add(new CallbackInventoryListener(function (Inventory $inventory, int $slot, Item $oldItem) use ($player): void {
            $this->handleEffectsRemove($player, $oldItem);

            $this->handleEffectsAdd($player, $inventory);
        }, null));
    }

    /**
     * @param Player $player
     * @param Item   $oldItem
     */
    private function handleEffectsRemove(Player $player, Item $oldItem): void {
        if (($nbt = $oldItem->getCustomBlockData()) === null) return;
        if (!($tag = $nbt->getTag('kit_name')) instanceof StringTag) return;

        if (($kit = AbstractKits::getInstance()->getKit($tag->getValue())) === null) return;

        foreach ($kit->getEffects() as $effectInstance) {
            $player->getEffects()->remove($effectInstance->getType());
        }
    }

    /**
     * @param Player    $player
     * @param Inventory $inventory
     */
    private function handleEffectsAdd(Player $player, Inventory $inventory): void {
        /** @var Kit|null $kitMatch */
        $kitMatch = null;
        $match = 0;

        foreach ($inventory->getContents() as $item) {
            if (($nbt = $item->getCustomBlockData()) === null) continue;
            if (!($tag = $nbt->getTag('kit_name')) instanceof StringTag) continue;

            if (($kit = AbstractKits::getInstance()->getKit($tag->getValue())) === null) continue;

            if ($kitMatch !== $kit) {
                $match = 1;
            } else {
                $match++;
            }

            $kitMatch = $kit;
        }

        if ($match < 4 || $kitMatch === null) return;

        foreach ($kitMatch->getEffects() as $effectInstance) {
            $player->getEffects()->add($effectInstance);
        }
    }
}