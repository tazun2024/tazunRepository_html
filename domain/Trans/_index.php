<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
  //define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
  //define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
    define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
    define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/_html/');

    if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {echo '<body><div>invalid env.<br>APP_ROOT['.APP_ROOT.']<br>invalid HTML_ROOT['.HTML_ROOT.']<br></div></body>';}


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 定数設定
    // ==== ==== ==== ====
    define('DOMAIN_ROOT', HTML_ROOT.'domain/');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
    include(APP_ROOT.'Trans/Trans.php');


  function getEncode($str) {
  //return mb_convert_encoding($str, "UTF-8", "SJIS");
    return $str;
  }


  // ==== 表示
  $myTrans = new Trans($url);
  $view = $myTrans->view;
  $headerArr = array();
  $view['HEADER'] = $headerArr;
  include("_index.html");

?>
