<?php
/*
 csscolor.php
 Copyright 2004 Patrick Fitzgerald
 http://www.barelyfitz.com/projects/csscolor/

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class CSSColour{

  public function lighten($hex, $percent){
    return $this->mix($hex, $percent, 255);
  }
  public function darken($hex, $percent){
    return $this->mix($hex, $percent, 0);
  }

  //--------------------------------------------------
  public function mix($hex, $percent, $mask){
    // Make sure inputs are valid
    if (!is_numeric($percent) || $percent < 0 || $percent > 1) return false;
    if (!is_int($mask) || $mask < 0 || $mask > 255) return false;

    $rgb = $this->hex2RGB($hex);
    if (!is_array($rgb)) return false;

    for ($i=0; $i<3; $i++) {
      $rgb[$i] = round($rgb[$i] * $percent) + round($mask * (1-$percent));
      // In case rounding up causes us to go to 256
      if ($rgb[$i] > 255) $rgb[$i] = 255;
    }
    return $this->RGB2Hex($rgb);
  }

  public function hex2RGB($hex){
    //
    // Given a hex color (rrggbb or rgb),
    // returns an array (r, g, b) with decimal values
    // If $hex is not the correct format,
    // returns false.
    //
    // example:
    // $d = hex2RGB('#abc');
    // if (!$d) { error }

    // Regexp for a valid hex digit
    $d = '[a-fA-F0-9]';

    // Make sure $hex is valid
    if (preg_match("/^($d$d)($d$d)($d$d)\$/", $hex, $rgb)) {
      return array(
       hexdec($rgb[1]),
       hexdec($rgb[2]),
       hexdec($rgb[3])
       );
    }
    if (preg_match("/^($d)($d)($d)$/", $hex, $rgb)) {
      return array(
       hexdec($rgb[1] . $rgb[1]),
       hexdec($rgb[2] . $rgb[2]),
       hexdec($rgb[3] . $rgb[3])
       );
    }
    return false;
  }

  //--------------------------------------------------
  public function RGB2Hex($rgb){
    // Given an array(rval,gval,bval) consisting of
    // decimal color values (0-255), returns a hex string
    // suitable for use with CSS.
    // Returns false if the input is not in the correct format.
    // Example:
    // $h = RGB2Hex(array(255,0,255));
    // if (!$h) { error };

    // Make sure the input is valid
    if(!$this->isRGB($rgb)) return false;

    $hex = "";
    for($i=0; $i < 3; $i++) {
      // Convert the decimal digit to hex
      $hexDigit = dechex($rgb[$i]);
      // Add a leading zero if necessary
      if(strlen($hexDigit) == 1) $hexDigit = "0" . $hexDigit;
      // Append to the hex string
      $hex .= $hexDigit;
    }
    // Return the complete hex string
    return $hex;
  }

  //--------------------------------------------------
  function isHex($hex){
    // Returns true if $hex is a valid CSS hex color.
    // The "#" character at the start is optional.
    // Regexp for a valid hex digit
    $d = '[a-fA-F0-9]';
    // Make sure $hex is valid
    if (preg_match("/^#?$d$d$d$d$d$d\$/", $hex) || preg_match("/^#?$d$d$d\$/", $hex)) return true;
    return false;
  }

  //--------------------------------------------------
  function isRGB($rgb){
    // Returns true if $rgb is an array with three valid
    // decimal color digits.

    if (!is_array($rgb) || count($rgb) != 3) return false;

    for($i=0; $i < 3; $i++) {
      // Get the decimal digit
      $dec = intval($rgb[$i]);
      // Make sure the decimal digit is between 0 and 255
      if (!is_int($dec) || $dec < 0 || $dec > 255) return false;
    }
    return true;
  }


}
?>