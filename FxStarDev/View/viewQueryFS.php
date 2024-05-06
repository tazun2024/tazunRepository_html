
<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_fsEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(APP_ROOT.'COMMON/Base/CoreBase.php');
include(APP_ROOT.APP_TYPE.'/_FSCONF.php');
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');
include(APP_ROOT.'COMMON/Log/CmxLog.php');
include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');




// ==== ==== ==== ====
// ==== ==== ==== ==== ログ生成
// ==== ==== ==== ====
$myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/View/Logs/'.APP_TYPE.date('Ymd_H00').'.view.log');


// ==== ==== ==== ====
// ==== ==== ==== ==== DAO生成
// ==== ==== ==== ====
include(APP_ROOT.APP_TYPE.'/Util/FSUtil.php');
include(APP_ROOT.APP_TYPE.'/Dao/FSDao.php');
$myCmxDao = new FSDao($myCmxLog, INVESTX_NOT_APPLICABLE_VALUE, CoreBase::T_AUD5M);




$VIEW = array();
$contentsArr = array();

$contentsArr[] = '<br>';

// ==== ==== ==== ====
// ==== ==== ==== ==== URLパラメタで処理を場合分け
// ==== ==== ==== ====
$SQL = $_REQUEST['execQuery'] ?? '';
if ($SQL <> '') {

    ////////
    //////// execQueryとしてクエリ実行を仰せつかった
    ////////

    //////// getLoggingDatetimeStr関数()をかます必要があるカラム指定
    $dTimeColumn = $_REQUEST['dTimeColumn'] ?? '';
    $COLUMNARR_DTIMESTR = explode(',', $dTimeColumn);


    ////////////////////////////////
    //
    // 実行クエリのtextarea表示
    //
    ///////////////////////////////
  //$contentsArr[] = '<table>';
    $contentsArr[] = '<table style="width:80%; table-layout:fixed;">';
    $contentsArr[] = '<tr><th align=left>execQuery</th></tr>';
    $contentsArr[] = '<tr><td align=left>';
  //$contentsArr[] = '<textarea style="width:300px; min-height:80px;" name=execQuery>'.$SQL.'</textarea><br>';
    $contentsArr[] = '<textarea style="width:100%; min-height:80px;" name=execQuery>'.$SQL.'</textarea><br>';
    $contentsArr[] = '</td></tr>';
    $contentsArr[] = '</table>';

    //////// 更新可能なページへのリンクを置いておく（行った先でBASIC認証）
    $contentsArr[] = '<form method=post name=form1 action="../_secure/execQueryFS2022.php">';
    $contentsArr[] = '<input type=hidden name=execQuery value="'.$SQL.'">'; // ¶クエリにはSPCが含まれるのでvalueをダブルコーテーションで括る必要がある
    $contentsArr[] = '<input type=hidden name=dTimeColumn value='.$dTimeColumn.'>';
    $contentsArr[] = '<a href="javascript:form1.submit();">execQueryFS2022</a>';
    $contentsArr[] = '</form>';

    include('../../COMMON/View/execute_execQuery.php');
}




// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'viewQueryFS';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
?>
