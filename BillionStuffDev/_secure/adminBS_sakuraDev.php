<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_bsEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');

include(APP_ROOT.'COMMON/Base/CoreBase.php');




define('EXECUTEQUERY_SCRIPT', './execQueryBS2022.php');
$VIEW = array();
$contentsArr = array();

$contentsArr[] = '<br>';




////////////////////////////////
//
// 日時指定新値足 コンテンツ
//
///////////////////////////////
$contentsArr[] = '<form name=fm2 action=../View/indexBS.php method=post>';
$contentsArr[] = '<input type=hidden name=TAB value=1>';
$contentsArr[] = '<table>';
$contentsArr[] = '<tr>';
$contentsArr[] = '<td>&nbsp;</td>';
$contentsArr[] = '<td><input type=text name=FLOP_DTSTR value='.date('YmdH00').'></td>';
$contentsArr[] = '<td align=right><input style="width: 160px" type=submit value=日時指定新値足表示></td>';
$contentsArr[] = '</tr>';
$contentsArr[] = '</table>';
$contentsArr[] = '</form>';
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// BS_ADMINテーブル コンテンツ
//
///////////////////////////////
$parameterArr = array(
    'cron 監視項目' => array('execQuery' => "select * from BS_ADMIN where meigara_cd = 'CR_N225M' and ( side like 'WV_%' or side like 'NYV_%' or side like 'LS%' or side like 'SARA_%' or side in ('SPT', 'RGT', 'LSA_TJM', 'LSB_TJM', 'LST_TTG', 'A', 'B', 'M_MAX') ) order by side, date", 'dTimeColumn' => 'date'),
    'cron 新値足root以外' => array('execQuery' => "select * from BS_ADMIN where meigara_cd = 'CR_N225M' order by side, date", 'dTimeColumn' => 'date'),
    'simu 新値足root以外' => array('execQuery' => "select * from BS_ADMIN where meigara_cd = 'SM_N225M' order by side, date", 'dTimeColumn' => 'date')
);

$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('BS_ADMIN', $parameterArr));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// TRENDREPORTテーブル コンテンツ
//
///////////////////////////////
$linkArr = array(
    'execQuery' => array('execQuery' => "select date, id, msg from N225M_TRENDREPORT order by date, seq, id desc", 'dTimeColumn' => 'date'),
);

$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('TRENDREPORT', $linkArr));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// シミュレーター コンテンツ
//
///////////////////////////////
$contentsArr[] = '<H1 class=submenu>シミュレータ N225M</H1>';
$contentsArr[] = '<form action=../CronJobs/SimulatorBS.php?action=simulationStart method=post>';
$contentsArr[] = '<input type=hidden name=param value=init>';

$contentsArr[] = '<BR>';
$contentsArr[] = '<table>';

$contentsArr[] = '<TR>';
$contentsArr[] = '<TD><a><font size=+1>RecentMonths</font><a></TD>';
$contentsArr[] = '<TD>';

$contentsArr[] = "<select style='width: 96px' name=recent>";
$contentsArr[] = "<option value=0>当月</option>";
$contentsArr[] = "<option value=1 selected>前月</option>";

for ($recent = 2; $recent <= SIMULATOR_NUM_OF_RECENT_BS; $recent++) {
$contentsArr[] = "<option value={$recent}>".date('Y - m', strtotime(date('Y-m-1')."-{$recent} month"))."</option>";
}

$contentsArr[] = '</select>';
$contentsArr[] = "&nbsp;<input style='width: 80px' type=submit value=Simulation>";
$contentsArr[] = '</TD>';
$contentsArr[] = '</TR>';

$contentsArr[] = '<TR>';
$contentsArr[] = '<TD colspan=2>';
$contentsArr[] = "<input type=checkbox name=batch><a><font size=-1>Simulation Batch</font><a>";
$contentsArr[] = '</TD>';
$contentsArr[] = '</TR>';

$contentsArr[] = '<TR>';
$contentsArr[] = '<TD colspan=2>';
$contentsArr[] = "<input type=checkbox name=debugLogging><a><font size=-1>ログ詳細出力（TRENDREPORT挿入）</font><a>";
$contentsArr[] = '</TD>';
$contentsArr[] = '</TR>';

/*
//
// 2023.07.10 LEGACY_COMPATIBLE廃止（上弦下弦における旧来の仕様再現であったため∵上弦下弦は廃止済み）
//
$contentsArr[] = '<TR>';
$contentsArr[] = '<TD colspan=2>';
$contentsArr[] = "<input type=checkbox name=legacyCompatible><a><font size=-1>LEGACY COMPATIBLE（旧仕様動作）</font><a>";
$contentsArr[] = '</TD>';
$contentsArr[] = '</TR>';
*/

$contentsArr[] = '</table>';

$contentsArr[] = '<BR>';

$linkArr = array(
    'execQuery' => array('execQuery' => "select date, id, msg from N225M_TRENDREPORT_SIMU order by date, seq, id desc", 'dTimeColumn' => 'date'),
);
$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('TRENDREPORT_SIMU', $linkArr));

$contentsArr[] = '<BR>';
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// N225M_CHARTテーブル コンテンツ
//
///////////////////////////////
$linkArr = array(
    'execQuery' => array('execQuery' => "select * from N225M_CHART order by date desc LIMIT 1000", 'dTimeColumn' => 'date')
);

$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('N225M_CHART', $linkArr));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// 新値足root コンテンツ
//
///////////////////////////////
$linkArr = array(
    'execQuery' => array('execQuery' => "select * from BS_ADMIN where meigara_cd = '".CoreBase::getSinneashiMeigaraCd(CoreBase::T_N225M)."' order by date", 'dTimeColumn' => 'date'),
);

$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('新値足root', $linkArr));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// ZIP圧縮 コンテンツ
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getSingleActionLinkMenu('zip_icon.png', '（../app/../LOG_SIMULATORLOG.log）', 'COMP_SIMULATORLOG', true));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// データパッチ コンテンツ
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getSingleActionLinkMenu('exec_icon.jpg', 'データパッチ（'.EXECUTEQUERY_SCRIPT.'）', 'ayaya', false));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// ロリポップのINVESTX_ADMINをコピー コンテンツ（CRONをコピー）
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getLinkMenu('process_icon.png', 'ロリポップADMIN（CRONをコピー）<br>（./syncroCopyBsLOLIPOP_ADMIN.php）', './syncroCopyBsLOLIPOP_ADMIN.php'));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';

////////////////////////////////
//
// さくらのINVESTX_ADMINをコピー コンテンツ（SIMUをコピー）
//
///////////////////////////////
//$contentsArr[] = '<table><tr><td class=nallow><A href=./syncroCopyBsSAKURA_ADMINsimu.php><img src=/html/_invextX.files/process_icon.png><font size=+1>さくらADMIN（SIMU⇒CRONコピー）（./syncroCopyBsSAKURA_ADMINsimu.php）</font></A></td></tr></table>';
//$contentsArr[] = '<br><HR align=left width=80% color=silver>';

////////////////////////////////
//
// さくらのINVESTX_ADMINをダンプ コンテンツ（SIMUをダンプ）
//
///////////////////////////////
//$contentsArr[] = '<table><tr><td class=nallow><A href=./syncroCopyBsSAKURA_ADMINsimuDump.php><img src=/html/_invextX.files/process_icon.png><font size=+1>さくらADMIN（SIMUをダンプ）（./syncroCopyBsSAKURA_ADMINsimuDump.php）</font></A></td></tr></table>';
//$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// Cron コンテンツ
//
///////////////////////////////



/*
  // 2023.07.08 WebCron廃止
  // 開発が終了した後の使途はない（運用開始後、数年にわたり使用に実績はなし）
  // FxStarの横展開⇒USD/AUD環境が分離した場合の挙動の決定が複雑

////////////////////////////////
//
// WebCron コンテンツ
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getLinkMenu('cron_icon.png', 'WebCron', '../CronJobs/WebCronTrigBS.php?ACTION='.APP_TYPE.'_WebCronTrig'));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';
*/





// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'BillionStuffAdmin';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

?>

