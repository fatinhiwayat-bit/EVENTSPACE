// ================================
// EVENTS MODULE (customer + organiser)
// ================================

async function loadEvents(change) {
  page = Math.max(1, page + change);

  const qs = new URLSearchParams({
    page,
    limit: 6,
    search: el('search').value
  });

  const r = await request('/events?' + qs);

  if (r.data.length === 0 && page > 1) {
    page--;
    return loadEvents(0);
  }

  el('pageInfo').textContent = 'Page ' + page;

  el('eventList').innerHTML = r.data.map(e => `
    <div class="col-md-6 col-xl-4">
      <article class="card h-100 p-4">
        <div class="mb-2">
          <span class="badge bg-primary">${html(e.status)}</span>
        </div>
        <h4>${html(e.title)}</h4>
        <p class="muted">${html(e.description)}</p>
        <div><b>Venue:</b> ${html(e.venue_name)}</div>
        <div><b>Date:</b> ${html(e.event_date)}</div>
        <div><b>Price:</b> RM ${html(e.ticket_price)}</div>
        <div><b>Available:</b> ${html(e.available)}</div>
        <div class="d-flex gap-2 mt-3">
          <button
            class="btn btn-primary"
            onclick="book(${Number(e.id)}, ${Number(e.ticket_price)}, ${Number(e.available)})"
            ${Number(e.available) < 1 || e.status !== 'published' ? 'disabled' : ''}>
            Book
          </button>
          ${['admin', 'organiser'].includes(user.role)
            ? `<button class="btn btn-outline-danger" onclick="removeEvent(${Number(e.id)})">Delete</button>`
            : ''}
        </div>
      </article>
    </div>
  `).join('') || '<p>No events found.</p>';
}

el('search').addEventListener('input', () => {
  page = 1;
  loadEvents(0).catch(e => notice(e.message, true));
});

async function book(id, price, available) {
  const raw = prompt(`How many tickets? (available: ${available})`, '1');
  if (raw === null) return;

  const quantity = Number(raw);
  if (!Number.isInteger(quantity) || quantity < 1) {
    notice('Please enter a positive whole number.', true);
    return;
  }
  if (quantity > Number(available)) {
    notice(`Only ${available} tickets left.`, true);
    return;
  }

  const total = (quantity * Number(price)).toFixed(2);
  if (!confirm(`Confirm booking of ${quantity} ticket(s)? Total RM ${total}`)) return;

  try {
    const booking = await request('/bookings', 'POST', { event_id: id, quantity });
    notice(`Booking #${booking.id} confirmed.`);
    await showTab('bookings');
  } catch (e) {
    notice(e.message, true);
  }
}

async function removeEvent(id) {
  if (!confirm('Delete this event? Existing bookings will prevent deletion.')) return;
  try {
    await request('/events/' + id, 'DELETE');
    notice('Event deleted');
    await loadEvents(0);
  } catch (e) {
    notice(e.message, true);
  }
}