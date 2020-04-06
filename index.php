<?php

$filename = './all.csv';
$h = fopen($filename, 'r');
if ($h) {
    $i = 0;

    while (( $data = fgetcsv($h) ) !== FALSE) {


        $myvalue = str_pad($data[0], 6, "0", STR_PAD_LEFT);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://www.legrand.fr/services/suggest?s=' . $myvalue,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        ]);

        $resp = curl_exec($curl);
        curl_close($curl);

        //die();
        //load the html
        $myarry = json_decode($resp, true);

        $curl2 = curl_init();
        curl_setopt_array($curl2, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://www.legrand.fr/' . $myarry['datas']['suggestPro']['elements'][0]['href'],
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        ]);

        $resp2 = curl_exec($curl2);
        curl_close($curl2);
        $doc = new DOMDocument();
        $image_link = '';
        libxml_use_internal_errors(true);
        $doc->loadHTML($resp2);
        echo "----------------time to parse [" . $myvalue . "]------------------ " . "\n";
        //discard white space
        $doc->preserveWhiteSpace = false;
        $xpath = new \DOMXpath($doc);
        $articles = $xpath->query('//div[@class="block-container-item-view"]//img[1]/@src');
        $image_link = '';
        if (!is_null($articles)) {
            foreach ($articles as $container) {
                $image_link = $container->value;
            }
        }

        if (!empty($image_link)) {
            if (!file_exists('images/' . $myvalue)) {
                mkdir('images/' . $myvalue, 0777, true);
            }
            $link = explode("?", $image_link)[0];
            $destdir = 'images/' . $myvalue;
            $img = file_get_contents($link);
            $file_name = basename($link);
            $save = file_put_contents($destdir . "/" . $file_name, $img);
            if ($save) {
                echo "File downloaded successfully";
            } else {
                echo "File failed to save.";
            }
        } else {
            echo "---------------- Not found ------------------ " . "\n";
        }
    }
    fclose($h);
}
