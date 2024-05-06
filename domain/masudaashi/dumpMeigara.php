<?php


  $targetDir = __DIR__."/_img";
  $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator(
          $targetDir,
          FilesystemIterator::SKIP_DOTS
          |FilesystemIterator::KEY_AS_PATHNAME
          |FilesystemIterator::CURRENT_AS_FILEINFO
      ), RecursiveIteratorIterator::SELF_FIRST
  );

  $codeArr = array();
  foreach($iterator as $pathname => $info) {

    if( $info->isFile() ){

      if (mb_strpos($pathname, "target") !== false) {

        $code = mb_substr($pathname, mb_strpos($pathname, "target") - 4, 4);
        if (!in_array($code, $codeArr)) $codeArr[] = mb_substr($pathname, mb_strpos($pathname, "target") - 4, 4);
      }

    } else {

      // フォルダも再帰的に見てくれているようだ

    }
  }

  sort($codeArr);


  foreach ($codeArr as $code) {

    echo "\$targetCodeArr[] ='".$code."';<br>";
  }

?>
