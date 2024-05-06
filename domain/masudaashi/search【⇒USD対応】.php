<?php

// **
// ** 2021.11.12 再構築
// **
// ** ・ｵﾘｼﾞﾅﾙ増田足と共存していた際の「ｵﾘｼﾞﾅﾙが保持できていればそちらを表示」ロジックを廃止⇒必ずMY_MAを表示
// ** ・同日同銘柄のチャートが生成済みであれば「再生成を省く」ロジックを廃止⇒毎回MY_MYを生成

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


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(DOMAIN_ROOT.'masudaashi/search_common.php');
////include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');             //// search_Common.phpで定義される
////include(APP_ROOT.'COMMON/Util/CMX_common.php');                //// search_Common.phpで定義される
////include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');             //// search_Common.phpで定義される
    include(APP_ROOT.'COMMON/Log/CmxLog.php');




  define('ORGMASUDAASHI_LEASTDAY', '2020-07-31');


  echo '指定された日付['.$_REQUEST['TARGET_YMD'].']<br>';
  if (!isset($_REQUEST['TARGET_YMD'])) {

    // ======== (targetCdを指定した最初の遷移)

    // ==== 営業日を取得
    $ymd = getBcakToDaysYMD(date('Ymd1200', strtotime('-915 min')), '0'); // 15:15から本日付のymdになる（15:10までは前営業日）
    $targetYmd = ''; // -------------------------------- rev/fwdリンクでなく、最初の遷移であることを判断するためにクリア（Undefined variable: targetYmd 対応）

    // ==== 前の営業日/次の営業日をさくらAPIで取得する
    $prvfwdResult = getRevFwdYMD($ymd.'1200');

  } else {

    // ======== (日付指定、rev/fwdリンクで移動した場合の遷移)

    $targetYmd = $_REQUEST['TARGET_YMD'];

    if ($targetYmd == '20201001') {

      // 2020.10.01 東証システム障害で全面休場の対応
      //（URLダイレクトで不正な日付の場合）
      $targetYmd ='20201002';
    }

    //////// 翌営業日のymdをSAKURA_APIから取得する
    $chkYmd = getNextGoDaysYMD($targetYmd, '0');
  echo 'さくらAPIの営業日判定結果['.$chkYmd.']<br>';

    if ($chkYmd <> $targetYmd) {

      // -------- 指定した日付が営業日でない場合（URLダイレクトで不正な日付の場合）

      $ymd = $chkYmd;    // -------------------------------- 日付ごとのファイル名指定に必要なのでコピー

    } else {

      $ymd = $targetYmd; // -------------------------------- 日付ごとのファイル名指定に必要なのでコピー
    }

    // ==== 前の営業日/次の営業日をさくらAPIで取得する
    $prvfwdResult = getRevFwdYMD($targetYmd."1200");
  }
echo 'rev['.$prvfwdResult->rev.']<br>';
echo 'fwd['.$prvfwdResult->fwd.']<br>';




  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
  echo "<!-- saved from url=(0029)http://www9.ocn.ne.jp/~tazun/ -->";
  echo "<HTML xmlns=\"http://www.w3.org/1999/xhtml\">\n";
  echo "<HEAD>\n";
  echo "<TITLE>MY_MA SEARCH</TITLE>\n";
  echo "<meta NAME=\"ROBOTS\" CONTENT=\"NOINDEX,NOFOLLOW,NOARCHIVE\">\n";
  echo "<meta name=\"viewport\" content=\"width=device-width\">\n";
  echo "<META http-equiv=Content-Type content=\"text/html; charset=UTF-8\">\n";

  echo "</HEAD>\n";
  echo "<BODY>\n";

                                  // ■■■■ float＆中央寄せ ■■■■
                                  echo "<style type='text/css'>";
                                  echo ".example {margin: 10px auto;";
                                  echo " width:85%;"; // -------- ##withを良しなに調整する
                                //echo " background: wheat;}";
                                  echo " height: 200px;}";
                                  echo ".yellow{margin: 10px;";
                                  echo " background-color: lightgray;";
                                  echo " width: 220px;";
                                  echo " height: 900px;float: left;}";
                                  echo ".green{";
                                //echo " background-color: green;";
                                //echo " width: 400px;";
                                  echo " height: 100px;float: left;}";
                                  echo ".pink{";
                                //echo " background-color: pink;";
                                  echo " width: 500px;";
                                  echo " height: 50px;clear: left;}";
                                  echo "</style>";


      if (isset($_REQUEST['TARGET_CD'])) $targetCode = $_REQUEST["TARGET_CD"]; else $targetCode = "";
      if (isset($_REQUEST['MASUDA_PTN'])) $masudaPtn = $_REQUEST["MASUDA_PTN"]; else $masudaPtn = "";
      if (isset($_REQUEST['SIXCLR_DISTRIBUTION'])) $sixclrDistribution = $_REQUEST["SIXCLR_DISTRIBUTION"]; else $sixclrDistribution = "";

      if ($sixclrDistribution == '') $sixClrDistributionLinkStr = ''; else $sixClrDistributionLinkStr = '&SIXCLR_DISTRIBUTION='.$sixclrDistribution;


      // ==== 検索履歴の表示
      echo '<div class=example>';
      echo "<div class='yellow'><font size=-1>";
      echo '<a href=./search.php?TARGET_CD=1001'.$sixClrDistributionLinkStr.'>1001 日経２２５種平均</a><br>';
      echo '<a href=./search.php?TARGET_CD=1009'.$sixClrDistributionLinkStr.'>1009 東証マザーズ指数</a><br>';
      echo '<a href=./search.php?TARGET_CD=1021'.$sixClrDistributionLinkStr.'>1021 ミニ２２５先物（中心限月）</a><br>';
      echo '<a href=./search.php?TARGET_CD=1003'.$sixClrDistributionLinkStr.'>1003 米ドル／円</a><br>';
      echo '<a href=./search.php?TARGET_CD=1052'.$sixClrDistributionLinkStr.'>1052 豪ドル／円</a><br>';
      echo '<a href=./search.php?TARGET_CD=1081'.$sixClrDistributionLinkStr.'>1081 ダウ工業株３０種平均</a><br>';
      echo '<hr>';

      try {

        $cfgFile = HTML_ROOT.'_cfg/masudaashiSearch.cfg';
        include($cfgFile);

        foreach ($historyArr as $history) {
          echo "<A href='./search.php?TARGET_CD=".trimString_Tail_SimplNew($history, ' ').$sixClrDistributionLinkStr."'>".$history."</A><br>\n";
        }

      } catch(Exception $e) {

        //////// 不正なcfgだった場合

      } catch(Error $e) {

        //////// 不正なcfgだった場合
      }

      echo '</font></div>';

      //////// 銘柄名除去済みの配列に入れ替え
    //$historyArr = $historyCodeArr;



echo "<div class='green'>";



  // 2021.05.28 場中tmpがある状態で、その表示をリクエストする[fwd]が押された場合の遷移でセットされてくる（&BACHU_TMP=DONE）
  $bachuTmp = @$_REQUEST['BACHU_TMP'] ?: '';


      // ================
      // ================ MY増田足の検索機能
      // ================

      $execStatus = '';
      $dummyResult = '';



      if ($targetCode <> '') {

        $targetFile = 'target'.$ymd.'.gif';

        if ($targetYmd == '') {

          //////////////// 対象銘柄の最新日付画像を取得

          // ==== さくらサーバに一時ファイルを作成させる
          $dummyResult = file_get_contents('http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MASUDAASHI.php?TARGET_CODE='.$targetCode.'&BACHU_TMP_REQUEST='.$bachuTmp);

          if (mb_strpos($dummyResult, 'no_img') === false) {

            // ==== 画像ファイルを取得
            $image = file_get_contents("http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/".$targetCode."target.gif?dummy=".date('Ymdhis'));
            file_put_contents(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$targetFile, $image);

          } else {

            $execStatus = '暫定メッセージ no_img';
          }

        } else {

          //////////////// 対象銘柄の指定日付画像を取得

            $WORKFILE_DIR = DOMAIN_ROOT.'masudaashi/_img/'.$ymd.'/';

          if ( strtotime($targetYmd) <= strtotime(ORGMASUDAASHI_LEASTDAY)
           and file_exists($WORKFILE_DIR.$targetCode.$targetFile) ) {

            // ==== ロリポップサーバに保持している画像を使用する

            $execStatus = 'ロリポップサーバに保持している画像を使用します';
            $image = file_get_contents($WORKFILE_DIR.$targetCode.$targetFile);
            file_put_contents(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$targetFile, $image);

          } else {

            // ==== さくらサーバに一時ファイルを作成させる
            $dummyResult = file_get_contents('http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MASUDAASHI.php?TARGET_CODE='.$targetCode.'&TARGET_DATE='.$targetYmd.'&BACHU_TMP_REQUEST='.$bachuTmp);

            if (mb_strpos($dummyResult, 'no_img') === false) {

              // ==== 画像ファイルを取得
              $image = file_get_contents("http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/".$targetCode."target.gif?dummy=".date('Ymdhis'));
              file_put_contents(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$targetFile, $image);

            } else {

              $execStatus = '指定日付画像 暫定メッセージ no_img';
            }

          }

        } // -------- end of 「$targetYmd == ''」



        $meigaraNm = ' '.getMeigaraNm($targetCode);

        if ( mb_strpos($dummyResult, 'no_img') === false
         and $targetCode <> '1001'
         and $targetCode <> '1009'
         and $targetCode <> '1021'
         and $targetCode <> '1052'
         and $targetCode <> '1081' ) {

          // ==== 新しいcfgファイルに書き替え
          unlink($cfgFile);

          $existArr = array();
          error_log("<?PHP". "\n", 3, $cfgFile);
          error_log("\$historyArr[] = \"".$targetCode.$meigaraNm."\";". "\n", 3, $cfgFile);
          $existArr[] = $targetCode.$meigaraNm;

          $count = 1;
          foreach ($historyArr as $history) {

            if (!in_array($history, $existArr)) {

              error_log("\$historyArr[] = \"".$history."\";". "\n", 3, $cfgFile);
              $existArr[] = $history;

              $count ++;
              if ($count >= 32) break;
            }
          }
          error_log("?>". "\n", 3, $cfgFile);
        }




        // ==== 銘柄コード指定用form表示
        echo "<nobr>\n";
        echo "<form action='./search.php' method='get'>\n";
        echo "<input type='text' size='20' name='TARGET_CD' value='".$targetCode."'>\n";
        echo "<input type='submit' value='analyze'>\n";

        if (mb_strpos($dummyResult, 'MY_MA_5M=EXIST') !== false) {
          echo "<A href='http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MASUDAASHI_5M.php?TARGET_CODE=".$targetCode."'>[5分足 ".$targetCode."]</A>\n";
        }

        echo "</form>\n";
        echo "</nobr>\n";

        echo "<script type=\"text/javascript\">\n";
        echo "document.getElementsByName('TARGET_CD').item(0).select();\n";
        echo "</script>\n";






        echo "<table>\n";
        echo "<tr>\n";

        // 2021.05.28 場中tmpデータの存在をチェック表示を切り替える
        if ( mb_strpos($dummyResult, 'BACHU_TMP=EXIST') !== false // 画像を生成してみて、「場中tmpが存在している」と情報が返却された
         and $bachuTmp <> 'DONE' ) {                              // 場中tmpがある状態で、その表示をリクエストする[fwd]が押された場合の遷移でセットされてくる（&BACHU_TMP=DONE）

      //echo '<p>場中tmpアルヨ！'.mb_substr($dummyResult, mb_strpos($dummyResult, 'BACHU_TMP=EXIST') + 15, 10)."</p>\n";
        $additionalDate = date('Ymd', strtotime(mb_substr($dummyResult, mb_strpos($dummyResult, 'BACHU_TMP=EXIST') + 15, 10)));

                  echo "<td align=left>\n";
                  echo '<font size=-1><a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD='.$prvfwdResult->rev.$sixClrDistributionLinkStr.'>'.date('Y/m/d', strtotime($prvfwdResult->rev)).'<br>《《</a></font>';
                  echo "</td>\n";

                  echo "<td align=right>\n";
                  echo '<font size=-1><a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD='.$additionalDate.'&BACHU_TMP=DONE'.$sixClrDistributionLinkStr.'>'.date('Y/m/d', strtotime($additionalDate)).'<br>》》</a></font>';
                  echo "</td>\n";

        } else {


          // --------------------------------
          $targetDir = DOMAIN_ROOT.'masudaashi/_img/'.$prvfwdResult->rev.'/';

          if ( !strtotime($prvfwdResult->rev) <= strtotime(ORGMASUDAASHI_LEASTDAY)
            or (file_exists($targetDir) and file_exists($targetDir.$targetCode."target".$prvfwdResult->rev.".gif")) ) {

            echo "<td align=left>\n";

            if ($targetYmd == '20201002') {

              // 2020.10.01 東証システム障害で全面休場の対応

              echo '<font size=-1><a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD=20200930'.$sixClrDistributionLinkStr.'>2020/09/30<br>《《</a></font>';

            } else {

              echo '<font size=-1><a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD='.$prvfwdResult->rev.$sixClrDistributionLinkStr.'>'.date('Y/m/d', strtotime($prvfwdResult->rev)).'<br>《《</a></font>';
            }

            echo "</td>\n";

          } else {

            echo "<td align=left style=\"background-image:url('./_img/noData.png'); background-size:80px; background-position:left; background-repeat:no-repeat;\">\n";
            echo "<font size=-1>&nbsp;<br>《《</font>";
            echo "</td>\n";

          }

          echo "</td>\n";
          // --------------------------------


          // --------------------------------
          $targetDir = DOMAIN_ROOT.'masudaashi/_img/'.$prvfwdResult->fwd.'/';

          if ( (!strtotime($prvfwdResult->fwd) <= strtotime(ORGMASUDAASHI_LEASTDAY) and strtotime($prvfwdResult->fwd) <= strtotime('-915 min'))  // 15:15から本日付のymdになる（15:10までは前営業日）
            or (file_exists($targetDir) and file_exists($targetDir.$targetCode."target".$prvfwdResult->fwd.".gif")) ) {

            echo "<td align=right>\n";

            if ($targetYmd == '20200930') {

              // 2020.10.01 東証システム障害で全面休場の対応

              echo '<font size=-1><a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD=20201002'.$sixClrDistributionLinkStr.'>20201002<br>》》</a></font>';

            } else {

              echo '<font size=-1><a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD='.$prvfwdResult->fwd.$sixClrDistributionLinkStr.'>'.date('Y/m/d', strtotime($prvfwdResult->fwd)).'<br>》》</a></font>';
            }

            echo "</td>\n";

          } else {

            echo "<td align=right style=\"background-image: url('./_img/noData.png'); background-size:80px; background-position:right; background-repeat:no-repeat;\">\n";
            echo "<font size=-1>&nbsp;<br>》》</font>";
            echo "</td>\n";
          }

          echo "</td>\n";
          // --------------------------------

        }




        echo "</tr>\n";


        echo "<tr>\n";
        echo "<td colspan=2 style=\"background-color:#e1f0f5;\">";
        echo '<B>'.date('Y/m/d（', strtotime($ymd)).array('日', '月', '火', '水', '木', '金', '土')[date('w', strtotime($ymd))].'）</B>';

      //$meigaraNm = getMeigaraNm($targetCode);
        echo 'TARGET_CD['.$targetCode.']<br>';

        echo "<TABLE border=0 cellspacing=0 cellpadding=0>";
        echo   "<TR>";
        echo     "<TD>".$meigaraNm."</TD>";

        // 蛇足ですが、フォームの下にできる１行分の空白を消すためには、TD タグと FORM タグの位置を交換します。
        // これも常套手段の１つで、ほとんどのブラウザでは正しく表示されますが、HTML の構造としては正しくないので、何らかのツールでチェックすると警告されることがあります。
        echo     "<form action='./search.php' method='get'>";
        echo     "<input type='hidden' name='TARGET_CD' value='".$targetCode."'>";
        echo     "<input type='hidden' name='SIXCLR_DISTRIBUTION' value='".$sixclrDistribution."'>";
        echo     "<TD>&nbsp;<input type='text' size='6' name='TARGET_YMD' value='".$targetYmd."' onfocus='this.select();'>";
        echo     "&nbsp;<input type='submit' value='日付指定'>";
        echo     "&nbsp;</span><a href=http://aoyoko2.com/marketInfo/marketInfo.php?TARGET_CODE=".$targetCode."><font size=-1>marketInfo</font></a>";
        echo     "&nbsp;</span><a href=https://finance.yahoo.co.jp/quote/".$targetCode."/chart><font size=-1>Yahooチャート</font></a>";
        echo     "</TD>";
        echo     "</form>";
        echo   "</TR>";
        echo "</TABLE>";


        echo "<img src=./_img/".$targetCode.$targetFile." style=\"margin-top:4px;\"><BR>\n";




        if ($sixclrDistribution == '') {

          if (!isset($_REQUEST['TARGET_YMD'])) {

            echo "<a href='./search.php?TARGET_CD=".$targetCode."&SIXCLR_DISTRIBUTION=N225'>6色分布図表示</a><BR>\n";

          } else {

            echo "<a href='./search.php?TARGET_CD=".$targetCode."&TARGET_YMD=".$ymd."&SIXCLR_DISTRIBUTION=N225'>6色分布図表示</a><BR>\n";
          }


        } else {

          if (!isset($_REQUEST['TARGET_YMD'])) {

            if ($sixclrDistribution == 'N225') {
              $dummyResult = file_get_contents("http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MASUDAASHI_SIXCLR.php");
              echo "<img src='http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/sixClrDistributiontarget.gif'><BR>\n";
              echo "<a href='./search.php?TARGET_CD=".$targetCode."'>6色分布図非表示</a>";
              echo "&nbsp;<a href='./search.php?SIXCLR_DISTRIBUTION=MOTHERS&TARGET_CD=".$targetCode."'>（マザーズの6色分布図を表示）</a><BR>\n";
            } else {
              $dummyResult = file_get_contents("http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MASUDAASHI_SIXCLR_MOTHERS.php");
              echo "<img src='http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/sixClrDistributionMotherstarget.gif'><BR>\n";
              echo "<a href='./search.php?TARGET_CD=".$targetCode."'>6色分布図非表示</a>";
              echo "&nbsp;<a href='./search.php?SIXCLR_DISTRIBUTION=N225&TARGET_CD=".$targetCode."'>（日経225の6色分布図を表示）</a><BR>\n";
            }
          } else {

            if ($sixclrDistribution == 'N225') {
              $dummyResult = file_get_contents("http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MASUDAASHI_SIXCLR.php?TARGET_DATE=".$ymd);
              echo "<img src='http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/sixClrDistributiontarget.gif'><BR>\n";
              echo "<a href='./search.php?TARGET_CD=".$targetCode."&TARGET_YMD=".$ymd."'>6色分布図非表示</a>";
              echo "&nbsp;<a href='./search.php?SIXCLR_DISTRIBUTION=MOTHERS&TARGET_CD=".$targetCode."&TARGET_YMD=".$ymd."'>（マザーズの6色分布図を表示）</a><BR>\n";
            } else {
              $dummyResult = file_get_contents("http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MASUDAASHI_SIXCLR_MOTHERS.php?TARGET_DATE=".$ymd);
              echo "<img src='http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/sixClrDistributionMotherstarget.gif'><BR>\n";
              echo "<a href='./search.php?TARGET_CD=".$targetCode."&TARGET_YMD=".$ymd."'>6色分布図非表示</a>";
              echo "&nbsp;<a href='./search.php?SIXCLR_DISTRIBUTION=N225&TARGET_CD=".$targetCode."&TARGET_YMD=".$ymd."'>（日経225の6色分布図を表示）</a><BR>\n";
            }
          }

        }

      echo "</td>";
      echo "</tr>";
      echo "</table>";

      } else { // -------- end of「$targetCode <> ''」

  // ==== 銘柄コード指定用form表示
  echo "<nobr>\n";
  echo "<form action='./search.php' method='get'>\n";
  echo "<input type='text' size='20' name='TARGET_CD' value='".$targetCode."'>\n";
  echo "<input type='submit' value='analyze'>\n";
  echo "</form>\n";
  echo "</nobr>\n";

  echo "<script type=\"text/javascript\">\n";
//echo "document.getElementsByName('TARGET_CD').item(0).select();\n";
  echo "■■■■■■■■\n";
  echo "</script>\n";


      }

      echo "</tr>\n";
      echo "</table>\n";







/*
  if (isset($sixClrPtn)) {

    echo "6色パターン[".$sixClrPtn."]<BR>\n";
  }
*/

if (isset($execStatus)) echo $execStatus."<br>";

  echo "<table>\n";
  echo "  <FORM>\n";
  echo "    <INPUT type=\"button\" value=\"戻る\" onClick=\"history.back()\">\n";
  echo "  </FORM>\n";
  echo "</table>\n";


echo "</div>"; // green
echo "<div class='pink'></div>";
echo "</div>";

  echo "</BODY>\n";
  echo "</html>\n";

?>
