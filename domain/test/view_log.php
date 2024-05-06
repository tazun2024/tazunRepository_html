<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
  //define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
  //define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
    define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
    define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/_html/');

    if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {echo '<body><div>invalid env.<br>APP_ROOT['.APP_ROOT.']<br>invalid HTML_ROOT['.HTML_ROOT.']<br></div></body>';}

  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
  echo "<!-- saved from url=(0029)http://www9.ocn.ne.jp/~tazun/ -->";
  echo "<HTML xmlns=\"http://www.w3.org/1999/xhtml\">";
  echo "<HEAD>";
  echo "<TITLE>VIEW LOG</TITLE>";
  echo "<meta NAME=\"ROBOTS\" CONTENT=\"NOINDEX,NOFOLLOW,NOARCHIVE\">";
  echo "<META http-equiv=Content-Type content=\"text/html; charset=UTF-8\">";
  echo "</HEAD>";
  echo "<BODY>";

  echo "<font size=+1><B>ＴＥＳＴ</B></font><br>\n";

  // エルビス演算子 $hoge = @$_POST['foo'] ?: 'bar'; // @ Undefined index の Notice を抑止
  $targetCode = @$_REQUEST['NM'] ?: '';
  $tailValue = @$_REQUEST['TAIL'] ?: '5';
  echo APP_ROOT.'accessCMX/Logs/'.$targetCode."<br>\n";
  echo "<br>";


  $response = file_get_contents(APP_ROOT.'accessCMX/Logs/'.$targetCode);
  if ($response !== FALSE) {


    $responseArr = preg_split("/\n/", $response);

    for ($arrIdx = count($responseArr) - intval($tailValue); $arrIdx < count($responseArr); $arrIdx ++) {

      if (strpos($responseArr[$arrIdx], "｛ページが更新されたようです。") === false) {  // これを含まない行がチェック対象

        echo "<div style=\"font-family:monospace; white-space:nowrap; font-size:120%; line-height:150%;\">".htmlentities($responseArr[$arrIdx], ENT_QUOTES, 'UTF-8')."</div>";
      }
    }
  }

  echo "<br>\n";
  echo "<a href=./view_log.php?NM=".$targetCode."&TAIL=".strval(intval($tailValue) + 4).">>>more</a>\n";

  echo "</body>";
  echo "</html>";

?>

