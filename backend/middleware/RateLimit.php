<?php
/** A basic local-install rate limiter for authentication requests. */
function rateLimitLogin():void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = hash('sha256', $ip);
    $file = sys_get_temp_dir().'/eventspace_login_'.$key.'.json';
    $fp = fopen($file, 'c+');
    if (!$fp) return; // Inability to create the local log must not break a student demo.
    try {
        if (!flock($fp, LOCK_EX)) return;
        $raw = stream_get_contents($fp);
        $data = json_decode($raw ?: '{}', true) ?: [];
        $now = time();
        $start = (int)($data['start'] ?? $now);
        $count = (int)($data['count'] ?? 0);
        if ($now - $start >= 60) { $start = $now; $count = 0; }
        if ($count >= 12) {
            header('Retry-After: '.max(1, 60 - ($now - $start)));
            reply(429, ['error' => 'Too many login attempts. Retry later.']);
        }
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode(['start'=>$start,'count'=>$count+1]));
        fflush($fp);
        flock($fp, LOCK_UN);
    } finally { fclose($fp); }
}
