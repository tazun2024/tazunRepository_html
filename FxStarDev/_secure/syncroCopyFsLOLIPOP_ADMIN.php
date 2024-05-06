<?php

    // シャットダウンシーケンスを設定
    register_shutdown_function('my_shutdown_handler');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    include('../_fsEnv.cfg');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
    include(APP_ROOT.'COMMON/Log/CmxLog.php');
    include(APP_ROOT.APP_TYPE.'/Dao/FSDao.php');
    include(APP_ROOT.APP_TYPE.'/Util/FSUtil.php'); // FSDao内で BSUtil::T_CMX_SIMULATIONの場合分けをするので必要

    include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');




            $myCmxLog = new CmxLog(__DIR__.'/syncroCopyFS_log/'.date('YmdHis').'.log');
            $myCmxLog->infEcho('>>execute syncroCopyFsLOLIPOP_ADMIN');

            $myFSDao = new FSDao($myCmxLog, INVESTX_NOT_APPLICABLE_VALUE, CoreBase::T_AUD5M);

            //////// クエリの実行
            $deleteQuery= "delete from FS_ADMIN where meigara_cd in ('CR_FX', 'CR_FSAUD')";
            echo '$deleteQuery=['.$deleteQuery.']<br><br>';
            $myFSDao->getListTypeRecord($deleteQuery);


            //////// //////// //////// ////////
            $baseUrl = 'https://cmx.boy.jp/html/FxStarRsv/CmxApi/queryFS.php';
            $queryStr = "select meigara_cd, date, side, value, price from FS_ADMIN where meigara_cd='CR_FSAUD'";
            $url = $baseUrl.'?logId=syncroCopyFsLOLOPOP_ADMIN'.'&QUERY='.urlencode($queryStr);
            //////// //////// //////// ////////

            // curl初期化
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5秒 2020.05.20
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //「無：ｴﾗｰ bool(true)」「有：string(647) "ｴﾗｰ」

            // curlリクエスト
            $result = curl_exec($ch);

            // curl後始末
            curl_close($ch);

//echo $result;




            // ◆ 本体からpageHashの切り出し
            $dom = new DOMDocument;
            libxml_use_internal_errors( true ); // これと：ワーニング無視
            $dom->loadHTML($result);
            libxml_clear_errors();              //   これ：ワーニング無視
            $xpath = new DOMXPath($dom);




            // row
            $result = new StdClass;
            $rowNodes = $xpath->query('//row');

            if (count($rowNodes) <= 0) throw new Exception('**curl_exec()結果の返却値がありません**');

            foreach($rowNodes as $theRow){

                $meigaraCd = $theRow->getElementsByTagName('meigara_cd')->item(0)->nodeValue; // ¶XML 文書における HTML 要素を扱う場合で、タグ名は大文字に変換されません。XHTML 要素なので小文字が返されます。
                $date      = $theRow->getElementsByTagName('date')->item(0)->nodeValue;
                $side      = $theRow->getElementsByTagName('side')->item(0)->nodeValue;
                $value     = $theRow->getElementsByTagName('value')->item(0)->nodeValue;
                $price     = $theRow->getElementsByTagName('price')->item(0)->nodeValue;

                if ($meigaraCd == 'CR_FSAUD') {

                    $SQL = "insert into FS_ADMIN values('CR_FSAUD".       // meigara_cd
                           "', '".date('Y-m-d H:i', strtotime($date)).    // date
                           "', '".$side.                                  // side
                           // Trimでも消えないスペースって？ 文字コードC2A0
                           // どうやら、文字コードC2A0とは、「UTF-8の半角スペース」というか、HTMLでいう「&nbsp;」スペースのバイナリで、
                           //「そこでは改行しないスペース」という意味をもった、通常の半角スペース(20)とは全く別のスペースのようです。
                           "', '".(str_replace( "\xc2\xa0", '', $value)). // value
                           "', ".$price.                                  // price
                           ")";

                    echo "<code>".$SQL.';'."</code><br>";

                    //////// クエリの実行
                    $myFSDao->getListTypeRecord($SQL);

                }
            }




        ////include(APP_ROOT.'BillionStuffRsv/Obj/NY_ELMCT.syncro');
        ////$myFSDao->getListTypeRecord("update FS_ADMIN set value = '".$NY_ELMCT."' where meigara_cd = 'CR_N225M' and side = 'NY_ELMCT';");




    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====  shutdownハンドラ  ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    function my_shutdown_handler(){

      $error = error_get_last();

      if(empty($error)) return;

      switch($error['type']){
          case(E_ERROR):   $type = "Fatal Error"; break;
          case(E_WARNING): $type = "Warning"; break;
          case(E_NOTICE):  $type = "Notice"; break;
          default:         $type = "Error";
      }


      var_dump($error);

      $date = new DateTime();
      $line = sprintf("%s: %s. %s in %s on line %s\n", $date->format('Y-m-d H:i:s'), $type, $error['message'], $error['file'], $error['line']);
      file_put_contents(dirname(__FILE__) . '/_'.APP_TYPE.'_shutdown_handler.log', $line, FILE_APPEND | LOCK_EX);

      if ( $type <> "Warning"
        and $type <> "Notice" ) {

              // 自力でメール送信
              $subject=mb_convert_encoding(' ALERT!! on '.APP_TYPE, 'JIS', 'AUTO');
              $subject=mb_encode_mimeheader($subject, 'ISO-2022-JP', 'B');
              $message=mb_convert_encoding('type['.$error['type'].', '.$type."]\n".APP_TYPE.", my_shutdown_handler occured!!\n\n".$line, 'JIS', 'AUTO');
              $headers ="From: CMX_ALERT\n";
              $headers.="MIME-Version: 1.0\n";
              $headers.="Content-Type: text/plain; charset=iso-2022-jp\n";
              $headers.="Content-Transfer-Encoding: 7bit\n";
              $headers.="X-Mailer: PHP/" . phpversion() . "\n";

              mail('rishiriunizoh@yahoo.co.jp', $subject, $message, $headers);

          }
    }
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====  shutdownハンドラ  ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
?>
