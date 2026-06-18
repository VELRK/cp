'use client';

import React, { useState, useEffect, useRef } from 'react';
import {
  ChevronLeft,
  ChevronRight,
  Calculator,
  Compass,
  Layers,
  MapPin,
  ClipboardList,
  Wallet,
  Calendar,
  DollarSign,
  ArrowRight,
  Info
} from 'lucide-react';

interface BankOption {
  name: string;
  rate: number;
}

export default function ResearchTools() {
  const [activeTab, setActiveTab] = useState<string | null>(null);
  const carouselRef = useRef<HTMLDivElement>(null);
  const consoleRef = useRef<HTMLDivElement>(null);

  // Bank Options for EMI Calculator
  const bankOptions: BankOption[] = [
    { name: 'Select Bank (Optional)', rate: 9.0 },
    { name: 'HDFC Bank', rate: 7.25 },
    { name: 'SBI', rate: 7.25 },
    { name: 'Bajaj Finserv', rate: 7.15 },
    { name: 'LIC HFL', rate: 7.8 },
    { name: 'Canara Bank', rate: 7.15 },
    { name: 'ICICI Bank', rate: 7.35 },
    { name: 'Axis Bank', rate: 7.45 }
  ];

  // 1. EMI Calculator States
  const [selectedBank, setSelectedBank] = useState<string>('Select Bank (Optional)');
  const [loanAmount, setLoanAmount] = useState<number>(5000000);
  const [tenure, setTenure] = useState<number>(10);
  const [interestRate, setInterestRate] = useState<number>(9);

  // 2. Eligibility Calculator States
  const [monthlyIncome, setMonthlyIncome] = useState<number>(150000);
  const [existingEmi, setExistingEmi] = useState<number>(20000);
  const [eligibilityTenure, setEligibilityTenure] = useState<number>(20);
  const [eligibilityRate, setEligibilityRate] = useState<number>(8.5);

  // 3. Affordability Calculator States
  const [downPayment, setDownPayment] = useState<number>(1500000);
  const [monthlyIncomeAff, setMonthlyIncomeAff] = useState<number>(150000);
  const [monthlySavings, setMonthlySavings] = useState<number>(50000);
  const [affRate, setAffRate] = useState<number>(8.5);
  const [affTenure, setAffTenure] = useState<number>(15);

  // 4. Area Converter States (Dynamic Grid)
  const [areaSqFt, setAreaSqFt] = useState<string>('1000');
  const [areaSqYards, setAreaSqYards] = useState<string>('111.11');
  const [areaCents, setAreaCents] = useState<string>('2.296');
  const [areaGrounds, setAreaGrounds] = useState<string>('0.417');
  const [areaAcres, setAreaAcres] = useState<string>('0.023');
  const [areaGuntha, setAreaGuntha] = useState<string>('0.918');

  // 5. Valuation Calculator States
  const [valuationLocality, setValuationLocality] = useState<string>('Avinashi Road');
  const [valuationArea, setValuationArea] = useState<number>(1500);
  const [valuationType, setValuationType] = useState<string>('villa');
  const [valuationAge, setValuationAge] = useState<string>('new');
  const [valuationQuality, setValuationQuality] = useState<string>('premium');

  // 6. Rent Calculator States
  const [rentLocality, setRentLocality] = useState<string>('Avinashi Road');
  const [rentArea, setRentArea] = useState<number>(1200);
  const [rentBhk, setRentBhk] = useState<number>(2);
  const [rentFurnishing, setRentFurnishing] = useState<string>('semi');
  const [rentAmenities, setRentAmenities] = useState<string>('medium');

  // Sync bank selection interest rate
  const handleBankChange = (bankName: string) => {
    setSelectedBank(bankName);
    const bank = bankOptions.find(b => b.name === bankName);
    if (bank && bankName !== 'Select Bank (Optional)') {
      setInterestRate(bank.rate);
    }
  };

  // Scroll carousel
  const scroll = (direction: 'left' | 'right') => {
    if (carouselRef.current) {
      const { scrollLeft, clientWidth } = carouselRef.current;
      const scrollAmount = clientWidth * 0.75;
      carouselRef.current.scrollTo({
        left: direction === 'left' ? scrollLeft - scrollAmount : scrollLeft + scrollAmount,
        behavior: 'smooth'
      });
    }
  };

  // Switch tab and smooth scroll to console
  const handleCardClick = (tabId: string) => {
    setActiveTab(tabId);
    setTimeout(() => {
      consoleRef.current?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 150);
  };

  // Area conversion synchronization
  const syncAreaUnits = (value: string, unit: string) => {
    const numericVal = parseFloat(value);
    if (isNaN(numericVal) || value === '') {
      if (unit === 'sqft') setAreaSqFt(value);
      if (unit === 'sqyards') setAreaSqYards(value);
      if (unit === 'cents') setAreaCents(value);
      if (unit === 'grounds') setAreaGrounds(value);
      if (unit === 'acres') setAreaAcres(value);
      if (unit === 'guntha') setAreaGuntha(value);
      return;
    }

    // Convert everything to Sq.Ft first
    let sqft = 0;
    switch (unit) {
      case 'sqft': sqft = numericVal; break;
      case 'sqyards': sqft = numericVal * 9; break;
      case 'cents': sqft = numericVal * 435.6; break;
      case 'grounds': sqft = numericVal * 2400; break;
      case 'acres': sqft = numericVal * 43560; break;
      case 'guntha': sqft = numericVal * 1089; break;
    }

    // Update all fields
    if (unit !== 'sqft') setAreaSqFt(sqft.toFixed(0));
    else setAreaSqFt(value);

    if (unit !== 'sqyards') setAreaSqYards((sqft / 9).toFixed(2));
    else setAreaSqYards(value);

    if (unit !== 'cents') setAreaCents((sqft / 435.6).toFixed(3));
    else setAreaCents(value);

    if (unit !== 'grounds') setAreaGrounds((sqft / 2400).toFixed(3));
    else setAreaGrounds(value);

    if (unit !== 'acres') setAreaAcres((sqft / 43560).toFixed(4));
    else setAreaAcres(value);

    if (unit !== 'guntha') setAreaGuntha((sqft / 1089).toFixed(3));
    else setAreaGuntha(value);
  };

  // Helper formatting functions
  const formatIndianNumber = (num: number) => {
    const rounded = Math.round(num);
    const str = rounded.toString();
    if (str.length <= 3) return str;
    const lastThree = str.substring(str.length - 3);
    const otherNumbers = str.substring(0, str.length - 3);
    const formatted = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + "," + lastThree;
    return formatted;
  };

  // 1. EMI Calculator logic
  const calculateEMI = () => {
    const P = loanAmount;
    const r = interestRate / (12 * 100);
    const n = tenure * 12;
    if (r === 0) return { emi: P / n, totalInterest: 0, processingFee: P * 0.005 };

    const emi = (P * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
    const totalPayment = emi * n;
    const totalInterest = totalPayment - P;
    const processingFee = P * 0.005; // 0.5% Processing Fee

    return {
      emi: Math.round(emi),
      totalInterest: Math.round(totalInterest),
      processingFee: Math.round(processingFee)
    };
  };

  // 2. Eligibility logic
  const calculateEligibility = () => {
    // FOIR factor = 50%
    const foirMaxEmi = monthlyIncome * 0.5;
    const availableEmi = Math.max(0, foirMaxEmi - existingEmi);

    const r = eligibilityRate / (12 * 100);
    const n = eligibilityTenure * 12;

    if (availableEmi === 0) return { eligibleLoan: 0, estimatedEmi: 0 };

    // PV Formula
    const eligibleLoan = availableEmi * ((1 - Math.pow(1 + r, -n)) / r);
    return {
      eligibleLoan: Math.round(eligibleLoan),
      estimatedEmi: Math.round(availableEmi)
    };
  };

  // 3. Affordability logic
  const calculateAffordability = () => {
    const r = affRate / (12 * 100);
    const n = affTenure * 12;
    // Monthly savings used entirely for EMI
    const loanCapacity = monthlySavings * ((1 - Math.pow(1 + r, -n)) / r);
    const totalBudget = downPayment + loanCapacity;

    return {
      affordableBudget: Math.round(totalBudget),
      loanRequired: Math.round(loanCapacity)
    };
  };

  // 5. Valuation logic
  const calculateValuation = () => {
    // Rates per sqft
    const rates: Record<string, number> = {
      'Sitra': 5500,
      'Avinashi Road': 7500,
      'Saravanampatti': 4500,
      'Gandhipuram': 6500,
      'Peelamedu': 6000
    };
    const baseRate = rates[valuationLocality] || 5000;

    let typeMult = 1.0;
    if (valuationType === 'villa') typeMult = 1.25;
    if (valuationType === 'commercial') typeMult = 1.5;

    let ageMult = 1.0;
    if (valuationAge === 'mid') ageMult = 0.85;
    if (valuationAge === 'old') ageMult = 0.65;

    let qualityMult = 1.0;
    if (valuationQuality === 'premium') qualityMult = 1.2;
    if (valuationQuality === 'luxury') qualityMult = 1.45;

    const rate = baseRate * typeMult * ageMult * qualityMult;
    const totalValuation = valuationArea * rate;

    return {
      rate: Math.round(rate),
      valuation: Math.round(totalValuation)
    };
  };

  // 6. Rent Estimator logic
  const calculateRent = () => {
    const rentRates: Record<string, number> = {
      'Sitra': 12,
      'Avinashi Road': 18,
      'Saravanampatti': 10,
      'Gandhipuram': 15,
      'Peelamedu': 14
    };
    const baseRentRate = rentRates[rentLocality] || 11;

    let bhkMult = 1.0;
    if (rentBhk === 1) bhkMult = 0.85;
    if (rentBhk === 3) bhkMult = 1.25;
    if (rentBhk >= 4) bhkMult = 1.5;

    let furnishingMult = 1.0;
    if (rentFurnishing === 'semi') furnishingMult = 1.15;
    if (rentFurnishing === 'fully') furnishingMult = 1.35;

    let amenitiesMult = 1.0;
    if (rentAmenities === 'medium') amenitiesMult = 1.1;
    if (rentAmenities === 'high') amenitiesMult = 1.25;

    const rate = baseRentRate * bhkMult * furnishingMult * amenitiesMult;
    const estimatedRent = rentArea * rate;

    return {
      rate: rate.toFixed(1),
      rent: Math.round(estimatedRent)
    };
  };

  // EMI computations
  const { emi, totalInterest, processingFee } = calculateEMI();
  const totalEmiSum = loanAmount + totalInterest + processingFee;

  // SVG circular arc computations for Pie Chart (EMI)
  const circumference = 2 * Math.PI * 50; // ~314.159
  const loanPct = totalEmiSum > 0 ? (loanAmount / totalEmiSum) * 100 : 0;
  const interestPct = totalEmiSum > 0 ? (totalInterest / totalEmiSum) * 100 : 0;
  const feePct = totalEmiSum > 0 ? (processingFee / totalEmiSum) * 100 : 0;

  const loanLen = (loanPct / 100) * circumference;
  const interestLen = (interestPct / 100) * circumference;
  const feeLen = (feePct / 100) * circumference;

  // Slices offsets
  const loanOffset = 0;
  const interestOffset = -loanLen;
  const feeOffset = -(loanLen + interestLen);

  return (
    <div className="nb-research-section fade-in-up">
      <div className="nb-research-header d-flex justify-content-between align-items-end">
        <div>
          <h2 className="nb-research-title">User property research tools</h2>
          <p className="nb-research-subtitle m-0">Calculate your borrowing power and understand your financial options</p>
        </div>
        <div className="d-flex gap-2">
          {/* Scroll Arrows */}
          <button className="btn btn-light rounded-circle shadow-sm border border-light p-2" onClick={() => scroll('left')} aria-label="Previous tools">
            <ChevronLeft size={20} />
          </button>
          <button className="btn btn-light rounded-circle shadow-sm border border-light p-2" onClick={() => scroll('right')} aria-label="More tools">
            <ChevronRight size={20} />
          </button>
        </div>
      </div>

      {/* Cards Slider */}
      <div className="nb-research-carousel-wrapper">
        <div className="nb-research-carousel" ref={carouselRef}>
          {/* Card 1: EMI Calculator */}
          <div
            className={`nb-research-card ${activeTab === 'emi' ? 'active' : ''}`}
            onClick={() => handleCardClick('emi')}
          >
            <div className="nb-research-card-content">
              <h3 className="nb-research-card-title">EMI Calculator</h3>
              <p className="nb-research-card-desc">Find your monthly EMI</p>
            </div>
            <div className="nb-research-card-graphic">
              <div className="nb-research-card-bg-circle" />
              <svg width="100" height="90" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                {/* House */}
                <path d="M55 45L78 28L101 45V78H55V45Z" fill="#F59E0B" fillOpacity="0.8" />
                <rect x="71" y="58" width="14" height="20" fill="#B45309" />
                {/* Phone */}
                <rect x="15" y="10" width="34" height="68" rx="6" fill="#78350F" />
                <rect x="18" y="15" width="28" height="52" fill="#FBBF24" />
                <rect x="23" y="22" width="18" height="6" rx="1" fill="#78350F" />
                <rect x="23" y="32" width="18" height="3" rx="0.5" fill="#78350F" />
                <rect x="23" y="38" width="18" height="3" rx="0.5" fill="#78350F" />
                <rect x="23" y="44" width="10" height="3" rx="0.5" fill="#78350F" />
                <circle cx="32" cy="72" r="2.5" fill="#FFFFFF" />
              </svg>
            </div>
            <div className="nb-research-card-arrow">
              <ArrowRight size={14} />
            </div>
          </div>

          {/* Card 2: Eligibility Calculator */}
          <div
            className={`nb-research-card ${activeTab === 'eligibility' ? 'active' : ''}`}
            onClick={() => handleCardClick('eligibility')}
          >
            <div className="nb-research-card-content">
              <h3 className="nb-research-card-title">Eligibility Calculator</h3>
              <p className="nb-research-card-desc">Find your home loan limit</p>
            </div>
            <div className="nb-research-card-graphic">
              <div className="nb-research-card-bg-circle" />
              <svg width="100" height="90" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                {/* House */}
                <path d="M55 48L75 32L95 48V78H55V48Z" fill="#F59E0B" fillOpacity="0.8" />
                <rect x="68" y="58" width="12" height="20" fill="#B45309" />
                {/* Clipboard */}
                <rect x="18" y="12" width="32" height="62" rx="4" fill="#E2E8F0" stroke="#78350F" strokeWidth="2" />
                <rect x="26" y="8" width="16" height="8" rx="2" fill="#78350F" />
                {/* Checks */}
                <path d="M24 28L28 32L36 24" stroke="#D97706" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M24 44L28 48L36 40" stroke="#D97706" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M24 60L28 64L36 56" stroke="#D97706" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </div>
            <div className="nb-research-card-arrow">
              <ArrowRight size={14} />
            </div>
          </div>

          {/* Card 3: Affordability Calculator */}
          <div
            className={`nb-research-card ${activeTab === 'affordability' ? 'active' : ''}`}
            onClick={() => handleCardClick('affordability')}
          >
            <div className="nb-research-card-content">
              <h3 className="nb-research-card-title">Affordability Calculator</h3>
              <p className="nb-research-card-desc">Find the best budget for your home search</p>
            </div>
            <div className="nb-research-card-graphic">
              <div className="nb-research-card-bg-circle" />
              <svg width="100" height="90" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                {/* Pie chart/circle */}
                <circle cx="70" cy="36" r="20" fill="#FDE68A" />
                <path d="M70 36L70 16A20 20 0 0 1 90 36Z" fill="#F59E0B" />
                {/* Wallet */}
                <rect x="15" y="32" width="55" height="42" rx="6" fill="#78350F" />
                <rect x="42" y="44" width="28" height="18" rx="3" fill="#B45309" />
                <circle cx="49" cy="53" r="2.5" fill="#FBBF24" />
                {/* Gold coins */}
                <ellipse cx="28" cy="28" rx="8" ry="4" fill="#FBBF24" stroke="#78350F" strokeWidth="1.5" />
                <ellipse cx="36" cy="22" rx="8" ry="4" fill="#FBBF24" stroke="#78350F" strokeWidth="1.5" />
              </svg>
            </div>
            <div className="nb-research-card-arrow">
              <ArrowRight size={14} />
            </div>
          </div>

          {/* Card 4: Area Calculator */}
          <div
            className={`nb-research-card ${activeTab === 'area' ? 'active' : ''}`}
            onClick={() => handleCardClick('area')}
          >
            <div className="nb-research-card-content">
              <h3 className="nb-research-card-title">Area Calculator</h3>
              <p className="nb-research-card-desc">Calculator for land area conversion</p>
            </div>
            <div className="nb-research-card-graphic">
              <div className="nb-research-card-bg-circle" />
              <svg width="100" height="90" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                {/* House */}
                <path d="M58 48L76 32L94 48V78H58V48Z" fill="#F59E0B" fillOpacity="0.8" />
                {/* Calculator */}
                <rect x="15" y="18" width="36" height="56" rx="4" fill="#D97706" />
                <rect x="19" y="22" width="28" height="14" fill="#FDE68A" />
                {/* Screen digits */}
                <text x="31" y="31" fill="#78350F" fontSize="9" fontWeight="bold" fontFamily="monospace">256</text>
                {/* Keys */}
                <rect x="19" y="42" width="6" height="5" rx="1" fill="#78350F" />
                <rect x="29" y="42" width="6" height="5" rx="1" fill="#78350F" />
                <rect x="39" y="42" width="6" height="5" rx="1" fill="#78350F" />
                <rect x="19" y="52" width="6" height="5" rx="1" fill="#78350F" />
                <rect x="29" y="52" width="6" height="5" rx="1" fill="#78350F" />
                <rect x="39" y="52" width="6" height="5" rx="1" fill="#78350F" />
                <rect x="19" y="62" width="6" height="5" rx="1" fill="#78350F" />
                <rect x="29" y="62" width="16" height="5" rx="1" fill="#78350F" />
              </svg>
            </div>
            <div className="nb-research-card-arrow">
              <ArrowRight size={14} />
            </div>
          </div>

          {/* Card 5: Valuation Calculator */}
          <div
            className={`nb-research-card ${activeTab === 'valuation' ? 'active' : ''}`}
            onClick={() => handleCardClick('valuation')}
          >
            <div className="nb-research-card-content">
              <h3 className="nb-research-card-title">Valuation Calculator</h3>
              <p className="nb-research-card-desc">Calculate the value of your property</p>
            </div>
            <div className="nb-research-card-graphic">
              <div className="nb-research-card-bg-circle" />
              <svg width="100" height="90" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                {/* House */}
                <path d="M50 48L68 32L86 48V76H50V48Z" fill="#F59E0B" fillOpacity="0.8" />
                {/* Magnifier */}
                <path d="M12 66L30 48" stroke="#78350F" strokeWidth="6" strokeLinecap="round" />
                <circle cx="36" cy="38" r="20" fill="#FFFFFF" stroke="#78350F" strokeWidth="4" />
                <circle cx="36" cy="38" r="16" fill="#FEF3C7" />
                <text x="31" y="44" fill="#B45309" fontSize="16" fontWeight="bold">₹</text>
              </svg>
            </div>
            <div className="nb-research-card-arrow">
              <ArrowRight size={14} />
            </div>
          </div>

          {/* Card 6: Rent Value Calculator */}
          <div
            className={`nb-research-card ${activeTab === 'rent' ? 'active' : ''}`}
            onClick={() => handleCardClick('rent')}
          >
            <div className="nb-research-card-content">
              <h3 className="nb-research-card-title">Rent Value Calculator</h3>
              <p className="nb-research-card-desc">Calculate the right rental value of your property</p>
            </div>
            <div className="nb-research-card-graphic">
              <div className="nb-research-card-bg-circle" />
              <svg width="100" height="90" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                {/* House */}
                <path d="M55 48L74 32L93 48V78H55V48Z" fill="#F59E0B" fillOpacity="0.8" />
                {/* Calendar */}
                <rect x="15" y="18" width="34" height="52" rx="4" fill="#FFFFFF" stroke="#78350F" strokeWidth="2.5" />
                <rect x="15" y="18" width="34" height="14" fill="#D97706" />
                {/* Spiral loops */}
                <circle cx="23" cy="18" r="2.5" fill="#78350F" />
                <circle cx="41" cy="18" r="2.5" fill="#78350F" />
                {/* Symbol */}
                <text x="26" y="52" fill="#78350F" fontSize="16" fontWeight="bold">₹</text>
              </svg>
            </div>
            <div className="nb-research-card-arrow">
              <ArrowRight size={14} />
            </div>
          </div>

        </div>
      </div>

      {/* Interactive Console */}
      {activeTab && (
        <div className="nb-calc-console-container" ref={consoleRef}>
          <div className="nb-calc-console">
            {/* Breadcrumbs */}
            <div className="nb-calc-breadcrumbs">
              <span className="cursor-pointer" onClick={() => setActiveTab(null)}>Home</span>
              <span className="mx-2">/</span>
              <span>Home Loans</span>
              <span className="mx-2">/</span>
              <span className="text-capitalize">{activeTab} Calculator</span>
            </div>

            {/* Nav Tabs Inside Console for easy switching */}
            <div className="d-flex flex-wrap gap-2 mb-4 pb-3 border-bottom">
              {[
                { id: 'emi', label: 'EMI Calculator' },
                { id: 'eligibility', label: 'Eligibility Calculator' },
                { id: 'affordability', label: 'Affordability Calculator' },
                { id: 'area', label: 'Area Calculator' },
                { id: 'valuation', label: 'Valuation Calculator' },
                { id: 'rent', label: 'Rent Estimator' }
              ].map(tab => (
                <button
                  key={tab.id}
                  type="button"
                  className={`btn btn-sm px-3 rounded-pill fw-semibold border ${
                    activeTab === tab.id
                      ? 'btn-primary bg-primary text-white border-primary shadow-sm'
                      : 'btn-light text-secondary border-light'
                  }`}
                  onClick={() => setActiveTab(tab.id)}
                  style={activeTab === tab.id ? { backgroundColor: '#7c3aed', borderColor: '#7c3aed' } : {}}
                >
                  {tab.label}
                </button>
              ))}
            </div>

            {/* 1. EMI CALCULATOR */}
            {activeTab === 'emi' && (
              <div className="row g-5">
                <div className="col-lg-7">
                  <h3 className="nb-calc-title">Home Loan EMI Calculator</h3>

                  {/* Select Bank Dropdown */}
                  <div className="nb-calc-input-group">
                    <label className="nb-calc-label">Select Bank (Optional)</label>
                    <select
                      className="nb-calc-select"
                      value={selectedBank}
                      onChange={(e) => handleBankChange(e.target.value)}
                    >
                      {bankOptions.map((bank, index) => (
                        <option key={index} value={bank.name}>
                          {bank.name} {bank.name !== 'Select Bank (Optional)' ? `(${bank.rate}%)` : ''}
                        </option>
                      ))}
                    </select>
                  </div>

                  {/* Loan Amount Slider */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Loan Amount (₹)</label>
                      <span className="fw-semibold text-secondary small">₹{formatIndianNumber(loanAmount)}</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={100000}
                      max={50000000}
                      step={50000}
                      value={loanAmount}
                      onChange={(e) => setLoanAmount(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>1L</span>
                      <span>5Cr</span>
                    </div>
                    <input
                      type="text"
                      className="nb-calc-value-display"
                      value={`₹${formatIndianNumber(loanAmount)}`}
                      onChange={(e) => {
                        const raw = e.target.value.replace(/[^0-9]/g, '');
                        if (raw) setLoanAmount(Number(raw));
                      }}
                    />
                  </div>

                  {/* Tenure Slider */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Tenure (Years)</label>
                      <span className="fw-semibold text-secondary small">{tenure} Years</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={2}
                      max={30}
                      value={tenure}
                      onChange={(e) => setTenure(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>2</span>
                      <span>30</span>
                    </div>
                    <input
                      type="text"
                      className="nb-calc-value-display"
                      value={tenure}
                      onChange={(e) => {
                        const raw = e.target.value.replace(/[^0-9]/g, '');
                        if (raw) setTenure(Number(raw));
                      }}
                    />
                  </div>

                  {/* Interest Rate Slider */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Rate of Interest (%)</label>
                      <span className="fw-semibold text-secondary small">{interestRate}%</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={7}
                      max={15}
                      step={0.05}
                      value={interestRate}
                      onChange={(e) => {
                        setSelectedBank('Select Bank (Optional)');
                        setInterestRate(Number(e.target.value));
                      }}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>7%</span>
                      <span>15%</span>
                    </div>
                    <input
                      type="text"
                      className="nb-calc-value-display"
                      value={interestRate}
                      onChange={(e) => {
                        const raw = e.target.value.replace(/[^0-9.]/g, '');
                        if (raw) setInterestRate(Number(raw));
                      }}
                    />
                  </div>
                </div>

                <div className="col-lg-5">
                  <div className="nb-calc-result-panel">
                    <span className="nb-calc-emi-heading">Your EMI Per Month</span>
                    <h4 className="nb-calc-emi-value" style={{ color: '#7c3aed' }}>₹{formatIndianNumber(emi)}</h4>

                    {/* Dynamic SVGPie Chart */}
                    <div className="nb-calc-chart-container">
                      <svg viewBox="0 0 120 120" className="nb-calc-pie-chart-svg">
                        {/* Slice 1: Loan Amount */}
                        {loanPct > 0 && (
                          <circle
                            cx="60"
                            cy="60"
                            r="50"
                            className="nb-calc-pie-slice"
                            stroke="#312e81" // Dark Navy / Dark Indigo
                            strokeDasharray={`${loanLen} ${circumference}`}
                            strokeDashoffset={loanOffset}
                          />
                        )}
                        {/* Slice 2: Total Interest */}
                        {interestPct > 0 && (
                          <circle
                            cx="60"
                            cy="60"
                            r="50"
                            className="nb-calc-pie-slice"
                            stroke="#7c3aed" // Violet/Purple
                            strokeDasharray={`${interestLen} ${circumference}`}
                            strokeDashoffset={interestOffset}
                          />
                        )}
                        {/* Slice 3: Processing Fees */}
                        {feePct > 0 && (
                          <circle
                            cx="60"
                            cy="60"
                            r="50"
                            className="nb-calc-pie-slice"
                            stroke="#ec4899" // Pink
                            strokeDasharray={`${feeLen} ${circumference}`}
                            strokeDashoffset={feeOffset}
                          />
                        )}
                      </svg>
                    </div>

                    {/* Breakdown */}
                    <div className="nb-calc-breakdown-list">
                      <div className="nb-calc-breakdown-item">
                        <span className="nb-calc-legend-label">
                          <span className="nb-calc-legend-dot" style={{ backgroundColor: '#7c3aed' }} />
                          Total Interest
                        </span>
                        <span className="nb-calc-legend-val">₹{formatIndianNumber(totalInterest)}</span>
                      </div>
                      <div className="nb-calc-breakdown-item">
                        <span className="nb-calc-legend-label">
                          <span className="nb-calc-legend-dot" style={{ backgroundColor: '#ec4899' }} />
                          Processing Fees
                        </span>
                        <span className="nb-calc-legend-val">₹{formatIndianNumber(processingFee)}</span>
                      </div>
                      <div className="nb-calc-breakdown-item">
                        <span className="nb-calc-legend-label">
                          <span className="nb-calc-legend-dot" style={{ backgroundColor: '#312e81' }} />
                          Loan Amount
                        </span>
                        <span className="nb-calc-legend-val">₹{formatIndianNumber(loanAmount)}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* 2. ELIGIBILITY CALCULATOR */}
            {activeTab === 'eligibility' && (
              <div className="row g-5">
                <div className="col-lg-7">
                  <h3 className="nb-calc-title">Home Loan Eligibility Calculator</h3>

                  {/* Monthly Income */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Gross Monthly Income (₹)</label>
                      <span className="fw-semibold text-secondary small">₹{formatIndianNumber(monthlyIncome)}</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={10000}
                      max={1000000}
                      step={5000}
                      value={monthlyIncome}
                      onChange={(e) => setMonthlyIncome(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>10K</span>
                      <span>10L</span>
                    </div>
                    <input
                      type="text"
                      className="nb-calc-value-display"
                      value={`₹${formatIndianNumber(monthlyIncome)}`}
                      onChange={(e) => {
                        const raw = e.target.value.replace(/[^0-9]/g, '');
                        if (raw) setMonthlyIncome(Number(raw));
                      }}
                    />
                  </div>

                  {/* Existing EMI */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Existing Monthly EMIs (if any) (₹)</label>
                      <span className="fw-semibold text-secondary small">₹{formatIndianNumber(existingEmi)}</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={0}
                      max={500000}
                      step={2000}
                      value={existingEmi}
                      onChange={(e) => setExistingEmi(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>0</span>
                      <span>5L</span>
                    </div>
                    <input
                      type="text"
                      className="nb-calc-value-display"
                      value={`₹${formatIndianNumber(existingEmi)}`}
                      onChange={(e) => {
                        const raw = e.target.value.replace(/[^0-9]/g, '');
                        if (raw) setExistingEmi(Number(raw));
                      }}
                    />
                  </div>

                  {/* Tenure */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Tenure (Years)</label>
                      <span className="fw-semibold text-secondary small">{eligibilityTenure} Years</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={5}
                      max={30}
                      value={eligibilityTenure}
                      onChange={(e) => setEligibilityTenure(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>5</span>
                      <span>30</span>
                    </div>
                  </div>

                  {/* Interest rate */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Expected Interest Rate (%)</label>
                      <span className="fw-semibold text-secondary small">{eligibilityRate}%</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={7}
                      max={15}
                      step={0.1}
                      value={eligibilityRate}
                      onChange={(e) => setEligibilityRate(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>7%</span>
                      <span>15%</span>
                    </div>
                  </div>
                </div>

                <div className="col-lg-5">
                  <div className="nb-calc-result-panel text-center">
                    <span className="nb-calc-emi-heading">Maximum Loan Eligibility</span>
                    <h4 className="nb-calc-emi-value text-success" style={{ fontSize: '2.5rem', fontWeight: 800 }}>
                      ₹{formatIndianNumber(calculateEligibility().eligibleLoan)}
                    </h4>

                    <div className="p-3 bg-light rounded-3 w-100 mb-3 text-start">
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Disposable Income Limit (50% FOIR):</span>
                        <span className="fw-semibold text-dark">₹{formatIndianNumber(monthlyIncome * 0.5)}</span>
                      </div>
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Adjusted EMI Capacity:</span>
                        <span className="fw-semibold text-dark">₹{formatIndianNumber(calculateEligibility().estimatedEmi)}</span>
                      </div>
                      <div className="d-flex justify-content-between small text-secondary">
                        <span>Calculated Interest Rate:</span>
                        <span className="fw-semibold text-dark">{eligibilityRate}%</span>
                      </div>
                    </div>

                    <div className="small text-muted text-start mt-2">
                      <Info size={14} className="me-1 d-inline" />
                      Eligibility estimate assumes bank requirements of 50% fixed obligations ratio. Actual offers vary by client credit score.
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* 3. AFFORDABILITY CALCULATOR */}
            {activeTab === 'affordability' && (
              <div className="row g-5">
                <div className="col-lg-7">
                  <h3 className="nb-calc-title">Affordability Calculator</h3>

                  {/* Cash Savings (Down Payment) */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Cash Available (Down Payment) (₹)</label>
                      <span className="fw-semibold text-secondary small">₹{formatIndianNumber(downPayment)}</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={100000}
                      max={20000000}
                      step={100000}
                      value={downPayment}
                      onChange={(e) => setDownPayment(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>1L</span>
                      <span>2Cr</span>
                    </div>
                  </div>

                  {/* Monthly Savings for EMI */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Monthly Savings Allocated to EMI (₹)</label>
                      <span className="fw-semibold text-secondary small">₹{formatIndianNumber(monthlySavings)}</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={5000}
                      max={200000}
                      step={2000}
                      value={monthlySavings}
                      onChange={(e) => setMonthlySavings(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>5K</span>
                      <span>2L</span>
                    </div>
                  </div>

                  {/* Tenure */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Loan Tenure (Years)</label>
                      <span className="fw-semibold text-secondary small">{affTenure} Years</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={5}
                      max={30}
                      value={affTenure}
                      onChange={(e) => setAffTenure(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>5</span>
                      <span>30</span>
                    </div>
                  </div>
                </div>

                <div className="col-lg-5">
                  <div className="nb-calc-result-panel text-center">
                    <span className="nb-calc-emi-heading">Affordable Property Price Budget</span>
                    <h4 className="nb-calc-emi-value text-primary" style={{ fontSize: '2.5rem', fontWeight: 800, color: '#7c3aed' }}>
                      ₹{formatIndianNumber(calculateAffordability().affordableBudget)}
                    </h4>

                    <div className="p-3 bg-light rounded-3 w-100 mb-3 text-start">
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Down Payment (Own Funds):</span>
                        <span className="fw-semibold text-dark">₹{formatIndianNumber(downPayment)}</span>
                      </div>
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Loan Capacity:</span>
                        <span className="fw-semibold text-dark">₹{formatIndianNumber(calculateAffordability().loanRequired)}</span>
                      </div>
                      <div className="d-flex justify-content-between small text-secondary">
                        <span>Allocated Monthly EMI:</span>
                        <span className="fw-semibold text-dark">₹{formatIndianNumber(monthlySavings)}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* 4. AREA CALCULATOR */}
            {activeTab === 'area' && (
              <div>
                <h3 className="nb-calc-title">Land Area Converter</h3>
                <p className="text-secondary small mb-4">Enter a value in any unit to automatically convert it to all other units instantly.</p>

                <div className="nb-calc-converter-grid">
                  <div className="nb-calc-converter-box">
                    <label className="nb-calc-converter-label">Square Feet (Sq.Ft)</label>
                    <input
                      type="text"
                      className="form-control fw-bold fs-5 text-dark"
                      value={areaSqFt}
                      onChange={(e) => syncAreaUnits(e.target.value, 'sqft')}
                    />
                  </div>
                  <div className="nb-calc-converter-box">
                    <label className="nb-calc-converter-label">Square Yards</label>
                    <input
                      type="text"
                      className="form-control fw-bold fs-5 text-dark"
                      value={areaSqYards}
                      onChange={(e) => syncAreaUnits(e.target.value, 'sqyards')}
                    />
                  </div>
                  <div className="nb-calc-converter-box">
                    <label className="nb-calc-converter-label">Cents (Coimbatore Standard)</label>
                    <input
                      type="text"
                      className="form-control fw-bold fs-5 text-dark"
                      value={areaCents}
                      onChange={(e) => syncAreaUnits(e.target.value, 'cents')}
                    />
                  </div>
                  <div className="nb-calc-converter-box">
                    <label className="nb-calc-converter-label">Grounds</label>
                    <input
                      type="text"
                      className="form-control fw-bold fs-5 text-dark"
                      value={areaGrounds}
                      onChange={(e) => syncAreaUnits(e.target.value, 'grounds')}
                    />
                  </div>
                  <div className="nb-calc-converter-box">
                    <label className="nb-calc-converter-label">Acres</label>
                    <input
                      type="text"
                      className="form-control fw-bold fs-5 text-dark"
                      value={areaAcres}
                      onChange={(e) => syncAreaUnits(e.target.value, 'acres')}
                    />
                  </div>
                  <div className="nb-calc-converter-box">
                    <label className="nb-calc-converter-label">Guntha</label>
                    <input
                      type="text"
                      className="form-control fw-bold fs-5 text-dark"
                      value={areaGuntha}
                      onChange={(e) => syncAreaUnits(e.target.value, 'guntha')}
                    />
                  </div>
                </div>

                <div className="mt-4 p-3 bg-light rounded-3 small text-secondary">
                  <strong>Conversion Cheat Sheet:</strong><br />
                  • 1 Cent = 435.6 Sq.Ft | • 1 Ground = 2,400 Sq.Ft | • 1 Acre = 100 Cents = 43,560 Sq.Ft | • 1 Sq.Yard = 9 Sq.Ft | • 1 Guntha = 1,089 Sq.Ft
                </div>
              </div>
            )}

            {/* 5. VALUATION CALCULATOR */}
            {activeTab === 'valuation' && (
              <div className="row g-5">
                <div className="col-lg-7">
                  <h3 className="nb-calc-title">Property Valuation Estimator</h3>

                  {/* Locality select */}
                  <div className="nb-calc-input-group">
                    <label className="nb-calc-label">Select Locality</label>
                    <select
                      className="nb-calc-select"
                      value={valuationLocality}
                      onChange={(e) => setValuationLocality(e.target.value)}
                    >
                      <option value="Sitra">Sitra</option>
                      <option value="Avinashi Road">Avinashi Road</option>
                      <option value="Saravanampatti">Saravanampatti</option>
                      <option value="Gandhipuram">Gandhipuram</option>
                      <option value="Peelamedu">Peelamedu</option>
                    </select>
                  </div>

                  {/* Builtup Area */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Property Area (Sq.Ft)</label>
                      <span className="fw-semibold text-secondary small">{valuationArea} Sq.Ft</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={500}
                      max={8000}
                      step={50}
                      value={valuationArea}
                      onChange={(e) => setValuationArea(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>500</span>
                      <span>8000</span>
                    </div>
                  </div>

                  {/* Property type */}
                  <div className="mb-4">
                    <label className="nb-calc-label">Property Type</label>
                    <div className="d-flex gap-3">
                      {[
                        { id: 'apartment', label: 'Apartment' },
                        { id: 'villa', label: 'Villa/House' },
                        { id: 'commercial', label: 'Commercial' }
                      ].map(type => (
                        <label key={type.id} className="d-flex align-items-center gap-2 cursor-pointer">
                          <input
                            type="radio"
                            name="val-type"
                            className="form-check-input"
                            checked={valuationType === type.id}
                            onChange={() => setValuationType(type.id)}
                          />
                          <span className="small fw-semibold">{type.label}</span>
                        </label>
                      ))}
                    </div>
                  </div>

                  {/* Construction quality */}
                  <div className="mb-4">
                    <label className="nb-calc-label">Finishing Quality</label>
                    <div className="d-flex gap-3">
                      {[
                        { id: 'standard', label: 'Standard' },
                        { id: 'premium', label: 'Premium' },
                        { id: 'luxury', label: 'Luxury' }
                      ].map(qual => (
                        <label key={qual.id} className="d-flex align-items-center gap-2 cursor-pointer">
                          <input
                            type="radio"
                            name="val-quality"
                            className="form-check-input"
                            checked={valuationQuality === qual.id}
                            onChange={() => setValuationQuality(qual.id)}
                          />
                          <span className="small fw-semibold">{qual.label}</span>
                        </label>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="col-lg-5">
                  <div className="nb-calc-result-panel text-center">
                    <span className="nb-calc-emi-heading">Estimated Property Value</span>
                    <h4 className="nb-calc-emi-value text-success" style={{ fontSize: '2.5rem', fontWeight: 800 }}>
                      ₹{formatIndianNumber(calculateValuation().valuation)}
                    </h4>

                    <div className="p-3 bg-light rounded-3 w-100 mb-3 text-start">
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Base Rate (Locality):</span>
                        <span className="fw-semibold text-dark">₹{formatIndianNumber(calculateValuation().rate)} / Sq.Ft</span>
                      </div>
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Total Area:</span>
                        <span className="fw-semibold text-dark">{valuationArea} Sq.Ft</span>
                      </div>
                      <div className="d-flex justify-content-between small text-secondary">
                        <span>Property Type:</span>
                        <span className="fw-semibold text-dark text-capitalize">{valuationType}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* 6. RENT ESTIMATOR */}
            {activeTab === 'rent' && (
              <div className="row g-5">
                <div className="col-lg-7">
                  <h3 className="nb-calc-title">Rent Estimator</h3>

                  {/* Locality select */}
                  <div className="nb-calc-input-group">
                    <label className="nb-calc-label">Select Locality</label>
                    <select
                      className="nb-calc-select"
                      value={rentLocality}
                      onChange={(e) => setRentLocality(e.target.value)}
                    >
                      <option value="Sitra">Sitra</option>
                      <option value="Avinashi Road">Avinashi Road</option>
                      <option value="Saravanampatti">Saravanampatti</option>
                      <option value="Gandhipuram">Gandhipuram</option>
                      <option value="Peelamedu">Peelamedu</option>
                    </select>
                  </div>

                  {/* Property Area */}
                  <div className="nb-calc-input-group">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="nb-calc-label m-0">Property Area (Sq.Ft)</label>
                      <span className="fw-semibold text-secondary small">{rentArea} Sq.Ft</span>
                    </div>
                    <input
                      type="range"
                      className="nb-calc-slider"
                      min={400}
                      max={5000}
                      step={50}
                      value={rentArea}
                      onChange={(e) => setRentArea(Number(e.target.value))}
                    />
                    <div className="nb-calc-slider-limits">
                      <span>400</span>
                      <span>5000</span>
                    </div>
                  </div>

                  {/* Bedrooms select */}
                  <div className="nb-calc-input-group">
                    <label className="nb-calc-label">Bedrooms (BHK)</label>
                    <select
                      className="nb-calc-select"
                      value={rentBhk}
                      onChange={(e) => setRentBhk(Number(e.target.value))}
                    >
                      <option value="1">1 BHK</option>
                      <option value="2">2 BHK</option>
                      <option value="3">3 BHK</option>
                      <option value="4">4+ BHK</option>
                    </select>
                  </div>

                  {/* Furnishing select */}
                  <div className="mb-4">
                    <label className="nb-calc-label">Furnishing Status</label>
                    <div className="d-flex gap-3">
                      {[
                        { id: 'unfurnished', label: 'Unfurnished' },
                        { id: 'semi', label: 'Semi-Furnished' },
                        { id: 'fully', label: 'Fully Furnished' }
                      ].map(furnish => (
                        <label key={furnish.id} className="d-flex align-items-center gap-2 cursor-pointer">
                          <input
                            type="radio"
                            name="rent-furnishing"
                            className="form-check-input"
                            checked={rentFurnishing === furnish.id}
                            onChange={() => setRentFurnishing(furnish.id)}
                          />
                          <span className="small fw-semibold">{furnish.label}</span>
                        </label>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="col-lg-5">
                  <div className="nb-calc-result-panel text-center">
                    <span className="nb-calc-emi-heading">Estimated Monthly Rental</span>
                    <h4 className="nb-calc-emi-value text-primary" style={{ fontSize: '2.5rem', fontWeight: 800, color: '#7c3aed' }}>
                      ₹{formatIndianNumber(calculateRent().rent)} / month
                    </h4>

                    <div className="p-3 bg-light rounded-3 w-100 mb-3 text-start">
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Locality Rate Factor:</span>
                        <span className="fw-semibold text-dark">₹{calculateRent().rate} / Sq.Ft</span>
                      </div>
                      <div className="d-flex justify-content-between mb-2 small text-secondary">
                        <span>BHK:</span>
                        <span className="fw-semibold text-dark">{rentBhk} BHK</span>
                      </div>
                      <div className="d-flex justify-content-between small text-secondary">
                        <span>Furnishing status:</span>
                        <span className="fw-semibold text-dark text-capitalize">{rentFurnishing.replace('-', ' ')}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
