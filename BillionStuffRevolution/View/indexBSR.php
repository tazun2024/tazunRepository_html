<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_bsrEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');
include(APP_ROOT.'COMMON/Log/CmxLog.php');
include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
include(APP_ROOT.'COMMON/Util/InvestxNewUtil.php');
//include(APP_ROOT.APP_TYPE.'/Util/BSRUtil.php'); // いったん不要とする

// ==== ==== ==== ==== 2023.07.06 FxStarの横展開⇒SimulationFSから読込む「_FSSimulationPARAM.php」内で使用されることから、移設しました
include(APP_ROOT.'COMMON/Base/CoreBase.php');

include(APP_ROOT.'COMMON/Core/CreateShinneashi.php');
include(APP_ROOT.'COMMON/Core/CreateMasudaashi.php');

include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');

include(APP_ROOT.APP_TYPE.'/_BSRCONF.php');








//////// ログ生成
$myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/View/Logs/'.APP_TYPE.date('Ymd_H00').'.view.log');
$myCmxLog->setLoggingLevel(CmxLog::T_LOGGING_DEBUG);

//////// DAO生成
//include(APP_ROOT.APP_TYPE.'/Business/BSR_BusinessBase.php'); // いったん不要とする
include(APP_ROOT.'COMMON/Base/AdminBase.php');
include(APP_ROOT.APP_TYPE.'/Dao/BSRDao.php');
$myBSRDao = new BSRDao($myCmxLog, 'view_dummyValue');




//////// BSR_ORIGIN_DATEは銘柄ごとに異なる
// 3936 (株)グローバルウェイ
// 20191209 20210914
// 20210915 20211029 分割：1株->5株
// 20211101 20211201 分割：1株->3株
// 20211202 20991231 分割：1株->2株



//////// 表示する銘柄コード
if (($_REQUEST['MEIGARA_CD'] ?? '') == '') {

    //// 日時の指定が特にないのでフツーに現在の状況を見る時
    $meigaraCd = '1001'; // for debug

} else {

    //// admin画面で過去の日付を指定してきた場合
    $meigaraCd = $_REQUEST['MEIGARA_CD'];
}

//////// 表示する日時範囲を決める
if (($_REQUEST['FLOP_DTSTR'] ?? '') == '') {

    //// 日時の指定が特にないのでフツーに現在の状況を見る時
    $showTargetDtime = strtotime(date('Y-m-d'));

} else {

    //// admin画面で過去の日付を指定してきた場合
    $showTargetDtime =strtotime($_REQUEST['FLOP_DTSTR']);
}

//////// 念願の表示さかのぼり対応
if ($showTargetDtime !== ''
    and $showTargetDtime == ($_REQUEST['VIEW_FLOP_DTSTR'] ?? '')) {

        //// 現在表示中のFLOP_DTSTRと同じ指定をした場合
        echo 'さかのぼりきぼん \'+15 day\'<br>';
        $showTargetDtime = strtotime('+15 day', $showTargetDtime);
}


//////// 2024.01.26 FX通貨の表示不具合対応
switch($meigaraCd) {

    case '1003':
        header('Location:../../'.'FxStar'.ENV_NAME.'/View/indexFS.php?SYMBOL_CODE=USD5M');
        exit();
        break;

    case '1052':
        header('Location:../../'.'FxStar'.ENV_NAME.'/View/indexFS.php?SYMBOL_CODE=AUD5M');
        exit();
        break;
default:
        break;

}  // -- end of switch()


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
$contentsArr[] = '<form method=post name=TABMENU action=./indexBSR.php><input type=hidden name=FLOP_DTSTR value="" id=FLOP_DTSTR />'; // ¶再描画の時のchecked用にラジオボタンの選択状態が欲しいので<a>タグでPOSTする


////////////////////////////////
//
// メインタブ1「新値足」コンテンツ
//
///////////////////////////////
$main1ContentsName = '新値足';
$manu1ContentsArr = array();

$contentsArr[] = "<table width=300px><tr><td>";
include(HTML_ROOT.'_cfg/masudaashiSearch.cfg');
$baseURL = 'https://cmx.boy.jp/html/BillionStuffRevolutionStg/View/indexBSR.php?MEIGARA_CD=';
$maxCount = 10;
$linkMymaStr = '';
foreach ($historyArr as $entry) {

    // 頁末でMY_MAはのリンク表示用に当該銘柄の文言を保持しておく
    if (left($entry,4) == $meigaraCd) {
        $linkMymaStr = $entry;
    }

    $contentsArr[] = "<a href='".$baseURL.left($entry, 4)."'>".$entry."</a>&nbsp";
    if ($maxCount -- <= 1) break;
}
$contentsArr[] = "</td></tr></table>";

/*
// ----------------------------------------
// ◆新値足の生成⇒FLOP_DTSTRの指定があった場合は「そこFLOP_DTSTR」を含む新値足をinit()する必要がある（単純に$currentDtimeでgetShinneRootDateTime()はダメ）
// ----------------------------------------
*/
//$showTargetDtime = strtotime('2021-12-10'); // for debug これで新値足・増田足・レートの最終日が決まっている
$rootDTimeStr = $myBSRDao->getMeigaraRootDateTime($meigaraCd);
echo "<div style='clear:both; color:lightgray;'>".$meigaraCd." \$rootDTimeStr[".getLoggingDatetimeStr($rootDTimeStr)."]</div>";
$myShinneashi = new CreateShinneashi($myCmxLog, $myBSRDao);
$myShinneashi->init($meigaraCd, $rootDTimeStr, getLoggingDatetimeStr_fromDtime($showTargetDtime));

// ========
// ======== 作成済みの新値足のうち、どこから（～$showTargetDtimeまで）を表示するかをSHOW_ELEMENTRANGEで決定する
// ========
$showTopDatetimeStr = $myShinneashi->getElement_dtime($showTargetDtime, SHOW_ELEMENTRANGE - 1)->date;
if ($showTopDatetimeStr > $myShinneashi->getFlopElement_dtime($showTargetDtime, 1)->date) {

    //// ==== 更足が続きすぎてflop1まで到達しない場合はSHOW_ELEMENTRANGEを超えてしまうけどflop1を採用（しないと、FlopLinkできなくなる）
    $showTopDatetimeStr = $myShinneashi->getFlopElement_dtime($showTargetDtime, 1)->date;
}
//$showTopDatetimeStr = '2021-09-14'; // for debug


// ◆増田足も同時に表示するモード
$myMasudaashi = new CreateMasudaashi($myCmxLog, $myBSRDao);
// ==== CreateMasudaashi.init()では、本当に欲しい日時$showTargetDtimeStrをinit()に伝えて、その中で適切な本数を取得するクエリを作って取得してもらう
$myMasudaashi->init($meigaraCd, $rootDTimeStr, getLoggingDatetimeStr_fromDtime($showTargetDtime));
//$myMasudaashi->init($meigaraCd, strtotime('2019-12-09'), getLoggingDatetimeStr_fromDtime($showTargetDtime)); // for debug
$myShinneashi->setMasudaashi($myMasudaashi);

// ======== 新値足表示
$manu1ContentsArr[] = '<table>';
$manu1ContentsArr = array_merge($manu1ContentsArr, $myShinneashi->html_getShow_dateTime($showTopDatetimeStr)); // 新値足の表示は$showTopDatetimeStrから保持データの最後まで

// ======== 現在値が新値足最終エレメントと異なる場合、タブと同じレイアウトで現在値を出力
$dateValue = $myShinneashi->getMasudaashi()->getLatestElement()->date;
if (strtotime($myShinneashi->getLatestElement()->date) < strtotime($dateValue)) {
    $manu1ContentsArr[] = '<tr>';
    $manu1ContentsArr[] = '<td colspan=2>';
    $manu1ContentsArr[] = '</td>';
    $manu1ContentsArr[] = '</tr>';
    $manu1ContentsArr[] = '<tr>';
    $manu1ContentsArr[] = '<td>';
    $manu1ContentsArr[] = getDatetimeColumnValue($myShinneashi->getMasudaashi()->getLatestElement()->date, $meigaraCd);
    $manu1ContentsArr[] = '</td>';
    $manu1ContentsArr[] = '<td>';
    $priceValue = $myShinneashi->getMasudaashi()->getLatestElement()->closeValue / 100;
    $manu1ContentsArr[] = getPriceValueStr($priceValue, $priceValue);
    $manu1ContentsArr[] = '</td>';
    $manu1ContentsArr[] = '</tr>';
}

$manu1ContentsArr[] = '</table>';


////////////////////////////////
//
// メインタブ2「増田足」コンテンツ
//
///////////////////////////////
$main2ContentsName = '増田足';
$manu2ContentsArr = array();

// ======== 増田足表示
$manu2ContentsArr[] = '<table>';
$manu2ContentsArr = array_merge($manu2ContentsArr, $myMasudaashi->html_getShow_dateTime($showTopDatetimeStr)); // 増田足の表示は($showTopDatetimeStr+1)から保持データの最後まで
//$manu2ContentsArr = array_merge($manu2ContentsArr, $myMasudaashi->html_getShow_dateTime(date('Y-m-d', strtotime('-1 day', strtotime($showTopDatetimeStr))))); // 増田足の表示は($showTopDatetimeStr+1)から保持データの最後まで
$manu2ContentsArr[] = '</table>';

// 2023.08.06 現在表示中のFLOP_DTSTRと同じ指定をした場合の検知用に追加（念願の表示さかのぼり対応）
$manu1ContentsArr[] = '<input type=hidden name=VIEW_FLOP_DTSTR value='.$showTargetDtime.'>';


////////////////////////////////
//
// メインタブ3「レート」コンテンツ
//
///////////////////////////////
$main3ContentsName = 'レート';
$manu3ContentsArr = array();
//////// BSR_ORIGIN_DATEは銘柄ごとに異なる
// 3936 (株)グローバルウェイ
// 20191209 20210914
// 20210915 20211029 分割：1株->5株
// 20211101 20211201 分割：1株->3株
// 20211202 20991231 分割：1株->2株
$sql = "select date, close_value from DAILY_CHART where meigara_cd = '".$meigaraCd."' and date >= '".$showTopDatetimeStr."' and date <= '".getLoggingDatetimeStr_fromDtime($showTargetDtime)."'";
$chartArr = $myBSRDao->getListTypeRecord($sql); // 表示用の値なので描画用途の調整値ではない
$manu3ContentsArr[] = '<table>';
$manu3ContentsArr = array_merge($manu3ContentsArr, html_getChartTable($chartArr, $myShinneashi, $meigaraCd));
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

// ======== MY_MAリンク
$contentsArr[] = '<table>';
$contentsArr[] = '<tr>';
$contentsArr[] = "<td><a href='https://cmx.boy.jp/html/domain/masudaashi/search.php?TARGET_CD=".$meigaraCd."'>MY_MA ".$linkMymaStr."</a></td>";
$contentsArr[] = '</tr>';
$contentsArr[] = '</table>';


////////////////////////////////
//
// リンクボタン「TRENDREPORT」
// ※新値足の生成で決まる$showTopDatetimeStrを利用したいのでココへ移動
//
///////////////////////////////


////////////////////////////////
//
// currentDisp情報
// ・建玉（ダミー正否）
// ・前回建玉、ゲイン/ロス
// ・上弦/下弦
// ・TJM_HOLD有無
///////////////////////////////





$contentsArr[] = '<br><HR align=left width=80% color=silver>';

$contentsArr[] = '<p></p>';




////////////////////////////////
//
// リンクボタン「シミュレーション」
//
///////////////////////////////


////////////////////////////////
//
// リンクボタン「管理画面」
//
///////////////////////////////




// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'BillionStuffRevolution';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

?>