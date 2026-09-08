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
          <h2 className="h4 fw-bold text-dark m-0">User Property Research Tools</h2>
          <p className="text-muted small m-0">Calculate your borrowing power and understand your financial options</p>
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

          {/* Card 1: EMI Calculator */}
          <div
            className={`nb-research-card border-0 rounded-4 p-4 transition-all ${activeTab === 'emi' ? 'active shadow' : 'shadow-sm'}`}
            style={{
              cursor: 'pointer',
              backgroundColor: activeTab === 'emi' ? '#ffffff' : '#f8fafc',
              transform: activeTab === 'emi' ? 'translateY(-5px)' : 'none',
              border: activeTab === 'emi' ? '2px solid #3b82f6' : '1px solid #e2e8f0',
              flexShrink: 0
            }}
            onClick={() => handleCardClick('emi')}
          >
            <div className="d-flex flex-column h-100 justify-content-between position-relative z-1">
              <div className="mb-4">
                <h3 className="fw-bolder fs-5 text-dark mb-1">EMI Calculator</h3>
                <p className="small text-secondary m-0" style={{ fontWeight: 500 }}>Find your monthly EMI</p>
              </div>
              <div className="d-flex justify-content-between align-items-end">
                <div className="position-relative" style={{ width: '80px', height: '80px' }}>
                  <div style={{ position: 'absolute', inset: -15, background: 'radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%)', borderRadius: '50%' }} />
                  <svg width="80" height="72" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ position: 'relative', zIndex: 2, transform: 'translateY(8px)' }}>
                    <path d="M55 45L78 28L101 45V78H55V45Z" fill="#3b82f6" fillOpacity="0.8" />
                    <rect x="71" y="58" width="14" height="20" fill="#1e3a8a" />
                    <rect x="15" y="10" width="34" height="68" rx="6" fill="#0f172a" />
                    <rect x="18" y="15" width="28" height="52" fill="#60a5fa" />
                    <rect x="23" y="22" width="18" height="6" rx="1" fill="#0f172a" />
                    <rect x="23" y="32" width="18" height="3" rx="0.5" fill="#0f172a" />
                    <rect x="23" y="38" width="18" height="3" rx="0.5" fill="#0f172a" />
                    <rect x="23" y="44" width="10" height="3" rx="0.5" fill="#0f172a" />
                    <circle cx="32" cy="72" r="2.5" fill="#FFFFFF" />
                  </svg>
                </div>
                <div className={`rounded-circle p-2 d-flex align-items-center justify-content-center transition-all ${activeTab === 'emi' ? 'text-white shadow-sm' : 'bg-white text-secondary border'}`} style={{ width: '36px', height: '36px', backgroundColor: activeTab === 'emi' ? '#3b82f6' : '#fff' }}>
                  <ArrowRight size={16} />
                </div>
              </div>
            </div>
          </div>

          {/* Card 2: Eligibility Calculator */}
          <div
            className={`nb-research-card border-0 rounded-4 p-4 transition-all ${activeTab === 'eligibility' ? 'active shadow' : 'shadow-sm'}`}
            style={{
              cursor: 'pointer',
              backgroundColor: activeTab === 'eligibility' ? '#ffffff' : '#f8fafc',
              transform: activeTab === 'eligibility' ? 'translateY(-5px)' : 'none',
              border: activeTab === 'eligibility' ? '2px solid #10b981' : '1px solid #e2e8f0',
              flexShrink: 0
            }}
            onClick={() => handleCardClick('eligibility')}
          >
            <div className="d-flex flex-column h-100 justify-content-between position-relative z-1">
              <div className="mb-4">
                <h3 className="fw-bolder fs-5 text-dark mb-1">Eligibility Calculator</h3>
                <p className="small text-secondary m-0" style={{ fontWeight: 500 }}>Find your home loan limit</p>
              </div>
              <div className="d-flex justify-content-between align-items-end">
                <div className="position-relative" style={{ width: '80px', height: '80px' }}>
                  <div style={{ position: 'absolute', inset: -15, background: 'radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%)', borderRadius: '50%' }} />
                  <svg width="80" height="72" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ position: 'relative', zIndex: 2, transform: 'translateY(8px)' }}>
                    <path d="M55 48L75 32L95 48V78H55V48Z" fill="#10b981" fillOpacity="0.8" />
                    <rect x="68" y="58" width="12" height="20" fill="#064e3b" />
                    <rect x="18" y="12" width="32" height="62" rx="4" fill="#f1f5f9" stroke="#0f172a" strokeWidth="2" />
                    <rect x="26" y="8" width="16" height="8" rx="2" fill="#0f172a" />
                    <path d="M24 28L28 32L36 24" stroke="#059669" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                    <path d="M24 44L28 48L36 40" stroke="#059669" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                    <path d="M24 60L28 64L36 56" stroke="#059669" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </div>
                <div className={`rounded-circle p-2 d-flex align-items-center justify-content-center transition-all ${activeTab === 'eligibility' ? 'text-white shadow-sm' : 'bg-white text-secondary border'}`} style={{ width: '36px', height: '36px', backgroundColor: activeTab === 'eligibility' ? '#10b981' : '#fff' }}>
                  <ArrowRight size={16} />
                </div>
              </div>
            </div>
          </div>

          {/* Card 3: Affordability Calculator */}
          <div
            className={`nb-research-card border-0 rounded-4 p-4 transition-all ${activeTab === 'affordability' ? 'active shadow' : 'shadow-sm'}`}
            style={{
              cursor: 'pointer',
              backgroundColor: activeTab === 'affordability' ? '#ffffff' : '#f8fafc',
              transform: activeTab === 'affordability' ? 'translateY(-5px)' : 'none',
              border: activeTab === 'affordability' ? '2px solid #8b5cf6' : '1px solid #e2e8f0',
              flexShrink: 0
            }}
            onClick={() => handleCardClick('affordability')}
          >
            <div className="d-flex flex-column h-100 justify-content-between position-relative z-1">
              <div className="mb-4">
                <h3 className="fw-bolder fs-5 text-dark mb-1">Affordability</h3>
                <p className="small text-secondary m-0" style={{ fontWeight: 500 }}>Best budget for home search</p>
              </div>
              <div className="d-flex justify-content-between align-items-end">
                <div className="position-relative" style={{ width: '80px', height: '80px' }}>
                  <div style={{ position: 'absolute', inset: -15, background: 'radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%)', borderRadius: '50%' }} />
                  <svg width="80" height="72" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ position: 'relative', zIndex: 2, transform: 'translateY(8px)' }}>
                    <circle cx="70" cy="36" r="20" fill="#ddd6fe" />
                    <path d="M70 36L70 16A20 20 0 0 1 90 36Z" fill="#8b5cf6" />
                    <rect x="15" y="32" width="55" height="42" rx="6" fill="#0f172a" />
                    <rect x="42" y="44" width="28" height="18" rx="3" fill="#4c1d95" />
                    <circle cx="49" cy="53" r="2.5" fill="#a78bfa" />
                    <ellipse cx="28" cy="28" rx="8" ry="4" fill="#a78bfa" stroke="#0f172a" strokeWidth="1.5" />
                    <ellipse cx="36" cy="22" rx="8" ry="4" fill="#a78bfa" stroke="#0f172a" strokeWidth="1.5" />
                  </svg>
                </div>
                <div className={`rounded-circle p-2 d-flex align-items-center justify-content-center transition-all ${activeTab === 'affordability' ? 'text-white shadow-sm' : 'bg-white text-secondary border'}`} style={{ width: '36px', height: '36px', backgroundColor: activeTab === 'affordability' ? '#8b5cf6' : '#fff' }}>
                  <ArrowRight size={16} />
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {/* Interactive Console */}

      {activeTab && (
        <div className="nb-calc-console-container mt-5" ref={consoleRef}>
          <div className="nb-calc-console bg-white rounded-5 p-4 p-md-5 shadow-lg border-0" style={{ position: 'relative', overflow: 'hidden' }}>
            {/* Subtle background pattern */}
            <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', opacity: 0.03, pointerEvents: 'none', backgroundImage: 'radial-gradient(#000 1px, transparent 1px)', backgroundSize: '20px 20px' }}></div>
            
            <div style={{ position: 'relative', zIndex: 1 }}>
              {/* Breadcrumbs */}
              <div className="nb-calc-breadcrumbs mb-4 d-flex align-items-center gap-2 small fw-semibold" style={{ color: '#64748b' }}>
                <span className="cursor-pointer hover-text-primary transition-all" onClick={() => setActiveTab(null)}>Home</span>
                <span className="opacity-50">/</span>
                <span>Home Loans</span>
                <span className="opacity-50">/</span>
                <span className="text-capitalize text-dark">{activeTab} Calculator</span>
              </div>

              {/* Nav Tabs Inside Console for easy switching */}
              <div className="d-flex flex-wrap gap-2 mb-5 p-2 rounded-4 justify-content-center" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                {[
                  { id: 'emi', label: 'EMI' },
                  { id: 'eligibility', label: 'Eligibility' },
                  { id: 'affordability', label: 'Affordability' }
                ].map(tab => (
                  <button
                    key={tab.id}
                    type="button"
                    className={`btn border-0 rounded-pill fw-bold px-4 py-2 transition-all`}
                    style={activeTab === tab.id ? { 
                      backgroundColor: '#ffffff', 
                      color: '#0f172a',
                      boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                      transform: 'translateY(-1px)'
                    } : {
                      backgroundColor: 'transparent',
                      color: '#64748b'
                    }}
                    onClick={() => setActiveTab(tab.id)}
                  >
                    {tab.label}
                  </button>
                ))}
              </div>

              {/* 1. EMI CALCULATOR */}
              {activeTab === 'emi' && (
                <div className="row g-5 align-items-center">
                  <div className="col-lg-7">
                    <h3 className="mb-4 fw-bolder section-heading" style={{ color: '#0f172a', fontSize: 'var(--nb-text-section, 24px)', lineHeight: '1.3' }}>Home Loan EMI Calculator</h3>

                    {/* Select Bank Dropdown */}
                    <div className="mb-4">
                      <label className="form-label fw-bold small text-uppercase" style={{ color: '#64748b', letterSpacing: '0.5px' }}>Select Bank (Optional)</label>
                      <select
                        className="form-select form-select-lg rounded-3 shadow-sm border-0"
                        style={{ backgroundColor: '#f1f5f9', color: '#334155' }}
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
                    <div className="mb-4 p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <div className="d-flex justify-content-between align-items-center mb-3">
                        <label className="m-0 fw-bold" style={{ color: '#334155' }}>Loan Amount (₹)</label>
                        <input
                          type="text"
                          className="form-control form-control-sm text-end fw-bolder border-0 rounded-3"
                          style={{ width: '120px', backgroundColor: '#e2e8f0', color: '#0f172a' }}
                          value={`₹${formatIndianNumber(loanAmount)}`}
                          onChange={(e) => {
                            const raw = e.target.value.replace(/[^0-9]/g, '');
                            if (raw) setLoanAmount(Number(raw));
                          }}
                        />
                      </div>
                      <input
                        type="range"
                        className="w-100"
                        style={{ accentColor: '#3b82f6' }}
                        min={100000}
                        max={50000000}
                        step={50000}
                        value={loanAmount}
                        onChange={(e) => setLoanAmount(Number(e.target.value))}
                      />
                      <div className="d-flex justify-content-between mt-1 small fw-semibold text-muted opacity-75">
                        <span>1L</span>
                        <span>5Cr</span>
                      </div>
                    </div>

                    {/* Tenure Slider */}
                    <div className="mb-4 p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <div className="d-flex justify-content-between align-items-center mb-3">
                        <label className="m-0 fw-bold" style={{ color: '#334155' }}>Tenure (Years)</label>
                        <input
                          type="text"
                          className="form-control form-control-sm text-end fw-bolder border-0 rounded-3"
                          style={{ width: '80px', backgroundColor: '#e2e8f0', color: '#0f172a' }}
                          value={tenure}
                          onChange={(e) => {
                            const raw = e.target.value.replace(/[^0-9]/g, '');
                            if (raw) setTenure(Number(raw));
                          }}
                        />
                      </div>
                      <input
                        type="range"
                        className="w-100"
                        style={{ accentColor: '#3b82f6' }}
                        min={2}
                        max={30}
                        value={tenure}
                        onChange={(e) => setTenure(Number(e.target.value))}
                      />
                      <div className="d-flex justify-content-between mt-1 small fw-semibold text-muted opacity-75">
                        <span>2</span>
                        <span>30</span>
                      </div>
                    </div>

                    {/* Interest Rate Slider */}
                    <div className="mb-4 p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <div className="d-flex justify-content-between align-items-center mb-3">
                        <label className="m-0 fw-bold" style={{ color: '#334155' }}>Rate of Interest (%)</label>
                        <input
                          type="text"
                          className="form-control form-control-sm text-end fw-bolder border-0 rounded-3"
                          style={{ width: '80px', backgroundColor: '#e2e8f0', color: '#0f172a' }}
                          value={interestRate}
                          onChange={(e) => {
                            const raw = e.target.value.replace(/[^0-9.]/g, '');
                            if (raw) setInterestRate(Number(raw));
                          }}
                        />
                      </div>
                      <input
                        type="range"
                        className="w-100"
                        style={{ accentColor: '#3b82f6' }}
                        min={7}
                        max={15}
                        step={0.05}
                        value={interestRate}
                        onChange={(e) => {
                          setSelectedBank('Select Bank (Optional)');
                          setInterestRate(Number(e.target.value));
                        }}
                      />
                      <div className="d-flex justify-content-between mt-1 small fw-semibold text-muted opacity-75">
                        <span>7%</span>
                        <span>15%</span>
                      </div>
                    </div>
                  </div>

                  <div className="col-lg-5">
                    <div className="rounded-5 p-4 p-lg-5 text-white h-100 d-flex flex-column justify-content-center position-relative overflow-hidden" 
                         style={{ background: 'linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%)', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)' }}>
                      <div style={{ position: 'absolute', top: '-50px', right: '-50px', width: '200px', height: '200px', background: 'radial-gradient(circle, rgba(59, 130, 246, 0.4) 0%, rgba(0,0,0,0) 70%)', borderRadius: '50%' }}></div>
                      
                      <div className="text-center mb-4 position-relative z-1">
                        <span className="text-white-50 fw-semibold text-uppercase d-block mb-2" style={{ letterSpacing: '1px', fontSize: '0.85rem' }}>Your EMI Per Month</span>
                        <h4 className="display-6 fw-bolder text-white mb-0">₹{formatIndianNumber(emi)}</h4>
                      </div>

                      {/* Dynamic SVGPie Chart */}
                      <div className="d-flex justify-content-center mb-4 position-relative z-1">
                        <svg viewBox="0 0 120 120" width="160" height="160" style={{ transform: 'rotate(-90deg)' }}>
                          {loanPct > 0 && (
                            <circle cx="60" cy="60" r="50" fill="transparent" stroke="#3b82f6" strokeWidth="15" strokeDasharray={`${loanLen} ${circumference}`} strokeDashoffset={loanOffset} style={{ transition: 'stroke-dasharray 0.5s ease' }} />
                          )}
                          {interestPct > 0 && (
                            <circle cx="60" cy="60" r="50" fill="transparent" stroke="#8b5cf6" strokeWidth="15" strokeDasharray={`${interestLen} ${circumference}`} strokeDashoffset={interestOffset} style={{ transition: 'stroke-dasharray 0.5s ease' }} />
                          )}
                          {feePct > 0 && (
                            <circle cx="60" cy="60" r="50" fill="transparent" stroke="#ec4899" strokeWidth="15" strokeDasharray={`${feeLen} ${circumference}`} strokeDashoffset={feeOffset} style={{ transition: 'stroke-dasharray 0.5s ease' }} />
                          )}
                        </svg>
                      </div>

                      {/* Breakdown */}
                      <div className="d-flex flex-column gap-3 position-relative z-1">
                        <div className="d-flex justify-content-between align-items-center p-3 rounded-3" style={{ backgroundColor: 'rgba(255,255,255,0.05)' }}>
                          <div className="d-flex align-items-center gap-2">
                            <div style={{ width: '12px', height: '12px', borderRadius: '50%', backgroundColor: '#8b5cf6' }}></div>
                            <span className="text-white-50 small fw-semibold">Total Interest</span>
                          </div>
                          <span className="fw-bold">₹{formatIndianNumber(totalInterest)}</span>
                        </div>
                        <div className="d-flex justify-content-between align-items-center p-3 rounded-3" style={{ backgroundColor: 'rgba(255,255,255,0.05)' }}>
                          <div className="d-flex align-items-center gap-2">
                            <div style={{ width: '12px', height: '12px', borderRadius: '50%', backgroundColor: '#ec4899' }}></div>
                            <span className="text-white-50 small fw-semibold">Processing Fees</span>
                          </div>
                          <span className="fw-bold">₹{formatIndianNumber(processingFee)}</span>
                        </div>
                        <div className="d-flex justify-content-between align-items-center p-3 rounded-3" style={{ backgroundColor: 'rgba(255,255,255,0.05)' }}>
                          <div className="d-flex align-items-center gap-2">
                            <div style={{ width: '12px', height: '12px', borderRadius: '50%', backgroundColor: '#3b82f6' }}></div>
                            <span className="text-white-50 small fw-semibold">Loan Amount</span>
                          </div>
                          <span className="fw-bold">₹{formatIndianNumber(loanAmount)}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* 2. ELIGIBILITY CALCULATOR */}
              {activeTab === 'eligibility' && (
                <div className="row g-5 align-items-center">
                  <div className="col-lg-7">
                    <h3 className="mb-4 fw-bolder section-heading" style={{ color: '#0f172a', fontSize: 'var(--nb-text-section, 24px)', lineHeight: '1.3' }}>Eligibility Calculator</h3>

                    <div className="mb-4 p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <div className="d-flex justify-content-between align-items-center mb-3">
                        <label className="m-0 fw-bold" style={{ color: '#334155' }}>Gross Monthly Income (₹)</label>
                        <input
                          type="text"
                          className="form-control form-control-sm text-end fw-bolder border-0 rounded-3"
                          style={{ width: '120px', backgroundColor: '#e2e8f0', color: '#0f172a' }}
                          value={`₹${formatIndianNumber(monthlyIncome)}`}
                          onChange={(e) => {
                            const raw = e.target.value.replace(/[^0-9]/g, '');
                            if (raw) setMonthlyIncome(Number(raw));
                          }}
                        />
                      </div>
                      <input
                        type="range"
                        className="w-100"
                        style={{ accentColor: '#10b981' }}
                        min={10000}
                        max={1000000}
                        step={5000}
                        value={monthlyIncome}
                        onChange={(e) => setMonthlyIncome(Number(e.target.value))}
                      />
                      <div className="d-flex justify-content-between mt-1 small fw-semibold text-muted opacity-75">
                        <span>10K</span>
                        <span>10L</span>
                      </div>
                    </div>

                    <div className="mb-4 p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <div className="d-flex justify-content-between align-items-center mb-3">
                        <label className="m-0 fw-bold" style={{ color: '#334155' }}>Existing EMIs (₹)</label>
                        <input
                          type="text"
                          className="form-control form-control-sm text-end fw-bolder border-0 rounded-3"
                          style={{ width: '120px', backgroundColor: '#e2e8f0', color: '#0f172a' }}
                          value={`₹${formatIndianNumber(existingEmi)}`}
                          onChange={(e) => {
                            const raw = e.target.value.replace(/[^0-9]/g, '');
                            if (raw) setExistingEmi(Number(raw));
                          }}
                        />
                      </div>
                      <input
                        type="range"
                        className="w-100"
                        style={{ accentColor: '#10b981' }}
                        min={0}
                        max={500000}
                        step={2000}
                        value={existingEmi}
                        onChange={(e) => setExistingEmi(Number(e.target.value))}
                      />
                      <div className="d-flex justify-content-between mt-1 small fw-semibold text-muted opacity-75">
                        <span>0</span>
                        <span>5L</span>
                      </div>
                    </div>

                    <div className="row g-3">
                      <div className="col-md-6">
                        <div className="p-3 rounded-4 h-100" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                          <label className="mb-2 fw-bold d-block" style={{ color: '#334155' }}>Tenure (Years)</label>
                          <div className="d-flex align-items-center gap-2">
                            <input
                              type="range"
                              className="w-100"
                              style={{ accentColor: '#10b981' }}
                              min={5}
                              max={30}
                              value={eligibilityTenure}
                              onChange={(e) => setEligibilityTenure(Number(e.target.value))}
                            />
                            <span className="fw-bolder" style={{ color: '#0f172a' }}>{eligibilityTenure}</span>
                          </div>
                        </div>
                      </div>
                      <div className="col-md-6">
                        <div className="p-3 rounded-4 h-100" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                          <label className="mb-2 fw-bold d-block" style={{ color: '#334155' }}>Interest Rate (%)</label>
                          <div className="d-flex align-items-center gap-2">
                            <input
                              type="range"
                              className="w-100"
                              style={{ accentColor: '#10b981' }}
                              min={7}
                              max={15}
                              step={0.1}
                              value={eligibilityRate}
                              onChange={(e) => setEligibilityRate(Number(e.target.value))}
                            />
                            <span className="fw-bolder" style={{ color: '#0f172a' }}>{eligibilityRate}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div className="col-lg-5">
                    <div className="rounded-5 p-4 p-lg-5 text-white h-100 d-flex flex-column justify-content-center position-relative overflow-hidden" 
                         style={{ background: 'linear-gradient(135deg, #064e3b 0%, #0f172a 100%)', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)' }}>
                      <div style={{ position: 'absolute', top: '-50px', right: '-50px', width: '200px', height: '200px', background: 'radial-gradient(circle, rgba(16, 185, 129, 0.4) 0%, rgba(0,0,0,0) 70%)', borderRadius: '50%' }}></div>
                      
                      <div className="text-center mb-5 position-relative z-1">
                        <span className="text-white-50 fw-semibold text-uppercase d-block mb-2" style={{ letterSpacing: '1px', fontSize: '0.85rem' }}>Maximum Loan Eligibility</span>
                        <h4 className="display-6 fw-bolder text-white mb-0">₹{formatIndianNumber(calculateEligibility().eligibleLoan)}</h4>
                      </div>

                      <div className="d-flex flex-column gap-3 position-relative z-1">
                        <div className="p-3 rounded-3" style={{ backgroundColor: 'rgba(255,255,255,0.05)', borderLeft: '4px solid #10b981' }}>
                          <div className="d-flex justify-content-between mb-1">
                            <span className="text-white-50 small fw-semibold">Disposable Income (50%)</span>
                            <span className="fw-bold">₹{formatIndianNumber(monthlyIncome * 0.5)}</span>
                          </div>
                          <div className="d-flex justify-content-between mb-1">
                            <span className="text-white-50 small fw-semibold">Adjusted EMI Capacity</span>
                            <span className="fw-bold">₹{formatIndianNumber(calculateEligibility().estimatedEmi)}</span>
                          </div>
                          <div className="d-flex justify-content-between">
                            <span className="text-white-50 small fw-semibold">Interest Rate</span>
                            <span className="fw-bold">{eligibilityRate}%</span>
                          </div>
                        </div>
                      </div>

                      <div className="mt-4 p-3 rounded-3 d-flex gap-2 align-items-start" style={{ backgroundColor: 'rgba(16, 185, 129, 0.1)' }}>
                        <Info size={18} color="#10b981" style={{ flexShrink: 0 }} />
                        <p className="m-0 small text-white-50 lh-sm">Eligibility estimate assumes bank requirements of 50% fixed obligations ratio. Actual offers vary by client credit score.</p>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* 3. AFFORDABILITY CALCULATOR */}
              {activeTab === 'affordability' && (
                <div className="row g-5 align-items-center">
                  <div className="col-lg-7">
                    <h3 className="mb-4 fw-bolder section-heading" style={{ color: '#0f172a', fontSize: 'var(--nb-text-section, 24px)', lineHeight: '1.3' }}>Affordability Calculator</h3>

                    <div className="mb-4 p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <div className="d-flex justify-content-between align-items-center mb-3">
                        <label className="m-0 fw-bold" style={{ color: '#334155' }}>Down Payment Available (₹)</label>
                        <input
                          type="text"
                          className="form-control form-control-sm text-end fw-bolder border-0 rounded-3"
                          style={{ width: '120px', backgroundColor: '#e2e8f0', color: '#0f172a' }}
                          value={`₹${formatIndianNumber(downPayment)}`}
                          onChange={(e) => {
                            const raw = e.target.value.replace(/[^0-9]/g, '');
                            if (raw) setDownPayment(Number(raw));
                          }}
                        />
                      </div>
                      <input
                        type="range"
                        className="w-100"
                        style={{ accentColor: '#f59e0b' }}
                        min={100000}
                        max={20000000}
                        step={100000}
                        value={downPayment}
                        onChange={(e) => setDownPayment(Number(e.target.value))}
                      />
                      <div className="d-flex justify-content-between mt-1 small fw-semibold text-muted opacity-75">
                        <span>1L</span>
                        <span>2Cr</span>
                      </div>
                    </div>

                    <div className="mb-4 p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <div className="d-flex justify-content-between align-items-center mb-3">
                        <label className="m-0 fw-bold" style={{ color: '#334155' }}>Monthly Savings for EMI (₹)</label>
                        <input
                          type="text"
                          className="form-control form-control-sm text-end fw-bolder border-0 rounded-3"
                          style={{ width: '120px', backgroundColor: '#e2e8f0', color: '#0f172a' }}
                          value={`₹${formatIndianNumber(monthlySavings)}`}
                          onChange={(e) => {
                            const raw = e.target.value.replace(/[^0-9]/g, '');
                            if (raw) setMonthlySavings(Number(raw));
                          }}
                        />
                      </div>
                      <input
                        type="range"
                        className="w-100"
                        style={{ accentColor: '#f59e0b' }}
                        min={5000}
                        max={200000}
                        step={2000}
                        value={monthlySavings}
                        onChange={(e) => setMonthlySavings(Number(e.target.value))}
                      />
                      <div className="d-flex justify-content-between mt-1 small fw-semibold text-muted opacity-75">
                        <span>5K</span>
                        <span>2L</span>
                      </div>
                    </div>

                    <div className="p-3 rounded-4" style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0' }}>
                      <label className="mb-2 fw-bold d-block" style={{ color: '#334155' }}>Loan Tenure (Years)</label>
                      <div className="d-flex align-items-center gap-3">
                        <input
                          type="range"
                          className="w-100"
                          style={{ accentColor: '#f59e0b' }}
                          min={5}
                          max={30}
                          value={affTenure}
                          onChange={(e) => setAffTenure(Number(e.target.value))}
                        />
                        <span className="fw-bolder px-3 py-1 rounded-3" style={{ backgroundColor: '#e2e8f0', color: '#0f172a' }}>{affTenure}</span>
                      </div>
                    </div>
                  </div>

                  <div className="col-lg-5">
                    <div className="rounded-5 p-4 p-lg-5 text-white h-100 d-flex flex-column justify-content-center position-relative overflow-hidden" 
                         style={{ background: 'linear-gradient(135deg, #78350f 0%, #0f172a 100%)', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)' }}>
                      <div style={{ position: 'absolute', top: '-50px', right: '-50px', width: '200px', height: '200px', background: 'radial-gradient(circle, rgba(245, 158, 11, 0.4) 0%, rgba(0,0,0,0) 70%)', borderRadius: '50%' }}></div>
                      
                      <div className="text-center mb-5 position-relative z-1">
                        <span className="text-white-50 fw-semibold text-uppercase d-block mb-2" style={{ letterSpacing: '1px', fontSize: '0.85rem' }}>Affordable Property Budget</span>
                        <h4 className="display-6 fw-bolder text-white mb-0">₹{formatIndianNumber(calculateAffordability().affordableBudget)}</h4>
                      </div>

                      <div className="d-flex flex-column gap-3 position-relative z-1">
                        <div className="p-3 rounded-3" style={{ backgroundColor: 'rgba(255,255,255,0.05)', borderLeft: '4px solid #f59e0b' }}>
                          <div className="d-flex justify-content-between mb-2">
                            <span className="text-white-50 small fw-semibold">Down Payment (Own Funds)</span>
                            <span className="fw-bold">₹{formatIndianNumber(downPayment)}</span>
                          </div>
                          <div className="d-flex justify-content-between mb-2">
                            <span className="text-white-50 small fw-semibold">Loan Capacity</span>
                            <span className="fw-bold">₹{formatIndianNumber(calculateAffordability().loanRequired)}</span>
                          </div>
                          <div className="d-flex justify-content-between">
                            <span className="text-white-50 small fw-semibold">Allocated Monthly EMI</span>
                            <span className="fw-bold">₹{formatIndianNumber(monthlySavings)}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}




            </div>
          </div>
        </div>
      )}
    </div>
  );
}
