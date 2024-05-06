<?php

    // ==== ==== ==== ====
    // ==== ==== ==== ==== 環境定数【環境依存】
    // ==== ==== ==== ====
    define('APP_ROOT',    '/home/users/2/boy.jp-cmx/web/app/');
    define('HTML_ROOT',   '/home/users/2/boy.jp-cmx/web/html/');
  //define('APP_ROOT',    'C:/_developmentWork/PHP_appResource/');
  //define('HTML_ROOT',   'C:/_developmentWork/xampp/htdocs/html/');

    if (!file_exists(APP_ROOT) or !file_exists(HTML_ROOT)) {echo '<body><div>invalid env.<br>APP_ROOT['.APP_ROOT.']<br>invalid HTML_ROOT['.HTML_ROOT.']<br></div></body>';}


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 定数設定
    // ==== ==== ==== ====
    define('DOMAIN_ROOT', HTML_ROOT.'domain/');


    // ==== ==== ==== ====
    // ==== ==== ==== ==== 必要なファイルをinclude
    // ==== ==== ==== ====
    include(APP_ROOT.'COMMON/Util/webAccessUtil.php');
    include(DOMAIN_ROOT.'masudaashi/search_common.php');

    //include(APP_ROOT.'COMMON/Util/NewSimpleUtil.php');
    include(APP_ROOT.'COMMON/Log/CmxLog.php');
    $cmxLog = new CmxLog(dirname(__FILE__) .'/log.txt');


    $cookieFile = HTML_ROOT.'_cfg/COOKIE_GMO.php';


    $timerWait = @$_REQUEST['TIMER'] ?: '60'; // ▼そこでエルビス演算子ですよ
    $currentLogName = date('Ymd_Hi');




    $file = dirname(__FILE__) . '/getGmoResponse1.log';
    if (file_exists($file)) unlink($file);
    $file = dirname(__FILE__) . '/getGmoResponse2.log';
    if (file_exists($file)) unlink($file);
    $file = dirname(__FILE__) . '/getGmoResponse3.log';
    if (file_exists($file)) unlink($file);


    // ==========================================================
    // ======== 2020.10.09 GMOのログオン取得を共通util化 ========

    $result = getGMO_Logon($cmxLog, $cookieFile);
    $baseUrl            = $result->BASE_URL;
    $cookieParamStrBuff = $result->COOKIE_BUFF;

    // ======== 2020.10.09 GMOのログオン取得を共通util化 ========
    // ==========================================================


    //echo '- - - - - - - - - - - - - - - -<br>';
    //echo '$baseUrl<br>';
    //echo '- - - - - - - - - - - - - - - -<br>';
    //echo $baseUrl.'<br>';
    //echo '- - - - - - - - - - - - - - - -<br>';
    //echo '- - - - - - - - - - - - - - - -<br>';
    //echo '<br>';

    echo '- - - - - - - - - - - - - - - -<br>';
    echo '今の$cookieParamStrBuff<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo $cookieParamStrBuff.'<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '<br>';

    //echo '- - - - - - - - - - - - - - - -<br>';
    //echo '$result->RESULT<br>';
    //echo '- - - - - - - - - - - - - - - -<br>';
    //var_dump($result->RESULT);
    //echo '<br>';
    //echo '- - - - - - - - - - - - - - - -<br>';
    //echo '- - - - - - - - - - - - - - - -<br>';
    //echo '<br>';



    //$url = $baseUrl."kabu/portfolioList.do"; // 「ウォッチリスト登録・編集」のリンクurl

    $url = 'https://sec-sso.click-sec.com/loginweb/sso-redirect?s=09&p=01&sp=01';
    //$url = 'https://cmx.boy.jp/html/domain/test/konnanoTodokimashita.php';

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_COOKIE, $cookieParamStrBuff); // session1で取得後に処理した文字列をCookie情報として引き渡す
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');        // リンクurlなのでGETリクエスト
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        // curl_execの結果を文字列で得るよう指示
    curl_setopt($ch, CURLOPT_HEADER, true);                // 次回のsession用にレスポンスヘッダも文字列として得るよう指示

    // 2024.05.02、2024.05.03
    // 追従しない curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// リダイレクト許可

    // ======== リクエスト発行
    $result = curl_exec($ch); // （リダイレクト追従すると「ログインしてくださいページ」が返ってくる）

    // ========
    // ======== GMOのサイトはSJISだ！
    // ======== 文字化け対策のためHTML-ENTITIESで処理する
    // ========
    $result = mb_convert_encoding($result, 'HTML-ENTITIES', 'SJIS');


    if ($result == null) {

        //$dispStrArr = array(); // 2023.08.29 Empty string supplied as input 対応
        echo '$resul is NULL!<br>';
        echo '- - - - - - - - - - - - - - - -<br>';
        echo 'curl_errno() '.curl_errno($ch).'<br>';
        echo 'curl_errno() '.curl_error($ch).'<br>';
        ;

    } else {

        file_put_contents(dirname(__FILE__) . '/getGmoResponse1.log', $result, LOCK_EX);

        //echo $result.'<br>';
        //echo '- - - - - - - - - - - - - - - -<br>';
    }

    // curl後始末
    curl_close($ch);








    //
    //
    // レスポンスのヘッダから新しいLocationを取得する
    //
    //
    $ayaya = trimString_HeadTail_Simpl($result, 'Location:', 'Content-Length:'); // ブラウザーでこのアドレスを打ち込むと希望のページに行ける







    $url = $ayaya;
    //$url = 'https://cmx.boy.jp/html/domain/test/konnanoTodokimashita.php';

    echo '- - - - - - - - - - - - - - - -<br>';
    echo '$ayaya<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo $ayaya.'<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '<br>';

    $ch = curl_init($url);

    echo '- - - - - - - - - - - - - - - -<br>';
    echo 'curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo curl_getinfo($ch, CURLINFO_EFFECTIVE_URL).'<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '<br>';

    curl_setopt($ch, CURLOPT_COOKIE, $cookieParamStrBuff); // session1で取得後に処理した文字列をCookie情報として引き渡す
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');        // リンクurlなのでGETリクエスト
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        // curl_execの結果を文字列で得るよう指示
    curl_setopt($ch, CURLOPT_HEADER, true);                // 次回のsession用にレスポンスヘッダも文字列として得るよう指示

    // 2024.05.02、2024.05.03
    // 追従しない curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// リダイレクト許可

    // ======== リクエスト発行
    $result = curl_exec($ch);

    // ========
    // ======== GMOのサイトはSJISだ！
    // ======== 文字化け対策のためHTML-ENTITIESで処理する
    // ========
    $result = mb_convert_encoding($result, 'HTML-ENTITIES', 'SJIS');


    // ======== CURLOPT_HEADER指示で$resultに得たCookie情報を切り出し＆結合 処理
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
    $cookieParamStr = '';
    foreach($matches[1] as $item) {
        parse_str($item, $cookie);
        $cookieParamStr = $cookieParamStr.key($cookie).'='.$cookie[key($cookie)].';';
    }

    echo '- - - - - - - - - - - - - - - -<br>';
    echo '今の$cookieParamStrBuff、、何も変わらない<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo $cookieParamStrBuff.'<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '<br>';

    if ($result == null) {

        //$dispStrArr = array(); // 2023.08.29 Empty string supplied as input 対応
        echo '$resul is NULL!<br>';
        echo '- - - - - - - - - - - - - - - -<br>';
        echo 'curl_errno() '.curl_errno($ch).'<br>'; // cURL：（3）URLが不正な形式または不足しているURLを使用しています
        echo 'curl_errno() '.curl_error($ch).'<br>';

    } else {

        file_put_contents(dirname(__FILE__) . '/getGmoResponse2.log', $result, LOCK_EX);

        echo $result.'<br>';
        echo '- - - - - - - - - - - - - - - -<br>';
    }

    // curl後始末
    curl_close($ch);

/*











    echo '- - - - - - - - - - - - - - - -<br>';
    echo '$cookieParamStrBuff<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo $cookieParamStrBuff.'<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '- - - - - - - - - - - - - - - -<br>';
    echo '<br>';

    //$url = $baseUrl."kabu/portfolioList.do"; // 「ウォッチリスト登録・編集」のリンクurl
    $url = 'https://sec-sso.click-sec.com/loginweb/sso-redirect?s=09&p=01&sp=01';
    //$url = 'https://fx.click-sec.com/neo/web/trade';

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_COOKIE, $cookieParamStrBuff); // session1で取得後に処理した文字列をCookie情報として引き渡す
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');        // リンクurlなのでGETリクエスト
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        // curl_execの結果を文字列で得るよう指示
    curl_setopt($ch, CURLOPT_HEADER, true);                // 次回のsession用にレスポンスヘッダも文字列として得るよう指示

    // 2024.05.02
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// リダイレクト許可


    // ======== リクエスト発行
    $result = curl_exec($ch);


    // ======== CURLOPT_HEADER指示で$resultに得たCookie情報を切り出し＆結合 処理
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
    $cookieParamStr = '';
    foreach($matches[1] as $item) {
        parse_str($item, $cookie);
        $cookieParamStr = $cookieParamStr.key($cookie).'='.$cookie[key($cookie)].';';
    }

    $cookieParamStrBuff = $cookieParamStr;

    // curl後始末
    curl_close($ch);

    if ($result == null) {

        //$dispStrArr = array(); // 2023.08.29 Empty string supplied as input 対応
        echo '$resul is NULL!<br>';
        echo '- - - - - - - - - - - - - - - -<br>';

    } else {

        file_put_contents(dirname(__FILE__) . '/getGmoResponse2.log', $result, LOCK_EX);

        echo $result.'<br>';
        echo '- - - - - - - - - - - - - - - -<br>';
    }


*/

    // ----------------------------------------------------------------
    // ---------------------------------------------------------------- 個別銘柄のページから情報を抜き出して表示用文字列生成する
    // ----------------------------------------------------------------






?>
