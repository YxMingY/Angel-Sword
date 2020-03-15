<?php
namespace yxmingy\angelsword;;

class Lang
{
  private static $langs = [
    "check-event-isset"=>[
      "chs"=>"正在检测这个事件class是否存在...(如果不存在将会报错)",
      "eng"=>"Checking the Event class isset or not...(If not, AN ERROR will be reported)"
    ],
    "event-not-defined"=>[
      "chs"=>"事件class %0 没有被定义(这个事件不存在)",
      "eng"=>"The Event Class %0 is not defined"
    ],
    "checked"=>[
      "chs"=>"检测完毕，一切正常",
      "eng"=>"Successfully checked."
    ],
    "input-instruction"=>[
      "chs"=>'以"#"开头输入你的代码\n以符号"!"单独一行可以删除上一行代码\n以符号"?"单独一行结束你的输入\n变量$event已经被定义',
      "eng"=>"Input your code lines which begins with '#'\n'!' line to delete recent line\n'?' line to stop inputting\n\$event is defined"
    ],
    "line-inputted"=>[
      "chs"=>"新的一行代码已被输入，所有代码:\n",
      "eng"=>"New line is inputted, all code:\n"
    ],
    "line-deleted"=>[
      "chs"=>"上一行代码已被删除，所有代码:\n",
      "eng"=>"Recent line is deleted, all code:\n"
    ],
    "input-finished"=>[
      "chs"=>"输入结束，所有代码:\n",
      "eng"=>"Inputting finished, all code:\n"
    ],
    "code-running"=>[
      "chs"=>"你输入的代码正在运行，如果没有看到“执行完毕”，那么你的代码可能有错误",
      "eng"=>"Code is running,if not see 'Code run completed', your code might have some error(s)."
    ],
    "run-completed"=>[
      "chs"=>"执行完毕",
      "eng"=>"Code run completed"
    ]
  ];
  public static function parse(string $note,...$args):?string
  {
    if(!isset(self::$langs[$note]) || !isset(self::$langs[$note][Main::getLang()]))
      return null;
    $note = self::$langs[$note][Main::getLang()];
    for($i=0;$i<count($args);$i++) {
      $note = str_replace("%".$i, $args[$i], $note);
    }
    return $note;
  }
}

