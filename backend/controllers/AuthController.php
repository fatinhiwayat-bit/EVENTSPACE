<?php
function AuthController(string $resource, ?int $id, array $part, string $method):void {
if($resource==='auth'&&($part[1]??'')==='register'&&$method==='POST'){
  $d=body();requireFields($d,['name','email','password']);need(filter_var($d['email'],FILTER_VALIDATE_EMAIL)!==false,'Invalid email');need(strlen($d['password'])>=8,'Password must be 8+ characters');
  $s=db()->prepare('SELECT id FROM users WHERE email=?');$s->execute([$d['email']]);need(!$s->fetch(),'Email already registered',409);
  $s=db()->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,'customer')");$s->execute([trim($d['name']),strtolower(trim($d['email'])),password_hash($d['password'],PASSWORD_DEFAULT)]);reply(201,['message'=>'Registered successfully','id'=>(int)db()->lastInsertId()]);
 }
 if($resource==='auth'&&($part[1]??'')==='login'&&$method==='POST'){
  rateLimitLogin();
  $d=body();requireFields($d,['email','password']);$s=db()->prepare('SELECT * FROM users WHERE email=?');$s->execute([$d['email']]);$u=$s->fetch();need($u&&password_verify($d['password'],$u['password_hash']),'Invalid credentials',401);reply(200,['token'=>token($u),'user'=>['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']]]);
 }
 if($resource==='auth'&&($part[1]??'')==='me'&&$method==='GET')reply(200,currentUser());
}
