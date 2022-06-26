<?php

declare(strict_types=1);

namespace abstractkits;

use abstractkits\command\KitCommand;
use abstractkits\listener\PlayerJoinListener;
use abstractkits\object\Kit;
use abstractkits\storage\Storage;
use Exception;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class AbstractKits extends PluginBase {
    use SingletonTrait;

    /** @var Kit[] */
    private array $kits = [];

    public function onEnable(): void {
        self::setInstance($this);

        foreach ((new Config($this->getDataFolder() . 'kits.yml'))->getAll() as $kitName => $kitSerialized) {
            if (!is_string($kitName) || !is_array($kitSerialized)) continue;

            $this->registerNewKit(Kit::deserialize($kitName, $kitSerialized));
        }

        $this->getServer()->getLogger()->info(self::prefix() . TextFormat::AQUA . 'Successfully loaded ' . count($this->kits) . ' kit(s)!');

        Storage::getInstance()->init();

        $this->getServer()->getCommandMap()->register(KitCommand::class, new KitCommand('kit', 'AbstractKits command management'));

        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);
    }

    /**
     * @param Kit  $kit
     * @param bool $forceSave
     */
    public function registerNewKit(Kit $kit, bool $forceSave = false): void {
        $this->kits[strtolower($kit->getName())] = $kit;

        if (!$forceSave) return;

        try {
            $config = new Config($this->getDataFolder() . 'kits.yml');

            $config->set($kit->getName(), [
                'countdown' => $kit->getCountdown(),
                'armor' => array_map(fn(int $slot, Item $item) => [
                    'id' => $item->getId(),
                    'meta' => $item->getMeta(),
                    'slot' => $slot,
                    'name' => $item->getCustomName(),
                    'lore' => $item->getLore(),
                    'enchants' => array_map(fn(EnchantmentInstance $enchantmentInstance) => [
                        'id' => EnchantmentIdMap::getInstance()->toId($enchantmentInstance->getType()),
                        'level' => $enchantmentInstance->getLevel()
                    ], $item->getEnchantments())
                ], array_keys($kit->getArmor()), $kit->getArmor()),
                'items' => array_map(fn(int $slot, Item $item) => [
                    'id' => $item->getId(),
                    'meta' => $item->getMeta(),
                    'slot' => $slot,
                    'name' => $item->getCustomName(),
                    'lore' => $item->getLore(),
                    'enchants' => array_map(fn(EnchantmentInstance $enchantmentInstance) => [
                        'id' => EnchantmentIdMap::getInstance()->toId($enchantmentInstance->getType()),
                        'level' => $enchantmentInstance->getLevel()
                    ], $item->getEnchantments())
                ], array_keys($kit->getInventory()), $kit->getInventory()),
                'effects' => array_map(fn(EffectInstance $effectInstance) => [
                    'type' => EffectIdMap::getInstance()->toId($effectInstance->getType()),
                    'duration' => $effectInstance->getDuration(),
                    'amplifier' => $effectInstance->getAmplifier(),
                    'visible' => $effectInstance->isVisible()
                ], $kit->getEffects())
            ]);
            $config->save();
        } catch (Exception $e) {
            $this->getLogger()->error('An error occurred when tried save the Kits config... Error: ' . $e->getTraceAsString());
        }
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

    /**
     * @return string
     */
    public static function prefix(): string {
        return TextFormat::LIGHT_PURPLE . 'AbstractKits' . TextFormat::DARK_GRAY . ' > ';
    }
}