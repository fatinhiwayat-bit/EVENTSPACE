<?php
function logAction(?int $user, string $action, string $detail = ''): void
{
    $s = db()->prepare('INSERT INTO audit_logs(user_id,action,detail) VALUES (?,?,?)');
    $s->execute([$user, $action, $detail]);
}
