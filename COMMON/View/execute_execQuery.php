<?PHP

////////////////////////////////
//
// 実行クエリのtextarea表示
//
///////////////////////////////




////////////////////////////////
//
// クエリの実行
//
///////////////////////////////
try {

    $resultArr = $myCmxDao->getListTypeRecord($SQL);

    // ☆ブロック要素<span>はdiv要素とは異なり前後に改行が入りません。文章の途中でスタイルを変更したい場合等に使います。
    $contentsArr[] = '<span style="text-align:left; font-weight:bold;">record(s)&nbsp;</span><span>'.count($resultArr).'</span>';

} catch (Exception $e) {

    $tmpMsg = $e->getMessage();

    //////// エラーメッセージから「getListTypeRecord(), 発生行:」や「at line 行」を除去する
    if (mb_strpos($tmpMsg, 'getListTypeRecord(),') !== false) {

        if (mb_strpos($tmpMsg, ' at') !== false) {

            $tmpMsg = trimString_HeadTail_Simpl($tmpMsg, ': ', ' at');

        } else {

            $tmpMsg = trimString_Head_Simpl($tmpMsg, ': ');
        }
    }

    // ☆段落を指定する時に使うブロック要素<p>や、汎用ブロック要素<div>は独立した改行が入る
    $contentsArr[] = '<p style="color:red; font-weight:bold;">'.$tmpMsg.'</p>';
}


////////////////////////////////
//
// 抽出結果の表示
//
///////////////////////////////
$contentsArr[] = '<HR align=left width=80% color=silver>';

if (isset($resultArr)) {

    if (count($resultArr) > 0      // 条件：結果行数があったこと
        and $resultArr[0] <> '') { // 条件：select文の発行であったこと（insert、対象有無にかかわらずupdate/delete が成功の場合は空文字が1行入ってくる）

        //////// 最初のrowを利用してカラム名称を取得する（取得用プロパティとしての保持と表示）
        $contentsArr[] = '<table border=1 celpadding=0 cellspacing=0>';
        $contentsArr[] = '<tr>';
        $COLUMNARR = array();
        foreach ($resultArr[0] as $key => $val) {
            $COLUMNARR[] = $key;
            $contentsArr[] = '<th>'.$key.'</th>';
        }
        $contentsArr[] = '</tr>';

        $isEven = true;
        //////// 取得したrowをループ
        foreach ($resultArr as $row) {

            $contentsArr[] = '<tr>';

            if ($isEven) {
                $isEven = false;
                $tagOpenTD = '<td class=even>';
            } else {
               $isEven = true;
                $tagOpenTD = '<td>';
            }

            //////// row内の各カラムをループ
            foreach ($COLUMNARR as $column) {

                //////// 日時表示カラムとして指定のあったカラムはgetLoggingDatetimeStr()関数をかます
                if (in_array($column, $COLUMNARR_DTIMESTR)) {

                    // ☆StdClass の場合はプロパティ全体を {} で囲む必要があります。
                    $contentsArr[] = $tagOpenTD.date('Y-m-d H:i', strtotime($row->{$column})).'</td>';

                } else {

                    // ☆StdClass の場合はプロパティ全体を {} で囲む必要があります。
                    $contentsArr[] = $tagOpenTD.$row->{$column}.'</td>';
                }

            }

            $contentsArr[] = '</tr>';

        }

        $contentsArr[] = '</table>';
    }
}
?>
