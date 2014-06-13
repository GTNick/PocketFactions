<?php

namespace pocketfactions\utils;

use pocketmine\Server;
use pocketmine\command\CommandExecutor as CmdExe;
use pocketmine\command\CommandExecutor; // it is ok, ignore this line
use pocketmine\command\CommandMap;
use pocketmine\command\CommandSender as Issuer;
use pocketmine\command\PluginCommand as ParentClass;
use pocketmine\plugin\Plugin;

class PluginCmd extends ParentClass{
	protected $exe;
	const NO_PERM = 0b0001;
	const WRONG_USE = 0b0010;
	const TOO_FEW_ARGS = 0b0100;
	const RUN_CONSOLE = 0b1000;
	const RUN_IN_GAME = 0b10000;
	const DB_LOADING = 0b100000;
	const NO_FACTION = 0b1000000;
	const INVALID_PLAYER = 0b10000000;
	const INVALID_FACTION = 0b100000000;
	/**
	 * Constructs a new PluginCmd instance.
	 * @param string $name
	 * @param Plugin $plugin
	 * @param callable|CommandExecutor|null $exe
	 */
	public function __construct($name, Plugin $plugin, $exe = null){
		parent::__construct($name, $plugin);
		if($exe === null){
			$exe = $plugin;
		}
		if(!is_callable($exe) and !($exe instanceof CmdExe)){
			trigger_error("Unexpected argument type passed to PluginCmd::__construct()", E_USER_ERROR);
		}
		$this->exe = $exe === null ? $plugin:$exe;
	}
	public function execute(Issuer $isr, $lbl, array $args){
		if(is_callable($this->exe)){
			$result = call_user_func($this->exe, $this, $isr, $lbl, $args);
		}
		else{
			$result = $this->exe->onCommand($this, $isr, $lbl, $args);
		}
		if(is_bool($result) or is_null($result)){
			if($result === false)
				$isr->sendMessage($this->getUsage());
			return (bool)$result;
		}
		if(is_string($result)){
			$isr->sendMessage($result);
			return true;
		}
		if(is_numeric($result)){
			if($result & self::NO_PERM){
				$isr->sendMessage("You don't have permission to use /".$this->getName()." ".implode(" ", $args).". This can be due to your faction not giving you permission, or because you are using an op-only command.");
			}
			if($result & self::WRONG_USE){
				$isr->sendMessage("Incorrect usage. Usage: ".$this->getUsage());
			}
			if($result & self::TOO_FEW_ARGS){
				$isr->sendMessage("Not enough arguments are given. Usage: ".$this->getUsage());
			}
			if($result & self::RUN_CONSOLE){
				$isr->sendMessage("This command can only be run on console.");
			}
			if($result & self::RUN_IN_GAME){
				$isr->sendMessage("Please run this command in-game.");
			}
			if($result & self::DB_LOADING){
				$isr->sendMessage("Sorry, the faction database has not finished loading. Please wait for a while.");
			}
			if($result & self::NO_FACTION){
				$isr->sendMessage("You don't belong to any factions!");
			}
			if($result & self::INVALID_PLAYER){
				$isr->sendMessage("Player not found!");
			}
			if($result & self::INVALID_FACTION){
				$isr->sendMessage("Faction not found!");
			}
			return true;
		}
	}
	/**
	 * Registers the command to the server command map
	 */
	public function reg(){
		Server::getInstance()->getCommandMap()->register($this->getPlugin()->getName(), $this);
	}
}
