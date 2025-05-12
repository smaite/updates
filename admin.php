<?php
// Simple security check - you can enhance this as needed
$adminPassword = 'admin'; // Change this to a secure password
$isAuthenticated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $adminPassword) {
        $isAuthenticated = true;
        setcookie('admin_auth', 'true', time() + 3600, '/');
    }
} elseif (isset($_COOKIE['admin_auth']) && $_COOKIE['admin_auth'] === 'true') {
    $isAuthenticated = true;
}

if (!$isAuthenticated) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>NepalBooks Admin Login</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #1a1b1e 0%, #2c2e33 100%);
                color: #fff;
            }
            .login-container {
                background: rgba(26, 27, 30, 0.9);
                padding: 2rem;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
            }
            h1 {
                color: #1c7ed6;
                margin: 0 0 1.5rem 0;
                text-align: center;
            }
            .form-group {
                margin-bottom: 1rem;
            }
            label {
                display: block;
                margin-bottom: 0.5rem;
                color: #adb5bd;
            }
            input {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 4px;
                background: rgba(37, 38, 43, 0.8);
                color: #fff;
                font-size: 1rem;
                box-sizing: border-box;
            }
            button {
                width: 100%;
                padding: 0.75rem;
                background: #1c7ed6;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 1rem;
                cursor: pointer;
                transition: background-color 0.2s;
            }
            button:hover {
                background: #1971c2;
            }
            .error {
                color: #ff4d4f;
                margin-top: 1rem;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>NepalBooks Admin</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Login</button>
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <div class="error">Invalid password</div>
                <?php endif; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NepalBooks Admin Dashboard</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1b1e 0%, #2c2e33 100%);
            color: #fff;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        h1 {
            color: #1c7ed6;
            margin: 0;
        }
        .card {
            background: rgba(26, 27, 30, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #adb5bd;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            background: rgba(37, 38, 43, 0.8);
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box;
        }
        button {
            padding: 0.75rem 1.5rem;
            background: #1c7ed6;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background: #1971c2;
        }
        .error {
            color: #ff4d4f;
            margin-top: 1rem;
            display: none;
        }
        .success {
            color: #52c41a;
            margin-top: 1rem;
            display: none;
        }
        .releases-list {
            margin-top: 2rem;
        }
        .release-item {
            background: rgba(37, 38, 43, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .release-item h3 {
            margin: 0 0 0.5rem 0;
            color: #1c7ed6;
        }
        .release-item p {
            margin: 0.5rem 0;
            color: #adb5bd;
        }
        .channel {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
        .channel.stable {
            background: #2b8a3e;
            color: white;
        }
        .channel.beta {
            background: #e67700;
            color: white;
        }
        .logout-btn {
            padding: 0.5rem 1rem;
            background: #e03131;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .logout-btn:hover {
            background: #c92a2a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NepalBooks Admin Dashboard</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <div class="card">
            <h2>Publish New Release</h2>
            <form id="publishForm">
                <div class="form-group">
                    <label for="version">Version (without v prefix)</label>
                    <input type="text" id="version" name="version" required placeholder="1.0.0">
                </div>

                <div class="form-group">
                    <label for="channel">Channel</label>
                    <select id="channel" name="channel" required>
                        <option value="stable">Stable</option>
                        <option value="beta">Beta</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Release Notes</label>
                    <textarea id="notes" name="notes" rows="4" required placeholder="Enter release notes, changes, features, etc."></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="mandatory" name="mandatory">
                        Mandatory update (users must install this update)
                    </label>
                </div>

                <h3>Download URLs</h3>
                <div class="form-group">
                    <label for="winUrl">Windows Download URL</label>
                    <input type="url" id="winUrl" name="winUrl" placeholder="https://example.com/NepalBooks-1.0.0-win.exe">
                </div>

                <div class="form-group">
                    <label for="macUrl">macOS Download URL</label>
                    <input type="url" id="macUrl" name="macUrl" placeholder="https://example.com/NepalBooks-1.0.0-mac.dmg">
                </div>

                <div class="form-group">
                    <label for="linuxUrl">Linux Download URL</label>
                    <input type="url" id="linuxUrl" name="linuxUrl" placeholder="https://example.com/NepalBooks-1.0.0-linux.AppImage">
                </div>

                <button type="submit">Publish Release</button>
                <div id="error" class="error"></div>
                <div id="success" class="success"></div>
            </form>
        </div>

        <div class="releases-list">
            <h2>Recent Releases</h2>
            <div id="releases"></div>
        </div>
    </div>

    <script>
        function logout() {
            document.cookie = 'admin_auth=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            window.location.reload();
        }

        // Load releases
        async function loadReleases() {
            try {
                const response = await fetch('/api/updates/releases/all');
                if (response.ok) {
                    const releases = await response.json();
                    const releasesDiv = document.getElementById('releases');
                    releasesDiv.innerHTML = releases.map(release => `
                        <div class="release-item">
                            <h3>v${release.tag_name.replace('v', '')}</h3>
                            <span class="channel ${release.channel}">${release.channel}</span>
                            <p>${release.body.replace(/\n/g, '<br>')}</p>
                            <p>Published: ${new Date(release.published_at).toLocaleString()}</p>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Failed to load releases:', error);
            }
        }

        // Handle form submission
        document.getElementById('publishForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('error');
            const successDiv = document.getElementById('success');
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            const version = document.getElementById('version').value;
            const notes = document.getElementById('notes').value;
            const channel = document.getElementById('channel').value;
            const mandatory = document.getElementById('mandatory').checked;
            const winUrl = document.getElementById('winUrl').value;
            const macUrl = document.getElementById('macUrl').value;
            const linuxUrl = document.getElementById('linuxUrl').value;

            if (!winUrl && !macUrl && !linuxUrl) {
                errorDiv.textContent = 'At least one download URL is required';
                errorDiv.style.display = 'block';
                return;
            }

            const assets = [];
            if (winUrl) assets.push({ platform: 'win', browser_download_url: winUrl });
            if (macUrl) assets.push({ platform: 'mac', browser_download_url: macUrl });
            if (linuxUrl) assets.push({ platform: 'linux', browser_download_url: linuxUrl });

            try {
                const response = await fetch('/api/admin/publish', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        tag_name: `v${version}`,
                        name: `NepalBooks v${version}`,
                        body: notes,
                        channel,
                        mandatory,
                        published_at: new Date().toISOString(),
                        assets
                    })
                });

                if (response.ok) {
                    successDiv.textContent = `Version ${version} published successfully to ${channel} channel`;
                    successDiv.style.display = 'block';
                    document.getElementById('publishForm').reset();
                    loadReleases();
                } else {
                    const error = await response.json();
                    errorDiv.textContent = error.error || 'Failed to publish release';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = 'Failed to connect to server';
                errorDiv.style.display = 'block';
            }
        });

        // Load releases on page load
        loadReleases();
    </script>
</body>
</html> 