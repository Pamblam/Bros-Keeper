<?php

/**
 * Anonymous image upload using imgur API
 * @param $client_id String - The client ID (See: https://imgur.com/account/settings/apps)
 * @param $img_path String - The path to image to be uploaded (or tempname of uploaded image)
 * @return String|Bool - false on failure or URL of uploaded image on success.
 * https://gist.github.com/Pamblam/210f3870d28149428d8ea6d3ccf55013
 */
function imgur_anon_upload($client_id, $img_path){
  if(!file_exists($img_path) || !is_readable($img_path)) return false;
  $handle = fopen($img_path, 'r');
  $data = fread($handle, filesize($img_path));
  $pvars   = array('image' => base64_encode($data));
  $timeout = 30;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image');
  curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $pvars);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  $out = curl_exec($curl);
  curl_close ($curl);
  $pms = json_decode($out,true);
  if(!$pms || !$pms['success']) return false;
  return $pms['data']['link'];
}