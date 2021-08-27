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

class Unban extends PluginCommand
{

    public $plugin;

    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);
        $this->plugin = $owner;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage("Usage /unban <playername>");
        } elseif (isset($args[0]) and $sender->hasPermission("unban.cmd") or $sender->isOp() and mysqli_fetch_row($this->plugin->getDatabase()->query("SELECT username FROM ban WHERE username='$args[0]'")) !== null) {
            $a = $this->plugin->getDatabase()->query("SELECT username FROM ban WHERE username='$args[0]'");
            $p = mysqli_fetch_row($a);
            if ($a !== null) {
                $this->plugin->getDatabase()->query("DELETE FROM ban WHERE username='$args[0]'");
                $sender->sendMessage("Successfully Unban $args[0]");
            }
            return true;
        }
    }
}