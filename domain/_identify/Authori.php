<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
    define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
  //define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
  //define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/_html/');

    if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {echo '<body><div>invalid env.<br>APP_ROOT['.APP_ROOT.']<br>invalid HTML_ROOT['.HTML_ROOT.']<br></div></body>';}


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 定数設定
    // ==== ==== ==== ====
    define('DOMAIN_ROOT', HTML_ROOT.'domain/');
    define('FILENAME_IDENTIFYLOG', DOMAIN_ROOT.'_identify/logs/LOG_');

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
    include(APP_ROOT.'COMMON/Log/CmxLog.php');


//////// ログ生成
$myCmxLog = new CmxLog(FILENAME_IDENTIFYLOG.date('Ymd').'.log');


$ipa =    $_SERVER['REMOTE_ADDR'];  //IPアドレス指定
// エルビス演算子 $hoge = @$_POST['foo'] ?: 'bar'; // @ Undefined index の Notice を抑止
$action = @$_REQUEST['action'] ?: '';
$myCmxLog ->inf('access : from['.$ipa.'] $action['.$action.']');

switch ($action) {

  case 'login':
  case 'loginerr':
  $myCmxLog->inf('$user['.$_REQUEST['user'].']');
  $myCmxLog->inf('$pass['.$_REQUEST['pass'].']');
  echo "<form action='./Authori.php?action=loginerr&contents=' method=post>\n";
  echo "  <table>\n";
  echo "  <tr><td colspan=2>入力に誤りがあります。</td></tr>\n";
  echo "  <tr><td>user</td>    <td><input type=text size=16 name=user></td></tr>\n";
  echo "  <tr><td>password</td><td><input type=text size=16 name=pass>&nbsp;<input style='width:96px' type=submit value='ログオン'></tr>\n";
  echo "  </table>\n";
  echo "</form>\n";
  echo "<br>\n";
  echo "<form action='./Authori.php?action=regist&contents=' method=post>\n";
  echo "  <table>\n";
  echo "  <tr><td colspan=2><B>新規登録</B></td></tr>\n";
  echo "  <tr><td>user</td>    <td><input type=text size=16 name=user></td></tr>\n";
  echo "  <tr><td colspan=2><input type=radio name=type value=type1>趣味「旅行」</td></tr>\n";
  echo "  <tr><td colspan=2><input type=radio name=type value=type2>ツアークリエイター</td></tr>\n";
  echo "  <tr><td colspan=2><input type=radio name=type value=type3>まさか？！関係者</td></tr>\n";
  echo "  <tr><td colspan=2>&nbsp;<input style=\"width:96px\" type=submit value=\"登録\"></td></tr>\n";
  echo "  </table>\n";
  echo "</form>\n";
  break;

  case 'regist':
  $myCmxLog->inf('$user['.$_REQUEST['user'].']');

  echo "  <table>\n";
  echo "  <tr><td colspan=2><B>新規登録</B></td></tr>\n";

  switch (@$_REQUEST['type'] ?: "") {
    case 'type1':
    $myCmxLog->inf('type1 : 趣味「旅行」');
    break;
    case 'type2':
    $myCmxLog->inf('type2 : ツアークリエイター');
    break;
    case 'type3':
    $myCmxLog->inf('type3 : まさか？！関係者');
    break;
    default:
    $myCmxLog->inf('type 未選択');
  }

  echo "  <tr><td>入力に誤りがあります。<a href=http://aoyoko.work/welcomeKyukamura/index.html>TOPへ戻る</a></td></tr>\n";
  echo "  </table>\n";
  break;

default:
  $myCmxLog->inf('$contents['.$_REQUEST['contents'].']');
  echo "<form action='./Authori.php?action=login&contents=' method=post>\n";
  echo "  <table>\n";
  echo "  <tr><td>user</td>    <td><input type=text size=16 name=user></td></tr>\n";
  echo "  <tr><td>password</td><td><input type=text size=16 name=pass>&nbsp;<input style='width:96px' type=submit value='ログオン'></tr>\n";
  echo "  </table>\n";
  echo "</form>\n";

}

exit;

?>
