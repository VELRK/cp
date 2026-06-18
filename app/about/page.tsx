'use client';

import React from 'react';
import Link from 'next/link';
import { Shield, Target, Compass, Award, Clock, Users, LayoutGrid, HeartHandshake, ArrowRight } from 'lucide-react';

export default function AboutPage() {
  return (
    <div className="nb-body">
      {/* Hero Header Section */}
      <section
        className="text-white text-center py-5 mt-5 d-flex align-items-center justify-content-center position-relative"
        style={{
          background: 'linear-gradient(135deg, #0b2c56 0%, #071f3f 100%)',
          minHeight: '260px',
        }}
      >
        <div className="container position-relative" style={{ zIndex: 2 }}>
          <nav aria-label="breadcrumb" className="d-flex justify-content-center mb-2">
            <ol className="breadcrumb mb-0" style={{ fontSize: '0.85rem' }}>
              <li className="breadcrumb-item"><Link href="/" className="text-white opacity-75 text-decoration-none">Home</Link></li>
              <li className="breadcrumb-item active text-white fw-bold" aria-current="page">/ About Us</li>
            </ol>
          </nav>
          <h1 className="display-5 fw-extrabold text-white mb-2">About Us</h1>
          <p className="lead text-white-50 max-w-2xl mx-auto small" style={{ maxWidth: '600px' }}>
            Building Your Legacy with Trust, Quality, and Excellence
          </p>
        </div>

        {/* Subtle background glow */}
        <div
          className="position-absolute top-50 start-50 translate-middle rounded-circle opacity-10"
          style={{
            width: '400px',
            height: '400px',
            background: 'radial-gradient(circle, #f2b203 0%, transparent 70%)',
            filter: 'blur(50px)',
            zIndex: 1,
          }}
        />
      </section>

      {/* Main Content Section */}
      <section className="py-5 bg-light">
        <div className="container">
          <div className="row justify-content-center mb-5">
            <div className="col-lg-10 text-center">
              <h2 className="display-6 fw-bold text-dark mb-3">Coimbatore Properties</h2>
              <p className="text-secondary fs-5" style={{ lineHeight: '1.8' }}>
                At Coimbatore Properties , we believe that a home is more than just a building—it's a reflection of your dreams, your lifestyle, and your future. With a strong commitment to quality craftsmanship, transparency, and customer satisfaction, we have grown into a trusted name in real estate development.
              </p>
              <p className="text-secondary fs-6 mt-3" style={{ lineHeight: '1.8' }}>
                For years, we have been creating thoughtfully planned homes and residential spaces designed to offer comfort, convenience, and long-lasting value. Every project we undertake is shaped by our passion for innovation, architectural excellence, and attention to detail.
              </p>
            </div>
          </div>

          {/* Mission & Vision Row */}
          <div className="row g-4 mb-5">
            <div className="col-md-6">
              <div
                className="card border-0 shadow-sm h-100 p-4 rounded-4"
                style={{ background: '#fff', borderLeft: '5px solid #0b2c56' }}
              >
                <div className="d-flex align-items-center gap-2 mb-3">
                  <div className="bg-primary text-white p-2.5 rounded-3 d-inline-flex">
                    <Target size={22} className="text-accent" />
                  </div>
                  <h3 className="h4 fw-bold text-dark m-0">Our Mission</h3>
                </div>
                <p className="text-secondary small mb-3" style={{ lineHeight: '1.7' }}>
                  Our mission is simple: To build premium-quality homes that enhance the lives of our customers while upholding the highest standards of professionalism and integrity.
                </p>
                <p className="text-secondary small mb-0" style={{ lineHeight: '1.7' }}>
                  We aim to create modern, sustainable, and beautifully designed living spaces that deliver both aesthetic appeal and functional value.
                </p>
              </div>
            </div>

            <div className="col-md-6">
              <div
                className="card border-0 shadow-sm h-100 p-4 rounded-4"
                style={{ background: '#fff', borderLeft: '5px solid #f2b203' }}
              >
                <div className="d-flex align-items-center gap-2 mb-3">
                  <div className="bg-primary text-white p-2.5 rounded-3 d-inline-flex" style={{ backgroundColor: '#071f3f' }}>
                    <Compass size={22} className="text-accent" />
                  </div>
                  <h3 className="h4 fw-bold text-dark m-0">Our Vision</h3>
                </div>
                <p className="text-secondary small mb-3" style={{ lineHeight: '1.7' }}>
                  To be recognized as a leading real estate builder and listing platform known for trust, timely delivery, superior construction, and customer-centric services.
                </p>
                <p className="text-secondary small mb-0" style={{ lineHeight: '1.7' }}>
                  We strive to set new benchmarks in quality housing and redefine the modern home-buying experience.
                </p>
              </div>
            </div>
          </div>

          {/* What Sets Us Apart */}
          <div className="my-5">
            <h3 className="h3 fw-bold text-center text-dark mb-5">What Sets Us Apart</h3>

            <div className="row g-4">
              {/* Feature 1 */}
              <div className="col-md-6 col-lg-4">
                <div className="card border-0 shadow-sm p-4 h-100 rounded-3 bg-white text-center hover-up">
                  <div className="bg-light p-3 rounded-circle d-inline-flex mx-auto mb-3 text-primary" style={{ width: '60px', height: '60px', alignItems: 'center', justifyContent: 'center' }}>
                    <Award size={30} className="text-accent" />
                  </div>
                  <h4 className="h5 fw-bold text-dark mb-2">Quality Construction</h4>
                  <p className="text-secondary small mb-0">
                    We use high-grade materials, skilled workmanship, and advanced construction practices to ensure every home is built to last.
                  </p>
                </div>
              </div>

              {/* Feature 2 */}
              <div className="col-md-6 col-lg-4">
                <div className="card border-0 shadow-sm p-4 h-100 rounded-3 bg-white text-center hover-up">
                  <div className="bg-light p-3 rounded-circle d-inline-flex mx-auto mb-3 text-primary" style={{ width: '60px', height: '60px', alignItems: 'center', justifyContent: 'center' }}>
                    <Clock size={30} className="text-accent" />
                  </div>
                  <h4 className="h5 fw-bold text-dark mb-2">On-Time Delivery</h4>
                  <p className="text-secondary small mb-0">
                    We understand the importance of time. Our streamlined processes and professional project management guarantee timely delivery.
                  </p>
                </div>
              </div>

              {/* Feature 3 */}
              <div className="col-md-6 col-lg-4">
                <div className="card border-0 shadow-sm p-4 h-100 rounded-3 bg-white text-center hover-up">
                  <div className="bg-light p-3 rounded-circle d-inline-flex mx-auto mb-3 text-primary" style={{ width: '60px', height: '60px', alignItems: 'center', justifyContent: 'center' }}>
                    <Users size={30} className="text-accent" />
                  </div>
                  <h4 className="h5 fw-bold text-dark mb-2">Customer-First Approach</h4>
                  <p className="text-secondary small mb-0">
                    Every step of your home-buying journey is guided with transparency, clear communication, and personalized support.
                  </p>
                </div>
              </div>

              {/* Feature 4 */}
              <div className="col-md-6 col-lg-6">
                <div className="card border-0 shadow-sm p-4 h-100 rounded-3 bg-white text-center hover-up">
                  <div className="bg-light p-3 rounded-circle d-inline-flex mx-auto mb-3 text-primary" style={{ width: '60px', height: '60px', alignItems: 'center', justifyContent: 'center' }}>
                    <LayoutGrid size={30} className="text-accent" />
                  </div>
                  <h4 className="h5 fw-bold text-dark mb-2">Modern Designs</h4>
                  <p className="text-secondary small mb-0">
                    Our homes feature contemporary architecture, smart space planning, and functional layouts crafted for today's lifestyle.
                  </p>
                </div>
              </div>

              {/* Feature 5 */}
              <div className="col-md-6 col-lg-6">
                <div className="card border-0 shadow-sm p-4 h-100 rounded-3 bg-white text-center hover-up">
                  <div className="bg-light p-3 rounded-circle d-inline-flex mx-auto mb-3 text-primary" style={{ width: '60px', height: '60px', alignItems: 'center', justifyContent: 'center' }}>
                    <Shield size={30} className="text-accent" />
                  </div>
                  <h4 className="h5 fw-bold text-dark mb-2">Trusted Expertise</h4>
                  <p className="text-secondary small mb-0">
                    With an experienced team and a strong track record, we bring reliability, consistency, and professional assurance to every project we handle.
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Our Commitment Banner */}
          <div className="mt-5 text-center">
            <div
              className="p-5 rounded-4 shadow-sm text-white position-relative overflow-hidden"
              style={{
                background: 'linear-gradient(135deg, #0b2c56 0%, #0c2340 100%)',
              }}
            >
              <div className="position-relative" style={{ zIndex: 2 }}>
                <div className="d-flex align-items-center justify-content-center gap-2 mb-3">
                  <HeartHandshake size={32} className="text-accent animate-pulse" />
                  <h3 className="h2 fw-bold text-white m-0">Our Commitment</h3>
                </div>
                <p className="fs-5 text-white-50 mx-auto mb-4" style={{ maxWidth: '750px', lineHeight: '1.8' }}>
                  At Coimbatore Properties, we are committed to turning your vision into reality. Whether you are buying your first home, planning an investment, or searching for a premium living space, we ensure that your experience with us is smooth, seamless, and satisfying.
                </p>
                <p className="fs-4 fw-bold text-accent italic mb-4" style={{ fontStyle: 'italic' }}>
                  "We don't just build houses - We build homes that last a lifetime."
                </p>
                <Link
                  href="/search"
                  className="btn btn-warning btn-lg px-4 py-2.5 rounded-pill fw-bold text-primary shadow-sm hover-up d-inline-flex align-items-center gap-2"
                  style={{ backgroundColor: '#f2b203', borderColor: '#f2b203', color: '#0b2c56' }}
                >
                  <span>Explore Villas &amp; Plots</span>
                  <ArrowRight size={18} />
                </Link>
              </div>

              {/* Decorative design vector element */}
              <div
                className="position-absolute end-0 bottom-0 opacity-10"
                style={{
                  transform: 'translate(20%, 20%)',
                  width: '300px',
                  height: '300px',
                  border: '40px solid #f2b203',
                  borderRadius: '50%',
                  zIndex: 1,
                }}
              />
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
