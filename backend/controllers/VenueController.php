<?php
function VenueController(string $resource, ?int $id, array $part, string $method):void {
if($resource==='venues'){
  if($method==='GET'&&$id===null){$q=db()->query('SELECT * FROM venues ORDER BY name');reply(200,$q->fetchAll());}
  if($method==='GET'&&$id!==null){$s=db()->prepare('SELECT * FROM venues WHERE id=?');$s->execute([$id]);$v=$s->fetch();reply($v?200:404,$v?:['error'=>'Venue not found']);}
  if($method==='POST'){roles(['admin','organiser']);$d=body();requireFields($d,['name','address','capacity']);need(positive($d['capacity']),'Capacity must be positive');$s=db()->prepare('INSERT INTO venues(name,address,capacity) VALUES(?,?,?)');$s->execute([$d['name'],$d['address'],$d['capacity']]);reply(201,['id'=>(int)db()->lastInsertId()]);}
  if(in_array($method,['PUT','PATCH'],true)&&$id!==null){roles(['admin','organiser']);$d=body();requireFields($d,['name','address','capacity']);need(positive($d['capacity']),'Invalid capacity');$s=db()->prepare('UPDATE venues SET name=?,address=?,capacity=? WHERE id=?');$s->execute([$d['name'],$d['address'],$d['capacity'],$id]);reply(200,['message'=>'Venue updated']);}
  if($method==='DELETE'&&$id!==null){roles(['admin']);$s=db()->prepare('DELETE FROM venues WHERE id=?');$s->execute([$id]);reply(200,['message'=>'Venue deleted']);}
 }
}
