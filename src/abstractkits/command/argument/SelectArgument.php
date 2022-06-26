<?php

declare(strict_types=1);

namespace abstractkits\command\argument;

use abstractkits\AbstractKits;
use abstractkits\command\Argument;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class SelectArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param array         $args
     */
    public function execute(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        if (count($args) === 0) {
             $sender->sendMessage(TextFormat::RED . 'Usage: /kit select <kit_name>');

             return;
        }

        if (($kit = AbstractKits::getInstance()->getKit($args[0])) === null) {
            $sender->sendMessage(TextFormat::RED . 'Kit ' . $args[0] . ' not exists.');

            return;
        }

        foreach (array_merge($kit->getInventory(), $kit->getArmor()) as $item) {
            if (!$sender->getInventory()->canAddItem($item)) {
                $sender->getWorld()->dropItem($sender->getLocation(), $item);
            } else {
                $sender->getInventory()->addItem($item);
            }
        }

        // TODO: Add countdown

        $sender->sendMessage(AbstractKits::prefix() . TextFormat::GREEN . 'Kit ' . $kit->getName() . ' successfully selected!');
    }
}