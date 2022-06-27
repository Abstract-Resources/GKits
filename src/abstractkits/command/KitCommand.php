<?php

declare(strict_types=1);

namespace abstractkits\command;

use abstractkits\AbstractKits;
use abstractkits\command\argument\CreateArgument;
use abstractkits\command\argument\SelectArgument;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class KitCommand extends Command {

    /** @var Argument[] */
    private array $arguments = [];

    /**
     * @param string                   $name
     * @param Translatable|string      $description
     * @param Translatable|string|null $usageMessage
     * @param array                    $aliases
     */
    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);

        $this->addArgument(
            new CreateArgument('create', 'abstract.kits.admin.create'),
            new SelectArgument('select', 'abstract.kits.admin.select')
        );
    }

    /**
     * @param Argument ...$arguments
     *
     * @return void
     */
    protected function addArgument(Argument ...$arguments): void {
        foreach ($arguments as $argument) {
            $this->arguments[$argument->getName()] = $argument;
        }
    }

    /**
     * @param string $label
     *
     * @return Argument|null
     */
    protected function getArgument(string $label): ?Argument {
        return $this->arguments[strtolower($label)] ?? null;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $name = array_shift($args);

        if ($name === null) {
            if ($this->showKitsMenu($sender)) return;

            throw new InvalidCommandSyntaxException();
        }

        $argument = $this->getArgument($name);

        if ($argument === null) {
            if ($this->showKitsMenu($sender)) return;

            throw new InvalidCommandSyntaxException();
        }

        if (!$sender->hasPermission($argument->getPermission())) {
            $sender->sendMessage(TextFormat::RED . 'You don\'t have permissions to use this command!');

            return;
        }

        $argument->execute($sender, $args);
    }

    /**
     * @param CommandSender $sender
     *
     * @return bool
     */
    private function showKitsMenu(CommandSender $sender): bool {
        if (!$sender instanceof Player) return false;

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)
            ->setName(is_string($title = AbstractKits::getInstance()->getConfig()->get('menu-title')) ? $title : '');

        foreach (AbstractKits::getInstance()->getKits() as $kit) {
            if (($representativeItem = $kit->getRepresentativeItem()) === null) continue;

            $menu->getInventory()->setItem($kit->getRepresentativeSlot(), $representativeItem);
        }

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction): void {
            $item = $transaction->getItemClicked();

            if (($nbt = $item->getCustomBlockData()) === null) return;
            if (!($tag = $nbt->getTag('kit_name')) instanceof StringTag) return;

            if (($kit = AbstractKits::getInstance()->getKit($tag->getValue())) === null) return;

            $player = $transaction->getPlayer();

            // TODO: Force close the current window
            $player->removeCurrentWindow();

            if ($kit->getRepresentativeSlot() !== $transaction->getAction()->getSlot()) {
                $player->sendMessage(AbstractKits::prefix() . TextFormat::RED . 'An error occurred when tried execute support action... The support slot is incorrect.');

                return;
            }

            // TODO: Check if the player already has countdown

        }));

        $menu->send($sender);

        return true;
    }
}