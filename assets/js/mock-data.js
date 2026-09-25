/**
 * CampusRent Hub - Mock Data Store
 * Tailored for Chittagong University Campuses:
 * - Premier University, Chittagong (PUC)
 * - International Islamic University Chittagong (IIUC)
 * - Chittagong University of Engineering and Technology (CUET)
 * - University of Chittagong (CU)
 */

const CAMPUS_DATA = {
  // Pre-configured university institutions in Chittagong
  universities: [
    { code: "PUC", name: "Premier University, Chittagong" },
    { code: "IIUC", name: "International Islamic University Chittagong (IIUC)" },
    { code: "CUET", name: "Chittagong University of Engineering and Technology (CUET)" },
    { code: "CU", name: "University of Chittagong (CU)" }
  ],

  // Registered students & faculty accounts for testing
  users: [
    {
      id: "usr_001",
      name: "Rafiqul Islam",
      studentId: "PUC-22-0145",
      department: "CSE",
      campus: "Premier University, Chittagong",
      campusShort: "PUC",
      role: "renter",
      phone: "+8801712-345678",
      email: "rafiqul@puc.ac.bd",
      password: "123",
      trustScore: 4.95,
      verifiedStatus: "Verified Student",
      ratingCount: 28,
      avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80"
    },
    {
      id: "usr_002",
      name: "Tanvir Ahmed",
      studentId: "CUET-21-0342",
      department: "CSE",
      campus: "Chittagong University of Engineering and Technology (CUET)",
      campusShort: "CUET",
      role: "owner",
      phone: "+8801723-984421",
      email: "tanvir@cuet.ac.bd",
      password: "123",
      trustScore: 4.9,
      verifiedStatus: "Verified Student",
      ratingCount: 32,
      avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80"
    },
    {
      id: "usr_003",
      name: "Farhan Kabir",
      studentId: "IIUC-22-0892",
      department: "CSE",
      campus: "International Islamic University Chittagong (IIUC)",
      campusShort: "IIUC",
      role: "owner",
      phone: "+8801688-442211",
      email: "farhan@iiuc.ac.bd",
      password: "123",
      trustScore: 5.0,
      verifiedStatus: "Verified Student",
      ratingCount: 19,
      avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80"
    },
    {
      id: "usr_004",
      name: "Nusrat Jahan",
      studentId: "CU-20-0081",
      department: "Architecture",
      campus: "University of Chittagong (CU)",
      campusShort: "CU",
      role: "owner",
      phone: "+8801819-556677",
      email: "nusrat@cu.ac.bd",
      password: "123",
      trustScore: 4.8,
      verifiedStatus: "Verified Student",
      ratingCount: 24,
      avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80"
    },
    {
      id: "usr_admin",
      name: "Chittagong Proctorate Admin",
      studentId: "ADMIN-CTG-01",
      department: "Central Administration",
      campus: "Chittagong University Consortium",
      campusShort: "Admin",
      role: "admin",
      phone: "+8801700-000000",
      email: "admin@campusrent.ctg",
      password: "admin",
      trustScore: 5.0,
      verifiedStatus: "Proctor Verified",
      ratingCount: 100,
      avatar: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80"
    }
  ],

  // Active default user (can be changed via login/logout)
  currentUser: null,

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
    { id: "spot_1", name: "Hazari Lane", campus: "PU", note: "Hazari Lane Campus" },
    { id: "spot_2", name: "Wasa", campus: "PU", note: "Wasa Campus Point" },
    { id: "spot_3", name: "GEC Campus", campus: "PU", note: "GEC Campus Point" }
  ],

  categories: [
    { id: "calc", name: "Scientific Calculators", icon: "calculator" },
    { id: "drafter", name: "Drafter Kits", icon: "ruler" },
    { id: "labcoat", name: "Lab Coats & Safety", icon: "shield" },
    { id: "iot", name: "Arduino / IoT Kits", icon: "cpu" },
    { id: "dslr", name: "DSLR Cameras", icon: "camera" },
    { id: "sports", name: "Sports Gear", icon: "activity" },
    { id: "instruments", name: "Lab Instruments", icon: "tool" }
  ],

  items: [],

  // Renter Active & Past Rentals
  rentals: [],

  // Owner's listings
  ownerListings: [],

  // Student verification queue for Admin console
  verificationQueue: [
    {
      id: "verif_501",
      name: "Fahim Muntasir",
      studentId: "CUET-23-0182",
      rollNo: "2305182",
      department: "CSE",
      campus: "Chittagong University of Engineering and Technology (CUET)",
      session: "2022-2023",
      phone: "+8801712-998811",
      email: "fahim.cse23@cuet.ac.bd",
      submittedDate: "2026-09-09",
      idCardFront: "https://images.unsplash.com/photo-1589330694653-dad6d3240e2b?w=600&auto=format&fit=crop&q=80",
      status: "Pending Verification"
    },
    {
      id: "verif_502",
      name: "Samira Akhtar",
      studentId: "PUC-23-0044",
      rollNo: "2301044",
      department: "Architecture",
      campus: "Premier University, Chittagong",
      session: "2022-2023",
      phone: "+8801898-765432",
      email: "samira.arch23@puc.ac.bd",
      submittedDate: "2026-09-09",
      idCardFront: "https://images.unsplash.com/photo-1589330694653-dad6d3240e2b?w=600&auto=format&fit=crop&q=80",
      status: "Pending Verification"
    },
    {
      id: "verif_503",
      name: "Tahsin Zaman",
      studentId: "IIUC-22-0511",
      rollNo: "2206511",
      department: "EEE",
      campus: "International Islamic University Chittagong (IIUC)",
      session: "2021-2022",
      phone: "+8801911-223344",
      email: "tahsin.eee22@iiuc.ac.bd",
      submittedDate: "2026-09-08",
      idCardFront: "https://images.unsplash.com/photo-1589330694653-dad6d3240e2b?w=600&auto=format&fit=crop&q=80",
      status: "Pending Verification"
    }
  ],

  // Admin Dispute Cases
  disputes: []
};

// Ensure currentUser is null by default on dataset
CAMPUS_DATA.currentUser = null;

// Storage version 4 for clean, unauthenticated initial launch with zero dummy items
const STORAGE_KEY = 'CRH_DATA_CLEAN_V4';

function initStorage() {
  if (!localStorage.getItem(STORAGE_KEY)) {
    // Purge legacy storage items from older versions to ensure clean unauthenticated start
    const legacyKeys = [
      'CRH_DATA_CTG_V2',
      'CRH_DATA_V1',
      'CRH_AUTH_TOKEN',
      'CRH_CURRENT_USER',
      'CRH_USER_ROLE',
      'CRH_EXPLICIT_LOGOUT'
    ];
    legacyKeys.forEach(k => localStorage.removeItem(k));
    localStorage.setItem(STORAGE_KEY, JSON.stringify(CAMPUS_DATA));
  }
}

function getStoredData() {
  initStorage();
  try {
    const data = JSON.parse(localStorage.getItem(STORAGE_KEY));
    return data || CAMPUS_DATA;
  } catch (e) {
    return CAMPUS_DATA;
  }
}

function saveStoredData(data) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
}

// Global accessor
window.CampusRentData = {
  get: getStoredData,
  save: saveStoredData,
  default: CAMPUS_DATA
};

