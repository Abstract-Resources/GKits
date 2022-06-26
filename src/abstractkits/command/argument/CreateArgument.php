<?php

declare(strict_types=1);

namespace abstractkits\command\argument;

use abstractkits\AbstractKits;
use abstractkits\command\Argument;
use abstractkits\object\Kit;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CreateArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param array         $args
     */
    public function execute(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /kit create <kit_name> <countdown>');

            return;
        }

        if (AbstractKits::getInstance()->getKit($args[0]) !== null) {
            $sender->sendMessage(TextFormat::RED . 'Kit ' . $args[0] . ' already exists');

            return;
        }

        if (!is_numeric($args[1])) {
            $sender->sendMessage(TextFormat::RED . 'Invalid kit countdown.');

            return;
        }

        $armorContents = $sender->getArmorInventory()->getContents();
        foreach ($armorContents as $item) {
            $item->setCustomBlockData(($item->getCustomBlockData() ?? CompoundTag::create())
                ->setString('kit_name', $args[0])
            );
        }

        $contents = $sender->getInventory()->getContents();
        foreach ($contents as $item) {
            $item->setCustomBlockData(($item->getCustomBlockData() ?? CompoundTag::create())
                ->setString('kit_name', $args[0])
            );
        }

        AbstractKits::getInstance()->registerNewKit(new Kit(
            $args[0],
            (int) $args[1],
            $armorContents,
            $contents,
            $sender->getEffects()->all(),
        ), true);

        $sender->sendMessage(AbstractKits::prefix() . TextFormat::GREEN . 'Successfully created kit ' . TextFormat::BLUE . $args[0]);
    }
}