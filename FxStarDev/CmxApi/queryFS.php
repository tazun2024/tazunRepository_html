<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_fsEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');
include(APP_ROOT.'COMMON/Log/CmxLog.php');
include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');


$myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/CmxApi/Logs/syncroLog'.date('Ymd_H00').'.log');


$myCmxLog->inf('request logId['.(@$_REQUEST['logId'] ?: '').']');

$queryStr = @$_REQUEST['QUERY'] ?: '';

if (mb_strpos(strtoupper($queryStr), 'SELECT') === 0) { // ¶selectから始まる文字列であること

    include(APP_ROOT.APP_TYPE.'/CmxApi/FS_ExecQuery.php');

    try {

        $contentsArr = (new FS_ExecQuery($myCmxLog))->execQuery($queryStr);

    } catch (Throwable $th) {

        $myCmxLog->err($th->getTraceAsString());
        $contentsArr[] = $th->getTraceAsString();
    }

} else {

    $contentsArr = ['no row(s)'];
    $contentsArr[] = 'invalid QUERY ['.$queryStr.']';
}


$VIEW = array();

// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'CmxApi FxStar';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
?>