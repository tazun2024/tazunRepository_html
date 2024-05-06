<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_fsEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(APP_ROOT.'COMMON/Base/CoreBase.php'); // 2023.07.07
include(APP_ROOT.APP_TYPE.'/_FSCONF.php');
include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');
include(APP_ROOT.'COMMON/Log/CmxLog.php');
include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');

include(APP_ROOT.APP_TYPE.'/Util/FSUtil.php'); // ※内部でTickDoneをinclude∴CoreBase.phpをinclude
include(APP_ROOT.'COMMON/Core/CreateShinneashi.php');
include(APP_ROOT.'COMMON/Core/CreateMasudaashi.php');




// ==== ==== ==== ====
// ==== ==== ==== ==== ログ生成
// ==== ==== ==== ====
$myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/View/Logs/'.APP_TYPE.date('Ymd_H00').'.view.log');

// ==== ==== ==== ====
// ==== ==== ==== ==== DAO生成
// ==== ==== ==== ====
include(APP_ROOT.APP_TYPE.'/Dao/FSDao.php');
$myCmxDao = new FSDao($myCmxLog, 'view_dummyValue', CoreBase::T_USD5M); // 2023.07.10 暫定実装、ただしくはシミュレーションのSymbolCodeをどこかconfにもたせる

// 2023.01.07
//$myCmxDao->setLegacyCompatible($FS_PARAM['FS_LEGACY_COMPATIBLE']);
// 2023.07.10 LEGACY_COMPATIBLE廃止（上弦下弦における旧来の仕様再現であったため∵上弦下弦は廃止済み）



$VIEW = array();
$contentsArr = array();

$contentsArr[] = '<br>';

// ==== ==== ==== ====
// ==== ==== ==== ==== URLパラメタで処理を場合分け
// ==== ==== ==== ====
$SQL = $_REQUEST['execQuery'] ?? ''; // どうしてもUndefined index の Notice を抑止できない。。
if ($SQL == '') {

    //////// URLパラメータの取得
    $action = $_REQUEST['action'] ?? ''; // どうしてもUndefined index の Notice を抑止できない。。
    $contentsArr[] = '<B>$action : [</B>'.$action.'<B>]</B><br>'."\n";


    switch($action) {

      case 'insert__FS_ADMIN':

          break;

      case 'cloneAdmin_CronAUD':

          // クーロンadmin from Prd環境 コンテンツ（CR_FSAUDをコピー）
          include(HTML_ROOT.APP_TYPE.'/_secure/syncroCopyFsSAKURA_ADMIN.php');

          break;

      case 'synchroFsChart_AUD':

          // FS_CHARTの同期
          include(HTML_ROOT.APP_TYPE.'/_secure/synchroFsSAKURA_FS_CHART.php');

          break;

      case 'synchroFsChart_USD':

          // FS_CHARTの同期
          include(HTML_ROOT.APP_TYPE.'/_secure/synchroFsSAKURA_FS_CHART.php');

          break;

      default:

          ////////////////////////////////////////////////////////
          //       ↓↓↓↓【ここに処理を入れる】↓↓↓↓       //
          ////////////////////////////////////////////////////////

/*
          echo date('Y-m-d H:i:s').'<br>';

          for ($i=1; $i<=100; $i++) {
              // 後から計算させる方が遅い、10回平均で15.5秒
              $resultArr = $myCmxDao->getListTypeRecord("select ask, bid from FS_CHART where symbolCd= 'AUD' order by date LIMIT 100000");
              // 遅いのかと心配して計測したが、10回平均で11.3秒
            //$resultArr = $myCmxDao->getListTypeRecord("select (bid + ask) div 2 as price from FS_CHART where symbolCd= 'AUD' order by date LIMIT 100000");

              $count = 0;
              foreach ($resultArr as $row) {

                  $price = ($row->ask + $row->bid)/2;
                  $count ++;
              }
              //echo 'count='.$count.'<br>';
              echo date('Y-m-d H:i:s').'<br>';

          }
*/





          ////////////////////////////////////////////////////////
          //       ↑↑↑↑【ここに処理を入れる】↑↑↑↑       //
          ////////////////////////////////////////////////////////
          break;

    }  // -- end of switch()

} else {

    ////////
    //////// execQueryとしてクエリ実行を仰せつかった
    ////////

    //////// getLoggingDatetimeStr関数()をかます必要があるカラム指定
    $dTimeColumn = $_REQUEST['dTimeColumn'] ?? ''; // どうしてもUndefined index の Notice を抑止できない。。
    $COLUMNARR_DTIMESTR = explode(',', $dTimeColumn);


    ////////////////////////////////
    //
    // 実行クエリのtextarea表示
    //
    ///////////////////////////////
    $contentsArr[] = '<table style="width:80%; table-layout:fixed;">';
    $contentsArr[] = '<tr><th align=left>execQuery</th></tr>';
    $contentsArr[] = '<tr><td align=left>';
    $contentsArr[] = '<form method=post action=./execQueryFS2022.php>';
    $contentsArr[] = '<textarea style="width:100%; min-height:80px;" name=execQuery>'.$SQL.'</textarea><br>';
    $contentsArr[] = '<div style="padding-top:1px;"><input style="width:160px" type=submit value=再実行><input type=hidden name=dTimeColumn value='.$dTimeColumn.'></div>';
    $contentsArr[] = '</form>';
    $contentsArr[] = '</td></tr>';
    $contentsArr[] = '</table>';

    include('../../COMMON/View/execute_execQuery.php');
}




// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'execQueryFS2022';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
?>
