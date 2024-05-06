<?PHP

// ==== ==== ==== ====
// ==== ==== ==== ==== 環境定数【環境依存】
// ==== ==== ==== ====
include('../_fsEnv.cfg');


// ==== ==== ==== ====
// ==== ==== ==== ==== 必要なファイルをinclude
// ==== ==== ==== ====
include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');
include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');

include(APP_ROOT.'COMMON/Base/CoreBase.php');




define('EXECUTEQUERY_SCRIPT', './execQueryFS2022.php');
$VIEW = array();
$contentsArr = array();

$contentsArr[] = '<br>';




////////////////////////////////
//
// 日時指定新値足 コンテンツ
//
///////////////////////////////
$contentsArr[] = '<form name=fm2 action=../View/indexFS.php method=post>';
$contentsArr[] = '<input type=hidden name=TAB value=1>';
$contentsArr[] = '<table>';
$contentsArr[] = '<tr>';
$contentsArr[] = '<td>&nbsp;</td>';
$contentsArr[] = '<td><input type=text name=FLOP_DTSTR value='.date('YmdH00').'></td>';
$contentsArr[] = '<td align=right><input style="width: 160px" type=submit value=日時指定新値足表示></td>';
$contentsArr[] = '</tr>';
$contentsArr[] = '</table>';
$contentsArr[] = '</form>';




////////////////////////////////
//
// FS_ADMIN コンテンツ
//
///////////////////////////////
$contentsArr[] = '<H1 class=submenu>FS_ADMIN コンテンツ</H1>';

$linkArr = array(
    'cron USD監視項目のみ' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = 'CR_FS".CoreBase::getSymbolCd(CoreBase::T_USD5M)."' and (side in ('A', 'B', 'M_MAX', 'TJM_TIME', 'TJM_PT', 'COLRZONE') ) order by side, date", 'dTimeColumn' => 'date'),
    'cron USD新値足root以外すべて' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = 'CR_FSUSD' order by side, date", 'dTimeColumn' => 'date'),
    'simu USD新値足root以外すべて' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = 'SM_FSUSD' order by side, date", 'dTimeColumn' => 'date'),
    '新値足rootUSD' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = '".CoreBase::getSinneashiMeigaraCd(CoreBase::T_USD5M)."' order by date", 'dTimeColumn' => 'date')
);
$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('FS_ADMIN<br>USD', $linkArr));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';

$linkArr = array(
    'cron AUD監視項目のみ' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = 'CR_FS".CoreBase::getSymbolCd(CoreBase::T_AUD5M)."' and (side in ('A', 'B', 'M_MAX', 'TJM_TIME', 'TJM_PT', 'COLRZONE') ) order by side, date", 'dTimeColumn' => 'date'),
    'cron AUD新値足root以外すべて' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = 'CR_FSAUD' order by side, date", 'dTimeColumn' => 'date'),
    'simu AUD新値足root以外すべて' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = 'SM_FSAUD' order by side, date", 'dTimeColumn' => 'date'),
    '新値足rootAUD' => array('execQuery' => "select * from FS_ADMIN where meigara_cd = '".CoreBase::getSinneashiMeigaraCd(CoreBase::T_AUD5M)."' order by date", 'dTimeColumn' => 'date')
);
$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('FS_ADMIN<br>AUD', $linkArr));
$contentsArr[] = '<br>';




////////////////////////////////
//
// シミュレーター コンテンツ
//
///////////////////////////////
$contentsArr[] = '<H1 class=submenu>シミュレータ</H1>';
$contentsArr[] = '<form action=../CronJobs/SimulatorFS.php?action=simulationStart method=post>';
$contentsArr[] = '<input type=hidden name=param value=init>';

$contentsArr[] = '<BR>';
$contentsArr[] = '<table>';

$contentsArr[] = '<TR>';
$contentsArr[] = '<TD><a><font size=+1>RecentMonths</font><a></TD>';
$contentsArr[] = '<TD>';

$contentsArr[] = "<select style='width: 96px' name=recent>";
$contentsArr[] = "<option value=0>当月</option>";
$contentsArr[] = "<option value=1 selected>前月</option>";

for ($recent = 2; $recent <= SIMULATOR_NUM_OF_RECENT_FS; $recent++) {
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
    'execQuery' => array('execQuery' => "select date, id, msg from FS_TRENDREPORT_SIMU order by date, seq, id desc", 'dTimeColumn' => 'date'),
);
$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('TRENDREPORT_SIMU', $linkArr));

$contentsArr[] = '<BR>';
$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// TRENDREPORTテーブル コンテンツ
//
///////////////////////////////
$linkArr = array(
    'execQuery' => array('execQuery' => "select date, id, msg from FS_TRENDREPORT order by date, seq, id desc", 'dTimeColumn' => 'date'),
);
$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('TRENDREPORT', $linkArr));
$contentsArr[] = '<br>';




////////////////////////////////
//
// FS_CHARTテーブル コンテンツ
//
///////////////////////////////
$contentsArr[] = '<H1 class=submenu>FS_CHARTテーブル コンテンツ</H1>';
$linkArr = array(
    'AUD order by date desc LIMIT 1000' => array('execQuery' => "select * from FS_CHART where symbolCd= '".CoreBase::getSymbolCd(CoreBase::T_AUD5M)."' order by date desc LIMIT 1000", 'dTimeColumn' => 'date'),
    'USD order by date desc LIMIT 1000' => array('execQuery' => "select * from FS_CHART where symbolCd= '".CoreBase::getSymbolCd(CoreBase::T_USD5M)."' order by date desc LIMIT 1000", 'dTimeColumn' => 'date')
);
$contentsArr = array_merge($contentsArr, html_getSelectTableLinkMenu('FS_CHART<br>', $linkArr));
$contentsArr[] = '<br>';




$contentsArr[] = '<H1 class=submenu>メンテナンス</H1>';


////////////////////////////////
//
// ZIP圧縮 コンテンツ
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getSingleActionLinkMenu('zip_icon.png', '（../app/../LOG_SIMULATORLOG.log）', 'ayaya', true));
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
$contentsArr = array_merge($contentsArr, html_getLinkMenu('process_icon.png', 'ロリポップADMIN（CRONをコピー）<br>（./syncroCopyFsLOLIPOP_ADMIN.php）', './syncroCopyFsLOLIPOP_ADMIN.php'));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';


////////////////////////////////
//
// さくらのINVESTX_ADMINをコピー コンテンツ（SIMUをコピー）
//
///////////////////////////////
//$contentsArr[] = '<table><tr><td class=nallow><A href=./syncroCopyFsSAKURA_ADMINsimu.php><img src=/html/_invextX.files/process_icon.png><font size=+1>さくらADMIN（SIMU⇒CRONコピー）（./syncroCopyFsSAKURA_ADMINsimu.php）</font></A></td></tr></table>';
//$contentsArr[] = '<br><HR align=left width=80% color=silver>';

////////////////////////////////
//
// さくらのINVESTX_ADMINをダンプ コンテンツ（SIMUをダンプ）
//
///////////////////////////////
//$contentsArr[] = '<table><tr><td class=nallow><A href=./syncroCopyFsSAKURA_ADMINsimuDump.php><img src=/html/_invextX.files/process_icon.png><font size=+1>さくらADMIN（SIMUをダンプ）（./syncroCopyFsSAKURA_ADMINsimuDump.php）</font></A></td></tr></table>';
//$contentsArr[] = '<br><HR align=left width=80% color=silver>';




////////////////////////////////
//
// Cron コンテンツ
//
///////////////////////////////



/*
  // 2023.07.08 WebCron廃止
  // 開発が終了した後の使途はない（運用開始後、数年にわたり使用に実績はなし）
  // USD/AUD環境が分離した場合の挙動の決定が複雑

////////////////////////////////
//
// WebCron コンテンツ
//
///////////////////////////////
$contentsArr = array_merge($contentsArr, html_getLinkMenu('cron_icon.png', 'WebCron', '../CronJobs/WebCronTrigFS.php?ACTION='.APP_TYPE.'_WebCronTrig'));
$contentsArr[] = '<br><HR align=left width=80% color=silver>';
*/





// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
$VIEW['TITLE'] = 'FxStarAdmin';
$VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
$VIEW['CONTENTS'] = $contentsArr;
include(HTML_ROOT.'COMMON/View/view.html');
// // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

?>

