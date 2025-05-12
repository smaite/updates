<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$releasesDir = __DIR__ . '/releases';
$latestStableFile = $releasesDir . '/latest-stable.json';
$latestBetaFile = $releasesDir . '/latest-beta.json';

// Create releases directory if it doesn't exist
if (!file_exists($releasesDir)) {
    mkdir($releasesDir, 0777, true);
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request path from either REQUEST_URI or path parameter
$requestPath = '';
if (isset($_GET['path'])) {
    $requestPath = $_GET['path'];
} else {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestPath = trim($requestPath, '/');
}

// Handle different routes
switch ($requestPath) {
    case 'api/updates/latest':
        handleLatestRelease();
        break;
    case 'api/updates/latest/stable':
        handleLatestStable();
        break;
    case 'api/updates/latest/beta':
        handleLatestBeta();
        break;
    case 'api/updates/releases/all':
        handleAllReleases();
        break;
    case 'api/admin/publish':
        handlePublishRelease();
        break;
    default:
        if (preg_match('/^api\/updates\/releases\/(stable|beta)$/', $requestPath, $matches)) {
            handleChannelReleases($matches[1]);
        } elseif (preg_match('/^api\/updates\/version\/([0-9.]+)$/', $requestPath, $matches)) {
            handleVersionRelease($matches[1]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'path' => $requestPath]);
        }
}

function handleLatestRelease() {
    global $latestStableFile;
    if (file_exists($latestStableFile)) {
        echo file_get_contents($latestStableFile);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No releases found']);
    }
}

function handleLatestStable() {
    global $latestStableFile;
    if (file_exists($latestStableFile)) {
        echo file_get_contents($latestStableFile);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No stable releases found']);
    }
}

function handleLatestBeta() {
    global $latestBetaFile;
    if (file_exists($latestBetaFile)) {
        echo file_get_contents($latestBetaFile);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No beta releases found']);
    }
}

function handleChannelReleases($channel) {
    global $releasesDir;
    $releases = [];
    
    foreach (glob($releasesDir . "/*.json") as $file) {
        $content = json_decode(file_get_contents($file), true);
        if ($content && isset($content['channel']) && $content['channel'] === $channel) {
            $releases[] = $content;
        }
    }
    
    // Sort releases by version (newest first)
    usort($releases, function($a, $b) {
        return version_compare($b['tag_name'], $a['tag_name']);
    });
    
    echo json_encode($releases);
}

function handleAllReleases() {
    global $releasesDir;
    $releases = [];
    
    foreach (glob($releasesDir . "/*.json") as $file) {
        $content = json_decode(file_get_contents($file), true);
        if ($content) {
            $releases[] = $content;
        }
    }
    
    // Sort releases by version (newest first)
    usort($releases, function($a, $b) {
        return version_compare($b['tag_name'], $a['tag_name']);
    });
    
    echo json_encode($releases);
}

function handleVersionRelease($version) {
    global $releasesDir;
    $versionFile = $releasesDir . "/{$version}.json";
    
    if (file_exists($versionFile)) {
        echo file_get_contents($versionFile);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Version not found']);
    }
}

function handlePublishRelease() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }

    // Validate required fields
    $requiredFields = ['tag_name', 'name', 'body', 'channel', 'published_at', 'assets'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: {$field}"]);
            return;
        }
    }

    global $releasesDir;
    $version = trim($data['tag_name'], 'v');
    $releaseFile = $releasesDir . "/{$version}.json";
    $channel = $data['channel'];
    $latestFile = $releasesDir . "/latest-{$channel}.json";

    // Save release file
    file_put_contents($releaseFile, json_encode($data, JSON_PRETTY_PRINT));

    // Update latest file for the channel
    file_put_contents($latestFile, json_encode($data, JSON_PRETTY_PRINT));

    echo json_encode(['message' => 'Release published successfully']);
}
?> 