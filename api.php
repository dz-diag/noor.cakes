<?php
// Noor Cakes – حفظ البيانات على الاستضافة
$PASSWORD = '';   // اختياري: ضع هنا كلمة سر لحماية بياناتك (اتركها فارغة لعدم الحماية)

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($PASSWORD !== '') {
  $k = $_SERVER['HTTP_X_KEY'] ?? '';
  if (!hash_equals($PASSWORD, $k)) { http_response_code(401); echo '{"error":"auth"}'; exit; }
}
$file = __DIR__ . '/noor-data.php';   // ملف يُنشأ تلقائياً، ولا يمكن فتحه مباشرة من المتصفح
$guard = "<?php http_response_code(403); exit; ?>\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $raw = file_get_contents('php://input');
  if (strlen($raw) > 2000000) { http_response_code(413); echo '{"error":"big"}'; exit; }
  $d = json_decode($raw, true);
  if (!is_array($d) || !isset($d['ings'], $d['projects']) || !is_array($d['ings']) || !is_array($d['projects'])) {
    http_response_code(400); echo '{"error":"bad"}'; exit;
  }
  $out = json_encode(['ings' => $d['ings'], 'projects' => $d['projects']], JSON_UNESCAPED_UNICODE);
  if (file_put_contents($file, $guard . $out, LOCK_EX) === false) { http_response_code(500); echo '{"error":"write"}'; exit; }
  echo '{"ok":true}'; exit;
}
if (is_file($file)) { echo substr(file_get_contents($file), strlen($guard)); }
else { echo '{"ings":[],"projects":[]}'; }
