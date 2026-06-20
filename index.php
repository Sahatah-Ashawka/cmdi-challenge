<?php
error_reporting(0);
$cmd = $_GET['cmd'] ?? '';
$raw = $_SERVER['QUERY_STRING'] ?? '';
$blocked = '';

$banned = [
    '../' => '../', '..././' => '..././', '....//' => '....//',
    'head' => 'head', 'tail' => 'tail', 'cat' => 'cat', 'tac' => 'tac',
    'find' => 'find', 'locate' => 'locate',
    ' ' => 'space', "\t" => 'tab', "\n" => 'newline', "\v" => 'vertical tab',
    '&&' => '&&', '||' => '||', '|' => '|',
    '*' => '*', '<' => '<', '{,' => '{,', '$IFS$9' => '$IFS$9',
    'env' => 'env', 'printenv' => 'printenv', 'set' => 'set', 'export' => 'export',
    'phpinfo' => 'phpinfo', 'php -i' => 'php -i', 'php -r' => 'php -r',
    'proc/self' => 'proc/self',
    'more' => 'more', 'less' => 'less', 'sort' => 'sort',
    'od' => 'od', 'xxd' => 'xxd', 'strings' => 'strings',
    'sed' => 'sed', 'awk' => 'awk', 'grep' => 'grep', 'rev' => 'rev',
    'python3 -c' => 'python3 -c',
];
$banned["\$'\\x20'"] = "\$'\\x20'";
$banned["\$'\\040'"] = "\$'\\040'";
$banned['-al'] = '-al';
$banned['-la'] = '-la';
$banned['$IFS-l'] = '$IFS-l';
$banned['$IFS-a'] = '$IFS-a';
$banned['${IFS}-l'] = '${IFS}-l';
$banned['${IFS}-a'] = '${IFS}-a';
$encoded = [
    '%2e%2e%2f' => '../', '%2e%2e%2e%2f%2e%2f' => '..././', '%2e%2e%2e%2e%2f%2f' => '....//',
    '%0a' => 'newline', '%0b' => 'vertical tab', '%09' => 'tab'
];

foreach ($banned as $pat => $label) {
    if (stripos($cmd, $pat) !== false) { $blocked = $label; break; }
}
if ($blocked === '') {
    foreach ($encoded as $pat => $label) {
        if (stripos($raw, $pat) !== false) { $blocked = $label; break; }
    }
}

if (isset($_GET['ajax'])) {
    if ($blocked !== '') {
        echo '<span class="banned-msg">✖ ' . htmlspecialchars($blocked) . ' is banned</span>';
    } else {
        echo '<pre>'; system($cmd); echo '</pre>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hidden Leaf Console</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700;900&family=Fira+Code:wght@400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Fira Code', 'Noto Sans JP', monospace;
            background: linear-gradient(135deg, #fff8f0 0%, #fff0e0 100%);
            color: #2d2d2d;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .wrapper {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 1;
        }
        .side-char { width: 160px; text-align: center; flex-shrink: 0; }
        .side-char img { width: 160px; height: 200px; object-fit: cover; border-radius: 12px; display: block; }
        .side-char .name { font-family: 'Noto Sans JP', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1px; margin-top: 6px; }
        .side-char.naruto-side .name { color: #e87400; }
        .side-char.sasuke-side .name { color: #324bb4; }
        .container {
            position: relative;
            max-width: 740px;
            width: 100%;
            padding: 30px 35px 35px;
            background: rgba(255,255,255,0.92);
            border: 2px solid #e87400;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(232,116,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
        }
        .container::before {
            content: '';
            position: absolute;
            top: 8px; left: 8px; right: 8px; bottom: 8px;
            border: 1px dashed rgba(232,116,0,0.15);
            border-radius: 12px;
            pointer-events: none;
        }
        .header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e87400; }
        .icon-leaf { font-size: 24px; line-height: 1; }
        h1 { font-family: 'Noto Sans JP', sans-serif; font-size: 18px; font-weight: 900; color: #e87400; letter-spacing: 2px; flex: 1; }
        h1 .en { font-weight: 400; font-size: 11px; color: #ff9a3c; letter-spacing: 1px; margin-left: 6px; }
        .subtitle { font-size: 11px; color: #b06c3a; text-align: right; margin-top: -16px; margin-bottom: 16px; }
        .subtitle .en { font-size: 10px; color: #c9a88a; }
        form { display: flex; gap: 10px; margin-bottom: 16px; }
        input[type=text] {
            flex: 1; padding: 11px 14px; font-family: 'Fira Code', monospace; font-size: 13px;
            background: #fffaf5; color: #2d2d2d; border: 2px solid #f5d6b8; border-radius: 8px; outline: none; transition: border-color 0.2s;
        }
        input[type=text]:focus { border-color: #e87400; }
        input[type=submit] {
            padding: 11px 22px; font-family: 'Noto Sans JP', sans-serif; font-size: 12px; font-weight: 700;
            background: #e87400; color: #fff; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s; letter-spacing: 1px;
        }
        input[type=submit]:hover { background: #d06600; }
        .prompt-symbol { font-size: 11px; color: #b06c3a; margin-bottom: 5px; padding-left: 2px; }
        .output {
            text-align: left; padding: 14px; background: #fdf6ee; border: 1px solid #f5d6b8;
            border-radius: 8px; min-height: 50px; font-size: 13px; line-height: 1.5;
        }
        .output pre { margin: 0; color: #2d2d2d; white-space: pre-wrap; word-break: break-all; }
        .banned-msg { color: #d32f2f; font-weight: 600; }
        .footer { text-align: center; margin-top: 14px; font-size: 10px; color: #ccc0b0; letter-spacing: 2px; }
        #result-area { display: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="side-char naruto-side">
            <img src="naruto.jpg" alt="Naruto">
            <div class="name">NARUTO</div>
        </div>
        <div class="container">
            <div class="header">
                <div class="icon-leaf">🍥</div>
                <h1>Hidden Leaf <span class="en">// terminal</span></h1>
            </div>
            <div class="subtitle"><span class="en">Ichiraku Ramen</span></div>
            <form id="cmdForm">
                <input type="text" id="cmdInput" placeholder=">" autofocus>
                <input type="submit" value="run">
            </form>
            <div id="result-area">
                <div class="prompt-symbol" id="promptLine"></div>
                <div class="output" id="outputArea"></div>
            </div>
            <div class="footer">⠿ Hidden Leaf Village ⠿</div>
        </div>
        <div class="side-char sasuke-side">
            <img src="images.jpg" alt="Sasuke">
            <div class="name">SASUKE</div>
        </div>
    </div>
    <audio id="bgm" loop>
        <source src="bgm.mp3" type="audio/mpeg">
    </audio>
    <script>
        var bgm = document.getElementById('bgm');
        bgm.volume = 0.3;
        function playBGM() {
            bgm.play();
            document.removeEventListener('click', playBGM);
            document.removeEventListener('keydown', playBGM);
        }
        document.addEventListener('click', playBGM);
        document.addEventListener('keydown', playBGM);

        var form = document.getElementById('cmdForm');
        var input = document.getElementById('cmdInput');
        var resultArea = document.getElementById('result-area');
        var promptLine = document.getElementById('promptLine');
        var outputArea = document.getElementById('outputArea');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var cmd = input.value.trim();
            if (!cmd) return;
            promptLine.textContent = '🍥 naruto:~$ ' + cmd;
            outputArea.innerHTML = '<span style="color:#999">running...</span>';
            resultArea.style.display = 'block';
            fetch('?' + new URLSearchParams({cmd: cmd, ajax: 1}))
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    outputArea.innerHTML = html;
                });
        });
    </script>
</body>
</html>
