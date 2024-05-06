<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_bsEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');
include(APP_ROOT.'COMMON/Log/CmxLog.php');
include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
include(APP_ROOT.'COMMON/Util/InvestxNewUtil.php');
include(APP_ROOT.APP_TYPE.'/Util/BSUtil.php');

// ==== ==== ==== ==== 2023.07.06 FxStarの横展開⇒SimulationFSから読込む「_FSSimulationPARAM.php」内で使用されることから、移設しました
include(APP_ROOT.'COMMON/Base/CoreBase.php');

include(APP_ROOT.'COMMON/Core/CreateShinneashi.php');
include(APP_ROOT.'COMMON/Core/CreateMasudaashi.php');

include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');

include(APP_ROOT.APP_TYPE.'/_BSCONF.php');








//////// ログ生成
$myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/View/Logs/'.APP_TYPE.date('Ymd_H00').'.view.log');
$myCmxLog->setLoggingLevel(CmxLog::T_LOGGING_DEBUG);

//////// DAO生成
include(APP_ROOT.APP_TYPE.'/Business/BS_BusinessBase.php');
include(APP_ROOT.'COMMON/Base/AdminBase.php');
include(APP_ROOT.APP_TYPE.'/Dao/BSDao.php');
$myBSDao = new BSDao($myCmxLog, 'view_dummyValue');

// 2023.01.07
//$myBSDao->setLegacyCompatible($N225M_PARAM['BS_LEGACY_COMPATIBLE']);
// 2023.07.10 LEGACY_COMPATIBLE廃止（上弦下弦における旧来の仕様再現であったため∵上弦下弦は廃止済み）



//////// 表示する日時範囲を決める
if (($_REQUEST['FLOP_DTSTR'] ?? '') == '') {

    //// 日時の指定が特にないのでフツーに現在の状況を見る時
    $showTargetDtime = time();

} else {

    //// admin画面で過去の日付を指定してきた場合
    $showTargetDtime =strtotime($_REQUEST['FLOP_DTSTR']);
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
define('SHOW_ELEMENTRANGE', 20); // 暫定値element20


$VIEW = array();
$contentsArr = array();

$contentsArr[] = '<br>';


////////////////////////////////
//
// リンクボタン「TRENDREPORT」
// ※新値足の生成で決まる$showTopDatetimeStrを利用したいのでココへ移動
//
///////////////////////////////


////////
// 各タブからの<a>タグPOSTのためのformタグ、ここから
////////
$contentsArr[] = '<script>function requestFlopLink(ftDtStr){document.getElementById("FLOP_DTSTR").value=ftDtStr;}</script>';
$contentsArr[] = '<form method=post name=TABMENU action=./indexBS.php><input type=hidden name=FLOP_DTSTR value="" id=FLOP_DTSTR />'; // ¶再描画の時のchecked用にラジオボタンの選択状態が欲しいので<a>タグでPOSTする


////////////////////////////////
//
// メインタブ1「新値足」コンテンツ
//
///////////////////////////////
$main1ContentsName = '新値足';
$manu1ContentsArr = array();

// ----------------------------------------
// ◆新値足の生成⇒FLOP_DTSTRの指定があった場合は「そこFLOP_DTSTR」を含む新値足をinit()する必要がある（単純に$currentDtimeでgetShinneRootDateTime()はダメ）
// ----------------------------------------
$rootDTimeStr = $myBSDao->getShinneRootDateTime(getLoggingDatetimeStr_fromDtime($showTargetDtime));
////echo '$rootDTimeStr['.getLoggingDatetimeStr($rootDTimeStr).']<br>';
$myShinneashi = new CreateShinneashi($myCmxLog, $myBSDao);
$myShinneashi->init(CoreBase::T_N225M, ($rootDTimeStr ?? BS_ORIGIN_DATE), getLoggingDatetimeStr_fromDtime($showTargetDtime));

// ========
// ======== 作成済みの新値足のうち、どこから（～$showTargetDtimeまで）を表示するかをSHOW_ELEMENTRANGEで決定する
// ========
$showTopDatetimeStr = $myShinneashi->getElement_dtime($showTargetDtime, SHOW_ELEMENTRANGE - 1)->date;
if ($showTopDatetimeStr > $myShinneashi->getFlopElement_dtime($showTargetDtime, 1)->date) {

    //// ==== 更足が続きすぎてflop1まで到達しない場合はSHOW_ELEMENTRANGEを超えてしまうけどflop1を採用（しないと、FlopLinkできなくなる）
    $showTopDatetimeStr = $myShinneashi->getFlopElement_dtime($showTargetDtime, 1)->date;
}


// ◆増田足も同時に表示するモード
$myMasudaashi = new CreateMasudaashi($myCmxLog, $myBSDao);
// ==== CreateMasudaashi.init()では、本当に欲しい日時$showTargetDtimeStrをinit()に伝えて、その中で適切な本数を取得するクエリを作って取得してもらう
$myMasudaashi->init(CoreBase::T_N225M, $showTopDatetimeStr, getLoggingDatetimeStr_fromDtime($showTargetDtime));
$myShinneashi->setMasudaashi($myMasudaashi);

// ======== 新値足表示
$manu1ContentsArr[] = '<table>';
$manu1ContentsArr = array_merge($manu1ContentsArr, $myShinneashi->html_getShow_dateTime($showTopDatetimeStr));
$manu1ContentsArr[] = '</table>';


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

// 2023.08.06 現在表示中のFLOP_DTSTRと同じ指定をした場合の検知用に追加（念願の表示さかのぼり対応）
$manu1ContentsArr[] = '<input type=hidden name=VIEW_FLOP_DTSTR value='.$showTargetDtime.'>';


////////////////////////////////
//
// メインタブ3「チャート」コンテンツ
//
///////////////////////////////
$main3ContentsName = 'チャート';
$manu3ContentsArr = array();
$chartArr = $myBSDao->getListTypeRecord("select date, close_value from N225M_CHART where date >= '".$showTopDatetimeStr."' and date <= '".getLoggingDatetimeStr_fromDtime($showTargetDtime)."'");
$manu3ContentsArr[] = '<table>';
$manu3ContentsArr = array_merge($manu3ContentsArr, html_getChartTable($chartArr, $myShinneashi, CoreBase::T_N225M));
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
$sql = "select date, id, msg from N225M_TRENDREPORT where date >= '".$showTopDatetimeStr."' order by date";
$dTimeColumn = '&dTimeColumn=date';
$contentsArr = array_merge(array('<p></p>'), html_getLinkButon('TRENDREPORT', './viewQueryBS.php?execQuery='.$sql.$dTimeColumn, 'target=_blank'), $contentsArr);




////////////////////////////////
//
// currentDisp情報
// ・建玉（ダミー正否）
// ・前回建玉、ゲイン/ロス
// ・上弦/下弦
// ・TJM_HOLD有無
///////////////////////////////
//$informationObj = $myBSDao->getCurrentDipInformation();
//$contentsArr = array_merge($contentsArr, html_getBsDispInfo($informationObj, CoreBase::T_N225M));





$contentsArr[] = '<br><HR align=left width=80% color=silver>';

$contentsArr[] = '<p></p>';




////////////////////////////////
//
// リンクボタン「シミュレーション」
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getLinkButon('シミュレーション', './indexBS.php', ''));


////////////////////////////////
//
// リンクボタン「管理画面」
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getLinkButon('管理画面', '../_secure/adminBS.php', ''));




// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'BillionStuff';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

?>