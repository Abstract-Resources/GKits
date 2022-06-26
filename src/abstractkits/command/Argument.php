<?php

declare(strict_types=1);

namespace abstractkits\command;

use pocketmine\command\CommandSender;

abstract class Argument {

    /**
     * @param string $name
     * @param string $permission
     */
    public function __construct(
        private string $name,
        private string $permission
    ) {}

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPermission(): string {
        return $this->permission;
    }

    /**
     * @param CommandSender $sender
     * @param array         $args
     */
    public abstract function execute(CommandSender $sender, array $args): void;
}