import './globals.css';
import Navbar from '../components/public/Navbar';
import { Toaster } from 'react-hot-toast';

export const metadata = {
  title: 'Exam Result Management',
  description: 'Professional result publication and certificate verification platform.'
};

export default function RootLayout({ children }) {
  return (
    <html lang="en">
      <body>
        <Navbar />
        <Toaster position="top-right" />
        {children}
      </body>
    </html>
  );
}
