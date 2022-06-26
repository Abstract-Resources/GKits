<?php

declare(strict_types=1);

namespace abstractkits\storage;

use abstractkits\AbstractKits;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

final class Storage {
    use SingletonTrait;

    private Config $config;

    // TODO: Do the init function with the config
    public function init(): void {
        $this->config = new Config(AbstractKits::getInstance()->getDataFolder() . 'countdowns.yml');
    }

    /**
     * @param string $xuid
     * @param string $kitName
     * @param int    $secondsTime
     *
     * @throws \JsonException
     */
    public function setPlayerCountdown(string $xuid, string $kitName, int $secondsTime): void {
        $this->config->setNested($xuid . '.' . $kitName, time() + $secondsTime);

        $this->config->save();
    }

    /**
     * @param string $xuid
     * @param string $kitName
     *
     * @return int
     */
    public function getPlayerCountdown(string $xuid, string $kitName): int {
        return is_int($countdown = $this->config->getNested($xuid . '.' . $kitName)) ? $countdown : 0;
    }
}