// Mock data for reservations
let reservations = [
  {
    id: '1',
    date: 'April 21, 2025',
    timeSlot: '10:00-11:00 AM',
    roomName: 'Meeting Room 1',
    reservedBy: 'Stepping Curry',
    numberOfPeople: 10,
    status: 'pending',
    notificationSent: false,
  },
  {
    id: '2',
    date: 'April 21, 2025',
    timeSlot: '1:00-2:00 PM',
    roomName: 'Meeting Room 2',
    reservedBy: 'Lebwrong James',
    numberOfPeople: 5,
    status: 'approved',
    notificationSent: true,
  },
];



// DOM Elements
const dashboard = document.getElementById('dashboard');
const reservationTable = document.getElementById('reservationTable');
const walkInFormModal = document.getElementById('walkInFormModal');
const walkInForm = document.getElementById('walkInForm');
const reservationTableBody = document.getElementById('reservationTableBody');

// View Functions
function viewCalendar() {
  // Calendar view implementation
  console.log('Calendar view clicked');
}

function viewTable() {
  dashboard.classList.add('hidden');
  reservationTable.classList.remove('hidden');
  renderReservationTable();
}

function backToDashboard() {
  reservationTable.classList.add('hidden');
  dashboard.classList.remove('hidden');
}

// Walk-in Form Functions
function openWalkInForm() {
  walkInFormModal.classList.remove('hidden');
  // Set default date to today
  const today = new Date().toISOString().split('T')[0];
  walkInForm.elements.date.value = today;
}

function closeWalkInForm() {
  walkInFormModal.classList.add('hidden');
  walkInForm.reset();
}

function incrementPeople() {
  const input = walkInForm.elements.numberOfPeople;
  if (input.value < 10) {
    input.value = parseInt(input.value) + 1;
  }
}

function decrementPeople() {
  const input = walkInForm.elements.numberOfPeople;
  if (input.value > 5) {
    input.value = parseInt(input.value) - 1;
  }
}

function handleWalkInSubmit(event) {
  event.preventDefault();
  const formData = new FormData(walkInForm);

  const newReservation = {
    id: crypto.randomUUID(),
    date: new Date(formData.get('date')).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }),
    timeSlot: formData.get('timeSlot'),
    roomName: formData.get('roomName'),
    reservedBy: formData.get('reservedBy'),
    numberOfPeople: parseInt(formData.get('numberOfPeople')),
    status: 'approved',
    notificationSent: true,
  };

  reservations.push(newReservation);
  closeWalkInForm();
  viewTable();
}

// Reservation Table Functions
function renderReservationTable() {
  reservationTableBody.innerHTML = '';

  reservations.forEach((reservation) => {
    const row = document.createElement('tr');
    row.innerHTML = `
            <td>${reservation.date}</td>
            <td>${reservation.timeSlot}</td>
            <td>${reservation.roomName}</td>
            <td>${reservation.reservedBy}</td>
            <td>${reservation.numberOfPeople}</td>
            <td>
                <span class="status status-${reservation.status}">
                    ● ${
                      reservation.status.charAt(0).toUpperCase() +
                      reservation.status.slice(1)
                    }
                </span>
            </td>
            <td>
                <span class="status ${
                  reservation.notificationSent
                    ? 'status-approved'
                    : 'status-rejected'
                }">
                    ${reservation.notificationSent ? '✓ Yes' : '✗ No'}
                </span>
            </td>
            <td>
                ${
                  reservation.status === 'pending'
                    ? `<button class="btn" onclick="approveReservation('${reservation.id}')">✓ Approve</button>
                       <button class="btn-secondary" onclick="rejectReservation('${reservation.id}')">✗ Reject</button>`
                    : `<button class="btn" onclick="editReservation('${reservation.id}')">✎ Edit</button>`
                }
            </td>
        `;
    reservationTableBody.appendChild(row);
  });
}

function approveReservation(id) {
  const reservation = reservations.find((r) => r.id === id);
  if (reservation) {
    reservation.status = 'approved';
    renderReservationTable();
  }
}

function rejectReservation(id) {
  if (confirm('Are you sure you want to reject this reservation?')) {
    const reservation = reservations.find((r) => r.id === id);
    if (reservation) {
      reservation.status = 'rejected';
      renderReservationTable();
    }
  }
}

function editReservation(id) {
  // Edit reservation implementation
  console.log('Edit reservation:', id);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  // Set up initial view
  renderReservationTable();
});

// Add this function to handle opening the edit modal
function openEditModal(reservation) {
  const modal = document.getElementById('editFormModal');
  const form = document.getElementById('editForm');
  
  // Populate form with reservation data
  form.elements.id.value = reservation.id;
  form.elements.roomName.value = reservation.roomName;
  
  // Convert date from "April 21, 2025" format back to "YYYY-MM-DD"
  const dateObj = new Date(reservation.date);
  const formattedDate = dateObj.toISOString().split('T')[0];
  form.elements.date.value = formattedDate;
  
  form.elements.timeSlot.value = reservation.timeSlot;
  form.elements.reservedBy.value = reservation.reservedBy;
  form.elements.numberOfPeople.value = reservation.numberOfPeople;
  form.elements.status.value = reservation.status;
  form.elements.notificationSent.checked = reservation.notificationSent;
  
  modal.classList.remove('hidden');
}

// Update the editReservation function
function editReservation(id) {
  const reservation = reservations.find(r => r.id === id);
  if (reservation) {
    openEditModal(reservation);
  }
}

// Add this function to handle the edit form submission
function handleEditSubmit(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  const id = formData.get('id');
  
  const updatedReservation = {
    id,
    date: new Date(formData.get('date')).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    }),
    timeSlot: formData.get('timeSlot'),
    roomName: formData.get('roomName'),
    reservedBy: formData.get('reservedBy'),
    numberOfPeople: parseInt(formData.get('numberOfPeople')),
    status: formData.get('status'),
    notificationSent: formData.get('notificationSent') === 'on'
  };
  
  // Update the reservation in the array
  const index = reservations.findIndex(r => r.id === id);
  if (index !== -1) {
    reservations[index] = updatedReservation;
  }
  
  // Close modal and refresh table
  closeEditModal();
  renderReservationTable();
}

// Add this function to close the edit modal
function closeEditModal() {
  document.getElementById('editFormModal').classList.add('hidden');
  document.getElementById('editForm').reset();
}

// Add increment/decrement functions for the edit form
function incrementEditPeople() {
  const input = document.getElementById('editForm').elements.numberOfPeople;
  if (input.value < 10) {
    input.value = parseInt(input.value) + 1;
  }
}

function decrementEditPeople() {
  const input = document.getElementById('editForm').elements.numberOfPeople;
  if (input.value > 5) {
    input.value = parseInt(input.value) - 1;
  }
}