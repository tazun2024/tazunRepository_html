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
            $myCmxLog->infEcho('>>execute syncroCopyFsSAKURA_ADMINsimu');

            $myFSDao = new FSDao($myCmxLog, INVESTX_NOT_APPLICABLE_VALUE, CoreBase::T_AUD5M);

            //////// クエリの実行
            $myFSDao->getListTypeRecord("delete from FS_ADMIN where meigara_cd = 'CR_FS'");


            //////// //////// //////// ////////
            $url = 'https://ik1-326-23246.vs.sakura.ne.jp/FxStar/%5Eact/View/tmp.selectResultFs.php?execQuery='.str_replace(' ', '%20', "select meigara_cd,date,side,value,price from FS_ADMIN where meigara_cd='SM_FX'&dTimeColumn=date&");
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

            //////// 2つめの<table>タグを得る
            $content = $xpath->query('//table')->item(1);
            //var_dump( $content);


            //////// <tr>タグをループ
            foreach ($content->childNodes as $node) {

/****
                foreach ($node->childNodes as $item) {

                    var_dump($item->nodeValue, $item->textContent);
                    echo '<br>';
                }
****/

/****
                //////// 各<td>タグ要素を表示
                echo $node->childNodes[0]->nodeValue.'<br>';
                echo $node->childNodes[2]->nodeValue.'<br>';
                echo $node->childNodes[4]->nodeValue.'<br>';
                echo $node->childNodes[6]->nodeValue.'<br>';
                echo $node->childNodes[8]->nodeValue.'<br>';
****/

                if (isset($node->childNodes[0])) {

                    $side = $node->childNodes[4]->nodeValue;
                //////// 2022.08.31 （現行）tateGYKU ⇒ tateGYK1 変更対応
                ////if ($side === 'tateGYKU') $side = 'tateGYK1';

                    if ($node->childNodes[0]->nodeValue == 'SM_FX') {

                        $SQL = "insert into FS_ADMIN values('CR_FS".                                // meigara_cd
                               "', '".date('Y-m-d H:i', strtotime($node->childNodes[2]->nodeValue)).   // date
                               "', '".$side.                                                           // side
                               // Trimでも消えないスペースって？ 文字コードC2A0
                               // どうやら、文字コードC2A0とは、「UTF-8の半角スペース」というか、HTMLでいう「&nbsp;」スペースのバイナリで、
                               //「そこでは改行しないスペース」という意味をもった、通常の半角スペース(20)とは全く別のスペースのようです。
                               "', '".(str_replace( "\xc2\xa0", '', $node->childNodes[6]->nodeValue)). // value
                               "', ".$node->childNodes[8]->nodeValue.                                  // price
                               ")";

                        echo "<code>".$SQL.';'."</code><br>";

                        //////// クエリの実行
                        $myFSDao->getListTypeRecord($SQL);

                    }

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
