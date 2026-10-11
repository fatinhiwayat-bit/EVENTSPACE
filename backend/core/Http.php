<?php
function reply(int $code,$data):never {http_response_code($code);echo json_encode($data,JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
function need(bool $ok,string $error='Invalid request',int $status=422):void {if(!$ok)reply($status,['error'=>$error]);}
function body():array {$data=json_decode(file_get_contents('php://input'),true);need(is_array($data),'Invalid JSON body',400);return $data;}

function positive($v):bool {return filter_var($v,FILTER_VALIDATE_INT)!==false&&(int)$v>0;}

function requireFields(array $data,array $names):void {foreach($names as $n)need(isset($data[$n])&&$data[$n]!=='',"Field $n is required");}
