<?php

// **
// ** 2021.11.12 再構築
// **
// ** 2022.10.03 再構築
//

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
    define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
  //define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
  //define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/html/');


    while (true) {

        /**
         * 環境変数のチェック
         *
         */
        if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {

            $VIEW['TITLE'] = 'ERROR';
            $VIEW['ERRORMSG'] = '<div style="color: red;">invalid env.<br>APP_ROOT['.APP_ROOT.']<br>HTML_ROOT['.HTML_ROOT.']<br></div>';

            //// //// //// //// 強制終了 //// //// //// ////
            break;
        }




        // ==== ==== ==== ====
        // ==== ==== ==== ==== 定数設定
        // ==== ==== ==== ====
        define('DOMAIN_ROOT', HTML_ROOT.'domain/');


        // ==== ==== ==== ====
        // ==== ==== ==== ==== 必要なファイルをinclude
        // ==== ==== ==== ====
        include(DOMAIN_ROOT.'masudaashi/search_common.php');
////////include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');             //// search_Common.phpで定義される
////////include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');             //// search_Common.phpで定義される
////////include(APP_ROOT.'COMMON/Util/CMX_common.php');                //// search_Common.phpで定義される
////////include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');             //// search_Common.phpで定義される

        include(APP_ROOT.'COMMON/Log/CmxLog.php');



        /**
         * 全URLパラメタ
         *
         */
        $targetCode = $_REQUEST['TARGET_CODE'] ?? '';
        $targetDate = $_REQUEST['TARGET_DATE'] ?? '';
        $bachuTmpRequest = $_REQUEST['BACHU_TMP_REQUEST'] ?? '';
        $sixcolorDistributiontargetYmd = $_REQUEST['SIXCLR_DISTRIBUTION'] ?? '';


        /**
         * 新しいタブで表示する処理ログ
         *
         */
        $logMsgArr = array();








        /**
         * 左側の検索履歴一覧
         *
         */
        $leftContentsArr =array();

        $sixClrDistributionLinkStr = '';
        $leftContentsArr[] = '<a href=./search0.php?TARGET_CODE=1001'.$sixClrDistributionLinkStr.'>1001 日経２２５種平均</a><br>';
        $leftContentsArr[] = '<a href=./search0.php?TARGET_CODE=1021'.$sixClrDistributionLinkStr.'>1021 ミニ２２５先物（中心限月）</a><br>';
        $leftContentsArr[] = '<a href=./search0.php?TARGET_CODE=1003'.$sixClrDistributionLinkStr.'>1003 米ドル／円</a><br>';
        $leftContentsArr[] = '<a href=./search0.php?TARGET_CODE=1081'.$sixClrDistributionLinkStr.'>1081 ダウ工業株３０種平均</a><br>';
        $leftContentsArr[] = '<hr>';
        $leftContentsArr[] = '<a href=./search0.php?TARGET_CODE=7974'.$sixClrDistributionLinkStr.'>7974 任天堂</a><br>';







        /**
         * 右側のメインコンテンツ
         *
         */
        $rightContentsArr =array();

        if ($targetCode <> '') {

            $rightContentsArr[] = '<div style="color:lightgray;">右側のメインコンテンツ</div>';

            $rightContentsArr[] = "<nobr>\n";
            $rightContentsArr[] = "<form action='./search.php' method='get'>\n";
            $rightContentsArr[] = "<input type='text' size='20' name='TARGET_CD' value='".$targetCode."'>\n";
            $rightContentsArr[] = "<input type='submit' value='analyze'>\n";

            $rightContentsArr[] = "&nbsp;</span><a href=https://cmx.boy.jp/html/domain/marketInfo/marketInfo.php?TARGET_CODE=".$targetCode."><font size=-1>marketInfo</font></a>";
            $rightContentsArr[] = "&nbsp;</span><a href=https://finance.yahoo.co.jp/quote/".$targetCode."/chart><font size=-1>Yahooチャート</font></a>";

            $rightContentsArr[] = "</form>\n";
            $rightContentsArr[] = "</nobr>\n";

            $rightContentsArr[] = "<script type=\"text/javascript\">\n";
            $rightContentsArr[] = "document.getElementsByName('TARGET_CD').item(0).select();\n";
            $rightContentsArr[] = "</script>\n";

            $rightContentsArr[] = "<table>\n";

            $rightContentsArr[] = "<tr>\n";
            $rightContentsArr[] = "<td colspan=2 style=\"background-color:#e1f0f5;\">";
            $ymd = date('Y-m-d');
            $meigaraNm = getMeigaraNm($targetCode);
            $rightContentsArr[] = '<B>'.date('Y/m/d（', strtotime($ymd)).array('日', '月', '火', '水', '木', '金', '土')[date('w', strtotime($ymd))].'）</B>';
            $rightContentsArr[] = "<TD>".$meigaraNm."</TD>";
            $rightContentsArr[] = "</tr>\n";

            $rightContentsArr[] = "</table>\n";

            $rightContentsArr[] = "<TABLE border=0 cellspacing=0 cellpadding=0 width=600px>";
            $rightContentsArr[] = "<TR>";

            $rightContentsArr[] = "<TD>";
            // 蛇足ですが、フォームの下にできる１行分の空白を消すためには、TD タグと FORM タグの位置を交換します。
            // これも常套手段の１つで、ほとんどのブラウザでは正しく表示されますが、HTML の構造としては正しくないので、何らかのツールでチェックすると警告されることがあります。
            $rightContentsArr[] = "<form action='./search.php' method='get'>";
          //$rightContentsArr[] = "<input type='hidden' name='TARGET_CD' value='".$targetCode."'>";
          //$rightContentsArr[] = "<input type='hidden' name='SIXCLR_DISTRIBUTION' value='".$sixclrDistribution."'>";
            $targetYmd = $ymd;
            $rightContentsArr[] = "</form>";
            $rightContentsArr[] = "</TD>";

            $rightContentsArr[] = "</TR>";
/*
        echo APP_ROOT.'BillionStuffRsv/Util/BSUtil.php<br>';
        include(APP_ROOT.'BillionStuffRsv/Util/BSUtil.php');
        、、、がj必要になる。しかもBS系とFS系の切り替えが必要¶BS運用停止後の方針決定が必要

        さらにBSUtil.php内で定数APP_TYPEは随所に使われているので定義が必要



            $prvfwdResult = (object)['rev'=>date('Y/m/d', _backToDaysBS(strtotime($ymd), 1))]; // ◆◆◆◆直ぐにstrtotimeしてdate('Y/m/d')する、しかも持って帰らない
            $prvfwdResult = (object)['fwd'=>date('Y/m/d', _nextGoDaysBS(strtotime($ymd), 1))]; // ◆◆◆◆直ぐにstrtotimeしてdate('Y/m/d')する、しかも持って帰らない
            $rightContentsArr[] = "<tr>\n";
*/
            $rev = date('Y/m/d', strtotime('-1 day'));
            $fwd = date('Y/m/d', strtotime('+1 day'));

            $rightContentsArr[] = "<td align=left>\n";
            $rightContentsArr[] = '<font size=-1>';
            $rightContentsArr[] = '<a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD='.$rev.$sixClrDistributionLinkStr.'>'.date('Y/m/d', strtotime($rev)).'<br>《《</a>';
            $rightContentsArr[] = '</font>';
            $rightContentsArr[] = "</td>\n";

            $rightContentsArr[] = "<TD>&nbsp;<input type='text' size='6' name='TARGET_YMD' value='".$targetYmd."' onfocus='this.select();'>";
            $rightContentsArr[] = "&nbsp;<input type='submit' value='日付指定'>";
            $rightContentsArr[] = "</TD>";

            $rightContentsArr[] = "<td align=right>\n";
            $rightContentsArr[] = '<font size=-1>';
            $rightContentsArr[] = '<a href=./search.php?TARGET_CD='.$targetCode.'&TARGET_YMD='.$fwd.'&BACHU_TMP=DONE'.$sixClrDistributionLinkStr.'>'.date('Y/m/d', strtotime($fwd)).'<br>》》</a>';
            $rightContentsArr[] = '</font>';
            $rightContentsArr[] = "</td>\n";

            $rightContentsArr[] = "</tr>\n";

            $rightContentsArr[] = "</TABLE>";

/*
            $rightContentsArr[] = "<form action='./search0.php' method='get'>";
            $rightContentsArr[] = "<input type='hidden' name='TARGET_CODE' value='".$targetCode."'>";
            $rightContentsArr[] = "<input type='hidden' name='SIXCLR_DISTRIBUTION' value=''>";
            $rightContentsArr[] = "<input type='text' size='12' name='TARGET_DATE' value='' onfocus='this.select();'>&nbsp;<input type='submit' value='日時指定'>";
            $rightContentsArr[] = "</form>";
*/

            /**
             * 描画をsakuraに指示
             *
             * [画像URL]受領
             * [属性情報]受領
             * [最新〆済日]受領
             * [BATHU_TMP有無※最新〆済日とMY_MA_5Mとの比較]/[FWD日]受領
             * [RWD日]受領
             *
             */
            $logMsgArr[] = 'sakuraに描画を依頼します';
            $logMsgArr[] = '';

            $requestParm = '?TARGET_CODE='.$targetCode.'&TARGET_DATE='.$targetDate.'&BACHU_TMP_REQUEST='.$bachuTmpRequest;
            $dummyResult = file_get_contents('http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MA_requestHandler.php'.$requestParm);

           if (mb_strpos($dummyResult, 'no_img') === false) {

               $sakuraUrl = 'http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/';
               $putFileNameBase = '_'; // ¶ここにリクエストごとのハッシュ値を設定すれば不特定多数からのリクエストに対応できる

               // ==== 画像ファイルを取得
               $image = file_get_contents($sakuraUrl.$targetCode.'target.gif?dummy='.date('Ymdhis'));
               file_put_contents(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$putFileNameBase.'gif', $image);

               // ==== 属性情報ファイルを取得
               $attribute = file_get_contents($sakuraUrl.$targetCode.'.attribute?dummy='.date('Ymdhis'));
               file_put_contents(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$putFileNameBase.'.attribute', $attribute);
               include(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$putFileNameBase.'.attribute');

               $rightContentsArr[] = '<div><img src=./_img/'.$targetCode.$putFileNameBase.'gif'.'></div>';

               // ==== 新しいcfgファイルに書き替え

          } else {

            $rightContentsArr[] = '暫定メッセージ no_img';
          }

        } else {

            $logMsgArr[] = '$targetCodeの指定がないので描画指示は行いません';
        }


        break;

    }

// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'MY_MA'.($targetCode ? ' '.$targetCode : '');
$VIEW['ERRORMSG'] = '';
$VIEW['LOGMSG'] = $logMsgArr;
$VIEW['LEFTCONTENTS'] = $leftContentsArr;
$VIEW['RIGHTCONTENTS'] = $rightContentsArr;
//$VIEW['LOGMSG'] = $logMsgArr;
include('./search.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

?>
