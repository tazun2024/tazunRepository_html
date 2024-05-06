<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
    define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
  //define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
  //define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/html/');

    if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {echo '<body><div>invalid env.<br>APP_ROOT['.APP_ROOT.']<br>invalid HTML_ROOT['.HTML_ROOT.']<br></div></body>';}


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 定数設定
    // ==== ==== ==== ====
    define('DOMAIN_ROOT', HTML_ROOT.'domain/');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Util/webAccessUtil.php');
    include(DOMAIN_ROOT.'masudaashi/search_common.php');


    $cookieFile = HTML_ROOT.'_cfg/COOKIE_GMO.php';


    $timerWait = @$_REQUEST['TIMER'] ?: '60'; // ▼そこでエルビス演算子ですよ
    $currentLogName = date('Ymd_Hi');


    // ==========================================================
    // ======== 2020.10.09 GMOのログオン取得を共通util化 ========

    $result = getGMO_Logon(NULL, $cookieFile);
    $baseUrl            = $result->BASE_URL;
    $cookieParamStrBuff = $result->COOKIE_BUFF;

    // ======== 2020.10.09 GMOのログオン取得を共通util化 ========
    // ==========================================================


    // =============================================================================
    // ======== 2020.09.29 ウォッチリストに登録されている銘柄も自動的に登録 ========

    //$targetCodeArr = getGMO_PortfolioList($baseUrl, $cookieParamStrBuff); // ayaya
    $resultList = getGMO_PortfolioList($baseUrl, $cookieParamStrBuff);

    // ======== 2020.09.29 ウォッチリストに登録されている銘柄も自動的に登録 ========
    // =============================================================================




    $url = $baseUrl."kabu/portfolioList.do"; // 「ウォッチリスト登録・編集」のリンクurl
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_COOKIE, $cookieParamStrBuff); // session1で取得後に処理した文字列をCookie情報として引き渡す
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');        // リンクurlなのでGETリクエスト
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        // curl_execの結果を文字列で得るよう指示
    curl_setopt($ch, CURLOPT_HEADER, true);                // 次回のsession用にレスポンスヘッダも文字列として得るよう指示

    // ======== リクエスト発行
    $result = curl_exec($ch);

    // curl後始末
    curl_close($ch);


    // ----------------------------------------------------------------
    // ---------------------------------------------------------------- 個別銘柄のページから情報を抜き出して表示用文字列生成する
    // ----------------------------------------------------------------

    // ========
    // ======== GMOのサイトはSJISだ！
    // ======== 文字化け対策のためHTML-ENTITIESで処理する
    // ========
    $result = mb_convert_encoding($result, "HTML-ENTITIES", "SJIS");

    $dom = new DOMDocument;
    libxml_use_internal_errors( true ); // これと：ワーニング無視

    $dispStrArr = array(); // 2023.08.29 Empty string supplied as input 対応
    if ($result != null) {

        $dom->loadHTML($result); // ★null✓

        libxml_clear_errors();              //   これ：ワーニング無視
        $xpath = new DOMXPath($dom);

/* 2024.05.06 処理の一本化
        // ---------------- 銘柄コードを配列に取得する
        //       各 <tr class="is-selectable" data-selectable>～</tr> タグ内にある
        //         各 <td id="securityCode～" class="col-01">9142</td> タグをnodeとしてarrayで取得
        $nodeArray_securityCode = $xpath->query("//tbody//tr[contains(@class, 'is-selectable')]//td[contains(@id, 'securityCode')]");

        // ---------------- 現在値を配列に取得する
        //       各 <tr class="is-selectable" data-selectable>～</tr> タグ内にある
        //         各 <div id="currentPrice～">2,945<span class="unit">円</span></div> タグの内容だけをnodeとしてarrayで取得
        $nodeArray_currentPrice = $xpath->query("//tbody//tr[contains(@class, 'is-selectable')]//div[contains(@id, 'currentPrice')]");

        // ---------------- 昨営業日との差を配列に取得する
        //       各 <tr class="is-selectable" data-selectable>～</tr> タグ内にある
        //         各 <div id="previousDaysDiffMargin～" class="is-minus">-33 (-1.1<span class="percent">%</span>)</div> タグの内容だけをnodeとしてarrayで取得
        $nodeArray_previousDaysDiffMargin = $xpath->query("//tbody//tr[contains(@class, 'is-selectable')]//div[contains(@id, 'previousDaysDiffMargin')]");
*/
        $nodeArray_securityCode           = $resultList->CODE;
        $nodeArray_currentPrice           = $resultList->PRICE;
        $nodeArray_previousDaysDiffMargin = $resultList->MARGIN;


        $idxNum = 0;
        $dispStrArr = array();
        foreach($nodeArray_securityCode as $node){

            $currentPriceStr                         = trim(str_replace(",", "", $nodeArray_currentPrice[$idxNum]->nodeValue));
            $currpreviousDaysDiffMargintPriceSignStr = left($nodeArray_previousDaysDiffMargin[$idxNum]->nodeValue, 1);
            $currpreviousDaysDiffMargintPriceStr     = trim(str_replace(",", "", str_replace("+", "", str_replace("-", "", $nodeArray_previousDaysDiffMargin[$idxNum]->nodeValue))));

            if ($currpreviousDaysDiffMargintPriceStr == "") {
              // ==== 寄付き前の価格表示のみのケース対応
              $currpreviousDaysDiffMargintPriceSignStr = " ";
            } elseif ($currpreviousDaysDiffMargintPriceSignStr == "0") {
              // ==== 結果的に±0の場合に対応
              $currpreviousDaysDiffMargintPriceSignStr = "Z";
            }

            // 各nodeの値を元に表示する固定長文字列を生成する（Ajax通信update対応でない初回のみgetMeigaraShortNm()）
            $dispStrArr[] = ((@$_REQUEST['ACTION'] ?: '' == "UPDATE") ? '―――――' : getMeigaraShortNm(left(($node->nodeValue), 4)))."|".
                            left(($node->nodeValue), 4)."|".right("     ".left($currentPriceStr, mb_strpos($currentPriceStr, "円")), 6)."|".
                            $currpreviousDaysDiffMargintPriceSignStr.right("    ".left($currpreviousDaysDiffMargintPriceStr, mb_strpos($currpreviousDaysDiffMargintPriceStr, " ")), 5);

            $idxNum ++;
        }
    }

    // ----------------------------------------------------------------
    // ---------------------------------------------------------------- 個別銘柄のページから情報を抜き出して表示用文字列生成する
    // ----------------------------------------------------------------








    // ================ 2020.07.04 Ajax通信update対応
    if (@$_REQUEST['ACTION'] ?: '' == "UPDATE") { // ▼そこでエルビス演算子ですよ

        header("Content-Type: application/json; charset=UTF-8");

        $resultArr = array();

        foreach($dispStrArr as $dispStr){


            $dispStr = right($dispStr, mb_strlen($dispStr) - mb_strpos($dispStr, "|") - 1);
            $targetCode = left($dispStr, mb_strpos($dispStr, "|"));
            $dispStr = right($dispStr, mb_strlen($dispStr) - mb_strpos($dispStr, "|") - 1);


            if ($targetCode == '9765') {

                    $resultArr['#price'.$targetCode] = getPriceMsg(left($timerWait.'XX', 2).substr($dispStr, 2, 4));  // 「5」で有用    「100」は '10'
                  //$resultArr['#price'.$targetCode] = getPriceMsg(right('XX'.$timerWait, 2).substr($dispStr, 2, 4)); // 「100」で有用  「5」は   ' 5'

            } else {

                    $resultArr['#price'.$targetCode] = getPriceMsg(substr($dispStr, 0, 6));

            }


            $dispStr = right($dispStr, mb_strlen($dispStr) - mb_strpos($dispStr, "|") - 1);

            if ($targetCode == '9765') {

                     $resultArr['#diff'.$targetCode] = getPriceMsg(date('s').right('    '.str_replace(' ', '', substr($dispStr, 0, 6)), 4));

            } else {

                     $resultArr['#diff'.$targetCode]  = getPriceMsg(substr($dispStr, 0, 6));
            }
        }




        echo json_encode($resultArr); //jsonオブジェクト化。必須。
        exit; //処理の終了

    }
    // ================ 2020.07.04 Ajax通信update対応








    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
    echo "<!-- saved from url=(0029)http://www9.ocn.ne.jp/~tazun/ -->";
    echo "<HTML xmlns=\"http://www.w3.org/1999/xhtml\">\n";
    echo "<HEAD>\n";
    echo "<TITLE>SC Display</TITLE>\n";
    echo "<meta NAME=\"ROBOTS\" CONTENT=\"NOINDEX,NOFOLLOW,NOARCHIVE\">\n";
    echo "<meta name=\"viewport\" content=\"width=device-width\">\n";
    echo "<meta http-equiv=Content-Type content=\"text/html; charset=UTF-8\">\n";
    echo "</HEAD>\n";
    echo "<BODY>\n";

    echo "<style>";
    echo "table{width:200px; border-collapse:collapse; border-spacing:0; background-color:#474747;}";
    echo "table th.MEIGARANM{color:#ffffff; font-family:'HG丸ｺﾞｼｯｸM-PRO','HGMaruGothicMPRO','ヒラギノ丸ゴ ProN W4','Hiragino Maru Gothic Pro W4','ヒラギノ丸ゴ Pro W4'; font-size:5.2em; transform:scale(0.9,1);}";
    echo "table th.MEIGARACD{color:#ffffff; font-family:'HG丸ｺﾞｼｯｸM-PRO','HGMaruGothicMPRO','ヒラギノ丸ゴ ProN W4','Hiragino Maru Gothic Pro W4','ヒラギノ丸ゴ Pro W4'; font-size:4em; transform:scale(1.0,0.5); padding:0px 10px; line-height:32px}";
    echo "div.FLOAT {float:left; margin:2px;}";
    echo "div.FLOATCLEAR {clear:both; color:lightgray;}";
    echo "</style>";


    // 夜間メンテナンス等での銘柄取得不能時用にダミーファイルをしておく
    $cfgFile = __DIR__."/scDisplay.cfg";

    if (count($dispStrArr) == 0) {

      include($cfgFile);

    } else {

      unlink($cfgFile);
      error_log("<?PHP". "\n", 3, $cfgFile);
      foreach ($dispStrArr as $dispStr) {
        $pos = mb_strpos($dispStr, "|");
        error_log("\$dispStrArr[] = \"".left($dispStr, mb_strpos($dispStr, "|"))."|".mb_substr($dispStr, $pos + 1, 4)."|XXXXXX|XXXXXX"."\";"."\n", 3, $cfgFile);
      }
      error_log("?>". "\n", 3, $cfgFile);
    }


    // ================ 2020.07.04 Ajax通信update対応
    echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js\"></script>\n";

    echo "<script>\n";

    echo "$(document).ready(function() {";

    // 2021.12.02 JQueryでgetJSONのキャッシュをfalseに設定するにはどうすればよいですか？

    // コードを有効にするにはトリガーが必要です。
    // これにより、将来のすべてのajaxでキャッシュを無効にできます
    echo "$.ajaxSetup({ cache: false });";

    // 一定時間ごとにポーリング
    echo "$(() => poll());";
    echo "async function poll() {";
    echo "  try {";
    echo "    const response = await $.getJSON('scDisplay.php?ACTION=UPDATE&TIMER=".$timerWait."');";
    echo "    console.log(response);";


    // こうすればOK（Object.keysであればプロトタイプチェーンは対象外なので、そのオブジェクトだけが保持しているものだけでループを回せる。）
    echo "    Object.keys(response).forEach(function (key) {\n";
    echo "      $(key).html(response[key]);";
    echo "   });\n";

    echo "  } catch (e) {";
    echo "    console.error(e);";
    echo "  } finally {";
    echo "    setTimeout(poll, ".($timerWait * 1000).");";
    echo "  }";
    echo "}\n";

    echo "});";

    echo "</script>\n";
    // ================ 2020.07.04 Ajax通信update対応


    foreach($dispStrArr as $dispStr){

        echo "<div class=FLOAT>";
        echo "<table>";

        echo "  <tr>";
        echo "    <th colspan=8 class=MEIGARANM>".left($dispStr, mb_strpos($dispStr, "|"))."</th>";
        $dispStr = right($dispStr, mb_strlen($dispStr) - mb_strpos($dispStr, "|") - 1);
        echo "  </tr>";

        $targetCode = left($dispStr, mb_strpos($dispStr, "|"));

        echo "  <tr>";
        echo "    <th colspan=8 class=MEIGARACD>".$targetCode."</th>";
        $dispStr = right($dispStr, mb_strlen($dispStr) - mb_strpos($dispStr, "|") - 1);
        echo "  </tr>";

        echo "  <tr id=price".$targetCode.">";
        echo getPriceMsg(substr($dispStr, 0, 6));
        echo "  </tr>";
        $dispStr = right($dispStr, mb_strlen($dispStr) - mb_strpos($dispStr, "|") - 1);

        echo "  <tr>";
        echo "    <td colspan=8>&nbsp;</td>";
        echo "  </tr>";

        echo "  <tr id=diff".$targetCode.">";
        echo getPriceMsg(substr($dispStr, 0, 6));
        echo "  </tr>";

        echo "  <tr>";
        echo "    <td colspan=8>&nbsp;</td>";
        echo "  </tr>";

        echo "</table>";
        echo "</div>";
    }


    echo "<div class=FLOATCLEAR>";
    echo "<br>";
    foreach($dispStrArr as $dispStr){
      echo $dispStr."<br>";
    }
    echo "</div>";

    echo "</BODY>\n";
    echo "</html>\n";




//////////////// 6桁の価格文字列から<tr>タグ内に出力するhtmlコードを返却するサブルーチン

function getPriceMsg($priceStr) {

    $result = "<td class=MARGIN>&nbsp;</td>";

    for ($idx = 0; $idx <= 5; $idx ++) {

        $result = $result."<td class=DECIMAL><img src=".getImgFile(substr($priceStr, $idx, 1))."></td>";
    }

    $result = $result."<td class=MARGIN>&nbsp;</td>";

    return $result;
}




//////////////// 銘柄名称（scDisplay用短縮名称）を取得するサブルーチン

function getImgFile($char) {

    $fileRoot = './img/';

    switch($char) {

        case '0':
        case '1':
        case '2':
        case '3':
        case '4':
        case '5':
        case '6':
        case '7':
        case '8':
        case '9':
        case 'Z':
          return $fileRoot.'dec_'.$char.'.png';
          break;

        case '+':
          return $fileRoot.'dec_P.png';
          break;

        case '-':
          return $fileRoot.'dec_M.png';
          break;

        case '.':
          return $fileRoot.'dec_D.png';
          break;

    }  // -- end of switch()

    return $fileRoot.'dec_B.png';
}

?>
