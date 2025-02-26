<?php

namespace Frontend100p\Frontend100p_Settings\Utils;

class StrUtil
{
  public function findWord(string $text, string $subword): string
  {
    $substring  = '';
    $lengthWord = 0;
    $initialPos = strpos($text, $subword);
    for ($i = $initialPos, $textLen = strlen($text); $i < $textLen; $i++) {
      if ($text[$i] === ' ') {
        $substring = substr($text, $initialPos, $lengthWord);
        break;
      } else {
        $lengthWord++;
      }
    }

    return $substring;
  }
}
