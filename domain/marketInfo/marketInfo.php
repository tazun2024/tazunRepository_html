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
    // 2024.03.09 search_common.phpを追加したので不要 // include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
  //include(APP_ROOT.'COMMON/Log/CmxLog.php');
  //include(APP_ROOT.'COMMON/Cfg/CmxCfg.php');
  //include(APP_ROOT.'COMMON/Mail/CmxMailSender.php');
  //include(APP_ROOT.'COMMON/Mail/_MAILconf.php');
    include(APP_ROOT.'COMMON/Util/webAccessUtil.php');
    // 2024.03.09 search_common.phpを追加したので不要 // include(APP_ROOT.'COMMON/Util/CMX_common.php');

    include(DOMAIN_ROOT.'masudaashi/search_common.php'); // 2024.03.09 getMeigaraNm()が使いたくて追加


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

  if ($targetCode <> "") {

      echo '<TABLE>';

      echo '<TR>';
      echo '<TD style="background:darkseagreen; color:white; font-weight:bold;">';
      echo '<span style="letter-spacing:0.5em;">&nbsp;</span>'.$targetCode.' '.getMeigaraNm($targetCode).'<span style="letter-spacing:0.5em;">&nbsp;</span>';
      echo '</TD>';
      echo '</TR>';

      $targetDate = @$_REQUEST["TARGET_DATE"];
      if (mb_strlen($targetDate) <> 8 or !is_numeric($targetDate)) {

          $targetDate = getBcakToDaysYMD(date('Ymd1200'), 0);



          // ================ 裁定残のグラフを取得する
          // 2024.04.01 9468//https://chart.googleapis.com/chart?chs=360x150&chf=bg,s,f8f8f8&chdl=%E7%A9%BA%E5%A3%B2%E6%99%82%E4%BE%A1%E7%B7%8F%E9%A1%8D|%E5%A3%B2%E5%A2%97&chco=FF0000,00FF00&chls=0.5,0,0|0.5,0,0&cht=lc&chd=t2:-1,1334467,761649,602849,531942,800019,628000,551376,1075848,1097874,1058742,907963,1018820,546287,546069,621089,622204,640428,708722,917834,2106120,936225,837925,647478,1197285,923687,1119190,957915,1172683,936200,1108038,1789424,1319316,1185180,1676701,1754004,2768063,935610,689720,131021,-1|-1,791278,238230,279901,304035,380622,244575,210282,274467,266182,254301,299005,373286,193146,236368,303133,239999,334192,407942,482049,931835,333274,431662,356566,288448,486809,341911,378423,698967,646968,630802,375489,372147,264477,873836,754976,332174,281833,282657,37206,-1|-1,3701,3726,3742,3755,3741,3719,3766,3826,3887,3899,3934,3980,3972,3972,3952,4009,4065,4057,4055,4035,4076,4152,4136,4129,4101,4104,3997,3973,3991,3985,3996,4041,4088,4196,4224,4195,4180,4198,4183,-1|-1,3705,3729,3760,3784,3763,3728,3774,3839,3893,3912,3953,4014,3990,3998,3966,4025,4082,4072,4072,4042,4081,4180,4147,4134,4193,4121,4050,3996,4067,4006,3999,4045,4107,4215,4255,4233,4192,4203,4219,-1|-1,3764,3730,3752,3775,3751,3749,3825,3826,3938,3914,3959,3995,3988,3982,3971,4058,4066,4073,4068,4067,4144,4162,4166,4162,4108,4115,4031,4030,4016,4030,4016,4122,4146,4232,4243,4195,4197,4227,4192,-1|-1,3764,3749,3780,3784,3784,3754,3833,3867,3942,3923,3961,4031,3999,4020,3979,4063,4090,4090,4078,4074,4150,4187,4175,4168,4201,4150,4051,4030,4067,4031,4027,4122,4148,4237,4264,4238,4206,4250,4227,-1&chds=0,2768063,0,2768063,3700,4300&chm=F,0044FF,2,,4,-1&chxt=x,r&chxl=0:||||||||02/08||||||||02/21||||||||03/05||||||||03/15||||||||03/28||1:|37000|38200|39400|40600|41800|43000&chg=4.8780487804878,10,1,5

          // contextを渡してあげればいいようで、試したところ指定したtimeoutは秒数っぽいです
          $context = stream_context_create([
              'http' => [
                  'timeout' => 2
              ]
          ]);
          $result = file_get_contents("https://karauri.net/".$targetCode."/sokuhou/", false, $context);


          $dom = new DOMDocument;
          libxml_use_internal_errors( true ); // これと：ワーニング無視
          if (!empty($result)) {

              $dom->loadHTML($result);

              echo '<TR>';
              echo '<TD style="color:lightgray;">';
              echo '＜裁定残のグラフを取得する＞';
              echo '</TD>';
              echo '</TR>';

          } else {

              echo '<TR>';
              echo '<TD style="color:lightgray;">';
              echo '＜裁定残のグラフを取得する is empty＞';
              echo '</TD>';
              echo '</TR>';
          }
          libxml_clear_errors();              //   これ：ワーニング無視
          $xpath = new DOMXPath($dom);

          // ---- body 内の
          //       <img class="oc" src="～> タグ内のsrcの値を取得
          //
          $node = $xpath->query("//body//img[contains(@class, 'oc')]/@src")[0];
          if (!is_null($node))  file_put_contents(DOMAIN_ROOT.'saiteizanDailyChart/_fileWork/'.$targetCode.".".date('Ymd', strtotime($targetDate)).".png", file_get_contents($node->nodeValue));

      }

      echo '</TABLE>';

//} 


    $yorimaeGraphOutputDir = DOMAIN_ROOT.'marketInfo/_fileWork/';
    $marketOrderDir    = DOMAIN_ROOT.'marketOrder/_fileWork/'.$targetDate.'/';
    $saiteizanChartDir = DOMAIN_ROOT.'saiteizanDailyChart/_fileWork/';
    $yahooChartDir     = DOMAIN_ROOT.'yahooDailyChart/_fileWork/'.$targetDate.'/';

//if ($targetCode <> "") {

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
  // ==== 日付指定用form表示https://cmx.boy.jp/tazun/index.files/MY_MA.png
  echo "<nobr>\n";
  echo "<form action='./marketInfo.php' method='get'>";
  echo "TARGET DATE <input type='text' size='20' name='TARGET_DATE' value='".$targetDate."'>";
  echo "<input type='hidden' name='TARGET_CODE' value='".$targetCode."'>";
  echo "&nbsp;<input type='submit' value='日付 指定'>";
  echo "</form>";
  echo '</TD>';
  echo '<TD>';
  echo "&nbsp;</span><a href=https://cmx.boy.jp/html/domain/masudaashi/search.php?TARGET_CD=".$targetCode."><img width=48 src=../masudaashi/_img/linkMY_MA.png></a>";
  echo "&nbsp;</span><a href=https://finance.yahoo.co.jp/quote/".$targetCode."/chart><img width=48 src=../masudaashi/_img/linkYahoo.png></a>";
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

  $currentDtime = time();
  $secondFriday = strtotime('second friday', strtotime('-1 day', strtotime(date('Ym01', $currentDtime)))); // 前月末日から見て第二金曜日のYmdを計算する
  //echo '<TR>';
  //echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
  //echo '今月の第二金曜日'.date('Y-m-d', $secondFriday);
  //echo '</TD>';
  //echo '</TR>';
  if (strtotime(date('Ymd', $currentDtime)) <= strtotime(date('Ymd', $secondFriday))
   and strtotime(date('Ymd', $currentDtime)) >= strtotime('-4 day', strtotime(date('Ymd', $secondFriday)))
   and (date('n', $secondFriday) == '3' or date('n', $secondFriday) == '6' or date('n', $secondFriday) == '9' or date('n', $secondFriday) == '12')) {

      // ---- 今日は、メジャーSQのある月の「メジャーSQ日の前」かつ「その週の近辺」

      // 第二金曜日前の営業日の並びを先ず取得する
      $checkDtime = strtotime(date('Ymd0845', $secondFriday));
      $check = (getNextGoDaysYMD(date('Ymd', $checkDtime), '0') == date('Ymd', $checkDtime)) ? '○' : '休';          // 金曜日の状態
      $checkDtime = strtotime('-1 day', $checkDtime);
      $check = ((getNextGoDaysYMD(date('Ymd', $checkDtime), '0') == date('Ymd', $checkDtime)) ? '○' : '休').$check; // 木曜日の状態
      $checkDtime = strtotime('-1 day', $checkDtime);
      $check = ((getNextGoDaysYMD(date('Ymd', $checkDtime), '0') == date('Ymd', $checkDtime)) ? '○' : '休').$check; // 水曜日の状態
      $checkDtime = strtotime('-1 day', $checkDtime);
      $check = ((getNextGoDaysYMD(date('Ymd', $checkDtime), '0') == date('Ymd', $checkDtime)) ? '○' : '休').$check; // 火曜日の状態
      $checkDtime = strtotime('-1 day', $checkDtime);
      $check = ((getNextGoDaysYMD(date('Ymd', $checkDtime), '0') == date('Ymd', $checkDtime)) ? '○' : '休').$check; // 月曜日の状態

      $dayStrArr = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)');
      //echo '<TR>';
      //echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
      //echo $check;
      //echo '</TD>';
      //echo '</TR>';
      switch($dayStrArr[date('w', $currentDtime)]) {

          //
          // ＳＱの売り崩し／買い上りにご用心
          //
          // 株式調査ファイル
          // 2024/3/8 17:25
          //
          // 株式市場は８日のメジャーＳＱ（特別清算指数）を前に大波乱の展開になった。
          // ヘッジファンドなど日経２２５先物の売り方は、これまで３カ月間、巨額の損失を強いられてきた。
          // それだけに、この日は禁じ手とも言える「最終売買日の売り崩し」を以前から周到に準備していたのかもしれない。
          // 通常の相場であれば、売り仕掛けはＳＱ２日前までで自粛するのが慣習である。
          // ＳＱ前日の売り仕掛けは規制当局の調査対象になりやすい。
          // そのためＳＱ２日前が「急落の急所」と恐れられてきたわけだが、今回はそんな紳士協定も守れないほど売り方は窮地に立たされていたのだろう。
          //

          case '(月)':

              //echo '<TR>';
              //echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
              //echo '(月)'.$check;
              //echo '</TD>';
              //echo '</TR>';

              if ($check == '○○休休休') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '明日'.date('n/j', strtotime('+1 day', $currentDtime)).$dayStrArr[date('w', strtotime('+1 day', $currentDtime))].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';

              } elseif ($check == '○休休休休') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '本日'.date('n/j', $currentDtime).$dayStrArr[date('w', $currentDtime)].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';

              } elseif ($check == '○休休休○') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '次営業日'.date('n/j', strtotime('+4 day', $currentDtime)).$dayStrArr[date('w', strtotime('+4 day', $currentDtime))].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';
              }

              break;


          case '(火)':

              //echo '<TR>';
              //echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
              //echo '(火)'.right($check, 4);
              //echo '</TD>';
              //echo '</TR>';

              if (right($check, 4) == '○○休休') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '明日'.date('n/j', strtotime('+1 day', $currentDtime)).$dayStrArr[date('w', strtotime('+1 day', $currentDtime))].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';

              } elseif (right($check, 4) == '○休休休') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '本日'.date('n/j', $currentDtime).$dayStrArr[date('w', $currentDtime)].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';

              } elseif (right($check, 4) == '○休休○') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '次営業日'.date('n/j', strtotime('+3 day', $currentDtime)).$dayStrArr[date('w', strtotime('+3 day', $currentDtime))].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';
              }

              break;


          case '(水)':

              //echo '<TR>';
              //echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
              //echo '(水)'.right($check, 3);
              //echo '</TD>';
              //echo '</TR>';

              if (right($check, 3) == '○○休') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '明日'.date('n/j', strtotime('+1 day', $currentDtime)).$dayStrArr[date('w', strtotime('+1 day', $currentDtime))].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';

              } elseif (right($check, 3) == '○休休') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '本日'.date('n/j', $currentDtime).$dayStrArr[date('w', $currentDtime)].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';

              } elseif (right($check, 3) == '○休○') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '次営業日'.date('n/j', strtotime('+2 day', $currentDtime)).$dayStrArr[date('w', strtotime('+2 day', $currentDtime))].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';
              }
              break;


          case '(木)':

              //echo '<TR>';
              //echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
              //echo '(木)'.right($check, 2);
              //echo '</TD>';
              //echo '</TR>';

              if (right($check, 2) == '○○') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '明日'.date('n/j', strtotime('+1 day', $currentDtime)).$dayStrArr[date('w', strtotime('+1 day', $currentDtime))].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';

              } elseif (right($check, 2) == '○休') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '本日'.date('n/j', $currentDtime).$dayStrArr[date('w', $currentDtime)].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';
              }

              break;


          case '(金)':

              //echo '<TR>';
              //echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
              //echo '(金)'.right($check, 1);
              //echo '</TD>';
              //echo '</TR>';

              if (right($check, 1) == '○') {

                  echo '<TR>';
                  echo '<TD colspan=2 style="background:deeppink; color:white; font-weight:bold;">';
                  echo '本日'.date('n/j', $currentDtime).$dayStrArr[date('w', $currentDtime)].'はメジャーSQです';
                  echo '</TD>';
                  echo '</TR>';
              }

              break;


          default:
              break;

      }  // -- end of switch()
  }


                  //
                  // 裁定残グラフ
                  // https://karauri.net/
                  //

                  echo '<img src=./_fileWork/'.$targetFileMarketOrderImg.'>';

  if (file_exists($saiteizanChartDir.$targetCode.".".$targetDate."__.png")) {

                  //echo '<a href=https://karauri.net/'.$targetCode.'/sokuhou/><img src=../saiteizanDailyChart/_fileWork/'.$targetCode.'.'.$targetDate.'.png></a>';
                  echo '<a href=https://karauri.net/'.$targetCode.'><img src=../saiteizanDailyChart/_fileWork/'.$targetCode.'.'.$targetDate.'__.png></a>';
  } else {

                  echo '<a href=https://karauri.net/'.$targetCode.'><img src=./_fileWork/no_saiteizanChart.png></a>';
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

  if ($targetCode <> "") {
      if (file_exists($yahooChartDir.$targetCode.'.'.$targetDate.'.png')) {

                      echo '<img src=../yahooDailyChart/_fileWork/'.$targetDate.'/'.$targetCode.'.'.$targetDate.'.png>';
      } else {

                      echo '<img src=../yahooDailyChart/_fileWork/no_chart.png>';
      }
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
