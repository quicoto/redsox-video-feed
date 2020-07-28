<?php
// ========================================
// CONFIGURATION
// ========================================
$config__channel_id = "UCB7VFqP4qJd3ST4PjMEzApA";
$config__meta_key = "redsox_video_id";
$config__author_id = 1;
$config__category_id = 1611;

// ========================================
// STOP EDITING HERE
// ========================================
include_once("../wp-load.php");

// Will be used to send an email if new videos are found
$send_email = 0;

$url = "https://www.youtube.com/feeds/videos.xml?channel_id=" . $config__channel_id;

$xml = simplexml_load_file($url);

// Make sure we could retrieve the feed
if ($xml) {
  $length = count($xml->entry);

  for($i = 0; $i < $length; $i++){
    $entry = $xml->entry[$i];

    // Look for these 2 strings in the title
    $isFound = preg_match('/\bCondensed\b.*\bRed Sox\b/', $entry->title, $matches);

    if ($isFound) {
      $entry_id = $entry->id;
      $title = $entry->title;
      $link = $entry->link;
      $published = $entry->published;

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

      // WordPress integration
      // Prepare search query
      $args = array(
        'meta_query' => array(
          array(
            'key' => $config__meta_key,
            'value' => (string)$entry_id,
            'compare' => 'LIKE'
          ),
        'posts_per_page' => 1
        )
      );

      // Check if we have any stored video with the same ID
      $query = new WP_Query( $args );

      // No posts found, means it's a new video. Let's insert it.
      if( !$query->have_posts() ) {
        $date_time = new DateTime($published);
        $post_date = $date_time->format('Y-m-d H:i:s');

        // Prepare post data
        $post = array(
          'post_author'    => $config__author_id,
          'post_category'  => array($config__category_id),
          'post_status'    => 'publish',
          'post_title'     => wp_strip_all_tags((string)$title),
          'post_content'   => $url,
          'post_type'      => 'post'
        );

        $post_id = wp_insert_post( $post );

        update_post_meta($post_id, $config__meta_key, (string)$entry_id);

        $send_email++;
      }
    } // end if is found
  }
}

if ($send_email > 0) {
  $video_string = "video";

  if ($send_email > 1) {
    $video_string = "videos";
  }

  mail("torres.rick@gmail.com", $send_email . " New Red Sox " . $video_string, "https://www.quicoto.com/twitter/?cat=1611");
}
