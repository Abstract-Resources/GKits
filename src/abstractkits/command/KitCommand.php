<?php

declare(strict_types=1);

namespace abstractkits\command;

use abstractkits\command\argument\CreateArgument;
use abstractkits\command\argument\SelectArgument;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\Translatable;
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
            throw new InvalidCommandSyntaxException();
        }

        $argument = $this->getArgument($name);

        if ($argument === null) {
            throw new InvalidCommandSyntaxException();
        }

        if (!$sender->hasPermission($argument->getPermission())) {
            $sender->sendMessage(TextFormat::RED . 'You don\'t have permissions to use this command!');

            return;
        }

        $argument->execute($sender, $args);
    }
}