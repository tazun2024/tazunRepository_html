<?php

    // シャットダウンシーケンスを設定
    register_shutdown_function('my_shutdown_handler');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    //include('../_fsEnv.cfg');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    //include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
    //include(APP_ROOT.'COMMON/Log/CmxLog.php');
    //include(APP_ROOT.APP_TYPE.'/Dao/FSDao.php');
    //include(APP_ROOT.APP_TYPE.'/Util/FSUtil.php'); // FSDao内で BSUtil::T_CMX_SIMULATIONの場合分けをするので必要

    //include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');




            $myCmxLog = new CmxLog(__DIR__.'/syncroCopyFS_log/'.date('YmdHis').'.log');
            $myCmxLog->infEcho('>>execute synchroSAKURA_FS_CHART');

            $myFSDao = new FSDao($myCmxLog, INVESTX_NOT_APPLICABLE_VALUE, CoreBase::T_AUD5M);

            //////// クエリの実行
            //$deleteQuery= "delete from FS_ADMIN where meigara_cd in ('CR_FSAUD')";
            //echo '$deleteQuery=['.$deleteQuery.']<br><br>';
            //$myFSDao->getListTypeRecord($deleteQuery);


            $symbolCd = '';
            $ym = $_REQUEST['YM'];
            switch($_REQUEST['action']) {

              case 'synchroFsChart_AUD':

                  if (mb_strlen($ym) == 6 and checkdate(intval(right($ym, 2)), 1, intval(left($ym, 4)))) {

                      $symbolCd = 'AUD';
                      $startDate = getLoggingDatetimeStr_fromDtime(strtotime($ym.'010000'));
                      $endDate   = getLoggingDatetimeStr_fromDtime(strtotime(date('Y-m-d 23:59', strtotime('last day of ' . $ym.'01'))));

                  } else {

                      echo '?? YM<br>';
                      echo $_REQUEST['YM'].'<br>';
                  }
                  break;

              case 'synchroFsChart_USD':

                  if (mb_strlen($ym) == 6 and checkdate(intval(right($ym, 2)), 1, intval(left($ym, 4)))) {

                      $symbolCd = 'USD';
                      $startDate = getLoggingDatetimeStr_fromDtime(strtotime($ym.'010000'));
                      $endDate   = getLoggingDatetimeStr_fromDtime(strtotime(date('Y-m-d 23:59', strtotime('last day of ' . $ym.'01'))));

                  } else {

                      echo '?? YM<br>';
                      echo $_REQUEST['YM'].'<br>';
                  }
                  break;

              default:
                  echo '?? symbolCd<br>';
                  echo $_REQUEST['action'].'<br>';
                  break;

            }  // -- end of switch()

            if ($symbolCd == '') exit;



            //////// //////// //////// ////////
            $url = 'https://ik1-326-23246.vs.sakura.ne.jp/FxStarPrd/_secure/execQueryFS2022.php?execQuery='
                .str_replace(' ', '%20', "select symbolCd,date,bid,ask from FS_CHART where symbolCd='".$symbolCd
                    ."' and date >= '".$startDate."' and date <= '".$endDate."'&dTimeColumn=date");
            //////// //////// //////// ////////
//echo $url.'<br>';

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
            $i=0;
            foreach ($content->childNodes as $node) {


                if ($i++ == -1) {

                    var_dump($node);
                    echo '<br>';
                    echo '<br>';

                    $itemCount = 0;
                    foreach ($node->childNodes as $item) {

                        echo '$itemCount=['.$itemCount.']';
                        var_dump($item->nodeValue, $item->textContent);
                        echo '<br>';

                        $itemCount++;
                    }
                    echo '<br>';

                    //////// 各<td>タグ要素を表示
                    echo '0['.$node->childNodes[0]->nodeValue.']<br>';
                    echo '1['.$node->childNodes[1]->nodeValue.']<br>';
                    echo '2['.$node->childNodes[2]->nodeValue.']<br>';
                    echo '3['.$node->childNodes[3]->nodeValue.']<br>';
                    echo '<br>';
                }


                if (isset($node->childNodes[0])) {

                    $responseSymbolCd = $node->childNodes[0]->nodeValue;
                    $responseDate     = $node->childNodes[1]->nodeValue;
                    $responseBid      = $node->childNodes[2]->nodeValue;
                    $responseAsk      = $node->childNodes[3]->nodeValue;

                    if ($responseSymbolCd == $symbolCd) {

                        //////// クエリの実行
                        $registeredRecord = $myFSDao->getFirstRecord_DAO_Sql("select bid, ask from FS_CHART where symbolCd = '".$symbolCd."' and date = '".$responseDate."'");

                        if ($registeredRecord != null) {

                            // 登録済みのレコードが既に存在している場合は差異をチェックして必要ならupdateする


                            if ($registeredRecord->bid == intval($responseBid) and $registeredRecord->ask == intval($responseAsk)) {

                                //echo $responseDate.'は一致しています<br>';
                                //$contentsArr[] = $responseDate.'は一致しています<br>';

                            } else {

                                //echo $responseDate.'は差異があります<br>';
                                //$contentsArr[] = $responseDate.'は差異があります<br>';

                                //echo $responseDate.' (bid='.$registeredRecord->bid.' ask='.$registeredRecord->ask.') ⇒bid='.$responseBid.' ask='.$responseAsk.'<br>';
                                $contentsArr[] = $responseDate.' (bid='.$registeredRecord->bid.' ask='.$registeredRecord->ask.') ⇒bid='.$responseBid.' ask='.$responseAsk.'<br>';

                                $SQL = "update FS_CHART set bid =".intval($responseBid).", ask = ".intval($responseAsk)." where symbolCd = '".$symbolCd."' and date = '".$responseDate."'";
                                //echo $SQL.'<br>';
                                //$contentsArr[] = $SQL.'<br>';

                                //////// クエリの実行
                                $myFSDao->getListTypeRecord($SQL);
                            }

                        } else {

                            // 登録済みのレコードが存在していない場合はinsertする


                            $SQL = "insert into FS_CHART values('".$symbolCd."', '".$responseDate."', ".intval($responseBid).", ".intval($responseAsk).")";

                            //echo $responseDate.'は登録されていません<br>';
                            $contentsArr[] = $responseDate.'を登録します '.$SQL.'<br>';

                            //////// クエリの実行
                            $myFSDao->getListTypeRecord($SQL);
                        }
                    }
                }
            }

            $contentsArr[] = '<br>';
            $contentsArr[] = '処理が終了しました。'.count($content->childNodes).' record(s)<br>';

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
