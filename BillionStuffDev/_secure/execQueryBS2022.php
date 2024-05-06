<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_bsEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(APP_ROOT.'COMMON/Base/CoreBase.php'); // 2023.07.07
include(APP_ROOT.APP_TYPE.'/_BSCONF.php');
include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');
include(APP_ROOT.'COMMON/Log/CmxLog.php');
include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');

include(APP_ROOT.APP_TYPE.'/Util/BSUtil.php'); // BSDao内で BSUtil::T_CMX_SIMULATIONの場合分けをするので必要 ※内部でTickDoneをinclude∴CoreBase.phpをinclude
//include(APP_ROOT.'COMMON/Base/CoreBase.php');
include(APP_ROOT.'COMMON/Core/CreateShinneashi.php');
include(APP_ROOT.'COMMON/Core/CreateMasudaashi.php');




// ==== ==== ==== ====
// ==== ==== ==== ==== ログ生成
// ==== ==== ==== ====
$myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/View/Logs/'.APP_TYPE.date('Ymd_H00').'.view.log');


// ==== ==== ==== ====
// ==== ==== ==== ==== DAO生成
// ==== ==== ==== ====
include(APP_ROOT.APP_TYPE.'/Dao/BSDao.php');
$myCmxDao = new BSDao($myCmxLog, INVESTX_NOT_APPLICABLE_VALUE);

// 2023.01.07
//$myCmxDao->setLegacyCompatible($N225M_PARAM['BS_LEGACY_COMPATIBLE']);
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

      case "insert__BS_ADMIN":

          break;


      default:

          ////////////////////////////////////////////////////////
          //       ↓↓↓↓【ここに処理を入れる】↓↓↓↓       //
          ////////////////////////////////////////////////////////

/*
        //$sql = "insert N225M_CHART values('2023-01-01 00:00', 25825)";
          $sql = "delete from N225M_CHART where date = '2023-01-01 00:00'";
          $contentsArr[] = $sql.'<br>';
          foreach (json_decode($myCmxDao->query($sql), true) as $key => $value) $contentsArr[] = $key.':'.$value.'<br>';
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
    $contentsArr[] = '<form method=post action=./execQueryBS2022.php>';
    $contentsArr[] = '<textarea style="width:100%; min-height:80px;" name=execQuery>'.$SQL.'</textarea><br>';
    $contentsArr[] = '<div style="padding-top:1px;"><input style="width:160px" type=submit value=再実行><input type=hidden name=dTimeColumn value='.$dTimeColumn.'></div>';
    $contentsArr[] = '</form>';
    $contentsArr[] = '</td></tr>';
    $contentsArr[] = '</table>';

    include('../../COMMON/View/execute_execQuery.php');
}




// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'execQueryBS2022';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
?>
