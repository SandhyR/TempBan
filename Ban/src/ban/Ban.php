<?php

namespace ban;


use ban\command\Bancmd;
use ban\command\Unban;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Ban extends PluginBase implements Listener
{

    private $config;


    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->unregistercmd();
        $this->registercmd();
        $this->getDatabase()->query("CREATE TABLE ban ( id INT PRIMARY KEY AUTO_INCREMENT , username VARCHAR(255) NOT NULL , admin VARCHAR(255) NOT NULL, reason VARCHAR(255) NOT NULL, duration VARCHAR(255) NOT NULL);");
        // exec("sudo shutdown");
    }

    public function getDatabase()
    {
        return new \mysqli($this->config->get("host"), $this->config->get("user"), $this->config->get("password"), $this->config->get("db-name"));
    }

    private function registercmd(): void
    {
        $this->getServer()->getCommandMap()->register("Ban player on server", new Bancmd("ban", $this));
        $this->getServer()->getCommandMap()->register("Unban player on server", new Unban("unban", $this));
    }

    private function unregistercmd(): void
    {
        $commandMap = $this->getServer()->getCommandMap();
        $cmd = $commandMap->getCommand("ban");
        $this->getServer()->getCommandMap()->unregister($cmd);
        $commandMap = $this->getServer()->getCommandMap();
        $cmd = $commandMap->getCommand("unban");
        $this->getServer()->getCommandMap()->unregister($cmd);
        $commandMap = $this->getServer()->getCommandMap();
        $cmd = $commandMap->getCommand("banlist");
        $this->getServer()->getCommandMap()->unregister($cmd);
    }

    public function Prelogin(PlayerPreLoginEvent $event)
    {
        $player = $event->getPlayer();
        $playername = $player->getName();
        $p = $this->getDatabase()->query("SELECT username FROM ban where username='$playername'");
        $playerban = mysqli_fetch_array($p);
        if ($playerban !== null) {
            $admin = $this->getDatabase()->query("SELECT admin FROM ban WHERE username='$playername'");
            $admin = mysqli_fetch_row($admin);
            $reason = $this->getDatabase()->query("SELECT reason FROM ban WHERE username='$playername'");
            $reason = mysqli_fetch_row($reason);
            $duration = $this->getDatabase()->query("SELECT duration FROM ban WHERE username='$playername'");
            $duration = mysqli_fetch_row($duration);
            $a = strtotime($duration[0]);
            $now = time();
            if ($a > $now) {
                $player->kick("You are banned by $admin[0] reason $reason[0] until $duration[0]");
            } else {
                $this->getDatabase()->query("DELETE FROM ban WHERE username='$playername'");
            }
        }
    }
}