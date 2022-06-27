<?php

declare(strict_types=1);

namespace abstractkits\provider;

use abstractkits\AbstractKits;
use abstractkits\object\Kit;
use DateTime;
use Exception;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class StorageProvider {
    use SingletonTrait;

    private Config $config;

    // TODO: Do the init function with the config
    public function init(): void {
        $this->config = new Config(AbstractKits::getInstance()->getDataFolder() . 'countdowns.yml');
    }

    /**
     * @param string $xuid
     * @param string $name
     * @param Kit    $kit
     */
    public function storeSync(string $xuid, string $name, Kit $kit): void {
        if (!isset($this->config)) {
            Server::getInstance()->getLogger()->error(AbstractKits::prefix() . TextFormat::RED . 'Storage provider is not initialized... Storage method is disabled.');

            return;
        }

        try {
            $this->config->setNested($xuid . '.' . $kit->getName(), [
                'name' => $name,
                'end_at' => time() + $kit->getCountdown()
            ]);
            $this->config->save();
        } catch (Exception $e) {
            AbstractKits::getInstance()->getLogger()->error('An error occurred when tried save the supports config... Error: ' . $e->getTraceAsString());
        }
    }

    /**
     * @param string $xuid
     * @param string $kitName
     */
    public function resetSync(string $xuid, string $kitName): void {
        if (!isset($this->config)) {
            Server::getInstance()->getLogger()->error(AbstractKits::prefix() . TextFormat::RED . 'Storage provider is not initialized... Storage method is disabled.');

            return;
        }

        try {
            $countdowns = $this->config->get($xuid);

            if (!is_array($countdowns) || !isset($countdowns[$kitName])) return;

            unset($countdowns[$kitName]);

            $this->config->set($xuid, $countdowns);
            $this->config->save();
        } catch (Exception $e) {
            AbstractKits::getInstance()->getLogger()->error('An error occurred when tried save the supports config... Error: ' . $e->getTraceAsString());
        }
    }

    /**
     * @param string $xuid
     * @param string $kitName
     *
     * @return string|null
     */
    public function getNiceCountdown(string $xuid, string $kitName): ?string {
        if (!isset($this->config)) {
            Server::getInstance()->getLogger()->error(AbstractKits::prefix() . TextFormat::RED . 'Storage provider is not initialized... Storage method is disabled.');

            return null;
        }

        // TODO: Fetch the player countdown and convert it to string
        if (!is_int($countdown = $this->config->getNested($xuid . '.' . $kitName . '.end_at')) || time() > $countdown) {
            return null;
        }

        try {
            $dateInterval = (new DateTime(date('Y-m-d H:i:s', time())))
                ->diff(new DateTime(date('Y-m-d H:i:s', $countdown)));

            return match (true) {
                $dateInterval->y > 0 => $dateInterval->y . ' year',
                $dateInterval->m > 0 => $dateInterval->m . ' month',
                $dateInterval->d > 0 => $dateInterval->d . ' day',
                $dateInterval->h > 0 => $dateInterval->h . ' hour',
                $dateInterval->i > 0 => $dateInterval->i . ' minute',
                $dateInterval->s > 0 => $dateInterval->s . ' second',
                default => 'Undefined'
            } . '(s)';
        } catch (Exception $e) {
            AbstractKits::getInstance()->getLogger()->error('An error occurred when tried save the supports config... Error: ' . $e->getTraceAsString());
        }

        return null;
    }
}