<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
    define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
  //define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
  //define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/_html/');

    if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {echo '<body><div>invalid env.<br>APP_ROOT['.APP_ROOT.']<br>invalid HTML_ROOT['.HTML_ROOT.']<br></div></body>';}

  //define('APP_TYPE', 'BillionStuffRsv'); // ¶ENV判断に使う意味が大きい
  //define('DB_SERVER', 'mysql154.phy.lolipop.lan');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 定数設定
    // ==== ==== ==== ====
    define('DOMAIN_ROOT', HTML_ROOT.'domain/');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
  //include(APP_ROOT.'COMMON/Log/CmxLog.php');
  //include(APP_ROOT.'COMMON/Cfg/CmxCfg.php');
  //include(APP_ROOT.'COMMON/Mail/CmxMailSender.php');
  //include(APP_ROOT.'COMMON/Mail/_MAILconf.php');
    include(APP_ROOT.'COMMON/Util/webAccessUtil.php');
  //include(APP_ROOT.'COMMON/Util/CMX_common.php');




if (isset($_REQUEST['TARGET_CD'])) {

    $targetCode = $_REQUEST["TARGET_CD"];

    $currentLogName = date('Ymd_Hi');
    $cookieFile = HTML_ROOT.'_cfg/COOKIE_GMO.php';


      // ==========================================================
      // ======== 2020.10.09 GMOのログオン取得を共通util化 ========

      $result = getGMO_Logon(NULL, $cookieFile);
      $baseUrl            = $result->BASE_URL;
      $cookieParamStrBuff = $result->COOKIE_BUFF;

      // ======== 2020.10.09 GMOのログオン取得を共通util化 ========
      // ==========================================================




                  $url = $baseUrl."ajax/spa/tabStockPriceInfo.do";
                  $ch = curl_init($url);

                  curl_setopt($ch, CURLOPT_COOKIE, $cookieParamStrBuff);
                  // ここひっかかった、POSTパラメータは「application/x-www-form-urlencodedで送信する場合。」にしないとダメ
                  curl_setopt($ch, CURLOPT_POSTFIELDS, 'securityCode='.$targetCode.'&meigaraCode=0'.$targetCode.'00&marketCode=001');
                  // ■解決法■postパラメタに設定していない「1」が入る 2020.06.15 ブルックスのサイト
                  curl_setopt_array($ch, array( CURLOPT_RETURNTRANSFER => true,
                                                CURLOPT_FOLLOWLOCATION => true,
                                                CURLOPT_AUTOREFERER    => true ) );
                  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); // formからのsubmitなのでPOSTリクエスト
                  curl_setopt($ch, CURLOPT_HEADER, true);          // 次回のsession用にレスポンスヘッダも文字列として得るよう指示

                  // ======== リクエスト発行
                  $result = curl_exec($ch);

//file_put_contents(__DIR__.'/resultFILE.txt', $result);

                  // curl後始末
                  curl_close($ch);




                // ----------------------------------------------------------------
                // ---------------------------------------------------------------- 個別銘柄のページから情報を抜き出してhtmlを生成する
                // ----------------------------------------------------------------

                  echo "<meta name=\"viewport\" content=\"width=device-width\">";


                  echo "<br>";
                  echo "<br>";


                  echo "<style>";
                  echo "td,th{border-top:1px solid #666; padding:10px;}";
                  echo "td{text-align: right;}";
                  echo "tr:last-child td,tr:last-child th{border-bottom:1px solid #666;}";
                  echo "</style>";




                  $dom = new DOMDocument;
                  libxml_use_internal_errors( true ); // これと：ワーニング無視
                  $dom->loadHTML($result);
                  libxml_clear_errors();              //   これ：ワーニング無視
                  $xpath = new DOMXPath($dom);


                        // ---- body 内の
                        //       <td id="currenPrice" colspan="2" class="u-ta-r u-fz18 u-fw-b genzai"> タグ内の値を取得
                        //
                        $node = $xpath->query("//body//td[contains(@id, 'currenPrice')]")[0];
                        $currenPrice = is_null($node) ? "" : $node->nodeValue;


                        // ---- body 内の
                        //       <div id="dayPrevBalance" class="is-down"> タグ内の値を取得
                        //
                        $node = $xpath->query("//body//div[contains(@id, 'dayPrevBalance')]")[0];
                        $dayPrevBalance = is_null($node) ? "" : $node->nodeValue;
                        // 増減割合は削除
                        $dayPrevBalance = mb_substr($dayPrevBalance, 0, mb_strpos($dayPrevBalance, '(') - 1);


                        // ---- body 内の
                        //       <td id="atTheMarketAskVolume" class="sel"> タグ内の値を取得
                        //
                        $node = $xpath->query("//body//td[contains(@id, 'atTheMarketAskVolume')]")[0];
                        $atTheMarketAskVolume = is_null($node) ? "" : $node->nodeValue;

                        // ---- body 内の
                        //       <td id="atTheMarketBidVolume" class="buy"> タグ内の値を取得
                        //
                        $node = $xpath->query("//body//td[contains(@id, 'atTheMarketBidVolume')]")[0];
                        $atTheMarketBidVolume = is_null($node) ? "" : $node->nodeValue;

                        // ---- body 内の
                        //       <td id="overVolume" class="sel"> 内の
                        //         <span> タグ内の値を取得
                        //
                        $node = $xpath->query("//body//td[contains(@id, 'overVolume')]//span/text()")[0];
                        $overVolume = is_null($node) ? "" : $node->nodeValue;

                        // ---- body 内の
                        //       <td id="underVolume" class="buy"> 内の
                        //         <span> タグ内の値を取得
                        //
                        $node = $xpath->query("//body//td[contains(@id, 'underVolume')]//span/text()")[0];
                        $underVolume = is_null($node) ? "" : $node->nodeValue;

                  echo "<table>\n";
                  echo "  <tr bgcolor=#e1f0f5>";
                  echo "    <th>".$targetCode."</th>";
                  echo "    <td id='currenPrice'>".$currenPrice."</td>";
                  echo "    <td id='dayPrevBalance'>(".$dayPrevBalance.")</td>";
                  echo "  </tr>\n";
                  echo "  <tr>";
                  echo "    <th>売数量</th>";
                  echo "    <th>&nbsp;</th>";
                  echo "    <th>買数量</th>";
                  echo "  </tr>\n";
                  echo "  <tr>";
                  echo "    <td id='overVolume'>".$overVolume."</td>";
                  echo "    <th>ＯＶＥＲ</th>";
                  echo "    <td>&nbsp;</td>";
                  echo "  </tr>\n";
                  echo "  <tr>";
                  echo "    <td id='atTheMarketAskVolume'>".$atTheMarketAskVolume."</td>";
                  echo "    <th>成行</th>";
                  echo "    <td id='atTheMarketBidVolume'>".$atTheMarketBidVolume."</td>";
                  echo "  </tr>\n";
                  echo "  <tr>";
                  echo "    <td>&nbsp;</td>";
                  echo "    <th>ＵＮＤＥＲ</th>";
                  echo "    <td id='underVolume'>".$underVolume."</td>";
                  echo "  </tr>\n";
                  echo "</table>\n";

} else {

  echo "No set, TARGET_CD<br>";
}


?>
