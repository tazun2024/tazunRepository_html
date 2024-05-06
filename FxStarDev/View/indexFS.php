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
include(APP_ROOT.'COMMON/Util/InvestxNewUtil.php');
include(APP_ROOT.APP_TYPE.'/Util/FSUtil.php');

// ==== ==== ==== ==== 2023.07.06 SimulationFSから読込む「_FSSimulationPARAM.php」内で使用されることから、移設しました
include(APP_ROOT.'COMMON/Base/CoreBase.php');

include(APP_ROOT.'COMMON/Core/CreateShinneashi.php');
include(APP_ROOT.'COMMON/Core/CreateMasudaashi.php');

include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');

include(APP_ROOT.APP_TYPE.'/_FSCONF.php');








//////// ログ生成
$myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/View/Logs/'.APP_TYPE.date('Ymd_H00').'.view.log');

//////// DAO生成
include(APP_ROOT.APP_TYPE.'/Business/FS_BusinessBase.php');
include(APP_ROOT.'COMMON/Base/AdminBase.php');
include(APP_ROOT.APP_TYPE.'/Dao/FSDao.php');




//////// 2022.10.10 USD切替え対応


$sybolCode = $_REQUEST['SYMBOL_CODE'] ?? CoreBase::T_USD5M;
$myFSDao = new FSDao($myCmxLog, 'view_dummyValue', $sybolCode);

// 2023.01.07
//$myFSDao->setLegacyCompatible($FS_PARAM['FS_LEGACY_COMPATIBLE']);
// 2023.07.10 LEGACY_COMPATIBLE廃止（上弦下弦における旧来の仕様再現であったため∵上弦下弦は廃止済み）



//////// 表示する日時範囲を決める
if (($_REQUEST['FLOP_DTSTR'] ?? '') == '') {

    //// 日時の指定が特にないのでフツーに現在の状況を見る時
    $showTargetDtime = time();
    $radioLinkParamStr = '';

} else {

    //// admin画面で過去の日付を指定してきた場合
    $showTargetDtime =strtotime($_REQUEST['FLOP_DTSTR']);
    $radioLinkParamStr = '&FLOP_DTSTR='.$_REQUEST['FLOP_DTSTR'];
}
////echo '$showTargetDtime['.getLoggingDatetimeStr_fromDtime($showTargetDtime).']<br>';


//////// SHOW_ELEMENTRANGEを決める
if (($_REQUEST['SHOW_ELEMENTRANGE'] ?? '') == '') {

    //// 日時の指定が特にないのでフツーに現在の状況を見る時
    $showRange = 20; // 暫定値element20

} else {

    //// admin画面で過去の日付を指定してきた場合
    $showRange = $_REQUEST['SHOW_ELEMENTRANGE'];
}

//////// 念願の表示さかのぼり対応
if ($showTargetDtime !== ''
    and $showTargetDtime == ($_REQUEST['VIEW_FLOP_DTSTR'] ?? '')) {

        //// 現在表示中のFLOP_DTSTRと同じ指定をした場合
        echo 'さかのぼりきぼん \'+12 hour\'<br>';
        $showTargetDtime = strtotime('+12 hour', $showTargetDtime);
}

//////// 指定されたFLOP_DTSTRまでが$showTargetDtimeとして表示対象になる
//       では、どこからが表示対象かというと「$showTargetDtimeから遡るflop数」
define('SHOW_ELEMENTRANGE', $showRange);


$VIEW = array();
$contentsArr = array();

$contentsArr[] = '<br>';


////////////////////////////////
//
// リンクボタン「TRENDREPORT」
//
///////////////////////////////


////////
// SYMBOL_CODE選択のラジオボタン建設中
// ラジオボタンをおしゃれにする https://kodocode.net/design-css-radiobutton/
////////
$contentsArr[] = '<script>function jumpAUD(){location.href="./indexFS.php?SYMBOL_CODE=AUD5M'.$radioLinkParamStr.'"}</script>';
$contentsArr[] = '<script>function jumpUSD(){location.href="./indexFS.php?SYMBOL_CODE=USD5M'.$radioLinkParamStr.'"}</script>';
$contentsArr[] = '<form name="radioForm">'; // submitはjavaScriptでやってる
$contentsArr[] = '<input name=symbolCd type="radio" value="AUD5M"'.($sybolCode == CoreBase::T_AUD5M ? 'checked=checked' : '').'onClick=jumpAUD()>AUD</input>';
$contentsArr[] = '<input name=symbolCd type="radio" value="USD5M"'.($sybolCode == CoreBase::T_USD5M ? 'checked=checked' : '').'onClick=jumpUSD()>USD</input>';


////////
// 固定20のSHOW_ELEMENTRANGを可変にするプルダウンリスト
////////
$contentsArr[] = '<script>function jumpSelect(range){';
$contentsArr[] = 'const symbolCd = document.radioForm.symbolCd;';
$contentsArr[] = 'let result="";';
$contentsArr[] = 'for(let i=0; i<symbolCd.length; i++) {if (symbolCd[i].checked) {result=symbolCd[i].value; break;}}';
//$contentsArr[] = 'alert(result);alert(range);';
$contentsArr[] = 'location.href=`./indexFS.php?SYMBOL_CODE=${result}&SHOW_ELEMENTRANGE=${range}`'.$radioLinkParamStr.'}</script>'; // javascript 文字列結合 変数
$contentsArr[] = '<style>select{font-size:20px;</style>';
$contentsArr[] = '<span style="margin-right:20px;"></span><select onChange="jumpSelect(this.options[this.selectedIndex].value);">';
$contentsArr[] = '<option value=20'.(SHOW_ELEMENTRANGE == 20 ? ' selected' : '').'>20</option>';
$contentsArr[] = '<option value=100'.(SHOW_ELEMENTRANGE == 100 ? ' selected' : '').'>100</option>';
$contentsArr[] = '<option value=200'.(SHOW_ELEMENTRANGE == 200 ? ' selected' : '').'>200</option>';
$contentsArr[] = '<option value=500'.(SHOW_ELEMENTRANGE == 500 ? ' selected' : '').'>500</option>';
$contentsArr[] = '</select>';

$contentsArr[] = '</form>';


////////
// 各タブからの<a>タグPOSTのためのformタグ、ここから
////////
$contentsArr[] = '<script>function requestFlopLink(ftDtStr){document.getElementById("FLOP_DTSTR").value=ftDtStr;}</script>';
$contentsArr[] = '<form method=post name=TABMENU action=./indexFS.php?SYMBOL_CODE='.$sybolCode.'><input type=hidden name=FLOP_DTSTR value="" id=FLOP_DTSTR />'; // ¶再描画の時のchecked用にラジオボタンの選択状態が欲しいので<a>タグでPOSTする


////////////////////////////////
//
// メインタブ1「新値足」コンテンツ
//
///////////////////////////////
$main1ContentsName = '新値足';
$manu1ContentsArr = array();

// ----------------------------------------
// ◆新値足の生成
// ----------------------------------------
$rootDTimeStr = $myFSDao->getShinneRootDateTime(getLoggingDatetimeStr_fromDtime($showTargetDtime));
////echo '$rootDTimeStr['.getLoggingDatetimeStr($rootDTimeStr).']<br>';
$myShinneashi = new CreateShinneashi($myCmxLog, $myFSDao);
$myShinneashi->init($sybolCode, ($rootDTimeStr ?? FS_ORIGIN_DATE), getLoggingDatetimeStr_fromDtime($showTargetDtime));

// ========
// ======== 作成済みの新値足のうち、どこから（～$showTargetDtimeまで）を表示するかをSHOW_ELEMENTRANGEで決定する
// ========
$showTopDatetimeStr = $myShinneashi->getElement_dtime($showTargetDtime, SHOW_ELEMENTRANGE - 1)->date;
if ($showTopDatetimeStr > $myShinneashi->getFlopElement_dtime($showTargetDtime, 1)->date) {

    //// ==== 更足が続きすぎてflop1まで到達しない場合はSHOW_ELEMENTRANGEを超えてしまうけどflop1を採用（しないと、FlopLinkできなくなる）
    $showTopDatetimeStr = $myShinneashi->getFlopElement_dtime($showTargetDtime, 1)->date;
}

// ◆増田足も同時に表示するモード
$myMasudaashi = new CreateMasudaashi($myCmxLog, $myFSDao);
$myMasudaashi->init($sybolCode, $showTopDatetimeStr, getLoggingDatetimeStr_fromDtime($showTargetDtime));
$myShinneashi->setMasudaashi($myMasudaashi);

// ======== 新値足表示
$manu1ContentsArr[] = '<table>';
$manu1ContentsArr = array_merge($manu1ContentsArr, $myShinneashi->html_getShow_dateTime($showTopDatetimeStr));
$manu1ContentsArr[] = '</table>';

// 2023.08.06 現在表示中のFLOP_DTSTRと同じ指定をした場合の検知用に追加（念願の表示さかのぼり対応）
$manu1ContentsArr[] = '<input type=hidden name=VIEW_FLOP_DTSTR value='.$showTargetDtime.'>';


////////////////////////////////
//
// メインタブ2「増田足」コンテンツ
//
///////////////////////////////
$main2ContentsName = '増田足';
$manu2ContentsArr = array();

// ======== 新値足表示
$manu2ContentsArr[] = '<table>';
$manu2ContentsArr = array_merge($manu2ContentsArr, $myMasudaashi->html_getShow_dateTime($showTopDatetimeStr));
$manu2ContentsArr[] = '</table>';


////////////////////////////////
//
// メインタブ3「チャート」コンテンツ
//
///////////////////////////////
$main3ContentsName = 'チャート';
$manu3ContentsArr = array();
$chartArr = $myFSDao->getListTypeRecord("select date, bid, ask from FS_CHART as FS_CHART where symbolCd = '".CoreBase::getSymbolCd($sybolCode)."' and date >= '".$showTopDatetimeStr."' and date <= '".getLoggingDatetimeStr_fromDtime($showTargetDtime)."'");
$manu3ContentsArr[] = '<table>';
$manu3ContentsArr = array_merge($manu3ContentsArr, html_getChartTable($chartArr, $myShinneashi, $sybolCode));
$manu3ContentsArr[] = '</table>';


////////////////////////////////
//
// メインタブ コンテンツ作成
//
///////////////////////////////
$parameterArr = array(
    (object)['NAME'=>$main1ContentsName, 'CONTENTS'=>$manu1ContentsArr],
    (object)['NAME'=>$main2ContentsName, 'CONTENTS'=>$manu2ContentsArr],
    (object)['NAME'=>$main3ContentsName, 'CONTENTS'=>$manu3ContentsArr]
);

$contentsArr = array_merge($contentsArr, html_getMainSelectTableLinkMenu($parameterArr, intval($_REQUEST['TAB'] ?? '1'))); // name=TABでグループ化されたラジオボタンで選択されていたタブの番号がPOSTされてくる


////
// 各タブからの<a>タグPOSTのためのformタグ、ここまで
////
$contentsArr[] = '</form>';




////////////////////////////////
//
// リンクボタン「TRENDREPORT」
// ※新値足の生成で決まる$showTopDatetimeStrを利用したいのでココへ移動
//
///////////////////////////////
$sql = "select date, id, msg from FS_TRENDREPORT where date >= '".$showTopDatetimeStr."' order by date";
$dTimeColumn = '&dTimeColumn=date';
$contentsArr = array_merge(array('<p></p>'), html_getLinkButon('TRENDREPORT', './viewQueryFS.php?execQuery='.$sql.$dTimeColumn, 'target=_blank'), $contentsArr);




////////////////////////////////
//
// currentDisp情報
// ・建玉（ダミー正否）
// ・前回建玉、ゲイン/ロス
// ・上弦/下弦
// ・TJM_HOLD有無
///////////////////////////////
//$informationObj = $myFSDao->getCurrentDipInformation();
//$contentsArr = array_merge($contentsArr, html_getFsDispInfo($informationObj, $sybolCode));







$contentsArr[] = '<br><HR align=left width=80% color=silver>';

$contentsArr[] = '<p></p>';


////
// 各タブからの<a>タグPOSTのためのformタグ、ここまで
////
$contentsArr[] = '</form>';



////////////////////////////////
//
// リンクボタン「シミュレーション」
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getLinkButon('シミュレーション', './indexFS.php?SYMBOL_CODE='.$sybolCode, ''));


////////////////////////////////
//
// リンクボタン「管理画面」
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getLinkButon('管理画面', '../_secure/adminFS.php', ''));




// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'FxStar';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

?>