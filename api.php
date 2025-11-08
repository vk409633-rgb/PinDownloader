<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function extractPinterestVideo($url) {
    // Extract pin ID
    if (preg_match('/pinterest\.com\/pin\/(\d+)/', $url, $matches)) {
        $pinId = $matches[1];
    } else {
        return ['success' => false, 'error' => 'Invalid Pinterest URL'];
    }
    
    // Method 1: Try Pinterest API
    $apiUrl = "https://www.pinterest.com/resource/PinResource/get/?source_url=/pin/{$pinId}/&data=" . urlencode(json_encode([
        'options' => [
            'field_set_key' => 'unauth_react_main_pin',
            'id' => $pinId
        ],
        'context' => new stdClass()
    ]));
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: https://www.pinterest.com/'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['resource_response']['data'])) {
            $pinData = $data['resource_response']['data'];
            
            // Check videos_v2
            if (isset($pinData['videos_v2']['video_list'])) {
                $videos = $pinData['videos_v2']['video_list'];
                $qualities = ['V_720P', 'V_HLSV4', 'V_HLSV3_MOBILE', 'V_ORIGINAL'];
                
                foreach ($qualities as $quality) {
                    if (isset($videos[$quality]['url'])) {
                        return [
                            'success' => true,
                            'videoUrl' => $videos[$quality]['url'],
                            'title' => $pinData['title'] ?? $pinData['grid_title'] ?? 'Pinterest Video',
                            'thumbnail' => $pinData['images']['orig']['url'] ?? null
                        ];
                    }
                }
            }
            
            // Check story pins
            if (isset($pinData['story_pin_data']['pages'])) {
                foreach ($pinData['story_pin_data']['pages'] as $page) {
                    if (isset($page['blocks'])) {
                        foreach ($page['blocks'] as $block) {
                            if (isset($block['video']['video_list'])) {
                                $videos = $block['video']['video_list'];
                                $qualities = ['V_720P', 'V_HLSV4', 'V_HLSV3_MOBILE'];
                                
                                foreach ($qualities as $quality) {
                                    if (isset($videos[$quality]['url'])) {
                                        return [
                                            'success' => true,
                                            'videoUrl' => $videos[$quality]['url'],
                                            'title' => $pinData['title'] ?? 'Pinterest Video',
                                            'thumbnail' => $pinData['images']['orig']['url'] ?? null
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Method 2: Scrape the page
    $pageUrl = "https://www.pinterest.com/pin/{$pinId}/";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $pageUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if ($html) {
        // Look for video URLs in the HTML
        $patterns = [
            '/\"url\":\"(https?:[^\"]+\.mp4[^\"]*)\"/i',
            '/https:\\\\/\\\\/[^\"\\s]+\.pinimg\.com\\\\/[^\"\\s]+\.mp4/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $videoUrl = str_replace('\\/', '/', $matches[1] ?? $matches[0]);
                $videoUrl = str_replace('\u002F', '/', $videoUrl);
                
                if (strpos($videoUrl, '.mp4') !== false) {
                    return [
                        'success' => true,
                        'videoUrl' => $videoUrl,
                        'title' => 'Pinterest Video',
                        'thumbnail' => null
                    ];
                }
            }
        }
    }
    
    return [
        'success' => false,
        'error' => 'Unable to extract video from this Pinterest URL. The pin may not contain a video, or Pinterest has blocked the request.'
    ];
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $url = $_POST['url'] ?? $_GET['url'] ?? '';
    
    if (empty($url)) {
        echo json_encode(['success' => false, 'error' => 'No URL provided']);
        exit;
    }
    
    $result = extractPinterestVideo($url);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
