<?php
@error_reporting(0);
@session_start();

define('PASSWORD_HASH', '$2b$12$GkqIIDan04pJc9PpLS24Su/wPdGhrgb5F6uam89UsaItjabqYkTJ6');
define('APP_TITLE', 'File Manager');

// --- Helper Functions ---
function get_session($name) {
    return $_SESSION[$name] ?? false;
}
function set_session($name, $val) {
    $_SESSION[$name] = $val;
}
function get_post($name) {
    return $_POST[$name] ?? false;
}
function get_get($name) {
    return $_GET[$name] ?? false;
}
function get_files($name) {
    return $_FILES[$name] ?? false;
}
function redirect($url) {
    header("Location: $url");
    exit();
}
function get_self() {
    return $_SERVER['PHP_SELF'];
}

function filesize_convert($bytes) {
    $label = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++);
    return (round($bytes, 2) . " " . $label[$i]);
}

// --- Core Logic Functions ---
function get_path() {
    $path = defined('START_PATH') ? START_PATH : __DIR__;
    $requested_path = get_get('path');
    if ($requested_path) {
        $real_path = realpath($requested_path);
        if ($real_path !== false) {
            $path = $real_path;
        }
    }
    return str_replace('\\', '/', $path);
}

function get_dir_list($path) {
    if (!is_dir($path) || !is_readable($path)) return [];
    
    $dir = scandir($path);
    $files = [];
    $dirs = [];
    
    foreach ($dir as $d) {
        if ($d == '.' || $d == '..') continue;
        
        $p = $path . '/' . $d;
        $is_file = is_file($p);
        
        // Get owner safely
        $owner = fileowner($p);
        if (function_exists('posix_getpwuid')) {
            $owner_info = posix_getpwuid($owner);
            $owner = $owner_info['name'] ?? $owner;
        }
        
        $item = [
            'name' => $d,
            'path' => $p,
            'is_dir' => is_dir($p),
            'is_file' => $is_file,
            'size' => $is_file ? filesize_convert(filesize($p)) : '--',
            'modified' => date("M d Y H:i:s", filemtime($p)),
            'perms' => substr(sprintf('%o', fileperms($p)), -4),
            'owner' => $owner,
        ];
        
        // Separate directories and files for sorting
        if ($item['is_dir']) {
            $dirs[] = $item;
        } else {
            $files[] = $item;
        }
    }
    
    // Sort directories alphabetically
    usort($dirs, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    // Sort files alphabetically
    usort($files, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    // Combine: directories first, then files
    return array_merge($dirs, $files);
}

function save_file($path, $content) {
    if (is_file($path) && is_writable($path)) {
        return file_put_contents($path, $content) !== false;
    }
    return false;
}

function upload_file($path, $file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return false;
    $name = basename($file['name']);
    $target_path = $path . '/' . $name;
    if (!file_exists($target_path)) {
        return move_uploaded_file($file["tmp_name"], $target_path);
    }
    return false;
}

function rename_item($old_path, $new_name) {
    // Sanitize new name
    $new_name = trim($new_name);
    if (empty($new_name)) {
        return false;
    }
    
    // Remove any path components from the new name to prevent directory traversal
    $new_name = basename($new_name);
    
    // Get directory of the old path
    $dir = dirname($old_path);
    
    // Create new path
    $new_path = $dir . '/' . $new_name;
    
    // Normalize paths
    $old_path = str_replace('\\', '/', $old_path);
    $new_path = str_replace('\\', '/', $new_path);
    
    // Check if old path exists
    if (!file_exists($old_path)) {
        return false;
    }
    
    // Check if new name is the same as old name
    if (basename($old_path) === $new_name) {
        return true; // Nothing to rename
    }
    
    // Check if new path already exists
    if (file_exists($new_path)) {
        return false;
    }
    
    // Check if we have permission to rename
    if (!is_writable($dir)) {
        return false;
    }
    
    // Attempt to rename
    return @rename($old_path, $new_path);
}

// --- View Functions ---
function render_header($is_login = false) {
    $body_class = $is_login ? 'login-page' : '';
    echo '<!DOCTYPE html><html><head><title>'.APP_TITLE.'</title><style>
    /* Magenta Theme */
    :root {
        --magenta-primary: #9C27B0;
        --magenta-dark: #7B1FA2;
        --magenta-light: #E1BEE7;
        --magenta-bg: #FCE4EC;
        --magenta-accent: #FF4081;
        --text-dark: #333;
        --text-light: #666;
        --border-color: #E1C4E9;
        --success-bg: #E8F5E9;
        --success-text: #2E7D32;
        --error-bg: #FFEBEE;
        --error-text: #C62828;
        --table-hover: #F3E5F5;
    }
    
    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #FCE4EC 0%, #F3E5F5 100%);
        color: var(--text-dark);
        margin: 0;
        line-height: 1.6;
    }
    
    .container {
        width: 85%;
        margin: 20px auto;
        background: white;
        padding: 25px;
        box-shadow: 0 6px 20px rgba(156, 39, 176, 0.15);
        border-radius: 12px;
        border-left: 5px solid var(--magenta-primary);
    }
    
    h1 {
        color: var(--magenta-primary);
        border-bottom: 2px solid var(--magenta-light);
        padding-bottom: 10px;
        margin-top: 0;
    }
    
    h2 {
        color: var(--magenta-dark);
    }
    
    h3 {
        color: var(--magenta-primary);
        background: var(--magenta-bg);
        padding: 10px 15px;
        border-radius: 6px;
        border-left: 4px solid var(--magenta-accent);
    }
    
    h4 {
        color: var(--magenta-dark);
        margin-bottom: 15px;
    }
    
    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border-radius: 8px;
        overflow: hidden;
    }
    
    th {
        background: linear-gradient(to right, var(--magenta-primary), var(--magenta-dark));
        color: white;
        padding: 14px 12px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 14px;
        letter-spacing: 0.5px;
    }
    
    td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
    }
    
    tr:hover {
        background: var(--table-hover);
        transition: background 0.2s;
    }
    
    a {
        color: var(--magenta-primary);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    a:hover {
        color: var(--magenta-accent);
        text-decoration: underline;
    }
    
    .message {
        padding: 15px 20px;
        margin-bottom: 25px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }
    
    .msg-success {
        background: var(--success-bg);
        color: var(--success-text);
        border-left: 4px solid #4CAF50;
    }
    
    .msg-error {
        background: var(--error-bg);
        color: var(--error-text);
        border-left: 4px solid #F44336;
    }
    
    .actions a {
        display: inline-block;
        margin-right: 8px;
        padding: 4px 10px;
        background: var(--magenta-light);
        border-radius: 4px;
        font-size: 13px;
    }
    
    .actions a:hover {
        background: var(--magenta-primary);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(156, 39, 176, 0.3);
    }
    
    body.login-page {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: linear-gradient(135deg, var(--magenta-primary) 0%, var(--magenta-dark) 100%);
    }
    
    .login-box {
        background: white;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        width: 360px;
        text-align: center;
    }
    
    .login-box h2 {
        margin-bottom: 25px;
        color: var(--magenta-primary);
        font-size: 28px;
    }
    
    .login-box input[type="password"] {
        width: 100%;
        padding: 14px;
        margin-bottom: 20px;
        border: 2px solid var(--magenta-light);
        border-radius: 8px;
        box-sizing: border-box;
        font-size: 16px;
        transition: border 0.3s;
    }
    
    .login-box input[type="password"]:focus {
        border-color: var(--magenta-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(156, 39, 176, 0.1);
    }
    
    .login-box input[type="submit"] {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(to right, var(--magenta-primary), var(--magenta-dark));
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        letter-spacing: 0.5px;
    }
    
    .login-box input[type="submit"]:hover {
        background: linear-gradient(to right, var(--magenta-dark), #6A1B9A);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(156, 39, 176, 0.4);
    }
    
    .login-box input[type="submit"]:active {
        transform: translateY(0);
    }
    
    .rename-form {
        display: inline-block;
    }
    
    .rename-form input {
        width: 150px;
        padding: 6px 10px;
        margin-right: 5px;
        border: 2px solid var(--magenta-light);
        border-radius: 4px;
        transition: border 0.3s;
    }
    
    .rename-form input:focus {
        border-color: var(--magenta-primary);
        outline: none;
    }
    
    .rename-form button {
        padding: 6px 14px;
        margin-right: 5px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .rename-form button[type="submit"] {
        background: var(--magenta-primary);
        color: white;
    }
    
    .rename-form button[type="submit"]:hover {
        background: var(--magenta-dark);
    }
    
    .rename-form button[type="button"] {
        background: #f5f5f5;
        color: var(--text-light);
    }
    
    .rename-form button[type="button"]:hover {
        background: #e0e0e0;
    }
    
    hr {
        border: none;
        height: 1px;
        background: linear-gradient(to right, transparent, var(--magenta-light), transparent);
        margin: 25px 0;
    }
    
    /* Action Forms */
    form {
        margin-bottom: 15px;
    }
    
    form label {
        display: block;
        margin-bottom: 5px;
        color: var(--magenta-dark);
        font-weight: 500;
    }
    
    form input[type="text"],
    form input[type="password"],
    form input[type="file"] {
        padding: 10px 14px;
        border: 2px solid var(--magenta-light);
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    form input[type="text"]:focus,
    form input[type="password"]:focus {
        border-color: var(--magenta-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(156, 39, 176, 0.1);
    }
    
    form input[type="submit"],
    form button[type="submit"] {
        padding: 10px 20px;
        background: linear-gradient(to right, var(--magenta-primary), var(--magenta-dark));
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
        margin-left: 8px;
    }
    
    form input[type="submit"]:hover,
    form button[type="submit"]:hover {
        background: linear-gradient(to right, var(--magenta-dark), #6A1B9A);
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(156, 39, 176, 0.3);
    }
    
    /* Progress Bar */
    #upload-progress-container {
        width: 100%;
        background-color: var(--magenta-light);
        border-radius: 8px;
        margin-top: 10px;
        overflow: hidden;
        height: 24px;
    }
    
    #upload-progress {
        height: 100%;
        background: linear-gradient(to right, var(--magenta-primary), var(--magenta-accent));
        text-align: center;
        color: white;
        line-height: 24px;
        font-weight: 600;
        transition: width 0.3s;
    }
    
    /* Breadcrumbs */
    .breadcrumbs {
        background: var(--magenta-bg);
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid var(--magenta-primary);
    }
    
    .breadcrumbs a {
        color: var(--magenta-dark);
        font-weight: 500;
    }
    
    .breadcrumbs a:hover {
        color: var(--magenta-accent);
    }
    
    /* File icons */
    td a:before {
        margin-right: 8px;
        font-size: 16px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .container {
            width: 95%;
            padding: 15px;
        }
        
        th, td {
            padding: 8px;
            font-size: 14px;
        }
        
        .actions a {
            display: block;
            margin-bottom: 5px;
        }
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--magenta-bg);
    }
    
    ::-webkit-scrollbar-thumb {
        background: var(--magenta-primary);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: var(--magenta-dark);
    }
    </style></head><body class="'.$body_class.'">';
    if (!$is_login) {
        echo '<div class="container"><h1>'.APP_TITLE.'</h1>';
    }
}

function render_footer($is_login = false) {
    if (!$is_login) {
        echo '</div>';
    }
    echo '</body></html>';
}

function render_login($error = false) {
    render_header(true);
    echo '<div class="login-box">';
    echo '<h2>'.APP_TITLE.'</h2>';
    if($error) echo "<div class='message msg-error'>Invalid Password</div>";
    echo '<form method="POST"><input type="password" name="pass" placeholder="Enter Password" required autofocus> <input type="submit" name="login" value="Login"></form>';
    echo '</div>';
    render_footer(true);
}

function render_editor($path, $content) {
    render_header();
    echo '<div id="status-message" style="margin-bottom: 15px;"></div>';
    echo '<h2>Edit File: <span style="color:var(--magenta-accent)">'.basename($path).'</span></h2>';
    echo '<form id="editor-form" onsubmit="saveFile(); return false;">';
    echo '<input type="hidden" id="file-path" value="'.htmlspecialchars($path).'">';
    echo '<textarea id="file-content" style="width:100%;height:400px;padding:15px;border:2px solid var(--magenta-light);border-radius:8px;font-family:monospace;font-size:14px;">'.htmlentities($content).'</textarea><br><br>';
    echo '<button type="submit" style="padding:10px 25px;background:linear-gradient(to right, var(--magenta-primary), var(--magenta-dark));color:white;border:none;border-radius:6px;cursor:pointer;font-weight:500;">Save Changes</button>';
    echo ' <a href="?path='.urlencode(dirname($path)).'" style="padding:10px 20px;background:#f5f5f5;border-radius:6px;">Back to Manager</a>';
    echo <<<JS
    <script>
    function saveFile() {
        const path = document.getElementById('file-path').value;
        const content = document.getElementById('file-content').value;
        const statusDiv = document.getElementById('status-message');

        statusDiv.className = 'message';
        statusDiv.innerText = 'Saving...';

        const formData = new FormData();
        formData.append('action', 'save_ajax');
        formData.append('path', path);
        formData.append('content', content);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            statusDiv.innerText = data.message;
            statusDiv.className = 'message ' + (data.status === 'success' ? 'msg-success' : 'msg-error');
        })
        .catch(error => {
            console.error('Error:', error);
            statusDiv.innerText = 'An unexpected error occurred. Check console for details.';
            statusDiv.className = 'message msg-error';
        });
    }
    </script>
JS;
    render_footer();
}

function render_breadcrumbs($path) {
    $path = str_replace('\\', '/', $path);
    
    echo '<div class="breadcrumbs">';
    echo '<strong>Path:</strong> ';
    echo '<a href="?path=/" style="color:var(--magenta-primary);">/</a> ';
    echo '<span style="color:var(--text-light);">›</span> ';
    
    // Create clickable breadcrumbs
    $current_path = '';
    $parts = explode('/', trim($path, '/'));
    
    for ($i = 0; $i < count($parts); $i++) {
        if (empty($parts[$i])) continue;
        
        $current_path .= '/' . $parts[$i];
        echo '<a href="?path=' . urlencode($current_path) . '">' . htmlspecialchars($parts[$i]) . '</a>';
        
        if ($i < count($parts) - 1) {
            echo ' <span style="color:var(--text-light);">›</span> ';
        }
    }
    echo '</div>';
    
    // Show full path in a code block
    echo '<div style="margin: 10px 0; padding: 10px 15px; background: #f9f9f9; border: 1px solid var(--border-color); border-radius: 6px; font-family: monospace; font-size: 14px;">';
    echo '<strong>Full Path:</strong> <span style="color: var(--magenta-dark);">' . htmlspecialchars($path) . '</span>';
    echo '</div>';
}

function render_file_manager($path, $dir_list, $message = '') {
    render_header();
    if ($message) {
        $msg_type = strpos(strtolower($message), 'error') === false ? 'msg-success' : 'msg-error';
        echo "<div class='message $msg_type'>".htmlspecialchars(urldecode($message))."</div>";
    }

    // Breadcrumbs
    render_breadcrumbs($path);

    // Navigation buttons
    echo '<div style="margin-bottom:20px;">';
    $parent_path = dirname($path);
    if ($parent_path != $path && $path != '/') {
        echo '<a href="?path='.urlencode($parent_path).'" style="padding:8px 15px;background:var(--magenta-light);border-radius:6px;margin-right:10px;">[&larr; Back]</a> ';
    }
    echo '<a href="'.get_self().'" style="padding:8px 15px;background:var(--magenta-light);border-radius:6px;margin-right:10px;">[Home]</a> ';
    echo '<a href="?logout=1" style="padding:8px 15px;background:#FF4081;color:white;border-radius:6px;float:right;">[Logout]</a>';
    echo '</div>';
    
    // Action Forms
    echo '<hr><h4>📁 File Operations</h4>';
    echo '<div style="display:flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; margin-bottom: 25px;">';
    echo '<form method="POST"><label>📄 New File:</label><br><input type="text" name="filename" placeholder="filename.txt"><input type="submit" name="newfile" value="Create"></form>';
    echo '<form method="POST"><label>📁 New Directory:</label><br><input type="text" name="dirname" placeholder="directory-name"><input type="submit" name="newdir" value="Create"></form>';
    echo '<form id="upload-form"><label>⬆️ Upload File:</label><br><input type="file" id="file-input"><input type="submit" value="Upload"></form>';
    echo '</div>';

    // Progress bar and status
    echo '<div id="upload-status" style="margin-top: 15px; font-weight: bold; color: var(--magenta-dark);"></div>';
    echo '<div style="width: 100%; background-color: var(--magenta-light); border-radius: 5px; margin-top: 5px; display: none;" id="upload-progress-container"><div id="upload-progress" style="width: 0%; height: 24px; background: linear-gradient(to right, var(--magenta-primary), var(--magenta-accent)); text-align: center; color: white; border-radius: 5px; line-height: 24px; font-weight: 600;"></div></div>';
    echo '<hr>';

    // File Listing
    echo '<table><thead><tr><th>Name</th><th>Type</th><th>Size</th><th>Modified</th><th>Perms</th><th>Owner</th><th>Actions</th></tr></thead><tbody>';
    
    // Add ".." link for parent directory if not at root
    if ($path != '/' && $path != dirname($path)) {
        echo '<tr>';
        echo '<td><a href="?path='.urlencode(dirname($path)).'">📁 ..</a></td>';
        echo '<td><span style="background:#e8f5e9;color:#2e7d32;padding:3px 8px;border-radius:4px;font-size:12px;">DIR</span></td>';
        echo '<td>--</td>';
        echo '<td>--</td>';
        echo '<td>--</td>';
        echo '<td>--</td>';
        echo '<td class="actions">Parent Directory</td>';
        echo '</tr>';
    }
    
    foreach ($dir_list as $item) {
        echo '<tr>';
        $link = $item['is_dir'] 
            ? '?path='.urlencode($item['path']) 
            : '?edit='.urlencode($item['path']);
        $icon = $item['is_dir'] ? '📁' : '📄';
        $type = $item['is_dir'] ? 
            '<span style="background:#e8f5e9;color:#2e7d32;padding:3px 8px;border-radius:4px;font-size:12px;">DIR</span>' : 
            '<span style="background:#e3f2fd;color:#1565c0;padding:3px 8px;border-radius:4px;font-size:12px;">FILE</span>';
        
        echo '<td><a href="'.$link.'">'.$icon.' '.htmlspecialchars($item['name']).'</a></td>';
        echo '<td>'.$type.'</td>';
        echo '<td>'.$item['size'].'</td>';
        echo '<td>'.$item['modified'].'</td>';
        echo '<td><code style="background:var(--magenta-light);padding:2px 6px;border-radius:3px;">'.$item['perms'].'</code></td>';
        echo '<td>'.$item['owner'].'</td>';
        echo '<td class="actions" id="actions-'.md5($item['path']).'">';
        if ($item['is_file']) echo '<a href="?edit='.urlencode($item['path']).'">Edit</a> ';
        echo '<a href="#" onclick="showRenameForm(\''.htmlspecialchars($item['path']).'\', \''.htmlspecialchars($item['name']).'\', \''.md5($item['path']).'\'); return false;">Rename</a> ';
        echo '<a href="?delete='.urlencode($item['path']).'" onclick="return confirm(\'Are you sure you want to delete '.htmlspecialchars($item['name']).'?\');">Delete</a> ';
        if ($item['is_file']) echo '<a href="?download='.urlencode($item['path']).'">Download</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    if (empty($dir_list) && !isset($has_parent)) {
        echo '<tr><td colspan="7" style="text-align:center;padding:40px;color:#999;"><em>Directory is empty</em></td></tr>';
    }
    
    echo '</tbody></table>';

    echo <<<JS
    <script>
    function showRenameForm(path, name, elementId) {
        const actionsCell = document.getElementById('actions-' + elementId);
        const originalContent = actionsCell.innerHTML;
        
        const form = document.createElement('form');
        form.className = 'rename-form';
        form.method = 'POST';
        form.onsubmit = function() {
            if (!this.newname.value.trim()) {
                alert('Please enter a new name');
                return false;
            }
            return true;
        };
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'rename';
        input.value = path;
        form.appendChild(input);
        
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.name = 'newname';
        nameInput.value = name;
        form.appendChild(nameInput);
        
        const submit = document.createElement('button');
        submit.type = 'submit';
        submit.textContent = 'Save';
        form.appendChild(submit);
        
        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.textContent = 'Cancel';
        cancel.onclick = function() {
            actionsCell.innerHTML = originalContent;
        };
        form.appendChild(cancel);
        
        actionsCell.innerHTML = '';
        actionsCell.appendChild(form);
        nameInput.focus();
        nameInput.select();
    }
    
    const uploadForm = document.getElementById('upload-form');
    const fileInput = document.getElementById('file-input');
    const uploadStatus = document.getElementById('upload-status');
    const progressBar = document.getElementById('upload-progress');
    const progressContainer = document.getElementById('upload-progress-container');

    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const file = fileInput.files[0];
        if (!file) {
            uploadStatus.innerText = '⚠️ Please select a file to upload.';
            uploadStatus.style.color = '#FF9800';
            return;
        }
        
        progressContainer.style.display = 'block';
        const CHUNK_SIZE = 1024 * 1024; // 1MB chunks
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        let currentChunk = 0;

        function uploadChunk() {
            if (currentChunk >= totalChunks) {
                return;
            }

            const start = currentChunk * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunk = file.slice(start, end);
            
            const formData = new FormData();
            formData.append('action', 'upload_chunk');
            formData.append('chunk', chunk, file.name);
            formData.append('chunk_num', currentChunk);
            formData.append('total_chunks', totalChunks);
            formData.append('filename', file.name);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    uploadStatus.innerText = '❌ Error: ' + data.message;
                    uploadStatus.style.color = '#F44336';
                    progressBar.style.background = '#F44336';
                    return;
                }

                currentChunk++;
                const progress = Math.round((currentChunk / totalChunks) * 100);
                progressBar.style.width = progress + '%';
                progressBar.innerText = progress + '%';

                if (data.status === 'success') {
                    uploadStatus.innerText = '✅ ' + data.message;
                    uploadStatus.style.color = '#4CAF50';
                    progressBar.style.background = 'linear-gradient(to right, #4CAF50, #8BC34A)';
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    uploadStatus.innerText = '⏳ ' + data.message;
                    uploadStatus.style.color = 'var(--magenta-dark)';
                    uploadChunk(); // Send next chunk
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                uploadStatus.innerText = '❌ A critical error occurred during upload.';
                uploadStatus.style.color = '#F44336';
                progressBar.style.background = '#F44336';
            });
        }

        uploadStatus.innerText = '🚀 Starting upload for ' + file.name + '...';
        uploadStatus.style.color = 'var(--magenta-dark)';
        progressBar.style.width = '0%';
        progressBar.innerText = '0%';
        progressBar.style.background = 'linear-gradient(to right, var(--magenta-primary), var(--magenta-accent))';
        uploadChunk();
    });
    </script>
JS;

    render_footer();
}

// --- Main Controller ---

// 1. Logout
if (get_get('logout')) {
    unset($_SESSION['login']);
    redirect(get_self());
}

// 2. Authentication
if (!get_session('login')) {
    $login_error = false;
    if (get_post('login')) {
        $password = get_post('pass') ?? '';
        if ($password && password_verify($password, PASSWORD_HASH)) {
            set_session('login', true);
            redirect(get_self());
        } else {
            $login_error = true;
        }
    }
    render_login($login_error);
    exit;
}

// 3. Initialize
$path = get_path();
$message = $_GET['msg'] ?? '';

// 4. Handle Actions
$redirect_path = '?path='.urlencode($path);

// -- AJAX Actions --
if (get_post('action') === 'save_ajax') {
    header('Content-Type: application/json');
    $edit_path = get_post('path') ?? '';
    $content = $_POST['content'] ?? '';
    
    if (!is_file($edit_path) || !is_writable($edit_path)) {
        echo json_encode(['status' => 'error', 'message' => 'Error: File not found or not writable.']);
        exit;
    }

    if (save_file($edit_path, $content)) {
        echo json_encode(['status' => 'success', 'message' => 'File saved successfully! (' . date('H:i:s') . ')']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: Could not save file. Check permissions.']);
    }
    exit;
}

if (get_post('action') === 'upload_chunk') {
    header('Content-Type: application/json');
    
    $file = get_files('chunk');
    $chunk_num = get_post('chunk_num');
    $total_chunks = get_post('total_chunks');
    $filename = get_post('filename');
    
    if (!$file || $chunk_num === false || $total_chunks === false || !$filename) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid chunk upload request.']);
        exit;
    }

    $temp_filename = $filename . '.part';
    $temp_filepath = $path . '/' . $temp_filename;

    $chunk_content = file_get_contents($file['tmp_name']);
    if ($chunk_content === false) {
        echo json_encode(['status' => 'error', 'message' => 'Could not read chunk data.']);
        exit;
    }

    if (file_put_contents($temp_filepath, $chunk_content, FILE_APPEND) === false) {
        echo json_encode(['status' => 'error', 'message' => 'Could not write to .part file. Check permissions.']);
        exit;
    }

    if ((int)$chunk_num === (int)$total_chunks - 1) {
        $final_filepath = $path . '/' . $filename;
        if (file_exists($final_filepath)) {
             unlink($temp_filepath); // Clean up .part file
             echo json_encode(['status' => 'error', 'message' => 'Error: File with this name already exists.']);
        } else {
            if (rename($temp_filepath, $final_filepath)) {
                echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully! Reloading...']);
            } else {
                unlink($temp_filepath); // Clean up
                echo json_encode(['status' => 'error', 'message' => 'Could not finalize file.']);
            }
        }
    } else {
        echo json_encode(['status' => 'chunk_received', 'message' => "Chunk " . ((int)$chunk_num + 1) . " of $total_chunks received..."]);
    }
    exit;
}

// -- Write Actions --
if (get_post('newfile') && ($filename = get_post('filename'))) {
    $new_path = $path . '/' . basename($filename);
    if (!file_exists($new_path)) {
        touch($new_path);
        $message = "File created: " . $filename;
    } else {
        $message = "Error: File already exists.";
    }
    redirect($redirect_path . '&msg='.urlencode($message));
}

if (get_post('newdir') && ($dirname = get_post('dirname'))) {
    $new_path = $path . '/' . basename($dirname);
    if (!file_exists($new_path)) {
        mkdir($new_path);
        $message = "Directory created: " . $dirname;
    } else {
        $message = "Error: Directory already exists.";
    }
    redirect($redirect_path . '&msg='.urlencode($message));
}

// -- Rename Action --
if ($rename_path = get_post('rename')) {
    $new_name = get_post('newname');
    if ($new_name) {
        // Fix: Use the actual path without URL encoding
        $rename_path = str_replace('\\', '/', $rename_path);
        
        if (rename_item($rename_path, $new_name)) {
            $message = "Renamed successfully";
        } else {
            // Debug information
            $old_name = basename($rename_path);
            $dir = dirname($rename_path);
            $new_path = $dir . '/' . basename($new_name);
            $error_msg = "Error: Could not rename. ";
            
            if (!file_exists($rename_path)) {
                $error_msg .= "Source file does not exist. (Path: $rename_path) ";
            }
            if (file_exists($new_path)) {
                $error_msg .= "Target file already exists. ";
            }
            if (!is_writable($dir)) {
                $error_msg .= "Directory is not writable. ";
            }
            if ($old_name === basename($new_name)) {
                $error_msg .= "New name is the same as old name. ";
            }
            
            $message = $error_msg;
        }
    } else {
        $message = "Error: New name is empty.";
    }
    redirect($redirect_path . '&msg='.urlencode($message));
}

if ($delete_path = get_get('delete')) {
    $is_dir = is_dir($delete_path);
    $parent_path = '?path='.urlencode(dirname($delete_path));
    if ($is_dir ? rmdir($delete_path) : unlink($delete_path)) {
        $message = "Deleted: " . basename($delete_path);
    } else {
        $message = "Error: Could not delete.";
    }
    redirect($parent_path . '&msg='.urlencode($message));
}

// -- View/Edit Actions (These render a page and exit) --
if ($edit_path = get_get('edit')) {
    if (!is_file($edit_path) || !is_readable($edit_path)) {
        redirect('?path='.urlencode($path).'&msg='.urlencode('Error: File not found.'));
    }
    
    render_editor($edit_path, file_get_contents($edit_path));
    exit;
}

if ($dl_path = get_get('download')) {
    if (is_file($dl_path) && is_readable($dl_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($dl_path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($dl_path));
        readfile($dl_path);
        exit;
    } else {
        redirect('?path='.urlencode($path).'&msg='.urlencode('Error: File not found.'));
    }
}

// 5. Default View (File Manager)
$dir_list = get_dir_list($path);
render_file_manager($path, $dir_list, $message);

?>
