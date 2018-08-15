<?php
// ========================================
// CONFIGURATION
// ========================================
$config__channel_id = "UCoLrcjPV5PbUrUyXq5mjc_A";


// ========================================
// STOP EDITING HERE
// ========================================
$html = "";

$url = "https://www.youtube.com/feeds/videos.xml?channel_id=" . $config__channel_id;

$url = "./sample.xml";

$xml = simplexml_load_file($url);

if ($xml) {
  $length = count($xml->entry);

  for($i = 0; $i < $length; $i++){
    $entry = $xml->entry[$i];

    // Look for these 2 strings in the title
    $isFound = preg_match('/\bCondensed\b.*\bBOS\b/', $entry->title, $matches);

    if ($isFound) {
      $title = $entry->title;
      $link = $entry->link;

      // The link node looks like:
      // <link rel="alternate" href="https://www.youtube.com/watch?v=ycq3VVT-_bQ"/>
      // Hence, we need to extract the attributes.
      if ($link->attributes()) {
        foreach($link->attributes() as $a => $b) {
          if ($a === "href") {
            $url = $b;
          }
        }
      }

      $html .= $title . "<br>" . $url . "<br><br>";
    } // end if is found
  }
}

// If we have something, send it by email
if ($html !== "") {
  // Send the mail
  echo $html;
}