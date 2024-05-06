<?php

  const ROOT_URL  = 'https://cmx.boy.jp/html/Minamicho/';
  const ROOT_PATH = '/home/users/2/boy.jp-cmx/web/'; // appとhtmlのあるルートを指すサーバ上の絶対パス

  const GENERAL_ROOT = ROOT_PATH;
  const PUBLIC_ROOT  = GENERAL_ROOT."html/Minamicho/";

  include(GENERAL_ROOT."app/Minamicho/Minamicho.php");


  function getEncode($str) {
  //return mb_convert_encoding($str, "UTF-8", "SJIS");
    return $str;
  }




  $headerArr = array();
  $path = (@$_REQUEST['PATH'] ?: ROOT_PATH);

  switch($_REQUEST['ACTION'] ?? '') {

    case 'uploadDone':

      // ==== uploadでのsubmitの場合はここでアップロードしてからファイルリスト取得する

      $path       = $_POST['PATH'];  // 2018.07.28 会社環境対応
      uploadDone($path);

      break;


    case 'changeDT':

      // ==== changeDTでのsubmitの場合はファイル日時を変更してからファイルリスト取得する

      $targetFile = $_POST['FILE'];
      $dtimeStr   = $_POST['DTIME'];
      $path       = $_POST['PATH'];  // 2018.07.28 会社環境対応
      changeDT($targetFile, $dtimeStr);

      break;


    case 'rename':

      // ==== renameでの遷移の場合は変更用formをする

      $targetFile = $_REQUEST['FILE'];

      setlocale(LC_ALL, 'ja_JP.UTF-8'); // ロケールを設定
      $headerArr[] = "<font color=red >ファイル名の変更</font>&nbsp;<a href='./index.php?PATH=".$path."'>[戻る]</a><br>";

      $headerArr[] = "<form action='./index.php' method='post' enctype='multipart/form-data'>";
      $headerArr[] = "&nbsp;<input type='text' size='40' name='DESTNAME' value='".basename(getEncode($targetFile))."'>";
      $headerArr[] = "&nbsp;<input type='hidden' name='ACTION' value='rename_done'>";
      $headerArr[] = "&nbsp;<input type='hidden' name='PATH'   value='".$path."'>";
      $headerArr[] = "&nbsp;<input type='hidden' name='FILE'   value='".getEncode($targetFile)."'>";
      $headerArr[] = "&nbsp;<input type='submit' value='変更'>";
      $headerArr[] = "</form>\n";

      $headerArr[] = "<br>";

      break;


    case 'rename_done':

      // ==== rename_doneでの遷移の場合はここでファイル名変更する

      $targetFile = $_REQUEST['FILE'];
      $destName   = $_REQUEST['DESTNAME'];
      rename($targetFile, dirname($targetFile).'/'.$destName);

      break;


    case 'delete':

      // ==== deleteでの遷移の場合はここで「確認リンク」を表示する

      $targetFile = $_REQUEST['FILE'];
      $headerArr[] = "<font color=blue>".getEncode($targetFile)."</font><br>";
      $headerArr[] = "<font color=red >削除しますか？<a href='./index.php?ACTION=delete_done&FILE=".urlencode(getEncode($targetFile))."&PATH=".$path."'>&nbsp;[削除する]</a>&nbsp;<a href='./index.php?PATH=".$path."'>[戻る]</a></font><br>";
      $headerArr[] = "<br>";

      break;


    case 'delete_done':

      // ==== delete_doneでの遷移の場合はここで削除する

      $targetFile = $_REQUEST['FILE'];
      delete_done(getEncode($targetFile)); // 2018.07.28 会社環境対応

      break;

    case 'comp':

      // ==== 既存の圧縮（フォルダ対象の身）機能にI/Fを合わせている

      $order = new stdClass;
      $order->TARGET_PATH = $path;                                                                         // 対象root
      $order->TARGET_FILE = '';                                                                            // 単ファイル指定
      $order->REGEX = '';                                                                                  // 対象ファイルのフィルタリング
      $order->EXCLUDE_PATH = array('COMP_DEST');                                                           // 除外フォルダ
      $order->DEST_FILEPATH = $path.'/COMP_DEST'.date('YmdHis').'.zip';                                    // 出力ファイル名
      compress($order);

      break;

    case 'compFile':

      // ==== 指定ファイルの圧縮（フォルダ対象の身）機能にI/Fを合わせている

      $order = new stdClass;
      $order->TARGET_PATH = $path;                                                                         // 対象root
      $order->TARGET_FILE = $_REQUEST['FILE'];                                                             // 単ファイル指定
      $order->REGEX = '';                                                                                  // 対象ファイルのフィルタリング
      $order->EXCLUDE_PATH = array();                                                                      // 除外フォルダ
      $order->DEST_FILEPATH = $path.'/'.$_REQUEST['FILE'].'.'.date('YmdHis').'.zip';                       // 出力ファイル名
      compress($order);

      break;

  }  // -- end of switch()









  // ==== ファイルリストの取得と表示
  $myMinamicho = new Minamicho($path);
  $view = $myMinamicho->view;
  $view['HEADER'] = $headerArr;
  include('index.html');

  // ==== アップロード用form表示
  echo "<HR>";
  echo "<form action='./index.php' method='post' enctype='multipart/form-data'>";
  echo "&nbsp;<input type='hidden' name='ACTION' value='uploadDone'>";
  echo "&nbsp;<input type='hidden' name='PATH'   value='".$path."'>";
  echo "&nbsp;<input type='file' size='40' name='upload'>";
  echo "&nbsp;<input type='submit' value='upload'>";
  echo "</form>\n";

  // ==== 圧縮用ボタン表示
  echo "<HR>";
  echo "<form action='./index.php' method='post'>";
  echo "&nbsp;<input type='hidden' name='ACTION' value='comp'>";
  echo "&nbsp;<input type='hidden' name='PATH'   value='".$path."'>";
  echo "&nbsp;<input type='submit' value='comprerss'>";
  echo "&nbsp;<font size=-1 style='background:linear-gradient(transparent 40%,#DCDCDC 80%);'>".$path."</font>";
  echo "</form>\n";

?>
