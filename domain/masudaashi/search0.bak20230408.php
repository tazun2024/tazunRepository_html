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
    ////include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');             //// search_Common.phpで定義される
    ////include(APP_ROOT.'COMMON/Util/CMX_common.php');                //// search_Common.phpで定義される
    ////include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');             //// search_Common.phpで定義される
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

            $rightContentsArr[] = '<div>右側のメインコンテンツ $targetCode=['.$targetCode.']</div>';

            $rightContentsArr[] = "<form action='./search0.php' method='get'>";
            $rightContentsArr[] = "<input type='hidden' name='TARGET_CODE' value='".$targetCode."'>";
            $rightContentsArr[] = "<input type='hidden' name='SIXCLR_DISTRIBUTION' value=''>";
            $rightContentsArr[] = "<input type='text' size='6' name='TARGET_DATE' value='' onfocus='this.select();'>&nbsp;<input type='submit' value='日付指定'>";
            $rightContentsArr[] = "</form>";


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
            $logMsgArr[] = 'sakuraに描画を指示します';
            $requestParm = '?TARGET_CODE='.$targetCode.'&TARGET_DATE='.$targetDate.'&BACHU_TMP_REQUEST='.$bachuTmpRequest;
            $dummyResult = file_get_contents('http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/MY_MA_requestHandler.php'.$requestParm);

           if (mb_strpos($dummyResult, 'no_img') === false) {

               $sakuraUrl = 'http://ik1-326-23246.vs.sakura.ne.jp/BillionStuff/%5Eact/MY_MASUDAASHI/_fileWork/';
               $putFileNameBase = '_'; // ¶ここにリクエストごとのハッシュ値を設定すれば不特定多数からのリクエストに対応できる

               // ==== 画像ファイルを取得
             //$image = file_get_contents($sakuraUrl.$targetCode.'target.gif?dummy='.date('Ymdhis'));
             //file_put_contents(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$putFileNameBase.'gif', $image);

               // ==== 属性情報ファイルを取得
               $attribute = file_get_contents($sakuraUrl.$targetCode.'.attribute?dummy='.date('Ymdhis'));
               file_put_contents(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$putFileNameBase.'.attribute', $attribute);
               include(DOMAIN_ROOT.'masudaashi/_img/'.$targetCode.$putFileNameBase.'.attribute');

       ////////$rightContentsArr[] = '<div><img src=./_img/kaisyaDummy.org.png></div>';

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
