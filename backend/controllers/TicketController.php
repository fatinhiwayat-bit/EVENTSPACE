<?php
function TicketController(string $resource, ?int $id, array $part, string $method):void {
if($resource==='ticket-qr'&&$id!==null&&$method==='GET'){
  $u=currentUser();$s=db()->prepare('SELECT b.*,e.organiser_id FROM bookings b JOIN events e ON e.id=b.event_id WHERE b.id=?');$s->execute([$id]);$b=$s->fetch();need((bool)$b,'Booking not found',404);need($u['role']==='admin'||(int)$u['id']===(int)$b['user_id']||($u['role']==='organiser'&&(int)$u['id']===(int)$b['organiser_id']),'Access denied',403);
  $value='Ticket booking #'.$b['id'].' | event #'.$b['event_id'].' | '.$b['status'];$url='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.rawurlencode($value);
  $curl=curl_init($url);curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_FOLLOWLOCATION=>false]);$image=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_HTTP_CODE);$mime=curl_getinfo($curl,CURLINFO_CONTENT_TYPE);curl_close($curl);if($status!==200||!$image||!str_starts_with((string)$mime,'image/'))reply(502,['error'=>'External QR service unavailable']);header_remove('Content-Type');header('Content-Type: image/png');echo $image;exit;
 }
}
