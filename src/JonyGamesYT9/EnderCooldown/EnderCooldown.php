<?php

namespace JonyGamesYT9\EnderCooldown;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\item\EnderPearl;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use function time;
use function str_replace;

/**
* Class EnderCooldown
* @package JonyGamesYT9\EnderCooldown
*/
class EnderCooldown extends PluginBase implements Listener
{

  /** @var Config $config */
  private Config $config;

  /** @var array $cooldowns */
  private array $cooldowns = [];

  /**
  * @return void
  */
  public function onEnable(): void
  {
    $this->saveResource("config.yml");
    $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    if ($this->config->get("version") === 1) {
      if ($this->config->get("cooldown-time") == 0 || !is_numeric($this->config->get("cooldown-time"))) {
        $this->getLogger()->error("EnderCooldown: The cooldown is not numeric, change it and restart the server.");
        $this->getServer()->getPluginManager()->disablePlugin($this);
      } else {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
      }
    } else {
      $this->getLogger()->error("EnderCooldown: Error in config.yml please delete file and restart server!");
      $this->getServer()->getPluginManager()->disablePlugin($this);
    }
  }

  /**
  * @param PlayerQuitEvent $event
  * @return void
  */
  public function onQuit(PlayerQuitEvent $event): void
  {
    $player = $event->getPlayer();
      if (isset($this->cooldowns[$player->getName()])) {
        unset($this->cooldowns[$player->getName()]);
    }
  }

  /**
  * @param PlayerInteractEvent $event
  * @return void
  */
  public function onUseEnder(PlayerInteractEvent $event): void
  {
    $item = $event->getItem();
    $player = $event->getPlayer();
    if ($player->hasPermission("endercooldown.bypass")) {
      return;
    }
      if ($item instanceof EnderPearl) {
        if (isset($this->cooldowns[$player->getName()]) and time() - $this->cooldowns[$player->getName()] < $this->config->get("cooldown-time")) {
          $event->cancel();
          $time = $this->config->get("cooldown-time") - (time() - $this->cooldowns[$player->getName()]);
          $player->sendMessage(str_replace(["&", "{cooldown}"], ["§", $time], $this->config->get("message-hascooldown")));
        } else {
          $this->cooldowns[$player->getName()] = (time() + $this->config->get("cooldown-time"));
      }
    }
  }
}