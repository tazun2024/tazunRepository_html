<?php

/*
echo " URL=".$_REQUEST["URL"]."<BR>";
echo "   S=".$_REQUEST["S"]."<BR>";
echo "eki1=".$_REQUEST["eki1"]."<BR>";
echo "eki2=".$_REQUEST["eki2"]."<BR>";
*/


  // URLを指定【　POST送信　テスト確認用ページ】※HTTPアクセス　POST送信　テスト確認用ページ
  // $url = "http://requestb.in/1eo34ah1";

  // ==== URLを指定
  $url = $_REQUEST["URL"]."?S=".$_REQUEST["S"]."&eki1=".$_REQUEST["eki1"]."&eki2=".$_REQUEST["eki2"];


  include("./_index.php");




  function getTransResponse($result) {

    // ページ内のリンク先を全置換
    $result = str_replace("href=\"/", "href=\"https://www.jorudan.co.jp/", $result);
    $result = str_replace("src=\"/", "src=\"https://www.jorudan.co.jp/", $result);

    $result = str_replace("action=\"/", "action=\"https://www.jorudan.co.jp/", $result);
    /*--これを実現するには、画面下の検索から時刻や経由などのパラメタも拾う必要がでてくるので見送り
    $result = str_replace("<form action=\"/norikae/cgi/nori.cgi", "<form action=\"http://aoyoko.work/transe/index.php", $result);
    $result = str_replace("<div class=\"header\">", "<div class=\"header\"><input type=\"hidden\" name=\"URL\" value=\"https://www.jorudan.co.jp/norikae/cgi/nori.cgi\" />", $result);
    */


    // 文字サイズを大きくする
    $result = str_replace("<div id=\"contents_out\" >", "<div id=\"contents_out\" ><font size=+2><!-- ■■ -->", $result);
    $result = str_replace("</div><!-- /contents_out -->", "</font><!-- ■■ --></div><!-- /contents_out -->", $result);

    // 全体を大きくしたので下の検索も広げておく
    $result = str_replace("id=\"eki1_in\"", "id=\"eki1_in\" style=\"height:32px\"", $result);
    $result = str_replace("id=\"eki2_in\"", "id=\"eki2_in\" style=\"height:32px\"", $result);
    $result = str_replace("id=\"eki3_in\"", "id=\"eki3_in\" style=\"height:32px\"", $result);


    // 入力予測機能欄を非表示にする
    $result = str_replace("<script>document.write('<span id=\"ck_sg\">　</span>'); </script>", "", $result);

    // 入力予測機能を無効にする
    $result = str_replace("<script type=\"text/javascript\">init_suggest();</script>", "", $result);


    // 横幅制限を解除
    $result = str_replace("<div id=\"left\">", "<div id=\"left_invalid\">", $result);

    // ヘッダーを削除
    $headerStr = trimString_HeadTail_Simpl($result, "<div id=\"header\" >", "</div><!-- /header -->");
    $result = str_replace($headerStr, "", $result);
    $result = str_replace("<div id=\"header\" ></div><!-- /header -->", "", $result);

    // 右側を削除
    $rightStr = trimString_HeadTail_Simpl($result, "<div id=\"right\"><!-- right -->", "</div><!-- right end -->");
    $result = str_replace($rightStr, "", $result);
    $result = str_replace("<div id=\"right\"><!-- right --></div><!-- right end -->", "", $result);

    // フッターを削除
    $footerStr = trimString_HeadTail_Simpl($result, "<div id=\"footer\" >", "</div><!-- /footer -->");
    $result = str_replace($footerStr, "", $result);
    $result = str_replace("<div id=\"footer\" ></div><!-- /footer -->", "", $result);

    // ホテル検索を削除
    $hotelsearchStr = trimString_HeadTail_Simpl($result, "<form action=\"https://api.jorudan.co.jp/hotel_vrg", "</form>");
    $result = str_replace($hotelsearchStr, "", $result);
    $result = str_replace("<form action=\"https://api.jorudan.co.jp/hotel_vrg</form>", "", $result);

    return $result;
  }

?>
