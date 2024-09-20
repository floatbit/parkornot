<?php

if (isset($_POST['image'])) {
    $imageData = $_POST['image'];
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageContent = base64_decode($imageData);

    // Generate a unique ID
    $unique_id = uniqid();

    // Create a new post of post type 'shot'
    $post_id = wp_insert_post([
        'post_title' => $unique_id,
        'post_type' => 'shot',
        'post_status' => 'publish',
        'post_author' => 0, // Anonymous author
    ]);

    // Save the image in the media library
    $upload_dir = wp_upload_dir();
    $image_name = $unique_id . '.png';
    $image_path = $upload_dir['path'] . '/' . $image_name;
    file_put_contents($image_path, $imageContent);

    // Resize the image
    function resizeImage($file, $maxWidth, $maxHeight) {
        list($origWidth, $origHeight) = getimagesize($file);
        $width = $origWidth;
        $height = $origHeight;

        if ($width > $maxWidth) {
            $height = ($maxWidth / $width) * $height;
            $width = $maxWidth;
        }

        if ($height > $maxHeight) {
            $width = ($maxHeight / $height) * $width;
            $height = $maxHeight;
        }

        $src = imagecreatefrompng($file); // Assuming PNG as the format
        $dst = imagecreatetruecolor($width, $height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        return $dst;
    }

    // Resize the image
    $resizedImage = resizeImage($image_path, 800, 600); // Resize to fit within 800x600

    // Save the resized image to a new file
    $resizedImageName = $unique_id . '_resized.png';
    $resizedImagePath = $upload_dir['path'] . '/' . $resizedImageName;
    imagepng($resizedImage, $resizedImagePath, 8); // 0-9 quality for PNG, 0 is no compression

    // Add the resized image to the media library
    $attachment = [
        'guid' => $upload_dir['url'] . '/' . $resizedImageName,
        'post_mime_type' => 'image/png',
        'post_title' => sanitize_file_name($resizedImageName),
        'post_content' => '',
        'post_status' => 'inherit'
    ];

    $attachment_id = wp_insert_attachment($attachment, $resizedImagePath, $post_id);

    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attachment_id, $resizedImagePath);
    wp_update_attachment_metadata($attachment_id, $attach_data);

    // Set the image as the featured image of the post
    set_post_thumbnail($post_id, $attachment_id);

    // Get current date, time, and day
    $date = $_POST['date'];
    $time = $_POST['time'];
    $day = $_POST['day'];

    // save acf
    update_field('field_66ecddb18f439', $date, $post_id);
    update_field('field_66ecddbf8f43a', $time, $post_id);
    update_field('field_66ecddac8f438', $day, $post_id);

    // format items
    $formatted_time = date('g:ia', strtotime($time));
    $formatted_date = date('F j, Y', strtotime($date));

    // do api response
    // Updated prompt to incorporate all suggestions
    $prompt = "It's $day, $date, $formatted_time . There may be more than one sign in the image, so examine each one carefully. Combine the conditions from all signs and answer this: can I park here right now? Consider the exact current time when deciding if a parking restriction applies. Start with YES, NO, or MAYBE in uppercase, and include whether payment is required. In a smartass tone, explain why in 3 sentences or less.";

      // Create the JSON payload
      $data = [
          "model" => "gpt-4o",
          "messages" => [
              [
                  "role" => "user",
                  "content" => [
                      [
                          "type" => "text",
                          "text" => $prompt
                      ],
                      [
                          "type" => "image_url",
                          "image_url" => [
                              "url" => wp_get_attachment_image_url($attachment_id, 'full')
                              //"url" => "https://parkornot.com/wp-content/uploads/2024/09/66ed7e7b4bf25_resized.png"
                          ]
                      ]
                  ]
              ]
          ],
          "max_tokens" => 2000
      ];
  
      // API endpoint and API key
      $url = 'https://api.openai.com/v1/chat/completions';
      $apiKey = CHATGPT_API_KEY;
  
      // Initialize cURL
      $ch = curl_init($url);
  
      // Set cURL options
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'Authorization: Bearer ' . $apiKey
      ]);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  
      // Execute cURL request and get the response
      $response = curl_exec($ch);
// $json = json_decode($response, true);
// pd($json);
// die;
      // Save the response to acf field chatgpt_response
      if ($response !== false) {
        echo $response;
        echo '<br>';
        echo '<br>';
        echo '<br>';
          $json = json_decode($response, true);
          if (isset($json['choices'][0]['message']['content'])) {
            $chatgpt_response = $json['choices'][0]['message']['content'];
            $chatgpt_response_clean = preg_replace('/\s+/', ' ', trim($chatgpt_response));
            update_field('field_66e98115ce90c', $chatgpt_response_clean, $post_id);
          }
      }
      
      // Redirect to the single shot page
      $redirect_url = get_permalink($post_id);
      echo '<script type="text/javascript">';
      echo 'window.location.href="' . $redirect_url . '";';
      echo '</script>';
      exit;
}
?>
