<?php

namespace yxmingy\angelsword;
use Exception;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class Main extends PluginBase implements Listener
{
  use starter\Starter;
  private $econf;
  private $xconf;
  private $ecache = [];
  private $einput = [];
  private $xinput = [];
  const PLUGIN_NAME = "Angel-Sword";
  public function onLoad()
  {
    self::info("[".self::PLUGIN_NAME."] is Loading...");
  }
  public function onEnable()
  {
    $this->econf = new Config($this->getDataFolder()."/eConfig.yml",Config::YAML,array());
    $this->xconf = new Config($this->getDataFolder()."/xConfig.yml",Config::YAML,array());
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    $this->registerEvents();
    self::notice("[".self::PLUGIN_NAME."] is Enabled by xMing!");
  }
  public function onCommand(CommandSender $sender,Command $command, $label,array $args)
  {
    if(count($args) < 1)
      return false;
    switch ($args[0]){
      case "e":
        if(!isset($args[1])){
          $sender->sendMessage("usage: /as e [Event]");
          return true;
        }
        if(!class_exists($args[1])){
          $sender->sendMessage("The Event Class $args[1] is not defined");
          return true;
        }
        $this->einput[$sender->getName()] = [];
        $this->ecache[$sender->getName()] = $args[1];
        $sender->sendMessage("Input your code lines which begins with '#'\n'!' line to delete recent line\n'?' line to stop inputting\n\$event is defined");
        return true;
      case "x":
        $this->xinput[$sender->getName()] = [];
        $sender->sendMessage("Input your code lines which begins with '#'\n'!' line to delete recent line\n'?' line to stop inputting\n\$this is defined");
        return true;
      default:
        return false;
    }
  }
  public function onMessage(PlayerCommandPreprocessEvent $event)
  {
    $name = $event->getPlayer()->getName();
    if(!isset($this->einput[$name]) && !isset($this->xinput[$name]))
      return;
    $start = substr($event->getMessage(), 0,1);
    $line = substr($event->getMessage(), 1);
    if(in_array($start, ["#","!","?"])){
      $event->setCancelled();
      $this->handleInput($event->getPlayer(),$start, $line, ($is_e=isset($this->einput[$name])) ? $this->einput[$name] : $this->xinput[$name],$is_e);
    }
  }
  public function handleInput(Player $player,$start,$line,array &$lines,bool $is_e)
  {
    switch ($start){
      case "#":
        $lines[] = $line;
        $player->sendMessage("New line is inputted, all code:\n".implode("\n", $lines));
        break;
      case "!":
        array_pop($lines);
        $player->sendMessage("Recent line is deleted, all code:\n".implode("\n", $lines));
        break;
      case "?":
        $player->sendMessage("Inputting finished, all code:\n".implode("\n", $lines));
        
        if($is_e){
          $ename = $this->ecache[$player->getName()];
          try {
            eval('$this->getServer()->getPluginManager()->registerEvents(new class implements Listener{
              public function onEvent('.$ename.' $event) {
                '.implode("\n", $lines).'
              }
            }, $this)');
            if(!$this->econf->exists($ename)){
              $this->econf->set($ename,[implode("\n", $lines),]);
            }else{
              $codes = $this->econf->get($ename);
              $codes[] = implode("\n", $lines);
              $this->econf->set($ename,$codes);
            }
            $this->econf->save();
          } catch (Exception $e) {
            $player->sendMessage($e->getMessage());
          }
          unset($this->ecache[$player->getName()]);
          unset($this->einput[$player->getName()]);
        }else{
          try {
            eval(implode("\n", $lines));
            $player->sendMessage("Code runned");
            unset($this->xinput[$player->getName()]);
          } catch (Exception $e) {
            $player->sendMessage($e->getMessage());
          }
        }
    }
  }
  public function registerEvents()
  {
    foreach($this->econf->getAll() as $ename=>$ecodes){
      foreach ($ecodes as $code){
        eval('$this->getServer()->getPluginManager()->registerEvents(new class implements Listener{
          public function onEvent('.$ename.' $event) {
            '.$code.'
          }
        }, $this)');
      }
    }
  }
  public function onDisable()
  {
    self::warning("[".self::PLUGIN_NAME."] is Turned Off.");
  }
}
