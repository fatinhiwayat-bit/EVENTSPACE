async function loadBookings() {
    try {
        let rows = await request('/bookings');
        let bookingListEl = el('bookingList');
        
        if (!rows || rows.length === 0) {
            bookingListEl.innerHTML = '<div class="alert alert-info">No bookings found.</div>';
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-hover align-middle bg-white shadow-sm rounded">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking ID</th>
                            <th>Event Title</th>
                            <th>Customer ID</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Ticket QR</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        rows.forEach(b => {
            // Third-Party QR Code API fallback & endpoint integration
            let qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=BOOKING-REF-${b.id}`;

            html += `
                <tr>
                    <td><strong>#${b.id}</strong></td>
                    <td>${b.event_title || 'Event #' + b.event_id}</td>
                    <td>User #${b.user_id}</td>
                    <td><span class="badge bg-secondary">${b.quantity} ticket(s)</span></td>
                    <td>RM ${parseFloat(b.total_amount || 0).toFixed(2)}</td>
                    <td>
                        <span class="badge ${b.status === 'confirmed' ? 'bg-success' : 'bg-danger'}">
                            ${b.status}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="ticketQR(${b.id})">
                            🔍 View QR
                        </button>
                    </td>
                    <td>
                        ${b.status !== 'cancelled' ? `
                            <button class="btn btn-sm btn-outline-danger" onclick="cancelBooking(${b.id})">
                                Cancel
                            </button>
                        ` : '<span class="text-muted small">N/A</span>'}
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        bookingListEl.innerHTML = html;

    } catch (err) {
        console.error('Failed to load bookings:', err);
    }
}

async function ticketQR(id) {
    try {
        let r = await fetch(API + '/ticket-qr/' + id, {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        
        if (!r.ok) {
            let data = await r.json();
            throw new Error(data.message || 'Failed to fetch QR');
        }

        // if the backend returns an image, we can display it directly
        let blob = await r.blob();
        let imgUrl = URL.createObjectURL(blob);
        
        // Papar pop-up modal / alert window khas untuk tunjuk QR code
        let win = window.open("", "Ticket QR Code", "width=350,height=350");
        win.document.write(`
            <div style="text-align:center; font-family:sans-serif; padding:20px;">
                <h3>🎟️ Ticket QR Code</h3>
                <p>Booking Ref ID: #${id}</p>
                <img src="${imgUrl}" style="width:180px; height:180px; margin:10px 0;" /><br>
                <small style="color:gray;">Scanned via Organiser QR Verification System</small>
            </div>
        `);

    } catch (err) {
        // Fallback to third-party QR code generation if backend fails
        let fallbackQr = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=BOOKING-REF-${id}`;
        let win = window.open("", "Ticket QR Code", "width=350,height=350");
        win.document.write(`
            <div style="text-align:center; font-family:sans-serif; padding:20px;">
                <h3>🎟️ Ticket QR Code</h3>
                <p>Booking Ref ID: #${id}</p>
                <img src="${fallbackQr}" style="width:180px; height:180px; margin:10px 0;" /><br>
                <small style="color:gray;">Generated via External QR Code API</small>
            </div>
        `);
    }
}

async function cancelBooking(id) {
    if (!confirm('Cancel booking #' + id + '?')) return;
    try {
        await request('/bookings/' + id, 'PATCH', { status: 'cancelled' });
        if (typeof notice === 'function') notice('Booking cancelled successfully');
        loadBookings();
    } catch (err) {
        alert(err.message || 'Failed to cancel booking');
    }
}