<?php

declare(strict_types=1);

namespace abstractkits\object;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

final class Kit {

    /**
     * @param string $name
     * @param int    $countdown
     * @param Item[]  $armor
     * @param Item[]  $inventory
     * @param EffectInstance[]  $effects
     */
    public function __construct(
        private string $name,
        private int $countdown,
        private array $armor,
        private array $inventory,
        private array $effects
    ) {}

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCountdown(): int {
        return $this->countdown;
    }

    /**
     * @return Item[]
     */
    public function getArmor(): array {
        return $this->armor;
    }

    /**
     * @return Item[]
     */
    public function getInventory(): array {
        return $this->inventory;
    }

    /**
     * @return EffectInstance[]
     */
    public function getEffects(): array {
        return $this->effects;
    }

    /**
     * @param string $name
     * @param array  $serialized
     *
     * @return Kit
     */
    public static function deserialize(string $name, array $serialized): Kit {
        return new Kit(
            $name,
            $serialized['countdown'] ?? 0,
            self::deserializeItems($name, $serialized['armor'] ?? []),
            self::deserializeItems($name, $serialized['items'] ?? []),
            self::deserializeEffects($serialized['effects'] ?? [])
        );
    }

    /**
     * @param string $kitName
     * @param array  $itemsSerialized
     *
     * @return Item[]
     */
    private static function deserializeItems(string $kitName, array $itemsSerialized): array {
        /** @var Item[] $items */
        $items = [];

        foreach ($itemsSerialized as $itemSerialized) {
            if (!isset($itemSerialized['id'])) {
                continue;
            }

            if (($item = self::parseItem($itemSerialized['id'])) === null) continue;

            if (isset($itemSerialized['name'])) {
                $item->setCustomName(TextFormat::colorize($itemSerialized['name']));
            }

            if (isset($itemSerialized['lore']) && is_array($itemSerialized['lore'])) {
                $item->setLore(array_map(fn(string $lore) => TextFormat::colorize($lore), $itemSerialized['lore']));
            }

            foreach ($itemsSerialized['enchants'] ?? [] as $enchantData) {
                if (($enchantment = EnchantmentIdMap::getInstance()->fromId($enchantData['id'])) === null) {
                    continue;
                }

                $item->addEnchantment(new EnchantmentInstance($enchantment, $enchantData['level']));
            }

            $item->setCustomBlockData(($item->getCustomBlockData() ?? CompoundTag::create())
                ->setString('kit_name', $kitName)
            );

            if (isset($itemSerialized['slot'])) {
                $items[$itemSerialized['slot']] = $item;
            } else {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array $effectsSerialized
     *
     * @return EffectInstance[]
     */
    private static function deserializeEffects(array $effectsSerialized): array {
        /** @var EffectInstance[] $effects */
        $effects = [];

        foreach ($effectsSerialized as $serialized) {
            if (($effectType = EffectIdMap::getInstance()->fromId($serialized['type'])) === null) continue;

            $effects[] = new EffectInstance(
                $effectType,
                $serialized['duration'],
            $serialized['amplifier'],
                $serialized['visible']
            );
        }

        return $effects;
    }

    /**
     * @param string|int $value
     *
     * @return Item|null
     */
    private static function parseItem(string|int $value): ?Item {
        return is_int($value) ? ItemFactory::getInstance()->get($value) : StringToItemParser::getInstance()->parse($value);
    }
}