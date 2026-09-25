/**
 * CampusRent Hub - Form Handling & Business Logic Simulation
 * Designed with standard HTML5 form attributes ready for PHP/MySQL processing.
 */

document.addEventListener('DOMContentLoaded', () => {
  initFormInterceptors();
  initHandoverTokenVerifier();
});

function initFormInterceptors() {
  // Catch any form with action ending in .php to simulate seamless local testing
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
      const action = form.getAttribute('action') || '';
      
      if (action.includes('process_add_item.php')) {
        e.preventDefault();
        handleAddNewItem(form);
      } else if (action.includes('process_rental_request.php')) {
        e.preventDefault();
        handleRentalRequest(form);
      } else if (action.includes('process_payment.php')) {
        e.preventDefault();
        handleSimulatedPayment(form);
      }
    });
  });
}

// 1. Add New Equipment Handler
function handleAddNewItem(form) {
  const formData = new FormData(form);
  const title = formData.get('item_title');
  const category = formData.get('category');
  const department = formData.get('department');
  const condition = formData.get('item_condition');
  const dailyRate = Number(formData.get('daily_rate'));
  const securityDeposit = Number(formData.get('security_deposit'));
  const pickupSpot = formData.get('pickup_spot');
  const description = formData.get('item_description');
  const specsText = formData.get('technical_specs') || '';

  if (!title || !dailyRate || !securityDeposit) {
    App.showToast('Please fill in all required fields.', 'warning');
    return;
  }

  const data = CampusRentData.get();
  const currentUser = (window.Auth && Auth.getCurrentUser()) || data.currentUser || {
    id: 'usr_1',
    name: 'Rafiqul Islam',
    studentId: 'PUC-22-0145',
    department: 'CSE',
    phone: '+8801712001122'
  };
  const newItemId = 'item_' + (Date.now() % 10000);

  // Check if a custom base64 image was uploaded via drag-and-drop or file input
  let finalImage = window._uploadedListingImageBase64 || null;

  if (!finalImage) {
    // Fallback image based on category
    finalImage = "https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80";
    if (category.toLowerCase().includes('calc')) {
      finalImage = "https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80";
    } else if (category.toLowerCase().includes('camera') || category.toLowerCase().includes('dslr')) {
      finalImage = "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop&q=80";
    } else if (category.toLowerCase().includes('iot') || category.toLowerCase().includes('arduino')) {
      finalImage = "https://images.unsplash.com/photo-1553406830-ef2513450d76?w=600&auto=format&fit=crop&q=80";
    } else if (category.toLowerCase().includes('coat')) {
      finalImage = "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80";
    }
  }

  const newItem = {
    id: newItemId,
    title: title,
    category: category,
    department: department,
    condition: condition,
    dailyRate: dailyRate,
    securityDeposit: securityDeposit,
    pickupSpot: pickupSpot,
    image: finalImage,
    gallery: [finalImage],
    lender: {
      id: currentUser.id,
      name: currentUser.name,
      studentId: currentUser.studentId,
      department: currentUser.department || 'CSE',
      rating: 5.0,
      reviewsCount: 1,
      verified: true,
      phone: currentUser.phone || '+8801700-000000'
    },
    specs: {
      "Condition": condition,
      "Campus Pickup": pickupSpot,
      "Overview": specsText || "Verified student equipment item in tested condition."
    },
    description: description,
    rules: "Valid University Student ID required at pickup. Pay in cash upon item inspection. Return on time.",
    featured: false,
    status: "Available"
  };

  // Add to items catalog
  data.items.unshift(newItem);

  // Add to owner's listings
  data.ownerListings.unshift({
    id: 'own_' + newItemId,
    title: title,
    category: category,
    department: department,
    dailyRate: dailyRate,
    deposit: securityDeposit,
    condition: condition,
    status: "Active",
    totalLends: 0,
    pickupSpot: pickupSpot,
    image: finalImage
  });

  CampusRentData.save(data);

  // Reset image upload preview state
  window._uploadedListingImageBase64 = null;
  const promptEl = document.getElementById('item-upload-prompt');
  const previewContainer = document.getElementById('item-preview-container');
  const previewImg = document.getElementById('item-preview-img');
  const fileInput = document.getElementById('item_image_file');
  if (promptEl) promptEl.classList.remove('hidden');
  if (previewContainer) previewContainer.classList.add('hidden');
  if (previewImg) previewImg.src = '';
  if (fileInput) fileInput.value = '';

  App.showToast(`Listing "${title}" published successfully to campus catalog!`, 'success');
  App.closeModal('add-equipment-modal');
  form.reset();

  // If on owner-dashboard.html or index.html, refresh view
  if (typeof renderOwnerListings === 'function') {
    renderOwnerListings();
  }
  if (typeof renderEquipmentGrid === 'function') {
    renderEquipmentGrid();
  }
}

// 2. Rental Request & Checkout Flow
function handleRentalRequest(form) {
  const formData = new FormData(form);
  const itemId = formData.get('item_id');
  const startDate = formData.get('rental_start_date');
  const endDate = formData.get('rental_end_date');
  const pickupSpot = formData.get('pickup_spot_id');
  const days = Number(formData.get('calculated_days')) || 1;
  const totalRent = Number(formData.get('calculated_subtotal')) || 0;
  const deposit = Number(formData.get('security_deposit')) || 0;
  const totalPayable = totalRent + deposit;

  if (!startDate || !endDate) {
    App.showToast('Please select both start and end dates.', 'warning');
    return;
  }

  // Store temporary booking intent for payment modal
  window._pendingBooking = {
    itemId,
    startDate,
    endDate,
    pickupSpot,
    days,
    totalRent,
    deposit,
    totalPayable
  };

  // Populate checkout modal values
  const rentDisplay = document.getElementById('modal-checkout-rent');
  const depositDisplay = document.getElementById('modal-checkout-deposit');
  const totalDisplay = document.getElementById('modal-checkout-total');
  
  if (rentDisplay) rentDisplay.textContent = App.formatBDT(totalRent);
  if (depositDisplay) depositDisplay.textContent = App.formatBDT(deposit);
  if (totalDisplay) totalDisplay.textContent = App.formatBDT(totalPayable);

  App.openModal('payment-checkout-modal');
}

// 3. Cash on Delivery (COD) / Cash at Handover Confirmation Flow
function handleSimulatedPayment(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = `
    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg> Confirming Cash on Delivery Booking...
  `;

  setTimeout(() => {
    const data = CampusRentData.get();
    const currentUser = (window.Auth && Auth.getCurrentUser()) || data.currentUser || {
      name: 'Rafiqul Islam',
      studentId: 'PUC-22-0145',
      phone: '+8801712001122'
    };
    const pending = window._pendingBooking || {};
    const item = (data.items && data.items.find(i => i.id === pending.itemId)) || {
      id: pending.itemId || 'item_default',
      title: 'Equipment Item',
      image: 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80',
      dailyRate: pending.totalRent ? Math.round(pending.totalRent / (pending.days || 1)) : 100,
      securityDeposit: pending.deposit || 500,
      pickupSpot: pending.pickupSpot || 'Campus Central Spot',
      lender: {
        name: 'Equipment Lender',
        studentId: 'CAMPUS-22',
        phone: '+8801700-000000'
      }
    };

    const randomSuffix = Math.floor(1000 + Math.random() * 9000);
    const newToken = `TRX-${randomSuffix}`;
    const newTrxId = `COD-${Date.now().toString().slice(-6)}`;

    const newRental = {
      id: `rent_${Date.now() % 10000}`,
      token: newToken,
      itemId: item.id,
      itemTitle: item.title,
      itemImage: item.image,
      lenderName: item.lender ? item.lender.name : 'Equipment Lender',
      lenderStudentId: item.lender ? item.lender.studentId : 'CAMPUS-22',
      lenderPhone: item.lender ? item.lender.phone : '+8801700-000000',
      renterName: currentUser.name,
      renterStudentId: currentUser.studentId,
      renterPhone: currentUser.phone,
      startDate: pending.startDate || '2026-09-12',
      endDate: pending.endDate || '2026-09-15',
      days: pending.days || 3,
      dailyRate: item.dailyRate || 100,
      totalRent: pending.totalRent || ((item.dailyRate || 100) * 3),
      deposit: pending.deposit || (item.securityDeposit || 500),
      escrowPaid: false,
      escrowPaymentMethod: "Cash on Delivery (COD)",
      escrowTrxId: newTrxId,
      pickupSpot: pending.pickupSpot || item.pickupSpot,
      pickupSpotNote: "Pay exact cash to lender upon item inspection at pickup spot",
      status: "Ready for Handover",
      urgencyBadge: "Pay Cash at Handover",
      statusCode: "ready_handover"
    };

    data.rentals.unshift(newRental);
    CampusRentData.save(data);

    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
    App.closeModal('payment-checkout-modal');

    // Show confirmation toast
    App.showToast(`COD Booking Confirmed! Token: ${newToken}. Pay ৳${pending.totalPayable} in cash at pickup.`, 'success');

    // Redirect to renter-dashboard.html to view token
    setTimeout(() => {
      window.location.href = 'renter-dashboard.html';
    }, 1200);
  }, 1200);
}

// 4. In-Person Handover Token Verifier (Used by Owner)
function initHandoverTokenVerifier() {
  const verifyBtn = document.getElementById('btn-verify-token');
  const tokenInput = document.getElementById('handover_token_input');
  const resultCard = document.getElementById('token-verification-result');

  if (!verifyBtn || !tokenInput) return;

  verifyBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const token = tokenInput.value.trim().toUpperCase();

    if (!token) {
      App.showToast('Please enter the 8-character secret token.', 'warning');
      return;
    }

    const data = CampusRentData.get();
    const match = data.rentals.find(r => r.token === token);

    if (match) {
      // Valid Token
      resultCard.classList.remove('hidden');
      resultCard.innerHTML = `
        <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-slate-800">
          <div class="flex items-center gap-3 mb-3">
            <span class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">
              ✓
            </span>
            <div>
              <h4 class="font-bold text-emerald-900 text-base">Token Verified & Validated!</h4>
              <p class="text-xs text-emerald-700">Refundable Security Deposit Confirmed</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2 text-xs bg-white p-3 rounded-xl border border-emerald-100 mb-3">
            <div><span class="text-slate-400">Renter:</span> <span class="font-bold text-slate-800">${match.renterName}</span> (${match.renterStudentId})</div>
            <div><span class="text-slate-400">Item:</span> <span class="font-bold text-slate-800">${match.itemTitle}</span></div>
            <div><span class="text-slate-400">Total Payable:</span> <span class="font-bold text-emerald-600">৳${match.totalRent + match.deposit}</span></div>
            <div><span class="text-slate-400">Pickup Spot:</span> <span class="font-bold text-slate-800">${match.pickupSpot}</span></div>
          </div>
          <div class="flex items-center gap-2">
            <button onclick="confirmItemRelease('${match.id}')" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs shadow-md transition-all">
              Confirm Physical Handover & Release Item
            </button>
          </div>
        </div>
      `;
      App.showToast('Valid rental token found for ' + match.renterName, 'success');
    } else {
      resultCard.classList.remove('hidden');
      resultCard.innerHTML = `
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-slate-800 flex items-start gap-3">
          <span class="text-red-500 font-bold text-lg">✕</span>
          <div class="text-xs">
            <h5 class="font-bold text-red-900">Token Not Found or Expired</h5>
            <p class="text-red-700 mt-0.5">Please check with the student. (Hint for prototype demo: try <strong>TRX-8291</strong> or <strong>RENT-4092</strong>)</p>
          </div>
        </div>
      `;
      App.showToast('Invalid handover token entered.', 'error');
    }
  });
}

// 5. Confirm Handover
function confirmItemRelease(rentalId) {
  const data = CampusRentData.get();
  const rental = data.rentals.find(r => r.id === rentalId);
  if (rental) {
    rental.status = 'Active';
    rental.statusCode = 'active';
    rental.urgencyBadge = 'In Use (Handover Verified)';
    CampusRentData.save(data);
    App.showToast(`Handover verified! Equipment is now officially checked out to ${rental.renterName}.`, 'success');
    
    const resultCard = document.getElementById('token-verification-result');
    if (resultCard) {
      resultCard.innerHTML = `
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-900 text-xs font-semibold text-center">
          🎉 Item successfully released. Platform payout of ${App.formatBDT(rental.totalRent)} queued upon safe return.
        </div>
      `;
    }
    if (typeof renderOwnerListings === 'function') renderOwnerListings();
  }
}
