'use client';

import React from 'react';
import { Phone, X, User } from 'lucide-react';

interface OwnerPhoneModalProps {
  show: boolean;
  onClose: () => void;
  ownerName?: string | null;
  ownerPhone?: string | null;
  propertyTitle?: string | null;
}

export default function OwnerPhoneModal({
  show,
  onClose,
  ownerName,
  ownerPhone,
  propertyTitle,
}: OwnerPhoneModalProps) {
  if (!show) return null;

  const phone = ownerPhone?.trim() || '';
  const telHref = phone ? `tel:${phone.replace(/\s+/g, '')}` : undefined;

  return (
    <div
      className="modal fade show d-block"
      tabIndex={-1}
      role="dialog"
      aria-modal="true"
      aria-labelledby="ownerPhoneModalTitle"
      style={{ backgroundColor: 'rgba(7, 31, 63, 0.45)', backdropFilter: 'blur(6px)', zIndex: 1050 }}
      onClick={onClose}
    >
      <div
        className="modal-dialog modal-dialog-centered"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="modal-content border-0 shadow-lg rounded-4">
          <div className="modal-header border-bottom-0 pb-0">
            <h5 className="modal-title fw-bold text-dark" id="ownerPhoneModalTitle">
              Owner Contact
            </h5>
            <button type="button" className="btn-close" aria-label="Close" onClick={onClose} />
          </div>
          <div className="modal-body pt-2 pb-4 text-center">
            {propertyTitle && (
              <p className="text-muted small mb-3">{propertyTitle}</p>
            )}
            {ownerName && (
              <div className="d-flex align-items-center justify-content-center gap-2 mb-3 text-secondary small">
                <User size={16} />
                <span>{ownerName}</span>
              </div>
            )}
            {phone ? (
              <>
                <div
                  className="rounded-3 py-3 px-4 mb-3 mx-auto"
                  style={{ backgroundColor: '#f0f7fb', maxWidth: 280 }}
                >
                  <Phone size={28} className="text-primary mb-2" />
                  <div className="fs-4 fw-bold text-dark">{phone}</div>
                </div>
                {telHref && (
                  <a href={telHref} className="btn btn-primary rounded-pill px-4">
                    Call Now
                  </a>
                )}
              </>
            ) : (
              <p className="text-muted mb-0">Owner phone number is not available for this listing.</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
