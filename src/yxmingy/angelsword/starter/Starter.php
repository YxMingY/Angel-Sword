<?php
namespace yxmingy\angelsword\starter;
use pocketmine\Server;
//PHP特性 Trait
trait Starter
{
  protected static $instance;
  //这个一定要在主类执行
  protected function assignInstance()
  {
    self::$instance=$this;
  }
  public static function getInstance()
  {
    return self::$instance;
  }
  public static function info(string $message):void
  {
    Server::getInstance()->getLogger()->info($message);
  }
  public static function notice(string $message):void
  {
    Server::getInstance()->getLogger()->notice($message);
  }
  public static function warning(string $message):void
  {
    Server::getInstance()->getLogger()->warning($message);
  }
  public static function error(string $message):void
  {
    Server::getInstance()->getLogger()->error($message);
  }
}
