<?PHP
define('FILECONTENTS_DIR', str_replace(SERVER_ROOT, '', HTML_ROOT).'_invextX.files/'); // ◆◆htmlディレクトリでは「SERVER_ROOT」による管理は有効◆◆


function addBracketsSignStyle($value) {

    $tmpMsg = '(';

    if ($value < 0) {

        $tmpMsg = $tmpMsg."<span style='color:red;'>".number_format($value)."</span>";

    } else {

        $tmpMsg = $tmpMsg.'+'.number_format($value);
    }

    return $tmpMsg.')';
}

function addBoldSignStyle($value) {

    if ($value < 0) {

        return "<span style='color:red;'>".number_format($value)."</span>";

    } else {

        return '+'.number_format($value);
    }
}


////////////////////////////////
//
// ＞＞simulationNext 自動リロードスクリプト
//
///////////////////////////////
function html_getAutoLinkScript($url) {

    return "<SCRIPT LANGUAGE=JavaScript>\n"
        ."<!--\nfunction autoLink() {location.href='".$url."';}\nsetTimeout('autoLink()',1);\n// -->\n"
            ."</SCRIPT>\n";
}


/**
 * シングルアクションのメニューを生成してhtmlコードを配列で返却する
 *
 * @param string $iconFileName アイコンのファイル名
 * @param string $menuTitle メニュー名
 * @param string $actionScript リンク時にexecQueryBS2022/execQueryFS2022へ渡すactionパラメタ
 * @param bool $imgFlipped アイコンとメニュー名の反転（メニューリンクを左）をする場合はtrue
 * @return string[] 生成されたhtmlコード
 */
function html_getSingleActionLinkMenu(string $iconFileName, string $menuTitle, string $actionScript, bool $imgFlipped) {

    $resultArr = array();

    $resultArr[] = '<table class=test_link>';
    $resultArr[] = '<tr>';
    if (!$imgFlipped) {

        $resultArr[] = '<td class=nallow><A href='.EXECUTEQUERY_SCRIPT.'?action='.$actionScript.'><img src='.FILECONTENTS_DIR.$iconFileName.'><font size=+1>'.$menuTitle.'</font></A></td>';
    } else {

        $resultArr[] = '<td class=nallow><A href='.EXECUTEQUERY_SCRIPT.'?action='.$actionScript.'><font size=-1>'.$menuTitle.'</font><img src='.FILECONTENTS_DIR.$iconFileName.'></A></td>';
    }
    $resultArr[] = '</tr>';
    $resultArr[] = '</table>';

    return $resultArr;
}

/**
 * テーブルに対するselectアクションのメニューを生成してhtmlコードを配列で返却する
 *
 * @param string  対象のテーブル名
 * @param string[] ｛key $parameterArr メニュー名, value $query where句で指定する条件｝
 * @return string[] 生成されたhtmlコード
 */
function html_getSelectTableLinkMenu(string $targetTableName, array $parameterArr) {

    $tableStyle = 'width:96; height:80; text-align:center; vertical-align:top; background:url('.FILECONTENTS_DIR.'Table-icon.png) no-repeat center top;';
    $resultArr = array();

    $resultArr[] = '<table>';
    $resultArr[] = '<tr>';
    $resultArr[] = '<td rowspan='.(count($parameterArr) + 1).' style="'.$tableStyle .'"><b><br>'.$targetTableName.'</b></td>';
    $resultArr[] = '</tr>';


    foreach ($parameterArr as $linkMenu => $query) {

        $resultArr[] = '<tr>';
        $paramStr = '';
        foreach($query as $key => $value) {
            $paramStr =$paramStr.$key.'='.rawurlencode($value).'&';
        }
        $resultArr[] = '<td><A href='.EXECUTEQUERY_SCRIPT.'?'.$paramStr.'>'.$linkMenu.'</td>';
        $resultArr[] = '</tr>';
    }

    $resultArr[] = '</table>';

    return $resultArr;
}

/**
 * リンクメニューを生成してhtmlコードを配列で返却する
 *
 * @param string $iconFileName アイコンのファイル名
 * @param string $menuTitle メニュー名
 * @param string $url リンク先としてhrefに設定される文字列
 * @return string[] 生成されたhtmlコード
 */
function html_getLinkMenu(string $iconFileName, string $menuTitle, string $url) {

    $resultArr = array();

    $resultArr[] = '<table class=test_link>';
    $resultArr[] = '<tr>';
    $resultArr[] = '<td class=nallow><A href='.$url.'><img src='.FILECONTENTS_DIR.$iconFileName.'><font size=+1>'.$menuTitle.'</font></A></td>';
    $resultArr[] = '</tr>';
    $resultArr[] = '</table>';

    return $resultArr;
}

/**
 * indexXXメニューのタブを生成してhtmlコードを配列で返却する
 *
 * @param stdClass[] ｛NAME メニュー名, CONTENTS メニューに該当するhtmlコンテンツ｝
 * @param int 選択された状態にするタブ番号（先頭の場合は「1」）
 * @return string[] 生成されたhtmlコード
 */
function html_getMainSelectTableLinkMenu(array $parameterArr, int $selectedNumber) {

    $resultArr = array();
    if (count($parameterArr) > 0) {

        $resultArr[] = '<div class=cmxTabWrap>';

        $count = 1;
        foreach ($parameterArr as $menuElement) {

            //// ==== name=TABでグループ化されたラジオボタン、<a>タグPOSTでname=TABの値が$countで決まるvalue値で連携される
            $resultArr[] = '<input id=TAB-'.strval($count).' type=radio name=TAB value='.strval($count).' class=cmxTabSwitch '.($count == $selectedNumber ? 'checked=checked' : '').' /><label class=cmxTab for=TAB-'.strval($count).'>&nbsp;'.$menuElement->NAME.'&nbsp;</label>';
            $resultArr[] = '<div class=cmxTab-content>';

            foreach ($menuElement->CONTENTS as $contentsRow) {

                $resultArr[] = $contentsRow;
            }

            $resultArr[] = '</div>';

            $count ++;
        }

        $resultArr[] = '</div>';

        return $resultArr;
    }
}

/**
 * indexXXメニューのタブを生成してhtmlコードを配列で返却する
 *
 * @param string ラベル名
 * @param string hrefに設定する値
 * @param string 「target=_blank」を設定するか否かを任意の文字列で渡せる
 * @return string[] 生成されたhtmlコード
 */
function html_getLinkButon(string $labelName, string $linkStr, string $targetStr) {

    $resultArr = array();
    $resultArr[] = '<div class=commandButonLeftArea>';
    $resultArr[] = '<a '.$targetStr.' href="'.$linkStr.'" style="all:initial;font-weight:bold;">'; // ¶クエリにはSPCが含まれるのでhrefをダブルコーテーションで括る必要がある
    $resultArr[] = '<div id=linkButton>'.$labelName.'</div>';
    $resultArr[] = '</a><br>';
    $resultArr[] = '</div>';

    return $resultArr;
}


/**
 * チャート表の表示処理（htmlテキストの出力）
 * ¶呼び出し元の<table>タグ内に出力するべき<tr>～</tr>を生成して出力する
 *
 * @param stdClass[]｛date, close_value/bid ask｝ $chartArr 出力対象となるチャート配列
 * @param CreateShinneashi $myShinneashi 併記する新値足情報と増田足情報
 * @param string $theMeigaraCd 日時の表示形式が$theMeigaraCdによって変わる汎用性
 * @return array
 */
function html_getChartTable(array $chartArr, CreateShinneashi $myShinneashi = null, string $theMeigaraCd) {

    $resultArray = array();

    foreach ($chartArr as $chartElement) {

        $resultArray[] = '<tr>';

        if ($myShinneashi != null) {



        } else {

            $resultArray[] = '<td>&nbsp;</td>';
        }

        if ($myShinneashi != null) {


        } else {

            $resultArray[] = '<td>&nbsp;</td>';
        }
        switch($theMeigaraCd) {

            case CoreBase::T_N225M:

                //// 新値足表示
                $tmpShinnneashiElement = $myShinneashi->getElement_dtime(strtotime($chartElement->date), 0);
                if (strtotime($tmpShinnneashiElement->date) == strtotime($chartElement->date)) {

                    //// ==== この日時（$chartElement->date）に新値足がある場合はそれが採用される
                    $resultArray[] = $myShinneashi->html_getShinneashiStatus($tmpShinnneashiElement);

                } else {

                    $resultArray[] = '<td>&nbsp;</td>';
                }

                //// ==== 画面表示エリアが狭いので短縮形の日時表記で固定
                $resultArray[] = '<td>'.date('n/j H:i', strtotime($chartElement->date)).'</td>';

                //// 増田足6色パターン
                $resultArray[] = '<td>'.$myShinneashi->getMasudaashi()->writeSixPtnValue($myShinneashi->getMasudaashi()->getElementNum_DTime(strtotime($chartElement->date))).'</td>';

                $resultArray[] = '<td>'.$myShinneashi->getPriceColumnValueStr($chartElement->close_value).'</td>';
                break;

            case CoreBase::T_AUD5M:
            case CoreBase::T_USD5M:

                //// 新値足表示
                $tmpShinnneashiElement = $myShinneashi->getElement_dtime(strtotime($chartElement->date), 0);
                if (strtotime($tmpShinnneashiElement->date) == strtotime($chartElement->date)) {

                    //// ==== この日時（$chartElement->date）に新値足がある場合はそれが採用される
                    $resultArray[] = $myShinneashi->html_getShinneashiStatus($tmpShinnneashiElement);

                } else {

                    $resultArray[] = '<td>&nbsp;</td>';
                }

                //// ==== 画面表示エリアが狭いので短縮形の日時表記で固定
                $resultArray[] = '<td>'.date('n/j H:i', strtotime($chartElement->date)).'</td>';

                //// 増田足6色パターン
                $resultArray[] = '<td>'.$myShinneashi->getMasudaashi()->writeSixPtnValue($myShinneashi->getMasudaashi()->getElementNum_DTime(strtotime($chartElement->date))).'</td>';

                $resultArray[] = '<td>'.$myShinneashi->getPriceColumnValueStr($chartElement->bid).'</td>';
                $resultArray[] = '<td>'.$myShinneashi->getPriceColumnValueStr($chartElement->ask).'</td>';
                break;

            default:

                //////// 2023.10.07 BillionStuffRevolution個別銘柄対応

                //// 新値足表示
                $tmpShinnneashiElement = $myShinneashi->getElement_dtime(strtotime($chartElement->date), 0);
                if (strtotime($tmpShinnneashiElement->date) == strtotime($chartElement->date)) {

                    //// ==== この日時（$chartElement->date）に新値足がある場合はそれが採用される
                    $resultArray[] = $myShinneashi->html_getShinneashiStatus($tmpShinnneashiElement);

                } else {

                    $resultArray[] = '<td>&nbsp;</td>';
                }

                $resultArray[] = '<td>'.date('Y/n/j', strtotime($chartElement->date)).'</td>';

                //// 増田足6色パターン
                //// 増田足6色パターン
                $resultArray[] = '<td>'.$myShinneashi->getMasudaashi()->writeSixPtnValue($myShinneashi->getMasudaashi()->getElementNum_DTime(strtotime($chartElement->date))).'</td>';

                $resultArray[] = '<td align=right>'.getPriceValueStr($chartElement->close_value / 100, $chartElement->close_value / 100).'</td>';
                break;

        }  // -- end of switch()

        $resultArray[] = '</tr>';
    }

    return $resultArray;
}


/**
 * 画面の日時表示形式の統一（$meigaraCdによって異なる形式処理を一元化）
 *
 * @param string $theDatetimeStr 日時文字列
 * @param string $meigaraCd 銘柄コード（'N225M'と'AUD5M'だけ異なる表示形式で処理される）
 * @return string
 */
function getDatetimeColumnValue(string $theDatetimeStr, string $meigaraCd) {


    if ($meigaraCd === CoreBase::T_N225M or $meigaraCd === CoreBase::T_AUD5M or $meigaraCd === CoreBase::T_USD5M) {

        // N225mini/AUD5分足 の場合は日付の表示を変えている
        return date('Y年n月j日 H:i', strtotime($theDatetimeStr));

    } else {

        return date('Y年n月j日 ', strtotime($theDatetimeStr));
    }
}


function html_getPriceStr(string $theMeigaraCd, int $price) {

    switch($theMeigaraCd) {

        case CoreBase::T_N225M:

            return getPriceValueFormat__BillionStuff($price);
            break;

        case CoreBase::T_AUD5M:
        case CoreBase::T_USD5M:

            return getPriceValueFormat__FxStar($price);
            break;

        default:
            //////// どちらのケースのも該当しない異常ケース（こういうハンドリングは良い習慣）
            throw new UnexpectedCaseException($theMeigaraCd, UnexpectedCaseException::T_CASE_SWITCH);
            break;

    }  // -- end of switch()
}


/**
 * 建玉情報を表示（BillionStff用）
 */
function html_getBsDispInfo(stdClass $infomationObj, string $theMeigaraCd) {


    $resultArray = array();


    //////// ======== tableタグ１つめ（<td>タグは４列）
    $resultArray[] = '<table>';

    // ======== 1行目は現在の建玉情報
    if (isset($infomationObj->HOURBARARR)) {
        if ($infomationObj->TATEGYOKUSIDE <> INVESTX_NOT_APPLICABLE_VALUE) {

            $resultArray[] = '<tr>';
            if ($infomationObj->TJMHOLD) {

                $resultArray[] = '<td bgcolor=silver align=center><font size=-1>TJM_HOLD</font></td>';

            } else {

                $resultArray[] = '<td width=68>&nbsp;</td>';
            }
            $resultArray[] = '<td>&nbsp;</td>';
            $resultArray[] = '<td>'.getLoggingDatetimeStr($infomationObj->TATEGYOKUDATE).'</td>';
            switch($infomationObj->TATEGYOKUSIDE) {

                case INVESTX_TATEKAI:
                    if ($infomationObj->TATEGYOKUDUMMY) {
                        $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEKAI).'(skip)&nbsp;</td>';
                    } else {
                        $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEKAI).'('.paddingCommaRed($infomationObj->CURRENTPRICE - $infomationObj->TATEGYOKUPRICE).')&nbsp;</td>';
                    }
                    break;
                case INVESTX_TATEURI:
                    if ($infomationObj->TATEGYOKUDUMMY) {
                        $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEURI).'(skip)&nbsp;</td>';
                    } else {
                        $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEURI).'('.paddingCommaRed($infomationObj->TATEGYOKUPRICE - $infomationObj->CURRENTPRICE).')&nbsp;</td>';
                    }
                    break;
                default:
                    //////// どちらのケースのも該当しない異常ケース（こういうハンドリングは良い習慣）
                    throw new UnexpectedCaseException($infomationObj->TATEGYOKUSID, UnexpectedCaseException::T_CASE_SWITCH);
                    break;

            }  // -- end of switch()
            $resultArray[] = '<td>&nbsp;</td></tr>';
        }

        // ======== 2行目は前回建玉情報
        if ($infomationObj->LSTTGYKSIDE <> INVESTX_NOT_APPLICABLE_VALUE) {

            $resultArray[] = '<tr>';
            $resultArray[] = '<td>&nbsp;</td>';
            $resultArray[] = '<td align=right><font color=gray>《&nbsp;</font></td>';
            $resultArray[] = '<td><font color=gray>'.getLoggingDatetimeStr($infomationObj->LSTTGYKDATE).'</font></td>';
            $resultArray[] = '<td><font color=gray>'.getTategyokuValueStr($infomationObj->LSTTGYKSIDE).'('.paddingCommaRed($infomationObj->LSTTJMGAIN).')&nbsp;</font></td>';
            $resultArray[] = '<td><font color=gray>》</font></td></tr>';
        }
    }

    $resultArray[] = '</table>';


    //////// ======== tableタグ２つめ（<td>タグは３列）
    $resultArray[] = '<table>';

    // ======== 1行目は上弦情報
    $resultArray[] = '<tr>';
    if ($infomationObj->MAXPRICE > $infomationObj->WVTOPPRICE) {

        //// 現在価格が保持している上限を超えている場合
        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->MAXDATE).' '.'</font></td>';
        $resultArray[] = '<td align=center>MAX</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->MAXPRICE).'</td>';

    } else {

        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->WVTOPDATE).' '.'</font></td>';
        $resultArray[] = '<td align=center>wvTop</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->WVTOPPRICE).'</td>';
    }
    $resultArray[] = '</tr>';

    // ======== 2行目は上弦/下弦情報（上弦値の日時/下弦値の日時から上昇局面か下降局面かを判定）
    $resultArray[] = '<tr>';
    if (strtotime($infomationObj->WVTOPDATE) > strtotime($infomationObj->WVBTMDATE)) {

        $thresholdPrice = thresholdRound(($infomationObj->MINPRICE + ($infomationObj->WVTOPPRICE - $infomationObj->MINPRICE) * INVESTX_WVPOINT_THRESHOLD), BS_BusinessBase::BS_THRESHOLD_ROUND);
        $resultArray[] = '<td colspan=2 align=right><img src=../../_invextX.files/downSide.png width=33%></td>';
        $resultArray[] = '<td><font color=red>('.html_getPriceStr($theMeigaraCd, $thresholdPrice).')</font></td>';

    } else {

        $thresholdPrice = thresholdRound(($infomationObj->MAXPRICE - ($infomationObj->MAXPRICE - $infomationObj->WVBTMPRICE) * INVESTX_WVPOINT_THRESHOLD), BS_BusinessBase::BS_THRESHOLD_ROUND);
        $resultArray[] = '<td colspan=2 align=right><img src=../../_invextX.files/upSide.png width=33%></td>';
        $resultArray[] = '<td><font color=blue>('.html_getPriceStr($theMeigaraCd, $thresholdPrice).')</font></td>';
    }
    $resultArray[] = '</tr>';

    // ======== 3行目は下弦情報
    $resultArray[] = '<tr>';
    if ($infomationObj->MINPRICE < $infomationObj->WVBTMPRICE) {

        //// 現在価格が保持している上限を超えている場合
        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->MINDATE).'</font></td>';
        $resultArray[] = '<td align=center>MIN</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->MINPRICE).'</td>';

    } else {

        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->WVBTMDATE).'</font></td>';
        $resultArray[] = '<td align=center>wvBtm</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->WVBTMPRICE).'</td>';
    }
    $resultArray[] = '</tr>';

    $resultArray[] = '</table>';

/*
    $resultArray[] = '<table>';
    foreach ($infomationObj as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $elementKey => $elementValue) {
                $resultArray[] = '</tr>';
                if (is_array($elementValue)) {
                    $resultArray[] = '<td>'.$key.'['.$elementKey.']</td><td>array</td>';
                } elseif (is_object($elementValue)) {
                    $resultArray[] = '<td>'.$key.'['.$elementKey.']</td><td>stdClass</td>';
                } else {
                    $resultArray[] = '<td>'.$key.'['.$elementKey.']</td><td>['.$elementValue.']</td>';
                }
                $resultArray[] = '</tr>';
            }
        } else {
            $resultArray[] = '</tr>';
            $resultArray[] = '<td>'.$key.'</td><td>['.$value.']</td>';
            $resultArray[] = '</tr>';
        }
    }
    $resultArray[] = '</table>';
*/
    return $resultArray;
}


/**
 * 建玉情報を表示（FxStar用）
 */
function html_getFsDispInfo(stdClass $infomationObj, string $theMeigaraCd) {


    $resultArray = array();


    //////// ======== tableタグ１つめ（<td>タグは４列）
    $resultArray[] = '<table>';

    // ======== 1行目は現在の建玉情報
    // ---------------- レイアウト変更（「今回履歴対象のhourbar時刻とロウソク足結果」に移動しました）

    // ======== 2行目～は建玉履歴情報
    // ①今回履歴対象のhourbar時刻とロウソク足結果ブロック
    if (isset($infomationObj->HOURBARARR)) {

        $ct = 0;
        foreach ($infomationObj->HOURBARARR as $hourbarObj) {

            $resultArray[] = '<tr>';
            // ◆◆◆◆
            // ◆◆◆◆ htmlとはいえマジックナンバーの塊
            // ◆◆◆◆ '陽線' '陰線'
            // ◆◆◆◆
            switch($hourbarObj->CANDLEPTN) {
                case '陽線':
                    $resultArray[] = '<td bgcolor='.CreateShinneashi::T_BGCOLOR_POSI.'><font size=-1 color='.CreateShinneashi::T_COLOR_POSI.'>'.$hourbarObj->TIME.' '.$hourbarObj->CANDLEPTN.'&nbsp;</font></td>';
                    break;
                case '陰線':
                    $resultArray[] = '<td bgcolor='.CreateShinneashi::T_BGCOLOR_NEGA.'><font size=-1 color='.CreateShinneashi::T_COLOR_NEGA.'>'.$hourbarObj->TIME.' '.$hourbarObj->CANDLEPTN.'&nbsp;</font></td>';
                    break;
                default:
                    //////// どちらのケースのも該当しない異常ケース（こういうハンドリングは良い習慣）
                    throw new UnexpectedCaseException($hourbarObj->CANDLEPTN, UnexpectedCaseException::T_CASE_SWITCH);
                    break;
            }  // -- end of switch()

            if ($infomationObj->TATEGYOKUSIDE == INVESTX_NOT_APPLICABLE_VALUE) {

                //// 建玉がない場合

                $resultArray[] = '<td>&nbsp;</td>';
                $resultArray[] = '<td>&nbsp;</td>';

            } else {

                //// 建玉がある場合

                if ($ct == count($infomationObj->HOURBARARR) - 1) {

                    //// $infomationObj->HOURBARARRの最終行（通常は3行目）に建玉情報を表示する

                    $resultArray[] = '<td>'.getLoggingDatetimeStr($infomationObj->TATEGYOKUDATE).'</td>';
                    switch($infomationObj->TATEGYOKUSIDE) {

                        case INVESTX_TATEKAI:
                            if ($infomationObj->TATEGYOKUDUMMY) {
                                $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEKAI).'(skip)&nbsp;</td>';
                            } else {
                                $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEKAI).'('.paddingCommaRed($infomationObj->CURRENTPRICEBID - $infomationObj->TATEGYOKUPRICE).')&nbsp;</td>';
                            }
                            break;
                         case INVESTX_TATEURI:
                            if ($infomationObj->TATEGYOKUDUMMY) {
                                $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEURI).'(skip)&nbsp;</td>';
                            } else {
                                $resultArray[] = '<td>'.getTategyokuValueStr(INVESTX_TATEURI).'('.paddingCommaRed($infomationObj->TATEGYOKUPRICE - $infomationObj->CURRENTPRICEASK).')&nbsp;</td>';
                            }
                            break;
                        default:
                            //////// どちらのケースのも該当しない異常ケース（こういうハンドリングは良い習慣）
                            throw new UnexpectedCaseException($infomationObj->TATEGYOKUSID, UnexpectedCaseException::T_CASE_SWITCH);
                            break;

                    }  // -- end of switch()

                } else {

                    $resultArray[] = '<td colspan=2>&nbsp;</td>';
                }
            }

            $resultArray[] = '<td>&nbsp;</td>';
            $resultArray[] = '</tr>';

            $ct++;
        }

        // ②履歴表示ブロック
        foreach ($infomationObj->TATEGYOKUHISTORY as $historyElement) {

            $resultArray[] = '<tr>';
            $resultArray[] = '<td align=right><font color=gray>《&nbsp;</font></td>';
            $resultArray[] = '<td><font color=gray>'.getLoggingDatetimeStr($historyElement->date).'</font></td>';
            $resultArray[] = '<td><font color=gray>'.$infomationObj->TATEGYOKUHISTORYSIDE.'('.paddingCommaRed($historyElement->price).')&nbsp;</font></td>';
            $resultArray[] = '<td><font color=gray>》</font></td></tr>';
        }
    }

    $resultArray[] = '</table>';


    //////// ======== tableタグ２つめ（<td>タグは３列）
    $resultArray[] = '<table>';

if ($theMeigaraCd <> CoreBase::T_USD5M) { // ¶現状USDの上弦下弦はメンテされていない

    // ======== 1行目は上弦情報
    $resultArray[] = '<tr>';
    if ($infomationObj->MAXPRICE > $infomationObj->WVTOPPRICE) {

        //// 現在価格が保持している上限を超えている場合
        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->MAXDATE).' '.'</font></td>';
        $resultArray[] = '<td align=center>MAX</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->MAXPRICE).'</td>';

    } else {

        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->WVTOPDATE).' '.'</font></td>';
        $resultArray[] = '<td align=center>wvTop</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->WVTOPPRICE).'</td>';
    }
    $resultArray[] = '</tr>';

    // ======== 2行目は上弦/下弦情報（上弦値の日時/下弦値の日時から上昇局面か下降局面かを判定）
    $resultArray[] = '<tr>';
    if (strtotime($infomationObj->WVTOPDATE) > strtotime($infomationObj->WVBTMDATE)) {

        $thresholdPrice = thresholdRound(($infomationObj->MINPRICE + ($infomationObj->WVTOPPRICE - $infomationObj->MINPRICE) * INVESTX_WVPOINT_THRESHOLD), FS_BusinessBase::FS_THRESHOLD_ROUND);
        $resultArray[] = '<td colspan=2 align=right><img src=../../_invextX.files/downSide.png width=33%></td>';
        $resultArray[] = '<td><font color=red>('.html_getPriceStr($theMeigaraCd, $thresholdPrice).')</font></td>';

    } else {

        $thresholdPrice = thresholdRound(($infomationObj->MAXPRICE - ($infomationObj->MAXPRICE - $infomationObj->WVBTMPRICE) * INVESTX_WVPOINT_THRESHOLD), FS_BusinessBase::FS_THRESHOLD_ROUND);
        $resultArray[] = '<td colspan=2 align=right><img src=../../_invextX.files/upSide.png width=33%></td>';
        $resultArray[] = '<td><font color=blue>('.html_getPriceStr($theMeigaraCd, $thresholdPrice).')</font></td>';
    }
    $resultArray[] = '</tr>';

    // ======== 3行目は下弦情報
    $resultArray[] = '<tr>';
    if ($infomationObj->MINPRICE < $infomationObj->WVBTMPRICE) {

        //// 現在価格が保持している上限を超えている場合
        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->MINDATE).'</font></td>';
        $resultArray[] = '<td align=center>MIN</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->MINPRICE).'</td>';

    } else {

        $resultArray[] = '<td><font size=-1>'.getLoggingDatetimeStr($infomationObj->WVBTMDATE).'</font></td>';
        $resultArray[] = '<td align=center>wvBtm</td>';
        $resultArray[] = '<td>'.html_getPriceStr($theMeigaraCd, $infomationObj->WVBTMPRICE).'</td>';
    }
    $resultArray[] = '</tr>';

    $resultArray[] = '</table>';
}
/*
    $resultArray[] = '<table>';
    foreach ($infomationObj as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $elementKey => $elementValue) {
                $resultArray[] = '</tr>';
                if (is_array($elementValue)) {
                    $resultArray[] = '<td>'.$key.'['.$elementKey.']</td><td>array</td>';
                } elseif (is_object($elementValue)) {
                    $resultArray[] = '<td>'.$key.'['.$elementKey.']</td><td>stdClass</td>';
                } else {
                    $resultArray[] = '<td>'.$key.'['.$elementKey.']</td><td>['.$elementValue.']</td>';
                }
                $resultArray[] = '</tr>';
            }
        } else {
            $resultArray[] = '</tr>';
            $resultArray[] = '<td>'.$key.'</td><td>['.$value.']</td>';
            $resultArray[] = '</tr>';
        }
    }
    $resultArray[] = '</table>';
*/
    return $resultArray;
}

?>
