<?php
function b64(string $txt):string {return rtrim(strtr(base64_encode($txt),'+/','-_'),'=');}
function unb64(string $txt):string|false {return base64_decode(strtr($txt,'-_','+/'),true);}
function token(array $u):string {$head=b64(json_encode(['typ'=>'JWT','alg'=>'HS256']));$payload=b64(json_encode(['sub'=>(int)$u['id'],'role'=>$u['role'],'iat'=>time(),'exp'=>time()+86400]));$sig=b64(hash_hmac('sha256',"$head.$payload",JWT_SECRET,true));return "$head.$payload.$sig";}
function currentUser(bool $required=true):?array {
 // Apache may expose Authorization differently depending on its PHP handler.
 $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
 if ($hdr === '' && function_exists('apache_request_headers')) {
   foreach (apache_request_headers() as $key => $value) {
     if (strcasecmp($key, 'Authorization') === 0) { $hdr = $value; break; }
   }
 }
 if ($hdr === '' && function_exists('getallheaders')) {
   foreach (getallheaders() as $key => $value) {
     if (strcasecmp($key, 'Authorization') === 0) { $hdr = $value; break; }
   }
 }
 if(!preg_match('/^Bearer\s+(\S+)$/i',$hdr,$m)){if($required)reply(401,['error'=>'Bearer token required']);return null;}
 $p=explode('.',$m[1]);if(count($p)!==3)reply(401,['error'=>'Invalid token']);
 [$h,$b,$sig]=$p;$header=json_decode(unb64($h)?:'',true);$payload=json_decode(unb64($b)?:'',true);
 if(($header['alg']??null)!=='HS256'||!hash_equals(b64(hash_hmac('sha256',"$h.$b",JWT_SECRET,true)),$sig)||!is_array($payload)||($payload['exp']??0)<time()||!is_numeric($payload['sub']??null))reply(401,['error'=>'Invalid or expired token']);
 $s=db()->prepare('SELECT id,name,email,role FROM users WHERE id=?');$s->execute([(int)$payload['sub']]);$u=$s->fetch();if(!$u)reply(401,['error'=>'User no longer exists']);return $u;
}
function roles(array $allowed):array {$u=currentUser();need(in_array($u['role'],$allowed,true),'Access denied',403);return $u;}
