/**
 * CampusRent Hub - Mock Data Store
 * Tailored for Bangladeshi University Campus Operations
 */

const CAMPUS_DATA = {
  currentUser: {
    id: "usr_001",
    name: "Rafiqul Islam",
    studentId: "CSE-22-0145",
    department: "CSE",
    role: "student", // can act as renter and lender
    phone: "+8801712-345678",
    email: "rafiqul.cse22@univ.ac.bd",
    trustScore: 4.95,
    verifiedStatus: "Verified Student",
    ratingCount: 28,
    avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80",
    campus: "Central Campus (Curzon Hall / Engineering Complex)"
  },

  departments: [
    { code: "CSE", name: "Computer Science & Engineering" },
    { code: "EEE", name: "Electrical & Electronic Engineering" },
    { code: "ARCH", name: "Architecture & Design" },
    { code: "PHARM", name: "Pharmacy & Chemistry" },
    { code: "CE", name: "Civil Engineering" },
    { code: "ME", name: "Mechanical Engineering" },
    { code: "GEN", name: "General & Multimedia" }
  ],

  pickupSpots: [
    { id: "spot_1", name: "Central Library Front Gate", note: "Near the fountain, high security zone" },
    { id: "spot_2", name: "Campus Cafeteria Entrance", note: "Busy area near snack stalls" },
    { id: "spot_3", name: "Academic Building-1 Gate", note: "Under the shade near Notice Board" },
    { id: "spot_4", name: "Engineering Lab Complex (3rd Floor)", note: "Outside Hardware Lab 302" },
    { id: "spot_5", name: "TSC (Teacher-Student Centre) Ground", note: "Beside Cafeteria Lawn" },
    { id: "spot_6", name: "Annex Building Lobby", note: "Adjacent to Security Desk" }
  ],

  categories: [
    { id: "calc", name: "Scientific Calculators", icon: "calculator", count: 18 },
    { id: "drafter", name: "Drafter Kits", icon: "ruler", count: 12 },
    { id: "labcoat", name: "Lab Coats & Safety", icon: "shield", count: 24 },
    { id: "iot", name: "Arduino / IoT Kits", icon: "cpu", count: 15 },
    { id: "dslr", name: "DSLR Cameras", icon: "camera", count: 9 },
    { id: "sports", name: "Sports Gear", icon: "activity", count: 14 },
    { id: "instruments", name: "Lab Instruments", icon: "tool", count: 11 }
  ],

  items: [
    {
      id: "item_101",
      title: "Casio fx-991EX ClassWiz Scientific Calculator",
      category: "Scientific Calculators",
      categoryId: "calc",
      department: "CSE",
      condition: "Like New",
      dailyRate: 60,
      securityDeposit: 500,
      pickupSpot: "Central Library Front Gate",
      pickupSpotId: "spot_1",
      image: "https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80",
        "https://images.unsplash.com/photo-1587145820266-a5951ee6f620?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_002",
        name: "Tanvir Ahmed",
        studentId: "CSE-21-0342",
        department: "CSE",
        rating: 4.9,
        reviewsCount: 32,
        verified: true,
        phone: "+8801723-984421"
      },
      specs: {
        "Display": "High-Resolution Natural Textbook LCD",
        "Functions": "552 Functions with Spreadsheet mode",
        "Power Source": "Dual Solar + Two-way Battery (LR44)",
        "Allowed in": "Standard Semester Mid/Final Exams (Non-Programmable)",
        "Includes": "Protective Hard Slide-on Cover"
      },
      description: "Authentic Casio ClassWiz fx-991EX with QR code verification. Battery replaced last month, crystal clear display, all keys responsive. Ideal for engineering math, statistics, and matrix calculation exams.",
      rules: "No marker writing on casing. Please return before 6:00 PM on due date at Central Library.",
      featured: true,
      status: "Available"
    },
    {
      id: "item_102",
      title: "Rotring Precision Mini Drafter & 60cm T-Square Kit",
      category: "Drafter Kits",
      categoryId: "drafter",
      department: "Architecture",
      condition: "Good",
      dailyRate: 120,
      securityDeposit: 800,
      pickupSpot: "Academic Building-1 Gate",
      pickupSpotId: "spot_3",
      image: "https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80",
        "https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_003",
        name: "Nusrat Jahan",
        studentId: "ARCH-20-0081",
        department: "Architecture",
        rating: 4.8,
        reviewsCount: 24,
        verified: true,
        phone: "+8801819-556677"
      },
      specs: {
        "Brand": "Rotring College Drafter",
        "Scale Angles": "360° protractor head with positive locking",
        "Clamp Type": "Heavy-duty steel desk clamp (fits up to 45mm board)",
        "Accessories": "60cm Acrylic T-Square, set square 30/60 & 45/45",
        "Packaging": "Weatherproof zippered canvas carrying case"
      },
      description: "High precision engineering drawing kit. Arms are rigid with zero backlash. Clamps firmly to standard studio drawing boards. Perfect for Architecture studio juries and Civil drawing courses.",
      rules: "Do not bend steel arms. Pack into padded case after use to prevent acrylic scale scratching.",
      featured: true,
      status: "Available"
    },
    {
      id: "item_103",
      title: "Canon EOS 80D DSLR Kit + 18-135mm IS USM Lens",
      category: "DSLR Cameras",
      categoryId: "dslr",
      department: "General",
      condition: "Like New",
      dailyRate: 650,
      securityDeposit: 4000,
      pickupSpot: "TSC Ground / Student Union",
      pickupSpotId: "spot_5",
      image: "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop&q=80",
        "https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_004",
        name: "Farhan Kabir",
        studentId: "CSE-22-0145",
        department: "General",
        rating: 5.0,
        reviewsCount: 19,
        verified: true,
        phone: "+8801688-442211"
      },
      specs: {
        "Sensor": "24.2 MP APS-C Dual Pixel CMOS",
        "Video": "Full HD 1080p @ 60fps with HDR video",
        "Shutter Count": "Low (~14,200 actuations)",
        "Lens": "Canon EF-S 18-135mm f/3.5-5.6 IS USM Nano",
        "Inclusions": "64GB SanDisk Extreme SD Card, 2x LP-E6N Batteries, Charger, Lens Hood, Lowepro Bag"
      },
      description: "Ideal for club media fests, convocation shoots, cultural programs, and department project showcases. Dual pixel autofocus works smoothly for video interviews and event coverage.",
      rules: "Valid University ID card + NID photocopy required at handover. Handover verified via OTP.",
      featured: true,
      status: "Available"
    },
    {
      id: "item_104",
      title: "Arduino Mega 2560 Pro IoT Suite & 37 Sensor Kit",
      category: "Arduino / IoT Kits",
      categoryId: "iot",
      department: "EEE",
      condition: "Like New",
      dailyRate: 90,
      securityDeposit: 600,
      pickupSpot: "Engineering Lab Complex (3rd Floor)",
      pickupSpotId: "spot_4",
      image: "https://images.unsplash.com/photo-1553406830-ef2513450d76?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1553406830-ef2513450d76?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_005",
        name: "Sakib Al Hasan",
        studentId: "EEE-21-0912",
        department: "EEE",
        rating: 4.9,
        reviewsCount: 15,
        verified: true,
        phone: "+8801777-112233"
      },
      specs: {
        "Microcontroller": "ATmega2560 (54 digital I/O, 16 analog inputs)",
        "Modules Included": "ESP8266 Wi-Fi, HC-05 Bluetooth, Ultrasonic, DHT22, Relay, Servo SG90",
        "Breadboards": "2x MB-102 830-point Solderless Breadboards with power module",
        "Wiring": "120x Male-Male, Male-Female jumper wires",
        "Organizer": "Heavy plastic partitioned case"
      },
      description: "Complete hardware toolkit for Embedded Systems, Microprocessors (CSE-316 / EEE-312), and Final Year Capstone prototypes. Fully tested and organized.",
      rules: "Ensure no short-circuited pins when testing 12V supplies. Report any burnt components immediately.",
      featured: true,
      status: "Available"
    },
    {
      id: "item_105",
      title: "Premium White Cotton Lab Coat + Anti-Fog Splash Goggles",
      category: "Lab Coats & Safety",
      categoryId: "labcoat",
      department: "Pharmacy",
      condition: "Good",
      dailyRate: 40,
      securityDeposit: 250,
      pickupSpot: "Campus Cafeteria Entrance",
      pickupSpotId: "spot_2",
      image: "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_006",
        name: "Anika Tabassum",
        studentId: "PHARM-22-0410",
        department: "Pharmacy",
        rating: 4.7,
        reviewsCount: 11,
        verified: true,
        phone: "+8801934-889900"
      },
      specs: {
        "Size": "Medium (Unisex, fits chest 38-42 inches)",
        "Material": "100% Bleached Heavy Cotton (Flame & acid resistant)",
        "Goggles": "EN166 certified chemical splash wrap-around goggles",
        "Cleanliness": "Freshly laundered and ironed before every handover"
      },
      description: "Meets mandatory university chemistry and organic synthesis lab regulations. Thick fabric protects against splashes and stains. Available for emergency single-day or weekly lab rotations.",
      rules: "Strictly return washed or clean. Penalty if returned stained with permanent dyes/nitric acid.",
      featured: false,
      status: "Available"
    },
    {
      id: "item_106",
      title: "Yonex Astrox 88D Pro Badminton Rackets (Pair + 3 Feathers)",
      category: "Sports Gear",
      categoryId: "sports",
      department: "General",
      condition: "Good",
      dailyRate: 80,
      securityDeposit: 450,
      pickupSpot: "TSC Ground / Student Union",
      pickupSpotId: "spot_5",
      image: "https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_007",
        name: "Mehedi Hasan",
        studentId: "ME-21-0199",
        department: "General",
        rating: 4.8,
        reviewsCount: 16,
        verified: true,
        phone: "+8801552-667788"
      },
      specs: {
        "Flex": "Stiff / Head Heavy balance for aggressive smashes",
        "String Tension": "26 lbs BG65 Titanium string",
        "Grip": "Freshly replaced anti-sweat polyurethane overgrip",
        "Inclusions": "Padded Yonex full racket cover + tube with 3 nylon shuttles"
      },
      description: "Great for winter inter-department tournaments or evening matches at the campus gymnasium / TSC courts. Well strung and balanced.",
      rules: "Indoor court or carpet use recommended. Pay repair cost if frame cracks due to clash.",
      featured: false,
      status: "Available"
    },
    {
      id: "item_107",
      title: "Raspberry Pi 4 Model B (8GB RAM) with 3.5\" Touchscreen & Case",
      category: "Arduino / IoT Kits",
      categoryId: "iot",
      department: "CSE",
      condition: "Like New",
      dailyRate: 140,
      securityDeposit: 1200,
      pickupSpot: "Central Library Front Gate",
      pickupSpotId: "spot_1",
      image: "https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_002",
        name: "Tanvir Ahmed",
        studentId: "CSE-21-0342",
        department: "CSE",
        rating: 4.9,
        reviewsCount: 32,
        verified: true,
        phone: "+8801723-984421"
      },
      specs: {
        "Processor": "Broadcom BCM2711, Quad-core Cortex-A72 (ARM v8) 64-bit SoC @ 1.5GHz",
        "RAM": "8GB LPDDR4-3200 SDRAM",
        "Storage": "64GB SanDisk Ultra MicroSD preloaded with Raspberry Pi OS (Debian)",
        "Display": "3.5 inch 480x320 resistive touch LCD with stylus",
        "Power": "Official 5.1V 3.0A USB-C Power Adapter with ON/OFF switch cord"
      },
      description: "Ideal for computer vision (OpenCV), edge computing, robotics, and cloud IoT assignments. Comes with heatsinks and miniature cooling fan installed.",
      rules: "Do not overwrite root partition without backup. Return intact with official charger.",
      featured: false,
      status: "Available"
    },
    {
      id: "item_108",
      title: "Hantek 2D42 3-in-1 Handheld Digital Oscilloscope & Multimeter",
      category: "Lab Instruments",
      categoryId: "instruments",
      department: "EEE",
      condition: "Like New",
      dailyRate: 220,
      securityDeposit: 2500,
      pickupSpot: "Engineering Lab Complex (3rd Floor)",
      pickupSpotId: "spot_4",
      image: "https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600&auto=format&fit=crop&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600&auto=format&fit=crop&q=80"
      ],
      lender: {
        id: "usr_005",
        name: "Sakib Al Hasan",
        studentId: "EEE-21-0912",
        department: "EEE",
        rating: 4.9,
        reviewsCount: 15,
        verified: true,
        phone: "+8801777-112233"
      },
      specs: {
        "Bandwidth": "40MHz Dual Channel digital storage oscilloscope",
        "Real-time Sampling": "250MSa/s",
        "Multimeter": "True RMS 4000 counts (AC/DC Voltage, Current, Resistance, Diode)",
        "Signal Generator": "Built-in Arbitrary Waveform Generator (sine, square, triangle)",
        "Battery": "Dual 18650 Li-ion rechargeable (up to 8h runtime)"
      },
      description: "Portable lab unit for testing analog circuits, power supplies, and digital communication buses at home or in dormitories. Great for students without 24/7 university lab access.",
      rules: "Maximum input voltage 400V (PK-PK). Use probe ground clips safely.",
      featured: false,
      status: "Available"
    }
  ],

  // Renter Active & Past Rentals
  rentals: [
    {
      id: "rent_801",
      token: "TRX-8291",
      itemId: "item_103",
      itemTitle: "Canon EOS 80D DSLR Kit + 18-135mm Lens",
      itemImage: "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop&q=80",
      lenderName: "Farhan Kabir",
      lenderStudentId: "CSE-22-0145",
      lenderPhone: "+8801688-442211",
      renterName: "Rafiqul Islam",
      renterStudentId: "CSE-22-0145",
      renterPhone: "+8801712-345678",
      startDate: "2026-09-11",
      endDate: "2026-09-14",
      days: 3,
      dailyRate: 650,
      totalRent: 1950,
      deposit: 4000,
      escrowPaid: true,
      escrowPaymentMethod: "bKash",
      escrowTrxId: "BK9928172X",
      pickupSpot: "TSC Ground / Student Union",
      pickupSpotNote: "Beside Cafeteria Lawn at 1:30 PM",
      status: "Ready for Handover",
      urgencyBadge: "Pickup Scheduled Tomorrow",
      statusCode: "ready_handover"
    },
    {
      id: "rent_802",
      token: "RENT-4092",
      itemId: "item_101",
      itemTitle: "Casio fx-991EX ClassWiz Scientific Calculator",
      itemImage: "https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80",
      lenderName: "Tanvir Ahmed",
      lenderStudentId: "CSE-21-0342",
      lenderPhone: "+8801723-984421",
      renterName: "Rafiqul Islam",
      renterStudentId: "CSE-22-0145",
      renterPhone: "+8801712-345678",
      startDate: "2026-09-08",
      endDate: "2026-09-10",
      days: 2,
      dailyRate: 60,
      totalRent: 120,
      deposit: 500,
      escrowPaid: true,
      escrowPaymentMethod: "Nagad",
      escrowTrxId: "NG7744119Z",
      pickupSpot: "Central Library Front Gate",
      pickupSpotNote: "Meeting at 5:00 PM for return",
      status: "Active",
      urgencyBadge: "Due Today 5:00 PM",
      statusCode: "active"
    },
    {
      id: "rent_803",
      token: "REQ-1144",
      itemId: "item_102",
      itemTitle: "Rotring Precision Mini Drafter & 60cm T-Square Kit",
      itemImage: "https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80",
      lenderName: "Nusrat Jahan",
      lenderStudentId: "ARCH-20-0081",
      lenderPhone: "+8801819-556677",
      renterName: "Rafiqul Islam",
      renterStudentId: "CSE-22-0145",
      renterPhone: "+8801712-345678",
      startDate: "2026-09-15",
      endDate: "2026-09-17",
      days: 2,
      dailyRate: 120,
      totalRent: 240,
      deposit: 800,
      escrowPaid: false,
      pickupSpot: "Academic Building-1 Gate",
      pickupSpotNote: "Awaiting Owner Confirmation",
      status: "Pending Approval",
      urgencyBadge: "Lender Reviewing",
      statusCode: "pending"
    },
    {
      id: "rent_804",
      token: "RET-9011",
      itemId: "item_105",
      itemTitle: "Premium White Cotton Lab Coat + Splash Goggles",
      itemImage: "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80",
      lenderName: "Anika Tabassum",
      lenderStudentId: "PHARM-22-0410",
      lenderPhone: "+8801934-889900",
      renterName: "Rafiqul Islam",
      renterStudentId: "CSE-22-0145",
      renterPhone: "+8801712-345678",
      startDate: "2026-09-01",
      endDate: "2026-09-03",
      days: 2,
      totalRent: 80,
      deposit: 250,
      depositRefunded: true,
      refundTrx: "REF-BK-3391",
      status: "Returned",
      urgencyBadge: "Deposit ৳250 Refunded",
      statusCode: "returned"
    }
  ],

  // Lender's own listings (when acting as owner)
  ownerListings: [
    {
      id: "own_201",
      title: "Logitech MX Master 3S Wireless Mouse & Dongle",
      category: "Gadgets",
      department: "CSE",
      dailyRate: 70,
      deposit: 1500,
      condition: "Like New",
      status: "Active",
      totalLends: 14,
      pickupSpot: "Central Library Front Gate",
      image: "https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=600&auto=format&fit=crop&q=80"
    },
    {
      id: "own_202",
      title: "TI-Nspire CX II CAS Graphing Calculator",
      category: "Scientific Calculators",
      department: "CSE",
      dailyRate: 150,
      deposit: 3000,
      condition: "Like New",
      status: "Rented Out",
      totalLends: 8,
      pickupSpot: "Engineering Lab Complex (3rd Floor)",
      image: "https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80"
    },
    {
      id: "own_203",
      title: "Boya BY-M1 Lavalier Microphone + 6m Cable",
      category: "Media",
      department: "General",
      dailyRate: 50,
      deposit: 300,
      condition: "Good",
      status: "Active",
      totalLends: 21,
      pickupSpot: "TSC Ground / Student Union",
      image: "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80"
    }
  ],

  // Student verification queue for Admin console
  verificationQueue: [
    {
      id: "verif_501",
      name: "Fahim Muntasir",
      studentId: "CSE-23-0182",
      rollNo: "2305182",
      department: "CSE",
      session: "2022-2023",
      phone: "+8801712-998811",
      email: "fahim.cse23@univ.ac.bd",
      submittedDate: "2026-09-09",
      idCardFront: "https://images.unsplash.com/photo-1589330694653-dad6d3240e2b?w=600&auto=format&fit=crop&q=80",
      status: "Pending Verification"
    },
    {
      id: "verif_502",
      name: "Samira Akhtar",
      studentId: "ARCH-23-0044",
      rollNo: "2301044",
      department: "Architecture",
      session: "2022-2023",
      phone: "+8801898-765432",
      email: "samira.arch23@univ.ac.bd",
      submittedDate: "2026-09-09",
      idCardFront: "https://images.unsplash.com/photo-1589330694653-dad6d3240e2b?w=600&auto=format&fit=crop&q=80",
      status: "Pending Verification"
    },
    {
      id: "verif_503",
      name: "Tahsin Zaman",
      studentId: "EEE-22-0511",
      rollNo: "2206511",
      department: "EEE",
      session: "2021-2022",
      phone: "+8801911-223344",
      email: "tahsin.eee22@univ.ac.bd",
      submittedDate: "2026-09-08",
      idCardFront: "https://images.unsplash.com/photo-1589330694653-dad6d3240e2b?w=600&auto=format&fit=crop&q=80",
      status: "Pending Verification"
    }
  ],

  // Admin Dispute Resolution Cases
  disputes: [
    {
      id: "disp_901",
      itemTitle: "Canon EOS 80D DSLR Kit",
      lender: "Farhan Kabir (CSE-22)",
      renter: "Zubair Hossain (CE-21)",
      disputedAmount: 4000,
      reason: "Item returned with deep scratches on UV filter and minor lens barrel dent.",
      lenderClaim: "UV filter cracked and front element has hairline rub. Repair quote is ৳1,500 from Elephant Road camera market.",
      renterClaim: "Filter was already lightly scratched when collected at TSC. The lens functions normally without optical defects.",
      evidenceImages: [
        "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop&q=80"
      ],
      date: "2026-09-08",
      status: "Under Review"
    },
    {
      id: "disp_902",
      itemTitle: "Rotring College Drafter Kit",
      lender: "Nusrat Jahan (ARCH-20)",
      renter: "Kazi Rayhan (ME-22)",
      disputedAmount: 800,
      reason: "Equipment returned 4 days late without notifying the lender; missed another booking.",
      lenderClaim: "Late return penalty of ৳100/day for 4 days = ৳400 should be deducted from security deposit.",
      renterClaim: "Apologized due to illness; agreed to pay ৳200 reasonable late fee.",
      evidenceImages: [],
      date: "2026-09-07",
      status: "Pending Mediation"
    }
  ]
};

// Initialize or pull from localStorage to keep prototype responsive across navigations
function initStorage() {
  if (!localStorage.getItem('CRH_DATA')) {
    localStorage.setItem('CRH_DATA', JSON.stringify(CAMPUS_DATA));
  }
}

function getStoredData() {
  initStorage();
  try {
    return JSON.parse(localStorage.getItem('CRH_DATA')) || CAMPUS_DATA;
  } catch (e) {
    return CAMPUS_DATA;
  }
}

function saveStoredData(data) {
  localStorage.setItem('CRH_DATA', JSON.stringify(data));
}

// Global accessor
window.CampusRentData = {
  get: getStoredData,
  save: saveStoredData,
  default: CAMPUS_DATA
};
