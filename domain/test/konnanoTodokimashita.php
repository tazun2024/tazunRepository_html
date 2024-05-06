<?php

  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
  echo "<!-- saved from url=(0029)http://www9.ocn.ne.jp/~tazun/ -->";
  echo "<HTML xmlns=\"http://www.w3.org/1999/xhtml\">\n";
  echo "<HEAD>\n";
  echo "<TITLE>こんなの届きましたけど（再）</TITLE>\n";
  echo "<meta NAME=\"ROBOTS\" CONTENT=\"NOINDEX,NOFOLLOW,NOARCHIVE\">\n";
  echo "<meta name=\"viewport\" content=\"width=device-width\">\n";
  echo "<META http-equiv=Content-Type content=\"text/html; charset=UTF-8\">\n";
  echo "</HEAD>\n";

  echo "<BODY>\n";

//var_dump($_REQUEST);
echo "<br>";
echo "<br>";


  $count = 0;
  foreach ($_REQUEST as $key => $value) {
    echo $key." = [".$value."]<br>";
    $count ++;
  }
echo "<br>";
echo $count." param(s)<br>";

echo "<br>";
echo "<br>";

  $count = 0;
  foreach ($_COOKIE as $key => $value) {
    echo $key."=".$value."<br>";
    $count ++;
  }
echo "<br>";
echo $count." cookie(s)<br>";


  echo "</BODY>\n";
  echo "</html>\n";

?>
