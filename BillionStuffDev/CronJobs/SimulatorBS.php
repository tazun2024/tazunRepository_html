<?php

    // シャットダウンシーケンスを設定
    register_shutdown_function('my_shutdown_handler');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    include('../_bsEnv.cfg');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 定数設定
    // ==== ==== ==== ====
    //define('DOMAIN_ROOT', HTML_ROOT.'domain/');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Cfg/InvestxCfg.php');
    include(APP_ROOT.'COMMON/Log/CmxLog.php');
    include(APP_ROOT.'COMMON/Mail/CmxMailSender.php');
    include(APP_ROOT.'COMMON/Mail/_MAILconf.php');
    include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
    include(APP_ROOT.'COMMON/Util/InvestxNewUtil.php');
    include(APP_ROOT.APP_TYPE.'/Util/BSUtil.php');
    include(HTML_ROOT.'COMMON/Util/investX_viewUtil.php');

    // ==== ==== ==== ==== 2023.07.06 FxStarの横展開⇒SimulationFSから読込む「_FSSimulationPARAM.php」内で使用されることから、移設しました
    include(APP_ROOT.'COMMON/Base/CoreBase.php');

    // ======== 2022.09.14 シミュレーション時のステータス表示エリア用に定数化
    define('STATUSBAR_OPEN', '<H1 class=submenu>');
    define('STATUSBAR_CLOSE', '</H1>');
    define('STATUSBAR_SPAN', '<span style="margin-right:10px;"></span>');

    // ======== 2022.10.22 新値足のセッション保持は容量を食うのでzip化して保持することにした
    define('SHINNEASHI_WORKFILENAME', '__shinneashiTemporary');


    /**
     * 挙動を決めるURLパラメタを取得
     *
     * シミュレーション動作を起動～継続～終了までを司ります。
     * URLパラメータ「action」により挙動の分岐がなされます。
     *
     */
    $action = $_REQUEST['action'];

    switch ($action) {

        case 'simulationStart': // ■■シミュレートループ開始時の変数＆admin初期化

            $prefixKey = date('YmdHis');

            /**
             * ログ生成
             * @var CmxLog $myCmxLog
             */
            $currentDir = APP_TYPE.'_'.BSUtil::T_CMX_SIMULATION.$prefixKey;
            mkdir(APP_ROOT.APP_TYPE.'/CronJobs/Logs/'.$currentDir);
            $myCmxLog = new CmxLog(APP_ROOT.APP_TYPE.'/CronJobs/Logs/'.$currentDir.'/'.APP_TYPE.'_SimulationStart.log');
            $myCmxLog->inf('--------------------------------');
            $myCmxLog->inf('>>execute SimulatorBS [simulationStart]');

            /**
             * ■ログ出力■
             * 今回シミュレーションのプレフィックスkey設定
             *
             * @var Ambiguous $prefixKey
             */
          //$prefixKey = date('YmdHis');
            $myCmxLog->inf('prefixKey ['.$prefixKey.']');
            $myCmxLog->setPrefixKey($prefixKey);

            //////// ログ詳細出力がチェックされればCmxLog::T_LOGGING_DEBUG（ログレベル：詳細）
            $myCmxLog->setLoggingLevel(($_REQUEST['debugLogging'] ?? false) ? CmxLog::T_LOGGING_DEBUG : CmxLog::T_LOGGING_NORMAL);


            /**
             * $_REQUEST['action'] = simulationStart
             *
             *   シミュレーション開始時の呼び出し。
             *   合わせて以下のパラメタが連携されてきます。
             *   $_REQUEST['param'] = init
             *   $_REQUEST['recent'] = シミュレーション対象 当月=0, 前月=1, 前々月=2 …
             *
             *
             *
             *
             *   現行でセッションに保持していた$_SESSION[ENV_NAME.'PARAM']は不要
             *   ∵シミュレーション時に入力設定するパラメタは廃止した
             *
             */


            /**
             * recentと現在日時からシミュレーションレポート開始の年月を設定する
             *
             * ¶シミュレーション対象 当月=0, 前月=1, 前々月=2 …
             */
            $recent = $_REQUEST['recent'];
            $targetDTime = _backToMonthsBS(strtotime(date('Y-m-01')), $recent); // ¶_backToMonths()は指定の月初営業日正午12時が取得できる


            /**
             * ※ｼﾐｭﾚｰｼｮﾝ経過に必要な値を設定
             * $args->completeFlag ※初期値false
             * $args->displayEnable ※初期値false
             * $args->reportList[] ※初期値「....preparing」（ｼﾐｭﾚｰﾄ中の途中経過）
             * $args->startYMD ※$recentから設定 ※2022.06.19 startYMD廃止
             * $args->breakDateTime ※$args->startYMから設定
             * $args->finishDateTime ※$batchExecuteFlagと$args->breakDateTime
             * $args->currentDateTime ※（助走も含めた初期値）
             * 2022.10.17 廃止 $args->reportYm ※$recentから設定（simulation_done()内で助走終了判定のみで使用）
             * $args->startDTime ※$recentから設定
             * 2022.09.14 廃止 $args->reportTitleHtmltext ※初期値「<H1>Y/n</H1>」（ｼﾐｭﾚｰﾄ中の年月見出し行）
             * $args->dispSum ※未使用になった
             * $args->maxLoss ※未使用になった
             * $args->simulationDetail ※初期値array()
             * $args->dailyDetail ※初期値array()
             */
            $args = getArgs($targetDTime, $_REQUEST['batch'] ?? false);


            /**
             * シミュレーションパラメータ
             * （使用機会の減少から廃止見込み）
             *
             *  AYAYA, noriPなど
             *
             */
            $simulationParam = new stdClass;
            include(HTML_ROOT.APP_TYPE.'/CronJobs/_BSsimulationPARAM.php');

            //////// LEGACY_COMPATIBLE（旧仕様動作）がチェックされれば_BSCONFの設定が上書きされる
            //$simulationParam->BS_LEGACY_COMPATIBLE = ($_REQUEST['legacyCompatible'] ?? false);


            /**
             * シミュレーションパラメータをセッションに保持する
             *
             */
            $_SESSION[APP_TYPE.'PARM']  = serialize($simulationParam);

            /**
             * 助走日数を考慮したシミュレーションdTimeStringの初期値を設定する
             *
             * _BSsimulationPARAM.php内に定数を置いてあります
             *
             */
            $theDTime = _backToDaysBS($targetDTime, BS_REREOACTIVE_DAYS);
            $tempSessionInfo = BSUtil::getBSOnsessionInfo($theDTime); // ¶_backToDaysBS()内部では正午12時を基準に処理するので必ずデイタイムセッションのある日が得られる
            $args->currentDateTime = $tempSessionInfo->START;


            $myCmxLog->resetFileName(APP_TYPE.'_'.BSUtil::T_CMX_SIMULATION.$prefixKey.'_'.date('Ym', $targetDTime).'.log');

            //// ログ固有情報はセッションに保持して次回リクエストに引き継ぐ
            $args->prefixKey   = $prefixKey;
            $args->loggingLevel = $myCmxLog->getLoggingLevel();
            $args->logFilePath = $myCmxLog->getLogFilePath();


            $args->breakDoneForce = false;

            /**
             * シミュレーション変数一式をセッションに保持する
             *
             */
            $_SESSION[APP_TYPE.'ARGS']  = serialize($args);


            /**
             * シミュレーション開始時の建玉とかadminは放置で良いとは言ったもののnotice連発はイヤなので初期化いろいろ
             *
             */
            include(APP_ROOT.APP_TYPE.'/CronJobs/ExecBS.php'); // ¶ExecBSのインスタンスが取れれば何でもいい∵ExecBS->simulation_init()したい
            include(APP_ROOT.APP_TYPE.'/Obj/BS_Admin.php');    // ¶中でBSAdminのstatic値を使いたい
            include(APP_ROOT.APP_TYPE.'/Business/BS_BusinessBase.php'); // 2022.09.03 BusinessBaseはAdminがもっている大量のコンスタントを保持するクラスになった
            include(APP_ROOT.APP_TYPE.'/_BSCONF.php'); // 2023.07.03 USD/AUD環境分離対応
            (new ExecBS((new BSUtil($myCmxLog, BSUtil::T_CMX_SIMULATION))->getTickDone(INVESTX_NOT_APPLICABLE_NUMBER)))->simulation_init();


            $myCmxLog->inf('->currentDateTime['.getLoggingDatetimeStr_fromDtime($args->currentDateTime).']');
            $myCmxLog->inf('->breakDateTime['.getLoggingDatetimeStr_fromDtime($args->breakDateTime).']');
            $myCmxLog->inf('->finishDateTime['.getLoggingDatetimeStr_fromDtime($args->finishDateTime).']');




            $VIEW = array();
            $contentsArr = array();

            ////////////////////////////////
            //
            // 表示内容の設定（シミュレーションパラメータ）
            //
            ///////////////////////////////
            $contentsArr[] = '<br>';
            $paramObjArr = json_decode(json_encode($simulationParam), true);
            if (count($paramObjArr) > 0) {

                $contentsArr[] = '<table>';

                foreach ($paramObjArr as $key => $value) {

                    $contentsArr[] = '<tr><td class=simuParamKey>'.$key.'</td><td class=simuParamValue>'.$value.'</td></tr>';
                }

                $contentsArr[] = '</table>';
            }

            ////////////////////////////////
            //
            // リンクボタン「SIMU_TRENDREPORT」
            //
            ///////////////////////////////
            $sql = "select date, id, msg from N225M_TRENDREPORT_SIMU order by date, seq, id desc";
            $dTimeColumn = '&dTimeColumn=date';
            $contentsArr = array_merge($contentsArr, html_getLinkButon('TRENDREPORT', '../View/viewQueryBS.php?execQuery='.$sql.$dTimeColumn, 'target=_blank'));


            ////////////////////////////////
            //
            // 現在シミュレーション処理中の年月表示
            //
            ///////////////////////////////
            $contentsArr[] = STATUSBAR_OPEN.date('Y/n', $args->startDtime).STATUSBAR_CLOSE;


            ////////////////////////////////
            //
            // ＞＞simulationNext 自動リロードスクリプト
            //
            ///////////////////////////////
            //////// ＞＞simulationNext 自動リロードスクリプト
            $contentsArr[] = html_getAutoLinkScript('../CronJobs/SimulatorBS.php?action=simulationNext');




            $message = date('Y/m/d H:i:s').' --------';
            error_log($message."\n", 3, __DIR__.'/SimulationBatchOut'.date('Ymd', strtotime($myCmxLog->getPrefixKey())).'.txt');


            /**
             * シミュレーション経過を逐一表示する内容
             *
             * [0]は連想配列[totalValueHtmltext] 当月の合計金額（とmaxloss）                助走中は空値
             * [0]は連想配列[dayLinkHtmltext]    各日付ごとの詳細を表示するページへのリンク 助走中は固定文字「....preparing」
             * ※現行踏襲もreportListが何故配列になっているのかは不明
             */
            $contentsArr[] = $args->reportList[0]['dayLinkHtmltext'];
            $contentsArr[] = '<br>';
            $contentsArr[] = '<br>';


            ////////////////////////////////
            //
            // セッションに残る新値足の残骸をクリア（Warning: mysqli::close(): Couldn't fetch mysqli が表示されると見苦しい）
            //
            ///////////////////////////////
            $_SESSION[APP_TYPE.'SHINNEASHI'] = null;




            // // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
            $VIEW['TITLE'] = "case 'simulationStart'";
            $VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
            $VIEW['CONTENTS'] = $contentsArr;
            include(HTML_ROOT.'COMMON/View/view.html');
            // // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

            break;

        case 'simulationNext':  // ■■日々のシミュレートループ

            /**
             * シミュレーション変数一式をセッションから取得する
             *
             *
             * $args $_SESSION[APP_TYPE.'ARGS']<br>
             * $args->dailyDetail※一日内の全取引明細：引数→（利用・更新）→返却（セッションに保持）<br>
             * $args->simulationDetail※YYYYMMDDをキーにしたdailyDetailの連想配列：引数→（更新）→返却（セッションに保持）<br>
             * $args->startYMD※この階層でのみ使用（simulation_done()内では未使用）<br> ※2022.06.19 startYMD廃止
             * 2022.10.17 廃止 $args->reportYm<br>
             * $args->currentDateTime※引数→（利用・更新）→返却（セッションに保持）<br>
             * $args->breakDateTime※引数→（利用）→そのまま返却<br>
             * $args->finishDateTime※この階層では未使用（セッション保持の残留）<br>
             *
             * $simulationParam $_SESSION[APP_TYPE.'PARM']<br>
             *
             * $shinneashi $_SESSION[APP_TYPE.'SHINNEASHI']<br>
             */
            $args = unserialize(@$_SESSION[APP_TYPE.'ARGS']);

            //////// ログ固有情報を前回リクエストから引き継いでインスタンス生成
            $myCmxLog = new CmxLog($args->logFilePath);
            $myCmxLog->setPrefixKey($args->prefixKey);
            $myCmxLog->setLoggingLevel($args->loggingLevel);

            $myCmxLog->inf('>>execute SimulatorBS [simulationNext]');


            /**
             * シミュレーションパラメタ一式をセッションから取得する
             *
             * AYAYA, noriPなど
             *
             * 2023.01.03 BS_LEGACY_COMPATIBLE 実装
             *
             */
            $simulationParam = unserialize(@$_SESSION[APP_TYPE.'PARM']);


            /**
             * 新値足をセッションから取得する
             *
             */
            include(APP_ROOT.APP_TYPE.'/CronJobs/ExecBS.php'); // ¶先にExecBSをincludeしていないと __PHP_Incomplete_Classにされてしまう

            if (!empty($_SESSION[APP_TYPE.'SHINNEASHI'])) {

                $serializedZipContents = $_SESSION[APP_TYPE.'SHINNEASHI'];

                try {

                    $workFile = dirname($myCmxLog->getLogFilePath()).'/'.SHINNEASHI_WORKFILENAME;

                    file_put_contents($workFile.'.zip', $serializedZipContents);

                    //////// 一時ファイルに保存してunzip化したファイルを取得する
                    try {
                        $zip = new ZipArchive();
                        if ($zip->open($workFile.'.zip') === true) {
                            $zip->extractTo($workFile.'.unzip');
                            $zip->close();
                            $serializeContentsObj = file_get_contents($workFile.'.unzip/'.basename($workFile));
                            unlink($workFile.'.zip');
                            unlink($workFile.'.unzip/'.basename($workFile));
                            rmdir($workFile.'.unzip');
                        } else {
                            throw new Exception('<新値足保持の解凍失敗>');
                        }
                    } catch (Throwable $th) {
                        throw new Exception('新値足の解凍に失敗しました。'.$th->getMessage());
                    }

                } catch (Throwable $e) {

                    $myCmxLog->inf('新値足をセッションから復元できませんでした。'.$e->getMessage());
                    $serializeContentsObj = null; // ¶セッションから取得できなかった場合は明示的に nullをセットしておく
                }

            } else {

                $myCmxLog->inf('新値足をセッションから取得できません。');
                $serializeContentsObj = null; // ¶セッションから取得できなかった場合は明示的に nullをセットしておく
            }


            $myCmxLog->inf('->currentDateTime['.getLoggingDatetimeStr_fromDtime($args->currentDateTime).']');
            $myCmxLog->inf('->breakDateTime['.getLoggingDatetimeStr_fromDtime($args->breakDateTime).']');
            $myCmxLog->inf('->finishDateTime['.getLoggingDatetimeStr_fromDtime($args->finishDateTime).']');


            $VIEW = array();
            $contentsArr = array();


            $contentsArr[] = '<br>';

            /**
             * 表示内容の設定（シミュレーションパラメータ）
             *
             */
            $paramObjArr = json_decode(json_encode($simulationParam), true);
            if (count($paramObjArr) > 0) {

                $contentsArr[] = '<table>';

                foreach ($paramObjArr as $key => $value) {

                    $contentsArr[] = '<tr><td class=simuParamKey>'.$key.'</td><td class=simuParamValue>'.$value.'</td></tr>';
                }

                $contentsArr[] = '</table>';
            }


            ////////////////////////////////
            //
            // リンクボタン「SIMU_TRENDREPORT」
            //
            ///////////////////////////////
            $sql = "select date, id, msg from N225M_TRENDREPORT_SIMU order by date, seq, id desc";
            $dTimeColumn = '&dTimeColumn=date';
            $contentsArr = array_merge($contentsArr, html_getLinkButon('TRENDREPORT', '../View/viewQueryBS.php?execQuery='.$sql.$dTimeColumn, 'target=_blank'));


            // ======== ExecBSのインスタンス生成に必要なメンバだけとりあえずセットする
            $myBSUtil = new BSUtil($myCmxLog, BSUtil::T_CMX_SIMULATION);
            $myTickDone = $myBSUtil->getTickDone($args->currentDateTime);

            $myExecBS = new ExecBS($myTickDone);

            $simulationArgs = new stdClass;
            $simulationArgs->simulationParam    = $simulationParam;
            $simulationArgs->args               = $args;
            $simulationArgs->shinneashiContents = $serializeContentsObj;

            /**
             *
             * @var unknown $simulationResult
             * ->dispSum
             * ->maxLoss
             */
            include(APP_ROOT.APP_TYPE.'/_BSCONF.php'); //  FxStarの横展開⇒2023.07.03 USD/AUD環境分離対応
            $simulationArgs->N225M_PARAM = $N225M_PARAM; //  FxStarの横展開⇒2023.07.03 USD/AUD環境分離対応
            $simulationResult = $myExecBS->simulation_done($myBSUtil, $simulationArgs);

            /**
             * さて、finishなのか、breakなのか、はたまた途中なのか
             */
            /*
            // 2023.07.18 対応
            // FxStarの横展開⇒次のTickDoneが5:55/6:55に設定されて戻ってきた場合ExecFS側ではブレイク処理対象でないのに、こちらでブレイクしてしまうバグ
            if ( $args->currentDateTime >= $args->breakDateTime
                or $args->breakDoneForce ) {
            */
            if ( $args->currentDateTime >= $args->breakDateTime
              or $args->breakDoneForce ) {

                ////////
                //////// ブレイク処理が必要な場合の処理
                ////////

                /**
                 * SimulationBatchOutへの出力
                 *
                 */
                $message = date('Y/m/d H:i:s').' '.date('Y-m', $args->startDtime).' '.$simulationResult->args->dispSum.' '.$simulationResult->args->maxLoss;
                error_log($message."\n", 3, __DIR__.'/SimulationBatchOut'.date('Ymd', strtotime($myCmxLog->getPrefixKey())).'.txt');


                /**
                 * 引き継ぐ出力情報を保持
                 * ・プレフィックスkey
                 * ・ログレベル
                 *
                 */
                $prefixKey = $myCmxLog->getPrefixKey();
                $loggingLevel = $myCmxLog->getLoggingLevel();

                //////// 月々のログはブレイク時に圧縮処理
                /**
                 * ■終了月のログを圧縮■
                 *
                 */
                $order = new stdClass;
                $order->TARGET_PATH = dirname($myCmxLog->getLogFilePath()).'/'; // 対象root
                $order->TARGET_FILE = basename($myCmxLog->getLogFilePath());    // 単ファイル指定
                $order->DEST_FILEPATH = $myCmxLog->getLogFilePath().'.zip';     // 出力ファイル名
                //unset($myCmxLog);   新しいログに古いログのBS_ReporterメンバのCmxMailSender__destruct()処理が出力されると嫌なので破棄、、明示的な破棄が上手くいかない
                //unset($myBSUtil);   新しいログに古いログのBS_ReporterメンバのCmxMailSender__destruct()処理が出力されると嫌なので破棄、、明示的な破棄が上手くいかない
                //unset($myTickDone); 新しいログに古いログのBS_ReporterメンバのCmxMailSender__destruct()処理が出力されると嫌なので破棄、、明示的な破棄が上手くいかない
                //unset($myExecBS);   新しいログに古いログのBS_ReporterメンバのCmxMailSender__destruct()処理が出力されると嫌なので破棄、、明示的な破棄が上手くいかない

                //$myCmxLog->inf('max_execution_time 変更前['.ini_get('max_execution_time').']');
                //ini_set('max_execution_time', 0); // ----------------  0：タイムアウトをしないに設定(処理が終わるまで待ち続ける) ※2022-06実績 136.8MB 3分22秒
                //$myCmxLog->inf('max_execution_time 変更後['.ini_get('max_execution_time').']');
                compZip($order);
                unlink($order->TARGET_PATH.$order->TARGET_FILE);

                /**
                 * ■終了月のADMIN.dumpを圧縮■
                 *
                 * ログ出力レベルが「詳細」でADMIN.dumpがある場合のみ（ログレベルで判断）
                 *
                 */
                if ($myCmxLog->getLoggingLevel() == CmxLog::T_LOGGING_DEBUG) {

                    $order->TARGET_PATH = dirname($myCmxLog->getLogFilePath()).'/';                                  // 対象root
                    $order->TARGET_FILE = str_replace('.log', '.ADMIN.dump', basename($myCmxLog->getLogFilePath())); // 単ファイル指定
                    $order->DEST_FILEPATH = $order->TARGET_PATH.'/'.$order->TARGET_FILE.'.zip';                      // 出力ファイル名

                    //$myCmxLog->inf('max_execution_time 変更前['.ini_get('max_execution_time').']');
                    //ini_set('max_execution_time', 0); // ----------------  0：タイムアウトをしないに設定(処理が終わるまで待ち続ける) ※2022-06実績 136.8MB 3分22秒
                    //$myCmxLog->inf('max_execution_time 変更後['.ini_get('max_execution_time').']');
                    compZip($order);
                    unlink($order->TARGET_PATH.$order->TARGET_FILE);
                }


                if ( $args->currentDateTime >= $args->finishDateTime
                  or $args->breakDoneForce ) {

                    ////////
                    //////// バッチ処理の完全終了
                    ////////

                    //
                    //（ステータス表示エリアを「（ExecBSで設定した）break!!」⇒「終了」に書換え）
                    //
                    $simulationResult->STATUS = STATUSBAR_SPAN."<span id='blinkMsg' style='font-size:small; color:black; font-weight:bold;'>FINISH!!</span><script>with(blinkMsg)id='',style.opacity=1,setInterval(function(){style.opacity^=1},200)</script><br>";

                } else {

                    ////////
                    //////// バッチ処理の継続（＞＞simulationNext 自動リロードスクリプト 設定）
                    ////////
                    $contentsArr[] = html_getAutoLinkScript('../CronJobs/SimulatorBS.php?action=simulationNext');

                    $currentDateTimeBuff = $simulationResult->args->currentDateTime; // いろいろ便利なのでgetArgs()を利用するが再度の助走は無駄なので


                    /**
                     * _nextMonthsFS()で適切な翌月営業日は得られているが時刻は正しくない可能性があるので補正が必要
                     *
                     * 土日・祝日カレンダーで調べた月初の営業日は得られているので、セッションがあるとみなす正午を利用して該当セッションから開始時刻を得る
                     *
                     */
                    $mySessionInfo = BSUtil::getBSOnsessionInfo(strtotime(date('Y-m-d 12:00', _nextMonthsBS($args->startDtime, 0)))); // _nextMonths()では。引数の12:00は保持されて戻る
                    $targetDTime = $mySessionInfo->START;
                    $targetDTime = _nextMonthsBS($args->startDtime, 0); // _nextMonths()では。引数の12:00は保持されて戻る

                    //////// シミュレーション変数を新しいものに更新する
                    $newlyArgs = getArgs($targetDTime, true);           // ¶ここに来たということでシミュレーションバッチ中であることは確定なので第2引数は「true」
                    $newlyArgs->currentDateTime = $currentDateTimeBuff; // 保持していた値を設定
                    $newlyArgs->prefixKey = $prefixKey;
                    $newlyArgs->loggingLevel = $loggingLevel;

                    /**
                     * ログファイルの切り替え
                     * ¶セッションのパスだけ上書きして旧ファイルパスのログインスタンスが残るとそこにCmxMailSender__destruct()処理が出力される
                     *
                     */
                    $myCmxLog->resetFileName(APP_TYPE.'_'.BSUtil::T_CMX_SIMULATION.$prefixKey.'_'.date('Ym', strtotime('+1 month', $args->startDtime)).'.log');
                    $newlyArgs->logFilePath = $myCmxLog->getLogFilePath();


                    $simulationResult->args = $newlyArgs;
                }

                //////// 現在シミュレーション処理中の年月表示
                $contentsArr[] = STATUSBAR_OPEN.date('Y/n', $args->startDtime).$simulationResult->STATUS.STATUSBAR_CLOSE;

                //////// シミュレション経過の内容を表示
                $contentsArr = array_merge($contentsArr, $simulationResult->contentsArr);

            } else {

                //////// ＞＞simulationNext 自動リロードスクリプト
                $contentsArr[] = html_getAutoLinkScript('../CronJobs/SimulatorBS.php?action=simulationNext');


                //////// 現在シミュレーション処理中の年月表示
                $contentsArr[] = STATUSBAR_OPEN.date('Y/n', $args->startDtime).$simulationResult->STATUS.STATUSBAR_CLOSE;

                //////// シミュレション経過の内容を表示
                $contentsArr = array_merge($contentsArr, $simulationResult->contentsArr);
            }

            /**
             *  シミュレーション変数一式をセッションに保持する
             *
             * ->startYMD string YYYYMM01 ※2022.06.19 startYMD廃止
             * ->currentDateTime
             * ->reportYm
             * ->startDTime
             * だから未使用になったって、->dispSum 当月の収支（復活！maxLossの比較判定にあった方がスムーズ）
             * だから未使用になったって、->maxLoss 当月の最大負け幅
             * ->simulationDetail 当月の日々明細（後で詳細画面のリンクしたときに使う）¶dailyDetailの連想配列（キーはYmd）
             * ->dailyDetail 日次の建玉～手仕舞いの明細（key：Ymd、value：stdClass(tateDtime, tateSide, tatePrice, tejimaiDtime, tejimaiPrice)の連想配列の配列）
             *
             */
            $_SESSION[APP_TYPE.'ARGS']       = serialize($simulationResult->args);

            /**
             *  新値足をセッションに保持する
             *
             *  2022.09.18 メモリ領域不足で失敗した場合は諦めて次回再度作成する。
             *
             *  2022.10.22 サイズが大きくて「Fatal error」だとThrowableでもcatchしないのでzip化保存で解決
             *
             */
            try {

                $workFile = dirname($myCmxLog->getLogFilePath()).'/'.SHINNEASHI_WORKFILENAME;
                file_put_contents($workFile, $simulationResult->shinneashi->getSerializedObjectContents());

                try {
                    $order = new stdClass;
                    $order->TARGET_PATH = dirname($workFile).'/';       // 対象root
                    $order->TARGET_FILE = basename($workFile);          // 単ファイル指定
                    $order->DEST_FILEPATH = $workFile.'.zip';           // 出力ファイル名

                    //$myCmxLog->inf('max_execution_time 変更前['.ini_get('max_execution_time').']');
                    //ini_set('max_execution_time', 0); // ----------------  0：タイムアウトをしないに設定(処理が終わるまで待ち続ける) ※2022-06実績 136.8MB 3分22秒
                    //$myCmxLog->inf('max_execution_time 変更後['.ini_get('max_execution_time').']');
                    compZip($order);
                } catch (Throwable $th) {
                    throw new Exception('新値足保持の圧縮に失敗しました。'.$th->getMessage());
                }

                $serializedZipContents = file_get_contents($workFile.'.zip');
                unlink($workFile);
                unlink($workFile.'.zip');

                // strlen()ダメ！ error_log('strlen '.strlen($serializedZipContents)."\n", 3, __DIR__.'/SHINNEASHI.log');

                $_SESSION[APP_TYPE.'SHINNEASHI'] = $serializedZipContents;
                $myCmxLog->inf('新値足をセッションに保持しました');

            } catch (Throwable $e) {

                $myCmxLog->inf('新値足のセッション保存はできませんでした。'.$e->getMessage());
            }



            // // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
            $VIEW['TITLE'] = "case 'simulationNext'";
            $VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
            $VIEW['CONTENTS'] = $contentsArr;
            include(HTML_ROOT.'COMMON/View/view.html');
            // // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

            break;

        case 'dailyDetail': // ■年月日指定で（セッション保持の）詳細画面を表示

            /**
             * $_REQUEST['action'] = dailyDetail
             *
             * 詳細画面を表示
             *
             */

            /**
             * シミュレーション変数一式をセッションから取得する
             *
             * ->startYMD string YYYYMM01 ※2022.06.19 startYMD廃止
             * ->currentDateTime
             * ->reportYm
             * ->startDTime
             * ->dispSum 当月の収支（復活！maxLossの比較判定にあった方がスムーズ）
             * ->maxLoss 当月の最大負け幅
             * ->simulationDetail 当月の日々明細（後で詳細画面のリンクしたときに使う）¶dailyDetailの連想配列（キーはYmd）
             * ->dailyDetail 日次の建玉～手仕舞いの明細（key：Ymd、value：stdClass(tateDtime, tateSide, tatePrice, tejimaiDtime, tejimaiPrice)の連想配列の配列）
             *
             */
            $args = unserialize(@$_SESSION[APP_TYPE.'ARGS']);

            $VIEW = array();
            $contentsArr = array();

            $contentsArr[] = '<br>';

            ////////////////////////////////
            //
            // リンクボタン「SIMU_TRENDREPORT」
            //
            ///////////////////////////////
            $sql = "select date, id, msg from N225M_TRENDREPORT_SIMU order by date, seq, id desc";
            $dTimeColumn = '&dTimeColumn=date';
            $contentsArr = array_merge($contentsArr, html_getLinkButon('TRENDREPORT', '../View/viewQueryBS.php?execQuery='.$sql.$dTimeColumn, 'target=_blank'));

            $contentsArr[] = '<br>';
            $contentsArr[] = STATUSBAR_OPEN.date('Y/n/j', strtotime($_REQUEST['detailYMD'])).STATUSBAR_CLOSE;

            if (!empty($_REQUEST['detailYMD']) and !empty($args->simulationDetail[$_REQUEST['detailYMD']])) {

                $theDailyDetailArr = $args->simulationDetail[$_REQUEST['detailYMD']];

                include(APP_ROOT.APP_TYPE.'/Obj/BS_MakeOrder.php'); // このアクションではBS_Adminをincludeしないので個別に必要
                include(APP_ROOT.APP_TYPE.'/Obj/BS_Order.php');     // このアクションではBS_Adminをincludeしないので個別に必要
                include(APP_ROOT.APP_TYPE.'/Obj/BS_Tategyoku.php');

                $contentsArr[] = '<table>';
                $todaysBudget = 0;
                foreach ($theDailyDetailArr as $theTrade) {

                    $contentsArr[] = '<tr><td>'.date('m/d H:i', $theTrade->tateDtime).'</td><td>'
                                     .getTategyokuValueStr($theTrade->tateSide).'</td><td>'
                                     .number_format($theTrade->tatePrice).'円</td><td>'
                                     .date('H:i', $theTrade->tejimaiDtime).'</td><td>'
                                     .addBracketsSignStyle(getBasicGainValue($theTrade->tateSide, $theTrade->tatePrice, $theTrade->tejimaiPrice, $theTrade->tejimaiPrice)).'</td></tr>';

                    switch($theTrade->tateSide) {

                        case INVESTX_TATEKAI:
                            $todaysBudget += $theTrade->tejimaiPrice - $theTrade->tatePrice;
                            break;
                        case INVESTX_TATEURI:
                            $todaysBudget += $theTrade->tatePrice - $theTrade->tejimaiPrice;
                            break;
                         default:
                             //////// どちらのケースのも該当しない異常ケース（こういうハンドリングは良い習慣）
                             throw new UnexpectedCaseException($theTrade->tateSide, UnexpectedCaseException::T_CASE_SWITCH);
                             break;
                     }  // -- end of switch()
                }
                $contentsArr[] = '</table>';

                $contentsArr[] = '<table><tr>';
                $contentsArr[] = "<td><span style='font-weight:bold;'>計 ".addBoldSignStyle($todaysBudget)."円</span></td>";
                $contentsArr[] = '</tr></table>';

            } else {

                $contentsArr[] = '['.$_REQUEST['detailYMD'].']セッションに存在しません<br>';
            }




            // // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //
            $VIEW['TITLE'] = "case 'dailyDetail'";
            $VIEW['DIR'] = '<B><font STYLE="font-size: 8px;">'.__DIR__.'</font></B><br>';
            $VIEW['CONTENTS'] = $contentsArr;
            include(HTML_ROOT.'COMMON/View/view.html');
            // // // // // // // // // // // // // // // // htmlページ生成 // // // // // // // // // // // // // // // //

            break;

        default:
            //////// どちらのケースのも該当しない異常ケース（こういうハンドリングは良い習慣）
            throw new UnexpectedCaseException($action, UnexpectedCaseException::T_CASE_SWITCH);
            break;
    }

//return;



/**
 * recent値からシミュレーション変数を決定する
 *
 * ->completeFlag
 * ->displayEnable
 * ->startYMD string YYYYMM01 ※2022.06.19 startYMD廃止
 * ->currentDateTime          ※2022.08.20 呼び出し元で設定するよう変更
 * ->reportYm                 ※2022.08.20 呼び出し元で設定するよう変更
 * ->startDTime               ※2022.06.19 廃止
 * ->reportList[]
 * 廃止 2022.09.14 ->reportTitleHtmltext
 * ->dispSum 当月の収支（復活！maxLossの比較判定にあった方がスムーズ）
 * ->maxLoss 当月の最大負け幅
 * ->breakDateTime number シミュレーションの月末改ページとなる年月日（月末最後でページ越え処理を行なう）
 * ->finishDateTime number シミュレーション終了となる年月日（バッチであるか否かでも決まる）
 *
 * ->simulationDetail 当月の日々明細（後で詳細画面のリンクしたときに使う）¶dailyDetailの連想配列（キーはYmd）
 * ->dailyDetail 日次の建玉～手仕舞いの明細（key：Ymd、value：stdClass(tateDtime, tateSimaiDtime, tejimaiPrice)の連想配列の配列）
 *
 * @param int $targetDTime
 * @param bool $batchExecuteFlag 単月シミュレート/シミュレーションバッチの区分（->finishDateTimeの設定値が異なる）
 * @return stdClass 準備した「シミュレーション変数」
 */
function getArgs(int $targetDTime, bool $batchExecuteFlag) {

        $args = new stdClass;


        // ======== 初期値の生成
        $args->completeFlag = false;
        $args->displayEnable = false;

        /**
         * シミュレーション経過を逐一表示する内容
         *
         * [0]は連想配列[totalValueHtmltext] 当月の合計金額（とmaxloss）                助走中は空値
         * [0]は連想配列[dayLinkHtmltext]    各日付ごとの詳細を表示するページへのリンク 助走中は固定文字「....preparing」
         * ※現行踏襲もreportListが何故配列になっているのかは不明
         */
        $args->reportList[] = array('dayLinkHtmltext' => '....preparing', 'totalValueHtmltext' => '');


        /**
         * シミュレーションの月末改ページとなる年月日（月末最後でページ越え処理を行なう）
         *
         * ->breakDateTime number
         *
         */
        // 指定年月の最終日
        $lastDate = date('Y-m-d', strtotime('last day of', strtotime(date('Ymd', $targetDTime))));
        // ======== 最終の営業日はナイトタイムセッションで終わるかデイタイムセッションで終わるかを調べるて決める
        $lastNightSessionInfo = BSUtil::getBSOnsessionInfo(_backToDaysBS(strtotime($lastDate.' 23:00'), 0));
        $lastDaytSessionInfo = BSUtil::getBSOnsessionInfo(_backToDaysBS(strtotime($lastDate.' 12:00'), 0));
        $args->breakDateTime = ($lastNightSessionInfo->END > $lastDaytSessionInfo->END) ? $lastNightSessionInfo->END : $lastDaytSessionInfo->END;

        /**
         *  シミュレーション終了となる年月日（バッチであるか否かでも決まる）
         *
         *  ->finishDateTime number
         *
         */
        if (!$batchExecuteFlag) {

            $args->finishDateTime = $args->breakDateTime;

        } else {

            // 先月の最終日
            $lastDate = date('Y-m-d', strtotime('last day of', strtotime('-1 month')));

            // ======== 最終の営業日はナイトタイムセッションで終わるかデイタイムセッションで終わるかを調べるて決める
            $lastNightSessionInfo = BSUtil::getBSOnsessionInfo(_backToDaysBS(strtotime($lastDate.' 23:00'), 0));
            $lastDaytSessionInfo = BSUtil::getBSOnsessionInfo(_backToDaysBS(strtotime($lastDate.' 12:00'), 0));
            $args->finishDateTime = ($lastNightSessionInfo->END > $lastDaytSessionInfo->END) ? $lastNightSessionInfo->END : $lastDaytSessionInfo->END;
        }

        /**
         * 助走を終えて記録を始める日時を設定する
         *
         *
         * _nextDays($targetDTime, 0)
         * ¶$targetDTimeが営業日でない場合あるので、この場合日付が進んで最初の営業日に変わる
         *
         */
        $tempSessionInfo = BSUtil::getBSOnsessionInfo(_nextGoDaysBS($targetDTime, 0)); // ¶_backToDaysBS()内部では正午12時を基準に処理するので必ずデイタイムセッションのある日が得られる
        $args->startDtime = $tempSessionInfo->START;

        $args->simulationDetail = array();
        $args->dailyDetail = array();

        return $args;
    }

    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====  shutdownハンドラ  ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    function my_shutdown_handler(){

        $error = error_get_last();

        if(empty($error)) return;

        switch($error['type']){
            case(E_ERROR):   $type = "Fatal Error"; break;
            case(E_WARNING): $type = "Warning"; break;
            case(E_NOTICE):  $type = "Notice"; break;
            default:         $type = "Error";
        }


        var_dump($error);

        $date = new DateTime();
        $line = sprintf("%s: %s. %s in %s on line %s\n", $date->format('Y-m-d H:i:s'), $type, $error['message'], $error['file'], $error['line']);
        file_put_contents(dirname(__FILE__) . '/_'.APP_TYPE.'_shutdown_handler.log', $line, FILE_APPEND | LOCK_EX);
    }
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====  shutdownハンドラ  ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
    // ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====                    ==== ==== ==== ====  ==== ==== ==== ==== ==== ==== ==== ====  ==== ==== ==== ====
?>