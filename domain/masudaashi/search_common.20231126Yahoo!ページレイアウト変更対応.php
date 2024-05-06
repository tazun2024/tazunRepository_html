<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
////define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');       //// scDisplay.phpで定義された
////define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');      //// scDisplay.phpで定義された
////define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/app');    //// scDisplay.phpで定義された
////define('HTTP_ROOT',   'C:/_developmentWork/xampp/htdocs/_html/'); //// scDisplay.phpで定義された


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 定数設定
    // ==== ==== ==== ====
////define('DOMAIN_ROOT', HTML_ROOT.'domain/');                       //// scDisplay.phpで定義された


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
    include(APP_ROOT.'COMMON/Util/CMX_common.php');




    define('ROOT_X', 492 -6 * @$_REQUEST['RECENT'] ?: 1); // -------- そこでエルビス演算子ですよ

//////////////// ピンクかブルーか画像を解析して解析結果を返却する

function analyzeImg($im, $logFilePath) {

    $height1 = 0;
    $height2 = 0;
    $height3 = 0;
    $crossOccurred = false;
    $cross1 = false;
    $exitReady = false;
    // 最大で直近5日間の足の長さを合計して、動きの激しい線を短期足と判断する
    for ( $x = ROOT_X; $x >= ROOT_X - (6 * 4); $x -= 6 ) {

  ////echo "-------- scan (X=".$x.") --------<br>\n";
      $scanResult = getScanData($x, $im, $logFilePath);
    //$crossOccurred = $crossOccurred || $scanResult->crossOccurred;
    //$cross1 = $cross1 || $scanResult->cross1;
      // 順番大事（trueになると関数は呼ばれなくなる）
      $crossOccurred = $scanResult->crossOccurred || $crossOccurred;
      $cross1 = $scanResult->cross1 || $cross1;

      if ($x == ROOT_X) {

        // 最後に表示する用に、一番目のPINK/BLUEを保持しておく
        $lineBuff1 = $scanResult->lineBuff1;
        $lineBuff2 = $scanResult->lineBuff2;
        $lineBuff3 = $scanResult->lineBuff3;

      } elseif ($x == ROOT_X- (6 * 1)) {

        // 最後に表示する用に、二番目のPINK/BLUEを連結して保持しておく
        $lineBuff1 = $scanResult->lineBuff1.$lineBuff1;
        $lineBuff2 = $scanResult->lineBuff2.$lineBuff2;
        $lineBuff3 = $scanResult->lineBuff3.$lineBuff3;
      }

      if (!$exitReady) {

        $height1 = $height1 + $scanResult->height1;
        $height2 = $height2 + $scanResult->height2;
        $height3 = $height3 + $scanResult->height3;
      }

      //////// 短・中・長期足 の判断、成功したらexitできる
      if ( !$crossOccurred and $height1 > $height2 and $height1 > $height3
        or !$crossOccurred and $height2 > $height1 and $height2 > $height3
        or !$crossOccurred and $height3 > $height1 and $height3 > $height2 ) {

        if ($x < ROOT_X) break; else {

          // 最低条件として、キュービック検索のために2日間はループしたい
          $exitReady = true;
        }
      }

    }


    //////// 短・中・長期足 の判断

    if (!$crossOccurred and $height1 > $height2 and $height1 > $height3) {

      $lineBuff1 = $lineBuff1.$height1." 3day"; // 上から１本目の線を短期足と判断した
      $lineBuff2 = $lineBuff2.$height2;
      $lineBuff3 = $lineBuff3.$height3;

    } elseif (!$crossOccurred and $height2 > $height1 and $height2 > $height3) {

      $lineBuff1 = $lineBuff1.$height1;
      $lineBuff2 = $lineBuff2.$height2." 3day"; // 上から２本目の線を短期足と判断した
      $lineBuff3 = $lineBuff3.$height3;

    } elseif (!$crossOccurred and $height3 > $height1 and $height3 > $height2) {

      $lineBuff1 = $lineBuff1.$height1;
      $lineBuff2 = $lineBuff2.$height2;
      $lineBuff3 = $lineBuff3.$height3." 3day"; // 上から３本目の線を短期足と判断した

    } else {

      //////// とび抜けた足の長さが見つからず、短期足が判断できなかった場合

      $lineBuff1 = $lineBuff1.$height1;
      $lineBuff2 = $lineBuff2.$height2;
      $lineBuff3 = $lineBuff3.$height3;
    }


    $result = new stdClass;
    $result->cross1    = $cross1;
    $result->lineBuff1 = $lineBuff1;
    $result->lineBuff2 = $lineBuff2;
    $result->lineBuff3 = $lineBuff3;

    return $result;
}


//////////////// Y軸方向に画像走査するサブルーチン

function getScanData($x, $im, $logFilePath) {

  $buffColor = 0;
  $foundStep = 0;
  $height1 = 0;
  $height2 = 0;
  $height3 = 0;
  $lineBuff1 = "";
  $lineBuff2 = "";
  $lineBuff3 = "";
  $cross1 = false;
 for ($y = 42; $y <= 280; $y++) {

    $rgb = imagecolorat($im, $x, $y);
    if ($rgb <> 41 and $rgb <> 215) $rgb = 0; // ピンク/ブルー以外は無視する

    // 特定銘柄の解析ログが出力可能
    if ($logFilePath <> "") error_log("x=".$x." [".$foundStep."]y=".$y." ".$rgb."\n", 3, $logFilePath);

    switch($foundStep) {

    case 0:

      if ($rgb == 41) {

        $lineBuff1 = "[<font color=#00FFFF>BLUE</font>]";
        $buffColor = 41;
        $foundStep ++;

      } elseif ($rgb == 215) {

        $lineBuff1 = "[<font color=#FF00FF>PINK</font>]";
        $buffColor = 215;
        $foundStep ++;
      }
      break;

    case 1:

      $height1 ++;

      if ($rgb == 0) {

        $foundStep ++;

      } elseif ($rgb == 41 and $buffColor == 215) {

        // 地の黒色を挟まない「線のクロス」の場合
        $lineBuff2 = "[<font color=#00FFFF>BLUE</font>]";
        $buffColor = 41;
        $foundStep += 2;
        if ($x == ROOT_X) $cross1 = true;

      } elseif ($rgb == 215 and $buffColor == 41) {

        // 地の黒色を挟まない「線のクロス」の場合
        $lineBuff2 = "[<font color=#FF00FF>PINK</font>]";
        $buffColor = 215;
        $foundStep += 2;
        if ($x == ROOT_X) $cross1 = true;

      } elseif ($rgb <> $buffColor) {

        $foundStep ++;
      }
      break;

    case 2:

      if ($rgb == 41) {

        $lineBuff2 = "[<font color=#00FFFF>BLUE</font>]";
        $buffColor = 41;
        $foundStep ++;

      } elseif ($rgb == 215) {

        $lineBuff2 = "[<font color=#FF00FF>PINK</font>]";
        $buffColor = 215;
        $foundStep ++;
      }
      break;

    case 3:

      $height2 ++;

      if ($rgb == 0) {

        $foundStep ++;

      } elseif ($rgb == 41 and $buffColor == 215) {

        // 地の黒色を挟まない「線のクロス」の場合
        $lineBuff3 = "[<font color=#00FFFF>BLUE</font>]";
        $buffColor = 41;
        $foundStep += 2;
        if ($x == ROOT_X) $cross1 = true;

      } elseif ($rgb == 215 and $buffColor == 41) {

        // 地の黒色を挟まない「線のクロス」の場合
        $lineBuff3 = "[<font color=#FF00FF>PINK</font>]";
        $buffColor = 215;
        $foundStep += 2;
        if ($x == ROOT_X) $cross1 = true;

      } elseif ($rgb <> $buffColor) {

        $foundStep ++;
      }
      break;

    case 4:

      if ($rgb == 41) {

        $lineBuff3 = "[<font color=#00FFFF>BLUE</font>]";
        $buffColor = 41;
        $foundStep ++;

      } elseif ($rgb == 215) {

        $lineBuff3 = "[<font color=#FF00FF>PINK</font>]";
        $buffColor = 215;
        $foundStep ++;
      }
      break;

    case 5:

      $height3 ++;

      if ($rgb == 0) {

        $foundStep ++;

      } elseif ($rgb <> $buffColor) {

        $foundStep ++;
      }
      break;
    }

    if ($foundStep >= 6) break;
  }


  //// 返却値を作る
  $scanResult = new stdClass;
  $scanResult->cross1  = $cross1;    // １日目にクロスがあったか否か
  $scanResult->height1 = $height1;
  $scanResult->height2 = $height2;
  $scanResult->height3 = $height3;

  $crossOccurred = false;

  if ($lineBuff1 <> "") $scanResult->lineBuff1 = $lineBuff1; else {

    $crossOccurred = true;
    $scanResult->lineBuff1 = "[xxxx]";
  }

  if ($lineBuff2 <> "") $scanResult->lineBuff2 = $lineBuff2; else {

    $crossOccurred = true;
    $scanResult->lineBuff2 = "[xxxx]";
  }

  if ($lineBuff3 <> "") $scanResult->lineBuff3 = $lineBuff3; else {

    $crossOccurred = true;
    $scanResult->lineBuff3 = "[xxxx]";
  }

  $scanResult->crossOccurred  = $crossOccurred;

  return $scanResult;
}


////////////////
////////////////
//////////////// MY増田足チャート一括収集の対象となる銘柄コードを取得するサブルーチン
////////////////
////////////////

function getMeigaraArr($pattern) {

  // 増田足廃業に伴いindex銘柄のチャートは取得できないので使用しない機能
  if ($pattern == "index") return(getIndexMeigaraArr());

  $codeArray = array();

  //
  include(__DIR__."/search_addIndexCode.php");

  $codeArray[] ='1001'; // 日経225指数
  $codeArray[] ='1009'; // マザーズ総合指数

  return $codeArray;
}


//////////////// 指数銘柄を取得するサブルーチン

function getIndexMeigaraArr() {

  $codeArray = array();
  $codeArray[] = "1001"; // 日経２２５種平均株価
  $codeArray[] = "1002"; // ＴＯＰＩＸ（東証１部株価指数）
  $codeArray[] = "1003"; // 米ドル／円
  $codeArray[] = "1004"; // ユーロ／円
  $codeArray[] = "1005"; // JPX日経インデックス400
  $codeArray[] = "1006"; // 東証REIT指数
  $codeArray[] = "1007"; // 日経ジャスダック平均株価
  $codeArray[] = "1008"; // 東証２部総合指数
  $codeArray[] = "1009"; // 東証マザーズ指数
  $codeArray[] = "1010"; // 日経２２５先物（中心限月）夜無
  $codeArray[] = "1011"; // 日経２２５先物（中心限月）
  $codeArray[] = "1019"; // 東証マザーズ指数先物
  $codeArray[] = "1020"; // ミニ２２５先物（中心限月）夜無
  $codeArray[] = "1021"; // ミニ２２５先物（中心限月）
  $codeArray[] = "1028"; // JPX日経400先物
  $codeArray[] = "1029"; // ＴＯＰＩＸ先物
  $codeArray[] = "1030"; // WTI原油
  $codeArray[] = "1031"; // ＮＹ金先物
  $codeArray[] = "1052"; // 豪ドル／円
  $codeArray[] = "1081"; // ダウ工業株３０種平均
  $codeArray[] = "1082"; // ＮＡＳＤＡＱ総合指数
  $codeArray[] = "1083"; // ダウ輸送株指数
  $codeArray[] = "1090"; // アメリカ2年国債利回り
  $codeArray[] = "1091"; // アメリカ10年国債利回り

  return $codeArray;
}


//////////////// 銘柄名称を取得するサブルーチン

function getMeigaraNm($targetCode) {

        switch($targetCode) {

          case '1001':
            return '日経２２５種平均株価';
            break;
          case '1002':
            return 'ＴＯＰＩＸ（東証１部株価指数）';
            break;
          case '1003':
            return '米ドル／円';
            break;
          case '1004':
            return 'ユーロ／円';
            break;
          case '1005':
            return 'JPX日経インデックス400';
            break;
          case '1006':
            return '東証REIT指数';
            break;
          case '1007':
            return '日経ジャスダック平均株価';
            break;
          case '1008':
            return '東証２部総合指数';
            break;
          case '1009':
            return '東証マザーズ指数';
            break;
          case '1010':
            return '日経２２５先物（中心限月）夜無';
            break;
          case '1011':
            return '日経２２５先物（中心限月）';
            break;
          case '1019':
            return '東証マザーズ指数先物';
            break;
          case '1020':
            return 'ミニ２２５先物（中心限月）夜無';
            break;
          case '1021':
            return 'ミニ２２５先物（中心限月）';
            break;
          case '1030':
            return 'WTI原油';
            break;
          case '1031':
            return 'ＮＹ金先物';
            break;
          case '1052':
            return '豪ドル／円';
            break;
          case '1081':
            return 'ダウ工業株３０種平均';
            break;
          case '1082':
            return 'ＮＡＳＤＡＱ総合指数';
            break;
          case '1083':
            return 'ダウ輸送株指数';
            break;
          case '1090':
            return 'アメリカ2年国債利回り';
            break;
          case '1091':
            return 'アメリカ10年国債利回り';
            break;

        }  // -- end of switch()

/* **** 2020.07.29 増田足提供サービス終了 ****
    $preResult = file_get_contents("https://kabuyoho.ifis.co.jp/index.php?action=tp1&sa=report_chart&bcode=".$targetCode);
    $dom = new DOMDocument;
    @$dom->loadHTML($preResult); // @：ワーニング無視
    $xpath = new DOMXPath($dom);

    return $xpath->query("//div[contains(@class, 'stock_name left')]")[0]->nodeValue;
*/

  //$result = file_get_contents("https://stocks.finance.yahoo.co.jp/stocks/detail/?code=".$targetCode);
    $result = mb_convert_encoding(file_get_contents('https://finance.yahoo.co.jp/quote/'.$targetCode.'/chart'), "HTML-ENTITIES", "auto");
    $dom = new DOMDocument;
    @$dom->loadHTML($result); // @：ワーニング無視
    $xpath = new DOMXPath($dom);

    // <div class="DL5lxuTC"><h1 class="_6uDhA-ZV">東京エレクトロン(株)</h1></div>
    //return (@$xpath->query("//h1[contains(@class, '_6uDhA-ZV')]")[0]->nodeValue);

    // 2023.11.26 Yahoo!ページレイアウト変更対応
    return str_replace('の株価チャート', '', @$xpath->query("//h1[contains(@class, '_2IiVI_CY _1iM_QJBJ')]")[0]->nodeValue);

}


//////////////// 銘柄名称（scDisplay用短縮名称）を取得するサブルーチン

//               2021.06.10 SAKURA_API化して銘柄マスターに一元化

function getMeigaraShortNm($targetCode) {

  $url = "https://ik1-326-23246.vs.sakura.ne.jp/SAKURA_API/MY_MA_getMeigaraNm.php?TARGET_CD=".$targetCode;

  // curl初期化
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //「無：ｴﾗｰ bool(true)」「有：string(647) "ｴﾗｰ」

  // curlリクエスト
  $result = curl_exec($ch);

  // curl後始末
  curl_close($ch);

  // ========
  // ======== 文字化け対策のためHTML-ENTITIESで処理する
  // ========
  $result = mb_convert_encoding($result, 'HTML-ENTITIES', 'UTF-8');

  $dom = new DOMDocument;
  libxml_use_internal_errors( true ); // これと：ワーニング無視
  $dom->loadHTML($result);
  libxml_clear_errors();              //   これ：ワーニング無視
  $xpath = new DOMXPath($dom);

  $result = new stdClass;
  $result->meigara_nm = $xpath->query('//row')[0]->getElementsByTagName("meigara_nm")->item(0)->nodeValue;
  $result->meigara_name = $xpath->query('//row')[0]->getElementsByTagName("meigara_name")->item(0)->nodeValue;

  return $result->meigara_nm;


/*

        //////// 銘柄マスタを参照するAPI方式に改善されました

        switch($targetCode) {

          case "9142":
              :
              :
              :

        return "■".$targetCode."■";
*/
}

?>
