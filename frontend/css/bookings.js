async function loadBookings(){
  el('bookingList').innerHTML='<p class="muted">Memuatkan booking...</p>';
  try{
    let rows=await request('/bookings');
    renderCustomerSummary(rows);
    el('bookingList').innerHTML=rows.length
      ?`<div class="table-responsive"><table class="table table-striped bg-white"><thead><tr><th>ID</th><th>Event</th><th>Qty</th><th>Total (RM)</th><th>Status</th><th>Action</th></tr></thead><tbody>${rows.map(b=>`<tr><td>#${html(b.id)}</td><td>${html(b.event_title)}</td><td>${html(b.quantity)}</td><td>${html(b.total_amount)}</td><td>${html(b.status)}</td><td><div class="d-flex gap-2">${b.status==='confirmed'&&(user.role==='admin'||Number(b.user_id)===Number(user.id))?`<button class="btn btn-sm btn-outline-danger" onclick="cancelBooking(${Number(b.id)})">Cancel</button>`:''}${b.status==='confirmed'?`<button class="btn btn-sm btn-outline-secondary" onclick="ticketQR(${Number(b.id)})">QR Ticket</button>`:''}</div></td></tr>`).join('')}</tbody></table></div>`
      :'<p>No bookings yet.</p>';
  }catch(e){
    el('bookingList').innerHTML=`<div class="alert alert-danger">Gagal memuatkan booking: ${html(e.message)}</div>`;
  }
}

async function ticketQR(id){
  // Open the window first, while the click is still a user gesture, so the popup is not blocked
  let windowRef=window.open('','_blank','width=420,height=600');
  if(!windowRef){notice('Benarkan popup untuk lihat tiket QR.',true);return}
  try{
    windowRef.document.write('<p style="font-family:sans-serif;text-align:center">Memuatkan tiket...</p>');
    let r=await fetch(API+'/ticket-qr/'+id,{headers:{Authorization:'Bearer '+token}});
    if(!r.ok){let data=await r.json();throw Error(data.error||'QR unavailable')}
    let url=URL.createObjectURL(await r.blob());
    windowRef.document.open();
    windowRef.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Ticket QR #${id}</title><style>
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:"Segoe UI",Arial,sans-serif;background:linear-gradient(135deg,#0b1f44,#13367a 45%,#2457a7 75%,#60a5fa)}
.ticket{background:#fff;color:#0f1b33;border-radius:20px;border-top:6px solid #3b82f6;padding:28px 32px;text-align:center;width:min(340px,90vw);box-shadow:0 20px 50px rgba(0,0,0,.35)}
.ticket h2{font-size:22px;margin:0 0 14px;color:#0b1f44}
.ticket img{width:220px;height:220px;border:6px solid #eff6ff;border-radius:12px}
.ticket p{margin:14px 0 0;color:#2457a7;font-weight:600}
</style></head><body><div class="ticket"><h2>Booking #${id}</h2><img src="${url}" alt="QR code"><p>Present this ticket at the venue</p></div></body></html>`);
    windowRef.document.close();
  }catch(e){
    windowRef.close();
    notice(e.message,true);
  }
}

async function cancelBooking(id){
  if(!confirm('Cancel booking #'+id+'?'))return;
  try{
    await request('/bookings/'+id,'PATCH',{status:'cancelled'});
    notice('Booking cancelled');
    await loadBookings();
  }catch(e){
    notice(e.message,true);
  }
}

function renderCustomerSummary(rows){
  const box=el('customerSummary');
  if(!box)return;
  if(user.role!=='customer'){box.innerHTML='';return}
  const confirmed=rows.filter(b=>b.status==='confirmed');
  const tickets=confirmed.reduce((sum,b)=>sum+Number(b.quantity),0);
  const spent=confirmed.reduce((sum,b)=>sum+Number(b.total_amount),0);
  box.innerHTML=`
    <div class="customer-hero mb-3">Hai, ${html(user.name||'')}! Ini ringkasan tiket anda.</div>
    <div class="row g-3">
      <div class="col-md-6"><div class="stat"><div class="stat-label">Tiket aktif</div><div class="stat-value">${tickets}</div></div></div>
      <div class="col-md-6"><div class="stat"><div class="stat-label">Jumlah dibelanjakan</div><div class="stat-value">RM ${spent.toFixed(2)}</div></div></div>
    </div>`;
}