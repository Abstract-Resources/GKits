<?php

declare(strict_types=1);

namespace abstractkits;

use abstractkits\listener\PlayerJoinListener;
use abstractkits\object\Kit;
use abstractkits\storage\Storage;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

final class AbstractKits extends PluginBase {
    use SingletonTrait;

    /** @var Kit[] */
    private array $kits = [];

    public function onEnable(): void {
        self::setInstance($this);

        foreach ((new Config($this->getDataFolder() . 'kits.yml'))->getAll() as $kitName => $kitSerialized) {
            if (!is_string($kitName) || !is_array($kitSerialized)) continue;

            $this->kits[strtolower($kitName)] = Kit::deserialize($kitName, $kitSerialized);
        }

        Storage::getInstance()->init();

        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);
    }

    /**
     * @param string $name
     *
     * @return Kit|null
     */
    public function getKit(string $name): ?Kit {
        return $this->kits[strtolower($name)] ?? null;
    }

    /**
     * @param string $name
     *
     * @return Kit
     */
    public function getKitNonNull(string $name): Kit {
        return $this->getKit($name) ?? throw new PluginException('Invalid kit called as \'' . $name . '\'');
    }
}