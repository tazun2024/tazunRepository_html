<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
    define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
  //define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
  //define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/_html/');

    if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {echo '<body><div>invalid env.<br>APP_ROOT['.APP_ROOT.']<br>invalid HTML_ROOT['.HTML_ROOT.']<br></div></body>';}

  //define('APP_TYPE', 'BillionStuffRsv');
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
    include(APP_ROOT.'COMMON/Util/CMX_common.php');


////$yorimaeGraphOutputDir = DOMAIN_ROOT.'marketInfo/_fileWork/';
////$marketOrderDir    = DOMAIN_ROOT.'marketOrder/_fileWork/';
////$saiteizanChartDir = DOMAIN_ROOT.'saiteizanDailyChart/_fileWork/';
////$yahooChartDir     = DOMAIN_ROOT.'yahooDailyChart/_fileWork/'.$targetDate.'/';


        // ======== いったんここ、前場分（08:00～09:15まで5分ごとのmarketOrder情報が取得済みである想定）
        define('T_dataSt_zenba', "0800");
        define('T_dataEd_zenba', "0915");
        // ======== いったんここ、後場分（12:00～12:45まで5分ごとのmarketOrder情報が取得済みである想定）
        define('T_dataSt_goba', "1200");
        define('T_dataEd_goba', "1245");
        // ======== いったんここ、データ取得周期 =5分
        define('T_dataCycle', 300);




  $targetCode = @$_REQUEST["TARGET_CODE"];
  if (mb_strlen($targetCode) <> 4 or !is_numeric($targetCode)) $targetCode = "";

  $targetDate = @$_REQUEST["TARGET_DATE"];
  if (mb_strlen($targetDate) <> 8 or !is_numeric($targetDate)) {

    $targetDate = getBcakToDaysYMD(date('Ymd1200'), 0);

    if ($targetCode <> "") {

            // ================ 裁定残のグラフを取得する
            $result = file_get_contents("https://karauri.net/".$targetCode."/sokuhou/");

            $dom = new DOMDocument;
            libxml_use_internal_errors( true ); // これと：ワーニング無視
            $dom->loadHTML($result);
            libxml_clear_errors();              //   これ：ワーニング無視
            $xpath = new DOMXPath($dom);


            // ---- body 内の
            //       <img class="oc" src="～> タグ内のsrcの値を取得
            //
            $node = $xpath->query("//body//img[contains(@class, 'oc')]/@src")[0];
            if (!is_null($node))  file_put_contents(DOMAIN_ROOT.'saiteizanDailyChart/_fileWork/'.$targetCode.".".date('Ymd', strtotime($targetDate)).".png", file_get_contents($node->nodeValue));
    }
  }


    $yorimaeGraphOutputDir = DOMAIN_ROOT.'marketInfo/_fileWork/';
    $marketOrderDir    = DOMAIN_ROOT.'marketOrder/_fileWork/'.$targetDate.'/';
    $saiteizanChartDir = DOMAIN_ROOT.'saiteizanDailyChart/_fileWork/';
    $yahooChartDir     = DOMAIN_ROOT.'yahooDailyChart/_fileWork/'.$targetDate.'/';

  if ($targetCode <> "") {

        // ================ 自動処理で取得済みの「寄付き前marketOrder情報」を元に描画用グラフデータを生成する

        // ・日付が土曜日曜や祝日などは直近の過去営業日で処理する必要がある

        $sourDataArr = array();
        $canvasDataCountX = 0;

        // ======== ①前場分のデータを生成を処理
        for ($showTargetDtime = strtotime($targetDate.T_dataSt_zenba); $showTargetDtime <= strtotime($targetDate.T_dataEd_zenba); $showTargetDtime += T_dataCycle) {

          $canvasDataCountX ++;

          $sourFile = $targetCode."Response_".date('Ymd_Hi', $showTargetDtime).".html";
          if (file_exists($marketOrderDir.$sourFile)) {

          //echo $sourFile." FOUND!<br>";

            $result = file_get_contents($marketOrderDir.$sourFile);
            $sourData = createMarketOrderData($result);

            if (!is_null($sourData)) {

            //$sourData->atTheMarketAskVolume = $sourData->atTheMarketAskVolume * 20; // **テスト用にデータ数を拡大**
            //$sourData->atTheMarketBidVolume = $sourData->atTheMarketBidVolume * 20; // **テスト用にデータ数を拡大**

              // ==== 生成した描画用データを日付時刻をキーとする連想配列に格納する
              $sourDataArr[date('Y-m-d H:i', $showTargetDtime)] = $sourData;
            }

          } else {

           //echo $sourFile." NOT EXIST<br>";

          }

          // （対象ファイルが取得できていない場合、描画用データが生成できなかった場合は、描画時にﾊﾞｯﾌｧした前ﾙｰﾌﾟ分を利用する）
        }


        // ======== ②前場後場の境界分として描画対象データエリアを進めておく
        $canvasDataCountX ++;


        // ======== ③後場分のデータを生成を処理
        for ($showTargetDtime = strtotime($targetDate.T_dataSt_goba); $showTargetDtime <= strtotime($targetDate.T_dataEd_goba); $showTargetDtime += T_dataCycle) {

          $canvasDataCountX ++;

          $sourFile = $targetCode."Response_".date('Ymd_Hi', $showTargetDtime).".html";
          if (file_exists($marketOrderDir.$sourFile)) {

          //echo $sourFile." FOUND!<br>";

            $result = file_get_contents($marketOrderDir.$sourFile);
            $sourData = createMarketOrderData($result);

            // ==== 生成した描画用データを日付時刻をキーとする連想配列に格納する
            if (!is_null($sourData)) $sourDataArr[date('Y-m-d H:i', $showTargetDtime)] = $sourData;

          } else {

           //echo $sourFile." NOT EXIST<br>";

          }

          // （対象ファイルが取得できていない場合、描画用データが生成できなかった場合は、描画時にﾊﾞｯﾌｧした前ﾙｰﾌﾟ分を利用する）
        }




        // ================ 生成した描画用グラフデータを元にグラフを描画する

        // 画像のサイズ
      //$canvasWidth = 240;   // Yahooチャートが右側に来る場合のレイアウト
      //$canvasHeight = 320;
        $canvasWidth = 120;
        $canvasHeight = 200;

        // ==== 画像を作成します
        $image = imagecreatetruecolor($canvasWidth, $canvasHeight);

        // ==== 使用する色を割り当てます
        $backColor = imagecolorallocate($image, 0xE8, 0xE8, 0xD0);
        $brightCol = imagecolorallocate($image, 0xE0, 0xE0, 0xD0);
        $posiCol = imagecolorallocate($image, 0x80, 0x80, 0x80);
        $negaCol = imagecolorallocate($image, 0xD0, 0xD0, 0xD0);

        $atTheMarketAskVolumeCol = imagecolorallocate($image, 0x40, 0x80, 0xFF); // 青色
        $overVolumeCol           = imagecolorallocate($image, 0x80, 0xA0, 0xFF); // 青白色
        $underVolumeCol          = imagecolorallocate($image, 0xFF, 0xA0, 0xA0); // 赤白色
        $atTheMarketBidVolumeCol = imagecolorallocate($image, 0xFF, 0x80, 0x80); // 赤色


        // ======== 背景色を描画する
        imagefilledrectangle($image, 0, 0, $canvasWidth, $canvasHeight, $backColor);




        $stepWidth  = $canvasWidth  / $canvasDataCountX;
        $x = 0;

        // ================ ①前場分の描画
        $sourDataBuff = null;
        $drawLabels = false; // 前場寄り付きの直前の位置にエリアにラベルを描く
        $drawLine   = false; // 前場寄り付きの直後の位置に縦線を描く
        for ($showTargetDtime = strtotime($targetDate.T_dataSt_zenba); $showTargetDtime <= strtotime($targetDate.T_dataEd_zenba); $showTargetDtime += T_dataCycle) {

            if (array_key_exists(date('Y-m-d H:i', $showTargetDtime), $sourDataArr)) {

              $sourData = $sourDataArr[date('Y-m-d H:i', $showTargetDtime)];
              $sourDataBuff = $sourData;

            } else {

              if (is_null($sourDataBuff)) {

                $x += $stepWidth;
                continue;
              }

              $sourData = $sourDataBuff;
            }


            // ******************************** 同一コード ここから↓
            if ($sourData->onTrade) {

              // ---- 寄り付き後（成り行きデータなし）

              $canvasDataSizeY = $sourData->overVolume + $sourData->underVolume;

              ImageFilledRectangle($image, $x, 0,      $x + ($stepWidth - 1), $canvasHeight / $canvasDataSizeY * $sourData->overVolume - 1, $overVolumeCol);
              $tempY = $canvasHeight / $canvasDataSizeY * $sourData->overVolume;
              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $canvasHeight,                                                $underVolumeCol);

            } else {

              // ---- 寄り付き前（成り行きデータあり）

              $canvasDataSizeY = $sourData->overVolume + $sourData->atTheMarketAskVolume + $sourData->atTheMarketBidVolume + $sourData->underVolume;

              ImageFilledRectangle($image, $x, 0     , $x + ($stepWidth - 1),          $canvasHeight / $canvasDataSizeY * $sourData->overVolume           - 1, $overVolumeCol);
              $tempY = $canvasHeight / $canvasDataSizeY * $sourData->overVolume;

              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $tempY + $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketAskVolume - 1, $atTheMarketAskVolumeCol);
              $tempY += $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketAskVolume;

              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $tempY + $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketBidVolume - 1 , $atTheMarketBidVolumeCol);
              $tempY += $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketBidVolume;

              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $tempY + $canvasHeight                                                          , $underVolumeCol);

            }
            // ******************************** 同一コード ここまで↑

            // ======== 前場寄り付きの直前の位置にエリアにラベルを描く
            if (!$drawLabels and strtotime(date('H:i', $showTargetDtime)) >= strtotime("0850")) {

              $drawLabels = true;
              $drawLabelsX  = $x;
              $drawLabelsY1 =                 $canvasHeight / $canvasDataSizeY * $sourData->overVolume  - 18; // 文字の高さ分くらい、、少し上にあげる
              $drawLabelsY2 = $canvasHeight - $canvasHeight / $canvasDataSizeY * $sourData->underVolume + 2;
            }

            // ======== 前場寄り付きの直後の位置に縦線を描く
            if (!$drawLine and strtotime(date('H:i', $showTargetDtime)) >= strtotime("0900")) {

              $drawLine = true;
              ImageLine($image, $x, 0, $x, $canvasHeight, $negaCol);
            }

            $x += $stepWidth;
        }


        // ================ ②前場後場の境界分
        $x += $stepWidth;


        // ================ ③後場の描画
        $sourDataBuff = null;
        $drawLine   = false; // 後場寄り付きの直後の位置に縦線を描く
        for ($showTargetDtime = strtotime($targetDate.T_dataSt_goba); $showTargetDtime <= strtotime($targetDate.T_dataEd_goba); $showTargetDtime += T_dataCycle) {


            if (array_key_exists(date('Y-m-d H:i', $showTargetDtime), $sourDataArr)) {

              $sourData = $sourDataArr[date('Y-m-d H:i', $showTargetDtime)];
              $sourDataBuff = $sourData;

            } else {

              if (is_null($sourDataBuff)) {

                $x += $stepWidth;
                continue;
              }

              $sourData = $sourDataBuff;
            }


            // ******************************** 同一コード ここから↓
            if ($sourData->onTrade) {

              // ---- 寄り付き後（成り行きデータなし）

              $canvasDataSizeY = $sourData->overVolume + $sourData->underVolume;

              ImageFilledRectangle($image, $x, 0,      $x + ($stepWidth - 1), $canvasHeight / $canvasDataSizeY * $sourData->overVolume - 1, $overVolumeCol);
              $tempY = $canvasHeight / $canvasDataSizeY * $sourData->overVolume;
              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $canvasHeight,                                                $underVolumeCol);

            } else {

              // ---- 寄り付き前（成り行きデータあり）

              $canvasDataSizeY = $sourData->overVolume + $sourData->atTheMarketAskVolume + $sourData->atTheMarketBidVolume + $sourData->underVolume;

              ImageFilledRectangle($image, $x, 0     , $x + ($stepWidth - 1),          $canvasHeight / $canvasDataSizeY * $sourData->overVolume           - 1, $overVolumeCol);
              $tempY = $canvasHeight / $canvasDataSizeY * $sourData->overVolume;

              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $tempY + $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketAskVolume - 1, $atTheMarketAskVolumeCol);
              $tempY += $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketAskVolume;

              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $tempY + $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketBidVolume - 1 , $atTheMarketBidVolumeCol);
              $tempY += $canvasHeight / $canvasDataSizeY * $sourData->atTheMarketBidVolume;

              ImageFilledRectangle($image, $x, $tempY, $x + ($stepWidth - 1), $tempY + $canvasHeight                                                          , $underVolumeCol);

            }
            // ******************************** 同一コード ここまで↑

            // ======== 前場寄り付きの直後の位置に縦線を描く
            if (!$drawLine and strtotime(date('H:i', $showTargetDtime)) >= strtotime("1230")) {

              $drawLine = true;
              ImageLine($image, $x, 0, $x, $canvasHeight, $negaCol);
            }

            $x += $stepWidth;
        }




        // ================ グラフ描画後の上書き描画をする

        // ======== 中心線
        imagerectangle($image, 0, $canvasHeight / 2, $canvasWidth, $canvasHeight / 2, $backColor);

        // ======== 凡例（グラフの描画に成功した場合のみ）
        if (isset($drawLabelsX) and isset($drawLabelsY1) and isset($drawLabelsY2)) {

          imagestring($image, 2, $drawLabelsX, $drawLabelsY1, "BID", $brightCol);
          imagestring($image, 2, $drawLabelsX, $drawLabelsY2, "ASK", $brightCol);
        }



        // ================ 完成したグラフ画像を保存
        $targetFileMarketOrderImg = $targetCode."Response.png";
        imagepng($image, $yorimaeGraphOutputDir.$targetFileMarketOrderImg);




                // ----------------------------------------------------------------
                // ---------------------------------------------------------------- htmlを生成する
                // ----------------------------------------------------------------

echo "<html>\n";
echo "<head>\n";
echo "<TITLE>".$targetCode." Market Info</TITLE>\n";
                  echo "<meta name=\"viewport\" content=\"width=device-width,initial-scale=0.5\">\n";


echo "</head>\n";
echo "<body>\n";


  echo '<table>';

  echo '<TR>';
  echo '<TD>';
  // ==== 日付指定用form表示
  echo "<nobr>\n";
  echo "<form action='./marketInfo.php' method='get'>";
  echo "TARGET DATE <input type='text' size='20' name='TARGET_DATE' value='".$targetDate."'>";
  echo "<input type='hidden' name='TARGET_CODE' value='".$targetCode."'>";
  echo "&nbsp;<input type='submit' value='日付 指定'>";
  echo "</form>";
  echo '</TD>';
  echo '<TD>';
  echo "&nbsp;</span><a href=http://aoyoko2.com/masudaashi/search.php?TARGET_CD=".$targetCode."><font size=-1>MY_MA</font></a>";
  echo "&nbsp;</span><a href=https://finance.yahoo.co.jp/quote/".$targetCode."/chart><font size=-1>Yahooチャート</font></a>";
  echo "</nobr>\n";
  echo '</TD>';
  echo '</TR>';
/*
  echo '<TR>';
  echo '<TD colspan=2>';
  echo '<A href=./marketInfo.php?TARGET_CODE=8035>[8035]</A>';
  echo '<A href=./marketInfo.php?TARGET_CODE=5713>[5713]</A>';
  echo '<A href=./marketInfo.php?TARGET_CODE=4186>[4186]</A>';
  echo '<A href=./marketInfo.php?TARGET_CODE=8184>[8184]</A>';
  echo '<A href=./marketInfo.php?TARGET_CODE=9828>[9828]</A>';
  echo '<A href=./marketInfo.php?TARGET_CODE=9449>[9449]</A>';
  echo '<A href=./marketInfo.php?TARGET_CODE=9072>[9072]</A>';
  echo '<A href=./marketInfo.php?TARGET_CODE=2910>[2910]</A>';
  echo '</TD>';
  echo '</TR>';
*/
  echo '</table>';


                  echo '<img src=./_fileWork/'.$targetFileMarketOrderImg.'>';

  if (file_exists($saiteizanChartDir.$targetCode.".".$targetDate.".png")) {

                  echo '<a href=https://karauri.net/'.$targetCode.'/sokuhou/><img src=../saiteizanDailyChart/_fileWork/'.$targetCode.'.'.$targetDate.'.png></a>';
  } else {

                  echo '<a href=https://karauri.net/'.$targetCode.'/sokuhou/><img src=../saiteizanDailyChart/_fileWork/no_chart.png></a>';
  }

                  echo '<br>';

  } // ---------------- end of 「    if ($targetCode <> '') {    」


echo '<a href=http://cmx.boy.jp/html/domain/marketOrder/getMarketOrder.php?TARGET_CD='.$targetCode.' target=_blank>板情報</a>';
echo "<span style='margin-right:60px;'></span><a href=https://karauri.net/chart_saitei/ target=_blank>市況：日経225株価と裁定残の推移</a><br>";



    // 
    // 2020.09.14 歩値対応
    // 


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

                        // ========
                        // ======== GMOのサイトはSJISだ！
                        // ======== 文字化け対策のためHTML-ENTITIESで処理する
                        // ========
                        $result = mb_convert_encoding($result, 'HTML-ENTITIES', 'SJIS');

                        $dom = new DOMDocument;
                        libxml_use_internal_errors( true ); // これと：ワーニング無視
                        $dom->loadHTML($result);
                        libxml_clear_errors();              //   これ：ワーニング無視
                        $xpath = new DOMXPath($dom);


                        // ---------------- 現在値を取得する
                        //       <td id="currenPrice">"28,490"<i></i></td><td><span>15:00</span></td>
                        $node_currenPrice = $xpath->query("//tbody//td[contains(@id, 'currenPrice')]")[0];

                        // ---------------- 現在値時刻を取得する
                        //       <td class="u-ta-r u-pr32"><span id="currenPriceDate" class="datetime">15:00</span></td>
                        $node_currenPriceDate = $xpath->query("//tbody//span[contains(@id, 'currenPriceDate')]")[0];

                        // ---------------- 歩値1を取得する
                        //       <td id="timeSeriesPrice1" class="u-ta-r u-fz18">1,302</td>
                        $node_timeSeriesPrice1 = $xpath->query("//tbody//td[contains(@id, 'timeSeriesPrice1')]")[0];

                        // ---------------- 歩値1時刻を取得する
                        //       <td class="u-ta-r u-pr32"><span id="timeSeriesPriceDate1" class="datetime">14:59</span></td>
                        $node_timeSeriesPriceDate1 = $xpath->query("//tbody//span[contains(@id, 'timeSeriesPriceDate1')]")[0];

                        // ---------------- 歩値2を取得する
                        //       <td id="timeSeriesPrice2" class="u-ta-r u-fz18">1,300</td>
                        $node_timeSeriesPrice2 = $xpath->query("//tbody//td[contains(@id, 'timeSeriesPrice2')]")[0];

                        // ---------------- 歩値2時刻を取得する
                        //       <td class="u-ta-r u-pr32"><span id="timeSeriesPriceDate2" class="datetime">14:59</span></td>
                        $node_timeSeriesPriceDate2 = $xpath->query("//tbody//span[contains(@id, 'timeSeriesPriceDate2')]")[0];

                        // ---------------- 歩値3を取得する
                        //       <td id="timeSeriesPrice3" class="u-ta-r u-fz18">1,299</td>
                        $node_timeSeriesPrice3 = $xpath->query("//tbody//td[contains(@id, 'timeSeriesPrice3')]")[0];

                        // ---------------- 歩値3時刻を取得する
                        //       <td class="u-ta-r u-pr32"><span id="timeSeriesPriceDate3" class="datetime">14:56</span></td>
                        $node_timeSeriesPriceDate3 = $xpath->query("//tbody//span[contains(@id, 'timeSeriesPriceDate3')]")[0];


            if ( !is_null($node_currenPrice)
             and !is_null($node_currenPriceDate)
             and !is_null($node_timeSeriesPrice1)
             and !is_null($node_timeSeriesPriceDate1)
             and !is_null($node_timeSeriesPrice2)
             and !is_null($node_timeSeriesPriceDate2)
             and !is_null($node_timeSeriesPrice3)
             and !is_null($node_timeSeriesPriceDate3) ) {

                echo '<table>';
                echo '<tr>';
                echo '<td><span style=margin-right:360px;></span>現在値</td><td>'.$node_currenPriceDate->nodeValue.'&nbsp;</td><td>'.left($node_currenPrice->nodeValue, mb_strpos($node_currenPrice->nodeValue, ' ') - 2).'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><span style=margin-right:360px;></span>歩値1</td><td>'.$node_timeSeriesPriceDate1->nodeValue.'&nbsp;</td><td>'.$node_timeSeriesPrice1->nodeValue.'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><span style=margin-right:360px;></span>歩値2</td><td>'.$node_timeSeriesPriceDate2->nodeValue.'&nbsp;</td><td>'.$node_timeSeriesPrice2->nodeValue.'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><span style=margin-right:360px;></span>歩値3</td><td>'.$node_timeSeriesPriceDate3->nodeValue.'&nbsp;</td><td>'.$node_timeSeriesPrice3->nodeValue.'</td>';
                echo '</tr>';
                echo '</table>';

            } else {

                echo '<table>';
                echo '<td><span style=margin-right:360px;></span>現在値</td><td>xx:xx&nbsp;</td><td>xxxxxx</td>';
                echo '<tr>';
                echo '<td><span style=margin-right:360px;></span>歩値1</td><td>xx:xx&nbsp;</td><td>xxxxxx</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><span style=margin-right:360px;></span>歩値2</td><td>xx:xx&nbsp;</td><td>xxxxxx</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><span style=margin-right:360px;></span>歩値3</td><td>xx:xx&nbsp;</td><td>xxxxxx</td>';
                echo '</tr>';
                echo '</table>';

            }

            echo '<HR align=left color=silver width=50%>';




  // ==== 銘柄コード指定用form表示
  echo "<form action='./marketInfo.php' method='get'>\n";

  if (file_exists($yahooChartDir.$targetCode.'.'.$targetDate.'.png')) {

                  echo '<img src=../yahooDailyChart/_fileWork/'.$targetDate.'/'.$targetCode.'.'.$targetDate.'.png>';
  } else {

                  echo '<img src=../yahooDailyChart/_fileWork/no_chart.png>';
  }

  echo '<br>';
  echo "TARGET CODE <input type='text' size='20' name='TARGET_CODE' value='".$targetCode."'>\n";
  echo "<input type='submit' value='銘柄コード 指定'>\n";

  echo "</form>\n";


echo "</body>\n";
echo "</html>\n";







//////////////// ページレスポンスから対象データを抽出してstdClassを返却するサブルーチン

function createMarketOrderData($result) {

            // ========
            // ======== GMOのサイトはSJISだ！
            // ======== 文字化け対策のためHTML-ENTITIESで処理する
            // ========
            $result = mb_convert_encoding($result, "HTML-ENTITIES", "SJIS");

            $dom = new DOMDocument;
            libxml_use_internal_errors( true ); // これと：ワーニング無視
            $dom->loadHTML($result);
            libxml_clear_errors();              //   これ：ワーニング無視
            $xpath = new DOMXPath($dom);


            // ---- body 内の
            //       <td id='overVolume'>2,409</td> タグ内の値を取得
            //
            $overVolume = $xpath->query("//body//td[contains(@id, 'overVolume')]")[0]->nodeValue;
            $overVolume = trim(str_replace(",", "", $overVolume));


            // ---- body 内の
            //       <td id='atTheMarketAskVolume'>999</td> タグ内の値を取得
            //
            $atTheMarketAskVolume = $xpath->query("//body//td[contains(@id, 'atTheMarketAskVolume')]")[0]->nodeValue;
            $atTheMarketAskVolume = trim(str_replace(",", "", $atTheMarketAskVolume));

            // ---- body 内の
            //       <td id='atTheMarketBidVolume'>999</td> タグ内の値を取得
            //
            $atTheMarketBidVolume = $xpath->query("//body//td[contains(@id, 'atTheMarketBidVolume')]")[0]->nodeValue;
            $atTheMarketBidVolume = trim(str_replace(",", "", $atTheMarketBidVolume));

            // ---- body 内の
            //       <td id='underVolume'>1,997</td> タグ内の値を取得
            //
            $underVolume = $xpath->query("//body//td[contains(@id, 'underVolume')]")[0]->nodeValue;
            $underVolume = trim(str_replace(",", "", $underVolume));



            if ( is_numeric($atTheMarketAskVolume) 
             and is_numeric($overVolume)
             and is_numeric($underVolume)
             and is_numeric($atTheMarketBidVolume) ) {

              $sourData = new stdClass;
              $sourData->onTrade = false;
              $sourData->atTheMarketAskVolume = intval($atTheMarketAskVolume);
              $sourData->overVolume = intval($overVolume);
              $sourData->underVolume = intval($underVolume);
              $sourData->atTheMarketBidVolume = intval($atTheMarketBidVolume);

              return $sourData;

            } elseif ( is_numeric($overVolume) 
                   and is_numeric($underVolume) ) {

              $sourData = new stdClass;
              $sourData->onTrade = true;
              $sourData->atTheMarketAskVolume  = 0;
              $sourData->overVolume = intval($overVolume);
              $sourData->underVolume = intval($underVolume);
              $sourData->atTheMarketBidVolume = 0;

              return $sourData;

            } else {

              return null;
            }
}



?>
