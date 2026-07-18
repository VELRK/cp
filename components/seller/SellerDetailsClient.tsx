'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { Share2, MapPin, Search, Phone, Mail, User, Building, Award, Users, ChevronRight } from 'lucide-react';
import PropertyCard, { Property } from '@/components/property/PropertyCard';
import { searchProperties } from '@/lib/frontendApi';

interface SellerDetailsProps {
  id: string;
}

export default function SellerDetailsClient({ id }: SellerDetailsProps) {
  const [activeTab, setActiveTab] = useState<'buy' | 'rent'>('buy');
  const [buyProperties, setBuyProperties] = useState<Property[]>([]);
  const [rentProperties, setRentProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);

  // Parse ID to mock seller info nicely
  const rawName = id.split('-').slice(0, -1).join(' ');
  const titleName = rawName.toUpperCase() || 'SHREE SAIKRUPA PROPERTIES';
  const displayRole = id.includes('developer') ? 'Developer' : id.includes('owner') ? 'Owner' : 'Agent';
  const isAgent = displayRole === 'Agent';

  useEffect(() => {
    let isMounted = true;
    setLoading(true);
    
    searchProperties({ limit: 8, is_featured: 1 })
      .then((res) => {
        if (!isMounted) return;
        if (res.data?.success && Array.isArray(res.data.items)) {
          const all = res.data.items as Property[];
          setBuyProperties(all.filter((_, i) => i % 2 === 0));
          setRentProperties(all.filter((_, i) => i % 2 !== 0));
        }
      })
      .catch((e) => console.warn(e))
      .finally(() => {
        if (isMounted) setLoading(false);
      });
      
    return () => { isMounted = false; };
  }, [id]);

  const activeProperties = activeTab === 'buy' ? buyProperties : rentProperties;

  return (
    <div className="bg-light pb-5" style={{ minHeight: '100vh', paddingTop: '4.5rem' }}>
      
      {/* Breadcrumb & Top Bar */}
      <div className="bg-white border-bottom py-3 mb-4 shadow-sm">
        <div className="container">
          <nav aria-label="breadcrumb">
            <ol className="breadcrumb mb-0 small">
              <li className="breadcrumb-item"><Link href="/" className="text-decoration-none text-muted">Home</Link></li>
              <li className="breadcrumb-item"><Link href="/agents" className="text-decoration-none text-muted">{displayRole}s</Link></li>
              <li className="breadcrumb-item active text-dark fw-medium" aria-current="page">{titleName}</li>
            </ol>
          </nav>
        </div>
      </div>

      <div className="container">
        <div className="row g-4">
          
          {/* Main Content Area */}
          <div className="col-lg-8">
            
            {/* Seller Profile Header */}
            <div className="card border-0 shadow-sm rounded-3 mb-4">
              <div className="card-body p-4 p-md-5">
                <div className="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-4">
                  
                  {/* Profile Logo */}
                  <div className="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow" 
                       style={{ width: '90px', height: '90px', fontSize: '32px', fontWeight: 'bold', backgroundColor: 'var(--nb-primary)' }}>
                    {titleName.substring(0, 2)}
                  </div>
                  
                  {/* Profile Info */}
                  <div className="flex-grow-1">
                    <div className="d-flex justify-content-between align-items-start">
                      <div>
                        <h1 className="h3 fw-bold text-dark mb-1">{titleName}</h1>
                        <p className="text-secondary mb-2 d-flex align-items-center gap-1">
                          <User size={16} /> Partner at {titleName} • {displayRole}
                        </p>
                        <div className="d-flex flex-wrap gap-2 mt-3">
                          <span className="badge bg-light text-dark border fw-normal"><MapPin size={12} className="me-1"/> Kandivali East</span>
                          <span className="badge bg-light text-dark border fw-normal"><MapPin size={12} className="me-1"/> Borivali East</span>
                          <span className="badge bg-light text-dark border fw-normal"><MapPin size={12} className="me-1"/> Dahisar East</span>
                        </div>
                      </div>
                      <button className="btn btn-outline-secondary btn-sm d-none d-md-flex align-items-center gap-2 rounded-2">
                        <Share2 size={16} /> Share
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Listing Snapshot Classic Table */}
            <div className="card border-0 shadow-sm rounded-3 mb-4">
              <div className="card-header bg-white border-bottom py-3">
                <h3 className="h6 fw-bold text-dark m-0">Professional Snapshot</h3>
              </div>
              <div className="card-body p-0">
                <div className="row g-0 text-center">
                  <div className="col-6 col-md-3 p-4 border-end border-bottom">
                    <Award className="nb-text-brand mb-2" size={24} />
                    <p className="text-muted small mb-1">Experience</p>
                    <p className="fw-bold text-dark m-0 fs-5">18 Years</p>
                  </div>
                  <div className="col-6 col-md-3 p-4 border-end border-bottom">
                    <Building className="nb-text-brand mb-2" size={24} />
                    <p className="text-muted small mb-1">Total Listings</p>
                    <p className="fw-bold text-dark m-0 fs-5">77</p>
                  </div>
                  <div className="col-6 col-md-3 p-4 border-end border-bottom">
                    <User className="nb-text-brand mb-2" size={24} />
                    <p className="text-muted small mb-1">Firm Type</p>
                    <p className="fw-bold text-dark m-0 fs-5">Proprietorship</p>
                  </div>
                  <div className="col-6 col-md-3 p-4 border-bottom">
                    <Users className="nb-text-brand mb-2" size={24} />
                    <p className="text-muted small mb-1">Buyers Served</p>
                    <p className="fw-bold text-dark m-0 fs-5">100+</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Featured Properties */}
            <div className="card border-0 shadow-sm rounded-3 mb-4">
              <div className="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h3 className="h6 fw-bold text-dark m-0">Featured Properties</h3>
              </div>
              <div className="card-body p-4">
                
                {/* Clean Tabs */}
                <ul className="nav nav-pills mb-4 border-bottom pb-3 gap-2">
                  <li className="nav-item">
                    <button 
                      className={`nav-link rounded-pill px-4 ${activeTab === 'buy' ? 'active text-white shadow-sm' : 'bg-light text-dark border'}`}
                      style={activeTab === 'buy' ? { backgroundColor: 'var(--nb-primary)' } : {}}
                      onClick={() => setActiveTab('buy')}
                    >
                      Buy ({buyProperties.length})
                    </button>
                  </li>
                  <li className="nav-item">
                    <button 
                      className={`nav-link rounded-pill px-4 ${activeTab === 'rent' ? 'active text-white shadow-sm' : 'bg-light text-dark border'}`}
                      style={activeTab === 'rent' ? { backgroundColor: 'var(--nb-primary)' } : {}}
                      onClick={() => setActiveTab('rent')}
                    >
                      Rent ({rentProperties.length})
                    </button>
                  </li>
                </ul>

                {/* Property Grid Classic Layout */}
                {loading ? (
                  <div className="text-center py-5">
                    <div className="spinner-border nb-text-brand" role="status"></div>
                  </div>
                ) : (
                  <div className="row g-4">
                    {activeProperties.length > 0 ? (
                      activeProperties.map((property) => (
                        <div key={property.id} className="col-md-6">
                           <div className="card h-100 border rounded-3 overflow-hidden text-decoration-none shadow-sm classic-property-card transition-hover">
                             <div className="position-relative" style={{ height: '180px' }}>
                               <img 
                                 src={property.thumbnail_url || (property.images && Array.isArray(property.images) ? property.images[0] : (typeof property.images === 'string' ? JSON.parse(property.images)[0] : 'https://placehold.co/400x300/e2e8f0/94a3b8?text=No+Image'))} 
                                 alt={property.title}
                                 className="w-100 h-100 object-fit-cover"
                               />
                               <div className="position-absolute top-0 start-0 m-2">
                                 <span className="badge bg-white text-dark shadow-sm">{property.property_type || 'Property'}</span>
                               </div>
                             </div>
                             
                             <div className="card-body">
                               <h5 className="card-title fw-bold text-dark fs-6 mb-1 text-truncate">
                                 {property.title || `${property.bedrooms ? `${property.bedrooms} BHK ` : ''} in ${property.locality}`}
                               </h5>
                               <p className="card-text text-muted small mb-3 text-truncate">
                                 <MapPin size={12} className="me-1"/>
                                 {property.locality}{property.city_name ? `, ${property.city_name}` : ''}
                               </p>
                               
                               <div className="d-flex justify-content-between align-items-center mt-auto">
                                 <h6 className="fw-bold nb-text-brand m-0 fs-5">
                                   {property.price_formatted || `₹${(property.price / 10000000).toFixed(2)} Cr`}
                                 </h6>
                                 <span className="text-muted small">{property.area_sqft || 0} sq.ft</span>
                               </div>
                             </div>
                             <div className="card-footer bg-white border-top p-0">
                               <button className="btn btn-light w-100 py-2 rounded-0 text-dark fw-medium d-flex align-items-center justify-content-center gap-2">
                                 View Details <ChevronRight size={16} />
                               </button>
                             </div>
                           </div>
                        </div>
                      ))
                    ) : (
                      <div className="col-12">
                        <div className="text-center text-muted py-5 border rounded-3 bg-light">
                          <Search size={40} className="text-secondary mb-3 opacity-50" />
                          <p className="m-0">No properties available in this category.</p>
                        </div>
                      </div>
                    )}
                  </div>
                )}
              </div>
            </div>
            
          </div>
          
          {/* Right Sidebar - Classic Contact Form */}
          <div className="col-lg-4">
            <div className="position-sticky" style={{ top: '85px', zIndex: 10 }}>
              <div className="card border-0 shadow-sm rounded-3">
                
                <div className="card-header text-white py-3" style={{ backgroundColor: 'var(--nb-primary)' }}>
                  <h4 className="h6 fw-bold m-0 d-flex align-items-center gap-2">
                    <Mail size={18} /> Contact {titleName.split(' ')[0]}
                  </h4>
                </div>
                
                <div className="card-body p-4">
                  <form onSubmit={(e) => e.preventDefault()}>
                    <div className="mb-3">
                      <label className="form-label small fw-medium text-dark">Full Name</label>
                      <input type="text" className="form-control rounded-2" placeholder="Enter your name" />
                    </div>
                    
                    <div className="mb-3">
                      <label className="form-label small fw-medium text-dark">Phone Number</label>
                      <div className="input-group">
                        <span className="input-group-text bg-light border-end-0 text-muted">+91</span>
                        <input type="tel" className="form-control border-start-0 ps-0" placeholder="Enter mobile number" />
                      </div>
                    </div>
                    
                    <div className="mb-4">
                      <label className="form-label small fw-medium text-dark">Email Address</label>
                      <input type="email" className="form-control rounded-2" placeholder="Enter your email" />
                    </div>

                    <div className="mb-4 bg-light p-3 rounded-2 border">
                      <p className="fw-medium text-dark mb-2 small">Are you a Real Estate Agent?</p>
                      <div className="d-flex gap-2">
                        <div className="form-check form-check-inline">
                          <input className="form-check-input" type="radio" name="agentOptions" id="agentYes" defaultChecked={isAgent} />
                          <label className="form-check-label small" htmlFor="agentYes">Yes</label>
                        </div>
                        <div className="form-check form-check-inline">
                          <input className="form-check-input" type="radio" name="agentOptions" id="agentNo" defaultChecked={!isAgent} />
                          <label className="form-check-label small" htmlFor="agentNo">No</label>
                        </div>
                      </div>
                    </div>

                    <button type="submit" className="btn btn-primary-custom w-100 fw-bold py-2 rounded-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                      <Phone size={18} /> Get Contact Details
                    </button>
                    
                    <p className="text-center text-muted small mt-3 mb-0">
                      By sharing, you agree to our <a href="#" className="text-decoration-none">Terms & Conditions</a>
                    </p>
                  </form>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      
      <style dangerouslySetInnerHTML={{__html: `
        .transition-hover {
          transition: all 0.3s ease;
        }
        .classic-property-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
      `}} />
    </div>
  );
}


