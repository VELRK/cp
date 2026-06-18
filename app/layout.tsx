import type { Metadata } from 'next';
import './globals.css';
import { AuthProvider } from '@/components/AuthContext';
import Navbar from '@/components/common/Navbar';
import Footer from '@/components/common/Footer';
import AuthModals from '@/components/common/AuthModals';

export const metadata: Metadata = {
  title: 'Coimbatore Properties | Buy, Rent & Sale without Brokerage',
  description: 'Search owner-listed properties in Coimbatore. Zero brokerage, transparent pricing, verified listings, and direct connection with owners.',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossOrigin="anonymous" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
      </head>
      <body className="nb-body pt-5" suppressHydrationWarning>
        <AuthProvider>
          <Navbar />
          <main className="nb-main flex-grow-1" id="nbMain">
            {children}
          </main>
          <Footer />
          <AuthModals />
          {/* Bootstrap Bundle Script */}
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossOrigin="anonymous" async />
        </AuthProvider>
      </body>
    </html>
  );
}
