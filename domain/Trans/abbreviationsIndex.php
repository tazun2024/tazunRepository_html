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
  $url = $_REQUEST["URL"];


  include("./_index.php");




  function getTransResponse($result) {


    // 削除
    $replaceStr = trimString_HeadTail_Simpl($result, "<div id=\"fb-root\" class=\" fb_reset\">", "ダウンロード\"></script>");
    $result = str_replace($replaceStr, "", $result);
    $result = str_replace("<div id=\"fb-root\" class=\" fb_reset\">ダウンロード\"></script>", "", $result);

    // ヘッダーを削除
    $replaceStr = trimString_HeadTail_Simpl($result, "<header id=\"header\">", "</header>");
    $result = str_replace($replaceStr, "", $result);
    $result = str_replace("<header id=\"header\"></header>", "", $result);

    // 削除
    $replaceStr = trimString_HeadTail_Simpl($result, "<div id=\"page-word-search-ops\">", "<a href=\"https://www.abbreviations.com/random.php\" class=\"z\">RANDOM</a>");
    $result = str_replace($replaceStr, "", $result);
    $result = str_replace("<div id=\"page-word-search-ops\"><a href=\"https://www.abbreviations.com/random.php\" class=\"z\">RANDOM</a>", "", $result);

    // 削除
    $replaceStr = trimString_HeadTail_Simpl($result, "<div class=\"hidden-xs\">", "<button type=\"button\" class=\"btn primary\" data-dismiss=\"modal\">Close</button>");
    $result = str_replace($replaceStr, "", $result);
    $result = str_replace("<div class=\"hidden-xs\"><button type=\"button\" class=\"btn primary\" data-dismiss=\"modal\">Close</button>", "", $result);

    // フッターを削除
    $replaceStr = trimString_HeadTail_Simpl($result, "<footer id=\"footer\">", "</footer>");
    $result = str_replace($replaceStr, "", $result);
    $result = str_replace("<footer id=\"footer\"></footer>", "", $result);

    // 削除
    $replaceStr = trimString_HeadTail_Simpl($result, "<link rel=\"stylesheet\" href=\"./What is the abbreviation for Employment__files/css\" media=\"all\">", "style=\"height:0px;width:0px;display:none;\" scrolling=\"no\" src=\"./What is the abbreviation for Employment__files/pd.html\">");
    $result = str_replace($replaceStr, "", $result);
    $result = str_replace("<link rel=\"stylesheet\" href=\"./What is the abbreviation for Employment__files/css\" media=\"all\">style=\"height:0px;width:0px;display:none;\" scrolling=\"no\" src=\"./What is the abbreviation for Employment__files/pd.html\">", "", $result);

    // 最後に残ったiframeを削除
    $result = str_replace("</iframe>", "", $result);


  //$dom = new DOMDocument;
  //@$dom->loadHTML(file_get_contents("https://www.abbreviations.com/abbreviation/Employment")); // @：ワーニング無視
  //$xpath = new DOMXPath($dom);

    return $result;
  }

?>
