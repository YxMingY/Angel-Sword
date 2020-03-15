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
  private $ecache = [];
  private $einput = [];
  private $xinput = [];
  private static $lang = "eng";
  const PLUGIN_NAME = "Angel-Sword";
  public function onLoad()
  {
    self::info("[".self::PLUGIN_NAME."] is Loading...");
  }
  public function onEnable()
  {
    @mkdir($this->getDataFolder());
    $this->econf = new Config($this->getDataFolder()."/eConfig.yml",Config::YAML,array());
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    $this->registerEvents();
    self::notice("[".self::PLUGIN_NAME."] is Enabled by xMing! \n[Angle-Sword]指令/as l chs 切换为中文(关服失效)");
  }
  public static function getLang():string
  {
    return self::$lang;
  }
  public function onCommand(CommandSender $sender,Command $command,string $label,array $args):bool
  {
    if(count($args) < 1)
      return false;
    switch ($args[0]){
      case "e":
        if(!isset($args[1])){
          $sender->sendMessage("usage: /as e [Event]");
          return true;
        }
        $sender->sendMessage(Lang::parse("check-event-isset"));
        if(!class_exists($args[1])){
          $sender->sendMessage(Lang::parse("event-not-defined", $args[1]));
          return true;
        }
        $sender->sendMessage(Lang::parse("checked"));
        $this->einput[$sender->getName()] = [];
        $this->ecache[$sender->getName()] = $args[1];
        $sender->sendMessage(Lang::parse("input-instruction"));
        return true;
      case "x":
        $this->xinput[$sender->getName()] = [];
        $sender->sendMessage(Lang::parse("input-instruction"));
        return true;
      case "l":
        if(!isset($args[1]))
          return false;
        if($args[1] == "chs"){
          self::$lang = "chs";
          return true;
        }else if($args[1] == "eng"){
          self::$lang = "eng";
          return true;
        }else{
          return false;
        }
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
  public function handleInput(Player $player,$start,$line,array $lines,bool $is_e)
  {
    $name = $player->getName();
    switch ($start){
      case "#":
        $lines[] = $line;
        if($is_e){
          $this->einput[$name] = $lines;
        }else{
          $this->xinput[$name] = $lines;
        }
        $player->sendMessage(Lang::parse("line-inputted").implode("\n", $lines));
        break;
      case "!":
        array_pop($lines);
        if($is_e){
          $this->einput[$name] = $lines;
        }else{
          $this->xinput[$name] = $lines;
        }
        $player->sendMessage(Lang::parse("line-deleted").implode("\n", $lines));
        break;
      case "?":
        $player->sendMessage(Lang::parse("input-finished").implode("\n", $lines)."\n".Lang::parse("code-running"));
        
        if($is_e){
          $ename = $this->ecache[$name];
          try {
            eval('$this->getServer()->getPluginManager()->registerEvents(
            new class($this) implements \pocketmine\event\Listener{
              private $plugin;
              public function __construct(\yxmingy\angelsword\Main $plugin){
                $this->plugin = $plugin;
              }
              public function onEventHandle('.$ename.' $event) {
                '.implode("\n", $lines).'
              }
            }, $this);');
            $player->sendMessage(Lang::parse("run-completed"));
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
          unset($this->ecache[$name]);
          unset($this->einput[$name]);
        }else{
          try {
            eval(implode("\n", $lines));
            $player->sendMessage(Lang::parse("run-completed"));
            unset($this->xinput[$name]);
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
        eval('$this->getServer()->getPluginManager()->registerEvents(
        new class($this) implements \pocketmine\event\Listener{
          private $plugin;
          public function __construct(\yxmingy\angelsword\Main $plugin){
            $this->plugin = $plugin;
          }
          public function onEventHandle('.$ename.' $event) {
            '.$code.'
          }
        }, $this);');
      }
    }
  }
  public function onDisable()
  {
    self::warning("[".self::PLUGIN_NAME."] is Closed.");
  }
}
