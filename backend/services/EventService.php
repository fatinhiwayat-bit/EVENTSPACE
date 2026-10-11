<?php
function getEvent(int $id,bool $lock=false):?array {
 $s=db()->prepare('SELECT e.*,v.name AS venue_name,v.capacity,u.name AS organiser_name, (SELECT COALESCE(SUM(quantity),0) FROM bookings b WHERE b.event_id=e.id AND b.status="confirmed") AS sold FROM events e JOIN venues v ON v.id=e.venue_id JOIN users u ON u.id=e.organiser_id WHERE e.id=?'.($lock?' FOR UPDATE':''));$s->execute([$id]);$row=$s->fetch();if($row)$row['available']=(int)$row['total_tickets']-(int)$row['sold'];return $row?:null;
}
function allowedEvent(?array $event,array $user):bool {return $user['role']==='admin'||($event&&$user['role']==='organiser'&&(int)$event['organiser_id']===(int)$user['id']);}
