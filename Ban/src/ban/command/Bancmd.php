<?php

declare(strict_types=1);

namespace ban\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class Bancmd extends PluginCommand
{

    public $plugin;

    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);
        $this->plugin = $owner;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $name = $sender->getName();
        if (!isset($args[0]) or !isset($args[1]) or !isset($args[2])) {
            $sender->sendMessage("Usage /ban <playername> <duration> <reason>");
        } elseif (isset($args[2]) and isset($args[1]) and isset($args[0]) and is_numeric($args[1]) and $sender->hasPermission("ban.cmd") or $sender->isOp()) {
            $p = mysqli_fetch_row($this->plugin->getDatabase()->query("SELECT username FROM ban WHERE username='$args[0]'"));
            if (!isset($p[0]) and is_numeric($args[1])) {
                $datea = date_create(date('d-m-Y h:i', time()));
                date_add($datea,date_interval_create_from_date_string("$args[1] days"));
                $date = date_format($datea,"d-m-Y");
                $this->plugin->getDatabase()->query("INSERT INTO ban VALUES('', '$args[0]', '$name', '$args[2]', '$date')");
                $this->plugin->getServer()->broadcastMessage("$args[0] has been banned by $name",);
            }
        }
        if (isset($args[0]) and isset($args[1]) and isset($args[2]) and $this->plugin->getServer()->getPlayer($args[0]) !== null) {
            $target = $this->plugin->getServer()->getPlayer($args[0]);
            $datea = date_create(date('d-m-Y h:i', time()));
            date_add($datea,date_interval_create_from_date_string("$args[1] days"));
            $date = date_format($datea,"d-m-Y");
            $target->kick("You are banned by $name reason $args[2] until $date");
        } elseif (isset($args[0]) and isset($args[1]) and isset($args[2]) and mysqli_fetch_row($this->plugin->getDatabase()->query("SELECT username FROM ban WHERE username='$args[0]'")) !== null) {
            $sender->sendMessage("Player $args[0] already banned");
        } elseif (isset($args[0]) and isset($args[1]) and isset($args[2]) and $args[0] == $name) {
            $sender->sendMessage("You cant banned yourself");
        } elseif (isset($args[0]) and isset($args[1]) and isset($args[2]) and !is_numeric($args[1])) {
            $sender->sendMessage("Duration must be numeric");
        }
        return true;
    }
}

