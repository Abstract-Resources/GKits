<?php

declare(strict_types=1);

namespace abstractkits\command\argument;

use abstractkits\AbstractKits;
use abstractkits\command\Argument;
use abstractkits\provider\StorageProvider;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class ResetArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param array         $args
     */
    public function execute(CommandSender $sender, array $args): void {
        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /kit reset <player>');

            return;
        }

        if (($target = Server::getInstance()->getPlayerByPrefix($args[0])) === null) {
            $sender->sendMessage(TextFormat::RED . 'Player ' . $args[0] . ' not is online.');

            return;
        }

        if (($kit = AbstractKits::getInstance()->getKit($args[1])) === null) {
            $sender->sendMessage(TextFormat::RED . 'Kit ' . $args[1] . ' not exists.');

            return;
        }

        if (StorageProvider::getInstance()->getNiceCountdown($target->getXuid(), $kit->getName()) === null) {
            $sender->sendMessage(AbstractKits::prefix() . TextFormat::RED . 'Nothing to reset.');

            return;
        }

        StorageProvider::getInstance()->resetSync($target->getXuid(), $kit->getName());

        $sender->sendMessage(AbstractKits::prefix() . TextFormat::GREEN . 'Successfully removed kit countdown to ' . $target->getName() . ' (' . $kit->getName() . ')');
    }
}